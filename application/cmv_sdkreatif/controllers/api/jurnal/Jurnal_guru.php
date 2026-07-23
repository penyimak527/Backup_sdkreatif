<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jurnal_guru extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('api/jurnal/M_jurnal_guru', 'model');
        // $this->load->helper('url');
        // $this->load->library('input');
        $this->output->set_content_type('application/json');
    }

    // Contoh endpoint untuk ambil semua jurnal siswa
    public function jurnal_siswa_result()
    {
        $data = $this->model->jurnal_siswa_result();
        echo json_encode([
            'status' => true,
            'data' => $data,
        ]);
    }

    // Contoh endpoint untuk jadwal mengajar
    public function jadwal_mengajar_result()
    {
        $data = $this->model->jadwal_mengajar_result();
        echo json_encode([
            'status' => true,
            'data' => $data,
        ]);
    }

    // Contoh endpoint untuk jadwal mengajar guru, bisa dapat param via POST atau GET jika perlu
    public function jadwal_mengajar_result_guru()
    {
        $data = $this->model->jadwal_mengajar_result_guru();
        echo json_encode([
            'status' => true,
            'data' => $data,
        ]);
    }

    // Riwayat mengajar
    public function riwayat_mengajar_result()
    {
        $data = $this->model->riwayat_mengajar_result();
        echo json_encode([
            'status' => true,
            'data' => $data,
        ]);
    }

    // Kelas jadwal pelajaran
    public function kelas_jadwal_pelajaran_result()
    {
        $data = $this->model->kelas_jadwal_pelajaran_result();
        echo json_encode([
            'status' => true,
            'data' => $data,
        ]);
    }

    // Jurnal guru result
    public function jurnal_guru_result()
    {
        $data = $this->model->jurnal_guru_result();
        echo json_encode([
            'status' => true,
            'data' => $data,
        ]);
    }

    // Tambah data (POST)
    public function tambah()
    {
        $data = $this->model->tambah();
        echo json_encode([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $data,
        ]);
    }

    // Edit data (POST)
    public function edit()
    {
        $data = $this->model->edit();
        echo json_encode([
            'status' => true,
            'message' => 'Data berhasil diubah',
            'data' => $data,
        ]);
    }

    // Hapus data (POST)
    public function hapus()
    {
        $data = $this->model->hapus();
        echo json_encode([
            'status' => true,
            'message' => 'Data berhasil dihapus',
            'data' => $data,
        ]);
    }

    // Jika perlu method tambahan yang menerima parameter
    public function jurnal_mengajar($id_jadwal = null, $tanggal = null)
    {
        // Ubah tanggal ke format Y-m-d bila ada
        if ($tanggal !== null) {
            $tanggal = date('Y-m-d', strtotime($tanggal));
        }

        // Panggil model dengan parameter (ubah sesuai kebutuhan)
        $data = $this->model->get_jurnal_mengajar($id_jadwal, $tanggal);

        echo json_encode([
            'status' => true,
            'id_jadwal' => $id_jadwal,
            'tanggal' => $tanggal,
            'data' => $data,
        ]);
    }

    public function get_kelas()
    {
        $data = $this->model->get_kelas();

        if (!empty($data)) {
            echo json_encode([
                'status' => true,
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
}
?>
