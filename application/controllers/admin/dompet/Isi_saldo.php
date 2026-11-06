<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Isi_saldo extends CI_Controller
{
    const MENU_ID = 76;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_isi_saldo', 'model');
        $this->load->model('admin/dompet/M_transaksi_dompet', 'transaction_model');
        $this->require_login();
    }

    public function index()
    {
        $user = $this->current_user();
        $setting = $this->db
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('pengaturan_dompet')
            ->row_array();

        $initial_status = strtoupper(trim((string) $this->input->get('status', true)));
        $allowed_statuses = [
            '',
            'MENUNGGU_VERIFIKASI',
            'DIVERIFIKASI',
            'DISETUJUI',
            'DITOLAK',
            'DIBATALKAN_WALI',
            'DIBATALKAN_ADMIN',
        ];

        if (!in_array($initial_status, $allowed_statuses, true)) {
            $initial_status = '';
        }

        $data = [
            'title' => 'Isi Saldo',
            'csrf_token' => '',
            'can_direct_topup' => 1,
            'setting' => $setting ?: [],
            'initial_status' => $initial_status,
            'transfer_accounts' => $this->model->active_transfer_accounts(),
            'cash_accounts' => $this->model->active_cash_accounts(),
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/isi_saldo', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->post_only();

        $result = $this->model->result([
            'jenis' => strtoupper((string) $this->input->post('jenis')) === 'RIWAYAT'
                ? 'RIWAYAT'
                : 'PENGAJUAN',
            'search' => trim((string) $this->input->post('search', true)),
            'status' => strtoupper(trim((string) $this->input->post('status', true))),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ]);

        return $this->json(
            true,
            'Data isi saldo berhasil dimuat.',
            $result['rows'],
            200,
            [],
            array_merge($result['meta'], ['jenis' => $result['jenis']])
        );
    }

    public function detail()
    {
        $this->post_only();

        $result = $this->model->detail(
            (int) $this->input->post('id'),
            strtoupper((string) $this->input->post('jenis'))
        );

        return $this->json(
            (bool) $result,
            $result ? 'Detail berhasil dimuat.' : 'Data tidak ditemukan.',
            $result,
            $result ? 200 : 404
        );
    }

    public function verifikasi()
    {
        $this->post_only();

        $result = $this->transaction_model->verifikasi_topup(
            (int) $this->input->post('id'),
            $this->payment_input(),
            $this->current_user()
        );

        return $this->model_response(
            $result,
            'Pengajuan berhasil diverifikasi dan diposting atomik.'
        );
    }

    public function tolak()
    {
        $this->post_only();

        $reason = trim((string) $this->input->post('alasan', true));
        if (mb_strlen($reason) < 5) {
            return $this->json(false, 'Alasan penolakan minimal 5 karakter.', null, 422);
        }

        $result = $this->model->update_pending_status(
            (int) $this->input->post('id'),
            'DITOLAK',
            $reason,
            $this->current_user()
        );

        return $this->model_response(
            $result,
            'Pengajuan berhasil ditolak tanpa mutasi saldo.'
        );
    }

    public function batal()
    {
        $this->post_only();

        $reason = trim((string) $this->input->post('alasan', true));
        if (mb_strlen($reason) < 5) {
            return $this->json(false, 'Alasan pembatalan minimal 5 karakter.', null, 422);
        }

        $result = $this->model->update_pending_status(
            (int) $this->input->post('id'),
            'DIBATALKAN_ADMIN',
            $reason,
            $this->current_user()
        );

        return $this->model_response(
            $result,
            'Pengajuan berhasil dibatalkan tanpa mutasi saldo.'
        );
    }

    public function reversal()
    {
        $this->post_only();

        $reason = trim((string) $this->input->post('alasan', true));
        if (mb_strlen($reason) < 5) {
            return $this->json(false, 'Alasan reversal minimal 5 karakter.', null, 422);
        }

        $result = $this->transaction_model->reversal_topup(
            (int) $this->input->post('id'),
            $reason,
            $this->current_user()
        );

        return $this->model_response($result, 'Reversal berhasil diproses atomik.');
    }

    public function cari_siswa()
    {
        $this->post_only();

        $students = $this->model->active_wallet_students(
            trim((string) $this->input->post('search', true))
        );

        return $this->json(true, 'Siswa aktif berhasil dimuat.', $students);
    }

    public function tambah_langsung()
    {
        $this->post_only();

        $nominal = (int) preg_replace(
            '/\D+/',
            '',
            (string) $this->input->post('nominal')
        );
        $request_key = trim((string) $this->input->post('request_key', true));

        $result = $this->transaction_model->topup_langsung_admin(
            (int) $this->input->post('id_siswa'),
            $nominal,
            $request_key,
            $this->payment_input(),
            $this->current_user()
        );

        return $this->model_response(
            $result,
            'Isi saldo langsung berhasil diposting tanpa verifikasi.'
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

    private function payment_input()
    {
        return [
            'metode_pembayaran' => strtoupper(trim((string) $this->input->post('metode_pembayaran', true))),
            'id_rekening_dompet' => (int) $this->input->post('id_rekening_dompet'),
            'id_kasbank' => (int) $this->input->post('id_kasbank'),
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
