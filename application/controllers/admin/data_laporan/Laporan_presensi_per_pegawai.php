<?php
class Laporan_presensi_per_pegawai extends CI_Controller
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
		$id_pegawai = $ambil['id_pegawai_all'];
		if ($ambil['filter'] == 'tanggal') {
			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));
			$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan 
			WHERE id_pegawai = ?", [$id_pegawai])->row_array();
			
			$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
			$jabatan = $pegawai['jabatan'] ?? '-';
			$grouped_by_tanggal = [];
			$start = strtotime($dari_tanggal);
			$end = strtotime($sampai_tanggal);
			for ($current = $start; $current <= $end; $current = strtotime('+1 day', $current)) {
				$tanggal = date('d-m-Y', $current);
				$presensi_pegawai = $this->db->query("SELECT a.*, b.* FROM pegawai_jabatan a LEFT JOIN presensi_pegawai b ON b.id_pegawai = a.id_pegawai 
				WHERE a.id_pegawai = ? AND b.tanggal = ?", [$id_pegawai, $tanggal])->row_array();

				// CEK APAKAH ADA DATA
				if (empty($presensi_pegawai) || empty($presensi_pegawai['tanggal'])) {
					// TIDAK ADA PRESENSI
					$grouped_by_tanggal[$tanggal] = [
						'status' => '0',
						'tanggal' => $tanggal,
						'nama_pegawai' => $nama_pegawai,
						'jam_masuk' => null,
						'jam_terlambat' => null,
						'jam_pulang' => null,
						'status_absen' => 'Tidak Absen'
					];
				} else {
					// ADA PRESENSI
					$grouped_by_tanggal[$tanggal] = [
						'status' => '1',
						'tanggal' => $presensi_pegawai['tanggal'],
						'nama_pegawai' => $presensi_pegawai['nama_pegawai'],
						'jam_masuk' => $presensi_pegawai['waktu'] ?? null,
						'jam_terlambat' => $presensi_pegawai['jam_terlambat'] ?? null,
						'jam_pulang' => $presensi_pegawai['jam_pulang'] ?? null,
						'status_absen' => $presensi_pegawai['status'] ?? null,
					];
				}
			}
			$data = [
				'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Tanggal',
				'jabatan' => $jabatan
			];
		} else if ($ambil['filter'] == 'bulan') {
			// $bulan = sprintf("%02d", $ambil['filter_bulan']);
			// $tahun = $ambil['filter_tahun'];

			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
			$grouped_by_tanggal = [];

			for ($i = 1; $i <= $total_days; $i++) {

				$hari = sprintf("%02d", $i);
				$tanggal = $hari . '-' . $bulan . '-' . $tahun;

				$presensi_pegawai = $this->db->query("SELECT a.*, b.* FROM pegawai_jabatan a LEFT JOIN presensi_pegawai b on b.id_pegawai = a.id_pegawai 
				WHERE a.id_pegawai = ? AND b.tanggal = ?", [$id_pegawai, $tanggal])->row_array();

				if (empty($presensi_pegawai)) {
					$grouped_by_tanggal[$tanggal] = [
						'status' => '0',
						'tanggal' => $tanggal
					];
				} else {
					$grouped_by_tanggal[$tanggal] = [
						'status' => '1',
						'tanggal' => $presensi_pegawai['tanggal'],
						'nama_pegawai' => $presensi_pegawai['nama_pegawai'],
						'jam_masuk' => $presensi_pegawai['waktu'],
						'jam_terlambat' => $presensi_pegawai['jam_terlambat'],
						'jam_pulang' => $presensi_pegawai['jam_pulang'],
						'status_absen' => $presensi_pegawai['status'],
					];
				}
			}

			$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan 
			WHERE id_pegawai = ? ", [$id_pegawai])->row_array();

			$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
			$jabatan = $pegawai['jabatan'] ?? '-';
			$data = [
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Bulan',
				'jabatan' => $jabatan
			];

		} else {
			$tahun = $ambil['single_filter_tahun'];
			$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan 
			WHERE id_pegawai = ?", [$id_pegawai])->row_array();

			$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
			$jabatan = $pegawai['jabatan'] ?? '-';
			$grouped_by_tanggal = [];
			for ($bulan = 1; $bulan <= 12; $bulan++) {
				// Format bulan dengan leading zero
				$bulan_formatted = sprintf("%02d", $bulan);
				// Tentukan jumlah hari dalam bulan tersebut
				$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
				for ($hari = 1; $hari <= $total_days; $hari++) {
					$hari_formatted = sprintf("%02d", $hari);
					$tanggal = $hari_formatted . '-' . $bulan_formatted . '-' . $tahun;
					$presensi_pegawai = $this->db->query("SELECT a.*, b.* FROM pegawai_jabatan a LEFT JOIN presensi_pegawai b on b.id_pegawai = a.id_pegawai WHERE a.id_pegawai = ? AND b.tanggal = ? ", [$id_pegawai, $tanggal])->row_array();
					if (empty($presensi_pegawai) || empty($presensi_pegawai['tanggal'])) {
						$grouped_by_tanggal[$tanggal] = [
							'status' => '0',
							'tanggal' => $tanggal,
							'nama_pegawai' => $nama_pegawai,
							'jam_masuk' => null,
							'jam_terlambat' => null,
							'jam_pulang' => null,
							'status_absen' => 'Tidak Absen'
						];
					} else {
						$grouped_by_tanggal[$tanggal] = [
							'status' => '1',
							'tanggal' => $presensi_pegawai['tanggal'],
							'nama_pegawai' => $presensi_pegawai['nama_pegawai'],
							'jam_masuk' => $presensi_pegawai['waktu'] ?? null,
							'jam_terlambat' => $presensi_pegawai['jam_terlambat'] ?? null,
							'jam_pulang' => $presensi_pegawai['jam_pulang'] ?? null,
							'status_absen' => $presensi_pegawai['status'] ?? null,
						];
					}
				}
			}
			$data = [
				'judul' => $tahun,
				'grouped_by_tanggal' => $grouped_by_tanggal,
				'status' => 'Tahun',
				'jabatan' => $jabatan
			];
		}
		$this->load->view('admin/data_laporan/laporan_presensi_per_pegawai', $data);
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
