<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class M_siswa_pembayar extends CI_Model
{
    public function tagihan_list()
    {
        return $this->db->where_in('status', array('Aktif', 'Draft'))->order_by('id', 'DESC')->get('tagihan_master')->result_array();
    }

    public function result()
    {
        $id = (int) $this->input->post('id_tagihan');
        $kelas = (int) $this->input->post('id_kelas_setting');
        $search = trim((string) $this->input->post('search', true));
        $status = trim((string) $this->input->post('status_tagihan', true));

        $m = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$m) {
            return array('result' => 'false', 'message' => 'Pilih tagihan.');
        }

        if (!in_array($status, array('', 'Aktif', 'Belum Ditambahkan'), true)) {
            $status = '';
        }

        $like = '%' . $search . '%';
        $current = array();
        $candidates = array();

        if ($status !== 'Belum Ditambahkan') {
            $sqlCurrent = "SELECT
                                ts.id_siswa,
                                ts.nis,
                                ts.nisn,
                                ts.nama_siswa,
                                ts.id_kelas_setting,
                                ts.nama_kelas,
                                COUNT(*) jumlah_baris,
                                MAX(ts.nominal_tagihan) tarif,
                                COALESCE(SUM(ts.nominal_dibayar),0) dibayar,
                                COALESCE(SUM(ts.sisa_tagihan),0) sisa,
                                CASE
                                    WHEN SUM(CASE WHEN ts.status_pembayaran='Dibebaskan' THEN 1 ELSE 0 END)=COUNT(*) THEN 'Dibebaskan'
                                    WHEN COALESCE(SUM(ts.sisa_tagihan),0)<=0 THEN 'Lunas'
                                    WHEN COALESCE(SUM(ts.nominal_dibayar),0)>0 THEN 'Dibayar Sebagian'
                                    ELSE 'Belum Dibayar'
                                END status_pembayaran,
                                'Aktif' status_penerima
                           FROM tagihan_siswa ts
                           WHERE ts.id_tagihan_master=?
                             AND (ts.nama_siswa LIKE ? OR ts.nis LIKE ? OR ts.nisn LIKE ?)
                             AND NOT EXISTS (
                                 SELECT 1 FROM tagihan_target_siswa target
                                 WHERE target.id_tagihan_master=ts.id_tagihan_master
                                   AND target.id_siswa=ts.id_siswa
                                   AND target.status='Nonaktif'
                             )";
            $paramsCurrent = array($id, $like, $like, $like);

            if ($kelas > 0) {
                $sqlCurrent .= " AND ts.id_kelas_setting=?";
                $paramsCurrent[] = $kelas;
            }

            $sqlCurrent .= " GROUP BY
                                ts.id_siswa,
                                ts.nis,
                                ts.nisn,
                                ts.nama_siswa,
                                ts.id_kelas_setting,
                                ts.nama_kelas
                             ORDER BY ts.nama_siswa";

            $current = $this->db->query($sqlCurrent, $paramsCurrent)->result_array();

            foreach ($current as &$row) {
                $row['bisa_dikeluarkan'] = 1;
            }
            unset($row);
        }

        $periods = $this->db
            ->where('id_tagihan_master', $id)
            ->where('status', 'Aktif')
            ->order_by('tahun', 'ASC')
            ->order_by('bulan', 'ASC')
            ->get('tagihan_tarif_bulan')
            ->result_array();

        $classTargets = $this->db
            ->where('id_tagihan_master', $id)
            ->where('status', 'Aktif')
            ->get('tagihan_target_kelas')
            ->result_array();

        $classMap = array();
        foreach ($classTargets as $row) {
            $classMap[(int) $row['id_kelas_setting']] = $row;
        }

        if ($status !== 'Aktif') {
            $sql = "SELECT DISTINCT
                        s.id id_siswa,
                        s.nis,
                        s.nisn,
                        s.nama_lengkap nama_siswa,
                        k.id id_kelas_setting,
                        k.id_kelas,
                        k.nama_kelas,
                        CASE WHEN EXISTS (
                            SELECT 1 FROM tagihan_target_siswa target
                            WHERE target.id_tagihan_master=? AND target.id_siswa=s.id
                              AND target.status='Nonaktif'
                        ) THEN 'Dikeluarkan' ELSE 'Belum Ditambahkan' END status_penerima,
                        CASE WHEN EXISTS (
                            SELECT 1 FROM tagihan_siswa lama
                            WHERE lama.id_tagihan_master=? AND lama.id_siswa=s.id
                              AND lama.status_tagihan='Aktif'
                        ) THEN 1 ELSE 0 END perlu_pembebasan
                    FROM siswa s
                    JOIN kelas_siswa ks
                        ON CAST(ks.id_siswa AS UNSIGNED)=s.id
                       AND UPPER(ks.status_aktif)='AKTIF'
                    JOIN kelas_setting k
                        ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
                    WHERE UPPER(s.status_aktif)='AKTIF'  AND UPPER(s.status_siswa)='AKTIF'
                      AND CAST(k.id_periode AS UNSIGNED)=?
                      AND (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?) 
                      AND (
                          NOT EXISTS (
                              SELECT 1 FROM tagihan_siswa t
                              WHERE t.id_tagihan_master=? AND t.id_siswa=s.id
                          )
                          OR EXISTS (
                              SELECT 1 FROM tagihan_target_siswa target
                              WHERE target.id_tagihan_master=? AND target.id_siswa=s.id
                                AND target.status='Nonaktif'
                          )
                      )";
            $params = array($id, $id, (int) $m['id_periode'], $like, $like, $like, $id, $id);

            if ($kelas > 0) {
                $sql .= " AND k.id=?";
                $params[] = $kelas;
            }

            $sql .= " ORDER BY s.nama_lengkap LIMIT 500";
            $candidates = $this->db->query($sql, $params)->result_array();

            foreach ($candidates as &$row) {
                $row['tarif'] = $this->tarif_calon_siswa(
                    $id,
                    (int) $row['id_siswa'],
                    (int) $row['id_kelas_setting'],
                    $periods,
                    $classMap
                );
                $row['dibayar'] = 0;
                $row['sisa'] = (float) $row['tarif'];
                $row['status_pembayaran'] = '-';
                // Status 'Dikeluarkan' dari target siswa tetap ditampilkan agar bisa dimasukkan kembali.
            }
            unset($row);
        }

        $classes = $this->db->query(
            "SELECT id,id_kelas,nama_kelas,id_periode
             FROM kelas_setting
             WHERE CAST(id_periode AS UNSIGNED)=?
             ORDER BY nama_kelas",
            array((int) $m['id_periode'])
        )->result_array();

        return array(
            'result' => 'true',
            'master' => $m,
            'current' => $current,
            'candidates' => $candidates,
            'classes' => $classes
        );
    }

    public function export_rows($idTagihan, $idKelasSetting = 0, $search = '')
    {
        $idTagihan = (int) $idTagihan;
        $idKelasSetting = (int) $idKelasSetting;
        $search = trim((string) $search);
        $master = $this->db->where('id', $idTagihan)->get('tagihan_master')->row_array();

        if (!$master) {
            return array('master' => null, 'rows' => array(), 'kelas' => 'Semua Kelas');
        }

        $like = '%' . $search . '%';
        $sql = "SELECT
                    ts.id_siswa,
                    ts.nis,
                    ts.nisn,
                    ts.nama_siswa,
                    ts.id_kelas_setting,
                    ts.nama_kelas,
                    MAX(ts.nominal_tagihan) tarif,
                    COALESCE(SUM(ts.nominal_dibayar),0) dibayar,
                    COALESCE(SUM(ts.sisa_tagihan),0) sisa,
                    CASE
                        WHEN SUM(CASE WHEN ts.status_pembayaran='Dibebaskan' THEN 1 ELSE 0 END)=COUNT(*) THEN 'Dibebaskan'
                        WHEN COALESCE(SUM(ts.sisa_tagihan),0)<=0 THEN 'Lunas'
                        WHEN COALESCE(SUM(ts.nominal_dibayar),0)>0 THEN 'Dibayar Sebagian'
                        ELSE 'Belum Dibayar'
                    END status_pembayaran,
                    'Aktif' status_penerima
                FROM tagihan_siswa ts
                WHERE ts.id_tagihan_master=?
                  AND (ts.nama_siswa LIKE ? OR ts.nis LIKE ? OR ts.nisn LIKE ?)
                  AND NOT EXISTS (
                      SELECT 1 FROM tagihan_target_siswa target
                      WHERE target.id_tagihan_master=ts.id_tagihan_master
                        AND target.id_siswa=ts.id_siswa
                        AND target.status='Nonaktif'
                  )";
        $params = array($idTagihan, $like, $like, $like);

        if ($idKelasSetting > 0) {
            $sql .= " AND ts.id_kelas_setting=?";
            $params[] = $idKelasSetting;
        }

        $sql .= " GROUP BY
                    ts.id_siswa,
                    ts.nis,
                    ts.nisn,
                    ts.nama_siswa,
                    ts.id_kelas_setting,
                    ts.nama_kelas
                  ORDER BY ts.nama_siswa ASC";

        $rows = $this->db->query($sql, $params)->result_array();
        $kelasLabel = 'Semua Kelas';

        if ($idKelasSetting > 0) {
            $kelasRow = $this->db->where('id', $idKelasSetting)->get('kelas_setting')->row_array();
            if ($kelasRow) {
                $kelasLabel = $kelasRow['nama_kelas'];
            }
        }

        return array('master' => $master, 'rows' => $rows, 'kelas' => $kelasLabel);
    }

    public function tambah()
    {
        $id = (int) $this->input->post('id_tagihan');
        $ids = $this->input->post('id_siswa');
        if (!is_array($ids)) {
            $ids = array();
        }

        $m = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$m || !$ids) {
            return $this->model_response(false, 'Pilih tagihan dan siswa.');
        }
        if ($m['status'] === 'Draft') {
            return $this->model_response(false, 'Terbitkan draft terlebih dahulu sebelum menambah siswa.');
        }

        $periods = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_tarif_bulan')->result_array();
        $classes = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_kelas')->result_array();
        $classMap = array();
        foreach ($classes as $x) {
            $classMap[(int) $x['id_kelas_setting']] = $x;
        }

        $this->db->trans_begin();
        $success = 0;
        $skip = 0;
        $diaktifkanKembali = 0;

        foreach ($ids as $sid) {
            $sid = (int) $sid;
            $s = $this->db->query(
                "SELECT s.*,k.id id_kelas_setting,k.id_kelas,k.nama_kelas,k.id_periode,ta.periode
                 FROM siswa s
                 JOIN kelas_siswa ks ON CAST(ks.id_siswa AS UNSIGNED)=s.id AND UPPER(ks.status_aktif)='AKTIF'
                 JOIN kelas_setting k ON k.id=CAST(ks.id_kelas_setting AS UNSIGNED)
                 LEFT JOIN master_tahun_ajaran ta ON ta.id=CAST(k.id_periode AS UNSIGNED)
                 WHERE s.id=?
                   AND UPPER(s.status_aktif)='AKTIF'  AND UPPER(s.status_siswa)='AKTIF'
                   AND CAST(k.id_periode AS UNSIGNED)=?
                 ORDER BY ks.id DESC
                 LIMIT 1",
                array($sid, (int) $m['id_periode'])
            )->row_array();

            if (!$s) {
                $skip++;
                continue;
            }

            // Memasukkan kembali siswa mengaktifkan tagihan lamanya tanpa membuat
            // nomor tagihan baru dan tanpa mengubah pembayaran yang telah diterima.
            $target = $this->db->where('id_tagihan_master', $id)
                ->where('id_siswa', $sid)->get('tagihan_target_siswa')->row_array();
            if ($target && $target['status'] === 'Nonaktif') {
                $existing = $this->db->query(
                    'SELECT * FROM tagihan_siswa WHERE id_tagihan_master=? AND id_siswa=? FOR UPDATE',
                    array($id, $sid)
                )->result_array();
                foreach ($existing as $bill) {
                    if ($bill['status_tagihan'] !== 'Nonaktif') {
                        continue;
                    }
                    // Pembebasan Penuh yang diberikan melalui menu Potongan
                    // tetap berlaku, sekalipun penerimanya dimasukkan kembali.
                    $bebasPenuh = $bill['jenis_keringanan'] === 'Pembebasan Penuh'
                        || (float) $bill['nominal_tagihan'] <= 0;
                    $dibayar = (float) $bill['nominal_dibayar'];
                    $sisa = $bebasPenuh ? 0 : max(0, (float) $bill['nominal_tagihan'] - $dibayar);
                    $status = $bebasPenuh ? 'Dibebaskan'
                        : ($sisa <= 0 ? 'Lunas' : ($dibayar > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar'));
                    if ($bill['status_pembayaran'] === 'Dibatalkan') {
                        continue;
                    }
                    $this->db->where('id', (int) $bill['id'])->update('tagihan_siswa', array(
                        'status_tagihan' => 'Aktif',
                        'sisa_tagihan' => (string) $sisa,
                        'status_pembayaran' => $status,
                        'tanggal_update' => $this->tanggal_sekarang(),
                        'waktu_update' => $this->waktu_sekarang()
                    ));
                }
                $this->db->where('id', $target['id'])
                    ->update('tagihan_target_siswa', array('status' => 'Aktif'));
                $diaktifkanKembali++;
            }

            foreach ($periods as $p) {
                if ($this->db->where('id_tagihan_master', $id)->where('id_siswa', $sid)->where('bulan', $p['bulan'])->where('tahun', $p['tahun'])->count_all_results('tagihan_siswa')) {
                    $skip++;
                    continue;
                }

                $nom = (float) $p['nominal'];
                if (isset($classMap[(int) $s['id_kelas_setting']]) && (float) $classMap[(int) $s['id_kelas_setting']]['nominal_kelas'] > 0) {
                    $nom = (float) $classMap[(int) $s['id_kelas_setting']]['nominal_kelas'];
                }

                $special = $this->tarif_khusus_aktif($id, $sid, (int) $p['bulan'], (int) $p['tahun']);
                $jenisKeringanan = null;
                $nilaiKeringanan = 0;
                if ($special) {
                    $tarifNormal = $nom;
                    $nom = (float) $special['nominal_setelah_keringanan'];
                    $jenisKeringanan = 'Tarif Khusus';
                    $nilaiKeringanan = max(0, $tarifNormal - $nom);
                }

                $this->db->insert('tagihan_siswa', array(
                    'no_tagihan' => $this->tagihan_next_code('TAG', 'tagihan_siswa', 'no_tagihan'),
                    'id_tagihan_master' => $id,
                    'kode_tagihan' => $m['kode_tagihan'],
                    'id_jenis_tagihan' => $m['id_jenis_tagihan'],
                    'nama_jenis_tagihan' => $m['nama_jenis_tagihan'],
                    'nama_tagihan' => $m['nama_tagihan'],
                    'tipe_tagihan' => $m['tipe_tagihan'],
                    'id_periode' => $m['id_periode'],
                    'periode' => $m['periode'],
                    'semester' => null,
                    'bulan' => $p['bulan'],
                    'nama_bulan' => $p['nama_bulan'],
                    'tahun' => $p['tahun'],
                    'tanggal_jatuh_tempo' => $p['tanggal_jatuh_tempo'],
                    'id_siswa' => $sid,
                    'nis' => $s['nis'],
                    'nisn' => $s['nisn'],
                    'nama_siswa' => $s['nama_lengkap'],
                    'id_kelas_setting' => $s['id_kelas_setting'],
                    'id_kelas' => $s['id_kelas'],
                    'nama_kelas' => $s['nama_kelas'],
                    'nominal_awal' => $special ? $tarifNormal : $nom,
                    'jenis_keringanan' => $jenisKeringanan,
                    'nilai_keringanan' => $nilaiKeringanan,
                    'nominal_tagihan' => $nom,
                    'nominal_dibayar' => 0,
                    'sisa_tagihan' => $nom,
                    'dianggap_tunggakan' => $m['dianggap_tunggakan'],
                    'status_pembayaran' => 'Belum Dibayar',
                    'status_tagihan' => 'Aktif',
                    'tanggal_generate' => $this->tanggal_sekarang(),
                    'waktu_generate' => $this->waktu_sekarang(),
                    'id_user_generate' => $this->app_user_id(),
                    'nama_user_generate' => $this->app_user_name()
                ));
                $success++;
            }
        }

        $this->tagihan_log_activity(
            'Tambah Siswa Pembayar',
            'Tagihan',
            'Tambah',
            'tagihan_siswa',
            $id,
            $m['kode_tagihan'],
            $success . ' baris tagihan ditambahkan',
            null,
            array('siswa' => $ids)
        );

        return $this->tagihan_transaction_result(
            $success . ' baris tagihan ditambahkan' .
            ($diaktifkanKembali ? ', ' . $diaktifkanKembali . ' siswa diaktifkan kembali' : '') .
            ($skip ? ', dan ' . $skip . ' baris sudah ada/dilewati.' : '.')
        );
    }

    public function keluarkan()
    {
        $id = (int) $this->input->post('id_tagihan');
        $ids = $this->input->post('id_siswa');
        if (!is_array($ids)) {
            $ids = array((int) $ids);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $m = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$m) {
            return $this->model_response(false, 'Tagihan tidak ditemukan.');
        }
        if (!$ids) {
            return $this->model_response(false, 'Pilih siswa yang akan dikeluarkan.');
        }

        $this->db->trans_begin();
        $berhasil = 0;
        $dilewati = 0;
        $barisDibebaskan = 0;
        $barisDipertahankan = 0;
        $totalSisaDibebaskan = 0;

        foreach ($ids as $sid) {
            // Kunci tagihan supaya pembayaran paralel tidak masuk setelah
            // pemeriksaan tetapi sebelum tagihan dinonaktifkan.
            $rows = $this->db->query(
                'SELECT * FROM tagihan_siswa WHERE id_tagihan_master=? AND id_siswa=? FOR UPDATE',
                array($id, $sid)
            )->result_array();
            if (!$rows) {
                $dilewati++;
                continue;
            }

            $target = $this->db->where('id_tagihan_master', $id)
                ->where('id_siswa', $sid)->get('tagihan_target_siswa')->row_array();
            $perluPembebasan = false;
            foreach ($rows as $row) {
                if ($row['status_tagihan'] === 'Aktif') {
                    $perluPembebasan = true;
                    break;
                }
            }
            // Siswa yang sudah dikeluarkan lewat revisi terdahulu masih mungkin
            // memiliki tagihan aktif; izinkan menyelesaikan pembebasannya.
            if ($target && $target['status'] === 'Nonaktif' && !$perluPembebasan) {
                $dilewati++;
                continue;
            }

            if ($target) {
                $this->db->where('id', (int) $target['id'])
                    ->update('tagihan_target_siswa', array('status' => 'Nonaktif'));
            } else {
                $first = $rows[0];
                $this->db->insert('tagihan_target_siswa', array(
                    'id_tagihan_master' => $id,
                    'id_siswa' => $sid,
                    'nis' => $first['nis'],
                    'nisn' => $first['nisn'],
                    'nama_siswa' => $first['nama_siswa'],
                    'id_kelas_setting' => $first['id_kelas_setting'],
                    'id_kelas' => $first['id_kelas'],
                    'nama_kelas' => $first['nama_kelas'],
                    'nominal_target' => $first['nominal_tagihan'],
                    'status' => 'Nonaktif',
                    'tanggal' => $this->tanggal_sekarang(),
                    'waktu' => $this->waktu_sekarang(),
                    'id_user' => $this->app_user_id(),
                    'nama_user' => $this->app_user_name()
                ));
            }

            $dibebaskan = 0;
            $dipertahankan = 0;
            $sisaDibebaskan = 0;
            foreach ($rows as $row) {
                if ($row['status_tagihan'] !== 'Aktif') {
                    continue;
                }

                $dibayar = (float) $row['nominal_dibayar'];
                $sisa = max(0, (float) $row['sisa_tagihan']);
                $status = $row['status_pembayaran'];
                if ($sisa > 0 && !in_array($status, array('Lunas', 'Dibatalkan'), true)) {
                    $status = 'Dibebaskan';
                    $dibebaskan++;
                    $sisaDibebaskan += $sisa;
                } else {
                    $dipertahankan++;
                }

                // Semua periode (lampau, berjalan, mendatang) dinonaktifkan.
                // Baris tagihan, jumlah dibayar, dan detail transaksi tidak
                // dihapus atau diubah, termasuk jika baru dibayar sebagian.
                $this->db->where('id', (int) $row['id'])->update('tagihan_siswa', array(
                    'status_tagihan' => 'Nonaktif',
                    'status_pembayaran' => $status,
                    'sisa_tagihan' => '0',
                    'tanggal_update' => $this->tanggal_sekarang(),
                    'waktu_update' => $this->waktu_sekarang()
                ));
            }

            $barisDibebaskan += $dibebaskan;
            $barisDipertahankan += $dipertahankan;
            $totalSisaDibebaskan += $sisaDibebaskan;
            $this->tagihan_log_activity(
                'Keluarkan Siswa Pembayar',
                'Tagihan',
                'Ubah',
                'tagihan_target_siswa',
                $sid,
                $m['kode_tagihan'],
                'Siswa dikeluarkan dari semua periode. ' . $dibebaskan .
                ' tagihan/sisa tagihan dibebaskan; ' . $dipertahankan .
                ' tagihan lunas/tanpa sisa dipertahankan. Pembayaran tidak dihapus atau dikembalikan.',
                $rows,
                array('status_penerima' => 'Nonaktif', 'total_sisa_dibebaskan' => $sisaDibebaskan)
            );
            $berhasil++;
        }

        if ($berhasil <= 0) {
            $this->db->trans_rollback();
            return $this->model_response(false, 'Siswa tidak ditemukan atau seluruh tagihannya sudah dibebaskan.');
        }

        return $this->tagihan_transaction_result(
            $berhasil . ' siswa berhasil dikeluarkan dari semua periode. ' .
            $barisDibebaskan . ' tagihan yang belum lunas dibebaskan (total sisa Rp' .
            number_format($totalSisaDibebaskan, 0, ',', '.') . '). ' .
            $barisDipertahankan . ' tagihan lunas/tanpa sisa tetap tercatat. ' .
            'Seluruh pembayaran dan riwayat tersimpan; uang tidak dikembalikan.' .
            ($dilewati ? ' ' . $dilewati . ' siswa dilewati.' : '')
        );
    }

    private function tarif_calon_siswa($idTagihan, $idSiswa, $idKelasSetting, $periods, $classMap)
    {
        $tarif = 0;

        foreach ($periods as $p) {
            $nominal = (float) $p['nominal'];

            if (isset($classMap[$idKelasSetting]) && (float) $classMap[$idKelasSetting]['nominal_kelas'] > 0) {
                $nominal = (float) $classMap[$idKelasSetting]['nominal_kelas'];
            }

            $special = $this->tarif_khusus_aktif($idTagihan, $idSiswa, (int) $p['bulan'], (int) $p['tahun']);
            if ($special) {
                $nominal = (float) $special['nominal_setelah_keringanan'];
            }

            if ($nominal > $tarif) {
                $tarif = $nominal;
            }
        }

        return $tarif;
    }

    private function tarif_khusus_aktif($idTagihan, $idSiswa, $bulan, $tahun)
    {
        return $this->db->query(
            "SELECT *
             FROM tagihan_keringanan_siswa
             WHERE id_tagihan_master=?
               AND id_siswa=?
               AND jenis_keringanan='Tarif Khusus'
               AND status='Aktif'
               AND ((bulan=? AND tahun=?) OR (bulan=0 AND tahun=0))
             ORDER BY
                CASE WHEN bulan=? AND tahun=? THEN 0 ELSE 1 END,
                id DESC
             LIMIT 1",
            array($idTagihan, $idSiswa, $bulan, $tahun, $bulan, $tahun)
        )->row_array();
    }

    private function app_user_id()
    {
        $user = $this->session->userdata('admin');
        return is_array($user) ? (int) ($user['id_user'] ?? $user['id'] ?? 0) : 0;
    }

    private function app_user_name()
    {
        $user = $this->session->userdata('admin');
        return is_array($user) ? (string) ($user['nama_lengkap'] ?? $user['nama'] ?? $user['username'] ?? 'Administrator') : 'Administrator';
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
            'id_user' => is_array($user) ? (int) ($user['id_user'] ?? $user['id'] ?? 0) : 0,
            'nama_user' => is_array($user) ? (string) ($user['nama_lengkap'] ?? $user['nama'] ?? $user['username'] ?? 'Administrator') : 'Administrator'
        ));
    }

    private function tagihan_next_code($prefix, $table, $column)
    {
        $date = date('Ym');
        $like = $prefix . '/' . $date . '/';
        $row = $this->db->select($column)
            ->like($column, $like, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($table)
            ->row_array();

        $next = 1;
        if ($row && !empty($row[$column])) {
            $parts = explode('/', $row[$column]);
            $next = ((int) end($parts)) + 1;
        }

        return $like . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
