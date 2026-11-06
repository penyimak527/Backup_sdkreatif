<?php
class Laporan_rencana_pengeluaran extends CI_Controller
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

    $semester = 'Tahunan';
    $periode = $ambil['id_periode_form_semester'] ?? null;

 if ($periode == '') {
        $data = [
           'list_bulan'    => [],
        'semester'      => $semester,
        'tahun_ajaran'  => '-',
        'data_laporan'  => [],
        ];

        $this->load->view('admin/data_laporan/laporan_rencana_pengeluaran', $data);
        return;
    }

    $get_tahun_ajaran = $this->db
        ->get_where('master_tahun_ajaran', ['id' => $periode])
        ->row_array();

    if (!$get_tahun_ajaran) {
        $data = [
            'list_bulan'    => [],
            'semester'      => $semester,
            'tahun_ajaran'  => '-',
            'data_laporan'  => [],
        ];

        $this->load->view('admin/data_laporan/laporan_rencana_pengeluaran', $data);
        return;
    }

    if ($semester == 'Ganjil') {
        $list_bulan = ['07', '08', '09', '10', '11', '12'];
    } elseif($semester == 'Genap'){
        $list_bulan = ['01', '02', '03', '04', '05', '06'];
    } elseif ($semester == 'Tahunan') {
		$list_bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
	}

    $kolom_bulan = [];

    foreach ($list_bulan as $bulan) {
        $kolom_bulan[] = "
            COALESCE(
                SUM(
                    CASE
                        WHEN d.bulan = '$bulan'
                        THEN d.nominal
                        ELSE 0
                    END
                ),
            0) AS bulan_$bulan
        ";
    }

    $select_bulan = implode(',', $kolom_bulan);

    $data_laporan = $this->db->query("
        SELECT 
            a.id,
            a.keterangan,
            $select_bulan
        FROM kode_akun a
        LEFT JOIN (
            SELECT 
                d.kode_akun,
                d.bulan,
                d.nominal
            FROM rencana_pengeluaran_detail d
            INNER JOIN rencana_pengeluaran rp 
                ON rp.id = d.id_rencana_pengeluaran
            WHERE 
                rp.tahun_ajaran = ?
                AND rp.semester = ?
        ) d ON d.kode_akun = a.id
        WHERE a.jenis = 'Pengeluaran'
        GROUP BY a.id, a.keterangan
        ORDER BY a.id ASC
    ", [$periode, $semester])->result_array();

    $data = [
        'list_bulan'    => $list_bulan,
        'semester'      => $semester,
        'tahun_ajaran'  => $get_tahun_ajaran['periode'],
        'data_laporan'  => $data_laporan,
    ];

    $this->load->view('admin/data_laporan/laporan_rencana_pengeluaran', $data);
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
