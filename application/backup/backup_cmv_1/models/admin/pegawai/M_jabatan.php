<?php
class M_jabatan extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function jabatan_result()
	{

		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('jabatan', $search);
		}

		$jabatan = $this->db->get('jabatan')->result_array();
		return $jabatan;
	}


	public function tambah()
	{


		$jabatan = $this->input->post('jabatan');
		$data = [

			'jabatan' => $jabatan,

		];

		$response = $this->db->insert('jabatan', $data);

		return $response;
	}
	public function edit()
	{

		$id_jabatan = $this->input->post('id_jabatan');
		$jabatan = $this->input->post('jabatan');
		$data = [

			'jabatan' => $jabatan,

		];

		$response = $this->db->update('jabatan', $data, ['id' => $id_jabatan]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('jabatan', ['id' => $id]);

		return $response;
	}



}
?>
