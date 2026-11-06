<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tagihan extends CI_Controller
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wali/M_dompet_wali', 'wali_model');
        $this->load->model('wali/tagihan/M_tagihan', 'model');
        $this->user = $this->autentikasi_wali($this->input->is_ajax_request());
    }

    public function index()
    {
        $anak_list = $this->wali_model->daftar_anak((int) $this->user['id_wali_siswa']);

        $data = array(
            'title' => 'Tagihan Anak',
            'anak_list' => $anak_list,
            'anak_default' => !empty($anak_list) ? $anak_list[0] : null,
        );

        $this->halaman('wali/tagihan/tagihan', $data);
    }

    public function result()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        if (!$this->anak_wali($id_siswa)) {
            return $this->json(array('result' => 'false', 'message' => 'Data anak tidak ditemukan.'), 404);
        }

        $filter = array(
            'search' => trim((string) $this->input->post('search', true)),
            'id_periode' => (int) $this->input->post('id_periode'),
            'tipe_tagihan' => trim((string) $this->input->post('tipe_tagihan', true)),
            'status_pembayaran' => trim((string) $this->input->post('status_pembayaran', true)),
            'halaman' => max(1, (int) $this->input->post('halaman')),
            'per_halaman' => $this->per_halaman(),
            'client_paging' => $this->input->post('client_paging') === '1',
        );

        return $this->json(array(
            'result' => 'true',
            'data' => $this->model->result($id_siswa, $filter),
            'periode_list' => $this->model->periode_list($id_siswa),
        ));
    }

    public function detail()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        $id = (int) $this->input->post('id');

        if (!$this->anak_wali($id_siswa)) {
            return $this->json(array('result' => 'false', 'message' => 'Data anak tidak ditemukan.'), 404);
        }

        $row = $this->model->detail($id_siswa, $id);
        if (!$row) {
            return $this->json(array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.'), 404);
        }

        return $this->json(array('result' => 'true', 'data' => $row));
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
