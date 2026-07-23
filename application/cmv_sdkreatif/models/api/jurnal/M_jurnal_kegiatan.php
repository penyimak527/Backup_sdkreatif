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
		$id_pegawai = $this->input->post('id_pegawai');

		if (!$id_pegawai) {
			return [
				'status' => false,
				'message' => 'ID Pegawai tidak dikirim.'
			];
		}

		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();

		if ($pegawai) {
			return [
				'status' => true,
				'data' => $pegawai
			];
		} else {
			return [
				'status' => false,
				'message' => 'Data pegawai tidak ditemukan.'
			];
		}
	}

	public function riwayat_mengisi_result($level, $id_guru, $tanggal)
	{
		$this->db->select('jurnal_pegawai.*');
		$this->db->from('jurnal_pegawai');
		$this->db->where('jurnal_pegawai.status_approval !=', '0');

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

		$this->db->order_by("STR_TO_DATE(jurnal_pegawai.tanggal_input, '%d-%m-%Y') DESC");
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
		$id_pegawai = $this->input->post('id_pegawai');
		$tanggal_raw = $this->input->post('tanggal');
		$kegiatan = $this->input->post('kegiatan');

		// Validasi input wajib
		if (!$uuid || !$id_pegawai || !$tanggal_raw || !$kegiatan) {
			return [
				'status' => false,
				'message' => 'Parameter tidak lengkap.',
			];
		}

		$pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
		if (!$pegawai) {
			return [
				'status' => false,
				'message' => 'Data pegawai tidak ditemukan.',
			];
		}

		$tanggal = date('d-m-Y', strtotime($tanggal_raw));
		$waktu = date('H:i:s');
		$tanggal_input = date('d-m-Y');

		$this->db->select('kelas_jadwal_pelajaran.semester, master_tahun_ajaran.*');
		$this->db->join('kelas_jadwal_pelajaran', 'kelas_jadwal_pelajaran.id_periode = master_tahun_ajaran.id');
		$this->db->from('master_tahun_ajaran');
		$this->db->where('master_tahun_ajaran.status', 'Aktif');
		$jadwal_pelajaran = $this->db->get()->row_array();

		if (!$jadwal_pelajaran) {
			return [
				'status' => false,
				'message' => 'Data periode aktif tidak ditemukan.',
			];
		}

		// Cek duplikat UUID
		$data_checkin_uuid = $this->db->query("SELECT id FROM jurnal_pegawai WHERE uuid = ?", [$uuid]);
		if ($data_checkin_uuid->num_rows() > 0) {
			return [
				'status' => true,
				'message' => 'Data sudah pernah disimpan sebelumnya.',
			];
		}

		$data = [
			'id_pegawai' => $pegawai['id'],
			'nama_pegawai' => $pegawai['nama_pegawai'],
			'tanggal' => $tanggal,
			'waktu' => $waktu,
			'kegiatan' => $kegiatan,
			'status_approval' => 0,
			'tanggal_input' => $tanggal_input,
			'semester' => $jadwal_pelajaran['semester'],
			'id_periode' => $jadwal_pelajaran['id'],
			'periode' => $jadwal_pelajaran['periode'],
			'uuid' => $uuid,
		];

		$insert = $this->db->insert('jurnal_pegawai', $data);

		if ($insert) {
			return [
				'status' => true,
				'message' => 'Data berhasil disimpan.',
			];
		} else {
			return [
				'status' => false,
				'message' => 'Gagal menyimpan data.',
			];
		}
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
