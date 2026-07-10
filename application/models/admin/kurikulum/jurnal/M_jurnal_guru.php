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
		$level = $this->session->userdata('admin')['level'];
		$id_guru = $this->session->userdata('admin')['id_pegawai'];


		$this->db->select('jurnal_guru.*, kelas_jadwal_pelajaran.hari,kelas_jadwal_pelajaran.kelas,kelas.kode_kelas');
		$this->db->from('jurnal_guru');
		$this->db->join('kelas_jadwal_pelajaran', 'kelas_jadwal_pelajaran.id = jurnal_guru.id_kelas_jadwal_pelajaran');
		$this->db->join('kelas', 'kelas.id = jurnal_guru.id_kelas');


		if ($level === 'Admin') {
			if (!empty($tanggal)) {
				$this->db->where('jurnal_guru.tanggal', $tanggal);
			}
			if (!empty($id_kelas)) {
				// $this->db->where('kelas_setting.id_kelas', $id_kelas);
				$this->db->where('kelas.id', $id_kelas);
			}
		} else {
			$this->db->where('jurnal_guru.id_guru', $id_guru);
			if (!empty($tanggal)) {
				$this->db->where('jurnal_guru.tanggal', $tanggal);
			}
			if (!empty($id_kelas)) {
				$this->db->where('kelas_jadwal_pelajaran.id_kelas', $id_kelas);
			}
		}
		$this->db->where('jurnal_guru.status_approval !=', 0);

		$query = $this->db->get();
		$data = $query->result_array();
		if (count($data) > 0) {
			$data_res = [];
			foreach ($data as $item) {
				$jurnal_siswa = $this->db->get_where('jurnal_siswa', ['id_jurnal_guru' => $item['id']])->result_array();

				$data_res[] = [
					'foto_kegiatan_akhir' => $item['foto_kegiatan_akhir'],
					'foto_kegiatan_awal' => $item['foto_kegiatan_awal'],
					'hari' => $item['hari'],
					'id' => $item['id'],
					'id_guru' => $item['id_guru'],
					'id_kelas_jadwal_pelajaran' => $item['id_kelas_jadwal_pelajaran'],
					'id_mapel' => $item['id_mapel'],
					'id_periode' => $item['id_periode'],
					'jam_mulai_pelajaran' => $item['jam_mulai_pelajaran'],
					'jam_selesai_pelajaran' => $item['jam_selesai_pelajaran'],
					'kegiatan' => $item['kegiatan'],
					'mapel' => $item['mapel'],
					'nama_guru' => $item['nama_guru'],
					'nama_kelas' => $item['kelas'],
					'kode_kelas' => $item['kode_kelas'],
					'periode' => $item['periode'],
					'semester' => $item['semester'],
					'status_approval' => $item['status_approval'],
					'tanggal' => $item['tanggal'],
					'tanggal_input' => $item['tanggal_input'],
					'tema' => $item['tema'],
					'uuid' => $item['uuid'],
					'waktu' => $item['waktu'],
					'data' => $jurnal_siswa,
				];
			}

			return $data_res;
		}
		return [];
	}
	public function jadwal_mengajar_result()
	{

		$this->db->select('a.*,b.kode_kelas ');
		$this->db->from('kelas_jadwal_pelajaran a');
		$this->db->join('kelas b', 'a.id_kelas = b.id', 'left');
		$this->db->order_by("FIELD(a.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')");
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
		$id_guru = $this->session->userdata('admin')['id_pegawai'];
		$this->db->select('a.*,b.kode_kelas');
		$this->db->from('kelas_jadwal_pelajaran a');
		$this->db->join('kelas b', 'a.id_kelas = b.id', 'left');
		$this->db->where('a.id_guru', $id_guru);
		$query = $this->db->get();
		$result = $query->result_array();



		return $result;
	}

	// public function jurnal_guru_result()
	// {
	// 	$id_guru = $this->session->userdata('admin')['id_pegawai'];
	// 	$data_mengajar = $this->db->query("SELECT 
	// 	a.*,b.kode_kelas
	// 	FROM kelas_jadwal_pelajaran a left join kelas b on a.id_kelas=b.id
	// 	WHERE a.id_guru = '$id_guru' 
	// 	ORDER BY FIELD(a.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
	// ")->result_array();

	// 	foreach ($data_mengajar as &$item) {

	// 		$tahun = date('Y');
	// 		if ($tahun == 2025) {
	// 			$tanggal = $this->getTahunSementara(ucfirst(strtolower($item['hari'])));

	// 		} else {
	// 			$tanggal = $this->getTanggalHariSetahun(ucfirst(strtolower($item['hari'])));
	// 		}
	// 		$item['tanggal'] = $tanggal;
	// 	}



	// 	$grouped_jadwal = [];

	// 	foreach ($data_mengajar as $jadwal_item) {

	// 		if (is_array($jadwal_item['tanggal'])) {
	// 			foreach ($jadwal_item['tanggal'] as $tgl) {
	// 				$tgl_format = DateTime::createFromFormat('Y-m-d', $tgl);
	// 				$tgl_jurnal_guru = $this->db->get_where('jurnal_guru', [
	// 					'id_kelas_jadwal_pelajaran' => $jadwal_item['id'],
	// 					'tanggal' => date('d-m-Y', strtotime($tgl))
	// 				])->row_array();
	// 				if (!$tgl_format)
	// 					continue;

	// 				$tgl_string = $tgl_format->format('Y-m-d');


	// 				if ($tgl_string > date('Y-m-d')) {
	// 					continue;
	// 				}


	// 				if (date('d-m-Y', strtotime($tgl)) == !empty($tgl_jurnal_guru['tanggal']) || !empty($tgl_jurnal_guru['id_kelas_jadwal_pelajaran']) == !empty($jadwal_item['id'] || !empty($tgl_jurnal_guru['id_guru']) == $id_guru)) {
	// 					continue;
	// 				}



	// 				$tanggal = date('d-m-Y', strtotime($tgl));
	// 				$absen_tidak_hadir = $this->db->query("SELECT a.* FROM izin_pegawai a left join pegawai_jabatan b on a.id_pegawai = b.id_pegawai 
	// 				WHERE a.id_pegawai = '$id_guru' AND a.tgl_tidak_hadir = '$tanggal' AND a.status_approval=1 AND b.jabatan = 'Guru'  ")->row_array();

	// 				$skip = false;
	// 				if ($absen_tidak_hadir) {
	// 					$skip = true;
	// 				}

	// 				if ($skip) {
	// 					continue;
	// 				}


	// 				$grouped_jadwal[$tgl][] = $jadwal_item;
	// 			}
	// 		} else {
	// 			$tgl = DateTime::createFromFormat('d-m-Y', $jadwal_item['tanggal'])->format('Y-m-d');
	// 			$grouped_jadwal[$tgl][] = $jadwal_item;
	// 		}
	// 	}

	// 	krsort($grouped_jadwal);

	// 	return $grouped_jadwal;
	// }
	public function jurnal_guru_result()
{
    $id_guru = $this->session->userdata('admin')['id_pegawai'];

    // Ambil tahun ajaran aktif
    $periode_aktif = $this->db
        ->get_where('master_tahun_ajaran', ['status' => 'Aktif'])
        ->row_array();

    if (empty($periode_aktif)) {
        return [];
    }

    // Tentukan semester aktif berdasarkan bulan berjalan
    // Juli - Desember = Ganjil
    // Januari - Juni = Genap
    $bulan_sekarang = date('m');
    $semester_aktif = in_array($bulan_sekarang, ['07', '08', '09', '10', '11', '12'])
        ? 'Ganjil'
        : 'Genap';

    $this->db->select('a.*, b.kode_kelas');
    $this->db->from('kelas_jadwal_pelajaran a');
    $this->db->join('kelas b', 'a.id_kelas = b.id', 'left');
    $this->db->join('master_tahun_ajaran c', 'a.id_periode = c.id', 'inner');
    $this->db->where('a.id_guru', $id_guru);
    $this->db->where('a.id_periode', $periode_aktif['id']);
    $this->db->where('a.semester', $semester_aktif);
    $this->db->where('c.status', 'Aktif');
    $this->db->order_by("FIELD(a.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')", '', false);

    $data_mengajar = $this->db->get()->result_array();

    foreach ($data_mengajar as &$item) {
        $tanggal = $this->getTanggalHariSemester(
            ucfirst(strtolower($item['hari'])),
            $periode_aktif['periode'],
            $semester_aktif
        );

        $item['tanggal'] = $tanggal;
    }

    $grouped_jadwal = [];

    foreach ($data_mengajar as $jadwal_item) {
        if (is_array($jadwal_item['tanggal'])) {
            foreach ($jadwal_item['tanggal'] as $tgl) {
                $tgl_format = DateTime::createFromFormat('Y-m-d', $tgl);

                if (!$tgl_format) {
                    continue;
                }

                $tgl_string = $tgl_format->format('Y-m-d');

                // Jangan tampilkan tanggal masa depan
                if ($tgl_string > date('Y-m-d')) {
                    continue;
                }

                $tanggal = date('d-m-Y', strtotime($tgl));

                // Cek apakah jurnal untuk jadwal dan tanggal ini sudah dibuat
                $tgl_jurnal_guru = $this->db->get_where('jurnal_guru', [
                    'id_kelas_jadwal_pelajaran' => $jadwal_item['id'],
                    'id_guru' => $id_guru,
                    'tanggal' => $tanggal
                ])->row_array();

                if (!empty($tgl_jurnal_guru)) {
                    continue;
                }

                // Cek izin guru pada tanggal tersebut
                $absen_tidak_hadir = $this->db->query("
                    SELECT a.* 
                    FROM izin_pegawai a 
                    LEFT JOIN pegawai_jabatan b ON a.id_pegawai = b.id_pegawai 
                    WHERE a.id_pegawai = '$id_guru' 
                    AND a.tgl_tidak_hadir = '$tanggal' 
                    AND a.status_approval = 1 
                    AND b.jabatan = 'Guru'
                ")->row_array();

                if (!empty($absen_tidak_hadir)) {
                    continue;
                }

                $grouped_jadwal[$tgl][] = $jadwal_item;
            }
        }
    }

    krsort($grouped_jadwal);

    return $grouped_jadwal;
}

public function getTanggalHariSemester($hari, $periode, $semester)
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

    if (empty($hariArray[$hari])) {
        return [];
    }

    $periode_explode = explode('/', $periode);

    if (count($periode_explode) < 2) {
        return [];
    }

    $tahun_awal = $periode_explode[0];
    $tahun_akhir = $periode_explode[1];

    if ($semester == 'Ganjil') {
        $tanggal_awal = $tahun_awal . '-07-01';
        $tanggal_akhir = $tahun_awal . '-12-31';
    } else {
        $tanggal_awal = $tahun_akhir . '-01-01';
        $tanggal_akhir = $tahun_akhir . '-06-30';
    }

    $tanggal = [];

    $startDate = strtotime($tanggal_awal);
    $endDate = strtotime($tanggal_akhir);

    $targetHari = $hariArray[$hari];

    if (strtolower(date('l', $startDate)) === strtolower($targetHari)) {
        $date = $startDate;
    } else {
        $date = strtotime("next {$targetHari}", $startDate);
    }

    for ($date; $date <= $endDate; $date = strtotime('+1 week', $date)) {
        $tanggal[] = date('Y-m-d', $date);
    }

    return $tanggal;
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

		// Start dari tanggal 21 Juli
		$start = '2025-07-21'; // <-- kamu bisa ubah ini ke tanggal lain jika perlu

		$startDate = strtotime("next {$hariArray[$hari]}", strtotime($start));

		// Jika tepat 21 Juli adalah hari yang dimaksud, maka jangan lompat ke minggu berikutnya
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


}
?>