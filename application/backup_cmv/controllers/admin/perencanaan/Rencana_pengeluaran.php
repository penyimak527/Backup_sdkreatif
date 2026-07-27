<?php
class Rencana_pengeluaran extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/perencanaan/M_rencana_pengeluaran', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Rencana Pengeluaran';
		$data['kode_akun']	= $this->db->get_where('kode_akun', ['jenis' => 'Pengeluaran'])->result_array();
		$data['tahun_ajaran']  = $this->db->get_where('master_tahun_ajaran')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/perencanaan/rencana_pengeluaran', $data);
		$this->load->view('template/footer');
	}

	public function rencana_pengeluaran_result()
	{
		$data = $this->model->rencana_pengeluaran_result();

		echo json_encode($data);
	}
	public function ambilAsumsi()
	{
		$data = $this->model->ambilAsumsi();

		echo json_encode($data);
	}
	public function ambilAsumsiEdit()
	{
		$data = $this->model->ambilAsumsiEdit();

		echo json_encode($data);
	}
	public function detail()
	{
		$data = $this->model->detail();

		echo json_encode($data);
	}
	public function ambil_edit()
	{
		$data = $this->model->detail_edit();

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

	public function edit_persen_gaji()
	{
		$data = $this->model->edit_persen_gaji();

		echo json_encode($data);
	}
		public function get_persen_gaji()
	{
		$data = $this->model->get_persen_gaji();

		echo json_encode($data);
	}
}
?>
