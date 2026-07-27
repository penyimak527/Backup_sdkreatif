<?php
class Master_potongan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/gaji/M_master_potongan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Master Potongan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/gaji/master_potongan', $data);
		$this->load->view('template/footer');
	}

	public function master_potongan_result()
	{
		$data = $this->model->master_potongan_result();

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
