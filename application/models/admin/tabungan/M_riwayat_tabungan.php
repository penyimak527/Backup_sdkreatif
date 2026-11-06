<?php defined('BASEPATH') OR exit('No direct script access allowed');

class M_riwayat_tabungan extends CI_Model
{

    public function kelas_list()
    {
        return $this->db->select('id,id_kelas,nama_kelas,id_periode,periode')
            ->order_by('nama_kelas')->get('kelas_setting')->result_array();
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

    public function riwayat($filter = array(), $paginate = true)
    {
        if (!is_array($filter)) {
            $filter = array();
        }

        $search = trim((string) ($filter['search'] ?? ''));
        $jenis = $this->jenis($filter['jenis'] ?? '');
        $status = trim((string) ($filter['status'] ?? ''));
        $kelas = (int) ($filter['id_kelas_setting'] ?? 0);
        $tanggalMulai = trim((string) ($filter['tanggal_mulai'] ?? ''));
        $tanggalSelesai = trim((string) ($filter['tanggal_selesai'] ?? ''));

        // Seluruh filter berlaku sama pada tabel, ringkasan, cetak, dan Excel.
        $where = ' WHERE 1=1';
        $params = array();
        if ($search !== '') {
            $where .= ' AND (t.nama_siswa LIKE ? OR t.nis LIKE ? OR t.nisn LIKE ? OR t.no_transaksi LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($jenis) {
            $where .= ' AND t.jenis=?';
            $params[] = $jenis;
        }
        if (in_array($status, array('Aktif', 'Dibatalkan'), true)) {
            $where .= ' AND t.status_transaksi=?';
            $params[] = $status;
        }
        if ($kelas > 0) {
            $where .= ' AND t.id_kelas_setting=?';
            $params[] = $kelas;
        }

        $tanggalSql = "COALESCE(STR_TO_DATE(REPLACE(t.tanggal_transaksi,' ',''),'%d-%m-%Y'),STR_TO_DATE(REPLACE(t.tanggal_transaksi,' ',''),'%Y-%m-%d'))";
        if ($this->valid_date($tanggalMulai)) {
            $where .= ' AND ' . $tanggalSql . " >= STR_TO_DATE(?,'%d-%m-%Y')";
            $params[] = $tanggalMulai;
        }
        if ($this->valid_date($tanggalSelesai)) {
            $where .= ' AND ' . $tanggalSql . " <= STR_TO_DATE(?,'%d-%m-%Y')";
            $params[] = $tanggalSelesai;
        }

        // Struktur kolom union tetap sama; sumber saldo hanya dua tabel tabungan.
        $fields = "id,no_transaksi,tanggal_transaksi,id_siswa,nis,nisn,nama_siswa,
            id_kelas_setting,id_kelas,nama_kelas,metode_pembayaran metode,
            id_kasbank,nama_kasbank,nominal,catatan,id_user,nama_user,
            status_transaksi,id_user_batal,nama_user_batal,alasan_batal,
            tanggal_batal,waktu_batal,created_at";
        $base = "FROM (
            SELECT 'Setoran' jenis,{$fields} FROM tabungan_siswa_setoran
            UNION ALL
            SELECT 'Penarikan' jenis,{$fields} FROM tabungan_siswa_penarikan
        ) t" . $where;

        $summarySql = "SELECT COUNT(*) jumlah,
            COALESCE(SUM(CASE WHEN status_transaksi='Aktif' AND jenis='Setoran' THEN nominal ELSE 0 END),0) setoran,
            COALESCE(SUM(CASE WHEN status_transaksi='Aktif' AND jenis='Penarikan' THEN nominal ELSE 0 END),0) penarikan,
            COALESCE(SUM(CASE WHEN status_transaksi='Aktif' THEN 1 ELSE 0 END),0) aktif,
            COALESCE(SUM(CASE WHEN status_transaksi='Dibatalkan' THEN 1 ELSE 0 END),0) dibatalkan ";
        $summary = $this->db->query($summarySql . $base, $params)->row_array();

        $total = (int) ($summary['jumlah'] ?? 0);
        $page = max(1, (int) ($filter['page'] ?? 1));
        $limit = (int) ($filter['limit'] ?? 10);
        if (!in_array($limit, array(10, 25, 50, 100), true)) {
            $limit = 10;
        }
        $offset = ($page - 1) * $limit;

        $sql = 'SELECT t.* ' . $base . " ORDER BY
            COALESCE(STR_TO_DATE(REPLACE(t.tanggal_transaksi,' ',''),'%d-%m-%Y'),STR_TO_DATE(REPLACE(t.tanggal_transaksi,' ',''),'%Y-%m-%d')) DESC,
            t.created_at DESC,t.id DESC";
        $queryParams = $params;
        if ($paginate) {
            $sql .= ' LIMIT ? OFFSET ?';
            $queryParams[] = $limit;
            $queryParams[] = $offset;
        }
        $rows = $this->db->query($sql, $queryParams)->result_array();

        $totalSetoran = (float) ($summary['setoran'] ?? 0);
        $totalPenarikan = (float) ($summary['penarikan'] ?? 0);
        return array(
            'result' => 'true',
            'data' => $rows,
            'summary' => array(
                'setoran' => $totalSetoran,
                'penarikan' => $totalPenarikan,
                'selisih' => $totalSetoran - $totalPenarikan,
                'jumlah' => $total,
                'aktif' => (int) ($summary['aktif'] ?? 0),
                'dibatalkan' => (int) ($summary['dibatalkan'] ?? 0)
            ),
            'meta' => array(
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => max(1, (int) ceil($total / $limit))
            )
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

    private function table($jenis)
    {
        return $jenis === 'Setoran' ? 'tabungan_siswa_setoran' : 'tabungan_siswa_penarikan';
    }

    private function jenis($jenis)
    {
        $jenis = ucfirst(strtolower(trim((string) $jenis)));
        return in_array($jenis, array('Setoran', 'Penarikan'), true) ? $jenis : '';
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
