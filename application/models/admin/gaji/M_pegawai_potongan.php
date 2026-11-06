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

        if ($id_pegawai != null) {
            $this->db->or_where('p.id', $id_pegawai);
        }

        return $this->db->get()->result_array();
    }

    public function tambah()
    {
        $id_pegawai = $this->input->post('id_pegawai');
        $bulan = $this->input->post('bulan_potongan');
        $tahun = $this->input->post('tahun_potongan');

        $id_master_potongan = $this->input->post('id_master_potongan');
        $nominal = $this->input->post('nominal');
        $keterangan_potongan = $this->input->post('keterangan_potongan');

        $validasi = $this->validasi_detail_potongan(
            $id_master_potongan,
            $nominal,
            $keterangan_potongan
        );

        if ($validasi['status'] === false) {
            return $validasi;
        }

        $tanggal = date('d-m-Y');

        $header = [
            'id_pegawai' => $id_pegawai,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal
        ];

        $this->db->trans_begin();

        $this->db->insert('pegawai_potongan', $header);
        $id_header = $this->db->insert_id();

        foreach ($id_master_potongan as $key => $potongan) {
            $detail = [
                'id_pegawai_potongan' => $id_header,
                'id_master_potongan' => $potongan,
                'keterangan_potongan' => $this->ambil_keterangan_potongan(
                    $potongan,
                    $keterangan_potongan[$key] ?? ''
                ),
                'nominal' => (int) str_replace(',', '', $nominal[$key] ?? 0)
            ];

            $this->db->insert('pegawai_potongan_detail', $detail);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menyimpan data'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'code' => 200,
            'message' => 'Data berhasil disimpan'
        ];
    }

    public function edit()
    {
        $id_potongan_pegawai = $this->input->post('id_potong_pegawai');
        $id_pegawai = $this->input->post('id_pegawai');
        $bulan = $this->input->post('bulan_potongan');
        $tahun = $this->input->post('tahun_potongan');

        $id_master_potongan = $this->input->post('id_master_potongan');
        $nominal = $this->input->post('nominal');
        $keterangan_potongan = $this->input->post('keterangan_potongan');

        $validasi = $this->validasi_detail_potongan(
            $id_master_potongan,
            $nominal,
            $keterangan_potongan
        );

        if ($validasi['status'] === false) {
            return $validasi;
        }

        $data_header = [
            'id_pegawai' => $id_pegawai,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => date('d-m-Y')
        ];

        $this->db->trans_begin();

        $this->db
            ->where('id', $id_potongan_pegawai)
            ->update('pegawai_potongan', $data_header);

        $this->db->delete(
            'pegawai_potongan_detail',
            ['id_pegawai_potongan' => $id_potongan_pegawai]
        );

        foreach ($id_master_potongan as $key => $potongan) {
            $detail = [
                'id_pegawai_potongan' => $id_potongan_pegawai,
                'id_master_potongan' => $potongan,
                'keterangan_potongan' => $this->ambil_keterangan_potongan(
                    $potongan,
                    $keterangan_potongan[$key] ?? ''
                ),
                'nominal' => (int) str_replace(',', '', $nominal[$key] ?? 0)
            ];

            $this->db->insert('pegawai_potongan_detail', $detail);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'code' => 401,
                'message' => 'Gagal mengupdate data'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'code' => 200,
            'message' => 'Data berhasil diupdate'
        ];
    }

    private function validasi_detail_potongan($id_master_potongan, $nominal, $keterangan_potongan)
    {
        if (!is_array($id_master_potongan) || count($id_master_potongan) === 0) {
            return [
                'status' => false,
                'code' => 401,
                'message' => 'Tambahkan minimal satu potongan'
            ];
        }

        $id_lain_lain = $this->get_id_master_lain_lain();

        foreach ($id_master_potongan as $key => $potongan) {
            if (empty($potongan)) {
                return [
                    'status' => false,
                    'code' => 401,
                    'message' => 'Jenis potongan wajib dipilih'
                ];
            }

            $nominal_bersih = (int) str_replace(',', '', $nominal[$key] ?? 0);

            if ($nominal_bersih <= 0) {
                return [
                    'status' => false,
                    'code' => 401,
                    'message' => 'Nominal potongan wajib diisi'
                ];
            }

            if (
                $id_lain_lain !== null &&
                (string) $potongan === (string) $id_lain_lain &&
                trim((string) ($keterangan_potongan[$key] ?? '')) === ''
            ) {
                return [
                    'status' => false,
                    'code' => 401,
                    'message' => 'Keterangan potongan wajib diisi jika memilih Lain-lain'
                ];
            }
        }

        return [
            'status' => true,
            'code' => 200,
            'message' => 'Valid'
        ];
    }

 private function get_id_master_lain_lain()
{
    $data = $this->db
        ->select('id')
        ->from('master_potongan')
        ->where('LOWER(TRIM(nama_potongan))', 'lain-lain')
        ->get()
        ->row_array();

    return $data ? $data['id'] : null;
}

    private function ambil_keterangan_potongan($id_master_potongan, $keterangan)
    {
        $id_lain_lain = $this->get_id_master_lain_lain();

        if (
            $id_lain_lain !== null &&
            (string) $id_master_potongan === (string) $id_lain_lain
        ) {
            return trim((string) $keterangan);
        }

        return null;
    }

    public function hapus()
    {
        $id_potongan_pegawai = $this->input->post('id');

        $this->db->trans_begin();

        $this->db->delete(
            'pegawai_potongan_detail',
            ['id_pegawai_potongan' => $id_potongan_pegawai]
        );

        $this->db->delete(
            'pegawai_potongan',
            ['id' => $id_potongan_pegawai]
        );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menghapus data'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'code' => 200,
            'message' => 'Data berhasil dihapus'
        ];
    }
}
