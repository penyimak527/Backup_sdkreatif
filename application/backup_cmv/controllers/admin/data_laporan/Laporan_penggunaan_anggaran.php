<?php
class Laporan_penggunaan_anggaran extends CI_Controller
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

	// 	$bulan = $ambil['filter_bulan'];
	// 	$tahun = $ambil['filter_tahun'];

	// 	$total_pengajuan = $this->db->query("
	// 	    SELECT COALESCE(SUM(nominal),0) as total
	// 	    FROM rencana_pengeluaran WHERE bulan = '$bulan' AND tahun = '$tahun'
	// 	")->row_array()['total'];

	// 	$data_laporan = $this->db->query("
	// 	    SELECT 
	// 	        a.keterangan as kode_akun,
	// 	        COALESCE(SUM(p.jumlah),0) as realisasi
	// 	    FROM kode_akun a
	// 	    LEFT JOIN pengeluaran p 
	// 	        ON a.id = p.id_kode_akun
	// 	        AND p.bulan = '$bulan'
	// 	        AND p.tahun = '$tahun'
	// 	    WHERE a.jenis = 'Pengeluaran'
	// 	    GROUP BY a.id, a.keterangan
	// 	    ORDER BY a.id ASC
	// 	")->result_array();

	// 	$total_realisasi = 0;
	// 	foreach ($data_laporan as $d) {
	// 		$total_realisasi += $d['realisasi'];
	// 	}

	// 	$saldo_bulan_ini = $total_pengajuan - $total_realisasi;

	// 	$tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
	// 	$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
	// 	$data = [
	// 		'judul' => $this->getBulan($bulan) . " " . $tahun,
	// 		'status' => 'Bulan',
	// 		'data_laporan' => $data_laporan,
	// 		'total_pengajuan' => $total_pengajuan,
	// 		'total_realisasi' => $total_realisasi,
	// 		'saldo_bulan_ini' => $saldo_bulan_ini,
	// 		'tanggal_laporan'	=> $tanggal_laporan
	// 	];

	// 	$this->load->view('admin/data_laporan/laporan_penggunaan_anggaran', $data);
	// }

	public function print_laporan()
{
    $json = file_get_contents('php://input');
    $ambil = json_decode($json, true);

    $bulan_raw = (int) ($ambil['filter_bulan'] ?? 0);
    $tahun = (int) ($ambil['filter_tahun'] ?? 0);
    $bulan = str_pad($bulan_raw, 2, '0', STR_PAD_LEFT);
    $semester_rencana = 'Tahunan';

    if ($bulan_raw >= 7) {
        $tahun_ajaran_text = $tahun . '/' . ($tahun + 1);
    } else {
        $tahun_ajaran_text = ($tahun - 1) . '/' . $tahun;
    }

    $get_ta = $this->db->get_where('master_tahun_ajaran', [
        'periode' => $tahun_ajaran_text
    ])->row_array();

    $id_tahun_ajaran = $get_ta['id'] ?? 0;

    $total_pengajuan = $this->db->query("
        SELECT COALESCE(SUM(rpd.nominal), 0) AS total
        FROM rencana_pengeluaran_detail rpd
        JOIN rencana_pengeluaran rp 
            ON rp.id = rpd.id_rencana_pengeluaran
        WHERE rp.tahun_ajaran = " . $this->db->escape($id_tahun_ajaran) . "
          AND rp.semester = " . $this->db->escape($semester_rencana) . "
          AND rpd.bulan = " . $this->db->escape($bulan) . "
    ")->row_array()['total'];

    $data_laporan = $this->db->query("
        SELECT 
            a.keterangan AS kode_akun,
            COALESCE(SUM(p.jumlah), 0) AS realisasi
        FROM kode_akun a
        LEFT JOIN pengeluaran p 
            ON a.id = p.id_kode_akun
            AND p.bulan = " . $this->db->escape($bulan) . "
            AND p.tahun = " . $this->db->escape($tahun) . "
        WHERE a.jenis = 'Pengeluaran'
        GROUP BY a.id, a.keterangan
        ORDER BY a.id ASC
    ")->result_array();

    $total_realisasi = 0;
    foreach ($data_laporan as $d) {
        $total_realisasi += (float) $d['realisasi'];
    }

    $saldo_bulan_ini = 0;
    for ($i = 1; $i <= $bulan_raw; $i++) {
        $bulan_loop = str_pad($i, 2, '0', STR_PAD_LEFT);

        if ($i >= 7) {
            $periode_loop = $tahun . '/' . ($tahun + 1);
        } else {
            $periode_loop = ($tahun - 1) . '/' . $tahun;
        }

        $ta_loop = $this->db->get_where('master_tahun_ajaran', [
            'periode' => $periode_loop
        ])->row_array();

        $id_ta_loop = $ta_loop['id'] ?? 0;

        $rencana_loop = $this->db->query("
            SELECT COALESCE(SUM(rpd.nominal), 0) AS total
            FROM rencana_pengeluaran_detail rpd
            JOIN rencana_pengeluaran rp
                ON rp.id = rpd.id_rencana_pengeluaran
            WHERE rp.tahun_ajaran = " . $this->db->escape($id_ta_loop) . "
              AND rp.semester = " . $this->db->escape($semester_rencana) . "
              AND rpd.bulan = " . $this->db->escape($bulan_loop) . "
        ")->row_array();

        $total_rencana_loop = (float) ($rencana_loop['total'] ?? 0);

        $realisasi_loop = $this->db->query("
            SELECT COALESCE(SUM(p.jumlah), 0) AS total
            FROM pengeluaran p
            JOIN kode_akun a 
                ON a.id = p.id_kode_akun
            WHERE p.tahun = " . $this->db->escape($tahun) . "
              AND p.bulan = " . $this->db->escape($bulan_loop) . "
              AND a.jenis = 'Pengeluaran'
        ")->row_array();

        $total_realisasi_loop = (float) ($realisasi_loop['total'] ?? 0);
        $saldo_bulan_ini += ($total_rencana_loop - $total_realisasi_loop);
    }

    $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
    $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;

    $data = [
        'judul' => $this->getBulan($bulan) . " " . $tahun,
        'status' => 'Bulan',
        'data_laporan' => $data_laporan,
        'total_pengajuan' => $total_pengajuan,
        'total_realisasi' => $total_realisasi,
        'saldo_bulan_ini' => $saldo_bulan_ini,
        'tanggal_laporan' => $tanggal_laporan
    ];

    $this->load->view('admin/data_laporan/laporan_penggunaan_anggaran', $data);
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
