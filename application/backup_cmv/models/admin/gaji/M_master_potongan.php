<?php

class M_master_potongan extends CI_Model
{
    public function master_potongan_result()
    {
        $search = $this->input->post('search');
        $this->db->select('mp.*');
        if ($search != null) {
            $this->db->like('mp.nama_potongan', $search);
        }
        $master_potongan = $this->db->get('master_potongan mp')->result_array();
        return $master_potongan;
    }

    public function tambah()
    {
        $nama_potongan = $this->input->post('nama_potongan');
        $data = [
            'nama_potongan' => $nama_potongan
        ];
        $this->db->trans_begin();

        $this->db->insert('master_potongan', $data);
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
        $id_master_potongan = $this->input->post('id_master_potongan');
        $nama_potongan = $this->input->post('nama_potongan');

        $data = [
            'nama_potongan' => $nama_potongan
        ];

        $this->db->trans_begin();

        $this->db->where('id', $id_master_potongan);
        $this->db->update('master_potongan', $data);

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
        $id_master_potongan = $this->input->post('id');

        $this->db->delete('master_potongan', ['id' => $id_master_potongan]);
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