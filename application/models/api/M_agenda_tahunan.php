<?php
class M_agenda_tahunan extends CI_Model
{
	public function agenda_tahunan_result()
	{
		$search = $this->input->post('search');
		$level = $this->input->post('level'); // ganti dari session
		$id_pegawai = $this->input->post('id_pegawai');

		if ($level != 'Admin') {
			$this->db->where('id_pegawai', $id_pegawai);
		}

		if (!empty($search)) {
			$this->db->like('nama_kegiatan', $search);
		}

		return $this->db->get('agenda_tahunan')->result_array();
	}

	public function pegawai_result()
	{
		$this->db->where('jabatan !=', 'Guru');
		return $this->db->get('pegawai_jabatan')->result_array();
	}

	public function tambah()
	{
		$periode = $this->db->get_where('kelas_setting', ['id_periode' => $this->input->post('id_periode')])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();

		$config['upload_path'] = 'storage/agenda_tahunan/';
		$config['allowed_types'] = 'pdf';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);
		$this->load->library('image_lib');

		$nama_file = 'default.jpg';
		if (!empty($_FILES['file']['name'])) {
			if (!$this->upload->do_upload('file')) {
				return ['status' => false, 'message' => $this->upload->display_errors()];
			}
			$data_upload = $this->upload->data();
			$nama_file = $data_upload['file_name'] ?? 'default.jpg';
		}

		$data = [
			'nama_kegiatan'   => $this->input->post('nama_kegiatan'),
			'jenis_kegiatan'  => $this->input->post('jenis_kegiatan'),
			'tanggal_mulai'   => $this->input->post('tanggal_mulai'),
			'tanggal_selesai' => $this->input->post('tanggal_selesai'),
			'tempat'          => $this->input->post('tempat'),
			'keterangan'      => $this->input->post('keterangan'),
			'status'          => $this->input->post('status'),
			'id_periode'      => $periode['id_periode'] ?? '',
			'periode'         => $periode['periode'] ?? '',
			'semester'        => $periode['semester'] ?? '',
			'file'            => $nama_file,
			'id_pegawai'      => $pegawai['id'] ?? '',
			'pegawai'         => $pegawai['nama_pegawai'] ?? '',
		];

		$insert = $this->db->insert('agenda_tahunan', $data);
		return ['status' => $insert];
	}

	public function edit()
	{
		$id_agenda = $this->input->post('id_agenda');
		$periode = $this->db->get_where('kelas_setting', ['id_periode' => $this->input->post('id_periode')])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();

		$config['upload_path'] = 'storage/agenda_tahunan/';
		$config['allowed_types'] = 'pdf';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);
		$this->load->library('image_lib');

		$oldImage = $this->input->post('oldImage');
		$nama_file = $oldImage;

		if (!empty($_FILES['file']['name'])) {
			if (!$this->upload->do_upload('file')) {
				return ['status' => false, 'message' => $this->upload->display_errors()];
			}
			$data_upload = $this->upload->data();
			$nama_file = $data_upload['file_name'];

			if (!empty($oldImage) && file_exists('./storage/agenda_tahunan/' . $oldImage) && $oldImage !== 'default.jpg') {
				unlink('./storage/agenda_tahunan/' . $oldImage);
			}
		}

		$data = [
			'nama_kegiatan'   => $this->input->post('nama_kegiatan'),
			'jenis_kegiatan'  => $this->input->post('jenis_kegiatan'),
			'tanggal_mulai'   => $this->input->post('tanggal_mulai'),
			'tanggal_selesai' => $this->input->post('tanggal_selesai'),
			'tempat'          => $this->input->post('tempat'),
			'keterangan'      => $this->input->post('keterangan'),
			'status'          => $this->input->post('status'),
			'id_periode'      => $periode['id_periode'] ?? '',
			'periode'         => $periode['periode'] ?? '',
			'semester'        => $periode['semester'] ?? '',
			'file'            => $nama_file,
			'id_pegawai'      => $pegawai['id'] ?? '',
			'pegawai'         => $pegawai['nama_pegawai'] ?? '',
		];

		$update = $this->db->update('agenda_tahunan', $data, ['id' => $id_agenda]);
		return ['status' => $update];
	}

	public function hapus()
	{
		$id = $this->input->post('id');
		$agenda = $this->db->get_where('agenda_tahunan', ['id' => $id])->row_array();

		if (!empty($agenda['file']) && $agenda['file'] !== 'default.jpg' && file_exists('./storage/agenda_tahunan/' . $agenda['file'])) {
			unlink('./storage/agenda_tahunan/' . $agenda['file']);
		}

		$delete = $this->db->delete('agenda_tahunan', ['id' => $id]);
		return ['status' => $delete];
	}
}
?>
