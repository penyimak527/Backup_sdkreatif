<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_riwayat_pembayaran extends CI_Model
{
    public function periode_list($id_siswa)
    {
        return $this->db
            ->distinct()
            ->select('id_periode, periode')
            ->from('tagihan_pembayaran')
            ->where('id_siswa', (int) $id_siswa)
            ->where('id_periode >', 0)
            ->order_by('id_periode', 'DESC')
            ->get()
            ->result_array();
    }

    public function result($id_siswa, $filter)
    {
        $halaman = max(1, (int) $filter['halaman']);
        $per_halaman = max(1, (int) $filter['per_halaman']);
        $offset = ($halaman - 1) * $per_halaman;
        $client_paging = !empty($filter['client_paging']);

        $this->query_filter($id_siswa, $filter);
        $total = $this->db->count_all_results();

        $this->db->select('id, no_transaksi, tanggal_transaksi, waktu_transaksi, nama_siswa, nis, nisn, nama_kelas, id_periode, periode, '
            .'total_tagihan_dipilih, total_potongan, total_pembayaran, nama_metode_pembayaran, referensi_pembayaran, '
            .'status_transaksi, keterangan');
        $this->query_filter($id_siswa, $filter);
        $this->db
            ->order_by("STR_TO_DATE(CONCAT(tanggal_transaksi,' ',waktu_transaksi),'%d-%m-%Y %H:%i:%s')", 'DESC', false)
            ->order_by('id', 'DESC');
        if (!$client_paging) {
            $this->db->limit($per_halaman, $offset);
        }
        $rows = $this->db->get()->result_array();

        return array(
            'rows' => $rows,
            'total' => $total,
            'halaman' => $halaman,
            'per_halaman' => $per_halaman,
            'total_halaman' => max(1, (int) ceil($total / $per_halaman)),
        );
    }

    public function detail($id_siswa, $id)
    {
        $header = $this->db
            ->select('id, no_transaksi, tanggal_transaksi, waktu_transaksi, nama_siswa, nis, nisn, nama_kelas, periode, '
                .'total_tagihan_dipilih, total_potongan, total_pembayaran, nama_metode_pembayaran, referensi_pembayaran, '
                .'status_transaksi, keterangan, nama_user')
            ->from('tagihan_pembayaran')
            ->where('id', (int) $id)
            ->where('id_siswa', (int) $id_siswa)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$header) return null;

        $detail = $this->db
            ->select('id, no_tagihan, nama_jenis_tagihan, nama_tagihan, tipe_tagihan, nama_bulan, tahun, '
                .'nominal_tagihan, nominal_bayar, sisa_setelah, status_setelah, status_detail')
            ->from('tagihan_pembayaran_detail')
            ->where('id_pembayaran', (int) $id)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        return array('header' => $header, 'detail' => $detail);
    }

    public function bukti($id_wali_siswa, $id)
    {
        $header = $this->db
            ->select('p.*')
            ->from('tagihan_pembayaran p')
            ->join('wali_siswa_detail d', 'd.id_siswa=p.id_siswa', 'inner')
            ->where('p.id', (int) $id)
            ->where('d.id_wali_siswa', (int) $id_wali_siswa)
            ->where("UPPER(TRIM(d.status_aktif))='AKTIF'", null, false)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$header) return null;

        $detail = $this->db
            ->where('id_pembayaran', (int) $id)
            ->order_by('id', 'ASC')
            ->get('tagihan_pembayaran_detail')
            ->result_array();

        return array('result' => 'true', 'header' => $header, 'detail' => $detail);
    }

    private function query_filter($id_siswa, $filter)
    {
        $this->db
            ->from('tagihan_pembayaran')
            ->where('id_siswa', (int) $id_siswa);

        if (!empty($filter['search'])) {
            $this->db->like('no_transaksi', $filter['search']);
        }

        if (!empty($filter['id_periode'])) {
            $this->db->where('id_periode', (int) $filter['id_periode']);
        }

        if (in_array($filter['status'], array('Aktif', 'Dibatalkan'), true)) {
            $this->db->where('status_transaksi', $filter['status']);
        }

        if ($this->tanggal_valid($filter['tanggal_awal'])) {
            $this->db->where(
                "STR_TO_DATE(tanggal_transaksi,'%d-%m-%Y') >= STR_TO_DATE(".$this->db->escape($filter['tanggal_awal']).",'%Y-%m-%d')",
                null,
                false
            );
        }

        if ($this->tanggal_valid($filter['tanggal_akhir'])) {
            $this->db->where(
                "STR_TO_DATE(tanggal_transaksi,'%d-%m-%Y') <= STR_TO_DATE(".$this->db->escape($filter['tanggal_akhir']).",'%Y-%m-%d')",
                null,
                false
            );
        }
    }

    private function tanggal_valid($tanggal)
    {
        if (!$tanggal) return false;
        $date = DateTime::createFromFormat('!Y-m-d', $tanggal);
        return $date && $date->format('Y-m-d') === $tanggal;
    }
}
