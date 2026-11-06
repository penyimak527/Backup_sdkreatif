<?php

class M_kelas_setting extends CI_Model
{

	function __construct()
	{
		parent::__construct();

	}



	public function kelas_setting_result()
	{

		$search = $this->input->post('search');
		$level = $this->session->userdata('admin');
		if (((is_array($level) && in_array('guru', array_map('strtolower', array_map('trim', (!empty($level['levels']) && is_array($level['levels']) ? $level['levels'] : explode(',', (string) ($level['level'] ?? ''))))), true)) && !(is_array($level) && in_array('admin', array_map('strtolower', array_map('trim', (!empty($level['levels']) && is_array($level['levels']) ? $level['levels'] : explode(',', (string) ($level['level'] ?? ''))))), true)))) {
			$this->db->where('id_guru', $this->session->userdata('admin')['id_pegawai']);
		}
		if ($search != null) {
			$this->db->like('nama_kelas', $search);
		}
		$tahun = $this->db->get_where('kelas_setting')->result_array();


		return $tahun;
	}
	public function mata_pelajaran_result()
	{

		$id_kelas = $this->input->post('id_kelas');

		$query = " SELECT * FROM master_mata_pelajaran  
		";

		$mata_pelajaran = $this->db->query($query, array($id_kelas))->result_array();


		return $mata_pelajaran;
	}


	public function tambah()
	{
		$id_kelas = $this->input->post('id_kelas');


		$id_periode = $this->input->post('id_periode');
		$kelas = $this->active_class($id_kelas);
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $id_periode])->row_array();
		$semester = $this->input->post('semester');


		$id_guru = $this->input->post('id_guru');
		$guru = $this->db->get_where('guru', ['id' => $id_guru])->row_array();
		if (!$kelas || !$periode || !$guru || !in_array($semester, ['Ganjil', 'Genap'], true)) {
			return false;
		}
		$data = [
			'id_kelas' => $kelas['id'] ?? '',
			'nama_kelas' => $this->class_label($kelas),
			'id_guru' => $guru['id'] ?? '',
			'wali_kelas' => $guru['nama_guru'] ?? '',
			'id_periode' => $periode['id'] ?? '',
			'periode' => $periode['periode'] ?? '',
			'semester' => $semester,

		];

		$response = $this->db->insert('kelas_setting', $data);

		return $response;
	}
	public function tambah_siswa()
	{

		$id_siswa = $this->input->post('id_siswa');
		$id_kelas_setting = $this->input->post('id_kelas_setting');
		$data_siswa = [];
		foreach ($id_siswa as $key => $value) {
			$siswa = $this->db->get_where('siswa', ['id' => $value])->row_array();
			$data_siswa[] = [
				'id_kelas_setting' => $id_kelas_setting,
				'id_siswa' => $value,
				'nama_siswa' => $siswa['nama_lengkap'],
				'nis' => $siswa['nis'],
				'jenis_kelamin' => $siswa['jk'],
				'status_aktif' => 1,
			];

		}
		$response = $this->db->insert_batch('kelas_siswa', $data_siswa);

		if ($response) {
			$data = [
				'status' => true,
				'id_kelas_setting' => $id_kelas_setting
			];
		}
		return $data;
	}
	public function edit()
	{
		$id_kelas = $this->input->post('id_kelas');

		$id_kelas_setting = $this->input->post('id');
		$id_periode = $this->input->post('id_periode');
		$kelas = $this->active_class($id_kelas);
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $id_periode])->row_array();
		$semester = $this->input->post('semester');


		$id_guru = $this->input->post('id_guru');
		$guru = $this->db->get_where('guru', ['id' => $id_guru])->row_array();
		if (!$kelas || !$periode || !$guru || !in_array($semester, ['Ganjil', 'Genap'], true)) {
			return false;
		}
		$data = [
			'id_kelas' => $kelas['id'],
			'nama_kelas' => $this->class_label($kelas),
			'id_guru' => $guru['id'],
			'wali_kelas' => $guru['nama_guru'],
			'id_periode' => $periode['id'],
			'periode' => $periode['periode'],
			'semester' => $semester,

		];


		$response = $this->db->update('kelas_setting', $data, ['id' => $id_kelas_setting]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('kelas_setting', ['id' => $id]);

		return $response;
	}

	private function active_class($id)
	{
		$this->db->where('id', (int) $id);
		if ($this->db->field_exists('deleted_at', 'kelas')) {
			$this->db->where('deleted_at IS NULL', null, false);
		}
		return $this->db->get('kelas')->row_array();
	}

	private function class_label($kelas)
	{
		if (!$kelas) return '';
		return trim(trim((string) $kelas['nama_kelas']).' '.trim((string) ($kelas['kode_kelas'] ?? '')));
	}

}
?>
