<?php
class Laporan_per_jenis_tagihan extends CI_Controller
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
		$id_periode = max(0, (int) ($ambil['spp_id_periode_jenis'] ?? 0));
		$id_kelas = max(0, (int) ($ambil['spp_id_kelas_jenis'] ?? 0));
		$periode = $this->db->select('periode')->where('id', $id_periode)
			->get('master_tahun_ajaran')->row_array();
		if (!$periode) {
			show_error('Silakan pilih Tahun Ajaran yang valid.', 400);
			return;
		}
		// Pembayaran sah tetap dihitung, termasuk sebelum pembebasan.
		// Target efektif = pembayaran sah + sisa kewajiban aktif.
		$sql = "SELECT ts.id_jenis_tagihan, ts.id_tagihan_master,
    MAX(ts.nama_jenis_tagihan) AS nama_jenis_tagihan,
    MAX(ts.nama_tagihan) AS nama_tagihan,
    COUNT(DISTINCT CASE WHEN
        COALESCE(bayar.total_bayar, 0) > 0 OR
        (ts.status_tagihan = 'Aktif' AND
         ts.status_pembayaran NOT IN ('Dibebaskan', 'Dibatalkan') AND
         CAST(ts.sisa_tagihan AS DECIMAL(18,2)) > 0)
        THEN ts.id_siswa ELSE NULL END) AS jumlah_siswa,
    COALESCE(SUM(COALESCE(bayar.total_bayar, 0)),0) AS total_pembayaran,
    COALESCE(SUM(CASE
        WHEN ts.status_tagihan = 'Aktif' AND
             ts.status_pembayaran NOT IN ('Dibebaskan','Dibatalkan')
        THEN GREATEST(CAST(ts.sisa_tagihan AS DECIMAL(18,2)), 0)
        ELSE 0 END),0) AS total_sisa
FROM tagihan_siswa ts
LEFT JOIN (
    SELECT d.id_tagihan_siswa,
           SUM(CAST(d.nominal_bayar AS DECIMAL(18,2))) AS total_bayar
    FROM tagihan_pembayaran_detail d
    JOIN tagihan_pembayaran p ON p.id = d.id_pembayaran
    WHERE p.status_transaksi = 'Aktif' AND d.status_detail = 'Aktif'
    GROUP BY d.id_tagihan_siswa
) bayar ON bayar.id_tagihan_siswa = ts.id
WHERE ts.id_periode = ?
  AND ts.status_pembayaran <> 'Dibatalkan'
  AND (ts.status_tagihan = 'Aktif' OR COALESCE(bayar.total_bayar, 0) > 0)";
		$params = array($id_periode);
		$nama_kelas = 'Semua Kelas';
		if ($id_kelas > 0) {
			$kelas = $this->db->select('nama_kelas')->where('id', $id_kelas)
				->where('id_periode', $id_periode)->get('kelas_setting')->row_array();
			if (!$kelas) {
				show_error('Kelas tidak sesuai dengan Tahun Ajaran.', 400);
				return;
			}
			$nama_kelas = $kelas['nama_kelas'];
			$sql .= ' AND ts.id_kelas_setting = ?';
			$params[] = $id_kelas;
		}
		$sql .= " GROUP BY ts.id_jenis_tagihan, ts.id_tagihan_master
				  HAVING total_pembayaran > 0 OR total_sisa > 0
				  ORDER BY nama_jenis_tagihan ASC, nama_tagihan ASC";
		$rows = $this->db->query($sql, $params)->result_array();
		$target = 0;
		$bayar = 0;
		$sisa = 0;
		foreach ($rows as &$row) {
			$row['total_pembayaran'] = (float) $row['total_pembayaran'];
			$row['total_sisa'] = (float) $row['total_sisa'];
			$row['total_target'] = $row['total_pembayaran'] + $row['total_sisa'];
			$row['realisasi'] = $row['total_target'] > 0
				? round($row['total_pembayaran'] / $row['total_target'] * 100, 2) : 0;
			$target += $row['total_target'];
			$bayar += $row['total_pembayaran'];
			$sisa += $row['total_sisa'];
		}
		unset($row);
		$admin = $this->session->userdata('admin');
		$this->load->view('admin/data_laporan/laporan_per_jenis_tagihan', array(
			'tahun_ajaran' => $periode['periode'], 'nama_kelas' => $nama_kelas,
			'data_laporan' => $rows, 'total_target' => $target,
			'total_pembayaran' => $bayar, 'total_sisa' => $sisa,
			'total_realisasi' => $target > 0 ? round($bayar / $target * 100, 2) : 0,
			'petugas' => $admin['nama'] ?? $admin['username'] ?? '-'
		));
	}
	public function getBulan($bulan)
	{
		$d = array(1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember');
		return $d[(int)$bulan] ?? 'Bulan tidak valid';
	}
}
