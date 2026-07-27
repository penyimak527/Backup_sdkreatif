<?php
class Laporan_kas extends CI_Controller
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
		if ($ambil['filter'] == 'tanggal') {
			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));
			$tahun = date('Y', strtotime($ambil['dari_tanggal']));

// saldo_awal sebelum tahun dipilih
    $saldo_awal_lama = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun < '$tahun'
    ")->row()->total;

    // saldo_awal tahun yang dipilih
    $saldo_awal_sekarang = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun = '$tahun'
    ")->row()->total;

    // pemasukan sebelum tahun dipilih
    $pemasukan_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pemasukan
        WHERE tahun < '$tahun'
    ")->row()->total;

    // pengeluaran sebelum tahun dipilih
    $pengeluaran_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pengeluaran
        WHERE tahun < '$tahun'
    ")->row()->total;

			$saldo_awal = $saldo_awal_lama + $pemasukan_lama - $pengeluaran_lama + $saldo_awal_sekarang;

			$saldo_bulan_lalu = $this->db->query("
		SELECT 
		    (
		        (SELECT COALESCE(SUM(jumlah),0) FROM pemasukan 
		         WHERE STR_TO_DATE(tanggal_input, '%d-%m-%Y') < STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND tahun = '$tahun')
		        -
		        (SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran 
		         WHERE STR_TO_DATE(tanggal_input, '%d-%m-%Y') < STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND tahun = '$tahun')
		    ) as saldo
		")->row_array()['saldo'] ?? 0;

			$pemasukan = $this->db->query("
		    SELECT a.keterangan, COALESCE(SUM(p.jumlah),0) as total
		    FROM kode_akun a
		    LEFT JOIN pemasukan p 
		        ON a.id = p.id_kode_akun
				AND STR_TO_DATE(p.tanggal_input, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')
		    WHERE a.jenis = 'Pemasukan'
		    GROUP BY a.id
		")->result_array();

			$pengeluaran = $this->db->query("
		    SELECT a.keterangan, COALESCE(SUM(p.jumlah),0) as total
		    FROM kode_akun a
		    LEFT JOIN pengeluaran p 
		        ON a.id = p.id_kode_akun
				AND STR_TO_DATE(p.tanggal_input, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')
		    WHERE a.jenis = 'Pengeluaran'
		    GROUP BY a.id
		")->result_array();

			$total_pemasukan = array_sum(array_column($pemasukan, 'total'));
			$total_pengeluaran = array_sum(array_column($pengeluaran, 'total'));
			$total_saldo_bulan_lalu = $saldo_awal + $saldo_bulan_lalu;

			$saldo_bulan_ini = $total_saldo_bulan_lalu + $total_pemasukan - $total_pengeluaran;
			$data = [
				'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
				'saldo_bulan_lalu' => $total_saldo_bulan_lalu,
				'pemasukan' => $pemasukan,
				'pengeluaran' => $pengeluaran,
				'total_pemasukan' => $total_pemasukan,
				'total_pengeluaran' => $total_pengeluaran,
				'saldo_bulan_ini' => $saldo_bulan_ini,
				'status' => 'Tanggal',
				'tanggal_laporan' => $sampai_tanggal
			];

		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			// saldo_awal sebelum tahun dipilih
    $saldo_awal_lama = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun < '$tahun'
    ")->row()->total;

    // saldo_awal tahun yang dipilih
    $saldo_awal_sekarang = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun = '$tahun'
    ")->row()->total;

    // pemasukan sebelum tahun dipilih
    $pemasukan_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pemasukan
        WHERE tahun < '$tahun'
    ")->row()->total;

    // pengeluaran sebelum tahun dipilih
    $pengeluaran_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pengeluaran
        WHERE tahun < '$tahun'
    ")->row()->total;

			$saldo_awal = $saldo_awal_lama + $pemasukan_lama - $pengeluaran_lama + $saldo_awal_sekarang;
			$saldo_bulan_lalu = $this->db->query("
		SELECT 
		    (
		        (SELECT COALESCE(SUM(jumlah),0) FROM pemasukan 
		         WHERE bulan < '$bulan'
		         AND tahun = '$tahun')
		        -
		        (SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran 
		         WHERE bulan < '$bulan'
		         AND tahun = '$tahun')
		    ) as saldo
		")->row_array()['saldo'] ?? 0;

			$pemasukan = $this->db->query("
		    SELECT a.keterangan, SUM(p.jumlah) as total
		    FROM kode_akun a
		    LEFT JOIN pemasukan p 
		        ON a.id = p.id_kode_akun
		        AND p.bulan = '$bulan'
		        AND p.tahun = '$tahun'
		    WHERE a.jenis = 'Pemasukan'
		    GROUP BY a.id
		")->result_array();

			$pengeluaran = $this->db->query("
		    SELECT a.keterangan, SUM(p.jumlah) as total
		    FROM kode_akun a
		    LEFT JOIN pengeluaran p 
		        ON a.id = p.id_kode_akun
		        AND p.bulan = '$bulan'
		        AND p.tahun = '$tahun'
		    WHERE a.jenis = 'Pengeluaran'
		    GROUP BY a.id
		")->result_array();

			$total_pemasukan = array_sum(array_column($pemasukan, 'total'));
			$total_pengeluaran = array_sum(array_column($pengeluaran, 'total'));
			$total_saldo_bulan_lalu = $saldo_awal + $saldo_bulan_lalu;

			$saldo_bulan_ini = $total_saldo_bulan_lalu + $total_pemasukan - $total_pengeluaran;
			$tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
			$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
			$data = [
				'judul' => $this->getBulan($bulan) . " " . $tahun,
				'saldo_bulan_lalu' => $total_saldo_bulan_lalu,
				'pemasukan' => $pemasukan,
				'pengeluaran' => $pengeluaran,
				'total_pemasukan' => $total_pemasukan,
				'total_pengeluaran' => $total_pengeluaran,
				'saldo_bulan_ini' => $saldo_bulan_ini,
				'status' => 'Bulan',
				'tanggal_laporan' => $tanggal_laporan
			];
		} else {
			$tahun = $ambil['single_filter_tahun'];

// saldo_awal sebelum tahun dipilih
    $saldo_awal_lama = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun < '$tahun'
    ")->row()->total;

    // saldo_awal tahun yang dipilih
    $saldo_awal_sekarang = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun = '$tahun'
    ")->row()->total;

    // pemasukan sebelum tahun dipilih
    $pemasukan_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pemasukan
        WHERE tahun < '$tahun'
    ")->row()->total;

    // pengeluaran sebelum tahun dipilih
    $pengeluaran_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pengeluaran
        WHERE tahun < '$tahun'
    ")->row()->total;

			$saldo_awal = $saldo_awal_lama + $pemasukan_lama - $pengeluaran_lama + $saldo_awal_sekarang;

			$pemasukan_tahun = $this->db->query("SELECT id, keterangan
    FROM kode_akun
    WHERE jenis = 'Pemasukan'
    ORDER BY keterangan
")->result_array();

			$pengeluaran_tahun = $this->db->query("SELECT id, keterangan
    FROM kode_akun
    WHERE jenis = 'Pengeluaran'
    ORDER BY keterangan
")->result_array();

			$rekap_pemasukan = $this->db->query("
    SELECT id_kode_akun, bulan, SUM(jumlah) as total
    FROM pemasukan
    WHERE tahun = '$tahun'
    GROUP BY id_kode_akun, bulan
")->result_array();

			$map_pemasukan = [];
			foreach ($rekap_pemasukan as $row) {
				$map_pemasukan[$row['id_kode_akun']][$row['bulan']] = $row['total'];
			}

			$rekap_pengeluaran = $this->db->query("SELECT id_kode_akun, bulan, SUM(jumlah) as total
    FROM pengeluaran
    WHERE tahun = '$tahun'
    GROUP BY id_kode_akun, bulan
")->result_array();

			$map_pengeluaran = [];
			foreach ($rekap_pengeluaran as $row) {
				$map_pengeluaran[$row['id_kode_akun']][$row['bulan']] = $row['total'];
			}


			foreach ($pemasukan_tahun as $key => $row) {
				for ($i = 1; $i <= 12; $i++) {
					$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
					$pemasukan_tahun[$key]['bulan'][$bulan] = $map_pemasukan[$row['id']][$bulan] ?? 0;
				}
			}

			foreach ($pengeluaran_tahun as $key => $row) {
				for ($i = 1; $i <= 12; $i++) {
					$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
					$pengeluaran_tahun[$key]['bulan'][$bulan] = $map_pengeluaran[$row['id']][$bulan] ?? 0;
				}
			}

			$total_pemasukan_query = $this->db->query("SELECT bulan, SUM(jumlah) as total
    FROM pemasukan
    WHERE tahun = '$tahun'
    GROUP BY bulan")->result_array();

			$total_pemasukan_bulan = [];

			for ($i = 1; $i <= 12; $i++) {
				$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
				$total_pemasukan_bulan[$bulan] = 0;
			}

			foreach ($total_pemasukan_query as $row) {
				$total_pemasukan_bulan[$row['bulan']] = $row['total'];
			}

			$total_pengeluaran_query = $this->db->query("
    SELECT
        bulan,
        SUM(jumlah) as total
    FROM pengeluaran
    WHERE tahun = '$tahun'
    GROUP BY bulan
")->result_array();

			$total_pengeluaran_bulan = [];

			for ($i = 1; $i <= 12; $i++) {
				$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
				$total_pengeluaran_bulan[$bulan] = 0;
			}

			foreach ($total_pengeluaran_query as $row) {
				$total_pengeluaran_bulan[$row['bulan']] = $row['total'];
			}

			$saldo_bulanan = [];
			$total_kas_tersedia = [];
			$saldo_akhir_bulan = [];
			$saldo_berjalan = $saldo_awal;
			for ($i = 1; $i <= 12; $i++) {
				$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
				$saldo_bulanan[$bulan] = $saldo_berjalan;
				$total_kas_tersedia[$bulan] = $saldo_bulanan[$bulan] + $total_pemasukan_bulan[$bulan];
				$saldo_akhir_bulan[$bulan] = $total_kas_tersedia[$bulan] - $total_pengeluaran_bulan[$bulan];
				$saldo_berjalan = $saldo_akhir_bulan[$bulan];
			}
			$data = [
				'judul' => $tahun,
				'status' => 'Tahun',
				'saldo_awal' => $saldo_awal,
				'pemasukan_tahun' => $pemasukan_tahun,
				'pengeluaran_tahun' => $pengeluaran_tahun,
				'saldo_bulanan' => $saldo_bulanan,
				'total_pemasukan_bulan' => $total_pemasukan_bulan,
				'total_pengeluaran_bulan' => $total_pengeluaran_bulan,
				'total_kas_tersedia' => $total_kas_tersedia,
				'saldo_akhir_bulan' => $saldo_akhir_bulan,
				'tanggal_laporan' => '31 Desember ' . $tahun
			];
		}
		$this->load->view('admin/data_laporan/laporan_kas', $data);
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
