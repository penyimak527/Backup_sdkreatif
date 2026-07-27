<?php
class M_login extends CI_Model
{

	public function login($username, $password)
	{
		$this->db->where('username', $username);
		$query = $this->db->get('user');

		if ($query->num_rows() > 0) {
			$row = $query->row_array();


			if (password_verify($password, $row['password'])) {
				$user_data = array(
					'id_user' => $row['id'],
					'id_pegawai' => $row['id_pegawai'],
					'level' => $row['level'],
					'id_level' => $row['id_level'],
					'username' => $row['username'],
					'nama_lengkap' => $row['nama_user'],
					'logged_in' => true,
				);
				return $user_data;
			}
		}

		return false;
	}



}
?>
