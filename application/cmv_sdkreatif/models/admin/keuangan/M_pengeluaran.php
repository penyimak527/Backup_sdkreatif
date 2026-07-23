<?php
class M_pengeluaran extends CI_Model
{
	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function pengeluaran_result()
	{
		$search = $this->input->post('search');
		$tanggal_dari = $this->input->post('tanggal_dari');
		$tanggal_sampai = $this->input->post('tanggal_sampai');
		$keterangan = $this->input->post('keterangan');
		$tanggal = $this->input->post('tanggal');
		$sort_tanggal = $this->input->post('sort_tanggal');
		$this->db->select('a.*, pg.nama_pegawai, ka.keterangan AS nama_keterangan, kb.keterangan AS nama_sumber_dana, kaf.keterangan AS nama_filter_kode_akun');
		$this->db->from('pengeluaran a');
		$this->db->join('pegawai pg', 'a.id_pegawai = pg.id', 'left');
		$this->db->join('kode_akun ka', 'a.id_kode_akun = ka.id', 'left');
		$this->db->join('kasbank kb', 'a.sumber_dana = kb.id', 'left');
		$this->db->join('kode_akun kaf', 'a.filter_kode_akun = kaf.id', 'left');
		if ($search != null) {
			$this->db->where('a.id_kode_akun', $search);
		}
		if ($keterangan != null) {
			$this->db->like('a.keterangan', $keterangan);
		}
		if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
            $this->db->where("STR_TO_DATE(a.tanggal_input, '%d-%m-%Y') BETWEEN STR_TO_DATE('$tanggal_dari', '%d-%m-%Y') AND STR_TO_DATE('$tanggal_sampai', '%d-%m-%Y')", null, false);
        }
if (empty($sort_tanggal)) {
    $sort_tanggal = 'DESC';
}
$this->db->order_by("STR_TO_DATE(a.tanggal_input, '%d-%m-%Y')",$sort_tanggal,false);
		$pengeluaran = $this->db->get()->result_array();
		return $pengeluaran;
	}

	public function tambah()
	{
		$id_pegawai = $this->session->userdata('admin')['id_pegawai'];
		$id_kode_akun = $this->input->post('id_kode_akun');
		$tanggal_input = $this->input->post('tanggal');
		if ($tanggal_input == '') {
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
		$sumber_dana = $this->input->post('id_sumber_dana');
		$nominal = str_replace(',', '', $this->input->post('nominal', TRUE));
		$filter_kode_akun = $this->input->post('filter_kode_akun');
		$tanggal = date('d-m-Y');
		$this->load->library('upload');
		$src_media = null;
		// if ($_FILES['bukti']['name'] != '') {
		if (isset($_FILES['bukti']) && !empty($_FILES['bukti']['name'])) {
			// upload gambar
			$config['upload_path'] = "./storage/bukti/pengeluaran";
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['encrypt_name'] = true;
			$this->upload->initialize($config);

			if (!$this->upload->do_upload('bukti')) {
	// 			 return [
    //     'status' => false,
    //     'message' => strip_tags($this->upload->display_errors()),
    //     'code' => 401
    // ];
				$errors = array('error' => $this->upload->display_errors());
				$src_media = '';

			} else {
				$media = $this->upload->data()['file_name'];
				$src_media = "storage/bukti/pengeluaran/" . $media;
			}
		}

		$data = [
			'id_pegawai' => $id_pegawai,
			'id_kode_akun' => $id_kode_akun,
			'tanggal_input' => $tanggal_input,
			'bulan' => $bulan,
			'tahun' => $tahun,
			'sumber_dana' => $sumber_dana,
			'jumlah' => $nominal,
			'keterangan' => $keterangan,
			'bukti' => $src_media,
			'tanggal' => $tanggal,
			'filter_kode_akun' => $filter_kode_akun,
		];

		$this->db->trans_begin();
		$this->db->insert('pengeluaran', $data);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$response = array(
				'status' => false,
				'message' => 'Gagal menyimpan data',
				'code' => 401
			);
		} else {
			$this->db->trans_commit();
			$response = array(
				'status' => true,
				'message' => 'Data berhasil disimpan',
				'code' => 200
			);
		}
		return $response;
	}

	public function edit()
	{
		$id_pegawai = $this->session->userdata('admin')['id_pegawai'];
		$id_pengeluaran = $this->input->post('id_pengeluaran');
		$id_kode_akun = $this->input->post('id_kode_akun');
		$tanggal_input = $this->input->post('tanggal');
		$filter_kode_akun = $this->input->post('filter_kode_akun');
		if ($tanggal_input == '') {
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
		$sumber_dana = $this->input->post('id_sumber_dana');
		$nominal = str_replace(',', '', $this->input->post('nominal', TRUE));
		$tanggal = date('d-m-Y');
		$hapus_gambar = $this->input->post('hapus_gambar');
		$pengeluaran = $this->db->get_where('pengeluaran', ['id' => $id_pengeluaran])->row_array();
		$old_image = $pengeluaran['bukti'];
		// $old_image = $this->input->post('oldImage');


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

			$config['upload_path'] = "./storage/bukti/pengeluaran";
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['encrypt_name'] = true;

			$this->upload->initialize($config);

			if ($this->upload->do_upload('bukti')) {
				$media = $this->upload->data('file_name');
				$src_media = "storage/bukti/pengeluaran/" . $media;

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
			'id_pegawai' => $id_pegawai,
			'id_kode_akun' => $id_kode_akun,
			'tanggal_input' => $tanggal_input,
			'bulan' => $bulan,
			'tahun' => $tahun,
			'sumber_dana' => $sumber_dana,
			'jumlah' => $nominal,
			'keterangan' => $keterangan,
			'bukti' => $src_media,
			'tanggal' => $tanggal,
			'filter_kode_akun' => $filter_kode_akun,
		];
		$this->db->trans_begin();
		$this->db->where('id', $id_pengeluaran);
		$this->db->update('pengeluaran', $data);
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
		$id = $this->input->post('id');
		$image = $this->db->get_where('pengeluaran', ['id' => $id])->row_array();
		if ($image['bukti'] !== 'default.jpg' && $image['bukti'] !== null && $image['bukti'] != '') {
			unlink('./' . $image['bukti']);
		}
		$response = $this->db->delete('pengeluaran', ['id' => $id]);
		return $response;
	}
}