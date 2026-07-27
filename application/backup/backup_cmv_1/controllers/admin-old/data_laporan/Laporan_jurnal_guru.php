<?php
class Laporan_jurnal_guru extends CI_Controller
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


		$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
		$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));

		$semester = $ambil['semester_jurnal'];
		$periode = $ambil['id_periode_jurnal'];
		$id_kelas = $ambil['id_kelas'];


		$jurnal_guru = $this->db->query("SELECT a.*,c.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			left join kelas_setting c on b.id_kelas_setting = c.id left join kelas d on c.id_kelas = d.id
			WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
			AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') 
			AND a.id_periode = '$periode' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
		$grouped_by_guru_mapel = [];

		foreach ($jurnal_guru as $row) {
			$nama_guru = $row['nama_guru'];
			$mapel = $row['mapel'];
			if (!isset($grouped_by_guru_mapel[$nama_guru])) {
				$grouped_by_guru_mapel[$nama_guru] = [];
			}
			if (!isset($grouped_by_guru_mapel[$nama_guru][$mapel])) {
				$grouped_by_guru_mapel[$nama_guru][$mapel] = [];
			}
			$grouped_by_guru_mapel[$nama_guru][$mapel][] = $row;
		}



		$periode = $this->db->get_where('master_tahun_ajaran', ['id' => $periode])->row_array();
		$kelas = $this->db->get_where('kelas', ['id' => $id_kelas])->row_array();
		$data = [
			'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
			'grouped_by_guru_mapel' => $grouped_by_guru_mapel,
			'semester' => $semester,
			'periode' => $periode['periode'],
			'kelas' => $kelas['nama_kelas'],
		];

		$this->load->view('admin/data_laporan/laporan_jurnal_guru', $data);
	}

}
