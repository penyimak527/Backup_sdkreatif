<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Agenda_tahunan extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('api/M_agenda_tahunan', 'model');
	}

	public function agenda_tahunan_result()
	{
		$data = $this->model->agenda_tahunan_result(); // sudah menangani pencarian di model
		echo json_encode($data);
	}

	public function pegawai_result()
	{
		$data = $this->model->pegawai_result();
		echo json_encode($data);
	}

	public function tambah()
	{
		$data = $this->model->tambah(); // pastikan menerima input POST dan file dengan benar
		echo json_encode($data);
	}

	public function edit()
	{
		$data = $this->model->edit(); // pastikan menerima id_agenda, data update, dan file jika ada
		echo json_encode($data);
	}

	public function hapus()
	{
		$data = $this->model->hapus(); // pastikan menerima id agenda
		echo json_encode($data);
	}
}
