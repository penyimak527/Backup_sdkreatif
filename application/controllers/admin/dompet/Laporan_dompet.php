<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan_dompet extends CI_Controller
{
    const MENU_ID = 82;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_laporan_dompet', 'model');
        $this->load->library('dompet_report_xlsx');
        $this->require_login();
    }

    public function index()
    {
        $data = [
            'title' => 'Laporan Dompet Digital',
            'report_types' => $this->report_types(),
            'kelas' => $this->db
                ->select('ks.id, ks.nama_kelas, ks.periode')
                ->from('kelas_setting ks')
                ->order_by('ks.periode', 'DESC')
                ->order_by('ks.nama_kelas')
                ->get()
                ->result_array(),
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/laporan/index', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->post_only();

        $type = trim((string) $this->input->post('jenis', true));
        if (!$this->is_valid_type($type)) {
            return $this->json(false, 'Jenis laporan tidak valid.', null, 422);
        }

        $result = $this->model->report(
            $type,
            $this->filters(false),
            max(1, (int) $this->input->post('page')),
            min(200, max(10, (int) $this->input->post('limit')))
        );

        return $this->json(
            true,
            'Laporan berhasil dimuat.',
            $result['rows'],
            200,
            [],
            array_merge($result['meta'], [
                'totals' => $result['totals'],
                'columns' => $result['columns'],
            ])
        );
    }

    public function cetak()
    {
        $type = trim((string) $this->input->get('jenis', true));
        if (!$this->is_valid_type($type)) {
            show_error('Jenis laporan tidak valid.', 422);
        }

        $filters = $this->filters(true);
        $result = $this->model->report($type, $filters, 1, 1000000);
        $session = $this->session->userdata('admin');

        $data = [
            'title' => $this->report_types()[$type],
            'columns' => $result['columns'],
            'rows' => $result['rows'],
            'totals' => $result['totals'],
            'filter_summary' => $this->filter_summary($filters),
            'printed_at' => date('d-m-Y H:i:s'),
            'printed_by' => (string) (
                $session['nama_lengkap']
                ?? $session['username']
                ?? '-'
            ),
        ];

        $this->load->view('admin/dompet/laporan/print', $data);
    }

    public function export()
    {
        $type = trim((string) $this->input->get('jenis', true));
        if (!$this->is_valid_type($type)) {
            show_error('Jenis laporan tidak valid.', 422);
        }

        $filters = $this->filters(true);
        $result = $this->model->report($type, $filters, 1, 1000000);
        $headers = array_merge(['No.'], array_values($result['columns']));
        $rows = [];

        foreach ($result['rows'] as $index => $row) {
            $rows[] = [
                $index + 1,
                $row['label_1'] ?? '',
                $row['label_2'] ?? '',
                $row['label_3'] ?? '',
                (float) ($row['nilai_1'] ?? 0),
                (float) ($row['nilai_2'] ?? 0),
                (float) ($row['nilai_3'] ?? 0),
                $this->readable_status($row['status'] ?? ''),
                $row['tanggal'] ?? '',
            ];
        }

        $path = $this->dompet_report_xlsx->create(
            $this->report_types()[$type],
            $headers,
            $rows,
            [
                (float) ($result['totals']['nilai_1'] ?? 0),
                (float) ($result['totals']['nilai_2'] ?? 0),
                (float) ($result['totals']['nilai_3'] ?? 0),
            ],
            $this->filter_summary($filters),
            $this->excel_sheet_name($type)
        );

        $filename = 'laporan-dompet-'
            .$type
            .'-'
            .date('Ymd-His')
            .'.xlsx';

        return $this->send_xlsx($path, $filename);
    }

    private function report_types()
    {
        return [
            'saldo' => 'Saldo Siswa',
            'isi_saldo' => 'Riwayat Isi Saldo',
            'transaksi_kantin' => 'Riwayat Transaksi Kantin',
            'biaya_layanan' => 'Biaya Admin',
        ];
    }

    private function is_valid_type($type)
    {
        return array_key_exists($type, $this->report_types());
    }

    private function filters($use_get)
    {
        $read = function ($key) use ($use_get) {
            $value = $use_get
                ? $this->input->get($key, true)
                : $this->input->post($key, true);

            return trim((string) $value);
        };

        $date = function ($value) {
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
        };

        return [
            'search' => $read('search'),
            'status' => strtoupper($read('status')),
            'tanggal_awal' => $date($read('tanggal_awal')),
            'tanggal_akhir' => $date($read('tanggal_akhir')),
            'id_kelas_setting' => (int) $read('id_kelas_setting'),
        ];
    }

    private function filter_summary($filters)
    {
        $summary = [];

        if ($filters['search'] !== '') {
            $summary[] = 'Pencarian: '.$filters['search'];
        }

        if ($filters['status'] !== '') {
            $summary[] = 'Status: '.$this->readable_status($filters['status']);
        }

        if ($filters['tanggal_awal'] !== '' || $filters['tanggal_akhir'] !== '') {
            $summary[] = 'Periode: '
                .($filters['tanggal_awal'] ?: 'awal')
                .' s.d. '
                .($filters['tanggal_akhir'] ?: 'sekarang');
        }

        if ($filters['id_kelas_setting'] > 0) {
            $class = $this->db
                ->select('nama_kelas, periode')
                ->where('id', $filters['id_kelas_setting'])
                ->get('kelas_setting')
                ->row_array();

            if ($class) {
                $summary[] = 'Kelas: '
                    .$class['nama_kelas']
                    .' - '
                    .$class['periode'];
            }
        }

        return $summary ?: ['Semua data'];
    }

    private function readable_status($status)
    {
        $status = str_replace('_', ' ', strtolower(trim((string) $status)));
        return $status === '' ? '-' : ucwords($status);
    }

    private function excel_sheet_name($type)
    {
        $names = [
            'saldo' => 'Saldo Siswa',
            'isi_saldo' => 'Isi Saldo',
            'transaksi_kantin' => 'Transaksi Kantin',
            'biaya_layanan' => 'Biaya Admin',
        ];

        return $names[$type];
    }

    private function send_xlsx($path, $filename)
    {
        $contents = file_get_contents($path);
        @unlink($path);

        return $this->output
            ->set_content_type(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            )
            ->set_header('Content-Disposition: attachment; filename="'.$filename.'"')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_output($contents);
    }

    private function post_only()
    {
        if ($this->input->method(true) === 'POST') {
            return;
        }

        $this->json(false, 'Metode request tidak diizinkan.', null, 405);
        $this->output->_display();
        exit;
    }

    private function require_login()
    {
        $session = $this->session->userdata('admin');
        if (is_array($session) && !empty($session['id_user'])) {
            return;
        }

        if ($this->router->fetch_method() === 'index') {
            redirect('/');
            exit;
        }

        $this->json(false, 'Sesi login tidak ditemukan.', null, 401);
        $this->output->_display();
        exit;
    }

    private function json(
        $success,
        $message,
        $data = null,
        $status = 200,
        $errors = [],
        $meta = []
    ) {
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' => (bool) $success,
                'message' => (string) $message,
                'data' => $data,
                'errors' => $errors,
                'meta' => $meta,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
