<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan_tunggakan extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
	}

	public function print_laporan()
	{
		$json = file_get_contents('php://input');
		$ambil = json_decode($json, true);
		$ambil = is_array($ambil) ? $ambil : array();
		$id_periode = (int) ($ambil['spp_id_periode_tunggakan'] ?? 0);
		$id_kelas = (int) ($ambil['spp_id_kelas_tunggakan'] ?? 0);
		$id_jenis = (int) ($ambil['spp_id_jenis_tunggakan'] ?? 0);

		$tanggalField = "STR_TO_DATE(COALESCE(NULLIF(ts.tanggal_jatuh_tempo,''),ts.tanggal_generate),'%d-%m-%Y')";
		$sql = "SELECT ts.id_siswa, MAX(ts.nis) AS nis, MAX(ts.nama_siswa) AS nama_siswa,
				COALESCE(MAX(ks.nama_kelas),MAX(ts.nama_kelas),'-') AS nama_kelas,
				ts.periode AS tahun_asal, COUNT(ts.id) AS jumlah_tagihan,
				COALESCE(SUM(CAST(ts.sisa_tagihan AS DECIMAL(15,2))),0) AS total_tunggakan
			FROM tagihan_siswa ts
			JOIN siswa s ON s.id=ts.id_siswa
			LEFT JOIN kelas_setting ks ON ks.id=ts.id_kelas_setting
			WHERE ts.status_tagihan='Aktif'
			AND ts.dianggap_tunggakan='Ya'
			AND CAST(ts.sisa_tagihan AS DECIMAL(15,2))> 0
			AND ts.status_pembayaran NOT IN ('Lunas','Dibebaskan','Dibatalkan')
			AND $tanggalField <= CURDATE()";
		$params = array();
		if ($id_periode > 0) {
			$sql .= ' AND ts.id_periode=?';
			$params[] = $id_periode;
		}
		if ($id_kelas > 0) {
			$sql .= ' AND ts.id_kelas_setting=?';
			$params[] = $id_kelas;
		}
		if ($id_jenis > 0) {
			$sql .= ' AND ts.id_jenis_tagihan=?';
			$params[] = $id_jenis;
		}
		// Pertahankan cakupan siswa Aktif seperti default laporan sebelumnya.
		$sql .= " AND UPPER(TRIM(s.status_aktif))='AKTIF'";
		$sql .= ' GROUP BY ts.id_siswa, ts.id_periode, ts.periode ORDER BY total_tunggakan DESC, nama_siswa ASC';
		$rows = $this->db->query($sql, $params)->result_array();
		$total = 0;
		foreach ($rows as $row) $total += (float) $row['total_tunggakan'];

		$nama_tahun = 'Semua Tahun Ajaran';
		if ($id_periode > 0) {
			$periode = $this->db->select('periode')->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
			$nama_tahun = $periode['periode'] ?? '-';
		}
		$nama_kelas = 'Semua Kelas';
		if ($id_kelas > 0) {
			$kelas = $this->db->select('nama_kelas')->where('id', $id_kelas)->get('kelas_setting')->row_array();
			$nama_kelas = $kelas['nama_kelas'] ?? '-';
		}
		$nama_jenis = 'Semua Jenis';
		if ($id_jenis > 0) {
			$jenis = $this->db->select('nama_jenis')->where('id', $id_jenis)->get('tagihan_jenis')->row_array();
			$nama_jenis = $jenis['nama_jenis'] ?? '-';
		}
		$admin = $this->session->userdata('admin');
		$this->load->view('admin/data_laporan/laporan_tunggakan', array(
			'title' => 'Laporan Tunggakan',
			'laporan' => array(
				'columns' => array('nama_siswa' => 'Siswa', 'nis' => 'NIS', 'nama_kelas' => 'Kelas', 'tahun_asal' => 'Tahun Asal', 'jumlah_tagihan' => 'Jumlah Tagihan', 'total_tunggakan' => 'Total Tunggakan'),
				'money' => array('total_tunggakan'),
				'rows' => $rows,
				'summary' => array('Jumlah Siswa' => count($rows), 'Total Tunggakan' => $total)
			),
			'filter_laporan' => array('Tahun Ajaran' => $nama_tahun, 'Kelas' => $nama_kelas, 'Jenis Tagihan' => $nama_jenis),
			'petugas' => $admin['nama'] ?? $admin['username'] ?? '-',
			'orientation' => 'landscape'
		));
	}
}
