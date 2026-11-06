<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cicilan extends CI_Controller
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
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path','admin/transaksi/cicilan')->count_all_results('conf_list_menu')) {
            show_error('Menu tidak diizinkan untuk level pengguna ini.', 403);
        }

        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/transaksi/M_cicilan', 'model');
    }

    public function index()
    {
        $data['title'] = 'Riwayat Cicilan';
        $data['periode'] = $this->model->periode_list();
        $data['periode_aktif'] = $this->model->periode_aktif();

        $idPeriodeAktif = isset($data['periode_aktif']['id']) ? (int) $data['periode_aktif']['id'] : 0;
        $data['kelas'] = $this->model->kelas_list($idPeriodeAktif);

        $this->load->view('template/header', $data);
        $this->load->view('admin/transaksi/cicilan', $data);
        $this->load->view('template/footer');
    }

    public function kelas_result()
    {
        $data = $this->model->kelas_list();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT))
            ->_display();
        exit;
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

    public function detail()
    {
        $data = $this->model->detail();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }
}
