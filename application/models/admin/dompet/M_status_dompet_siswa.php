<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_status_dompet_siswa extends CI_Model
{
    public function result($filter)
    {
        $page = $filter['page']; $limit = $filter['limit'];
        $this->query($filter, true); $total = (int) $this->db->count_all_results();
        $this->query($filter, false); $rows = $this->db->limit($limit, ($page - 1) * $limit)->get()->result_array();
        return ['rows' => $rows, 'meta' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => max(1, (int) ceil($total / $limit))]];
    }

    private function query($filter, $count)
    {
        if ($count) {
            $this->db->select('s.id');
        } else {
            $this->db->select("s.id, s.nis, s.nama_lengkap, s.status_siswa, kst.nama_kelas, kst.periode,
                sd.id AS id_wallet, COALESCE(sd.saldo,0) saldo, COALESCE(sd.status_dompet,'BELUM_DIAKTIFKAN') status_dompet,
                qr.id AS id_qr, qr.kode_tampil, qr.status_qr,
                CASE WHEN pds.id IS NOT NULL AND pds.batas_harian > 0 THEN pds.batas_harian ELSE COALESCE(pd.batas_harian_default,0) END batas_harian,
                CASE WHEN pds.id IS NOT NULL AND pds.batas_per_transaksi > 0 THEN pds.batas_per_transaksi ELSE COALESCE(pd.batas_transaksi_default,0) END batas_transaksi,
                COALESCE(pds.batas_harian,0) batas_harian_individual,
                COALESCE(pds.batas_per_transaksi,0) batas_transaksi_individual,
                CASE WHEN pds.id IS NOT NULL AND (pds.batas_harian > 0 OR pds.batas_per_transaksi > 0) THEN 'INDIVIDUAL' ELSE 'GLOBAL' END sumber_limit,
                COALESCE(w.wali_aktif,0) wali_aktif, w.nama_wali", false);
        }
        $this->db->from('siswa s');
        $this->db->join('kelas_siswa ks', "ks.id=(SELECT MAX(ks2.id) FROM kelas_siswa ks2 JOIN kelas_setting k2 ON k2.id=ks2.id_kelas_setting JOIN master_tahun_ajaran ta2 ON ta2.id=k2.id_periode AND ta2.status='Aktif' WHERE ks2.id_siswa=s.id AND ks2.status_aktif IN ('1','AKTIF'))", 'left', false);
        $this->db->join('kelas_setting kst', 'kst.id=ks.id_kelas_setting', 'left');
        $this->db->join('saldo_dompet_siswa sd', 'sd.id_siswa=s.id', 'left');
        $this->db->join('qr_siswa qr', "qr.id=(SELECT MAX(q2.id) FROM qr_siswa q2 WHERE q2.id_siswa=s.id AND q2.status_qr='AKTIF')", 'left', false);
        $this->db->join('pengaturan_dompet_siswa pds', 'pds.id_siswa=s.id', 'left');
        $this->db->join('(SELECT * FROM pengaturan_dompet ORDER BY id DESC LIMIT 1) pd', '1=1', 'left', false);
        $this->db->join("(SELECT wd.id_siswa, COUNT(*) wali_aktif, GROUP_CONCAT(DISTINCT ws.nama_wali ORDER BY ws.nama_wali SEPARATOR ', ') nama_wali FROM wali_siswa_detail wd JOIN wali_siswa ws ON ws.id=wd.id_wali_siswa AND ws.status_aktif='AKTIF' WHERE wd.status_aktif='AKTIF' GROUP BY wd.id_siswa) w", 'w.id_siswa=s.id', 'left', false);
        if ($filter['search'] !== '') {
            $this->db->group_start()->like('s.nis', $filter['search'])->or_like('s.nama_lengkap', $filter['search'])->group_end();
        }
        if ($filter['status_wallet'] === 'BELUM_DIAKTIFKAN') $this->db->where('sd.id IS NULL', null, false);
        if ($filter['status_wallet'] === 'AKTIF') $this->db->where('sd.status_dompet', 'AKTIF');
        if ($filter['status_wallet'] === 'NONAKTIF') $this->db->where('sd.status_dompet', 'NONAKTIF');
        if ($filter['id_kelas_setting'] > 0) $this->db->where('kst.id', $filter['id_kelas_setting']);
        if (!$count) $this->db->order_by('s.nama_lengkap', 'ASC');
    }
}
