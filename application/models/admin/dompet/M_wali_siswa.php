<?php defined('BASEPATH') or exit('No direct script access allowed');
class M_wali_siswa extends CI_Model
{
    public function result($f)
    {
        $this->base($f, true);
        $count = $this->db->select('COUNT(DISTINCT w.id) total', false)->get()->row_array();
        $total = (int)($count['total'] ?? 0);
        $this->base($f, false);
        $rows = $this->db->limit($f['limit'], ($f['page'] - 1) * $f['limit'])->get()->result_array();
        return ['rows' => $rows, 'meta' => ['page' => $f['page'], 'limit' => $f['limit'], 'total' => $total, 'pages' => max(1, (int)ceil($total / $f['limit']))]];
    }
    private function base($f, $count)
    {
        $this->db->from('wali_siswa w')->join('wali_siswa_detail d', 'd.id_wali_siswa=w.id', 'left')->join('user u', 'u.id_wali_siswa=w.id AND u.id_level=8', 'left')->where('w.status_aktif !=', 'TERHAPUS');
        if ($f['search'] !== '') $this->db->group_start()->like('w.nama_wali', $f['search'])->or_like('w.no_telp', $f['search'])->or_like('w.email', $f['search'])->group_end();
        if (in_array($f['status'], ['AKTIF', 'NONAKTIF'], true)) $this->db->where('w.status_aktif', $f['status']);
        if (!$count) $this->db->select("w.id,w.nama_wali,w.hubungan,w.no_telp,w.email,w.status_aktif,COUNT(DISTINCT CASE WHEN d.status_aktif='AKTIF' THEN d.id_siswa END) jumlah_anak,u.username,u.password_text,u.status_aktif status_user", false)->group_by('w.id')->order_by('w.nama_wali');
    }
    public function detail($id)
    {
        $w = $this->db->select('w.*,u.username,u.status_aktif status_user')->from('wali_siswa w')->join('user u', 'u.id_wali_siswa=w.id AND u.id_level=8', 'left')->where('w.id', $id)->get()->row_array();
        if (!$w) return null;
        $w['anak'] = $this->db->select('d.id,d.id_siswa,d.status_utama,d.status_aktif,s.nis,s.nama_lengkap,s.status_siswa')->from('wali_siswa_detail d')->join('siswa s', 's.id=d.id_siswa')->where('d.id_wali_siswa', $id)->order_by('d.status_aktif', 'DESC')->order_by('s.nama_lengkap')->get()->result_array();
        return $w;
    }
    public function save($id, $p, $username, $user)
    {
        $wali = null;
        if ($id > 0) {
            $wali = $this->db->get_where('wali_siswa', ['id' => $id])->row_array();
            if (!$wali) return $this->err('Wali tidak ditemukan.', 404);
        }

        $akun = $id > 0
            ? $this->db->select('id')->where('id_wali_siswa', $id)->where('id_level', '8')->order_by('id')->get('user')->row_array()
            : null;
        $username_query = $this->db->where('username', $username);
        if ($akun) $username_query->where('id !=', (int) $akun['id']);
        if ($username_query->count_all_results('user') > 0) return $this->err('Username sudah digunakan oleh pengguna lain.', 409);

        $data = array_merge($p, ['tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'), 'id_user' => (int)$user['id'], 'nama_user' => $user['nama_user']]);
        $generated_password = null;

        $this->db->trans_begin();

        if ($id > 0) {
            $this->db->where('id', $id)->update('wali_siswa', $data);
            $id_wali = $id;
        } else {
            $this->db->insert('wali_siswa', array_merge($data, ['status_aktif' => 'AKTIF']));
            $id_wali = (int) $this->db->insert_id();
        }

        if ($akun) {
            $this->db->where('id', (int) $akun['id'])->update('user', [
                'nama_user' => $p['nama_wali'],
                'username' => $username,
                'email' => $p['email'],
                'no_telp' => $p['no_telp'],
            ]);
        } else {
            $generated_password = $this->generate_password();
            $password_hash = $generated_password !== null
                ? password_hash($generated_password, PASSWORD_DEFAULT)
                : false;

            if ($password_hash === false) {
                $this->db->trans_rollback();
                return $this->err('Password akun wali gagal dibuat.', 500);
            }

            $this->db->insert('user', [
                'nama_user' => $p['nama_wali'],
                'username' => $username,
                'password' => $password_hash,
                'password_text' => $generated_password,
                'id_level' => '8',
                'level' => 'Wali Murid',
                'id_wali_siswa' => $id_wali,
                'email' => $p['email'],
                'no_telp' => $p['no_telp'],
                'status_aktif' => $wali ? $wali['status_aktif'] : 'AKTIF',
            ]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->err('Data wali dan akun pengguna gagal disimpan.', 500);
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'data' => [
                'id' => $id_wali,
                'username' => $username,
                'generated_password' => $generated_password,
            ],
        ];
    }
    public function status($id, $status, $user)
    {
        if (!$this->db->where('id', $id)->count_all_results('wali_siswa')) return $this->err('Wali tidak ditemukan.', 404);
        $this->db->trans_begin();
        $this->db->where('id', $id)->update('wali_siswa', ['status_aktif' => $status, 'tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'), 'id_user' => (int)$user['id'], 'nama_user' => $user['nama_user']]);
        $this->db->where('id_wali_siswa', $id)->where('id_level', '8')->update('user', ['status_aktif' => $status]);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->err('Status wali gagal diperbarui.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true];
    }
    public function trash_rows()
    {
        return $this->db->select('id,nama_wali,hubungan,no_telp,email,tanggal,waktu,nama_user')->where('status_aktif', 'TERHAPUS')->order_by('tanggal', 'DESC')->order_by('waktu', 'DESC')->get('wali_siswa')->result_array();
    }
    public function soft_delete($id, $user)
    {
        if (!$this->db->where('id', $id)->where('status_aktif !=', 'TERHAPUS')->count_all_results('wali_siswa')) return $this->err('Wali tidak ditemukan atau sudah berada di Trash.', 404);
        $this->db->trans_begin();
        $audit = ['tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'), 'id_user' => (int)$user['id'], 'nama_user' => $user['nama_user']];
        $this->db->where('id', $id)->update('wali_siswa', array_merge($audit, ['status_aktif' => 'TERHAPUS']));
        $this->db->where('id_wali_siswa', $id)->where('id_level', '8')->update('user', ['status_aktif' => 'NONAKTIF']);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->err('Wali gagal dipindahkan ke Trash.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true];
    }
    public function restore($id, $user)
    {
        if (!$this->db->where('id', $id)->where('status_aktif', 'TERHAPUS')->count_all_results('wali_siswa')) return $this->err('Wali tidak ditemukan di Trash.', 404);
        $ok = $this->db->where('id', $id)->update('wali_siswa', ['status_aktif' => 'NONAKTIF', 'tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'), 'id_user' => (int)$user['id'], 'nama_user' => $user['nama_user']]);
        return $ok ? ['success' => true] : $this->err('Wali gagal dipulihkan.', 500);
    }
    public function relate($id_wali, $id_siswa, $utama, $user)
    {
        $w = $this->db->get_where('wali_siswa', ['id' => $id_wali])->row_array();
        $s = $this->db->get_where('siswa', ['id' => $id_siswa])->row_array();
        if (!$w || !$s) return $this->err('Wali atau siswa tidak ditemukan.', 404);
        $this->db->trans_begin();
        if ($utama === 'YA') $this->db->where('id_siswa', $id_siswa)->update('wali_siswa_detail', ['status_utama' => 'TIDAK']);
        $old = $this->db->get_where('wali_siswa_detail', ['id_wali_siswa' => $id_wali, 'id_siswa' => $id_siswa])->row_array();
        $data = ['nama_wali' => $w['nama_wali'], 'nis' => $s['nis'], 'nama_siswa' => $s['nama_lengkap'], 'status_utama' => $utama, 'status_aktif' => 'AKTIF', 'tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'), 'id_user' => (int)$user['id'], 'nama_user' => $user['nama_user']];
        if ($old) $this->db->where('id', $old['id'])->update('wali_siswa_detail', $data);
        else $this->db->insert('wali_siswa_detail', array_merge($data, ['id_wali_siswa' => $id_wali, 'id_siswa' => $id_siswa]));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->err('Relasi wali dan siswa gagal disimpan.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true];
    }
    public function reset_password($id, $password)
    {
        $u = $this->db->select('id')->get_where('user', ['id_wali_siswa' => $id, 'id_level' => '8'])->row_array();
        if (!$u) return $this->err('Akun wali belum tersedia.', 404);
        $ok = $this->db->where('id', $u['id'])->update('user', ['password' => password_hash($password, PASSWORD_DEFAULT), 'password_text' => $password]);
        return $ok ? ['success' => true] : $this->err('Password gagal diperbarui.', 500);
    }
    public function active_students($q)
    {
        $this->db->select('id,nis,nama_lengkap')->where('status_siswa', 'AKTIF');
        if ($q !== '') $this->db->group_start()->like('nis', $q)->or_like('nama_lengkap', $q)->group_end();
        return $this->db->order_by('nama_lengkap')->limit(30)->get('siswa')->result_array();
    }
    private function generate_password()
    {
        $digits = random_int(0, 1) === 0
            ? (string) random_int(100, 999)
            : (string) random_int(1000, 9999);

        return 'portal'.$digits;
    }
    private function err($m, $s)
    {
        return ['success' => false, 'message' => $m, 'status' => $s];
    }
}
