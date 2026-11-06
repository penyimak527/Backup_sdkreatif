<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Surat_tunggakan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $admin = $this->session->userdata('admin');
        if (!is_array($admin) || empty($admin['username'])) {
            redirect('/');
            exit;
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
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path', 'admin/tunggakan/surat_tunggakan')->count_all_results('conf_list_menu')) {
            show_error('Menu tidak diizinkan untuk level pengguna ini.', 403);
        }
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/tunggakan/M_surat_tunggakan', 'model');
        $this->load->model('admin/spp/M_pengaturan_cetak', 'format_model');
    }
    public function index()
    {
        $data = array('title' => 'Surat Pemberitahuan Tunggakan', 'periode' => $this->model->periode_list());
        $this->load->view('template/header', $data);
        $this->load->view('admin/tunggakan/surat_tunggakan', $data);
        $this->load->view('template/footer');
    }
    public function cari_siswa()
    {
        $this->json_response($this->model->cari_siswa());
    }
    public function siswa($id = 0)
    {
        $this->json_response($this->model->siswa_by_id((int)$id));
    }
    public function tagihan()
    {
        $this->json_response($this->model->tagihan());
    }
    public function simpan()
    {
        $this->json_response($this->model->simpan());
    }
    public function riwayat()
    {
        $this->json_response($this->model->riwayat());
    }
    public function detail()
    {
        $this->json_response($this->model->detail((int)$this->input->post('id')));
    }
    public function cetak($id = 0)
    {
        $d = $this->model->detail((int)$id);
        if ($d['result'] !== 'true') show_404();
        $d['format'] = $this->format_model->default_format('Surat Tunggakan');
        if (!empty($d['format']['pengaturan_json'])) {
            $tmp = json_decode($d['format']['pengaturan_json'], true);
            $d['format_config'] = is_array($tmp) ? $tmp : array();
        } else {
            $d['format_config'] = array();
        }
        $this->db->where('id', (int)$id)->update('tagihan_surat_tunggakan', array('status_cetak' => 'Sudah'));
        $this->load->view('admin/tunggakan/cetak_surat_tunggakan', $d);
    }
    public function siapkan_whatsapp()
    {
        $this->json_response($this->model->siapkan_whatsapp());
    }

    private function json_response($data, $status = 200)
    {
        $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }
}
