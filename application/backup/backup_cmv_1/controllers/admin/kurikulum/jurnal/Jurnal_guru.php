<?php
class Jurnal_guru extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/jurnal/M_jurnal_guru', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Jurnal Guru';
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/jurnal_guru', $data);
		$this->load->view('template/footer');
	}
	public function jurnal_mengajar($id_jadwal, $tanggal)
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$data['title'] = 'Jurnal Mengajar';
		$data['id_jadwal'] = $id_jadwal;
		$data['tanggal'] = date('Y-m-d', strtotime($tanggal));

		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/jurnal_mengajar', $data);
		$this->load->view('template/footer');
	}
	public function riwayat_mengajar()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$data['title'] = 'Riwayat Mengajar';
		$data['kelas'] = $this->db->get('kelas')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/riwayat_mengajar', $data);
		$this->load->view('template/footer');
	}
	public function jadwal_mengajar()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$data['title'] = 'Jadwal Mengajar';
		$data['level'] = $this->session->userdata('admin')['level'];
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/jadwal_mengajar', $data);
		$this->load->view('template/footer');
	}

	public function jurnal_siswa_result()
	{

		$data = $this->model->jurnal_siswa_result();

		echo json_encode($data);
	}
	public function jadwal_mengajar_result()
	{

		$data = $this->model->jadwal_mengajar_result();

		echo json_encode($data);
	}
	public function jadwal_mengajar_result_guru()
	{

		$data = $this->model->jadwal_mengajar_result_guru();

		echo json_encode($data);
	}
	public function riwayat_mengajar_result()
	{

		$data = $this->model->riwayat_mengajar_result();
		echo json_encode($data);
	}
	public function kelas_jadwal_pelajaran_result()
	{

		$data = $this->model->kelas_jadwal_pelajaran_result();

		echo json_encode($data);
	}
	public function jurnal_guru_result()
	{

		$data = $this->model->jurnal_guru_result();

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
