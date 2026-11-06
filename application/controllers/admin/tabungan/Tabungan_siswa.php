<?php
 defined('BASEPATH') or exit('No direct script access allowed');

class Tabungan_siswa extends CI_Controller
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
        if (empty($level) || !$this->db
            ->where_in('id_level', $level)
            ->where('path', 'admin/tabungan/tabungan_siswa')
            ->count_all_results('conf_list_menu')) {
            show_error('Menu Tabungan Siswa tidak diizinkan.', 403);
        }

        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/tabungan/M_tabungan_siswa', 'model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Tabungan Siswa',
            'kasbank' => $this->model->kasbank(),
            'kelas' => $this->model->kelas_list(),
            'metode' => $this->model->metode_list()
        );

        $this->load->view('template/header', $data);
        $this->load->view('admin/tabungan/tabungan_siswa', $data);
        $this->load->view('template/footer');
    }

    public function cari_siswa()
    {
        $q = trim((string) $this->input->post('q', TRUE));
        $page = max(1, (int) $this->input->post('page'));
        $limit = (int) $this->input->post('limit');

        if (!in_array($limit, array(10, 25, 50), true)) {
            $limit = 10;
        }

        $result = $this->model->cari($q, $page, $limit);

        $data = array(
            'result' => 'true',
            'data' => $result['data'],
            'meta' => $result['meta']
        );

        echo json_encode($data);
    }

    public function detail()
    {
        $data = $this->model->detail((int) $this->input->post('id_siswa'));

        echo json_encode($data);
    }

    public function detail_transaksi()
    {
        $jenis = (string) $this->input->post('jenis', TRUE);
        $id = (int) $this->input->post('id');

        $data = $this->model->detail_transaksi($jenis, $id);

        echo json_encode($data);
    }

    public function simpan()
    {
        $data = $this->model->simpan(
            $this->input->post(NULL, TRUE),
            $this->session->userdata('admin')
        );

        echo json_encode($data);
    }

    public function edit()
    {
        $data = $this->model->edit(
            $this->input->post(NULL, TRUE),
            $this->session->userdata('admin')
        );

        echo json_encode($data);
    }

    public function batalkan()
    {
        $data = $this->model->batalkan(
            $this->input->post(NULL, TRUE),
            $this->session->userdata('admin')
        );

        echo json_encode($data);
    }

    public function whatsapp_preview()
    {
        $jenis = (string) $this->input->post('jenis', TRUE);
        $id = (int) $this->input->post('id');

        $data = $this->model->whatsapp_preview($jenis, $id);

        echo json_encode($data);
    }

    public function siapkan_whatsapp()
    {
        $data = $this->model->siapkan_whatsapp(
            $this->input->post(NULL, FALSE),
            $this->session->userdata('admin')
        );

        echo json_encode($data);
    }

    // Alias untuk kompatibilitas tombol/JS versi sebelumnya.
    public function whatsapp()
    {
        $jenis = (string) ($this->input->post('jenis', TRUE) ?: 'Setoran');
        $id = (int) $this->input->post('id');

        $data = $this->model->whatsapp_preview($jenis, $id);

        echo json_encode($data);
    }

    public function bukti($jenis = '', $id = 0)
    {
        $row = $this->model->transaksi($jenis, (int) $id);

        if (!$row) {
            show_404();
        }

        $this->load->view('admin/tabungan/bukti_tabungan', array('row' => $row));
    }


}
