<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_petugas extends CI_Model
{
    private $batas_pencarian;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_auth', 'auth');
        $this->config->load('api');
        $this->batas_pencarian = (int) $this->config->item('api_default_page_size');
        if ($this->batas_pencarian <= 0) {
            $this->batas_pencarian = 20;
        }
    }

    public function dashboard()
    {
        $otorisasi = $this->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        $tanggal = date('Y-m-d');

        $ringkasan = $this->db->query(
            "SELECT COUNT(*) AS jumlah, COALESCE(SUM(nominal), 0) AS total
             FROM transaksi_kantin
             WHERE tanggal = ?
                AND status_transaksi IN ('BERHASIL', 'PEMBATALAN_MENUNGGU')",
            array($tanggal)
        )->row_array();

        $rows = $this->db->query(
            "SELECT no_transaksi, id_permintaan, id_siswa, nis, nama_siswa,
                    nama_kelas, nominal, status_transaksi, tanggal, waktu
             FROM transaksi_kantin
             ORDER BY id DESC
             LIMIT 5"
        )->result_array();
        $transaksi = array();
        foreach ($rows as $row) {
            $transaksi[] = $this->format_transaksi_ringkas($row);
        }

        $pengaturan = $this->db
            ->select('nominal_cepat, status_layanan')
            ->from('pengaturan_dompet')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        return $this->berhasil('Dashboard petugas berhasil dimuat.', array(
            'tanggal_server' => $tanggal,
            'jumlah_transaksi_hari_ini' => (int) $ringkasan['jumlah'],
            'total_nominal_hari_ini' => (int) $ringkasan['total'],
            'nominal_cepat' => $this->nominal_cepat(
                $pengaturan ? $pengaturan['nominal_cepat'] : null
            ),
            'status_layanan' => $pengaturan
                ? $this->nilai_nullable($pengaturan['status_layanan'])
                : null,
            'transaksi_terakhir' => $transaksi,
        ));
    }

    public function scan_qr()
    {
        $otorisasi = $this->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        $input = $this->input_json();
        $token = isset($input['token_qr']) && is_string($input['token_qr'])
            ? trim($input['token_qr'])
            : '';
        if ($token === '' || strlen($token) > 4096) {
            return $this->gagal('Token QR belum valid.', 422, array(
                'token_qr' => 'Token QR wajib diisi.',
            ));
        }

        $qr = $this->db->query(
            "SELECT id_siswa FROM qr_siswa
             WHERE (token_qr_hash = ? OR kode_tampil = ?)
                AND UPPER(TRIM(status_qr)) = 'AKTIF'
             ORDER BY CASE WHEN token_qr_hash = ? THEN 0 ELSE 1 END, id DESC
             LIMIT 1",
            array(hash('sha256', $token), $token, hash('sha256', $token))
        )->row_array();
        if (!$qr) {
            return $this->gagal('QR siswa tidak valid atau sudah tidak aktif.', 404);
        }

        $siswa = $this->ambil_siswa((int) $qr['id_siswa']);
        if (!$siswa) {
            return $this->gagal('Data siswa tidak ditemukan.', 404);
        }
        return $this->berhasil('QR siswa berhasil dikenali.', $this->format_siswa($siswa));
    }

    public function cari_siswa()
    {
        $otorisasi = $this->konteks_petugas();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        $cari = $this->input->get('q', true);
        $cari = is_string($cari) ? trim($cari) : '';
        if ($cari === '' || strlen($cari) < 2 || strlen($cari) > 100) {
            return $this->gagal('Kata pencarian belum valid.', 422, array(
                'q' => 'Masukkan minimal 2 karakter NIS atau nama siswa.',
            ));
        }

        $sql = $this->query_siswa_dasar()."
            WHERE (s.nis = ? OR s.nama_lengkap LIKE ?)
                AND UPPER(TRIM(s.status_siswa)) = 'AKTIF'
                AND UPPER(TRIM(s.status_aktif)) IN ('AKTIF', '1', 'YA')
            ORDER BY CASE WHEN s.nis = ? THEN 0 ELSE 1 END, s.nama_lengkap ASC
            LIMIT ?";
        $rows = $this->db->query(
            $sql,
            array(date('Y-m-d'), $cari, '%'.$cari.'%', $cari, $this->batas_pencarian)
        )->result_array();
        $data = array();
        foreach ($rows as $row) {
            $data[] = $this->format_siswa($row);
        }

        return $this->berhasil(
            empty($data) ? 'Siswa tidak ditemukan.' : 'Pencarian siswa berhasil.',
            $data
        );
    }

    public function konteks_petugas()
    {
        $otorisasi = $this->auth->otorisasi();
        if (!$otorisasi['status']) {
            return $otorisasi;
        }
        if ($otorisasi['data']['mode_aplikasi'] !== 'PETUGAS') {
            return array(
                'status' => false,
                'response' => $this->gagal('Endpoint ini hanya dapat diakses Petugas Kantin.', 403),
            );
        }
        return $otorisasi;
    }

    public function ambil_siswa($id_siswa, $terkunci = false)
    {
        if (!$this->id_valid($id_siswa)) {
            return null;
        }
        $sql = $this->query_siswa_dasar().' WHERE s.id = ? LIMIT 1';
        if ($terkunci) {
            $sql .= ' FOR UPDATE';
        }
        return $this->db->query(
            $sql,
            array(date('Y-m-d'), (int) $id_siswa)
        )->row_array();
    }

    public function format_siswa($row)
    {
        $limit_tersedia = $row['id_limit'] !== null
            && strtoupper(trim((string) $row['status_limit'])) === 'AKTIF';
        $batas_harian = $limit_tersedia ? (int) $row['batas_harian'] : null;
        $penggunaan = (int) $row['penggunaan_hari_ini'];

        return array(
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => (string) $row['nis'],
            'nama_siswa' => (string) $row['nama_siswa'],
            'kelas' => $this->nilai_nullable($row['nama_kelas']),
            'status_siswa' => (string) $row['status_siswa'],
            'status_aktif_siswa' => (string) $row['status_aktif_siswa'],
            'dompet_tersedia' => $row['id_saldo'] !== null,
            'saldo' => $row['id_saldo'] !== null ? (int) $row['saldo'] : null,
            'status_dompet' => $this->nilai_nullable($row['status_dompet']),
            'alasan_blokir' => $this->nilai_nullable($row['alasan_blokir']),
            'limit_tersedia' => $limit_tersedia,
            'limit_valid' => $limit_tersedia
                && (int) $row['batas_harian'] > 0
                && (int) $row['batas_per_transaksi'] > 0
                && (int) $row['batas_per_transaksi'] <= (int) $row['batas_harian'],
            'batas_harian' => $batas_harian,
            'batas_per_transaksi' => $limit_tersedia
                ? (int) $row['batas_per_transaksi']
                : null,
            'penggunaan_hari_ini' => $penggunaan,
            'sisa_limit_hari_ini' => $batas_harian !== null
                ? max(0, $batas_harian - $penggunaan)
                : null,
            'tanggal_penggunaan' => date('Y-m-d'),
        );
    }

    public function format_transaksi_ringkas($row)
    {
        return array(
            'no_transaksi' => (string) $row['no_transaksi'],
            'id_permintaan' => $this->nilai_nullable($row['id_permintaan']),
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => $this->nilai_nullable($row['nis']),
            'nama_siswa' => $this->nilai_nullable($row['nama_siswa']),
            'kelas' => $this->nilai_nullable($row['nama_kelas']),
            'nominal' => (int) $row['nominal'],
            'status_transaksi' => (string) $row['status_transaksi'],
            'tanggal' => $this->nilai_nullable($row['tanggal']),
            'waktu' => $this->nilai_nullable($row['waktu']),
        );
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function query_siswa_dasar()
    {
        return "SELECT s.id AS id_siswa, s.nis, s.nama_lengkap AS nama_siswa,
                    s.status_siswa, s.status_aktif AS status_aktif_siswa,
                    kset.id AS id_kelas_setting, kset.nama_kelas,
                    sd.id AS id_saldo, sd.saldo, sd.status_dompet, sd.alasan_blokir,
                    COALESCE(p.id, pd.id) AS id_limit,
                    COALESCE(p.batas_harian, pd.batas_harian_default) AS batas_harian,
                    COALESCE(p.batas_per_transaksi, pd.batas_transaksi_default) AS batas_per_transaksi,
                    CASE
                        WHEN p.id IS NOT NULL THEN p.status_aktif
                        WHEN pd.id IS NOT NULL THEN 'AKTIF'
                        ELSE NULL
                    END AS status_limit,
                    COALESCE((
                        SELECT SUM(t.nominal)
                        FROM transaksi_kantin t
                        WHERE t.id_siswa = s.id
                            AND t.tanggal = ?
                            AND t.status_transaksi IN ('BERHASIL', 'PEMBATALAN_MENUNGGU')
                    ), 0) AS penggunaan_hari_ini
                FROM siswa s
                LEFT JOIN kelas_siswa ks ON ks.id = (
                    SELECT ks2.id FROM kelas_siswa ks2
                    WHERE ks2.id_siswa = CAST(s.id AS CHAR)
                        AND UPPER(TRIM(ks2.status_aktif)) IN ('1', 'AKTIF', 'YA')
                    ORDER BY ks2.id DESC LIMIT 1
                )
                LEFT JOIN kelas_setting kset
                    ON kset.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                LEFT JOIN saldo_dompet_siswa sd ON sd.id_siswa = s.id
                LEFT JOIN pengaturan_dompet_siswa p ON p.id_siswa = s.id
                LEFT JOIN (
                    SELECT id, batas_harian_default, batas_transaksi_default
                    FROM pengaturan_dompet
                    ORDER BY id DESC
                    LIMIT 1
                ) pd ON 1 = 1";
    }

    private function nominal_cepat($nilai)
    {
        if (!is_string($nilai) || trim($nilai) === '') {
            return array();
        }
        $hasil = array();
        foreach (explode(',', $nilai) as $item) {
            $item = trim($item);
            if (filter_var($item, FILTER_VALIDATE_INT) !== false && (int) $item > 0) {
                $hasil[(int) $item] = (int) $item;
            }
        }
        sort($hasil, SORT_NUMERIC);
        return array_values($hasil);
    }

    private function input_json()
    {
        $raw = $this->input->raw_input_stream;
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    private function id_valid($id)
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0;
    }

    private function nilai_nullable($nilai)
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }
        return (string) $nilai;
    }

    private function berhasil($pesan, $data)
    {
        return array(
            'status' => true, 'result' => true, 'kode' => 200,
            'message' => $pesan, 'pesan' => $pesan, 'data' => $data,
        );
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
