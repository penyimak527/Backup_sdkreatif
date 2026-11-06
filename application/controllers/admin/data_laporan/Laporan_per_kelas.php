<?php
class Laporan_per_kelas extends CI_Controller
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
		$ambil = is_array($ambil) ? $ambil : array();
		$filter = $ambil['filter'] ?? 'tanggal';
		$id_periode = (int) ($ambil['spp_id_periode_kelas'] ?? 0);
		$id_jenis = (int) ($ambil['spp_id_jenis_tagihan_kelas'] ?? 0);

		$sql = "SELECT ts.id_kelas_setting, ks.nama_kelas,
			COUNT(DISTINCT ts.id_siswa) AS jumlah_siswa,
			SUM(CAST(ts.nominal_tagihan AS DECIMAL(15,2))) AS total_tagihan,
			SUM(CAST(ts.nominal_dibayar AS DECIMAL(15,2))) AS total_dibayar,
			SUM(CAST(ts.sisa_tagihan AS DECIMAL(15,2))) AS total_sisa,
			CASE WHEN SUM(CAST(ts.nominal_tagihan AS DECIMAL(15,2))) > 0
			THEN ROUND(SUM(CAST(ts.nominal_dibayar AS DECIMAL(15,2))) /
				SUM(CAST(ts.nominal_tagihan AS DECIMAL(15,2))) * 100, 2)
			ELSE 0 END AS realisasi
			FROM tagihan_siswa ts
			INNER JOIN kelas_setting ks ON ks.id = ts.id_kelas_setting AND ks.id_periode = ts.id_periode
			WHERE ts.status_tagihan = 'Aktif'";
		$params = array();

		if ($filter == 'tanggal') {
			$dari = $ambil['dari_tanggal'] ?? '';
			$sampai = $ambil['sampai_tanggal'] ?? '';
			$sql .= " AND STR_TO_DATE(ts.tanggal_generate, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
			$params[] = $dari;
			$params[] = $sampai;
			$judul = $dari && $sampai ? date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai)) : '-';
		} else if ($filter == 'bulan') {
			$bulan = (int) ($ambil['filter_bulan'] ?? 0);
			$tahun = (int) ($ambil['filter_tahun'] ?? 0);
			$sql .= ' AND ts.bulan = ? AND ts.tahun = ?';
			$params[] = $bulan;
			$params[] = $tahun;
			$judul = $this->getBulan($bulan) . ' ' . $tahun;
		} else {
			$tahun = (int) ($ambil['single_filter_tahun'] ?? 0);
			$sql .= ' AND ts.tahun = ?';
			$params[] = $tahun;
			$judul = $tahun;
		}
		if ($id_periode > 0) {
			$sql .= ' AND ts.id_periode = ?';
			$params[] = $id_periode;
		}
		if ($id_jenis > 0) {
			$sql .= ' AND ts.id_jenis_tagihan = ?';
			$params[] = $id_jenis;
		}
		$sql .= ' GROUP BY ts.id_kelas_setting, ks.nama_kelas ORDER BY ks.nama_kelas ASC';

		$rows = $this->db->query($sql, $params)->result_array();
		$total_tagihan = 0;
		$total_dibayar = 0;
		$total_sisa = 0;
		foreach ($rows as $row) {
			$total_tagihan += (float) $row['total_tagihan'];
			$total_dibayar += (float) $row['total_dibayar'];
			$total_sisa += (float) $row['total_sisa'];
		}

		$tahun_ajaran = 'Semua Tahun Ajaran';
		if ($id_periode > 0) {
			$periode = $this->db->get_where('master_tahun_ajaran', array('id' => $id_periode))->row_array();
			$tahun_ajaran = $periode['periode'] ?? '-';
		}
		$jenis_tagihan = 'Semua Jenis';
		if ($id_jenis > 0) {
			$jenis = $this->db->get_where('tagihan_jenis', array('id' => $id_jenis))->row_array();
			$jenis_tagihan = $jenis['nama_jenis'] ?? '-';
		}

		$this->load->view('admin/data_laporan/laporan_per_kelas', array(
			'judul' => $judul,
			'tahun_ajaran' => $tahun_ajaran,
			'jenis_tagihan' => $jenis_tagihan,
			'petugas' => $this->session->userdata('admin')['nama'] ?? ($this->session->userdata('admin')['nama_user'] ?? '-'),
			'data_laporan' => $rows,
			'total_tagihan' => $total_tagihan,
			'total_dibayar' => $total_dibayar,
			'total_sisa' => $total_sisa
		));
	}

	public function getBulan($bulan)
	{
		$daftar = array(1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember');
		return $daftar[(int) $bulan] ?? 'Bulan tidak valid';
	}
}
