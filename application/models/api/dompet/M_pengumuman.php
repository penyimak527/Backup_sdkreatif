<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_pengumuman extends CI_Model
{
    private $jumlah_data_per_halaman;
    private $jumlah_data_maksimal;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/dompet/M_auth', 'auth');
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

    public function daftar()
    {
        $otorisasi = $this->auth->otorisasi();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $pagination = $this->pagination();
        if (!$pagination['status']) {
            return $pagination['response'];
        }

        $halaman = $pagination['halaman'];
        $per_halaman = $pagination['per_halaman'];
        $offset = ($halaman - 1) * $per_halaman;
        $scope = $this->scope_target($otorisasi['data']['mode_aplikasi']);
        $tanggal = date('Y-m-d');
        $filter = $this->filter_tayang(count($scope));
        $params = array_merge($scope, array($tanggal, $tanggal));

        $total = $this->db
            ->query(
                'SELECT COUNT(*) AS jumlah FROM pengumuman_dompet p WHERE '.$filter,
                $params
            )
            ->row_array();
        $total_data = $total ? (int) $total['jumlah'] : 0;

        $sql = "SELECT p.id, p.judul, p.isi, p.target_pengguna, p.lampiran,
                    p.tanggal_mulai, p.tanggal_selesai, p.tanggal, p.waktu
                FROM pengumuman_dompet p
                WHERE ".$filter."
                ORDER BY
                    COALESCE(NULLIF(TRIM(p.tanggal_mulai), ''), NULLIF(TRIM(p.tanggal), ''), '0000-00-00') DESC,
                    COALESCE(NULLIF(TRIM(p.waktu), ''), '00:00:00') DESC,
                    p.id DESC
                LIMIT ? OFFSET ?";
        $params[] = $per_halaman;
        $params[] = $offset;
        $rows = $this->db->query($sql, $params)->result_array();

        $data = array();
        foreach ($rows as $row) {
            $data[] = $this->format_daftar($row);
        }

        return $this->berhasil_dengan_meta(
            empty($data) ? 'Belum ada pengumuman.' : 'Daftar pengumuman berhasil dimuat.',
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

    public function detail($id_pengumuman)
    {
        if (!$this->id_valid($id_pengumuman)) {
            return $this->gagal('ID pengumuman belum valid.', 422, array(
                'id_pengumuman' => 'ID pengumuman harus berupa bilangan bulat positif.',
            ));
        }

        $otorisasi = $this->auth->otorisasi();
        if (!$otorisasi['status']) {
            return $otorisasi['response'];
        }

        $scope = $this->scope_target($otorisasi['data']['mode_aplikasi']);
        $tanggal = date('Y-m-d');
        $filter = $this->filter_tayang(count($scope));
        $params = array_merge(array((int) $id_pengumuman), $scope, array($tanggal, $tanggal));

        $sql = "SELECT p.id, p.judul, p.isi, p.target_pengguna, p.lampiran,
                    p.tanggal_mulai, p.tanggal_selesai, p.tanggal, p.waktu
                FROM pengumuman_dompet p
                WHERE p.id = ? AND ".$filter."
                LIMIT 1";
        $row = $this->db->query($sql, $params)->row_array();

        if (!$row) {
            return $this->gagal('Pengumuman tidak ditemukan.', 404);
        }

        return $this->berhasil('Detail pengumuman berhasil dimuat.', $this->format_detail($row));
    }

    public function metode_tidak_diizinkan()
    {
        return $this->gagal('Metode HTTP tidak diizinkan.', 405);
    }

    private function filter_tayang($jumlah_target)
    {
        $placeholder = implode(',', array_fill(0, $jumlah_target, '?'));

        return "UPPER(TRIM(p.status_aktif)) = 'AKTIF'
            AND UPPER(TRIM(p.target_pengguna)) IN (".$placeholder.")
            AND (p.tanggal_mulai IS NULL OR TRIM(p.tanggal_mulai) = '' OR p.tanggal_mulai <= ?)
            AND (p.tanggal_selesai IS NULL OR TRIM(p.tanggal_selesai) = '' OR p.tanggal_selesai >= ?)";
    }

    private function scope_target($mode)
    {
        if ($mode === 'WALI') {
            return array('SEMUA', 'WALI', 'WALI MURID', 'WALI_MURID');
        }

        return array('SEMUA', 'PETUGAS', 'PETUGAS KANTIN', 'PETUGAS_KANTIN');
    }

    private function format_daftar($row)
    {
        return array(
            'id_pengumuman' => (int) $row['id'],
            'judul' => (string) $row['judul'],
            'ringkasan' => $this->ringkasan($row['isi']),
            'target_pengguna' => (string) $row['target_pengguna'],
            'lampiran' => $this->nilai_nullable($row['lampiran']),
            'tanggal_mulai' => $this->nilai_nullable($row['tanggal_mulai']),
            'tanggal_selesai' => $this->nilai_nullable($row['tanggal_selesai']),
            'tanggal' => $this->nilai_nullable($row['tanggal']),
            'waktu' => $this->nilai_nullable($row['waktu']),
        );
    }

    private function format_detail($row)
    {
        $data = $this->format_daftar($row);
        $data['isi'] = $this->teks_bersih($row['isi']);
        return $data;
    }

    private function ringkasan($isi)
    {
        $teks = $this->teks_bersih($isi);
        if (function_exists('mb_strlen') && mb_strlen($teks, 'UTF-8') > 240) {
            return rtrim(mb_substr($teks, 0, 237, 'UTF-8')).'...';
        }
        if (!function_exists('mb_strlen') && strlen($teks) > 240) {
            return rtrim(substr($teks, 0, 237)).'...';
        }
        return $teks;
    }

    private function teks_bersih($isi)
    {
        $html = preg_replace('/<\s*br\s*\/?\s*>/i', ' ', (string) $isi);
        $html = preg_replace('/<\/\s*(p|div|li|h[1-6])\s*>/i', ' ', $html);
        $teks = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $teks = preg_replace('/\s+/', ' ', $teks);
        return trim($teks === null ? '' : $teks);
    }

    private function nilai_nullable($nilai)
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }
        return (string) $nilai;
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
