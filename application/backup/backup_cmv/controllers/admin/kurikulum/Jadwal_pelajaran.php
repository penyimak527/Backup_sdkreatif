<?php
class Jadwal_pelajaran extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_jadwal_pelajaran', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Jadwal Pelajaran';
		$data['level'] = $this->session->userdata('admin')['level'];
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jadwal_pelajaran', $data);
		$this->load->view('template/footer');
	}

	public function mata_pelajaran_result()
	{
		$data = $this->model->mata_pelajaran_result();

		echo json_encode($data);
	}
	public function jadwal_pelajaran_result()
	{
		$data = $this->model->jadwal_pelajaran_result();

		echo json_encode($data);
	}
	public function tambah()
	{
		$data = $this->model->tambah();

		echo json_encode($data);
	}
	public function upload()
	{
		$data = $this->model->upload();

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