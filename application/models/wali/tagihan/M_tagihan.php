<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tagihan extends CI_Model
{
    public function periode_list($id_siswa)
    {
        return $this->db
            ->distinct()
            ->select('id_periode, periode')
            ->from('tagihan_siswa')
            ->where('id_siswa', (int) $id_siswa)
            ->where('status_tagihan', 'Aktif')
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

        $this->db->select('id, no_tagihan, nama_jenis_tagihan, nama_tagihan, tipe_tagihan, id_periode, periode, semester, '
            .'bulan, nama_bulan, tahun, tanggal_jatuh_tempo, nominal_awal, jenis_keringanan, nilai_keringanan, '
            .'nominal_tagihan, nominal_dibayar, sisa_tagihan, dianggap_tunggakan, status_pembayaran, keterangan');
        $this->query_filter($id_siswa, $filter);
        $this->db
            ->order_by('tahun', 'DESC')
            ->order_by('bulan', 'DESC')
            ->order_by('id', 'DESC');
        if (!$client_paging) {
            $this->db->limit($per_halaman, $offset);
        }
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$row) {
            $row['is_tunggakan'] = $this->is_tunggakan($row) ? 'Ya' : 'Tidak';
        }
        unset($row);

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
        $row = $this->db
            ->select('id, no_tagihan, kode_tagihan, nama_jenis_tagihan, nama_tagihan, tipe_tagihan, id_periode, periode, semester, '
                .'bulan, nama_bulan, tahun, tanggal_jatuh_tempo, nis, nisn, nama_siswa, nama_kelas, nominal_awal, '
                .'jenis_keringanan, nilai_keringanan, nominal_tagihan, nominal_dibayar, sisa_tagihan, dianggap_tunggakan, '
                .'status_pembayaran, status_tagihan, keterangan')
            ->from('tagihan_siswa')
            ->where('id', (int) $id)
            ->where('id_siswa', (int) $id_siswa)
            ->where('status_tagihan', 'Aktif')
            ->limit(1)
            ->get()
            ->row_array();

        if ($row) $row['is_tunggakan'] = $this->is_tunggakan($row) ? 'Ya' : 'Tidak';
        return $row;
    }

    private function query_filter($id_siswa, $filter)
    {
        $this->db
            ->from('tagihan_siswa')
            ->where('id_siswa', (int) $id_siswa)
            ->where('status_tagihan', 'Aktif');

        if (!empty($filter['search'])) {
            $this->db->group_start()
                ->like('no_tagihan', $filter['search'])
                ->or_like('nama_tagihan', $filter['search'])
                ->or_like('nama_jenis_tagihan', $filter['search'])
                ->group_end();
        }

        if (!empty($filter['id_periode'])) {
            $this->db->where('id_periode', (int) $filter['id_periode']);
        }

        if (in_array($filter['tipe_tagihan'], array('Bulanan', 'Tahunan', 'Langsung'), true)) {
            $this->db->where('tipe_tagihan', $filter['tipe_tagihan']);
        }

        if (in_array($filter['status_pembayaran'], array('Belum Dibayar', 'Dibayar Sebagian', 'Lunas', 'Dibebaskan'), true)) {
            $this->db->where('status_pembayaran', $filter['status_pembayaran']);
        }
    }

    private function is_tunggakan($row)
    {
        if (($row['dianggap_tunggakan'] ?? 'Tidak') !== 'Ya' || (float) ($row['sisa_tagihan'] ?? 0) <= 0) {
            return false;
        }

        $jatuh_tempo = DateTime::createFromFormat('!d-m-Y', (string) ($row['tanggal_jatuh_tempo'] ?? ''));
        return $jatuh_tempo && $jatuh_tempo < new DateTime('today');
    }
}
