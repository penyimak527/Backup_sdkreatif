<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan_pembatalan extends CI_Controller
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
        $id_periode = max(0, (int) ($ambil['spp_id_periode_pembatalan'] ?? 0));
        $id_siswa = max(0, (int) ($ambil['spp_id_siswa_pembatalan'] ?? 0));
        $tahun_ajaran = $this->db->select('periode')->where('id', $id_periode)
            ->get('master_tahun_ajaran')->row_array();
        if (!$tahun_ajaran) {
            show_error('Silakan pilih tahun ajaran yang valid.', 400);
            return;
        }

        $nama_siswa = 'Semua Siswa';
        if ($id_siswa > 0) {
            $siswa = $this->db->select('nama_siswa')->where('id_siswa', $id_siswa)
                ->get('tagihan_pembatalan_transaksi')->row_array();
            $nama_siswa = $siswa['nama_siswa'] ?? '-';
        }

        $sql = "SELECT c.no_transaksi, c.nama_siswa, c.nis, c.total_pembayaran,
                       c.nama_user_transaksi, c.nama_user_pembatalan,
                       c.tanggal_pembatalan, c.waktu_pembatalan, c.alasan_pembatalan
                FROM tagihan_pembatalan_transaksi c
                JOIN tagihan_pembayaran p ON p.id = c.id_pembayaran
                WHERE p.id_periode = ?";
        $params = array($id_periode);
        if ($id_siswa > 0) {
            $sql .= ' AND c.id_siswa = ?';
            $params[] = $id_siswa;
        }
        $sql .= " ORDER BY STR_TO_DATE(c.tanggal_pembatalan, '%d-%m-%Y') DESC,
                  c.waktu_pembatalan DESC, c.id DESC";
        $rows = $this->db->query($sql, $params)->result_array();
        $total = 0;
        foreach ($rows as $row) {
            $total += (float) $row['total_pembayaran'];
        }

        $admin = $this->session->userdata('admin');
        $petugas = $admin['nama'] ?? $admin['username'] ?? '-';
        $this->load->view('admin/data_laporan/laporan_pembatalan', array(
            'data_laporan' => $rows,
            'jumlah_pembatalan' => count($rows),
            'total_dibatalkan' => $total,
            'tahun_ajaran_laporan' => $tahun_ajaran['periode'],
            'siswa_laporan' => $nama_siswa,
            'petugas_cetak' => $petugas
        ));
    }

    public function getBulan($bulan)
    {
        $daftar = array(1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember');
        return $daftar[(int) $bulan] ?? 'Bulan tidak valid';
    }
}
