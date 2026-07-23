<?php
class Kelas_setting extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_kelas_setting', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Kelas Setting';
		$data['level'] = $this->session->userdata('admin')['level'];
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/kelas_setting', $data);
		$this->load->view('template/footer');
	}

	public function mata_pelajaran_result()
	{
		$data = $this->model->mata_pelajaran_result();

		echo json_encode($data);
	}
	public function kelas_setting_result()
	{
		$data = $this->model->kelas_setting_result();

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
