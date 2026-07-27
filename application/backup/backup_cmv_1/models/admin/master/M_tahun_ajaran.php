<?php
class M_tahun_ajaran extends CI_Model
{
	protected $id_user;
	protected $nama_user;

	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}
	public function tahun_ajaran_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('periode', $search);
		}
		$tahun = $this->db->get('master_tahun_ajaran')->result_array();


		return $tahun;
	}

	public function tambah()
	{
		$periode = $this->input->post('periode');
		$tanggal_awal_semester_ganjil = $this->input->post('tanggal_awal_semester_ganjil');
		$tanggal_awal_semester_genap = $this->input->post('tanggal_awal_semester_genap');
		$tanggal_akhir_semester_ganjil = $this->input->post('tanggal_akhir_semester_ganjil');
		$tanggal_akhir_semester_genap = $this->input->post('tanggal_akhir_semester_genap');

		$data = [
			'periode' => $periode,
			'status' => 'Tidak Aktif',
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,
			'tanggal_awal_semester_ganjil' => $tanggal_awal_semester_ganjil == '' ? null : $tanggal_awal_semester_ganjil,
			'tanggal_awal_semester_genap' => $tanggal_awal_semester_genap == '' ? null : $tanggal_awal_semester_genap,
			'tanggal_akhir_semester_ganjil' => $tanggal_akhir_semester_ganjil == '' ? null : $tanggal_akhir_semester_ganjil,
			'tanggal_akhir_semester_genap' => $tanggal_akhir_semester_genap == '' ? null : $tanggal_akhir_semester_genap,
		];

		$response = $this->db->insert('master_tahun_ajaran', $data);

		return $response;
	}
	public function edit()
	{
		$periode = $this->input->post('periode');
		$id_periode = $this->input->post('id_periode');
		$tanggal_awal_semester_ganjil = $this->input->post('tanggal_awal_semester_ganjil');
		$tanggal_awal_semester_genap = $this->input->post('tanggal_awal_semester_genap');
		$tanggal_akhir_semester_ganjil = $this->input->post('tanggal_akhir_semester_ganjil');
		$tanggal_akhir_semester_genap = $this->input->post('tanggal_akhir_semester_genap');

		$data = [
			'periode' => $periode,
			'id_user' => $this->id_user,
			'nama_user' => $this->nama_user,
			'tanggal_awal_semester_ganjil' => $tanggal_awal_semester_ganjil == '' ? null : $tanggal_awal_semester_ganjil,
			'tanggal_awal_semester_genap' => $tanggal_awal_semester_genap == '' ? null : $tanggal_awal_semester_genap,
			'tanggal_akhir_semester_ganjil' => $tanggal_akhir_semester_ganjil == '' ? null : $tanggal_akhir_semester_ganjil,
			'tanggal_akhir_semester_genap' => $tanggal_akhir_semester_genap == '' ? null : $tanggal_akhir_semester_genap,
		];

		$response = $this->db->update('master_tahun_ajaran', $data, ['id' => $id_periode]);

		return $response;
	}
	public function update_status()
	{
		$id_periode = $this->input->post('id', true);


		$this->db->update('master_tahun_ajaran', ['status' => 'Tidak Aktif']);


		$this->db->where('id', $id_periode);
		$response = $this->db->update('master_tahun_ajaran', ['status' => 'Aktif']);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('master_tahun_ajaran', ['id' => $id]);

		return $response;
	}

}
?>