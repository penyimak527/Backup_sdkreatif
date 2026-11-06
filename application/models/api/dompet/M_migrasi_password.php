<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_migrasi_password extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function audit()
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
                SUM(CASE WHEN password_text IS NOT NULL AND TRIM(password_text) <> '' THEN 1 ELSE 0 END) AS sumber_tersedia,
                SUM(CASE WHEN password LIKE '$2y$%' OR password LIKE '$2a$%' OR password LIKE '$2b$%' THEN 1 ELSE 0 END) AS bcrypt
             FROM user"
        )->row_array();

        return array(
            'status' => true,
            'message' => 'Audit password selesai.',
            'data' => array(
                'total_akun' => (int) $row['total'],
                'password_text_terisi' => (int) $row['sumber_tersedia'],
                'password_bcrypt' => (int) $row['bcrypt'],
            ),
        );
    }

    public function jalankan()
    {
        $pengguna = $this->db
            ->select('id, password_text')
            ->from('user')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        if (empty($pengguna)) {
            return $this->gagal('Tidak ada akun yang dapat dimigrasikan.');
        }

        $hash_per_user = array();

        foreach ($pengguna as $row) {
            $password_text = isset($row['password_text']) ? (string) $row['password_text'] : '';
            if ($password_text === '') {
                return $this->gagal(
                    'Migrasi dibatalkan karena ada password_text yang kosong.',
                    array('id_user' => (int) $row['id'])
                );
            }

            $hash = password_hash($password_text, PASSWORD_DEFAULT);
            if ($hash === false || !password_verify($password_text, $hash)) {
                return $this->gagal(
                    'Migrasi dibatalkan karena pembuatan hash gagal.',
                    array('id_user' => (int) $row['id'])
                );
            }

            $hash_per_user[(int) $row['id']] = $hash;
            unset($password_text);
        }

        $this->db->trans_begin();

        foreach ($hash_per_user as $id_user => $hash) {
            $this->db->where('id', $id_user);
            $this->db->update('user', array(
                'password' => $hash,
                'password_text' => null,
            ));
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Migrasi password gagal dan seluruh perubahan dibatalkan.');
        }

        $this->db->trans_commit();

        $hasil = $this->db->query(
            "SELECT COUNT(*) AS total,
                SUM(CASE WHEN password_text IS NULL OR password_text = '' THEN 1 ELSE 0 END) AS teks_dikosongkan,
                SUM(CASE WHEN password LIKE '$2y$%' OR password LIKE '$2a$%' OR password LIKE '$2b$%' THEN 1 ELSE 0 END) AS bcrypt
             FROM user"
        )->row_array();

        return array(
            'status' => true,
            'message' => 'Migrasi password berhasil.',
            'data' => array(
                'total_akun' => (int) $hasil['total'],
                'password_text_dikosongkan' => (int) $hasil['teks_dikosongkan'],
                'password_bcrypt_php' => (int) $hasil['bcrypt'],
            ),
        );
    }

    private function gagal($message, $data = null)
    {
        return array(
            'status' => false,
            'message' => $message,
            'data' => $data,
        );
    }
}
