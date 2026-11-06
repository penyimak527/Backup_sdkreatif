<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tabungan extends CI_Model
{
    public function ringkasan($id_siswa, $filter = array())
    {
        $id_siswa = (int) $id_siswa;
        $filter = is_array($filter) ? $filter : array();

        $where_setoran = $this->where_sql('s', $filter, 'Setoran');
        $where_penarikan = $this->where_sql('p', $filter, 'Penarikan');

        $setoran = $this->db->query(
            "SELECT COALESCE(SUM(s.nominal),0) AS total, COUNT(*) AS jumlah
             FROM tabungan_siswa_setoran s
             WHERE s.id_siswa={$id_siswa}{$where_setoran}"
        )->row_array();

        $penarikan = $this->db->query(
            "SELECT COALESCE(SUM(p.nominal),0) AS total, COUNT(*) AS jumlah
             FROM tabungan_siswa_penarikan p
             WHERE p.id_siswa={$id_siswa}{$where_penarikan}"
        )->row_array();

        // Saldo saat ini tetap saldo transaksi aktif seluruh periode,
        // sedangkan total setoran/penarikan di atas mengikuti filter Portal Wali.
        $saldo_setoran = $this->db
            ->select('COALESCE(SUM(nominal),0) AS total', false)
            ->from('tabungan_siswa_setoran')
            ->where('id_siswa', $id_siswa)
            ->where('status_transaksi', 'Aktif')
            ->get()
            ->row_array();

        $saldo_penarikan = $this->db
            ->select('COALESCE(SUM(nominal),0) AS total', false)
            ->from('tabungan_siswa_penarikan')
            ->where('id_siswa', $id_siswa)
            ->where('status_transaksi', 'Aktif')
            ->get()
            ->row_array();

        return array(
            'total_setoran' => (float) ($setoran['total'] ?? 0),
            'jumlah_setoran' => (int) ($setoran['jumlah'] ?? 0),
            'total_penarikan' => (float) ($penarikan['total'] ?? 0),
            'jumlah_penarikan' => (int) ($penarikan['jumlah'] ?? 0),
            'saldo' => (float) ($saldo_setoran['total'] ?? 0) - (float) ($saldo_penarikan['total'] ?? 0),
        );
    }

    public function result($id_siswa, $filter)
    {
        $halaman = max(1, (int) $filter['halaman']);
        $per_halaman = max(1, (int) $filter['per_halaman']);
        $offset = ($halaman - 1) * $per_halaman;
        $client_paging = !empty($filter['client_paging']);

        $where_setoran = $this->where_sql('s', $filter, 'Setoran');
        $where_penarikan = $this->where_sql('p', $filter, 'Penarikan');
        $id_siswa = (int) $id_siswa;

        $union = "SELECT s.id,'Setoran' AS jenis,s.no_transaksi,s.tanggal_transaksi,s.metode_pembayaran,s.nama_kasbank,s.nominal,s.catatan,s.status_transaksi,s.created_at,s.alasan_batal
                  FROM tabungan_siswa_setoran s
                  WHERE s.id_siswa={$id_siswa}{$where_setoran}
                  UNION ALL
                  SELECT p.id,'Penarikan' AS jenis,p.no_transaksi,p.tanggal_transaksi,p.metode_pembayaran,p.nama_kasbank,p.nominal,p.catatan,p.status_transaksi,p.created_at,p.alasan_batal
                  FROM tabungan_siswa_penarikan p
                  WHERE p.id_siswa={$id_siswa}{$where_penarikan}";

        $count = $this->db->query("SELECT COUNT(*) AS total FROM ({$union}) x")->row_array();
        $total = (int) ($count['total'] ?? 0);

        $sql = "SELECT * FROM ({$union}) x ORDER BY created_at DESC, id DESC";
        $params = array();
        if (!$client_paging) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params = array($per_halaman, $offset);
        }
        $rows = $this->db->query($sql, $params)->result_array();

        return array(
            'rows' => $rows,
            'total' => $total,
            'halaman' => $halaman,
            'per_halaman' => $per_halaman,
            'total_halaman' => max(1, (int) ceil($total / $per_halaman)),
        );
    }

    public function detail($id_siswa, $jenis, $id)
    {
        $jenis = ucfirst(strtolower($jenis));
        if (!in_array($jenis, array('Setoran', 'Penarikan'), true)) return null;

        $table = $jenis === 'Setoran' ? 'tabungan_siswa_setoran' : 'tabungan_siswa_penarikan';
        $row = $this->db
            ->where('id', (int) $id)
            ->where('id_siswa', (int) $id_siswa)
            ->limit(1)
            ->get($table)
            ->row_array();

        if (!$row) return null;
        $row['jenis'] = $jenis;
        return $row;
    }

   private function where_sql($alias, $filter, $jenis)
    {
        $where = '';
        $jenis_filter = isset($filter['jenis']) ? (string) $filter['jenis'] : '';
        $status_filter = isset($filter['status']) ? (string) $filter['status'] : '';
        $tanggal_awal = isset($filter['tanggal_awal']) ? (string) $filter['tanggal_awal'] : '';
        $tanggal_akhir = isset($filter['tanggal_akhir']) ? (string) $filter['tanggal_akhir'] : '';

        if ($jenis_filter !== '' && $jenis_filter !== $jenis) {
            return ' AND 1=0';
        }

        if (in_array($status_filter, array('Aktif', 'Dibatalkan'), true)) {
            $where .= ' AND '.$alias.'.status_transaksi='.$this->db->escape($status_filter);
        }

        // Kolom tanggal_transaksi Tabungan menyimpan d-m-Y. Bandingkan sebagai DATE,
        // bukan sebagai teks, dengan tetap menerima filter AJAX Y-m-d yang sudah ada.
        $tanggal_sql = "COALESCE(STR_TO_DATE(REPLACE({$alias}.tanggal_transaksi,' ',''),'%d-%m-%Y'),STR_TO_DATE(REPLACE({$alias}.tanggal_transaksi,' ',''),'%Y-%m-%d'))";

        if ($this->tanggal_valid($tanggal_awal)) {
            $where .= ' AND '.$tanggal_sql.' >= STR_TO_DATE('.$this->db->escape($tanggal_awal).",'%Y-%m-%d')";
        }

        if ($this->tanggal_valid($tanggal_akhir)) {
            $where .= ' AND '.$tanggal_sql.' <= STR_TO_DATE('.$this->db->escape($tanggal_akhir).",'%Y-%m-%d')";
        }

        return $where;
    }

    private function tanggal_valid($tanggal)
    {
        if (!$tanggal) return false;
        $date = DateTime::createFromFormat('!Y-m-d', $tanggal);
        return $date && $date->format('Y-m-d') === $tanggal;
    }
}
