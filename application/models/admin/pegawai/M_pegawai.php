<?php
class M_pegawai extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function pegawai_result()
	{

		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('nama_pegawai', $search);
		}

		$pegawai = $this->db->get('pegawai')->result_array();
		return $pegawai;
	}



	public function tambah()
	{


		$nama_pegawai = $this->input->post('nama_pegawai');
		$jk = $this->input->post('jk');
		$pend_terakhir = $this->input->post('pendk_terakhir');
		$tempat_lahir = $this->input->post('tempat_lahir');
		$tanggal_lahir = $this->input->post('tanggal_lahir');
		$no_telp = $this->input->post('no_tlp');
		$tmt = $this->input->post('tmt');
		$tahun_tmt = date('Y', strtotime($tmt));

		$angkatan = $this->db->where('tahun_awal <=', $tahun_tmt)->where('tahun_akhir >=', $tahun_tmt)->get('master_angkatan')->row_array();
		$data = [

			'nama_pegawai' => $nama_pegawai,
			'jk' => $jk,
			'pendidikan_terakhir' => $pend_terakhir,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'no_tlp' => $no_telp,
			'tmt' => $tmt,
			'angkatan' => $angkatan['angkatan']

		];
		$response = $this->db->insert('pegawai', $data);
		$id_pegawai = $this->db->insert_id();

		$id_jabatan = $this->input->post('id_jabatan');

		if ($id_jabatan != null && in_array(1, $id_jabatan)) {
			$nbm = $this->input->post('nbm');
			$data = [
				'id' => $id_pegawai,
				'nama_guru' => $nama_pegawai,
				'nbm' => $nbm,
				'jk' => $jk,
				'tempat_lahir' => $tempat_lahir,
				'tanggal_lahir' => $tanggal_lahir,
				'no_telp' => $no_telp,

			];
			$response = $this->db->insert('guru', $data);
		}

		$data_jabatan = [];
		if ($id_jabatan != null) {
			foreach ($id_jabatan as $jabatan) {
				$jabatan_row = $this->db->get_where('jabatan', ['id' => $jabatan])->row_array();
				$data_jabatan[] = [
					'id_pegawai' => $id_pegawai,
					'nama_pegawai' => $nama_pegawai,
					'id_jabatan' => $jabatan,
					'jabatan' => $jabatan_row['jabatan'] ?? '',
				];
			}
			$this->db->insert_batch('pegawai_jabatan', $data_jabatan);
		}

		$id_mapel = $this->input->post('id_mapel');
		$data_mapel = [];
		if ($id_mapel != null) {
			foreach ($id_mapel as $mapel) {
				$mapel_row = $this->db->get_where('master_mata_pelajaran', ['id' => $mapel])->row_array();
				$data_mapel[] = [
					'id_guru' => $id_pegawai,
					'guru' => $nama_pegawai,
					'id_mapel' => $mapel,
					'mapel' => $mapel_row['mapel'],
				];
			}
			$this->db->insert_batch('guru_mapel', $data_mapel);
		}

		return $response;
	}
	public function edit()
	{

		$id_pegawai = $this->input->post('id_pegawai');
		$nama_pegawai = $this->input->post('nama_pegawai');
		$jk = $this->input->post('jk');
		$pend_terakhir = $this->input->post('pendk_terakhir');
		$tempat_lahir = $this->input->post('tempat_lahir');
		$tanggal_lahir = $this->input->post('tanggal_lahir');
		$no_telp = $this->input->post('no_tlp');
		$tmt = $this->input->post('tmt');
		$tahun_tmt = date('Y', strtotime($tmt));
		$angkatan = $this->db->where('tahun_awal <=', $tahun_tmt)->where('tahun_akhir >=', $tahun_tmt)->get('master_angkatan')->row_array();
		$data = [

			'nama_pegawai' => $nama_pegawai,
			'jk' => $jk,
			'pendidikan_terakhir' => $pend_terakhir,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'no_tlp' => $no_telp,
			'tmt' => $tmt,
			'angkatan' => $angkatan['angkatan']
		];

		$response = $this->db->update('pegawai', $data, ['id' => $id_pegawai]);

		$id_jabatan = $this->input->post('id_jabatan');
		if (in_array(1, $id_jabatan)) {
			$nbm = $this->input->post('nbm');
			$data = [
				'id' => $id_pegawai,
				'nama_guru' => $nama_pegawai,
				'nbm' => $nbm,
				'jk' => $jk,
				'tempat_lahir' => $tempat_lahir,
				'tanggal_lahir' => $tanggal_lahir,
				'no_telp' => $no_telp,

			];
			$id_guru = $this->db->get_where('guru', ['id' => $id_pegawai])->row_array();
			if ($id_guru != null) {
				$response = $this->db->update('guru', $data, ['id' => $id_pegawai]);
			} else {
				$response = $this->db->insert('guru', $data);
			}
			$response = $this->db->update('guru', $data, ['id' => $id_pegawai]);
		}

		$data_jabatan = [];
		$this->db->delete('pegawai_jabatan', ['id_pegawai' => $id_pegawai]);
		if ($id_jabatan != null) {
			foreach ($id_jabatan as $jabatan) {
				$jabatan_row = $this->db->get_where('jabatan', ['id' => $jabatan])->row_array();
				$data_jabatan[] = [
					'id_pegawai' => $id_pegawai,
					'nama_pegawai' => $nama_pegawai,
					'id_jabatan' => $jabatan,
					'jabatan' => $jabatan_row['jabatan'] ?? '',
				];
			}
			$this->db->insert_batch('pegawai_jabatan', $data_jabatan);
		}

		$id_mapel = $this->input->post('id_mapel');
		$data_mapel = [];

		$this->db->delete('guru_mapel', ['id_guru' => $id_pegawai]);
		if ($id_mapel != null) {
			foreach ($id_mapel as $mapel) {
				$mapel_row = $this->db->get_where('master_mata_pelajaran', ['id' => $mapel])->row_array();
				$data_mapel[] = [
					'id_guru' => $id_pegawai,
					'guru' => $nama_pegawai,
					'id_mapel' => $mapel,
					'mapel' => $mapel_row['mapel'],
				];
			}
			$this->db->insert_batch('guru_mapel', $data_mapel);
		}

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');

		$this->db->delete('pegawai_jabatan', ['id_pegawai' => $id]);
		$this->db->delete('guru_mapel', ['id_guru' => $id]);
		$this->db->delete('guru', ['id' => $id]);
		$this->db->delete('user', ['id_pegawai' => $id]);
		$response = $this->db->delete('pegawai', ['id' => $id]);

		return $response;
	}
	public function pegawai_user($id_pegawai)
	{
		$this->db->from('pegawai a');
		$this->db->select('a.*, b.jabatan');
		$this->db->join('pegawai_jabatan b', 'a.id = b.id_pegawai');
		if (!empty($id_pegawai)) {
			$this->db->where('a.id', $id_pegawai);
			return $this->db->get()->row_array();
		} else {
			return $this->db->get()->result_array();
		}
	}
}
?>