<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan_tabungan extends CI_Controller
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
		$filter = (string) ($ambil['filter'] ?? 'tanggal');
		$id_periode = (int) ($ambil['spp_id_periode_tabungan'] ?? 0);
		$id_kelas = (int) ($ambil['spp_id_kelas_tabungan'] ?? 0);
		$jenis = trim((string) ($ambil['spp_jenis_tabungan'] ?? ''));
		$status = trim((string) ($ambil['spp_status_tabungan'] ?? ''));

		$where = array('1=1');
		$paramsFilter = array();
		$nama_bulan = array(1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember');

		if ($filter === 'tanggal') {
			$dari = (string) ($ambil['dari_tanggal'] ?? '');
			$sampai = (string) ($ambil['sampai_tanggal'] ?? '');
			if ($dari === '' || $sampai === '') {
				show_error('Pilih rentang tanggal laporan.', 400);
				return;
			}
			$where[] = "STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
			$paramsFilter[] = $dari;
			$paramsFilter[] = $sampai;
			$nama_periode = date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai));
		} elseif ($filter === 'bulan') {
			$bulan = (int) ($ambil['filter_bulan'] ?? 0);
			$tahun = (int) ($ambil['filter_tahun'] ?? 0);
			$where[] = "MONTH(STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y'))=? AND YEAR(STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y'))=?";
			$paramsFilter[] = $bulan;
			$paramsFilter[] = $tahun;
			$nama_periode = ($nama_bulan[$bulan] ?? '-') . ' ' . $tahun;
		} else {
			$tahun = (int) ($ambil['single_filter_tahun'] ?? 0);
			$where[] = "YEAR(STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y'))=?";
			$paramsFilter[] = $tahun;
			$nama_periode = (string) $tahun;
		}

		if ($id_periode > 0) {
			$where[] = 'id_periode=?';
			$paramsFilter[] = $id_periode;
		}
		if ($id_kelas > 0) {
			$where[] = 'id_kelas_setting=?';
			$paramsFilter[] = $id_kelas;
		}
		if ($status !== '') {
			$where[] = 'status_transaksi=?';
			$paramsFilter[] = $status;
		}

		$columns = 'id, no_transaksi, tanggal_transaksi, id_siswa, nis, nama_siswa, nama_kelas, metode_pembayaran, status_transaksi, nama_user, nominal';
		$parts = array();
		$params = array();
		if ($jenis === '' || $jenis === 'Setoran') {
			$parts[] = "SELECT $columns, 'Setoran' AS jenis FROM tabungan_siswa_setoran WHERE " . implode(' AND ', $where);
			$params = array_merge($params, $paramsFilter);
		}
		if ($jenis === '' || $jenis === 'Penarikan') {
			$parts[] = "SELECT $columns, 'Penarikan' AS jenis FROM tabungan_siswa_penarikan WHERE " . implode(' AND ', $where);
			$params = array_merge($params, $paramsFilter);
		}

		$rows = array();
		if (!empty($parts)) {
			$sql = 'SELECT * FROM (' . implode(' UNION ALL ', $parts) . ') AS transaksi '
				. "ORDER BY STR_TO_DATE(transaksi.tanggal_transaksi,'%d-%m-%Y') ASC, transaksi.id ASC";
			$rows = $this->db->query($sql, $params)->result_array();
		}

		$total_setoran = 0;
		$total_penarikan = 0;
		foreach ($rows as $row) {
			if ($row['status_transaksi'] !== 'Aktif') continue;
			if ($row['jenis'] === 'Setoran') {
				$total_setoran += (float) $row['nominal'];
			} else {
				$total_penarikan += (float) $row['nominal'];
			}
		}

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

		$this->load->view('admin/data_laporan/laporan_tabungan', array(
			'data_laporan' => $rows,
			'total_setoran' => $total_setoran,
			'total_penarikan' => $total_penarikan,
			'saldo' => $total_setoran - $total_penarikan,
			'filters' => array(
				'Periode' => $nama_periode,
				'Tahun Ajaran' => $nama_tahun,
				'Kelas' => $nama_kelas,
				'Jenis Transaksi' => $jenis ?: 'Semua Jenis',
				'Status' => $status ?: 'Semua Status'
			),
			'petugas' => trim((string) ($this->session->userdata('admin')['nama'] ?? $this->session->userdata('admin')['username'] ?? ''))
		));
	}
}
