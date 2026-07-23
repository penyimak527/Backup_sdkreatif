<?php
class M_level extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function level_result()
	{

		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('level', $search);
		}

		$level = $this->db->get('level')->result_array();
		return $level;
	}


	public function tambah()
	{


		$level = $this->input->post('level');
		$data = [

			'level' => $level,

		];

		$response = $this->db->insert('level', $data);

		return $response;
	}
	public function edit()
	{

		$id_level = $this->input->post('id_level');
		$level = $this->input->post('level');
		$data = [

			'level' => $level,

		];

		$response = $this->db->update('level', $data, ['id' => $id_level]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');

		$this->db->delete('conf_list_menu', ['id_level' => $id]);
		$response = $this->db->delete('level', ['id' => $id]);

		return $response;
	}



}
?>