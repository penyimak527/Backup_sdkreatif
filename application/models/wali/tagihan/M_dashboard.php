<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_dashboard extends CI_Model
{
    public function dashboard($id_siswa)
    {
        $id_siswa = (int) $id_siswa;

        $tagihan = $this->db
            ->select("COUNT(*) AS jumlah_tagihan,
                COALESCE(SUM(CAST(nominal_tagihan AS DECIMAL(15,2))),0) AS total_tagihan,
                COALESCE(SUM(CAST(nominal_dibayar AS DECIMAL(15,2))),0) AS total_pembayaran,
                COALESCE(SUM(CAST(sisa_tagihan AS DECIMAL(15,2))),0) AS total_sisa,
                SUM(CASE WHEN status_pembayaran='Lunas' OR status_pembayaran='Dibebaskan' THEN 1 ELSE 0 END) AS jumlah_lunas,
                SUM(CASE WHEN status_pembayaran NOT IN ('Lunas','Dibebaskan') THEN 1 ELSE 0 END) AS jumlah_belum_lunas", false)
            ->from('tagihan_siswa')
            ->where('id_siswa', $id_siswa)
            ->where('status_tagihan', 'Aktif')
            ->get()
            ->row_array();

        $setoran = $this->db
            ->select('COALESCE(SUM(nominal),0) AS total', false)
            ->from('tabungan_siswa_setoran')
            ->where('id_siswa', $id_siswa)
            ->where('status_transaksi', 'Aktif')
            ->get()
            ->row_array();

        $penarikan = $this->db
            ->select('COALESCE(SUM(nominal),0) AS total', false)
            ->from('tabungan_siswa_penarikan')
            ->where('id_siswa', $id_siswa)
            ->where('status_transaksi', 'Aktif')
            ->get()
            ->row_array();

        $pembayaran_terakhir = $this->db
            ->select('id, no_transaksi, tanggal_transaksi, waktu_transaksi, total_pembayaran, nama_metode_pembayaran, status_transaksi')
            ->from('tagihan_pembayaran')
            ->where('id_siswa', $id_siswa)
            ->order_by("STR_TO_DATE(CONCAT(tanggal_transaksi,' ',waktu_transaksi),'%d-%m-%Y %H:%i:%s')", 'DESC', false)
            ->order_by('id', 'DESC')
            ->limit(5)
            ->get()
            ->result_array();

        $tagihan_terdekat = $this->db
            ->select('id, no_tagihan, nama_tagihan, tipe_tagihan, periode, nama_bulan, tahun, tanggal_jatuh_tempo, nominal_tagihan, nominal_dibayar, sisa_tagihan, status_pembayaran, dianggap_tunggakan')
            ->from('tagihan_siswa')
            ->where('id_siswa', $id_siswa)
            ->where('status_tagihan', 'Aktif')
            ->where_not_in('status_pembayaran', array('Lunas', 'Dibebaskan'))
            ->order_by("STR_TO_DATE(tanggal_jatuh_tempo,'%d-%m-%Y')", 'ASC', false)
            ->order_by('id', 'ASC')
            ->limit(5)
            ->get()
            ->result_array();

        $riwayat_tabungan = $this->db->query(
            "SELECT id,'Setoran' AS jenis,no_transaksi,tanggal_transaksi,nominal,status_transaksi,created_at
             FROM tabungan_siswa_setoran
             WHERE id_siswa=?
             UNION ALL
             SELECT id,'Penarikan' AS jenis,no_transaksi,tanggal_transaksi,nominal,status_transaksi,created_at
             FROM tabungan_siswa_penarikan
             WHERE id_siswa=?
             ORDER BY created_at DESC, id DESC
             LIMIT 5",
            array($id_siswa, $id_siswa)
        )->result_array();

        $total_setoran = (float) ($setoran['total'] ?? 0);
        $total_penarikan = (float) ($penarikan['total'] ?? 0);

        return array(
            'tagihan' => $tagihan ?: array(),
            'tabungan' => array(
                'total_setoran' => $total_setoran,
                'total_penarikan' => $total_penarikan,
                'saldo' => $total_setoran - $total_penarikan,
            ),
            'pembayaran_terakhir' => $pembayaran_terakhir,
            'tagihan_terdekat' => $tagihan_terdekat,
            'riwayat_tabungan' => $riwayat_tabungan,
        );
    }
}
