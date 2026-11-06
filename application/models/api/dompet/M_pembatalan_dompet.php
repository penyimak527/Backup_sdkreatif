<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_pembatalan_dompet extends CI_Model
{
    private $jumlah_data_per_halaman;
    private $jumlah_data_maksimal;
    private $integrasi_keuangan;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_auth', 'auth');
        $this->load->model('api/dompet/M_petugas', 'petugas');
        $this->config->load('api');
        $this->jumlah_data_per_halaman = max(1, (int) $this->config->item('api_default_page_size'));
        $this->jumlah_data_maksimal = max(
            $this->jumlah_data_per_halaman,
            (int) $this->config->item('api_max_page_size')
        );
        $this->integrasi_keuangan = (bool) $this->config->item('api_finance_integration_enabled');
    }

    public function ajukan_kantin($no_transaksi)
    {
        $otorisasi = $this->petugas->konteks_petugas();
        if (!$otorisasi['status']) return $otorisasi['response'];
        return $this->buat_pengajuan(
            'KANTIN',
            $no_transaksi,
            $otorisasi['data'],
            true
        );
    }

    public function ajukan_isi_saldo($no_transaksi)
    {
        $otorisasi = $this->auth->otorisasi_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        return $this->buat_pengajuan(
            'ISI_SALDO',
            $no_transaksi,
            $otorisasi['data'],
            false
        );
    }

    public function daftar()
    {
        $otorisasi = $this->auth->otorisasi_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        $pagination = $this->pagination();
        if (!$pagination['status']) return $pagination['response'];
        $sql = '1 = 1';
        $params = array();
        $jenis = $this->input->get('jenis', true);
        if (is_string($jenis) && trim($jenis) !== '') {
            $jenis = strtoupper(trim($jenis));
            if (!in_array($jenis, array('ISI_SALDO', 'KANTIN'), true)) {
                return $this->gagal('Jenis transaksi belum valid.', 422);
            }
            $sql .= ' AND jenis_transaksi = ?';
            $params[] = $jenis;
        }
        $status = $this->input->get('status', true);
        if (is_string($status) && trim($status) !== '') {
            $status = strtoupper(trim($status));
            if (!in_array($status, array(
                'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DITOLAK', 'DIBATALKAN_PENGAJU',
            ), true)) {
                return $this->gagal('Status pembatalan belum valid.', 422);
            }
            $sql .= ' AND status_pembatalan = ?';
            $params[] = $status;
        }
        $total = $this->db->query(
            'SELECT COUNT(*) jumlah FROM pembatalan_dompet WHERE '.$sql,
            $params
        )->row_array();
        $total_data = $total ? (int) $total['jumlah'] : 0;
        $offset = ($pagination['halaman'] - 1) * $pagination['per_halaman'];
        $query_params = $params;
        $query_params[] = $pagination['per_halaman'];
        $query_params[] = $offset;
        $rows = $this->db->query(
            'SELECT * FROM pembatalan_dompet WHERE '.$sql.' ORDER BY id DESC LIMIT ? OFFSET ?',
            $query_params
        )->result_array();
        $data = array();
        foreach ($rows as $row) $data[] = $this->format_pembatalan($row);
        return $this->berhasil_dengan_meta(
            empty($data) ? 'Belum ada pembatalan.' : 'Daftar pembatalan berhasil dimuat.',
            $data,
            array(
                'halaman' => $pagination['halaman'],
                'per_halaman' => $pagination['per_halaman'],
                'total_data' => $total_data,
                'total_halaman' => $total_data > 0
                    ? (int) ceil($total_data / $pagination['per_halaman']) : 0,
            )
        );
    }

    public function detail($no_pembatalan)
    {
        $otorisasi = $this->auth->otorisasi_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        if (!$this->nomor_valid($no_pembatalan)) return $this->gagal('Nomor pembatalan belum valid.', 422);
        $row = $this->ambil_pembatalan(strtoupper(trim($no_pembatalan)), false);
        if (!$row) return $this->gagal('Pembatalan tidak ditemukan.', 404);
        return $this->berhasil('Detail pembatalan berhasil dimuat.', $this->format_pembatalan($row));
    }

    public function setujui($no_pembatalan)
    {
        $otorisasi = $this->auth->otorisasi_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        if (!$this->nomor_valid($no_pembatalan)) return $this->gagal('Nomor pembatalan belum valid.', 422);
        $konteks = $otorisasi['data'];
        $nomor = strtoupper(trim($no_pembatalan));
        $this->db->trans_begin();
        $pembatalan = $this->ambil_pembatalan($nomor, true);
        if (!$pembatalan) {
            $this->db->trans_rollback();
            return $this->gagal('Pembatalan tidak ditemukan.', 404);
        }
        $status = strtoupper(trim((string) $pembatalan['status_pembatalan']));
        if ($status === 'DISETUJUI') {
            $this->db->trans_rollback();
            $data = $this->format_pembatalan($pembatalan);
            $data['diproses_ulang'] = true;
            return $this->berhasil('Pembatalan ini sudah disetujui sebelumnya.', $data);
        }
        if ($status !== 'MENUNGGU_PERSETUJUAN') {
            $this->db->trans_rollback();
            return $this->gagal('Status pembatalan tidak dapat disetujui.', 409);
        }

        $saldo = $this->db->query(
            'SELECT * FROM saldo_dompet_siswa WHERE id_siswa = ? LIMIT 1 FOR UPDATE',
            array((int) $pembatalan['id_siswa'])
        )->row_array();
        if (!$saldo) {
            $this->db->trans_rollback();
            return $this->gagal('Dompet siswa belum tersedia.', 409);
        }
        $saldo_sebelum = (int) $saldo['saldo'];
        $jenis = strtoupper(trim((string) $pembatalan['jenis_transaksi']));
        $id_koreksi = array();
        if ($jenis === 'ISI_SALDO') {
            $transaksi = $this->db->query(
                'SELECT * FROM riwayat_isi_saldo WHERE id = ? LIMIT 1 FOR UPDATE',
                array((int) $pembatalan['id_transaksi'])
            )->row_array();
            if (!$transaksi
                || strtoupper(trim((string) $transaksi['status_transaksi'])) !== 'PEMBATALAN_MENUNGGU') {
                $this->db->trans_rollback();
                return $this->gagal('Transaksi isi saldo tidak berada pada status pembatalan menunggu.', 409);
            }
            $nominal = (int) $transaksi['nominal_saldo'];
            if ($nominal <= 0 || $saldo_sebelum < $nominal) {
                $this->db->trans_rollback();
                return $this->gagal('Saldo saat ini tidak cukup untuk pembatalan otomatis.', 409);
            }
            $saldo_sesudah = $saldo_sebelum - $nominal;
            $this->db->where('id', (int) $transaksi['id']);
            $this->db->update('riwayat_isi_saldo', array('status_transaksi' => 'DIBATALKAN'));
        } elseif ($jenis === 'KANTIN') {
            $transaksi = $this->db->query(
                'SELECT * FROM transaksi_kantin WHERE id = ? LIMIT 1 FOR UPDATE',
                array((int) $pembatalan['id_transaksi'])
            )->row_array();
            if (!$transaksi
                || strtoupper(trim((string) $transaksi['status_transaksi'])) !== 'PEMBATALAN_MENUNGGU') {
                $this->db->trans_rollback();
                return $this->gagal('Transaksi kantin tidak berada pada status pembatalan menunggu.', 409);
            }
            $nominal = (int) $transaksi['nominal'];
            if ($nominal <= 0 || $saldo_sebelum > 2147483647 - $nominal) {
                $this->db->trans_rollback();
                return $this->gagal('Saldo hasil koreksi berada di luar batas penyimpanan.', 409);
            }
            $saldo_sesudah = $saldo_sebelum + $nominal;
            $this->db->where('id', (int) $transaksi['id']);
            $this->db->update('transaksi_kantin', array('status_transaksi' => 'DIBATALKAN'));
        } else {
            $this->db->trans_rollback();
            return $this->gagal('Jenis transaksi pembatalan tidak dikenali.', 409);
        }

        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $nama = $this->nama_pengguna($konteks);
        $this->db->where('id', (int) $saldo['id']);
        $this->db->update('saldo_dompet_siswa', array(
            'saldo' => $saldo_sesudah,
            'tanggal_update' => $tanggal,
            'waktu_update' => $waktu,
            'id_user_update' => (int) $konteks['id'],
            'nama_user_update' => $nama,
        ));
        $this->db->where('id', (int) $pembatalan['id']);
        $this->db->update('pembatalan_dompet', array(
            'saldo_sebelum_koreksi' => $saldo_sebelum,
            'saldo_sesudah_koreksi' => $saldo_sesudah,
            'status_pembatalan' => 'DISETUJUI',
            'id_user_penyetuju' => (int) $konteks['id'],
            'nama_user_penyetuju' => $nama,
            'tanggal_proses' => $tanggal,
            'waktu_proses' => $waktu,
        ));
        $this->catat_log(
            $konteks,
            'SETUJUI_PEMBATALAN_'.$jenis,
            (int) $pembatalan['id'],
            $nomor,
            'Pembatalan '.$jenis.' diproses dengan koreksi saldo atomik.'
        );
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Pembatalan belum dapat diproses secara atomik.', 500);
        }
        $this->db->trans_commit();
        return $this->berhasil('Pembatalan berhasil disetujui.', array(
            'no_pembatalan' => $nomor,
            'jenis_transaksi' => $jenis,
            'no_transaksi' => (string) $pembatalan['no_transaksi'],
            'id_siswa' => (int) $pembatalan['id_siswa'],
            'nominal_koreksi' => (int) $pembatalan['nominal_koreksi'],
            'saldo_sebelum_koreksi' => $saldo_sebelum,
            'saldo_sesudah_koreksi' => $saldo_sesudah,
            'status_pembatalan' => 'DISETUJUI',
            'id_koreksi_pemasukan' => 0,
            'diproses_ulang' => false,
        ));
    }

    public function tolak($no_pembatalan)
    {
        $otorisasi = $this->auth->otorisasi_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        if (!$this->nomor_valid($no_pembatalan)) return $this->gagal('Nomor pembatalan belum valid.', 422);
        $input = $this->input_json();
        $alasan = isset($input['alasan']) && is_string($input['alasan'])
            ? trim($input['alasan']) : '';
        if ($alasan === '' || strlen($alasan) > 255) {
            return $this->gagal('Data penolakan belum valid.', 422, array(
                'alasan' => 'Alasan wajib diisi dan maksimal 255 karakter.',
            ));
        }
        $konteks = $otorisasi['data'];
        $nomor = strtoupper(trim($no_pembatalan));
        $this->db->trans_begin();
        $pembatalan = $this->ambil_pembatalan($nomor, true);
        if (!$pembatalan) {
            $this->db->trans_rollback();
            return $this->gagal('Pembatalan tidak ditemukan.', 404);
        }
        $status = strtoupper(trim((string) $pembatalan['status_pembatalan']));
        if ($status === 'DITOLAK') {
            $this->db->trans_rollback();
            $data = $this->format_pembatalan($pembatalan);
            $data['diproses_ulang'] = true;
            return $this->berhasil('Pembatalan ini sudah ditolak sebelumnya.', $data);
        }
        if ($status !== 'MENUNGGU_PERSETUJUAN') {
            $this->db->trans_rollback();
            return $this->gagal('Status pembatalan tidak dapat ditolak.', 409);
        }
        $jenis = strtoupper(trim((string) $pembatalan['jenis_transaksi']));
        $table = $jenis === 'ISI_SALDO' ? 'riwayat_isi_saldo' : 'transaksi_kantin';
        if (!in_array($jenis, array('ISI_SALDO', 'KANTIN'), true)) {
            $this->db->trans_rollback();
            return $this->gagal('Jenis transaksi pembatalan tidak dikenali.', 409);
        }
        $transaksi = $this->db->query(
            'SELECT id, status_transaksi FROM '.$table.' WHERE id = ? LIMIT 1 FOR UPDATE',
            array((int) $pembatalan['id_transaksi'])
        )->row_array();
        if (!$transaksi
            || strtoupper(trim((string) $transaksi['status_transaksi'])) !== 'PEMBATALAN_MENUNGGU') {
            $this->db->trans_rollback();
            return $this->gagal('Status transaksi asli tidak sesuai.', 409);
        }
        $this->db->where('id', (int) $transaksi['id']);
        $this->db->update($table, array('status_transaksi' => 'BERHASIL'));
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $nama = $this->nama_pengguna($konteks);
        $this->db->where('id', (int) $pembatalan['id']);
        $this->db->update('pembatalan_dompet', array(
            'status_pembatalan' => 'DITOLAK',
            'id_user_penyetuju' => (int) $konteks['id'],
            'nama_user_penyetuju' => $nama,
            'tanggal_proses' => $tanggal,
            'waktu_proses' => $waktu,
        ));
        $this->catat_log(
            $konteks,
            'TOLAK_PEMBATALAN_'.$jenis,
            (int) $pembatalan['id'],
            $nomor,
            'Pembatalan ditolak: '.$alasan
        );
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Penolakan pembatalan belum dapat disimpan.', 500);
        }
        $this->db->trans_commit();
        return $this->berhasil('Pembatalan berhasil ditolak.', array(
            'no_pembatalan' => $nomor,
            'status_pembatalan' => 'DITOLAK',
            'saldo_berubah' => false,
            'diproses_ulang' => false,
        ));
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function buat_pengajuan($jenis, $no_transaksi, $konteks, $batasi_user)
    {
        if (!$this->nomor_valid($no_transaksi)) return $this->gagal('Nomor transaksi belum valid.', 422);
        $input = $this->input_json();
        $alasan = isset($input['alasan']) && is_string($input['alasan'])
            ? trim($input['alasan']) : '';
        if ($alasan === '' || strlen($alasan) > 255) {
            return $this->gagal('Data pembatalan belum valid.', 422, array(
                'alasan' => 'Alasan wajib diisi dan maksimal 255 karakter.',
            ));
        }
        $nomor_transaksi = strtoupper(trim($no_transaksi));
        $table = $jenis === 'ISI_SALDO' ? 'riwayat_isi_saldo' : 'transaksi_kantin';
        $field_nominal = $jenis === 'ISI_SALDO' ? 'nominal_saldo' : 'nominal';
        $sql = 'SELECT * FROM '.$table.' WHERE no_transaksi = ?';
        $params = array($nomor_transaksi);
        if ($batasi_user) {
            $sql .= ' AND id_user = ?';
            $params[] = (int) $konteks['id'];
        }
        $sql .= ' LIMIT 1 FOR UPDATE';

        $this->db->trans_begin();
        $transaksi = $this->db->query($sql, $params)->row_array();
        if (!$transaksi) {
            $this->db->trans_rollback();
            return $this->gagal('Transaksi tidak ditemukan.', 404);
        }
        $lama = $this->db->query(
            "SELECT * FROM pembatalan_dompet
             WHERE jenis_transaksi = ? AND id_transaksi = ?
                AND status_pembatalan IN ('MENUNGGU_PERSETUJUAN', 'DISETUJUI')
             ORDER BY id DESC LIMIT 1 FOR UPDATE",
            array($jenis, (int) $transaksi['id'])
        )->row_array();
        if ($lama) {
            $this->db->trans_rollback();
            $data = $this->format_pembatalan($lama);
            $data['diproses_ulang'] = true;
            return $this->berhasil('Transaksi ini sudah mempunyai pembatalan aktif.', $data);
        }
        if (strtoupper(trim((string) $transaksi['status_transaksi'])) !== 'BERHASIL') {
            $this->db->trans_rollback();
            return $this->gagal('Hanya transaksi berhasil yang dapat diajukan untuk pembatalan.', 409);
        }
        $nomor_batal = $this->buat_nomor($jenis === 'ISI_SALDO' ? 'BTL-ISD' : 'BTL-KTN');
        if ($nomor_batal === null) {
            $this->db->trans_rollback();
            return $this->gagal('Nomor pembatalan belum dapat dibuat.', 500);
        }
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $nama = $this->nama_pengguna($konteks);
        $this->db->insert('pembatalan_dompet', array(
            'no_pembatalan' => $nomor_batal,
            'jenis_transaksi' => $jenis,
            'id_transaksi' => (int) $transaksi['id'],
            'no_transaksi' => $nomor_transaksi,
            'id_siswa' => (int) $transaksi['id_siswa'],
            'nominal_koreksi' => (int) $transaksi[$field_nominal],
            'alasan' => $alasan,
            'status_pembatalan' => 'MENUNGGU_PERSETUJUAN',
            'id_user_pengaju' => (int) $konteks['id'],
            'nama_user_pengaju' => $nama,
            'tanggal_pengajuan' => $tanggal,
            'waktu_pengajuan' => $waktu,
        ));
        $id_pembatalan = (int) $this->db->insert_id();
        $this->db->where('id', (int) $transaksi['id']);
        $this->db->update($table, array('status_transaksi' => 'PEMBATALAN_MENUNGGU'));
        $this->catat_log(
            $konteks,
            'AJUKAN_PEMBATALAN_'.$jenis,
            $id_pembatalan,
            $nomor_batal,
            'Pembatalan diajukan. Saldo belum berubah.'
        );
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan pembatalan belum dapat disimpan.', 500);
        }
        $row = $this->ambil_pembatalan_id($id_pembatalan);
        $this->db->trans_commit();
        return $this->berhasil('Pembatalan berhasil diajukan dan menunggu persetujuan.', $this->format_pembatalan($row), 201);
    }

    private function ambil_pembatalan($nomor, $terkunci)
    {
        $sql = 'SELECT * FROM pembatalan_dompet WHERE no_pembatalan = ? LIMIT 1';
        if ($terkunci) $sql .= ' FOR UPDATE';
        return $this->db->query($sql, array($nomor))->row_array();
    }

    private function ambil_pembatalan_id($id)
    {
        return $this->db->query(
            'SELECT * FROM pembatalan_dompet WHERE id = ? LIMIT 1',
            array($id)
        )->row_array();
    }

    private function format_pembatalan($row)
    {
        return array(
            'no_pembatalan' => (string) $row['no_pembatalan'],
            'jenis_transaksi' => (string) $row['jenis_transaksi'],
            'no_transaksi' => $this->nullable($row['no_transaksi']),
            'id_siswa' => (int) $row['id_siswa'],
            'nominal_koreksi' => (int) $row['nominal_koreksi'],
            'saldo_sebelum_koreksi' => $row['saldo_sebelum_koreksi'] !== null
                ? (int) $row['saldo_sebelum_koreksi'] : null,
            'saldo_sesudah_koreksi' => $row['saldo_sesudah_koreksi'] !== null
                ? (int) $row['saldo_sesudah_koreksi'] : null,
            'alasan' => (string) $row['alasan'],
            'status_pembatalan' => (string) $row['status_pembatalan'],
            'nama_pengaju' => $this->nullable($row['nama_user_pengaju']),
            'nama_penyetuju' => $this->nullable($row['nama_user_penyetuju']),
            'tanggal_pengajuan' => $this->nullable($row['tanggal_pengajuan']),
            'waktu_pengajuan' => $this->nullable($row['waktu_pengajuan']),
            'tanggal_proses' => $this->nullable($row['tanggal_proses']),
            'waktu_proses' => $this->nullable($row['waktu_proses']),
            'saldo_berubah' => strtoupper(trim((string) $row['status_pembatalan'])) === 'DISETUJUI',
            'diproses_ulang' => false,
        );
    }

    private function pagination()
    {
        $halaman = $this->input->get('halaman', true);
        $per = $this->input->get('per_halaman', true);
        $halaman = $halaman === null || $halaman === '' ? 1 : $halaman;
        $per = $per === null || $per === '' ? $this->jumlah_data_per_halaman : $per;
        if (filter_var($halaman, FILTER_VALIDATE_INT) === false || (int) $halaman <= 0) {
            return array('status' => false, 'response' => $this->gagal('Parameter halaman belum valid.', 422));
        }
        if (filter_var($per, FILTER_VALIDATE_INT) === false
            || (int) $per <= 0 || (int) $per > $this->jumlah_data_maksimal) {
            return array('status' => false, 'response' => $this->gagal('Parameter per_halaman belum valid.', 422));
        }
        return array('status' => true, 'halaman' => (int) $halaman, 'per_halaman' => (int) $per);
    }

    private function catat_log($konteks, $aksi, $id, $nomor, $keterangan)
    {
        $this->db->insert('log_aktivitas_dompet', array(
            'id_user' => (int) $konteks['id'],
            'nama_user' => $this->nama_pengguna($konteks),
            'level' => (string) $konteks['level'],
            'modul' => 'PEMBATALAN_DOMPET',
            'aksi' => $aksi,
            'id_referensi' => $id,
            'no_referensi' => $nomor,
            'keterangan' => substr($keterangan, 0, 255),
            'alamat_ip' => $this->input->ip_address(),
            'id_perangkat' => isset($konteks['id_perangkat']) ? (string) $konteks['id_perangkat'] : null,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ));
    }

    private function buat_nomor($prefix)
    {
        try {
            return $prefix.'-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
        } catch (Exception $exception) {
            log_message('error', 'Gagal membuat nomor pembatalan: '.$exception->getMessage());
            return null;
        }
    }

    private function nomor_valid($nilai)
    {
        return is_string($nilai)
            && preg_match('/^[A-Z0-9-]{5,50}$/', strtoupper(trim($nilai))) === 1;
    }

    private function nama_pengguna($konteks)
    {
        $nama = trim((string) $konteks['nama_user']);
        return $nama !== '' ? $nama : trim((string) $konteks['username']);
    }

    private function input_json()
    {
        $raw = $this->input->raw_input_stream;
        if (!is_string($raw) || trim($raw) === '') return array();
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    private function nullable($nilai)
    {
        return $nilai === null || trim((string) $nilai) === '' ? null : (string) $nilai;
    }

    private function berhasil($pesan, $data, $kode = 200)
    {
        return array(
            'status' => true, 'result' => true, 'kode' => $kode,
            'message' => $pesan, 'pesan' => $pesan, 'data' => $data,
        );
    }

    private function berhasil_dengan_meta($pesan, $data, $meta)
    {
        $response = $this->berhasil($pesan, $data);
        $response['meta'] = $meta;
        return $response;
    }

    private function gagal($pesan, $kode, $kesalahan = array())
    {
        return array(
            'status' => false, 'result' => false, 'kode' => $kode,
            'message' => $pesan, 'pesan' => $pesan,
            'kesalahan' => (object) $kesalahan, 'data' => null,
        );
    }
}
