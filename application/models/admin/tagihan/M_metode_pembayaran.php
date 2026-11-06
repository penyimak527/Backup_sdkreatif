<?php defined('BASEPATH') OR exit('No direct script access allowed');

class M_metode_pembayaran extends CI_Model
{
    public function listing()
    {
        $search = trim((string)$this->input->post('search', true));
        $status = trim((string)$this->input->post('status', true));

        $this->db->from('tagihan_metode_pembayaran');
        if ($search !== '') {
            $this->db->group_start()
                ->like('nama_metode', $search)
                ->group_end();
        }
        if ($status !== '') {
            $this->db->where('status', $status);
        }

        return $this->db->order_by('urutan', 'ASC')->order_by('id', 'ASC')->get()->result_array();
    }

    public function save($p, $actor)
    {
        $id = (int)($p['id'] ?? 0);
        $nama = trim((string)($p['nama_metode'] ?? ''));
        $status = (string)($p['status'] ?? 'Aktif');

        if ($nama === '') return array('result'=>'false','message'=>'Nama metode wajib diisi.');
        if (!in_array($status, array('Aktif','Nonaktif'), true)) return array('result'=>'false','message'=>'Status tidak valid.');

        $before = $id > 0 ? $this->db->where('id', $id)->get('tagihan_metode_pembayaran')->row_array() : null;
        if ($id > 0 && !$before) return array('result'=>'false','message'=>'Metode pembayaran tidak ditemukan.');

        if ($this->db->where('nama_metode', $nama)->where('id !=', $id)->count_all_results('tagihan_metode_pembayaran') > 0) {
            return array('result'=>'false','message'=>'Nama metode pembayaran sudah digunakan.');
        }

        $user = $this->session->userdata('admin');
        $data = array(
            'nama_metode' => $nama,
            'butuh_uang_diterima' => 'Ya', // Kolom lama dipertahankan untuk kompatibilitas; semua metode menggunakan input uang diterima.
            'status' => $status,
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => (int)$actor,
            'nama_user' => (string)($user['nama_lengkap'] ?? $user['nama'] ?? $user['username'] ?? '')
        );

        // Kolom ini dipertahankan untuk kompatibilitas database SD Kreatif,
        // tetapi tidak menjadi field yang diatur pengguna (mengikuti konsep Almahbaro).
        if ($id > 0) {
            $data['kode_metode'] = (string)($before['kode_metode'] ?? $this->generate_code($nama, $id));
            // Jangan ubah kategori metode lama hanya karena seluruh metode kini memakai input Uang Diterima.
            $data['jenis_metode'] = (string)($before['jenis_metode'] ?? 'Lainnya');
            $data['urutan'] = (int)($before['urutan'] ?? 0);
        } else {
            $data['kode_metode'] = $this->generate_code($nama, 0);
            // Kategori internal ditentukan dari nama metode, bukan dari kebutuhan input uang.
            $data['jenis_metode'] = preg_match('/(?:^|[^a-z])(tunai|cash)(?:$|[^a-z])/i', $nama)
                ? 'Tunai'
                : (preg_match('/(?:^|[^a-z])(transfer|bank)(?:$|[^a-z])/i', $nama) ? 'Transfer' : 'Lainnya');
            $max = $this->db->select_max('urutan', 'max_urutan')->get('tagihan_metode_pembayaran')->row_array();
            $data['urutan'] = ((int)($max['max_urutan'] ?? 0)) + 1;
        }

        $this->db->trans_begin();
        if ($id > 0) $this->db->where('id', $id)->update('tagihan_metode_pembayaran', $data);
        else $this->db->insert('tagihan_metode_pembayaran', $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result'=>'false','message'=>'Metode pembayaran gagal disimpan.');
        }
        $this->db->trans_commit();
        return array('result'=>'true','message'=>'Metode pembayaran berhasil disimpan.');
    }

    public function toggle_status($id)
    {
        $row = $this->db->where('id', (int)$id)->get('tagihan_metode_pembayaran')->row_array();
        if (!$row) return array('result'=>'false','message'=>'Metode pembayaran tidak ditemukan.');

        $status = $row['status'] === 'Aktif' ? 'Nonaktif' : 'Aktif';
        $this->db->where('id', (int)$id)->update('tagihan_metode_pembayaran', array(
            'status'=>$status,
            'tanggal'=>date('d-m-Y'),
            'waktu'=>date('H:i:s')
        ));
        return array('result'=>'true','message'=>'Status metode pembayaran berhasil diubah.');
    }

    private function generate_code($nama, $excludeId)
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $nama));
        $base = substr($base, 0, 32);
        if ($base === '') $base = 'METODE';
        $candidate = $base;
        $n = 2;
        while (true) {
            $this->db->where('kode_metode', $candidate);
            if ((int)$excludeId > 0) $this->db->where('id !=', (int)$excludeId);
            if ($this->db->count_all_results('tagihan_metode_pembayaran') === 0) return $candidate;
            $candidate = substr($base, 0, 27) . '-' . $n;
            $n++;
        }
    }
}
