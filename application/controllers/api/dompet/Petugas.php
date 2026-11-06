<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Petugas extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('api/dompet/M_petugas', 'model');
    }

    public function dashboard()
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->dashboard()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function scan_qr()
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->scan_qr()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function cari_siswa()
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->cari_siswa()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    private function kirim($response)
    {
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }
}
