<?php
class Pengeluaran extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/keuangan/M_pengeluaran', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
        $data['kode_akun']  = $this->db->get_where('kode_akun', ['jenis' => 'Pengeluaran'])->result_array();
        $data['fkode_akun']  = $this->db->get_where('kode_akun')->result_array();
        $data['kasbank']  = $this->db->get_where('kasbank')->result_array();
		$data['title'] = 'Pengeluaran';
		$this->load->view('template/header', $data);
		$this->load->view('admin/keuangan/pengeluaran', $data);
		$this->load->view('template/footer');
	}

	public function pengeluaran_result()
	{
		$data = $this->model->pengeluaran_result();

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
