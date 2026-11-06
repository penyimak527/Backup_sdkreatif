<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_cicilan extends CI_Model
{
    public function periode_list()
    {
        return $this->db->order_by('id', 'DESC')->get('master_tahun_ajaran')->result_array();
    }

    public function periode_aktif()
    {
        $periode = $this->db
            ->where('status', 'Aktif')
            ->order_by('id', 'DESC')
            ->get('master_tahun_ajaran')
            ->row_array();

        if (!$periode) {
            $periode = $this->db
                ->order_by('id', 'DESC')
                ->get('master_tahun_ajaran')
                ->row_array();
        }

        return $periode;
    }

    public function kelas_list($idPeriode = null)
    {
        if ($idPeriode === null) {
            $idPeriode = (int) $this->input->post('id_periode');
        }

        $this->db
            ->select('ks.id, ks.nama_kelas, ks.id_periode, ta.periode')
            ->from('kelas_setting ks')
            ->join('master_tahun_ajaran ta', 'ta.id = CAST(ks.id_periode AS UNSIGNED)', 'left');

        if ((int) $idPeriode > 0) {
            $this->db->where('ks.id_periode', (string) $idPeriode);
        }

        return $this->db
            ->order_by('ks.nama_kelas', 'ASC')
            ->get()
            ->result_array();
    }

    public function result()
    {
        $idPeriode = (int) $this->input->post('id_periode');
        $idKelas = (int) $this->input->post('id_kelas_setting');
        $search = trim((string) $this->input->post('search', true));
        $dari = trim((string) $this->input->post('dari_tanggal', true));
        $sampai = trim((string) $this->input->post('sampai_tanggal', true));

        $sql = "SELECT
                    ts.id AS id_tagihan_siswa,
                    ts.no_tagihan,
                    ts.nama_tagihan,
                    ts.nama_bulan,
                    ts.tahun,
                    ts.nominal_tagihan,
                    ts.nominal_dibayar,
                    ts.sisa_tagihan,
                    ts.status_pembayaran,
                    ts.id_siswa,
                    ts.nis,
                    ts.nama_siswa,
                    ts.nama_kelas,
                    ts.id_kelas_setting,
                    ts.id_periode,
                    ts.periode,
                    COUNT(pd.id) AS jumlah_cicilan,
                    COALESCE(SUM(CAST(pd.nominal_bayar AS DECIMAL(18,2))), 0) AS total_dibayar_cicilan,
                    DATE_FORMAT(
                        MAX(STR_TO_DATE(CONCAT(p.tanggal_transaksi, ' ', p.waktu_transaksi), '%d-%m-%Y %H:%i:%s')),
                        '%d-%m-%Y'
                    ) AS tanggal_terakhir,
                    DATE_FORMAT(
                        MAX(STR_TO_DATE(CONCAT(p.tanggal_transaksi, ' ', p.waktu_transaksi), '%d-%m-%Y %H:%i:%s')),
                        '%H:%i:%s'
                    ) AS waktu_terakhir,
                    MAX(CASE WHEN CAST(pd.sisa_setelah AS DECIMAL(18,2)) > 0 THEN 1 ELSE 0 END) AS pernah_dicicil
                FROM tagihan_siswa ts
                INNER JOIN tagihan_pembayaran_detail pd
                    ON pd.id_tagihan_siswa = ts.id
                   AND pd.status_detail = 'Aktif'
                INNER JOIN tagihan_pembayaran p
                    ON p.id = pd.id_pembayaran
                   AND p.status_transaksi = 'Aktif'
                WHERE 1 = 1";
        $params = array();

        if ($idPeriode > 0) {
            $sql .= ' AND ts.id_periode = ?';
            $params[] = $idPeriode;
        }
        if ($idKelas > 0) {
            $sql .= ' AND ts.id_kelas_setting = ?';
            $params[] = $idKelas;
        }
        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= " AND (
                        ts.nama_siswa LIKE ?
                        OR ts.nis LIKE ?
                        OR ts.no_tagihan LIKE ?
                        OR ts.nama_tagihan LIKE ?
                        OR EXISTS (
                            SELECT 1
                            FROM tagihan_pembayaran_detail pd_search
                            INNER JOIN tagihan_pembayaran p_search ON p_search.id = pd_search.id_pembayaran
                            WHERE pd_search.id_tagihan_siswa = ts.id
                              AND pd_search.status_detail = 'Aktif'
                              AND p_search.status_transaksi = 'Aktif'
                              AND p_search.no_transaksi LIKE ?
                        )
                    )";
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($dari !== '' || $sampai !== '') {
            $sql .= " AND EXISTS (
                        SELECT 1
                        FROM tagihan_pembayaran_detail pd_filter
                        INNER JOIN tagihan_pembayaran p_filter ON p_filter.id = pd_filter.id_pembayaran
                        WHERE pd_filter.id_tagihan_siswa = ts.id
                          AND pd_filter.status_detail = 'Aktif'
                          AND p_filter.status_transaksi = 'Aktif'";
            if ($dari !== '') {
                $sql .= " AND STR_TO_DATE(p_filter.tanggal_transaksi, '%d-%m-%Y') >= STR_TO_DATE(?, '%d-%m-%Y')";
                $params[] = $dari;
            }
            if ($sampai !== '') {
                $sql .= " AND STR_TO_DATE(p_filter.tanggal_transaksi, '%d-%m-%Y') <= STR_TO_DATE(?, '%d-%m-%Y')";
                $params[] = $sampai;
            }
            $sql .= ')';
        }

        $sql .= " GROUP BY
                    ts.id,
                    ts.no_tagihan,
                    ts.nama_tagihan,
                    ts.nama_bulan,
                    ts.tahun,
                    ts.nominal_tagihan,
                    ts.nominal_dibayar,
                    ts.sisa_tagihan,
                    ts.status_pembayaran,
                    ts.id_siswa,
                    ts.nis,
                    ts.nama_siswa,
                    ts.nama_kelas,
                    ts.id_kelas_setting,
                    ts.id_periode,
                    ts.periode
                  HAVING COUNT(pd.id) > 1
                     OR MAX(CASE WHEN CAST(pd.sisa_setelah AS DECIMAL(18,2)) > 0 THEN 1 ELSE 0 END) = 1
                  ORDER BY ts.nama_siswa ASC,
                           MAX(STR_TO_DATE(CONCAT(p.tanggal_transaksi, ' ', p.waktu_transaksi), '%d-%m-%Y %H:%i:%s')) DESC,
                           ts.id DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    public function detail()
    {
        $idTagihanSiswa = (int) $this->input->post('id_tagihan_siswa');
        if ($idTagihanSiswa <= 0) {
            return array('result' => 'false', 'message' => 'Tagihan siswa tidak ditemukan.');
        }

        $tagihan = $this->db
            ->select('id, no_tagihan, nama_tagihan, nama_bulan, tahun, nominal_tagihan, nominal_dibayar, sisa_tagihan, status_pembayaran, nis, nama_siswa, nama_kelas, periode')
            ->where('id', $idTagihanSiswa)
            ->get('tagihan_siswa')
            ->row_array();

        if (!$tagihan) {
            return array('result' => 'false', 'message' => 'Tagihan siswa tidak ditemukan.');
        }

        $detail = $this->db->query(
            "SELECT
                pd.id,
                pd.id_pembayaran,
                pd.no_transaksi,
                pd.nominal_bayar,
                pd.nominal_sudah_dibayar_sebelum,
                pd.sisa_sebelum,
                pd.sisa_setelah,
                pd.status_setelah,
                p.tanggal_transaksi,
                p.waktu_transaksi,
                p.nama_metode_pembayaran,
                p.referensi_pembayaran,
                p.nama_user
             FROM tagihan_pembayaran_detail pd
             INNER JOIN tagihan_pembayaran p ON p.id = pd.id_pembayaran
             WHERE pd.id_tagihan_siswa = ?
               AND pd.status_detail = 'Aktif'
               AND p.status_transaksi = 'Aktif'
             ORDER BY
                STR_TO_DATE(CONCAT(p.tanggal_transaksi, ' ', p.waktu_transaksi), '%d-%m-%Y %H:%i:%s') ASC,
                pd.id ASC",
            array($idTagihanSiswa)
        )->result_array();

        return array(
            'result' => 'true',
            'tagihan' => $tagihan,
            'detail' => $detail
        );
    }
}
