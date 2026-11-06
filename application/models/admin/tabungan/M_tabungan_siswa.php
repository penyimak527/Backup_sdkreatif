<?php defined('BASEPATH') OR exit('No direct script access allowed');

class M_tabungan_siswa extends CI_Model
{
    public function kasbank()
    {
        return $this->db->order_by('kategori')->order_by('keterangan')->get('kasbank')->result_array();
    }

    public function metode_list()
    {
        return $this->db->where('status', 'Aktif')
            ->order_by('urutan')
            ->order_by('nama_metode')
            ->get('tagihan_metode_pembayaran')->result_array();
    }

    public function kelas_list()
    {
        return $this->db->select('id,id_kelas,nama_kelas,id_periode,periode')
            ->order_by('nama_kelas')->get('kelas_setting')->result_array();
    }

    public function cari($q, $page = 1, $limit = 10)
    {
        $q = trim((string) $q);
        $page = max(1, (int) $page);
        $limit = in_array((int) $limit, array(10, 25, 50), true) ? (int) $limit : 10;
        if (strlen($q) < 2) {
            return array('data' => array(), 'meta' => array('page' => 1, 'limit' => $limit, 'total' => 0, 'total_pages' => 1));
        }

        $like = '%' . $q . '%';
        $count = $this->db->query(
            "SELECT COUNT(*) total FROM siswa s WHERE (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?) AND UPPER(s.status_siswa)='AKTIF'",
            array($like, $like, $like)
        )->row_array();
        $total = (int) ($count['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $limit;

        $rows = $this->db->query(
            "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,s.status_siswa,
                    ks.id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,k.periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks ON ks.id=(SELECT MAX(ks2.id) FROM kelas_siswa ks2 WHERE CAST(ks2.id_siswa AS UNSIGNED)=s.id AND UPPER(ks2.status_aktif)='AKTIF')
             LEFT JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             WHERE (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?) AND UPPER(s.status_siswa)='AKTIF'
             ORDER BY s.nama_lengkap,s.id LIMIT ? OFFSET ?",
            array($like, $like, $like, $limit, $offset)
        )->result_array();

        return array('data' => $rows, 'meta' => array(
            'page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $totalPages
        ));
    }

    public function detail($id)
    {
        $siswa = $this->student((int) $id);
        if (!$siswa) {
            return array('result' => 'false', 'message' => 'Siswa tidak ditemukan.');
        }

        $items = $this->db->query(
            "SELECT * FROM (
                SELECT 'Setoran' jenis,id,no_transaksi,tanggal_transaksi,nominal,nama_kasbank,metode_pembayaran metode,catatan,created_at,id_user,nama_user,status_transaksi,id_user_batal,nama_user_batal,alasan_batal,tanggal_batal,waktu_batal FROM tabungan_siswa_setoran WHERE id_siswa=?
                UNION ALL
                SELECT 'Penarikan' jenis,id,no_transaksi,tanggal_transaksi,nominal,nama_kasbank,metode_pembayaran metode,catatan,created_at,id_user,nama_user,status_transaksi,id_user_batal,nama_user_batal,alasan_batal,tanggal_batal,waktu_batal FROM tabungan_siswa_penarikan WHERE id_siswa=?
             ) x ORDER BY COALESCE(STR_TO_DATE(REPLACE(tanggal_transaksi,' ',''),'%d-%m-%Y'),STR_TO_DATE(REPLACE(tanggal_transaksi,' ',''),'%Y-%m-%d')) DESC,created_at DESC,id DESC LIMIT 300",
            array((int) $id, (int) $id)
        )->result_array();

        return array(
            'result' => 'true',
            'siswa' => $siswa,
            'rekap' => $this->amounts((int) $id),
            'riwayat' => $items
        );
    }

    public function detail_transaksi($jenis, $id)
    {
        $row = $this->transaksi($jenis, $id);
        if (!$row) {
            return array('result' => 'false', 'message' => 'Transaksi tabungan tidak ditemukan.');
        }
        return array('result' => 'true', 'data' => $row, 'saldo' => $this->amounts((int) $row['id_siswa']));
    }

    // Setoran dan penarikan memakai validasi dan pola transaksi yang sama.
    // Perubahan berikut hanya penataan kode; aturan token, saldo, dan rollback tetap.
    public function simpan($p, $u)
    {
        $id = (int) ($p['id_siswa'] ?? 0);
        $jenis = $this->jenis($p['jenis'] ?? '');
        $amount = $this->money($p['nominal'] ?? 0);
        $idMetode = (int) ($p['id_metode_pembayaran'] ?? 0);
        $tanggalInput = trim((string) ($p['tanggal'] ?? ''));
        $token = trim((string) ($p['token'] ?? ''));
        $catatan = trim((string) ($p['catatan'] ?? ''));

        $valid = $this->validate_transaction($jenis, $id, $amount, $idMetode, $tanggalInput, $token, true);
        if ($valid['result'] !== 'true') {
            return $valid;
        }

        $tanggal = $tanggalInput;
        $table = $this->table($jenis);
        $this->db->trans_begin();

        $siswa = $this->lock_student($id);
        if (!$siswa) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Siswa tidak ditemukan.');
        }

        $exists = $this->db->where('token_transaksi', $token)->count_all_results('tabungan_siswa_setoran')
            + $this->db->where('token_transaksi', $token)->count_all_results('tabungan_siswa_penarikan');
        if ($exists) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi ini sudah pernah diproses.');
        }

        $saldo = $this->amounts($id);
        if ($jenis === 'Penarikan' && $amount > $saldo['saldo']) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Nominal penarikan melebihi saldo tabungan siswa.');
        }

        $code = $this->next_code($jenis === 'Setoran' ? 'TBS' : 'TBT', $table, $tanggal);
        $payload = $this->payload($siswa, $u, $tanggal, $valid['metode'], $amount, $catatan) + array(
            'no_transaksi' => $code,
            'token_transaksi' => $token,
            'status_transaksi' => 'Aktif',
            'created_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert($table, $payload);
        $newId = (int) $this->db->insert_id();
        if ($this->db->trans_status() === FALSE || $newId <= 0) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi tabungan gagal disimpan.');
        }

        $this->db->trans_commit();
        $saldoBaru = $this->amounts($id);
        return array(
            'result' => 'true',
            'message' => $jenis . ' tabungan berhasil dicatat.',
            'id_transaksi' => $newId,
            'jenis' => $jenis,
            'no_transaksi' => $code,
            'saldo' => $saldoBaru['saldo']
        );
    }

    public function edit($p, $u)
    {
        $jenis = $this->jenis($p['jenis'] ?? '');
        $id = (int) ($p['id'] ?? 0);
        if (!$jenis || $id < 1) {
            return array('result' => 'false', 'message' => 'Transaksi tidak valid.');
        }

        $table = $this->table($jenis);
        $this->db->trans_begin();
        $row = $this->db->query("SELECT * FROM {$table} WHERE id=? LIMIT 1 FOR UPDATE", array($id))->row_array();
        if (!$row) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi tidak ditemukan.');
        }
        if (($row['status_transaksi'] ?? 'Aktif') !== 'Aktif') {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi yang sudah dibatalkan tidak dapat diedit.');
        }

        $amount = $this->money($p['nominal'] ?? 0);
        $idMetode = (int) ($p['id_metode_pembayaran'] ?? 0);
        $tanggalInput = trim((string) ($p['tanggal'] ?? ''));
        $catatan = trim((string) ($p['catatan'] ?? ''));
        $valid = $this->validate_transaction($jenis, (int) $row['id_siswa'], $amount, $idMetode, $tanggalInput, '', false);
        if ($valid['result'] !== 'true') {
            $this->db->trans_rollback();
            return $valid;
        }

        $tanggal = $tanggalInput;
        $this->lock_student((int) $row['id_siswa']);
        $saldo = $this->amounts((int) $row['id_siswa']);
        $saldoBaru = $jenis === 'Setoran'
            ? $saldo['saldo'] - (float) $row['nominal'] + $amount
            : $saldo['saldo'] + (float) $row['nominal'] - $amount;
        if ($saldoBaru < 0) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Perubahan nominal membuat saldo menjadi negatif.');
        }

        $this->db->where('id', $id)->update($table, array(
            'tanggal_transaksi' => $tanggal,
            'id_metode_pembayaran' => (int) $valid['metode']['id'],
            'metode_pembayaran' => $valid['metode']['nama_metode'],
            // Data Kas/Bank transaksi lama tidak diubah saat edit.
            'nominal' => (string) (int) $amount,
            'catatan' => mb_substr($catatan, 0, 500)
        ));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Perubahan transaksi gagal disimpan.');
        }

        $this->db->trans_commit();
        return array(
            'result' => 'true',
            'message' => 'Transaksi ' . $row['no_transaksi'] . ' berhasil diperbarui.',
            'saldo' => $saldoBaru
        );
    }

    public function batalkan($p, $u)
    {
        $jenis = $this->jenis($p['jenis'] ?? '');
        $id = (int) ($p['id'] ?? 0);
        $alasan = trim((string) ($p['alasan'] ?? ''));
        if (!$jenis || $id < 1) {
            return array('result' => 'false', 'message' => 'Transaksi tabungan tidak valid.');
        }
        if (mb_strlen($alasan) < 3) {
            return array('result' => 'false', 'message' => 'Alasan pembatalan wajib diisi minimal 3 karakter.');
        }

        $table = $this->table($jenis);
        $this->db->trans_begin();
        $row = $this->db->query("SELECT * FROM {$table} WHERE id=? LIMIT 1 FOR UPDATE", array($id))->row_array();
        if (!$row) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi tabungan tidak ditemukan.');
        }
        if (($row['status_transaksi'] ?? 'Aktif') === 'Dibatalkan') {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Transaksi ini sudah dibatalkan sebelumnya.');
        }

        $this->lock_student((int) $row['id_siswa']);
        $saldo = $this->amounts((int) $row['id_siswa']);
        if ($jenis === 'Setoran' && ((float) $saldo['saldo'] - (float) $row['nominal']) < 0) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Setoran tidak dapat dibatalkan karena dana tersebut sudah digunakan untuk penarikan.');
        }

        $this->db->where('id', $id)->update($table, array(
            'status_transaksi' => 'Dibatalkan',
            'id_user_batal' => $this->user_id($u),
            'nama_user_batal' => $this->user_name($u),
            'alasan_batal' => mb_substr($alasan, 0, 500),
            'tanggal_batal' => date('d-m-Y'),
            'waktu_batal' => date('H:i:s')
        ));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Pembatalan transaksi tabungan gagal diproses.');
        }

        $this->db->trans_commit();
        return array(
            'result' => 'true',
            'message' => $jenis . ' ' . $row['no_transaksi'] . ' berhasil dibatalkan.',
            'saldo' => $this->amounts((int) $row['id_siswa'])['saldo']
        );
    }


    public function transaksi($jenis,$id)
    {
        $jenis=$this->jenis($jenis); if(!$jenis)return null; $row=$this->db->select('*, metode_pembayaran AS metode',false)->where('id',(int)$id)->get($this->table($jenis))->row_array(); if(!$row)return null; $row['jenis']=$jenis; return $row;
    }

    public function whatsapp_preview($jenis, $id)
    {
        $row = $this->transaksi($jenis, $id);
        if (!$row) {
            return array('result' => 'false', 'message' => 'Transaksi tabungan tidak ditemukan.');
        }

        if (($row['status_transaksi'] ?? 'Aktif') !== 'Aktif') {
            return array('result' => 'false', 'message' => 'Transaksi yang dibatalkan tidak dapat dikirim melalui WhatsApp.');
        }

        // Nomor wali boleh kosong: petugas dapat mengisinya di modal WhatsApp.
        $wali = $this->wali_by_siswa((int) $row['id_siswa']);
        $namaWali = trim((string) ($wali['nama_wali'] ?? '')) ?: 'Bapak/Ibu Wali';
        $saldo = $this->amounts((int) $row['id_siswa']);
        $templateType = $row['jenis'] === 'Setoran' ? 'Setoran Tabungan' : 'Penarikan Tabungan';

        // Menggunakan template yang sudah disediakan menu Pengaturan WhatsApp.
        $template = $this->db
            ->where('jenis_template', $templateType)
            ->where('status', 'Aktif')
            ->order_by("status_default='Ya'", 'DESC', false)
            ->order_by('id', 'DESC')
            ->get('spp_template_whatsapp')
            ->row_array();

        $defaultSetoran = 'Yth. Bapak/Ibu {nama_wali}, setoran tabungan {nama_siswa} sebesar {nominal} pada {tanggal} telah tercatat. Saldo saat ini {saldo}.';
        $defaultPenarikan = 'Yth. Bapak/Ibu {nama_wali}, penarikan tabungan {nama_siswa} sebesar {nominal} pada {tanggal} telah tercatat. Saldo saat ini {saldo}.';
        $fallback = $row['jenis'] === 'Setoran' ? $defaultSetoran : $defaultPenarikan;
        $isiTemplate = $template['isi_template'] ?? $fallback;

        return array(
            'result' => 'true',
            'data' => array(
                'jenis' => $row['jenis'],
                'id' => $row['id'],
                'no_transaksi' => $row['no_transaksi'],
                'wali' => array(
                    'nama' => $namaWali,
                    'hubungan' => $wali['hubungan'] ?? 'Wali',
                    'nomor' => $wali['no_telp'] ?? ''
                ),
                'nama_template' => $template['nama_template'] ?? 'Default',
                'template_raw' => $isiTemplate,
                'pesan' => $this->wa_message($isiTemplate, $row, $namaWali, $saldo['saldo'])
            )
        );
    }

        public function siapkan_whatsapp($p, $u)
    {
        $jenis = $this->jenis($p['jenis'] ?? '');
        $id = (int) ($p['id'] ?? 0);
        $row = $this->transaksi($jenis, $id);

        if (!$row) {
            return array('result' => 'false', 'message' => 'Transaksi tabungan tidak ditemukan.');
        }
        if ($row['status_transaksi'] !== 'Aktif') {
            return array('result' => 'false', 'message' => 'Transaksi dibatalkan tidak dapat dikirim.');
        }

        $wali = $this->wali_by_siswa((int) $row['id_siswa']);
        $source = trim((string) ($p['sumber_nomor'] ?? 'Orang Tua'));
        if (!in_array($source, array('Orang Tua', 'Nomor Lain'), true)) {
            $source = 'Orang Tua';
        }

        $name = trim((string) ($p['nama_penerima'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($wali['nama_wali'] ?? '')) ?: 'Bapak/Ibu Wali';
        }

        $hubungan = trim((string) ($p['hubungan_penerima'] ?? ''));
        if ($hubungan === '') {
            $hubungan = $source === 'Nomor Lain' ? 'Lainnya' : (trim((string) ($wali['hubungan'] ?? '')) ?: 'Wali');
        }

        $rawPhone = trim((string) ($p['nomor_whatsapp'] ?? ''));
        if ($rawPhone === '' && $source === 'Orang Tua') {
            $rawPhone = (string) ($wali['no_telp'] ?? '');
        }
        $phone = preg_replace('/\D+/', '', $rawPhone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62' || strlen($phone) < 10 || strlen($phone) > 16) {
            return array('result' => 'false', 'message' => 'Nomor WhatsApp tidak valid. Gunakan nomor Indonesia yang aktif.');
        }

        // Pesan hasil edit dipakai untuk kiriman ini saja, tidak mengubah template master.
        $message = trim((string) ($p['pesan'] ?? ''));
        if ($message === '') {
            $preview = $this->whatsapp_preview($jenis, $id);
            if ($preview['result'] !== 'true') {
                return $preview;
            }
            $saldo = $this->amounts((int) $row['id_siswa']);
            $message = $this->wa_message(
                $preview['data']['template_raw'] ?? $preview['data']['pesan'],
                $row,
                $name,
                $saldo['saldo']
            );
        }

        $this->db->insert('spp_riwayat_whatsapp', array(
            'jenis_kirim' => $jenis . ' Tabungan',
            'id_referensi' => $id,
            'nomor_referensi' => $row['no_transaksi'],
            'id_siswa' => $row['id_siswa'],
            'nama_siswa' => $row['nama_siswa'],
            'nama_penerima' => $name,
            'hubungan_penerima' => $hubungan,
            'nomor_whatsapp' => $phone,
            'isi_pesan' => $message,
            'metode_kirim' => 'Tautan',
            'status_kirim' => 'Disiapkan',
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $this->user_id($u),
            'nama_user' => $this->user_name($u)
        ));

        if (!$this->db->affected_rows()) {
            return array('result' => 'false', 'message' => 'Gagal menyiapkan WhatsApp.');
        }
        return array(
            'result' => 'true',
            'message' => 'WhatsApp siap dibuka.',
            'url' => 'https://wa.me/' . $phone . '?text=' . rawurlencode($message)
        );
    }

        private function wa_message($text, $row, $name, $saldo)
    {
        return strtr((string) $text, array(
            '{nama_wali}' => $name,
            '{nama_siswa}' => $row['nama_siswa'],
            '{kelas}' => $row['nama_kelas'],
            '{tanggal}' => $this->date_id($row['tanggal_transaksi']),
            '{no_transaksi}' => $row['no_transaksi'],
            '{nominal}' => $this->rupiah($row['nominal']),
            '{total_bayar}' => $this->rupiah($row['nominal']),
            '{saldo}' => $this->rupiah($saldo),
            '{metode_pembayaran}' => $row['metode'] ?? '',
            '{nama_sekolah}' => $this->config->item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah',
            '{nama_petugas}' => $row['nama_user']
        ));
    }

    private function validate_transaction($jenis, $id, $amount, $idMetode, $tanggal, $token, $requireToken)
    {
        if (!$jenis || $id < 1 || $amount <= 0 || $amount > 999999999999) {
            return array('result' => 'false', 'message' => 'Jenis transaksi, siswa, dan nominal harus valid.');
        }
        if ($requireToken && !preg_match('/^[a-f0-9]{32}$/', $token)) {
            return array('result' => 'false', 'message' => 'Token transaksi tidak valid. Muat ulang halaman.');
        }
        $metode = $idMetode > 0 ? $this->db
            ->where('id', $idMetode)
            ->where('status', 'Aktif')
            ->get('tagihan_metode_pembayaran')->row_array() : null;
        if (!$metode || trim((string) ($metode['nama_metode'] ?? '')) === '' || mb_strlen((string) $metode['nama_metode']) > 50) {
            return array('result' => 'false', 'message' => 'Metode pembayaran tidak valid atau tidak aktif.');
        }
        if (!$this->valid_date($tanggal)) {
            return array('result' => 'false', 'message' => 'Tanggal transaksi tidak valid.');
        }

        // Tabungan tidak lagi mengharuskan pilihan Kas/Bank.
        return array('result' => 'true', 'metode' => $metode);
    }

        private function payload($s, $u, $tanggal, $metode, $amount, $catatan)
    {
        return array(
            'tanggal_transaksi' => $tanggal,
            'id_siswa' => (int) $s['id'],
            'nis' => (string) $s['nis'],
            'nisn' => (string) ($s['nisn'] ?? ''),
            'nama_siswa' => (string) $s['nama_lengkap'],
            'id_kelas_setting' => (int) ($s['id_kelas_setting'] ?? 0),
            'id_kelas' => (int) ($s['id_kelas'] ?? 0),
            'nama_kelas' => (string) ($s['nama_kelas'] ?? '-'),
            'id_periode' => (int) ($s['id_periode'] ?? 0),
            'periode' => (string) ($s['periode'] ?? '-'),
            'id_metode_pembayaran' => (int) $metode['id'],
            'metode_pembayaran' => $metode['nama_metode'],
            'id_kasbank' => NULL,
            'nama_kasbank' => NULL,
            // VARCHAR menyimpan nilai angka murni (misal '50000', bukan '50.000').
            'nominal' => (string) (int) $amount,
            'catatan' => mb_substr($catatan, 0, 500),
            'id_user' => $this->user_id($u),
            'nama_user' => $this->user_name($u)
        );
    }

    private function student($id)
    {
        return $this->db->query(
            "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,ks.id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,k.periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks ON ks.id=(SELECT MAX(ks2.id) FROM kelas_siswa ks2 WHERE CAST(ks2.id_siswa AS UNSIGNED)=s.id AND UPPER(ks2.status_aktif)='AKTIF')
             LEFT JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             WHERE s.id=? LIMIT 1",
            array((int) $id)
        )->row_array();
    }

    private function wali_by_siswa($id)
    {
        return $this->db->query(
            "SELECT w.id,w.nama_wali,w.hubungan,w.no_telp
             FROM wali_siswa_detail d
             JOIN wali_siswa w ON w.id=d.id_wali_siswa
             WHERE d.id_siswa=?
               AND UPPER(d.status_aktif)='AKTIF'
               AND UPPER(w.status_aktif)='AKTIF'
             ORDER BY (UPPER(d.status_utama)='IYA') DESC,d.id DESC LIMIT 1",
            array((int) $id)
        )->row_array();
    }

        private function lock_student($id)
    {
        return $this->db->query(
            "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,ks.id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,k.periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks ON ks.id=(SELECT MAX(ks2.id) FROM kelas_siswa ks2 WHERE CAST(ks2.id_siswa AS UNSIGNED)=s.id AND UPPER(ks2.status_aktif)='AKTIF')
             LEFT JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             WHERE s.id=? LIMIT 1 FOR UPDATE",
            array((int) $id)
        )->row_array();
    }

    // Saldo tidak disimpan sebagai tabel tersendiri; selalu dihitung dari dua tabel transaksi aktif.
    private function amounts($id)
    {
        $setoran = $this->db->query(
            "SELECT COALESCE(SUM(nominal),0) v FROM tabungan_siswa_setoran WHERE id_siswa=? AND status_transaksi='Aktif'",
            array((int) $id)
        )->row_array();
        $penarikan = $this->db->query(
            "SELECT COALESCE(SUM(nominal),0) v FROM tabungan_siswa_penarikan WHERE id_siswa=? AND status_transaksi='Aktif'",
            array((int) $id)
        )->row_array();

        $totalSetoran = (float) ($setoran['v'] ?? 0);
        $totalPenarikan = (float) ($penarikan['v'] ?? 0);
        return array(
            'setoran' => $totalSetoran,
            'penarikan' => $totalPenarikan,
            'saldo' => $totalSetoran - $totalPenarikan
        );
    }

        private function next_code($prefix, $table, $tanggal)
    {
        $clean = str_replace(' ', '', (string) $tanggal);
        $date = DateTime::createFromFormat('!d-m-Y', $clean);
        if (!$date) {
            $date = DateTime::createFromFormat('!Y-m-d', $clean);
        }
        $tanggalKode = $date ? $date->format('Ymd') : date('Ymd');
        $base = $prefix . '/' . $tanggalKode . '/';
        $row = $this->db->select('no_transaksi')
            ->like('no_transaksi', $base, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($table)->row_array();
        $next = 1;
        if ($row && preg_match('/(\d+)$/', (string) $row['no_transaksi'], $match)) {
            $next = (int) $match[1] + 1;
        }
        return $base . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function table($jenis)
    {
        return $jenis === 'Setoran' ? 'tabungan_siswa_setoran' : 'tabungan_siswa_penarikan';
    }

    private function jenis($jenis)
    {
        $jenis = ucfirst(strtolower(trim((string) $jenis)));
        return in_array($jenis, array('Setoran', 'Penarikan'), true) ? $jenis : '';
    }

    private function money($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $raw = preg_replace('/[^0-9]/', '', (string) $value);
        return $raw === '' ? 0 : (float) $raw;
    }

    private function valid_date($date)
    {
        if ($date === '') {
            return false;
        }
        $parsed = DateTime::createFromFormat('!d-m-Y', $date);
        return $parsed && $parsed->format('d-m-Y') === $date;
    }

    private function rupiah($nominal)
    {
        return 'Rp' . number_format((float) $nominal, 0, ',', '.');
    }

    private function date_id($date)
    {
        $clean = str_replace(' ', '', (string) $date);
        $parsed = DateTime::createFromFormat('!d-m-Y', $clean);
        if (!$parsed) {
            $parsed = DateTime::createFromFormat('!Y-m-d', $clean);
        }
        return $parsed ? $parsed->format('d-m-Y') : $clean;
    }

    private function user_id($u)
    {
        return (int) ($u['id_user'] ?? $u['id'] ?? 0);
    }

    private function user_name($u)
    {
        return (string) ($u['nama_lengkap'] ?? $u['nama'] ?? $u['username'] ?? 'Administrator');
    }
}
