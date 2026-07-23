<?php
class Dashboard extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('M_dashboard', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}


		$data['title'] = 'Dashboard';
		$data['total_mapel'] = $this->db->get('master_mata_pelajaran')->num_rows();

		$data['total_kelas'] = $this->db->get('kelas')->num_rows();
		$data['total_pegawai'] = $this->db->get('pegawai')->num_rows();
		$this->load->view('template/header', $data);
		$this->load->view('dashboard', $data);
		$this->load->view('template/footer');
	}

	public function mapel_result()
	{
		$data = $this->model->mapel_result();

		echo json_encode($data);
	}
	public function dashboard_result()
	{
		$data = $this->model->dashboard_result();

		echo json_encode($data);
	}
	public function jadwal_result()
	{
		$data = $this->model->jadwal_result();

		echo json_encode($data);
	}
	public function saldo_result()
	{
		
		$tanggal_awal = $this->input->post('tanggal_awal');
		$tanggal_akhir = $this->input->post('tanggal_akhir');
		$data = $this->model->dashboard_keuangan($tanggal_awal,$tanggal_akhir);

		echo json_encode($data);
	}
public function grafik_perbandingan()
{
	$data = $this->model->grafik_perbandingan();

		echo json_encode($data);
    
}
	public function grafik_rencana_pemasukan_result()
	{
		$data = $this->model->rencana_perbandingan_pemasukan_result();

		echo json_encode($data);
	}
	public function grafik_rencana_pengeluaran_result()
	{
		$data = $this->model->rencana_perbandingan_pengeluaran_result();

		echo json_encode($data);
	}
	public function pengeluaran_terbesar_result()
	{
		$data = $this->model->top_pengeluaran_result();

		echo json_encode($data);
	}
	public function notif_keuangan()
	{
		$data = $this->model->notif_keuangan();

		echo json_encode($data);
	}
	public function api_jadwal_result()
	{
		$data = $this->model->api_jadwal_result();

		echo json_encode($data);
	}


}
?>