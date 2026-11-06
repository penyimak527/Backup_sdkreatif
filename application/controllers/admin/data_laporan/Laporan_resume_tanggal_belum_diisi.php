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
		if (!is_array($ambil)) {
			$ambil = [];
		}
		$id_pegawai = $ambil['id_pegawai'] ?? null;
		$laporan = $ambil['laporan'] ?? '';
		$filter = $ambil['filter'] ?? '';
		$id_periode = $ambil['id_periode_resume_tanggal'] ?? '';
		$data_periode = [];
		$nama_periode = '';

		if ($id_periode !== '') {
			$data_periode = $this->db
				->get_where('master_tahun_ajaran', ['id' => $id_periode])
				->row_array();

			$nama_periode = trim((string) ($data_periode['periode'] ?? ''));
		}

		/*
		 * Tahun Ajaran wajib dipilih dan harus ditemukan.
		 * Jika kosong/tidak valid, kirim data kosong ke view agar tampil
		 * "Tidak ada data", sama seperti konsep Laporan Jurnal Harian.
		 */
		if ($id_periode === '' || $nama_periode === '') {
			$this->load->view('admin/data_laporan/laporan_resume_tanggal_belum_diisi', [
				'judul' => '-',
				'jurnal_pegawai' => [],
				'pegawai' => 'Semua Pegawai',
				'print' => $ambil['print'] ?? '',
				'status' => 'Filter',
				'laporan' => $laporan
			]);
			return;
		}

		if ($filter == 'tanggal') {
			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal'] ?? ''));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal'] ?? ''));

			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();


			if ($pegawai == null) {


				if ($laporan == 'Karyawan') {
					$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan != 'Guru'")->result_array();
					$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);

					$sql = "   SELECT a.*
						FROM jurnal_pegawai a
						WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') 
							AND STR_TO_DATE(?, '%d-%m-%Y')  AND a.id_periode = ?
							Order by  STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC,
								STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
						";
					$rows = $this->db->query($sql, [$dari_tanggal, $sampai_tanggal, $id_periode])->result_array();

					$start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
					$end = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
					$endInc = (clone $end); // DatePeriod eksklusif di end, jadi tambah 1 hari
					$endInc->modify('+1 day');

					$allDates = [];
					$period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
					foreach ($period as $dt) {
						if ($this->tanggalSesuaiTahunAjaran($dt, $nama_periode)) {
							$allDates[] = $dt->format('d-m-Y');
						}
					}

					$filled = [];
					$meta = [];
					foreach ($rows as $r) {
						if (empty($r['tanggal_input']))
							continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal_input']);
						if (!$dt)
							continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_pegawai'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode'] = $r['periode'] ?? '-';
					}


					$missing = [];
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);

						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode' => $meta[$nama]['periode'] ?? '-',
								'tanggal' => $kosong
							];
						}
					}

					$jurnal_pegawai = $missing;
				} else {
					$pegawai = $this->db->query("SELECT a.id, a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan = 'Guru'")->result_array();
					$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);
					$guruIdByName = [];
					$guruIds = [];
					foreach ($pegawai as $row) {
						$guruIdByName[$row['nama_pegawai']] = $row['id'];
						$guruIds[] = $row['id'];
					}

					$sql = "   SELECT a.*,b.nama_kelas,b.kode_kelas
						FROM jurnal_guru a left join kelas b on a.id_kelas=b.id
						WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') 
							AND STR_TO_DATE(?, '%d-%m-%Y')  AND id_periode = ?
							Order by  STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC,
								STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
						";
					$rows = $this->db->query($sql, [$dari_tanggal, $sampai_tanggal, $id_periode])->result_array();

					$start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
					$end = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
					$endInc = (clone $end);
					$endInc->modify('+1 day');

					$allDates = [];
					$period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
					foreach ($period as $dt) {
						if ($this->tanggalSesuaiTahunAjaran($dt, $nama_periode)) {
							$allDates[] = $dt->format('d-m-Y');
						}
					}

					$tahunJadwal = null;
					if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
						$tahunMulai = (int) date('Y', strtotime($dari_tanggal));
						$tahunAkhir = (int) date('Y', strtotime($sampai_tanggal));
						if ($tahunMulai > 0 && $tahunMulai === $tahunAkhir) {
							$tahunJadwal = $tahunMulai;
						}
					}
					// $jadwalByGuru = $this->getJadwalByGuru($guruIds, $tahunJadwal);
					$jadwalByGuru = $this->getJadwalByGuru($guruIds, $id_periode);
					$jadwalByGuruHari = $this->indexJadwalByGuruHari($jadwalByGuru);

					$filled = [];
					foreach ($rows as $r) {
						if (empty($r['tanggal']))
							continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal']);
						if (!$dt)
							continue;
						$tgl = $dt->format('d-m-Y');
						$jadwalId = $r['id_kelas_jadwal_pelajaran'] ?? null;
						if (!empty($jadwalId)) {
							$filled[$jadwalId][$tgl] = true;
							continue;
						}

						if (!empty($r['id_guru'])) {
							$hari = $this->hariFromDate($tgl);
							$matchIds = $this->matchJadwalIds(
								$jadwalByGuruHari,
								$r['id_guru'],
								$r['mapel'] ?? '',
								$r['id_kelas'] ?? null,
								$r['nama_kelas'] ?? '',
								$hari
							);
							foreach ($matchIds as $matchId) {
								$filled[$matchId][$tgl] = true;
							}
						}
					}

					$datesByDow = $this->groupDatesByDow($allDates);

					$missing = [];
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$guruId = $guruIdByName[$nama] ?? null;
						if (!$guruId) {
							continue;
						}
						$jadwalList = $jadwalByGuru[$guruId] ?? [];
						foreach ($jadwalList as $jadwal) {
							$expectedDates = $this->expectedDatesForHari($datesByDow, [$jadwal['hari'] ?? '']);
							if (empty($expectedDates)) {
								continue;
							}
							$sudah = isset($filled[$jadwal['id']]) ? array_keys($filled[$jadwal['id']]) : [];
							$kosong = array_values(array_diff($expectedDates, $sudah));
							sort($kosong);

							if (!empty($kosong)) {
								$missing[$nama][] = [
									'semester' => $jadwal['semester'] ?? '-',
									'periode' => $jadwal['periode'] ?? '-',
									'mapel' => $jadwal['mapel'] ?? '-',
									'nama_kelas' => $jadwal['nama_kelas'] ?? '-',
									'kode_kelas' => $jadwal['kode_kelas'] ?? '-',
									'tanggal' => $kosong
								];
							}
						}
					}

				 
					$jurnal_pegawai = $missing;
				}

			} else {
				$jurnal_pegawai = $this->db->query(
					"SELECT a.* FROM jurnal_pegawai a
					 WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y')
					 AND STR_TO_DATE(?, '%d-%m-%Y')
					 AND a.id_pegawai = ?
					 AND a.id_periode = ?
					 ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC",
					[$dari_tanggal, $sampai_tanggal, $id_pegawai, $id_periode]
				)->result_array();

			}


			$data = [
				'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
				'jurnal_pegawai' => $jurnal_pegawai,
				'pegawai' => $pegawai ?? 'Semua Pegawai',
				'print' => $ambil['print'],
				'status' => 'Tanggal',
				'laporan' => $laporan
			];
		} else if ($filter == 'bulan') {
				$bulan = $ambil['filter_bulan'] ?? null;
				$tahun = $ambil['filter_tahun'] ?? null;
				$bulan = (int) $bulan;
				$tahun = (int) $tahun;
			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();

			if ($pegawai == null) {

				if ($laporan == 'Karyawan') {
					$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan != 'Guru'")->result_array();
					$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);


					$sql = "SELECT nama_pegawai, tanggal_input,semester,periode
							FROM jurnal_pegawai
							WHERE MONTH(STR_TO_DATE(tanggal_input,'%d-%m-%Y'))=? 
							AND YEAR(STR_TO_DATE(tanggal_input,'%d-%m-%Y'))=? 	AND id_periode = ?";
					$rows = $this->db->query($sql, [$bulan, $tahun, $id_periode])->result_array();
					$allDates = [];
					if ($bulan > 0 && $tahun > 0) {
						$hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
						for ($d = 1; $d <= $hari; $d++) {
							$dtFilter = DateTime::createFromFormat(
								'd-m-Y',
								sprintf('%02d-%02d-%04d', $d, $bulan, $tahun)
							);

							if ($dtFilter && $this->tanggalSesuaiTahunAjaran($dtFilter, $nama_periode)) {
								$allDates[] = $dtFilter->format('d-m-Y');
							}
						}
					}

					$filled = [];
					$meta = [];
					foreach ($rows as $r) {
						if (empty($r['tanggal_input']))
							continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal_input']);
						if (!$dt)
							continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_pegawai'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode'] = $r['periode'] ?? '-';
					}


					$missing = [];
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);

						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode' => $meta[$nama]['periode'] ?? sprintf('%02d-%04d', $bulan, $tahun),
								'tanggal' => $kosong
							];
						}
					}

					$jurnal_pegawai = $missing;
				} else {

			 
					$pegawai = $this->db->query("SELECT a.id, a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan = 'Guru'")->result_array();
					$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);
					$guruIdByName = [];
					$guruIds = [];
					foreach ($pegawai as $row) {
						$guruIdByName[$row['nama_pegawai']] = $row['id'];
						$guruIds[] = $row['id'];
					}

					$sql = "   SELECT a.*,b.nama_kelas,b.kode_kelas
						FROM jurnal_guru a left join kelas b on a.id_kelas=b.id
							WHERE MONTH(STR_TO_DATE(a.tanggal,'%d-%m-%Y'))=? 
							AND YEAR(STR_TO_DATE(a.tanggal,'%d-%m-%Y'))=? AND a.id_periode = ?";
					$rows = $this->db->query($sql, [$bulan, $tahun, $id_periode])->result_array();
					$allDates = [];
					if ($bulan > 0 && $tahun > 0) {
						$hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
						for ($d = 1; $d <= $hari; $d++) {
							$dtFilter = DateTime::createFromFormat(
								'd-m-Y',
								sprintf('%02d-%02d-%04d', $d, $bulan, $tahun)
							);

							if ($dtFilter && $this->tanggalSesuaiTahunAjaran($dtFilter, $nama_periode)) {
								$allDates[] = $dtFilter->format('d-m-Y');
							}
						}
					}

					// $jadwalByGuru = $this->getJadwalByGuru($guruIds, $tahun);
					$jadwalByGuru = $this->getJadwalByGuru($guruIds, $id_periode);
					$jadwalByGuruHari = $this->indexJadwalByGuruHari($jadwalByGuru);

					$filled = [];
					foreach ($rows as $r) {
						if (empty($r['tanggal']))
							continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal']);
						if (!$dt)
							continue;
						$tgl = $dt->format('d-m-Y');
						$jadwalId = $r['id_kelas_jadwal_pelajaran'] ?? null;
						if (!empty($jadwalId)) {
							$filled[$jadwalId][$tgl] = true;
							continue;
						}

						if (!empty($r['id_guru'])) {
							$hari = $this->hariFromDate($tgl);
							$matchIds = $this->matchJadwalIds(
								$jadwalByGuruHari,
								$r['id_guru'],
								$r['mapel'] ?? '',
								$r['id_kelas'] ?? null,
								$r['nama_kelas'] ?? '',
								$hari
							);
							foreach ($matchIds as $matchId) {
								$filled[$matchId][$tgl] = true;
							}
						}
					}

					$datesByDow = $this->groupDatesByDow($allDates);

					$missing = [];
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$guruId = $guruIdByName[$nama] ?? null;
						if (!$guruId) {
							continue;
						}
						$jadwalList = $jadwalByGuru[$guruId] ?? [];
						foreach ($jadwalList as $jadwal) {
							$expectedDates = $this->expectedDatesForHari($datesByDow, [$jadwal['hari'] ?? '']);
							if (empty($expectedDates)) {
								continue;
							}
							$sudah = isset($filled[$jadwal['id']]) ? array_keys($filled[$jadwal['id']]) : [];
							$kosong = array_values(array_diff($expectedDates, $sudah));
							sort($kosong);

							if (!empty($kosong)) {
								$missing[$nama][] = [
									'semester' => $jadwal['semester'] ?? '-',
									'periode' => $jadwal['periode'] ?? '-',
									'mapel' => $jadwal['mapel'] ?? '-',
									'nama_kelas' => $jadwal['nama_kelas'] ?? '-',
									'kode_kelas' => $jadwal['kode_kelas'] ?? '-',
									'tanggal' => $kosong
								];
							}
						}
					}

					 
					$jurnal_pegawai = $missing;
				}
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_pegawai = '$id_pegawai' AND a.id_periode = '$id_periode' Order by a.tanggal asc")->result_array();

			}

			$data = [
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'jurnal_pegawai' => $jurnal_pegawai,
				'pegawai' => $pegawai ?? 'Semua Pegawai',
				'status' => 'Bulan',
				'laporan' => $laporan
			];


		} else {
			$tahun = $ambil['single_filter_tahun'] ?? null;
			$tahun = (int) $tahun;

			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();

			if ($pegawai == null) {
				if ($laporan == 'Karyawan') {
					$pegawai = $this->db->query("SELECT a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan != 'Guru'")->result_array();
					$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);


					$sql = "SELECT nama_pegawai, tanggal_input,semester,periode
							FROM jurnal_pegawai
							WHERE YEAR(STR_TO_DATE(tanggal_input,'%d-%m-%Y'))=? AND id_periode = ?";
					$rows = $this->db->query($sql, [$tahun, $id_periode])->result_array();

					$allDates = [];
					if ($tahun > 0) {
						for ($month = 1; $month <= 12; $month++) {
							$hari = cal_days_in_month(CAL_GREGORIAN, $month, $tahun);
							for ($d = 1; $d <= $hari; $d++) {
								$dtFilter = DateTime::createFromFormat(
									'd-m-Y',
									sprintf('%02d-%02d-%04d', $d, $month, $tahun)
								);

								if ($dtFilter && $this->tanggalSesuaiTahunAjaran($dtFilter, $nama_periode)) {
									$allDates[] = $dtFilter->format('d-m-Y');
								}
							}
						}
					}


					$filled = [];
					$meta = [];
					foreach ($rows as $r) {
						if (empty($r['tanggal_input']))
							continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal_input']);
						if (!$dt)
							continue;
						$tgl = $dt->format('d-m-Y');
						$nama = $r['nama_pegawai'];
						$filled[$nama][$tgl] = true;

						$meta[$nama]['semester'] = $r['semester'] ?? '-';
						$meta[$nama]['periode'] = $r['periode'] ?? '-';
					}


					$missing = [];
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$sudah = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
						$kosong = array_values(array_diff($allDates, $sudah));
						sort($kosong);

						if (!empty($kosong)) {
							$missing[$nama] = [
								'semester' => $meta[$nama]['semester'] ?? '-',
								'periode' => $meta[$nama]['periode'] ?? (string) $tahun,
								'tanggal' => $kosong
							];
						}
					}

					$jurnal_pegawai = $missing;
				} else {
					$pegawai = $this->db->query("SELECT a.id, a.nama_pegawai FROM pegawai a left join pegawai_jabatan b on a.id = b.id_pegawai WHERE b.jabatan = 'Guru'")->result_array();
					$daftarPegawai = array_map(fn($x) => $x['nama_pegawai'], $pegawai);
					$guruIdByName = [];
					$guruIds = [];
					foreach ($pegawai as $row) {
						$guruIdByName[$row['nama_pegawai']] = $row['id'];
						$guruIds[] = $row['id'];
					}

					$sql = "   SELECT a.*,b.nama_kelas,b.kode_kelas
						FROM jurnal_guru a left join kelas b on a.id_kelas=b.id
							WHERE  YEAR(STR_TO_DATE(a.tanggal,'%d-%m-%Y'))=? AND id_periode = ? ";
					$rows = $this->db->query($sql, [$tahun, $id_periode])->result_array();
					$allDates = [];
					if ($tahun > 0) {
						for ($month = 1; $month <= 12; $month++) {
							$hari = cal_days_in_month(CAL_GREGORIAN, $month, $tahun);
							for ($d = 1; $d <= $hari; $d++) {
								$dtFilter = DateTime::createFromFormat(
									'd-m-Y',
									sprintf('%02d-%02d-%04d', $d, $month, $tahun)
								);

								if ($dtFilter && $this->tanggalSesuaiTahunAjaran($dtFilter, $nama_periode)) {
									$allDates[] = $dtFilter->format('d-m-Y');
								}
							}
						}
					}

					// $jadwalByGuru = $this->getJadwalByGuru($guruIds, $tahun);
					$jadwalByGuru = $this->getJadwalByGuru($guruIds, $id_periode);
					$jadwalByGuruHari = $this->indexJadwalByGuruHari($jadwalByGuru);

					$filled = [];
					foreach ($rows as $r) {
						if (empty($r['tanggal']))
							continue;
						$dt = DateTime::createFromFormat('d-m-Y', $r['tanggal']);
						if (!$dt)
							continue;
						$tgl = $dt->format('d-m-Y');
						$jadwalId = $r['id_kelas_jadwal_pelajaran'] ?? null;
						if (!empty($jadwalId)) {
							$filled[$jadwalId][$tgl] = true;
							continue;
						}

						if (!empty($r['id_guru'])) {
							$hari = $this->hariFromDate($tgl);
							$matchIds = $this->matchJadwalIds(
								$jadwalByGuruHari,
								$r['id_guru'],
								$r['mapel'] ?? '',
								$r['id_kelas'] ?? null,
								$r['nama_kelas'] ?? '',
								$hari
							);
							foreach ($matchIds as $matchId) {
								$filled[$matchId][$tgl] = true;
							}
						}
					}

					$datesByDow = $this->groupDatesByDow($allDates);

					$missing = [];
					foreach ($daftarPegawai as $nama) {
						$nama = trim($nama);
						$guruId = $guruIdByName[$nama] ?? null;
						if (!$guruId) {
							continue;
						}
						$jadwalList = $jadwalByGuru[$guruId] ?? [];
						foreach ($jadwalList as $jadwal) {
							$expectedDates = $this->expectedDatesForHari($datesByDow, [$jadwal['hari'] ?? '']);
							if (empty($expectedDates)) {
								continue;
							}
							$sudah = isset($filled[$jadwal['id']]) ? array_keys($filled[$jadwal['id']]) : [];
							$kosong = array_values(array_diff($expectedDates, $sudah));
							sort($kosong);

							if (!empty($kosong)) {
								$missing[$nama][] = [
									'semester' => $jadwal['semester'] ?? '-',
									'periode' => $jadwal['periode'] ?? '-',
									'mapel' => $jadwal['mapel'] ?? '-',
									'nama_kelas' => $jadwal['nama_kelas'] ?? '-',
									'kode_kelas' => $jadwal['kode_kelas'] ?? '-',
									'tanggal' => $kosong
								];
							}
						}
					}

					$jurnal_pegawai = $missing;
				}
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_pegawai = '$id_pegawai' AND a.id_periode = '$id_periode' Order by a.tanggal asc")->result_array();

			}


			$data = [
				'judul' => $tahun,
				'jurnal_pegawai' => $jurnal_pegawai,
				'pegawai' => $pegawai ?? 'Semua Pegawai',
				'status' => 'Tahun',
				'laporan' => $laporan
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

	private function getJadwalByGuru(
		array $guruIds,
		$idPeriode
	): array {
		if (empty($guruIds) || empty($idPeriode)) {
			return [];
		}

		$this->db->select("
			a.id,
			a.id_guru,
			a.hari,
			a.mapel,
			a.id_mapel,
			a.id_kelas,
			a.kelas,
			a.semester,
			a.id_periode,
			a.periode,
			k.kode_kelas,
			COALESCE(a.kelas, k.nama_kelas) AS nama_kelas
		");
		$this->db->from('kelas_jadwal_pelajaran a');
		$this->db->join('kelas k', 'k.id = a.id_kelas', 'left');
		$this->db->where_in('a.id_guru', $guruIds);
		$this->db->where('a.id_periode', $idPeriode);

		$rows = $this->db->get()->result_array();
		$jadwalByGuru = [];

		foreach ($rows as $row) {
			$idGuru = $row['id_guru'] ?? null;
			if (empty($idGuru)) {
				continue;
			}

			$row['hari'] = $this->normalizeHari($row['hari'] ?? '');
			$jadwalByGuru[$idGuru][] = $row;
		}

		return $jadwalByGuru;
	}

	private function indexJadwalByGuruHari(array $jadwalByGuru): array
	{
		$index = [];
		foreach ($jadwalByGuru as $idGuru => $jadwalList) {
			foreach ($jadwalList as $jadwal) {
				$hari = $this->normalizeHari($jadwal['hari'] ?? '');
				if ($hari === '') {
					continue;
				}
				$index[$idGuru][$hari][] = $jadwal;
			}
		}

		return $index;
	}

	private function matchJadwalIds(array $jadwalByGuruHari, $idGuru, $mapel, $idKelas, $namaKelas, $hari): array
	{
		$idGuru = $idGuru ?? null;
		if (empty($idGuru)) {
			return [];
		}
		$hari = $this->normalizeHari($hari ?? '');
		if ($hari === '') {
			return [];
		}

		$mapel = strtolower(trim((string) $mapel));
		$namaKelas = strtolower(trim((string) $namaKelas));
		$list = $jadwalByGuruHari[$idGuru][$hari] ?? [];
		$matches = [];
		foreach ($list as $jadwal) {
			$jadwalMapel = strtolower(trim((string) ($jadwal['mapel'] ?? '')));
			if ($mapel !== '' && $jadwalMapel !== $mapel) {
				continue;
			}
			$jadwalIdKelas = $jadwal['id_kelas'] ?? null;
			if (!empty($idKelas) && !empty($jadwalIdKelas) && (string) $jadwalIdKelas !== (string) $idKelas) {
				continue;
			}
			$jadwalNamaKelas = strtolower(trim((string) ($jadwal['nama_kelas'] ?? '')));
			if ($namaKelas !== '' && $jadwalNamaKelas !== '' && $jadwalNamaKelas !== $namaKelas) {
				continue;
			}
			if (!empty($jadwal['id'])) {
				$matches[] = $jadwal['id'];
			}
		}

		return $matches;
	}

	private function groupDatesByDow(array $allDates): array
	{
		$byDow = [];
		foreach ($allDates as $date) {
			$dt = DateTime::createFromFormat('d-m-Y', $date);
			if (!$dt) {
				continue;
			}
			$dow = (int) $dt->format('N');
			$byDow[$dow][] = $dt->format('d-m-Y');
		}
		return $byDow;
	}

	private function expectedDatesForHari(array $datesByDow, array $hariList): array
	{
		$hariMap = [
			'Senin' => 1,
			'Selasa' => 2,
			'Rabu' => 3,
			'Kamis' => 4,
			'Jumat' => 5,
			'Sabtu' => 6,
			'Minggu' => 7,
		];

		$expected = [];
		foreach ($hariList as $hari) {
			$hari = $this->normalizeHari($hari);
			$dow = $hariMap[$hari] ?? null;
			if ($dow === null) {
				continue;
			}
			if (!empty($datesByDow[$dow])) {
				$expected = array_merge($expected, $datesByDow[$dow]);
			}
		}

		$expected = array_values(array_unique($expected));
		sort($expected);
		return $expected;
	}

	private function hariFromDate(string $tgl): string
	{
		$dt = DateTime::createFromFormat('d-m-Y', $tgl);
		if (!$dt) {
			return '';
		}
		$dow = (int) $dt->format('N');
		$map = [
			1 => 'Senin',
			2 => 'Selasa',
			3 => 'Rabu',
			4 => 'Kamis',
			5 => 'Jumat',
			6 => 'Sabtu',
			7 => 'Minggu',
		];
		return $map[$dow] ?? '';
	}

	private function periodeDariTahun(int $tahun): string
	{
		if ($tahun <= 0) {
			return '';
		}
		$awal = $tahun - 1;
		if ($awal <= 0) {
			return '';
		}
		return $awal . '/' . $tahun;
	}

	/**
	 * Memastikan tanggal filter sesuai dengan Tahun Ajaran yang dipilih.
	 * Aturan yang digunakan: Juli-Desember masuk tahun pertama,
	 * Januari-Juni masuk tahun kedua.
	 *
	 * Contoh:
	 * - Juli 2025  => 2025/2026
	 * - Januari 2026 => 2025/2026
	 */
	private function tanggalSesuaiTahunAjaran(DateTime $tanggal, string $periode): bool
	{
		$periode = trim($periode);

		if (!preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', $periode, $match)) {
			return false;
		}

		$tahunAwal = (int) $match[1];
		$tahunAkhir = (int) $match[2];
		$tahunTanggal = (int) $tanggal->format('Y');
		$bulanTanggal = (int) $tanggal->format('n');

		if ($bulanTanggal >= 7) {
			return $tahunTanggal === $tahunAwal;
		}

		return $tahunTanggal === $tahunAkhir;
	}

	private function normalizeHari(string $hari): string
	{
		$hari = trim($hari);
		if ($hari === '') {
			return '';
		}
		return ucfirst(strtolower($hari));
	}

}