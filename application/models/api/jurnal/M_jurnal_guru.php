<?php
class M_jurnal_guru extends CI_Model
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
	public function jurnal_siswa_result()
	{
		$id_jadwal_pelajaran = $this->input->post('id_jadwal');

		$kelas_jadwal = $this->db->get_where('kelas_jadwal_pelajaran', ['id' => $id_jadwal_pelajaran])->row_array();

		$kelas_siswa = $this->db->get_where('kelas_siswa', ['id_kelas_setting' => $kelas_jadwal['id_kelas_setting']])->result_array();


		return $kelas_siswa;
	}
	public function riwayat_mengajar_result()
	{
		$tanggal = $this->input->post('tanggal');
		$id_kelas = $this->input->post('id_kelas');
		$id_guru = $this->input->post('id_guru'); // ambil dari body, bukan session

		$this->db->select('jurnal_guru.*, kelas_jadwal_pelajaran.hari, kelas_jadwal_pelajaran.kelas, kelas.kode_kelas');
		$this->db->from('jurnal_guru');
		$this->db->join('kelas_jadwal_pelajaran', 'kelas_jadwal_pelajaran.id = jurnal_guru.id_kelas_jadwal_pelajaran');
		$this->db->join('kelas', 'kelas.id = kelas_jadwal_pelajaran.id_kelas', 'left');

		// Filter berdasarkan input
		if (!empty($id_guru)) {
			$this->db->where('jurnal_guru.id_guru', $id_guru);
		}

		if (!empty($tanggal)) {
			$this->db->where('jurnal_guru.tanggal', $tanggal);
		}

		if (!empty($id_kelas)) {
			$this->db->where('kelas_jadwal_pelajaran.id_kelas', $id_kelas);
		}

		$this->db->where('jurnal_guru.status_approval !=', 0);

		$query = $this->db->get();
		$data = $query->result_array();

		$result = [];

		if (count($data) > 0) {
			foreach ($data as $item) {
				$jurnal_siswa = $this->db->get_where('jurnal_siswa', [
					'id_jurnal_guru' => $item['id']
				])->result_array();

				$result[] = [
					'id' => $item['id'],
					'id_guru' => $item['id_guru'],
					'nama_guru' => $item['nama_guru'],
					'id_kelas_jadwal_pelajaran' => $item['id_kelas_jadwal_pelajaran'],
					'id_mapel' => $item['id_mapel'],
					'mapel' => $item['mapel'],
					'hari' => $item['hari'],
					'nama_kelas' => $item['kelas'],
					'kode_kelas' => $item['kode_kelas'],
					'jam_mulai_pelajaran' => $item['jam_mulai_pelajaran'],
					'jam_selesai_pelajaran' => $item['jam_selesai_pelajaran'],
					'kegiatan' => $item['kegiatan'],
					'tema' => $item['tema'],
					'semester' => $item['semester'],
					'periode' => $item['periode'],
					'id_periode' => $item['id_periode'],
					'tanggal' => $item['tanggal'],
					'tanggal_input' => $item['tanggal_input'],
					'waktu' => $item['waktu'],
					'status_approval' => $item['status_approval'],
					'uuid' => $item['uuid'],
					'foto_kegiatan_awal' => $item['foto_kegiatan_awal'],
					'foto_kegiatan_akhir' => $item['foto_kegiatan_akhir'],
					'data' => $jurnal_siswa,
				];
			}

			echo json_encode([
				'status' => true,
				'data' => $result,
			]);
			exit;
		}

		echo json_encode([
			'status' => false,
			'data' => [],
			'message' => 'Data tidak ditemukan.',
		]);
		exit;
	}

	public function jadwal_mengajar_result()
	{
		$this->db->select('kelas_jadwal_pelajaran.*');
		$this->db->from('kelas_jadwal_pelajaran');
		$this->db->order_by("FIELD(kelas_jadwal_pelajaran.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')");
		$query = $this->db->get();
		$result = $query->result_array();

		$grouped_by_hari = [];
		foreach ($result as $row) {
			$hari = $row['hari'];
			if (!isset($grouped_by_hari[$hari])) {
				$grouped_by_hari[$hari] = [];
			}
			$grouped_by_hari[$hari][] = $row;
		}

		return $grouped_by_hari;
	}

	public function jadwal_mengajar_result_guru()
	{
		$id_guru = $this->input->post('id_guru');
		if (!$id_guru) {
			$id_guru = $this->input->get('id_guru');
		}

		if (!$id_guru) {
			return [];
		}

		$this->db->select('kelas_jadwal_pelajaran.*');
		$this->db->from('kelas_jadwal_pelajaran');
		$this->db->where('kelas_jadwal_pelajaran.id_guru', $id_guru);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}


	public function jurnal_guru_result()
	{
		$id_guru = $this->input->post('id_guru') ?? $this->input->get('id_guru');

		if (!$id_guru) {
			return []; // bisa diganti return error JSON juga
		}

		$data_mengajar = $this->db->query("SELECT 
		a.*,b.kode_kelas
		FROM kelas_jadwal_pelajaran a left join kelas b on a.id_kelas=b.id
        WHERE a.id_guru = '$id_guru' 
        ORDER BY FIELD(a.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
    ")->result_array();

		foreach ($data_mengajar as &$item) {
			$tahun = date('Y');
			if ($tahun == 2025) {
				$tanggal = $this->getTahunSementara(ucfirst(strtolower($item['hari'])));
			} else {
				$tanggal = $this->getTanggalHariSetahun(ucfirst(strtolower($item['hari'])));
			}
			$item['tanggal'] = $tanggal;
		}

		$grouped_jadwal = [];

		foreach ($data_mengajar as $jadwal_item) {
			if (is_array($jadwal_item['tanggal'])) {
				foreach ($jadwal_item['tanggal'] as $tgl) {
					$tgl_format = DateTime::createFromFormat('Y-m-d', $tgl);
					if (!$tgl_format)
						continue;

					$tgl_string = $tgl_format->format('Y-m-d');
					if ($tgl_string > date('Y-m-d'))
						continue;

					$jurnal = $this->db->get_where('jurnal_guru', [
						'id_kelas_jadwal_pelajaran' => $jadwal_item['id'],
						'tanggal' => date('d-m-Y', strtotime($tgl))
					])->row_array();

					if (date('d-m-Y', strtotime($tgl)) == ($jurnal['tanggal'] ?? ''))
						continue;

					$tanggal = date('d-m-Y', strtotime($tgl));
					$absen = $this->db->query("
                    SELECT a.* 
                    FROM izin_pegawai a 
                    LEFT JOIN pegawai_jabatan b ON a.id_pegawai = b.id_pegawai 
                    WHERE a.id_pegawai = '$id_guru' 
                    AND a.tgl_tidak_hadir = '$tanggal' 
                    AND a.status_approval = 1 
                    AND b.jabatan = 'Guru'
                ")->row_array();

					if ($absen)
						continue;

					$grouped_jadwal[$tgl][] = $jadwal_item;
				}
			} else {
				$tgl = DateTime::createFromFormat('d-m-Y', $jadwal_item['tanggal'])->format('Y-m-d');
				$grouped_jadwal[$tgl][] = $jadwal_item;
			}
		}

		ksort($grouped_jadwal);
		return $grouped_jadwal;
	}


	public function tambah()
	{

		$uuid = $this->input->post('uuid');
		$id_kelas_jadwal_pelajaran = $this->input->post('id_kelas_jadwal_pelajaran');
		$guru = $this->db->get_where('guru', ['id' => $this->input->post('id_guru')])->row_array();
		$mapel = $this->db->get_where('master_mata_pelajaran', ['id' => $this->input->post('id_mapel')])->row_array();

		$tanggal = date('d-m-Y', strtotime($this->input->post('tanggal')));
		$waktu = date('H:i:s');
		$tanggal_input = date('d-m-Y');
		$this->db->select('kelas_jadwal_pelajaran.*');
		$this->db->from('kelas_jadwal_pelajaran');
		$this->db->where('kelas_jadwal_pelajaran.id', $id_kelas_jadwal_pelajaran);
		$jadwal_pelajaran = $this->db->get()->row_array();


		$jam_mulai_pelajaran = $jadwal_pelajaran['jam_pelajaran_awal'];
		$jam_selesai_pelajaran = $jadwal_pelajaran['jam_pelajaran_akhir'];

		$kegiatan = $this->input->post('kegiatan');
		$tema = $this->input->post('tema');
		$status_approval = 0;

		// $config['upload_path'] = 'storage/guru/jurnal/';
		// $config['allowed_types'] = 'png|jpg|jpeg';
		// $config['encrypt_name'] = true;

		// $this->load->library('upload', $config);
		// $this->load->library('image_lib');
		// $nama_file_awal = 'default.jpg';
		// $nama_file_akhir = 'default.jpg';
		// if (!empty($_FILES['foto_kegiatan_awal']['name'])) {
		// 	if (!$this->upload->do_upload('foto_kegiatan_awal')) {
		// 		echo $this->upload->display_errors();
		// 		return;
		// 	} else {
		// 		$data_upload_awal = $this->upload->data();
		// 		$nama_file_awal = $data_upload_awal['file_name'] ?? 'default.jpg';
		// 		$path_file = $data_upload_awal['full_path'];


		// 		$this->compress_image($path_file);
		// 	}
		// }


		// if (!empty($_FILES['foto_kegiatan_akhir']['name'])) {
		// 	if (!$this->upload->do_upload('foto_kegiatan_akhir')) {
		// 		echo $this->upload->display_errors();
		// 		return;
		// 	} else {
		// 		$data_upload_akhir = $this->upload->data();
		// 		$nama_file_akhir = $data_upload_akhir['file_name'] ?? 'default.jpg';
		// 		$path_file = $data_upload_akhir['full_path'];


		// 		$this->compress_image($path_file);
		// 	}
		// }

		$data_checkin_uuid = $this->db->query("SELECT a.id FROM jurnal_guru a WHERE a.uuid = '$uuid'");

		if ($data_checkin_uuid->num_rows() > 0) {
			return [
				'status' => true,
			];
		}

		$data = [
			'id_kelas_jadwal_pelajaran' => $id_kelas_jadwal_pelajaran,
			'id_guru' => $guru['id'],
			'nama_guru' => $guru['nama_guru'],
			'id_kelas' => $jadwal_pelajaran['id_kelas'],
			'id_mapel' => $mapel['id'],
			'mapel' => $mapel['mapel'],
			'tanggal' => $tanggal,
			'waktu' => $waktu,
			'jam_mulai_pelajaran' => $jam_mulai_pelajaran,
			'jam_selesai_pelajaran' => $jam_selesai_pelajaran,
			'kegiatan' => $kegiatan,
			'tema' => $tema,
			'status_approval' => $status_approval,
			'tanggal_input' => $tanggal_input,
			'semester' => $jadwal_pelajaran['semester'],
			'id_periode' => $jadwal_pelajaran['id_periode'],
			'periode' => $jadwal_pelajaran['periode'],
			'uuid' => $uuid,
		];

		$response = $this->db->insert('jurnal_guru', $data);

		// $id_jurnal_guru = $this->db->insert_id();
		// $id_kelas_siswa = $this->input->post('id_kelas_siswa');
		// $status_presensi_siswa = $this->input->post('status_presensi_siswa');
		// $files = $_FILES;
		// $data_siswa = [];
		// foreach ($id_kelas_siswa as $key => $value) {

		// 	$this->db->select('kelas_siswa.*, kelas_setting.id_kelas, kelas_setting.nama_kelas');
		// 	$this->db->join('kelas_setting', 'kelas_setting.id = kelas_siswa.id_kelas_setting');
		// 	$siswa = $this->db->get_where('kelas_siswa', ['id_siswa' => $value])->row_array();


		// 	$bukti_surat = '';
		// 	if (!empty($files['bukti_surat']['name'][$key])) {
		// 		$_FILES['bukti_surat']['name'] = $files['bukti_surat']['name'][$key];
		// 		$_FILES['bukti_surat']['type'] = $files['bukti_surat']['type'][$key];
		// 		$_FILES['bukti_surat']['tmp_name'] = $files['bukti_surat']['tmp_name'][$key];
		// 		$_FILES['bukti_surat']['error'] = $files['bukti_surat']['error'][$key];
		// 		$_FILES['bukti_surat']['size'] = $files['bukti_surat']['size'][$key];

		// 		$config_bukti['upload_path'] = 'storage/siswa/bukti_surat/';
		// 		$config_bukti['allowed_types'] = 'png|jpg|jpeg|pdf';
		// 		$config_bukti['encrypt_name'] = true;

		// 		$this->upload->initialize($config_bukti);

		// 		if ($this->upload->do_upload('bukti_surat')) {
		// 			$upload_data = $this->upload->data();
		// 			$bukti_surat = $upload_data['file_name'];
		// 		} else {

		// 			$bukti_surat = '';
		// 		}
		// 	}

		// 	$data_siswa[] = [
		// 		'id_jurnal_guru' => $id_jurnal_guru,
		// 		'id_kelas_siswa' => $value,
		// 		'id_siswa' => $siswa['id_siswa'],
		// 		'nama_siswa' => $siswa['nama_siswa'],
		// 		'id_kelas_setting' => $siswa['id_kelas_setting'],
		// 		'id_kelas' => $siswa['id_kelas'],
		// 		'nama_kelas' => $siswa['nama_kelas'],
		// 		'status_presensi' => $status_presensi_siswa[$key] ?? '',
		// 		'tanggal' => $tanggal,
		// 		'waktu' => $waktu,
		// 		'bukti_surat' => $bukti_surat,
		// 	];
		// }
		// $this->db->insert_batch('jurnal_siswa', $data_siswa);
		if ($response) {
			$data = [
				'status' => true,
			];
		}
		return $data;
	}

	public function edit()
	{

		$id_jurnal_guru = $this->input->post('id_jurnal_guru');

		$kegiatan = $this->input->post('kegiatan');
		$tema = $this->input->post('tema');

		$data = [
			'kegiatan' => $kegiatan,
			'tema' => $tema,
		];

		$response = $this->db->update('jurnal_guru', $data, ['id' => $id_jurnal_guru]);
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

		$start = '2025-07-21';

		$startDate = strtotime("next {$hariArray[$hari]}", strtotime($start));

		if (strtolower(date('l', strtotime($start))) === strtolower($hariArray[$hari])) {
			$startDate = strtotime($start);
		}

		$tahun = date('Y', $startDate);
		$endDate = strtotime("last day of December {$tahun}");

		for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 week', $date)) {
			$tanggal[] = date('Y-m-d', $date);
		}

		return $tanggal;
	}

	public function get_kelas()
	{
		return $this->db->get('kelas')->result_array();
	}
}
