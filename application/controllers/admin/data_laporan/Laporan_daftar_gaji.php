<?php
class Laporan_daftar_gaji extends CI_Controller
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

        $bulan = str_pad((int) ($ambil['filter_bulan'] ?? 0), 2, '0', STR_PAD_LEFT);
        $tahun = (int) ($ambil['filter_tahun'] ?? 0);

        $data_laporan = [];

        if ((int) $bulan >= 1 && (int) $bulan <= 12 && $tahun > 0) {
            $data_laporan = $this->db->query("SELECT p.id AS id_pegawai, p.nama_pegawai, p.no_rekening, pg.gaji_bersih 
            FROM penggajian pg JOIN pegawai p ON p.id = pg.id_pegawai WHERE pg.bulan = ? AND pg.tahun = ?", [$bulan, $tahun])->result_array();
        }

        $total_gaji_bersih = array_sum(array_column($data_laporan, 'gaji_bersih'));
        $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;

        $data = [
            'judul' => $this->getBulan($bulan) . ' ' . $tahun,
            'status' => 'Bulan',
            'data_laporan' => $data_laporan,
            'total_gaji_bersih' => $total_gaji_bersih,
            'tanggal_laporan' => $tanggal_laporan,
        ];

        $this->load->view('admin/data_laporan/laporan_daftar_gaji', $data);
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
?>
