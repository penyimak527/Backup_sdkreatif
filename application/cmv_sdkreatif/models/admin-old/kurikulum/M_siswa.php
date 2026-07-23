<?php
class M_siswa extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function siswa_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('nama_lengkap', $search);
		}
		$tahun = $this->db->get('siswa')->result_array();


		return $tahun;
	}

	public function tambah()
	{
		$nama_lengkap = $this->input->post('nama_lengkap');
		$nis = $this->input->post('nis');
		$jk = $this->input->post('jk');
		$tempat_lahir = $this->input->post('tempat_lahir');
		$tanggal_lahir = $this->input->post('tanggal_lahir');
		$alamat_siswa = $this->input->post('alamat_siswa');
		$nama_ayah = $this->input->post('nama_ayah');
		$pekerjaan_ayah = $this->input->post('pekerjaan_ayah');
		$telepon_ayah = $this->input->post('telepon_ayah');
		$alamat_ayah = $this->input->post('alamat_ayah');
		$usia_ayah = $this->input->post('usia_ayah');
		$nama_ibu = $this->input->post('nama_ibu');
		$pekerjaan_ibu = $this->input->post('pekerjaan_ibu');
		$telepon_ibu = $this->input->post('telepon_ibu');
		$alamat_ibu = $this->input->post('alamat_ibu');
		$usia_ibu = $this->input->post('usia_ibu');

		$data = [
			'nama_lengkap' => $nama_lengkap,
			'nis' => $nis,
			'jk' => $jk,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'alamat_siswa' => $alamat_siswa,
			'nama_ayah' => $nama_ayah,
			'pekerjaan_ayah' => $pekerjaan_ayah,
			'telepon_ayah' => $telepon_ayah,
			'alamat_ayah' => $alamat_ayah,
			'usia_ayah' => $usia_ayah,
			'nama_ibu' => $nama_ibu,
			'pekerjaan_ibu' => $pekerjaan_ibu,
			'telepon_ibu' => $telepon_ibu,
			'alamat_ibu' => $alamat_ibu,
			'usia_ibu' => $usia_ibu,
		];

		$response = $this->db->insert('siswa', $data);

		return $response;
	}
	public function edit()
	{
		$id_siswa = $this->input->post('id');
		$nama_lengkap = $this->input->post('nama_lengkap');
		$nis = $this->input->post('nis');
		$jk = $this->input->post('jk');
		$tempat_lahir = $this->input->post('tempat_lahir');
		$tanggal_lahir = $this->input->post('tanggal_lahir');
		$alamat_siswa = $this->input->post('alamat_siswa');
		$nama_ayah = $this->input->post('nama_ayah');
		$pekerjaan_ayah = $this->input->post('pekerjaan_ayah');
		$telepon_ayah = $this->input->post('telepon_ayah');
		$alamat_ayah = $this->input->post('alamat_ayah');
		$usia_ayah = $this->input->post('usia_ayah');
		$nama_ibu = $this->input->post('nama_ibu');
		$pekerjaan_ibu = $this->input->post('pekerjaan_ibu');
		$telepon_ibu = $this->input->post('telepon_ibu');
		$alamat_ibu = $this->input->post('alamat_ibu');
		$usia_ibu = $this->input->post('usia_ibu');

		$data = [
			'nama_lengkap' => $nama_lengkap,
			'nis' => $nis,
			'jk' => $jk,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'alamat_siswa' => $alamat_siswa,
			'nama_ayah' => $nama_ayah,
			'pekerjaan_ayah' => $pekerjaan_ayah,
			'telepon_ayah' => $telepon_ayah,
			'alamat_ayah' => $alamat_ayah,
			'usia_ayah' => $usia_ayah,
			'nama_ibu' => $nama_ibu,
			'pekerjaan_ibu' => $pekerjaan_ibu,
			'telepon_ibu' => $telepon_ibu,
			'alamat_ibu' => $alamat_ibu,
			'usia_ibu' => $usia_ibu,
		];

		$response = $this->db->update('siswa', $data, ['id' => $id_siswa]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('siswa', ['id' => $id]);

		return $response;
	}

}
?>
