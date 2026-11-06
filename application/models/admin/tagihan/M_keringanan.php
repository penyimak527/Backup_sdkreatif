<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_keringanan extends CI_Model
{
    // public function cari_siswa()
    // {
    //     $q = trim((string) $this->input->post('q', true));
    //     $like = '%' . $q . '%';
    //     return $this->db->query("SELECT DISTINCT id_siswa,nis,nisn,nama_siswa,nama_kelas FROM tagihan_siswa WHERE status_tagihan='Aktif' AND (nama_siswa LIKE ? OR nis LIKE ? OR nisn LIKE ?) ORDER BY nama_siswa LIMIT 30", array($like, $like, $like))->result_array();
    // }
    public function cari_siswa($page = 1, $limit = 10)
    {
        $q = trim((string) $this->input->post('q', true));
        $like = '%' . $q . '%';
        $limit = in_array((int) $limit, array(10, 25, 50, 100), true) ? (int) $limit : 10;
        $page = max(1, (int) $page);
        $where = "FROM siswa s
            WHERE EXISTS (
                SELECT 1 FROM tagihan_siswa ts
                WHERE ts.id_siswa = s.id AND ts.status_tagihan = 'Aktif'
            )
            AND (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?) AND UPPER(s.status_siswa)='AKTIF'";
        $params = array($like, $like, $like);

        $count = $this->db->query("SELECT COUNT(*) AS total " . $where, $params)->row_array();
        $total = (int) ($count['total'] ?? 0);
        $totalPages = (int) ceil($total / $limit);
        $page = $totalPages ? min($page, $totalPages) : 1;
        $offset = ($page - 1) * $limit;

        $rows = $this->db->query("SELECT s.id AS id_siswa, s.nis, s.nisn,
                s.nama_lengkap AS nama_siswa,
                COALESCE((
                    SELECT kset.nama_kelas
                    FROM kelas_siswa ks
                    INNER JOIN kelas_setting kset ON kset.id = ks.id_kelas_setting
                    INNER JOIN master_tahun_ajaran ta ON ta.id = kset.id_periode
                    WHERE ks.id_siswa = s.id
                      AND UPPER(ks.status_aktif) = 'AKTIF' AND ta.status = 'Aktif'
                    ORDER BY ks.id DESC
                    LIMIT 1
                ), '') AS nama_kelas
            " . $where . "
            ORDER BY s.nama_lengkap ASC, s.id ASC
            LIMIT ? OFFSET ?", array_merge($params, array($limit, $offset)))->result_array();

        return array(
            'data' => $rows,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages
        );
    }

    public function tagihan_siswa()
    {
        $sid = (int) $this->input->post('id_siswa');
        return $this->db->where('id_siswa', $sid)->where('status_tagihan', 'Aktif')->order_by('tahun')->order_by('bulan')->get('tagihan_siswa')->result_array();
    }
    public function riwayat()
    {
        $sid = (int) $this->input->post('id_siswa');
        return $this->db->where('id_siswa', $sid)->where_in('jenis_keringanan', array('Potongan Nominal', 'Potongan Persen', 'Pembebasan Penuh'))->order_by('id', 'DESC')->get('tagihan_keringanan_siswa')->result_array();
    }
    public function simpan()
    {
        $idTagihan = (int) $this->input->post('id_tagihan_siswa');
        $jenis = trim((string) $this->input->post('jenis_keringanan', true));
        $nilai = $this->nilai_nominal($this->input->post('nilai_keringanan'));
        $alasan = trim((string) $this->input->post('alasan', true));
        $row = $this->db->where('id', $idTagihan)->where('status_tagihan', 'Aktif')->get('tagihan_siswa')->row_array();
        if (!$row || !in_array($jenis, array('Potongan Nominal', 'Potongan Persen', 'Pembebasan Penuh'), true) || $alasan === '')
            return $this->model_response(false, 'Tagihan, jenis keringanan, dan alasan wajib diisi.');
        $awal = (float) $row['nominal_awal'];
        $dibayar = (float) $row['nominal_dibayar'];
        if ($jenis === 'Potongan Nominal')
            $potongan = $nilai;
        elseif ($jenis === 'Potongan Persen') {
            $nilai = max(0, min(100, $nilai));
            $potongan = $awal * $nilai / 100;
        } else {
            $potongan = $awal;
        }
        $akhir = max(0, $awal - $potongan);
        if ($akhir < $dibayar)
            return $this->model_response(false, 'Nominal akhir tidak boleh lebih kecil dari pembayaran yang sudah masuk.');
        $sisa = max(0, $akhir - $dibayar);
        $status = $jenis === 'Pembebasan Penuh' && $dibayar <= 0 ? 'Dibebaskan' : ($sisa <= 0 ? 'Lunas' : ($dibayar > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar'));
        $this->db->trans_begin();
        $this->db->where('id_tagihan_master', $row['id_tagihan_master'])->where('id_siswa', $row['id_siswa'])->where('bulan', $row['bulan'])->where('tahun', $row['tahun'])->where_in('jenis_keringanan', array('Potongan Nominal', 'Potongan Persen', 'Pembebasan Penuh'))->where('status', 'Aktif')->update('tagihan_keringanan_siswa', array('status' => 'Dibatalkan', 'tanggal_batal' => $this->tanggal_sekarang(), 'waktu_batal' => $this->waktu_sekarang(), 'id_user_batal' => $this->app_user_id(), 'nama_user_batal' => $this->app_user_name(), 'alasan_batal' => 'Diganti keringanan baru'));
        $data = array('id_tagihan_master' => $row['id_tagihan_master'], 'id_siswa' => $row['id_siswa'], 'nis' => $row['nis'], 'nisn' => $row['nisn'], 'nama_siswa' => $row['nama_siswa'], 'bulan' => $row['bulan'], 'tahun' => $row['tahun'], 'jenis_keringanan' => $jenis, 'nominal_awal' => $awal, 'nilai_keringanan' => $jenis === 'Potongan Persen' ? $nilai : $potongan, 'nominal_setelah_keringanan' => $akhir, 'alasan' => $alasan, 'status' => 'Aktif', 'tanggal_mulai' => $this->tanggal_sekarang(), 'tanggal' => $this->tanggal_sekarang(), 'waktu' => $this->waktu_sekarang(), 'id_user' => $this->app_user_id(), 'nama_user' => $this->app_user_name());
        $this->db->insert('tagihan_keringanan_siswa', $data);
        $id = $this->db->insert_id();
        $this->db->where('id', $idTagihan)->update('tagihan_siswa', array('jenis_keringanan' => $jenis, 'nilai_keringanan' => $potongan, 'nominal_tagihan' => $akhir, 'sisa_tagihan' => $sisa, 'status_pembayaran' => $status, 'tanggal_update' => $this->tanggal_sekarang(), 'waktu_update' => $this->waktu_sekarang()));
        $this->tagihan_log_activity('Keringanan Tagihan', 'Tagihan', 'Ubah', 'tagihan_siswa', $idTagihan, $row['no_tagihan'], $jenis . ' ' . $this->rupiah($potongan) . ' - ' . $alasan, $row, array('nominal_tagihan' => $akhir, 'sisa_tagihan' => $sisa, 'status' => $status));
        return $this->tagihan_transaction_result('Keringanan berhasil disimpan.');
    }
    public function batalkan()
    {
        $id = (int) $this->input->post('id');
        $alasan = trim((string) $this->input->post('alasan', true));
        $k = $this->db->where('id', $id)->where('status', 'Aktif')->get('tagihan_keringanan_siswa')->row_array();
        if (!$k || $alasan === '')
            return $this->model_response(false, 'Keringanan aktif dan alasan pembatalan wajib tersedia.');
        $row = $this->db->where('id_tagihan_master', $k['id_tagihan_master'])->where('id_siswa', $k['id_siswa'])->where('bulan', $k['bulan'])->where('tahun', $k['tahun'])->get('tagihan_siswa')->row_array();
        if (!$row)
            return $this->model_response(false, 'Tagihan siswa tidak ditemukan.');
        $normal = (float) $row['nominal_awal'];
        $sisa = max(0, $normal - (float) $row['nominal_dibayar']);
        $status = $sisa <= 0 ? 'Lunas' : ((float) $row['nominal_dibayar'] > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar');
        $this->db->trans_begin();
        $this->db->where('id', $id)->update('tagihan_keringanan_siswa', array('status' => 'Dibatalkan', 'tanggal_batal' => $this->tanggal_sekarang(), 'waktu_batal' => $this->waktu_sekarang(), 'id_user_batal' => $this->app_user_id(), 'nama_user_batal' => $this->app_user_name(), 'alasan_batal' => $alasan));
        $this->db->where('id', $row['id'])->update('tagihan_siswa', array('jenis_keringanan' => null, 'nilai_keringanan' => 0, 'nominal_tagihan' => $normal, 'sisa_tagihan' => $sisa, 'status_pembayaran' => $status, 'tanggal_update' => $this->tanggal_sekarang(), 'waktu_update' => $this->waktu_sekarang()));
        $this->tagihan_log_activity('Batalkan Keringanan', 'Tagihan', 'Batal', 'tagihan_keringanan_siswa', $id, $row['no_tagihan'], $alasan, $k, array('status' => 'Dibatalkan'));
        return $this->tagihan_transaction_result('Keringanan berhasil dibatalkan dan tarif normal dipulihkan.');
    }

    private function app_user_id()
    {
        $user = $this->session->userdata('admin');
        return is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0;
    }


    private function app_user_name()
    {
        $user = $this->session->userdata('admin');
        return is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator';
    }


    private function rupiah($nominal)
    {
        return 'Rp' . number_format((float) $nominal, 0, ',', '.');
    }


    private function nilai_nominal($value)
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim(str_ireplace(array('Rp', ' '), '', (string) $value));
        if (preg_match('/^-?\\d+\\.\\d{1,2}$/', $text)) {
            return (float) $text;
        }

        $clean = preg_replace('/[^0-9-]/', '', $text);
        if ($clean === '' || $clean === '-') {
            return 0;
        }

        return (float) $clean;
    }


    private function tanggal_sekarang()
    {
        return date('d-m-Y');
    }


    private function waktu_sekarang()
    {
        return date('H:i:s');
    }


    private function model_response($success, $message = '', $extra = array())
    {
        return array_merge(array(
            'result' => $success ? 'true' : 'false',
            'message' => $message
        ), $extra);
    }


    private function tagihan_transaction_result($success_message = 'Data berhasil disimpan.')
    {
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array(
                'result' => 'false',
                'message' => 'Proses database gagal. Tidak ada perubahan yang disimpan.'
            );
        }

        $this->db->trans_commit();
        return array(
            'result' => 'true',
            'message' => $success_message
        );
    }


    private function tagihan_log_activity($jenis, $modul, $aksi, $table, $id, $nomor, $keterangan, $before = null, $after = null)
    {
        $user = $this->session->userdata('admin');
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => $jenis,
            'modul' => $modul,
            'aksi' => $aksi,
            'nama_tabel' => $table,
            'id_referensi' => (string) $id,
            'nomor_referensi' => $nomor,
            'keterangan' => $keterangan,
            'data_sebelum' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator'
        ));
    }
}
