<?php
class Gaji_pegawai extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/gaji/M_gaji_pegawai', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Gaji Pegawai';
		$this->load->view('template/header', $data);
		$this->load->view('admin/gaji/gaji_pegawai', $data);
		$this->load->view('template/footer');
	}

	public function gaji_pegawai_result()
	{
		$data = $this->model->gaji_pegawai_result();

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
	public function gaji_pokok()
	{
		$data = $this->model->gaji_pokok();

		echo json_encode($data);
	}
	public function edit_gaji_rendah()
	{
		$data = $this->model->edit_gaji_rendah();

		echo json_encode($data);
	}
	public function hitung_gaji_pokok()
	{
		$data = $this->model->hitung_gaji_pokok();

		echo json_encode($data);
	}
	public function get_gaji_awal()
	{
		$data = $this->model->get_gaji_awal();

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
