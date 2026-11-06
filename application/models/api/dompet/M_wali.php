<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_wali extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_auth', 'auth');
    }

    public function daftar_anak()
    {
        $otorisasi = $this->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $rows = $this->query_anak(
            (int) $otorisasi['data']['id_wali_siswa'],
            null
        )->result_array();

        $data = array();
        foreach ($rows as $row) {
            $data[] = $this->format_anak($row);
        }

        return $this->berhasil(
            empty($data) ? 'Belum ada anak yang terhubung.' : 'Daftar anak berhasil dimuat.',
            $data
        );
    }

    public function ringkasan($id_siswa)
    {
        $otorisasi = $this->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $anak = $this->anak_dalam_scope(
            (int) $otorisasi['data']['id_wali_siswa'],
            $id_siswa
        );

        if (!$anak) {
            return $this->gagal('Anak tidak ditemukan atau tidak terhubung dengan akun Wali.', 404);
        }

        return $this->berhasil('Ringkasan anak berhasil dimuat.', array(
            'anak' => $this->format_anak($anak),
            'dompet' => $this->format_dompet($anak),
            'penggunaan_harian' => $this->format_penggunaan_harian($anak),
        ));
    }

    public function konteks_wali()
    {
        $otorisasi = $this->auth->otorisasi();
        if (!$otorisasi['status']) {
            return $otorisasi;
        }

        if ($otorisasi['data']['mode_aplikasi'] !== 'WALI') {
            return array(
                'status' => false,
                'response' => $this->gagal('Endpoint ini hanya dapat diakses Wali Murid.', 403),
            );
        }

        return $otorisasi;
    }

    public function anak_dalam_scope($id_wali_siswa, $id_siswa)
    {
        if (!$this->id_valid($id_siswa)) {
            return null;
        }

        return $this->query_anak((int) $id_wali_siswa, (int) $id_siswa)->row_array();
    }

    public function format_anak($row)
    {
        return array(
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => (string) $row['nis'],
            'nama_siswa' => (string) $row['nama_siswa'],
            'kelas' => $this->nilai_nullable($row['nama_kelas']),
            'status_siswa' => (string) $row['status_siswa'],
            'status_aktif_siswa' => (string) $row['status_aktif_siswa'],
            'status_utama' => (string) $row['status_utama'],
            'dompet_tersedia' => $row['id_saldo'] !== null,
            'status_dompet' => $this->nilai_nullable($row['status_dompet']),
            'saldo' => $row['id_saldo'] !== null ? (int) $row['saldo'] : null,
            'limit_tersedia' => $this->limit_tersedia($row),
            'batas_harian' => $this->limit_tersedia($row)
                ? (int) $row['batas_harian']
                : null,
            'batas_per_transaksi' => $this->limit_tersedia($row)
                ? (int) $row['batas_per_transaksi']
                : null,
            'penggunaan_hari_ini' => (int) $row['penggunaan_hari_ini'],
            'jumlah_transaksi_hari_ini' => (int) $row['jumlah_transaksi_hari_ini'],
            'sisa_limit_harian' => $this->sisa_limit($row),
        );
    }

    public function format_dompet($row)
    {
        $limit_tersedia = $this->limit_tersedia($row);
        $limit_valid = $limit_tersedia
            && (int) $row['batas_harian'] > 0
            && (int) $row['batas_per_transaksi'] > 0
            && (int) $row['batas_per_transaksi'] <= (int) $row['batas_harian'];

        return array(
            'id_siswa' => (int) $row['id_siswa'],
            'dompet_tersedia' => $row['id_saldo'] !== null,
            'saldo' => $row['id_saldo'] !== null ? (int) $row['saldo'] : null,
            'status_dompet' => $this->nilai_nullable($row['status_dompet']),
            'alasan_blokir' => $this->nilai_nullable($row['alasan_blokir']),
            'limit_tersedia' => $limit_tersedia,
            'limit_valid' => $limit_valid,
            'status_pengaturan_limit' => $this->nilai_nullable($row['status_limit']),
            'batas_harian' => $limit_tersedia ? (int) $row['batas_harian'] : null,
            'batas_per_transaksi' => $limit_tersedia
                ? (int) $row['batas_per_transaksi']
                : null,
            'penggunaan_hari_ini' => (int) $row['penggunaan_hari_ini'],
            'jumlah_transaksi_hari_ini' => (int) $row['jumlah_transaksi_hari_ini'],
            'sisa_limit_harian' => $this->sisa_limit($row),
            'tanggal_penggunaan' => date('Y-m-d'),
        );
    }

    public function format_penggunaan_harian($row)
    {
        return array(
            'id_siswa' => (int) $row['id_siswa'],
            'tanggal' => date('Y-m-d'),
            'penggunaan' => (int) $row['penggunaan_hari_ini'],
            'jumlah_transaksi' => (int) $row['jumlah_transaksi_hari_ini'],
            'batas_harian' => $this->limit_tersedia($row)
                ? (int) $row['batas_harian']
                : null,
            'sisa_limit_harian' => $this->sisa_limit($row),
        );
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function query_anak($id_wali_siswa, $id_siswa)
    {
        $sql = "SELECT s.id AS id_siswa, s.nis, s.nama_lengkap AS nama_siswa,
                    s.status_siswa, s.status_aktif AS status_aktif_siswa,
                    d.status_utama, kset.nama_kelas,
                    sd.id AS id_saldo, sd.saldo, sd.status_dompet, sd.alasan_blokir,
                    p.id AS id_limit, p.batas_harian, p.batas_per_transaksi,
                    p.status_aktif AS status_limit,
                    COALESCE((
                        SELECT SUM(t.nominal)
                        FROM transaksi_kantin t
                        WHERE t.id_siswa = s.id
                            AND t.tanggal = ?
                            AND t.status_transaksi IN ('BERHASIL', 'PEMBATALAN_MENUNGGU')
                    ), 0) AS penggunaan_hari_ini,
                    COALESCE((
                        SELECT COUNT(*)
                        FROM transaksi_kantin t
                        WHERE t.id_siswa = s.id
                            AND t.tanggal = ?
                            AND t.status_transaksi IN ('BERHASIL', 'PEMBATALAN_MENUNGGU')
                    ), 0) AS jumlah_transaksi_hari_ini
                FROM wali_siswa_detail d
                INNER JOIN siswa s ON s.id = d.id_siswa
                LEFT JOIN kelas_siswa ks ON ks.id = (
                    SELECT ks2.id
                    FROM kelas_siswa ks2
                    WHERE ks2.id_siswa = CAST(s.id AS CHAR)
                        AND UPPER(TRIM(ks2.status_aktif)) IN ('1', 'AKTIF', 'YA')
                    ORDER BY ks2.id DESC
                    LIMIT 1
                )
                LEFT JOIN kelas_setting kset ON kset.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                LEFT JOIN saldo_dompet_siswa sd ON sd.id_siswa = s.id
                LEFT JOIN pengaturan_dompet_siswa p ON p.id_siswa = s.id
                WHERE d.id_wali_siswa = ?
                    AND UPPER(TRIM(s.status_aktif)) = 'AKTIF'";
        $params = array(date('Y-m-d'), date('Y-m-d'), (int) $id_wali_siswa);

        if ($id_siswa !== null) {
            $sql .= ' AND s.id = ?';
            $params[] = (int) $id_siswa;
        }

        $sql .= " ORDER BY
                    CASE WHEN UPPER(TRIM(d.status_utama)) = 'YA' THEN 0 ELSE 1 END,
                    s.nama_lengkap ASC";

        if ($id_siswa !== null) {
            $sql .= ' LIMIT 1';
        }

        return $this->db->query($sql, $params);
    }

    private function limit_tersedia($row)
    {
        return $row['id_limit'] !== null
            && strtoupper(trim((string) $row['status_limit'])) === 'AKTIF';
    }

    private function sisa_limit($row)
    {
        if (!$this->limit_tersedia($row)) {
            return null;
        }

        return max(0, (int) $row['batas_harian'] - (int) $row['penggunaan_hari_ini']);
    }

    private function nilai_nullable($nilai)
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }

        return (string) $nilai;
    }

    private function id_valid($id)
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0;
    }

    private function berhasil($pesan, $data)
    {
        return array(
            'status' => true,
            'result' => true,
            'kode' => 200,
            'message' => $pesan,
            'pesan' => $pesan,
            'data' => $data,
        );
    }

    private function gagal($pesan, $kode, $kesalahan = array())
    {
        return array(
            'status' => false,
            'result' => false,
            'kode' => $kode,
            'message' => $pesan,
            'pesan' => $pesan,
            'kesalahan' => (object) $kesalahan,
            'data' => null,
        );
    }
}
