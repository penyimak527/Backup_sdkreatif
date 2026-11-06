<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_kantin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('api/dompet/M_transaksi_kantin', 'model');
    }

    public function index()
    {
        $method = $this->input->method(true);
        if ($method === 'GET') {
            $response = $this->model->riwayat();
        } elseif ($method === 'POST') {
            $response = $this->model->proses();
        } else {
            $response = $this->model->metode_tidak_diizinkan();
        }
        $this->kirim($response);
    }

    public function permintaan($id_permintaan)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->berdasarkan_permintaan($id_permintaan)
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function detail($no_transaksi)
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->detail($no_transaksi)
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
