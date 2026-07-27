<?php
class Pegawai_jabatan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/pegawai/M_pegawai', 'model');
	}

	public function index()
	{
		// if ($this->session->userdata('guru')['username'] == null) {
		// 	redirect('/');
		// }
		$data['jabatan'] = $this->db->get('jabatan')->result_array();
		$data['mapel'] = $this->db->get('master_mata_pelajaran')->result_array();
		$data['title'] = 'Pegawai';
		$data['active'] = 'pegawai';
		$data['active2'] = 'pegawai_jabatan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/pegawai/pegawai', $data);
		$this->load->view('template/footer');
	}
	public function view($id_pegawai)
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$jabatan = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->result_array();
		$mapel = $this->db->get_where('guru_mapel', ['id_guru' => $id_pegawai])->result_array();

		$selected_ids = array_column($jabatan, 'id_jabatan');
		$selected_ids_mapel = array_column($mapel, 'id_mapel');
		$data['select_jabatan'] = $selected_ids;
		$data['select_mapel'] = $selected_ids_mapel;

		$data['nbm'] = $this->db->get_where('guru', ['id' => $id_pegawai])->row_array();
		$data['jabatan'] = $this->db->get('jabatan')->result_array();
		$data['mapel'] = $this->db->get('master_mata_pelajaran')->result_array();
		$data['title'] = 'Pegawai';
		$data['id_pegawai'] = $id_pegawai;
		$this->load->view('template/header', $data);
		$this->load->view('admin/pegawai/view/pegawai', $data);
		$this->load->view('template/footer');
	}

	public function print_qr_user($id_pegawai = null)
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$data['title'] = 'Print Id Card User';
		$data['id_pegawai'] = $id_pegawai;
		$data['pegawai'] = $this->model->pegawai_user($id_pegawai);
		if (empty($id_pegawai)) {
			// cetak semua pegawai
			$this->load->view('admin/pegawai/view/cetak_pegawai/code_user_semua', $data);
		} else {
			// cetak satu pegawai
			$this->load->view('admin/pegawai/view/cetak_pegawai/code_user', $data);
		}
	}

	public function pegawai_result()
	{
		$data = $this->model->pegawai_result();

		echo json_encode($data);
	}
	public function pegawai_edit()
	{
		$data = $this->model->pegawai_edit();

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