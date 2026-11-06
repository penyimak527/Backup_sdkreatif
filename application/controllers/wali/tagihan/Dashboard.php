<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wali/M_dompet_wali', 'wali_model');
        $this->load->model('wali/tagihan/M_dashboard', 'model');
        $this->user = $this->autentikasi_wali($this->input->is_ajax_request());
    }

    public function index()
    {
        $anak_list = $this->wali_model->daftar_anak((int) $this->user['id_wali_siswa']);

        $data = array(
            'title' => 'Dashboard Tagihan & Tabungan',
            'anak_list' => $anak_list,
            'anak_default' => !empty($anak_list) ? $anak_list[0] : null,
        );

        $this->halaman('wali/tagihan/dashboard', $data);
    }

    public function result()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        $anak = $this->wali_model->anak((int) $this->user['id_wali_siswa'], $id_siswa);

        if (!$anak) {
            return $this->json(array(
                'result' => 'false',
                'message' => 'Data anak tidak ditemukan atau tidak terhubung dengan akun Wali.',
            ), 404);
        }

        return $this->json(array(
            'result' => 'true',
            'anak' => $anak,
            'data' => $this->model->dashboard($id_siswa),
        ));
    }

    private function autentikasi_wali($ajax = false)
    {
        $session = $this->session->userdata('wali');
        $user = is_array($session) && !empty($session['id_user'])
            ? $this->wali_model->user_wali((int) $session['id_user'])
            : null;

        if ($user) {
            return $user;
        }

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
