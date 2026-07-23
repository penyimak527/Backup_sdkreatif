<?php
class Guru extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_guru', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Guru';
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/guru', $data);
		$this->load->view('template/footer');
	}

	public function guru_result()
	{
		$data = $this->model->guru_result();

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
	public function update_status()
	{
		$data = $this->model->update_status();

		echo json_encode($data);
	}
	public function hapus()
	{
		$data = $this->model->hapus();

		echo json_encode($data);
	}

}
?>
