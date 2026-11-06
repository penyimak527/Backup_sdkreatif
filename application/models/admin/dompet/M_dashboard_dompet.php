<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_dashboard_dompet extends CI_Model
{
    public function ringkasan()
    {
        $today = date('Y-m-d');
        $wallet = $this->db->select("COUNT(*) total_wallet, SUM(CASE WHEN status_dompet='AKTIF' THEN 1 ELSE 0 END) wallet_aktif, COALESCE(SUM(saldo),0) total_saldo", false)->get('saldo_dompet_siswa')->row_array();
        $students = $this->db->select("COUNT(*) total_siswa, SUM(CASE WHEN sd.id IS NULL THEN 1 ELSE 0 END) belum_wallet", false)->from('siswa s')->join('saldo_dompet_siswa sd', 'sd.id_siswa=s.id', 'left')->where('s.status_siswa', 'AKTIF')->get()->row_array();
        $topup = $this->db->select("COUNT(*) jumlah, COALESCE(SUM(nominal_saldo),0) nominal, COALESCE(SUM(biaya_layanan),0) biaya", false)->where('tanggal', $today)->where('status_transaksi', 'BERHASIL')->get('riwayat_isi_saldo')->row_array();
        $canteen = $this->db->select("COUNT(*) jumlah, COALESCE(SUM(nominal),0) nominal", false)->where('tanggal', $today)->where('status_transaksi', 'BERHASIL')->get('transaksi_kantin')->row_array();
        $pending = $this->db->where('status_pengajuan', 'MENUNGGU_VERIFIKASI')->count_all_results('pengajuan_isi_saldo');

        return [
            'wallet' => $wallet,
            'siswa' => $students,
            'topup_hari_ini' => $topup,
            'kantin_hari_ini' => $canteen,
            'pengajuan_menunggu' => (int) $pending,
            'transaksi_terbaru' => $this->db->select('no_transaksi, nama_siswa, nominal, status_transaksi, tanggal, waktu')->order_by('id', 'DESC')->limit(8)->get('transaksi_kantin')->result_array(),
            'pengajuan_terbaru' => $this->db->select('no_pengajuan, nama_siswa, nominal_saldo, biaya_layanan, status_pengajuan, tanggal_pengajuan, waktu_pengajuan')->order_by('id', 'DESC')->limit(8)->get('pengajuan_isi_saldo')->result_array(),
        ];
    }
}
