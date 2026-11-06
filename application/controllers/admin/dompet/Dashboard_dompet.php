<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_dompet extends CI_Controller
{
    const MENU_ID = 72;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_dashboard_dompet', 'model');
        $this->require_login();
    }

    public function index()
    {
        $data = ['title' => 'Dashboard Dompet', 'ringkasan' => $this->model->ringkasan()];
        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/dashboard_dompet', $data);
        $this->load->view('template/footer');
    }

    private function require_login()
    {
        $session = $this->session->userdata('admin');
        if (is_array($session) && !empty($session['id_user'])) {
            return;
        }

        redirect('/');
        exit;
    }

}
