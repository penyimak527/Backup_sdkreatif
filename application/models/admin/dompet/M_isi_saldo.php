<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_isi_saldo extends CI_Model
{
    public function active_transfer_accounts()
    {
        return $this->db
            ->select(
                'r.id, r.id_kasbank, r.nama_bank, r.nomor_rekening, '
                .'r.nama_pemilik, COALESCE(k.keterangan, r.nama_kasbank) AS nama_kasbank',
                false
            )
            ->from('rekening_dompet r')
            ->join('kasbank k', 'k.id = r.id_kasbank', 'left')
            ->where('r.status_aktif', 'AKTIF')
            ->where('r.id_kasbank IS NOT NULL', null, false)
            ->order_by("CASE WHEN UPPER(TRIM(r.nama_bank)) = 'BNI' THEN 0 ELSE 1 END", '', false)
            ->order_by('r.urutan', 'ASC')
            ->order_by('r.id', 'ASC')
            ->get()
            ->result_array();
    }

    public function active_cash_accounts()
    {
        return $this->db
            ->select('id, keterangan AS nama_kasbank, kategori')
            ->from('kasbank')
            ->where("UPPER(TRIM(kategori)) = 'TUNAI'", null, false)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function result($filter)
    {
        $is_history = $filter['jenis'] === 'RIWAYAT';
        $table = $is_history ? 'riwayat_isi_saldo' : 'pengajuan_isi_saldo';
        $status_column = $is_history ? 'status_transaksi' : 'status_pengajuan';
        $date_column = $is_history ? 'tanggal' : 'tanggal_pengajuan';

        $this->db->from($table);

        if ($filter['search'] !== '') {
            $number_column = $is_history ? 'no_transaksi' : 'no_pengajuan';

            $this->db
                ->group_start()
                ->like($number_column, $filter['search'])
                ->or_like('nis', $filter['search'])
                ->or_like('nama_siswa', $filter['search'])
                ->group_end();
        }

        if ($filter['status'] !== '') {
            $this->db->where($status_column, $filter['status']);
        }

        $total = (int) $this->db->count_all_results('', false);
        $offset = ($filter['page'] - 1) * $filter['limit'];

        $rows = $this->db
            ->order_by($date_column, 'DESC')
            ->order_by('id', 'DESC')
            ->limit($filter['limit'], $offset)
            ->get()
            ->result_array();

        return [
            'rows' => $rows,
            'jenis' => $filter['jenis'],
            'meta' => [
                'page' => $filter['page'],
                'limit' => $filter['limit'],
                'total' => $total,
                'pages' => max(1, (int) ceil($total / $filter['limit'])),
            ],
        ];
    }

    public function detail($id, $jenis)
    {
        $table = $jenis === 'RIWAYAT'
            ? 'riwayat_isi_saldo'
            : 'pengajuan_isi_saldo';

        return $this->db
            ->get_where($table, ['id' => $id])
            ->row_array();
    }

    public function active_wallet_students($search)
    {
        $this->db
            ->select('s.id, s.nis, s.nama_lengkap, sd.saldo')
            ->from('siswa s')
            ->join(
                'saldo_dompet_siswa sd',
                "sd.id_siswa = s.id AND sd.status_dompet = 'AKTIF'",
                'inner'
            )
            ->where('s.status_siswa', 'AKTIF')
            ->where('s.status_aktif', 'AKTIF');

        if ($search !== '') {
            $this->db
                ->group_start()
                ->like('s.nis', $search)
                ->or_like('s.nama_lengkap', $search)
                ->group_end();
        }

        return $this->db
            ->order_by('s.nama_lengkap')
            ->limit(30)
            ->get()
            ->result_array();
    }

    public function update_pending_status($id, $status, $reason, $user)
    {
        $this->db->trans_begin();

        $submission = $this->db->query(
            'SELECT id, status_pengajuan
             FROM pengajuan_isi_saldo
             WHERE id = ?
             FOR UPDATE',
            [(int) $id]
        )->row_array();

        if (!$submission) {
            $this->db->trans_rollback();

            return $this->error_result('Pengajuan tidak ditemukan.', 404);
        }

        if ($submission['status_pengajuan'] !== 'MENUNGGU_VERIFIKASI') {
            $this->db->trans_rollback();

            return $this->error_result('Pengajuan sudah diproses.', 409);
        }

        $verification_data = [
            'status_pengajuan' => $status,
            'alasan_penolakan' => $reason,
            'tanggal_verifikasi' => date('Y-m-d'),
            'waktu_verifikasi' => date('H:i:s'),
            'id_user_verifikasi' => (int) $user['id'],
            'nama_user_verifikasi' => $user['nama_user'],
        ];

        $this->db
            ->where('id', $id)
            ->update('pengajuan_isi_saldo', $verification_data);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return $this->error_result('Status pengajuan gagal diperbarui.', 500);
        }

        $this->db->trans_commit();

        return ['success' => true];
    }

    private function error_result($message, $status)
    {
        return [
            'success' => false,
            'message' => $message,
            'status' => $status,
        ];
    }
}
