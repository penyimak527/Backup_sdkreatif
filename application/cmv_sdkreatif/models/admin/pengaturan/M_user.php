<?php
class M_user extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function user_result()
	{

		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->like('nama_user', $search);
		}

		$user = $this->db->get('user')->result_array();
		return $user;
	}
	public function user_edit()
	{

		$id_user = $this->input->post('id_user');



		$user = $this->db->get_where('user', ['id' => $id_user])->row_array();
		return $user;
	}


	public function tambah()
	{


		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$id_pegawai = $this->input->post('id_pegawai');
		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
		$level = $this->db->get_where('level', ['id' => $this->input->post('id_level')])->row_array();
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$data = [

			'nama_user' => $pegawai['nama_pegawai'],
			'username' => $username,
			'password' => $hashed_password,
			'password_text' => $password,
			'level' => $level['level'],
			'id_level' => $level['id'],
			'id_pegawai' => $id_pegawai,
		];

		$response = $this->db->insert('user', $data);

		return $response;
	}
	public function edit()
	{

		$id_user = $this->input->post('id_user');
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$id_pegawai = $this->input->post('id_pegawai');

		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
		$level = $this->db->get_where('level', ['id' => $this->input->post('id_level')])->row_array();
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		if ($password != null) {
			$data = [

				'nama_user' => $pegawai['nama_pegawai'],
				'username' => $username,
				'password' => $hashed_password,
				'password_text' => $password,
				'level' => $level['level'],
				'id_level' => $level['id'],
				'id_pegawai' => $id_pegawai,
			];
		} else {
			$data = [
				'nama_user' => $pegawai['nama_pegawai'],
				'username' => $username,
				'level' => $level['level'],
				'id_level' => $level['id'],
				'id_pegawai' => $id_pegawai,
			];
		}

		$response = $this->db->update('user', $data, ['id' => $id_user]);

		return $response;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('user', ['id' => $id]);

		return $response;
	}



}
?>
