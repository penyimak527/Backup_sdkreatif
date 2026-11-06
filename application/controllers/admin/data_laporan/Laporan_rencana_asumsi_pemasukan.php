<?php
class Laporan_rencana_asumsi_pemasukan extends CI_Controller
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
    $periode = $ambil['id_periode_form_semester'] ?? '';
if ($periode == '') {
        $data = [
        'tahun_ajaran' => '-',
        'semester' => $semester,
        'list_bulan' => [],
        'rencana_pemasukan' => []
    ];
        $this->load->view('admin/data_laporan/laporan_rencana_asumsi_pemasukan',$data);
        return;
    }
    $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', [
        'id' => $periode
    ])->row_array();

    if ($semester == 'Tahunan') {
    //     $list_bulan = ['07', '08', '09', '10', '11', '12'];
    // } elseif ($semester == 'Genap') {
    //     $list_bulan = ['01', '02', '03', '04', '05', '06'];
    // } else {
        $list_bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
    }

    $rencana_pemasukan = $this->db->query("
        SELECT
            ka.id,
            ka.keterangan AS kategori,

            COALESCE(MAX(x.total_asumsi_masuk), 0) AS total_asumsi_masuk,
            COALESCE(MAX(x.persen_masuk), 0) AS persen_masuk,
            COALESCE(MAX(x.asumsi_masuk), 0) AS asumsi_masuk,
            COALESCE(MAX(x.saving_normal), 0) AS saving_normal,
            COALESCE(MAX(x.saving_persen), 0) AS saving_persen

        FROM kode_akun ka

        LEFT JOIN (
            SELECT
                d.*
            FROM rencana_asumsi_pemasukan_detail d
            INNER JOIN rencana_asumsi_pemasukan h
                ON h.id = d.id_rencana_asumsi_pemasukan
            WHERE h.tahun_ajaran = ?
                AND h.semester = ?
        ) x ON x.kode_akun = ka.id

        WHERE ka.jenis = 'Pemasukan'

        GROUP BY ka.id, ka.keterangan
        ORDER BY ka.id ASC
    ", [$periode, $semester])->result_array();

    $detail_bulan = $this->db->query("
        SELECT
            d.kode_akun,
            d.bulan,
            SUM(d.nominal_bulan) AS nominal_bulan
        FROM rencana_asumsi_pemasukan_detail d
        INNER JOIN rencana_asumsi_pemasukan h
            ON h.id = d.id_rencana_asumsi_pemasukan
        WHERE h.tahun_ajaran = ?
            AND h.semester = ?
        GROUP BY d.kode_akun, d.bulan
    ", [$periode, $semester])->result_array();

    $bulan_per_akun = [];

    foreach ($detail_bulan as $db) {
        $kode_akun = $db['kode_akun'];
        $bulan = str_pad($db['bulan'], 2, '0', STR_PAD_LEFT);

        if (!isset($bulan_per_akun[$kode_akun])) {
            $bulan_per_akun[$kode_akun] = [];
        }

        $bulan_per_akun[$kode_akun][$bulan] = (float) $db['nominal_bulan'];
    }

    foreach ($rencana_pemasukan as &$r) {
        $kode_akun = $r['id'];

        $r['bulan'] = [];

        foreach ($list_bulan as $b) {
            $r['bulan'][$b] = $bulan_per_akun[$kode_akun][$b] ?? 0;
        }

        $r['total_asumsi_masuk'] = (float) $r['total_asumsi_masuk'];
        $r['persen_masuk'] = (float) $r['persen_masuk'];
        $r['asumsi_masuk'] = (float) $r['asumsi_masuk'];
        $r['saving_normal'] = (float) $r['saving_normal'];
        $r['saving_persen'] = (float) $r['saving_persen'];
    }
    unset($r);

    $data = [
        'tahun_ajaran' => $get_tahun_ajaran['periode'],
        'semester' => $semester,
        'list_bulan' => $list_bulan,
        'rencana_pemasukan' => $rencana_pemasukan
    ];

    $this->load->view('admin/data_laporan/laporan_rencana_asumsi_pemasukan', $data);
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
