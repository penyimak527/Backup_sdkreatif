<?php
class Pegawai_potongan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/gaji/M_pegawai_potongan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Pegawai Potongan';
		$data['master_potongan'] = $this->db->get('master_potongan')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/gaji/pegawai_potongan', $data);
		$this->load->view('template/footer');
	}

	public function pegawai_potongan_result()
	{
		$data = $this->model->pegawai_potongan_result();

		echo json_encode($data);
	}
    	public function pegawai_result()
	{
		$data = $this->model->pegawai_result();

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
