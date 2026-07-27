<?php
class Jabatan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/pegawai/M_jabatan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Jabatan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/pegawai/jabatan', $data);
		$this->load->view('template/footer');
	}

	public function jabatan_result()
	{
		$data = $this->model->jabatan_result();

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
