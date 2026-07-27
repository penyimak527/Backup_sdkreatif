<?php
class M_pemasukan extends CI_Model
{

	protected $id_user;
	protected $nama_user;
	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}


	public function pemasukan_result()
	{
		$search = $this->input->post('search');
		$keterangan = $this->input->post('keterangan');
		$tanggal_dari = $this->input->post('tanggal_dari');
		$tanggal_sampai = $this->input->post('tanggal_sampai');
		$sort_tanggal = $this->input->post('sort_tanggal');

        $this->db->select('a.*, pg.nama_pegawai, ka.keterangan AS nama_keterangan, kb.keterangan AS nama_kas');
        $this->db->from('pemasukan a');
		$this->db->join('pegawai pg', 'a.id_pegawai = pg.id', 'left');
        $this->db->join('kode_akun ka', 'a.id_kode_akun = ka.id', 'left');
        $this->db->join('kasbank kb', 'a.simpan_ke = kb.id', 'left');
		if ($search != null) {
			$this->db->where('a.id_kode_akun', $search);
		}
		if ($keterangan != null) {
			$this->db->like('a.keterangan', $keterangan);
		}
		if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
            $this->db->where("STR_TO_DATE(a.tanggal_input, '%d-%m-%Y') BETWEEN STR_TO_DATE('$tanggal_dari', '%d-%m-%Y') AND STR_TO_DATE('$tanggal_sampai', '%d-%m-%Y')", null, false);
        }
		// $this->db->order_by('a.id', 'DESC');
		if (empty($sort_tanggal)) {
    $sort_tanggal = 'DESC';
}
$this->db->order_by("STR_TO_DATE(a.tanggal_input, '%d-%m-%Y')",$sort_tanggal,false);
		$pemasukan = $this->db->get()->result_array();
		return $pemasukan;
	}

	public function tambah()
	{
		$id_pegawai = $this->session->userdata('admin')['id_pegawai'];
		$id_kode_akun = $this->input->post('id_kode_akun');
		$tanggal_input	= $this->input->post('tanggal');
		if($tanggal_input == ''){
			return array(
				'status' => false,
				'code' => 401,
				'message' => 'Tanggal belum diisi'
			);
		}
		$pecah = explode('-', $tanggal_input);
		$bulan = $pecah[1]; 
		$tahun = $pecah[2]; 
		$keterangan = $this->input->post('keterangan');
		$simpan_ke = $this->input->post('id_simpan_ke');
		$nominal = str_replace(',', '', $this->input->post('nominal', TRUE));
		$tanggal = date('d-m-Y');
		$this->load->library('upload');
		$src_media = null;
		// if ($_FILES['bukti']['name'] != '') {
		if (isset($_FILES['bukti']) && !empty($_FILES['bukti']['name'])) {
			// upload gambar
			$config['upload_path'] = "./storage/bukti/pemasukan";
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['encrypt_name'] = true;
			$this->upload->initialize($config);

			if (!$this->upload->do_upload('bukti')) {
				$erros = array('error' => $this->upload->display_errors());
				$src_media = '';
			} else {
				$media = $this->upload->data()['file_name'];
				$src_media = "storage/bukti/pemasukan/" . $media;
			}
		}

		$data = [
			'id_kode_akun' => $id_kode_akun,
			'id_pegawai'	=> $id_pegawai,
			'tanggal_input'	=> $tanggal_input,
			'bulan'		=> $bulan,
			'tahun'		=> $tahun,
			'keterangan' => $keterangan,
            'simpan_ke' => $simpan_ke,
			'jumlah' => $nominal,
			'bukti' => $src_media,
			'tanggal' => $tanggal,
		];

		$this->db->trans_begin();
		$this->db->insert('pemasukan', $data);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$response = array(
				'status' => false,
				'code' => 401,
			);
		} else {
			$this->db->trans_commit();
			$response = array(
				'status' => true,
				'code' => 200,
			);
		}
		return $response;
	}
	public function edit()
	{
		$id_pegawai = $this->session->userdata('admin')['id_pegawai'];
        $id_pemasukan = $this->input->post('id_pemasukan');
		$tanggal_input = $this->input->post('tanggal');
		if($tanggal_input == ''){
			return array(
				'status' => false,
				'code' => 401,
				'message' => 'Tanggal belum diisi'
			);
		}
		$pecah = explode('-', $tanggal_input);
		$bulan = $pecah[1]; 
		$tahun = $pecah[2]; 
		$id_kode_akun = $this->input->post('id_kode_akun');
		$keterangan = $this->input->post('keterangan');
		$simpan_ke = $this->input->post('id_simpan_ke');
		$nominal = str_replace(',', '', $this->input->post('nominal', TRUE));
		$tanggal = date('d-m-Y');
		$hapus_gambar = $this->input->post('hapus_gambar');
		$old_image = $this->input->post('oldImage');

		$this->load->library('upload');

		$src_media = $old_image; 
		if ($hapus_gambar == '1') {
		    if ($old_image && file_exists('./' . $old_image)) {
		        unlink('./' . $old_image);
		    }
		    $src_media = '';
		}

		// Jika upload foto baru
		if (!empty($_FILES['bukti']['name'])) {

			$config['upload_path'] = "./storage/bukti/pemasukan";
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['encrypt_name'] = true;

			$this->upload->initialize($config);

			if ($this->upload->do_upload('bukti')) {

				$media = $this->upload->data('file_name');

				$src_media = "storage/bukti/pemasukan/" . $media;

				// Hapus foto lama
				if ($old_image && file_exists('./' . $old_image)) {
					unlink('./' . $old_image);
				}

			} else {
				echo json_encode([
					'status' => false,
					'message' => $this->upload->display_errors()
				]);
				return;
			}
		}

		$data = [
            'id_kode_akun' => $id_kode_akun,
			'id_pegawai'	=> $id_pegawai,
			'tanggal_input'	=> $tanggal_input,
			'bulan'	=> $bulan,
			'tahun'	=> $tahun,
			'keterangan' => $keterangan,
            'simpan_ke' => $simpan_ke,
			'jumlah' => $nominal,
			'bukti' => $src_media,
			'tanggal' => $tanggal,
		];
		$this->db->trans_begin();
		$this->db->where('id', $id_pemasukan);
		$this->db->update('pemasukan', $data);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$response = array(
				'status' => false,
				'code' => 401,
			);
		} else {
			$this->db->trans_commit();
			$response = array(
				'status' => true,
				'code' => 200,
			);
		}
		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');
        $image = $this->db->get_where('pemasukan', ['id' => $id])->row_array();
		if ($image['bukti'] !== 'default.jpg' && $image['bukti'] !== null && $image['bukti'] != '') {
			unlink('./' . $image['bukti']);
		}
		$response = $this->db->delete('pemasukan', ['id' => $id]);
		return $response;
	}

}
?>