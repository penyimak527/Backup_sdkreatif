<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_buat_tagihan extends CI_Model
{
    public function periode_list()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get('master_tahun_ajaran')
            ->result_array();
    }

    public function jenis_list($tipe)
    {
        return $this->db
            ->where('status', 'Aktif')
            ->where('tipe_default', $tipe)
            ->order_by('nama_jenis')
            ->get('tagihan_jenis')
            ->result_array();
    }

    public function kelas_list()
    {
        return $this->db
            ->select('ks.*,ta.periode')
            ->from('kelas_setting ks')
            ->join('master_tahun_ajaran ta', 'ta.id=CAST(ks.id_periode AS UNSIGNED)', 'left')
            ->order_by('ta.id', 'DESC')
            ->order_by('ks.nama_kelas')
            ->get()
            ->result_array();
    }

    public function cari_siswa()
    {
        $q = trim((string) $this->input->post('q', true));
        $id_periode = (int) $this->input->post('id_periode');

        if (strlen($q) < 2 || $id_periode <= 0) {
            return array();
        }

        $like = '%' . $q . '%';

        $sql = $this->db->query("SELECT
                                    s.id,
                                    s.nis,
                                    s.nisn,
                                    s.nama_lengkap,
                                    k.id AS id_kelas_setting,
                                    k.id_kelas,
                                    k.nama_kelas,
                                    k.id_periode,
                                    ta.periode
                                FROM siswa s
                                INNER JOIN kelas_siswa ks
                                    ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                                    AND UPPER(ks.status_aktif) = 'AKTIF'
                                INNER JOIN kelas_setting k
                                    ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                                LEFT JOIN master_tahun_ajaran ta
                                    ON ta.id = CAST(k.id_periode AS UNSIGNED)
                                WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                                AND CAST(k.id_periode AS UNSIGNED) = ?
                                AND (
                                    s.nama_lengkap LIKE ?
                                    OR s.nis LIKE ?
                                    OR s.nisn LIKE ?
                                )
                                ORDER BY s.nama_lengkap
                                LIMIT 30", array($id_periode, $like, $like, $like))->result_array();

        return $sql;
    }

    public function preview_bulanan()
    {
        $id_periode = (int) $this->input->post('id_periode');
        $id_jenis = (int) $this->input->post('id_jenis_tagihan');
        $nama_tagihan = trim((string) $this->input->post('nama_tagihan', true));
        $target_tagihan = trim((string) $this->input->post('target_tagihan', true));
        $model_tarif = $this->input->post('model_tarif_bulanan', true) === 'Berbeda' ? 'Berbeda' : 'Sama';

        $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $this->input->post('nominal_default')));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
            $nominal_default = (float) $nominal_text;
        } else {
            $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
            $nominal_default = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
        }

        $periode = $this->db->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $jenis = $this->db->where('id', $id_jenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();

        if (!$periode || !$jenis) {
            return array('result' => 'false', 'message' => 'Tahun ajaran atau jenis tagihan tidak ditemukan.');
        }

        if ($jenis['tipe_default'] !== 'Bulanan') {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak sesuai dengan Tagihan Bulanan.');
        }

        if ($nama_tagihan === '' || $nominal_default < 0 || !in_array($target_tagihan, array('Semua', 'Kelas', 'Siswa'), true)) {
            return array('result' => 'false', 'message' => 'Nama tagihan, nominal, dan target wajib diisi dengan benar.');
        }

        $target_kelas = $this->input->post('target_kelas');
        $target_siswa = $this->input->post('target_siswa');
        $target_kelas = is_array($target_kelas) ? array_values(array_unique(array_filter(array_map('intval', $target_kelas)))) : array();
        $target_siswa = is_array($target_siswa) ? array_values(array_unique(array_filter(array_map('intval', $target_siswa)))) : array();

        if ($target_tagihan === 'Kelas' && count($target_kelas) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        }

        if ($target_tagihan === 'Siswa' && count($target_siswa) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');
        }

        $bulan_dipilih = $this->input->post('bulan');
        $nominal_bulan = $this->input->post('nominal_bulan');
        $jatuh_tempo_bulan = $this->input->post('jatuh_tempo_bulan');
        $bulan_dipilih = is_array($bulan_dipilih) ? $bulan_dipilih : array();
        $nominal_bulan = is_array($nominal_bulan) ? $nominal_bulan : array();
        $jatuh_tempo_bulan = is_array($jatuh_tempo_bulan) ? $jatuh_tempo_bulan : array();

        if (count($bulan_dipilih) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu bulan tagihan.');
        }

        $bulan_nama = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );

        $tahun_ajaran = explode('/', $periode['periode']);
        $tahun_awal = (int) $tahun_ajaran[0];
        $tahun_akhir = isset($tahun_ajaran[1]) ? (int) $tahun_ajaran[1] : $tahun_awal + 1;
        $periods = array();

        foreach ($bulan_dipilih as $bulan) {
            $bulan = (int) $bulan;
            if ($bulan < 1 || $bulan > 12) {
                continue;
            }

            $tahun = $bulan >= 7 ? $tahun_awal : $tahun_akhir;
            $nominal_value = isset($nominal_bulan[$bulan]) ? $nominal_bulan[$bulan] : $nominal_default;
            $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $nominal_value));

            if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
                $nominal = (float) $nominal_text;
            } else {
                $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
                $nominal = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
            }

            if ($nominal <= 0) {
                $nominal = $nominal_default;
            }

            if ($nominal <= 0) {
                return array('result' => 'false', 'message' => 'Nominal setiap bulan terpilih harus lebih dari nol.');
            }

            $periods[] = array(
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_bulan' => $bulan_nama[$bulan],
                'nominal' => $nominal,
                'jatuh_tempo' => isset($jatuh_tempo_bulan[$bulan]) ? trim((string) $jatuh_tempo_bulan[$bulan]) : ''
            );
        }

        if (count($periods) === 0) {
            return array('result' => 'false', 'message' => 'Bulan tagihan tidak valid.');
        }

        $sql_siswa = "SELECT DISTINCT
                            s.id,
                            s.nis,
                            s.nisn,
                            s.nama_lengkap,
                            s.jk,
                            k.id AS id_kelas_setting,
                            k.id_kelas,
                            k.nama_kelas,
                            k.id_periode,
                            ta.periode
                        FROM siswa s
                        INNER JOIN kelas_siswa ks
                            ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                            AND UPPER(ks.status_aktif) = 'AKTIF'
                        INNER JOIN kelas_setting k
                            ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                        LEFT JOIN master_tahun_ajaran ta
                            ON ta.id = CAST(k.id_periode AS UNSIGNED)
                        WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                        AND CAST(k.id_periode AS UNSIGNED) = ?";
        $params_siswa = array($id_periode);

        if ($target_tagihan === 'Kelas') {
            $sql_siswa .= " AND k.id IN (" . implode(',', array_fill(0, count($target_kelas), '?')) . ")";
            foreach ($target_kelas as $id_kelas) {
                $params_siswa[] = $id_kelas;
            }
        } elseif ($target_tagihan === 'Siswa') {
            $sql_siswa .= " AND s.id IN (" . implode(',', array_fill(0, count($target_siswa), '?')) . ")";
            foreach ($target_siswa as $id_siswa) {
                $params_siswa[] = $id_siswa;
            }
        }

        $sql_siswa .= " ORDER BY s.nama_lengkap";
        $students = $this->db->query($sql_siswa, $params_siswa)->result_array();

        if (count($students) === 0) {
            return array('result' => 'false', 'message' => 'Tidak ada siswa aktif pada target yang dipilih.');
        }

        $student_ids = array_map('intval', array_column($students, 'id'));
        $sql_duplikat = "SELECT
                            id,
                            no_tagihan,
                            id_siswa,
                            nis,
                            nama_siswa,
                            nama_tagihan,
                            bulan,
                            nama_bulan,
                            tahun
                        FROM tagihan_siswa
                        WHERE id_jenis_tagihan = ?
                        AND id_periode = ?
                        AND tipe_tagihan = 'Bulanan'
                        AND status_tagihan = 'Aktif'
                        AND id_siswa IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ")
                        AND (";
        $params_duplikat = array($id_jenis, $id_periode);
        foreach ($student_ids as $id_siswa) {
            $params_duplikat[] = $id_siswa;
        }

        $periode_where = array();
        foreach ($periods as $p) {
            $periode_where[] = '(bulan = ? AND tahun = ?)';
            $params_duplikat[] = (int) $p['bulan'];
            $params_duplikat[] = (int) $p['tahun'];
        }
        $sql_duplikat .= implode(' OR ', $periode_where) . ") ORDER BY tahun, bulan, nama_siswa";
        $duplicates_all = $this->db->query($sql_duplikat, $params_duplikat)->result_array();
        $duplicates = array_slice($duplicates_all, 0, 50);

        $total_nominal = 0;
        foreach ($periods as $p) {
            $total_nominal += count($students) * (float) $p['nominal'];
        }

        return array(
            'result' => 'true',
            'message' => count($duplicates_all) > 0
                ? 'Preview berhasil, tetapi ditemukan tagihan yang sudah ada. Tagihan tidak dapat diterbitkan sebelum konflik diselesaikan.'
                : 'Preview berhasil.',
            'jumlah_siswa' => count($students),
            'jumlah_baris' => count($students) * count($periods),
            'total_nominal' => $total_nominal,
            'periods' => $periods,
            'students' => array_slice($students, 0, 20),
            'has_duplicate' => count($duplicates_all) > 0,
            'duplicate_count' => count($duplicates_all),
            'duplicates' => $duplicates
        );
    }

    public function simpan_bulanan()
    {
        $id_periode = (int) $this->input->post('id_periode');
        $id_jenis = (int) $this->input->post('id_jenis_tagihan');
        $nama_tagihan = trim((string) $this->input->post('nama_tagihan', true));
        $target_tagihan = trim((string) $this->input->post('target_tagihan', true));
        $dianggap_tunggakan = $this->input->post('dianggap_tunggakan', true) === 'Tidak' ? 'Tidak' : 'Ya';
        $keterangan = trim((string) $this->input->post('keterangan', true));
        $model_tarif = $this->input->post('model_tarif_bulanan', true) === 'Berbeda' ? 'Berbeda' : 'Sama';
        $mode = $this->input->post('mode_simpan', true) === 'Draft' ? 'Draft' : 'Terbitkan';

        $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $this->input->post('nominal_default')));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
            $nominal_default = (float) $nominal_text;
        } else {
            $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
            $nominal_default = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
        }

        $periode = $this->db->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $jenis = $this->db->where('id', $id_jenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();

        if (!$periode || !$jenis) {
            return array('result' => 'false', 'message' => 'Tahun ajaran atau jenis tagihan tidak ditemukan.');
        }

        if ($jenis['tipe_default'] !== 'Bulanan') {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak sesuai dengan Tagihan Bulanan.');
        }

        if ($nama_tagihan === '' || $nominal_default < 0 || !in_array($target_tagihan, array('Semua', 'Kelas', 'Siswa'), true)) {
            return array('result' => 'false', 'message' => 'Nama tagihan, nominal, dan target wajib diisi dengan benar.');
        }

        $target_kelas = $this->input->post('target_kelas');
        $target_siswa = $this->input->post('target_siswa');
        $target_kelas = is_array($target_kelas) ? array_values(array_unique(array_filter(array_map('intval', $target_kelas)))) : array();
        $target_siswa = is_array($target_siswa) ? array_values(array_unique(array_filter(array_map('intval', $target_siswa)))) : array();

        if ($target_tagihan === 'Kelas' && count($target_kelas) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        }

        if ($target_tagihan === 'Siswa' && count($target_siswa) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');
        }

        $bulan_dipilih = $this->input->post('bulan');
        $nominal_bulan = $this->input->post('nominal_bulan');
        $jatuh_tempo_bulan = $this->input->post('jatuh_tempo_bulan');
        $bulan_dipilih = is_array($bulan_dipilih) ? $bulan_dipilih : array();
        $nominal_bulan = is_array($nominal_bulan) ? $nominal_bulan : array();
        $jatuh_tempo_bulan = is_array($jatuh_tempo_bulan) ? $jatuh_tempo_bulan : array();

        if (count($bulan_dipilih) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu bulan tagihan.');
        }

        $bulan_nama = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );

        $tahun_ajaran = explode('/', $periode['periode']);
        $tahun_awal = (int) $tahun_ajaran[0];
        $tahun_akhir = isset($tahun_ajaran[1]) ? (int) $tahun_ajaran[1] : $tahun_awal + 1;
        $periods = array();

        foreach ($bulan_dipilih as $bulan) {
            $bulan = (int) $bulan;
            if ($bulan < 1 || $bulan > 12) {
                continue;
            }

            $tahun = $bulan >= 7 ? $tahun_awal : $tahun_akhir;
            $nominal_value = isset($nominal_bulan[$bulan]) ? $nominal_bulan[$bulan] : $nominal_default;
            $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $nominal_value));

            if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
                $nominal = (float) $nominal_text;
            } else {
                $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
                $nominal = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
            }

            if ($nominal <= 0) {
                $nominal = $nominal_default;
            }

            if ($nominal <= 0) {
                return array('result' => 'false', 'message' => 'Nominal setiap bulan terpilih harus lebih dari nol.');
            }

            $periods[] = array(
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_bulan' => $bulan_nama[$bulan],
                'nominal' => $nominal,
                'jatuh_tempo' => isset($jatuh_tempo_bulan[$bulan]) ? trim((string) $jatuh_tempo_bulan[$bulan]) : ''
            );
        }

        if (count($periods) === 0) {
            return array('result' => 'false', 'message' => 'Bulan tagihan tidak valid.');
        }

        $sql_siswa = "SELECT DISTINCT
                            s.id,
                            s.nis,
                            s.nisn,
                            s.nama_lengkap,
                            s.jk,
                            k.id AS id_kelas_setting,
                            k.id_kelas,
                            k.nama_kelas,
                            k.id_periode,
                            ta.periode
                        FROM siswa s
                        INNER JOIN kelas_siswa ks
                            ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                            AND UPPER(ks.status_aktif) = 'AKTIF'
                        INNER JOIN kelas_setting k
                            ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                        LEFT JOIN master_tahun_ajaran ta
                            ON ta.id = CAST(k.id_periode AS UNSIGNED)
                        WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                        AND CAST(k.id_periode AS UNSIGNED) = ?";
        $params_siswa = array($id_periode);

        if ($target_tagihan === 'Kelas') {
            $sql_siswa .= " AND k.id IN (" . implode(',', array_fill(0, count($target_kelas), '?')) . ")";
            foreach ($target_kelas as $id_kelas) {
                $params_siswa[] = $id_kelas;
            }
        } elseif ($target_tagihan === 'Siswa') {
            $sql_siswa .= " AND s.id IN (" . implode(',', array_fill(0, count($target_siswa), '?')) . ")";
            foreach ($target_siswa as $id_siswa) {
                $params_siswa[] = $id_siswa;
            }
        }

        $sql_siswa .= " ORDER BY s.nama_lengkap";
        $students = $this->db->query($sql_siswa, $params_siswa)->result_array();

        if (count($students) === 0) {
            return array('result' => 'false', 'message' => 'Tidak ada siswa aktif pada target yang dipilih.');
        }

        if ($mode === 'Terbitkan') {
            $student_ids = array_map('intval', array_column($students, 'id'));
            $sql_duplikat = "SELECT
                                id,
                                no_tagihan,
                                id_siswa,
                                nis,
                                nama_siswa,
                                nama_tagihan,
                                bulan,
                                nama_bulan,
                                tahun
                            FROM tagihan_siswa
                            WHERE id_jenis_tagihan = ?
                            AND id_periode = ?
                            AND tipe_tagihan = 'Bulanan'
                            AND status_tagihan = 'Aktif'
                            AND id_siswa IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ")
                            AND (";
            $params_duplikat = array($id_jenis, $id_periode);
            foreach ($student_ids as $id_siswa) {
                $params_duplikat[] = $id_siswa;
            }

            $periode_where = array();
            foreach ($periods as $p) {
                $periode_where[] = '(bulan = ? AND tahun = ?)';
                $params_duplikat[] = (int) $p['bulan'];
                $params_duplikat[] = (int) $p['tahun'];
            }
            $sql_duplikat .= implode(' OR ', $periode_where) . ") ORDER BY tahun, bulan, nama_siswa";
            $duplicates_all = $this->db->query($sql_duplikat, $params_duplikat)->result_array();

            if (count($duplicates_all) > 0) {
                return array(
                    'result' => 'false',
                    'message' => 'Tagihan tidak dapat diterbitkan karena ditemukan ' . count($duplicates_all) . ' tagihan yang sudah ada untuk jenis, siswa, bulan, dan tahun ajaran yang sama.',
                    'duplicate_count' => count($duplicates_all),
                    'duplicates' => array_slice($duplicates_all, 0, 50)
                );
            }
        }

        $user = $this->session->userdata('admin');
        $id_user = is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0;
        $nama_user = is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator';
        $tanggal = date('d-m-Y');
        $waktu = date('H:i:s');

        $kode_date = date('Ym');
        $kode_like = 'TGH/' . $kode_date . '/';
        $last_master = $this->db->select('kode_tagihan')
            ->like('kode_tagihan', $kode_like, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tagihan_master')
            ->row_array();
        $nomor_master = 1;
        if ($last_master && !empty($last_master['kode_tagihan'])) {
            $parts = explode('/', $last_master['kode_tagihan']);
            $nomor_master = ((int) end($parts)) + 1;
        }
        $kode_tagihan = $kode_like . str_pad($nomor_master, 5, '0', STR_PAD_LEFT);

        $first = $periods[0];
        $last = $periods[count($periods) - 1];

        $master = array(
            'kode_tagihan' => $kode_tagihan,
            'id_jenis_tagihan' => $id_jenis,
            'nama_jenis_tagihan' => $jenis['nama_jenis'],
            'nama_tagihan' => $nama_tagihan,
            'tipe_tagihan' => 'Bulanan',
            'id_periode' => $id_periode,
            'periode' => $periode['periode'],
            'semester' => null,
            'nominal_default' => $nominal_default,
            'model_tarif_bulanan' => $model_tarif,
            'bulan_mulai' => $first['bulan'],
            'tahun_mulai' => $first['tahun'],
            'bulan_selesai' => $last['bulan'],
            'tahun_selesai' => $last['tahun'],
            'bulan_penagihan' => $first['bulan'],
            'tahun_penagihan' => $first['tahun'],
            'tanggal_jatuh_tempo' => $first['jatuh_tempo'],
            'target_tagihan' => $target_tagihan,
            'dianggap_tunggakan' => $dianggap_tunggakan,
            'status_generate' => $mode === 'Draft' ? 'Belum' : 'Selesai',
            'status' => $mode === 'Draft' ? 'Draft' : 'Aktif',
            'keterangan' => $keterangan,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->trans_begin();
        $this->db->insert('tagihan_master', $master);
        $id_master = $this->db->insert_id();

        foreach ($periods as $p) {
            $this->db->insert('tagihan_tarif_bulan', array(
                'id_tagihan_master' => $id_master,
                'bulan' => $p['bulan'],
                'nama_bulan' => $p['nama_bulan'],
                'tahun' => $p['tahun'],
                'nominal' => $p['nominal'],
                'tanggal_jatuh_tempo' => $p['jatuh_tempo'],
                'status' => 'Aktif',
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'id_user' => $id_user,
                'nama_user' => $nama_user
            ));
        }

        $class_seen = array();
        // Pada Draft bertarget Kelas, simpan seluruh kelas yang dipilih,
        // termasuk kelas yang belum memiliki siswa aktif.
        if ($mode === 'Draft' && $target_tagihan === 'Kelas') {
            $selected_classes = $this->db
                ->where_in('id', $target_kelas)
                ->where('CAST(id_periode AS UNSIGNED) = ' . (int) $id_periode, null, false)
                ->get('kelas_setting')->result_array();
            foreach ($selected_classes as $kelas) {
                $this->db->insert('tagihan_target_kelas', array(
                    'id_tagihan_master' => $id_master,
                    'id_kelas_setting' => (int) $kelas['id'],
                    'id_kelas' => (int) $kelas['id_kelas'],
                    'nama_kelas' => $kelas['nama_kelas'],
                    'id_periode' => (int) $kelas['id_periode'],
                    'periode' => $periode['periode'],
                    'semester' => null,
                    'nominal_kelas' => $nominal_default,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
                $class_seen[(int) $kelas['id']] = true;
            }
        }
        foreach ($students as $s) {
            if (!isset($class_seen[$s['id_kelas_setting']])) {
                $this->db->insert('tagihan_target_kelas', array(
                    'id_tagihan_master' => $id_master,
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'id_periode' => (int) $s['id_periode'],
                    'periode' => $s['periode'],
                    'semester' => null,
                    'nominal_kelas' => $nominal_default,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
                $class_seen[$s['id_kelas_setting']] = true;
            }

            if ($target_tagihan === 'Siswa') {
                $this->db->insert('tagihan_target_siswa', array(
                    'id_tagihan_master' => $id_master,
                    'id_siswa' => (int) $s['id'],
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'nama_siswa' => $s['nama_lengkap'],
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'nominal_target' => $nominal_default,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
            }
        }

        $generated = 0;
        if ($mode === 'Terbitkan') {
            $no_like = 'TAG/' . $kode_date . '/';
            $last_no = $this->db->select('no_tagihan')
                ->like('no_tagihan', $no_like, 'after')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('tagihan_siswa')
                ->row_array();
            $nomor_siswa = 1;
            if ($last_no && !empty($last_no['no_tagihan'])) {
                $parts = explode('/', $last_no['no_tagihan']);
                $nomor_siswa = ((int) end($parts)) + 1;
            }

            foreach ($students as $s) {
                foreach ($periods as $p) {
                    $no_tagihan = $no_like . str_pad($nomor_siswa, 5, '0', STR_PAD_LEFT);
                    $nomor_siswa++;
                    $nominal = (float) $p['nominal'];

                    $this->db->insert('tagihan_siswa', array(
                        'no_tagihan' => $no_tagihan,
                        'id_tagihan_master' => $id_master,
                        'kode_tagihan' => $kode_tagihan,
                        'id_jenis_tagihan' => $id_jenis,
                        'nama_jenis_tagihan' => $jenis['nama_jenis'],
                        'nama_tagihan' => $nama_tagihan,
                        'tipe_tagihan' => 'Bulanan',
                        'id_periode' => $id_periode,
                        'periode' => $periode['periode'],
                        'semester' => null,
                        'bulan' => $p['bulan'],
                        'nama_bulan' => $p['nama_bulan'],
                        'tahun' => $p['tahun'],
                        'tanggal_jatuh_tempo' => $p['jatuh_tempo'],
                        'id_siswa' => (int) $s['id'],
                        'nis' => $s['nis'],
                        'nisn' => $s['nisn'],
                        'nama_siswa' => $s['nama_lengkap'],
                        'id_kelas_setting' => (int) $s['id_kelas_setting'],
                        'id_kelas' => (int) $s['id_kelas'],
                        'nama_kelas' => $s['nama_kelas'],
                        'nominal_awal' => $nominal,
                        'jenis_keringanan' => null,
                        'nilai_keringanan' => 0,
                        'nominal_tagihan' => $nominal,
                        'nominal_dibayar' => 0,
                        'sisa_tagihan' => $nominal,
                        'dianggap_tunggakan' => $dianggap_tunggakan,
                        'status_pembayaran' => 'Belum Dibayar',
                        'status_tagihan' => 'Aktif',
                        'keterangan' => $keterangan,
                        'tanggal_generate' => $tanggal,
                        'waktu_generate' => $waktu,
                        'id_user_generate' => $id_user,
                        'nama_user_generate' => $nama_user
                    ));
                    $generated++;
                }
            }
        }

        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => $mode === 'Draft' ? 'Simpan Draft Tagihan' : 'Terbitkan Tagihan',
            'modul' => 'Tagihan',
            'aksi' => 'Tambah',
            'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id_master,
            'nomor_referensi' => $kode_tagihan,
            'keterangan' => $nama_tagihan . ' - ' . $generated . ' tagihan siswa',
            'data_sebelum' => null,
            'data_sesudah' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        ));

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
            'message' => $mode === 'Draft'
                ? 'Draft tagihan berhasil disimpan.'
                : $generated . ' tagihan siswa berhasil diterbitkan.',
            'id' => $id_master
        );
    }

    public function preview_langsung()
    {
        $id_periode = (int) $this->input->post('id_periode');
        $id_jenis = (int) $this->input->post('id_jenis_tagihan');
        $nama_tagihan = trim((string) $this->input->post('nama_tagihan', true));
        $target_tagihan = trim((string) $this->input->post('target_tagihan', true));
        $bulan = (int) $this->input->post('bulan_penagihan');
        $tahun = (int) $this->input->post('tahun_penagihan');
        $jatuh_tempo = trim((string) $this->input->post('tanggal_jatuh_tempo', true));

        $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $this->input->post('nominal_default')));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
            $nominal = (float) $nominal_text;
        } else {
            $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
            $nominal = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
        }

        $periode = $this->db->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $jenis = $this->db->where('id', $id_jenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();

        if (!$periode || !$jenis) {
            return array('result' => 'false', 'message' => 'Tahun ajaran atau jenis tagihan tidak ditemukan.');
        }

        if ($jenis['tipe_default'] !== 'Langsung') {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak sesuai dengan Tagihan Langsung.');
        }

        if ($nama_tagihan === '' || $nominal <= 0 || !in_array($target_tagihan, array('Semua', 'Kelas', 'Siswa'), true)) {
            return array('result' => 'false', 'message' => 'Nama tagihan, nominal, dan target wajib diisi dengan benar.');
        }

        if ($bulan < 1 || $bulan > 12) {
            return array('result' => 'false', 'message' => 'Bulan tagihan wajib dipilih.');
        }

        $tahun_ajaran = explode('/', $periode['periode']);
        $tahun_awal = (int) $tahun_ajaran[0];
        $tahun_akhir = isset($tahun_ajaran[1]) ? (int) $tahun_ajaran[1] : $tahun_awal + 1;
        if ($tahun <= 0) {
            $tahun = $bulan >= 7 ? $tahun_awal : $tahun_akhir;
        }

        $bulan_nama = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );

        $target_kelas = $this->input->post('target_kelas');
        $target_siswa = $this->input->post('target_siswa');
        $target_kelas = is_array($target_kelas) ? array_values(array_unique(array_filter(array_map('intval', $target_kelas)))) : array();
        $target_siswa = is_array($target_siswa) ? array_values(array_unique(array_filter(array_map('intval', $target_siswa)))) : array();

        if ($target_tagihan === 'Kelas' && count($target_kelas) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        }

        if ($target_tagihan === 'Siswa' && count($target_siswa) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');
        }

        $sql_siswa = "SELECT DISTINCT
                            s.id,
                            s.nis,
                            s.nisn,
                            s.nama_lengkap,
                            s.jk,
                            k.id AS id_kelas_setting,
                            k.id_kelas,
                            k.nama_kelas,
                            k.id_periode,
                            ta.periode
                        FROM siswa s
                        INNER JOIN kelas_siswa ks
                            ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                            AND UPPER(ks.status_aktif) = 'AKTIF'
                        INNER JOIN kelas_setting k
                            ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                        LEFT JOIN master_tahun_ajaran ta
                            ON ta.id = CAST(k.id_periode AS UNSIGNED)
                        WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                        AND CAST(k.id_periode AS UNSIGNED) = ?";
        $params_siswa = array($id_periode);

        if ($target_tagihan === 'Kelas') {
            $sql_siswa .= " AND k.id IN (" . implode(',', array_fill(0, count($target_kelas), '?')) . ")";
            foreach ($target_kelas as $id_kelas) {
                $params_siswa[] = $id_kelas;
            }
        } elseif ($target_tagihan === 'Siswa') {
            $sql_siswa .= " AND s.id IN (" . implode(',', array_fill(0, count($target_siswa), '?')) . ")";
            foreach ($target_siswa as $id_siswa) {
                $params_siswa[] = $id_siswa;
            }
        }

        $sql_siswa .= " ORDER BY s.nama_lengkap";
        $students = $this->db->query($sql_siswa, $params_siswa)->result_array();

        if (count($students) === 0) {
            return array('result' => 'false', 'message' => 'Tidak ada siswa aktif pada target yang dipilih.');
        }

        $periods = array(array(
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nama_bulan' => $bulan_nama[$bulan],
            'nominal' => $nominal,
            'jatuh_tempo' => $jatuh_tempo
        ));

        return array(
            'result' => 'true',
            'message' => 'Preview berhasil.',
            'jumlah_siswa' => count($students),
            'jumlah_baris' => count($students),
            'total_nominal' => count($students) * $nominal,
            'periods' => $periods,
            'students' => array_slice($students, 0, 20),
            'has_duplicate' => false,
            'duplicate_count' => 0,
            'duplicates' => array()
        );
    }

    public function simpan_langsung()
    {
        $id_periode = (int) $this->input->post('id_periode');
        $id_jenis = (int) $this->input->post('id_jenis_tagihan');
        $nama_tagihan = trim((string) $this->input->post('nama_tagihan', true));
        $target_tagihan = trim((string) $this->input->post('target_tagihan', true));
        $dianggap_tunggakan = $this->input->post('dianggap_tunggakan', true) === 'Tidak' ? 'Tidak' : 'Ya';
        $keterangan = trim((string) $this->input->post('keterangan', true));
        $mode = $this->input->post('mode_simpan', true) === 'Draft' ? 'Draft' : 'Terbitkan';
        $bulan = (int) $this->input->post('bulan_penagihan');
        $tahun = (int) $this->input->post('tahun_penagihan');
        $jatuh_tempo = trim((string) $this->input->post('tanggal_jatuh_tempo', true));

        $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $this->input->post('nominal_default')));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
            $nominal = (float) $nominal_text;
        } else {
            $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
            $nominal = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
        }

        $periode = $this->db->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $jenis = $this->db->where('id', $id_jenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();

        if (!$periode || !$jenis) {
            return array('result' => 'false', 'message' => 'Tahun ajaran atau jenis tagihan tidak ditemukan.');
        }

        if ($jenis['tipe_default'] !== 'Langsung') {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak sesuai dengan Tagihan Langsung.');
        }

        if ($nama_tagihan === '' || $nominal <= 0 || !in_array($target_tagihan, array('Semua', 'Kelas', 'Siswa'), true)) {
            return array('result' => 'false', 'message' => 'Nama tagihan, nominal, dan target wajib diisi dengan benar.');
        }

        if ($bulan < 1 || $bulan > 12) {
            return array('result' => 'false', 'message' => 'Bulan tagihan wajib dipilih.');
        }

        $tahun_ajaran = explode('/', $periode['periode']);
        $tahun_awal = (int) $tahun_ajaran[0];
        $tahun_akhir = isset($tahun_ajaran[1]) ? (int) $tahun_ajaran[1] : $tahun_awal + 1;
        if ($tahun <= 0) {
            $tahun = $bulan >= 7 ? $tahun_awal : $tahun_akhir;
        }

        $bulan_nama = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );

        $target_kelas = $this->input->post('target_kelas');
        $target_siswa = $this->input->post('target_siswa');
        $target_kelas = is_array($target_kelas) ? array_values(array_unique(array_filter(array_map('intval', $target_kelas)))) : array();
        $target_siswa = is_array($target_siswa) ? array_values(array_unique(array_filter(array_map('intval', $target_siswa)))) : array();

        if ($target_tagihan === 'Kelas' && count($target_kelas) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        }

        if ($target_tagihan === 'Siswa' && count($target_siswa) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');
        }

        $sql_siswa = "SELECT DISTINCT
                            s.id,
                            s.nis,
                            s.nisn,
                            s.nama_lengkap,
                            s.jk,
                            k.id AS id_kelas_setting,
                            k.id_kelas,
                            k.nama_kelas,
                            k.id_periode,
                            ta.periode
                        FROM siswa s
                        INNER JOIN kelas_siswa ks
                            ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                            AND UPPER(ks.status_aktif) = 'AKTIF'
                        INNER JOIN kelas_setting k
                            ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                        LEFT JOIN master_tahun_ajaran ta
                            ON ta.id = CAST(k.id_periode AS UNSIGNED)
                        WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                        AND CAST(k.id_periode AS UNSIGNED) = ?";
        $params_siswa = array($id_periode);

        if ($target_tagihan === 'Kelas') {
            $sql_siswa .= " AND k.id IN (" . implode(',', array_fill(0, count($target_kelas), '?')) . ")";
            foreach ($target_kelas as $id_kelas) {
                $params_siswa[] = $id_kelas;
            }
        } elseif ($target_tagihan === 'Siswa') {
            $sql_siswa .= " AND s.id IN (" . implode(',', array_fill(0, count($target_siswa), '?')) . ")";
            foreach ($target_siswa as $id_siswa) {
                $params_siswa[] = $id_siswa;
            }
        }

        $sql_siswa .= " ORDER BY s.nama_lengkap";
        $students = $this->db->query($sql_siswa, $params_siswa)->result_array();

        if (count($students) === 0) {
            return array('result' => 'false', 'message' => 'Tidak ada siswa aktif pada target yang dipilih.');
        }

        $user = $this->session->userdata('admin');
        $id_user = is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0;
        $nama_user = is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator';
        $tanggal = date('d-m-Y');
        $waktu = date('H:i:s');

        $kode_date = date('Ym');
        $kode_like = 'TGH/' . $kode_date . '/';
        $last_master = $this->db->select('kode_tagihan')
            ->like('kode_tagihan', $kode_like, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tagihan_master')
            ->row_array();
        $nomor_master = 1;
        if ($last_master && !empty($last_master['kode_tagihan'])) {
            $parts = explode('/', $last_master['kode_tagihan']);
            $nomor_master = ((int) end($parts)) + 1;
        }
        $kode_tagihan = $kode_like . str_pad($nomor_master, 5, '0', STR_PAD_LEFT);

        $master = array(
            'kode_tagihan' => $kode_tagihan,
            'id_jenis_tagihan' => $id_jenis,
            'nama_jenis_tagihan' => $jenis['nama_jenis'],
            'nama_tagihan' => $nama_tagihan,
            'tipe_tagihan' => 'Langsung',
            'id_periode' => $id_periode,
            'periode' => $periode['periode'],
            'semester' => null,
            'nominal_default' => $nominal,
            'model_tarif_bulanan' => 'Sama',
            'bulan_mulai' => $bulan,
            'tahun_mulai' => $tahun,
            'bulan_selesai' => $bulan,
            'tahun_selesai' => $tahun,
            'bulan_penagihan' => $bulan,
            'tahun_penagihan' => $tahun,
            'tanggal_jatuh_tempo' => $jatuh_tempo,
            'target_tagihan' => $target_tagihan,
            'dianggap_tunggakan' => $dianggap_tunggakan,
            'status_generate' => $mode === 'Draft' ? 'Belum' : 'Selesai',
            'status' => $mode === 'Draft' ? 'Draft' : 'Aktif',
            'keterangan' => $keterangan,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->trans_begin();
        $this->db->insert('tagihan_master', $master);
        $id_master = $this->db->insert_id();

        $this->db->insert('tagihan_tarif_bulan', array(
            'id_tagihan_master' => $id_master,
            'bulan' => $bulan,
            'nama_bulan' => $bulan_nama[$bulan],
            'tahun' => $tahun,
            'nominal' => $nominal,
            'tanggal_jatuh_tempo' => $jatuh_tempo,
            'status' => 'Aktif',
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        ));

        $class_seen = array();
        // Pada Draft bertarget Kelas, simpan seluruh kelas yang dipilih,
        // termasuk kelas yang belum memiliki siswa aktif.
        if ($mode === 'Draft' && $target_tagihan === 'Kelas') {
            $selected_classes = $this->db
                ->where_in('id', $target_kelas)
                ->where('CAST(id_periode AS UNSIGNED) = ' . (int) $id_periode, null, false)
                ->get('kelas_setting')->result_array();
            foreach ($selected_classes as $kelas) {
                $this->db->insert('tagihan_target_kelas', array(
                    'id_tagihan_master' => $id_master,
                    'id_kelas_setting' => (int) $kelas['id'],
                    'id_kelas' => (int) $kelas['id_kelas'],
                    'nama_kelas' => $kelas['nama_kelas'],
                    'id_periode' => (int) $kelas['id_periode'],
                    'periode' => $periode['periode'],
                    'semester' => null,
                    'nominal_kelas' => $nominal,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
                $class_seen[(int) $kelas['id']] = true;
            }
        }
        foreach ($students as $s) {
            if (!isset($class_seen[$s['id_kelas_setting']])) {
                $this->db->insert('tagihan_target_kelas', array(
                    'id_tagihan_master' => $id_master,
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'id_periode' => (int) $s['id_periode'],
                    'periode' => $s['periode'],
                    'semester' => null,
                    'nominal_kelas' => $nominal,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
                $class_seen[$s['id_kelas_setting']] = true;
            }

            if ($target_tagihan === 'Siswa') {
                $this->db->insert('tagihan_target_siswa', array(
                    'id_tagihan_master' => $id_master,
                    'id_siswa' => (int) $s['id'],
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'nama_siswa' => $s['nama_lengkap'],
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'nominal_target' => $nominal,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
            }
        }

        $generated = 0;
        if ($mode === 'Terbitkan') {
            $no_like = 'TAG/' . $kode_date . '/';
            $last_no = $this->db->select('no_tagihan')
                ->like('no_tagihan', $no_like, 'after')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('tagihan_siswa')
                ->row_array();
            $nomor_siswa = 1;
            if ($last_no && !empty($last_no['no_tagihan'])) {
                $parts = explode('/', $last_no['no_tagihan']);
                $nomor_siswa = ((int) end($parts)) + 1;
            }

            foreach ($students as $s) {
                $no_tagihan = $no_like . str_pad($nomor_siswa, 5, '0', STR_PAD_LEFT);
                $nomor_siswa++;

                $this->db->insert('tagihan_siswa', array(
                    'no_tagihan' => $no_tagihan,
                    'id_tagihan_master' => $id_master,
                    'kode_tagihan' => $kode_tagihan,
                    'id_jenis_tagihan' => $id_jenis,
                    'nama_jenis_tagihan' => $jenis['nama_jenis'],
                    'nama_tagihan' => $nama_tagihan,
                    'tipe_tagihan' => 'Langsung',
                    'id_periode' => $id_periode,
                    'periode' => $periode['periode'],
                    'semester' => null,
                    'bulan' => $bulan,
                    'nama_bulan' => $bulan_nama[$bulan],
                    'tahun' => $tahun,
                    'tanggal_jatuh_tempo' => $jatuh_tempo,
                    'id_siswa' => (int) $s['id'],
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'nama_siswa' => $s['nama_lengkap'],
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'nominal_awal' => $nominal,
                    'jenis_keringanan' => null,
                    'nilai_keringanan' => 0,
                    'nominal_tagihan' => $nominal,
                    'nominal_dibayar' => 0,
                    'sisa_tagihan' => $nominal,
                    'dianggap_tunggakan' => $dianggap_tunggakan,
                    'status_pembayaran' => 'Belum Dibayar',
                    'status_tagihan' => 'Aktif',
                    'keterangan' => $keterangan,
                    'tanggal_generate' => $tanggal,
                    'waktu_generate' => $waktu,
                    'id_user_generate' => $id_user,
                    'nama_user_generate' => $nama_user
                ));
                $generated++;
            }
        }

        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => $mode === 'Draft' ? 'Simpan Draft Tagihan' : 'Terbitkan Tagihan',
            'modul' => 'Tagihan',
            'aksi' => 'Tambah',
            'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id_master,
            'nomor_referensi' => $kode_tagihan,
            'keterangan' => $nama_tagihan . ' - ' . $generated . ' tagihan siswa',
            'data_sebelum' => null,
            'data_sesudah' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Proses database gagal. Tidak ada perubahan yang disimpan.');
        }

        $this->db->trans_commit();

        return array(
            'result' => 'true',
            'message' => $mode === 'Draft'
                ? 'Draft tagihan berhasil disimpan.'
                : $generated . ' tagihan siswa berhasil diterbitkan.',
            'id' => $id_master
        );
    }

    public function preview_tahunan()
    {
        $id_periode = (int) $this->input->post('id_periode');
        $id_jenis = (int) $this->input->post('id_jenis_tagihan');
        $nama_tagihan = trim((string) $this->input->post('nama_tagihan', true));
        $target_tagihan = trim((string) $this->input->post('target_tagihan', true));
        $bulan = (int) $this->input->post('bulan_penagihan');
        $tahun = (int) $this->input->post('tahun_penagihan');
        $jatuh_tempo = trim((string) $this->input->post('tanggal_jatuh_tempo', true));

        $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $this->input->post('nominal_default')));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
            $nominal = (float) $nominal_text;
        } else {
            $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
            $nominal = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
        }

        $periode = $this->db->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $jenis = $this->db->where('id', $id_jenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();

        if (!$periode || !$jenis) {
            return array('result' => 'false', 'message' => 'Tahun ajaran atau jenis tagihan tidak ditemukan.');
        }

        if ($jenis['tipe_default'] !== 'Tahunan') {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak sesuai dengan Tagihan Tahunan.');
        }

        if ($nama_tagihan === '' || $nominal <= 0 || !in_array($target_tagihan, array('Semua', 'Kelas', 'Siswa'), true)) {
            return array('result' => 'false', 'message' => 'Nama tagihan, nominal, dan target wajib diisi dengan benar.');
        }

        if ($bulan < 1 || $bulan > 12) {
            return array('result' => 'false', 'message' => 'Bulan mulai tampil wajib dipilih.');
        }

        $tahun_ajaran = explode('/', $periode['periode']);
        $tahun_awal = (int) $tahun_ajaran[0];
        $tahun_akhir = isset($tahun_ajaran[1]) ? (int) $tahun_ajaran[1] : $tahun_awal + 1;
        if ($tahun <= 0) {
            $tahun = $bulan >= 7 ? $tahun_awal : $tahun_akhir;
        }

        $bulan_nama = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );

        $target_kelas = $this->input->post('target_kelas');
        $target_siswa = $this->input->post('target_siswa');
        $target_kelas = is_array($target_kelas) ? array_values(array_unique(array_filter(array_map('intval', $target_kelas)))) : array();
        $target_siswa = is_array($target_siswa) ? array_values(array_unique(array_filter(array_map('intval', $target_siswa)))) : array();

        if ($target_tagihan === 'Kelas' && count($target_kelas) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        }

        if ($target_tagihan === 'Siswa' && count($target_siswa) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');
        }

        $sql_siswa = "SELECT DISTINCT
                            s.id,
                            s.nis,
                            s.nisn,
                            s.nama_lengkap,
                            s.jk,
                            k.id AS id_kelas_setting,
                            k.id_kelas,
                            k.nama_kelas,
                            k.id_periode,
                            ta.periode
                        FROM siswa s
                        INNER JOIN kelas_siswa ks
                            ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                            AND UPPER(ks.status_aktif) = 'AKTIF'
                        INNER JOIN kelas_setting k
                            ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                        LEFT JOIN master_tahun_ajaran ta
                            ON ta.id = CAST(k.id_periode AS UNSIGNED)
                        WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                        AND CAST(k.id_periode AS UNSIGNED) = ?";
        $params_siswa = array($id_periode);

        if ($target_tagihan === 'Kelas') {
            $sql_siswa .= " AND k.id IN (" . implode(',', array_fill(0, count($target_kelas), '?')) . ")";
            foreach ($target_kelas as $id_kelas) {
                $params_siswa[] = $id_kelas;
            }
        } elseif ($target_tagihan === 'Siswa') {
            $sql_siswa .= " AND s.id IN (" . implode(',', array_fill(0, count($target_siswa), '?')) . ")";
            foreach ($target_siswa as $id_siswa) {
                $params_siswa[] = $id_siswa;
            }
        }

        $sql_siswa .= " ORDER BY s.nama_lengkap";
        $students = $this->db->query($sql_siswa, $params_siswa)->result_array();

        if (count($students) === 0) {
            return array('result' => 'false', 'message' => 'Tidak ada siswa aktif pada target yang dipilih.');
        }

        $student_ids = array_map('intval', array_column($students, 'id'));
        $sql_duplikat = "SELECT
                            id,
                            no_tagihan,
                            id_siswa,
                            nis,
                            nama_siswa,
                            nama_tagihan,
                            bulan,
                            nama_bulan,
                            tahun
                        FROM tagihan_siswa
                        WHERE id_jenis_tagihan = ?
                        AND id_periode = ?
                        AND tipe_tagihan = 'Tahunan'
                        AND status_tagihan = 'Aktif'
                        AND id_siswa IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ")
                        ORDER BY nama_siswa";
        $params_duplikat = array($id_jenis, $id_periode);
        foreach ($student_ids as $id_siswa) {
            $params_duplikat[] = $id_siswa;
        }
        $duplicates_all = $this->db->query($sql_duplikat, $params_duplikat)->result_array();

        return array(
            'result' => 'true',
            'message' => count($duplicates_all) > 0
                ? 'Preview berhasil, tetapi ditemukan tagihan tahunan yang sudah ada. Tagihan tidak dapat diterbitkan sebelum konflik diselesaikan.'
                : 'Preview berhasil.',
            'jumlah_siswa' => count($students),
            'jumlah_baris' => count($students),
            'total_nominal' => count($students) * $nominal,
            'periods' => array(array(
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_bulan' => $bulan_nama[$bulan],
                'nominal' => $nominal,
                'jatuh_tempo' => $jatuh_tempo
            )),
            'students' => array_slice($students, 0, 20),
            'has_duplicate' => count($duplicates_all) > 0,
            'duplicate_count' => count($duplicates_all),
            'duplicates' => array_slice($duplicates_all, 0, 50)
        );
    }

    public function simpan_tahunan()
    {
        $id_periode = (int) $this->input->post('id_periode');
        $id_jenis = (int) $this->input->post('id_jenis_tagihan');
        $nama_tagihan = trim((string) $this->input->post('nama_tagihan', true));
        $target_tagihan = trim((string) $this->input->post('target_tagihan', true));
        $dianggap_tunggakan = $this->input->post('dianggap_tunggakan', true) === 'Tidak' ? 'Tidak' : 'Ya';
        $keterangan = trim((string) $this->input->post('keterangan', true));
        $mode = $this->input->post('mode_simpan', true) === 'Draft' ? 'Draft' : 'Terbitkan';
        $bulan = (int) $this->input->post('bulan_penagihan');
        $tahun = (int) $this->input->post('tahun_penagihan');
        $jatuh_tempo = trim((string) $this->input->post('tanggal_jatuh_tempo', true));

        $nominal_text = trim(str_ireplace(array('Rp', ' '), '', (string) $this->input->post('nominal_default')));
        if (preg_match('/^-?\d+\.\d{1,2}$/', $nominal_text)) {
            $nominal = (float) $nominal_text;
        } else {
            $nominal_clean = preg_replace('/[^0-9-]/', '', $nominal_text);
            $nominal = ($nominal_clean === '' || $nominal_clean === '-') ? 0 : (float) $nominal_clean;
        }

        $periode = $this->db->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $jenis = $this->db->where('id', $id_jenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();

        if (!$periode || !$jenis) {
            return array('result' => 'false', 'message' => 'Tahun ajaran atau jenis tagihan tidak ditemukan.');
        }

        if ($jenis['tipe_default'] !== 'Tahunan') {
            return array('result' => 'false', 'message' => 'Jenis tagihan tidak sesuai dengan Tagihan Tahunan.');
        }

        if ($nama_tagihan === '' || $nominal <= 0 || !in_array($target_tagihan, array('Semua', 'Kelas', 'Siswa'), true)) {
            return array('result' => 'false', 'message' => 'Nama tagihan, nominal, dan target wajib diisi dengan benar.');
        }

        if ($bulan < 1 || $bulan > 12) {
            return array('result' => 'false', 'message' => 'Bulan mulai tampil wajib dipilih.');
        }

        $tahun_ajaran = explode('/', $periode['periode']);
        $tahun_awal = (int) $tahun_ajaran[0];
        $tahun_akhir = isset($tahun_ajaran[1]) ? (int) $tahun_ajaran[1] : $tahun_awal + 1;
        if ($tahun <= 0) {
            $tahun = $bulan >= 7 ? $tahun_awal : $tahun_akhir;
        }

        $bulan_nama = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );

        $target_kelas = $this->input->post('target_kelas');
        $target_siswa = $this->input->post('target_siswa');
        $target_kelas = is_array($target_kelas) ? array_values(array_unique(array_filter(array_map('intval', $target_kelas)))) : array();
        $target_siswa = is_array($target_siswa) ? array_values(array_unique(array_filter(array_map('intval', $target_siswa)))) : array();

        if ($target_tagihan === 'Kelas' && count($target_kelas) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        }

        if ($target_tagihan === 'Siswa' && count($target_siswa) === 0) {
            return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');
        }

        $sql_siswa = "SELECT DISTINCT
                            s.id,
                            s.nis,
                            s.nisn,
                            s.nama_lengkap,
                            s.jk,
                            k.id AS id_kelas_setting,
                            k.id_kelas,
                            k.nama_kelas,
                            k.id_periode,
                            ta.periode
                        FROM siswa s
                        INNER JOIN kelas_siswa ks
                            ON CAST(ks.id_siswa AS UNSIGNED) = s.id
                            AND UPPER(ks.status_aktif) = 'AKTIF'
                        INNER JOIN kelas_setting k
                            ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                        LEFT JOIN master_tahun_ajaran ta
                            ON ta.id = CAST(k.id_periode AS UNSIGNED)
                        WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
                        AND CAST(k.id_periode AS UNSIGNED) = ?";
        $params_siswa = array($id_periode);

        if ($target_tagihan === 'Kelas') {
            $sql_siswa .= " AND k.id IN (" . implode(',', array_fill(0, count($target_kelas), '?')) . ")";
            foreach ($target_kelas as $id_kelas) {
                $params_siswa[] = $id_kelas;
            }
        } elseif ($target_tagihan === 'Siswa') {
            $sql_siswa .= " AND s.id IN (" . implode(',', array_fill(0, count($target_siswa), '?')) . ")";
            foreach ($target_siswa as $id_siswa) {
                $params_siswa[] = $id_siswa;
            }
        }

        $sql_siswa .= " ORDER BY s.nama_lengkap";
        $students = $this->db->query($sql_siswa, $params_siswa)->result_array();

        if (count($students) === 0) {
            return array('result' => 'false', 'message' => 'Tidak ada siswa aktif pada target yang dipilih.');
        }

        if ($mode === 'Terbitkan') {
            $student_ids = array_map('intval', array_column($students, 'id'));
            $sql_duplikat = "SELECT
                                id,
                                no_tagihan,
                                id_siswa,
                                nis,
                                nama_siswa,
                                nama_tagihan,
                                bulan,
                                nama_bulan,
                                tahun
                            FROM tagihan_siswa
                            WHERE id_jenis_tagihan = ?
                            AND id_periode = ?
                            AND tipe_tagihan = 'Tahunan'
                            AND status_tagihan = 'Aktif'
                            AND id_siswa IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ")
                            ORDER BY nama_siswa";
            $params_duplikat = array($id_jenis, $id_periode);
            foreach ($student_ids as $id_siswa) {
                $params_duplikat[] = $id_siswa;
            }
            $duplicates_all = $this->db->query($sql_duplikat, $params_duplikat)->result_array();

            if (count($duplicates_all) > 0) {
                return array(
                    'result' => 'false',
                    'message' => 'Tagihan tahunan tidak dapat diterbitkan karena ditemukan ' . count($duplicates_all) . ' tagihan yang sudah ada untuk siswa dan tahun ajaran yang sama pada jenis tersebut.',
                    'duplicate_count' => count($duplicates_all),
                    'duplicates' => array_slice($duplicates_all, 0, 50)
                );
            }
        }

        $user = $this->session->userdata('admin');
        $id_user = is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0;
        $nama_user = is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator';
        $tanggal = date('d-m-Y');
        $waktu = date('H:i:s');

        $kode_date = date('Ym');
        $kode_like = 'TGH/' . $kode_date . '/';
        $last_master = $this->db->select('kode_tagihan')
            ->like('kode_tagihan', $kode_like, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tagihan_master')
            ->row_array();
        $nomor_master = 1;
        if ($last_master && !empty($last_master['kode_tagihan'])) {
            $parts = explode('/', $last_master['kode_tagihan']);
            $nomor_master = ((int) end($parts)) + 1;
        }
        $kode_tagihan = $kode_like . str_pad($nomor_master, 5, '0', STR_PAD_LEFT);

        $master = array(
            'kode_tagihan' => $kode_tagihan,
            'id_jenis_tagihan' => $id_jenis,
            'nama_jenis_tagihan' => $jenis['nama_jenis'],
            'nama_tagihan' => $nama_tagihan,
            'tipe_tagihan' => 'Tahunan',
            'id_periode' => $id_periode,
            'periode' => $periode['periode'],
            'semester' => null,
            'nominal_default' => $nominal,
            'model_tarif_bulanan' => 'Sama',
            'bulan_mulai' => $bulan,
            'tahun_mulai' => $tahun,
            'bulan_selesai' => $bulan,
            'tahun_selesai' => $tahun,
            'bulan_penagihan' => $bulan,
            'tahun_penagihan' => $tahun,
            'tanggal_jatuh_tempo' => $jatuh_tempo,
            'target_tagihan' => $target_tagihan,
            'dianggap_tunggakan' => $dianggap_tunggakan,
            'status_generate' => $mode === 'Draft' ? 'Belum' : 'Selesai',
            'status' => $mode === 'Draft' ? 'Draft' : 'Aktif',
            'keterangan' => $keterangan,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        );

        $this->db->trans_begin();
        $this->db->insert('tagihan_master', $master);
        $id_master = $this->db->insert_id();

        $this->db->insert('tagihan_tarif_bulan', array(
            'id_tagihan_master' => $id_master,
            'bulan' => $bulan,
            'nama_bulan' => $bulan_nama[$bulan],
            'tahun' => $tahun,
            'nominal' => $nominal,
            'tanggal_jatuh_tempo' => $jatuh_tempo,
            'status' => 'Aktif',
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        ));

        $class_seen = array();
        // Pada Draft bertarget Kelas, simpan seluruh kelas yang dipilih,
        // termasuk kelas yang belum memiliki siswa aktif.
        if ($mode === 'Draft' && $target_tagihan === 'Kelas') {
            $selected_classes = $this->db
                ->where_in('id', $target_kelas)
                ->where('CAST(id_periode AS UNSIGNED) = ' . (int) $id_periode, null, false)
                ->get('kelas_setting')->result_array();
            foreach ($selected_classes as $kelas) {
                $this->db->insert('tagihan_target_kelas', array(
                    'id_tagihan_master' => $id_master,
                    'id_kelas_setting' => (int) $kelas['id'],
                    'id_kelas' => (int) $kelas['id_kelas'],
                    'nama_kelas' => $kelas['nama_kelas'],
                    'id_periode' => (int) $kelas['id_periode'],
                    'periode' => $periode['periode'],
                    'semester' => null,
                    'nominal_kelas' => $nominal,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
                $class_seen[(int) $kelas['id']] = true;
            }
        }
        foreach ($students as $s) {
            if (!isset($class_seen[$s['id_kelas_setting']])) {
                $this->db->insert('tagihan_target_kelas', array(
                    'id_tagihan_master' => $id_master,
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'id_periode' => (int) $s['id_periode'],
                    'periode' => $s['periode'],
                    'semester' => null,
                    'nominal_kelas' => $nominal,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
                $class_seen[$s['id_kelas_setting']] = true;
            }

            if ($target_tagihan === 'Siswa') {
                $this->db->insert('tagihan_target_siswa', array(
                    'id_tagihan_master' => $id_master,
                    'id_siswa' => (int) $s['id'],
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'nama_siswa' => $s['nama_lengkap'],
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'nominal_target' => $nominal,
                    'status' => 'Aktif',
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'id_user' => $id_user,
                    'nama_user' => $nama_user
                ));
            }
        }

        $generated = 0;
        if ($mode === 'Terbitkan') {
            $no_like = 'TAG/' . $kode_date . '/';
            $last_no = $this->db->select('no_tagihan')
                ->like('no_tagihan', $no_like, 'after')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('tagihan_siswa')
                ->row_array();
            $nomor_siswa = 1;
            if ($last_no && !empty($last_no['no_tagihan'])) {
                $parts = explode('/', $last_no['no_tagihan']);
                $nomor_siswa = ((int) end($parts)) + 1;
            }

            foreach ($students as $s) {
                $no_tagihan = $no_like . str_pad($nomor_siswa, 5, '0', STR_PAD_LEFT);
                $nomor_siswa++;

                $this->db->insert('tagihan_siswa', array(
                    'no_tagihan' => $no_tagihan,
                    'id_tagihan_master' => $id_master,
                    'kode_tagihan' => $kode_tagihan,
                    'id_jenis_tagihan' => $id_jenis,
                    'nama_jenis_tagihan' => $jenis['nama_jenis'],
                    'nama_tagihan' => $nama_tagihan,
                    'tipe_tagihan' => 'Tahunan',
                    'id_periode' => $id_periode,
                    'periode' => $periode['periode'],
                    'semester' => null,
                    'bulan' => $bulan,
                    'nama_bulan' => $bulan_nama[$bulan],
                    'tahun' => $tahun,
                    'tanggal_jatuh_tempo' => $jatuh_tempo,
                    'id_siswa' => (int) $s['id'],
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'nama_siswa' => $s['nama_lengkap'],
                    'id_kelas_setting' => (int) $s['id_kelas_setting'],
                    'id_kelas' => (int) $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'nominal_awal' => $nominal,
                    'jenis_keringanan' => null,
                    'nilai_keringanan' => 0,
                    'nominal_tagihan' => $nominal,
                    'nominal_dibayar' => 0,
                    'sisa_tagihan' => $nominal,
                    'dianggap_tunggakan' => $dianggap_tunggakan,
                    'status_pembayaran' => 'Belum Dibayar',
                    'status_tagihan' => 'Aktif',
                    'keterangan' => $keterangan,
                    'tanggal_generate' => $tanggal,
                    'waktu_generate' => $waktu,
                    'id_user_generate' => $id_user,
                    'nama_user_generate' => $nama_user
                ));
                $generated++;
            }
        }

        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => $mode === 'Draft' ? 'Simpan Draft Tagihan' : 'Terbitkan Tagihan',
            'modul' => 'Tagihan',
            'aksi' => 'Tambah',
            'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id_master,
            'nomor_referensi' => $kode_tagihan,
            'keterangan' => $nama_tagihan . ' - ' . $generated . ' tagihan siswa',
            'data_sebelum' => null,
            'data_sesudah' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'id_user' => $id_user,
            'nama_user' => $nama_user
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Proses database gagal. Tidak ada perubahan yang disimpan.');
        }

        $this->db->trans_commit();

        return array(
            'result' => 'true',
            'message' => $mode === 'Draft'
                ? 'Draft tagihan berhasil disimpan.'
                : $generated . ' tagihan siswa berhasil diterbitkan.',
            'id' => $id_master
        );
    }
}
?>
