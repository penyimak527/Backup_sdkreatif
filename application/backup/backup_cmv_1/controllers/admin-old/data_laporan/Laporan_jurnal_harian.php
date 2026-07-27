<?php
class Laporan_jurnal_harian extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		header('Access-Control-Allow-Origin: *');
	}

	public function print_laporan()
	{
		$json = file_get_contents('php://input');
		$ambil = json_decode($json, true);


		$tanggal = date('d-m-Y', strtotime($ambil['tanggal_jurnal']));
		$guru = $ambil['id_guru'];
		$semester = $ambil['semester'];
		$periode = $ambil['id_periode'];


		$jurnal_guru = $this->db->query("SELECT a.*,c.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			left join kelas_setting c on b.id_kelas_setting = c.id left join kelas d on c.id_kelas = d.id
		 WHERE a.tanggal = '$tanggal' AND a.id_guru = '$guru' AND a.id_periode = '$periode' AND a.semester = '$semester'")->result_array();

		$guru = $this->db->get_where('guru', ['id' => $guru])->row_array();

		$data = [
			'judul' => 'Laporan Jurnal Harian',
			'jurnal_guru' => $jurnal_guru,
			'tanggal' => $tanggal,
			'guru' => $guru
		];

		$this->load->view('admin/data_laporan/laporan_jurnal_harian', $data);
	}

}
