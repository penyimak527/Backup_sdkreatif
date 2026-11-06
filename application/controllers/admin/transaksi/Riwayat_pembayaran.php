<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Riwayat_pembayaran extends CI_Controller
{
    const MENU_ID = 90;
    public function __construct()
    {
        parent::__construct();
        $admin = $this->session->userdata('admin');
        if (!is_array($admin) || empty($admin['username'])) redirect('/');
        $multi_level_session = $admin;
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
            ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $level = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('id_menu', self::MENU_ID)->count_all_results('conf_list_menu')) show_error('Menu Riwayat Pembayaran tidak diizinkan.', 403);
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/transaksi/M_riwayat_pembayaran', 'model');
        $this->load->model('admin/transaksi/M_pembayaran', 'payment_model');
    }
    public function index()
    {
        $data = array('title' => 'Riwayat Pembayaran', 'metode' => $this->model->metode_list(), 'can_cancel' => true, 'history_base' => site_url('admin/transaksi/riwayat_pembayaran/'));
        $this->load->view('template/header', $data);
        $this->load->view('admin/transaksi/riwayat_pembayaran', $data);
        $this->load->view('template/footer');
    }
    public function result()
    {
        $this->json($this->model->result());
    }
    public function detail()
    {
        $this->json($this->model->detail((int)$this->input->post('id')));
    }
    public function batalkan()
    {
        $this->json($this->model->batalkan());
    }
    public function preview_whatsapp()
    {
        $this->json($this->payment_model->preview_whatsapp());
    }
    public function siapkan_whatsapp()
    {
        $this->json($this->payment_model->siapkan_whatsapp());
    }
    private function json($data)
    {
        $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))->_display();
        exit;
    }
}
