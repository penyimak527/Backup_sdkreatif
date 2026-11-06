<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Siswa extends CI_Controller
{
    const MENU_ID = 10;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/master/M_siswa', 'model');
        $this->load->library(['web_guard', 'student_xlsx']);

        $method = strtolower((string) $this->router->fetch_method());
        $html_methods = ['index', 'alumni', 'export_alumni', 'export', 'contoh_import'];
        $this->web_guard->enforce_login(!in_array($method, $html_methods, true));
    }

    public function index()
    {
        $data = [
            'title' => 'Master Siswa',
            'csrf_token' => $this->web_guard->csrf_token(),
            'periode' => $this->model->periode_options(),
            'kelas' => $this->model->kelas_options(),
            'kelas_master' => $this->model->master_class_options(),
            'wali_options' => $this->model->guardian_options(),
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/master/siswa', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->require_post();

        $result = $this->model->result([
            'search' => trim((string) $this->input->post('search', true)),
            'status' => trim((string) $this->input->post('status', true)),
            'id_kelas_setting' => (int) $this->input->post('id_kelas_setting'),
            'id_periode' => (int) $this->input->post('id_periode'),
            'sumber_data' => trim((string) $this->input->post('sumber_data', true)),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ]);

        return $this->web_guard->respond_json(true, 'Data siswa berhasil dimuat.', $result['rows'], 200, [], $result['meta']);
    }

    public function alumni()
    {
        $data = ['title' => 'Alumni Siswa', 'tahun_lulus' => $this->model->alumni_years()];
        $this->load->view('template/header', $data);
        $this->load->view('admin/master/alumni_siswa', $data);
        $this->load->view('template/footer');
    }

    public function alumni_result()
    {
        $this->require_post();
        $filter = $this->alumni_filters(true);
        $result = $this->model->alumni_result($filter);
        return $this->web_guard->respond_json(true, 'Data alumni berhasil dimuat.', $result['rows'], 200, [], $result['meta']);
    }

    public function export_alumni()
    {
        $filter = $this->alumni_filters(false);
        $result = $this->model->alumni_result($filter, false);
        $rows = [];
        foreach ($result['rows'] as $row) {
            $rows[] = [$row['nis'], $row['nisn'], $row['nama_lengkap'], $row['jk'], $row['nama_kelas'], $row['periode'], $row['tahun_lulus'], $row['nama_wali'], $row['no_telp']];
        }
        $headers = ['NIS', 'NISN', 'Nama Siswa', 'Jenis Kelamin', 'Kelas Terakhir', 'Tahun Ajaran Terakhir', 'Tahun Lulus', 'Nama Wali', 'Telepon Wali'];
        $suffix = $filter['tahun_lulus'] !== '' ? '-'.$filter['tahun_lulus'] : '';
        return $this->send_xlsx($this->student_xlsx->create($headers, $rows, 'Data Alumni'), 'data-alumni'.$suffix.'-'.date('Y-m-d').'.xlsx');
    }

    public function detail()
    {
        $this->require_post();
        $id = (int) $this->input->post('id');
        $row = $this->model->detail($id);

        if (!$row) {
            return $this->web_guard->respond_json(false, 'Data siswa tidak ditemukan.', null, 404);
        }

        return $this->web_guard->respond_json(true, 'Detail siswa berhasil dimuat.', $row);
    }

    public function tambah()
    {
        // $this->require_mutation('create');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        $payload = $this->student_payload();
        $payload['sumber_data'] = 'WEB';
        $errors = $this->validate_student($payload);
        if ($errors) {
            return $this->web_guard->respond_json(false, 'Data siswa belum valid.', null, 422, $errors);
        }

        $idKelas = (int) $this->input->post('id_kelas');
        $classSetting = $this->model->active_class_setting($idKelas);
        if (!$classSetting) {
            return $this->web_guard->respond_json(false, 'Kelas belum tersedia pada Kelas Setting periode aktif.', null, 422, ['id_kelas' => 'Pilih kelas yang sudah mempunyai Kelas Setting aktif.']);
        }
        $result = $this->model->create($payload, $this->web_guard->current_user(), $this->guardian_ids(), (int) $classSetting['id']);
        return $this->model_response($result, 'Data siswa, kelas, dan dompet aktif berhasil ditambahkan.');
    }

    public function edit()
    {
        // $this->require_mutation('edit');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        $id = (int) $this->input->post('id');
        $payload = $this->student_payload();
        $errors = $this->validate_student($payload);
        if ($id < 1) {
            $errors['id'] = 'ID siswa tidak valid.';
        }
        if ($errors) {
            return $this->web_guard->respond_json(false, 'Data siswa belum valid.', null, 422, $errors);
        }

        $idKelas = (int) $this->input->post('id_kelas');
        $classSetting = $this->model->active_class_setting($idKelas);
        if (!$classSetting) {
            return $this->web_guard->respond_json(false, 'Kelas belum tersedia pada Kelas Setting periode aktif.', null, 422, ['id_kelas' => 'Pilih kelas yang sudah mempunyai Kelas Setting aktif.']);
        }
        $result = $this->model->update_student($id, $payload, $this->web_guard->current_user(), $this->guardian_ids(), (int) $classSetting['id']);
        return $this->model_response($result, 'Data siswa berhasil diperbarui.');
    }

    public function status()
    {
        // $this->require_mutation('status');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        $id = (int) $this->input->post('id');
        $status = strtoupper(trim((string) $this->input->post('status', true)));
        $alasan = trim((string) $this->input->post('alasan', true));
        $errors = [];

        if ($id < 1) {
            $errors['id'] = 'ID siswa tidak valid.';
        }
        if (!in_array($status, ['AKTIF', 'NONAKTIF', 'ALUMNI'], true)) {
            $errors['status'] = 'Status siswa tidak valid.';
        }
        if ($status !== 'AKTIF' && mb_strlen($alasan) < 5) {
            $errors['alasan'] = 'Alasan minimal 5 karakter.';
        }
        if ($errors) {
            return $this->web_guard->respond_json(false, 'Perubahan status belum valid.', null, 422, $errors);
        }

        $result = $this->model->change_status($id, $status, $alasan, $this->web_guard->current_user());
        return $this->model_response($result, 'Status siswa berhasil diperbarui.');
    }

    public function penempatan_kelas()
    {
        // $this->require_mutation('class_assign');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        $id_siswa = (int) $this->input->post('id_siswa');
        $id_kelas_setting = (int) $this->input->post('id_kelas_setting');
        if ($id_siswa < 1 || $id_kelas_setting < 1) {
            return $this->web_guard->respond_json(false, 'Siswa dan kelas wajib dipilih.', null, 422, [
                'id_siswa' => $id_siswa < 1 ? 'Siswa tidak valid.' : null,
                'id_kelas_setting' => $id_kelas_setting < 1 ? 'Kelas tidak valid.' : null,
            ]);
        }

        $result = $this->model->assign_class($id_siswa, $id_kelas_setting);
        return $this->model_response($result, 'Penempatan kelas berhasil diperbarui.');
    }

    public function pindah_kelas()
    {
        // $this->require_mutation('class_assign');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        $idSiswa = (int) $this->input->post('id_siswa');
        $mode = strtoupper(trim((string) $this->input->post('mode', true)));
        if ($idSiswa < 1) {
            return $this->web_guard->respond_json(false, 'Siswa tidak valid.', null, 422);
        }
        if ($mode === 'LULUS') {
            $year = (int) $this->input->post('tahun_lulus');
            $result = $this->model->graduate_student($idSiswa, $year, $this->web_guard->current_user());
            return $this->model_response($result, 'Siswa berhasil diluluskan dan dipindahkan ke data alumni.');
        }
        $idClass = (int) $this->input->post('id_kelas_setting');
        if ($idClass < 1) {
            return $this->web_guard->respond_json(false, 'Kelas tujuan wajib dipilih.', null, 422);
        }
        return $this->model_response($this->model->assign_class($idSiswa, $idClass), 'Siswa berhasil dipindahkan ke kelas tujuan.');
    }

    public function trash()
    {
        $this->require_post();
        return $this->web_guard->respond_json(true, 'Trash siswa berhasil dimuat.', $this->model->trash_rows());
    }

    public function hapus()
    {
        // $this->require_mutation('status');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        return $this->model_response($this->model->soft_delete((int) $this->input->post('id'), $this->web_guard->current_user()), 'Siswa dipindahkan ke Trash tanpa menghapus data terkait.');
    }

    public function restore()
    {
        // $this->require_mutation('status');
         $this->require_post();
    $this->web_guard->enforce_csrf();
        return $this->model_response($this->model->restore((int) $this->input->post('id'), $this->web_guard->current_user()), 'Siswa dipulihkan dan langsung diaktifkan.');
    }

    public function export()
    {
        $headers = $this->excel_headers(false);
        $rows = [];
        foreach ($this->model->export_rows() as $row) {
            $rows[] = [
                $row['nis'], $row['nama_lengkap'], stripos((string) $row['jk'], 'perempuan') !== false ? 'P' : 'L',
                $row['tempat_lahir'], $row['tanggal_lahir'], $row['nama_kelas'], $row['username'], $row['nama_wali'],
            ];
        }
        return $this->send_xlsx($this->student_xlsx->create($headers, $rows), 'data-siswa-'.date('Y-m-d').'.xlsx');
    }

    public function contoh_import()
    {
        // $this->web_guard->enforce_action(self::MENU_ID, 'create', false);

        $rows = [
            ['1234', 'Muhammad Zainal Abidin', 'L', 'Bandung', '27 Juli 2016', '1B', '', '', 'Muhammad Walid Abidin'],
            ['5678', 'Fiona Citra', 'P', 'Jakarta', '5 Mei 2015', '1A', '', '', 'Ega Rodea Dimitri'],
        ];
        return $this->send_xlsx($this->student_xlsx->create($this->excel_headers(true), $rows, 'Contoh Import'), 'Contoh Import Data Siswa.xlsx');
    }

    public function import()
    {
        // $this->require_mutation('create');
        if (empty($_FILES['file_excel']['tmp_name']) || !is_uploaded_file($_FILES['file_excel']['tmp_name'])) {
            return $this->web_guard->respond_json(false, 'Pilih file Excel .xlsx terlebih dahulu.', null, 422);
        }
        $extension = strtolower(pathinfo((string) $_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'xlsx' || (int) $_FILES['file_excel']['size'] > 5 * 1024 * 1024) {
            return $this->web_guard->respond_json(false, 'File harus berformat .xlsx dan berukuran maksimal 5 MB.', null, 422);
        }
        try {
            $rows = $this->student_xlsx->read($_FILES['file_excel']['tmp_name']);
        } catch (Throwable $exception) {
            return $this->web_guard->respond_json(false, 'File Excel tidak dapat dibaca: '.$exception->getMessage(), null, 422);
        }
        if (!$rows) {
            return $this->web_guard->respond_json(false, 'File Excel tidak berisi data.', null, 422);
        }
        $normalizedHeaders = array_map([$this, 'normalize_header'], $rows[0]);
        $expected = array_map([$this, 'normalize_header'], $this->excel_headers(true));
        if ($normalizedHeaders !== $expected) {
            return $this->web_guard->respond_json(false, 'Susunan kolom tidak sesuai contoh impor. Unduh kembali file contoh yang tersedia.', null, 422);
        }
        array_shift($rows);
        if (count($rows) > 2000) {
            return $this->web_guard->respond_json(false, 'Maksimal 2.000 baris dalam satu proses impor.', null, 422);
        }
        $result = $this->model->import_students($rows, $this->web_guard->current_user());
        return $this->model_response($result, 'Impor data siswa selesai.');
    }

    private function require_post($action = null)
    {
        if ($action !== null) {
            $this->web_guard->enforce_action(self::MENU_ID, $action, true);
        }

        if (strtoupper((string) $this->input->method()) !== 'POST') {
            $this->web_guard->respond_json(false, 'Metode request tidak diizinkan.', null, 405);
            $this->output->_display();
            exit;
        }
    }

    private function require_mutation($action)
    {
        $this->require_post($action);
        $this->web_guard->enforce_csrf();
    }

    private function student_payload()
    {
        $fields = [
            'nis', 'nisn', 'nama_lengkap', 'jk', 'tempat_lahir', 'tanggal_lahir',
            'alamat_siswa',
        ];
        $payload = [];
        foreach ($fields as $field) {
            $payload[$field] = trim((string) $this->input->post($field, true));
        }
        return $payload;
    }

    private function guardian_ids()
    {
        $id = $this->input->post('id_wali_siswa');
        if (is_array($id)) {
            $id = reset($id);
        }
        $id = (int) $id;
        return $id > 0 ? [$id] : [];
    }

    private function alumni_filters($post)
    {
        $search = $post ? $this->input->post('search', true) : $this->input->get('search', true);
        $year = $post ? $this->input->post('tahun_lulus', true) : $this->input->get('tahun_lulus', true);
        $page = $post ? (int) $this->input->post('page') : 1;
        $limit = $post ? (int) $this->input->post('limit') : 100000;
        return ['search' => trim((string) $search), 'tahun_lulus' => preg_match('/^\d{4}$/', (string) $year) ? (string) $year : '', 'page' => max(1, $page), 'limit' => min(100000, max(10, $limit))];
    }

    private function validate_student($payload)
    {
        $errors = [];
        if (!preg_match('/^[A-Za-z0-9.\/-]{2,50}$/', $payload['nis'])) {
            $errors['nis'] = 'NIS wajib 2-50 karakter dan hanya boleh berisi huruf, angka, titik, garis miring, atau tanda hubung.';
        }
        if ($payload['nisn'] !== '' && !preg_match('/^[0-9]{5,50}$/', $payload['nisn'])) {
            $errors['nisn'] = 'NISN hanya boleh berisi 5-50 digit.';
        }
        if (mb_strlen($payload['nama_lengkap']) < 2 || mb_strlen($payload['nama_lengkap']) > 225) {
            $errors['nama_lengkap'] = 'Nama lengkap wajib 2-225 karakter.';
        }
        if (!in_array($payload['jk'], ['Laki-laki', 'Perempuan'], true)) {
            $errors['jk'] = 'Jenis kelamin wajib dipilih.';
        }
        if ($payload['tanggal_lahir'] !== '') {
            $date = DateTime::createFromFormat('Y-m-d', $payload['tanggal_lahir']);
            if (!$date || $date->format('Y-m-d') !== $payload['tanggal_lahir']) {
                $errors['tanggal_lahir'] = 'Tanggal lahir harus berformat YYYY-MM-DD.';
            }
        }
        return $errors;
    }

    private function model_response($result, $success_message)
    {
        if (!empty($result['success'])) {
            return $this->web_guard->respond_json(true, $success_message, $result['data'] ?? null);
        }
        return $this->web_guard->respond_json(
            false,
            $result['message'] ?? 'Proses tidak dapat diselesaikan.',
            null,
            $result['status'] ?? 422,
            $result['errors'] ?? []
        );
    }

    private function excel_headers($import)
    {
        $headers = [
            'NISN', 'Nama Siswa', 'Jenis Kelamin (L/P)', 'Tempat Lahir', 'Tanggal Lahir', 'Nama Kelas',
            $import ? 'Username Wali Murid (opsional)' : 'Username Wali Murid',
        ];

        if ($import) {
            $headers[] = 'Kata Sandi Wali Murid (opsional)';
        }

        $headers[] = 'Nama Wali Murid';

        return $headers;
    }

    public function normalize_header($value)
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    private function send_xlsx($path, $filename)
    {
        $contents = file_get_contents($path);
        @unlink($path);
        $this->output
            ->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->set_header('Content-Disposition: attachment; filename="'.$filename.'"')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_output($contents);
    }
}
