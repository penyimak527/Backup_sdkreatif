<?php
class M_kode_akun extends CI_Model
{

	protected $id_user;
	protected $nama_user;
	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}


	public function kode_akun_result()
	{
		$search = $this->input->post('search');
		$jenis = $this->input->post('jenis');

		if ($search != null) {
			$this->db->like('keterangan', $search);
		}
		if ($jenis != null) {
			$this->db->like('jenis', $jenis);
		}
		$kode_akun = $this->db->get('kode_akun')->result_array();
		return $kode_akun;
	}

	public function tambah()
	{
		$jenis = $this->input->post('jenis');
        $keterangan = $this->input->post('keterangan');

		$data = [
			'jenis' => $jenis,
			'keterangan'    => $keterangan
		];

		$response = $this->db->insert('kode_akun', $data);

		return $response;
	}
	public function edit()
	{
		$id_kode_akun = $this->input->post('id_kode_akun');
		$jenis = $this->input->post('jenis');
		$keterangan = $this->input->post('keterangan');


		$data = [
			'jenis' => $jenis,
			'keterangan' => $keterangan
		];

		$response = $this->db->update('kode_akun', $data, ['id' => $id_kode_akun]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');
		$response = $this->db->delete('kode_akun', ['id' => $id]);
		return $response;
	}

}
?>