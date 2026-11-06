<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Jenis_tagihan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $admin = $this->session->userdata('admin');
        if (!is_array($admin) || empty($admin['username'])) {
            redirect('/');
        }

        $multi_level_session = $admin;
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
                ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $level = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path','admin/tagihan/jenis_tagihan')->count_all_results('conf_list_menu')) {
            show_error('Menu Pos Penerimaan tidak diizinkan.',403);
        }
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/tagihan/M_jenis_tagihan', 'model');
    }

    public function index()
    {
        $data['title'] = 'Pos Penerimaan';
        $this->load->view('template/header', $data);
        $this->load->view('admin/tagihan/jenis_tagihan', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $data = $this->model->result();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }

    public function tambah()
    {
        $data = $this->model->tambah();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }

    public function edit()
    {
        $data = $this->model->edit();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }

    public function status()
    {
        $data = $this->model->ubah_status();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }
}
