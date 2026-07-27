<?php
class Laporan_izin_pegawai extends CI_Controller
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




			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  
					WHERE STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') AND a.status_approval !=0   Order by a.tgl_tidak_hadir asc")->result_array();
			$grouped_by_tanggal = [];

			foreach ($pegawai as $data) {
				$tanggal = $data['tgl_tidak_hadir'];
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

			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  WHERE MONTH(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $bulan 
			AND YEAR(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $tahun AND a.status_approval !=0
	  Order by a.tgl_tidak_hadir asc")->result_array();
			$grouped_by_tanggal = [];
			foreach ($pegawai as $data) {
				$tanggal = $data['tgl_tidak_hadir'];
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


			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  WHERE YEAR(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $tahun AND a.status_approval !=0
			  Order by a.tgl_tidak_hadir asc")->result_array();
			foreach ($pegawai as $data) {
				$tanggal = $data['tgl_tidak_hadir'];
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


		$this->load->view('admin/data_laporan/laporan_izin_pegawai', $data);
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
