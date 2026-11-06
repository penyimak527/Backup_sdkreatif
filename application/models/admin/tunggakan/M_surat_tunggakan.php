<?php
class M_surat_tunggakan extends CI_Model
{

    public function periode_list()
    {
        $sql = $this->db->query("SELECT * FROM master_tahun_ajaran ORDER BY id DESC")->result_array();

        return $sql;
    }

    public function cari_siswa()
    {
        $q = trim((string) $this->input->post('q', true));

        if (strlen($q) < 2) {
            return array();
        }

        $search = '%' . $q . '%';

        $sql = $this->db->query("SELECT
                                    s.id,
                                    s.nis,
                                    s.nisn,
                                    s.nama_lengkap,
                                    w.nama_wali,
                                    w.hubungan AS hubungan_wali,
                                    w.no_telp AS no_telp_wali,
                                    k.id AS id_kelas_setting,
                                    k.id_kelas,
                                    k.nama_kelas,
                                    k.id_periode,
                                    ta.periode
                                FROM siswa s
                                LEFT JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED) = s.id AND UPPER(ks.status_aktif) = 'AKTIF'
                                LEFT JOIN kelas_setting k ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                                LEFT JOIN master_tahun_ajaran ta ON ta.id = CAST(k.id_periode AS UNSIGNED)
                                LEFT JOIN wali_siswa_detail wd ON wd.id = (
                                    SELECT wd2.id FROM wali_siswa_detail wd2
                                    JOIN wali_siswa w2 ON w2.id = wd2.id_wali_siswa AND UPPER(w2.status_aktif) = 'AKTIF'
                                    WHERE wd2.id_siswa = s.id AND UPPER(wd2.status_aktif) = 'AKTIF'
                                    ORDER BY (UPPER(wd2.status_utama) = 'IYA') DESC, wd2.id DESC LIMIT 1
                                )
                                LEFT JOIN wali_siswa w ON w.id = wd.id_wali_siswa
                                WHERE (s.nama_lengkap LIKE ?
                                OR s.nis LIKE ?
                                OR s.nisn LIKE ?) AND UPPER(s.status_siswa)='AKTIF'
                                ORDER BY s.nama_lengkap ASC
                                LIMIT 20", array($search, $search, $search))->result_array();

        return $sql;
    }

    public function siswa_by_id($id)
    {
        $id = (int) $id;

        $sql = $this->db->query("SELECT
                                    s.id,
                                    s.nis,
                                    s.nisn,
                                    s.nama_lengkap,
                                    w.nama_wali,
                                    w.hubungan AS hubungan_wali,
                                    w.no_telp AS no_telp_wali,
                                    k.id AS id_kelas_setting,
                                    k.id_kelas,
                                    k.nama_kelas,
                                    k.id_periode,
                                    ta.periode
                                FROM siswa s
                                LEFT JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED) = s.id AND UPPER(ks.status_aktif) = 'AKTIF'
                                LEFT JOIN kelas_setting k ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                                LEFT JOIN master_tahun_ajaran ta ON ta.id = CAST(k.id_periode AS UNSIGNED)
                                LEFT JOIN wali_siswa_detail wd ON wd.id = (
                                    SELECT wd2.id FROM wali_siswa_detail wd2
                                    JOIN wali_siswa w2 ON w2.id = wd2.id_wali_siswa AND UPPER(w2.status_aktif) = 'AKTIF'
                                    WHERE wd2.id_siswa = s.id AND UPPER(wd2.status_aktif) = 'AKTIF'
                                    ORDER BY (UPPER(wd2.status_utama) = 'IYA') DESC, wd2.id DESC LIMIT 1
                                )
                                LEFT JOIN wali_siswa w ON w.id = wd.id_wali_siswa
                                WHERE s.id = ?
                                LIMIT 1", array($id))->row_array();

        if ($sql) {
            $response = array(
                'result' => 'true',
                'siswa' => $sql
            );
        } else {
            $response = array(
                'result' => 'false',
                'message' => 'Siswa tidak ditemukan.'
            );
        }

        return $response;
    }

    public function tagihan()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        $id_periode = (int) $this->input->post('id_periode');
        $tanggal_surat = trim((string) $this->input->post('tanggal_surat', true));

        if ($id_siswa == 0 || $tanggal_surat == '') {
            return array();
        }

        $tanggal = DateTime::createFromFormat('d-m-Y', $tanggal_surat);

        if (!$tanggal || $tanggal->format('d-m-Y') != $tanggal_surat) {
            return array();
        }

        $tanggal_acuan = $tanggal->format('Y-m-d');
        $where_periode = '';
        $params = array($id_siswa, $tanggal_acuan);

        if ($id_periode != 0) {
            $where_periode = 'AND id_periode = ?';
            $params[] = $id_periode;
        }

        $sql = $this->db->query("SELECT *
                                FROM tagihan_siswa
                                WHERE id_siswa = ?
                                AND status_tagihan = 'Aktif'
                                AND dianggap_tunggakan = 'Ya'
                                AND sisa_tagihan > 0
                                AND status_pembayaran NOT IN ('Lunas','Dibebaskan','Dibatalkan')
                                AND STR_TO_DATE(tanggal_jatuh_tempo, '%d-%m-%Y') <= ?
                                $where_periode
                                ORDER BY STR_TO_DATE(tanggal_jatuh_tempo, '%d-%m-%Y') ASC, id ASC", $params)->result_array();

        return $sql;
    }

    public function simpan()
    {
        $id_siswa = (int) $this->input->post('id_siswa');
        $tagihan = json_decode((string) $this->input->post('tagihan'), true);
        $tanggal_surat = trim((string) $this->input->post('tanggal_surat', true));
        $nama_penandatangan = trim((string) $this->input->post('nama_penandatangan', true));
        $jabatan_penandatangan = trim((string) $this->input->post('jabatan_penandatangan', true));
        $catatan = trim((string) $this->input->post('catatan', true));

        if ($id_siswa == 0 || !is_array($tagihan) || count($tagihan) == 0) {
            $response = array(
                'result' => 'false',
                'message' => 'Siswa dan minimal satu tagihan wajib dipilih.'
            );

            return $response;
        }

        if ($tanggal_surat == '' || $nama_penandatangan == '' || $jabatan_penandatangan == '') {
            $response = array(
                'result' => 'false',
                'message' => 'Tanggal dan penandatangan wajib diisi.'
            );

            return $response;
        }

        $tanggal = DateTime::createFromFormat('d-m-Y', $tanggal_surat);

        if (!$tanggal || $tanggal->format('d-m-Y') != $tanggal_surat) {
            $response = array(
                'result' => 'false',
                'message' => 'Format tanggal surat tidak valid.'
            );

            return $response;
        }

        $tanggal_acuan = $tanggal->format('Y-m-d');
        $batas_bulan = (int) $tanggal->format('n');
        $batas_tahun = (int) $tanggal->format('Y');

        $siswa = $this->db->query("SELECT
                                    s.*,
                                    k.id AS id_kelas_setting,
                                    k.id_kelas,
                                    k.nama_kelas,
                                    k.id_periode,
                                    ta.periode
                                FROM siswa s
                                LEFT JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED) = s.id AND UPPER(ks.status_aktif) = 'AKTIF'
                                LEFT JOIN kelas_setting k ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                                LEFT JOIN master_tahun_ajaran ta ON ta.id = CAST(k.id_periode AS UNSIGNED)
                                LEFT JOIN wali_siswa_detail wd ON wd.id = (
                                    SELECT wd2.id FROM wali_siswa_detail wd2
                                    JOIN wali_siswa w2 ON w2.id = wd2.id_wali_siswa AND UPPER(w2.status_aktif) = 'AKTIF'
                                    WHERE wd2.id_siswa = s.id AND UPPER(wd2.status_aktif) = 'AKTIF'
                                    ORDER BY (UPPER(wd2.status_utama) = 'IYA') DESC, wd2.id DESC LIMIT 1
                                )
                                LEFT JOIN wali_siswa w ON w.id = wd.id_wali_siswa
                                WHERE s.id = ?
                                LIMIT 1", array($id_siswa))->row_array();

        if (!$siswa) {
            $response = array(
                'result' => 'false',
                'message' => 'Siswa tidak ditemukan.'
            );

            return $response;
        }

        $id_tagihan = array_values(array_unique(array_filter(array_map('intval', $tagihan))));

        if (count($id_tagihan) == 0) {
            $response = array(
                'result' => 'false',
                'message' => 'Minimal satu tagihan wajib dipilih.'
            );

            return $response;
        }

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('status_tagihan', 'Aktif');
        $this->db->where('dianggap_tunggakan', 'Ya');
        $this->db->where('sisa_tagihan >', 0);
        $this->db->where_not_in('status_pembayaran', array('Lunas', 'Dibebaskan', 'Dibatalkan'));
        $this->db->where("STR_TO_DATE(tanggal_jatuh_tempo, '%d-%m-%Y') <=", $tanggal_acuan);
        $this->db->where_in('id', $id_tagihan);
        $this->db->order_by("STR_TO_DATE(tanggal_jatuh_tempo, '%d-%m-%Y')", 'ASC', false);
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get('tagihan_siswa')->result_array();

        if (count($rows) != count($id_tagihan)) {
            $response = array(
                'result' => 'false',
                'message' => 'Salah satu tagihan belum jatuh tempo atau tidak dapat dimasukkan ke surat.'
            );

            return $response;
        }

        $total_tunggakan = 0;
        foreach ($rows as $row) {
            $total_tunggakan += (float) $row['sisa_tagihan'];
        }

        $user = $this->session->userdata('admin');
        $id_user = is_array($user) ? (int) ($user['id_user'] ?? $user['id'] ?? 0) : 0;
        $nama_user = is_array($user) ? (string) ($user['nama_lengkap'] ?? $user['nama'] ?? $user['username'] ?? 'Administrator') : 'Administrator';

        $this->db->trans_begin();

        $kode_tanggal = date('Ym');
        $prefix = 'STG/' . $kode_tanggal . '/';
        $last = $this->db->query("SELECT no_surat
                                FROM tagihan_surat_tunggakan
                                WHERE no_surat LIKE ?
                                ORDER BY id DESC
                                LIMIT 1", array($prefix . '%'))->row_array();

        $nomor_urut = 1;
        if ($last && $last['no_surat'] != '') {
            $pecah = explode('/', $last['no_surat']);
            $nomor_urut = ((int) end($pecah)) + 1;
        }

        $no_surat = $prefix . str_pad($nomor_urut, 5, '0', STR_PAD_LEFT);

        $data = array(
            'no_surat' => $no_surat,
            'tanggal_surat' => $tanggal_surat,
            'id_siswa' => $id_siswa,
            'nis' => $siswa['nis'],
            'nisn' => $siswa['nisn'],
            'nama_siswa' => $siswa['nama_lengkap'],
            'id_kelas_setting' => (int) ($siswa['id_kelas_setting'] ?? 0),
            'id_kelas' => (int) ($siswa['id_kelas'] ?? 0),
            'nama_kelas' => $siswa['nama_kelas'] ?? '-',
            'id_periode' => (int) ($siswa['id_periode'] ?? 0),
            'periode' => $siswa['periode'] ?? '-',
            'batas_bulan' => $batas_bulan,
            'batas_tahun' => $batas_tahun,
            'total_tunggakan' => $total_tunggakan,
            'jumlah_tagihan' => count($rows),
            'nama_penandatangan' => $nama_penandatangan,
            'jabatan_penandatangan' => $jabatan_penandatangan,
            'catatan_surat' => $catatan,
            'status_cetak' => 'Belum',
            'status_kirim_whatsapp' => 'Belum',
            'status_surat' => 'Aktif',
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->insert('tagihan_surat_tunggakan', $data);
        $id_surat = (int) $this->db->insert_id();

        foreach ($rows as $row) {
            $data_detail = array(
                'id_surat_tunggakan' => $id_surat,
                'no_surat' => $no_surat,
                'id_tagihan_siswa' => $row['id'],
                'no_tagihan' => $row['no_tagihan'],
                'nama_tagihan' => $row['nama_tagihan'],
                'bulan' => $row['bulan'],
                'nama_bulan' => $row['nama_bulan'],
                'tahun' => $row['tahun'],
                'nominal_tagihan' => $row['nominal_tagihan'],
                'nominal_dibayar' => $row['nominal_dibayar'],
                'sisa_tagihan' => $row['sisa_tagihan']
            );

            $this->db->insert('tagihan_surat_tunggakan_detail', $data_detail);
        }

        $data_log = array(
            'jenis_aktivitas' => 'Buat Surat Tunggakan',
            'modul' => 'Tunggakan',
            'aksi' => 'Tambah',
            'nama_tabel' => 'tagihan_surat_tunggakan',
            'id_referensi' => (string) $id_surat,
            'nomor_referensi' => $no_surat,
            'keterangan' => 'Surat ' . $siswa['nama_lengkap'] . ' sebesar Rp' . number_format($total_tunggakan, 0, ',', '.'),
            'data_sebelum' => null,
            'data_sesudah' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->insert('tagihan_log_aktivitas', $data_log);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $response = array(
                'result' => 'false',
                'message' => 'Surat gagal disimpan.'
            );
        } else {
            $this->db->trans_commit();

            $response = array(
                'result' => 'true',
                'message' => 'Surat tunggakan berhasil disimpan.',
                'id' => $id_surat,
                'no_surat' => $no_surat
            );
        }

        return $response;
    }

    public function riwayat()
    {
        return $this->db->query("SELECT st.*,w.nama_wali,w.hubungan AS hubungan_wali,w.no_telp AS no_telp_wali
            FROM tagihan_surat_tunggakan st
            LEFT JOIN wali_siswa_detail wd ON wd.id=(SELECT wd2.id FROM wali_siswa_detail wd2 JOIN wali_siswa w2 ON w2.id=wd2.id_wali_siswa WHERE wd2.id_siswa=st.id_siswa AND UPPER(wd2.status_aktif)='AKTIF' AND UPPER(w2.status_aktif)='AKTIF' ORDER BY (UPPER(wd2.status_utama)='IYA') DESC,wd2.id DESC LIMIT 1)
            LEFT JOIN wali_siswa w ON w.id=wd.id_wali_siswa
            ORDER BY st.id DESC LIMIT 200")->result_array();
    }

    public function detail($id)
    {
        $id = (int) $id;

        $header = $this->db->query("SELECT * FROM tagihan_surat_tunggakan WHERE id = ? LIMIT 1", array($id))->row_array();

        if (!$header) {
            $response = array(
                'result' => 'false',
                'message' => 'Surat tidak ditemukan.'
            );

            return $response;
        }

        $detail = $this->db->query("SELECT *
                                    FROM tagihan_surat_tunggakan_detail
                                    WHERE id_surat_tunggakan = ?
                                    ORDER BY tahun ASC, bulan ASC", array($id))->result_array();

        $siswa = $this->db->query("SELECT * FROM siswa WHERE id = ? LIMIT 1", array((int) $header['id_siswa']))->row_array();

        $response = array(
            'result' => 'true',
            'header' => $header,
            'detail' => $detail,
            'siswa' => $siswa
        );

        return $response;
    }

    public function siapkan_whatsapp()
    {
        $id = (int) $this->input->post('id');
        $pesan = trim((string) $this->input->post('pesan', false));

        $header = $this->db->query("SELECT * FROM tagihan_surat_tunggakan WHERE id = ? LIMIT 1", array($id))->row_array();

        if (!$header) {
            $response = array(
                'result' => 'false',
                'message' => 'Surat tidak ditemukan.'
            );

            return $response;
        }

        $wali = $this->wali_by_siswa((int)$header['id_siswa']);
        $sumber = trim((string)$this->input->post('sumber_nomor', true));
        if (!in_array($sumber, array('Orang Tua','Nomor Lain'), true)) $sumber = 'Orang Tua';
        $nama_penerima = trim((string)$this->input->post('nama_penerima', true));
        if ($nama_penerima === '') $nama_penerima = trim((string)($wali['nama_wali'] ?? '')) ?: 'Bapak/Ibu Wali';
        $hubungan = trim((string)$this->input->post('hubungan_penerima', true));
        if ($hubungan === '') $hubungan = $sumber === 'Nomor Lain' ? 'Lainnya' : (trim((string)($wali['hubungan'] ?? '')) ?: 'Wali');
        $nomor_input = trim((string)$this->input->post('nomor_whatsapp', true));
        if ($nomor_input === '') $nomor_input = (string)($wali['no_telp'] ?? '');
        $nomor = preg_replace('/[^0-9]/', '', $nomor_input);
        if (substr($nomor, 0, 1) == '0') $nomor = '62' . substr($nomor, 1);
        if (substr($nomor,0,2)!=='62' || strlen($nomor)<10 || strlen($nomor)>16) return array('result'=>'false','message'=>'Nomor WhatsApp tidak valid. Gunakan nomor Indonesia yang aktif.');

        if ($pesan == '') {
            $template = $this->db->query("SELECT *
                                        FROM spp_template_whatsapp
                                        WHERE jenis_template = 'Surat Tunggakan'
                                        AND status = 'Aktif'
                                        ORDER BY (status_default = 'Ya') DESC, id DESC
                                        LIMIT 1")->row_array();

            if ($template) {
                $pesan = $template['isi_template'];
            } else {
                $pesan = 'Yth. Bapak/Ibu {nama_wali}, berikut kami sampaikan surat pemberitahuan tunggakan {nama_siswa} sebesar {total_tunggakan}. Terima kasih.';
            }

            $nama_wali = $nama_penerima ?: 'Bapak/Ibu Wali';

            $replace = array(
                '{nama_wali}' => $nama_wali,
                '{nama_siswa}' => $header['nama_siswa'],
                '{kelas}' => $header['nama_kelas'],
                '{tanggal}' => $header['tanggal_surat'],
                '{no_transaksi}' => $header['no_surat'],
                '{no_surat}' => $header['no_surat'],
                '{total_bayar}' => '',
                '{total_tunggakan}' => 'Rp' . number_format((float) $header['total_tunggakan'], 0, ',', '.'),
                '{jumlah_tagihan}' => ((int)$header['jumlah_tagihan']) . ' tagihan',
                '{nama_sekolah}' => $this->config->item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah',
                '{nama_petugas}' => $header['nama_user']
            );

            $pesan = strtr($pesan, $replace);
        }

        $user = $this->session->userdata('admin');
        $id_user = is_array($user) ? (int) ($user['id_user'] ?? $user['id'] ?? 0) : 0;
        $nama_user = is_array($user) ? (string) ($user['nama_lengkap'] ?? $user['nama'] ?? $user['username'] ?? 'Administrator') : 'Administrator';

        $this->db->trans_begin();

        $data_wa = array(
            'jenis_kirim' => 'Surat Tunggakan',
            'id_referensi' => $id,
            'nomor_referensi' => $header['no_surat'],
            'id_siswa' => $header['id_siswa'],
            'nama_siswa' => $header['nama_siswa'],
            'nama_penerima' => $nama_penerima,
            'hubungan_penerima' => $hubungan,
            'nomor_whatsapp' => $nomor,
            'isi_pesan' => $pesan,
            'metode_kirim' => 'Tautan',
            'status_kirim' => 'Disiapkan',
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->insert('spp_riwayat_whatsapp', $data_wa);
        $this->db->update('tagihan_surat_tunggakan', array('status_kirim_whatsapp' => 'Disiapkan'), array('id' => $id));

        $data_log = array(
            'jenis_aktivitas' => 'Kirim Surat Tunggakan WhatsApp',
            'modul' => 'Tunggakan',
            'aksi' => 'Kirim',
            'nama_tabel' => 'tagihan_surat_tunggakan',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $header['no_surat'],
            'keterangan' => 'Surat disiapkan ke ' . $nomor,
            'data_sebelum' => null,
            'data_sesudah' => null,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->insert('tagihan_log_aktivitas', $data_log);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $response = array(
                'result' => 'false',
                'message' => 'Gagal menyiapkan WhatsApp.'
            );
        } else {
            $this->db->trans_commit();

            $response = array(
                'result' => 'true',
                'message' => 'WhatsApp berhasil disiapkan.',
                'url' => 'https://wa.me/' . $nomor . '?text=' . rawurlencode($pesan),
                'pesan' => $pesan
            );
        }

        return $response;
    }

    private function wali_by_siswa($id)
    {
        return $this->db->query("SELECT w.id,w.nama_wali,w.hubungan,w.no_telp FROM wali_siswa_detail d JOIN wali_siswa w ON w.id=d.id_wali_siswa WHERE d.id_siswa=? AND UPPER(d.status_aktif)='AKTIF' AND UPPER(w.status_aktif)='AKTIF' ORDER BY (UPPER(d.status_utama)='IYA') DESC,d.id DESC LIMIT 1",array((int)$id))->row_array();
    }
}
?>
