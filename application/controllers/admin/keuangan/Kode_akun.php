<?php
class Kode_akun extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/keuangan/M_kode_akun', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Kode Akun';
		$this->load->view('template/header', $data);
		$this->load->view('admin/keuangan/kode_akun', $data);
		$this->load->view('template/footer');
	}

	public function kode_akun_result()
	{
		$data = $this->model->kode_akun_result();

		echo json_encode($data);
	}
	public function tambah()
	{
		$data = $this->model->tambah();

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
