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



		if ($ambil['filter'] == 'tanggal') {

			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));
			$guru = $ambil['id_guru'];
			$semester = $ambil['semester'];
			$periode = $ambil['id_periode'];


			$jurnal_guru = $this->db->query("SELECT a.*,b.kelas,d.kode_kelas FROM jurnal_guru a 
			left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				 left join kelas d on b.id_kelas = d.id
			 WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') >= STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND STR_TO_DATE(a.tanggal, '%d-%m-%Y') <= STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')  AND a.id_guru = '$guru' AND a.id_periode = '$periode' AND a.semester = '$semester' ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC")->result_array();

			$grouped_tanggal = [];
			foreach ($jurnal_guru as $j) {
				$tanggal = $j['tanggal'];
				$kegiatan = $j['kegiatan'];
				if (!isset($grouped_tanggal[$tanggal])) {
					$grouped_tanggal[$tanggal] = [];
				}
				$grouped_tanggal[$tanggal][] = $j;

				if (!isset($grouped_tanggal[$tanggal]['kegiatan'])) {
					$grouped_tanggal[$tanggal]['kegiatan'] = [];
				}
				$grouped_tanggal[$tanggal]['kegiatan'][] = $kegiatan;

			}


			$guru = $this->db->get_where('guru', ['id' => $guru])->row_array();

			$data = [
				'judul' => 'Laporan Jurnal Harian',
				'jurnal_guru' => $grouped_tanggal,
				'guru' => $guru,
				'cetak' => $ambil['print']
			];
		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			$guru = $ambil['id_guru'];
			$semester = $ambil['semester'];
			$periode = $ambil['id_periode'];


			$jurnal_guru = $this->db->query("SELECT a.*,b.kelas,d.kode_kelas FROM jurnal_guru a 
			left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				 left join kelas d on b.id_kelas = d.id
			 WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$bulan' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun' AND a.id_guru = '$guru' AND a.id_periode = '$periode' AND a.semester = '$semester'
			 ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC
			 ")->result_array();

			$grouped_bulan = [];
			foreach ($jurnal_guru as $j) {
				$tanggal = $j['tanggal'];
				$kegiatan = $j['kegiatan'];
				if (!isset($grouped_bulan[$tanggal])) {
					$grouped_bulan[$tanggal] = [];
				}
				$grouped_bulan[$tanggal][] = $j;

				if (!isset($grouped_bulan[$tanggal]['kegiatan'])) {
					$grouped_bulan[$tanggal]['kegiatan'] = [];
				}
				$grouped_bulan[$tanggal]['kegiatan'][] = $kegiatan;

			}



			$guru = $this->db->get_where('guru', ['id' => $guru])->row_array();

			$data = [
				'judul' => 'Laporan Jurnal Harian',
				'jurnal_guru' => $grouped_bulan,
				'guru' => $guru
			];
		} else {
			$tahun = $ambil['single_filter_tahun'];
			$guru = $ambil['id_guru'];
			$semester = $ambil['semester'];
			$periode = $ambil['id_periode'];


			$jurnal_guru = $this->db->query("SELECT a.*,b.kelas,d.kode_kelas FROM jurnal_guru a 
			left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				 left join kelas d on b.id_kelas = d.id
			 WHERE  YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun' AND a.id_guru = '$guru' AND a.id_periode = '$periode' AND a.semester = '$semester'
			  ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC
			 ")->result_array();

			$grouped_tahun = [];
			foreach ($jurnal_guru as $j) {
				$tanggal = $j['tanggal'];
				$kegiatan = $j['kegiatan'];
				if (!isset($grouped_tahun[$tanggal])) {
					$grouped_tahun[$tanggal] = [];
				}
				$grouped_tahun[$tanggal][] = $j;

				if (!isset($grouped_tahun[$tanggal]['kegiatan'])) {
					$grouped_tahun[$tanggal]['kegiatan'] = [];
				}
				$grouped_tahun[$tanggal]['kegiatan'][] = $kegiatan;

			}



			$guru = $this->db->get_where('guru', ['id' => $guru])->row_array();

			$data = [
				'judul' => 'Laporan Jurnal Harian',
				'jurnal_guru' => $grouped_tahun,
				'guru' => $guru
			];
		}

		$this->load->view('admin/data_laporan/laporan_jurnal_harian', $data);
	}

}
