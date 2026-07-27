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

			$this->db->select('rpp.*, kelas.kode_kelas');
			$this->db->from('rpp');
			$this->db->join('kelas', 'kelas.id = rpp.id_kelas');
			$rpp = $this->db->get()->result_array();

		} else {
			$this->db->select('rpp.*, kelas.kode_kelas');
			$this->db->from('rpp');
			$this->db->join('kelas', 'kelas.id = rpp.id_kelas');
			$this->db->where('rpp.id_guru', $this->session->userdata('admin')['id_pegawai']);
			$rpp = $this->db->get()->result_array();

		}


		return $rpp;
	}

	public function file_rpp()
	{


		$id_rpp = $this->input->post('id_rpp');
		if ($id_rpp != null) {
			$this->db->where('id_rpp', $id_rpp);
		}
		$response = $this->db->get_where('file_rpp', ['id_rpp' => $id_rpp])->result_array();

		if (!empty($response)) {
			$data = [
				'status' => true,
				'data' => $response,
			];
		} else {
			$data = [
				'status' => false,
			];

		}
		return $data;
	}


	public function tambah()
	{

		$guru = $this->db->get_where('guru', ['id' => $this->input->post('id_guru')])->row_array();
		$kelas = $this->db->get_where('kelas', ['id' => $this->input->post('id_kelas')])->row_array();
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $this->input->post('id_periode')])->row_array();
		$judul = $this->input->post('judul');
		$semester = $this->input->post('semester');


		$data = [

			'id_guru' => $guru['id'],
			'nama_guru' => $guru['nama_guru'],
			'id_kelas' => $kelas['id'],
			'kelas' => $kelas['nama_kelas'],
			'id_periode' => $periode['id'],
			'periode' => $periode['periode'],
			'judul' => $judul,
			'semester' => $semester,
		];

		$response = $this->db->insert('rpp', $data);

		$id_rpp = $this->db->insert_id();



		$files = $_FILES;
		$file_count = count($_FILES['file_rpp']['name']);

		$data_file = [];
		for ($i = 0; $i < $file_count; $i++) {
			$_FILES['file_rpp']['name'] = $files['file_rpp']['name'][$i];
			$_FILES['file_rpp']['type'] = $files['file_rpp']['type'][$i];
			$_FILES['file_rpp']['tmp_name'] = $files['file_rpp']['tmp_name'][$i];
			$_FILES['file_rpp']['error'] = $files['file_rpp']['error'][$i];
			$_FILES['file_rpp']['size'] = $files['file_rpp']['size'][$i];

			$config['upload_path'] = './storage/guru/rpp';
			$config['allowed_types'] = 'pdf';
			$config['max_size'] = 51200;

			$original_name = pathinfo($_FILES['file_rpp']['name'], PATHINFO_FILENAME);
			$extension = pathinfo($_FILES['file_rpp']['name'], PATHINFO_EXTENSION);
			$safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original_name);
			$config['file_name'] = $safe_name . '.' . $extension;

			$this->load->library('upload', $config);
			$this->upload->initialize($config);

			if (!$this->upload->do_upload('file_rpp')) {
				echo $this->upload->display_errors();
			} else {
				$data_file[] = [
					'id_rpp' => $id_rpp,
					'file' => $safe_name . '.' . $extension
				];
			}

		}
		if (!empty($data_file)) {
			$this->db->insert_batch('file_rpp', $data_file);
		}
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

		$this->db->trans_begin();

		$data = [

			'id_guru' => $guru['id'],
			'nama_guru' => $guru['nama_guru'],
			'id_kelas' => $kelas['id'],
			'kelas' => $kelas['nama_kelas'],
			'id_periode' => $periode['id'],
			'periode' => $periode['periode'],
			'judul' => $judul,
			'semester' => $semester
		];

		$this->db->update('rpp', $data, ['id' => $id_rpp]);
		if (!empty($_FILES['file_rpp']['name'][0])) {

			$old_files = $this->db->get_where('file_rpp', ['id_rpp' => $id_rpp])->result_array();
			foreach ($old_files as $file) {
				$old_path = FCPATH . 'storage/guru/rpp/' . $file['file'];
				if (file_exists($old_path)) {
					unlink($old_path);
				}
			}

			$this->db->delete('file_rpp', ['id_rpp' => $id_rpp]);


			$files = $_FILES;
			$count = count($_FILES['file_rpp']['name']);
			$data_file = [];

			for ($i = 0; $i < $count; $i++) {
				$_FILES['file_rpp']['name'] = $files['file_rpp']['name'][$i];
				$_FILES['file_rpp']['type'] = $files['file_rpp']['type'][$i];
				$_FILES['file_rpp']['tmp_name'] = $files['file_rpp']['tmp_name'][$i];
				$_FILES['file_rpp']['error'] = $files['file_rpp']['error'][$i];
				$_FILES['file_rpp']['size'] = $files['file_rpp']['size'][$i];

				$config['upload_path'] = './storage/guru/rpp/';
				$config['allowed_types'] = 'pdf';
				$config['max_size'] = 51200;

				$original_name = pathinfo($_FILES['file_rpp']['name'], PATHINFO_FILENAME);
				$extension = pathinfo($_FILES['file_rpp']['name'], PATHINFO_EXTENSION);
				$safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original_name);
				$config['file_name'] = $safe_name . '.' . $extension;

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				if (!$this->upload->do_upload('file_rpp')) {
					echo $this->upload->display_errors();
					return false;
				} else {
					$data_file[] = [
						'id_rpp' => $id_rpp,
						'file' => $safe_name . '.' . $extension
					];
				}
			}
			if (!empty($data_file)) {
				$this->db->insert_batch('file_rpp', $data_file);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return false;
		} else {
			$this->db->trans_commit();
			return true;
		}
	}
	public function hapus()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();
		$file_list = $this->db->get_where('file_rpp', ['id_rpp' => $id])->result_array();


		if (!empty($file_list)) {
			foreach ($file_list as $file) {
				$file_path = FCPATH . 'storage/guru/rpp/' . $file['file']; // lebih aman pakai FCPATH

				if (file_exists($file_path)) {
					unlink($file_path);
				}
			}

			// Hapus data file dari DB setelah file dihapus
			$this->db->delete('file_rpp', ['id_rpp' => $id]);
		}

		$this->db->delete('rpp', ['id' => $id]);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return false;
		} else {
			$this->db->trans_commit();
			return true;
		}
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