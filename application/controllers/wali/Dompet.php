<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dompet extends CI_Controller
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wali/M_dompet_wali', 'model');
        $this->load->library('web_guard');
        $this->user = $this->autentikasi_wali($this->input->is_ajax_request());
    }

    public function index()
    {
        $anak_list = $this->model->daftar_anak((int) $this->user['id_wali_siswa']);
        $anak = $this->pilih_anak($anak_list);
        $data = array(
            'title' => 'Dashboard Dompet',
            'anak_list' => $anak_list,
            'anak' => $anak,
            'ringkasan' => $anak
                ? $this->model->dashboard((int) $this->user['id_wali_siswa'], (int) $anak['id_siswa'])
                : null,
            'topup' => $this->model->pengaturan_topup(),
            'csrf_token' => $this->web_guard->csrf_token(),
        );
        $this->halaman('wali/dompet/dashboard', $data);
    }

    public function history_belanja()
    {
        $anak_list = $this->model->daftar_anak((int) $this->user['id_wali_siswa']);
        $anak = $this->pilih_anak($anak_list);
        $halaman = $this->nomor_halaman();
        $filter = $this->filter_tanggal();
        $per_halaman = (int) $this->input->get('limit', true);
        if (!in_array($per_halaman, array(10, 25, 50, 100), true)) {
            $per_halaman = 10;
        }

        $riwayat = $anak
            ? $this->model->riwayat_belanja(
                (int) $this->user['id_wali_siswa'],
                (int) $anak['id_siswa'],
                $halaman,
                $per_halaman,
                $filter['valid'] ? $filter['tanggal_awal'] : null,
                $filter['valid'] ? $filter['tanggal_akhir'] : null
            )
            : null;

        if ($this->input->is_ajax_request()) {
            return $this->json(true, 'Data riwayat belanja berhasil dimuat.', array(
                'rows' => $riwayat ? $riwayat['rows'] : array(),
                'total' => $riwayat ? $riwayat['total'] : 0,
                'page' => $riwayat ? $riwayat['halaman'] : 1,
                'limit' => $per_halaman,
            ), 200);
        }

        $data = array(
            'title' => 'History Belanja Siswa',
            'anak_list' => $anak_list,
            'anak' => $anak,
            'filter' => $filter,
            'riwayat' => $riwayat,
        );
        $this->halaman('wali/dompet/history_belanja', $data);
    }

    public function history_isi_saldo()
    {
        $anak_list = $this->model->daftar_anak((int) $this->user['id_wali_siswa']);
        $anak = $this->pilih_anak($anak_list);
        $halaman = $this->nomor_halaman();
        $filter = $this->filter_tanggal();
        $data = array(
            'title' => 'History Isi Saldo',
            'anak_list' => $anak_list,
            'anak' => $anak,
            'filter' => $filter,
            'riwayat' => $anak
                ? $this->model->riwayat_isi_saldo(
                    (int) $this->user['id_wali_siswa'],
                    (int) $anak['id_siswa'],
                    $halaman,
                    15,
                    $filter['valid'] ? $filter['tanggal_awal'] : null,
                    $filter['valid'] ? $filter['tanggal_akhir'] : null
                )
                : null,
        );
        $this->halaman('wali/dompet/history_isi_saldo', $data);
    }

    public function setting_limit()
    {
        $anak_list = $this->model->daftar_anak((int) $this->user['id_wali_siswa']);
        $anak = $this->pilih_anak($anak_list);
        $data = array(
            'title' => 'Setting Limit Transaksi',
            'anak_list' => $anak_list,
            'anak' => $anak,
            'histori' => $anak
                ? $this->model->histori_limit((int) $this->user['id_wali_siswa'], (int) $anak['id_siswa'])
                : array(),
            'csrf_token' => $this->web_guard->csrf_token(),
        );
        $this->halaman('wali/dompet/setting_limit', $data);
    }

    public function buat_pengajuan()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            return $this->json(false, 'Metode HTTP tidak diizinkan.', null, 405);
        }
        if (!$this->web_guard->validate_csrf()) {
            return $this->json(false, 'Token keamanan form tidak valid.', null, 403);
        }

        $id_siswa = $this->integer_post('id_siswa');
        $id_rekening = $this->integer_post('id_rekening_dompet');
        $nominal = $this->integer_post('nominal_saldo');
        if ($id_siswa <= 0 || $id_rekening <= 0 || $nominal <= 0) {
            return $this->json(false, 'Siswa, rekening, dan nominal isi saldo wajib diisi dengan benar.', null, 422);
        }

        $hasil = $this->model->buat_pengajuan($this->user, $id_siswa, $id_rekening, $nominal);
        return $this->json(
            (bool) $hasil['status'],
            $hasil['message'],
            isset($hasil['data']) ? $hasil['data'] : null,
            $hasil['status'] ? 201 : 422
        );
    }

    public function simpan_limit()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            return $this->json(false, 'Metode HTTP tidak diizinkan.', null, 405);
        }
        if (!$this->web_guard->validate_csrf()) {
            return $this->json(false, 'Token keamanan form tidak valid.', null, 403);
        }

        $id_siswa = $this->integer_post('id_siswa');
        $batas_harian = $this->integer_post('batas_harian');
        $batas_transaksi = $this->integer_post('batas_per_transaksi');
        if ($id_siswa <= 0) {
            return $this->json(false, 'Siswa belum dipilih.', null, 422);
        }

        $hasil = $this->model->ubah_limit($this->user, $id_siswa, $batas_harian, $batas_transaksi);
        return $this->json((bool) $hasil['status'], $hasil['message'], null, $hasil['status'] ? 200 : 422);
    }

    private function autentikasi_wali($json)
    {
        $session = $this->session->userdata('wali');
        $user = is_array($session) && !empty($session['id_user'])
            ? $this->model->user_wali((int) $session['id_user'])
            : null;

        if ($user) {
            return $user;
        }

        if ($json) {
            $this->json(false, 'Sesi Wali Murid tidak ditemukan atau sudah berakhir.', null, 401);
            $this->output->_display();
            exit;
        }

        $this->session->set_flashdata('message', '<div class="alert alert-danger">Silakan masuk menggunakan akun Wali Murid.</div>');
        redirect('/');
        exit;
    }

    private function pilih_anak($anak_list)
    {
        if (empty($anak_list)) {
            return null;
        }
        $dipilih = $this->input->get('id_siswa', true);
        if ($dipilih !== null && filter_var($dipilih, FILTER_VALIDATE_INT) !== false) {
            foreach ($anak_list as $anak) {
                if ((int) $anak['id_siswa'] === (int) $dipilih) {
                    return $anak;
                }
            }
        }
        return $anak_list[0];
    }

    private function nomor_halaman()
    {
        $halaman = $this->input->get('halaman', true);
        return filter_var($halaman, FILTER_VALIDATE_INT) !== false && (int) $halaman > 0
            ? (int) $halaman : 1;
    }

    private function filter_tanggal()
    {
        $awal = trim((string) $this->input->get('tanggal_awal', true));
        $akhir = trim((string) $this->input->get('tanggal_akhir', true));
        $valid = true;
        $pesan = null;

        if (($awal !== '' && !$this->tanggal_valid($awal))
            || ($akhir !== '' && !$this->tanggal_valid($akhir))) {
            $valid = false;
            $pesan = 'Format tanggal filter tidak valid.';
        } elseif ($awal !== '' && $akhir !== '' && $awal > $akhir) {
            $valid = false;
            $pesan = 'Tanggal awal tidak boleh melewati tanggal akhir.';
        }

        return array(
            'tanggal_awal' => $awal === '' ? null : $awal,
            'tanggal_akhir' => $akhir === '' ? null : $akhir,
            'valid' => $valid,
            'pesan' => $pesan,
        );
    }

    private function tanggal_valid($nilai)
    {
        $tanggal = DateTime::createFromFormat('Y-m-d', $nilai);
        return $tanggal && $tanggal->format('Y-m-d') === $nilai;
    }

    private function integer_post($nama)
    {
        $nilai = $this->input->post($nama, true);
        if (is_string($nilai)) {
            $nilai = preg_replace('/[^0-9]/', '', $nilai);
        }
        return filter_var($nilai, FILTER_VALIDATE_INT) !== false ? (int) $nilai : 0;
    }

    private function halaman($view, $data)
    {
        $this->load->view('template/header', $data);
        $this->load->view($view, $data);
        $this->load->view('template/footer');
    }

    private function json($success, $message, $data, $status)
    {
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array(
                'success' => (bool) $success,
                'message' => (string) $message,
                'data' => $data,
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
