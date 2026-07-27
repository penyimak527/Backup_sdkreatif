<?php
class Laporan_perbandingan_rencana_pengeluaran extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		header('Access-Control-Allow-Origin: *');
	}

	// public function print_laporan()
	// {
	// 	$json = file_get_contents('php://input');
	// 	$ambil = json_decode($json, true);

	// 	$tahun = $ambil['single_filter_tahun'];
	// 	$akun = $this->db->query("SELECT * FROM kode_akun WHERE jenis = 'Pengeluaran' ORDER BY id ASC")->result_array();

	// 	$data_laporan = [];
	// 	foreach ($akun as $a) {
	// 		$row = [
	// 			'kode_akun' => $a['keterangan']
	// 		];

	// 		for ($b = 1; $b <= 12; $b++) {

	// 			$pengeluaran = $this->db->query("
	//         SELECT COALESCE(SUM(jumlah),0) as total
	//         FROM pengeluaran
	//         WHERE id_kode_akun = '" . $a['id'] . "'
	//         AND bulan = '$b'
	//         AND tahun = '$tahun'
	//     ")->row_array();

	// 			$rencana_pengeluaran = $this->db->query("
	//         SELECT COALESCE(SUM(nominal),0) as total
	//         FROM rencana_pengeluaran
	//         WHERE kode_akun = '" . $a['id'] . "'
	//         AND bulan = '$b'
	//         AND tahun = '$tahun'
	//     ")->row_array();

	// 			$row['bulan'][$b] = [
	// 				'rencana' => $rencana_pengeluaran['total'],
	// 				'realisasi' => $pengeluaran['total']
	// 			];

	// 		}

	// 		$data_laporan[] = $row;
	// 	}

	// 	$data = [
	// 		'judul' => $tahun,
	// 		'status' => 'Tahun',
	// 		'data_laporan' => $data_laporan,
	// 		'tanggal_laporan' => '31 Desember ' . $tahun
	// 	];

	// 	$this->load->view('admin/data_laporan/laporan_perbandingan_rencana_pengeluaran', $data);
	// }
// 	public function print_laporan()
// 	{
// 		$json = file_get_contents('php://input');
// 		$ambil = json_decode($json, true);

// $semester = 'Tahunan';
// 		$id_tahun_ajaran = $ambil['id_periode'];

// 		$get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran',['id' => $id_tahun_ajaran])->row_array();

// 		// pecah periode 2025/2026
// 		list($tahun_awal, $tahun_akhir) = explode('/', $get_tahun_ajaran['periode']);
// 		if (empty($semester)) {
// 			$bulan_laporan = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
// 		} elseif ($semester == 'Ganjil') {
// 			$bulan_laporan = [7, 8, 9, 10, 11, 12];
// 		} else {
// 			$bulan_laporan = [1, 2, 3, 4, 5, 6];
// 		}

// 		$where_semester = '';

// 		if (!empty($semester)) {
// 			$where_semester = "AND r.semester = '" . $semester . "'";
// 		}
// 		$akun = $this->db->query("
//         SELECT * 
//         FROM kode_akun 
//         WHERE jenis = 'Pengeluaran' 
//         ORDER BY id ASC
//     ")->result_array();

// 		$data_laporan = [];

// 		foreach ($akun as $a) {

// 			$row = [
// 				'kode_akun' => $a['keterangan']
// 			];

// 			foreach ($bulan_laporan as $b) {
// 				if ($b >= 7) {
// 					$tahun_realisasi = $tahun_awal;   // Juli–Desember
// 				} else {
// 					$tahun_realisasi = $tahun_akhir;  // Jan–Juni
// 				}
// 				$bulan_db = str_pad($b, 2, '0', STR_PAD_LEFT);
// 				$pengeluaran = $this->db->query("
//                 SELECT COALESCE(SUM(jumlah),0) as total
//                 FROM pengeluaran
//                 WHERE id_kode_akun = '" . $a['id'] . "'
//                 AND bulan = '$bulan_db'
//                 AND tahun = '$tahun_realisasi'
//             ")->row_array();


// 				$rencana_pengeluaran = $this->db->query("
//                 SELECT COALESCE(SUM(d.nominal),0) as total
//                 FROM rencana_pengeluaran_detail d
//                 JOIN rencana_pengeluaran r 
//                     ON r.id = d.id_rencana_pengeluaran
//                 WHERE d.kode_akun = '" . $a['id'] . "'
//                 AND d.bulan = '$bulan_db'
//                 AND r.tahun_ajaran = '" . $id_tahun_ajaran . "'
//                 $where_semester
//             ")->row_array();

// 				$row['bulan'][$b] = [
// 					'rencana' => $rencana_pengeluaran['total'],
// 					'realisasi' => $pengeluaran['total']
// 				];
// 			}

// 			$data_laporan[] = $row;
// 		}

// 		$data = [
// 			'semester' => $semester,
// 			'tahun_ajaran' => $get_tahun_ajaran['periode'],
// 			'status' => 'Tahun Ajaran',
// 			'data_laporan' => $data_laporan,
// 			'bulan_laporan' => $bulan_laporan,
// 			// 'tanggal_laporan' => '31 Desember ' . date('Y')
// 		];

// 		$this->load->view(
// 			'admin/data_laporan/laporan_perbandingan_rencana_pengeluaran',
// 			$data
// 		);
// 	}
public function print_laporan()
{
    $json = file_get_contents('php://input');
    $ambil = json_decode($json, true);

    $semester = 'Tahunan';
    $id_tahun_ajaran = $ambil['id_periode'] ?? '';

    if ($id_tahun_ajaran == '') {
        show_error('Tahun ajaran wajib dipilih.');
        return;
    }

    $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', [
        'id' => $id_tahun_ajaran
    ])->row_array();

    if (!$get_tahun_ajaran) {
        show_error('Tahun ajaran tidak ditemukan.');
        return;
    }

    list($tahun_awal, $tahun_akhir) = explode('/', $get_tahun_ajaran['periode']);

    /*
     * Karena laporan menggunakan Tahun Ajaran dan semester Tahunan,
     * maka bulan laporan adalah Juli - Juni.
     */
    if ($semester == 'Tahunan') {
        $bulan_laporan = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
    } elseif ($semester == 'Ganjil') {
        $bulan_laporan = [7, 8, 9, 10, 11, 12];
    } else {
        $bulan_laporan = [1, 2, 3, 4, 5, 6];
    }

    $akun = $this->db->query("
        SELECT *
        FROM kode_akun
        WHERE jenis = 'Pengeluaran'
        ORDER BY id ASC
    ")->result_array();

    $data_laporan = [];

    foreach ($akun as $a) {

        $row = [
            'kode_akun'    => $a['keterangan'],
            'total_rencana'=> 0,
            'bulan'        => []
        ];

        foreach ($bulan_laporan as $b) {
            $bulan_db = str_pad($b, 2, '0', STR_PAD_LEFT);

            /*
             * Mapping tahun realisasi:
             * Juli - Desember memakai tahun_awal.
             * Januari - Juni memakai tahun_akhir.
             */
            if ($b >= 7) {
                $tahun_realisasi = $tahun_awal;
            } else {
                $tahun_realisasi = $tahun_akhir;
            }

            /*
             * Ambil realisasi pengeluaran.
             */
            $pengeluaran = $this->db->query("
                SELECT COALESCE(SUM(jumlah), 0) AS total
                FROM pengeluaran
                WHERE id_kode_akun = '".$a['id']."'
                AND bulan = '$bulan_db'
                AND tahun = '$tahun_realisasi'
            ")->row_array();

            /*
             * Ambil rencana pengeluaran.
             * Karena rencana_pengeluaran_detail sudah per bulan,
             * maka tidak perlu dibagi rata seperti pemasukan.
             */
            $rencana_pengeluaran = $this->db->query("
                SELECT COALESCE(SUM(d.nominal), 0) AS total
                FROM rencana_pengeluaran_detail d
                JOIN rencana_pengeluaran r
                    ON r.id = d.id_rencana_pengeluaran
                WHERE d.kode_akun = '".$a['id']."'
                AND d.bulan = '$bulan_db'
                AND r.tahun_ajaran = '$id_tahun_ajaran'
                AND r.semester = '$semester'
            ")->row_array();

            $rencana = (float) ($rencana_pengeluaran['total'] ?? 0);
            $realisasi = (float) ($pengeluaran['total'] ?? 0);

            $row['bulan'][$b] = [
                'rencana'   => $rencana,
                'realisasi' => $realisasi
            ];

            $row['total_rencana'] += $rencana;
        }

        $data_laporan[] = $row;
    }

    $data = [
        'semester'      => $semester,
        'tahun_ajaran'  => $get_tahun_ajaran['periode'],
        'status'        => 'Tahun Ajaran',
        'data_laporan'  => $data_laporan,
        'bulan_laporan' => $bulan_laporan
    ];

    $this->load->view(
        'admin/data_laporan/laporan_perbandingan_rencana_pengeluaran',
        $data
    );
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
