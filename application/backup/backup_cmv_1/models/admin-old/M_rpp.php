<?php
class M_rpp extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function rpp_result()
	{


		$level = $this->session->userdata('admin')['level'];
		$search = $this->input->post('search');
		if ($search != null) {
			$this->db->like('nama_guru', $search);
			$this->db->or_like('judul', $search);
		}
		if ($level == 'Admin') {

			$rpp = $this->db->get('rpp')->result_array();
		} else {

			$rpp = $this->db->get_where('rpp', ['id_guru' => $this->session->userdata('admin')['id_pegawai']])->result_array();
		}


		return $rpp;
	}


	public function tambah()
	{

		$guru = $this->db->get_where('guru', ['id' => $this->input->post('id_guru')])->row_array();
		$kelas = $this->db->get_where('kelas', ['id' => $this->input->post('id_kelas')])->row_array();
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $this->input->post('id_periode')])->row_array();
		$judul = $this->input->post('judul');
		$semester = $this->input->post('semester');


		$config['upload_path'] = 'storage/guru/rpp/';
		$config['allowed_types'] = 'png|jpg|jpeg|pdf';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);
		$this->load->library('image_lib');

		if (!empty($_FILES['file_rpp']['name'])) {
			if (!$this->upload->do_upload('file_rpp')) {
				echo $this->upload->display_errors();
				return;
			} else {
				$data_upload_awal = $this->upload->data();
				$nama_file_rpp = $data_upload_awal['file_name'] ?? 'default.jpg';
			}
		}
		$data = [

			'id_guru' => $guru['id'],
			'nama_guru' => $guru['nama_guru'],
			'id_kelas' => $kelas['id'],
			'kelas' => $kelas['nama_kelas'],
			'id_periode' => $periode['id'],
			'periode' => $periode['periode'],
			'judul' => $judul,
			'semester' => $semester,
			'file' => $nama_file_rpp,
		];

		$response = $this->db->insert('rpp', $data);

		return $response;
	}
	public function edit()
	{

		$id_rpp = $this->input->post('id_rpp');
		$guru = $this->db->get_where('guru', ['id' => $this->input->post('id_guru')])->row_array();
		$kelas = $this->db->get_where('kelas', ['id' => $this->input->post('id_kelas')])->row_array();
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $this->input->post('id_periode')])->row_array();
		$judul = $this->input->post('judul');
		$semester = $this->input->post('semester');


		$config['upload_path'] = 'storage/guru/rpp/';
		$config['allowed_types'] = 'png|jpg|jpeg|pdf';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);
		$this->load->library('image_lib');


		$oldImage = $this->input->post('oldImage');

		$nama_file_rpp = $oldImage;
		if (!empty($_FILES['file_rpp']['name'])) {
			if (!$this->upload->do_upload('file_rpp')) {
				echo $this->upload->display_errors();
				return;
			} else {
				$data_upload_awal = $this->upload->data();
				$nama_file_rpp = $data_upload_awal['file_name'] ?? 'default.jpg';

				if (!empty($oldImage) && file_exists('./storage/guru/rpp/' . $oldImage) && $oldImage !== 'default.jpg') {
					unlink('./storage/guru/rpp/' . $oldImage);
				}
			}
		}
		$data = [

			'id_guru' => $guru['id'],
			'nama_guru' => $guru['nama_guru'],
			'id_kelas' => $kelas['id'],
			'kelas' => $kelas['nama_kelas'],
			'id_periode' => $periode['id'],
			'periode' => $periode['periode'],
			'judul' => $judul,
			'semester' => $semester,
			'file' => $nama_file_rpp,
		];

		$response = $this->db->update('rpp', $data, ['id' => $id_rpp]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('rpp', ['id' => $id]);

		return $response;
	}




	public function getTanggalHariSetahun($hari)
	{
		$hariArray = [
			'Senin' => 'monday',
			'Selasa' => 'tuesday',
			'Rabu' => 'wednesday',
			'Kamis' => 'thursday',
			'Jumat' => 'friday',
			'Sabtu' => 'saturday',
			'Minggu' => 'sunday',
		];

		$tanggal = [];
		$tahun = date('Y');
		$startDate = strtotime("first {$hariArray[$hari]} of {$tahun}-01-01");
		$endDate = strtotime("last day of December {$tahun}");

		for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 week', $date)) {
			$tanggal[] = date('Y-m-d', $date);
		}

		return $tanggal;
	}
}
?>