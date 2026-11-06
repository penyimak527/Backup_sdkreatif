<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_dompet_wali extends CI_Model
{
    public function user_wali($id_user)
    {
        return $this->db
            ->select('u.id, u.nama_user, u.username, u.id_level, u.level, u.id_wali_siswa, '
                .'u.status_aktif, w.nama_wali, w.status_aktif AS status_wali')
            ->from('user u')
            ->join('wali_siswa w', 'w.id = u.id_wali_siswa', 'inner')
            ->where('u.id', (int) $id_user)
            ->where("UPPER(TRIM(u.status_aktif)) = 'AKTIF'", null, false)
            ->where("UPPER(TRIM(w.status_aktif)) = 'AKTIF'", null, false)
            ->group_start()
                ->where('u.id_level', 8)
                ->or_where("UPPER(TRIM(u.level)) = 'WALI MURID'", null, false)
            ->group_end()
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function daftar_anak($id_wali_siswa)
    {
        return $this->query_anak((int) $id_wali_siswa)
            ->order_by("CASE WHEN UPPER(TRIM(d.status_utama)) = 'YA' THEN 0 ELSE 1 END", '', false)
            ->order_by('s.nama_lengkap', 'ASC')
            ->get()
            ->result_array();
    }

    public function anak($id_wali_siswa, $id_siswa)
    {
        return $this->query_anak((int) $id_wali_siswa)
            ->where('s.id', (int) $id_siswa)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function dashboard($id_wali_siswa, $id_siswa)
    {
        $anak = $this->anak($id_wali_siswa, $id_siswa);
        if (!$anak) {
            return null;
        }

        $awal_bulan = date('Y-m-01');
        $akhir_bulan = date('Y-m-t');
        $belanja = $this->db
            ->select('COUNT(*) AS jumlah, COALESCE(SUM(nominal), 0) AS total', false)
            ->from('transaksi_kantin')
            ->where('id_siswa', (int) $id_siswa)
            ->where('tanggal >=', $awal_bulan)
            ->where('tanggal <=', $akhir_bulan)
            ->where_in('status_transaksi', array('BERHASIL', 'PEMBATALAN_MENUNGGU'))
            ->get()
            ->row_array();

        $topup = $this->db
            ->select('COUNT(*) AS jumlah, COALESCE(SUM(nominal_saldo), 0) AS total', false)
            ->from('riwayat_isi_saldo')
            ->where('id_siswa', (int) $id_siswa)
            ->where('tanggal >=', $awal_bulan)
            ->where('tanggal <=', $akhir_bulan)
            ->where('status_transaksi', 'BERHASIL')
            ->get()
            ->row_array();

        $terbaru = $this->db
            ->select('no_transaksi, nominal, status_transaksi, tanggal, waktu, keterangan')
            ->from('transaksi_kantin')
            ->where('id_siswa', (int) $id_siswa)
            ->order_by('id', 'DESC')
            ->limit(8)
            ->get()
            ->result_array();

        return array(
            'anak' => $anak,
            'belanja_bulan_ini' => $belanja,
            'topup_bulan_ini' => $topup,
            'transaksi_terbaru' => $terbaru,
        );
    }

    public function riwayat_belanja($id_wali_siswa, $id_siswa, $halaman, $per_halaman, $tanggal_awal = null, $tanggal_akhir = null)
    {
        if (!$this->anak($id_wali_siswa, $id_siswa)) {
            return null;
        }

        $this->db->from('transaksi_kantin')->where('id_siswa', (int) $id_siswa);
        $this->terapkan_filter_tanggal('tanggal', $tanggal_awal, $tanggal_akhir);
        $total = $this->db->count_all_results();

        $this->db
            ->select('no_transaksi, nominal, saldo_sebelum, saldo_sesudah, keterangan, '
                .'status_transaksi, tanggal, waktu')
            ->from('transaksi_kantin')
            ->where('id_siswa', (int) $id_siswa);
        $this->terapkan_filter_tanggal('tanggal', $tanggal_awal, $tanggal_akhir);
        $rows = $this->db
            ->order_by('id', 'DESC')
            ->limit((int) $per_halaman, ((int) $halaman - 1) * (int) $per_halaman)
            ->get()
            ->result_array();

        return $this->hasil_halaman($rows, $total, $halaman, $per_halaman);
    }

    public function riwayat_isi_saldo($id_wali_siswa, $id_siswa, $halaman, $per_halaman, $tanggal_awal = null, $tanggal_akhir = null)
    {
        if (!$this->anak($id_wali_siswa, $id_siswa)) {
            return null;
        }

        $this->db->from('pengajuan_isi_saldo p')
            ->where('p.id_wali_siswa', (int) $id_wali_siswa)
            ->where('p.id_siswa', (int) $id_siswa);
        $this->terapkan_filter_tanggal('p.tanggal_pengajuan', $tanggal_awal, $tanggal_akhir);
        $total = $this->db->count_all_results();

        $select = 'p.id, p.no_pengajuan, p.nominal_saldo, p.biaya_layanan, p.total_transfer, '
            .'p.nama_kasbank, p.status_pengajuan, p.alasan_penolakan, p.tanggal_pengajuan, '
            .'p.waktu_pengajuan, p.tanggal_verifikasi, p.waktu_verifikasi, '
            .'r.nama_bank, r.nomor_rekening, r.nama_pemilik';
        if ($this->db->field_exists('metode_pembayaran', 'pengajuan_isi_saldo')) {
            $select .= ', p.metode_pembayaran';
        } else {
            $select .= ", 'TRANSFER' AS metode_pembayaran";
        }

        $rows = $this->db
            ->select($select, false)
            ->from('pengajuan_isi_saldo p')
            ->join('rekening_dompet r', 'r.id = p.id_rekening_dompet', 'left')
            ->where('p.id_wali_siswa', (int) $id_wali_siswa)
            ->where('p.id_siswa', (int) $id_siswa);
        $this->terapkan_filter_tanggal('p.tanggal_pengajuan', $tanggal_awal, $tanggal_akhir);
        $rows = $this->db
            ->order_by('p.id', 'DESC')
            ->limit((int) $per_halaman, ((int) $halaman - 1) * (int) $per_halaman)
            ->get()
            ->result_array();

        return $this->hasil_halaman($rows, $total, $halaman, $per_halaman);
    }

    public function histori_limit($id_wali_siswa, $id_siswa, $batas = 20)
    {
        if (!$this->anak($id_wali_siswa, $id_siswa)) {
            return null;
        }

        return $this->db
            ->select('no_riwayat, batas_harian_lama, batas_harian_baru, '
                .'batas_transaksi_lama, batas_transaksi_baru, tanggal, waktu')
            ->from('riwayat_perubahan_limit')
            ->where('id_siswa', (int) $id_siswa)
            ->order_by('id', 'DESC')
            ->limit((int) $batas)
            ->get()
            ->result_array();
    }

    public function pengaturan_topup()
    {
        $pengaturan = $this->db
            ->select('biaya_layanan, minimal_isi_saldo, maksimal_isi_saldo, nominal_cepat, status_layanan')
            ->from('pengaturan_dompet')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        $rekening = $this->db
            ->select('id, id_kasbank, nama_kasbank, nama_bank, nomor_rekening, nama_pemilik, petunjuk')
            ->from('rekening_dompet')
            ->where("UPPER(TRIM(status_aktif)) = 'AKTIF'", null, false)
            ->order_by('urutan', 'ASC')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        $whatsapp = $this->db
            ->select('nomor')
            ->from('nomor_whatsapp_admin')
            ->where("TRIM(COALESCE(nomor, '')) <> ''", null, false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        return array(
            'pengaturan' => $pengaturan,
            'rekening' => $rekening,
            'whatsapp' => $whatsapp ? $this->nomor_whatsapp($whatsapp['nomor']) : null,
        );
    }

    public function buat_pengajuan($user, $id_siswa, $id_rekening, $nominal)
    {
        $this->db->trans_begin();

        $anak = $this->scope_anak_terkunci((int) $user['id_wali_siswa'], (int) $id_siswa);
        $pengaturan = $this->db->query(
            'SELECT biaya_layanan, minimal_isi_saldo, maksimal_isi_saldo, status_layanan '
            .'FROM pengaturan_dompet ORDER BY id DESC LIMIT 1 FOR UPDATE'
        )->row_array();
        $rekening = $this->db->query(
            "SELECT id, id_kasbank, nama_kasbank, nama_bank, nomor_rekening, nama_pemilik "
            ."FROM rekening_dompet WHERE id = ? AND UPPER(TRIM(status_aktif)) = 'AKTIF' LIMIT 1 FOR UPDATE",
            array((int) $id_rekening)
        )->row_array();
        $whatsapp = $this->db
            ->select('nomor')
            ->from('nomor_whatsapp_admin')
            ->where("TRIM(COALESCE(nomor, '')) <> ''", null, false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        $error = $this->validasi_topup($anak, $pengaturan, $rekening, $whatsapp, $nominal);
        if ($error !== null) {
            $this->db->trans_rollback();
            return array('status' => false, 'message' => $error);
        }

        try {
            $no_pengajuan = 'TOP-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
        } catch (Exception $exception) {
            $this->db->trans_rollback();
            log_message('error', 'Gagal membuat nomor pengajuan web: '.$exception->getMessage());
            return array('status' => false, 'message' => 'Nomor pengajuan belum dapat dibuat.');
        }

        $biaya = (int) $pengaturan['biaya_layanan'];
        $total = (int) $nominal + $biaya;
        $nama_wali = trim((string) $user['nama_wali']);
        if ($nama_wali === '') {
            $nama_wali = trim((string) $user['nama_user']);
        }

        $data = array(
            'no_pengajuan' => $no_pengajuan,
            'id_siswa' => (int) $anak['id_siswa'],
            'nis' => (string) $anak['nis'],
            'nama_siswa' => (string) $anak['nama_siswa'],
            'id_wali_siswa' => (int) $user['id_wali_siswa'],
            'nama_wali' => $nama_wali,
            'nominal_saldo' => (int) $nominal,
            'biaya_layanan' => $biaya,
            'total_transfer' => $total,
            'id_rekening_dompet' => (int) $rekening['id'],
            'id_kasbank' => $rekening['id_kasbank'] !== null ? (int) $rekening['id_kasbank'] : null,
            'nama_kasbank' => trim((string) $rekening['nama_kasbank']) !== ''
                ? trim((string) $rekening['nama_kasbank']) : trim((string) $rekening['nama_bank']),
            'bukti_transfer' => null,
            'catatan_wali' => null,
            'status_pengajuan' => 'MENUNGGU_VERIFIKASI',
            'tanggal_pengajuan' => date('Y-m-d'),
            'waktu_pengajuan' => date('H:i:s'),
        );
        if ($this->db->field_exists('metode_pembayaran', 'pengajuan_isi_saldo')) {
            $data['metode_pembayaran'] = 'TRANSFER';
        }

        $this->db->insert('pengajuan_isi_saldo', $data);
        $id_pengajuan = (int) $this->db->insert_id();
        $this->db->insert('log_aktivitas_dompet', array(
            'id_user' => (int) $user['id'],
            'nama_user' => $nama_wali,
            'level' => 'Wali Murid',
            'modul' => 'PENGAJUAN_ISI_SALDO',
            'aksi' => 'BUAT_PENGAJUAN_WEB',
            'id_referensi' => $id_pengajuan,
            'no_referensi' => $no_pengajuan,
            'keterangan' => 'Pengajuan isi saldo dibuat melalui portal Wali dan menunggu verifikasi.',
            'alamat_ip' => $this->input->ip_address(),
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return array('status' => false, 'message' => 'Pengajuan belum dapat disimpan. Coba lagi nanti.');
        }
        $this->db->trans_commit();

        $nomor_admin = $this->nomor_whatsapp($whatsapp['nomor']);
        $pesan = "Assalamu'alaikum Admin SD Kreatif.\n\n"
            ."Saya mengajukan isi saldo Dompet Digital.\n"
            ."No. Pengajuan: {$no_pengajuan}\n"
            ."Wali: {$nama_wali}\n"
            ."Siswa: {$anak['nama_siswa']} ({$anak['nis']})\n"
            ."Nominal saldo: Rp ".number_format((int) $nominal, 0, ',', '.')."\n"
            ."Biaya layanan: Rp ".number_format($biaya, 0, ',', '.')."\n"
            ."Total transfer: Rp ".number_format($total, 0, ',', '.')."\n"
            ."Rekening: {$rekening['nama_bank']} {$rekening['nomor_rekening']} a.n. {$rekening['nama_pemilik']}\n"
            ."Status: Menunggu verifikasi.";

        return array(
            'status' => true,
            'message' => 'Pengajuan isi saldo berhasil disimpan.',
            'data' => array(
                'no_pengajuan' => $no_pengajuan,
                'whatsapp_url' => 'https://wa.me/'.$nomor_admin.'?text='.rawurlencode($pesan),
            ),
        );
    }

    public function ubah_limit($user, $id_siswa, $batas_harian, $batas_transaksi)
    {
        if ($batas_harian <= 0 || $batas_transaksi <= 0 || $batas_transaksi > $batas_harian) {
            return array('status' => false, 'message' => 'Limit harus lebih dari nol dan limit per transaksi tidak boleh melebihi limit harian.');
        }

        try {
            $no_riwayat = 'LMT-'.date('YmdHis').'-'.strtoupper(bin2hex(random_bytes(5)));
        } catch (Exception $exception) {
            return array('status' => false, 'message' => 'Nomor riwayat limit belum dapat dibuat.');
        }

        $this->db->trans_begin();
        $scope = $this->db->query(
            "SELECT s.id, s.status_siswa, s.status_aktif, sd.id AS id_saldo, "
            ."p.id AS id_limit, p.batas_harian, p.batas_per_transaksi, p.status_aktif AS status_limit "
            ."FROM wali_siswa_detail d INNER JOIN siswa s ON s.id=d.id_siswa "
            ."LEFT JOIN saldo_dompet_siswa sd ON sd.id_siswa=s.id "
            ."LEFT JOIN pengaturan_dompet_siswa p ON p.id_siswa=s.id "
            ."WHERE d.id_wali_siswa=? AND d.id_siswa=? AND UPPER(TRIM(d.status_aktif))='AKTIF' "
            ."LIMIT 1 FOR UPDATE",
            array((int) $user['id_wali_siswa'], (int) $id_siswa)
        )->row_array();

        if (!$scope || !$scope['id_saldo']) {
            $this->db->trans_rollback();
            return array('status' => false, 'message' => 'Anak tidak ditemukan atau dompet belum tersedia.');
        }
        if (strtoupper(trim((string) $scope['status_siswa'])) !== 'AKTIF'
            || strtoupper(trim((string) $scope['status_aktif'])) !== 'AKTIF') {
            $this->db->trans_rollback();
            return array('status' => false, 'message' => 'Limit hanya dapat diubah untuk siswa aktif.');
        }

        $lama_harian = $scope['id_limit'] ? (int) $scope['batas_harian'] : 0;
        $lama_transaksi = $scope['id_limit'] ? (int) $scope['batas_per_transaksi'] : 0;
        if ($lama_harian === (int) $batas_harian && $lama_transaksi === (int) $batas_transaksi) {
            $this->db->trans_rollback();
            return array('status' => true, 'message' => 'Limit tidak berubah.');
        }

        $nama = trim((string) $user['nama_wali']);
        $data = array(
            'batas_harian' => (int) $batas_harian,
            'batas_per_transaksi' => (int) $batas_transaksi,
            'status_aktif' => 'AKTIF',
            'tanggal_update' => date('Y-m-d'),
            'waktu_update' => date('H:i:s'),
            'id_user_update' => (int) $user['id'],
            'nama_user_update' => $nama,
            'sumber_perubahan' => 'WALI_WEB',
        );
        if ($scope['id_limit']) {
            $this->db->where('id', (int) $scope['id_limit'])->update('pengaturan_dompet_siswa', $data);
        } else {
            $data['id_siswa'] = (int) $id_siswa;
            $this->db->insert('pengaturan_dompet_siswa', $data);
        }
        $this->db->insert('riwayat_perubahan_limit', array(
            'no_riwayat' => $no_riwayat,
            'id_siswa' => (int) $id_siswa,
            'batas_harian_lama' => $lama_harian,
            'batas_harian_baru' => (int) $batas_harian,
            'batas_transaksi_lama' => $lama_transaksi,
            'batas_transaksi_baru' => (int) $batas_transaksi,
            'sumber_perubahan' => 'WALI_WEB',
            'id_user' => (int) $user['id'],
            'nama_user' => $nama,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return array('status' => false, 'message' => 'Limit belum dapat disimpan.');
        }
        $this->db->trans_commit();
        return array('status' => true, 'message' => 'Limit transaksi berhasil diperbarui.');
    }

    private function query_anak($id_wali_siswa)
    {
        $pengaturan = '(SELECT batas_harian_default, batas_transaksi_default '
            .'FROM pengaturan_dompet ORDER BY id DESC LIMIT 1)';
        return $this->db
            ->select('s.id AS id_siswa, s.nis, s.nama_lengkap AS nama_siswa, s.status_siswa, '
                .'s.status_aktif AS status_aktif_siswa, d.status_utama, ks.nama_siswa AS nama_siswa_kelas, '
                .'kset.nama_kelas, sd.id AS id_saldo, COALESCE(sd.saldo, 0) AS saldo, '
                .'sd.status_dompet, p.id AS id_limit, p.status_aktif AS status_limit, '
                .'COALESCE(NULLIF(p.batas_harian, 0), g.batas_harian_default, 0) AS batas_harian, '
                .'COALESCE(NULLIF(p.batas_per_transaksi, 0), g.batas_transaksi_default, 0) AS batas_per_transaksi, '
                .'COALESCE((SELECT SUM(t.nominal) FROM transaksi_kantin t WHERE t.id_siswa=s.id '
                ."AND t.tanggal='".$this->db->escape_str(date('Y-m-d'))."' "
                ."AND t.status_transaksi IN ('BERHASIL','PEMBATALAN_MENUNGGU')), 0) AS penggunaan_hari_ini", false)
            ->from('wali_siswa_detail d')
            ->join('siswa s', 's.id = d.id_siswa', 'inner')
            ->join('kelas_siswa ks', "ks.id = (SELECT ks2.id FROM kelas_siswa ks2 WHERE ks2.id_siswa = CAST(s.id AS CHAR) AND UPPER(TRIM(ks2.status_aktif)) IN ('1','AKTIF','YA') ORDER BY ks2.id DESC LIMIT 1)", 'left', false)
            ->join('kelas_setting kset', 'kset.id = CAST(ks.id_kelas_setting AS UNSIGNED)', 'left', false)
            ->join('saldo_dompet_siswa sd', 'sd.id_siswa = s.id', 'left')
            ->join('pengaturan_dompet_siswa p', 'p.id_siswa = s.id', 'left')
            ->join($pengaturan.' g', '1=1', 'left', false)
            ->where('d.id_wali_siswa', (int) $id_wali_siswa)
            ->where("UPPER(TRIM(d.status_aktif)) = 'AKTIF'", null, false);
    }

    private function scope_anak_terkunci($id_wali_siswa, $id_siswa)
    {
        return $this->db->query(
            "SELECT s.id AS id_siswa, s.nis, s.nama_lengkap AS nama_siswa, s.status_siswa, "
            ."s.status_aktif AS status_aktif_siswa, sd.id AS id_saldo, sd.status_dompet "
            ."FROM wali_siswa_detail d INNER JOIN siswa s ON s.id=d.id_siswa "
            ."LEFT JOIN saldo_dompet_siswa sd ON sd.id_siswa=s.id "
            ."WHERE d.id_wali_siswa=? AND d.id_siswa=? AND UPPER(TRIM(d.status_aktif))='AKTIF' "
            ."LIMIT 1 FOR UPDATE",
            array($id_wali_siswa, $id_siswa)
        )->row_array();
    }

    private function validasi_topup($anak, $pengaturan, $rekening, $whatsapp, $nominal)
    {
        if (!$anak) {
            return 'Anak tidak ditemukan atau tidak terhubung dengan akun Wali.';
        }
        if (strtoupper(trim((string) $anak['status_siswa'])) !== 'AKTIF'
            || strtoupper(trim((string) $anak['status_aktif_siswa'])) !== 'AKTIF') {
            return 'Pengajuan hanya dapat dibuat untuk siswa aktif.';
        }
        if (!$anak['id_saldo']) {
            return 'Dompet siswa belum tersedia.';
        }
        if (!$pengaturan || strtoupper(trim((string) $pengaturan['status_layanan'])) !== 'AKTIF') {
            return 'Layanan isi saldo sedang tidak aktif.';
        }
        $minimal = (int) $pengaturan['minimal_isi_saldo'];
        $maksimal = (int) $pengaturan['maksimal_isi_saldo'];
        if ($minimal <= 0 || $maksimal < $minimal || $nominal < $minimal || $nominal > $maksimal) {
            return 'Nominal isi saldo harus antara Rp '.number_format($minimal, 0, ',', '.').' dan Rp '.number_format($maksimal, 0, ',', '.').'.';
        }
        if ((int) $pengaturan['biaya_layanan'] < 0 || $nominal > 2147483647 - (int) $pengaturan['biaya_layanan']) {
            return 'Perhitungan total transfer tidak valid.';
        }
        if (!$rekening) {
            return 'Rekening tujuan tidak ditemukan atau tidak aktif.';
        }
        if (!$whatsapp || !$this->nomor_whatsapp($whatsapp['nomor'])) {
            return 'Nomor WhatsApp admin belum tersedia.';
        }
        return null;
    }

    private function nomor_whatsapp($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', (string) $nomor);
        if (strpos($nomor, '0') === 0) {
            $nomor = '62'.substr($nomor, 1);
        }
        return strlen($nomor) >= 8 && strlen($nomor) <= 15 ? $nomor : null;
    }

    private function hasil_halaman($rows, $total, $halaman, $per_halaman)
    {
        return array(
            'rows' => $rows,
            'total' => (int) $total,
            'halaman' => (int) $halaman,
            'per_halaman' => (int) $per_halaman,
            'total_halaman' => $total > 0 ? (int) ceil($total / $per_halaman) : 0,
        );
    }

    private function terapkan_filter_tanggal($kolom, $tanggal_awal, $tanggal_akhir)
    {
        if ($tanggal_awal !== null) {
            $this->db->where($kolom.' >=', $tanggal_awal);
        }
        if ($tanggal_akhir !== null) {
            $this->db->where($kolom.' <=', $tanggal_akhir);
        }
    }
}
