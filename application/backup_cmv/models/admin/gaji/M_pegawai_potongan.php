<?php

class M_pegawai_potongan extends CI_Model
{
    public function pegawai_potongan_result()
    {
        $search = $this->input->post('search');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');

        $this->db->select('pp.*, pg.nama_pegawai');
        $this->db->join('pegawai pg', 'pp.id_pegawai = pg.id');
        if (!empty($search)) {
            $this->db->like('pg.nama_pegawai', $search);
        }
        if (!empty($bulan) && !empty($tahun)) {
            $this->db->where('pp.bulan', $bulan);
            $this->db->where('pp.tahun', $tahun);
        }
        $data = $this->db->get('pegawai_potongan pp')->result_array();
        foreach ($data as &$d) {
            $this->db->select('ppd.*, mp.nama_potongan');
            $this->db->from('pegawai_potongan_detail ppd');
            $this->db->join('master_potongan mp', 'ppd.id_master_potongan = mp.id');
            $this->db->where('ppd.id_pegawai_potongan', $d['id']);
            $d['detail_potongan'] = $this->db->get()->result_array();
        }
        return $data;
    }
    public function pegawai_result()
    {
        $id_pegawai = $this->input->post('id_pegawai');

        $this->db->select('p.*');
        $this->db->from('pegawai p');
        // $this->db->where("p.id NOT IN (SELECT id_pegawai FROM potongan_lain)", null, false);
        if ($id_pegawai != null) {
            $this->db->or_where('p.id', $id_pegawai);
        }

        $pegawai = $this->db->get()->result_array();
        return $pegawai;
    }
    public function tambah()
    {
        $id_pegawai = $this->input->post('id_pegawai');
        $bulan = $this->input->post('bulan_potongan');
        $tahun = $this->input->post('tahun_potongan');
        // $tanggal_input = $this->input->post('tanggal');
        // if ($tanggal_input == '') {
        //     return array(
        //         'status' => false,
        //         'code' => 401,
        //         'message' => 'Tanggal belum diisi'
        //     );
        // }
        // $pecah = explode('-', $tanggal_input);
        // $bulan = $pecah[1]; // 05
        // $tahun = $pecah[2]; // 2026
        // $get_potongan = $this->db->get_where('pegawai_potongan', ['id_pegawai' => $id_pegawai, 'bulan' => $bulan, 'tahun' => $tahun])->num_rows();
        // if ($get_potongan > 0) {
        //     return array(
        //         'status' => false,
        //         'code' => 401,
        //         'message' => 'Potongan untuk pegawai ini sudah ada pada bulan ' . $bulan . ' ' . $tahun
        //     );
        // }
        $tanggal = date('d-m-Y');
        $header = [
            'id_pegawai' => $id_pegawai,
            // 'tanggal_input' => $tanggal_input,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal
        ];
        $this->db->trans_begin();

        $this->db->insert('pegawai_potongan', $header);
        $id_header = $this->db->insert_id();
        $id_master_potongan = $this->input->post('id_master_potongan');
        $nominal = $this->input->post('nominal');
        foreach ($id_master_potongan as $key => $potongan) {
            $detail = [
                'id_pegawai_potongan' => $id_header,
                'id_master_potongan' => $potongan,
                'nominal' => (int) str_replace(',', '', $nominal[$key])
            ];
            $this->db->insert('pegawai_potongan_detail', $detail);
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
        $id_potongan_pegawai = $this->input->post('id_potong_pegawai');
        $id_pegawai = $this->input->post('id_pegawai');
        $bulan = $this->input->post('bulan_potongan');
        $tahun = $this->input->post('tahun_potongan');
        // $tanggal_input = $this->input->post('tanggal');
        // if ($tanggal_input == '') {
        //     return array(
        //         'status' => false,
        //         'code' => 401,
        //         'message' => 'Tanggal belum diisi'
        //     );
        // }
        // $pecah = explode('-', $tanggal_input);
        // $bulan = $pecah[1]; // 05
        // $tahun = $pecah[2]; // 2026
        $tanggal = date('d-m-Y');


        $id_master_potongan = $this->input->post('id_master_potongan');
        $nominal = $this->input->post('nominal');

        $data_header = [
            'id_pegawai' => $id_pegawai,
            // 'tanggal_input' => $tanggal_input,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal
        ];

        $this->db->trans_begin();

        // update header
        $this->db->where('id', $id_potongan_pegawai);
        $this->db->update('pegawai_potongan', $data_header);

        // hapus detail lama
        $this->db->delete('pegawai_potongan_detail', ['id_pegawai_potongan' => $id_potongan_pegawai]);

        // insert detail baru
        foreach ($id_master_potongan as $key => $potongan) {
            $detail = [
                'id_pegawai_potongan' => $id_potongan_pegawai,
                'id_master_potongan' => $potongan,
                'nominal' =>  (int) str_replace(',', '',  $nominal[$key])
            ];
            $this->db->insert('pegawai_potongan_detail', $detail);
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
        $id_potongan_pegawai = $this->input->post('id');

        $this->db->delete('pegawai_potongan_detail', ['id_pegawai_potongan' => $id_potongan_pegawai]);
        // hapus header
        $this->db->delete('pegawai_potongan', ['id' => $id_potongan_pegawai]);
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