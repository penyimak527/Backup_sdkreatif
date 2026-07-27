<?php

class M_bonus extends CI_Model
{
	public function bonus_result()
	{
		$search = $this->input->post('search');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');
		$this->db->select('b.*, pg.nama_pegawai');
		$this->db->join('pegawai pg', 'b.id_pegawai = pg.id');
		if ($search != null) {
			$this->db->like('pg.nama_pegawai', $search);
		}
		if ($bulan != null  && $tahun != null) {
			$this->db->where('b.bulan', $bulan);
			$this->db->where('b.tahun', $tahun);
		}
		$bonus = $this->db->get('bonus b')->result_array();
		return $bonus;
	}
	public function pegawai_result()
    {
        $id_pegawai = $this->input->post('id_pegawai');

        $this->db->select('p.*');
        $this->db->from('pegawai p');
        // $this->db->where("p.id NOT IN (SELECT id_pegawai FROM gaji)", null, false);
        if ($id_pegawai != null) {
            $this->db->or_where('p.id', $id_pegawai);
        }

        $pegawai = $this->db->get()->result_array();
        return $pegawai;
    }

	 public function tambah(){
        $id_pegawai = $this->input->post('id_pegawai');
        $keterangan = $this->input->post('keterangan');
        // $tanggal_input = $this->input->post('tanggal');
        $nominal = (int) str_replace(',', '', $this->input->post('nominal'));
        $bulan = $this->input->post('bulan_bonus');
        $tahun = $this->input->post('tahun_bonus');
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

        $data = [
            'id_pegawai' => $id_pegawai,
            // 'tanggal_input' => $tanggal_input,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'keterangan' => $keterangan,
            'nominal' => $nominal,
            'tanggal' => $tanggal
        ];
        $this->db->trans_begin();
      
        $this->db->insert('bonus', $data);
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
        $id_bonus = $this->input->post('id_bonus');
        $id_pegawai = $this->input->post('id_pegawai');
		$keterangan = $this->input->post('keterangan');
        // $tanggal_input = $this->input->post('tanggal');
        $nominal = (int) str_replace(',', '', $this->input->post('nominal'));
        $bulan = $this->input->post('bulan_bonus');
        $tahun = $this->input->post('tahun_bonus');
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

        $data = [
            'id_pegawai' => $id_pegawai,
            // 'tanggal_input' => $tanggal_input,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'keterangan' => $keterangan,
            'nominal' => $nominal,
            'tanggal' => $tanggal
        ];

        $this->db->trans_begin();
        
        $this->db->where('id', $id_bonus);
        $this->db->update('bonus', $data);

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
        $id_bonus = $this->input->post('id');

        $this->db->delete('bonus', ['id' => $id_bonus]);
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
                'message'   => 'Data berhasil dihapus'
            );
        }
        return $response;
    }
}