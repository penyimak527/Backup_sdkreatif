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
				$nama_lengkap = $this->db->get_where('pegawai', ['id' => $row['id_pegawai']])->row_array();
				$user_data = array(
					'id_user' => $row['id'],
					'id_pegawai' => $row['id_pegawai'],
					'level' => $row['level'],
					'id_level' => $row['id_level'],
					'username' => $row['username'],
					'nama_lengkap' => $nama_lengkap['nama_pegawai'] ?? 'Developer',
					'logged_in' => true,
				);
				return $user_data;
			}
		}

		return false;
	}



}
?>