<?php
class Mata_Pelajaran extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/master/M_mata_pelajaran', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Mata Pelajaran';
		$this->load->view('template/header', $data);
		$this->load->view('admin/master/mata_pelajaran', $data);
		$this->load->view('template/footer');
	}

	public function mata_pelajaran_result()
	{
		$data = $this->model->mata_pelajaran_result();

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
