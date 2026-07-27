<?php
class Hak_akses extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/pengaturan/M_hak_akses', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Hak Akses';
		$data['level'] = $this->db->get('level')->result_array();
		$data['pegawai'] = $this->db->get('pegawai')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/pengaturan/hak_akses', $data);
		$this->load->view('template/footer');
	}


	public function hak_akses_result()
	{
		$data = $this->model->hak_akses_result();

		echo json_encode($data);
	}
	public function pilih_menu_result()
	{
		$data = $this->model->pilih_menu_result();

		echo json_encode($data);
	}
	public function user_edit()
	{
		$data = $this->model->user_edit();

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
