<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Isi_saldo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('api/dompet/M_isi_saldo', 'model');
    }

    public function pengaturan()
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->pengaturan()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function buat($id_siswa)
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->buat($id_siswa)
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function index()
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->daftar()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function detail($no_pengajuan)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->detail($no_pengajuan)
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function batalkan($no_pengajuan)
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->batalkan($no_pengajuan)
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
