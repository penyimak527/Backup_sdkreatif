<?php
class M_izin extends CI_Model
{
	public function izin_result()
	{
		$search = $this->input->post('search');

		if (!empty($search)) {
			$this->db->like('nama_izin', $search);
		}

		return $this->db->get('master_izin')->result_array();
	}

	public function tambah()
	{
		$izin = $this->input->post('nama_izin');
		$id_user = $this->input->post('id_user'); // ambil dari Flutter
		$nama_user = $this->input->post('nama_user');

		$data = [
			'nama_izin' => $izin,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $id_user,
			'nama_user' => $nama_user,
		];

		$this->db->insert('master_izin', $data);
		return ['insert_id' => $this->db->insert_id()];
	}

	public function edit()
	{
		$id_izin = $this->input->post('id_izin');
		$izin = $this->input->post('nama_izin');
		$id_user = $this->input->post('id_user');
		$nama_user = $this->input->post('nama_user');

		$data = [
			'nama_izin' => $izin,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s'),
			'id_user' => $id_user,
			'nama_user' => $nama_user,
		];

		$this->db->where('id', $id_izin);
		return $this->db->update('master_izin', $data);
	}

	public function hapus()
	{
		$id = $this->input->post('id');
		$this->db->where('id', $id);
		return $this->db->delete('master_izin');
	}
}
?>
