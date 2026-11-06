<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dompet extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('api/dompet/M_dompet', 'model');
    }

    public function detail($id_siswa)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->detail($id_siswa)
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function penggunaan_harian($id_siswa)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->penggunaan_harian($id_siswa)
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function ubah_limit($id_siswa)
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->ubah_limit($id_siswa)
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function histori_limit($id_siswa)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->histori_limit($id_siswa)
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function riwayat_transaksi($id_siswa)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->riwayat_transaksi($id_siswa)
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function detail_transaksi($id_siswa, $jenis_transaksi, $no_transaksi)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->detail_transaksi($id_siswa, $jenis_transaksi, $no_transaksi)
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
