<?php
class M_izin_pegawai extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
	}


	public function izin_pegawai_result()
	{

		$search = $this->input->post('search');


		$level = $this->session->userdata('admin')['level'];
		if ($search != null) {
			$this->db->like('nama_pegawai', $search);
		}
		if ($level == 'Admin') {

			$izin = $this->db->get('izin_pegawai')->result_array();
		} else {
			$izin = $this->db->get_where('izin_pegawai', ['id_pegawai' => $this->session->userdata('admin')['id_pegawai']])->result_array();
		}
		return $izin;
	}

	public function pegawai_edit()
	{

		$id_pegawai = $this->input->post('id_pegawai');


		$jabatan = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
		return $jabatan;
	}


	public function tambah()
	{



		$izin = $this->db->get_where('master_izin', ['id' => $this->input->post('id_izin')])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();

		$tgl_tidak_hadir = $this->input->post('tgl_tidak_hadir');
		$alasan_tidak_hadir = $this->input->post('alasan_tidak_hadir');
		$tanggal_input = date('d-m-Y');

		$data = [

			'id_master_izin' => $izin['id'] ?? '',
			'keterangan' => $izin['nama_izin'] ?? '',
			'id_pegawai' => $pegawai['id'] ?? '',
			'nama_pegawai' => $pegawai['nama_pegawai'] ?? '',
			'tgl_tidak_hadir' => $tgl_tidak_hadir,
			'alasan_tidak_hadir' => $alasan_tidak_hadir,
			'tanggal_input' => $tanggal_input,
			'status_approval' => 0,

		];
		$response = $this->db->insert('izin_pegawai', $data);


		return $response;
	}
	public function edit()
	{


		$id_pegawai = $this->input->post('id_izin_pegawai');

		$izin = $this->db->get_where('master_izin', ['id' => $this->input->post('id_izin')])->row_array();
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();

		$tgl_tidak_hadir = $this->input->post('tgl_tidak_hadir');
		$alasan_tidak_hadir = $this->input->post('alasan_tidak_hadir');
		$tanggal_input = date('d-m-Y');

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

		$response = $this->db->update('izin_pegawai', $data, ['id' => $id_pegawai]);


		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');

		$response = $this->db->delete('izin_pegawai', ['id' => $id]);

		return $response;
	}



}
?>