<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model bersama untuk mutasi wallet yang harus atomik.
 * Tidak digunakan oleh controller API/Android.
 */
class M_transaksi_dompet extends CI_Model
{
    public function aktifkan_dompet($id_siswa, $user)
    {
        $this->db->trans_begin();
        $student = $this->db->query(
            'SELECT id, nis, nama_lengkap, status_siswa, status_aktif FROM siswa WHERE id = ? FOR UPDATE',
            [(int) $id_siswa]
        )->row_array();

        if (!$student) {
            $this->db->trans_rollback();
            return $this->error('Siswa tidak ditemukan.', 404);
        }
        if ($student['status_siswa'] !== 'AKTIF' || $student['status_aktif'] !== 'AKTIF') {
            $this->db->trans_rollback();
            return $this->error('Dompet hanya dapat diaktifkan untuk siswa aktif.', 422);
        }

        $wallet = $this->db->query(
            'SELECT id FROM saldo_dompet_siswa WHERE id_siswa = ? FOR UPDATE',
            [(int) $id_siswa]
        )->row_array();
        if ($wallet) {
            $this->db->trans_rollback();
            return $this->error('Dompet siswa sudah pernah diaktifkan.', 409);
        }

        $active_qr = $this->db->query(
            "SELECT id FROM qr_siswa WHERE id_siswa = ? AND status_qr = 'AKTIF' FOR UPDATE",
            [(int) $id_siswa]
        )->row_array();
        if ($active_qr) {
            $this->db->trans_rollback();
            return $this->error('Siswa sudah mempunyai QR aktif. Aktivasi dibatalkan untuk mencegah data ganda.', 409);
        }

        $now = $this->audit_fields($user);
        $this->db->insert('saldo_dompet_siswa', [
            'id_siswa' => (int) $student['id'],
            'nis' => $student['nis'],
            'nama_siswa' => $student['nama_lengkap'],
            'saldo' => 0,
            'status_dompet' => 'AKTIF',
            'tanggal_update' => $now['tanggal'],
            'waktu_update' => $now['waktu'],
            'id_user_update' => $now['id_user'],
            'nama_user_update' => $now['nama_user'],
        ]);
        $wallet_id = (int) $this->db->insert_id();

        $raw_token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $raw_token);
        $display_code = strtoupper(substr(bin2hex(random_bytes(8)), 0, 12));
        $this->db->insert('qr_siswa', [
            'id_siswa' => (int) $student['id'],
            'nis' => $student['nis'],
            'token_qr_hash' => $token_hash,
            'kode_tampil' => $display_code,
            'status_qr' => 'AKTIF',
            'versi_qr' => 1,
            'tanggal_buat' => $now['tanggal'],
            'waktu_buat' => $now['waktu'],
            'id_user' => $now['id_user'],
            'nama_user' => $now['nama_user'],
        ]);
        $qr_id = (int) $this->db->insert_id();

        $global = $this->db->select('id')->order_by('id', 'DESC')->limit(1)->get('pengaturan_dompet')->row_array();
        $this->db->insert('log_aktivitas_dompet', [
            'id_user' => $now['id_user'],
            'nama_user' => $now['nama_user'],
            'level' => (string) ($user['level'] ?? ''),
            'modul' => 'STATUS_DOMPET_SISWA',
            'aksi' => 'AKTIVASI_DOMPET',
            'id_referensi' => (int) $student['id'],
            'no_referensi' => $student['nis'],
            'keterangan' => 'Dompet diaktifkan dengan saldo awal 0; '
                .'QR aktif dibuat; limit memakai pengaturan global'
                .($global ? '.' : ' (pengaturan global belum tersedia).'),
            'alamat_ip' => $this->input->ip_address(),
            'tanggal' => $now['tanggal'],
            'waktu' => $now['waktu'],
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->error('Aktivasi dompet gagal dan seluruh perubahan telah dibatalkan.', 500);
        }
        $this->db->trans_commit();

        return [
            'success' => true,
            'data' => [
                'id_siswa' => (int) $student['id'],
                'id_wallet' => $wallet_id,
                'id_qr' => $qr_id,
                'kode_tampil' => $display_code,
                'saldo' => 0,
            ],
        ];
    }

    public function verifikasi_topup($id_pengajuan, $payment, $user, $failure_step = null)
    {
        $this->db->trans_begin();
        $request = $this->db->query(
            'SELECT * FROM pengajuan_isi_saldo WHERE id = ? FOR UPDATE',
            [(int) $id_pengajuan]
        )->row_array();
        if (!$request) {
            $this->db->trans_rollback();
            return $this->error('Pengajuan isi saldo tidak ditemukan.', 404);
        }

        $existing = $this->db->get_where('riwayat_isi_saldo', ['id_pengajuan' => (int) $id_pengajuan])->row_array();
        if ($existing) {
            $this->db->trans_rollback();

            return [
                'success' => true,
                'data' => [
                    'id_riwayat' => (int) $existing['id'],
                    'idempotent' => true,
                ],
            ];
        }
        if ($request['status_pengajuan'] !== 'MENUNGGU_VERIFIKASI') {
            $this->db->trans_rollback();
            return $this->error('Pengajuan sudah diproses atau tidak lagi menunggu verifikasi.', 409);
        }

        $principal = (int) $request['nominal_saldo'];
        $fee = (int) $request['biaya_layanan'];
        if ($principal <= 0 || $fee < 0 || (int) $request['total_transfer'] !== $principal + $fee) {
            $this->db->trans_rollback();
            return $this->error('Nominal pengajuan tidak konsisten.', 422);
        }

        $payment_result = $this->resolve_payment_destination($payment, true);
        if (empty($payment_result['success'])) {
            $this->db->trans_rollback();
            return $payment_result;
        }
        $destination = $payment_result['data'];

        $wallet = $this->db->query(
            "SELECT sd.*
             FROM saldo_dompet_siswa sd
             JOIN siswa s ON s.id = sd.id_siswa
             WHERE sd.id_siswa = ?
                AND sd.status_dompet = 'AKTIF'
                AND s.status_siswa = 'AKTIF'
             FOR UPDATE",
            [(int) $request['id_siswa']]
        )->row_array();
        if (!$wallet) {
            $this->db->trans_rollback();
            return $this->error('Wallet aktif siswa tidak ditemukan.', 422);
        }

        $kasbank_id = (int) $destination['id_kasbank'];
        $before = (int) $wallet['saldo'];
        $after = $before + $principal;
        $now = $this->audit_fields($user);
        $number = 'ISW-' . date('Ymd') . '-' . strtoupper(substr(hash('sha256', $request['no_pengajuan']), 0, 12));
        $this->db->insert('riwayat_isi_saldo', [
            'no_transaksi' => $number,
            'id_pengajuan' => (int) $request['id'],
            'sumber_transaksi' => 'PENGAJUAN_WALI',
            'id_siswa' => (int) $request['id_siswa'],
            'nis' => $request['nis'],
            'nama_siswa' => $request['nama_siswa'],
            'nominal_saldo' => $principal,
            'biaya_layanan' => $fee,
            'total_pembayaran' => $principal + $fee,
            'metode_pembayaran' => $destination['metode_pembayaran'],
            'saldo_sebelum' => $before,
            'saldo_sesudah' => $after,
            'id_kasbank' => $kasbank_id,
            'nama_kasbank' => $destination['nama_kasbank'],
            'bukti' => $request['bukti_transfer'],
            'keterangan' => 'Verifikasi pengajuan '.$request['no_pengajuan']
                .' melalui '.$destination['metode_pembayaran'],
            'status_transaksi' => 'BERHASIL',
            'tanggal' => $now['tanggal'],
            'waktu' => $now['waktu'],
            'id_user' => $now['id_user'],
            'nama_user' => $now['nama_user'],
        ]);
        $history_id = (int) $this->db->insert_id();
        if ($this->inject_failure($failure_step, 'after_history')) {
            return $this->rollback_failure();
        }

        $this->db->where('id', $wallet['id'])->update('saldo_dompet_siswa', [
            'saldo' => $after,
            'tanggal_update' => $now['tanggal'],
            'waktu_update' => $now['waktu'],
            'id_user_update' => $now['id_user'],
            'nama_user_update' => $now['nama_user'],
        ]);
        $this->db->where('id', $history_id)->update('riwayat_isi_saldo', [
            'id_pemasukan_titipan' => 0,
            'id_pemasukan_biaya' => 0,
        ]);
        
        $this->db->where('id', $request['id'])->update('pengajuan_isi_saldo', [
            'status_pengajuan' => 'DIVERIFIKASI',
            'metode_pembayaran' => $destination['metode_pembayaran'],
            'id_rekening_dompet' => $destination['id_rekening_dompet'],
            'id_kasbank' => $kasbank_id,
            'nama_kasbank' => $destination['nama_kasbank'],
            'tanggal_verifikasi' => $now['tanggal'],
            'waktu_verifikasi' => $now['waktu'],
            'id_user_verifikasi' => $now['id_user'],
            'nama_user_verifikasi' => $now['nama_user'],
        ]);
        $description = 'Pokok Rp '.$principal
            .', fee Rp '.$fee
            .', metode '.$destination['metode_pembayaran']
            .', kas/bank '.$destination['nama_kasbank']
            .', saldo '.$before
            .' menjadi '.$after.'.';

        $this->log(
            $user,
            'ISI_SALDO',
            'VERIFIKASI',
            $history_id,
            $number,
            $description
        );

        if ($this->inject_failure($failure_step, 'before_commit')) {
            return $this->rollback_failure();
        }

        if ($this->db->trans_status() === false) {
            return $this->rollback_failure(
                'Verifikasi gagal dan seluruh komponen telah di-rollback.'
            );
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'data' => [
                'id_riwayat' => $history_id,
                'no_transaksi' => $number,
                'saldo_sebelum' => $before,
                'saldo_sesudah' => $after,
                'idempotent' => false,
            ],
        ];
    }

    /**
     * Top-up yang dibuat Admin langsung diposting tanpa antrean verifikasi.
     * Pengajuan tetap dibuat sebagai jejak audit dan kunci idempotensi.
     */
    public function topup_langsung_admin($id_siswa, $principal, $request_key, $payment, $user, $failure_step = null)
    {
        $id_siswa = (int) $id_siswa;
        $principal = (int) $principal;
        $request_key = trim((string) $request_key);

        if ($id_siswa <= 0 || $principal <= 0) {
            return $this->error(
                'Siswa dan nominal isi saldo wajib diisi.',
                422
            );
        }

        if (!preg_match('/^[A-Za-z0-9-]{16,64}$/', $request_key)) {
            return $this->error(
                'Kunci permintaan isi saldo tidak valid.',
                422
            );
        }

        $this->db->trans_begin();
        $setting = $this->db->query('SELECT * FROM pengaturan_dompet ORDER BY id DESC LIMIT 1 FOR UPDATE')->row_array();
        if (!$setting || $setting['status_layanan'] !== 'AKTIF') {
            $this->db->trans_rollback();
            return $this->error('Layanan Dompet sedang tidak aktif.', 422);
        }
        if ($principal < (int) $setting['minimal_isi_saldo'] || $principal > (int) $setting['maksimal_isi_saldo']) {
            $this->db->trans_rollback();
            return $this->error('Nominal isi saldo berada di luar batas pengaturan.', 422);
        }

        $payment_result = $this->resolve_payment_destination($payment, true);
        if (empty($payment_result['success'])) {
            $this->db->trans_rollback();
            return $payment_result;
        }
        $destination = $payment_result['data'];

        $student = $this->db->query(
            "SELECT
                s.id,
                s.nis,
                s.nama_lengkap,
                sd.id id_wallet,
                sd.saldo
             FROM siswa s
             JOIN saldo_dompet_siswa sd
                ON sd.id_siswa = s.id
                AND sd.status_dompet = 'AKTIF'
             WHERE s.id = ?
                AND s.status_siswa = 'AKTIF'
                AND s.status_aktif = 'AKTIF'
             FOR UPDATE",
            [$id_siswa]
        )->row_array();
        if (!$student) {
            $this->db->trans_rollback();
            return $this->error('Siswa aktif dengan wallet aktif tidak ditemukan.', 422);
        }
        $fee = max(0, (int) $setting['biaya_layanan']);
        $request_no = 'ADM-' . strtoupper(substr(hash('sha256', $request_key), 0, 24));
        $now = $this->audit_fields($user);
        $this->db->query(
            'INSERT INTO pengajuan_isi_saldo (
                no_pengajuan,
                id_siswa,
                nis,
                nama_siswa,
                nominal_saldo,
                biaya_layanan,
                total_transfer,
                metode_pembayaran,
                id_rekening_dompet,
                id_kasbank,
                nama_kasbank,
                catatan_wali,
                status_pengajuan,
                tanggal_pengajuan,
                waktu_pengajuan
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
            [
                $request_no,
                $id_siswa,
                $student['nis'],
                $student['nama_lengkap'],
                $principal,
                $fee,
                $principal + $fee,
                $destination['metode_pembayaran'],
                $destination['id_rekening_dompet'],
                (int) $destination['id_kasbank'],
                $destination['nama_kasbank'],
                'Top-up langsung oleh Admin melalui '.$destination['metode_pembayaran'],
                'MENUNGGU_VERIFIKASI',
                $now['tanggal'],
                $now['waktu'],
            ]
        );
        $request_id = (int) $this->db->insert_id();

        $request = $this->db->query(
            'SELECT *
             FROM pengajuan_isi_saldo
             WHERE id = ?
             FOR UPDATE',
            [$request_id]
        )->row_array();
        if (!$request || (int) $request['id_siswa'] !== $id_siswa || (int) $request['nominal_saldo'] !== $principal) {
            $this->db->trans_rollback();
            return $this->error('Kunci permintaan sudah dipakai untuk data top-up yang berbeda.', 409);
        }
        $existing = $this->db->get_where('riwayat_isi_saldo', ['id_pengajuan' => $request_id])->row_array();
        if ($existing) {
            $this->db->trans_rollback();

            return [
                'success' => true,
                'data' => [
                    'id_pengajuan' => $request_id,
                    'id_riwayat' => (int) $existing['id'],
                    'no_transaksi' => $existing['no_transaksi'],
                    'saldo_sesudah' => (int) $existing['saldo_sesudah'],
                    'idempotent' => true,
                ],
            ];
        }
        if ($request['status_pengajuan'] !== 'MENUNGGU_VERIFIKASI') {
            $this->db->trans_rollback();
            return $this->error('Permintaan top-up langsung sudah diproses.', 409);
        }

        $kasbank_id = (int) $destination['id_kasbank'];

        $before = (int) $student['saldo'];
        $after = $before + $principal;
        $number = 'ISA-' . date('Ymd') . '-' . strtoupper(substr(hash('sha256', $request_no), 0, 12));
        $this->db->insert('riwayat_isi_saldo', [
            'no_transaksi' => $number,
            'id_pengajuan' => $request_id,
            'sumber_transaksi' => 'ADMIN_LANGSUNG',
            'id_siswa' => $id_siswa,
            'nis' => $student['nis'],
            'nama_siswa' => $student['nama_lengkap'],
            'nominal_saldo' => $principal,
            'biaya_layanan' => $fee,
            'total_pembayaran' => $principal + $fee,
            'metode_pembayaran' => $destination['metode_pembayaran'],
            'saldo_sebelum' => $before,
            'saldo_sesudah' => $after,
            'id_kasbank' => $kasbank_id,
            'nama_kasbank' => $destination['nama_kasbank'],
            'keterangan' => 'Top-up langsung Admin '.$request_no
                .' melalui '.$destination['metode_pembayaran'],
            'status_transaksi' => 'BERHASIL',
            'tanggal' => $now['tanggal'],
            'waktu' => $now['waktu'],
            'id_user' => $now['id_user'],
            'nama_user' => $now['nama_user'],
        ]);
        $history_id = (int) $this->db->insert_id();

        if ($this->inject_failure($failure_step, 'after_history')) {
            return $this->rollback_failure();
        }

        $this->db->where('id', $student['id_wallet'])->update('saldo_dompet_siswa', [
            'saldo' => $after,
            'tanggal_update' => $now['tanggal'],
            'waktu_update' => $now['waktu'],
            'id_user_update' => $now['id_user'],
            'nama_user_update' => $now['nama_user'],
        ]);

        $this->db
            ->where('id', $history_id)
            ->update('riwayat_isi_saldo', [
                'id_pemasukan_titipan' => 0,
                'id_pemasukan_biaya' => 0,
            ]);

        $this->db->where('id', $request_id)->update('pengajuan_isi_saldo', [
            'status_pengajuan' => 'DIVERIFIKASI',
            'tanggal_verifikasi' => $now['tanggal'],
            'waktu_verifikasi' => $now['waktu'],
            'id_user_verifikasi' => $now['id_user'],
            'nama_user_verifikasi' => $now['nama_user'],
        ]);

        $description = 'Pokok Rp '.$principal
            .', fee Rp '.$fee
            .', metode '.$destination['metode_pembayaran']
            .', kas/bank '.$destination['nama_kasbank']
            .', saldo '.$before
            .' menjadi '.$after.'.';

        $this->log(
            $user,
            'ISI_SALDO',
            'TOPUP_LANGSUNG_ADMIN',
            $history_id,
            $number,
            $description
        );

        if ($this->inject_failure($failure_step, 'before_commit')) {
            return $this->rollback_failure();
        }

        if ($this->db->trans_status() === false) {
            return $this->rollback_failure(
                'Top-up langsung gagal dan seluruh komponen telah di-rollback.'
            );
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'data' => [
                'id_pengajuan' => $request_id,
                'id_riwayat' => $history_id,
                'no_transaksi' => $number,
                'saldo_sebelum' => $before,
                'saldo_sesudah' => $after,
                'idempotent' => false,
            ],
        ];
    }

    public function reversal_topup($id_riwayat, $reason, $user, $failure_step = null)
    {
        $this->db->trans_begin();

        $history = $this->db->query(
            'SELECT *
             FROM riwayat_isi_saldo
             WHERE id = ?
             FOR UPDATE',
            [(int) $id_riwayat]
        )->row_array();

        if (!$history) {
            $this->db->trans_rollback();
            return $this->error('Riwayat isi saldo tidak ditemukan.', 404);
        }

        $existing = $this->db->get_where('pembatalan_dompet', [
            'jenis_transaksi' => 'ISI_SALDO',
            'id_transaksi' => (int) $id_riwayat,
        ])->row_array();

        if ($existing) {
            $this->db->trans_rollback();

            return [
                'success' => true,
                'data' => [
                    'id_pembatalan' => (int) $existing['id'],
                    'idempotent' => true,
                ],
            ];
        }
        if ($history['status_transaksi'] !== 'BERHASIL') {
            $this->db->trans_rollback();
            return $this->error('Transaksi isi saldo sudah dibatalkan atau tidak dapat direversal.', 409);
        }

        $wallet = $this->db->query(
            'SELECT *
             FROM saldo_dompet_siswa
             WHERE id_siswa = ?
             FOR UPDATE',
            [(int) $history['id_siswa']]
        )->row_array();
        $principal = (int) $history['nominal_saldo'];
        $fee = (int) $history['biaya_layanan'];
        if (!$wallet || (int) $wallet['saldo'] < $principal) {
            $this->db->trans_rollback();
            return $this->error('Saldo siswa tidak cukup untuk reversal otomatis.', 422);
        }

        $before = (int) $wallet['saldo'];
        $after = $before - $principal;
        $cancel_number = 'REV-'
            .date('Ymd').'-'
            .strtoupper(substr(hash('sha256', $history['no_transaksi']), 0, 12));
        $now = $this->audit_fields($user);
        $this->db->insert('pembatalan_dompet', [
            'no_pembatalan' => $cancel_number,
            'jenis_transaksi' => 'ISI_SALDO',
            'id_transaksi' => (int) $history['id'],
            'no_transaksi' => $history['no_transaksi'],
            'id_siswa' => (int) $history['id_siswa'],
            'nominal_koreksi' => $principal,
            'saldo_sebelum_koreksi' => $before,
            'saldo_sesudah_koreksi' => $after,
            'alasan' => $reason,
            'status_pembatalan' => 'DISETUJUI',
            'id_user_pengaju' => $now['id_user'],
            'nama_user_pengaju' => $now['nama_user'],
            'id_user_penyetuju' => $now['id_user'],
            'nama_user_penyetuju' => $now['nama_user'],
            'tanggal_pengajuan' => $now['tanggal'],
            'waktu_pengajuan' => $now['waktu'],
            'tanggal_proses' => $now['tanggal'],
            'waktu_proses' => $now['waktu'],
        ]);
        $cancel_id = (int) $this->db->insert_id();
        if ($this->inject_failure($failure_step, 'after_cancel')) {
            return $this->rollback_failure();
        }

        $this->db->where('id', $wallet['id'])->update('saldo_dompet_siswa', [
            'saldo' => $after,
            'tanggal_update' => $now['tanggal'],
            'waktu_update' => $now['waktu'],
            'id_user_update' => $now['id_user'],
            'nama_user_update' => $now['nama_user'],
        ]);
        $this->db
            ->where('id', $history['id'])
            ->update('riwayat_isi_saldo', [
                'status_transaksi' => 'DIBATALKAN',
            ]);

        if (!empty($history['id_pengajuan'])) {
            $this->db
                ->where('id', $history['id_pengajuan'])
                ->update('pengajuan_isi_saldo', [
                    'status_pengajuan' => 'DIBATALKAN_ADMIN',
                ]);
        }

        $description = 'Refund pokok Rp '.$principal
            .' dan fee Rp '.$fee
            .'; saldo '.$before
            .' menjadi '.$after.'.';

        $this->log(
            $user,
            'PEMBATALAN_DOMPET',
            'REVERSAL_TOPUP',
            $cancel_id,
            $cancel_number,
            $description
        );

        if ($this->db->trans_status() === false) {
            return $this->rollback_failure(
                'Reversal gagal dan seluruh komponen telah di-rollback.'
            );
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'data' => [
                'id_pembatalan' => $cancel_id,
                'no_pembatalan' => $cancel_number,
                'saldo_sebelum' => $before,
                'saldo_sesudah' => $after,
                'idempotent' => false,
            ],
        ];
    }

    private function resolve_payment_destination($payment, $lock = false)
    {
        if (!is_array($payment)) {
            return $this->error('Metode pembayaran wajib dipilih.', 422);
        }

        $method = strtoupper(trim((string) ($payment['metode_pembayaran'] ?? '')));
        if (!in_array($method, ['TRANSFER', 'CASH'], true)) {
            return $this->error('Metode pembayaran harus Transfer atau Cash.', 422);
        }

        $lock_sql = $lock ? ' FOR UPDATE' : '';

        if ($method === 'TRANSFER') {
            $account_id = (int) ($payment['id_rekening_dompet'] ?? 0);
            if ($account_id <= 0) {
                return $this->error('Bank penerima wajib dipilih untuk pembayaran Transfer.', 422);
            }

            $account = $this->db->query(
                "SELECT r.id, r.id_kasbank,
                        COALESCE(k.keterangan, r.nama_kasbank, r.nama_bank) AS nama_kasbank
                 FROM rekening_dompet r
                 LEFT JOIN kasbank k ON k.id = r.id_kasbank
                 WHERE r.id = ?
                    AND r.status_aktif = 'AKTIF'
                    AND r.id_kasbank IS NOT NULL
                 LIMIT 1".$lock_sql,
                [$account_id]
            )->row_array();

            if (!$account || (int) $account['id_kasbank'] <= 0) {
                return $this->error('Rekening bank aktif tidak ditemukan.', 422);
            }

            return [
                'success' => true,
                'data' => [
                    'metode_pembayaran' => 'TRANSFER',
                    'id_rekening_dompet' => (int) $account['id'],
                    'id_kasbank' => (int) $account['id_kasbank'],
                    'nama_kasbank' => (string) $account['nama_kasbank'],
                ],
            ];
        }

        $cash_id = (int) ($payment['id_kasbank'] ?? 0);
        if ($cash_id <= 0) {
            return $this->error('Kas penerima wajib dipilih untuk pembayaran Cash.', 422);
        }

        $cash = $this->db->query(
            "SELECT id, keterangan AS nama_kasbank
             FROM kasbank
             WHERE id = ?
                AND UPPER(TRIM(kategori)) = 'TUNAI'
             LIMIT 1".$lock_sql,
            [$cash_id]
        )->row_array();

        if (!$cash) {
            return $this->error('Kas penerima tidak ditemukan.', 422);
        }

        return [
            'success' => true,
            'data' => [
                'metode_pembayaran' => 'CASH',
                'id_rekening_dompet' => null,
                'id_kasbank' => (int) $cash['id'],
                'nama_kasbank' => (string) $cash['nama_kasbank'],
            ],
        ];
    }

    private function account($type, $description)
    {
        return $this->db
            ->get_where('kode_akun', [
                'jenis' => $type,
                'keterangan' => $description,
            ])
            ->row_array();
    }

    private function log($user, $module, $action, $reference_id, $reference_no, $description)
    {
        $this->db->insert('log_aktivitas_dompet', [
            'id_user' => (int) ($user['id'] ?? 0),
            'nama_user' => (string) ($user['nama_user'] ?? ''),
            'level' => (string) ($user['level'] ?? ''),
            'modul' => $module,
            'aksi' => $action,
            'id_referensi' => (int) $reference_id,
            'no_referensi' => $reference_no,
            'keterangan' => $description,
            'alamat_ip' => $this->input->ip_address(),
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ]);
    }

    private function inject_failure($requested, $step)
    {
        return is_string($requested)
            && $requested !== ''
            && hash_equals($step, $requested);
    }

    private function rollback_failure($message = 'Failure injection aktif; seluruh transaksi di-rollback.')
    {
        $this->db->trans_rollback();

        return $this->error($message, 500);
    }

    private function audit_fields($user)
    {
        return [
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
            'id_user' => (int) ($user['id'] ?? 0),
            'nama_user' => (string) ($user['nama_user'] ?? ''),
        ];
    }

    private function error($message, $status)
    {
        return [
            'success' => false,
            'message' => $message,
            'status' => $status,
        ];
    }
}
