<?php
class Pindah_kelas extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_pindah_kelas', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Pindah Kelas';
		$data['kelas_kiri'] = $this->db->get('kelas_setting')->result_array();
		$data['periode_kiri'] = $this->db->get('master_tahun_ajaran')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/pindah_kelas', $data);
		$this->load->view('template/footer');
	}

	public function pindah_kelas_result()
	{
		$id_kelas_kiri = $this->input->post('id_kelas_kiri');
		$id_periode_kiri = $this->input->post('id_periode_kiri');
		$id_kelas_kanan = $this->input->post('id_kelas_kanan');
		$id_periode_kanan = $this->input->post('id_periode_kanan');


		$data = $this->model->pindah_kelas_result($id_kelas_kiri, $id_periode_kiri, $id_kelas_kanan, $id_periode_kanan);

		echo json_encode($data);
	}


	public function pindah_ke_kanan()
	{
		$id_kelas_kanan = $this->input->post('id_kelas_kanan');
		$id_periode_kanan = $this->input->post('id_periode_kanan');


		$checkbox_kiri = $this->input->post('checkbox_kiri');

		$kelas_setting = $this->db->get_where('kelas_setting', array('id' => $id_kelas_kanan, 'id_periode' => $id_periode_kanan))->row_array();

		if ($kelas_setting && !empty($checkbox_kiri)) {
			$kelas = $kelas_setting['nama_kelas'];

			$this->model->pindah_ke_kanan($kelas_setting['id'], $checkbox_kiri);

			echo json_encode(array("status" => "success", "message" => "Siswa berhasil dipindahkan ke kelas $kelas"));
		} else {
			$kelas = isset($kelas_setting['nama_kelas']) ? $kelas_setting['nama_kelas'] : 'kelas yang dituju atau Kelas yang dituju belum di tambahkan di Kelas Setting';

			echo json_encode(array("status" => "error", "message" => "Gagal memindahkan siswa ke $kelas"));
		}
	}

	public function pindah_ke_kiri()
	{
		$id_kelas_kiri = $this->input->post('id_kelas_kiri');
		$id_periode_kiri = $this->input->post('id_periode_kiri');
		$checkbox_kanan = $this->input->post('checkbox_kanan');

		$kelas_setting = $this->db->get_where('kelas_setting', array('id' => $id_kelas_kiri, 'id_periode' => $id_periode_kiri))->row_array();

		if ($kelas_setting && !empty($checkbox_kanan)) {
			$kelas = $kelas_setting['nama_kelas'];

			$this->model->pindah_ke_kanan($kelas_setting['id'], $checkbox_kanan);

			echo json_encode(array("status" => "success", "message" => "Siswa berhasil dipindahkan ke kelas $kelas"));
		} else {
			$kelas = isset($kelas_setting['nama_kelas']) ? $kelas_setting['nama_kelas'] : 'kelas yang dituju atau Kelas yang dituju belum di tambahkan di Kelas Setting';

			echo json_encode(array("status" => "error", "message" => "Gagal memindahkan siswa ke $kelas"));
		}
	}

}
?>