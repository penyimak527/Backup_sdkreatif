<?php
class Jurnal_kegiatan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/jurnal/M_jurnal_kegiatan', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$id_pegawai = $this->session->userdata('admin')['id_pegawai'];
		$data_kegiatan = $this->db->query(" SELECT a.*, b.jabatan 
			FROM pegawai a
			JOIN pegawai_jabatan b ON a.id = b.id_pegawai 
			WHERE a.id = '$id_pegawai'
		")->result_array();


		$daftar_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

		$grouped_jadwal = [];

		foreach ($daftar_hari as $hari) {

			$tanggal_list = date('Y') == 2025
				? $this->getTahunSementara($hari)
				: $this->getTanggalHariSetahun($hari);

			foreach ($data_kegiatan as $item) {
				foreach ($tanggal_list as $tgl) {
					$tgl_format = DateTime::createFromFormat('Y-m-d', $tgl);


					if (!$tgl_format)
						continue;

					$tgl_string = $tgl_format->format('Y-m-d');


					if ($tgl_string > date('Y-m-d'))
						continue;
					$tgl_convert = date('d-m-Y', strtotime($tgl_string));
					$jurnal_kegiatan = $this->db->query("SELECT tanggal FROM jurnal_pegawai WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tgl_convert'")->result_array();
					$array_tanggal = array_column($jurnal_kegiatan, 'tanggal');

					// Jika sudah tercatat, skip
					if (in_array($tgl_convert, $array_tanggal)) {
						continue;
					}

					$tgl_indo = date('d-m-Y', strtotime($tgl));
					$jadwal_item = $item;
					$jadwal_item['tanggal'] = $tgl_indo;

					$grouped_jadwal[$tgl_string][] = $jadwal_item;
				}
			}
		}


		krsort($grouped_jadwal);




		$data['grouped_jadwal'] = $grouped_jadwal;
		$data['title'] = 'Jurnal Kegiatan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/jurnal_kegiatan', $data);
		$this->load->view('template/footer');
	}
	public function jurnal_mengisi($id_jadwal, $tanggal)
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$data['title'] = 'Jurnal Kegiatan';
		$data['id_jadwal'] = $id_jadwal;
		$data['tanggal'] = date('Y-m-d', strtotime($tanggal));

		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/jurnal_mengisi', $data);
		$this->load->view('template/footer');
	}
	public function riwayat_mengisi()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$data['title'] = 'Riwayat Kegiatan';
		$data['kelas'] = $this->db->get('kelas')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/jurnal/riwayat_mengisi', $data);
		$this->load->view('template/footer');
	}


	public function pegawai_result()
	{

		$data = $this->model->pegawai_result();

		echo json_encode($data);
	}
	public function jadwal_mengajar_result()
	{

		$data = $this->model->jadwal_mengajar_result();

		echo json_encode($data);
	}
	public function jadwal_kegiatan_result()
	{

		$data = $this->model->jadwal_kegiatan_result();

		echo json_encode($data);
	}
	public function riwayat_mengisi_result()
	{

		$data = $this->model->riwayat_mengisi_result();
		echo json_encode($data);
	}

	public function jurnal_kegiatan_result()
	{

		$data = $this->model->jurnal_kegiatan_result();

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



	public function getTanggalHariSetahun($hari)
	{
		$hariArray = [
			'Senin' => 'monday',
			'Selasa' => 'tuesday',
			'Rabu' => 'wednesday',
			'Kamis' => 'thursday',
			'Jumat' => 'friday',
			'Sabtu' => 'saturday',
			'Minggu' => 'sunday',
		];

		$tanggal = [];
		$tahun = date('Y');
		$startDate = strtotime("first {$hariArray[$hari]} of {$tahun}-01-01");
		$endDate = strtotime("last day of December {$tahun}");

		for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 week', $date)) {
			$tanggal[] = date('Y-m-d', $date);
		}

		return $tanggal;
	}

	public function getTahunSementara($hari)
	{
		$hariArray = [
			'Senin' => 'monday',
			'Selasa' => 'tuesday',
			'Rabu' => 'wednesday',
			'Kamis' => 'thursday',
			'Jumat' => 'friday',
			'Sabtu' => 'saturday',
			'Minggu' => 'sunday',
		];

		$tanggal = [];
		$tahun = date('Y');


		$startDate = strtotime("first {$hariArray[$hari]} of {$tahun}-06-01");


		$endDate = strtotime("last day of December {$tahun}");


		for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 week', $date)) {
			$tanggal[] = date('Y-m-d', $date);
		}

		return $tanggal;
	}
}
?>