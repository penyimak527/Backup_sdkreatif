<?php
class riwayat_presensi_pegawai extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/presensi/M_riwayat_presensi_pegawai', 'model');
	}

	public function index()
	{
		$data['title'] = 'Riwayat Presensi Pegawai';
		$this->load->view('template/header', $data);
		$this->load->view('admin/presensi/riwayat_presensi_pegawai', $data);
		$this->load->view('template/footer');
	}
	public function presensi_pegawai_tambah()
	{
		$id_pegawai = $this->input->post('id_pegawai');

		if (!$id_pegawai) {
			echo json_encode(false);
			return;
		}
		$data = $this->model->presensi_pegawai_tambah($id_pegawai);
		header('Content-Type: application/json');
		echo json_encode($data);
	}
	public function result_presensi_hariini()
	{
		$data = $this->model->result_presensi_hariini();
		echo json_encode($data);
	}
	public function riwayatpresensi_pegawai()
	{
		$data = $this->model->riwayatpresensi_pegawai();
		echo json_encode($data);
	}
	public function cek_absensi()
	{
		$data = $this->model->cek_absensi();
		echo json_encode($data);
	}
	public function cek_jabatan()
	{
		$data = $this->model->cek_jabatan();
		echo json_encode($data);
	}

}

?>