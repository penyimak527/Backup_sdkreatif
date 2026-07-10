<?php
class Laporan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/M_rpp', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Laporan';
		$data['guru'] = $this->db->get('guru')->result_array();
		$data['pegawai'] = $this->db->get('pegawai')->result_array();
		$data['periode'] = $this->db->get('master_tahun_ajaran')->result_array();
		$data['kelas'] = $this->db->get('kelas')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/laporan', $data);
		$this->load->view('template/footer');
	}

	public function laporan_result()
	{
		$id_level = $this->session->userdata('admin')['id_level'];
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->where('group', 'Laporan');
			$this->db->where('id_level', $id_level);
			$this->db->like('name', $search); // pakai LIKE agar pencarian fleksibel
			$data = $this->db->get('conf_list_menu')->result_array();
		} else {
			$data = $this->db->get_where('conf_list_menu', [
				'group' => 'Laporan',
				'id_level' => $id_level
			])->result_array();
		}
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
