<?php
class Mutasi_kas extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/keuangan/M_mutasi_kas', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
        $data['kasbank']  = $this->db->get_where('kasbank')->result_array();
		$data['title'] = 'Mutasi Kas';
		$this->load->view('template/header', $data);
		$this->load->view('admin/keuangan/mutasi_kas', $data);
		$this->load->view('template/footer');
	}

	public function mutasi_kas_result()
	{
		$data = $this->model->mutasi_kas_result();

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
