<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Qr_siswa extends CI_Controller
{
    const MENU_ID = 78;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_qr_siswa', 'model');
        $this->load->library('qr_code_svg');
        $this->require_login();
    }

    public function index()
    {
        $classes = $this->db
            ->select('ks.id,ks.nama_kelas')
            ->from('kelas_setting ks')
            ->join(
                'master_tahun_ajaran ta',
                "ta.id=ks.id_periode AND ta.status='Aktif'",
                'inner'
            )
            ->order_by('ks.nama_kelas')
            ->get()
            ->result_array();

        $data = [
            'title' => 'QR Code Siswa',
            'csrf_token' => '',
            'kelas' => $classes,
        ];

        $this->load->view('template/header', $data);
        $this->load->view('admin/dompet/qr_siswa', $data);
        $this->load->view('template/footer');
    }

    public function result()
    {
        $this->post_only();

        $result = $this->model->result([
            'search' => trim((string) $this->input->post('search', true)),
            'kelas' => (int) $this->input->post('kelas'),
            'page' => max(1, (int) $this->input->post('page')),
            'limit' => min(100, max(10, (int) $this->input->post('limit'))),
        ]);

        return $this->json(
            true,
            'QR siswa berhasil dimuat.',
            $result['rows'],
            200,
            [],
            $result['meta']
        );
    }

    public function regenerate()
    {
        $this->post_only();

        $result = $this->model->regenerate(
            (int) $this->input->post('id_siswa'),
            $this->current_user()
        );

        return $this->json(
            !empty($result['success']),
            !empty($result['success'])
                ? 'QR baru berhasil dibuat.'
                : $result['message'],
            $result['data'] ?? null,
            $result['status'] ?? 200
        );
    }

    public function cetak()
    {
        $rows = $this->card_rows(
            (int) $this->input->get('id'),
            (int) $this->input->get('kelas')
        );

        $this->load->view('admin/dompet/cetak_qr_siswa', [
            'rows' => $rows,
            'front_image' => base_url('assets/depan.jpg'),
            'back_image' => base_url('assets/belakang.jpg'),
            'pdf_mode' => false,
        ]);
    }

    public function download()
    {
        $id = (int) $this->input->get('id');
        if ($id <= 0) {
            show_404();
        }

        $rows = $this->model->print_rows($id, 0);
        if (!$rows) {
            show_404();
        }

        $png = $this->card_png($rows[0]);
        $filename = preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '-',
            $rows[0]['nis'] . '-' . $rows[0]['nama_lengkap']
        );

        $this->output
            ->set_content_type('image/png')
            ->set_header(
                'Content-Disposition: inline; filename="kartu-dompet-' . $filename . '.png"'
            )
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_output($png);
    }

    private function card_png($row)
    {
        $front = imagecreatefromjpeg(FCPATH . 'assets/depan.jpg');
        $back = imagecreatefromjpeg(FCPATH . 'assets/belakang.jpg');

        if (!$front || !$back) {
            show_error('Template kartu tidak dapat dibuka.', 500);
        }

        $width = imagesx($front);
        $height = imagesy($front);
        $canvas = imagecreatetruecolor($width * 2, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $front, 0, 0, 0, 0, $width, $height);
        imagecopyresampled(
            $canvas,
            $back,
            $width,
            0,
            0,
            0,
            imagesx($back),
            imagesy($back),
            imagesx($back),
            imagesy($back)
        );

        $this->draw_card_name($canvas, (string) $row['nama_lengkap']);

        $qr_data = $this->qr_code_svg->render_png($row['kode_tampil'], 22, 1);
        $qr = imagecreatefromstring($qr_data);
        imagecopyresampled(
            $canvas,
            $qr,
            292,
            632,
            0,
            0,
            458,
            458,
            imagesx($qr),
            imagesy($qr)
        );

        ob_start();
        imagepng($canvas, null, 9);
        $png = ob_get_clean();

        imagedestroy($qr);
        imagedestroy($front);
        imagedestroy($back);
        imagedestroy($canvas);

        return $png;
    }

    private function draw_card_name($image, $name)
    {
        $font = APPPATH
            . 'libraries/dompdf/vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf';
        $color = imagecolorallocate($image, 36, 84, 138);
        $size = 31;

        do {
            $box = imagettfbbox($size, 0, $font, $name);
            $width = abs($box[2] - $box[0]);
            if ($width <= 385) {
                break;
            }
            $size--;
        } while ($size >= 18);

        imagettftext($image, $size, 0, 98, 393, $color, $font, $name);
    }

    private function card_rows($id, $class_id)
    {
        $rows = $this->model->print_rows($id, $class_id);
        foreach ($rows as &$row) {
            $row['qr_svg'] = $this->qr_code_svg->render($row['kode_tampil']);
        }
        unset($row);

        return $rows;
    }

    private function current_user()
    {
        $session = $this->session->userdata('admin');

        return [
            'id' => (int) ($session['id_user'] ?? 0),
            'nama_user' => (string) ($session['nama_lengkap'] ?? $session['username'] ?? ''),
            'id_level' => (int) ($session['id_level'] ?? 0),
            'level' => (string) ($session['level'] ?? ''),
        ];
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
            ->set_status_header($status)
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
