<?php
class M_agenda_tahunan extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
	}


	public function agenda_tahunan_result()
	{

		$search = $this->input->post('search');
		$level = $this->session->userdata('admin')['level'];
		if ($level != 'Admin') {
			$this->db->where('id_pegawai', $this->session->userdata('admin')['id_pegawai']);
		}
		if ($search != null) {
			$this->db->like('nama_kegiatan', $search);
		}

		$agenda_tahunan = $this->db->get('agenda_tahunan')->result_array();
		return $agenda_tahunan;
	}

	public function pegawai_result()
	{

		$this->db->where('jabatan !=', 'Guru');
		$pegawai = $this->db->get('pegawai_jabatan')->result_array();
		return $pegawai;
	}


	public function tambah()
	{

		$periode = $this->db->get_where('kelas_setting', ['id_periode' => $this->input->post('id_periode')])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();
		$nama_kegiatan = $this->input->post('nama_kegiatan');
		$jenis_kegiatan = $this->input->post('jenis_kegiatan');
		$tanggal_mulai = $this->input->post('tanggal_mulai');
		$tanggal_selesai = $this->input->post('tanggal_selesai');
		$tempat = $this->input->post('tempat');
		$keterangan = $this->input->post('keterangan');
		$status = $this->input->post('status');


		$config['upload_path'] = 'storage/agenda_tahunan/';
		$config['allowed_types'] = 'pdf';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);
		$this->load->library('image_lib');
		$nama_file = 'default.jpg';
		if (!empty($_FILES['file']['name'])) {
			if (!$this->upload->do_upload('file')) {
				echo $this->upload->display_errors();
				return;
			} else {
				$data_upload_file = $this->upload->data();
				$nama_file = $data_upload_file['file_name'] ?? 'default.jpg';

			}
		}
		$data = [

			'nama_kegiatan' => $nama_kegiatan,
			'jenis_kegiatan' => $jenis_kegiatan,
			'tanggal_mulai' => $tanggal_mulai,
			'tanggal_selesai' => $tanggal_selesai,
			'tempat' => $tempat,
			'keterangan' => $keterangan,
			'status' => $status,
			'id_periode' => $periode['id_periode'] ?? '',
			'periode' => $periode['periode'] ?? '',
			'semester' => $periode['semester'] ?? '',
			'file' => $nama_file,
			'id_pegawai' => $pegawai['id'] ?? '',
			'pegawai' => $pegawai['nama_pegawai'] ?? '',
		];

		$response = $this->db->insert('agenda_tahunan', $data);

		return $response;
	}
	public function edit()
	{

		$id_agenda = $this->input->post('id_agenda');
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();
		$periode = $this->db->get_where('kelas_setting', ['id_periode' => $this->input->post('id_periode')])->row_array();
		$nama_kegiatan = $this->input->post('nama_kegiatan');
		$jenis_kegiatan = $this->input->post('jenis_kegiatan');
		$tanggal_mulai = $this->input->post('tanggal_mulai');
		$tanggal_selesai = $this->input->post('tanggal_selesai');
		$tempat = $this->input->post('tempat');
		$keterangan = $this->input->post('keterangan');
		$status = $this->input->post('status');


		$config['upload_path'] = 'storage/agenda_tahunan/';
		$config['allowed_types'] = 'pdf';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);
		$this->load->library('image_lib');


		$oldImage = $this->input->post('oldImage');

		$nama_file = $oldImage;
		if (!empty($_FILES['file']['name'])) {
			if (!$this->upload->do_upload('file')) {
				echo $this->upload->display_errors();
				return;
			} else {
				$data_upload_awal = $this->upload->data();
				$nama_file = $data_upload_awal['file_name'] ?? 'default.jpg';
				$path_file = $data_upload_awal['full_path'];




				if (!empty($oldImage) && file_exists('./storage/agenda_tahunan/' . $oldImage) && $oldImage !== 'default.jpg') {
					unlink('./storage/agenda_tahunan/' . $oldImage);
				}
			}
		}
		$data = [

			'nama_kegiatan' => $nama_kegiatan,
			'jenis_kegiatan' => $jenis_kegiatan,
			'tanggal_mulai' => $tanggal_mulai,
			'tanggal_selesai' => $tanggal_selesai,
			'tempat' => $tempat,
			'keterangan' => $keterangan,
			'status' => $status,
			'id_periode' => $periode['id_periode'],
			'periode' => $periode['periode'],
			'semester' => $periode['semester'],
			'file' => $nama_file,
			'id_pegawai' => $pegawai['id'],
			'pegawai' => $pegawai['nama_pegawai'],
		];

		$response = $this->db->update('agenda_tahunan', $data, ['id' => $id_agenda]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');
		$image = $this->db->get_where('agenda_tahunan', ['id' => $id])->row_array();
		if ($image['file'] !== 'default.jpg') {
			unlink('./storage/agenda_tahunan/' . $image['file']);
		}

		$response = $this->db->delete('agenda_tahunan', ['id' => $id]);

		return $response;
	}



}
?>
