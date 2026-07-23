<?php
class M_izin_pegawai extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function izin_pegawai_result()
	{
		$search = $this->input->post('search');
		$level = $this->input->post('level');
		$id_pegawai = $this->input->post('id_pegawai');

		if (!empty($search)) {
			$this->db->like('nama_pegawai', $search);
		}

		if ($level === 'Admin') {
			$izin = $this->db->get('izin_pegawai')->result_array();
		} else {
			$izin = $this->db->get_where('izin_pegawai', ['id_pegawai' => $id_pegawai])->result_array();
		}

		return $izin;
	}

	public function pegawai_edit()
	{
		$id_pegawai = $this->input->post('id_pegawai');

		if (!$id_pegawai) return [];

		return $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
	}

	public function tambah()
	{
		$id_izin = $this->input->post('id_izin');
		$id_pegawai = $this->input->post('id_pegawai');
		$tgl_tidak_hadir = $this->input->post('tgl_tidak_hadir');
		$alasan_tidak_hadir = $this->input->post('alasan_tidak_hadir');
		$tanggal_input = date('d-m-Y');

		$izin = $this->db->get_where('master_izin', ['id' => $id_izin])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();

		if (!$izin || !$pegawai) return false;

		$data = [
			'id_master_izin' => $izin['id'],
			'keterangan' => $izin['nama_izin'],
			'id_pegawai' => $pegawai['id'],
			'nama_pegawai' => $pegawai['nama_pegawai'],
			'tgl_tidak_hadir' => $tgl_tidak_hadir,
			'alasan_tidak_hadir' => $alasan_tidak_hadir,
			'tanggal_input' => $tanggal_input,
			'status_approval' => 0,
		];

		return $this->db->insert('izin_pegawai', $data);
	}

	public function edit()
	{
		$id_izin_pegawai = $this->input->post('id_izin_pegawai');
		$id_izin = $this->input->post('id_izin');
		$id_pegawai = $this->input->post('id_pegawai');
		$tgl_tidak_hadir = $this->input->post('tgl_tidak_hadir');
		$alasan_tidak_hadir = $this->input->post('alasan_tidak_hadir');
		$tanggal_input = date('d-m-Y');

		$izin = $this->db->get_where('master_izin', ['id' => $id_izin])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();

		if (!$izin || !$pegawai) return false;

		$data = [
			'id_master_izin' => $izin['id'],
			'keterangan' => $izin['nama_izin'],
			'id_pegawai' => $pegawai['id'],
			'nama_pegawai' => $pegawai['nama_pegawai'],
			'tgl_tidak_hadir' => $tgl_tidak_hadir,
			'alasan_tidak_hadir' => $alasan_tidak_hadir,
			'tanggal_input' => $tanggal_input,
			'status_approval' => 0,
		];

		return $this->db->update('izin_pegawai', $data, ['id' => $id_izin_pegawai]);
	}

	public function hapus()
	{
		$id = $this->input->post('id');
		if (!$id) return false;

		return $this->db->delete('izin_pegawai', ['id' => $id]);
	}
}
?>
