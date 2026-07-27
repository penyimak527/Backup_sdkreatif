<?php
class Rpp extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('api/M_rpp', 'model');
	}

	public function rpp_result()
	{
		$data = $this->model->rpp_result();
		echo json_encode($data);
	}

	public function file_rpp()
	{
		$data = $this->model->file_rpp();
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
