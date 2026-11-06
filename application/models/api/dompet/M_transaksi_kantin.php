<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_transaksi_kantin extends CI_Model
{
    private $jumlah_data_per_halaman;
    private $jumlah_data_maksimal;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_petugas', 'petugas');
        $this->config->load('api');
        $this->jumlah_data_per_halaman = (int) $this->config->item('api_default_page_size');
        $this->jumlah_data_maksimal = (int) $this->config->item('api_max_page_size');
        if ($this->jumlah_data_per_halaman <= 0) {
            $this->jumlah_data_per_halaman = 20;
        }
        if ($this->jumlah_data_maksimal < $this->jumlah_data_per_halaman) {
            $this->jumlah_data_maksimal = max(100, $this->jumlah_data_per_halaman);
        }
    }

    public function proses()
    {
        $otorisasi = $this->petugas->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        $konteks = $otorisasi['data'];
        $input = $this->input_json();
        $id_permintaan = $this->id_permintaan();
        $kesalahan = $this->validasi_transaksi($input, $id_permintaan);
        if (!empty($kesalahan)) {
            return $this->gagal('Data transaksi belum valid.', 422, $kesalahan);
        }

        $id_siswa = (int) $input['id_siswa'];
        $nominal = (int) $input['nominal'];
        $keterangan = isset($input['keterangan']) && is_string($input['keterangan'])
            ? trim($input['keterangan'])
            : '';

        $this->db->trans_begin();

        $lama = $this->transaksi_permintaan_terkunci($id_permintaan);
        if ($lama) {
            $this->db->trans_rollback();
            return $this->respons_idempotent(
                $lama,
                (int) $konteks['id'],
                $id_siswa,
                $nominal,
                $keterangan
            );
        }

        $pengaturan = $this->db->query(
            'SELECT status_layanan FROM pengaturan_dompet ORDER BY id DESC LIMIT 1 FOR UPDATE'
        )->row_array();
        if (!$pengaturan || strtoupper(trim((string) $pengaturan['status_layanan'])) !== 'AKTIF') {
            $this->db->trans_rollback();
            return $this->gagal('Layanan Dompet Digital sedang tidak aktif.', 409);
        }

        $siswa = $this->petugas->ambil_siswa($id_siswa, true);
        if (!$siswa) {
            $this->db->trans_rollback();
            return $this->gagal('Siswa tidak ditemukan.', 404);
        }

        $validasi_siswa = $this->validasi_siswa($siswa, $nominal);
        if ($validasi_siswa !== null) {
            $this->db->trans_rollback();
            return $validasi_siswa;
        }

        $saldo_sebelum = (int) $siswa['saldo'];
        $saldo_sesudah = $saldo_sebelum - $nominal;
        $penggunaan_sebelum = (int) $siswa['penggunaan_hari_ini'];
        $penggunaan_sesudah = $penggunaan_sebelum + $nominal;
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $no_transaksi = $this->buat_nomor_transaksi();
        $nama_petugas = $this->nama_pengguna($konteks);

        $this->db->insert('transaksi_kantin', array(
            'no_transaksi' => $no_transaksi,
            'id_permintaan' => $id_permintaan,
            'id_siswa' => $id_siswa,
            'nis' => (string) $siswa['nis'],
            'nama_siswa' => (string) $siswa['nama_siswa'],
            'id_kelas_setting' => $siswa['id_kelas_setting'] !== null
                ? (int) $siswa['id_kelas_setting']
                : null,
            'nama_kelas' => $siswa['nama_kelas'],
            'nominal' => $nominal,
            'saldo_sebelum' => $saldo_sebelum,
            'saldo_sesudah' => $saldo_sesudah,
            'penggunaan_harian_sebelum' => $penggunaan_sebelum,
            'penggunaan_harian_sesudah' => $penggunaan_sesudah,
            'keterangan' => $keterangan === '' ? 'Transaksi Kantin' : $keterangan,
            'status_transaksi' => 'BERHASIL',
            'sumber_transaksi' => 'ANDROID',
            'id_perangkat' => null,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => (int) $konteks['id'],
            'nama_user' => $nama_petugas,
        ));
        $id_transaksi = (int) $this->db->insert_id();

        $this->db->where('id', (int) $siswa['id_saldo']);
        $this->db->update('saldo_dompet_siswa', array(
            'saldo' => $saldo_sesudah,
            'tanggal_update' => $tanggal,
            'waktu_update' => $waktu,
            'id_user_update' => (int) $konteks['id'],
            'nama_user_update' => $nama_petugas,
        ));

        $this->db->insert('log_aktivitas_dompet', array(
            'id_user' => (int) $konteks['id'],
            'nama_user' => $nama_petugas,
            'level' => (string) $konteks['level'],
            'modul' => 'TRANSAKSI_KANTIN',
            'aksi' => 'TRANSAKSI_KANTIN',
            'id_referensi' => $id_transaksi,
            'no_referensi' => $no_transaksi,
            'keterangan' => 'Transaksi kantin Android berhasil diproses.',
            'alamat_ip' => $this->input->ip_address(),
            'id_perangkat' => isset($konteks['id_perangkat'])
                ? (string) $konteks['id_perangkat']
                : null,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
        ));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $tersimpan = $this->transaksi_permintaan($id_permintaan);
            if ($tersimpan) {
                return $this->respons_idempotent(
                    $tersimpan,
                    (int) $konteks['id'],
                    $id_siswa,
                    $nominal,
                    $keterangan
                );
            }
            return $this->gagal('Transaksi belum dapat diproses. Coba periksa kembali status permintaan.', 500);
        }

        $this->db->trans_commit();
        $data_hasil = $this->format_detail(array(
                'id' => $id_transaksi,
                'no_transaksi' => $no_transaksi,
                'id_permintaan' => $id_permintaan,
                'id_siswa' => $id_siswa,
                'nis' => $siswa['nis'],
                'nama_siswa' => $siswa['nama_siswa'],
                'nama_kelas' => $siswa['nama_kelas'],
                'nominal' => $nominal,
                'saldo_sebelum' => $saldo_sebelum,
                'saldo_sesudah' => $saldo_sesudah,
                'penggunaan_harian_sebelum' => $penggunaan_sebelum,
                'penggunaan_harian_sesudah' => $penggunaan_sesudah,
                'keterangan' => $keterangan === '' ? 'Transaksi Kantin' : $keterangan,
                'status_transaksi' => 'BERHASIL',
                'sumber_transaksi' => 'ANDROID',
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'nama_user' => $nama_petugas,
            ), false);
        $data_hasil['sisa_limit_hari_ini'] = max(
            0,
            (int) $siswa['batas_harian'] - $penggunaan_sesudah
        );
        return $this->berhasil(
            'Transaksi kantin berhasil.',
            $data_hasil,
            201
        );
    }

    public function berdasarkan_permintaan($id_permintaan)
    {
        $otorisasi = $this->petugas->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        if (!$this->id_permintaan_valid($id_permintaan)) {
            return $this->gagal('Request ID belum valid.', 422);
        }
        $row = $this->transaksi_permintaan(strtolower($id_permintaan));
        if (!$row) {
            return $this->gagal('Hasil transaksi untuk request ID tersebut belum ditemukan.', 404);
        }
        $data = $this->format_detail($row, true);
        $data['sisa_limit_hari_ini'] = $this->sisa_limit_hari_ini((int) $row['id_siswa']);
        return $this->berhasil('Hasil transaksi berhasil ditemukan.', $data);
    }

    public function riwayat()
    {
        $otorisasi = $this->petugas->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        $pagination = $this->pagination();
        if (!$pagination['status']) {
            return $pagination['response'];
        }
        $filter = $this->filter_riwayat();
        if (!$filter['status']) {
            return $filter['response'];
        }

        $total = $this->db->query(
            'SELECT COUNT(*) AS jumlah FROM transaksi_kantin t WHERE '.$filter['sql'],
            $filter['params']
        )->row_array();
        $total_data = $total ? (int) $total['jumlah'] : 0;
        $offset = ($pagination['halaman'] - 1) * $pagination['per_halaman'];
        $params = $filter['params'];
        $params[] = $pagination['per_halaman'];
        $params[] = $offset;
        $rows = $this->db->query(
            "SELECT t.no_transaksi, t.id_permintaan, t.id_siswa, t.nis,
                    t.nama_siswa, t.nama_kelas, t.nominal, t.status_transaksi,
                    t.tanggal, t.waktu
             FROM transaksi_kantin t
             WHERE ".$filter['sql']."
             ORDER BY t.id DESC LIMIT ? OFFSET ?",
            $params
        )->result_array();
        $data = array();
        foreach ($rows as $row) {
            $data[] = $this->petugas->format_transaksi_ringkas($row);
        }

        return $this->berhasil_dengan_meta(
            empty($data) ? 'Belum ada transaksi kantin.' : 'Riwayat transaksi berhasil dimuat.',
            $data,
            array(
                'halaman' => $pagination['halaman'],
                'per_halaman' => $pagination['per_halaman'],
                'total_data' => $total_data,
                'total_halaman' => $total_data > 0
                    ? (int) ceil($total_data / $pagination['per_halaman'])
                    : 0,
            )
        );
    }

    public function detail($no_transaksi)
    {
        $otorisasi = $this->petugas->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        if (!$this->nomor_transaksi_valid($no_transaksi)) {
            return $this->gagal('Nomor transaksi belum valid.', 422);
        }
        $row = $this->db->query(
            'SELECT * FROM transaksi_kantin WHERE no_transaksi = ? LIMIT 1',
            array($no_transaksi)
        )->row_array();
        if (!$row) {
            return $this->gagal('Transaksi tidak ditemukan.', 404);
        }
        return $this->berhasil('Detail transaksi berhasil dimuat.', $this->format_detail($row, true));
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function validasi_siswa($siswa, $nominal)
    {
        if (strtoupper(trim((string) $siswa['status_siswa'])) !== 'AKTIF'
            || !in_array(strtoupper(trim((string) $siswa['status_aktif_siswa'])), array('AKTIF', '1', 'YA'), true)) {
            return $this->gagal('Siswa sedang tidak aktif.', 409);
        }
        if ($siswa['id_saldo'] === null) {
            return $this->gagal('Dompet siswa belum tersedia.', 409);
        }
        if (strtoupper(trim((string) $siswa['status_dompet'])) !== 'AKTIF') {
            return $this->gagal('Dompet siswa tidak aktif atau sedang diblokir.', 409);
        }
        if ($siswa['id_limit'] === null
            || strtoupper(trim((string) $siswa['status_limit'])) !== 'AKTIF'
            || (int) $siswa['batas_harian'] <= 0
            || (int) $siswa['batas_per_transaksi'] <= 0
            || (int) $siswa['batas_per_transaksi'] > (int) $siswa['batas_harian']) {
            return $this->gagal('Pengaturan limit siswa belum valid.', 409);
        }
        if ($nominal > (int) $siswa['batas_per_transaksi']) {
            return $this->gagal('Nominal melebihi batas per transaksi.', 422, array(), array(
                'batas_per_transaksi' => (int) $siswa['batas_per_transaksi'],
            ));
        }
        $sisa_limit = max(0, (int) $siswa['batas_harian'] - (int) $siswa['penggunaan_hari_ini']);
        if ($nominal > $sisa_limit) {
            return $this->gagal('Nominal melebihi sisa limit harian.', 422, array(), array(
                'sisa_limit_hari_ini' => $sisa_limit,
            ));
        }
        if ($nominal > (int) $siswa['saldo']) {
            return $this->gagal('Saldo siswa tidak mencukupi.', 422, array(), array(
                'saldo' => (int) $siswa['saldo'],
            ));
        }
        return null;
    }

    private function respons_idempotent($row, $id_user, $id_siswa, $nominal, $keterangan)
    {
        if ((int) $row['id_user'] !== $id_user) {
            return $this->gagal('Request ID sudah digunakan.', 409);
        }
        $keterangan_lama = trim((string) $row['keterangan']);
        $keterangan_baru = $keterangan === '' ? 'Transaksi Kantin' : $keterangan;
        if ((int) $row['id_siswa'] !== $id_siswa
            || (int) $row['nominal'] !== $nominal
            || $keterangan_lama !== $keterangan_baru) {
            return $this->gagal('Request ID sudah digunakan untuk transaksi dengan data berbeda.', 409);
        }
        $data = $this->format_detail($row, true);
        $data['sisa_limit_hari_ini'] = $this->sisa_limit_hari_ini((int) $row['id_siswa']);
        $data['diproses_ulang'] = true;
        return $this->berhasil('Transaksi dengan request ID ini sudah diproses.', $data);
    }

    private function transaksi_permintaan_terkunci($id_permintaan)
    {
        return $this->db->query(
            'SELECT * FROM transaksi_kantin WHERE id_permintaan = ? LIMIT 1 FOR UPDATE',
            array($id_permintaan)
        )->row_array();
    }

    private function transaksi_permintaan($id_permintaan)
    {
        return $this->db->query(
            'SELECT * FROM transaksi_kantin WHERE id_permintaan = ? LIMIT 1',
            array($id_permintaan)
        )->row_array();
    }

    private function sisa_limit_hari_ini($id_siswa)
    {
        $row = $this->db->query(
            "SELECT p.batas_harian,
                    COALESCE(SUM(CASE
                        WHEN t.tanggal = ?
                            AND t.status_transaksi IN ('BERHASIL', 'PEMBATALAN_MENUNGGU')
                        THEN t.nominal ELSE 0 END), 0) AS penggunaan
             FROM pengaturan_dompet_siswa p
             LEFT JOIN transaksi_kantin t ON t.id_siswa = p.id_siswa
             WHERE p.id_siswa = ? AND UPPER(TRIM(p.status_aktif)) = 'AKTIF'
             GROUP BY p.id, p.batas_harian
             LIMIT 1",
            array(date('Y-m-d'), $id_siswa)
        )->row_array();
        if (!$row) return null;
        return max(0, (int) $row['batas_harian'] - (int) $row['penggunaan']);
    }

    private function format_detail($row, $diproses_ulang)
    {
        return array(
            'no_transaksi' => (string) $row['no_transaksi'],
            'id_permintaan' => (string) $row['id_permintaan'],
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => $this->nilai_nullable($row['nis']),
            'nama_siswa' => $this->nilai_nullable($row['nama_siswa']),
            'kelas' => $this->nilai_nullable($row['nama_kelas']),
            'nominal' => (int) $row['nominal'],
            'saldo_sebelum' => $row['saldo_sebelum'] !== null ? (int) $row['saldo_sebelum'] : null,
            'saldo_sesudah' => $row['saldo_sesudah'] !== null ? (int) $row['saldo_sesudah'] : null,
            'penggunaan_harian_sebelum' => $row['penggunaan_harian_sebelum'] !== null
                ? (int) $row['penggunaan_harian_sebelum'] : null,
            'penggunaan_harian_sesudah' => $row['penggunaan_harian_sesudah'] !== null
                ? (int) $row['penggunaan_harian_sesudah'] : null,
            'keterangan' => $this->nilai_nullable($row['keterangan']),
            'status_transaksi' => (string) $row['status_transaksi'],
            'sumber_transaksi' => $this->nilai_nullable($row['sumber_transaksi']),
            'tanggal' => $this->nilai_nullable($row['tanggal']),
            'waktu' => $this->nilai_nullable($row['waktu']),
            'nama_petugas' => $this->nilai_nullable($row['nama_user']),
            'diproses_ulang' => (bool) $diproses_ulang,
        );
    }

    private function filter_riwayat()
    {
        $sql = '1 = 1';
        $params = array();
        $awal = $this->input->get('tanggal_awal', true);
        $akhir = $this->input->get('tanggal_akhir', true);
        $q = $this->input->get('q', true);
        $status = $this->input->get('status', true);

        if ($awal !== null && $awal !== '') {
            if (!$this->tanggal_valid($awal)) {
                return array('status' => false, 'response' => $this->gagal('Tanggal awal belum valid.', 422));
            }
            $sql .= ' AND t.tanggal >= ?';
            $params[] = $awal;
        }
        if ($akhir !== null && $akhir !== '') {
            if (!$this->tanggal_valid($akhir)) {
                return array('status' => false, 'response' => $this->gagal('Tanggal akhir belum valid.', 422));
            }
            $sql .= ' AND t.tanggal <= ?';
            $params[] = $akhir;
        }
        if ($awal && $akhir && $awal > $akhir) {
            return array('status' => false, 'response' => $this->gagal('Rentang tanggal belum valid.', 422));
        }
        if (is_string($q) && trim($q) !== '') {
            $q = trim($q);
            if (strlen($q) > 100) {
                return array('status' => false, 'response' => $this->gagal('Kata pencarian terlalu panjang.', 422));
            }
            $sql .= ' AND (t.no_transaksi LIKE ? OR t.nis LIKE ? OR t.nama_siswa LIKE ?)';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
        }
        if (is_string($status) && trim($status) !== '') {
            $status = strtoupper(trim($status));
            if (!in_array($status, array('BERHASIL', 'DIBATALKAN', 'PEMBATALAN_MENUNGGU'), true)) {
                return array('status' => false, 'response' => $this->gagal('Status transaksi belum valid.', 422));
            }
            $sql .= ' AND t.status_transaksi = ?';
            $params[] = $status;
        }
        return array('status' => true, 'sql' => $sql, 'params' => $params);
    }

    private function pagination()
    {
        $halaman = $this->input->get('halaman', true);
        $per_halaman = $this->input->get('per_halaman', true);
        $halaman = $halaman === null || $halaman === '' ? 1 : $halaman;
        $per_halaman = $per_halaman === null || $per_halaman === ''
            ? $this->jumlah_data_per_halaman : $per_halaman;
        if (filter_var($halaman, FILTER_VALIDATE_INT) === false || (int) $halaman <= 0) {
            return array('status' => false, 'response' => $this->gagal('Parameter halaman belum valid.', 422));
        }
        if (filter_var($per_halaman, FILTER_VALIDATE_INT) === false
            || (int) $per_halaman <= 0 || (int) $per_halaman > $this->jumlah_data_maksimal) {
            return array('status' => false, 'response' => $this->gagal(
                'Parameter per_halaman harus antara 1 dan '.$this->jumlah_data_maksimal.'.', 422
            ));
        }
        return array('status' => true, 'halaman' => (int) $halaman, 'per_halaman' => (int) $per_halaman);
    }

    private function validasi_transaksi($input, $id_permintaan)
    {
        $kesalahan = array();
        if (!$this->id_permintaan_valid($id_permintaan)) {
            $kesalahan['X-Request-ID'] = 'Header harus berisi UUID yang valid.';
        }
        if (!isset($input['id_siswa']) || !is_int($input['id_siswa']) || $input['id_siswa'] <= 0) {
            $kesalahan['id_siswa'] = 'ID siswa harus berupa bilangan bulat positif.';
        }
        if (!isset($input['nominal']) || !is_int($input['nominal']) || $input['nominal'] <= 0) {
            $kesalahan['nominal'] = 'Nominal harus berupa bilangan bulat lebih dari nol.';
        }
        if (isset($input['keterangan'])
            && (!is_string($input['keterangan']) || strlen(trim($input['keterangan'])) > 255)) {
            $kesalahan['keterangan'] = 'Keterangan maksimal 255 karakter.';
        }
        return $kesalahan;
    }

    private function id_permintaan()
    {
        $nilai = $this->input->get_request_header('X-Request-ID', true);
        return is_string($nilai) ? strtolower(trim($nilai)) : '';
    }

    private function id_permintaan_valid($nilai)
    {
        return is_string($nilai) && preg_match(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i',
            trim($nilai)
        ) === 1;
    }

    private function nomor_transaksi_valid($nilai)
    {
        return is_string($nilai)
            && preg_match('/^[A-Z0-9-]{5,50}$/', strtoupper(trim($nilai))) === 1;
    }

    private function tanggal_valid($nilai)
    {
        if (!is_string($nilai)) return false;
        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai);
        return $tanggal && $tanggal->format('Y-m-d') === $nilai;
    }

    private function buat_nomor_transaksi()
    {
        return 'KTN-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
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

    private function nilai_nullable($nilai)
    {
        if ($nilai === null || trim((string) $nilai) === '') return null;
        return (string) $nilai;
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

    private function gagal($pesan, $kode, $kesalahan = array(), $data = null)
    {
        return array(
            'status' => false, 'result' => false, 'kode' => $kode,
            'message' => $pesan, 'pesan' => $pesan,
            'kesalahan' => (object) $kesalahan, 'data' => $data,
        );
    }
}
