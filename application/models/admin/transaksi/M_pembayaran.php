<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_pembayaran extends CI_Model
{
    public function metode_list()
    {
        return $this->db->where('status', 'Aktif')->order_by('urutan')->order_by('nama_metode')->get('tagihan_metode_pembayaran')->result_array();
    }

    public function cari_siswa($page = 1, $limit = 10)
    {
        $q = trim((string)$this->input->post('q', true));
        $limit = in_array((int)$limit, array(10, 25, 50, 100), true) ? (int)$limit : 10;
        $page = max(1, (int)$page);
        if (strlen($q) < 2) {
            return array('data' => array(), 'total' => 0, 'page' => 1, 'limit' => $limit, 'total_pages' => 0);
        }

        $like = '%' . $q . '%';
        $params = array($like, $like, $like);
        $total = (int)$this->db->query(
            "SELECT COUNT(*) AS jumlah
             FROM siswa s
             LEFT JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
             WHERE (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?) AND UPPER(s.status_siswa)='AKTIF'",
            $params
        )->row('jumlah');

        $totalPages = (int)ceil($total / $limit);
        $page = $totalPages > 0 ? min($page, $totalPages) : 1;
        $offset = ($page - 1) * $limit;

        $rows = $this->db->query(
            "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,s.status_siswa,s.status_aktif,
                    w.nama_wali,w.hubungan AS hubungan_wali,w.no_telp AS no_telp_wali,
                    ks.id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,ta.periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
             LEFT JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             LEFT JOIN master_tahun_ajaran ta ON ta.id=CAST(k.id_periode AS UNSIGNED)
             LEFT JOIN wali_siswa_detail wd ON wd.id=(SELECT wd2.id FROM wali_siswa_detail wd2 JOIN wali_siswa w2 ON w2.id=wd2.id_wali_siswa WHERE wd2.id_siswa=s.id AND UPPER(wd2.status_aktif)='AKTIF' AND UPPER(w2.status_aktif)='AKTIF' ORDER BY (UPPER(wd2.status_utama)='IYA') DESC,wd2.id DESC LIMIT 1)
             LEFT JOIN wali_siswa w ON w.id=wd.id_wali_siswa
             WHERE (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)
             AND UPPER(s.status_siswa)='AKTIF'
             ORDER BY s.nama_lengkap, s.id, ks.id
             LIMIT {$limit} OFFSET {$offset}",
            $params
        )->result_array();

        return array('data' => $rows, 'total' => $total, 'page' => $page, 'limit' => $limit, 'total_pages' => $totalPages);
    }

    public function siswa_by_id($id)
    {
        $row = $this->db->query(
            "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,s.status_siswa,s.status_aktif,
                w.nama_wali,w.hubungan AS hubungan_wali,w.no_telp AS no_telp_wali,
                ks.id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,ta.periode
             FROM siswa s LEFT JOIN kelas_siswa ks
                 ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
             LEFT JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             LEFT JOIN master_tahun_ajaran ta ON ta.id=CAST(k.id_periode AS UNSIGNED)
             LEFT JOIN wali_siswa_detail wd ON wd.id=(SELECT wd2.id FROM wali_siswa_detail wd2 JOIN wali_siswa w2 ON w2.id=wd2.id_wali_siswa WHERE wd2.id_siswa=s.id AND UPPER(wd2.status_aktif)='AKTIF' AND UPPER(w2.status_aktif)='AKTIF' ORDER BY (UPPER(wd2.status_utama)='IYA') DESC,wd2.id DESC LIMIT 1)
             LEFT JOIN wali_siswa w ON w.id=wd.id_wali_siswa
             WHERE s.id=? LIMIT 1",
            array((int)$id)
        )->row_array();
        return $row ? array('result' => 'true', 'siswa' => $row) : $this->response(false, 'Siswa tidak ditemukan.');
    }

    public function tagihan_siswa()
    {
        $id = (int)$this->input->post('id_siswa');
        $student = $this->siswa_by_id($id);
        if (($student['result'] ?? '') !== 'true') return $student;
        $rows = $this->db->query(
            "SELECT * FROM tagihan_siswa WHERE id_siswa=? AND status_tagihan='Aktif'
               AND status_pembayaran NOT IN ('Lunas','Dibebaskan','Dibatalkan')
               AND CAST(sisa_tagihan AS DECIMAL(18,2)) > 0
             ORDER BY tahun ASC, bulan ASC, id ASC",
            array($id)
        )->result_array();
        $now = new DateTime('today');
        $periodKey = (int)$now->format('Y') * 100 + (int)$now->format('n');
        $current = array();
        $future = array();
        $total = 0;
        foreach ($rows as $r) {
            $year = (int)($r['tahun'] ?? 0);
            $month = (int)($r['bulan'] ?? 0);
            $key = ($year > 0 && $month >= 1 && $month <= 12) ? $year * 100 + $month : 0;
            $due = DateTime::createFromFormat('!d-m-Y', trim((string)($r['tanggal_jatuh_tempo'] ?? '')));
            $validDue = $due instanceof DateTime;
            // Tagihan non-tunggakan yang telah lewat jatuh tempo tidak ditawarkan untuk dibayar.
            // Data aslinya tetap ada untuk monitoring dan histori.
            if ($this->tagihan_non_tunggakan_lewat_tempo($r, $now, $due)) continue;
            $overdue = $validDue && $due < $now && ($r['dianggap_tunggakan'] ?? 'Ya') === 'Ya';
            // Tahun lama tetap dapat dibayar walau kolom jatuh tempo kosong.
            $oldPeriod = $key > 0 && $key < $periodKey;
            $isFuture = $key > $periodKey && $key > 0;
            $r['kategori_pembayaran'] = $isFuture ? 'future' : ($overdue || ($oldPeriod && ($r['dianggap_tunggakan'] ?? 'Ya') === 'Ya') ? 'overdue' : (($r['status_pembayaran'] ?? '') === 'Dibayar Sebagian' ? 'partial' : 'unpaid'));
            $r['is_tunggakan'] = ($r['kategori_pembayaran'] === 'overdue') ? 'Ya' : 'Tidak';
            $r['is_bulan_ini'] = $key === $periodKey ? 'Ya' : 'Tidak';
            $r['is_bulan_sebelumnya'] = $oldPeriod ? 'Ya' : 'Tidak';
            $r['is_mendatang'] = $isFuture ? 'Ya' : 'Tidak';
            $r['tanggal_jatuh_tempo_iso'] = $validDue ? $due->format('Y-m-d') : '';
            if ($isFuture) $future[] = $r;
            else {
                $current[] = $r;
                $total += (float)$r['sisa_tagihan'];
            }
        }
        $active = $this->db->select('periode')->where('status', 'Aktif')->limit(1)->get('master_tahun_ajaran')->row_array();
        return array(
            'result' => 'true',
            'siswa' => $student['siswa'],
            'periode_aktif' => $active['periode'] ?? ($student['siswa']['periode'] ?? ''),
            'tagihan' => $current,
            'tagihan_mendatang' => $future,
            'total_sisa_tagihan' => $total,
            'tanggal_hari_ini' => $now->format('d-m-Y')
        );
    }

    public function simpan()
    {
        $token = trim((string)$this->input->post('token', true));
        $active = (string)$this->session->userdata('token_pembayaran_aktif');
        if ($token === '' || $active === '' || !hash_equals($active, $token)) return $this->response(false, 'Token transaksi tidak valid atau transaksi sudah diproses. Muat ulang halaman pembayaran.');

        $idSiswa = (int)$this->input->post('id_siswa');
        $idMetode = (int)$this->input->post('id_metode');
        $tanggal = trim((string)$this->input->post('tanggal', true));
        $uang = $this->nilai_nominal($this->input->post('uang_diterima'));
        $referensi = trim((string)$this->input->post('referensi', true));
        $catatan = trim((string)$this->input->post('catatan', true));
        $items = json_decode((string)$this->input->post('items'), true);
        if ($idSiswa <= 0 || $idMetode <= 0 || !is_array($items) || !count($items)) return $this->response(false, 'Siswa, metode, dan minimal satu tagihan wajib dipilih.');
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal)) return $this->response(false, 'Tanggal pembayaran harus berformat dd-mm-yyyy.');

        $metode = $this->db->where('id', $idMetode)->where('status', 'Aktif')->get('tagihan_metode_pembayaran')->row_array();
        if (!$metode) return $this->response(false, 'Metode pembayaran tidak ditemukan.');

        $this->db->trans_begin();
        $siswa = $this->db->query(
            "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,ks.id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,ta.periode
             FROM siswa s
             LEFT JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
             LEFT JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
             LEFT JOIN master_tahun_ajaran ta ON ta.id=CAST(k.id_periode AS UNSIGNED)
             WHERE s.id=? LIMIT 1 FOR UPDATE",
            array($idSiswa)
        )->row_array();
        if (!$siswa) {
            $this->db->trans_rollback();
            return $this->response(false, 'Siswa tidak ditemukan.');
        }

        $normalized = array();
        $total = 0;
        $seen = array();
        foreach ($items as $item) {
            $billId = (int)($item['id_tagihan_siswa'] ?? 0);
            $bayar = $this->nilai_nominal($item['nominal_bayar'] ?? 0);
            if ($billId <= 0 || $bayar <= 0 || isset($seen[$billId])) {
                $this->db->trans_rollback();
                return $this->response(false, 'Keranjang pembayaran tidak valid atau berisi tagihan ganda.');
            }
            $seen[$billId] = true;
            $bill = $this->db->query("SELECT * FROM tagihan_siswa WHERE id=? AND id_siswa=? FOR UPDATE", array($billId, $idSiswa))->row_array();
            if (!$bill || $bill['status_tagihan'] !== 'Aktif' || in_array($bill['status_pembayaran'], array('Lunas', 'Dibebaskan', 'Dibatalkan'), true)) {
                $this->db->trans_rollback();
                return $this->response(false, 'Salah satu tagihan sudah tidak dapat dibayar. Muat ulang data tagihan.');
            }
            // Validasi ulang di server agar tagihan yang disembunyikan tidak dapat dibayar lewat request langsung.
            if ($this->tagihan_non_tunggakan_lewat_tempo($bill, new DateTime('today'))) {
                $this->db->trans_rollback();
                return $this->response(false, 'Tagihan ' . $bill['nama_tagihan'] . ' sudah melewati jatuh tempo dan tidak dihitung sebagai tunggakan, sehingga tidak dapat dibayar. Muat ulang data tagihan.');
            }
            $sisa = (float)$bill['sisa_tagihan'];
            if ($bayar - $sisa > 0.001) {
                $this->db->trans_rollback();
                return $this->response(false, 'Nominal bayar ' . $bill['nama_tagihan'] . ' melebihi sisa tagihan.');
            }
            $normalized[] = array('row' => $bill, 'bayar' => $bayar);
            $total += $bayar;
        }
        if ($total <= 0) {
            $this->db->trans_rollback();
            return $this->response(false, 'Total pembayaran harus lebih dari nol.');
        }
        // Semua metode menggunakan nominal Uang Diterima yang diinput petugas.
        if ($uang + 0.001 < $total) {
            $this->db->trans_rollback();
            return $this->response(false, 'Uang diterima kurang dari total pembayaran.');
        }
        $kembalian = max(0, $uang - $total);

        $no = $this->next_code('BYR', 'tagihan_pembayaran', 'no_transaksi');
        $header = array(
            'no_transaksi' => $no,
            'token_transaksi' => $token,
            'tanggal_transaksi' => $tanggal,
            'waktu_transaksi' => date('H:i:s'),
            'id_siswa' => $idSiswa,
            'nis' => $siswa['nis'],
            'nisn' => $siswa['nisn'],
            'nama_siswa' => $siswa['nama_lengkap'],
            'id_kelas_setting' => (int)($siswa['id_kelas_setting'] ?? 0),
            'id_kelas' => (int)($siswa['id_kelas'] ?? 0),
            'nama_kelas' => $siswa['nama_kelas'] ?? '-',
            'id_periode' => (int)($siswa['id_periode'] ?? 0),
            'periode' => $siswa['periode'] ?? '-',
            'total_tagihan_dipilih' => (string)$total,
            'total_potongan' => '0',
            'total_pembayaran' => (string)$total,
            'id_metode_pembayaran' => $idMetode,
            'nama_metode_pembayaran' => $metode['nama_metode'],
            'uang_diterima' => (string)$uang,
            'kembalian' => (string)$kembalian,
            'referensi_pembayaran' => $referensi,
            'status_transaksi' => 'Aktif',
            'status_cetak' => 'Belum',
            'jumlah_cetak' => 0,
            'keterangan' => $catatan,
            'id_user' => $this->app_user_id(),
            'nama_user' => $this->app_user_name()
        );
        $this->db->insert('tagihan_pembayaran', $header);
        $idPembayaran = (int)$this->db->insert_id();
        if ($idPembayaran <= 0) {
            $this->db->trans_rollback();
            return $this->response(false, 'Header pembayaran gagal disimpan.');
        }

        foreach ($normalized as $x) {
            $r = $x['row'];
            $bayar = $x['bayar'];
            $paidBefore = (float)$r['nominal_dibayar'];
            $sisaBefore = (float)$r['sisa_tagihan'];
            $paidAfter = $paidBefore + $bayar;
            $sisaAfter = max(0, $sisaBefore - $bayar);
            $status = $this->payment_status($paidAfter, (float)$r['nominal_tagihan']);
            $this->db->insert('tagihan_pembayaran_detail', array(
                'id_pembayaran' => $idPembayaran,
                'no_transaksi' => $no,
                'id_tagihan_siswa' => $r['id'],
                'no_tagihan' => $r['no_tagihan'],
                'id_tagihan_master' => $r['id_tagihan_master'],
                'id_jenis_tagihan' => $r['id_jenis_tagihan'],
                'nama_jenis_tagihan' => $r['nama_jenis_tagihan'],
                'nama_tagihan' => $r['nama_tagihan'],
                'tipe_tagihan' => $r['tipe_tagihan'],
                'bulan' => $r['bulan'],
                'nama_bulan' => $r['nama_bulan'],
                'tahun' => $r['tahun'],
                'nominal_tagihan' => (string)$r['nominal_tagihan'],
                'nominal_sudah_dibayar_sebelum' => (string)$paidBefore,
                'sisa_sebelum' => (string)$sisaBefore,
                'nominal_bayar' => (string)$bayar,
                'sisa_setelah' => (string)$sisaAfter,
                'status_setelah' => $status,
                'status_detail' => 'Aktif',
                'tanggal' => $tanggal,
                'waktu' => date('H:i:s')
            ));
            $this->db->where('id', $r['id'])->update('tagihan_siswa', array('nominal_dibayar' => (string)$paidAfter, 'sisa_tagihan' => (string)$sisaAfter, 'status_pembayaran' => $status, 'tanggal_update' => date('d-m-Y'), 'waktu_update' => date('H:i:s')));
            // Posting penerimaan bersifat per detail/jenis; seluruhnya dalam satu DB transaction.
            // Jika migrasi belum terpasang, pembayaran SPP tetap berjalan tanpa posting keuangan.
            if ($this->db->table_exists('spp_pengaturan_integrasi')) {
                $conf = $this->db->where('id_jenis_tagihan', (int)$r['id_jenis_tagihan'])->get('spp_pengaturan_integrasi')->row_array();
                if ($conf && $conf['integrasi_pemasukan'] === 'Ya') {
                    $akun = (int)$conf['id_kode_akun'];
                    if (empty($conf['id_kasbank']) || !$this->db->where('id', (int)$conf['id_kasbank'])->count_all_results('kasbank')) {
                        $this->db->trans_rollback();
                        return $this->response(false, 'Konfigurasi Kas/Bank Pemasukan belum valid.');
                    }
                    if (!$akun || !$this->db->where('id', $akun)->where('jenis', 'Pemasukan')->count_all_results('kode_akun')) {
                        $this->db->trans_rollback();
                        return $this->response(false, 'Konfigurasi pos penerimaan jenis tagihan tidak valid.');
                    }
                    // Insert ID detail diperoleh dari query detail (bukan hasil UPDATE).
                    $detailRow = $this->db->select('id')->where('id_pembayaran', $idPembayaran)->where('id_tagihan_siswa', (int)$r['id'])->order_by('id', 'DESC')->limit(1)->get('tagihan_pembayaran_detail')->row_array();
                    $detailId = (int)($detailRow['id'] ?? 0);
                    if (!$detailId) {
                        $this->db->trans_rollback();
                        return $this->response(false, 'Referensi detail pembayaran tidak ditemukan.');
                    }
                    $key = 'SPP:' . $detailId;
                    if ($this->db->where('idempotency_key', $key)->count_all_results('pemasukan')) {
                        $this->db->trans_rollback();
                        return $this->response(false, 'Posting pemasukan telah ada. Transaksi dibatalkan untuk mencegah duplikasi.');
                    }
                    $this->db->insert('pemasukan', array(
                        'id_pegawai' => (string)$this->app_user_id(),
                        'tanggal_input' => $tanggal,
                        'bulan' => substr($tanggal, 3, 2),
                        'tahun' => substr($tanggal, 6, 4),
                        'id_kode_akun' => (string)$akun,
                        'keterangan' => 'Pembayaran ' . $r['nama_tagihan'] . ' - ' . $siswa['nama_lengkap'] . ' - ' . $no,
                        'simpan_ke' => empty($conf['id_kasbank']) ? '' : (string)(int)$conf['id_kasbank'],
                        'jumlah' => (string)$bayar,
                        'tanggal' => $tanggal,
                        'sumber_transaksi' => 'SPP',
                        'id_sumber' => $detailId,
                        'no_referensi' => $no,
                        'idempotency_key' => $key
                    ));
                }
            }
        }
        $this->log('Terima Pembayaran', 'Transaksi', 'Tambah', 'tagihan_pembayaran', $idPembayaran, $no, 'Pembayaran ' . $siswa['nama_lengkap'] . ' sebesar ' . $this->rupiah($total), null, $header);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->response(false, 'Pembayaran gagal disimpan. Tidak ada perubahan yang diterapkan.');
        }
        $this->db->trans_commit();
        $this->session->unset_userdata('token_pembayaran_aktif');
        return $this->response(true, 'Pembayaran berhasil disimpan.', array('id_pembayaran' => $idPembayaran, 'no_transaksi' => $no, 'total' => $total, 'uang_diterima' => $uang, 'kembalian' => $kembalian));
    }

    public function detail($id)
    {
        $header = $this->db->where('id', (int)$id)->get('tagihan_pembayaran')->row_array();
        if (!$header) return $this->response(false, 'Transaksi tidak ditemukan.');
        $detail = $this->db->where('id_pembayaran', (int)$id)->order_by('id')->get('tagihan_pembayaran_detail')->result_array();
        $cancel = $this->db->where('id_pembayaran', (int)$id)->get('tagihan_pembatalan_transaksi')->row_array();
        $student = $this->siswa_by_id((int)$header['id_siswa']);
        return array('result' => 'true', 'header' => $header, 'detail' => $detail, 'pembatalan' => $cancel, 'siswa' => $student['siswa'] ?? array());
    }

    private function payment_template_wa()
    {
        return $this->db->where('jenis_template', 'Bukti Pembayaran')->where('status', 'Aktif')
            ->order_by("status_default='Ya'", 'DESC', FALSE)->order_by('id', 'DESC')
            ->get('spp_template_whatsapp')->row_array();
    }
    private function wa_message($template, $header, $name)
    {
        $name = trim((string)$name) ?: 'Bapak/Ibu Wali';
        return strtr((string)$template, array(
            '{nama_wali}' => $name,
            '{nama_siswa}' => $header['nama_siswa'],
            '{kelas}' => $header['nama_kelas'],
            '{tanggal}' => $header['tanggal_transaksi'],
            '{no_transaksi}' => $header['no_transaksi'],
            '{total_bayar}' => $this->rupiah($header['total_pembayaran']),
            '{metode_pembayaran}' => $header['nama_metode_pembayaran'] ?? '',
            '{total_tunggakan}' => '',
            '{nama_sekolah}' => $this->config->item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah',
            '{nama_petugas}' => $header['nama_user']
        ));
    }
    public function preview_whatsapp()
    {
        $data = $this->detail((int)$this->input->post('id'));
        if (($data['result'] ?? '') !== 'true') return $data;
        if ($data['header']['status_transaksi'] !== 'Aktif') return $this->response(false, 'Pembayaran sudah dibatalkan.');
        $tpl = $this->payment_template_wa();
        $text = $tpl['isi_template'] ?? 'Yth. {nama_wali}, pembayaran {nama_siswa} sebesar {total_bayar} telah kami terima tanggal {tanggal}. No. {no_transaksi}. Terima kasih.';
        $name = trim((string)($data['siswa']['nama_wali'] ?? '')) ?: 'Bapak/Ibu Wali';
        return $this->response(true, 'Template siap.', array(
            'nama_template' => $tpl['nama_template'] ?? 'Default',
            'wali' => array('nama' => $name, 'hubungan' => $data['siswa']['hubungan_wali'] ?? 'Wali', 'nomor' => $data['siswa']['no_telp_wali'] ?? ''),
            'pesan' => $this->wa_message($text, $data['header'], $name)
        ));
    }
    public function siapkan_whatsapp()
    {
        $id = (int)$this->input->post('id');
        $data = $this->detail($id);
        if (($data['result'] ?? '') !== 'true') return $data;
        if ($data['header']['status_transaksi'] !== 'Aktif') return $this->response(false, 'Transaksi dibatalkan tidak dapat dikirim.');
        $source = trim((string)$this->input->post('sumber_nomor', TRUE));
        if (!in_array($source, array('Orang Tua', 'Nomor Lain'), true)) $source = 'Orang Tua';
        $name = trim((string)$this->input->post('nama_penerima', TRUE));
        if ($name === '') $name = trim((string)($data['siswa']['nama_wali'] ?? '')) ?: 'Bapak/Ibu Wali';
        $hubungan = trim((string)$this->input->post('hubungan_penerima', TRUE));
        if ($hubungan === '') $hubungan = $source === 'Nomor Lain' ? 'Lainnya' : (trim((string)($data['siswa']['hubungan_wali'] ?? '')) ?: 'Wali');
        $postedPhone = trim((string)$this->input->post('nomor_whatsapp', TRUE));
        if ($postedPhone === '') $postedPhone = (string)($data['siswa']['no_telp_wali'] ?? '');
        $phone = preg_replace('/\D+/', '', $postedPhone);
        if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
        if (substr($phone, 0, 2) !== '62' || strlen($phone) < 10 || strlen($phone) > 16) return $this->response(false, 'Nomor WhatsApp tidak valid. Gunakan nomor Indonesia yang aktif.');
        $msg = trim((string)$this->input->post('pesan', FALSE));
        if ($msg === '') {
            $tpl = $this->payment_template_wa();
            $msg = $this->wa_message($tpl['isi_template'] ?? 'Pembayaran {nama_siswa} sejumlah {total_bayar} telah diterima.', $data['header'], $name);
        }
        $this->db->trans_begin();
        $this->db->insert('spp_riwayat_whatsapp', array(
            'jenis_kirim' => 'Bukti Pembayaran',
            'id_referensi' => $id,
            'nomor_referensi' => $data['header']['no_transaksi'],
            'id_siswa' => $data['header']['id_siswa'],
            'nama_siswa' => $data['header']['nama_siswa'],
            'nama_penerima' => $name,
            'hubungan_penerima' => $hubungan,
            'nomor_whatsapp' => $phone,
            'isi_pesan' => $msg,
            'metode_kirim' => 'Tautan',
            'status_kirim' => 'Disiapkan',
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $this->app_user_id(),
            'nama_user' => $this->app_user_name()
        ));
        $this->db->where('id', $id)->update('tagihan_pembayaran', array('status_kirim_whatsapp' => 'Disiapkan'));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->response(false, 'Gagal menyiapkan WhatsApp.');
        }
        $this->db->trans_commit();
        return $this->response(true, 'WhatsApp siap dibuka.', array('url' => 'https://wa.me/' . $phone . '?text=' . rawurlencode($msg)));
    }

    public function format_kartu_list()
    {
        return $this->db
            ->where('jenis_dokumen', 'Kartu SPP')
            ->where('status', 'Aktif')
            ->order_by("status_default='Ya'", 'DESC', false)
            ->order_by('nama_format', 'ASC')
            ->get('spp_pengaturan_cetak')
            ->result_array();
    }

    public function format_kartu($id = 0)
    {
        $this->db
            ->where('jenis_dokumen', 'Kartu SPP')
            ->where('status', 'Aktif');

        if ((int) $id > 0) {
            $this->db->where('id', (int) $id);
        } else {
            $this->db
                ->order_by("status_default='Ya'", 'DESC', false)
                ->order_by('id', 'DESC');
        }

        return $this->db->get('spp_pengaturan_cetak')->row_array();
    }

    private function kartu_config($format)
    {
        $config = array();
        if (!empty($format['pengaturan_json'])) {
            $decoded = json_decode($format['pengaturan_json'], true);
            if (is_array($decoded)) {
                $config = $decoded;
            }
        }

        return array(
            'jumlah_baris' => max(1, (int) ($config['jumlah_baris'] ?? $config['jumlahBaris'] ?? 12)),
            'jarak_baris' => max(0, (float) ($config['jarak_baris'] ?? $config['jarak'] ?? 8)),
            'posisi_x' => max(0, (float) ($config['posisi_x'] ?? $config['x'] ?? 10)),
            'posisi_y' => max(0, (float) ($config['posisi_y'] ?? $config['y'] ?? 10)),
            'lebar_tanggal' => max(0, (float) ($config['lebar_tanggal'] ?? $config['wTanggal'] ?? 25)),
            'lebar_jenis' => max(0, (float) ($config['lebar_jenis'] ?? $config['wJenis'] ?? 70)),
            'lebar_nominal' => max(0, (float) ($config['lebar_nominal'] ?? $config['wNominal'] ?? 35)),
            'lebar_petugas' => max(0, (float) ($config['lebar_petugas'] ?? $config['wPetugas'] ?? 20)),
            'kolom' => !empty($config['kolom']) && is_array($config['kolom'])
                ? array_values($config['kolom'])
                : array('Tanggal', 'Jenis/Bulan', 'Nominal', 'Petugas')
        );
    }

    public function cek_baris_kartu()
    {
        $id = (int) $this->input->post('id');
        $idFormat = (int) $this->input->post('id_format');
        $baris = (int) $this->input->post('nomor_baris');
        $data = $this->detail($id);

        if ($data['result'] !== 'true') {
            return $data;
        }

        $format = $this->format_kartu($idFormat);
        if (!$format) {
            return $this->response(false, 'Format kartu tidak ditemukan atau tidak aktif.');
        }

        $config = $this->kartu_config($format);
        if ($baris < 1 || $baris > $config['jumlah_baris']) {
            return $this->response(false, 'Nomor baris berada di luar jumlah baris pada format kartu.');
        }

        $this->db
            ->where('id_siswa', (int) $data['header']['id_siswa'])
            ->where('id_format_cetak', (int) $format['id'])
            ->where('nomor_baris', $baris);
        $jumlah = $this->db->count_all_results('spp_cetak_kartu');

        $terakhir = $this->db
            ->where('id_siswa', (int) $data['header']['id_siswa'])
            ->where('id_format_cetak', (int) $format['id'])
            ->where('nomor_baris', $baris)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('spp_cetak_kartu')
            ->row_array();

        return $this->response(true, '', array(
            'pernah_digunakan' => $jumlah > 0 ? 'Ya' : 'Tidak',
            'jumlah' => $jumlah,
            'terakhir' => $terakhir,
            'posisi_x_default' => $config['posisi_x'],
            'posisi_y_default' => $config['posisi_y'] + (($baris - 1) * $config['jarak_baris'])
        ));
    }

    public function catat_cetak_kartu()
    {
        $id = (int) $this->input->post('id');
        $idFormat = (int) $this->input->post('id_format');
        $baris = (int) $this->input->post('nomor_baris');
        $aksi = $this->input->post('aksi', true) === 'tandai' ? 'tandai' : 'cetak';
        $data = $this->detail($id);

        if ($data['result'] !== 'true') {
            return $data;
        }
        if ($data['header']['status_transaksi'] !== 'Aktif') {
            return $this->response(false, 'Transaksi dibatalkan tidak dapat dicetak ke kartu.');
        }

        $format = $this->format_kartu($idFormat);
        if (!$format) {
            return $this->response(false, 'Format kartu tidak ditemukan atau tidak aktif.');
        }

        $config = $this->kartu_config($format);
        if ($baris < 1 || $baris > $config['jumlah_baris']) {
            return $this->response(false, 'Nomor baris berada di luar jumlah baris pada format kartu.');
        }

        $defaultX = $config['posisi_x'];
        $defaultY = $config['posisi_y'] + (($baris - 1) * $config['jarak_baris']);
        $xInput = $this->input->post('posisi_x', true);
        $yInput = $this->input->post('posisi_y', true);
        $x = is_numeric($xInput) ? max(0, (float) $xInput) : $defaultX;
        $y = is_numeric($yInput) ? max(0, (float) $yInput) : $defaultY;

        $existing = $this->db
            ->where('id_siswa', (int) $data['header']['id_siswa'])
            ->where('id_format_cetak', (int) $format['id'])
            ->where('nomor_baris', $baris)
            ->count_all_results('spp_cetak_kartu');

        if ($aksi === 'tandai') {
            $statusCetak = 'Ditandai';
            $jumlahCetak = 0;
            $jenisAktivitas = 'Tandai Baris Kartu Pembayaran';
            $aksiLog = 'Ubah';
            $keterangan = 'Baris kartu ditandai sudah digunakan tanpa proses cetak.';
        } else {
            $statusCetak = $existing ? 'Cetak Ulang' : 'Berhasil';
            $jumlahCetak = 1;
            $jenisAktivitas = $existing ? 'Cetak Ulang Kartu Pembayaran' : 'Cetak Kartu Pembayaran';
            $aksiLog = 'Cetak';
            $keterangan = $existing
                ? 'Cetak ulang kartu pembayaran pada baris yang pernah digunakan.'
                : 'Cetak kartu pembayaran.';
        }

        $this->db->trans_begin();
        $this->db->insert('spp_cetak_kartu', array(
            'id_pembayaran' => $id,
            'no_transaksi' => $data['header']['no_transaksi'],
            'id_siswa' => $data['header']['id_siswa'],
            'nama_siswa' => $data['header']['nama_siswa'],
            'id_format_cetak' => (int) $format['id'],
            'nama_format' => $format['nama_format'],
            'nomor_baris' => $baris,
            'posisi_x' => $x,
            'posisi_y' => $y,
            'status_cetak' => $statusCetak,
            'jumlah_cetak' => $jumlahCetak,
            'keterangan' => $keterangan,
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $this->app_user_id(),
            'nama_user' => $this->app_user_name()
        ));
        $idCetak = (int) $this->db->insert_id();

        $this->log(
            $jenisAktivitas,
            'Transaksi',
            $aksiLog,
            'spp_cetak_kartu',
            $idCetak,
            $data['header']['no_transaksi'] . ' / Baris ' . str_pad((string) $baris, 2, '0', STR_PAD_LEFT),
            'Format ' . $format['nama_format'] . ', baris ' . str_pad((string) $baris, 2, '0', STR_PAD_LEFT)
                . ', X ' . $x . ' mm, Y ' . $y . ' mm'
                . ($existing ? ' (baris pernah digunakan)' : '')
        );

        $message = $aksi === 'tandai'
            ? 'Baris berhasil ditandai sudah digunakan dan dicatat pada Log Aktivitas.'
            : ($existing
                ? 'Cetak ulang kartu berhasil dicatat pada Log Aktivitas.'
                : 'Cetak kartu berhasil dicatat pada Log Aktivitas.');

        return $this->card_transaction_result($message);
    }



    private function card_transaction_result($message)
    {
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->response(false, 'Gagal mencatat cetak kartu.');
        }
        $this->db->trans_commit();
        return $this->response(true, $message);
    }

    // Aturan khusus halaman Pembayaran: non-tunggakan tidak dapat dibayar setelah jatuh tempo.
    // Jatuh tempo hari ini masih boleh dibayar; tanggal kosong/tidak valid tidak otomatis diblokir.
    private function tagihan_non_tunggakan_lewat_tempo($tagihan, DateTime $hariIni, $jatuhTempo = null)
    {
        if (strcasecmp(trim((string)($tagihan['dianggap_tunggakan'] ?? 'Ya')), 'Tidak') !== 0) return false;
        $tanggal = trim((string)($tagihan['tanggal_jatuh_tempo'] ?? ''));
        if ($tanggal === '') return false;
        if (!($jatuhTempo instanceof DateTime)) $jatuhTempo = DateTime::createFromFormat('!d-m-Y', $tanggal);
        return $jatuhTempo instanceof DateTime
            && $jatuhTempo->format('d-m-Y') === $tanggal
            && $jatuhTempo < $hariIni;
    }

    private function payment_status($paid, $total)
    {
        if ($paid <= 0) return 'Belum Dibayar';
        if ($paid + 0.001 >= $total) return 'Lunas';
        return 'Dibayar Sebagian';
    }
    private function nilai_nominal($v)
    {
        if (is_int($v) || is_float($v)) return (float)$v;
        $t = trim(str_ireplace(array('Rp', ' '), '', (string)$v));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $t)) return (float)$t;
        $c = preg_replace('/[^0-9-]/', '', $t);
        return ($c === '' || $c === '-') ? 0 : (float)$c;
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
    private function rupiah($n)
    {
        return 'Rp' . number_format((float)$n, 0, ',', '.');
    }
    private function response($ok, $msg, $extra = array())
    {
        return array_merge(array('result' => $ok ? 'true' : 'false', 'message' => $msg), $extra);
    }
    private function next_code($prefix, $table, $column)
    {
        $like = $prefix . '/' . date('Ym') . '/';
        $row = $this->db->select($column)->like($column, $like, 'after')->order_by('id', 'DESC')->limit(1)->get($table)->row_array();
        $n = 1;
        if ($row && !empty($row[$column])) {
            $p = explode('/', $row[$column]);
            $n = ((int)end($p)) + 1;
        }
        return $like . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
    private function log($jenis, $modul, $aksi, $table, $id, $nomor, $ket, $before = null, $after = null)
    {
        $this->db->insert('tagihan_log_aktivitas', array('jenis_aktivitas' => $jenis, 'modul' => $modul, 'aksi' => $aksi, 'nama_tabel' => $table, 'id_referensi' => (string)$id, 'nomor_referensi' => $nomor, 'keterangan' => $ket, 'data_sebelum' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE), 'data_sesudah' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE), 'ip_address' => $this->input->ip_address(), 'user_agent' => $this->input->user_agent(), 'tanggal' => date('d-m-Y'), 'waktu' => date('H:i:s'), 'id_user' => $this->app_user_id(), 'nama_user' => $this->app_user_name()));
    }
}
