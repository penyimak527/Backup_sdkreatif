<?php
 defined('BASEPATH') OR exit('No direct script access allowed');

class Metode_pembayaran extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/tagihan/M_metode_pembayaran', 'model');
        $this->guard();
    }

    public function index()
    {
        $data = array('title'=>'Metode Pembayaran');
        $this->load->view('template/header', $data);
        $this->load->view('admin/tagihan/metode_pembayaran', $data);
        $this->load->view('template/footer');
    }

    public function data()
    {
        $this->post_only();
        $this->respond(array('result'=>'true','data'=>$this->model->listing()));
    }

    public function simpan()
    {
        $this->post_only();
        $this->respond($this->model->save($this->input->post(NULL, TRUE), $this->actor()));
    }

    public function status()
    {
        $this->post_only();
        $this->respond($this->model->toggle_status((int)$this->input->post('id')));
    }

    private function guard()
    {
        $u = $this->session->userdata('admin');
        if (!is_array($u) || empty($u['username'])) { redirect('/'); exit; }
        $multi_level_session = $u;
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
                ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $level = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path','admin/tagihan/metode_pembayaran')->count_all_results('conf_list_menu')) {
            show_error('Menu Metode Pembayaran tidak diizinkan untuk level pengguna ini.', 403);
        }
    }

    private function respond($data)
    {
        $this->output->set_content_type('application/json','utf-8')->set_output(json_encode($data,JSON_UNESCAPED_UNICODE));
    }
    private function post_only(){ if ($this->input->method(TRUE)!=='POST') show_error('Method tidak diizinkan',405); }
    private function actor(){ $u=$this->session->userdata('admin'); return (int)($u['id_user'] ?? $u['id'] ?? 0); }
}
