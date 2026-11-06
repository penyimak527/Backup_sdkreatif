<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_profil extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_auth', 'auth');
    }

    public function profil()
    {
        $otorisasi = $this->auth->otorisasi();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $konteks = $otorisasi['data'];
        $sql = "SELECT u.id, u.nama_user, u.username, u.level, u.email AS email_user,
                    u.no_telp AS no_telp_user, u.terakhir_login,
                    w.nama_wali, w.hubungan, w.email AS email_wali,
                    w.no_telp AS no_telp_wali,
                    p.id_perangkat, p.nama_perangkat, p.sistem_operasi,
                    p.versi_aplikasi
                FROM user u
                LEFT JOIN wali_siswa w ON w.id = u.id_wali_siswa
                LEFT JOIN perangkat_pengguna p
                    ON p.id_user = u.id AND p.id_perangkat = ?
                WHERE u.id = ?
                LIMIT 1";

        $profil = $this->db
            ->query($sql, array($konteks['id_perangkat'], (int) $konteks['id']))
            ->row_array();

        if (!$profil) {
            return $this->gagal('Profil pengguna tidak ditemukan.', 404);
        }

        $nama = trim((string) $profil['nama_user']);
        if ($nama === '') {
            $nama = trim((string) $profil['nama_wali']);
        }
        if ($nama === '') {
            $nama = trim((string) $profil['username']);
        }

        $email = trim((string) $profil['email_wali']);
        if ($email === '') {
            $email = trim((string) $profil['email_user']);
        }

        $no_telp = trim((string) $profil['no_telp_wali']);
        if ($no_telp === '') {
            $no_telp = trim((string) $profil['no_telp_user']);
        }

        return $this->berhasil('Profil berhasil dimuat.', array(
            'id' => (int) $profil['id'],
            'nama' => $nama,
            'username' => (string) $profil['username'],
            'level' => (string) $profil['level'],
            'mode_aplikasi' => $konteks['mode_aplikasi'],
            'email' => $email,
            'no_telp' => $no_telp,
            'hubungan' => $konteks['mode_aplikasi'] === 'WALI'
                ? (string) $profil['hubungan']
                : null,
            'terakhir_login' => $profil['terakhir_login'],
            'perangkat' => array(
                'id_perangkat' => (string) $profil['id_perangkat'],
                'nama_perangkat' => (string) $profil['nama_perangkat'],
                'sistem_operasi' => (string) $profil['sistem_operasi'],
                'versi_aplikasi' => (string) $profil['versi_aplikasi'],
            ),
        ));
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
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

    private function gagal($pesan, $kode)
    {
        return array(
            'status' => false,
            'result' => false,
            'kode' => $kode,
            'message' => $pesan,
            'pesan' => $pesan,
            'kesalahan' => (object) array(),
            'data' => null,
        );
    }
}
