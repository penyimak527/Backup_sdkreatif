<?php
class Laporan_rekap_keterlambatan_pegawai extends CI_Controller
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
			$presensi_pegawai = $this->db->query("SELECT a.* FROM presensi_pegawai a  
					WHERE a.status = 'Terlambat' AND STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')    Order by a.tanggal asc")->result_array();
			$grouped_by_tanggal = [];

			foreach ($presensi_pegawai as $data) {
				$tanggal = $data['tanggal'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}


			$data = [
				'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Tanggal'
			];



		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];

			$presensi_pegawai = $this->db->query("SELECT a.* FROM presensi_pegawai a  WHERE a.status = 'Terlambat' AND MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan 
			AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun 
	  		Order by a.tanggal asc")->result_array();
			$grouped_by_tanggal = [];
			foreach ($presensi_pegawai as $data) {
				$tanggal = $data['tanggal'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}

			$data = [
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Bulan'
			];


		} else {
			$tahun = $ambil['single_filter_tahun'];
			$presensi_pegawai = $this->db->query("SELECT a.* FROM presensi_pegawai a  WHERE a.status = 'Terlambat' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = 
			$tahun Order by a.tanggal asc")->result_array();
			$grouped_by_tanggal = [];
			foreach ($presensi_pegawai as $data) {
				$tanggal = $data['tanggal'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}

			$data = [
				'judul' => $tahun,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Tahun'
			];

		}


		$this->load->view('admin/data_laporan/laporan_rekap_keterlambatan_pegawai', $data);
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
