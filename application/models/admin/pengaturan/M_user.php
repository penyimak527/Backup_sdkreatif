<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_user extends CI_Model
{
    public function user_result($search = '')
    {
        $this->db->select('id, nama_user, username, id_level, level, id_pegawai, status_aktif');
		$this->db->where("FIND_IN_SET('8', REPLACE(id_level, ' ', '')) = 0", null, false);
        if ($search !== '') {
            $this->db->group_start()
                ->like('nama_user', $search)
                ->or_like('username', $search)
                ->group_end();
        }

        return $this->db
            ->order_by('nama_user', 'ASC')
            ->get('user')
            ->result_array();
    }

    public function user_edit($id_user)
    {
        return $this->db
            ->select('id, nama_user, username, id_level, level, id_pegawai, status_aktif')
			->where("FIND_IN_SET('8', REPLACE(id_level, ' ', '')) = 0", null, false)
			->get_where('user', ['id' => (int) $id_user])
            ->row_array();
    }

    // public function tambah($input)
    // {
    //     $pegawai = $this->db->get_where('pegawai', ['id' => (int) $input['id_pegawai']])->row_array();
    //     $level = $this->db->get_where('level', ['id' => (int) $input['id_level']])->row_array();
    //     if (!$pegawai || !$level || (int) $level['id'] === 8) {
    //         return $this->failure('Pegawai atau level web tidak valid.');
    //     }

    //     if ($this->username_exists($input['username'])) {
    //         return $this->failure('Username sudah digunakan.', 409);
    //     }

    //     $data = [
    //         'nama_user' => $pegawai['nama_pegawai'],
    //         'username' => $input['username'],
    //         'password' => password_hash($input['password'], PASSWORD_DEFAULT),
    //         'level' => $level['level'],
    //         'id_level' => $level['id'],
    //         'id_pegawai' => $pegawai['id'],
    //         'status_aktif' => 'AKTIF',
    //     ];

    //     if (!$this->db->insert('user', $data)) {
    //         return $this->failure('User gagal disimpan.', 500);
    //     }

    //     return ['success' => true, 'data' => ['id' => $this->db->insert_id()]];
    // }

    // public function edit($id_user, $input)
    // {
    //     $current = $this->user_edit($id_user);
    //     if (!$current) {
    //         return $this->failure('User tidak ditemukan.', 404);
    //     }

    //     $id_pegawai = (int) ($input['id_pegawai'] ?? 0);
    //     $id_level = (int) ($input['id_level'] ?? 0);
    //     $username = trim((string) ($input['username'] ?? ''));
    //     $password = (string) ($input['password'] ?? '');

    //     $pegawai = $this->db
    //         ->select('id, nama_pegawai')
    //         ->get_where('pegawai', ['id' => $id_pegawai])
    //         ->row_array();
    //     $level = $this->db
    //         ->select('id, level')
    //         ->get_where('level', ['id' => $id_level])
    //         ->row_array();

    //     if (!$pegawai
    //         || !$level
    //         || (int) $level['id'] === 8
    //         || trim((string) ($level['level'] ?? '')) === '') {
    //         return $this->failure('Pegawai atau level web tidak valid.');
    //     }
    //     if ($username === '') {
    //         return $this->failure('Username wajib diisi.');
    //     }
    //     if ($this->username_exists($username, $id_user)) {
    //         return $this->failure('Username sudah digunakan.', 409);
    //     }

    //     $data = [
    //         'nama_user' => isset($pegawai['nama_pegawai']) ? $pegawai['nama_pegawai'] : '',
    //         'username' => $username,
    //         'level' => (string) $level['level'],
    //         'id_level' => (int) $level['id'],
    //         'id_pegawai' => isset($pegawai['id']) ? (int) $pegawai['id'] : 0,
    //     ];
    //     if ($password !== '') {
    //         $password_hash = password_hash($password, PASSWORD_DEFAULT);
    //         if ($password_hash === false) {
    //             return $this->failure('Password gagal diamankan.', 500);
    //         }
    //         $data['password'] = $password_hash;
    //     }

    //     if (!$this->db->update('user', $data, ['id' => (int) $id_user])) {
    //         return $this->failure('User gagal diperbarui.', 500);
    //     }

    //     return ['success' => true, 'data' => ['id' => (int) $id_user]];
    // }

    // Level internal disimpan sebagai daftar ID dan nama yang dipisahkan koma.
    private function selected_levels()
    {
        $raw = $this->input->post('id_level');
        if (!is_array($raw)) {
            $raw = array($raw);
        }
        $ids = array();
        foreach ($raw as $value) {
            $value = trim((string) $value);
            if ($value === '' || !ctype_digit($value) || (int) $value <= 0 || (int) $value === 8) {
                return false;
            }
            $ids[] = (int) $value;
        }
        $ids = array_values(array_unique($ids));
        if (!$ids) {
            return false;
        }
        $levels = $this->db->select('id, level')->where_in('id', $ids)->get('level')->result_array();
        if (count($levels) !== count($ids)) {
            return false;
        }
        $names_by_id = array();
        foreach ($levels as $level) {
            if (trim((string) ($level['level'] ?? '')) === '') {
                return false;
            }
            $names_by_id[(int) $level['id']] = (string) $level['level'];
        }
        $names = array();
        foreach ($ids as $id) {
            $names[] = $names_by_id[$id];
        }
        $level_ids = implode(',', $ids);
        $level_names = implode(',', $names);
        // Setelah SQL migrasi, ukuran kolom user adalah VARCHAR(100) dan VARCHAR(255).
        if (strlen($level_ids) > 100 || strlen($level_names) > 255) {
            return false;
        }
        return array('id_level' => $level_ids, 'level' => $level_names);
    }

    public function tambah()
    {
        $id_pegawai = (int) $this->input->post('id_pegawai');
        $selected_levels = $this->selected_levels();
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
        

        if (!$pegawai || !$selected_levels) {
            return false;
        }

        if ($this->username_exists($username)) {
            return false;
        }

        $data = [
            'nama_user' => $pegawai['nama_pegawai'],
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'password_text' => $password,
            'level' => $selected_levels['level'],
            'id_level' => $selected_levels['id_level'],
            'id_pegawai' => $pegawai['id'],
            'status_aktif' => 'AKTIF',
        ];

        return $this->db->insert('user', $data);
    }

    public function edit()
    {
        $id_user = (int) $this->input->post('id_user');
        $current = $this->user_edit($id_user);

        if (!$current) {
            return false;
        }

        $id_pegawai = (int) $this->input->post('id_pegawai');
        $selected_levels = $this->selected_levels();
        $username = trim((string) $this->input->post('username'));
        $password = (string) $this->input->post('password');

        $pegawai = $this->db
            ->select('id, nama_pegawai')
            ->get_where('pegawai', ['id' => $id_pegawai])
            ->row_array();


        if (
            !$pegawai
            || !$selected_levels
        ) {
            return false;
        }

        if ($username === '') {
            return false;
        }

        if ($this->username_exists($username, $id_user)) {
            return false;
        }

        $data = [
            'nama_user' => isset($pegawai['nama_pegawai']) ? $pegawai['nama_pegawai'] : '',
            'username' => $username,
            'level' => $selected_levels['level'],
            'id_level' => $selected_levels['id_level'],
            'id_pegawai' => isset($pegawai['id']) ? (int) $pegawai['id'] : 0,
        ];

        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            if ($password_hash === false) {
                return false;
            }

            $data['password'] = $password_hash;
            $data['password_text'] = $password;
        }

        return $this->db->update('user', $data, ['id' => $id_user]);
    }

    public function hapus()
    {
        $id = (int) $this->input->post('id');

        return $this->db->delete('user', ['id' => $id]);
    }
    
    public function nonaktifkan($id_user, $actor_id)
    {
        if ((int) $id_user === (int) $actor_id) {
            return $this->failure('User yang sedang login tidak dapat menonaktifkan dirinya sendiri.');
        }
        if (!$this->user_edit($id_user)) {
            return $this->failure('User tidak ditemukan.', 404);
        }

        if (!$this->db->update('user', ['status_aktif' => 'NONAKTIF'], ['id' => (int) $id_user])) {
            return $this->failure('User gagal dinonaktifkan.', 500);
        }

        return ['success' => true, 'data' => ['id' => (int) $id_user]];
    }

    private function username_exists($username, $exclude_id = null)
    {
        $this->db->where('username', $username);
        if ($exclude_id !== null) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results('user') > 0;
    }

    private function failure($message, $status = 422)
    {
        return ['success' => false, 'message' => $message, 'status' => $status];
    }
}
