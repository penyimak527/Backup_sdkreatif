<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pindah_kelas extends CI_Controller
{
    const MENU_ID = 27;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/kurikulum/M_pindah_kelas', 'model');
        $this->load->library('web_guard');
        $this->web_guard->enforce_login($this->router->fetch_method() !== 'index');
    }

    public function index()
    {
        $data = [
            'title' => 'Pindah Kelas',
            'kelas_setting' => $this->model->class_settings(),
            'tahun_lulus' => (int) date('Y'),
            'csrf_token' => $this->web_guard->csrf_token(),
        ];
        $this->load->view('template/header', $data);
        $this->load->view('admin/kurikulum/pindah_kelas', $data);
        $this->load->view('template/footer');
    }

    public function pindah_kelas_result()
    {
        $this->require_post();
        $this->enforce_configured_action('view');
        $source = (int) $this->input->post('id_kelas_sumber');
        $target = trim((string) $this->input->post('tujuan', true));
        if ($source < 1) {
            return $this->json(false, 'Pilih kelas sumber dari Kelas Setting.', null, 422);
        }
        $result = $this->model->preview($source, $target, (int) date('Y'));
        return $this->json(!empty($result['success']), $result['message'] ?? 'Data kelas berhasil dimuat.', $result['data'] ?? null, $result['status'] ?? 200);
    }

    public function proses()
    {
        $this->require_post();
        $this->enforce_configured_action('class_assign');
        $this->web_guard->enforce_csrf();
        $source = (int) $this->input->post('id_kelas_sumber');
        $target = trim((string) $this->input->post('tujuan', true));
        $ids = $this->input->post('id_siswa');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids)))) : [];
        if ($source < 1 || !$ids) {
            return $this->json(false, 'Kelas sumber dan siswa yang dipindahkan wajib dipilih.', null, 422);
        }
        if ($target === 'LULUS_'.date('Y')) {
            $result = $this->model->graduate($source, $ids, (int) date('Y'), $this->web_guard->current_user());
            $message = count($ids).' siswa berhasil diluluskan pada tahun '.date('Y').'.';
        } else {
            $result = $this->model->move($source, (int) $target, $ids);
            $message = count($ids).' siswa berhasil dipindahkan ke kelas tujuan.';
        }
        return $this->json(!empty($result['success']), !empty($result['success']) ? $message : ($result['message'] ?? 'Proses gagal.'), $result['data'] ?? null, $result['status'] ?? 200);
    }

    public function pindah_ke_kanan()
    {
        return $this->proses();
    }

    private function require_post()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            $this->json(false, 'Metode request tidak diizinkan.', null, 405);
            $this->output->_display();
            exit;
        }
    }

    private function enforce_configured_action($action)
    {
        if (!$this->db->table_exists('conf_menu_action')) {
            return;
        }

        $hasActionConfiguration = $this->db
            ->where('id_menu', self::MENU_ID)
            ->where('status_aktif', 'AKTIF')
            ->count_all_results('conf_menu_action') > 0;

        if ($hasActionConfiguration) {
        }
    }

    private function json($success, $message, $data = null, $status = 200)
    {
        return $this->web_guard->respond_json($success, $message, $data, $status);
    }
}
