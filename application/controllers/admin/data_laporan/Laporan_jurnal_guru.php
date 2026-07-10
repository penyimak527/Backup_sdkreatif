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

		if ($ambil['filter'] == 'tanggal') {
			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));
			$semester = $ambil['semester_jurnal'];
			$periode = $ambil['id_periode_jurnal'];
			$id_kelas = $ambil['id_kelas'];

			if ($id_kelas == 'Semua') {

				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			  left join kelas d on b.id_kelas = d.id
				WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
				AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') 
				AND a.id_periode = '$periode' 
				AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			} else {
				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				 left join kelas d on b.id_kelas = d.id
				WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
				AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') 
				AND a.id_periode = '$periode' 
				AND b.id_kelas = '$id_kelas' 
				AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			}
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
				'status' => "Tanggal",
				'grouped_by_guru_mapel' => $grouped_by_guru_mapel,
				'semester' => $semester,
				'periode' => $periode['periode'] ?? '',
				'kelas' => $kelas['nama_kelas'] ?? 'Semua Kelas',
			];
		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			$semester = $ambil['semester_jurnal'];
			$periode = $ambil['id_periode_jurnal'];
			$id_kelas = $ambil['id_kelas'];

			if ($id_kelas == 'Semua') {

				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
		  left join kelas d on b.id_kelas = d.id
			WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$bulan' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun'  
			AND a.id_periode = '$periode' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			} else {
				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			 left join kelas d on b.id_kelas = d.id
			WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$bulan' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun'   
			AND a.id_periode = '$periode' 
			AND b.id_kelas = '$id_kelas' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			}
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
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'status' => "Bulan",
				'grouped_by_guru_mapel' => $grouped_by_guru_mapel,
				'semester' => $semester,
				'periode' => $periode['periode'] ?? '',
				'kelas' => $kelas['nama_kelas'] ?? 'Semua Kelas',
			];
		} else {
			$tahun = $ambil['single_filter_tahun'];
			$semester = $ambil['semester_jurnal'];
			$periode = $ambil['id_periode_jurnal'];
			$id_kelas = $ambil['id_kelas'];

			if ($id_kelas == 'Semua') {

				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
		  left join kelas d on b.id_kelas = d.id
			WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun'  
			AND a.id_periode = '$periode' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			} else {
				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			 left join kelas d on b.id_kelas = d.id
			WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun'   
			AND a.id_periode = '$periode' 
			AND b.id_kelas = '$id_kelas' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			}
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
				'judul' => $tahun,
				'status' => "Tahun",
				'grouped_by_guru_mapel' => $grouped_by_guru_mapel,
				'semester' => $semester,
				'periode' => $periode['periode'],
				'kelas' => $kelas['nama_kelas'] ?? 'Semua Kelas',
			];
		}


		$this->load->view('admin/data_laporan/laporan_jurnal_guru', $data);
	}

	public function getBulan($bulan)
	{
		$daftar_bulan = [
			1 => 'Januari',
			2 => 'Februari',
			3 => 'Maret',
			4 => 'April',
			5 => 'Mei',
			6 => 'Juni',
			7 => 'Juli',
			8 => 'Agustus',
			9 => 'September',
			10 => 'Oktober',
			11 => 'November',
			12 => 'Desember',
		];

		return $daftar_bulan[(int) $bulan] ?? 'Bulan tidak valid';
	}
}
