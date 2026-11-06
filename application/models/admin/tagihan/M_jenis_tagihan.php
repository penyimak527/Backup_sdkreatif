<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_jenis_tagihan extends CI_Model
{
    public function result()
    {
        $search = trim((string) $this->input->post('search', true));
        $tipe = trim((string) $this->input->post('tipe', true));
        $status = trim((string) $this->input->post('status', true));

        $this->db->from('tagihan_jenis');
        if ($search !== '') {
            $this->db->group_start()
                ->like('nama_jenis', $search)
                ->or_like('kode_jenis', $search)
                ->group_end();
        }
        if ($tipe !== '') {
            $this->db->where('tipe_default', $tipe);
        }
        if ($status !== '') {
            $this->db->where('status', $status);
        }

        return $this->db->order_by('id', 'DESC')->get()->result_array();
    }

    public function tambah()
    {
        $nama = trim((string) $this->input->post('nama_jenis', true));
        $kode = strtoupper(trim((string) $this->input->post('kode_jenis', true)));
        $tipe = trim((string) $this->input->post('tipe_default', true));
        $status = $this->input->post('status', true) === 'Nonaktif' ? 'Nonaktif' : 'Aktif';
        $keterangan = trim((string) $this->input->post('keterangan', true));

        if ($nama === '' || !in_array($tipe, array('Bulanan', 'Langsung', 'Tahunan'), true)) {
            return array('result' => 'false', 'message' => 'Nama dan tipe tagihan wajib diisi.');
        }
        if ($kode === '') {
            $kode = 'JNS-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $nama), 0, 12));
        }
        if ($this->db->where('nama_jenis', $nama)->where('status', 'Aktif')->count_all_results('tagihan_jenis') > 0) {
            return array('result' => 'false', 'message' => 'Nama jenis tagihan aktif sudah digunakan.');
        }

        $user = $this->session->userdata('admin');
        $data = array(
            'kode_jenis' => $kode,
            'nama_jenis' => $nama,
            'tipe_default' => $tipe,
            'status' => $status,
            'keterangan' => $keterangan,
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        );

        $this->db->trans_begin();
        $this->db->insert('tagihan_jenis', $data);
        $id = $this->db->insert_id();
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Tambah Jenis Tagihan',
            'modul' => 'Master Data',
            'aksi' => 'Tambah',
            'nama_tabel' => 'tagihan_jenis',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $kode,
            'keterangan' => 'Menambah jenis tagihan.',
            'data_sebelum' => null,
            'data_sesudah' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $data['id_user'],
            'nama_user' => $data['nama_user']
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Jenis tagihan gagal disimpan.');
        }

        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Jenis tagihan berhasil disimpan.');
    }

    public function edit()
    {
        $id = (int) $this->input->post('id');
        $nama = trim((string) $this->input->post('nama_jenis', true));
        $kode = strtoupper(trim((string) $this->input->post('kode_jenis', true)));
        $tipe = trim((string) $this->input->post('tipe_default', true));
        $status = $this->input->post('status', true) === 'Nonaktif' ? 'Nonaktif' : 'Aktif';
        $keterangan = trim((string) $this->input->post('keterangan', true));
        $before = $this->db->where('id', $id)->get('tagihan_jenis')->row_array();

        if (!$before) {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak ditemukan.');
        }
        if ($nama === '' || !in_array($tipe, array('Bulanan', 'Langsung', 'Tahunan'), true)) {
            return array('result' => 'false', 'message' => 'Nama dan tipe tagihan wajib diisi.');
        }
        if ($kode === '') {
            $kode = $before['kode_jenis'];
        }
        if ($this->db->where('nama_jenis', $nama)->where('status', 'Aktif')->where('id !=', $id)->count_all_results('tagihan_jenis') > 0) {
            return array('result' => 'false', 'message' => 'Nama jenis tagihan aktif sudah digunakan.');
        }

        $user = $this->session->userdata('admin');
        $data = array(
            'kode_jenis' => $kode,
            'nama_jenis' => $nama,
            'tipe_default' => $tipe,
            'status' => $status,
            'keterangan' => $keterangan,
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        );

        $this->db->trans_begin();
        $this->db->where('id', $id)->update('tagihan_jenis', $data);
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Ubah Jenis Tagihan',
            'modul' => 'Master Data',
            'aksi' => 'Ubah',
            'nama_tabel' => 'tagihan_jenis',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $kode,
            'keterangan' => 'Mengubah jenis tagihan.',
            'data_sebelum' => json_encode($before, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => json_encode(array_merge($before, $data), JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $data['id_user'],
            'nama_user' => $data['nama_user']
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Jenis tagihan gagal diperbarui.');
        }

        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Jenis tagihan berhasil diperbarui.');
    }

    public function ubah_status()
    {
        $id = (int) $this->input->post('id');
        $row = $this->db->where('id', $id)->get('tagihan_jenis')->row_array();
        if (!$row) {
            return array('result' => 'false', 'message' => 'Data tidak ditemukan.');
        }

        $status = $row['status'] === 'Aktif' ? 'Nonaktif' : 'Aktif';
        $user = $this->session->userdata('admin');
        $data = array(
            'status' => $status,
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s')
        );

        $this->db->trans_begin();
        $this->db->where('id', $id)->update('tagihan_jenis', $data);
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Ubah Status Jenis Tagihan',
            'modul' => 'Master Data',
            'aksi' => 'Ubah',
            'nama_tabel' => 'tagihan_jenis',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $row['kode_jenis'],
            'keterangan' => 'Status menjadi ' . $status,
            'data_sebelum' => json_encode($row, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => json_encode(array_merge($row, $data), JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Status jenis tagihan gagal diubah.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Status jenis tagihan berhasil diubah.');
    }
}
