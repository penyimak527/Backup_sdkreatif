<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_monitoring_tagihan extends CI_Model
{
    public function periode_list()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get('master_tahun_ajaran')
            ->result_array();
    }

    public function kelas_list()
    {
        return $this->db
            ->select('k.id, k.nama_kelas, k.id_periode, ta.periode')
            ->from('kelas_setting k')
            ->join('master_tahun_ajaran ta', 'ta.id=CAST(k.id_periode AS UNSIGNED)', 'left')
            ->order_by('k.id_periode', 'DESC')
            ->order_by('k.nama_kelas', 'ASC')
            ->get()
            ->result_array();
    }

    public function jenis_list()
    {
        return $this->db
            ->where('status', 'Aktif')
            ->order_by('nama_jenis', 'ASC')
            ->get('tagihan_jenis')
            ->result_array();
    }

    public function siswa_by_id($idSiswa)
    {
        $idSiswa = (int) $idSiswa;
        if ($idSiswa <= 0) {
            return null;
        }

        return $this->db->query(
            "SELECT
                s.id,
                s.nis,
                s.nisn,
                s.nama_lengkap,
                s.status_aktif AS status_pendaftaran,
                COALESCE(k.nama_kelas, '') AS nama_kelas,
                COALESCE(k.id, 0) AS id_kelas_setting,
                COALESCE(k.id_periode, 0) AS id_periode,
                COALESCE(ta.periode, '') AS periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks
                ON ks.id=(
                    SELECT MAX(ks2.id)
                    FROM kelas_siswa ks2
                    WHERE CAST(ks2.id_siswa AS UNSIGNED)=s.id
                      AND UPPER(ks2.status_aktif)='AKTIF'
                )
             LEFT JOIN kelas_setting k
                ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             LEFT JOIN master_tahun_ajaran ta
                ON ta.id=CAST(k.id_periode AS UNSIGNED)
             WHERE s.id=?
             LIMIT 1",
            array($idSiswa)
        )->row_array();
    }

    public function cari_siswa()
    {
        $q = trim((string) $this->input->post('q', true));
        if (strlen($q) < 2) {
            return array();
        }

        $like = '%' . $q . '%';

        return $this->db->query(
            "SELECT
                s.id,
                s.nis,
                s.nisn,
                s.nama_lengkap,
                s.status_aktif AS status_pendaftaran,
                COALESCE(k.nama_kelas, '') AS nama_kelas,
                COALESCE(k.id, 0) AS id_kelas_setting,
                COALESCE(k.id_periode, 0) AS id_periode,
                COALESCE(ta.periode, '') AS periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks
                ON ks.id=(
                    SELECT MAX(ks2.id)
                    FROM kelas_siswa ks2
                    WHERE CAST(ks2.id_siswa AS UNSIGNED)=s.id
                      AND UPPER(ks2.status_aktif)='AKTIF'
                )
             LEFT JOIN kelas_setting k
                ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             LEFT JOIN master_tahun_ajaran ta
                ON ta.id=CAST(k.id_periode AS UNSIGNED)
             WHERE ( s.nama_lengkap LIKE ?
                OR s.nis LIKE ?
                OR s.nisn LIKE ? ) AND UPPER(s.status_siswa)='AKTIF'
             ORDER BY s.nama_lengkap ASC
             LIMIT 25",
            array($like, $like, $like)
        )->result_array();
    }

    public function master_list()
    {
        $periode = (int) $this->input->post('id_periode');
        $jenis = (int) $this->input->post('id_jenis');

        $this->db
            ->select('id, kode_tagihan, nama_tagihan, tipe_tagihan, id_periode, id_jenis_tagihan')
            ->from('tagihan_master')
            ->where_in('status', array('Aktif', 'Dibatalkan'));

        if ($periode > 0) {
            $this->db->where('id_periode', $periode);
        }

        if ($jenis > 0) {
            $this->db->where('id_jenis_tagihan', $jenis);
        }

        return $this->db
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();
    }

    public function monitoring_result($filter = array())
    {
        $filter = $this->resolve_filter($filter);

        $this->db
            ->select("ts.*,
                COALESCE(NULLIF(ts.kode_tagihan,''), m.kode_tagihan, '') AS kode_tagihan_monitoring",
                false
            )
            ->from('tagihan_siswa ts')
            ->join('tagihan_master m', 'm.id=ts.id_tagihan_master', 'left');

        if ($filter['id_siswa'] > 0) {
            $this->db->where('ts.id_siswa', $filter['id_siswa']);
        }

        if ($filter['id_periode'] > 0) {
            $this->db->where('ts.id_periode', $filter['id_periode']);
        }

        if ($filter['id_kelas_setting'] > 0) {
            $this->db->where('ts.id_kelas_setting', $filter['id_kelas_setting']);
        }

        if ($filter['id_jenis'] > 0) {
            $this->db->where('ts.id_jenis_tagihan', $filter['id_jenis']);
        }

        if ($filter['id_master'] > 0) {
            $this->db->where('ts.id_tagihan_master', $filter['id_master']);
        }

        if ($filter['tipe'] !== '') {
            $this->db->where('ts.tipe_tagihan', $filter['tipe']);
        }

        if ($filter['status'] !== '') {
            $this->db->where('ts.status_pembayaran', $filter['status']);
        }

        if ($filter['sampai_bulan'] > 0) {
            $urutanSampai = $filter['sampai_bulan'] >= 7
                ? $filter['sampai_bulan'] - 6
                : $filter['sampai_bulan'] + 6;

            $this->db->where(
                "(CASE WHEN ts.bulan >= 7 THEN ts.bulan - 6 ELSE ts.bulan + 6 END) <= " . (int) $urutanSampai,
                null,
                false
            );
        }

        $rows = $this->db
            ->order_by('ts.nama_siswa', 'ASC')
            ->order_by('ts.id_periode', 'DESC')
            ->order_by('ts.tahun', 'ASC')
            ->order_by('ts.bulan', 'ASC')
            ->order_by('ts.nama_tagihan', 'ASC')
            ->get()
            ->result_array();

        $summary = array(
            'tagihan' => 0,
            'dibayar' => 0,
            'sisa' => 0,
            'realisasi' => 0,
            'jumlah_data' => count($rows)
        );

        foreach ($rows as &$row) {
            if (isset($row['kode_tagihan_monitoring']) && $row['kode_tagihan_monitoring'] !== '') {
                $row['kode_tagihan'] = $row['kode_tagihan_monitoring'];
            }
            unset($row['kode_tagihan_monitoring']);

            // Seluruh ringkasan mengikuti rows yang sudah terkena filter Monitoring.
            // Termasuk ketika filter status memilih Dibatalkan, Dibebaskan, Lunas, dan lainnya.
            $summary['tagihan'] += (float) $row['nominal_tagihan'];
            $summary['dibayar'] += (float) $row['nominal_dibayar'];
            $summary['sisa'] += (float) $row['sisa_tagihan'];
        }
        unset($row);

        if ($summary['tagihan'] > 0) {
            $summary['realisasi'] = round(
                ($summary['dibayar'] / $summary['tagihan']) * 100,
                2
            );
        }

        return array(
            'result' => 'true',
            'rows' => $rows,
            'summary' => $summary
        );
    }

    public function filter_info($filter = array())
    {
        $filter = $this->resolve_filter($filter, false);

        $info = array(
            'siswa' => 'Semua Siswa',
            'tahun_ajaran' => 'Semua Tahun Ajaran',
            'kelas' => 'Semua Kelas',
            'jenis_tagihan' => 'Semua Jenis',
            'batch_periode' => 'Semua Batch/Periode',
            'tipe' => $filter['tipe'] !== '' ? $filter['tipe'] : 'Semua Tipe',
            'status' => $filter['status'] !== '' ? $filter['status'] : 'Semua Status',
            'sampai_bulan' => 'Semua Bulan'
        );

        if ($filter['id_siswa'] > 0) {
            $row = $this->db
                ->select('nama_lengkap, nis, nisn')
                ->where('id', $filter['id_siswa'])
                ->get('siswa')
                ->row_array();
            if ($row) {
                $info['siswa'] = $row['nama_lengkap'];
                if (!empty($row['nis'])) {
                    $info['siswa'] .= ' (' . $row['nis'] . ')';
                }
            }
        }

        if ($filter['id_periode'] > 0) {
            $row = $this->db
                ->select('periode')
                ->where('id', $filter['id_periode'])
                ->get('master_tahun_ajaran')
                ->row_array();
            if ($row) {
                $info['tahun_ajaran'] = $row['periode'];
            }
        }

        if ($filter['id_kelas_setting'] > 0) {
            $row = $this->db
                ->select('nama_kelas')
                ->where('id', $filter['id_kelas_setting'])
                ->get('kelas_setting')
                ->row_array();
            if ($row) {
                $info['kelas'] = $row['nama_kelas'];
            }
        }

        if ($filter['id_jenis'] > 0) {
            $row = $this->db
                ->select('nama_jenis')
                ->where('id', $filter['id_jenis'])
                ->get('tagihan_jenis')
                ->row_array();
            if ($row) {
                $info['jenis_tagihan'] = $row['nama_jenis'];
            }
        }

        if ($filter['id_master'] > 0) {
            $row = $this->db
                ->select('nama_tagihan, tipe_tagihan')
                ->where('id', $filter['id_master'])
                ->get('tagihan_master')
                ->row_array();
            if ($row) {
                $info['batch_periode'] = $row['nama_tagihan'];
                if (!empty($row['tipe_tagihan'])) {
                    $info['batch_periode'] .= ' (' . $row['tipe_tagihan'] . ')';
                }
            }
        }

        $bulan = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );
        if ($filter['sampai_bulan'] > 0 && isset($bulan[$filter['sampai_bulan']])) {
            $info['sampai_bulan'] = $bulan[$filter['sampai_bulan']];
        }

        return $info;
    }

    private function resolve_filter($filter = array(), $allowPost = true)
    {
        return array(
            'id_siswa' => isset($filter['id_siswa'])
                ? (int) $filter['id_siswa']
                : ($allowPost ? (int) $this->input->post('id_siswa') : 0),
            'id_periode' => isset($filter['id_periode'])
                ? (int) $filter['id_periode']
                : ($allowPost ? (int) $this->input->post('id_periode') : 0),
            'id_kelas_setting' => isset($filter['id_kelas_setting'])
                ? (int) $filter['id_kelas_setting']
                : ($allowPost ? (int) $this->input->post('id_kelas_setting') : 0),
            'id_jenis' => isset($filter['id_jenis'])
                ? (int) $filter['id_jenis']
                : ($allowPost ? (int) $this->input->post('id_jenis') : 0),
            'id_master' => isset($filter['id_master'])
                ? (int) $filter['id_master']
                : ($allowPost ? (int) $this->input->post('id_master') : 0),
            'tipe' => isset($filter['tipe'])
                ? trim((string) $filter['tipe'])
                : ($allowPost ? trim((string) $this->input->post('tipe', true)) : ''),
            'status' => isset($filter['status'])
                ? trim((string) $filter['status'])
                : ($allowPost ? trim((string) $this->input->post('status', true)) : ''),
            'sampai_bulan' => isset($filter['sampai_bulan'])
                ? (int) $filter['sampai_bulan']
                : ($allowPost ? (int) $this->input->post('sampai_bulan') : 0)
        );
    }
}
