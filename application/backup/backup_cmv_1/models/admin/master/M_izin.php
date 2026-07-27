<?php
class M_izin extends CI_Model
{

	protected $id_user;
	protected $nama_user;
	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}


	public function izin_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('nama_izin', $search);
		}
		$tahun = $this->db->get('master_izin')->result_array();


		return $tahun;
	}

	public function tambah()
	{
		$izin = $this->input->post('nama_izin');

		$data = [
			'nama_izin' => $izin,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,

		];

		$response = $this->db->insert('master_izin', $data);

		return $response;
	}
	public function edit()
	{
		$izin = $this->input->post('nama_izin');
		$id_izin = $this->input->post('id_izin');


		$data = [
			'nama_izin' => $izin,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,

		];

		$response = $this->db->update('master_izin', $data, ['id' => $id_izin]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('master_izin', ['id' => $id]);

		return $response;
	}

}
?>