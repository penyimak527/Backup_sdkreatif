<?php
class M_jadwal_pelajaran extends CI_Model
{

	function __construct()
	{
		parent::__construct();

	}


	public function jadwal_pelajaran_result()
	{
		$search = $this->input->post('search');


		if ($search != null) {
			$this->db->like('mapel', $search);
			$this->db->or_like('nama_guru', $search);
		}
		$level = $this->session->userdata('admin')['level'];
		$this->db->select('kelas_jadwal_pelajaran.*');
		if ($level == 'Guru') {
			$this->db->where('kelas_jadwal_pelajaran.id_guru', $this->session->userdata('admin')['id_pegawai']);
		}
		$this->db->order_by('kelas_jadwal_pelajaran.jam_pelajaran_awal', 'ASC');
		$jadwal_pelajaran = $this->db->get('kelas_jadwal_pelajaran')->result_array();


		return $jadwal_pelajaran;
	}

	public function tambah()
	{
		$id_kelas = $this->input->post('id_kelas');


		$id_periode = $this->input->post('id_periode');
		$kelas = $this->db->get_where('kelas', ['id' => $id_kelas])->row_array();
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $id_periode])->row_array();
		$semester = $this->input->post('semester');
		$id_guru = $this->input->post('id_guru');
		$guru = $this->db->get_where('guru', ['id' => $id_guru])->row_array();

		$jam_pelajaran_awal = $this->input->post('jam_pelajaran_awal');
		$jam_pelajaran_akhir = $this->input->post('jam_pelajaran_akhir');
		$mapel = $this->db->get_where('master_mata_pelajaran', ['id' => $this->input->post('id_mapel')])->row_array();


		$hari = $this->input->post('hari');
		$awal = new DateTime($jam_pelajaran_awal);
		$akhir = new DateTime($jam_pelajaran_akhir);
		$selisih = $awal->diff($akhir);
		$jam_saja = $selisih->h;

		$data = [
			'id_kelas' => $kelas['id'] ?? '',
			'kelas' => $kelas['nama_kelas'] ?? '',
			'id_guru' => $guru['id'] ?? '',
			'nama_guru' => $guru['nama_guru'] ?? '',
			'id_periode' => $periode['id'] ?? '',
			'periode' => $periode['periode'] ?? '',
			'semester' => $semester,
			'jam_pelajaran_awal' => $jam_pelajaran_awal,
			'jam_pelajaran_akhir' => $jam_pelajaran_akhir,
			'jumlah_jam' => $jam_saja,
			'hari' => $hari,
			'id_mapel' => $mapel['id'] ?? '',
			'mapel' => $mapel['mapel'] ?? '',
		];

		$response = $this->db->insert('kelas_jadwal_pelajaran', $data);

		return $response;
	}
	public function upload()
	{
		$kelas = $this->input->post('kelas');
		$mapel = $this->input->post('mapel');
		$kode_kelas = $this->input->post('kode_kelas');
		$jam_awal = $this->input->post('jam_awal');
		$jam_akhir = $this->input->post('jam_akhir');
		$hari = $this->input->post('hari');
		$semester = $this->input->post('semester');
		$periode = $this->input->post('periode');

		$data = [];
		$id_guru = $this->session->userdata('admin')['id_pegawai'];
		$guru = $this->session->userdata('admin')['nama_lengkap'];
		if ($kelas) {
			foreach ($kelas as $key => $value) {
				$kelas_row = $this->db->get_where('kelas', [
					'nama_kelas' => $kelas[$key],
					'kode_kelas' => $kode_kelas[$key]
				])->row_array();

				$mapel_row = $this->db->get_where('master_mata_pelajaran', [
					'mapel' => $mapel[$key]
				])->row_array();
				$periode_row = $this->db->get_where('master_tahun_ajaran', [
					'periode' => $periode[$key]
				])->row_array();

				if ($kelas_row && $mapel_row && $periode_row) {
					$data[] = [
						'id_kelas' => $kelas_row['id'],
						'kelas' => $value,
						'id_mapel' => $mapel_row['id'],
						'mapel' => $mapel_row['mapel'],
						'id_guru' => $id_guru,
						'nama_guru' => $guru,
						'jam_pelajaran_awal' => $jam_awal[$key],
						'jam_pelajaran_akhir' => $jam_akhir[$key],
						'semester' => $semester[$key],
						'id_periode' => $periode_row['id'],
						'periode' => $periode_row['periode'],
						'hari' => $hari[$key],
					];
				} else {

					var_dump($mapel[$key]);
					return false;
				}

			}
			if (!empty($data)) {
				$this->db->insert_batch('kelas_jadwal_pelajaran', $data);
				return true;
			}
		}


		return false;
	}

	public function edit()
	{

		$id_kelas = $this->input->post('id_kelas');

		$id_jadwal = $this->input->post('id');
		$id_periode = $this->input->post('id_periode');
		$kelas = $this->db->get_where('kelas', ['id' => $id_kelas])->row_array();
		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $id_periode])->row_array();
		$semester = $this->input->post('semester');
		$id_guru = $this->input->post('id_guru');
		$guru = $this->db->get_where('guru', ['id' => $id_guru])->row_array();

		$jam_pelajaran_awal = $this->input->post('jam_pelajaran_awal');
		$jam_pelajaran_akhir = $this->input->post('jam_pelajaran_akhir');
		$hari = $this->input->post('hari');
		$mapel = $this->db->get_where('master_mata_pelajaran', ['id' => $this->input->post('id_mapel')])->row_array();
		$awal = new DateTime($jam_pelajaran_awal);
		$akhir = new DateTime($jam_pelajaran_akhir);


		$selisih = $awal->diff($akhir);

		$jam_saja = $selisih->h;
		$data = [
			'id_kelas' => $kelas['id'],
			'kelas' => $kelas['nama_kelas'],
			'id_guru' => $guru['id'],
			'id_periode' => $periode['id'],
			'periode' => $periode['periode'],
			'semester' => $semester,
			'hari' => $hari,
			'jam_pelajaran_awal' => $jam_pelajaran_awal,
			'jam_pelajaran_akhir' => $jam_pelajaran_akhir,
			'jumlah_jam' => $jam_saja,
			'id_mapel' => $mapel['id'],
			'mapel' => $mapel['mapel'],

		];


		$response = $this->db->update('kelas_jadwal_pelajaran', $data, ['id' => $id_jadwal]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('kelas_jadwal_pelajaran', ['id' => $id]);

		return $response;
	}

}
?>