<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_isi_saldo extends CI_Model
{
    private $jumlah_data_per_halaman;
    private $jumlah_data_maksimal;
    private $ukuran_maksimal_bukti;
    private $folder_bukti;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_wali', 'wali');
        $this->config->load('api');

        $this->jumlah_data_per_halaman = (int) $this->config->item('api_default_page_size');
        $this->jumlah_data_maksimal = (int) $this->config->item('api_max_page_size');
        $this->ukuran_maksimal_bukti = (int) $this->config->item('api_upload_proof_max_kb');
        $this->folder_bukti = rtrim(FCPATH, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'dompet'
            .DIRECTORY_SEPARATOR.'bukti_transfer'.DIRECTORY_SEPARATOR;

        if ($this->jumlah_data_per_halaman <= 0) {
            $this->jumlah_data_per_halaman = 20;
        }
        if ($this->jumlah_data_maksimal < $this->jumlah_data_per_halaman) {
            $this->jumlah_data_maksimal = max(100, $this->jumlah_data_per_halaman);
        }
        if ($this->ukuran_maksimal_bukti <= 0) {
            $this->ukuran_maksimal_bukti = 5120;
        }
    }

    public function pengaturan()
    {
        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $pengaturan = $this->ambil_pengaturan(false);
        if (!$pengaturan) {
            return $this->gagal('Pengaturan isi saldo belum tersedia.', 409);
        }

        $rekening = $this->db
            ->select('id, id_kasbank, nama_kasbank, nama_bank, nomor_rekening,
                nama_pemilik, petunjuk, urutan')
            ->from('rekening_dompet')
            ->where("UPPER(TRIM(status_aktif)) = 'AKTIF'", null, false)
            ->order_by('urutan', 'ASC')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();

        $data_rekening = array();
        foreach ($rekening as $item) {
            $data_rekening[] = $this->format_rekening($item);
        }

        $whatsapp = $this->db
            ->select('nomor')
            ->from('nomor_whatsapp_admin')
            ->where("TRIM(COALESCE(nomor, '')) <> ''", null, false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        return $this->berhasil('Pengaturan isi saldo berhasil dimuat.', array(
            'biaya_layanan' => (int) $pengaturan['biaya_layanan'],
            'minimal_isi_saldo' => (int) $pengaturan['minimal_isi_saldo'],
            'maksimal_isi_saldo' => (int) $pengaturan['maksimal_isi_saldo'],
            'nominal_cepat' => $this->nominal_cepat($pengaturan['nominal_cepat']),
            'status_layanan' => (string) $pengaturan['status_layanan'],
            'maksimal_ukuran_bukti_kb' => $this->ukuran_maksimal_bukti,
            'format_bukti' => array('jpg', 'jpeg', 'png', 'pdf'),
            'nomor_whatsapp_admin' => $this->format_nomor_whatsapp(
                $whatsapp ? $whatsapp['nomor'] : null
            ),
            'rekening' => $data_rekening,
        ));
    }

    public function buat($id_siswa)
    {
        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        if (!$this->id_valid($id_siswa)) {
            return $this->gagal('ID siswa belum valid.', 422, array(
                'id_siswa' => 'ID siswa harus berupa bilangan bulat positif.',
            ));
        }

        $input = $this->input->post(null, true);
        $input = is_array($input) ? $input : array();
        $kesalahan = $this->validasi_input_pengajuan($input);
        if (!empty($kesalahan)) {
            return $this->gagal('Data pengajuan belum valid.', 422, $kesalahan);
        }

        $konteks = $otorisasi['data'];
        $nominal = (int) $input['nominal_saldo'];
        $id_rekening = (int) $input['id_rekening_dompet'];
        $catatan = isset($input['catatan_wali'])
            ? trim((string) $input['catatan_wali'])
            : '';

        $validasi_awal = $this->validasi_bisnis_awal(
            (int) $konteks['id_wali_siswa'],
            (int) $id_siswa,
            $id_rekening,
            $nominal
        );
        if (!$validasi_awal['status']) {
            return $validasi_awal['response'];
        }

        $path_rel = null;
        $path_penuh = null;
        if ($this->bukti_dikirim()) {
            $upload = $this->unggah_bukti();
            if (!$upload['status']) {
                return $upload['response'];
            }
            $path_rel = 'uploads/dompet/bukti_transfer/'.$upload['nama_file'];
            $path_penuh = $upload['path_penuh'];
        }

        try {
            $no_pengajuan = 'TOP-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
        } catch (Exception $exception) {
            $this->hapus_upload_baru($path_penuh);
            log_message('error', 'Gagal membuat nomor pengajuan: '.$exception->getMessage());
            return $this->gagal('Nomor pengajuan belum dapat dibuat.', 500);
        }

        $this->db->trans_begin();
        $pengaturan = $this->ambil_pengaturan(true);
        $rekening = $this->ambil_rekening($id_rekening, true);
        $anak = $this->ambil_scope_anak_terkunci(
            (int) $konteks['id_wali_siswa'],
            (int) $id_siswa
        );

        $validasi_akhir = $this->validasi_bisnis($pengaturan, $rekening, $anak, $nominal);
        if ($validasi_akhir !== null) {
            $this->db->trans_rollback();
            $this->hapus_upload_baru($path_penuh);
            return $validasi_akhir;
        }

        $biaya = (int) $pengaturan['biaya_layanan'];
        $total = $nominal + $biaya;
        $nama_user = $this->nama_pengguna($konteks);
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');

        $this->db->insert('pengajuan_isi_saldo', array(
            'no_pengajuan' => $no_pengajuan,
            'id_siswa' => (int) $id_siswa,
            'nis' => (string) $anak['nis'],
            'nama_siswa' => (string) $anak['nama_siswa'],
            'id_wali_siswa' => (int) $konteks['id_wali_siswa'],
            'nama_wali' => $nama_user,
            'nominal_saldo' => $nominal,
            'biaya_layanan' => $biaya,
            'total_transfer' => $total,
            'id_rekening_dompet' => $id_rekening,
            'id_kasbank' => $rekening['id_kasbank'] !== null
                ? (int) $rekening['id_kasbank'] : null,
            'nama_kasbank' => $this->nama_rekening($rekening),
            'bukti_transfer' => $path_rel,
            'catatan_wali' => $catatan === '' ? null : $catatan,
            'status_pengajuan' => 'MENUNGGU_VERIFIKASI',
            'tanggal_pengajuan' => $tanggal,
            'waktu_pengajuan' => $waktu,
        ));
        $id_pengajuan = (int) $this->db->insert_id();

        $this->catat_aktivitas(
            $konteks,
            'BUAT_PENGAJUAN',
            $id_pengajuan,
            $no_pengajuan,
            'Pengajuan isi saldo Android dibuat dan menunggu verifikasi.'
        );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->hapus_upload_baru($path_penuh);
            return $this->gagal('Pengajuan belum dapat disimpan. Coba lagi nanti.', 500);
        }

        $row = $this->ambil_detail_id($id_pengajuan, (int) $konteks['id_wali_siswa']);
        if (!$row) {
            $this->db->trans_rollback();
            $this->hapus_upload_baru($path_penuh);
            return $this->gagal('Pengajuan belum dapat diverifikasi setelah disimpan.', 500);
        }

        $this->db->trans_commit();
        return $this->berhasil(
            'Pengajuan isi saldo berhasil dibuat dan menunggu verifikasi.',
            $this->format_pengajuan($row),
            201
        );
    }

    public function daftar()
    {
        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        $pagination = $this->pagination();
        if (!$pagination['status']) {
            return $pagination['response'];
        }
        $filter = $this->filter_daftar((int) $otorisasi['data']['id_wali_siswa']);
        if (!$filter['status']) {
            return $filter['response'];
        }

        $total = $this->db->query(
            'SELECT COUNT(*) AS jumlah FROM pengajuan_isi_saldo p WHERE '.$filter['sql'],
            $filter['params']
        )->row_array();
        $total_data = $total ? (int) $total['jumlah'] : 0;
        $offset = ($pagination['halaman'] - 1) * $pagination['per_halaman'];
        $params = $filter['params'];
        $params[] = $pagination['per_halaman'];
        $params[] = $offset;
        $rows = $this->db->query(
            $this->select_pengajuan().'
             WHERE '.$filter['sql'].'
             ORDER BY p.id DESC LIMIT ? OFFSET ?',
            $params
        )->result_array();

        $data = array();
        foreach ($rows as $row) {
            $data[] = $this->format_pengajuan($row);
        }

        return $this->berhasil_dengan_meta(
            empty($data) ? 'Belum ada pengajuan isi saldo.' : 'Daftar pengajuan berhasil dimuat.',
            $data,
            array(
                'halaman' => $pagination['halaman'],
                'per_halaman' => $pagination['per_halaman'],
                'total_data' => $total_data,
                'total_halaman' => $total_data > 0
                    ? (int) ceil($total_data / $pagination['per_halaman']) : 0,
            )
        );
    }

    public function detail($no_pengajuan)
    {
        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        if (!$this->nomor_pengajuan_valid($no_pengajuan)) {
            return $this->gagal('Nomor pengajuan belum valid.', 422);
        }

        $row = $this->ambil_detail_nomor(
            strtoupper(trim($no_pengajuan)),
            (int) $otorisasi['data']['id_wali_siswa']
        );
        if (!$row) {
            return $this->gagal('Pengajuan isi saldo tidak ditemukan.', 404);
        }
        return $this->berhasil('Detail pengajuan berhasil dimuat.', $this->format_pengajuan($row));
    }

    public function batalkan($no_pengajuan)
    {
        $otorisasi = $this->wali->konteks_wali();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }
        if (!$this->nomor_pengajuan_valid($no_pengajuan)) {
            return $this->gagal('Nomor pengajuan belum valid.', 422);
        }
        $konteks = $otorisasi['data'];
        $nomor = strtoupper(trim($no_pengajuan));

        $this->db->trans_begin();
        $row = $this->db->query(
            'SELECT * FROM pengajuan_isi_saldo
             WHERE no_pengajuan = ? AND id_wali_siswa = ? LIMIT 1 FOR UPDATE',
            array($nomor, (int) $konteks['id_wali_siswa'])
        )->row_array();
        if (!$row) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan isi saldo tidak ditemukan.', 404);
        }
        if (strtoupper(trim((string) $row['status_pengajuan'])) !== 'MENUNGGU_VERIFIKASI') {
            $this->db->trans_rollback();
            return $this->gagal('Hanya pengajuan yang menunggu verifikasi yang dapat dibatalkan.', 409);
        }

        $this->db->where('id', (int) $row['id']);
        $this->db->update('pengajuan_isi_saldo', array(
            'status_pengajuan' => 'DIBATALKAN_WALI',
        ));
        $this->catat_aktivitas(
            $konteks,
            'BATALKAN_PENGAJUAN',
            (int) $row['id'],
            $nomor,
            'Pengajuan isi saldo dibatalkan oleh Wali. Saldo tidak berubah.'
        );
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->gagal('Pengajuan belum dapat dibatalkan.', 500);
        }

        $detail = $this->ambil_detail_id(
            (int) $row['id'],
            (int) $konteks['id_wali_siswa']
        );
        if (!$detail) {
            $this->db->trans_rollback();
            return $this->gagal('Status pembatalan belum dapat diverifikasi.', 500);
        }
        $this->db->trans_commit();
        return $this->berhasil(
            'Pengajuan isi saldo berhasil dibatalkan. Saldo tidak berubah.',
            $this->format_pengajuan($detail)
        );
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function validasi_bisnis_awal($id_wali_siswa, $id_siswa, $id_rekening, $nominal)
    {
        $anak = $this->wali->anak_dalam_scope($id_wali_siswa, $id_siswa);
        $pengaturan = $this->ambil_pengaturan(false);
        $rekening = $this->ambil_rekening($id_rekening, false);
        $response = $this->validasi_bisnis($pengaturan, $rekening, $anak, $nominal);
        return $response === null
            ? array('status' => true)
            : array('status' => false, 'response' => $response);
    }

    private function validasi_bisnis($pengaturan, $rekening, $anak, $nominal)
    {
        if (!$pengaturan) {
            return $this->gagal('Pengaturan isi saldo belum tersedia.', 409);
        }
        if (strtoupper(trim((string) $pengaturan['status_layanan'])) !== 'AKTIF') {
            return $this->gagal('Layanan Dompet Digital sedang tidak aktif.', 409);
        }
        $minimal = (int) $pengaturan['minimal_isi_saldo'];
        $maksimal = (int) $pengaturan['maksimal_isi_saldo'];
        if ($minimal <= 0 || $maksimal < $minimal) {
            return $this->gagal('Batas pengajuan isi saldo belum valid.', 409);
        }
        if ($nominal < $minimal || $nominal > $maksimal) {
            return $this->gagal('Nominal isi saldo berada di luar batas yang diizinkan.', 422, array(
                'nominal_saldo' => 'Nominal harus antara '.$minimal.' dan '.$maksimal.'.',
            ));
        }
        if ((int) $pengaturan['biaya_layanan'] < 0
            || $nominal > 2147483647 - (int) $pengaturan['biaya_layanan']) {
            return $this->gagal('Perhitungan total transfer belum valid.', 409);
        }
        if (!$rekening) {
            return $this->gagal('Rekening tujuan tidak ditemukan atau tidak aktif.', 404);
        }
        if (!$anak) {
            return $this->gagal('Anak tidak ditemukan atau tidak terhubung dengan akun Wali.', 404);
        }
        if (strtoupper(trim((string) $anak['status_siswa'])) !== 'AKTIF'
            || strtoupper(trim((string) $anak['status_aktif_siswa'])) !== 'AKTIF') {
            return $this->gagal('Pengajuan hanya dapat dibuat untuk siswa aktif.', 409);
        }
        if (!array_key_exists('id_saldo', $anak) || $anak['id_saldo'] === null) {
            return $this->gagal('Dompet siswa belum tersedia.', 409);
        }
        return null;
    }

    private function validasi_input_pengajuan($input)
    {
        $kesalahan = array();
        foreach (array('nominal_saldo', 'id_rekening_dompet') as $field) {
            if (!isset($input[$field])
                || filter_var($input[$field], FILTER_VALIDATE_INT) === false
                || (int) $input[$field] <= 0) {
                $kesalahan[$field] = 'Nilai harus berupa bilangan bulat positif.';
            }
        }
        if (isset($input['catatan_wali'])
            && (!is_string($input['catatan_wali'])
                || strlen(trim($input['catatan_wali'])) > 255)) {
            $kesalahan['catatan_wali'] = 'Catatan maksimal 255 karakter.';
        }
        return $kesalahan;
    }

    private function bukti_dikirim()
    {
        return isset($_FILES['bukti_transfer'])
            && isset($_FILES['bukti_transfer']['error'])
            && (int) $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_NO_FILE;
    }

    private function unggah_bukti()
    {
        if (!is_dir($this->folder_bukti) || !is_writable($this->folder_bukti)) {
            log_message('error', 'Folder bukti transfer tidak tersedia atau tidak dapat ditulis.');
            return array(
                'status' => false,
                'response' => $this->gagal('Penyimpanan bukti transfer belum tersedia.', 500),
            );
        }

        $config = array(
            'upload_path' => $this->folder_bukti,
            'allowed_types' => 'jpg|jpeg|png|pdf',
            'max_size' => $this->ukuran_maksimal_bukti,
            'encrypt_name' => true,
            'remove_spaces' => true,
            'detect_mime' => true,
            'mod_mime_fix' => true,
        );
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('bukti_transfer')) {
            $pesan = strip_tags($this->upload->display_errors('', ''));
            return array(
                'status' => false,
                'response' => $this->gagal('Bukti transfer belum valid.', 422, array(
                    'bukti_transfer' => $pesan,
                )),
            );
        }
        $data = $this->upload->data();
        return array(
            'status' => true,
            'nama_file' => (string) $data['file_name'],
            'path_penuh' => (string) $data['full_path'],
        );
    }

    private function hapus_upload_baru($path)
    {
        if (!is_string($path) || !is_file($path)) {
            return;
        }
        $folder = realpath($this->folder_bukti);
        $file = realpath($path);
        if ($folder !== false && $file !== false
            && dirname($file) === $folder) {
            @unlink($file);
        }
    }

    private function ambil_pengaturan($terkunci)
    {
        $sql = 'SELECT biaya_layanan, minimal_isi_saldo, maksimal_isi_saldo,
                    nominal_cepat, status_layanan
                FROM pengaturan_dompet ORDER BY id DESC LIMIT 1';
        if ($terkunci) {
            $sql .= ' FOR UPDATE';
        }
        return $this->db->query($sql)->row_array();
    }

    private function ambil_rekening($id_rekening, $terkunci)
    {
        $sql = "SELECT id, id_kasbank, nama_kasbank, nama_bank, nomor_rekening,
                    nama_pemilik, petunjuk, urutan
                FROM rekening_dompet
                WHERE id = ? AND UPPER(TRIM(status_aktif)) = 'AKTIF'
                LIMIT 1";
        if ($terkunci) {
            $sql .= ' FOR UPDATE';
        }
        return $this->db->query($sql, array($id_rekening))->row_array();
    }

    private function ambil_scope_anak_terkunci($id_wali_siswa, $id_siswa)
    {
        return $this->db->query(
            "SELECT s.id AS id_siswa, s.nis, s.nama_lengkap AS nama_siswa,
                    s.status_siswa, s.status_aktif AS status_aktif_siswa,
                    sd.id AS id_saldo, sd.status_dompet
             FROM wali_siswa_detail d
             INNER JOIN siswa s ON s.id = d.id_siswa
             LEFT JOIN saldo_dompet_siswa sd ON sd.id_siswa = s.id
             WHERE d.id_wali_siswa = ? AND d.id_siswa = ?
                AND UPPER(TRIM(d.status_aktif)) = 'AKTIF'
             LIMIT 1 FOR UPDATE",
            array($id_wali_siswa, $id_siswa)
        )->row_array();
    }

    private function ambil_detail_id($id, $id_wali_siswa)
    {
        return $this->db->query(
            $this->select_pengajuan().' WHERE p.id = ? AND p.id_wali_siswa = ? LIMIT 1',
            array($id, $id_wali_siswa)
        )->row_array();
    }

    private function ambil_detail_nomor($nomor, $id_wali_siswa)
    {
        return $this->db->query(
            $this->select_pengajuan().' WHERE p.no_pengajuan = ? AND p.id_wali_siswa = ? LIMIT 1',
            array($nomor, $id_wali_siswa)
        )->row_array();
    }

    private function select_pengajuan()
    {
        return "SELECT p.*, r.nama_bank, r.nomor_rekening, r.nama_pemilik,
                    r.petunjuk, r.urutan
                FROM pengajuan_isi_saldo p
                LEFT JOIN rekening_dompet r ON r.id = p.id_rekening_dompet";
    }

    private function format_pengajuan($row)
    {
        return array(
            'no_pengajuan' => (string) $row['no_pengajuan'],
            'id_siswa' => (int) $row['id_siswa'],
            'nis' => $this->nilai_nullable($row['nis']),
            'nama_siswa' => $this->nilai_nullable($row['nama_siswa']),
            'nama_wali' => $this->nilai_nullable($row['nama_wali']),
            'nominal_saldo' => (int) $row['nominal_saldo'],
            'biaya_layanan' => (int) $row['biaya_layanan'],
            'total_transfer' => (int) $row['total_transfer'],
            'rekening' => array(
                'id_rekening_dompet' => $row['id_rekening_dompet'] !== null
                    ? (int) $row['id_rekening_dompet'] : null,
                'id_kasbank' => $row['id_kasbank'] !== null ? (int) $row['id_kasbank'] : null,
                'nama_kasbank' => $this->nilai_nullable($row['nama_kasbank']),
                'nama_bank' => $this->nilai_nullable($row['nama_bank']),
                'nomor_rekening' => $this->nilai_nullable($row['nomor_rekening']),
                'nama_pemilik' => $this->nilai_nullable($row['nama_pemilik']),
            ),
            'nama_bukti' => $row['bukti_transfer'] !== null
                ? basename((string) $row['bukti_transfer']) : null,
            'catatan_wali' => $this->nilai_nullable($row['catatan_wali']),
            'status_pengajuan' => (string) $row['status_pengajuan'],
            'alasan_penolakan' => $this->nilai_nullable($row['alasan_penolakan']),
            'tanggal' => $this->nilai_nullable($row['tanggal_pengajuan']),
            'waktu' => $this->nilai_nullable($row['waktu_pengajuan']),
            'tanggal_verifikasi' => $this->nilai_nullable($row['tanggal_verifikasi']),
            'waktu_verifikasi' => $this->nilai_nullable($row['waktu_verifikasi']),
            'nama_verifikator' => $this->nilai_nullable($row['nama_user_verifikasi']),
            'saldo_berubah' => false,
        );
    }

    private function format_rekening($row)
    {
        return array(
            'id_rekening_dompet' => (int) $row['id'],
            'id_kasbank' => $row['id_kasbank'] !== null ? (int) $row['id_kasbank'] : null,
            'nama_kasbank' => $this->nilai_nullable($row['nama_kasbank']),
            'nama_bank' => $this->nilai_nullable($row['nama_bank']),
            'nomor_rekening' => $this->nilai_nullable($row['nomor_rekening']),
            'nama_pemilik' => $this->nilai_nullable($row['nama_pemilik']),
            'petunjuk' => $this->nilai_nullable($row['petunjuk']),
        );
    }

    private function filter_daftar($id_wali_siswa)
    {
        $sql = 'p.id_wali_siswa = ?';
        $params = array($id_wali_siswa);
        $id_siswa = $this->input->get('id_siswa', true);
        $status = $this->input->get('status', true);
        if ($id_siswa !== null && $id_siswa !== '') {
            if (!$this->id_valid($id_siswa)) {
                return array('status' => false, 'response' => $this->gagal('ID siswa belum valid.', 422));
            }
            $sql .= ' AND p.id_siswa = ?';
            $params[] = (int) $id_siswa;
        }
        if ($status !== null && $status !== '') {
            $status = strtoupper(trim((string) $status));
            $daftar_status = array(
                'MENUNGGU_VERIFIKASI', 'DISETUJUI', 'DITOLAK',
                'DIBATALKAN_WALI', 'KEDALUWARSA',
            );
            if (!in_array($status, $daftar_status, true)) {
                return array('status' => false, 'response' => $this->gagal('Status pengajuan belum valid.', 422));
            }
            $sql .= ' AND p.status_pengajuan = ?';
            $params[] = $status;
        }
        return array('status' => true, 'sql' => $sql, 'params' => $params);
    }

    private function pagination()
    {
        $halaman = $this->input->get('halaman', true);
        $per_halaman = $this->input->get('per_halaman', true);
        $halaman = $halaman === null || $halaman === '' ? 1 : $halaman;
        $per_halaman = $per_halaman === null || $per_halaman === ''
            ? $this->jumlah_data_per_halaman : $per_halaman;
        if (filter_var($halaman, FILTER_VALIDATE_INT) === false || (int) $halaman <= 0) {
            return array('status' => false, 'response' => $this->gagal('Parameter halaman belum valid.', 422));
        }
        if (filter_var($per_halaman, FILTER_VALIDATE_INT) === false
            || (int) $per_halaman <= 0 || (int) $per_halaman > $this->jumlah_data_maksimal) {
            return array('status' => false, 'response' => $this->gagal(
                'Parameter per_halaman harus antara 1 dan '.$this->jumlah_data_maksimal.'.', 422
            ));
        }
        return array(
            'status' => true,
            'halaman' => (int) $halaman,
            'per_halaman' => (int) $per_halaman,
        );
    }

    private function catat_aktivitas($konteks, $aksi, $id_referensi, $no_referensi, $keterangan)
    {
        $this->db->insert('log_aktivitas_dompet', array(
            'id_user' => (int) $konteks['id'],
            'nama_user' => $this->nama_pengguna($konteks),
            'level' => (string) $konteks['level'],
            'modul' => 'PENGAJUAN_ISI_SALDO',
            'aksi' => $aksi,
            'id_referensi' => $id_referensi,
            'no_referensi' => $no_referensi,
            'keterangan' => $keterangan,
            'alamat_ip' => $this->input->ip_address(),
            'id_perangkat' => isset($konteks['id_perangkat'])
                ? (string) $konteks['id_perangkat'] : null,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
        ));
    }

    private function nominal_cepat($nilai)
    {
        if (!is_string($nilai) || trim($nilai) === '') {
            return array();
        }
        $hasil = array();
        foreach (explode(',', $nilai) as $item) {
            $item = trim($item);
            if (filter_var($item, FILTER_VALIDATE_INT) !== false && (int) $item > 0) {
                $hasil[(int) $item] = (int) $item;
            }
        }
        sort($hasil, SORT_NUMERIC);
        return array_values($hasil);
    }

    private function format_nomor_whatsapp($nilai)
    {
        if ($nilai === null) {
            return null;
        }
        $nomor = preg_replace('/[^0-9]/', '', trim((string) $nilai));
        $panjang = strlen($nomor);
        return $panjang >= 8 && $panjang <= 15 ? $nomor : null;
    }

    private function nama_pengguna($konteks)
    {
        $nama = trim((string) $konteks['nama_user']);
        if ($nama === '' && isset($konteks['nama_wali'])) {
            $nama = trim((string) $konteks['nama_wali']);
        }
        return $nama !== '' ? $nama : trim((string) $konteks['username']);
    }

    private function nama_rekening($rekening)
    {
        $nama = trim((string) $rekening['nama_kasbank']);
        if ($nama === '') {
            $nama = trim((string) $rekening['nama_bank']);
        }
        return $nama === '' ? null : $nama;
    }

    private function id_valid($id)
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0;
    }

    private function nomor_pengajuan_valid($nilai)
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

    private function berhasil($pesan, $data, $kode = 200)
    {
        return array(
            'status' => true, 'result' => true, 'kode' => $kode,
            'message' => $pesan, 'pesan' => $pesan, 'data' => $data,
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
            'status' => false, 'result' => false, 'kode' => $kode,
            'message' => $pesan, 'pesan' => $pesan,
            'kesalahan' => (object) $kesalahan, 'data' => null,
        );
    }
}
