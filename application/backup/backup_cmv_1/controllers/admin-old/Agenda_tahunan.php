<?php
class Agenda_tahunan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/M_agenda_tahunan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Agenda Tahunan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/agenda_tahunan', $data);
		$this->load->view('template/footer');
	}

	public function agenda_tahunan_result()
	{
		$data = $this->model->agenda_tahunan_result();

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
