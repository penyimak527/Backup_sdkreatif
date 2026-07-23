<?php
class M_setting_presensi_pegawai extends CI_Model{
    public function get_setting_presensi(){
		$search = $this->input->post('search');
		if ($search != null) {
			$this->db->like('nama_aturan', $search);
		}
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('presensi_setting')->result_array();
		return $result;
    }
    public function get_setting_presensi_aktif(){
		$search = $this->input->post('search');
		if ($search != null) {
			$this->db->like('nama_aturan', $search);
		}
        $this->db->where('status', 'aktif');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('presensi_setting')->result_array();
		return $result;
    }
    public function tambah(){
        $inputan = array(
            'nama_aturan'   => $this->input->post('nama_aturan'),
            'jam_masuk'     => $this->input->post('jam_masuk'),
            'jam_pulang'     => $this->input->post('jam_pulang'),
            'status'     => $this->input->post('status'),
            'status_jam'     => $this->input->post('status_jam'),
        );
        $response = $this->db->insert('presensi_setting', $inputan);
        return $response;
    }
    public function edit()
    {

        $id_setting = $this->input->post('id_setting');
        $nama_aturan = $this->input->post('nama_aturan');
        $jam_masuk = $this->input->post('jam_masuk');
        $jam_pulang = $this->input->post('jam_pulang');
        $status = $this->input->post('status');
        $status_jam = $this->input->post('status_jam');
        $data = [
            'nama_aturan' => $nama_aturan,
            'jam_masuk' => $jam_masuk,
            'jam_pulang' => $jam_pulang,
            'status' => $status,
            'status_jam' => $status_jam,
        ];

        $response = $this->db->update('presensi_setting', $data, ['id' => $id_setting]);
        return $response;
    }
    public function hapus()
    {
        $id = $this->input->post('id');
        $response = $this->db->delete('presensi_setting', ['id' => $id]);
        return $response;
    }

}
?>