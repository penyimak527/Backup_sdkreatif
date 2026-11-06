<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Integrasi_pemasukan extends CI_Controller
{
    const MENU_ID = 100;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/spp/M_pengaturan_integrasi', 'model');
        $this->guard();
    }
    public function index()
    {
        $data = ['title' => 'Pengaturan Integrasi Pemasukan'];
        $this->load->view('template/header', $data);
        $this->load->view('admin/spp/integrasi_pemasukan', $data);
        $this->load->view('template/footer');
    }
    public function opsi()
    {
        $this->post_only();
        $this->respond($this->model->options());
    }
    public function data()
    {
        $this->post_only();
        $this->respond(['result' => 'true', 'data' => $this->model->listing()]);
    }
    public function simpan()
    {
        $this->post_only();
        $this->respond($this->model->save((int)$this->input->post('id_jenis_tagihan'), (string)$this->input->post('integrasi_pemasukan'), (int)$this->input->post('id_kode_akun'), (int)$this->input->post('id_kasbank'), $this->actor()));
    }
    private function guard()
    {
        $u = $this->session->userdata('admin');
        if (!is_array($u) || empty($u['username'])) {
            redirect('/');
            exit;
        }
        $multi_level_session = $u;
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
            ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $idLevel = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (empty($idLevel) || !$this->db->where('id_menu', self::MENU_ID)->where_in('id_level', $idLevel)->count_all_results('conf_list_menu')) show_error('Menu tidak diizinkan untuk level pengguna ini.', 403);
    }
    private function respond($data)
    {
        $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    private function post_only()
    {
        if ($this->input->method(TRUE) !== 'POST') show_error('Method tidak diizinkan', 405);
    }
    private function actor()
    {
        $u = $this->session->userdata('admin');
        return (int)($u['id_user'] ?? 0);
    }
}
