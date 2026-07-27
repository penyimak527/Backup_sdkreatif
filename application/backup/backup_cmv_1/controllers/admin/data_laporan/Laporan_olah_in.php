<?php
class Laporan_olah_in extends CI_Controller
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

        $data_laporan = [];
		foreach ($akun as $a) {
			$row = [
				'kode_akun' => $a['keterangan']
			];

			for ($b = 1; $b <= 12; $b++) {
				$masuk = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pemasukan WHERE id_kode_akun = '" . $a['id'] . "' 
			AND bulan = '$b' AND tahun = '$tahun'
        ")->row_array();

				$row['bulan'][$b] = $masuk['total'];
			}

			$data_laporan[] = $row;
		}
		$data = [
			'judul' =>  $tahun,
			'status' => 'Tahun',
			'tahun' => $tahun,
			'data_laporan' => $data_laporan,
		];

		$this->load->view('admin/data_laporan/laporan_olah_in', $data);
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
