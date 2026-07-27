<?php

class M_pinjaman_pegawai extends CI_Model
{
    public function pinjaman_pegawai_result()
    {
        $search = $this->input->post('search');
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_akhir = $this->input->post('tanggal_akhir');

          if (!empty($tanggal_mulai)) {
        $pecah1 = explode('-', $tanggal_mulai);

        $bulan_mulai = (int)$pecah1[1];
        $tahun_mulai = (int)$pecah1[2];
    }

    if (!empty($tanggal_akhir)) {
        $pecah2 = explode('-', $tanggal_akhir);

        $bulan_akhir = (int)$pecah2[1];
        $tahun_akhir = (int)$pecah2[2];
    }

        
        $this->db->select('pj.*, pg.nama_pegawai');
        $this->db->join('pegawai pg', 'pj.id_pegawai = pg.id');
        if ($search != null) {
            $this->db->like('pg.nama_pegawai', $search);
        }
           if(!empty($tanggal_mulai) && !empty($tanggal_akhir)){

        $this->db->where("
        (pj.tahun_awal < $tahun_akhir OR
            (
                pj.tahun_awal = $tahun_akhir
                AND
                pj.bulan_awal <= $bulan_akhir
            ))
        AND
        (pj.tahun_akhir > $tahun_mulai OR
            (pj.tahun_akhir = $tahun_mulai
                AND
                pj.bulan_akhir >= $bulan_mulai))", null, false);
    }
        $pinjaman = $this->db->get('pinjaman pj')->result_array();
        return $pinjaman;
    }
    public function pegawai_result()
    {
        $this->db->select('p.*');
        $this->db->from('pegawai p');
        $pegawai = $this->db->get()->result_array();
        return $pegawai;
    }

    public function row_pinjaman()
    {
        $id_pinjaman = $this->input->post('id_pinjaman');
        $pinjaman = $this->db->query("SELECT pj.*, pg.nama_pegawai FROM pinjaman pj LEFT JOIN pegawai pg on pj.id_pegawai = pg.id WHERE pj.id='$id_pinjaman'")->row_array();
        $pinjaman_detail = $this->db->query("SELECT pjd.* FROM pinjaman_detail pjd WHERE pjd.id_pinjaman = '$id_pinjaman' ORDER BY pjd.id")->result_array();

        return [
            'pinjaman' => $pinjaman,
            'detail' => $pinjaman_detail
        ];
    }
    public function tambah()
    {
        $id_pegawai = $this->input->post('id_pegawai');
        $bulan_awal = $this->input->post('bulan_awal');
        $tahun_awal = $this->input->post('tahun_awal');
        $bulan_akhir = $this->input->post('bulan_akhir');
        $tahun_akhir = $this->input->post('tahun_akhir');
        $lama_pinjaman = $this->input->post('lama_pinjaman');
        $nilai_pinjaman = (int) str_replace(',', '', $this->input->post('nilai_pinjaman'));
        $tanggal = date('d-m-Y');
        $waktu = date('H:i:s');

        $data = [
            'id_pegawai' => $id_pegawai,
            'bulan_awal' => $bulan_awal,
            'tahun_awal' => $tahun_awal,
            'bulan_akhir' => $bulan_akhir,
            'tahun_akhir' => $tahun_akhir,
            'lama_pinjaman' => $lama_pinjaman,
            'nilai_pinjaman' => $nilai_pinjaman,
            'sisa_pinjaman' => $nilai_pinjaman,
            'status' => 'Hutang',
            'tanggal' => $tanggal,
            'waktu' => $waktu
        ];


        $this->db->trans_begin();
        $this->db->insert('pinjaman', $data);

        $get_id_pinjaman = $this->db->insert_id();
        $bulan = $this->input->post('detail_bulan');
        $tahun = $this->input->post('detail_tahun');
        $nominal = $this->input->post('nominal_tagihan');

        for ($i = 0; $i < count($bulan); $i++) {
            $detail = [
                'id_pinjaman' => $get_id_pinjaman,
                'bulan' => $bulan[$i],
                'tahun' => $tahun[$i],
                'nominal_tagihan' => str_replace(',', '', $nominal[$i]),
                'status_bayar' => 'Belum',
                'tanggal' => date('d-m-Y')
            ];
            $this->db->insert('pinjaman_detail', $detail);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menyimpan data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil disimpan'
            );
        }
        return $response;
    }

    public function edit()
    {
        $nominal_tagihan = $this->input->post('nominal_tagihan');
        $detail_id = $this->input->post('detail_id');

        $this->db->trans_begin();

        if (!empty($detail_id)) {
            foreach ($detail_id as $key => $id_detail) {
                $nominal = preg_replace('/[^0-9]/', '', $nominal_tagihan[$key]);
                $nominal = (int) $nominal;
                $data_detail = [
                    'nominal_tagihan' => $nominal
                ];

                $this->db->where('id', $id_detail)->update('pinjaman_detail', $data_detail);
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal mengupdate data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil diupdate'
            );
        }
        return $response;
    }

    public function hapus()
    {
        $id_pinjaman = $this->input->post('id');

        $this->db->delete('pinjaman', ['id' => $id_pinjaman]);
        $this->db->delete('pinjaman_detail', ['id_pinjaman' => $id_pinjaman]);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menghapus data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil dihapus'
            );
        }
        return $response;
    }
}