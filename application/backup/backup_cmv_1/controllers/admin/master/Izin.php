<?php
class izin extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/master/M_izin', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Izin';
		$this->load->view('template/header', $data);
		$this->load->view('admin/master/izin', $data);
		$this->load->view('template/footer');
	}

	public function izin_result()
	{
		$data = $this->model->izin_result();

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
