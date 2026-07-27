<?php
class Rencana_pemasukan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/perencanaan/M_rencana_pemasukan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Rencana Pemasukan';
		$data['kode_akun']  = $this->db->get_where('kode_akun', ['jenis' => 'Pemasukan'])->result_array();
		$data['tahun_ajaran']  = $this->db->get_where('master_tahun_ajaran')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/perencanaan/rencana_pemasukan', $data);
		$this->load->view('template/footer');
	}

	public function rencana_pemasukan_result()
	{
		$data = $this->model->rencana_pemasukan_result();

		echo json_encode($data);
	}
	public function detail()
	{
		$data = $this->model->detail();

		echo json_encode($data);
	}
	public function detail_edit()
	{
		$data = $this->model->detail_edit();

		echo json_encode($data);
	}
	public function tambah()
	{
		$data = $this->model->tambah();

		echo json_encode($data);
	}
	public function get_data_asumsi_pemasukan()
	{
		$data = $this->model->get_data_asumsi_pemasukan();

		echo json_encode($data);
	}
	public function simpan_asumsi_pemasukan()
	{
		$data = $this->model->simpan_asumsi_pemasukan();

		echo json_encode($data);
	}
	public function cek_rab_pemasukan()
	{
		$data = $this->model->cek_rab_pemasukan();

		echo json_encode($data);
	}
	public function edit()
	{
		$data = $this->model->edit();

		echo json_encode($data);
	}

	public function hapus()
	{
		$data = $this->model->hapus();

		echo json_encode($data);
	}

	public function print_laporan()
    {
        // $tahun = $this->input->post('single_filter_tahun');
        $semester = $this->input->post('semester');
        $periode = $this->input->post('id_periode');
        $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $periode])->row_array();

        // if ((int) $bulan >= 7) {
        //     $semester = 'Ganjil';
        //     $tahun_ajaran = $tahun . '/' . ($tahun + 1);
        //     $bulan_awal = '07';
        //     $bulan_akhir = '12';
        // } else {
        //     $semester = 'Genap';
        //     $tahun_ajaran = ($tahun - 1) . '/' . $tahun;
        //     $bulan_awal = '01';
        //     $bulan_akhir = '06';
        // }

        //     $jenis = $this->db->query("
        //     SELECT
        //         j.id,
        //         j.nama_jenis
        //     FROM rencana_pemasukan_jenis j
        //     INNER JOIN rencana_pemasukan p
        //         ON p.id=j.id_rencana_pemasukan
        //      WHERE p.bulan >= '$bulan_awal'
        //     AND p.bulan <= '$bulan_akhir'
        //     AND p.tahun = '$tahun'
        //     ORDER BY j.id ASC
        // ")->result_array();

        $jenis = $this->db->query("SELECT j.id, j.nama_jenis FROM rencana_pemasukan_jenis j
        INNER JOIN rencana_pemasukan p ON p.id=j.id_rencana_pemasukan
        WHERE  p.semester = '$semester' AND p.tahun_ajaran = '$periode'
        ORDER BY j.id ASC
    ")->result_array();

        $grand_total = 0;
        foreach ($jenis as &$j) {
            $detail = $this->db->query("SELECT d.* FROM rencana_pemasukan_detail d
            INNER JOIN rencana_pemasukan_jenis j ON j.id = d.id_jenis
            INNER JOIN rencana_pemasukan p ON p.id = j.id_rencana_pemasukan
            WHERE d.id_jenis = '" . $j['id'] . "' AND p.semester = '$semester' AND p.tahun_ajaran = '$periode'
            ORDER BY d.id ASC
        ")->result_array();
            //     $detail = $this->db->query("
            //     SELECT d.*
            //     FROM rencana_pemasukan_detail d
            //     INNER JOIN rencana_pemasukan_jenis j ON j.id = d.id_jenis
            //     INNER JOIN rencana_pemasukan p ON p.id = j.id_rencana_pemasukan
            //     WHERE d.id_jenis = '" . $j['id'] . "'
            //     AND p.bulan >= '$bulan_awal'
            //     AND p.bulan <= '$bulan_akhir'
            //     AND p.tahun = '$tahun'
            //     ORDER BY d.id ASC
            // ")->result_array();

            $j['detail'] = $detail;
            $j['subtotal_volume'] = 0;
            $j['subtotal_jumlah'] = 0;
            $j['subtotal_total'] = 0;
            foreach ($detail as $d) {
                $j['subtotal_volume'] += $d['volume'];
                $j['subtotal_jumlah'] += $d['jumlah'];
                $j['subtotal_total'] += $d['total'];
            }
            $grand_total += $j['subtotal_total'];
        }
        $data = [
            'status' => 'Tahun',
            'semester' => $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
            'data_laporan' => $jenis,
            'grand_total' => $grand_total
        ];

        $this->load->view('admin/data_laporan/laporan_rencana_pemasukan', $data);
    }

}
?>
