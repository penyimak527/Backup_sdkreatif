<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tagihan_tunggakan extends CI_Model
{
    public function periode_list()
    {
        return $this->db->order_by('id', 'DESC')->get('master_tahun_ajaran')->result_array();
    }
    public function jenis_list()
    {
        return $this->db->order_by('nama_jenis')->get('tagihan_jenis')->result_array();
    }
    public function kelas_list()
    {
        $id = (int)$this->input->post('id_periode');
        $this->db->select('id,nama_kelas')->from('kelas_setting');
        if ($id > 0) $this->db->where('CAST(id_periode AS UNSIGNED) = ' . $id, null, false);
        return $this->db->order_by('nama_kelas')->get()->result_array();
    }
    public function result()
    {
        $search = trim((string)$this->input->post('search', true));
        $idPer = (int)$this->input->post('id_periode');
        $idKelas = (int)$this->input->post('id_kelas_setting');
        $idJenis = (int)$this->input->post('id_jenis_tagihan');
        $mode = trim((string)$this->input->post('mode', true));
        $today = date('Y-m-d');
        $this->db->from('tagihan_siswa ts')->where('ts.status_tagihan', 'Aktif')->where("CAST(ts.sisa_tagihan AS DECIMAL(18,2)) > 0", null, false)->where_not_in('ts.status_pembayaran', array('Lunas', 'Dibebaskan', 'Dibatalkan'));
        if ($mode === 'Tunggakan') {
            $this->db->where('ts.dianggap_tunggakan', 'Ya')->where("STR_TO_DATE(ts.tanggal_jatuh_tempo,'%d-%m-%Y') < " . $this->db->escape($today), null, false);
        }
        if ($search !== '') $this->db->group_start()->like('ts.nama_siswa', $search)->or_like('ts.nis', $search)->or_like('ts.nama_tagihan', $search)->group_end();
        if ($idPer > 0) $this->db->where('ts.id_periode', $idPer);
        if ($idKelas > 0) $this->db->where('ts.id_kelas_setting', $idKelas);
        if ($idJenis > 0) $this->db->where('ts.id_jenis_tagihan', $idJenis);
        $rows = $this->db->order_by('ts.nama_siswa')->order_by('ts.tahun')->order_by('ts.bulan')->limit(3000)->get()->result_array();
        $total = 0;
        $hariIni = new DateTime('today');
        foreach ($rows as &$r) {
            $total += (float)$r['sisa_tagihan'];
            $jatuhTempo = false;
            if ($r['dianggap_tunggakan'] === 'Ya' && preg_match('/^\d{2}-\d{2}-\d{4}$/', (string)$r['tanggal_jatuh_tempo'])) {
                $jatuhTempo = DateTime::createFromFormat('!d-m-Y', $r['tanggal_jatuh_tempo']);
            }
            $r['status_tunggakan'] = ($jatuhTempo instanceof DateTime && $jatuhTempo < $hariIni) ? 'Tunggakan' : 'Tagihan Aktif';
        }
        unset($r);
        return array('result' => 'true', 'rows' => $rows, 'summary' => array('jumlah' => count($rows), 'total_sisa' => $total));
    }
}
