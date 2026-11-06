<?php
class M_login extends CI_Model
{

	public function login($username, $password)
	{
		$this->db->where('username', $username);
		$query = $this->db->get('user');

		if ($query->num_rows() > 0) {
			$row = $query->row_array();

			if (strtoupper(trim((string) ($row['status_aktif'] ?? ''))) !== 'AKTIF') {
				return false;
			}

			if (password_verify($password, $row['password'])) {
				$nama_lengkap = null;
				if (!empty($row['id_wali_siswa'])
					&& ((int) $row['id_level'] === 8 || strtoupper(trim((string) $row['level'])) === 'WALI MURID')) {
					$wali = $this->db->get_where('wali_siswa', [
						'id' => (int) $row['id_wali_siswa'],
						'status_aktif' => 'AKTIF',
					])->row_array();
					if (!$wali) {
						return false;
					}
					$nama_lengkap = $wali['nama_wali'];
				} elseif (!empty($row['id_pegawai'])) {
					$pegawai = $this->db->get_where('pegawai', ['id' => $row['id_pegawai']])->row_array();
					$nama_lengkap = $pegawai['nama_pegawai'] ?? null;
				}

				$id_levels = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $row['id_level'])))));
				$levels = array_values(array_filter(array_map('trim', explode(',', (string) $row['level'])), 'strlen'));
				$user_data = array(
					'id_user' => $row['id'],
					'id_pegawai' => $row['id_pegawai'],
					'id_wali_siswa' => $row['id_wali_siswa'],
					'level' => $row['level'],
					'id_level' => $row['id_level'],
					'id_levels' => $id_levels,
					'levels' => $levels,
					'username' => $row['username'],
					'nama_lengkap' => $nama_lengkap ?: ($row['nama_user'] ?: $row['username']),
					'logged_in' => true,
				);
				return $user_data;
			}
		}

		return false;
	}



}
?>
