<?php
class M_dashboard extends CI_Model
{


	public function mapel_result()
	{


		$id_siswa = $this->session->userdata('siswa')['id_user'];

		$kelas_siswa = $this->db->get_where('kelas_siswa', array('id_siswa' => $id_siswa))->row_array();

		$kelas_setting = $this->db->get_where('kelas_setting', array('id' => $kelas_siswa['id_kelas_setting']))->row_array();
		$this->db->select('id_mapel, mapel');
		$this->db->from('kelas_jadwal_pelajaran');
		$this->db->where('id_kelas_setting', $kelas_setting['id']);
		$this->db->group_by('mapel');
		$mapel = $this->db->get()->result_array();

		$data = array();
		foreach ($mapel as $m) {
			$data[] = array(
				'id_mapel' => $m['id_mapel'],
				'mapel' => $m['mapel'],
			);
		}
		return $data;
	}
	// public function jadwal_result()
	// {

	// 	$hariInggris = date('l');
	// 	$daftarHari = [
	// 		'Sunday' => 'Minggu',
	// 		'Monday' => 'Senin',
	// 		'Tuesday' => 'Selasa',
	// 		'Wednesday' => 'Rabu',
	// 		'Thursday' => 'Kamis',
	// 		'Friday' => 'Jumat',
	// 		'Saturday' => 'Sabtu'
	// 	];

	// 	$hari = $daftarHari[$hariInggris];
	// 	$tanggal = date('d-m-Y');
	// 	$id_guru = $this->session->userdata('admin')['id_pegawai'];


	// 	$sql = " SELECT 
	// 			a.*, 
	// 			a.id AS id_jadwal,
	// 			b.kode_kelas  
	// 		FROM 
	// 			kelas_jadwal_pelajaran a left join kelas b on a.id_kelas=b.id
	// 		WHERE 
	// 			a.hari = ? and a.id_guru = ?
	// 			AND a.id NOT IN (
	// 				SELECT id_kelas_jadwal_pelajaran 
	// 				FROM jurnal_guru 
	// 				WHERE tanggal = ?
	// 			)
	// 		ORDER BY 
	// 			a.jam_pelajaran_awal ASC
	// 	";


	// 	$query = $this->db->query($sql, [$hari, $id_guru, $tanggal]);
	// 	$result = $query->result_array();




	// 	return $result;
	// }

	public function jadwal_result()
{
    $hariInggris = date('l');
    $daftarHari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $hari = $daftarHari[$hariInggris];
    $tanggal = date('d-m-Y');
    $id_guru = $this->session->userdata('admin')['id_pegawai'];

    // Ambil tahun ajaran aktif
    $periode_aktif = $this->db
        ->get_where('master_tahun_ajaran', ['status' => 'Aktif'])
        ->row_array();

    if (empty($periode_aktif)) {
        return [];
    }

    // Semester aktif berdasarkan bulan berjalan
    // Juli - Desember = Ganjil
    // Januari - Juni = Genap
    $bulan_sekarang = date('m');
    $semester_aktif = in_array($bulan_sekarang, ['07', '08', '09', '10', '11', '12'])
        ? 'Ganjil'
        : 'Genap';

    $sql = "
        SELECT 
            a.*, 
            a.id AS id_jadwal,
            b.kode_kelas  
        FROM kelas_jadwal_pelajaran a
        LEFT JOIN kelas b ON a.id_kelas = b.id
        LEFT JOIN master_tahun_ajaran c ON a.id_periode = c.id
        WHERE 
            a.hari = ?
            AND a.id_guru = ?
            AND a.id_periode = ?
            AND a.semester = ?
            AND c.status = 'Aktif'
            AND a.id NOT IN (
                SELECT id_kelas_jadwal_pelajaran 
                FROM jurnal_guru 
                WHERE tanggal = ?
                AND id_guru = ?
            )
        ORDER BY 
            a.jam_pelajaran_awal ASC
    ";

    $query = $this->db->query($sql, [
        $hari,
        $id_guru,
        $periode_aktif['id'],
        $semester_aktif,
        $tanggal,
        $id_guru
    ]);

    return $query->result_array();
}

	public function api_jadwal_result()
	{
		// Ambil id_guru dari request (GET/POST)
		$id_guru = $this->input->get('id_guru')
			?? $this->input->post('id_guru');

		if (!$id_guru) {
			return [
				'status' => false,
				'message' => 'id_guru harus dikirim'
			];
		}

		// Konversi hari Inggris ke Indonesia
		$hariInggris = date('l');
		$daftarHari = [
			'Sunday' => 'Minggu',
			'Monday' => 'Senin',
			'Tuesday' => 'Selasa',
			'Wednesday' => 'Rabu',
			'Thursday' => 'Kamis',
			'Friday' => 'Jumat',
			'Saturday' => 'Sabtu'
		];
		$hari = $daftarHari[$hariInggris] ?? $hariInggris;
		$tanggal = date('d-m-Y');

		$periode_aktif = $this->db
			->get_where('master_tahun_ajaran', ['status' => 'Aktif'])
			->row_array();

		if (empty($periode_aktif)) {
			return [];
		}

		$bulan_sekarang = date('m');
		$semester_aktif = in_array($bulan_sekarang, ['07', '08', '09', '10', '11', '12'])
			? 'Ganjil'
			: 'Genap';

		$sql = " SELECT 
                a.id,
				a.id_kelas,
				CONCAT(a.kelas, ' ', b.kode_kelas) as kelas,
				a.id_mapel,
				a.mapel,
				a.id_guru,
				a.nama_guru,
				a.jam_pelajaran_awal,
				a.jam_pelajaran_akhir,
				a.hari,
				a.ruangan,
				a.jumlah_jam,
				a.semester,
				a.id_periode,
				a.periode,
                a.id AS id_jadwal,
                b.kode_kelas  
            FROM 
                kelas_jadwal_pelajaran a 
                LEFT JOIN kelas b ON a.id_kelas=b.id
            WHERE 
                a.hari = ? AND a.id_guru = ? AND a.id_periode = ? AND a.semester = ?
                AND a.id NOT IN (
                    SELECT id_kelas_jadwal_pelajaran 
                    FROM jurnal_guru 
                    WHERE tanggal = ?
                )
            ORDER BY a.jam_pelajaran_awal ASC
    ";

		$query = $this->db->query($sql, [$hari, $id_guru, $periode_aktif['id'], $semester_aktif, $tanggal]);
		$result = $query->result_array();

		return [
			'status' => true,
			'data' => $result
		];
	}


	public function dashboard_result()
	{
		$id_siswa = $this->session->userdata('siswa')['id_user'];
		$kelas_siswa = $this->db->get_where('kelas_siswa', array('id_siswa' => $id_siswa))->row_array();
		$kelas_setting = $this->db->get_where('kelas_setting', array('id' => $kelas_siswa['id_kelas_setting']))->row_array();
		$this->db->select('id_mapel, mapel,nama_guru');
		$this->db->from('kelas_jadwal_pelajaran');
		$this->db->where('id_kelas_setting', $kelas_setting['id']);
		$this->db->group_by('mapel');
		$mapel = $this->db->get()->result_array();

		$data = array();
		foreach ($mapel as $m) {
			$data[] = array(
				'id_mapel' => $m['id_mapel'],
				'mapel' => $m['mapel'],
				'guru' => $m['nama_guru'],
			);
		}
		return $data;
	}

	// public function dashboard_keuangan($tahun = null)
	// {
	// 	// Saldo awal
	// 	$saldo_awal = $this->get_saldo_awal($tahun);

	// 	// Pemasukan tahun berjalan
	// 	$this->db->select_sum('jumlah');
	// 	$this->db->where('tahun', $tahun);
	// 	$total_pemasukan = $this->db->get('pemasukan')->row()->jumlah ?? 0;

	// 	// Pengeluaran tahun berjalan
	// 	$this->db->select_sum('jumlah');
	// 	$this->db->where('tahun', $tahun);
	// 	$total_pengeluaran = $this->db->get('pengeluaran')->row()->jumlah ?? 0;

	// 	$saldo_akhir = $saldo_awal + $total_pemasukan - $total_pengeluaran;

    // $range = $this->db->query("
    //     SELECT 
    //         MIN(tgl) AS tanggal_awal,
    //         MAX(tgl) AS tanggal_akhir
    //     FROM (
    //         SELECT STR_TO_DATE(tanggal_input, '%d-%m-%Y') AS tgl
    //         FROM pemasukan
    //         WHERE tahun = ?

    //         UNION ALL

    //         SELECT STR_TO_DATE(tanggal_input, '%d-%m-%Y') AS tgl
    //         FROM pengeluaran
    //         WHERE tahun = ?
    //     ) transaksi
    // ", [$tahun, $tahun])->row();

    // $tanggal_awal = !empty($range->tanggal_awal) ? date('d-m-Y', strtotime($range->tanggal_awal)) : '01-01-' . $tahun;
    // $tanggal_akhir = !empty($range->tanggal_akhir) ? date('d-m-Y', strtotime($range->tanggal_akhir)) : '31-12-' . $tahun;
	// 	return [
	// 		'tanggal_awal' => $tanggal_awal,
	// 		'tanggal_akhir' => $tanggal_akhir,
	// 		'saldo_awal' => $saldo_awal,
	// 		'pemasukan' => $total_pemasukan,
	// 		'pengeluaran' => $total_pengeluaran,
	// 		'saldo_akhir' => $saldo_akhir
	// 	];
	// }
	public function dashboard_keuangan($tanggal_awal = null, $tanggal_akhir = null)
{
	// Default jika tanggal kosong
	if ($tanggal_awal == null || $tanggal_awal == '') {
		$tanggal_awal = date('01-m-Y');
	}

	if ($tanggal_akhir == null || $tanggal_akhir == '') {
		$tanggal_akhir = date('t-m-Y');
	}

	// Ambil saldo awal periode berdasarkan tanggal awal
	$saldo_awal = $this->get_saldo_awal_by_tanggal($tanggal_awal);

	// Pemasukan dalam rentang tanggal
	$total_pemasukan = $this->db->query("
		SELECT COALESCE(SUM(jumlah), 0) AS total
		FROM pemasukan
		WHERE STR_TO_DATE(tanggal_input, '%d-%m-%Y') >= STR_TO_DATE(?, '%d-%m-%Y')
		AND STR_TO_DATE(tanggal_input, '%d-%m-%Y') <= STR_TO_DATE(?, '%d-%m-%Y')
	", [$tanggal_awal, $tanggal_akhir])->row()->total ?? 0;

	// Pengeluaran dalam rentang tanggal
	$total_pengeluaran = $this->db->query("
		SELECT COALESCE(SUM(jumlah), 0) AS total
		FROM pengeluaran
		WHERE STR_TO_DATE(tanggal_input, '%d-%m-%Y') >= STR_TO_DATE(?, '%d-%m-%Y')
		AND STR_TO_DATE(tanggal_input, '%d-%m-%Y') <= STR_TO_DATE(?, '%d-%m-%Y')
	", [$tanggal_awal, $tanggal_akhir])->row()->total ?? 0;

	$saldo_awal = $saldo_awal ?: 0;
	$total_pemasukan = $total_pemasukan ?: 0;
	$total_pengeluaran = $total_pengeluaran ?: 0;

	$saldo_akhir = $saldo_awal + $total_pemasukan - $total_pengeluaran;

	return [
		'tanggal_awal' => $tanggal_awal,
		'tanggal_akhir' => $tanggal_akhir,
		'saldo_awal' => $saldo_awal,
		'pemasukan' => $total_pemasukan,
		'pengeluaran' => $total_pengeluaran,
		'saldo_akhir' => $saldo_akhir
	];
}

private function get_saldo_awal_by_tanggal($tanggal_awal)
{
	if ($tanggal_awal == null || $tanggal_awal == '') {
		return 0;
	}

	$pecah_tanggal = explode('-', $tanggal_awal);

	if (count($pecah_tanggal) != 3) {
		return 0;
	}

	$tahun = $pecah_tanggal[2];

	// Pakai function lama untuk saldo awal sampai awal tahun
	$saldo_awal = $this->get_saldo_awal($tahun);

	// Pemasukan sebelum tanggal awal pada tahun yang sama
	$pemasukan = $this->db->query("
		SELECT COALESCE(SUM(jumlah), 0) AS total
		FROM pemasukan
		WHERE tahun = ?
		AND STR_TO_DATE(tanggal_input, '%d-%m-%Y') < STR_TO_DATE(?, '%d-%m-%Y')
	", [$tahun, $tanggal_awal])->row()->total ?? 0;

	// Pengeluaran sebelum tanggal awal pada tahun yang sama
	$pengeluaran = $this->db->query("
		SELECT COALESCE(SUM(jumlah), 0) AS total
		FROM pengeluaran
		WHERE tahun = ?
		AND STR_TO_DATE(tanggal_input, '%d-%m-%Y') < STR_TO_DATE(?, '%d-%m-%Y')
	", [$tahun, $tanggal_awal])->row()->total ?? 0;

	$saldo_awal = $saldo_awal ?: 0;
	$pemasukan = $pemasukan ?: 0;
	$pengeluaran = $pengeluaran ?: 0;

	return $saldo_awal + $pemasukan - $pengeluaran;
}
	private function get_saldo_awal($tahun)
	{
		// Total saldo awal sebelum tahun dipilih
		$this->db->select_sum('nominal');
		$this->db->where('tahun <', $tahun);
		$saldo_awal = $this->db->get('saldo_awal')->row()->nominal ?? 0;

		$this->db->select_sum('nominal');
		$this->db->where('tahun', $tahun);
		$saldo_awal_sekarang = $this->db->get('saldo_awal')->row()->nominal ?? 0;

		// Total pemasukan sebelum tahun dipilih
		$this->db->select_sum('jumlah');
		$this->db->where('tahun <', $tahun);
		$pemasukan = $this->db->get('pemasukan')->row()->jumlah ?? 0;

		// Total pengeluaran sebelum tahun dipilih
		$this->db->select_sum('jumlah');
		$this->db->where('tahun <', $tahun);
		$pengeluaran = $this->db->get('pengeluaran')->row()->jumlah ?? 0;

		return $saldo_awal + $pemasukan - $pengeluaran + $saldo_awal_sekarang;
	}
	// public function pengeluaran_result()
	// {
	// 	$bulan = $this->input->post('bulan');
	// 	$tahun = $this->input->post('tahun');
	// 	$where_bulan = "";
	// 	$params = [$tahun];
	// 	if ($bulan != 'semua') {
	// 		$where_bulan = " AND pe.bulan = ?";
	// 		$params[] = $bulan;
	// 	}

	// 	$sql = $this->db->query("SELECT ka.id, ka.keterangan, COALESCE(SUM(pe.jumlah),0) jumlah FROM kode_akun ka 
	// LEFT JOIN pengeluaran pe ON ka.id = pe.id_kode_akun AND pe.tahun = ? 
	// $where_bulan WHERE ka.jenis='Pengeluaran' GROUP BY ka.id,ka.keterangan ORDER BY ka.id ASC", $params);

	// 	return $sql->result_array();
	// }
	// public function pemasukan_result()
	// {
	// 	$bulan = $this->input->post('bulan');
	// 	$tahun = $this->input->post('tahun');
	// 	$where_bulan = "";

	// 	$params = [$tahun];

	// 	if ($bulan != 'semua') {
	// 		$where_bulan = " AND pe.bulan = ?";
	// 		$params[] = $bulan;
	// 	}

	// 	$sql = $this->db->query("SELECT ka.id, ka.keterangan, COALESCE(SUM(pe.jumlah),0) jumlah FROM kode_akun ka 
	// LEFT JOIN pemasukan pe ON ka.id = pe.id_kode_akun AND pe.tahun = ? 
	// $where_bulan WHERE ka.jenis='Pemasukan' GROUP BY ka.id,ka.keterangan ORDER BY ka.id ASC", $params);
	// 	return $sql->result_array();
	// }

	public function grafik_perbandingan()
	{
		$tahun = $this->input->post('tahun');

		$bulanArr = [
			'01',
			'02',
			'03',
			'04',
			'05',
			'06',
			'07',
			'08',
			'09',
			'10',
			'11',
			'12'
		];

		$hasil = [];

		foreach ($bulanArr as $b) {

			$pemasukan = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) total
            FROM pemasukan
            WHERE tahun=? AND bulan=?
        ", [$tahun, $b])->row()->total;

			$pengeluaran = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) total
            FROM pengeluaran
            WHERE tahun=? AND bulan=?
        ", [$tahun, $b])->row()->total;

			$hasil[] = [
				'bulan' => $b,
				'pemasukan' => $pemasukan,
				'pengeluaran' => $pengeluaran
			];
		}

		return $hasil;
	}

	public function top_pengeluaran_result()
	{
		$bulan = $this->input->post('bulan');
		$tahun = $this->input->post('tahun');

		$where_bulan = "";
		$params = [$tahun];

		if ($bulan != 'semua') {
			$where_bulan = " AND p.bulan=?";
			$params[] = $bulan;
		}

		$sql = $this->db->query("SELECT ka.keterangan, SUM(p.jumlah) total FROM pengeluaran p 
	JOIN kode_akun ka ON p.id_kode_akun=ka.id WHERE p.tahun=? $where_bulan 
	GROUP BY p.id_kode_akun, ka.keterangan ORDER BY total DESC LIMIT 5", $params);
		return $sql->result_array();
	}

	public function notif_keuangan()
	{
		$hasil = [];
		$saldo = $this->cek_saldo_menipis();
		if ($saldo) {
			$hasil[] = $saldo;
		}

		$pengeluaran = $this->cek_pengeluaran_besar();
		foreach ($pengeluaran as $p) {
			$hasil[] = $p;
		}
		return $hasil;
	}


	public function cek_saldo_menipis()
	{
		$tahun = date('Y');

		// sesuaikan dengan struktur saldo anda
		// $saldo_awal = $this->db->query("SELECT COALESCE(SUM(nominal),0) total FROM saldo_awal WHERE tahun=? ", [$tahun])->row()->total;
		$saldo_awal = $this->get_saldo_awal($tahun);
		$pemasukan = $this->db->query("SELECT COALESCE(SUM(jumlah),0) total FROM pemasukan WHERE tahun=?", [$tahun])->row()->total;
		$pengeluaran = $this->db->query("SELECT COALESCE(SUM(jumlah),0) total FROM pengeluaran WHERE tahun=?", [$tahun])->row()->total;

		$saldo_akhir = $saldo_awal + $pemasukan - $pengeluaran;

		// Jika saldo habis atau minus
		if ($saldo_akhir <= 0) {
			return [
				'tipe' => 'danger',
				'judul' => 'Saldo Habis',
				'pesan' => 'Saldo saat ini Rp ' . number_format($saldo_akhir, 0, ',', '.')
			];
		}

		// Batas minimum saldo
		$batas = 5000000;

		// Jika saldo menipis
		if ($saldo_akhir < $batas) {
			return [
				'tipe' => 'warning',
				'judul' => 'Saldo Menipis',
				'pesan' => 'Saldo tersisa Rp ' . number_format($saldo_akhir, 0, ',', '.')
			];
		}
		// if ($saldo_akhir < $batas) {
		// 	return [
		// 		'tipe' => 'warning',
		// 		'judul' => 'Saldo Menipis',
		// 		'pesan' => 'Saldo tersisa Rp ' . number_format($saldo_akhir, 0, ',', '.')
		// 	];
		// }
		return null;
	}

	public function cek_pengeluaran_besar()
{
	$tahun = date('Y');

	$sql = $this->db->query("SELECT ka.keterangan, COALESCE(pg.realisasi, 0) AS realisasi, COALESCE(rp.rencana, 0) AS rencana,
			CASE 
				WHEN COALESCE(rp.rencana, 0) > 0 
				THEN ROUND((COALESCE(pg.realisasi, 0) / rp.rencana) * 100)
				ELSE 0
			END AS persen
		FROM kode_akun ka
		LEFT JOIN (
			SELECT id_kode_akun, SUM(jumlah) AS realisasi
			FROM pengeluaran WHERE tahun = ? GROUP BY id_kode_akun ) pg ON pg.id_kode_akun = ka.id
		LEFT JOIN (
			SELECT rpd.kode_akun, SUM(rpd.nominal) AS rencana
			FROM rencana_pengeluaran rp
			JOIN rencana_pengeluaran_detail rpd ON rpd.id_rencana_pengeluaran = rp.id
			JOIN master_tahun_ajaran ta ON ta.id = rp.tahun_ajaran
			WHERE rp.semester = 'Tahunan'
			AND (
				(
					rpd.bulan IN ('01','02','03','04','05','06')
					AND SUBSTRING_INDEX(ta.periode, '/', -1) = ?
				)
				OR
				(
					rpd.bulan IN ('07','08','09','10','11','12')
					AND SUBSTRING_INDEX(ta.periode, '/', 1) = ?
				)
			)
			GROUP BY rpd.kode_akun
		) rp ON rp.kode_akun = ka.id
		WHERE ka.jenis = 'Pengeluaran'
		HAVING persen >= 80
		ORDER BY persen DESC", [$tahun, $tahun, $tahun]);

	$hasil = [];
	foreach ($sql->result() as $r) {
		$tipe = 'warning';

		if ($r->persen >= 100) {
			$tipe = 'danger';
		}

		$hasil[] = [
			'tipe'  => $tipe,
			'judul' => 'Pengeluaran Besar',
			'pesan' => $r->keterangan . ' telah mencapai ' . $r->persen . '% dari anggaran'
		];
	}
	return $hasil;
}

	// public function rencana_perbandingan_pengeluaran_result()
	// {
	// 	$tahun = $this->input->post('tahun');

	// 	$data = $this->db->query("
    //     SELECT b.bulan,

    //     COALESCE(
    //     (
    //         SELECT SUM(rpd.nominal)
    //         FROM rencana_pengeluaran rp
    //         JOIN master_tahun_ajaran ta
    //             ON ta.id = rp.tahun_ajaran
    //         JOIN rencana_pengeluaran_detail rpd
    //             ON rpd.id_rencana_pengeluaran = rp.id
    //         WHERE rpd.bulan = b.bulan
    //         AND (
    //             (
    //                 rp.semester = 'Genap'
    //                 AND b.bulan IN ('01','02','03','04','05','06')
    //                 AND SUBSTRING_INDEX(ta.periode,'/',-1) = '$tahun'
    //             )
    //             OR
    //             (
    //                 rp.semester = 'Ganjil'
    //                 AND b.bulan IN ('07','08','09','10','11','12')
    //                 AND SUBSTRING_INDEX(ta.periode,'/',1) = '$tahun'
    //             )
    //         )
    //     ),0) AS rencana,

    //     COALESCE(
    //     (
    //         SELECT SUM(p.jumlah)
    //         FROM pengeluaran p
    //         WHERE p.bulan = b.bulan
    //         AND p.tahun = '$tahun'
    //     ),0) AS realisasi

    //     FROM(
    //         SELECT '01' bulan
    //         UNION SELECT '02'
    //         UNION SELECT '03'
    //         UNION SELECT '04'
    //         UNION SELECT '05'
    //         UNION SELECT '06'
    //         UNION SELECT '07'
    //         UNION SELECT '08'
    //         UNION SELECT '09'
    //         UNION SELECT '10'
    //         UNION SELECT '11'
    //         UNION SELECT '12'
    //     ) b
    // ")->result_array();

	// 	foreach ($data as &$d) {

	// 		$persen = 0;

	// 		if ($d['rencana'] > 0) {
	// 			$persen = ($d['realisasi'] / $d['rencana']) * 100;
	// 		}

	// 		$d['serapan'] = round(min($persen, 100), 2);
	// 	}

	// 	return $data;
	// }
public function rencana_perbandingan_pengeluaran_result()
{
	$tahun = $this->input->post('tahun');

	$data = $this->db->query("
		SELECT 
			b.bulan,

			COALESCE((
				SELECT SUM(rpd.nominal)
				FROM rencana_pengeluaran rp
				JOIN master_tahun_ajaran ta 
					ON ta.id = rp.tahun_ajaran
				JOIN rencana_pengeluaran_detail rpd 
					ON rpd.id_rencana_pengeluaran = rp.id
				WHERE rpd.bulan = b.bulan
				AND (
					(
						b.bulan IN ('01','02','03','04','05','06')
						AND SUBSTRING_INDEX(ta.periode,'/',-1) = ?
						AND rp.semester IN ('Genap', 'Tahunan')
					)
					OR
					(
						b.bulan IN ('07','08','09','10','11','12')
						AND SUBSTRING_INDEX(ta.periode,'/',1) = ?
						AND rp.semester IN ('Ganjil', 'Tahunan')
					)
				)
			), 0) AS rencana,

			COALESCE((
				SELECT SUM(p.jumlah)
				FROM pengeluaran p
				WHERE p.bulan = b.bulan
				AND p.tahun = ?
			), 0) AS realisasi

		FROM (
			SELECT '01' bulan
			UNION SELECT '02'
			UNION SELECT '03'
			UNION SELECT '04'
			UNION SELECT '05'
			UNION SELECT '06'
			UNION SELECT '07'
			UNION SELECT '08'
			UNION SELECT '09'
			UNION SELECT '10'
			UNION SELECT '11'
			UNION SELECT '12'
		) b
	", [$tahun, $tahun, $tahun])->result_array();

	foreach ($data as &$d) {
		$rencana = (float) $d['rencana'];
		$realisasi = (float) $d['realisasi'];

		$persen = 0;
		if ($rencana > 0) {
			$persen = ($realisasi / $rencana) * 100;
		}

		$d['rencana'] = round($rencana);
		$d['realisasi'] = round($realisasi);
		$d['serapan'] = round(min($persen, 100), 2);
	}

	return $data;
}
// 	public function rencana_perbandingan_pemasukan_result()
// 	{
// 		$tahun = $this->input->post('tahun');

// 		// $tahun_ajaran = $this->db->query("SELECT * FROM master_tahun_ajaran WHERE periode LIKE '%$tahun%'")->result_array();
// 		$data = $this->db->query("
// SELECT b.bulan,

// COALESCE((
//     SELECT SUM(d.total * j.persen / 100)
//     FROM rencana_pemasukan rp
//     JOIN master_tahun_ajaran ta ON ta.id = rp.tahun_ajaran
// 	JOIN rencana_pemasukan_jenis j on j.id_rencana_pemasukan=rp.id
//     JOIN rencana_pemasukan_detail d ON d.id_jenis=j.id
//     WHERE
//     (
//         b.bulan IN ('01','02','03','04','05','06')
//         AND rp.semester='Genap'
//         AND SUBSTRING_INDEX(ta.periode,'/',-1) = '$tahun'
//     )
//     OR
//     (
//         b.bulan IN ('07','08','09','10','11','12')
//         AND rp.semester='Ganjil'
//         AND SUBSTRING_INDEX(ta.periode,'/',1) = '$tahun'
//     )
// ),0) AS rencana,

// COALESCE((
//     SELECT SUM(p.jumlah)
//     FROM pemasukan p
//     WHERE p.tahun='$tahun'
//     AND p.bulan=b.bulan
// ),0) AS realisasi

// FROM (
//     SELECT '01' bulan
//     UNION SELECT '02'
//     UNION SELECT '03'
//     UNION SELECT '04'
//     UNION SELECT '05'
//     UNION SELECT '06'
//     UNION SELECT '07'
//     UNION SELECT '08'
//     UNION SELECT '09'
//     UNION SELECT '10'
//     UNION SELECT '11'
//     UNION SELECT '12'
// ) b
// ")->result_array();

// 		foreach ($data as &$d) {
// 			$d['serapan'] = 0;
// 			$total_rencana = $d['rencana'];
// 			$realisasi = $d['realisasi'];

// 			// tentukan bulan semester
// 			$bulan = (int) $d['bulan'];

// 			if ($bulan >= 1 && $bulan <= 6) {
// 				$bulan_semester = 6;
// 			} else {
// 				$bulan_semester = 6;
// 			}

// 			// distribusi rencana per bulan
// 			$rencana_bulanan = ($bulan_semester > 0) ? ($total_rencana / $bulan_semester) : 0;

// 			// $d['rencana'] = round($rencana_bulanan, 2);
// 			$d['rencana'] = intval(round($rencana_bulanan));
// 			if ($d['rencana'] > 0) {
// 				$persen = ($realisasi / $d['rencana']) * 100;
// 				$d['serapan'] = round(min($persen, 100), 2);
// 			}
// 		}

// 		return $data;
// 	}
public function rencana_perbandingan_pemasukan_result()
{
	$tahun = $this->input->post('tahun');

	$data = $this->db->query("
		SELECT 
			b.bulan,

			COALESCE((
				SELECT SUM(d.nominal_bulan)
				FROM rencana_asumsi_pemasukan r
				JOIN master_tahun_ajaran ta 
					ON ta.id = r.tahun_ajaran
				JOIN rencana_asumsi_pemasukan_detail d 
					ON d.id_rencana_asumsi_pemasukan = r.id
				WHERE d.bulan = b.bulan
				AND (
					(
						b.bulan IN ('01','02','03','04','05','06')
						AND SUBSTRING_INDEX(ta.periode,'/',-1) = ?
						AND r.semester IN ('Genap', 'Tahunan')
					)
					OR
					(
						b.bulan IN ('07','08','09','10','11','12')
						AND SUBSTRING_INDEX(ta.periode,'/',1) = ?
						AND r.semester IN ('Ganjil', 'Tahunan')
					)
				)
			), 0) AS rencana,

			COALESCE((
				SELECT SUM(p.jumlah)
				FROM pemasukan p
				WHERE p.bulan = b.bulan
				AND p.tahun = ?
			), 0) AS realisasi

		FROM (
			SELECT '01' bulan
			UNION SELECT '02'
			UNION SELECT '03'
			UNION SELECT '04'
			UNION SELECT '05'
			UNION SELECT '06'
			UNION SELECT '07'
			UNION SELECT '08'
			UNION SELECT '09'
			UNION SELECT '10'
			UNION SELECT '11'
			UNION SELECT '12'
		) b
	", [$tahun, $tahun, $tahun])->result_array();

	foreach ($data as &$d) {
		$rencana = (float) $d['rencana'];
		$realisasi = (float) $d['realisasi'];

		$persen = 0;

		if ($rencana > 0) {
			$persen = ($realisasi / $rencana) * 100;
		}

		$d['rencana'] = round($rencana);
		$d['realisasi'] = round($realisasi);
		$d['serapan'] = round(min($persen, 100), 2);
	}

	return $data;
}
}
