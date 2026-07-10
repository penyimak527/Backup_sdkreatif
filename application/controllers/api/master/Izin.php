<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Izin extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('api/master/M_izin', 'model');
		$this->load->helper('security');
		// $this->load->helper('json_output'); // optional: custom helper for json format
		// $this->load->library('input');
	}

	// GET: /api/izin
	public function index()
	{
		// List semua izin
		$data = $this->model->izin_result();
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $data
			]));
	}

	// POST: /api/izin/tambah
	public function tambah()
	{
		$input = $this->input->post();

		$data = $this->model->tambah($input);
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Data izin berhasil ditambahkan',
				'data' => $data
			]));
	}

	// POST: /api/izin/edit
	public function edit()
	{
		$input = $this->input->post();

		$data = $this->model->edit($input);
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Data izin berhasil diubah',
				'data' => $data
			]));
	}

	// POST: /api/izin/hapus
	public function hapus()
	{
		$input = $this->input->post();

		$data = $this->model->hapus($input);
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Data izin berhasil dihapus',
				'data' => $data
			]));
	}
}
