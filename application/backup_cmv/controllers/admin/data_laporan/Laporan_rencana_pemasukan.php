<?php
class Laporan_rencana_pemasukan extends CI_Controller
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

        // $tahun = $ambil['single_filter_tahun'];
        // $semester = $ambil['semester'];
        $semester = 'Tahunan';
        $periode = $ambil['id_periode'];
        $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $periode])->row_array();

        // if ((int) $bulan >= 7) {
        //     $semester = 'Ganjil';
        //     $tahun_ajaran = $tahun . '/' . ($tahun + 1);
        //     $bulan_awal = '07';
        //     $bulan_akhir = '12';
        // } else {
        //     $semester = 'Genap';
        //     $tahun_ajaran = ($tahun - 1) . '/' . $tahun;
        //     $bulan_awal = '01';
        //     $bulan_akhir = '06';
        // }

        //     $jenis = $this->db->query("
        //     SELECT
        //         j.id,
        //         j.nama_jenis
        //     FROM rencana_pemasukan_jenis j
        //     INNER JOIN rencana_pemasukan p
        //         ON p.id=j.id_rencana_pemasukan
        //      WHERE p.bulan >= '$bulan_awal'
        //     AND p.bulan <= '$bulan_akhir'
        //     AND p.tahun = '$tahun'
        //     ORDER BY j.id ASC
        // ")->result_array();

        $jenis = $this->db->query("SELECT j.id, j.nama_jenis FROM rencana_pemasukan_jenis j
        INNER JOIN rencana_pemasukan p ON p.id=j.id_rencana_pemasukan
        WHERE p.semester = '$semester' AND p.tahun_ajaran = '$periode'
        ORDER BY j.id ASC
    ")->result_array();

        $grand_total = 0;
        foreach ($jenis as &$j) {
            $detail = $this->db->query("SELECT d.* FROM rencana_pemasukan_detail d
            INNER JOIN rencana_pemasukan_jenis j ON j.id = d.id_jenis
            INNER JOIN rencana_pemasukan p ON p.id = j.id_rencana_pemasukan
            WHERE d.id_jenis = '" . $j['id'] . "' AND p.semester = '$semester' AND p.tahun_ajaran = '$periode'
            ORDER BY d.id ASC
        ")->result_array();
            //     $detail = $this->db->query("
            //     SELECT d.*
            //     FROM rencana_pemasukan_detail d
            //     INNER JOIN rencana_pemasukan_jenis j ON j.id = d.id_jenis
            //     INNER JOIN rencana_pemasukan p ON p.id = j.id_rencana_pemasukan
            //     WHERE d.id_jenis = '" . $j['id'] . "'
            //     AND p.bulan >= '$bulan_awal'
            //     AND p.bulan <= '$bulan_akhir'
            //     AND p.tahun = '$tahun'
            //     ORDER BY d.id ASC
            // ")->result_array();

            $j['detail'] = $detail;
            $j['subtotal_volume'] = 0;
            $j['subtotal_jumlah'] = 0;
            $j['subtotal_total'] = 0;
            foreach ($detail as $d) {
                $j['subtotal_volume'] += $d['volume'];
                $j['subtotal_jumlah'] += $d['jumlah'];
                $j['subtotal_total'] += $d['total'];
            }
            $grand_total += $j['subtotal_total'];
        }
        $data = [
            'judul' => $get_tahun_ajaran['periode'],
            'semester' => $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
            'data_laporan' => $jenis,
            'grand_total' => $grand_total
        ];

        $this->load->view('admin/data_laporan/laporan_rencana_pemasukan', $data);
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
