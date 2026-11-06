<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Format_kartu extends CI_Controller
{
    const MENU_ID = 0; // Resolved by path, not hardcoded IDs across installations.
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
        $this->load->model('admin/spp/M_format_kartu', 'model');
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path', 'admin/spp/format_kartu')->count_all_results('conf_list_menu'))
            show_error('Menu Format Kartu tidak diizinkan.', 403);
    }
    public function index()
    {
        $data = array('title' => 'Format Kartu Pembayaran');
        $this->load->view('template/header', $data);
        $this->load->view('admin/spp/format_kartu', $data);
        $this->load->view('template/footer');
    }
    public function result()
    {
        $this->json($this->model->listing());
    }
    public function detail()
    {
        $this->json($this->model->detail((int)$this->input->post('id')));
    }
    public function simpan()
    {
        $this->post_only();
        $this->json($this->model->save());
    }
    public function set_default()
    {
        $this->post_only();
        $this->json($this->model->set_default((int)$this->input->post('id')));
    }
    private function post_only()
    {
        if ($this->input->method(TRUE) !== 'POST') show_error('Method tidak diizinkan.', 405);
    }
    private function json($v)
    {
        $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode($v, JSON_UNESCAPED_UNICODE));
    }
}
