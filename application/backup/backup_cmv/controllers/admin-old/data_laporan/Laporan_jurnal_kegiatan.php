<?php
class Laporan_jurnal_kegiatan extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		header('Access-Control-Allow-Origin: *');
	}

	public function print_laporan()
	{
		$json = file_get_contents('php://input');
		$ambil = json_decode($json, true);

		$id_pegawai = $ambil['id_pegawai'];

		if ($ambil['filter'] == 'tanggal') {
			$dari_tanggal = date('d-m-Y', strtotime($ambil['dari_tanggal']));
			$sampai_tanggal = date('d-m-Y', strtotime($ambil['sampai_tanggal']));

			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();


			if ($pegawai['jabatan'] == 'Guru') {

				$jurnal_guru = $this->db->query("SELECT a.*,c.nama_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
					left join kelas_setting c on b.id_kelas_setting = c.id left join kelas d on c.id_kelas = d.id
					WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')  
					AND a.id_guru = '$id_pegawai' Order by a.tanggal,a.jam_mulai_pelajaran asc")->result_array();

				$grouped_by_tanggal = [];

				foreach ($jurnal_guru as $jurnal) {
					$tanggal = $jurnal['tanggal'];
					$kelas = $jurnal['nama_kelas'];

					if (!isset($grouped_by_tanggal[$tanggal])) {
						$grouped_by_tanggal[$tanggal] = [];
					}

					if (!isset($grouped_by_tanggal[$tanggal][$kelas])) {
						$grouped_by_tanggal[$tanggal][$kelas] = [];
					}


					$grouped_by_tanggal[$tanggal][$kelas][] = $jurnal;
				}
				$data = [
					'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
					'grouped_by_tanggal' => $grouped_by_tanggal,
					'pegawai' => $pegawai,
					'status' => 'Tanggal'
				];
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  
					WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')  
					AND a.id_pegawai = '$id_pegawai' Order by a.tanggal asc")->result_array();
				$data = [
					'judul' => $dari_tanggal . ' s/d ' . $sampai_tanggal,
					'jurnal_pegawai' => $jurnal_pegawai,
					'pegawai' => $pegawai,
					'status' => 'Tanggal'
				];
			}


		} else if ($ambil['filter'] == 'bulan') {
			$bulan = $ambil['filter_bulan'];
			$tahun = $ambil['filter_tahun'];
			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();


			if ($pegawai['jabatan'] == 'Guru') {
				$jurnal_guru = $this->db->query("SELECT a.*,c.nama_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				left join kelas_setting c on b.id_kelas_setting = c.id left join kelas d on c.id_kelas = d.id
				WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_guru = '$id_pegawai' Order by a.tanggal,a.jam_mulai_pelajaran asc")->result_array();

				$grouped_by_tanggal = [];
				foreach ($jurnal_guru as $jurnal) {
					$tanggal = $jurnal['tanggal'];
					$kelas = $jurnal['nama_kelas'];

					if (!isset($grouped_by_tanggal[$tanggal])) {
						$grouped_by_tanggal[$tanggal] = [];
					}

					if (!isset($grouped_by_tanggal[$tanggal][$kelas])) {
						$grouped_by_tanggal[$tanggal][$kelas] = [];
					}


					$grouped_by_tanggal[$tanggal][$kelas][] = $jurnal;
				}


				$data = [
					'judul' => $this->getBulan($bulan) . " " . $tahun,
					'grouped_by_tanggal' => $grouped_by_tanggal,
					'pegawai' => $pegawai,
					'status' => 'Bulan'
				];
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_pegawai = '$id_pegawai' Order by a.tanggal asc")->result_array();

				$data = [
					'judul' => $this->getBulan($bulan) . " " . $tahun,
					'jurnal_pegawai' => $jurnal_pegawai,
					'pegawai' => $pegawai,
					'status' => 'Bulan'
				];
			}

		} else {
			$tahun = $ambil['single_filter_tahun'];

			$pegawai = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();



			if ($pegawai['jabatan'] == 'Guru') {
				$jurnal_guru = $this->db->query("SELECT a.*,c.nama_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				left join kelas_setting c on b.id_kelas_setting = c.id left join kelas d on c.id_kelas = d.id
				WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_guru = '$id_pegawai' Order by a.tanggal,a.jam_mulai_pelajaran asc")->result_array();

				$grouped_by_tanggal = [];
				foreach ($jurnal_guru as $jurnal) {
					$tanggal = $jurnal['tanggal'];
					$kelas = $jurnal['nama_kelas'];

					if (!isset($grouped_by_tanggal[$tanggal])) {
						$grouped_by_tanggal[$tanggal] = [];
					}

					if (!isset($grouped_by_tanggal[$tanggal][$kelas])) {
						$grouped_by_tanggal[$tanggal][$kelas] = [];
					}


					$grouped_by_tanggal[$tanggal][$kelas][] = $jurnal;
				}

				$data = [
					'judul' => $tahun,
					'grouped_by_tanggal' => $grouped_by_tanggal,
					'pegawai' => $pegawai,
					'status' => 'Tahun'
				];
			} else {
				$jurnal_pegawai = $this->db->query("SELECT a.* FROM jurnal_pegawai a  WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
				AND a.id_pegawai = '$id_pegawai' Order by a.tanggal asc")->result_array();
				$data = [
					'judul' => $tahun,
					'jurnal_pegawai' => $jurnal_pegawai,
					'pegawai' => $pegawai,
					'status' => 'Tahun'
				];
			}
		}


		$this->load->view('admin/data_laporan/laporan_jurnal_kegiatan', $data);
	}

	public function getBulan($bulan)
	{
		$daftar_bulan = [
			1 => 'Januari',
			2 => 'Februari',
			3 => 'Maret',
			4 => 'April',
			5 => 'Mei',
			6 => 'Juni',
			7 => 'Juli',
			8 => 'Agustus',
			9 => 'September',
			10 => 'Oktober',
			11 => 'November',
			12 => 'Desember',
		];

		return $daftar_bulan[(int) $bulan] ?? 'Bulan tidak valid';
	}

}
