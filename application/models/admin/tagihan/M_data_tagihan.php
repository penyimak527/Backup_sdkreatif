<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_data_tagihan extends CI_Model
{
    public function periode_list()
    {
        return $this->db->order_by('id', 'DESC')->get('master_tahun_ajaran')->result_array();
    }

    public function jenis_list()
    {
        return $this->db->order_by('nama_jenis', 'ASC')->get('tagihan_jenis')->result_array();
    }

    public function kelas_list()
    {
        $idPeriode = (int) $this->input->post('id_periode');
        $this->db->select('id,nama_kelas,id_kelas,id_periode,semester')->from('kelas_setting');
        if ($idPeriode > 0) {
            $this->db->where('CAST(id_periode AS UNSIGNED) = ' . $idPeriode, null, false);
        }
        return $this->db->order_by('nama_kelas', 'ASC')->get()->result_array();
    }

    public function result()
    {
        $search = trim((string) $this->input->post('search', true));
        $idPeriode = (int) $this->input->post('id_periode');
        $idKelas = (int) $this->input->post('id_kelas_setting');
        $idJenis = (int) $this->input->post('id_jenis_tagihan');
        $bulan = (int) $this->input->post('bulan');
        $status = trim((string) $this->input->post('status_pembayaran', true));
        $statusSiswa = strtoupper(trim((string) $this->input->post('status_siswa', true)));

        $this->db->select('ts.*, s.status_siswa AS status_siswa_sekarang, s.status_aktif AS status_aktif_sekarang')
            ->from('tagihan_siswa ts')
            ->join('siswa s', 's.id=ts.id_siswa', 'left');

        if ($search !== '') {
            $this->db->group_start()
                ->like('ts.nama_siswa', $search)
                ->or_like('ts.nis', $search)
                ->or_like('ts.nisn', $search)
                ->or_like('ts.no_tagihan', $search)
                ->or_like('ts.nama_tagihan', $search)
                ->group_end();
        }
        if ($idPeriode > 0) $this->db->where('ts.id_periode', $idPeriode);
        if ($idKelas > 0) $this->db->where('ts.id_kelas_setting', $idKelas);
        if ($idJenis > 0) $this->db->where('ts.id_jenis_tagihan', $idJenis);
        if ($bulan > 0) $this->db->where('ts.bulan', $bulan);
        if ($status !== '') $this->db->where('ts.status_pembayaran', $status);
        if ($statusSiswa !== '') $this->db->where('UPPER(s.status_siswa)', $statusSiswa);

        return $this->db
            ->order_by('ts.id_periode', 'DESC')
            ->order_by('ts.tahun', 'DESC')
            ->order_by('ts.bulan', 'DESC')
            ->order_by('ts.nama_siswa', 'ASC')
            ->limit(2000)
            ->get()
            ->result_array();
    }

    public function detail()
    {
        $id = (int) $this->input->post('id');
        $row = $this->db->where('id', $id)->get('tagihan_siswa')->row_array();
        if (!$row) {
            return array('result' => 'false', 'message' => 'Tagihan siswa tidak ditemukan.');
        }

        $payments = $this->db->query(
            "SELECT pd.*, p.tanggal_transaksi, p.waktu_transaksi, p.nama_metode_pembayaran, p.status_transaksi, p.nama_user
             FROM tagihan_pembayaran_detail pd
             INNER JOIN tagihan_pembayaran p ON p.id=pd.id_pembayaran
             WHERE pd.id_tagihan_siswa=?
             ORDER BY p.id ASC, pd.id ASC",
            array($id)
        )->result_array();

        return array('result' => 'true', 'tagihan' => $row, 'pembayaran' => $payments);
    }
}
