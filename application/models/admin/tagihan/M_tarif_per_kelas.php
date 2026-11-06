<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_tarif_per_kelas extends CI_Model
{
    public function tagihan_list()
    {
        return $this->db
            ->where_in('status', array('Aktif', 'Draft'))
            ->order_by('id', 'DESC')
            ->get('tagihan_master')
            ->result_array();
    }

    public function result()
    {
        $id = (int) $this->input->post('id_tagihan');
        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();

        if (!$master) {
            return array('result' => 'false', 'message' => 'Tagihan tidak ditemukan.');
        }

        $classes = $this->db->query(
            "SELECT
                tk.*,
                COUNT(DISTINCT ts.id_siswa) jumlah_siswa
            FROM tagihan_target_kelas tk
            LEFT JOIN tagihan_siswa ts
                ON ts.id_tagihan_master=tk.id_tagihan_master
               AND ts.id_kelas_setting=tk.id_kelas_setting
               AND ts.status_tagihan='Aktif'
            WHERE tk.id_tagihan_master=?
              AND tk.status='Aktif'
            GROUP BY tk.id
            ORDER BY tk.nama_kelas",
            array($id)
        )->result_array();

        return array(
            'result' => 'true',
            'master' => $master,
            'classes' => $classes
        );
    }

    public function simpan()
    {
        $id = (int) $this->input->post('id_tagihan');
        $tarif = $this->input->post('tarif');
        if (!is_array($tarif)) {
            $tarif = array();
        }

        $master = $this->db->where('id', $id)->get('tagihan_master')->row_array();
        if (!$master || !$tarif) {
            return $this->model_response(false, 'Data tarif tidak lengkap.');
        }

        // Saat kembali ke tarif umum, setiap bulan mengikuti nominal bulan
        // masing-masing, bukan disamakan dengan nominal bulan pertama.
        $periodMap = array();
        $periods = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_tarif_bulan')->result_array();
        foreach ($periods as $period) {
            $periodMap[(int) $period['bulan'] . '-' . (int) $period['tahun']] = (float) $period['nominal'];
        }

        $studentMap = array();
        $studentTargets = $this->db->where('id_tagihan_master', $id)->where('status', 'Aktif')->get('tagihan_target_siswa')->result_array();
        foreach ($studentTargets as $target) $studentMap[(int) $target['id_siswa']] = $target;

        $this->db->trans_begin();

        foreach ($tarif as $idTarget => $nominalInput) {
            $idTarget = (int) $idTarget;
            $nominal = $this->nilai_nominal($nominalInput);

            if ($nominal < 0) {
                $this->db->trans_rollback();
                return $this->model_response(false, 'Tarif tidak boleh negatif.');
            }

            $target = $this->db
                ->where('id', $idTarget)
                ->where('id_tagihan_master', $id)
                ->where('status', 'Aktif')
                ->get('tagihan_target_kelas')
                ->row_array();

            if (!$target) {
                continue;
            }

            $tarifBerubah = abs((float) $target['nominal_kelas'] - $nominal) > 0.001;
            $tarifKelasKhusus = $nominal > 0 && abs($nominal - (float) $master['nominal_default']) > 0.001;
            // Jangan menimpa variasi tarif umum bulanan untuk kelas yang tidak diubah.
            // Kelas khusus tetap dapat disinkronkan ulang untuk tagihan belum dibayar.
            if (!$tarifBerubah && !$tarifKelasKhusus) {
                continue;
            }

            $before = $target;
            if ($tarifBerubah) {
                $this->db->where('id', $idTarget)->update('tagihan_target_kelas', array(
                    'nominal_kelas' => $nominal,
                    'tanggal' => $this->tanggal_sekarang(),
                    'waktu' => $this->waktu_sekarang(),
                    'id_user' => $this->app_user_id(),
                    'nama_user' => $this->app_user_name()
                ));
            }

            $rows = $this->db
                ->where('id_tagihan_master', $id)
                ->where('id_kelas_setting', $target['id_kelas_setting'])
                ->where('status_tagihan', 'Aktif')
                ->get('tagihan_siswa')
                ->result_array();

            $paidRows = $this->db
                ->select('DISTINCT pd.id_tagihan_siswa', false)
                ->from('tagihan_pembayaran_detail pd')
                ->join('tagihan_pembayaran p', 'p.id = pd.id_pembayaran')
                ->join('tagihan_siswa ts', 'ts.id = pd.id_tagihan_siswa')
                ->where('ts.id_tagihan_master', $id)
                ->where('ts.id_kelas_setting', $target['id_kelas_setting'])
                ->where('pd.status_detail', 'Aktif')
                ->where('p.status_transaksi', 'Aktif')
                ->get()->result_array();
            $paidIds = array_fill_keys(array_map('intval', array_column($paidRows, 'id_tagihan_siswa')), true);

            foreach ($rows as $row) {
                // Snapshot per siswa/per bulan tidak berubah begitu pembayaran masuk,
                // meskipun tarif kelas kemudian naik atau turun.
                if ((float) $row['nominal_dibayar'] > 0 || isset($paidIds[(int) $row['id']])) {
                    continue;
                }

                $idSiswa = (int) $row['id_siswa'];
                if (isset($studentMap[$idSiswa])) {
                    $nominalSiswa = (float) $studentMap[$idSiswa]['nominal_target'];
                    if ($nominalSiswa > 0 && abs($nominalSiswa - (float) $master['nominal_default']) > 0.001) {
                        continue;
                    }
                }

                // Tarif khusus siswa mempunyai prioritas di atas tarif kelas.
                if ($this->tarif_khusus_aktif($id, $idSiswa, (int) $row['bulan'], (int) $row['tahun'])
                    || trim((string) $row['jenis_keringanan']) === 'Tarif Khusus') {
                    continue;
                }

                $key = (int) $row['bulan'] . '-' . (int) $row['tahun'];
                $nominalDasar = $tarifKelasKhusus ? $nominal : (isset($periodMap[$key]) ? $periodMap[$key] : $nominal);
                $jenisKeringanan = trim((string) $row['jenis_keringanan']);
                $nominalAkhir = $nominalDasar;
                $potongan = 0;

                // Potongan/pembebasan pada tagihan yang belum dibayar tetap berlaku.
                $keringananAktif = $this->db
                    ->where('id_tagihan_master', $id)
                    ->where('id_siswa', $idSiswa)
                    ->where('bulan', (int) $row['bulan'])
                    ->where('tahun', (int) $row['tahun'])
                    ->where_in('jenis_keringanan', array('Potongan Nominal', 'Potongan Persen', 'Pembebasan Penuh'))
                    ->where('status', 'Aktif')
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('tagihan_keringanan_siswa')
                    ->row_array();

                if ($keringananAktif) {
                    $jenisKeringanan = trim((string) $keringananAktif['jenis_keringanan']);
                    if ($jenisKeringanan === 'Potongan Nominal') {
                        $potongan = min($nominalDasar, max(0, (float) $keringananAktif['nilai_keringanan']));
                        $nominalAkhir = max(0, $nominalDasar - $potongan);
                    } elseif ($jenisKeringanan === 'Potongan Persen') {
                        $persen = min(100, max(0, (float) $keringananAktif['nilai_keringanan']));
                        $potongan = $nominalDasar * $persen / 100;
                        $nominalAkhir = max(0, $nominalDasar - $potongan);
                    } elseif ($jenisKeringanan === 'Pembebasan Penuh') {
                        $potongan = $nominalDasar;
                        $nominalAkhir = 0;
                    }
                    $this->db->where('id', (int) $keringananAktif['id'])->update('tagihan_keringanan_siswa', array(
                        'nominal_awal' => $nominalDasar,
                        'nominal_setelah_keringanan' => $nominalAkhir
                    ));
                } elseif ($jenisKeringanan === 'Potongan Nominal') {
                    $potongan = min($nominalDasar, max(0, (float) $row['nilai_keringanan']));
                    $nominalAkhir = max(0, $nominalDasar - $potongan);
                } elseif ($jenisKeringanan === 'Potongan Persen') {
                    $oldBase = (float) $row['nominal_awal'];
                    $oldDiscount = max(0, (float) $row['nilai_keringanan']);
                    $persen = $oldBase > 0 ? min(100, max(0, $oldDiscount / $oldBase * 100)) : 0;
                    $potongan = $nominalDasar * $persen / 100;
                    $nominalAkhir = max(0, $nominalDasar - $potongan);
                } elseif ($jenisKeringanan === 'Pembebasan Penuh' || $jenisKeringanan === 'Pembebasan') {
                    $jenisKeringanan = 'Pembebasan Penuh';
                    $potongan = $nominalDasar;
                    $nominalAkhir = 0;
                }

                $status = $jenisKeringanan === 'Pembebasan Penuh'
                    ? 'Dibebaskan'
                    : ($nominalAkhir <= 0 ? 'Lunas' : 'Belum Dibayar');

                $this->db->where('id', (int) $row['id'])->update('tagihan_siswa', array(
                    'nominal_awal' => $nominalDasar,
                    'jenis_keringanan' => $jenisKeringanan !== '' ? $jenisKeringanan : null,
                    'nilai_keringanan' => $potongan,
                    'nominal_tagihan' => $nominalAkhir,
                    'sisa_tagihan' => $nominalAkhir,
                    'status_pembayaran' => $status,
                    'tanggal_update' => $this->tanggal_sekarang(),
                    'waktu_update' => $this->waktu_sekarang()
                ));
            }

            $this->tagihan_log_activity(
                $tarifBerubah ? 'Ubah Tarif Kelas' : 'Sinkronisasi Tarif Kelas',
                'Tagihan',
                'Ubah',
                'tagihan_target_kelas',
                $idTarget,
                $master['kode_tagihan'],
                $target['nama_kelas'] . ' menjadi ' . $this->rupiah($nominal),
                $before,
                array('nominal_kelas' => $nominal)
            );
        }

        return $this->tagihan_transaction_result('Tarif per kelas berhasil disimpan.');
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
        return is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0;
    }


    private function app_user_name()
    {
        $user = $this->session->userdata('admin');
        return is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator';
    }


    private function rupiah($nominal)
    {
        return 'Rp' . number_format((float) $nominal, 0, ',', '.');
    }


    private function nilai_nominal($value)
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim(str_ireplace(array('Rp', ' '), '', (string) $value));
        if (preg_match('/^-?\\d+\\.\\d{1,2}$/', $text)) {
            return (float) $text;
        }

        $clean = preg_replace('/[^0-9-]/', '', $text);
        if ($clean === '' || $clean === '-') {
            return 0;
        }

        return (float) $clean;
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
            'id_user' => is_array($user) && isset($user['id_user']) ? (int) $user['id_user'] : 0,
            'nama_user' => is_array($user) && isset($user['nama_lengkap']) && $user['nama_lengkap'] !== '' ? $user['nama_lengkap'] : 'Administrator'
        ));
    }
}
