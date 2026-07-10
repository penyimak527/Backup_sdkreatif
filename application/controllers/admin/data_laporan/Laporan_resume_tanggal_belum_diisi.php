<?php
class Laporan_resume_tanggal_belum_diisi extends CI_Controller
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

		$id_pegawai = $ambil['id_pegawai'];

		$laporan = $ambil['laporan'];



		if ($ambil['filter'] == 'tanggal') {
			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));

			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();


			if ($pegawai == null) {
				

		if ($laporan == 'Karyawan') {
				$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan != 'Guru'")->result_array();
				$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

					$sql = "   SELECT a.*
						FROM jurnal_pegawai a
						WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') 
							AND STR_TO_DATE(?, '%d-%m-%Y')  
							Order by  STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC,
								STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
						";
					$rows = $this->db->query($sql, [$dari_tanggal, $sampai_tanggal])->result_array();
					 
					$start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
					$end   = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
					$endInc = (clone $end); // DatePeriod eksklusif di end, jadi tambah 1 hari
					$endInc->modify('+1 day');

					$allDates = [];
					$period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
					foreach ($period as $dt) {
						$allDates[] = $dt->format('d-m-Y');
					}
					
					$filled = []; 
					$meta=[];
					foreach ($rows as $r) {
						if (empty($r['tanggal_input'])) continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal_input']);
						if (!$dt) continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_pegawai'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode']  = $r['periode'] ?? '-';
					}

					
					$missing = [];  
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);
					
						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode'  => $meta[$nama]['periode']  ?? '-',
								'tanggal'  => $kosong
							];
						}
					} 

					$jurnal_pegawai = $missing;
			}else{
				$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan = 'Guru'")->result_array();
				$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

					$sql = "   SELECT a.*,b.nama_kelas,b.kode_kelas
						FROM jurnal_guru a left join kelas b on a.id_kelas=b.id
						WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') 
							AND STR_TO_DATE(?, '%d-%m-%Y')  
							Order by  STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC,
								STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
						";
					$rows = $this->db->query($sql, [$dari_tanggal, $sampai_tanggal])->result_array();
					 
					$start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
					$end   = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
					$endInc = (clone $end);  
					$endInc->modify('+1 day');

					$allDates = [];
					$period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
					foreach ($period as $dt) {
						$allDates[] = $dt->format('d-m-Y');
					}
					
					$filled = []; 
					$meta=[];
					foreach ($rows as $r) {
						if (empty($r['tanggal'])) continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal']);
						if (!$dt) continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_guru'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode']  = $r['periode'] ?? '-';
						$meta[$nama]['mapel']  = $r['mapel'] ?? '-';
						$meta[$nama]['nama_kelas']  = $r['nama_kelas'] ?? '-';
						$meta[$nama]['kode_kelas']  = $r['kode_kelas'] ?? '-';
					}

					
					$missing = [];  
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);
					
						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode'  => $meta[$nama]['periode']  ?? '-',
								'mapel'  => $meta[$nama]['mapel']  ?? '-',
								'nama_kelas'  => $meta[$nama]['nama_kelas']  ?? '-',
								'kode_kelas'  => $meta[$nama]['kode_kelas']  ?? '-',
								'tanggal'  => $kosong
							];
						}
					} 

					$jurnal_pegawai = $missing;
			}
			 
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  
					WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')  
					AND a.id_pegawai = '$id_pegawai' Order by a.tanggal asc")->result_array();

			}

		 
			$data = [
				'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
				'jurnal_pegawai' => $jurnal_pegawai,
				'pegawai' => $pegawai ?? 'Semua Pegawai',
				'print' => $ambil['print'],
				'status' => 'Tanggal',
				'laporan' => $laporan
			];
		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();

			if ($pegawai == null) {

				if ($laporan == 'Karyawan') {
				$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan != 'Guru'")->result_array();
				$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

 
					$sql = "SELECT nama_pegawai, tanggal_input,semester,periode
							FROM jurnal_pegawai
							WHERE MONTH(STR_TO_DATE(tanggal_input,'%d-%m-%Y'))=? 
							AND YEAR(STR_TO_DATE(tanggal_input,'%d-%m-%Y'))=?";
					$rows = $this->db->query($sql, [$bulan, $tahun])->result_array();
					$hari = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
					$allDates = [];
					for ($d = 1; $d <= $hari; $d++) {
						$allDates[] = sprintf('%02d-%02d-%04d', $d, $bulan, $tahun);
					}
					
					$filled = []; 
					$meta=[];
					foreach ($rows as $r) {
						if (empty($r['tanggal_input'])) continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal_input']);
						if (!$dt) continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_pegawai'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode']  = $r['periode'] ?? '-';
					}

					
					$missing = [];  
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);
					
						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode'  => $meta[$nama]['periode']  ?? sprintf('%02d-%04d', $bulan, $tahun),
								'tanggal'  => $kosong
							];
						}
					} 

					$jurnal_pegawai = $missing;
				}else{
				$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan = 'Guru'")->result_array();
				$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

					$sql = "   SELECT a.*,b.nama_kelas,b.kode_kelas
						FROM jurnal_guru a left join kelas b on a.id_kelas=b.id
							WHERE MONTH(STR_TO_DATE(a.tanggal,'%d-%m-%Y'))=? 
							AND YEAR(STR_TO_DATE(a.tanggal,'%d-%m-%Y'))=?"; 
					$rows = $this->db->query($sql, [$bulan, $tahun])->result_array();
					$hari = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
					$allDates = [];
					for ($d = 1; $d <= $hari; $d++) {
						$allDates[] = sprintf('%02d-%02d-%04d', $d, $bulan, $tahun);
					}
					
					$filled = []; 
					$meta=[];
					foreach ($rows as $r) {
						if (empty($r['tanggal'])) continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal']);
						if (!$dt) continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_guru'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode']  = $r['periode'] ?? '-';
						$meta[$nama]['mapel']  = $r['mapel'] ?? '-';
						$meta[$nama]['nama_kelas']  = $r['nama_kelas'] ?? '-';
						$meta[$nama]['kode_kelas']  = $r['kode_kelas'] ?? '-';
					}

					
					$missing = [];  
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);
					
						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode'  => $meta[$nama]['periode']  ?? '-',
								'mapel'  => $meta[$nama]['mapel']  ?? '-',
								'nama_kelas'  => $meta[$nama]['nama_kelas']  ?? '-',
								'kode_kelas'  => $meta[$nama]['kode_kelas']  ?? '-',
								'tanggal'  => $kosong
							];
						}
					} 

					$jurnal_pegawai = $missing;
				}
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_pegawai = '$id_pegawai' Order by a.tanggal asc")->result_array();

			}
	 
			$data = [
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'jurnal_pegawai' => $jurnal_pegawai,
				'pegawai' => $pegawai ?? 'Semua Pegawai',
				'status' => 'Bulan',
				'laporan' => $laporan
			];


		} else {
			$tahun = $ambil['single_filter_tahun'];

			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();







			if ($pegawai == null) { 
					if ($laporan == 'Karyawan') {
				$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan != 'Guru'")->result_array();
				$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

 
					$sql = "SELECT nama_pegawai, tanggal_input,semester,periode
							FROM jurnal_pegawai
							WHERE YEAR(STR_TO_DATE(tanggal_input,'%d-%m-%Y'))=?";
					$rows = $this->db->query($sql, [ $tahun])->result_array();
						
					$allDates = [];
						for ($month = 1; $month <= 12; $month++) {
							$hari = cal_days_in_month(CAL_GREGORIAN, $month, (int)$tahun);
							for ($d = 1; $d <= $hari; $d++) {
								$allDates[] = sprintf('%02d-%02d-%04d', $d, $month, $tahun);
							}
						}

					
					$filled = []; 
					$meta=[];
					foreach ($rows as $r) {
						if (empty($r['tanggal_input'])) continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal_input']);
						if (!$dt) continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_pegawai'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode']  = $r['periode'] ?? '-';
					}

					
					$missing = [];  
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);
					
						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode'  => $meta[$nama]['periode']  ?? (string) $tahun,
								'tanggal'  => $kosong
							];
						}
					} 

					$jurnal_pegawai = $missing;
				}else{
				$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan = 'Guru'")->result_array();
				$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

					$sql = "   SELECT a.*,b.nama_kelas,b.kode_kelas
						FROM jurnal_guru a left join kelas b on a.id_kelas=b.id
							WHERE  YEAR(STR_TO_DATE(a.tanggal,'%d-%m-%Y'))=?"; 
					$rows = $this->db->query($sql, [$tahun])->result_array();
						$allDates = [];
						for ($month = 1; $month <= 12; $month++) {
							$hari = cal_days_in_month(CAL_GREGORIAN, $month, (int)$tahun);
							for ($d = 1; $d <= $hari; $d++) {
								$allDates[] = sprintf('%02d-%02d-%04d', $d, $month, $tahun);
							}
						}
					
					$filled = []; 
					$meta=[];
					foreach ($rows as $r) {
						if (empty($r['tanggal'])) continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal']);
						if (!$dt) continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_guru'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode']  = $r['periode'] ?? '-';
						$meta[$nama]['mapel']  = $r['mapel'] ?? '-';
						$meta[$nama]['nama_kelas']  = $r['nama_kelas'] ?? '-';
						$meta[$nama]['kode_kelas']  = $r['kode_kelas'] ?? '-';
					}

					
					$missing = [];  
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);
					
						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode'  => $meta[$nama]['periode']  ?? '-',
								'mapel'  => $meta[$nama]['mapel']  ?? '-',
								'nama_kelas'  => $meta[$nama]['nama_kelas']  ?? '-',
								'kode_kelas'  => $meta[$nama]['kode_kelas']  ?? '-',
								'tanggal'  => $kosong
							];
						}
					} 

					$jurnal_pegawai = $missing;
				}
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_pegawai = '$id_pegawai' Order by a.tanggal asc")->result_array();

			}


			$data = [
				'judul' => $tahun,
				'jurnal_pegawai' => $jurnal_pegawai,
				'pegawai' => $pegawai ?? 'Semua Pegawai',
				'status' => 'Tahun',
				'laporan' =>$laporan
			];

		}


		$this->load->view('admin/data_laporan/laporan_resume_tanggal_belum_diisi', $data);
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
