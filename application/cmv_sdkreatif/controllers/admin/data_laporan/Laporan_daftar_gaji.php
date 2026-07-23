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

        $bulan = $ambil['filter_bulan'];
        $tahun = $ambil['filter_tahun'];
        $pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan ORDER BY id_pegawai asc")->result_array();

        $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
		$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
        $data = [
            'judul' => $this->getBulan($bulan) . " " . $tahun,
            'title' => 'TTD Penerima Gaji',
            'status' => 'Bulan',
            'penerimaan_gaji' => $pegawai_list,
            'tanggal_laporan'	=> $tanggal_laporan
        ];

        $this->load->view('admin/data_laporan/laporan_ttd_penerimaan_gaji', $data);
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