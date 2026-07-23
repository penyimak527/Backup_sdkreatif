<?php
class M_kelas extends CI_Model
{

	protected $id_user;
	protected $nama_user;
	function __construct()
	{
		parent::__construct();
	}


	public function kelas_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('nama_kelas', $search);
		}
		$tahun = $this->db->get('kelas')->result_array();


		return $tahun;
	}
	public function kelas_setting_result()
	{
		$id_kelas_setting = $this->input->post('id_kelas_setting');


		$tahun = $this->db->get_where('kelas_siswa', ['id_kelas_setting' => $id_kelas_setting])->result_array();


		return $tahun;
	}
	public function siswa_result()
	{


		$siswa = $this->db->query("SELECT a.* FROM siswa a where a.id not in (select id_siswa from kelas_siswa)")->result_array();


		return $siswa;
	}

	public function tambah()
	{
		$kelas = $this->input->post('nama_kelas');
		$kode_kelas = $this->input->post('kode_kelas');

		$data = [
			'nama_kelas' => strtoupper($kelas),
			'kode_kelas' => $kode_kelas,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,


		];

		$response = $this->db->insert('kelas', $data);

		return $response;
	}

	public function edit()
	{
		$kode_kelas = $this->input->post('kode_kelas');
		$kelas = $this->input->post('nama_kelas');
		$id_kelas = $this->input->post('id_kelas');


		$data = [
			'nama_kelas' => strtoupper($kelas),
			'kode_kelas' => $kode_kelas,
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,

		];

		$response = $this->db->update('kelas', $data, ['id' => $id_kelas]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('kelas', ['id' => $id]);

		return $response;
	}

}
?>