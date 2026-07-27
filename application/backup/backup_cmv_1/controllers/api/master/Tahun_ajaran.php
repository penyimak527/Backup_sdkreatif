<?php
class Tahun_ajaran extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('api/master/M_tahun_ajaran', 'model');

		// Optional: Set header JSON untuk semua response di controller ini
		header('Content-Type: application/json');
	}

	public function index()
	{
		// Hapus pengecekan session dan redirect
		// Jika ingin, bisa tampilkan pesan API sederhana saja
		echo json_encode(['message' => 'API Tahun Ajaran']);
	}

	public function tahun_ajaran_result()
	{
		$data = $this->model->tahun_ajaran_result();

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
