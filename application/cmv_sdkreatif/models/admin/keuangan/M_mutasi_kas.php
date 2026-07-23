<?php
class M_mutasi_kas extends CI_Model
{

    protected $id_user;
    protected $nama_user;
    function __construct()
    {
        parent::__construct();
        $this->id_user = $this->session->userdata('admin')['id_pegawai'];
        $this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
    }


    public function mutasi_kas_result()
    {
        $search = $this->input->post('search');
        $tanggal = $this->input->post('tanggal');
        $sort_tanggal = $this->input->post('sort_tanggal');

        $this->db->select('a.*, pg.nama_pegawai, kb_masuk.keterangan AS nama_kas_masuk, kb_keluar.keterangan AS nama_kas_keluar');
        $this->db->from('mutasi_kas a');
        $this->db->join('pegawai pg', 'a.id_pegawai = pg.id', 'left');
        $this->db->join('kasbank kb_masuk', 'a.kas_masuk = kb_masuk.id', 'left');
        $this->db->join('kasbank kb_keluar', 'a.kas_keluar = kb_keluar.id', 'left');
        if ($search != null) {
            $this->db->like('keterangan', $search);
        }
        if (!empty($tanggal)) {
            $this->db->where("a.tanggal_input", $tanggal);
        }
        if (empty($sort_tanggal)) {
    $sort_tanggal = 'DESC';
}
$this->db->order_by("STR_TO_DATE(a.tanggal_input, '%d-%m-%Y')",$sort_tanggal,false);
        $mutasi_kas = $this->db->get()->result_array();
        return $mutasi_kas;
    }


    public function tambah()
    {
        $id_pegawai = $this->session->userdata('admin')['id_pegawai'];
        $tanggal_input = $this->input->post('tanggal');
		if($tanggal_input == ''){
			return array(
				'status' => false,
				'code' => 401,
				'message' => 'Tanggal belum diisi'
			);
		}
		$pecah = explode('-', $tanggal_input);
		$bulan = $pecah[1]; // 05
		$tahun = $pecah[2]; // 2026
        $keterangan = $this->input->post('keterangan');
        $kas_masuk = $this->input->post('kas_masuk');
        $kas_keluar = $this->input->post('kas_keluar');
        $nominal = (int) str_replace(',', '', $this->input->post('nominal'));
        $tanggal = date('d-m-Y');
        $waktu = date('H:i:s');
        $data = [
            'id_pegawai'    => $id_pegawai,
            'tanggal_input' => $tanggal_input,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'keterangan' => $keterangan,
            'tanggal' => $tanggal,
            'kas_masuk' => $kas_masuk,
            'kas_keluar' => $kas_keluar,
            'nominal' => $nominal,
            'waktu' => $waktu
        ];
        if ($kas_masuk == $kas_keluar) {
            return array(
                'status' => false,
                'message' => 'Kas masuk dan keluar tidak boleh sama'
            );
        }
        $this->db->trans_begin();
      
        $this->db->insert('mutasi_kas', $data);
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
        $id_pegawai = $this->session->userdata('admin')['id_pegawai'];
        $tanggal_input = $this->input->post('tanggal');
		if($tanggal_input == ''){
			return array(
				'status' => false,
				'code' => 401,
				'message' => 'Tanggal belum diisi'
			);
		}
		$pecah = explode('-', $tanggal_input);
		$bulan = $pecah[1]; // 05
		$tahun = $pecah[2]; // 2026
        $id_mutasi_kas = $this->input->post('id_mutasi_kas');
        $keterangan = $this->input->post('keterangan');
        $kas_masuk = $this->input->post('kas_masuk');
        $kas_keluar = $this->input->post('kas_keluar');
        $nominal = (int) str_replace(',', '', $this->input->post('nominal'));
        $tanggal = date('d-m-Y');
        $waktu = date('H:i:s');


        if ($kas_masuk == $kas_keluar) {
            return array(
                'status' => false,
                'message' => 'Kas masuk dan keluar tidak boleh sama'
            );
        }
        $data = [
            'id_pegawai'    => $id_pegawai,
            'tanggal_input' => $tanggal_input,
            'bulan' => $bulan,
            'tahun' => $tahun, 
            'keterangan' => $keterangan,
            'kas_masuk' => $kas_masuk,
            'kas_keluar' => $kas_keluar,
            'nominal' => $nominal,
            'tanggal'   => $tanggal,
            'waktu' => $waktu
        ];

        $this->db->trans_begin();
        
        $this->db->where('id', $id_mutasi_kas);
        $this->db->update('mutasi_kas', $data);

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
        $id_mutasi_kas = $this->input->post('id');

        $this->db->delete('mutasi_kas', ['id' => $id_mutasi_kas]);
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
?>