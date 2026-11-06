m daftar tagihan:
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_daftar_tagihan extends CI_Model
{
    public function periode_list()
    {
        return $this->db->order_by('id', 'DESC')->get('master_tahun_ajaran')->result_array();
    }

    public function jenis_list()
    {
        return $this->db->order_by('nama_jenis', 'ASC')->get('tagihan_jenis')->result_array();
    }

    public function result()
    {
        $idPeriode = (int) $this->input->post('id_periode');
        $tipe = trim((string) $this->input->post('tipe', true));
        $jenis = (int) $this->input->post('id_jenis');
        $status = trim((string) $this->input->post('status', true));
        $search = trim((string) $this->input->post('search', true));

        $this->db
            ->select("m.*,
                COUNT(DISTINCT ts.id_siswa) jumlah_siswa,
                COALESCE(SUM(CAST(ts.nominal_tagihan AS DECIMAL(18,2))),0) total_nominal,
                SUM(CASE WHEN ts.status_pembayaran='Belum Dibayar' THEN 1 ELSE 0 END) belum_bayar,
                SUM(CASE WHEN ts.status_pembayaran='Dibayar Sebagian' THEN 1 ELSE 0 END) sebagian,
                SUM(CASE WHEN ts.status_pembayaran='Lunas' THEN 1 ELSE 0 END) lunas,
                SUM(CASE WHEN ts.status_pembayaran='Dibebaskan' THEN 1 ELSE 0 END) dibebaskan")
            ->from('tagihan_master m')
            ->join('tagihan_siswa ts', 'ts.id_tagihan_master = m.id', 'left')
            ->group_by('m.id');

        if ($idPeriode > 0) $this->db->where('m.id_periode', $idPeriode);
        if ($tipe !== '') $this->db->where('m.tipe_tagihan', $tipe);
        if ($jenis > 0) $this->db->where('m.id_jenis_tagihan', $jenis);
        if ($status !== '') $this->db->where('m.status', $status);
        if ($search !== '') {
            $this->db->group_start()
                ->like('m.nama_tagihan', $search)
                ->or_like('m.kode_tagihan', $search)
                ->group_end();
        }

        return $this->db->order_by('m.id', 'DESC')->get()->result_array();
    }

    public function detail()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) {
            return array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.');
        }

        return array(
            'result' => 'true',
            'master' => $master,
            'periods' => $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->order_by('tahun')->order_by('bulan')->get('tagihan_tarif_bulan')->result_array(),
            'classes' => $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_kelas')->result_array()
        );
    }

    public function cari_siswa()
    {
        $idPeriode = (int) $this->input->post('id_periode');
        $q = trim((string) $this->input->post('q', true));
        if ($idPeriode <= 0 || strlen($q) < 1) return array();

        $like = '%' . $q . '%';
        return $this->db->query(
            "SELECT DISTINCT s.id, s.nis, s.nisn, s.nama_lengkap,
                    k.id AS id_kelas_setting, k.id_kelas, k.nama_kelas, k.id_periode
             FROM siswa s
             INNER JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED) = s.id AND UPPER(ks.status_aktif) = 'AKTIF'
             INNER JOIN kelas_setting k ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
             WHERE UPPER(s.status_aktif) = 'AKTIF' AND UPPER(s.status_siswa) = 'AKTIF'
               AND CAST(k.id_periode AS UNSIGNED) = ?
               AND (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)
             ORDER BY s.nama_lengkap ASC
             LIMIT 30",
            array($idPeriode, $like, $like, $like)
        )->result_array();
    }

    public function kelas_periode()
    {
        $idPeriode = (int) $this->input->post('id_periode');
        if ($idPeriode <= 0) {
            return array();
        }

        return $this->db
            ->select('id, id_kelas, nama_kelas, id_periode')
            ->where('CAST(id_periode AS UNSIGNED) = ' . $idPeriode, null, false)
            ->order_by('nama_kelas', 'ASC')
            ->get('kelas_setting')
            ->result_array();
    }

    public function draft_detail()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Draft tagihan tidak ditemukan.');
        if ($master['status'] !== 'Draft') return array('result' => 'false', 'message' => 'Hanya tagihan berstatus Draft yang dapat diedit penuh.');

        $periods = $this->db
            ->where('id_tagihan_master', $id)
            ->where('status', 'Aktif')
            ->order_by('tahun', 'ASC')
            ->order_by('bulan', 'ASC')
            ->get('tagihan_tarif_bulan')
            ->result_array();

        $classes = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_kelas')->result_array();
        $students = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_siswa')->result_array();
        $availableClasses = $this->db
            ->select('id, id_kelas, nama_kelas, id_periode')
            ->where('CAST(id_periode AS UNSIGNED) = ' . (int) $master['id_periode'], null, false)
            ->order_by('nama_kelas', 'ASC')
            ->get('kelas_setting')
            ->result_array();

        return array(
            'result' => 'true',
            'message' => 'Draft berhasil dimuat.',
            'master' => $master,
            'periods' => $periods,
            'classes' => $classes,
            'students' => $students,
            'available_classes' => $availableClasses
        );
    }

    public function update_draft()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Draft tagihan tidak ditemukan.');
        if ($master['status'] !== 'Draft') return array('result' => 'false', 'message' => 'Hanya tagihan berstatus Draft yang dapat diubah penuh.');
        if ($this->db->where('id_tagihan_master', $id)->count_all_results('tagihan_siswa') > 0) {
            return array('result' => 'false', 'message' => 'Draft sudah memiliki tagihan siswa sehingga tidak aman diedit penuh.');
        }

        $idPeriode = (int) $this->input->post('id_periode');
        $nama = trim((string) $this->input->post('nama_tagihan', true));
        $idJenis = (int) $this->input->post('id_jenis_tagihan');
        $tunggakan = $this->input->post('dianggap_tunggakan', true) === 'Tidak' ? 'Tidak' : 'Ya';
        $target = trim((string) $this->input->post('target_tagihan', true));
        $keterangan = trim((string) $this->input->post('keterangan', true));
        $periods = json_decode((string) $this->input->post('period_json'), true);
        $targetKelas = json_decode((string) $this->input->post('target_kelas_json'), true);
        $targetSiswa = json_decode((string) $this->input->post('target_siswa_json'), true);

        if ($nama === '') return array('result' => 'false', 'message' => 'Nama tagihan wajib diisi.');
        $periode = $this->db->where('id', $idPeriode)->get('master_tahun_ajaran')->row_array();
        if (!$periode) return array('result' => 'false', 'message' => 'Tahun ajaran tidak ditemukan.');
        if (!in_array($target, array('Semua', 'Kelas', 'Siswa'), true)) return array('result' => 'false', 'message' => 'Target tagihan tidak valid.');
        if (!is_array($periods) || count($periods) === 0) return array('result' => 'false', 'message' => 'Minimal satu periode tagihan harus dipilih.');

        $jenis = $this->db->where('id', $idJenis)->where('status', 'Aktif')->get('tagihan_jenis')->row_array();
        if (!$jenis || $jenis['tipe_default'] !== $master['tipe_tagihan']) {
            return array('result' => 'false', 'message' => 'Jenis tagihan harus sesuai dengan tipe draft ' . $master['tipe_tagihan'] . '.');
        }

        $targetKelas = is_array($targetKelas) ? array_values(array_unique(array_filter(array_map('intval', $targetKelas)))) : array();
        $targetSiswa = is_array($targetSiswa) ? array_values(array_unique(array_filter(array_map('intval', $targetSiswa)))) : array();
        if ($target === 'Kelas' && !$targetKelas) return array('result' => 'false', 'message' => 'Pilih minimal satu kelas target.');
        if ($target === 'Siswa' && !$targetSiswa) return array('result' => 'false', 'message' => 'Pilih minimal satu siswa target.');

        $oldPeriods = $this->db->where('id_tagihan_master', $id)->get('tagihan_tarif_bulan')->result_array();
        $oldPeriodMap = array();
        foreach ($oldPeriods as $row) {
            $oldPeriodMap[(int) $row['bulan'] . '-' . (int) $row['tahun']] = $row;
        }

        $normalPeriods = array();
        foreach ($periods as $row) {
            $bulan = (int) ($row['bulan'] ?? 0);
            $tahun = (int) ($row['tahun'] ?? 0);
            $namaBulan = trim((string) ($row['nama_bulan'] ?? ''));
            $nominal = (float) preg_replace('/[^0-9]/', '', (string) ($row['nominal'] ?? '0'));
            $jatuhTempo = trim((string) ($row['tanggal_jatuh_tempo'] ?? ''));
            $tanggalValid = DateTime::createFromFormat('!d-m-Y', $jatuhTempo);
            if ($bulan < 1 || $bulan > 12 || $tahun <= 0 || $nominal <= 0 || !$tanggalValid || $tanggalValid->format('d-m-Y') !== $jatuhTempo) {
                return array('result' => 'false', 'message' => 'Periode, nominal, tahun, atau tanggal jatuh tempo tagihan tidak valid.');
            }

            $tahunAjaran = explode('/', (string) $periode['periode']);
            $tahunAwal = (int) ($tahunAjaran[0] ?? 0);
            $tahunAkhir = (int) ($tahunAjaran[1] ?? ($tahunAwal + 1));
            $tahunSeharusnya = $bulan >= 7 ? $tahunAwal : $tahunAkhir;
            if ($tahun !== $tahunSeharusnya) {
                return array('result' => 'false', 'message' => 'Periode bulan dan tahun harus sesuai dengan Tahun Ajaran yang dipilih.');
            }

            $normalPeriods[] = array('bulan' => $bulan, 'tahun' => $tahun, 'nama_bulan' => $namaBulan, 'nominal' => $nominal, 'tanggal_jatuh_tempo' => $jatuhTempo);
        }

        usort($normalPeriods, function ($a, $b) use ($master) {
            $aKey = $a['tahun'] * 100 + $a['bulan'];
            $bKey = $b['tahun'] * 100 + $b['bulan'];
            return $aKey <=> $bKey;
        });
        $first = reset($normalPeriods);
        $last = end($normalPeriods);

        $oldClass = $this->db->where('id_tagihan_master', $id)->get('tagihan_target_kelas')->result_array();
        $oldClassMap = array();
        foreach ($oldClass as $row) $oldClassMap[(int) $row['id_kelas_setting']] = (float) $row['nominal_kelas'];
        $oldStudent = $this->db->where('id_tagihan_master', $id)->get('tagihan_target_siswa')->result_array();
        $oldStudentMap = array();
        foreach ($oldStudent as $row) $oldStudentMap[(int) $row['id_siswa']] = (float) $row['nominal_target'];

        $this->db->trans_begin();
        $this->db->where('id_tagihan_master', $id)->update('tagihan_tarif_bulan', array('status' => 'Nonaktif'));
        foreach ($normalPeriods as $row) {
            $key = $row['bulan'] . '-' . $row['tahun'];
            $dataPeriod = array(
                'id_tagihan_master' => $id,
                'bulan' => $row['bulan'],
                'nama_bulan' => $row['nama_bulan'],
                'tahun' => $row['tahun'],
                'nominal' => $row['nominal'],
                'tanggal_jatuh_tempo' => $row['tanggal_jatuh_tempo'],
                'status' => 'Aktif',
                'tanggal' => date('d-m-Y'),
                'waktu' => date('H:i:s')
            );
            if (isset($oldPeriodMap[$key])) {
                $this->db->where('id', (int) $oldPeriodMap[$key]['id'])->update('tagihan_tarif_bulan', $dataPeriod);
            } else {
                $this->db->insert('tagihan_tarif_bulan', $dataPeriod);
            }
        }

        $this->db->where('id_tagihan_master', $id)->delete('tagihan_target_kelas');
        $this->db->where('id_tagihan_master', $id)->delete('tagihan_target_siswa');

        if ($target === 'Semua') {
            $classRows = $this->db
                ->where('CAST(id_periode AS UNSIGNED) = ' . $idPeriode, null, false)
                ->order_by('nama_kelas', 'ASC')
                ->get('kelas_setting')->result_array();
        } elseif ($target === 'Kelas') {
            $classRows = $this->db->where_in('id', $targetKelas)->where('CAST(id_periode AS UNSIGNED) = ' . $idPeriode, null, false)->get('kelas_setting')->result_array();
        } else {
            $placeholders = implode(',', array_fill(0, count($targetSiswa), '?'));
            $classRows = $this->db->query(
                "SELECT DISTINCT k.* FROM kelas_setting k
                 INNER JOIN kelas_siswa ks ON CAST(ks.id_kelas_setting AS UNSIGNED) = k.id AND UPPER(ks.status_aktif) = 'AKTIF'
                 WHERE CAST(ks.id_siswa AS UNSIGNED) IN ($placeholders)
                   AND CAST(k.id_periode AS UNSIGNED) = ?",
                array_merge($targetSiswa, array($idPeriode))
            )->result_array();
        }

        foreach ($classRows as $row) {
            $idKelasSetting = (int) $row['id'];
            $this->db->insert('tagihan_target_kelas', array(
                'id_tagihan_master' => $id,
                'id_kelas_setting' => $idKelasSetting,
                'id_kelas' => (int) $row['id_kelas'],
                'nama_kelas' => $row['nama_kelas'],
                'id_periode' => $idPeriode,
                'periode' => $periode['periode'],
                'semester' => null,
                'nominal_kelas' => isset($oldClassMap[$idKelasSetting])
                    && $oldClassMap[$idKelasSetting] > 0
                    && abs($oldClassMap[$idKelasSetting] - (float) $master['nominal_default']) > 0.001
                        ? $oldClassMap[$idKelasSetting]
                        : (float) $first['nominal'],
                'status' => 'Aktif',
                'tanggal' => date('d-m-Y'),
                'waktu' => date('H:i:s')
            ));
        }

        if ($target === 'Siswa') {
            $placeholders = implode(',', array_fill(0, count($targetSiswa), '?'));
            $students = $this->db->query(
                "SELECT DISTINCT s.id, s.nis, s.nisn, s.nama_lengkap,
                        k.id AS id_kelas_setting, k.id_kelas, k.nama_kelas
                 FROM siswa s
                 INNER JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED) = s.id AND UPPER(ks.status_aktif) = 'AKTIF'
                 INNER JOIN kelas_setting k ON k.id = CAST(ks.id_kelas_setting AS UNSIGNED)
                 WHERE s.id IN ($placeholders)
                   AND CAST(k.id_periode AS UNSIGNED) = ?",
                array_merge($targetSiswa, array($idPeriode))
            )->result_array();

            foreach ($students as $row) {
                $idSiswa = (int) $row['id'];
                $this->db->insert('tagihan_target_siswa', array(
                    'id_tagihan_master' => $id,
                    'id_siswa' => $idSiswa,
                    'nis' => $row['nis'],
                    'nisn' => $row['nisn'],
                    'nama_siswa' => $row['nama_lengkap'],
                    'id_kelas_setting' => (int) $row['id_kelas_setting'],
                    'id_kelas' => (int) $row['id_kelas'],
                    'nama_kelas' => $row['nama_kelas'],
                    'nominal_target' => isset($oldStudentMap[$idSiswa])
                        && $oldStudentMap[$idSiswa] > 0
                        && abs($oldStudentMap[$idSiswa] - (float) $master['nominal_default']) > 0.001
                            ? $oldStudentMap[$idSiswa]
                            : (float) $first['nominal'],
                    'status' => 'Aktif',
                    'tanggal' => date('d-m-Y'),
                    'waktu' => date('H:i:s')
                ));
            }
        }

        $user = $this->session->userdata('admin');
        $update = array(
            'id_periode' => $idPeriode,
            'periode' => $periode['periode'],
            'id_jenis_tagihan' => $idJenis,
            'nama_jenis_tagihan' => $jenis['nama_jenis'],
            'nama_tagihan' => $nama,
            'nominal_default' => (float) $first['nominal'],
            'bulan_mulai' => (int) $first['bulan'],
            'tahun_mulai' => (int) $first['tahun'],
            'bulan_selesai' => (int) $last['bulan'],
            'tahun_selesai' => (int) $last['tahun'],
            'bulan_penagihan' => (int) $first['bulan'],
            'tahun_penagihan' => (int) $first['tahun'],
            'tanggal_jatuh_tempo' => $first['tanggal_jatuh_tempo'],
            'target_tagihan' => $target,
            'dianggap_tunggakan' => $tunggakan,
            'keterangan' => $keterangan,
            'tanggal_update' => date('d-m-Y'),
            'waktu_update' => date('H:i:s'),
            'id_user_update' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user_update' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        );
        $this->db->where('id', $id)->update('tagihan_master', $update);
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Edit Draft Tagihan', 'modul' => 'Tagihan', 'aksi' => 'Ubah', 'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id, 'nomor_referensi' => $master['kode_tagihan'],
            'keterangan' => 'Mengubah informasi, periode, tarif, dan target draft sebelum diterbitkan.',
            'data_sebelum' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => json_encode(array_merge($master, $update), JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(), 'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'), 'waktu' => date('H:i:s'),
            'id_user' => $update['id_user_update'], 'nama_user' => $update['nama_user_update']
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Draft tagihan gagal diperbarui.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Draft tagihan berhasil diperbarui.');
    }

    public function terbit_detail()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.');
        if ($master['status'] !== 'Aktif') return array('result' => 'false', 'message' => 'Hanya tagihan yang sudah diterbitkan dan masih aktif yang dapat diedit.');

        return array(
            'result' => 'true',
            'master' => $master,
            'periods' => $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->order_by('tahun')->order_by('bulan')->get('tagihan_tarif_bulan')->result_array()
        );
    }

    public function update_terbit()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.');
        if ($master['status'] !== 'Aktif') return array('result' => 'false', 'message' => 'Hanya tagihan aktif yang dapat diedit setelah diterbitkan.');

        $nama = trim((string) $this->input->post('nama_tagihan', true));
        $periods = json_decode((string) $this->input->post('period_json'), true);
        if ($nama === '') return array('result' => 'false', 'message' => 'Nama tagihan wajib diisi.');
        if (!is_array($periods) || !$periods) return array('result' => 'false', 'message' => 'Periode tagihan tidak valid.');

        $stored = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_tarif_bulan')->result_array();
        $storedMap = array();
        foreach ($stored as $row) $storedMap[(int) $row['id']] = $row;

        // Acuan tarif khusus kelas/siswa dibaca sebelum tarif umum diubah.
        // Sama seperti saat menerbitkan tagihan: tarif berbeda dari nominal_default
        // tidak boleh tertimpa perubahan nominal bulanan.
        $classMap = array();
        $classTargets = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_kelas')->result_array();
        foreach ($classTargets as $row) $classMap[(int) $row['id_kelas_setting']] = $row;
        $studentMap = array();
        $studentTargets = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_siswa')->result_array();
        foreach ($studentTargets as $row) $studentMap[(int) $row['id_siswa']] = $row;

        $updatedStudent = 0;
        $protectedStudent = 0;
        $firstNominal = null;
        $firstDue = '';
        $this->db->trans_begin();

        foreach ($periods as $row) {
            $periodId = (int) ($row['id'] ?? 0);
            $nominal = (float) preg_replace('/[^0-9]/', '', (string) ($row['nominal'] ?? '0'));
            $jatuhTempo = trim((string) ($row['tanggal_jatuh_tempo'] ?? ''));
            $tanggalValid = DateTime::createFromFormat('!d-m-Y', $jatuhTempo);
            if ($periodId <= 0 || !isset($storedMap[$periodId]) || $nominal <= 0 || !$tanggalValid || $tanggalValid->format('d-m-Y') !== $jatuhTempo) {
                $this->db->trans_rollback();
                return array('result' => 'false', 'message' => 'Nominal, periode, atau tanggal jatuh tempo tagihan tidak valid.');
            }

            $period = $storedMap[$periodId];
            $this->db->where('id', $periodId)->update('tagihan_tarif_bulan', array('nominal' => $nominal, 'tanggal_jatuh_tempo' => $jatuhTempo));
            if ($firstNominal === null) {
                $firstNominal = $nominal;
                $firstDue = $jatuhTempo;
            }

            $studentRows = $this->db
                ->where('id_tagihan_master', $id)
                ->where('bulan', (int) $period['bulan'])
                ->where('tahun', (int) $period['tahun'])
                ->get('tagihan_siswa')->result_array();

            foreach ($studentRows as $tagihan) {
                $paidDetail = $this->db->query(
                    "SELECT COUNT(*) AS total
                     FROM tagihan_pembayaran_detail pd
                     INNER JOIN tagihan_pembayaran p ON p.id = pd.id_pembayaran
                     WHERE pd.id_tagihan_siswa = ? AND pd.status_detail = 'Aktif' AND p.status_transaksi = 'Aktif'",
                    array((int) $tagihan['id'])
                )->row_array();

                if ((float) $tagihan['nominal_dibayar'] > 0 || (int) ($paidDetail['total'] ?? 0) > 0) {
                    $protectedStudent++;
                    continue;
                }

                $nominalDasar = $nominal;
                $idKelas = (int) $tagihan['id_kelas_setting'];
                $idSiswa = (int) $tagihan['id_siswa'];
                if (isset($classMap[$idKelas])) {
                    $nominalKelas = (float) $classMap[$idKelas]['nominal_kelas'];
                    if ($nominalKelas > 0 && abs($nominalKelas - (float) $master['nominal_default']) > 0.001) {
                        $nominalDasar = $nominalKelas;
                    }
                }
                if (isset($studentMap[$idSiswa])) {
                    $nominalSiswa = (float) $studentMap[$idSiswa]['nominal_target'];
                    if ($nominalSiswa > 0 && abs($nominalSiswa - (float) $master['nominal_default']) > 0.001) {
                        $nominalDasar = $nominalSiswa;
                    }
                }

                $nominalAkhir = $nominalDasar;
                $jenisKeringanan = trim((string) $tagihan['jenis_keringanan']);
                $nilaiKeringananNominal = 0;

                // Untuk hitung ulang tagihan terbit, sumber aturan keringanan harus berasal
                // dari riwayat keringanan aktif. Pada tagihan_siswa, nilai_keringanan
                // disimpan sebagai nominal Rupiah potongan, bukan angka persen.
                $keringananAktif = $this->db
                    ->where('id_tagihan_master', $id)
                    ->where('id_siswa', (int) $tagihan['id_siswa'])
                    ->where('bulan', (int) $tagihan['bulan'])
                    ->where('tahun', (int) $tagihan['tahun'])
                    ->where_in('jenis_keringanan', array('Potongan Nominal', 'Potongan Persen', 'Pembebasan Penuh'))
                    ->where('status', 'Aktif')
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('tagihan_keringanan_siswa')
                    ->row_array();

                $tarifKhusus = $this->db->query(
                    "SELECT * FROM tagihan_keringanan_siswa
                     WHERE id_tagihan_master=? AND id_siswa=?
                       AND jenis_keringanan='Tarif Khusus' AND status='Aktif'
                       AND ((bulan=? AND tahun=?) OR (bulan=0 AND tahun=0))
                     ORDER BY CASE WHEN bulan=? AND tahun=? THEN 0 ELSE 1 END, id DESC
                     LIMIT 1",
                    array($id, $idSiswa, (int) $tagihan['bulan'], (int) $tagihan['tahun'], (int) $tagihan['bulan'], (int) $tagihan['tahun'])
                )->row_array();

                if ($tarifKhusus) {
                    $jenisKeringanan = 'Tarif Khusus';
                    $nominalAkhir = max(0, (float) $tarifKhusus['nominal_setelah_keringanan']);
                    $nilaiKeringananNominal = max(0, $nominalDasar - $nominalAkhir);
                } elseif ($keringananAktif) {
                    $jenisKeringanan = trim((string) $keringananAktif['jenis_keringanan']);

                    if ($jenisKeringanan === 'Potongan Nominal') {
                        $nilaiAturan = max(0, (float) $keringananAktif['nilai_keringanan']);
                        $nilaiKeringananNominal = min($nominalDasar, $nilaiAturan);
                        $nominalAkhir = max(0, $nominalDasar - $nilaiKeringananNominal);
                    } elseif ($jenisKeringanan === 'Potongan Persen') {
                        $persen = min(100, max(0, (float) $keringananAktif['nilai_keringanan']));
                        $nilaiKeringananNominal = $nominalDasar * $persen / 100;
                        $nominalAkhir = max(0, $nominalDasar - $nilaiKeringananNominal);
                    } elseif ($jenisKeringanan === 'Pembebasan Penuh') {
                        $nilaiKeringananNominal = $nominalDasar;
                        $nominalAkhir = 0;
                    }

                    // Snapshot nominal pada keringanan aktif ikut menyesuaikan jika nominal
                    // dasar tagihan berubah, sedangkan nilai aturan (mis. 20/50 persen) tetap.
                    $this->db->where('id', (int) $keringananAktif['id'])->update('tagihan_keringanan_siswa', array(
                        'nominal_awal' => $nominalDasar,
                        'nominal_setelah_keringanan' => $nominalAkhir
                    ));
                } else {
                    // Tarif khusus lama tetap dipertahankan apabila tidak ada aturan aktif.
                    if ($jenisKeringanan === 'Tarif Khusus') {
                        $nominalAkhir = max(0, (float) $tagihan['nominal_tagihan']);
                        $nilaiKeringananNominal = max(0, $nominalDasar - $nominalAkhir);
                    }
                    // Potongan lama dihitung dari tarif dasar kelas/siswa yang berlaku.
                    if ($jenisKeringanan === 'Potongan Nominal') {
                        $nilaiKeringananNominal = min($nominalDasar, max(0, (float) $tagihan['nilai_keringanan']));
                        $nominalAkhir = max(0, $nominalDasar - $nilaiKeringananNominal);
                    } elseif ($jenisKeringanan === 'Potongan Persen') {
                        $nominalAwalLama = (float) $tagihan['nominal_awal'];
                        $potonganLama = max(0, (float) $tagihan['nilai_keringanan']);
                        $persen = $nominalAwalLama > 0 ? ($potonganLama / $nominalAwalLama) * 100 : 0;
                        $persen = min(100, max(0, $persen));
                        $nilaiKeringananNominal = $nominalDasar * $persen / 100;
                        $nominalAkhir = max(0, $nominalDasar - $nilaiKeringananNominal);
                    } elseif ($jenisKeringanan === 'Pembebasan Penuh' || $jenisKeringanan === 'Pembebasan') {
                        $jenisKeringanan = 'Pembebasan Penuh';
                        $nilaiKeringananNominal = $nominalDasar;
                        $nominalAkhir = 0;
                    }
                }

                $statusPembayaran = $jenisKeringanan === 'Pembebasan Penuh'
                    ? 'Dibebaskan'
                    : ($nominalAkhir <= 0 ? 'Lunas' : 'Belum Dibayar');

                $this->db->where('id', (int) $tagihan['id'])->update('tagihan_siswa', array(
                    'nama_tagihan' => $nama,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'nominal_awal' => $nominalDasar,
                    'jenis_keringanan' => $jenisKeringanan !== '' ? $jenisKeringanan : null,
                    'nilai_keringanan' => $nilaiKeringananNominal,
                    'nominal_tagihan' => $nominalAkhir,
                    'sisa_tagihan' => $nominalAkhir,
                    'status_pembayaran' => $statusPembayaran,
                    'tanggal_update' => date('d-m-Y'),
                    'waktu_update' => date('H:i:s')
                ));
                $updatedStudent++;
            }
        }

        $user = $this->session->userdata('admin');
        if ($firstNominal !== null) {
            $targetKelas = $this->db->where('id_tagihan_master', $id)->get('tagihan_target_kelas')->result_array();
            foreach ($targetKelas as $targetRow) {
                if (abs((float) $targetRow['nominal_kelas'] - (float) $master['nominal_default']) <= 0.001) {
                    $this->db->where('id', (int) $targetRow['id'])->update('tagihan_target_kelas', array('nominal_kelas' => (float) $firstNominal));
                }
            }
            $targetSiswa = $this->db->where('id_tagihan_master', $id)->get('tagihan_target_siswa')->result_array();
            foreach ($targetSiswa as $targetRow) {
                if (abs((float) $targetRow['nominal_target'] - (float) $master['nominal_default']) <= 0.001) {
                    $this->db->where('id', (int) $targetRow['id'])->update('tagihan_target_siswa', array('nominal_target' => (float) $firstNominal));
                }
            }
        }
        $this->db->where('id', $id)->update('tagihan_master', array(
            'nama_tagihan' => $nama,
            'nominal_default' => (float) $firstNominal,
            'tanggal_jatuh_tempo' => $firstDue,
            'tanggal_update' => date('d-m-Y'),
            'waktu_update' => date('H:i:s'),
            'id_user_update' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user_update' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Edit Tagihan Terbit', 'modul' => 'Tagihan', 'aksi' => 'Ubah', 'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id, 'nomor_referensi' => $master['kode_tagihan'],
            'keterangan' => 'Nama tagihan/nominal/jatuh tempo diperbarui untuk ' . $updatedStudent . ' tagihan siswa belum bayar; ' . $protectedStudent . ' tagihan yang sudah memiliki pembayaran dipertahankan.',
            'data_sebelum' => json_encode($master, JSON_UNESCAPED_UNICODE), 'data_sesudah' => null,
            'ip_address' => $this->input->ip_address(), 'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'), 'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Tagihan terbit gagal diperbarui.');
        }
        $this->db->trans_commit();
        return array(
            'result' => 'true',
            'message' => 'Tagihan berhasil diperbarui. Nama tagihan, nominal, dan jatuh tempo diterapkan pada ' . $updatedStudent . ' tagihan siswa yang belum membayar; ' . $protectedStudent . ' tagihan yang sudah memiliki pembayaran tetap menggunakan snapshot lama.'
        );
    }

    public function hapus_draft()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Draft tagihan tidak ditemukan.');
        if ($master['status'] !== 'Draft') return array('result' => 'false', 'message' => 'Hanya tagihan berstatus Draft yang dapat dihapus.');
        if ($this->db->where('id_tagihan_master', $id)->count_all_results('tagihan_siswa') > 0) {
            return array('result' => 'false', 'message' => 'Draft sudah memiliki tagihan siswa sehingga tidak dapat dihapus.');
        }

        $user = $this->session->userdata('admin');
        $idUser = is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0;
        $namaUser = is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator';

        $this->db->trans_begin();
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Hapus Draft Tagihan',
            'modul' => 'Tagihan',
            'aksi' => 'Batal',
            'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $master['kode_tagihan'],
            'keterangan' => 'Menghapus draft tagihan yang belum diterbitkan.',
            'data_sebelum' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => null,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => $idUser,
            'nama_user' => $namaUser
        ));
        $this->db->where('id_tagihan_master', $id)->delete('tagihan_tarif_bulan');
        $this->db->where('id_tagihan_master', $id)->delete('tagihan_target_kelas');
        $this->db->where('id_tagihan_master', $id)->delete('tagihan_target_siswa');
        $this->db->where('id', $id)->delete('tagihan_master');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Draft tagihan gagal dihapus.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Draft tagihan berhasil dihapus.');
    }

    public function terbitkan()
    {
        $id = (int) $this->input->post('id');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.');
        if ($master['status'] !== 'Draft') return array('result' => 'false', 'message' => 'Hanya draft yang dapat diterbitkan.');

        $periods = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_tarif_bulan')->result_array();
        $classTargets = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_kelas')->result_array();
        $studentTargets = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_siswa')->result_array();
        if (!$periods) return array('result' => 'false', 'message' => 'Draft belum memiliki periode tagihan aktif.');

        if ($master['target_tagihan'] === 'Siswa') {
            if (!$studentTargets) return array('result' => 'false', 'message' => 'Draft belum memiliki siswa target.');
            $ids = array_map('intval', array_column($studentTargets, 'id_siswa'));
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $students = $this->db->query(
                "SELECT s.id,s.nis,s.nisn,s.nama_lengkap,k.id id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,ta.periode
                 FROM siswa s
                 INNER JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
                 INNER JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
                 LEFT JOIN master_tahun_ajaran ta ON ta.id=CAST(k.id_periode AS UNSIGNED)
                 WHERE s.id IN ($placeholders) AND CAST(k.id_periode AS UNSIGNED)=?",
                array_merge($ids, array((int) $master['id_periode']))
            )->result_array();
        } else {
            $classIds = array_map('intval', array_column($classTargets, 'id_kelas_setting'));
            if (!$classIds) return array('result' => 'false', 'message' => 'Draft belum memiliki kelas target.');
            $placeholders = implode(',', array_fill(0, count($classIds), '?'));
            $students = $this->db->query(
                "SELECT DISTINCT s.id,s.nis,s.nisn,s.nama_lengkap,k.id id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,ta.periode
                 FROM siswa s
                 INNER JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
                 INNER JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
                 LEFT JOIN master_tahun_ajaran ta ON ta.id=CAST(k.id_periode AS UNSIGNED)
                 WHERE k.id IN ($placeholders) AND UPPER(s.status_aktif)='AKTIF' AND UPPER(s.status_siswa)='AKTIF'",
                $classIds
            )->result_array();
        }

        $classMap = array();
        foreach ($classTargets as $row) $classMap[(int) $row['id_kelas_setting']] = $row;
        $studentMap = array();
        foreach ($studentTargets as $row) $studentMap[(int) $row['id_siswa']] = $row;

        $generated = 0;
        $skipped = 0;
        $this->db->trans_begin();
        foreach ($students as $siswa) {
            foreach ($periods as $period) {
                $this->db->from('tagihan_siswa')
                    ->where('id_siswa', (int) $siswa['id'])
                    ->where('id_jenis_tagihan', (int) $master['id_jenis_tagihan'])
                    ->where('id_periode', (int) $master['id_periode'])
                    ->where('status_tagihan', 'Aktif');

                if ($master['tipe_tagihan'] === 'Tahunan') {
                    $this->db->where('tipe_tagihan', 'Tahunan');
                } elseif ($master['tipe_tagihan'] === 'Bulanan') {
                    $this->db->where('tipe_tagihan', 'Bulanan')->where('bulan', (int) $period['bulan'])->where('tahun', (int) $period['tahun']);
                } else {
                    $this->db->where('id_tagihan_master', $id)->where('bulan', (int) $period['bulan'])->where('tahun', (int) $period['tahun']);
                }

                if ($this->db->count_all_results() > 0) {
                    $skipped++;
                    continue;
                }

                $nominal = (float) $period['nominal'];
                if (isset($classMap[(int) $siswa['id_kelas_setting']])) {
                    $nominalKelas = (float) $classMap[(int) $siswa['id_kelas_setting']]['nominal_kelas'];
                    if ($nominalKelas > 0 && abs($nominalKelas - (float) $master['nominal_default']) > 0.001) {
                        $nominal = $nominalKelas;
                    }
                }
                if (isset($studentMap[(int) $siswa['id']])) {
                    $nominalSiswa = (float) $studentMap[(int) $siswa['id']]['nominal_target'];
                    if ($nominalSiswa > 0 && abs($nominalSiswa - (float) $master['nominal_default']) > 0.001) {
                        $nominal = $nominalSiswa;
                    }
                }

                $date = date('Ym');
                $like = 'TAG/' . $date . '/';
                $lastCode = $this->db->select('no_tagihan')->like('no_tagihan', $like, 'after')->order_by('id', 'DESC')->limit(1)->get('tagihan_siswa')->row_array();
                $next = 1;
                if ($lastCode && !empty($lastCode['no_tagihan'])) {
                    $parts = explode('/', $lastCode['no_tagihan']);
                    $next = ((int) end($parts)) + 1;
                }
                $noTagihan = $like . str_pad($next, 5, '0', STR_PAD_LEFT);

                $this->db->insert('tagihan_siswa', array(
                    'no_tagihan' => $noTagihan, 'id_tagihan_master' => $id, 'kode_tagihan' => $master['kode_tagihan'],
                    'id_jenis_tagihan' => $master['id_jenis_tagihan'], 'nama_jenis_tagihan' => $master['nama_jenis_tagihan'],
                    'nama_tagihan' => $master['nama_tagihan'], 'tipe_tagihan' => $master['tipe_tagihan'],
                    'id_periode' => $master['id_periode'], 'periode' => $master['periode'], 'semester' => null,
                    'bulan' => $period['bulan'], 'nama_bulan' => $period['nama_bulan'], 'tahun' => $period['tahun'],
                    'tanggal_jatuh_tempo' => $period['tanggal_jatuh_tempo'], 'id_siswa' => $siswa['id'],
                    'nis' => $siswa['nis'], 'nisn' => $siswa['nisn'], 'nama_siswa' => $siswa['nama_lengkap'],
                    'id_kelas_setting' => $siswa['id_kelas_setting'], 'id_kelas' => $siswa['id_kelas'], 'nama_kelas' => $siswa['nama_kelas'],
                    'nominal_awal' => $nominal, 'nilai_keringanan' => 0, 'nominal_tagihan' => $nominal,
                    'nominal_dibayar' => 0, 'sisa_tagihan' => $nominal, 'dianggap_tunggakan' => $master['dianggap_tunggakan'],
                    'status_pembayaran' => 'Belum Dibayar', 'status_tagihan' => 'Aktif', 'keterangan' => $master['keterangan'],
                    'tanggal_generate' => date('d-m-Y'), 'waktu_generate' => date('H:i:s'),
                    'id_user_generate' => isset($this->session->userdata('admin')['id_user']) ? (int) $this->session->userdata('admin')['id_user'] : 0,
                    'nama_user_generate' => isset($this->session->userdata('admin')['nama_lengkap']) ? $this->session->userdata('admin')['nama_lengkap'] : 'Administrator'
                ));
                $generated++;
            }
        }

        if ($skipped > 0) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Draft tidak dapat diterbitkan karena ditemukan ' . $skipped . ' tagihan yang sudah ada.');
        }
        if ($generated <= 0) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Tidak ada target siswa yang dapat dibuatkan tagihan.');
        }

        $user = $this->session->userdata('admin');
        $this->db->where('id', $id)->update('tagihan_master', array(
            'status' => 'Aktif', 'status_generate' => 'Selesai', 'tanggal_update' => date('d-m-Y'), 'waktu_update' => date('H:i:s'),
            'id_user_update' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user_update' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Terbitkan Draft Tagihan',
            'modul' => 'Tagihan',
            'aksi' => 'Ubah',
            'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $master['kode_tagihan'],
            'keterangan' => $generated . ' tagihan diterbitkan.',
            'data_sebelum' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => json_encode(array('status' => 'Aktif', 'jumlah_tagihan' => $generated), JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Penerbitan tagihan gagal.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => $generated . ' tagihan berhasil diterbitkan.');
    }

    public function batalkan_sisa()
    {
        $id = (int) $this->input->post('id');
        $alasan = trim((string) $this->input->post('alasan', true));
        if ($alasan === '') return array('result' => 'false', 'message' => 'Alasan pembatalan wajib diisi.');

        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master) return array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.');

        $rows = $this->db->where('id_tagihan_master', $id)->where('status_tagihan', 'Aktif')->where('CAST(sisa_tagihan AS DECIMAL(18,2)) > 0', null, false)->get('tagihan_siswa')->result_array();
        $this->db->trans_begin();
        foreach ($rows as $row) {
            $this->db->where('id', $row['id'])->update('tagihan_siswa', array(
                'sisa_tagihan' => 0,
                'status_tagihan' => 'Dibatalkan',
                'status_pembayaran' => 'Dibatalkan',
                'keterangan' => trim($row['keterangan'] . ' | Sisa dibatalkan: ' . $alasan),
                'tanggal_update' => date('d-m-Y'),
                'waktu_update' => date('H:i:s')
            ));
        }
        $user = $this->session->userdata('admin');
        $this->db->where('id', $id)->update('tagihan_master', array(
            'status' => 'Dibatalkan',
            'tanggal_update' => date('d-m-Y'),
            'waktu_update' => date('H:i:s'),
            'id_user_update' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user_update' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));
        $this->db->insert('tagihan_log_aktivitas', array(
            'jenis_aktivitas' => 'Batalkan Sisa Tagihan',
            'modul' => 'Tagihan',
            'aksi' => 'Batal',
            'nama_tabel' => 'tagihan_master',
            'id_referensi' => (string) $id,
            'nomor_referensi' => $master['kode_tagihan'],
            'keterangan' => $alasan,
            'data_sebelum' => json_encode($master, JSON_UNESCAPED_UNICODE),
            'data_sesudah' => json_encode(array('status' => 'Dibatalkan', 'jumlah_sisa_dibatalkan' => count($rows)), JSON_UNESCAPED_UNICODE),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'tanggal' => date('d-m-Y'),
            'waktu' => date('H:i:s'),
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Administrator'
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Sisa tagihan gagal dibatalkan.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => count($rows) . ' sisa tagihan berhasil dibatalkan. Pembayaran yang sudah masuk tetap tersimpan sebagai histori transaksi.');
    }
}
