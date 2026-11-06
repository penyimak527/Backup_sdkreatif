<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('api/dompet/M_auth', 'model');
    }

    public function login()
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->login()
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function sesi()
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->sesi()
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function login_internal()
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->login_internal()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function sesi_internal()
    {
        $response = $this->input->method(true) === 'GET'
            ? $this->model->sesi_internal()
            : $this->model->metode_tidak_diizinkan();
        $this->kirim($response);
    }

    public function logout()
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->logout()
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function logout_internal()
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->logout_internal()
            : $this->model->metode_tidak_diizinkan();

        $this->kirim($response);
    }

    public function ubah_password()
    {
        $response = $this->input->method(true) === 'POST'
            ? $this->model->ubah_password()
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
