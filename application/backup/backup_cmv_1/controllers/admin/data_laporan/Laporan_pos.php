<?php
class Laporan_pos extends CI_Controller
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

        $bulan = $ambil['filter_bulan'];
        $tahun = $ambil['filter_tahun'];

        $data_pos = [];
        $akun = $this->db->query("SELECT id, keterangan FROM kode_akun WHERE jenis = 'Pemasukan' ORDER BY id ASC")->result_array();
         foreach ($akun as $a) {

        // SALDO BULAN LALU
        $saldo_lalu = $this->db->query("
            SELECT
                COALESCE(
                    (
                        SELECT SUM(jumlah)
                        FROM pemasukan
                        WHERE id_kode_akun = '".$a['id']."'
                        AND bulan < '$bulan'
                        AND tahun = '$tahun'
                    ),0
                )
                -
                COALESCE(
                    (
                        SELECT SUM(jumlah)
                        FROM pengeluaran
                        WHERE id_kode_akun = '".$a['id']."'
                        AND bulan < '$bulan'
                        AND tahun = '$tahun'
                    ),0
                ) as saldo
        ")->row_array()['saldo'] ?? 0;

        // MASUK BULAN INI
        $masuk = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pemasukan
            WHERE id_kode_akun = '".$a['id']."'
            AND bulan = '$bulan'
            AND tahun = '$tahun'
        ")->row_array()['total'] ?? 0;

        // KELUAR BULAN INI
        $keluar = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pengeluaran
            WHERE filter_kode_akun = '".$a['id']."'
            AND bulan = '$bulan'    
            AND tahun = '$tahun'
        ")->row_array()['total'] ?? 0;
// WHERE id_kode_akun = '".$a['id']."'
        // SALDO
        $saldo = $saldo_lalu + $masuk - $keluar;
        $data_pos[] = [
            'uraian' => $a['keterangan'],
            'saldo_lalu' => $saldo_lalu,
            'masuk' => $masuk,
            'keluar' => $keluar,
            'saldo' => $saldo
        ];
    }

        $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
		$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
        $data = [
            'judul' => $this->getBulan($bulan) . " " . $tahun,
            'status' => 'Bulan',
            'data_pos' => $data_pos,
            'tanggal_laporan'	=> $tanggal_laporan
        ];

        $this->load->view('admin/data_laporan/laporan_pos', $data);
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
