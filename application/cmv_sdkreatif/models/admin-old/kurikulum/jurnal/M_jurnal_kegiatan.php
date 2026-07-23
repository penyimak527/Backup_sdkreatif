<?php
class M_jurnal_kegiatan extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function kelas_jadwal_pelajaran_result()
	{
		$id_jadwal_pelajaran = $this->input->post('id_jadwal');


		$guru = $this->db->get_where('kelas_jadwal_pelajaran', ['id' => $id_jadwal_pelajaran])->row_array();
		return $guru;
	}
	public function pegawai_result()
	{
		$id_pegawai = $this->session->userdata('admin')['id_pegawai'];

		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
		return $pegawai;
	}
	public function riwayat_mengisi_result()
	{
		$tanggal = $this->input->post('tanggal');
		$level = $this->session->userdata('admin')['level'];
		$id_guru = $this->session->userdata('admin')['id_pegawai'];


		$this->db->select('jurnal_pegawai.*');
		$this->db->from('jurnal_pegawai');


		if ($level === 'Admin') {
			if (!empty($tanggal)) {
				$this->db->where('jurnal_pegawai.tanggal', $tanggal);
			}

		} else {
			$this->db->where('jurnal_pegawai.id_pegawai', $id_guru);
			if (!empty($tanggal)) {
				$this->db->where('jurnal_pegawai.tanggal', $tanggal);
			}

		}

		$query = $this->db->get();
		$data = $query->result_array();
		if (count($data) > 0) {
			$data_res = [];
			foreach ($data as $item) {
				$data_res[] = [
					'id' => $item['id'],
					'id_pegawai' => $item['id_pegawai'],
					'id_periode' => $item['id_periode'],
					'kegiatan' => $item['kegiatan'],
					'nama_pegawai' => $item['nama_pegawai'],
					'periode' => $item['periode'],
					'semester' => $item['semester'],
					'status_approval' => $item['status_approval'],
					'tanggal' => $item['tanggal'],
					'tanggal_input' => $item['tanggal_input'],
					'uuid' => $item['uuid'],
					'waktu' => $item['waktu'],
				];
			}

			return $data_res;
		}

		return [];
	}



	public function jurnal_kegiatan_result()
	{
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


					$tgl_indo = date('d-m-Y', strtotime($tgl));
					$jadwal_item = $item;
					$jadwal_item['hari'] = $hari;
					$jadwal_item['tanggal'] = $tgl_indo;

					$grouped_jadwal[$tgl_string][] = $jadwal_item;
				}
			}
		}

		// Urutkan berdasarkan tanggal
		ksort($grouped_jadwal);

		return $grouped_jadwal;
	}

	public function tambah()
	{

		$uuid = $this->input->post('uuid');
		$pegawai = $this->db->get_where('pegawai', ['id' => $this->input->post('id_pegawai')])->row_array();
		$tanggal = date('d-m-Y', strtotime($this->input->post('tanggal')));
		$waktu = date('H:i:s');
		$tanggal_input = date('d-m-Y');

		$this->db->select('kelas_setting.semester, master_tahun_ajaran.*');
		$this->db->join('kelas_setting', 'kelas_setting.id_periode = master_tahun_ajaran.id');
		$this->db->from('master_tahun_ajaran');
		$this->db->where('master_tahun_ajaran.status', 'Aktif');
		$jadwal_pelajaran = $this->db->get()->row_array();



		$kegiatan = $this->input->post('kegiatan');
		$status_approval = 0;

		$data_checkin_uuid = $this->db->query("SELECT a.id FROM jurnal_pegawai a WHERE a.uuid = '$uuid'");

		if ($data_checkin_uuid->num_rows() > 0) {
			return [
				'status' => true,
			];
		}



		$data = [
			'id_pegawai' => $pegawai['id'],
			'nama_pegawai' => $pegawai['nama_pegawai'],
			'tanggal' => $tanggal,
			'waktu' => $waktu,
			'kegiatan' => $kegiatan,
			'status_approval' => $status_approval,
			'tanggal_input' => $tanggal_input,
			'semester' => $jadwal_pelajaran['semester'],
			'id_periode' => $jadwal_pelajaran['id'],
			'periode' => $jadwal_pelajaran['periode'],
			'uuid' => $uuid,
		];

		$response = $this->db->insert('jurnal_pegawai', $data);


		if ($response) {
			$data = [
				'status' => true,
			];
		}
		return $data;
	}
	public function edit()
	{

		$id_jurnal_pegawai = $this->input->post('id_jurnal_pegawai');


		$kegiatan = $this->input->post('kegiatan');
		$data = [

			'kegiatan' => $kegiatan,
		];

		$response = $this->db->update('jurnal_pegawai', $data, ['id' => $id_jurnal_pegawai]);


		if ($response) {
			$data = [
				'status' => true,
			];
		}
		return $data;
	}
	public function hapus()
	{
		$id = $this->input->post('id');


		$response = $this->db->delete('kelas_setting', ['id' => $id]);

		return $response;
	}


	private function compress_image($path)
	{
		$config['image_library'] = 'gd2';
		$config['source_image'] = $path;
		$config['quality'] = '70%';
		$config['maintain_ratio'] = TRUE;
		$config['width'] = 1024;
		$config['height'] = 768;
		$this->image_lib->initialize($config);
		if (!$this->image_lib->resize()) {
			echo $this->image_lib->display_errors();
		}
		$this->image_lib->clear();
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

		// Awal dari bulan Juni
		$startDate = strtotime("first {$hariArray[$hari]} of {$tahun}-06-01");

		// Akhir tahun berjalan
		$endDate = strtotime("last day of December {$tahun}");

		// Loop dari tanggal awal ke akhir tahun
		for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 week', $date)) {
			$tanggal[] = date('Y-m-d', $date);
		}

		return $tanggal;
	}

}
?>