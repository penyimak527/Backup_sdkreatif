<?php
class M_kas_bank extends CI_Model
{

    protected $id_user;
    protected $nama_user;
    function __construct()
    {
        parent::__construct();
        $this->id_user = $this->session->userdata('admin')['id_pegawai'];
        $this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
    }

    public function kas_bank_result()
    {
        $search = $this->input->post('search');
        // $tahun = date('Y');

        if ($search != null) {
            $this->db->like('k.keterangan', $search);
        }

        $this->db->select('k.*, s.nominal as saldo_awal, s.id AS id_saldo');
        $this->db->from('kasbank k');
        // $this->db->join('saldo_awal s', 's.id_kasbank = k.id AND s.tahun = ' . $tahun, 'left');
        $this->db->join('saldo_awal s', 's.id_kasbank = k.id', 'left');

        return $this->db->get()->result_array();
    }

    public function tambah()
    {
        $keterangan = $this->input->post('nama_kas');
        $kategori = $this->input->post('kategori');

        $data = [
            'keterangan' => $keterangan,
            'kategori' => $kategori,
        ];

        $response = $this->db->insert('kasbank', $data);

        return $response;
    }

   
    public function get_saldo()
    {
        $id = $this->input->post('id');
        return $this->db->where('id_kasbank', $id)->order_by('tahun', 'ASC')
        ->order_by('bulan', 'ASC')->get('saldo_awal')->result_array();
    }
   
    public function simpan_saldo_awal()
    {
        $this->db->trans_begin();
        $id_kasbank = $this->input->post('id_kasbank');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');

        $nominal = str_replace(',', '', $this->input->post('nominal'));
        // cek apakah periode sudah ada
        $cek = $this->db->get_where(
            'saldo_awal',
            [
                'id_kasbank' => $id_kasbank,
                // 'bulan' => $bulan,
                'tahun' => $tahun
            ]
        )->row();

        if ($cek) {
            $this->db->trans_rollback();
            return [
                'status' => false,
                'message' => 'Saldo periode ini sudah ada'
            ];
        }

        $data = [
            'id_kasbank' => $id_kasbank,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nominal' => $nominal,
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s')
        ];
        $this->db->insert('saldo_awal', $data);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return [
                'status' => false,
                'message' => 'Gagal menyimpan saldo'
            ];
        } else {
            $this->db->trans_commit();
            return [
                'status' => true,
                'message' => 'Saldo awal berhasil disimpan'
            ];
        }
    }

    public function edit()
    {
        $id_kasbank = $this->input->post('id_kasbank');
        $keterangan = $this->input->post('nama_kas');
        $kategori = $this->input->post('kategori');

        $data = [
            'keterangan' => $keterangan,
            'kategori' => $kategori,
        ];

        $response = $this->db->update('kasbank', $data, ['id' => $id_kasbank]);

        return $response;
    }
    public function hapus()
    {
        $id = $this->input->post('id');
        $response = $this->db->delete('kasbank', ['id' => $id]);
        return $response;
    }

}
?>