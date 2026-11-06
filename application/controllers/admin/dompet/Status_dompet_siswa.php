<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Status_dompet_siswa extends CI_Controller
{
    const MENU_ID = 73;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_status_dompet_siswa', 'model');
        $this->load->model('admin/dompet/M_saldo_siswa', 'saldo_model');
        $this->load->model('admin/dompet/M_transaksi_dompet', 'transaction_model');
        $this->require_login();
    }

    public function index()
    {
        $classes = $this->db
            ->select('ks.id,ks.nama_kelas,ks.periode')
            ->from('kelas_setting ks')
            ->join(
                'master_tahun_ajaran ta',
                "ta.id=ks.id_periode AND ta.status='Aktif'",
                'inner'
            )
            ->order_by('ks.nama_kelas')
            ->get()
            ->result_array();

        $data = [
            'title' => 'Status & Saldo Siswa',
            'csrf_token' => '',
            'can_manage' => 1,
            'kelas' => $classes,
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/status_dompet_siswa', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->post_only();

        $result = $this->model->result([
            'search' => trim((string) $this->input->post('search', true)),
            'status_wallet' => strtoupper(
                trim((string) $this->input->post('status_wallet', true))
            ),
            'id_kelas_setting' => (int) $this->input->post('id_kelas_setting'),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ]);

        return $this->json(
            true,
            'Status dompet berhasil dimuat.',
            $result['rows'],
            200,
            [],
            $result['meta']
        );
    }

    public function aktifkan()
    {
        $this->post_only();

        $result = $this->transaction_model->aktifkan_dompet(
            (int) $this->input->post('id_siswa'),
            $this->current_user()
        );

        return $this->model_response($result, 'Dompet berhasil diaktifkan.');
    }

    public function histori()
    {
        $this->post_only();

        $history = $this->saldo_model->history(
            (int) $this->input->post('id_siswa')
        );

        return $this->json(true, 'Histori saldo berhasil dimuat.', $history);
    }

    public function atur_limit()
    {
        $this->post_only();

        $daily_limit = $this->money('batas_harian');
        $transaction_limit = $this->money('batas_transaksi');

        if ($daily_limit > 0 && $transaction_limit > $daily_limit) {
            return $this->json(
                false,
                'Batas per transaksi tidak boleh melebihi batas harian.',
                null,
                422
            );
        }

        $result = $this->saldo_model->set_limit(
            (int) $this->input->post('id_siswa'),
            $daily_limit,
            $transaction_limit,
            $this->current_user()
        );

        return $this->model_response($result, 'Limit siswa berhasil diperbarui.');
    }

    public function status()
    {
        $this->post_only();

        $status = strtoupper(trim((string) $this->input->post('status', true)));
        $reason = trim((string) $this->input->post('alasan', true));
        $valid_status = in_array($status, ['AKTIF', 'NONAKTIF'], true);
        $valid_reason = $status !== 'NONAKTIF' || mb_strlen($reason) >= 5;

        if (!$valid_status || !$valid_reason) {
            return $this->json(false, 'Status atau alasan belum valid.', null, 422);
        }

        $result = $this->saldo_model->set_status(
            (int) $this->input->post('id_siswa'),
            $status,
            $reason,
            $this->current_user()
        );

        return $this->model_response(
            $result,
            'Status layanan berhasil diperbarui.'
        );
    }

    private function money($field)
    {
        return max(
            0,
            (int) preg_replace(
                '/[^0-9]/',
                '',
                (string) $this->input->post($field, true)
            )
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

    private function model_response($result, $message)
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
            ->set_status_header($status)
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
