<?php

use SebastianBergmann\Environment\Console;

class Approval extends CI_Controller
{

	function __construct()
	{
		parent::__construct();


	}

	public function izin()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Approval Izin';
		$this->load->view('template/header', $data);
		$this->load->view('admin/approval/izin', $data);
		$this->load->view('template/footer');
	}
	public function jurnal_mengajar()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Approval Jurnal';
		$data['guru'] = $this->db->get('guru')->result_array();
		$data['periode'] = $this->db->get('master_tahun_ajaran')->result_array();
		$this->load->view('template/header', $data);
		$this->load->view('admin/approval/jurnal_mengajar', $data);
		$this->load->view('template/footer');
	}

	public function izin_result()
	{
		$search = $this->input->post('search');
		$this->db->from('izin_pegawai');
		$this->db->where('status_approval', 0);

		// Jika ada pencarian, tambahkan LIKE
		if (!empty($search)) {
			$this->db->like('nama_pegawai', $search);
		}

		$response = $this->db->get()->result_array();

		$grouped = [];
		foreach ($response as $item) {
			$grouped[$item['tgl_tidak_hadir']][] = $item;
		}

		echo json_encode($grouped);
	}
	public function jurnal_mengajar_result()
	{
		$tanggal_awal = $this->input->post('tanggal_awal');
		$tanggal_akhir = $this->input->post('tanggal_akhir');

		$whereGuru = [];
		$wherePegawai = [];
		$params = [];

		if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
			$whereGuru[] = "a.tanggal BETWEEN ? AND ?";
			$wherePegawai[] = "tanggal BETWEEN ? AND ?";
			$params[] = $tanggal_awal;
			$params[] = $tanggal_akhir;
			$params[] = $tanggal_awal;
			$params[] = $tanggal_akhir;
		} elseif (!empty($tanggal_awal)) {
			$whereGuru[] = "a.tanggal >= ?";
			$wherePegawai[] = "tanggal >= ?";
			$params[] = $tanggal_awal;
			$params[] = $tanggal_awal;
		} elseif (!empty($tanggal_akhir)) {
			$whereGuru[] = "a.tanggal <= ?";
			$wherePegawai[] = "tanggal <= ?";
			$params[] = $tanggal_akhir;
			$params[] = $tanggal_akhir;
		}

		$whereGuru[] = "a.status_approval = 0";
		$wherePegawai[] = "status_approval = 0";

		$sql = "  
			SELECT 
				a.id_guru, a.id, a.periode, a.semester, a.nama_guru, a.tanggal, 
				a.kegiatan, a.tema, 'guru' AS tipe, a.mapel, 
				a.jam_selesai_pelajaran, a.jam_mulai_pelajaran
			FROM jurnal_guru a
			" . (!empty($whereGuru) ? "WHERE " . implode(' AND ', $whereGuru) : "") . "
			
			UNION ALL
	
			SELECT 
				a.id_pegawai AS id_guru, a.id, a.periode, a.semester, 
				a.nama_pegawai AS nama_guru, a.tanggal, a.kegiatan, 
				'' AS tema, 'pegawai' AS tipe, '' AS mapel, 
				a.kegiatan AS jam_selesai_pelajaran, '' AS jam_mulai_pelajaran
			FROM jurnal_pegawai a
			" . (!empty($wherePegawai) ? "WHERE " . implode(' AND ', $wherePegawai) : "") . "
	
			ORDER BY tanggal DESC
		";



		$query = $this->db->query($sql, $params)->result_array();

		$grouped = [];
		foreach ($query as $item) {

			$grouped[$item['tanggal']][] = $item;
		}


		echo json_encode($grouped);


		// $grouped_by_date_guru = [];

		// foreach ($query as $item) {
		// 	$tanggal = $item['tanggal'];
		// 	$id_guru = $item['nama_guru'];

		// 	if (!isset($grouped_by_date_guru[$tanggal])) {
		// 		$grouped_by_date_guru[$tanggal] = [];
		// 	}

		// 	if (!isset($grouped_by_date_guru[$tanggal][$id_guru])) {
		// 		$grouped_by_date_guru[$tanggal][$id_guru] = [];
		// 	}

		// 	$grouped_by_date_guru[$tanggal][$id_guru][] = $item;
		// }



		// echo json_encode($grouped_by_date_guru);
	}

	public function jurnal_tanggal_result()
	{
		$this->db->select('jurnal_guru.*, kelas_jadwal_pelajaran.kelas,kelas.kode_kelas');
		$this->db->join('kelas_jadwal_pelajaran', 'kelas_jadwal_pelajaran.id = jurnal_guru.id_kelas_jadwal_pelajaran');
		$this->db->join('kelas', 'kelas.id = kelas_jadwal_pelajaran.id_kelas');
		$this->db->where('status_approval', 0);
		$this->db->where('jurnal_guru.tanggal', $this->input->post('tanggal'));
		$this->db->where('jurnal_guru.id_guru', $this->input->post('id_guru'));
		$this->db->order_by('tanggal', 'DESC');
		$response = $this->db->get('jurnal_guru')->result_array();

		echo json_encode($response);
	}
	public function jurnal_pegawai_result()
	{


		$this->db->where('status_approval', 0);
		$this->db->where('id', $this->input->post('id'));
		$this->db->order_by('tanggal', 'DESC');
		$response = $this->db->get('jurnal_pegawai')->row_array();

		echo json_encode($response);
	}

	public function update_approval()
	{
		$status = $this->input->post('status');


		$id_pegawai = $this->input->post('id_pegawai');
		$data_approval = [
			'status_approval' => $status
		];
		$this->db->where_in('id', $id_pegawai);
		$response = $this->db->update('izin_pegawai', $data_approval);
		if ($response) {
			$data = [
				'status' => true
			];
		}
		echo json_encode($data);
	}
	public function jurnal_mengajar_update()
	{

		$id_jurnal = $this->input->post('id_jurnal');
		$status = $this->input->post('tipe');



		if (!empty($id_jurnal) && is_array($id_jurnal) && is_array($status)) {
			foreach ($id_jurnal as $key => $value) {
				$tipe = isset($status[$key]) ? $status[$key] : null;

				if ($tipe === 'guru') {
					$this->db->where('id', $value);
					$this->db->update('jurnal_guru', ['status_approval' => $this->input->post('status')]);
				} elseif ($tipe === 'pegawai') {
					$this->db->where('id', $value);
					$this->db->update('jurnal_pegawai', ['status_approval' => $this->input->post('status')]);
				}
			}

			echo json_encode(['status' => true]);
		} else {
			echo json_encode(['status' => false]);
		}

	}

	public function hapus()
	{
		$data = $this->model->hapus();

		echo json_encode($data);
	}

}
?>