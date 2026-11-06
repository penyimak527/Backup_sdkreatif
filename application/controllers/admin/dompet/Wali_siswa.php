<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wali_siswa extends CI_Controller
{
    const MENU_ID = 74;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_wali_siswa', 'model');
        $this->require_login();
    }

    public function index()
    {
        $this->render('wali_siswa', [
            'csrf_token' => '',
        ]);
    }

    public function result()
    {
        $this->post_only();

        $result = $this->model->result($this->filters());

        return $this->json(
            true,
            'Data wali berhasil dimuat.',
            $result['rows'],
            200,
            [],
            $result['meta']
        );
    }

    public function detail()
    {
        $this->post_only();

        $result = $this->model->detail((int) $this->input->post('id'));

        return $this->json(
            (bool) $result,
            $result ? 'Detail wali berhasil dimuat.' : 'Wali tidak ditemukan.',
            $result,
            $result ? 200 : 404
        );
    }

    public function simpan()
    {
        $this->post_only();

        $payload = [
            'nama_wali' => trim((string) $this->input->post('nama_wali', true)),
            'hubungan' => trim((string) $this->input->post('hubungan', true)),
            'no_telp' => trim((string) $this->input->post('no_telp', true)),
            'email' => trim((string) $this->input->post('email', true)),
            'alamat' => trim((string) $this->input->post('alamat', true)),
        ];
        $username = trim((string) $this->input->post('username', true));

        if (
            mb_strlen($payload['nama_wali']) < 2
            || ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL))
            || !preg_match('/^[A-Za-z0-9._-]{4,100}$/', $username)
        ) {
            return $this->json(
                false,
                'Data wali belum valid.',
                null,
                422,
                [
                    'nama_wali' => 'Nama minimal 2 karakter; email harus valid bila diisi.',
                    'username' => 'Username wajib 4-100 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
                ]
            );
        }

        $result = $this->model->save(
            (int) $this->input->post('id'),
            $payload,
            $username,
            $this->current_user()
        );

        return $this->model_json($result, 'Data wali berhasil disimpan.');
    }

    public function status()
    {
        $this->post_only();

        $status = strtoupper((string) $this->input->post('status', true));
        if (!in_array($status, ['AKTIF', 'NONAKTIF'], true)) {
            return $this->json(false, 'Status tidak valid.', null, 422);
        }

        $result = $this->model->status(
            (int) $this->input->post('id'),
            $status,
            $this->current_user()
        );

        return $this->model_json($result, 'Status wali berhasil diperbarui.');
    }

    public function relasi()
    {
        $this->post_only();

        $status_utama = strtoupper((string) $this->input->post('status_utama')) === 'YA'
            ? 'YA'
            : 'TIDAK';

        $result = $this->model->relate(
            (int) $this->input->post('id_wali'),
            (int) $this->input->post('id_siswa'),
            $status_utama,
            $this->current_user()
        );

        return $this->model_json($result, 'Relasi anak berhasil disimpan.');
    }

    public function reset_password()
    {
        $this->post_only();

        $password = (string) $this->input->post('password', false);
        if (strlen($password) < 6) {
            return $this->json(
                false,
                'Password minimal 6 karakter.',
                null,
                422
            );
        }

        $result = $this->model->reset_password(
            (int) $this->input->post('id'),
            $password
        );

        return $this->model_json(
            $result,
            'Password wali berhasil direset tanpa penyimpanan plaintext.'
        );
    }

    public function cari_siswa()
    {
        $this->post_only();

        $data = $this->model->active_students(
            trim((string) $this->input->post('search', true))
        );

        return $this->json(true, 'Data siswa berhasil dimuat.', $data);
    }

    public function trash()
    {
        $this->post_only();

        return $this->json(
            true,
            'Trash wali berhasil dimuat.',
            $this->model->trash_rows()
        );
    }

    public function hapus()
    {
        $this->post_only();

        $result = $this->model->soft_delete(
            (int) $this->input->post('id'),
            $this->current_user()
        );

        return $this->model_json(
            $result,
            'Wali dipindahkan ke Trash tanpa menghapus data dan relasi.'
        );
    }

    public function restore()
    {
        $this->post_only();

        $result = $this->model->restore(
            (int) $this->input->post('id'),
            $this->current_user()
        );

        return $this->model_json($result, 'Wali dipulihkan sebagai NONAKTIF.');
    }

    private function filters()
    {
        return [
            'search' => trim((string) $this->input->post('search', true)),
            'status' => strtoupper(trim((string) $this->input->post('status', true))),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ];
    }

    private function render($view, $data = [])
    {
        $data['title'] = 'Wali Siswa';

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/'.$view, $data);
        $this->load->view('template/footer');
    }

    private function current_user()
    {
        $session = $this->session->userdata('admin');

        return [
            'id' => (int) ($session['id_user'] ?? 0),
            'nama_user' => (string) ($session['nama_lengkap'] ?? $session['username'] ?? ''),
            'id_level' => (int) ($session['id_level'] ?? 0),
            'level' => (string) ($session['level'] ?? ''),
        ];
    }

    private function post_only()
    {
        if ($this->input->method(true) === 'POST') {
            return;
        }

        $this->json(false, 'Metode request tidak diizinkan.', null, 405);
        $this->output->_display();
        exit;
    }

    private function model_json($result, $message)
    {
        return $this->json(
            !empty($result['success']),
            !empty($result['success']) ? $message : $result['message'],
            $result['data'] ?? null,
            $result['status'] ?? 200
        );
    }

    private function require_login()
    {
        $session = $this->session->userdata('admin');
        if (is_array($session) && !empty($session['id_user'])) {
            return;
        }

        if ($this->router->fetch_method() === 'index') {
            redirect('/');
            exit;
        }

        $this->json(false, 'Sesi login tidak ditemukan.', null, 401);
        $this->output->_display();
        exit;
    }

    private function json(
        $success,
        $message,
        $data = null,
        $status = 200,
        $errors = [],
        $meta = []
    ) {
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' => (bool) $success,
                'message' => (string) $message,
                'data' => $data,
                'errors' => $errors,
                'meta' => $meta,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
