<?php
class Siswa extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_siswa', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Siswa';
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/siswa', $data);
		$this->load->view('template/footer');
	}

	public function siswa_result()
	{
		$data = $this->model->siswa_result();

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
