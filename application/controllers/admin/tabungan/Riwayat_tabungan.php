<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Riwayat_tabungan extends CI_Controller
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
            ->where('path', 'admin/tabungan/riwayat_tabungan')
            ->count_all_results('conf_list_menu')) {
            show_error('Menu Riwayat Tabungan Siswa tidak diizinkan.', 403);
        }
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/tabungan/M_riwayat_tabungan', 'model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Riwayat Tabungan Siswa',
            'kelas' => $this->model->kelas_list()
        );
        $this->load->view('template/header', $data);
        $this->load->view('admin/tabungan/riwayat_tabungan', $data);
        $this->load->view('template/footer');
    }

    public function riwayat()
    {
        echo json_encode($this->model->riwayat($this->input->post(NULL, TRUE), true));
    }

    public function detail_transaksi()
    {
        echo json_encode($this->model->detail_transaksi(
            (string) $this->input->post('jenis', TRUE),
            (int) $this->input->post('id')
        ));
    }

    public function whatsapp_preview()
    {
        echo json_encode($this->model->whatsapp_preview(
            (string) $this->input->post('jenis', TRUE),
            (int) $this->input->post('id')
        ));
    }

    public function siapkan_whatsapp()
    {
        echo json_encode($this->model->siapkan_whatsapp(
            $this->input->post(NULL, FALSE),
            $this->session->userdata('admin')
        ));
    }

    public function bukti($jenis = '', $id = 0)
    {
        $row = $this->model->transaksi($jenis, (int) $id);
        if (!$row) {
            show_404();
        }
        $this->load->view('admin/tabungan/bukti_tabungan', array('row' => $row));
    }

    public function cetak_riwayat()
    {
        $filter = $this->input->get(NULL, TRUE);
        $filter = is_array($filter) ? $filter : array();
        $result = $this->model->riwayat($filter, false);
        $tanggalMulai = trim((string) ($filter['tanggal_mulai'] ?? ''));
        $tanggalSelesai = trim((string) ($filter['tanggal_selesai'] ?? ''));
        if ($tanggalMulai !== '' && $tanggalSelesai !== '') {
            $periode = $tanggalMulai . ' s.d. ' . $tanggalSelesai;
        } elseif ($tanggalMulai !== '') {
            $periode = 'Mulai ' . $tanggalMulai;
        } elseif ($tanggalSelesai !== '') {
            $periode = 'Sampai ' . $tanggalSelesai;
        } else {
            $periode = 'Semua Tanggal';
        }
        $admin = $this->session->userdata('admin');
        $data = array(
            'rows' => $result['data'],
            'summary' => $result['summary'],
            'filter' => $filter,
            'periode_cetak' => $periode,
            'tanggal_cetak' => date('d-m-Y H:i:s'),
            'petugas' => $admin['nama'] ?? ($admin['nama_user'] ?? ($admin['username'] ?? '-'))
        );
        $this->load->view('admin/tabungan/cetak_riwayat', $data);
    }

    public function export_riwayat()
    {
        echo json_encode($this->model->riwayat($this->input->get(NULL, TRUE), false));
    }
}
