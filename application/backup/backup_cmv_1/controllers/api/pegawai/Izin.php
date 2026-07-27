<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Izin extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('api/pegawai/M_izin_pegawai', 'model');
	}

	// GET: /api/pegawai/izin
	public function index()
	{
		$data['pegawai'] = $this->db->get('pegawai')->result_array();
		$data['izin'] = $this->db->get('master_izin')->result_array();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $data
			]));
	}

	// GET: /api/pegawai/izin/view/{id_pegawai}
	public function view($id_pegawai)
	{
		$jabatan = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->result_array();
		$mapel = $this->db->get_where('guru_mapel', ['id_guru' => $id_pegawai])->result_array();

		$selected_ids = array_column($jabatan, 'id_jabatan');
		$selected_ids_mapel = array_column($mapel, 'id_mapel');

		$data = [
			'nip' => $this->db->get_where('guru', ['id' => $id_pegawai])->row_array(),
			'jabatan' => $this->db->get('jabatan')->result_array(),
			'mapel' => $this->db->get('master_mata_pelajaran')->result_array(),
			'select_jabatan' => $selected_ids,
			'select_mapel' => $selected_ids_mapel,
			'id_pegawai' => $id_pegawai
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $data
			]));
	}

	// POST: /api/pegawai/izin/izin_pegawai_result
	public function izin_pegawai_result()
	{
		$data = $this->model->izin_pegawai_result();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $data
			]));
	}

	// POST: /api/pegawai/izin/pegawai_edit
	public function pegawai_edit()
	{
		$data = $this->model->pegawai_edit();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $data
			]));
	}

	// POST: /api/pegawai/izin/tambah
	public function tambah()
	{
		$data = $this->model->tambah();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Izin berhasil ditambahkan',
				'data' => $data
			]));
	}

	// POST: /api/pegawai/izin/edit
	public function edit()
	{
		$data = $this->model->edit();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Izin berhasil diubah',
				'data' => $data
			]));
	}

	// POST: /api/pegawai/izin/hapus
	public function hapus()
	{
		$data = $this->model->hapus();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Izin berhasil dihapus',
				'data' => $data
			]));
	}
}
?>
