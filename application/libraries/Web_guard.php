<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Guard ringan untuk endpoint web admin baru dan endpoint existing yang
 * disentuh bertahap. Library ini tidak digunakan oleh Android/API.
 */
class Web_guard
{
    const SESSION_KEY = 'admin';
    const CSRF_SESSION_KEY = 'web_csrf_token';
    const CSRF_FIELD = 'web_csrf_token';
    const ACTION_TABLE = 'conf_menu_action';

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function session_data()
    {
        $session = $this->CI->session->userdata(self::SESSION_KEY);

        return is_array($session) ? $session : null;
    }

    public function current_user()
    {
        $session = $this->session_data();
        if (empty($session['id_user'])) {
            return null;
        }

        return $this->CI->db
            ->select('id, nama_user, username, id_level, level, id_pegawai, id_wali_siswa, status_aktif')
            ->get_where('user', ['id' => (int) $session['id_user']])
            ->row_array();
    }

    public function is_web_user_allowed($user)
    {
        if (!is_array($user)) {
            return false;
        }

        $status = strtoupper(trim((string) ($user['status_aktif'] ?? '')));
        $level = strtoupper(trim((string) ($user['level'] ?? '')));
        $id_level = (string) ($user['id_level'] ?? '');

        return $status === 'AKTIF'
            && $id_level !== '8'
            && $level !== 'WALI MURID';
    }

    public function enforce_login($json = false)
    {
        $session = $this->session_data();
        $user = $this->current_user();

        if (!$session || !$user) {
            $this->deny(401, 'Sesi login tidak ditemukan.', $json);
        }

        if (!$this->is_web_user_allowed($user)) {
            $this->CI->session->unset_userdata(self::SESSION_KEY);
            $this->CI->session->sess_regenerate(true);
            $this->deny(403, 'Akun tidak diizinkan masuk ke web admin.', $json);
        }

        return $user;
    }

    public function enforce_level($allowed_level_ids, $json = true)
    {
        $user = $this->enforce_login($json);
        $allowed_level_ids = array_map('strval', (array) $allowed_level_ids);

        if (!in_array((string) $user['id_level'], $allowed_level_ids, true)) {
            $this->deny(403, 'Level pengguna tidak memiliki akses.', $json);
        }

        return $user;
    }

    public function enforce_menu($id_menu, $json = true)
    {
        $user = $this->enforce_login($json);
        $has_menu = $this->CI->db
            ->where('id_level', (string) $user['id_level'])
            ->where('id_menu', (int) $id_menu)
            ->count_all_results('conf_list_menu') > 0;

        if (!$has_menu) {
            $this->deny(403, 'Menu tidak tersedia untuk level pengguna.', $json);
        }

        return $user;
    }

    /**
     * $allow_menu_fallback hanya untuk endpoint legacy selama rollout.
     * Endpoint Master Siswa/Dompet baru wajib memakai nilai default FALSE.
     */
    public function enforce_action($id_menu, $action, $json = true, $allow_menu_fallback = false)
    {
        $user = $this->enforce_menu($id_menu, $json);
        $action = strtolower(trim((string) $action));

        if (!preg_match('/^[a-z][a-z0-9_]{0,49}$/', $action)) {
            $this->deny(400, 'Action permission tidak valid.', $json);
        }

        if (!$this->CI->db->table_exists(self::ACTION_TABLE)) {
            if ($allow_menu_fallback) {
                return $user;
            }

            $this->deny(503, 'Action permission belum diaktifkan.', $json);
        }

        $allowed = $this->CI->db
            ->where('id_level', (int) $user['id_level'])
            ->where('id_menu', (int) $id_menu)
            ->where('action', $action)
            ->where('status_aktif', 'AKTIF')
            ->count_all_results(self::ACTION_TABLE) > 0;

        if (!$allowed) {
            $this->deny(403, 'Action tidak diizinkan untuk level pengguna.', $json);
        }

        return $user;
    }

    public function csrf_token()
    {
        $token = $this->CI->session->userdata(self::CSRF_SESSION_KEY);
        if (!is_string($token) || strlen($token) < 64) {
            $token = bin2hex(random_bytes(32));
            $this->CI->session->set_userdata(self::CSRF_SESSION_KEY, $token);
        }

        return $token;
    }

    public function rotate_csrf_token()
    {
        $token = bin2hex(random_bytes(32));
        $this->CI->session->set_userdata(self::CSRF_SESSION_KEY, $token);

        return $token;
    }

    public function validate_csrf()
    {
        $expected = $this->CI->session->userdata(self::CSRF_SESSION_KEY);
        $provided = $this->CI->input->post(self::CSRF_FIELD, false);

        if (!$provided) {
            $provided = $this->CI->input->get_request_header('X-Web-CSRF-Token', true);
        }

        return is_string($expected)
            && is_string($provided)
            && strlen($expected) >= 64
            && hash_equals($expected, $provided);
    }

    public function enforce_csrf($json = true)
    {
        if (!$this->validate_csrf()) {
            $this->deny(403, 'Token keamanan form tidak valid.', $json);
        }

        return true;
    }

    public function respond_json($success, $message, $data = null, $status = 200, $errors = [], $meta = [])
    {
        $payload = [
            'success' => (bool) $success,
            'message' => (string) $message,
            'data' => $data,
            'errors' => $errors,
            'meta' => $meta,
        ];

        return $this->CI->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            ));
    }

    protected function deny($status, $message, $json)
    {
        if ($json) {
            $this->respond_json(false, $message, null, $status);
            $this->CI->output->_display();
            exit;
        }

        $this->CI->session->set_flashdata(
            'message',
            '<div class="alert alert-danger" role="alert">' . html_escape($message) . '</div>'
        );
        redirect('/');
        exit;
    }
}

