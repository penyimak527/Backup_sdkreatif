<?php
class Saldo extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/keuangan/M_saldo', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Saldo Bank';
		$this->load->view('template/header', $data);
		$this->load->view('admin/keuangan/saldo', $data);
		$this->load->view('template/footer');
	}

	public function saldo_result()
	{
		$data = $this->model->saldo_result();

		echo json_encode($data);
	}
}
?>
