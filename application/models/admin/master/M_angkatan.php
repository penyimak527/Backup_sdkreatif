<?php
class M_angkatan extends CI_Model
{
	protected $id_user;
	protected $nama_user;

	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}



	public function angkatan_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			// $this->db->like('nama_angkatan', $search);
			  $this->db->group_start();
        $this->db->like('tahun_awal', $search);
        $this->db->or_like('tahun_akhir', $search);
		$this->db->group_end();
		}
		$this->db->order_by('tahun_awal', 'ASC');
		$tahun = $this->db->get('master_angkatan')->result_array();
		return $tahun;
	}

	public function tambah()
	{
		$tahun_awal = $this->input->post('rentang_tahun_awal');
		$tahun_akhir = $this->input->post('rentang_tahun_akhir');
		if ($tahun_awal >= $tahun_akhir) {
			return false;
		}
		$kenaikan_gaji = str_replace(',', '', $this->input->post('kenaikan_gaji', TRUE));
		$angkatan = $this->db
			->select_max('angkatan')
			->get('master_angkatan')
			->row_array();

		$angkatan_baru = ($angkatan['angkatan'] ?? 0) + 1;
		$data = [
			'tahun_awal' => $tahun_awal,
			'tahun_akhir' => $tahun_akhir,
			'kenaikan_gaji' => $kenaikan_gaji,
			'angkatan' => $angkatan_baru
		];

		$response = $this->db->insert('master_angkatan', $data);
		if ($response) {
			$this->updateNomorAngkatan();
			// $this->sinkronAngkatanPegawai();
		}
		return $response;
	}
	public function edit()
	{
		$id_angkatan = $this->input->post('id_angkatan');
		$tahun_awal = $this->input->post('rentang_tahun_awal');
		$tahun_akhir = $this->input->post('rentang_tahun_akhir');
		$kenaikan_gaji = str_replace(',', '', $this->input->post('kenaikan_gaji', TRUE));

		$data = [
			'tahun_awal' => $tahun_awal,
			'tahun_akhir' => $tahun_akhir,
			'kenaikan_gaji' => $kenaikan_gaji,
		];
		$cek = $this->db->get_where('master_angkatan', ['id' => $id_angkatan])->row_array();
		// if ($cek['tahun_awal'] != $tahun_awal || $cek['tahun_akhir'] != $tahun_akhir) {
		// 	$response = $this->db->update('master_angkatan', $data, ['id' => $id_angkatan]);
		// 	if ($response) {
			// 		$this->sinkronAngkatanPegawai();
			// 	}
			// }else{}
			$response = $this->db->update('master_angkatan', $data, ['id' => $id_angkatan]);
			if($response){
				$this->updateNomorAngkatan();
			}
		
		return $response;
	}
	private function updateNomorAngkatan()
	{
		$data = $this->db->order_by('tahun_awal', 'ASC')->get('master_angkatan')->result_array();
		$no = 1;
		foreach ($data as $row) {
			$this->db->update('master_angkatan',['angkatan' => $no],['id' => $row['id']]);
			$no++;
		}
	}

	public function hapus()
    {
        $id = $this->input->post('id');
		$cek_angkatan = $this->db->get_where('master_angkatan', ['id' => $id])->row_array();
		$cek = $this->db->where('angkatan', $cek_angkatan['angkatan'])->count_all_results('pegawai');

        $this->db->trans_begin();
		if ($cek > 0) {
			return array(
				'status'	=> false,
				'message'	=> 'Angkatan sudah digunakan pada pegawai',
				'code'		=> 401);
		}
		$this->db->delete('master_angkatan', ['id' => $id]);
		$this->updateNomorAngkatan();
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
				'message' => 'Data gagal dihapus'
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
?>