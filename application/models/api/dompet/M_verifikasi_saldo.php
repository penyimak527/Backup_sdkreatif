<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_verifikasi_saldo extends CI_Model
{
    private $jumlah_data_per_halaman;
    private $jumlah_data_maksimal;
    private $integrasi_keuangan;
    private $id_akun_titipan;
    private $id_akun_biaya;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_auth', 'auth');
        $this->config->load('api');
        $this->jumlah_data_per_halaman = max(1, (int) $this->config->item('api_default_page_size'));
        $this->jumlah_data_maksimal = max(
            $this->jumlah_data_per_halaman,
            (int) $this->config->item('api_max_page_size')
        );
        $this->integrasi_keuangan = (bool) $this->config->item('api_finance_integration_enabled');
        $this->id_akun_titipan = (int) $this->config->item('api_finance_principal_account_id');
        $this->id_akun_biaya = (int) $this->config->item('api_finance_fee_account_id');
    }

    public function daftar()
    {
        $otorisasi = $this->konteks_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        $pagination = $this->pagination();
        if (!$pagination['status']) return $pagination['response'];

        $sql = '1 = 1';
        $params = array();
        $status = $this->input->get('status', true);
        if (is_string($status) && trim($status) !== '') {
            $status = strtoupper(trim($status));
            if (!in_array($status, array(
                'MENUNGGU_VERIFIKASI', 'DISETUJUI', 'DITOLAK',
                'DIBATALKAN_WALI', 'KEDALUWARSA',
            ), true)) {
                return $this->gagal('Status pengajuan belum valid.', 422);
            }
            $sql .= ' AND p.status_pengajuan = ?';
            $params[] = $status;
        }
        $q = $this->input->get('q', true);
        if (is_string($q) && trim($q) !== '') {
            $q = trim($q);
            if (strlen($q) > 100) return $this->gagal('Kata pencarian terlalu panjang.', 422);
            $sql .= ' AND (p.no_pengajuan LIKE ? OR p.nis LIKE ? OR p.nama_siswa LIKE ?)';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
        }

        $total = $this->db->query(
            'SELECT COUNT(*) jumlah FROM pengajuan_isi_saldo p WHERE '.$sql,
            $params
        )->row_array();
        $total_data = $total ? (int) $total['jumlah'] : 0;
        $offset = ($pagination['halaman'] - 1) * $pagination['per_halaman'];
        $query_params = $params;
        $query_params[] = $pagination['per_halaman'];
        $query_params[] = $offset;
        $rows = $this->db->query(
            $this->select_pengajuan().' WHERE '.$sql.' ORDER BY p.id DESC LIMIT ? OFFSET ?',
            $query_params
        )->result_array();
        $data = array();
        foreach ($rows as $row) $data[] = $this->format_pengajuan($row);

        return $this->berhasil_dengan_meta(
            empty($data) ? 'Belum ada pengajuan.' : 'Daftar pengajuan berhasil dimuat.',
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

    public function detail($no_pengajuan)
    {
        $otorisasi = $this->konteks_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        if (!$this->nomor_valid($no_pengajuan)) return $this->gagal('Nomor pengajuan belum valid.', 422);
        $row = $this->ambil_pengajuan(strtoupper(trim($no_pengajuan)), false);
        if (!$row) return $this->gagal('Pengajuan isi saldo tidak ditemukan.', 404);
        return $this->berhasil('Detail pengajuan berhasil dimuat.', $this->format_pengajuan($row));
    }

    public function setujui($no_pengajuan)
    {
        $otorisasi = $this->konteks_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        if (!$this->nomor_valid($no_pengajuan)) return $this->gagal('Nomor pengajuan belum valid.', 422);
        $input = $this->input_json();
        $kesalahan = $this->validasi_persetujuan($input);
        if (!empty($kesalahan)) return $this->gagal('Data persetujuan belum valid.', 422, $kesalahan);

        $konteks = $otorisasi['data'];
        $nomor = strtoupper(trim($no_pengajuan));
        $this->db->trans_begin();
        $pengajuan = $this->ambil_pengajuan($nomor, true);
        if (!$pengajuan) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan isi saldo tidak ditemukan.', 404);
        }

        $status = strtoupper(trim((string) $pengajuan['status_pengajuan']));
        if ($status === 'DISETUJUI') {
            $transaksi = $this->ambil_transaksi_pengajuan((int) $pengajuan['id'], false);
            $this->db->trans_rollback();
            if (!$transaksi) return $this->gagal('Pengajuan disetujui tanpa transaksi saldo yang valid.', 409);
            $data = $this->format_transaksi($transaksi);
            $data['diproses_ulang'] = true;
            return $this->berhasil('Pengajuan ini sudah disetujui sebelumnya.', $data);
        }
        if ($status !== 'MENUNGGU_VERIFIKASI') {
            $this->db->trans_rollback();
            return $this->gagal('Status pengajuan tidak dapat disetujui.', 409);
        }
        if ($this->ambil_transaksi_pengajuan((int) $pengajuan['id'], true)) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan sudah mempunyai transaksi isi saldo.', 409);
        }

        $konfigurasi = $this->validasi_konfigurasi_keuangan();
        if ($konfigurasi !== null) {
            $this->db->trans_rollback();
            return $konfigurasi;
        }

        $saldo = $this->db->query(
            'SELECT * FROM saldo_dompet_siswa WHERE id_siswa = ? LIMIT 1 FOR UPDATE',
            array((int) $pengajuan['id_siswa'])
        )->row_array();
        if (!$saldo) {
            $this->db->trans_rollback();
            return $this->gagal('Dompet siswa belum tersedia.', 409);
        }
        $status_dompet = strtoupper(trim((string) $saldo['status_dompet']));
        $konfirmasi_blokir = isset($input['konfirmasi_dompet_diblokir'])
            && $input['konfirmasi_dompet_diblokir'] === true;
        if ($status_dompet !== 'AKTIF'
            && !($status_dompet === 'DIBLOKIR' && $konfirmasi_blokir)) {
            $this->db->trans_rollback();
            return $this->gagal(
                $status_dompet === 'DIBLOKIR'
                    ? 'Dompet diblokir memerlukan konfirmasi eksplisit.'
                    : 'Dompet siswa tidak aktif.',
                409
            );
        }

        $id_kasbank = (int) $pengajuan['id_kasbank'];
        if (isset($input['id_kasbank'])) {
            if ($id_kasbank > 0 && (int) $input['id_kasbank'] !== $id_kasbank) {
                $this->db->trans_rollback();
                return $this->gagal('Kas/bank harus sesuai rekening pengajuan.', 409);
            }
            $id_kasbank = (int) $input['id_kasbank'];
        }
        if ($id_kasbank <= 0) {
            $this->db->trans_rollback();
            return $this->gagal('Kas/bank pengajuan belum tersedia.', 409);
        }
        $kasbank = $this->db->query(
            'SELECT id, keterangan, kategori FROM kasbank WHERE id = ? LIMIT 1 FOR UPDATE',
            array($id_kasbank)
        )->row_array();
        if (!$kasbank) {
            $this->db->trans_rollback();
            return $this->gagal('Kas/bank penerima tidak ditemukan.', 404);
        }
        $metode_pembayaran = strtoupper(trim((string) $kasbank['kategori'])) === 'TUNAI'
            ? 'CASH'
            : 'TRANSFER';
        $akun = $this->ambil_akun_keuangan_terkunci((int) $pengajuan['biaya_layanan']);
        if (!$akun['status']) {
            $this->db->trans_rollback();
            return $akun['response'];
        }

        $nominal = (int) $pengajuan['nominal_saldo'];
        $saldo_sebelum = (int) $saldo['saldo'];
        if ($nominal <= 0 || $saldo_sebelum > 2147483647 - $nominal) {
            $this->db->trans_rollback();
            return $this->gagal('Nominal atau saldo akhir berada di luar batas penyimpanan.', 409);
        }
        $saldo_sesudah = $saldo_sebelum + $nominal;
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $nama = $this->nama_pengguna($konteks);
        $no_transaksi = $this->buat_nomor('ISD');
        if ($no_transaksi === null) {
            $this->db->trans_rollback();
            return $this->gagal('Nomor transaksi belum dapat dibuat.', 500);
        }
        $keterangan = isset($input['keterangan']) && trim((string) $input['keterangan']) !== ''
            ? trim((string) $input['keterangan'])
            : 'Verifikasi pengajuan '.$nomor;

        $this->db->insert('riwayat_isi_saldo', array(
            'no_transaksi' => $no_transaksi,
            'id_pengajuan' => (int) $pengajuan['id'],
            'sumber_transaksi' => 'PENGAJUAN_WALI',
            'id_siswa' => (int) $pengajuan['id_siswa'],
            'nis' => $pengajuan['nis'],
            'nama_siswa' => $pengajuan['nama_siswa'],
            'nominal_saldo' => $nominal,
            'biaya_layanan' => (int) $pengajuan['biaya_layanan'],
            'total_pembayaran' => (int) $pengajuan['total_transfer'],
            'metode_pembayaran' => $metode_pembayaran,
            'saldo_sebelum' => $saldo_sebelum,
            'saldo_sesudah' => $saldo_sesudah,
            'id_kasbank' => $id_kasbank,
            'nama_kasbank' => (string) $kasbank['keterangan'],
            'id_pemasukan_titipan' => 0,
            'id_pemasukan_biaya' => 0,
            'bukti' => $pengajuan['bukti_transfer'],
            'keterangan' => $keterangan,
            'status_transaksi' => 'BERHASIL',
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => (int) $konteks['id'],
            'nama_user' => $nama,
        ));
        $id_transaksi = (int) $this->db->insert_id();
        $this->db->where('id', (int) $saldo['id']);
        $this->db->update('saldo_dompet_siswa', array(
            'saldo' => $saldo_sesudah,
            'tanggal_update' => $tanggal,
            'waktu_update' => $waktu,
            'id_user_update' => (int) $konteks['id'],
            'nama_user_update' => $nama,
        ));
        $this->db->where('id', (int) $pengajuan['id']);
        $this->db->update('pengajuan_isi_saldo', array(
            'status_pengajuan' => 'DISETUJUI',
            'metode_pembayaran' => $metode_pembayaran,
            'alasan_penolakan' => null,
            'tanggal_verifikasi' => $tanggal,
            'waktu_verifikasi' => $waktu,
            'id_user_verifikasi' => (int) $konteks['id'],
            'nama_user_verifikasi' => $nama,
        ));
        $this->catat_log(
            $konteks,
            'SETUJUI_PENGAJUAN',
            $id_transaksi,
            $no_transaksi,
            'Pengajuan '.$nomor.' disetujui; saldo dan pemasukan diposting atomik.'
        );
        if ($this->db->trans_status() === false || ((int) $pengajuan['biaya_layanan'] > 0)) {
            $this->db->trans_rollback();
            return $this->gagal('Persetujuan belum dapat diposting secara atomik.', 500);
        }
        $this->db->trans_commit();

        return $this->berhasil('Pengajuan disetujui dan saldo berhasil ditambahkan.', array(
            'no_pengajuan' => $nomor,
            'no_transaksi' => $no_transaksi,
            'id_siswa' => (int) $pengajuan['id_siswa'],
            'nominal_saldo' => $nominal,
            'biaya_layanan' => (int) $pengajuan['biaya_layanan'],
            'total_pembayaran' => (int) $pengajuan['total_transfer'],
            'saldo_sebelum' => $saldo_sebelum,
            'saldo_sesudah' => $saldo_sesudah,
            'status_transaksi' => 'BERHASIL',
            'id_pemasukan_titipan' => 0,
            'id_pemasukan_biaya' => 0,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'diproses_ulang' => false,
        ), 201);
    }

    public function tolak($no_pengajuan)
    {
        $otorisasi = $this->konteks_internal();
        if (!$otorisasi['status']) return $otorisasi['response'];
        if (!$this->nomor_valid($no_pengajuan)) return $this->gagal('Nomor pengajuan belum valid.', 422);
        $input = $this->input_json();
        $alasan = isset($input['alasan']) && is_string($input['alasan'])
            ? trim($input['alasan']) : '';
        if ($alasan === '' || strlen($alasan) > 255) {
            return $this->gagal('Data penolakan belum valid.', 422, array(
                'alasan' => 'Alasan wajib diisi dan maksimal 255 karakter.',
            ));
        }

        $konteks = $otorisasi['data'];
        $nomor = strtoupper(trim($no_pengajuan));
        $this->db->trans_begin();
        $pengajuan = $this->ambil_pengajuan($nomor, true);
        if (!$pengajuan) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan isi saldo tidak ditemukan.', 404);
        }
        $status = strtoupper(trim((string) $pengajuan['status_pengajuan']));
        if ($status === 'DITOLAK') {
            $this->db->trans_rollback();
            return $this->berhasil('Pengajuan ini sudah ditolak sebelumnya.', array(
                'no_pengajuan' => $nomor,
                'status_pengajuan' => 'DITOLAK',
                'saldo_berubah' => false,
                'diproses_ulang' => true,
            ));
        }
        if ($status !== 'MENUNGGU_VERIFIKASI') {
            $this->db->trans_rollback();
            return $this->gagal('Status pengajuan tidak dapat ditolak.', 409);
        }
        if ($this->ambil_transaksi_pengajuan((int) $pengajuan['id'], true)) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan sudah mempunyai transaksi isi saldo.', 409);
        }
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $nama = $this->nama_pengguna($konteks);
        $this->db->where('id', (int) $pengajuan['id']);
        $this->db->update('pengajuan_isi_saldo', array(
            'status_pengajuan' => 'DITOLAK',
            'alasan_penolakan' => $alasan,
            'tanggal_verifikasi' => $tanggal,
            'waktu_verifikasi' => $waktu,
            'id_user_verifikasi' => (int) $konteks['id'],
            'nama_user_verifikasi' => $nama,
        ));
        $this->catat_log(
            $konteks,
            'TOLAK_PENGAJUAN',
            (int) $pengajuan['id'],
            $nomor,
            'Pengajuan ditolak. Saldo dan pemasukan tidak berubah.'
        );
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Penolakan belum dapat disimpan.', 500);
        }
        $this->db->trans_commit();
        return $this->berhasil('Pengajuan berhasil ditolak.', array(
            'no_pengajuan' => $nomor,
            'status_pengajuan' => 'DITOLAK',
            'alasan_penolakan' => $alasan,
            'saldo_berubah' => false,
            'diproses_ulang' => false,
        ));
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function konteks_internal()
    {
        $otorisasi = $this->auth->otorisasi_internal();
        if (!$otorisasi['status']) return $otorisasi;
        return $otorisasi;
    }

    private function validasi_konfigurasi_keuangan()
    {
        if (!$this->integrasi_keuangan) {
            return $this->gagal('Integrasi Keuangan belum diaktifkan.', 409);
        }
        if ($this->id_akun_titipan <= 0 || $this->id_akun_biaya <= 0
            || $this->id_akun_titipan === $this->id_akun_biaya) {
            return $this->gagal('Kode akun titipan dan biaya layanan belum dikonfigurasi dengan benar.', 409);
        }
        return null;
    }

    private function ambil_akun_keuangan_terkunci($biaya)
    {
        $ids = $biaya > 0
            ? array($this->id_akun_titipan, $this->id_akun_biaya)
            : array($this->id_akun_titipan);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->query(
            "SELECT id, jenis, keterangan FROM kode_akun
             WHERE id IN ($placeholders) FOR UPDATE",
            $ids
        )->result_array();
        $ditemukan = array();
        foreach ($rows as $row) {
            if (strcasecmp(trim((string) $row['jenis']), 'Pemasukan') !== 0) {
                return array(
                    'status' => false,
                    'response' => $this->gagal('Kode akun Keuangan bukan kategori Pemasukan.', 409),
                );
            }
            $ditemukan[(int) $row['id']] = true;
        }
        foreach ($ids as $id) {
            if (!isset($ditemukan[$id])) {
                return array(
                    'status' => false,
                    'response' => $this->gagal('Kode akun Keuangan tidak ditemukan.', 409),
                );
            }
        }
        return array('status' => true);
    }

    private function ambil_pengajuan($nomor, $terkunci)
    {
        $sql = $this->select_pengajuan().' WHERE p.no_pengajuan = ? LIMIT 1';
        if ($terkunci) $sql .= ' FOR UPDATE';
        return $this->db->query($sql, array($nomor))->row_array();
    }

    private function select_pengajuan()
    {
        return "SELECT p.*, r.nama_bank, r.nomor_rekening, r.nama_pemilik,
                    r.petunjuk, h.no_transaksi AS no_transaksi_saldo,
                    h.status_transaksi AS status_transaksi_saldo
                FROM pengajuan_isi_saldo p
                LEFT JOIN rekening_dompet r ON r.id = p.id_rekening_dompet
                LEFT JOIN riwayat_isi_saldo h ON h.id_pengajuan = p.id";
    }

    private function ambil_transaksi_pengajuan($id_pengajuan, $terkunci)
    {
        $sql = 'SELECT * FROM riwayat_isi_saldo WHERE id_pengajuan = ? ORDER BY id DESC LIMIT 1';
        if ($terkunci) $sql .= ' FOR UPDATE';
        return $this->db->query($sql, array($id_pengajuan))->row_array();
    }

    private function format_pengajuan($row)
    {
        return array(
            'no_pengajuan' => (string) $row['no_pengajuan'],
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => $this->nullable($row['nis']),
            'nama_siswa' => $this->nullable($row['nama_siswa']),
            'nama_wali' => $this->nullable($row['nama_wali']),
            'nominal_saldo' => (int) $row['nominal_saldo'],
            'biaya_layanan' => (int) $row['biaya_layanan'],
            'total_transfer' => (int) $row['total_transfer'],
            'id_kasbank' => $row['id_kasbank'] !== null ? (int) $row['id_kasbank'] : null,
            'nama_kasbank' => $this->nullable($row['nama_kasbank']),
            'nama_bank' => $this->nullable($row['nama_bank']),
            'nomor_rekening' => $this->nullable($row['nomor_rekening']),
            'nama_pemilik' => $this->nullable($row['nama_pemilik']),
            'nama_bukti' => $row['bukti_transfer'] ? basename((string) $row['bukti_transfer']) : null,
            'catatan_wali' => $this->nullable($row['catatan_wali']),
            'status_pengajuan' => (string) $row['status_pengajuan'],
            'alasan_penolakan' => $this->nullable($row['alasan_penolakan']),
            'tanggal' => $this->nullable($row['tanggal_pengajuan']),
            'waktu' => $this->nullable($row['waktu_pengajuan']),
            'tanggal_verifikasi' => $this->nullable($row['tanggal_verifikasi']),
            'waktu_verifikasi' => $this->nullable($row['waktu_verifikasi']),
            'nama_verifikator' => $this->nullable($row['nama_user_verifikasi']),
            'no_transaksi_saldo' => $this->nullable($row['no_transaksi_saldo']),
            'status_transaksi_saldo' => $this->nullable($row['status_transaksi_saldo']),
        );
    }

    private function format_transaksi($row)
    {
        return array(
            'no_transaksi' => (string) $row['no_transaksi'],
            'id_siswa' => (int) $row['id_siswa'],
            'nominal_saldo' => (int) $row['nominal_saldo'],
            'biaya_layanan' => (int) $row['biaya_layanan'],
            'total_pembayaran' => (int) $row['total_pembayaran'],
            'saldo_sebelum' => $row['saldo_sebelum'] !== null ? (int) $row['saldo_sebelum'] : null,
            'saldo_sesudah' => $row['saldo_sesudah'] !== null ? (int) $row['saldo_sesudah'] : null,
            'status_transaksi' => (string) $row['status_transaksi'],
            'id_pemasukan_titipan' => $row['id_pemasukan_titipan'] !== null
                ? (int) $row['id_pemasukan_titipan'] : null,
            'id_pemasukan_biaya' => $row['id_pemasukan_biaya'] !== null
                ? (int) $row['id_pemasukan_biaya'] : null,
            'tanggal' => $this->nullable($row['tanggal']),
            'waktu' => $this->nullable($row['waktu']),
            'diproses_ulang' => false,
        );
    }

    private function validasi_persetujuan($input)
    {
        $kesalahan = array();
        if (isset($input['id_kasbank'])
            && (!is_int($input['id_kasbank']) || $input['id_kasbank'] <= 0)) {
            $kesalahan['id_kasbank'] = 'ID kas/bank harus berupa bilangan bulat positif.';
        }
        if (isset($input['keterangan'])
            && (!is_string($input['keterangan']) || strlen(trim($input['keterangan'])) > 255)) {
            $kesalahan['keterangan'] = 'Keterangan maksimal 255 karakter.';
        }
        if (isset($input['konfirmasi_dompet_diblokir'])
            && !is_bool($input['konfirmasi_dompet_diblokir'])) {
            $kesalahan['konfirmasi_dompet_diblokir'] = 'Konfirmasi harus berupa boolean.';
        }
        return $kesalahan;
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
            'modul' => 'VERIFIKASI_ISI_SALDO',
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
            log_message('error', 'Gagal membuat nomor transaksi: '.$exception->getMessage());
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
