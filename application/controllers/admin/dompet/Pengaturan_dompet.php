<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pengaturan_dompet extends CI_Controller
{
    const MENU_ID = 81;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_pengaturan_dompet', 'model');
        $this->require_login();
    }

    public function index()
    {
        $setting = $this->model->get();
        $selected = array_values(array_filter(array_map('intval', explode(',', (string) ($setting['nominal_cepat'] ?? '')))));
        $options = array_values(array_unique(array_merge(
            [100, 500, 1000, 2000, 3000, 5000, 10000, 20000, 50000, 100000, 200000, 500000],
            $selected
        )));
        sort($options);
        $data = [
            'title' => 'Pengaturan Dompet',
            'csrf_token' => '',
            'setting' => $setting,
            'nominal_options' => $options,
            'nominal_selected' => $selected,
        ];
        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/pengaturan_dompet', $data);
        $this->load->view('template/footer');
    }

    public function simpan()
    {
        $this->post();
        $user = $this->current_user();

        $payload = [
            'minimal_isi_saldo' => $this->money('minimal_isi_saldo'),
            'maksimal_isi_saldo' => $this->money('maksimal_isi_saldo'),
            'batas_harian_default' => $this->money('batas_harian_default'),
            'batas_transaksi_default' => $this->money('batas_transaksi_default'),
            'nominal_cepat' => $this->quick_nominals(),
            'nomor_whatsapp' => trim((string) $this->input->post('nomor_whatsapp', true)),
            'batas_waktu_pembatalan_menit' => max(1, (int) $this->input->post('batas_waktu_pembatalan_menit')),
            'kebijakan_persetujuan_pembatalan' => strtoupper(trim((string) $this->input->post('kebijakan_persetujuan_pembatalan', true))),
            'status_layanan' => strtoupper(trim((string) $this->input->post('status_layanan', true))),
        ];

        if ($payload['maksimal_isi_saldo'] > 0 && $payload['minimal_isi_saldo'] > $payload['maksimal_isi_saldo']) {
            return $this->json(false, 'Minimal isi saldo tidak boleh melebihi maksimal.', null, 422);
        }
        if ($payload['batas_harian_default'] > 0 && $payload['batas_transaksi_default'] > $payload['batas_harian_default']) {
            return $this->json(false, 'Batas per transaksi tidak boleh melebihi batas harian.', null, 422);
        }
        if (!in_array($payload['status_layanan'], ['AKTIF', 'NONAKTIF'], true)) {
            return $this->json(false, 'Status layanan tidak valid.', null, 422);
        }
        if (!in_array($payload['kebijakan_persetujuan_pembatalan'], ['PERSETUJUAN', 'OTOMATIS'], true)) {
            return $this->json(false, 'Kebijakan persetujuan pembatalan tidak valid.', null, 422);
        }

        $result = $this->model->save($payload, $user);
        return $this->json(
            !empty($result['success']),
            !empty($result['success']) ? 'Pengaturan operasional berhasil disimpan.' : $result['message'],
            null,
            $result['status'] ?? 200
        );
    }

    private function money($field)
    {
        $value = preg_replace('/[^0-9]/', '', (string) $this->input->post($field, true));
        return max(0, (int) $value);
    }

    private function quick_nominals()
    {
        $values = $this->input->post('nominal_cepat', true);
        if (!is_array($values)) {
            $values = explode(',', (string) $values);
        }
        $result = [];
        foreach ($values as $value) {
            $number = (int) preg_replace('/[^0-9]/', '', (string) $value);
            if ($number > 0) {
                $result[$number] = $number;
            }
        }
        ksort($result);
        return implode(',', array_values($result));
    }

    private function post()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            $this->json(false, 'Metode request tidak diizinkan.', null, 405);
            $this->output->_display();
            exit;
        }
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
