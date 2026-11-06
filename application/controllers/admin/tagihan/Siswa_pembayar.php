<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Siswa_pembayar extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $admin = $this->session->userdata('admin');
        if (!is_array($admin) || empty($admin['username'])) {
            redirect('/');
            exit;
        }
        $multi_level_session = $admin;
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
                ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $level = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (empty($level) || !$this->db->where_in('id_level', $level)->where('path', 'admin/tagihan/siswa_pembayar')->count_all_results('conf_list_menu')) {
            show_error('Menu tidak diizinkan untuk level pengguna ini.', 403);
        }
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('admin/tagihan/M_siswa_pembayar', 'model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Pembebasan Biaya',
            'tagihan' => $this->model->tagihan_list(),
            'id_tagihan' => (int) $this->input->get('id_tagihan')
        );
        $this->load->view('template/header', $data);
        $this->load->view('admin/tagihan/siswa_pembayar', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->json_response($this->model->result());
    }

    public function tambah()
    {
        $this->json_response($this->model->tambah());
    }

    public function keluarkan()
    {
        $this->json_response($this->model->keluarkan());
    }

    public function export()
    {
        $this->load_phpspreadsheet();

        $id = (int) $this->input->get('id_tagihan');
        $kelas = (int) $this->input->get('id_kelas_setting');
        $search = trim((string) $this->input->get('search', true));
        $data = $this->model->export_rows($id, $kelas, $search);

        if (!$data['master']) {
            show_404();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Siswa Pembayar');

        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'DAFTAR SISWA PEMBAYAR');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Tagihan');
        $sheet->setCellValue('B3', $data['master']['nama_tagihan']);
        $sheet->setCellValue('A4', 'Tahun Ajaran');
        $sheet->setCellValue('B4', $data['master']['periode']);
        $sheet->setCellValue('A5', 'Kelas');
        $sheet->setCellValue('B5', $data['kelas']);
        $sheet->setCellValue('A6', 'Pencarian');
        $sheet->setCellValue('B6', $search !== '' ? $search : '-');
        $sheet->setCellValue('A7', 'Tanggal Ekspor');
        $sheet->setCellValue('B7', date('d-m-Y H:i:s'));
        $sheet->getStyle('A3:A7')->getFont()->setBold(true);

        $headerRow = 9;
        $headers = array(
            'A' => 'No',
            'B' => 'NIS',
            'C' => 'NISN',
            'D' => 'Nama Siswa',
            'E' => 'Kelas',
            'F' => 'Tarif',
            'G' => 'Dibayar',
            'H' => 'Sisa',
            'I' => 'Status Pembayaran',
            'J' => 'Status Penerima'
        );
        foreach ($headers as $column => $label) {
            $sheet->setCellValue($column . $headerRow, $label);
        }
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFont()->setBold(true);

        $rowNumber = $headerRow + 1;
        foreach ($data['rows'] as $index => $row) {
            $sheet->setCellValue('A' . $rowNumber, $index + 1);
            $sheet->setCellValueExplicit('B' . $rowNumber, (string) $row['nis'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNumber, (string) $row['nisn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowNumber, $row['nama_siswa']);
            $sheet->setCellValue('E' . $rowNumber, $row['nama_kelas']);
            $sheet->setCellValue('F' . $rowNumber, (float) $row['tarif']);
            $sheet->setCellValue('G' . $rowNumber, (float) $row['dibayar']);
            $sheet->setCellValue('H' . $rowNumber, (float) $row['sisa']);
            $sheet->setCellValue('I' . $rowNumber, $row['status_pembayaran']);
            $sheet->setCellValue('J' . $rowNumber, $row['status_penerima']);
            $rowNumber++;
        }

        if ($rowNumber > $headerRow + 1) {
            $sheet->getStyle('F' . ($headerRow + 1) . ':H' . ($rowNumber - 1))
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        foreach (
            array(
                'A' => 6,
                'B' => 16,
                'C' => 18,
                'D' => 28,
                'E' => 18,
                'F' => 16,
                'G' => 16,
                'H' => 16,
                'I' => 20,
                'J' => 20
            ) as $column => $width
        ) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A10');

        $filename = 'siswa_pembayar_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $data['master']['kode_tagihan']) . '_' . date('Ymd_His') . '.xlsx';
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }

    private function load_phpspreadsheet()
    {
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            return;
        }

        $autoload = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }

        if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            show_error('Library PhpSpreadsheet belum tersedia.', 500);
        }
    }

    private function json_response($data, $status = 200)
    {
        $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->_display();
        exit;
    }
}
