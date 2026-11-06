<?php
class M_kelas extends CI_Model
{

	protected $id_user;
	protected $nama_user;
	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}


	public function kelas_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->group_start()->like('nama_kelas', $search)->or_like('kode_kelas', $search)->group_end();
		}
		if ($this->supports_soft_delete()) $this->db->where('deleted_at IS NULL', null, false);
		$this->db->order_by('nama_kelas', 'ASC')->order_by('kode_kelas', 'ASC');
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

		$this->db->where('id', $id_kelas);
		if ($this->supports_soft_delete()) $this->db->where('deleted_at IS NULL', null, false);
		$response = $this->db->update('kelas', $data);

		return $response;
	}
	public function hapus()
	{
		if (!$this->supports_soft_delete()) return false;
		$id = (int) $this->input->post('id');
		return $this->db->where('id', $id)->where('deleted_at IS NULL', null, false)->update('kelas', [
			'deleted_at' => date('Y-m-d H:i:s'), 'tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'),
			'id_user' => $this->id_user, 'nama_user' => $this->nama_user,
		]);
	}

	public function trash_result()
	{
		if (!$this->supports_soft_delete()) return [];
		return $this->db
			->select("k.id,k.nama_kelas,k.kode_kelas,k.deleted_at,k.nama_user,(SELECT COUNT(*) FROM kelas_setting ks WHERE CAST(ks.id_kelas AS UNSIGNED)=k.id) jumlah_setting,(SELECT COUNT(*) FROM kelas_jadwal_pelajaran jp WHERE jp.id_kelas=k.id) jumlah_jadwal", false)
			->from('kelas k')->where('k.deleted_at IS NOT NULL', null, false)
			->order_by('k.deleted_at', 'DESC')->get()->result_array();
	}

	public function restore()
	{
		if (!$this->supports_soft_delete()) return false;
		$id = (int) $this->input->post('id');
		return $this->db->where('id', $id)->where('deleted_at IS NOT NULL', null, false)->update('kelas', [
			'deleted_at' => null, 'tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'),
			'id_user' => $this->id_user, 'nama_user' => $this->nama_user,
		]);
	}

	public function supports_soft_delete()
	{
		return $this->db->field_exists('deleted_at', 'kelas');
	}

}
?>
