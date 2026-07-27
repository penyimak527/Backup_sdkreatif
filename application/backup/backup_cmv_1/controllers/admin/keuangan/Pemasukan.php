<?php
class Pemasukan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/keuangan/M_pemasukan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
        $data['kode_akun']  = $this->db->get_where('kode_akun', ['jenis' => 'Pemasukan'])->result_array();
        $data['kasbank']  = $this->db->get_where('kasbank')->result_array();
		$data['title'] = 'Pemasukan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/keuangan/pemasukan', $data);
		$this->load->view('template/footer');
	}

	public function pemasukan_result()
	{
		$data = $this->model->pemasukan_result();

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
