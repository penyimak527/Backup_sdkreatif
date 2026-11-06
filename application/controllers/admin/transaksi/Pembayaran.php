<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pembayaran extends CI_Controller
{
    const MENU_ID = 89;
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
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('id_menu', self::MENU_ID)->count_all_results('conf_list_menu')) show_error('Menu Pembayaran Tagihan tidak diizinkan.', 403);
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/transaksi/M_pembayaran', 'model');
    }

    public function index()
    {
        $token = bin2hex(random_bytes(16));
        $this->session->set_userdata('token_pembayaran_aktif', $token);
        $data = array('title' => 'Pembayaran Tagihan', 'metode' => $this->model->metode_list(), 'token_pembayaran' => $token);
        $this->load->view('template/header', $data);
        $this->load->view('admin/transaksi/pembayaran', $data);
        $this->load->view('template/footer');
    }

    public function cari_siswa()
    {
        $page = max(1, (int)$this->input->post('page'));
        $limit = (int)$this->input->post('limit');
        $this->json_response($this->model->cari_siswa($page, $limit));
    }
    public function siswa($id = 0)
    {
        $id = $id ?: (int)$this->input->post('id');
        $this->json_response($this->model->siswa_by_id((int)$id));
    }
    public function tagihan_siswa()
    {
        $this->json_response($this->model->tagihan_siswa());
    }
    public function simpan()
    {
        $this->json_response($this->model->simpan());
    }
    public function detail()
    {
        $this->json_response($this->model->detail((int)$this->input->post('id')));
    }

    public function bukti($id = 0)
    {
        $data = $this->model->detail((int)$id);
        if (empty($data['result']) || $data['result'] !== 'true') show_404();
        $this->load->model('admin/spp/M_pengaturan_cetak', 'format_cetak');
        $cfg = $this->format_cetak->default_format('Bukti Pembayaran') ?: array();
        $opt = array();
        if (!empty($cfg['pengaturan_json'])) {
            $tmp = json_decode($cfg['pengaturan_json'], true);
            if (is_array($tmp)) $opt = $tmp;
        }
        $data['format'] = array_merge($cfg, $opt, array(
            'judul_bukti' => $cfg['judul'] ?? 'BUKTI PEMBAYARAN',
            // 'nama_sekolah' => $this->config->item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah',
            'header_cetak' => $cfg['header_text'] ?? '',
            'footer_cetak' => $cfg['footer_text'] ?? ''
        ));
        $this->load->view('admin/transaksi/bukti_pembayaran', $data);
    }

    public function preview_whatsapp()
    {
        $this->json_response($this->model->preview_whatsapp());
    }
    public function siapkan_whatsapp()
    {
        $this->json_response($this->model->siapkan_whatsapp());
    }

    public function cetak_kartu($id = 0)
    {
        $data = $this->model->detail((int)$id);
        if (($data['result'] ?? '') !== 'true') show_404();
        $data['format_list'] = $this->model->format_kartu_list();
        $data['format'] = $this->model->format_kartu();
        $this->load->view('admin/transaksi/cetak_kartu', $data);
    }
    public function cek_baris_kartu()
    {
        $this->json_response($this->model->cek_baris_kartu());
    }
    public function catat_cetak_kartu()
    {
        $this->json_response($this->model->catat_cetak_kartu());
    }

    private function json_response($data, $status = 200)
    {
        $this->output->set_status_header((int)$status)->set_content_type('application/json', 'utf-8')->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))->_display();
        exit;
    }
}
