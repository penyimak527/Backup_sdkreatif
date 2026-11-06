<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tabungan extends CI_Controller
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wali/M_dompet_wali', 'wali_model');
        $this->load->model('wali/tabungan/M_tabungan', 'model');
        $this->user = $this->autentikasi_wali($this->input->is_ajax_request());
    }

    public function index()
    {
        $anak_list = $this->wali_model->daftar_anak((int) $this->user['id_wali_siswa']);

        $data = array(
            'title' => 'Tabungan Anak',
            'anak_list' => $anak_list,
            'anak_default' => !empty($anak_list) ? $anak_list[0] : null,
        );

        $this->halaman('wali/tabungan/tabungan', $data);
    }

    public function result()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        if (!$this->anak_wali($id_siswa)) {
            return $this->json(array('result' => 'false', 'message' => 'Data anak tidak ditemukan.'), 404);
        }

        $filter = array(
            'jenis' => trim((string) $this->input->post('jenis', true)),
            'status' => trim((string) $this->input->post('status', true)),
            'tanggal_awal' => trim((string) $this->input->post('tanggal_awal', true)),
            'tanggal_akhir' => trim((string) $this->input->post('tanggal_akhir', true)),
            'halaman' => max(1, (int) $this->input->post('halaman')),
            'per_halaman' => $this->per_halaman(),
            'client_paging' => $this->input->post('client_paging') === '1',
        );

        return $this->json(array(
            'result' => 'true',
            'ringkasan' => $this->model->ringkasan($id_siswa, $filter),
            'data' => $this->model->result($id_siswa, $filter),
        ));
    }

    public function detail()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        $jenis = trim((string) $this->input->post('jenis', true));
        $id = (int) $this->input->post('id');

        if (!$this->anak_wali($id_siswa)) {
            return $this->json(array('result' => 'false', 'message' => 'Data anak tidak ditemukan.'), 404);
        }

        $data = $this->model->detail($id_siswa, $jenis, $id);
        if (!$data) {
            return $this->json(array('result' => 'false', 'message' => 'Transaksi tabungan tidak ditemukan.'), 404);
        }

        return $this->json(array('result' => 'true', 'data' => $data));
    }

    private function anak_wali($id_siswa)
    {
        return $id_siswa > 0
            ? $this->wali_model->anak((int) $this->user['id_wali_siswa'], $id_siswa)
            : null;
    }

    private function per_halaman()
    {
        $per_halaman = (int) $this->input->post('per_halaman');
        return in_array($per_halaman, array(10, 25, 50, 100), true) ? $per_halaman : 10;
    }

    private function autentikasi_wali($ajax = false)
    {
        $session = $this->session->userdata('wali');
        $user = is_array($session) && !empty($session['id_user'])
            ? $this->wali_model->user_wali((int) $session['id_user'])
            : null;

        if ($user) return $user;

        if ($ajax) {
            $this->json(array('result' => 'false', 'message' => 'Sesi Wali Murid sudah berakhir.'), 401);
            $this->output->_display();
            exit;
        }

        redirect('/');
        exit;
    }

    private function halaman($view, $data)
    {
        $this->load->view('template/header', $data);
        $this->load->view($view, $data);
        $this->load->view('template/footer');
    }

    private function json($data, $status = 200)
    {
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
