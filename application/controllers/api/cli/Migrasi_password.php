<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migrasi_password extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');

        if (!is_cli()) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(array(
                    'status' => false,
                    'message' => 'Endpoint tidak ditemukan.',
                ), JSON_PRETTY_PRINT))
                ->_display();
            exit;
        }

        $this->load->model('api/model/M_migrasi_password', 'model');
    }

    public function audit()
    {
        $this->tampilkan($this->model->audit());
    }

    public function jalankan()
    {
        $this->tampilkan($this->model->jalankan());
    }

    private function tampilkan($response)
    {
        echo json_encode($response, JSON_PRETTY_PRINT), PHP_EOL;
    }
}
