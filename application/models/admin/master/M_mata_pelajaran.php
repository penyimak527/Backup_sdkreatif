<?php
class M_mata_pelajaran extends CI_Model
{

	protected $id_user;
	protected $nama_user;

	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}


	public function mata_pelajaran_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('mapel', $search);
		}
		$tahun = $this->db->get('master_mata_pelajaran')->result_array();


		return $tahun;
	}

	public function tambah()
	{
		$mapel = $this->input->post('mapel');

		$data = [
			'mapel' => $mapel,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,


		];

		$response = $this->db->insert('master_mata_pelajaran', $data);

		return $response;
	}
	public function edit()
	{
		$mapel = $this->input->post('mapel');
		$id_mapel = $this->input->post('id_mapel');


		$data = [
			'mapel' => $mapel,
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,

		];

		$response = $this->db->update('master_mata_pelajaran', $data, ['id' => $id_mapel]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('master_mata_pelajaran', ['id' => $id]);

		return $response;
	}

}
?>