<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengumuman extends CI_Controller
{
    const MENU_ID = 79;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_pengumuman', 'model');
        $this->require_login();
    }

    public function index()
    {
        $data = [
            'title' => 'Pengumuman Dompet',
            'csrf_token' => '',
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/pengumuman', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->post_only();

        $result = $this->model->result([
            'search' => trim((string) $this->input->post('search', true)),
            'status' => strtoupper(trim((string) $this->input->post('status', true))),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ]);

        return $this->json(
            true,
            'Pengumuman berhasil dimuat.',
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
            $result ? 'Detail berhasil dimuat.' : 'Pengumuman tidak ditemukan.',
            $result,
            $result ? 200 : 404
        );
    }

    public function simpan()
    {
        $this->post_only();

        $id = (int) $this->input->post('id');
        $payload = [
            'judul' => trim((string) $this->input->post('judul', true)),
            'isi' => trim((string) $this->input->post('isi', true)),
            'target_pengguna' => strtoupper(trim((string) $this->input->post('target_pengguna', true))),
            'tanggal_mulai' => $this->date('tanggal_mulai'),
            'tanggal_selesai' => $this->date('tanggal_selesai'),
        ];

        if (
            mb_strlen($payload['judul']) < 3
            || mb_strlen($payload['isi']) < 5
            || !in_array($payload['target_pengguna'], ['SEMUA', 'WALI', 'SISWA'], true)
        ) {
            return $this->json(false, 'Data pengumuman belum valid.', null, 422);
        }

        $result = $this->model->save($id, $payload, $this->current_user());

        return $this->model_json($result, 'Pengumuman berhasil disimpan.');
    }

    public function status()
    {
        $this->post_only();

        $status = strtoupper((string) $this->input->post('status'));
        if (!in_array($status, ['AKTIF', 'NONAKTIF'], true)) {
            return $this->json(false, 'Status tidak valid.', null, 422);
        }

        $result = $this->model->status(
            (int) $this->input->post('id'),
            $status
        );

        return $this->model_json($result, 'Status pengumuman berhasil diperbarui.');
    }

    private function date($field)
    {
        $value = trim((string) $this->input->post($field, true));

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
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
