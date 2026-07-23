<?php
class M_kelas_jadwal_pelajaran extends CI_Model
{


	function __construct()
	{
		parent::__construct();
	}


	public function kelas_jadwal_pelajaran_result()
	{
		$search = $this->input->post('search');
		$id_jadwal = $this->input->post('id_jadwal');

		if ($id_jadwal != null) {
			$this->db->where('id_kelas_setting', $id_jadwal);
		}
		if ($search != null) {
			$this->db->like('mapel', $search);
			$this->db->or_like('nama_guru', $search);
		}
		$level = $this->session->userdata('admin')['level'];
		$this->db->select('kelas_jadwal_pelajaran.*, kelas_setting.nama_kelas');
		$this->db->join('kelas_setting', 'kelas_setting.id = kelas_jadwal_pelajaran.id_kelas_setting');
		if ($level == 'Guru') {
			$this->db->where('kelas_jadwal_pelajaran.id_guru', $this->session->userdata('admin')['id_pegawai']);
		}
		$this->db->order_by('kelas_jadwal_pelajaran.jam_pelajaran_awal', 'ASC');
		$tahun = $this->db->get('kelas_jadwal_pelajaran')->result_array();


		return $tahun;
	}

	public function tambah()
	{

		$kelas_setting = $this->db->get_where('kelas_setting', ['id' => $this->input->post('id_kelas_setting')])->row_array();
		$mapel = $this->db->get_where('master_mata_pelajaran', ['id' => $this->input->post('id_mapel')])->row_array();
		$guru = $this->db->get_where('guru', ['id' => $this->input->post('id_guru')])->row_array();
		$jam_pelajaran_awal = $this->input->post('jam_pelajaran_awal');
		$jam_pelajaran_akhir = $this->input->post('jam_pelajaran_akhir');
		$hari = $this->input->post('hari');
		$ruangan = $this->input->post('ruangan');

		$awal = new DateTime($jam_pelajaran_awal);
		$akhir = new DateTime($jam_pelajaran_akhir);


		$selisih = $awal->diff($akhir);

		$jam_saja = $selisih->h;

		$data = [
			'id_kelas_setting' => $kelas_setting['id'] ?? '',
			'id_mapel' => $mapel['id'] ?? '',
			'mapel' => $mapel['mapel'] ?? '',
			'id_guru' => $guru['id'] ?? '',
			'nama_guru' => $guru['nama_guru'] ?? '',
			'jam_pelajaran_awal' => $jam_pelajaran_awal,
			'jam_pelajaran_akhir' => $jam_pelajaran_akhir,
			'hari' => $hari,
			'ruangan' => $ruangan,
			'jumlah_jam' => $jam_saja,

		];

		$response = $this->db->insert('kelas_jadwal_pelajaran', $data);
		if ($response) {
			$data = [
				'status' => true,
				'id_jadwal' => $kelas_setting['id'],
			];
		}
		return $data;
	}
	public function edit()
	{
		$id_kelas_jadwal_pelajaran = $this->input->post('id_jadwal_pelajaran');
		$kelas_setting = $this->db->get_where('kelas_setting', ['id' => $this->input->post('id_kelas_setting')])->row_array();
		$mapel = $this->db->get_where('master_mata_pelajaran', ['id' => $this->input->post('id_mapel')])->row_array();
		$guru = $this->db->get_where('guru', ['id' => $this->input->post('id_guru')])->row_array();
		$jam_pelajaran_awal = $this->input->post('jam_pelajaran_awal');
		$jam_pelajaran_akhir = $this->input->post('jam_pelajaran_akhir');
		$hari = $this->input->post('hari');
		$ruangan = $this->input->post('ruangan');

		$awal = new DateTime($jam_pelajaran_awal);
		$akhir = new DateTime($jam_pelajaran_akhir);


		$selisih = $awal->diff($akhir);

		$jam_saja = $selisih->h;

		$data = [
			'id_kelas_setting' => $kelas_setting['id'],
			'id_mapel' => $mapel['id'],
			'mapel' => $mapel['mapel'],
			'id_guru' => $guru['id'],
			'nama_guru' => $guru['nama_guru'],
			'jam_pelajaran_awal' => $jam_pelajaran_awal,
			'jam_pelajaran_akhir' => $jam_pelajaran_akhir,
			'hari' => $hari,
			'ruangan' => $ruangan,
			'jumlah_jam' => $jam_saja,

		];

		$response = $this->db->update('kelas_jadwal_pelajaran', $data, ['id' => $id_kelas_jadwal_pelajaran]);
		if ($response) {
			$data = [
				'status' => true,
				'id_jadwal' => $kelas_setting['id']
			];
		}
		return $data;
	}
	public function hapus()
	{
		$id = $this->input->post('id');
		$id_kelas_setting = $this->input->post('id_kelas_setting');

		$response = $this->db->delete('kelas_jadwal_pelajaran', ['id' => $id]);

		if ($response) {
			$data = [
				'status' => true,
				'id_jadwal' => $id_kelas_setting
			];
		}
		return $data;
	}

}
?>