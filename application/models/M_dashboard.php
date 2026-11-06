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

	// ==================== DASHBOARD SPP (READ ONLY) ====================
	// Ringkasan dan filter SPP; dipindahkan dari M_dashboard_spp tanpa mengubah SQL.
    private function spp_money($row, $key)
    {
        return isset($row[$key]) ? (float) $row[$key] : 0;
    }

    public function spp_filters()
    {
        $periode = $this->db->order_by('id', 'DESC')->get('master_tahun_ajaran')->result_array();
        $kelas = $this->db->select('id,nama_kelas,id_periode')->order_by('nama_kelas', 'ASC')->get('kelas_setting')->result_array();
        $jenis = $this->db->select('id,nama_jenis')->order_by('nama_jenis', 'ASC')->get('tagihan_jenis')->result_array();
        $active = 0;
        foreach ($periode as $p) {
            if (strtoupper(trim((string) $p['status'])) === 'AKTIF') { $active = (int) $p['id']; break; }
        }
        if (!$active && $periode) $active = (int) $periode[0]['id'];
        return array('periode' => $periode, 'kelas' => $kelas, 'jenis' => $jenis, 'active' => $active);
    }

    private function spp_scope($f, $withPeriod = true, $month = false)
    {
        $conditions = array(); $args = array();
        if ($withPeriod && $f['periode'] > 0) { $conditions[] = 'ts.id_periode = ?'; $args[] = $f['periode']; }
        if ($f['kelas'] > 0) { $conditions[] = 'ts.id_kelas_setting = ?'; $args[] = $f['kelas']; }
        if ($f['tingkat'] > 0) {
            $conditions[] = 'k.nama_kelas REGEXP ?';
            $args[] = '(^|[^0-9])' . $f['tingkat'] . '([^0-9]|$)';
        }
        if ($f['jenis'] > 0) { $conditions[] = 'ts.id_jenis_tagihan = ?'; $args[] = $f['jenis']; }
        if ($month && !empty($f['tahun_mulai'])) {
            // Juli–Juni dalam tahun ajaran. Filter YYYYMM bersifat kumulatif, bukan bulan tunggal.
            $startYm = ((int)$f['tahun_mulai'] * 100) + 7;
            $endYm = !empty($f['bulan']) ? (int)$f['bulan'] : (((int)$f['tahun_mulai'] + 1) * 100 + 6);
            $conditions[] = 'ts.bulan BETWEEN 1 AND 12 AND (ts.tahun * 100 + ts.bulan) BETWEEN ? AND ?';
            $args[] = $startYm;
            $args[] = $endYm;
        }
        return array('sql' => ($conditions ? ' AND ' . implode(' AND ', $conditions) : ''), 'params' => $args);
    }

    private function spp_activeBill()
    {
        // Dibebaskan/dibatalkan tidak masuk target piutang efektif.
        return "ts.status_tagihan='Aktif' AND ts.status_pembayaran NOT IN ('Dibatalkan','Dibebaskan')";
    }

    private function spp_billBase()
    {
        return ' FROM tagihan_siswa ts LEFT JOIN kelas_setting k ON k.id=ts.id_kelas_setting WHERE ' . $this->spp_activeBill();
    }

    private function spp_query($sql, $args = array())
    {
        return $this->db->query($sql, $args)->row_array() ?: array();
    }

    public function spp_summary($f, $canTagihan, $canTabungan)
    {
        $out = array('bulanan' => array(), 'tahunan' => array(), 'tunggakan' => array(), 'hari_ini' => array(), 'tabungan' => array(), 'grafik' => array(), 'perbandingan_jenis' => array(), 'perbandingan_kelas' => array());
        if ($canTagihan) {
            $base = $this->spp_billBase();
            $s = $this->spp_scope($f, true, true);
            $where = $base . " AND ts.tipe_tagihan='Bulanan'" . $s['sql'];
            $amount = $this->spp_query("SELECT COALESCE(SUM(ts.nominal_tagihan),0) target, COALESCE(SUM(ts.nominal_dibayar),0) dibayar, COALESCE(SUM(ts.sisa_tagihan),0) sisa" . $where, $s['params']);
            // Satu siswa dihitung sekali, meskipun mempunyai beberapa tagihan bulanan.
            $people = $this->spp_query("SELECT COALESCE(SUM(p.dibayar>0),0) sudah, COALESCE(SUM(p.dibayar=0),0) belum FROM (SELECT ts.id_siswa, SUM(ts.nominal_dibayar) dibayar" . $where . ' GROUP BY ts.id_siswa) p', $s['params']);
            $out['bulanan'] = array('sudah' => (int)($people['sudah'] ?? 0), 'belum' => (int)($people['belum'] ?? 0), 'target' => $this->spp_money($amount,'target'), 'dibayar' => $this->spp_money($amount,'dibayar'), 'sisa' => $this->spp_money($amount,'sisa'), 'persen' => $this->spp_money($amount,'target') > 0 ? min(100, round(100 * $this->spp_money($amount,'dibayar') / $this->spp_money($amount,'target'), 2)) : 0);

            $s = $this->spp_scope($f);
            $where = $base . " AND ts.tipe_tagihan IN ('Tahunan','Langsung')" . $s['sql'];
            $amount = $this->spp_query('SELECT COALESCE(SUM(ts.nominal_tagihan),0) target, COALESCE(SUM(ts.nominal_dibayar),0) dibayar, COALESCE(SUM(ts.sisa_tagihan),0) sisa' . $where, $s['params']);
            $people = $this->spp_query('SELECT COALESCE(SUM(p.sisa<=0),0) lunas, COALESCE(SUM(p.sisa>0),0) belum FROM (SELECT ts.id_siswa, SUM(ts.sisa_tagihan) sisa' . $where . ' GROUP BY ts.id_siswa) p', $s['params']);
            $out['tahunan'] = array('lunas'=>(int)($people['lunas'] ?? 0), 'belum'=>(int)($people['belum'] ?? 0), 'target'=>$this->spp_money($amount,'target'), 'dibayar'=>$this->spp_money($amount,'dibayar'), 'sisa'=>$this->spp_money($amount,'sisa'));

            // Tunggakan lintas tahun ajaran; periode terpilih tidak membatasi angka ini.
            // Tunggakan mengikuti flag pada tagihan dan masa tagih yang sudah lewat.
            $s = $this->spp_scope($f, false);
            $due = "( (STR_TO_DATE(ts.tanggal_jatuh_tempo,'%d-%m-%Y') IS NOT NULL AND STR_TO_DATE(ts.tanggal_jatuh_tempo,'%d-%m-%Y') < CURDATE()) OR (ts.tipe_tagihan='Bulanan' AND ts.tahun > 0 AND ts.bulan BETWEEN 1 AND 12 AND (ts.tahun * 100 + ts.bulan) < (YEAR(CURDATE()) * 100 + MONTH(CURDATE()))) )";
            $where = $base . " AND ts.dianggap_tunggakan='Ya' AND ts.sisa_tagihan>0 AND " . $due . $s['sql'];
            $arrears = $this->spp_query('SELECT COALESCE(SUM(ts.sisa_tagihan),0) total, COUNT(DISTINCT ts.id_siswa) siswa, COALESCE(SUM(CASE WHEN ts.id_periode=? THEN ts.sisa_tagihan ELSE 0 END),0) aktif, COALESCE(SUM(CASE WHEN ts.id_periode<>? THEN ts.sisa_tagihan ELSE 0 END),0) sebelumnya' . $where, array_merge(array($f['active'], $f['active']), $s['params']));
            $out['tunggakan'] = array('total'=>$this->spp_money($arrears,'total'), 'siswa'=>(int)($arrears['siswa'] ?? 0), 'aktif'=>$this->spp_money($arrears,'aktif'), 'sebelumnya'=>$this->spp_money($arrears,'sebelumnya'));

            $s = $this->spp_scope($f);
            $paymentDate = "COALESCE(STR_TO_DATE(p.tanggal_transaksi,'%d-%m-%Y'),STR_TO_DATE(p.tanggal_transaksi,'%Y-%m-%d'))";
            $payments = " FROM tagihan_pembayaran_detail pd INNER JOIN tagihan_pembayaran p ON p.id=pd.id_pembayaran INNER JOIN tagihan_siswa ts ON ts.id=pd.id_tagihan_siswa LEFT JOIN kelas_setting k ON k.id=ts.id_kelas_setting WHERE p.status_transaksi='Aktif' AND pd.status_detail='Aktif'";
            $today = $this->spp_query('SELECT COALESCE(SUM(pd.nominal_bayar),0) total, COUNT(DISTINCT p.id) jumlah' . $payments . ' AND ' . $paymentDate . '=CURDATE()' . $s['sql'], $s['params']);
            $out['hari_ini'] = array('total'=>$this->spp_money($today,'total'), 'jumlah'=>(int)($today['jumlah'] ?? 0));

            $chartWindow = $this->spp_chartMonths($f);
            if (!empty($f['bulan'])) {
                $endYm = (int)$f['bulan'];
                $chartWindow = array_values(array_filter($chartWindow, function ($m) use ($endYm) {
                    return (int)str_replace('-', '', $m['ym']) <= $endYm;
                }));
            }
            $from = $chartWindow[0]['date'];
            $lastMonth = end($chartWindow);
            $until = date('Y-m-d', strtotime($lastMonth['date'] . ' +1 month'));
            $paidRows = $this->db->query('SELECT DATE_FORMAT(' . $paymentDate . ", '%Y-%m') ym, COALESCE(SUM(pd.nominal_bayar),0) nominal" . $payments . ' AND ' . $paymentDate . '>=? AND ' . $paymentDate . '<?' . $s['sql'] . ' GROUP BY ym', array_merge(array($from,$until), $s['params']))->result_array();
            $paid = array(); foreach ($paidRows as $r) $paid[$r['ym']] = (float)$r['nominal'];
            $targetRows = $this->db->query("SELECT ts.tahun,ts.bulan,COALESCE(SUM(ts.nominal_tagihan),0) nominal" . $base . $s['sql'] . " AND ts.bulan BETWEEN 1 AND 12 AND ts.tahun>0 GROUP BY ts.tahun,ts.bulan", $s['params'])->result_array();
            $targets = array(); foreach ($targetRows as $r) $targets[sprintf('%04d-%02d',$r['tahun'],$r['bulan'])] = (float)$r['nominal'];
            foreach ($chartWindow as $m) $out['grafik'][] = array('label'=>$m['label'], 'target'=>$targets[$m['ym']] ?? 0, 'bayar'=>$paid[$m['ym']] ?? 0);

            $breakdowns = array('perbandingan_jenis'=>"COALESCE(NULLIF(ts.nama_jenis_tagihan,''),'Tanpa Jenis')", 'perbandingan_kelas'=>"COALESCE(NULLIF(ts.nama_kelas,''),'Tanpa Kelas')");
            foreach ($breakdowns as $key => $group) {
                $rows = $this->db->query('SELECT ' . $group . ' label, COALESCE(SUM(ts.nominal_tagihan),0) target, COALESCE(SUM(ts.nominal_dibayar),0) bayar' . $base . $s['sql'] . ' GROUP BY label ORDER BY target DESC LIMIT 12', $s['params'])->result_array();
                foreach ($rows as $r) $out[$key][] = array('label'=>$r['label'], 'target'=>(float)$r['target'], 'bayar'=>(float)$r['bayar']);
            }
        }
        if ($canTabungan) {
            $saving = array();
            foreach (array('setoran'=>'tabungan_siswa_setoran','penarikan'=>'tabungan_siswa_penarikan') as $key => $table) {
                $s = $this->spp_savingScope($f);
                $date = "COALESCE(STR_TO_DATE(t.tanggal_transaksi,'%d-%m-%Y'),STR_TO_DATE(t.tanggal_transaksi,'%Y-%m-%d'))";
                $row = $this->spp_query('SELECT COALESCE(SUM(t.nominal),0) nominal FROM ' . $table . " t LEFT JOIN kelas_setting k ON k.id=t.id_kelas_setting WHERE t.status_transaksi='Aktif' AND " . $date . '=CURDATE()' . $s['sql'], $s['params']);
                $saving[$key . '_hari_ini'] = $this->spp_money($row,'nominal');
            }
            // Saldo benar-benar seluruh siswa, tanpa dibatasi periode/kelas/jenis/bulan.
            $sums = array();
            foreach (array('setoran'=>'tabungan_siswa_setoran','penarikan'=>'tabungan_siswa_penarikan') as $key => $table) {
                $row = $this->spp_query("SELECT COALESCE(SUM(nominal),0) nominal FROM {$table} WHERE status_transaksi='Aktif'");
                $sums[$key] = $this->spp_money($row,'nominal');
            }
            $saving['saldo'] = $sums['setoran'] - $sums['penarikan'];
            $out['tabungan'] = $saving;
        }
        return $out;
    }

    private function spp_savingScope($f)
    {
        $sql = ''; $params = array();
        if ($f['periode'] > 0) { $sql .= ' AND t.id_periode=?'; $params[] = $f['periode']; }
        if ($f['kelas'] > 0) { $sql .= ' AND t.id_kelas_setting=?'; $params[] = $f['kelas']; }
        if ($f['tingkat'] > 0) { $sql .= ' AND k.nama_kelas REGEXP ?'; $params[] = '(^|[^0-9])' . $f['tingkat'] . '([^0-9]|$)'; }
        return array('sql'=>$sql,'params'=>$params);
    }

    private function spp_chartMonths($f)
    {
        $period = $this->db->select('periode')->where('id',$f['periode'])->get('master_tahun_ajaran')->row_array();
        $p = trim((string)($period['periode'] ?? ''));
        if (preg_match('/(20\d{2})\s*[\/\-]\s*(20\d{2})/', $p, $m)) {
            $start = (int)$m[1];
            $startDate = sprintf('%04d-07-01', $start);
        } else {
            $startDate = date('Y-01-01');
        }
        $names = array(1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember');
        $months = array();
        for ($i=0;$i<12;$i++) {
            $stamp = strtotime($startDate . ' +' . $i . ' months');
            $months[] = array('date'=>date('Y-m-01',$stamp),'ym'=>date('Y-m',$stamp),'label'=>$names[(int)date('n',$stamp)] . ' ' . date('Y',$stamp));
        }
        return $months;
    }
}
