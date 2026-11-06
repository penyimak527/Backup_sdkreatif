<?php
class Laporan_perbandingan_rencana_pemasukan extends CI_Controller
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
    $tahun_ajaran = $ambil['id_periode_form_semester'] ?? '';

    if ($tahun_ajaran == '') {
        $data = [
            'semester' => $semester,
            'tahun_ajaran' => '-',
            'bulan_laporan' => [],
            'data_laporan' => []
        ];
        $this->load->view('admin/data_laporan/laporan_perbandingan_rencana_pemasukan', $data);
        return;
    }
    $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', [
        'id' => $tahun_ajaran
    ])->row_array();

    list($tahun_awal, $tahun_akhir) = explode('/', $get_tahun_ajaran['periode']);

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
        WHERE jenis = 'Pemasukan'
        ORDER BY id ASC
    ")->result_array();

    $data_laporan = [];

    foreach ($akun as $a) {

        $row = [
            'kode_akun' => $a['keterangan'],
            'asumsi'    => 0,
            'bulan'     => []
        ];

        foreach ($bulan_laporan as $bln) {
            $row['bulan'][$bln] = [
                'rencana'   => 0,
                'realisasi' => 0
            ];
        }

        /*
         * 1. ASUMSI MASUK
         * Diambil dari total per kode akun rencana pemasukan.
         */
        $asumsi = $this->db->query("
            SELECT
                COALESCE(SUM(d.total * (j.persen / 100)), 0) AS total_asumsi
            FROM rencana_pemasukan_jenis j
            JOIN rencana_pemasukan_detail d
                ON d.id_jenis = j.id
            JOIN rencana_pemasukan r
                ON r.id = j.id_rencana_pemasukan
            WHERE j.kode_akun = '".$a['id']."'
            AND r.tahun_ajaran = '$tahun_ajaran'
            AND r.semester = '$semester'
        ")->row_array();

        $row['asumsi'] = (float) ($asumsi['total_asumsi'] ?? 0);

        /*
         * 2. RENCANA BULANAN
         * Diambil dari rencana_asumsi_pemasukan_detail.nominal_bulan.
         */
        $rencana_bulanan = $this->db->query("
            SELECT
                d.bulan,
                COALESCE(SUM(d.nominal_bulan), 0) AS total_rencana
            FROM rencana_asumsi_pemasukan_detail d
            JOIN rencana_asumsi_pemasukan r
                ON r.id = d.id_rencana_asumsi_pemasukan
            WHERE d.kode_akun = '".$a['id']."'
            AND r.tahun_ajaran = '$tahun_ajaran'
            AND r.semester = '$semester'
            GROUP BY d.bulan
        ")->result_array();

        foreach ($rencana_bulanan as $rb) {
            $bulan_int = (int) $rb['bulan'];

            if (isset($row['bulan'][$bulan_int])) {
                $row['bulan'][$bulan_int]['rencana'] += (float) $rb['total_rencana'];
            }
        }

        /*
         * 3. REALISASI PEMASUKAN
         * Dari tabel pemasukan berdasarkan tahun ajaran.
         */
        foreach ($bulan_laporan as $b) {
            $bulan_db = str_pad($b, 2, '0', STR_PAD_LEFT);

            if ($b >= 7) {
                $tahun_realisasi = $tahun_awal;
            } else {
                $tahun_realisasi = $tahun_akhir;
            }

            $pemasukan = $this->db->query("
                SELECT COALESCE(SUM(jumlah), 0) AS total
                FROM pemasukan
                WHERE id_kode_akun = '".$a['id']."'
                AND bulan = '$bulan_db'
                AND tahun = '$tahun_realisasi'
            ")->row_array();

            $row['bulan'][$b]['realisasi'] = (float) ($pemasukan['total'] ?? 0);
        }

        $data_laporan[] = $row;
    }

    $data = [
        // 'status'        => 'Tahun Ajaran',
        'semester'      => $semester,
        'tahun_ajaran'  => $get_tahun_ajaran['periode'],
        'bulan_laporan' => $bulan_laporan,
        'data_laporan'  => $data_laporan
    ];

    $this->load->view(
        'admin/data_laporan/laporan_perbandingan_rencana_pemasukan',
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
