<?php
class M_guru extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function guru_result()
	{
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('nama_guru', $search);
		}
		$guru = $this->db->get('guru')->result_array();

		$data_guru = [];

		foreach ($guru as $g) {
			$this->db->select('mapel');
			$mapel = $this->db->get_where('guru_mapel', ['id_guru' => $g['id']])->result_array();
			$data_guru[] = [
				'id' => $g['id'],
				'nama_guru' => $g['nama_guru'],
				'nbm' => $g['nbm'],
				'jk' => $g['jk'],
				'tempat_lahir' => $g['tempat_lahir'],
				'tanggal_lahir' => $g['tanggal_lahir'],
				'no_telp' => $g['no_telp'],
				'mapel' => $mapel
			];
		}

		return $data_guru;
	}

	public function tambah()
	{
		$nama_guru = $this->input->post('nama_guru');
		$nbm = $this->input->post('nbm');
		$jk = $this->input->post('jk');
		$tempat_lahir = $this->input->post('tempat_lahir');
		$tanggal_lahir = $this->input->post('tanggal_lahir');
		$no_telp = $this->input->post('no_telp');

		$data = [
			'nama_guru' => $nama_guru,
			'nbm' => $nbm,
			'jk' => $jk,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'no_telp' => $no_telp,
		];

		$response = $this->db->insert('guru', $data);

		return $response;
	}
	public function edit()
	{
		$id_guru = $this->input->post('id');
		$nama_guru = $this->input->post('nama_guru');
		$nbm = $this->input->post('nbm');
		$jk = $this->input->post('jk');
		$tempat_lahir = $this->input->post('tempat_lahir');
		$tanggal_lahir = $this->input->post('tanggal_lahir');
		$no_telp = $this->input->post('no_telp');

		$data = [
			'nama_guru' => $nama_guru,
			'nbm' => $nbm,
			'jk' => $jk,
			'tempat_lahir' => $tempat_lahir,
			'tanggal_lahir' => $tanggal_lahir,
			'no_telp' => $no_telp,
		];

		$response = $this->db->update('guru', $data, ['id' => $id_guru]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('guru', ['id' => $id]);

		return $response;
	}

}
?>
