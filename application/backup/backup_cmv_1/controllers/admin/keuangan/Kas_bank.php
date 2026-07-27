<?php
class Kas_bank extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/keuangan/M_kas_bank', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Kas Bank';
		$data['tahun'] = date("Y");
		$this->load->view('template/header', $data);
		$this->load->view('admin/keuangan/kas_bank', $data);
		$this->load->view('template/footer');
	}

	public function kas_bank_result()
	{
		$data = $this->model->kas_bank_result();

		echo json_encode($data);
	}
	public function get_saldo()
	{
		$data = $this->model->get_saldo();

		echo json_encode($data);
	}
	public function tambah()
	{
		$data = $this->model->tambah();

		echo json_encode($data);
	}
	public function simpan_saldo_awal()
	{
		$data = $this->model->simpan_saldo_awal();

		echo json_encode($data);
	}
	public function edit()
	{
		$data = $this->model->edit();

		echo json_encode($data);
	}
	public function hapus()
	{
		$data = $this->model->hapus();

		echo json_encode($data);
	}

}
?>