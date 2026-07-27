<?php
class Laporan_rencana_anggaran extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        header('Access-Control-Allow-Origin: *');
    }

//     public function print_laporan()
//     {
//         $json = file_get_contents('php://input');
//         $ambil = json_decode($json, true);

//         $bulan = $ambil['filter_bulan'];
//         $tahun = $ambil['filter_tahun'];
// $bulan_int = (int)$bulan;

// if ($bulan_int >= 7) {
//     $semester = 'Genap';
//     $tahun_ajaran = $tahun . '/' . ($tahun + 1);
// } else {
//     $semester = 'Ganjil';
//     $tahun_ajaran = ($tahun - 1) . '/' . $tahun;
// }
// $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran',['periode' => $tahun_ajaran])->row_array();
 
//     $id_tahun_ajaran = $get_tahun_ajaran['id'] ?? '';
// $pengeluaran = $this->db->query("
//     SELECT
//         a.keterangan,
//         COALESCE(SUM(
//             CASE
//                 WHEN rp.id IS NOT NULL THEN rpd.nominal
//                 ELSE 0
//             END
//         ),0) as total
//     FROM kode_akun a
//     LEFT JOIN rencana_pengeluaran_detail rpd ON a.id = rpd.kode_akun AND rpd.bulan = '$bulan'
//     LEFT JOIN rencana_pengeluaran rp ON rp.id = rpd.id_rencana_pengeluaran AND rp.tahun_ajaran = '$id_tahun_ajaran' AND rp.semester = '$semester'
//     WHERE a.jenis = 'Pengeluaran' GROUP BY a.id ORDER BY a.id ASC
// ")->result_array();

//         $total_rencana = array_sum(array_column($pengeluaran, 'total'));
//         $saldo_bulan_lalu = 0;

// for ($i = 1; $i < $bulan_int; $i++) {

//     $bulan_loop = str_pad($i, 2, '0', STR_PAD_LEFT);

//     // Tentukan tahun ajaran & semester sesuai bulan yang sedang di-loop
//     if ($i >= 7) {
//         $semester_loop = 'Ganjil';
//         $periode_loop = $tahun . '/' . ($tahun + 1);
//     } else {
//         $semester_loop = 'Genap';
//         $periode_loop = ($tahun - 1) . '/' . $tahun;
//     }

//     $ta_loop = $this->db
//         ->get_where('master_tahun_ajaran', [
//             'periode' => $periode_loop
//         ])
//         ->row_array();

//     $id_ta_loop = $ta_loop['id'] ?? 0;

//     // Total rencana bulan tersebut
//     $rencana_loop = $this->db->query("
//         SELECT COALESCE(SUM(rpd.nominal),0) as total
//         FROM rencana_pengeluaran_detail rpd
//         JOIN rencana_pengeluaran rp
//             ON rp.id = rpd.id_rencana_pengeluaran
//         WHERE rpd.bulan = '$bulan_loop'
//         AND rp.tahun_ajaran = '$id_ta_loop'
//         AND rp.semester = '$semester_loop'
//     ")->row_array()['total'] ?? 0;

//     // Total realisasi bulan tersebut
//     $realita_loop = $this->db->query("
//         SELECT COALESCE(SUM(jumlah),0) as total
//         FROM pengeluaran
//         WHERE bulan = '$bulan_loop'
//         AND tahun = '$tahun'
//     ")->row_array()['total'] ?? 0;

//     $saldo_bulan_lalu += ($rencana_loop - $realita_loop);

 
    
// }
//         $saldo_bulan_lalu = 0;
// if ($semester == 'Genap') {
//     $list_bulan = [7,8,9,10,11,12];
// } else {
//     $list_bulan = [1,2,3,4,5,6];
// }
// foreach ($list_bulan as $bln) {

//     if ($bln >= $bulan_int) {
//         break;
//     }

//     $bulan_loop = str_pad($bln, 2, '0', STR_PAD_LEFT);

//     $rencana_loop = $this->db->query("
//         SELECT COALESCE(SUM(rpd.nominal),0) as total
//         FROM rencana_pengeluaran_detail rpd
//         JOIN rencana_pengeluaran rp
//             ON rp.id = rpd.id_rencana_pengeluaran
//         WHERE rpd.bulan = '$bulan_loop'
//         AND rp.tahun_ajaran = '$id_tahun_ajaran'
//         AND rp.semester = '$semester'
//     ")->row_array()['total'] ?? 0;

//     $realita_loop = $this->db->query("
//         SELECT COALESCE(SUM(jumlah),0) as total
//         FROM pengeluaran
//         WHERE bulan = '$bulan_loop'
//         AND tahun = '$tahun'
//     ")->row_array()['total'] ?? 0;
//  echo "Bulan : ".$bulan_loop;
// echo "<br>Rencana : ".$rencana_loop;
// echo "<br>Realisasi : ".$realita_loop;
// echo "<hr>";
//     $saldo_bulan_lalu += ($rencana_loop - $realita_loop);
// }

    //     if ($total_rencana == 0) {
    //         $pengajuan_anggaran = 0;
    //     } else {
    //         $pengajuan_anggaran = $total_rencana - $saldo_bulan_lalu;
    //     }
    //     $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
	// 	$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
    //     $data = [
    //         'judul' => $this->getBulan($bulan) . " " . $tahun,
    //         'status' => 'Bulan',
    //         'pengeluaran' => $pengeluaran,
    //         'pengajuan_anggaran' => $pengajuan_anggaran,
    //         'saldo_bulan_lalu' => $saldo_bulan_lalu,
    //         'total_rencana' => $total_rencana,
    //         'tanggal_laporan'	=> $tanggal_laporan
    //     ];

    //     $this->load->view('admin/data_laporan/laporan_rencana_anggaran', $data);
    // }

public function print_laporan()
{
    $json = file_get_contents('php://input');
    $ambil = json_decode($json, true);

    $bulan = $ambil['filter_bulan'] ?? '';
    $tahun = $ambil['filter_tahun'] ?? '';
    
    $bulan_int = (int) $bulan;
    $tahun_int = (int) $tahun;
    $bulan = str_pad($bulan_int, 2, '0', STR_PAD_LEFT);

    $semester_rencana = 'Tahunan';

    if ($bulan_int >= 7) {
        $tahun_ajaran = $tahun_int . '/' . ($tahun_int + 1);
    } else {
        $tahun_ajaran = ($tahun_int - 1) . '/' . $tahun_int;
    }

    $get_tahun_ajaran = $this->db
        ->get_where('master_tahun_ajaran', [
            'periode' => $tahun_ajaran
        ])
        ->row_array();

    $id_tahun_ajaran = $get_tahun_ajaran['id'] ?? 0;

    $pengeluaran = $this->db->query("
        SELECT
            a.keterangan,
            COALESCE(SUM(
                CASE
                    WHEN rp.id IS NOT NULL THEN rpd.nominal
                    ELSE 0
                END
            ), 0) AS total
        FROM kode_akun a
        LEFT JOIN rencana_pengeluaran_detail rpd
            ON a.id = rpd.kode_akun
            AND rpd.bulan = " . $this->db->escape($bulan) . "
        LEFT JOIN rencana_pengeluaran rp
            ON rp.id = rpd.id_rencana_pengeluaran
            AND rp.tahun_ajaran = " . $this->db->escape($id_tahun_ajaran) . "
            AND rp.semester = " . $this->db->escape($semester_rencana) . "
        WHERE a.jenis = 'Pengeluaran'
        GROUP BY a.id
        ORDER BY a.id ASC
    ")->result_array();

    $total_rencana = array_sum(array_column($pengeluaran, 'total'));

    $saldo_bulan_lalu = 0;

    for ($i = 1; $i < $bulan_int; $i++) {
        $bulan_loop = str_pad($i, 2, '0', STR_PAD_LEFT);

        if ($i >= 7) {
            $periode_loop = $tahun_int . '/' . ($tahun_int + 1);
        } else {
            $periode_loop = ($tahun_int - 1) . '/' . $tahun_int;
        }

        $ta_loop = $this->db
            ->get_where('master_tahun_ajaran', [
                'periode' => $periode_loop
            ])
            ->row_array();

        $id_ta_loop = $ta_loop['id'] ?? 0;

        $rencana_loop = $this->db->query("
            SELECT COALESCE(SUM(rpd.nominal), 0) AS total
            FROM rencana_pengeluaran_detail rpd
            JOIN rencana_pengeluaran rp
                ON rp.id = rpd.id_rencana_pengeluaran
            WHERE rpd.bulan = " . $this->db->escape($bulan_loop) . "
            AND rp.tahun_ajaran = " . $this->db->escape($id_ta_loop) . "
            AND rp.semester = " . $this->db->escape($semester_rencana) . "
        ")->row_array();

        $total_rencana_loop = (float) ($rencana_loop['total'] ?? 0);

        $realita_loop = $this->db->query("
            SELECT COALESCE(SUM(p.jumlah), 0) AS total
            FROM pengeluaran p
            JOIN kode_akun a
                ON a.id = p.id_kode_akun
            WHERE p.bulan = " . $this->db->escape($bulan_loop) . "
            AND p.tahun = " . $this->db->escape($tahun_int) . "
            AND a.jenis = 'Pengeluaran'
        ")->row_array();

        $total_realita_loop = (float) ($realita_loop['total'] ?? 0);

        $saldo_bulan_lalu += ($total_rencana_loop - $total_realita_loop);
    }

    if ($total_rencana == 0) {
        $pengajuan_anggaran = 0;
    } else {
        $pengajuan_anggaran = $total_rencana - $saldo_bulan_lalu;
    }

    $tanggal_terakhir = date('t', strtotime($tahun_int . '-' . $bulan . '-01'));
    $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun_int;

    $data = [
        'judul'              => $this->getBulan($bulan) . " " . $tahun_int,
        'status'             => 'Bulan',
        'pengeluaran'        => $pengeluaran,
        'pengajuan_anggaran' => $pengajuan_anggaran,
        'saldo_bulan_lalu'   => $saldo_bulan_lalu,
        'total_rencana'      => $total_rencana,
        'tanggal_laporan'    => $tanggal_laporan
    ];

    $this->load->view('admin/data_laporan/laporan_rencana_anggaran', $data);
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
