<?php
class M_hak_akses extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function hak_akses_result()
	{

		$search = $this->input->post('search');


		$level = $this->db->get_where('conf_list_menu', ['id_level' => $search])->result_array();
		return $level;
	}

	public function pilih_menu_result()
	{
		$id_level = $this->input->post('id_level');
		$search = $this->input->post('search');

		$where_search = "";
		if (!empty($search)) {
			$search = $this->db->escape_like_str($search); // hindari SQL injection
			$where_search = "AND conf_menu.name LIKE '%$search%'";
		}

		$query = "
        SELECT * FROM conf_menu
        WHERE id NOT IN (
            SELECT id_menu FROM conf_list_menu WHERE id_level = ?
        )
        $where_search
    ";

		$level = $this->db->query($query, array($id_level))->result_array();
		return $level;
	}



	public function tambah()
	{


		$id_menu = $this->input->post('id_menu');
		$id_level = $this->input->post('id_level');

		$data_menu = [];
		foreach ($id_menu as $key => $value) {
			$menu = $this->db->get_where('conf_menu', ['id' => $value])->row_array();
			$data_menu[] = [
				'path' => $menu['path'],
				'name' => $menu['name'],
				'group' => $menu['group'],
				'id_level' => $id_level,
				'id_menu' => $menu['id'],
			];
		}

		$response = $this->db->insert_batch('conf_list_menu', $data_menu);
		if ($response) {
			$data = [
				'status' => true
			];
		}
		return $data;
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
		$id_hak_akses = $this->input->post('id_menu');


		$this->db->where_in('id', $id_hak_akses);
		$response = $this->db->delete('conf_list_menu');
		if ($response) {
			$data = [
				'status' => true
			];
		}
		return $data;
	}



}
?>