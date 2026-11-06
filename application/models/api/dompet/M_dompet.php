<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_dompet extends CI_Model
{
    private $jumlah_data_per_halaman;
    private $jumlah_data_maksimal;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_wali', 'wali');
        $this->config->load('api');

        $this->jumlah_data_per_halaman = (int) $this->config->item('api_default_page_size');
        $this->jumlah_data_maksimal = (int) $this->config->item('api_max_page_size');

        if ($this->jumlah_data_per_halaman <= 0) {
            $this->jumlah_data_per_halaman = 20;
        }
        if ($this->jumlah_data_maksimal < $this->jumlah_data_per_halaman) {
            $this->jumlah_data_maksimal = max(100, $this->jumlah_data_per_halaman);
        }
    }

    public function detail($id_siswa)
    {
        $scope = $this->ambil_anak($id_siswa);
        if (!$scope['status']) {
            return $scope['response'];
        }

        return $this->berhasil('Detail dompet berhasil dimuat.', array(
            'anak' => $this->wali->format_anak($scope['anak']),
            'dompet' => $this->wali->format_dompet($scope['anak']),
        ));
    }

    public function penggunaan_harian($id_siswa)
    {
        $scope = $this->ambil_anak($id_siswa);
        if (!$scope['status']) {
            return $scope['response'];
        }

        return $this->berhasil(
            'Penggunaan harian berhasil dimuat.',
            $this->wali->format_penggunaan_harian($scope['anak'])
        );
    }

    public function ubah_limit($id_siswa)
    {
        if (!$this->id_valid($id_siswa)) {
            return $this->gagal('ID siswa belum valid.', 422, array(
                'id_siswa' => 'ID siswa harus berupa bilangan bulat positif.',
            ));
        }

        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $input = $this->input_json();
        $kesalahan = $this->validasi_limit($input);
        if (!empty($kesalahan)) {
            return $this->gagal('Data limit belum valid.', 422, $kesalahan);
        }

        try {
            $no_riwayat = 'LMT-'.date('YmdHis').'-'.strtoupper(bin2hex(random_bytes(6)));
        } catch (Exception $exception) {
            log_message('error', 'Gagal membuat nomor riwayat limit: '.$exception->getMessage());
            return $this->gagal('Nomor riwayat limit belum dapat dibuat.', 500);
        }

        $konteks = $otorisasi['data'];
        $batas_harian = (int) $input['batas_harian'];
        $batas_per_transaksi = (int) $input['batas_per_transaksi'];
        $sekarang = date('Y-m-d H:i:s');

        $this->db->trans_begin();

        $scope = $this->scope_limit_terkunci(
            (int) $konteks['id_wali_siswa'],
            (int) $id_siswa
        );

        if (!$scope) {
            $this->db->trans_rollback();
            return $this->gagal('Anak tidak ditemukan atau tidak terhubung dengan akun Wali.', 404);
        }

        if (strtoupper(trim((string) $scope['status_siswa'])) !== 'AKTIF'
            || strtoupper(trim((string) $scope['status_aktif_siswa'])) !== 'AKTIF') {
            $this->db->trans_rollback();
            return $this->gagal('Limit hanya dapat diubah untuk siswa aktif.', 409);
        }

        if ($scope['id_saldo'] === null) {
            $this->db->trans_rollback();
            return $this->gagal('Dompet siswa belum tersedia.', 409);
        }

        if ($scope['id_limit'] !== null
            && strtoupper(trim((string) $scope['status_limit'])) !== 'AKTIF') {
            $this->db->trans_rollback();
            return $this->gagal('Pengaturan limit siswa sedang tidak aktif.', 409);
        }

        $batas_harian_lama = $scope['id_limit'] !== null
            ? (int) $scope['batas_harian']
            : 0;
        $batas_transaksi_lama = $scope['id_limit'] !== null
            ? (int) $scope['batas_per_transaksi']
            : 0;

        if ($scope['id_limit'] !== null
            && $batas_harian_lama === $batas_harian
            && $batas_transaksi_lama === $batas_per_transaksi) {
            $this->db->trans_rollback();
            return $this->berhasil('Limit tidak berubah.', array(
                'id_siswa' => (int) $id_siswa,
                'batas_harian' => $batas_harian,
                'batas_per_transaksi' => $batas_per_transaksi,
                'riwayat_dibuat' => false,
            ));
        }

        $nama_user = $this->nama_pengguna($konteks);
        $data_pengaturan = array(
            'batas_harian' => $batas_harian,
            'batas_per_transaksi' => $batas_per_transaksi,
            'status_aktif' => 'AKTIF',
            'tanggal_update' => date('Y-m-d'),
            'waktu_update' => date('H:i:s'),
            'id_user_update' => (int) $konteks['id'],
            'nama_user_update' => $nama_user,
            'sumber_perubahan' => 'WALI_ANDROID',
        );

        if ($scope['id_limit'] !== null) {
            $this->db->where('id', (int) $scope['id_limit']);
            $this->db->update('pengaturan_dompet_siswa', $data_pengaturan);
        } else {
            $data_pengaturan['id_siswa'] = (int) $id_siswa;
            $this->db->insert('pengaturan_dompet_siswa', $data_pengaturan);
        }

        $this->db->insert('riwayat_perubahan_limit', array(
            'no_riwayat' => $no_riwayat,
            'id_siswa' => (int) $id_siswa,
            'batas_harian_lama' => $batas_harian_lama,
            'batas_harian_baru' => $batas_harian,
            'batas_transaksi_lama' => $batas_transaksi_lama,
            'batas_transaksi_baru' => $batas_per_transaksi,
            'sumber_perubahan' => 'WALI_ANDROID',
            'id_user' => (int) $konteks['id'],
            'nama_user' => $nama_user,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Limit belum dapat disimpan. Coba lagi nanti.', 500);
        }

        $anak = $this->wali->anak_dalam_scope(
            (int) $konteks['id_wali_siswa'],
            (int) $id_siswa
        );

        if (!$anak) {
            $this->db->trans_rollback();
            return $this->gagal('Limit belum dapat diverifikasi setelah disimpan.', 500);
        }

        $this->db->trans_commit();

        return $this->berhasil('Limit berhasil diubah.', array(
            'no_riwayat' => $no_riwayat,
            'riwayat_dibuat' => true,
            'dompet' => $this->wali->format_dompet($anak),
            'diperbarui_pada' => $sekarang,
        ));
    }

    public function histori_limit($id_siswa)
    {
        $scope = $this->ambil_anak($id_siswa);
        if (!$scope['status']) {
            return $scope['response'];
        }

        $pagination = $this->pagination();
        if (!$pagination['status']) {
            return $pagination['response'];
        }

        $halaman = $pagination['halaman'];
        $per_halaman = $pagination['per_halaman'];
        $offset = ($halaman - 1) * $per_halaman;

        $total_data = (int) $this->db
            ->select('COUNT(*) AS jumlah', false)
            ->from('riwayat_perubahan_limit')
            ->where('id_siswa', (int) $id_siswa)
            ->get()
            ->row_array()['jumlah'];

        $rows = $this->db
            ->select('no_riwayat, batas_harian_lama, batas_harian_baru,
                batas_transaksi_lama, batas_transaksi_baru,
                sumber_perubahan, nama_user, tanggal, waktu')
            ->from('riwayat_perubahan_limit')
            ->where('id_siswa', (int) $id_siswa)
            ->order_by('id', 'DESC')
            ->limit($per_halaman, $offset)
            ->get()
            ->result_array();

        $data = array();
        foreach ($rows as $row) {
            $data[] = array(
                'no_riwayat' => (string) $row['no_riwayat'],
                'batas_harian_lama' => (int) $row['batas_harian_lama'],
                'batas_harian_baru' => (int) $row['batas_harian_baru'],
                'batas_per_transaksi_lama' => (int) $row['batas_transaksi_lama'],
                'batas_per_transaksi_baru' => (int) $row['batas_transaksi_baru'],
                'sumber_perubahan' => $row['sumber_perubahan'],
                'nama_pengubah' => $row['nama_user'],
                'tanggal' => $row['tanggal'],
                'waktu' => $row['waktu'],
            );
        }

        return $this->berhasil_dengan_meta(
            empty($data) ? 'Belum ada histori perubahan limit.' : 'Histori limit berhasil dimuat.',
            $data,
            array(
                'halaman' => $halaman,
                'per_halaman' => $per_halaman,
                'total_data' => $total_data,
                'total_halaman' => $total_data > 0
                    ? (int) ceil($total_data / $per_halaman)
                    : 0,
            )
        );
    }

    public function riwayat_transaksi($id_siswa)
    {
        $scope = $this->ambil_anak($id_siswa);
        if (!$scope['status']) {
            return $scope['response'];
        }

        $pagination = $this->pagination();
        if (!$pagination['status']) {
            return $pagination['response'];
        }

        $jumlah_isi_saldo = $this->jumlah_riwayat_transaksi(
            'riwayat_isi_saldo',
            (int) $id_siswa
        );
        $jumlah_kantin = $this->jumlah_riwayat_transaksi(
            'transaksi_kantin',
            (int) $id_siswa
        );

        if ($jumlah_isi_saldo === null || $jumlah_kantin === null) {
            return $this->gagal('Riwayat transaksi belum dapat dimuat.', 500);
        }

        $total_data = $jumlah_isi_saldo + $jumlah_kantin;
        $offset = ($pagination['halaman'] - 1) * $pagination['per_halaman'];
        $jumlah_pengambilan = $offset + $pagination['per_halaman'];

        $riwayat_isi_saldo = $this->riwayat_isi_saldo(
            (int) $id_siswa,
            $jumlah_pengambilan
        );
        $riwayat_kantin = $this->riwayat_kantin(
            (int) $id_siswa,
            $jumlah_pengambilan
        );

        if ($riwayat_isi_saldo === null || $riwayat_kantin === null) {
            return $this->gagal('Riwayat transaksi belum dapat dimuat.', 500);
        }

        $rows = array_merge($riwayat_isi_saldo, $riwayat_kantin);
        usort($rows, array($this, 'urutkan_riwayat_transaksi'));
        $rows = array_slice($rows, $offset, $pagination['per_halaman']);

        $data = array();
        foreach ($rows as $row) {
            $data[] = $this->format_riwayat_transaksi($row);
        }

        return $this->berhasil_dengan_meta(
            empty($data)
                ? 'Belum ada riwayat transaksi untuk anak ini.'
                : 'Riwayat transaksi berhasil dimuat.',
            $data,
            array(
                'halaman' => $pagination['halaman'],
                'per_halaman' => $pagination['per_halaman'],
                'total_data' => $total_data,
                'total_halaman' => $total_data > 0
                    ? (int) ceil($total_data / $pagination['per_halaman'])
                    : 0,
            )
        );
    }

    public function detail_transaksi($id_siswa, $jenis_transaksi, $no_transaksi)
    {
        $scope = $this->ambil_anak($id_siswa);
        if (!$scope['status']) {
            return $scope['response'];
        }

        $jenis = $this->jenis_transaksi($jenis_transaksi);
        if ($jenis === null) {
            return $this->gagal('Jenis transaksi belum valid.', 422, array(
                'jenis_transaksi' => 'Jenis transaksi harus isi-saldo atau kantin.',
            ));
        }
        if (!$this->nomor_transaksi_valid($no_transaksi)) {
            return $this->gagal('Nomor transaksi belum valid.', 422, array(
                'no_transaksi' => 'Nomor transaksi hanya boleh berisi huruf, angka, dan tanda hubung.',
            ));
        }

        $nomor = strtoupper(trim((string) $no_transaksi));
        $row = $jenis === 'ISI_SALDO'
            ? $this->detail_riwayat_isi_saldo((int) $id_siswa, $nomor)
            : $this->detail_riwayat_kantin((int) $id_siswa, $nomor);

        if ($row === false) {
            return $this->gagal('Detail transaksi belum dapat dimuat.', 500);
        }

        if (!$row) {
            return $this->gagal('Transaksi tidak ditemukan.', 404);
        }

        return $this->berhasil(
            'Detail transaksi berhasil dimuat.',
            $this->format_riwayat_transaksi($row)
        );
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function ambil_anak($id_siswa)
    {
        if (!$this->id_valid($id_siswa)) {
            return array(
                'status' => false,
                'response' => $this->gagal('ID siswa belum valid.', 422, array(
                    'id_siswa' => 'ID siswa harus berupa bilangan bulat positif.',
                )),
            );
        }

        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return array('status' => false, 'response' => $otorisasi['response']);
        }

        $anak = $this->wali->anak_dalam_scope(
            (int) $otorisasi['data']['id_wali_siswa'],
            (int) $id_siswa
        );

        if (!$anak) {
            return array(
                'status' => false,
                'response' => $this->gagal(
                    'Anak tidak ditemukan atau tidak terhubung dengan akun Wali.',
                    404
                ),
            );
        }

        return array('status' => true, 'anak' => $anak, 'konteks' => $otorisasi['data']);
    }

    private function scope_limit_terkunci($id_wali_siswa, $id_siswa)
    {
        $sql = "SELECT s.id AS id_siswa, s.status_siswa,
                    s.status_aktif AS status_aktif_siswa,
                    sd.id AS id_saldo,
                    p.id AS id_limit, p.batas_harian, p.batas_per_transaksi,
                    p.status_aktif AS status_limit
                FROM wali_siswa_detail d
                INNER JOIN siswa s ON s.id = d.id_siswa
                LEFT JOIN saldo_dompet_siswa sd ON sd.id_siswa = s.id
                LEFT JOIN pengaturan_dompet_siswa p ON p.id_siswa = s.id
                WHERE d.id_wali_siswa = ?
                    AND d.id_siswa = ?
                    AND UPPER(TRIM(d.status_aktif)) = 'AKTIF'
                LIMIT 1
                FOR UPDATE";

        return $this->db
            ->query($sql, array($id_wali_siswa, $id_siswa))
            ->row_array();
    }

    private function validasi_limit($input)
    {
        $kesalahan = array();

        foreach (array('batas_harian', 'batas_per_transaksi') as $field) {
            if (!array_key_exists($field, $input) || !is_int($input[$field])) {
                $kesalahan[$field] = 'Nilai harus berupa bilangan bulat.';
            } elseif ($input[$field] <= 0) {
                $kesalahan[$field] = 'Nilai harus lebih dari nol.';
            } elseif ($input[$field] > 2147483647) {
                $kesalahan[$field] = 'Nilai melebihi kapasitas penyimpanan.';
            }
        }

        if (empty($kesalahan)
            && $input['batas_per_transaksi'] > $input['batas_harian']) {
            $kesalahan['batas_per_transaksi'] =
                'Limit per transaksi tidak boleh melebihi limit harian.';
        }

        return $kesalahan;
    }

    private function pagination()
    {
        $halaman = $this->input->get('halaman', true);
        $per_halaman = $this->input->get('per_halaman', true);

        $halaman = $halaman === null || $halaman === '' ? 1 : $halaman;
        $per_halaman = $per_halaman === null || $per_halaman === ''
            ? $this->jumlah_data_per_halaman
            : $per_halaman;

        if (filter_var($halaman, FILTER_VALIDATE_INT) === false || (int) $halaman <= 0) {
            return array(
                'status' => false,
                'response' => $this->gagal('Parameter halaman belum valid.', 422),
            );
        }

        if (filter_var($per_halaman, FILTER_VALIDATE_INT) === false
            || (int) $per_halaman <= 0
            || (int) $per_halaman > $this->jumlah_data_maksimal) {
            return array(
                'status' => false,
                'response' => $this->gagal(
                    'Parameter per_halaman harus antara 1 dan '
                        .$this->jumlah_data_maksimal.'.',
                    422
                ),
            );
        }

        return array(
            'status' => true,
            'halaman' => (int) $halaman,
            'per_halaman' => (int) $per_halaman,
        );
    }

    private function nama_pengguna($konteks)
    {
        $nama = trim((string) $konteks['nama_user']);
        if ($nama === '' && isset($konteks['nama_wali'])) {
            $nama = trim((string) $konteks['nama_wali']);
        }
        if ($nama === '') {
            $nama = trim((string) $konteks['username']);
        }

        return $nama;
    }

    private function input_json()
    {
        $raw = $this->input->raw_input_stream;
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    private function jumlah_riwayat_transaksi($table, $id_siswa)
    {
        $query = $this->db
            ->select('COUNT(*) AS jumlah', false)
            ->from($table)
            ->where('id_siswa', (int) $id_siswa)
            ->get();

        if ($query === false) {
            return null;
        }

        $row = $query->row_array();
        return $row ? (int) $row['jumlah'] : 0;
    }

    private function riwayat_isi_saldo($id_siswa, $limit)
    {
        return $this->ambil_riwayat_isi_saldo(
            (int) $id_siswa,
            null,
            (int) $limit
        );
    }

    private function riwayat_kantin($id_siswa, $limit)
    {
        return $this->ambil_riwayat_kantin(
            (int) $id_siswa,
            null,
            (int) $limit
        );
    }

    private function detail_riwayat_isi_saldo($id_siswa, $no_transaksi)
    {
        $rows = $this->ambil_riwayat_isi_saldo(
            (int) $id_siswa,
            $no_transaksi,
            1
        );

        if ($rows === null) {
            return false;
        }

        return isset($rows[0]) ? $rows[0] : null;
    }

    private function detail_riwayat_kantin($id_siswa, $no_transaksi)
    {
        $rows = $this->ambil_riwayat_kantin(
            (int) $id_siswa,
            $no_transaksi,
            1
        );

        if ($rows === null) {
            return false;
        }

        return isset($rows[0]) ? $rows[0] : null;
    }

    private function ambil_riwayat_isi_saldo($id_siswa, $no_transaksi, $limit)
    {
        $this->db
            ->select(
                "r.id AS id_transaksi, r.no_transaksi, r.id_siswa, r.nis,
                 r.nama_siswa, NULL AS kelas, r.nominal_saldo AS nominal,
                 r.biaya_layanan, r.total_pembayaran, r.saldo_sebelum,
                 r.saldo_sesudah, r.keterangan, r.status_transaksi,
                 r.sumber_transaksi, r.tanggal, r.waktu,
                 r.nama_user AS nama_petugas, p.no_pembatalan,
                 p.status_pembatalan, p.alasan AS alasan_pembatalan,
                 p.nominal_koreksi, p.saldo_sebelum_koreksi,
                 p.saldo_sesudah_koreksi, p.tanggal_pengajuan,
                 p.waktu_pengajuan, p.tanggal_proses, p.waktu_proses",
                false
            )
            ->from('riwayat_isi_saldo r')
            ->join(
                'pembatalan_dompet p',
                "p.jenis_transaksi = 'ISI_SALDO' AND p.id_transaksi = r.id",
                'left'
            )
            ->where('r.id_siswa', (int) $id_siswa);

        if ($no_transaksi !== null) {
            $this->db->where('r.no_transaksi', $no_transaksi);
        }

        $query = $this->db
            ->order_by('r.tanggal', 'DESC')
            ->order_by('r.waktu', 'DESC')
            ->order_by('r.id', 'DESC')
            ->limit(max(1, (int) $limit))
            ->get();

        if ($query === false) {
            return null;
        }

        $rows = $query->result_array();
        foreach ($rows as &$row) {
            $row['jenis_transaksi'] = 'ISI_SALDO';
            $row['arah'] = 'MASUK';
        }
        unset($row);

        return $rows;
    }

    private function ambil_riwayat_kantin($id_siswa, $no_transaksi, $limit)
    {
        $this->db
            ->select(
                "t.id AS id_transaksi, t.no_transaksi, t.id_siswa, t.nis,
                 t.nama_siswa, t.nama_kelas AS kelas, t.nominal,
                 0 AS biaya_layanan, t.nominal AS total_pembayaran,
                 t.saldo_sebelum, t.saldo_sesudah, t.keterangan,
                 t.status_transaksi, t.sumber_transaksi, t.tanggal,
                 t.waktu, t.nama_user AS nama_petugas, p.no_pembatalan,
                 p.status_pembatalan, p.alasan AS alasan_pembatalan,
                 p.nominal_koreksi, p.saldo_sebelum_koreksi,
                 p.saldo_sesudah_koreksi, p.tanggal_pengajuan,
                 p.waktu_pengajuan, p.tanggal_proses, p.waktu_proses",
                false
            )
            ->from('transaksi_kantin t')
            ->join(
                'pembatalan_dompet p',
                "p.jenis_transaksi = 'KANTIN' AND p.id_transaksi = t.id",
                'left'
            )
            ->where('t.id_siswa', (int) $id_siswa);

        if ($no_transaksi !== null) {
            $this->db->where('t.no_transaksi', $no_transaksi);
        }

        $query = $this->db
            ->order_by('t.tanggal', 'DESC')
            ->order_by('t.waktu', 'DESC')
            ->order_by('t.id', 'DESC')
            ->limit(max(1, (int) $limit))
            ->get();

        if ($query === false) {
            return null;
        }

        $rows = $query->result_array();
        foreach ($rows as &$row) {
            $row['jenis_transaksi'] = 'KANTIN';
            $row['arah'] = 'KELUAR';
        }
        unset($row);

        return $rows;
    }

    private function urutkan_riwayat_transaksi($a, $b)
    {
        $waktu_a = (string) $a['tanggal'].' '.(string) $a['waktu'];
        $waktu_b = (string) $b['tanggal'].' '.(string) $b['waktu'];
        $urutan = strcmp($waktu_b, $waktu_a);

        if ($urutan !== 0) {
            return $urutan;
        }

        $urutan = (int) $b['id_transaksi'] - (int) $a['id_transaksi'];
        if ($urutan !== 0) {
            return $urutan;
        }

        return strcmp(
            (string) $b['jenis_transaksi'],
            (string) $a['jenis_transaksi']
        );
    }

    private function format_riwayat_transaksi($row)
    {
        $pembatalan = null;
        if ($this->nilai_nullable($row['no_pembatalan']) !== null) {
            $pembatalan = array(
                'no_pembatalan' => (string) $row['no_pembatalan'],
                'status_pembatalan' => $this->nilai_nullable($row['status_pembatalan']),
                'alasan' => $this->nilai_nullable($row['alasan_pembatalan']),
                'nominal_koreksi' => $row['nominal_koreksi'] !== null
                    ? (int) $row['nominal_koreksi'] : null,
                'saldo_sebelum_koreksi' => $row['saldo_sebelum_koreksi'] !== null
                    ? (int) $row['saldo_sebelum_koreksi'] : null,
                'saldo_sesudah_koreksi' => $row['saldo_sesudah_koreksi'] !== null
                    ? (int) $row['saldo_sesudah_koreksi'] : null,
                'tanggal_pengajuan' => $this->nilai_nullable($row['tanggal_pengajuan']),
                'waktu_pengajuan' => $this->nilai_nullable($row['waktu_pengajuan']),
                'tanggal_proses' => $this->nilai_nullable($row['tanggal_proses']),
                'waktu_proses' => $this->nilai_nullable($row['waktu_proses']),
            );
        }

        return array(
            'jenis_transaksi' => (string) $row['jenis_transaksi'],
            'arah' => (string) $row['arah'],
            'no_transaksi' => (string) $row['no_transaksi'],
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => $this->nilai_nullable($row['nis']),
            'nama_siswa' => $this->nilai_nullable($row['nama_siswa']),
            'kelas' => $this->nilai_nullable($row['kelas']),
            'nominal' => (int) $row['nominal'],
            'biaya_layanan' => (int) $row['biaya_layanan'],
            'total_pembayaran' => (int) $row['total_pembayaran'],
            'saldo_sebelum' => $row['saldo_sebelum'] !== null
                ? (int) $row['saldo_sebelum'] : null,
            'saldo_sesudah' => $row['saldo_sesudah'] !== null
                ? (int) $row['saldo_sesudah'] : null,
            'keterangan' => $this->nilai_nullable($row['keterangan']),
            'status_transaksi' => (string) $row['status_transaksi'],
            'sumber_transaksi' => $this->nilai_nullable($row['sumber_transaksi']),
            'tanggal' => $this->nilai_nullable($row['tanggal']),
            'waktu' => $this->nilai_nullable($row['waktu']),
            'nama_petugas' => $this->nilai_nullable($row['nama_petugas']),
            'pembatalan' => $pembatalan,
        );
    }

    private function jenis_transaksi($nilai)
    {
        $nilai = strtolower(trim((string) $nilai));
        if ($nilai === 'isi-saldo' || $nilai === 'isi_saldo') {
            return 'ISI_SALDO';
        }
        if ($nilai === 'kantin') {
            return 'KANTIN';
        }
        return null;
    }

    private function nomor_transaksi_valid($nilai)
    {
        return is_string($nilai)
            && preg_match('/^[A-Z0-9-]{5,50}$/', strtoupper(trim($nilai))) === 1;
    }

    private function nilai_nullable($nilai)
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }
        return (string) $nilai;
    }

    private function id_valid($id)
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0;
    }

    private function berhasil($pesan, $data)
    {
        return array(
            'status' => true,
            'result' => true,
            'kode' => 200,
            'message' => $pesan,
            'pesan' => $pesan,
            'data' => $data,
        );
    }

    private function berhasil_dengan_meta($pesan, $data, $meta)
    {
        $response = $this->berhasil($pesan, $data);
        $response['meta'] = $meta;
        return $response;
    }

    private function gagal($pesan, $kode, $kesalahan = array())
    {
        return array(
            'status' => false,
            'result' => false,
            'kode' => $kode,
            'message' => $pesan,
            'pesan' => $pesan,
            'kesalahan' => (object) $kesalahan,
            'data' => null,
        );
    }
}
