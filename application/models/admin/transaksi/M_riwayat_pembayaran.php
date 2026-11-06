<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_riwayat_pembayaran extends CI_Model
{
    public function metode_list()
    {
        return $this->db->where('status', 'Aktif')->order_by('urutan')->get('tagihan_metode_pembayaran')->result_array();
    }

    public function result()
    {
        $search = trim((string)$this->input->post('search', true));
        $awal = trim((string)$this->input->post('awal', true));
        $akhir = trim((string)$this->input->post('akhir', true));
        $metode = (int)$this->input->post('id_metode');
        $status = trim((string)$this->input->post('status', true));
        $this->db->select('p.*,c.alasan_pembatalan,c.nama_user_pembatalan,c.tanggal_pembatalan,c.waktu_pembatalan')->from('tagihan_pembayaran p')->join('tagihan_pembatalan_transaksi c', 'c.id_pembayaran=p.id', 'left');
        if ($search !== '') $this->db->group_start()->like('p.no_transaksi', $search)->or_like('p.nama_siswa', $search)->or_like('p.nis', $search)->group_end();
        if ($awal !== '' && preg_match('/^\d{2}-\d{2}-\d{4}$/', $awal)) $this->db->where("STR_TO_DATE(p.tanggal_transaksi,'%d-%m-%Y') >= STR_TO_DATE(" . $this->db->escape($awal) . ",'%d-%m-%Y')", null, false);
        if ($akhir !== '' && preg_match('/^\d{2}-\d{2}-\d{4}$/', $akhir)) $this->db->where("STR_TO_DATE(p.tanggal_transaksi,'%d-%m-%Y') <= STR_TO_DATE(" . $this->db->escape($akhir) . ",'%d-%m-%Y')", null, false);
        if ($metode > 0) $this->db->where('p.id_metode_pembayaran', $metode);
        if ($status !== '') $this->db->where('p.status_transaksi', $status);
        return $this->db->order_by("STR_TO_DATE(CONCAT(p.tanggal_transaksi,' ',p.waktu_transaksi),'%d-%m-%Y %H:%i:%s')", 'DESC', false)->order_by('p.id', 'DESC')->limit(2000)->get()->result_array();
    }

    public function detail($id)
    {
        $p = $this->db->where('id', $id)->get('tagihan_pembayaran')->row_array();
        if (!$p) return array('result' => 'false', 'message' => 'Transaksi tidak ditemukan.');
        $siswa = $this->db->query(
            "SELECT s.*,
                    w.nama_wali,
                    w.hubungan AS hubungan_wali,
                    w.no_telp AS no_telp_wali
             FROM siswa s
             LEFT JOIN wali_siswa_detail wd ON wd.id=(
                 SELECT wd2.id
                 FROM wali_siswa_detail wd2
                 JOIN wali_siswa w2 ON w2.id=wd2.id_wali_siswa
                 WHERE wd2.id_siswa=s.id
                   AND UPPER(wd2.status_aktif)='AKTIF'
                   AND UPPER(w2.status_aktif)='AKTIF'
                 ORDER BY (UPPER(wd2.status_utama)='IYA') DESC, wd2.id DESC
                 LIMIT 1
             )
             LEFT JOIN wali_siswa w ON w.id=wd.id_wali_siswa
             WHERE s.id=?
             LIMIT 1",
            array((int)$p['id_siswa'])
        )->row_array();
        return array('result' => 'true', 'header' => $p, 'detail' => $this->db->where('id_pembayaran', $id)->order_by('id')->get('tagihan_pembayaran_detail')->result_array(), 'pembatalan' => $this->db->where('id_pembayaran', $id)->get('tagihan_pembatalan_transaksi')->row_array(), 'siswa' => $siswa);
    }

    public function batalkan()
    {
        $id = (int)$this->input->post('id');
        $alasan = trim((string)$this->input->post('alasan', true));
        if ($id <= 0 || $alasan === '') return array('result' => 'false', 'message' => 'Transaksi dan alasan pembatalan wajib diisi.');
        $this->db->trans_begin();
        $p = $this->db->query('SELECT * FROM tagihan_pembayaran WHERE id=? FOR UPDATE', array($id))->row_array();
        if (!$p) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi tidak ditemukan.');
        }
        if ($p['status_transaksi'] === 'Dibatalkan' || $this->db->where('id_pembayaran', $id)->count_all_results('tagihan_pembatalan_transaksi') > 0) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi sudah dibatalkan sebelumnya.');
        }
        $details = $this->db->query("SELECT * FROM tagihan_pembayaran_detail WHERE id_pembayaran=? AND status_detail='Aktif' FOR UPDATE", array($id))->result_array();
        if (!count($details)) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Detail pembayaran aktif tidak ditemukan.');
        }
        foreach ($details as $d) {
            $bill = $this->db->query('SELECT * FROM tagihan_siswa WHERE id=? FOR UPDATE', array((int)$d['id_tagihan_siswa']))->row_array();
            if (!$bill) continue;
            $newPaid = max(0, (float)$bill['nominal_dibayar'] - (float)$d['nominal_bayar']);
            $newSisa = max(0, (float)$bill['nominal_tagihan'] - $newPaid);
            $newStatus = $newPaid <= 0 ? 'Belum Dibayar' : ($newSisa <= 0 ? 'Lunas' : 'Dibayar Sebagian');
            if ($bill['status_pembayaran'] === 'Dibebaskan') $newStatus = 'Dibebaskan';
            $this->db->where('id', $bill['id'])->update('tagihan_siswa', array('nominal_dibayar' => (string)$newPaid, 'sisa_tagihan' => (string)$newSisa, 'status_pembayaran' => $newStatus, 'tanggal_update' => date('d-m-Y'), 'waktu_update' => date('H:i:s')));
            $this->db->where('id', $d['id'])->update('tagihan_pembayaran_detail', array('status_detail' => 'Dibatalkan'));
            // Reversal penerimaan tanpa menghapus jurnal asli.
            // Nominal negatif menetralkan posting positif pada laporan sum(jumlah).
            $keyAsli = 'SPP:' . (int)$d['id'];
            $posting = $this->db->where('idempotency_key', $keyAsli)->where('sumber_transaksi', 'SPP')->get('pemasukan')->row_array();
            if ($posting) {
                $keyBatal = 'SPP-CANCEL:' . (int)$d['id'];
                if ($this->db->where('idempotency_key', $keyBatal)->count_all_results('pemasukan')) {
                    $this->db->trans_rollback();
                    return array('result' => 'false', 'message' => 'Jurnal koreksi sudah ada. Pembatalan dihentikan agar tidak ganda.');
                }
                $this->db->insert('pemasukan', array(
                    'id_pegawai' => (string)$this->app_user_id(),
                    'tanggal_input' => date('d-m-Y'),
                    'bulan' => date('m'),
                    'tahun' => date('Y'),
                    'id_kode_akun' => $posting['id_kode_akun'],
                    'keterangan' => 'Koreksi pembatalan ' . $p['no_transaksi'] . ' (pemasukan #' . $posting['id'] . ')',
                    'simpan_ke' => $posting['simpan_ke'],
                    'jumlah' => (string)(0 - (float)$posting['jumlah']),
                    'tanggal' => date('d-m-Y'),
                    'sumber_transaksi' => 'SPP_CANCEL',
                    'id_sumber' => (int)$d['id'],
                    'no_referensi' => $p['no_transaksi'],
                    'idempotency_key' => $keyBatal
                ));
            }
        }
        $this->db->where('id', $id)->update('tagihan_pembayaran', array('status_transaksi' => 'Dibatalkan', 'keterangan' => trim(($p['keterangan'] ?: '') . ' | Pembatalan: ' . $alasan)));
        $this->db->insert('tagihan_pembatalan_transaksi', array('id_pembayaran' => $id, 'no_transaksi' => $p['no_transaksi'], 'id_siswa' => $p['id_siswa'], 'nis' => $p['nis'], 'nama_siswa' => $p['nama_siswa'], 'total_pembayaran' => $p['total_pembayaran'], 'alasan_pembatalan' => $alasan, 'tanggal_transaksi_asli' => $p['tanggal_transaksi'], 'waktu_transaksi_asli' => $p['waktu_transaksi'], 'id_user_transaksi' => $p['id_user'], 'nama_user_transaksi' => $p['nama_user'], 'tanggal_pembatalan' => date('d-m-Y'), 'waktu_pembatalan' => date('H:i:s'), 'id_user_pembatalan' => $this->app_user_id(), 'nama_user_pembatalan' => $this->app_user_name(), 'ip_address' => $this->input->ip_address()));
        $this->log('Batalkan Pembayaran', 'Transaksi', 'Batal', 'tagihan_pembayaran', $id, $p['no_transaksi'], $alasan, $p, array('status_transaksi' => 'Dibatalkan'));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Pembatalan gagal. Tidak ada perubahan yang disimpan.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Pembayaran berhasil dibatalkan dan tagihan siswa dipulihkan.');
    }

    private function app_user_id()
    {
        $u = $this->session->userdata('admin');
        return is_array($u) && isset($u['id_user']) ? (int)$u['id_user'] : 0;
    }
    private function app_user_name()
    {
        $u = $this->session->userdata('admin');
        return is_array($u) && !empty($u['nama_lengkap']) ? $u['nama_lengkap'] : 'Administrator';
    }
    private function log($jenis, $modul, $aksi, $table, $id, $nomor, $ket, $before = null, $after = null)
    {
        $this->db->insert('tagihan_log_aktivitas', array('jenis_aktivitas' => $jenis, 'modul' => $modul, 'aksi' => $aksi, 'nama_tabel' => $table, 'id_referensi' => (string)$id, 'nomor_referensi' => $nomor, 'keterangan' => $ket, 'data_sebelum' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE), 'data_sesudah' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE), 'ip_address' => $this->input->ip_address(), 'user_agent' => $this->input->user_agent(), 'tanggal' => date('d-m-Y'), 'waktu' => date('H:i:s'), 'id_user' => $this->app_user_id(), 'nama_user' => $this->app_user_name()));
    }
}
