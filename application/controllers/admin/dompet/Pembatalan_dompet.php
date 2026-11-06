<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pembatalan_dompet extends CI_Controller
{
    const MENU_ID = 80;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_pembatalan_dompet', 'model');
        $this->load->model('admin/dompet/M_transaksi_dompet', 'transaction_model');
        $this->require_login();
    }

    public function index()
    {
        $options = $this->model->filter_options();
        $data = [
            'title' => 'Riwayat Pembatalan',
            'jenis_options' => $options['jenis'],
            'status_options' => $options['status'],
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/pembatalan_dompet', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->post_only();

        $result = $this->model->result([
            'search' => trim((string) $this->input->post('search', true)),
            'status' => strtoupper(trim((string) $this->input->post('status', true))),
            'jenis' => strtoupper(trim((string) $this->input->post('jenis', true))),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ]);

        return $this->json(
            true,
            'Riwayat pembatalan berhasil dimuat.',
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
            $result ? 'Detail berhasil dimuat.' : 'Data tidak ditemukan.',
            $result,
            $result ? 200 : 404
        );
    }

    public function proses()
    {
        $this->post_only();

        $alasan = trim((string) $this->input->post('alasan', true));
        if (mb_strlen($alasan) < 5) {
            return $this->json(
                false,
                'Alasan reversal minimal 5 karakter.',
                null,
                422
            );
        }

        $result = $this->transaction_model->reversal_topup(
            (int) $this->input->post('id_riwayat'),
            $alasan,
            $this->current_user()
        );

        return $this->json(
            !empty($result['success']),
            !empty($result['success'])
                ? 'Reversal berhasil diproses.'
                : $result['message'],
            $result['data'] ?? null,
            $result['status'] ?? 200
        );
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
