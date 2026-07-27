<?php
class Laporan_olah_pos extends CI_Controller
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
        $tahun = $ambil['single_filter_tahun'];
        $akun = $this->db->query("SELECT * FROM kode_akun WHERE jenis = 'Pemasukan' ORDER BY id ASC")->result_array();

        $hasil = [];

        foreach ($akun as $a) {
            $row = [
                'kode_akun' => $a['keterangan']
            ];
            $saldo_sebelumnya = 0;
            // looping bulan 1 - 12
            for ($b = 1; $b <= 12; $b++) {
                // pemasukan
                $masuk = $this->db->query("
                SELECT COALESCE(SUM(jumlah),0) as total
                FROM pemasukan
                WHERE id_kode_akun = '" . $a['id'] . "'
                AND bulan = '$b'
                AND tahun = '$tahun'
            ")->row_array();

                // pengeluaran
                $keluar = $this->db->query("
                SELECT COALESCE(SUM(jumlah),0) as total
                FROM pengeluaran
                WHERE id_kode_akun = '" . $a['id'] . "'
                AND bulan = '$b'
                AND tahun = '$tahun'
            ")->row_array();

                $total_masuk = $masuk['total'];
                $total_keluar = $keluar['total'];

                // CEK ADA TRANSAKSI ATAU TIDAK
                if ($total_masuk == 0 && $total_keluar == 0) {
                    $row['bulan'][$b] = [
                        'masuk' => 0,
                        'keluar' => 0,
                        'saldo' => 0
                    ];
                } else {
                    // SALDO BERJALAN
                    $saldo = $saldo_sebelumnya + $total_masuk - $total_keluar;
                    // SIMPAN SALDO SEBELUMNYA
                    $saldo_sebelumnya = $saldo;
                    $row['bulan'][$b] = [
                        'masuk' => $total_masuk,
                        'keluar' => $total_keluar,
                        'saldo' => $saldo
                    ];
                }
            }
            $hasil[] = $row;
        }

        $data = [
            'judul' => 'Laporan Olah Pos Tahun ' . $tahun,
            'status' => 'Tahun',
            'tahun' => $tahun,
            'data_laporan' => $hasil
        ];

        $this->load->view('admin/data_laporan/laporan_olah_pos', $data);
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
