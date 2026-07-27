<?php
class Bonus extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/gaji/M_bonus', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Bonus Pegawai';
		$this->load->view('template/header', $data);
		$this->load->view('admin/gaji/bonus', $data);
		$this->load->view('template/footer');
	}

	public function bonus_result()
	{
		$data = $this->model->bonus_result();

		echo json_encode($data);
	}
	public function pegawai_result()
	{
		$data = $this->model->pegawai_result();

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
