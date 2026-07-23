<?php
class M_tahun_ajaran extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Ambil seluruh data tahun ajaran
    public function tahun_ajaran_result()
    {
        // Ambil semua tahun ajaran
        $query = $this->db->get('master_tahun_ajaran');
        return $query->result_array();
    }

    public function tambah()
    {
        $data = [
            'periode' => $this->input->post('periode'),
            'status' => $this->input->post('status'),
            'tanggal' => date('Y-m-d H:i:s'),
            // Tambah field lain sesuai kebutuhan
        ];

        return $this->db->insert('master_tahun_ajaran', $data);
    }

    public function edit()
    {
        $id = $this->input->post('id');
        $data = [
            'periode' => $this->input->post('periode'),
            'status' => $this->input->post('status'),
            // Tambah field lain sesuai kebutuhan
        ];

        return $this->db->update('master_tahun_ajaran', $data, ['id' => $id]);
    }

    public function update_status()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');

        // Jika status aktif, pastikan nonaktifkan yang lain dulu
        if ($status == 'Aktif') {
            $this->db->update('master_tahun_ajaran', ['status' => 'Tidak Aktif']);
        }

        return $this->db->update('master_tahun_ajaran', ['status' => $status], ['id' => $id]);
    }

    public function hapus()
    {
        $id = $this->input->post('id');
        return $this->db->delete('master_tahun_ajaran', ['id' => $id]);
    }
}
?>
