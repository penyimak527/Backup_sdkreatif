<?php
class Pinjaman_pegawai extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/gaji/M_pinjaman_pegawai', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Pinjaman Pegawai';
		$this->load->view('template/header', $data);
		$this->load->view('admin/gaji/pinjaman_pegawai', $data);
		$this->load->view('template/footer');
	}

	public function pinjaman_pegawai_result()
	{
		$data = $this->model->pinjaman_pegawai_result();

		echo json_encode($data);
	}
	public function row_pinjaman()
	{
		$data = $this->model->row_pinjaman();

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
