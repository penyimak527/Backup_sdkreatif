<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Tagihan_tahunan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');

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
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path','admin/tagihan/daftar_tagihan')->count_all_results('conf_list_menu')) {
            show_error('Menu tidak diizinkan untuk level pengguna ini.', 403);
        }

        $this->load->model('admin/tagihan/M_buat_tagihan', 'model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Buat Jenis Biaya Tahunan',
            'tipe' => 'Tahunan',
            'periode' => $this->model->periode_list(),
            'jenis' => $this->model->jenis_list('Tahunan'),
            'kelas' => $this->model->kelas_list()
        );

        $this->load->view('template/header', $data);
        $this->load->view('admin/tagihan/tagihan_tahunan', $data);
        $this->load->view('template/footer');
    }

    public function preview()
    {
        $data = $this->model->preview_tahunan();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }

    public function simpan()
    {
        $data = $this->model->simpan_tahunan();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }

    public function cari_siswa()
    {
        $data = $this->model->cari_siswa();

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }
}
?>
