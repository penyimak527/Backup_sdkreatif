<?php
class Laporan_per_siswa extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		header('Access-Control-Allow-Origin: *');
	}

    public function print_laporan()
    {
        $ambil = json_decode(file_get_contents('php://input'), true);
        $ambil = is_array($ambil) ? $ambil : array();
        $id_periode = (int) ($ambil['spp_id_periode_siswa'] ?? 0);
        $id_siswa = (int) ($ambil['spp_id_siswa'] ?? 0);
        if ($id_periode <= 0 || $id_siswa <= 0) {
            show_error('Pilih Tahun Ajaran dan satu Siswa terlebih dahulu.', 422);
            return;
        }
        $data = $this->dataTagihanPerSiswa($id_periode, $id_siswa);
        $this->load->view('admin/data_laporan/laporan_per_siswa', $data);
    }

    /** Data khusus Laporan Tagihan Per Siswa; tidak mengubah transaksi atau saldo. */
    private function dataTagihanPerSiswa($id_periode, $id_siswa)
    {
        $tahun = $this->db->select('periode')->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $nama_tahun = trim((string) ($tahun['periode'] ?? ''));
        if ($nama_tahun === '') {
            return array('tahun_ajaran' => '-', 'siswa_data' => array(), 'ringkasan_total' => array());
        }

        $sql = "SELECT ts.*, COALESCE(bayar.jumlah_cicilan,0) AS jumlah_cicilan,
                       COALESCE(bayar.total_bayar,0) AS pembayaran_aktif
                FROM tagihan_siswa ts
                LEFT JOIN (
                    SELECT pd.id_tagihan_siswa, COUNT(pd.id) AS jumlah_cicilan,
                           SUM(CAST(pd.nominal_bayar AS DECIMAL(18,2))) AS total_bayar
                    FROM tagihan_pembayaran_detail pd
                    JOIN tagihan_pembayaran p ON p.id=pd.id_pembayaran
                    WHERE pd.status_detail='Aktif' AND p.status_transaksi='Aktif'
                    GROUP BY pd.id_tagihan_siswa
                ) bayar ON bayar.id_tagihan_siswa=ts.id
                WHERE ts.id_periode=? AND ts.id_siswa=? AND ts.status_pembayaran<>'Dibatalkan'
                  AND (ts.status_tagihan='Aktif' OR COALESCE(bayar.total_bayar,0)>0)";
        $params = array($id_periode, $id_siswa);
        $sql .= ' ORDER BY ts.nama_siswa ASC, ts.id_siswa ASC, ts.tahun ASC, ts.bulan ASC, ts.nama_tagihan ASC, ts.id ASC';
        $tagihan = $this->db->query($sql, $params)->result_array();

        // Rincian asli transaksi: hanya pembayaran dan detail yang masih aktif.
        $sql_bayar = "SELECT ts.id_siswa, ts.nis, ts.nama_siswa, ts.nama_kelas, ts.id_periode,
                             ts.id AS id_tagihan_siswa, ts.no_tagihan, ts.nama_tagihan, ts.nama_bulan,
                             p.no_transaksi, p.tanggal_transaksi, p.waktu_transaksi,
                             p.nama_metode_pembayaran, pd.nominal_bayar,
                             pd.sisa_sebelum, pd.sisa_setelah, pd.status_setelah
                      FROM tagihan_pembayaran_detail pd
                      JOIN tagihan_pembayaran p ON p.id=pd.id_pembayaran
                      JOIN tagihan_siswa ts ON ts.id=pd.id_tagihan_siswa
                      WHERE ts.id_periode=? AND ts.id_siswa=? AND p.status_transaksi='Aktif'
                        AND pd.status_detail='Aktif' AND ts.status_pembayaran<>'Dibatalkan'";
        $params_bayar = array($id_periode, $id_siswa);
        $sql_bayar .= " ORDER BY ts.nama_siswa ASC, ts.id_siswa ASC,
                        STR_TO_DATE(p.tanggal_transaksi,'%d-%m-%Y') ASC,
                        p.waktu_transaksi ASC, p.id ASC, pd.id ASC";
        $pembayaran = $this->db->query($sql_bayar, $params_bayar)->result_array();

        // Tahun asal diambil dari master, bukan menganggap ID tahun ajaran selalu berurutan.
        $tahun_awal = preg_match('/^(\d{4})/', $nama_tahun, $match) ? (int) $match[1] : 0;
        $tunggakan_lama = array();
        if ($tahun_awal > 0) {
            $tanggal_jatuh_tempo = "STR_TO_DATE(COALESCE(NULLIF(ts.tanggal_jatuh_tempo,''),ts.tanggal_generate),'%d-%m-%Y')";
            $sql_lama = "SELECT ts.id_siswa, ts.nis, ts.nama_siswa, ts.nama_kelas,
                                ts.periode AS tahun_asal, ts.nama_tagihan, ts.no_tagihan,
                                ts.tanggal_jatuh_tempo, ts.sisa_tagihan
                         FROM tagihan_siswa ts
                         JOIN master_tahun_ajaran ta ON ta.id=ts.id_periode
                         WHERE CAST(LEFT(TRIM(ta.periode),4) AS UNSIGNED) < ?
                           AND ts.status_tagihan='Aktif' AND ts.dianggap_tunggakan='Ya'
                           AND ts.status_pembayaran NOT IN ('Lunas','Dibebaskan','Dibatalkan')
                           AND CAST(ts.sisa_tagihan AS DECIMAL(18,2))>0
                           AND $tanggal_jatuh_tempo <= CURDATE() AND ts.id_siswa=?";
            $params_lama = array($tahun_awal, $id_siswa);
            $sql_lama .= ' ORDER BY ts.nama_siswa ASC, ts.id_siswa ASC, ta.periode ASC, ts.nama_tagihan ASC';
            $tunggakan_lama = $this->db->query($sql_lama, $params_lama)->result_array();
        }

        $siswa_data = array();
        $buat_siswa = function ($row) use (&$siswa_data) {
            $id = (int) $row['id_siswa'];
            if (!isset($siswa_data[$id])) {
                $siswa_data[$id] = array(
                    'id_siswa' => $id, 'nama_siswa' => (string) ($row['nama_siswa'] ?? ''),
                    'nis' => (string) ($row['nis'] ?? ''),
                    'nama_kelas' => (string) ($row['nama_kelas'] ?? '-'),
                    'tagihan' => array(), 'pembayaran' => array(), 'cicilan' => array(), 'tunggakan_sebelumnya' => array(),
                    'ringkasan' => array('total_tagihan' => 0, 'total_pembayaran' => 0,
                                         'total_sisa' => 0, 'tunggakan_sebelumnya' => 0,
                                         'jumlah_tagihan' => 0, 'jumlah_pembayaran' => 0)
                );
            }
            return $id;
        };
        foreach ($tagihan as $row) {
            $id = $buat_siswa($row);
            $bayar = max(0, (float) $row['pembayaran_aktif']);
            $sisa = ($row['status_tagihan'] === 'Aktif' && !in_array($row['status_pembayaran'], array('Dibebaskan','Dibatalkan'), true))
                ? max(0, (float) $row['sisa_tagihan']) : 0;
            $row['target_efektif'] = $bayar + $sisa;
            $row['total_pembayaran'] = $bayar;
            $row['sisa_efektif'] = $sisa;
            $siswa_data[$id]['tagihan'][] = $row;
            $siswa_data[$id]['ringkasan']['jumlah_tagihan']++;
            $siswa_data[$id]['ringkasan']['total_tagihan'] += $row['target_efektif'];
            $siswa_data[$id]['ringkasan']['total_sisa'] += $sisa;
        }
        foreach ($pembayaran as $row) {
            $id = $buat_siswa($row);
            $siswa_data[$id]['pembayaran'][] = $row;
            $siswa_data[$id]['ringkasan']['jumlah_pembayaran']++;
            $siswa_data[$id]['ringkasan']['total_pembayaran'] += (float) $row['nominal_bayar'];
        }
        // Riwayat cicilan adalah bagian dari pembayaran, bukan transaksi tambahan.
        // Gunakan kondisi saat transaksi disimpan (sisa_setelah/status_setelah),
        // sehingga pembayaran yang langsung lunas tidak keliru diberi nomor cicilan.
        foreach ($siswa_data as &$data_siswa) {
            $jumlah_per_tagihan = array();
            $ada_pembayaran_sebagian = array();
            foreach ($data_siswa['pembayaran'] as $baris_bayar) {
                $kunci = (string) $baris_bayar['id_tagihan_siswa'];
                $jumlah_per_tagihan[$kunci] = ($jumlah_per_tagihan[$kunci] ?? 0) + 1;
                if ((float) ($baris_bayar['sisa_setelah'] ?? 0) > 0
                    || strcasecmp(trim((string) ($baris_bayar['status_setelah'] ?? '')), 'Dibayar Sebagian') === 0) {
                    $ada_pembayaran_sebagian[$kunci] = true;
                }
            }
            $urutan_cicilan = array();
            foreach ($data_siswa['pembayaran'] as $baris_bayar) {
                $kunci = (string) $baris_bayar['id_tagihan_siswa'];
                if ($jumlah_per_tagihan[$kunci] > 1 || !empty($ada_pembayaran_sebagian[$kunci])) {
                    $urutan_cicilan[$kunci] = ($urutan_cicilan[$kunci] ?? 0) + 1;
                    $baris_bayar['cicilan_ke'] = $urutan_cicilan[$kunci];
                    $data_siswa['cicilan'][] = $baris_bayar;
                }
            }
        }
        unset($data_siswa);

        foreach ($tunggakan_lama as $row) {
            $id = $buat_siswa($row);
            $siswa_data[$id]['tunggakan_sebelumnya'][] = $row;
            $siswa_data[$id]['ringkasan']['tunggakan_sebelumnya'] += max(0, (float) $row['sisa_tagihan']);
        }
        if (!isset($siswa_data[$id_siswa])) {
            $siswa = $this->db->select('id AS id_siswa, nama_lengkap AS nama_siswa, nis')
                ->where('id', $id_siswa)->get('siswa')->row_array();
            if ($siswa) $buat_siswa($siswa);
        }
        uasort($siswa_data, function ($a, $b) {
            return strcasecmp($a['nama_siswa'], $b['nama_siswa']);
        });
        $ringkasan_total = array('total_tagihan' => 0, 'total_pembayaran' => 0,
                                 'total_sisa' => 0, 'tunggakan_sebelumnya' => 0);
        foreach ($siswa_data as $item) {
            foreach ($ringkasan_total as $key => $nilai) {
                $ringkasan_total[$key] += $item['ringkasan'][$key];
            }
        }
        return array('tahun_ajaran' => $nama_tahun, 'siswa_data' => array_values($siswa_data),
                     'ringkasan_total' => $ringkasan_total);
    }
}
