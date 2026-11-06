<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_auth extends CI_Model
{
    private $masa_berlaku_token;
    private $panjang_minimal_password;
    private $mode_per_level;
    private $level_internal;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('api');

        $this->masa_berlaku_token = (int) $this->config->item('api_token_lifetime_seconds');
        $this->panjang_minimal_password = (int) $this->config->item('api_password_min_length');
        $this->mode_per_level = (array) $this->config->item('api_role_modes');
        $this->level_internal = (array) $this->config->item('api_internal_levels');
    }

    public function login()
    {
        return $this->proses_login(false);
    }

    public function login_internal()
    {
        return $this->proses_login(true);
    }

    private function proses_login($internal)
    {
        $input = $this->input_json();
        $kesalahan = $this->validasi_login($input);

        if (!empty($kesalahan)) {
            return $this->gagal('Data login belum valid.', 422, $kesalahan);
        }

        $identitas = trim($input['identitas']);
        $pengguna = $this->cari_pengguna($identitas);

        if (count($pengguna) !== 1) {
            return $this->gagal('Identitas atau password tidak sesuai.', 401);
        }

        $pengguna = $pengguna[0];
        $pengguna['id_perangkat'] = trim($input['id_perangkat']);

        if (!password_verify($input['password'], $pengguna['password'])) {
            $this->catat_aktivitas(
                $pengguna,
                'LOGIN_GAGAL',
                'Login ditolak karena password tidak sesuai.'
            );
            return $this->gagal('Identitas atau password tidak sesuai.', 401);
        }

        if (strtoupper(trim((string) $pengguna['status_aktif'])) !== 'AKTIF') {
            $this->catat_aktivitas(
                $pengguna,
                'LOGIN_DITOLAK',
                'Login ditolak karena akun tidak aktif.'
            );
            return $this->gagal('Akun sedang tidak aktif. Hubungi pihak sekolah.', 403);
        }

        $mode = $internal
            ? ($this->level_internal_diizinkan($pengguna['level']) ? 'INTERNAL' : null)
            : $this->mode_pengguna($pengguna['level']);
        if ($mode === null) {
            $this->catat_aktivitas(
                $pengguna,
                'LOGIN_DITOLAK',
                $internal
                    ? 'Login internal ditolak karena level tidak diizinkan.'
                    : 'Login ditolak karena level tidak memiliki akses Android.'
            );
            return $this->gagal(
                $internal
                    ? 'Akun tidak memiliki akses ke API internal.'
                    : 'Akun tidak memiliki akses ke aplikasi Android.',
                403
            );
        }

        if ($mode === 'WALI') {
            if ((int) $pengguna['id_wali_siswa'] <= 0
                || strtoupper(trim((string) $pengguna['status_wali'])) !== 'AKTIF') {
                $this->catat_aktivitas(
                    $pengguna,
                    'LOGIN_DITOLAK',
                    'Login ditolak karena data Wali Murid tidak aktif atau belum terhubung.'
                );
                return $this->gagal('Data Wali Murid tidak aktif atau belum terhubung.', 403);
            }
        }

        $id_perangkat = trim($input['id_perangkat']);
        $perangkat = $this->perangkat_pengguna((int) $pengguna['id'], $id_perangkat);

        if ($perangkat
            && strtoupper(trim((string) $perangkat['status_perangkat'])) === 'DIBLOKIR') {
            $this->catat_aktivitas(
                $pengguna,
                'LOGIN_DITOLAK',
                'Login ditolak karena perangkat diblokir.'
            );
            return $this->gagal('Perangkat ini diblokir. Hubungi pihak sekolah.', 403);
        }

        if ($this->masa_berlaku_token <= 0) {
            return $this->gagal('Konfigurasi masa berlaku token tidak valid.', 500);
        }

        try {
            $token_mentah = bin2hex(random_bytes(32));
        } catch (Exception $exception) {
            log_message('error', 'Gagal membuat token API: '.$exception->getMessage());
            return $this->gagal('Sesi belum dapat dibuat. Coba lagi nanti.', 500);
        }

        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        $kedaluwarsa = $sekarang->modify('+'.$this->masa_berlaku_token.' seconds');

        $this->db->trans_begin();

        if (password_needs_rehash($pengguna['password'], PASSWORD_DEFAULT)) {
            $hash_baru = password_hash($input['password'], PASSWORD_DEFAULT);
            if ($hash_baru === false) {
                $this->db->trans_rollback();
                return $this->gagal('Password belum dapat diamankan. Coba lagi nanti.', 500);
            }

            $this->db->where('id', (int) $pengguna['id']);
            $this->db->update('user', array('password' => $hash_baru));
        }

        $data_perangkat = array(
            'nama_perangkat' => trim($input['nama_perangkat']),
            'sistem_operasi' => trim($input['sistem_operasi']),
            'versi_aplikasi' => trim($input['versi_aplikasi']),
            'status_perangkat' => 'AKTIF',
            'terakhir_aktif' => $sekarang->format('Y-m-d H:i:s'),
        );

        if ($perangkat) {
            $this->db->where('id', (int) $perangkat['id']);
            $this->db->update('perangkat_pengguna', $data_perangkat);
        } else {
            $data_perangkat['id_user'] = (int) $pengguna['id'];
            $data_perangkat['id_perangkat'] = $id_perangkat;
            $this->db->insert('perangkat_pengguna', $data_perangkat);
        }

        $this->db->where('id_user', (int) $pengguna['id']);
        $this->db->where('id_perangkat', $id_perangkat);
        $this->db->where('status_token', 'AKTIF');
        $this->db->update('token_api', array('status_token' => 'DICABUT'));

        $this->db->insert('token_api', array(
            'id_user' => (int) $pengguna['id'],
            'token_hash' => hash('sha256', $token_mentah),
            'jenis_perangkat' => trim($input['sistem_operasi']),
            'id_perangkat' => $id_perangkat,
            'tanggal_dibuat' => $sekarang->format('Y-m-d'),
            'waktu_dibuat' => $sekarang->format('H:i:s'),
            'tanggal_kedaluwarsa' => $kedaluwarsa->format('Y-m-d'),
            'waktu_kedaluwarsa' => $kedaluwarsa->format('H:i:s'),
            'status_token' => 'AKTIF',
            'terakhir_dipakai' => $sekarang->format('Y-m-d H:i:s'),
        ));
        $id_token = (int) $this->db->insert_id();

        $this->db->where('id', (int) $pengguna['id']);
        $this->db->update('user', array(
            'terakhir_login' => $sekarang->format('Y-m-d H:i:s'),
        ));

        $this->catat_aktivitas(
            $pengguna,
            'LOGIN',
            $internal ? 'Login API internal berhasil.' : 'Login aplikasi Android berhasil.',
            $id_token
        );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Login belum dapat diproses. Coba lagi nanti.', 500);
        }

        $this->db->trans_commit();

        return $this->berhasil('Login berhasil.', array(
            'token' => $token_mentah,
            'kedaluwarsa' => $kedaluwarsa->format(DateTimeInterface::ATOM),
            'pengguna' => $this->data_pengguna($pengguna),
            'mode_aplikasi' => $mode,
        ));
    }

    public function sesi()
    {
        $validasi = $this->otorisasi();
        if (!$validasi['status']) {
            return $validasi['response'];
        }

        $konteks = $validasi['data'];
        $sekarang = date('Y-m-d H:i:s');

        $this->db->where('id', (int) $konteks['id_token']);
        $this->db->update('token_api', array('terakhir_dipakai' => $sekarang));

        $this->db->where('id_user', (int) $konteks['id']);
        $this->db->where('id_perangkat', $konteks['id_perangkat']);
        $this->db->update('perangkat_pengguna', array('terakhir_aktif' => $sekarang));

        return $this->berhasil('Sesi masih aktif.', array(
            'kedaluwarsa' => $this->format_kedaluwarsa($konteks),
            'pengguna' => $this->data_pengguna($konteks),
            'mode_aplikasi' => $konteks['mode_aplikasi'],
        ));
    }

    public function sesi_internal()
    {
        $validasi = $this->otorisasi_internal();
        if (!$validasi['status']) {
            return $validasi['response'];
        }

        $konteks = $validasi['data'];
        $sekarang = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $konteks['id_token']);
        $this->db->update('token_api', array('terakhir_dipakai' => $sekarang));
        $this->db->where('id_user', (int) $konteks['id']);
        $this->db->where('id_perangkat', $konteks['id_perangkat']);
        $this->db->update('perangkat_pengguna', array('terakhir_aktif' => $sekarang));

        return $this->berhasil('Sesi internal masih aktif.', array(
            'kedaluwarsa' => $this->format_kedaluwarsa($konteks),
            'pengguna' => $this->data_pengguna($konteks),
            'mode_aplikasi' => 'INTERNAL',
        ));
    }

    public function logout()
    {
        return $this->proses_logout(false);
    }

    public function logout_internal()
    {
        return $this->proses_logout(true);
    }

    private function proses_logout($internal)
    {
        $token_mentah = $this->bearer_token();
        if ($token_mentah === null) {
            return $this->gagal('Bearer token wajib dikirim.', 401);
        }

        $sql = "SELECT t.id AS id_token, t.id_perangkat, t.status_token,
                    u.id, u.nama_user, u.username, u.level
                FROM token_api t
                INNER JOIN user u ON u.id = t.id_user
                WHERE t.token_hash = ?
                LIMIT 1";
        $token = $this->db
            ->query($sql, array(hash('sha256', $token_mentah)))
            ->row_array();

        if (!$token) {
            return $this->gagal('Sesi tidak valid atau sudah kedaluwarsa.', 401);
        }

        if (strtoupper(trim((string) $token['status_token'])) === 'AKTIF') {
            $this->db->trans_begin();

            $this->db->where('id', (int) $token['id_token']);
            $this->db->update('token_api', array(
                'status_token' => 'LOGOUT',
                'terakhir_dipakai' => date('Y-m-d H:i:s'),
            ));

            $this->catat_aktivitas(
                $token,
                'LOGOUT',
                $internal
                    ? 'Logout API internal berhasil.'
                    : 'Logout aplikasi Android berhasil.',
                (int) $token['id_token']
            );

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return $this->gagal('Logout belum dapat diproses. Coba lagi nanti.', 500);
            }

            $this->db->trans_commit();
        }

        return $this->berhasil('Logout berhasil.', null);
    }

    public function ubah_password()
    {
        $otorisasi = $this->otorisasi();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $input = $this->input_json();
        $kesalahan = array();

        if (!isset($input['password_lama'])
            || !is_string($input['password_lama'])
            || $input['password_lama'] === '') {
            $kesalahan['password_lama'] = 'Password lama wajib diisi.';
        } elseif (strlen($input['password_lama']) > 4096) {
            $kesalahan['password_lama'] = 'Password lama terlalu panjang.';
        }

        if (!isset($input['password_baru'])
            || !is_string($input['password_baru'])
            || $input['password_baru'] === '') {
            $kesalahan['password_baru'] = 'Password baru wajib diisi.';
        } elseif (strlen($input['password_baru']) < $this->panjang_minimal_password) {
            $kesalahan['password_baru'] = 'Password baru minimal '
                .$this->panjang_minimal_password.' karakter.';
        } elseif (strlen($input['password_baru']) > 4096) {
            $kesalahan['password_baru'] = 'Password baru terlalu panjang.';
        }

        if (!empty($kesalahan)) {
            return $this->gagal('Data perubahan password belum valid.', 422, $kesalahan);
        }

        $konteks = $otorisasi['data'];
        if (!password_verify($input['password_lama'], $konteks['password'])) {
            $this->catat_aktivitas(
                $konteks,
                'UBAH_PASSWORD_GAGAL',
                'Perubahan password ditolak karena password lama tidak sesuai.',
                (int) $konteks['id_token']
            );
            return $this->gagal(
                'Password lama tidak sesuai.',
                422,
                array('password_lama' => 'Password lama tidak sesuai.')
            );
        }

        if (hash_equals($input['password_lama'], $input['password_baru'])) {
            $this->catat_aktivitas(
                $konteks,
                'UBAH_PASSWORD_GAGAL',
                'Perubahan password ditolak karena password baru sama dengan password lama.',
                (int) $konteks['id_token']
            );
            return $this->gagal(
                'Password baru harus berbeda dari password lama.',
                422,
                array('password_baru' => 'Gunakan password baru yang berbeda.')
            );
        }

        $hash_baru = password_hash($input['password_baru'], PASSWORD_DEFAULT);
        if ($hash_baru === false || !password_verify($input['password_baru'], $hash_baru)) {
            return $this->gagal('Password baru belum dapat diamankan. Coba lagi nanti.', 500);
        }

        $this->db->trans_begin();

        $this->db->where('id', (int) $konteks['id']);
        $this->db->update('user', array(
            'password' => $hash_baru,
            'password_text' => null,
        ));

        $this->db->where('id_user', (int) $konteks['id']);
        $this->db->where('id !=', (int) $konteks['id_token']);
        $this->db->where('status_token', 'AKTIF');
        $this->db->update('token_api', array('status_token' => 'DICABUT'));
        $jumlah_sesi_dicabut = $this->db->affected_rows();

        $this->catat_aktivitas(
            $konteks,
            'UBAH_PASSWORD',
            'Password akun berhasil diubah; sesi lain dicabut.',
            (int) $konteks['id_token']
        );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Password belum dapat diubah. Coba lagi nanti.', 500);
        }

        $this->db->trans_commit();

        return $this->berhasil('Password berhasil diubah.', array(
            'sesi_lain_dicabut' => (int) $jumlah_sesi_dicabut,
        ));
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function validasi_login($input)
    {
        $kesalahan = array();

        $this->validasi_teks_wajib($input, 'identitas', 'Identitas', 150, $kesalahan);

        if (!isset($input['password']) || !is_string($input['password']) || $input['password'] === '') {
            $kesalahan['password'] = 'Password wajib diisi.';
        } elseif (strlen($input['password']) > 4096) {
            $kesalahan['password'] = 'Password terlalu panjang.';
        }

        $this->validasi_teks_wajib($input, 'id_perangkat', 'ID perangkat', 255, $kesalahan);
        $this->validasi_teks_wajib($input, 'nama_perangkat', 'Nama perangkat', 150, $kesalahan);
        $this->validasi_teks_wajib($input, 'sistem_operasi', 'Sistem operasi', 100, $kesalahan);
        $this->validasi_teks_wajib($input, 'versi_aplikasi', 'Versi aplikasi', 50, $kesalahan);

        return $kesalahan;
    }

    private function validasi_teks_wajib($input, $field, $label, $maksimal, &$kesalahan)
    {
        if (!isset($input[$field]) || !is_string($input[$field]) || trim($input[$field]) === '') {
            $kesalahan[$field] = $label.' wajib diisi.';
        } elseif (strlen(trim($input[$field])) > $maksimal) {
            $kesalahan[$field] = $label.' maksimal '.$maksimal.' karakter.';
        }
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

    private function cari_pengguna($identitas)
    {
        $sql = "SELECT u.id, u.nama_user, u.username, u.password, u.id_level, u.level,
                    u.id_pegawai, u.id_wali_siswa, u.status_aktif,
                    w.nama_wali, w.status_aktif AS status_wali
                FROM user u
                LEFT JOIN wali_siswa w ON w.id = u.id_wali_siswa
                WHERE u.username = ? OR u.email = ? OR u.no_telp = ?
                LIMIT 2";

        return $this->db->query($sql, array($identitas, $identitas, $identitas))->result_array();
    }

    private function perangkat_pengguna($id_user, $id_perangkat)
    {
        return $this->db
            ->from('perangkat_pengguna')
            ->where('id_user', $id_user)
            ->where('id_perangkat', $id_perangkat)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function otorisasi()
    {
        return $this->proses_otorisasi(false);
    }

    public function otorisasi_internal()
    {
        return $this->proses_otorisasi(true);
    }

    private function proses_otorisasi($internal)
    {
        $token_mentah = $this->bearer_token();
        if ($token_mentah === null) {
            return array('status' => false, 'response' => $this->gagal('Bearer token wajib dikirim.', 401));
        }

        $sql = "SELECT t.id AS id_token, t.id_perangkat, t.tanggal_kedaluwarsa,
                    t.waktu_kedaluwarsa, t.status_token,
                    u.id, u.nama_user, u.username, u.password, u.level,
                    u.id_pegawai,
                    u.id_wali_siswa, u.status_aktif,
                    w.nama_wali, w.status_aktif AS status_wali,
                    p.status_perangkat
                FROM token_api t
                INNER JOIN user u ON u.id = t.id_user
                LEFT JOIN wali_siswa w ON w.id = u.id_wali_siswa
                LEFT JOIN perangkat_pengguna p
                    ON p.id_user = t.id_user AND p.id_perangkat = t.id_perangkat
                WHERE t.token_hash = ?
                LIMIT 1";

        $konteks = $this->db
            ->query($sql, array(hash('sha256', $token_mentah)))
            ->row_array();

        if (!$konteks || strtoupper(trim((string) $konteks['status_token'])) !== 'AKTIF') {
            return array('status' => false, 'response' => $this->gagal('Sesi tidak valid atau sudah kedaluwarsa.', 401));
        }

        $kedaluwarsa = $this->waktu_kedaluwarsa($konteks);
        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));

        if ($kedaluwarsa === null || $kedaluwarsa <= $sekarang) {
            $this->db->where('id', (int) $konteks['id_token']);
            $this->db->update('token_api', array('status_token' => 'KEDALUWARSA'));
            return array('status' => false, 'response' => $this->gagal('Sesi tidak valid atau sudah kedaluwarsa.', 401));
        }

        if (strtoupper(trim((string) $konteks['status_aktif'])) !== 'AKTIF') {
            $this->cabut_token((int) $konteks['id_token']);
            return array('status' => false, 'response' => $this->gagal('Akun sedang tidak aktif. Hubungi pihak sekolah.', 403));
        }

        $mode = $internal
            ? ($this->level_internal_diizinkan($konteks['level']) ? 'INTERNAL' : null)
            : $this->mode_pengguna($konteks['level']);
        if ($mode === null) {
            return array('status' => false, 'response' => $this->gagal(
                $internal
                    ? 'Akun tidak memiliki akses ke API internal.'
                    : 'Akun tidak memiliki akses ke aplikasi Android.',
                403
            ));
        }

        if ($mode === 'WALI'
            && ((int) $konteks['id_wali_siswa'] <= 0
                || strtoupper(trim((string) $konteks['status_wali'])) !== 'AKTIF')) {
            $this->cabut_token((int) $konteks['id_token']);
            return array('status' => false, 'response' => $this->gagal('Data Wali Murid tidak aktif atau belum terhubung.', 403));
        }

        if (strtoupper(trim((string) $konteks['status_perangkat'])) !== 'AKTIF') {
            $this->cabut_token((int) $konteks['id_token']);
            return array('status' => false, 'response' => $this->gagal('Perangkat tidak aktif atau diblokir.', 403));
        }

        $konteks['mode_aplikasi'] = $mode;
        return array('status' => true, 'data' => $konteks);
    }

    public function catat_aktivitas($pengguna, $aksi, $keterangan, $id_referensi = null)
    {
        $nama = trim((string) $pengguna['nama_user']);
        if ($nama === '' && isset($pengguna['nama_wali'])) {
            $nama = trim((string) $pengguna['nama_wali']);
        }
        if ($nama === '') {
            $nama = trim((string) $pengguna['username']);
        }

        $this->db->insert('log_aktivitas_dompet', array(
            'id_user' => (int) $pengguna['id'],
            'nama_user' => $nama,
            'level' => (string) $pengguna['level'],
            'modul' => 'AUTENTIKASI',
            'aksi' => $aksi,
            'id_referensi' => $id_referensi,
            'keterangan' => $keterangan,
            'alamat_ip' => $this->input->ip_address(),
            'id_perangkat' => isset($pengguna['id_perangkat'])
                ? (string) $pengguna['id_perangkat']
                : null,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ));
    }

    private function bearer_token()
    {
        $header = $this->input->get_request_header('Authorization', true);
        if (!$header && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!is_string($header) || !preg_match('/^Bearer\s+([a-f0-9]{64})$/i', trim($header), $cocok)) {
            return null;
        }

        return $cocok[1];
    }

    private function mode_pengguna($level)
    {
        foreach ($this->mode_per_level as $nama_level => $mode) {
            if (strcasecmp(trim((string) $level), trim((string) $nama_level)) === 0) {
                return $mode;
            }
        }

        return null;
    }

    private function level_internal_diizinkan($level)
    {
        foreach ($this->level_internal as $nama_level) {
            if (strcasecmp(trim((string) $level), trim((string) $nama_level)) === 0) {
                return true;
            }
        }
        return false;
    }

    private function data_pengguna($pengguna)
    {
        $nama = trim((string) $pengguna['nama_user']);
        if ($nama === '' && isset($pengguna['nama_wali'])) {
            $nama = trim((string) $pengguna['nama_wali']);
        }
        if ($nama === '') {
            $nama = trim((string) $pengguna['username']);
        }

        return array(
            'id' => (int) $pengguna['id'],
            'nama' => $nama,
            'level' => (string) $pengguna['level'],
        );
    }

    private function waktu_kedaluwarsa($data)
    {
        $nilai = trim((string) $data['tanggal_kedaluwarsa']).' '.trim((string) $data['waktu_kedaluwarsa']);
        $tanggal = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            $nilai,
            new DateTimeZone('Asia/Jakarta')
        );

        return $tanggal instanceof DateTimeImmutable ? $tanggal : null;
    }

    private function format_kedaluwarsa($data)
    {
        $tanggal = $this->waktu_kedaluwarsa($data);
        return $tanggal ? $tanggal->format(DateTimeInterface::ATOM) : null;
    }

    private function cabut_token($id_token)
    {
        $this->db->where('id', $id_token);
        $this->db->update('token_api', array('status_token' => 'DICABUT'));
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
