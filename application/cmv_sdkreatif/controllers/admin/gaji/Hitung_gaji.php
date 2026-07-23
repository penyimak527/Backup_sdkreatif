<?php
class Hitung_gaji extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('admin/gaji/M_hitung_gaji', 'model');

    }

    public function index()
    {
        if ($this->session->userdata('admin')['username'] == null) {
            redirect('/');
        }

        $data['title'] = 'Hitung Gaji';
        $this->load->view('template/header', $data);
        $this->load->view('admin/gaji/hitung_gaji', $data);
        $this->load->view('template/footer');
    }

    public function hitung_gaji_result()
    {
        $search = $this->input->post('search');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');
        $data = $this->model->hitung_gaji_result($search, $bulan, $tahun);
        echo json_encode($data);
    }
    public function pegawai_result()
    {
        $data = $this->model->pegawai_result();

        echo json_encode($data);
    }
    public function tambah()
    {
        $data = $this->model->tambah();

        echo json_encode($data);
    }
    public function edit()
    {
        $data = $this->model->edit();

        echo json_encode($data);
    }

    public function hitung()
    {
        $data = $this->model->hitung();
        echo json_encode($data);
    }
    public function hitung_semua()
    {
        $data = $this->model->hitung_semua();
        echo json_encode($data);
    }
    	public function edit_potongan_gaji()
	{
		$data = $this->model->edit_potongan_gaji();

		echo json_encode($data);
	}
		public function get_potongan_gaji()
	{
		$data = $this->model->get_potongan_gaji();

		echo json_encode($data);
	}
    // public function cetak_semua_slip_gaji($bulan, $tahun)
    // {
        // if ($this->session->userdata('admin')['username'] == null) {
        //     redirect('/');
        // }
    //     $data['title'] = 'Print Slip Gaji';
    //     $data['slip_gaji'] = $this->model->penggajian_result(null, $bulan, $tahun);
    //     $this->load->view('admin/pegawai/view/cetak_slip_gaji/cetak_slip_gaji', $data);
    // }

}
?>