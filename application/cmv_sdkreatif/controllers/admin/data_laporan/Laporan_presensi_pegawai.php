<?php
class Laporan_presensi_pegawai extends CI_Controller
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
			$tampil = $ambil['pegawai_all_absen'];
			$pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan ORDER BY id asc")->result_array();
			$grouped_by_tanggal = [];
			if ($tampil == 'tampil' || $tampil == '') {
				$start = strtotime($dari_tanggal);
				$end = strtotime($sampai_tanggal);
				for ($current = $start; $current <= $end; $current = strtotime('+1 day', $current)) {
					$tanggal = date('d-m-Y', $current);
					foreach ($pegawai_list as $pegawai) {
						$presensi = $this->db->query("
            SELECT * FROM presensi_pegawai
            WHERE id_pegawai = ? AND tanggal = ?
        ", [$pegawai['id_pegawai'], $tanggal])->row_array();

						$grouped_by_tanggal[$tanggal][] = [
							'nama_pegawai' => $pegawai['nama_pegawai'],
							'jabatan' => $pegawai['jabatan'],
							'status' => $presensi ? '1' : '0',
							'jam_masuk' => $presensi['waktu'] ?? null,
							'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
							'jam_pulang' => $presensi['jam_pulang'] ?? null,
							'status_absen' => $presensi['status'] ?? null
						];
					}
				}
			} else {
				$presensi = $this->db->query("SELECT a.* FROM presensi_pegawai a WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') ORDER BY a.id asc")->result_array();
				foreach ($presensi as $key => $data) {
					$tanggal = $data['tanggal'];
					$grouped_by_tanggal[$tanggal][] = [
						'nama_pegawai' => $data['nama_pegawai'],
						'jabatan' => $data['jabatan'],
						'status' => $data ? '1' : '0',
						'jam_masuk' => $data['waktu'] ?? null,
						'jam_terlambat' => $data['jam_terlambat'] ?? null,
						'jam_pulang' => $data['jam_pulang'] ?? null,
						'status_absen' => $data['status'] ?? null
					];
				}
			}
			$data = [
				'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Tanggal',
				'tampil' => 'Tampil',
				'tampil_pegawai' => $tampil
			];

		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			$tampil = $ambil['pegawai_all_absen'];
			$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
			$pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan ORDER BY id asc")->result_array();
			$grouped_by_tanggal = [];
			if ($tampil == 'tampil' || $tampil == '') {
				for ($i = 1; $i <= $total_days; $i++) {
					$hari = sprintf("%02d", $i);
					$tanggal = $hari . '-' . $bulan . '-' . $tahun;

					foreach ($pegawai_list as $pegawai) {
						$presensi = $this->db->query("
            SELECT * FROM presensi_pegawai 
            WHERE id_pegawai = ? AND tanggal = ?
        ", [$pegawai['id_pegawai'], $tanggal])->row_array();

						$grouped_by_tanggal[$tanggal][] = [
							'nama_pegawai' => $pegawai['nama_pegawai'],
							'jabatan' => $pegawai['jabatan'],
							'status' => $presensi ? '1' : '0',
							'jam_masuk' => $presensi['waktu'] ?? null,
							'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
							'jam_pulang' => $presensi['jam_pulang'] ?? null,
							'status_absen' => $presensi['status'] ?? null
						];
					}
				}

			} else {
				$presensi = $this->db->query("SELECT a.* FROM presensi_pegawai a WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun ORDER BY a.id asc")->result_array();
				foreach ($presensi as $key => $data) {
					$tanggal = $data['tanggal'];
					$grouped_by_tanggal[$tanggal][] = [
						'nama_pegawai' => $data['nama_pegawai'],
						'jabatan' => $data['jabatan'],
						'status' => $data ? '1' : '0',
						'jam_masuk' => $data['waktu'] ?? null,
						'jam_terlambat' => $data['jam_terlambat'] ?? null,
						'jam_pulang' => $data['jam_pulang'] ?? null,
						'status_absen' => $data['status'] ?? null
					];
				}

			}

			$data = [
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Bulan',
				'tampil' => 'Tampil',
				'tampil_pegawai' => $tampil
			];

		} else {
			$tahun = $ambil['single_filter_tahun'];
			$tampil = $ambil['pegawai_all_absen'];
			$start = new DateTime("01-01-$tahun");
			$end = new DateTime("31-12-$tahun");
			$end->modify('+1 day'); // agar 31-12 ikut
			$grouped_by_tanggal = [];
			$pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan ORDER BY id asc")->result_array();
			$presensi_raw = $this->db->query("
			    SELECT id_pegawai, tanggal, waktu, jam_terlambat, jam_pulang, status
			    FROM presensi_pegawai
			    WHERE tanggal LIKE '%-$tahun'
			")->result_array();
			$presensi_index = [];
			foreach ($presensi_raw as $p) {
				$presensi_index[$p['tanggal']][$p['id_pegawai']] = $p;
			}

			if ($tampil == 'tampil' || $tampil == '') {
				$period = new DatePeriod($start, new DateInterval("P1D"), $end);
				foreach ($period as $date) {
					$tanggal = $date->format('d-m-Y');

					foreach ($pegawai_list as $pegawai) {
						$presensi = $presensi_index[$tanggal][$pegawai['id_pegawai']] ?? null;

						$grouped_by_tanggal[$tanggal][] = [
							'nama_pegawai' => $pegawai['nama_pegawai'],
							'jabatan' => $pegawai['jabatan'],
							'status' => $presensi ? '1' : '0',
							'jam_masuk' => $presensi['waktu'] ?? null,
							'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
							'jam_pulang' => $presensi['jam_pulang'] ?? null,
							'status_absen' => $presensi['status'] ?? null
						];
					}
				}
				// $period = new DatePeriod($start, new DateInterval('P1D'), $end);
				// foreach ($period as $date) {
				// 	$tanggal = $date->format('d-m-Y');
				// 	foreach ($pegawai_list as $key => $pegawai) {
				// 		$presensi = $this->db->query("SELECT * FROM presensi_pegawai WHERE id_pegawai = ? AND tanggal = ?  ", [$pegawai['id_pegawai'], $tanggal])->row_array();
				// 		$grouped_by_tanggal[$tanggal][] = [
				// 			'nama_pegawai' => $pegawai['nama_pegawai'],
				// 			'jabatan' => $pegawai['jabatan'],
				// 			'status' => $presensi ? '1' : '0',
				// 			'jam_masuk' => $presensi['waktu'] ?? null,
				// 			'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
				// 			'jam_pulang' => $presensi['jam_pulang'] ?? null,
				// 			'status_absen' => $presensi['status'] ?? null,
				// 		];
				// 	}
				// }

			} else {
				$presensi = $this->db->query("SELECT a.* FROM presensi_pegawai a WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun ORDER BY a.id asc")->result_array();
				foreach ($presensi as $key => $data) {
					$tanggal = $data['tanggal'];
					$grouped_by_tanggal[$tanggal][] = [
						'nama_pegawai' => $data['nama_pegawai'],
						'jabatan' => $data['jabatan'],
						'status' => $data ? '1' : '0',
						'jam_masuk' => $data['waktu'] ?? null,
						'jam_terlambat' => $data['jam_terlambat'] ?? null,
						'jam_pulang' => $data['jam_pulang'] ?? null,
						'status_absen' => $data['status'] ?? null
					];
				}
			}
			$data = [
				'judul' => "Presensi Tahun $tahun",
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Tahun',
				'tampil' => 'Tampil',
				'tampil_pegawai' => $tampil
			];
		}
		$this->load->view('admin/data_laporan/laporan_presensi_pegawai', $data);
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
