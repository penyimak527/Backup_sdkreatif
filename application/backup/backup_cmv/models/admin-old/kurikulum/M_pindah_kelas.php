<?php
class M_pindah_kelas extends CI_Model
{

	protected $id_user;
	function __construct()
	{
		parent::__construct();
		$id_user = 1;
	}


	public function pindah_kelas_result($id_kelas_kiri, $id_periode_kiri, $id_kelas_kanan, $id_periode_kanan)
	{


		$where_kelas_kiri = "";
		if ($id_kelas_kiri != "") {
			$where_kelas_kiri = $where_kelas_kiri . " AND (b.id_kelas_setting LIKE '$id_kelas_kiri')";
		}

		$where_periode_kiri = "";
		if ($id_periode_kiri != "") {
			$where_periode_kiri = $where_periode_kiri . " AND (b.id_periode LIKE '$id_periode_kiri')";
		}

		$where_kelas_kanan = "";
		if ($id_kelas_kanan != "") {
			$where_kelas_kanan = $where_kelas_kanan . " AND (b.id_kelas_setting LIKE '$id_kelas_kanan')";
		}

		$where_periode_kanan = "";
		if ($id_periode_kanan != "") {
			$where_periode_kanan = $where_periode_kanan . " AND (b.id_periode LIKE '$id_periode_kanan')";
		}

		$query_kiri = "";
		if ($id_kelas_kiri != "" && $id_periode_kiri != "") {
			$querykr = ($id_kelas_kanan != '') ? $where_kelas_kiri : '';
			$query_kiri = "AND a.id NOT IN (SELECT
										a.id as id_siswa
									FROM siswa a
									JOIN (
										SELECT
											a.*,
											a.id as id_kelas_siswa,
											b.id_kelas,
											b.id_periode,
											c.nama_kelas,
											d.periode
										FROM kelas_siswa a
										JOIN kelas_setting b ON a.id_kelas_setting = b.id
										JOIN kelas c ON b.id_kelas = c.id
										JOIN master_tahun_ajaran d ON b.id_periode = d.id
										WHERE a.status_aktif = '1'
									) b ON a.id = b.id_siswa
									WHERE 1=1
									$querykr
									$where_periode_kiri)";
		}

		$query_kanan = "";
		if ($id_kelas_kanan != "" && $id_periode_kanan != "") {
			$querykn = ($id_kelas_kiri != '') ? $where_kelas_kanan : '';
			$query_kanan = "AND a.id NOT IN (SELECT
										a.id as id_siswa
									FROM siswa a
									JOIN (
										SELECT
											a.*,
											a.id as id_kelas_siswa,
											b.id_kelas,
											b.id_periode,
											c.nama_kelas,
											d.periode
										FROM kelas_siswa a
										JOIN kelas_setting b ON a.id_kelas_setting = b.id
										JOIN kelas c ON b.id_kelas = c.id
										JOIN master_tahun_ajaran d ON b.id_periode = d.id
										WHERE a.status_aktif = 1
									) b ON a.id = b.id_siswa
									WHERE 1=1
									$querykn
									$where_periode_kanan)";
		}

		$sql_kiri = $this->db->query("SELECT
										a.*,
										a.nama_lengkap as nama_siswa,
										b.id_kelas_setting as id_kelas_kiri,
										b.id_periode as id_periode_kiri,
										b.id_kelas_siswa
									FROM siswa a
									LEFT JOIN (
										SELECT
											a.*,
											a.id as id_kelas_siswa,
											b.id_kelas,
											b.id_periode,
											c.nama_kelas,
											d.periode
										FROM kelas_siswa a
										JOIN kelas_setting b ON a.id_kelas_setting = b.id
										JOIN kelas c ON b.id_kelas = c.id
										JOIN master_tahun_ajaran d ON b.id_periode = d.id
										WHERE a.status_aktif = 1
									) b ON a.id = b.id_siswa
									WHERE 1=1
									$query_kanan
									$where_kelas_kiri
									$where_periode_kiri
									");

		$sql_kanan = $this->db->query("SELECT
										a.*,
										a.nama_lengkap as nama_siswa,
										b.id_kelas_setting as id_kelas_kanan,
										b.id_periode as id_periode_kanan,
										b.id_kelas_siswa
									FROM siswa a
									LEFT JOIN (
										SELECT
											a.*,
											a.id as id_kelas_siswa,
											b.id_kelas,
											b.id_periode,
											c.nama_kelas,
											d.periode
										FROM kelas_siswa a
										JOIN kelas_setting b ON a.id_kelas_setting = b.id
										JOIN kelas c ON b.id_kelas = c.id
										JOIN master_tahun_ajaran d ON b.id_periode = d.id
										WHERE a.status_aktif = '1'
									) b ON a.id = b.id_siswa
									WHERE 1=1
									$query_kiri
									$where_kelas_kanan
									$where_periode_kanan
									");

		return [
			'data_kiri' => $sql_kiri->result_array(),
			'data_kanan' => $sql_kanan->result_array()
		];
	}

	public function pindah_ke_kanan($id_kelas_setting, $checkbox_kiri)
	{
		foreach ($checkbox_kiri as $value) {
			$this->db->where('id_siswa', $value);
			$this->db->update('kelas_siswa', array('status_aktif' => 0));

			$row_siswa = $this->db->get_where('siswa', array('id' => $value))->row_array();

			$data = array(
				'id_kelas_setting' => $id_kelas_setting,
				'id_siswa' => $row_siswa['id'],
				'nama_siswa' => $row_siswa['nama_lengkap'],
				'nis' => $row_siswa['nis'],
				'jenis_kelamin' => $row_siswa['jk'],
				'status_aktif' => 1
			);

			$this->db->insert('kelas_siswa', $data);
		}
	}

	public function pindah_ke_kiri($id_kelas_setting, $checkbox_kanan)
	{
		foreach ($checkbox_kanan as $value) {
			$this->db->where('id_siswa', $value);
			$this->db->update('kelas_siswa', array('status_aktif' => 0));

			$row_siswa = $this->db->get_where('siswa', array('id' => $value))->row_array();

			$data = array(
				'id_kelas_setting' => $id_kelas_setting,
				'id_siswa' => $row_siswa['id'],
				'nama_siswa' => $row_siswa['nama_lengkap'],
				'nis' => $row_siswa['nis'],
				'jenis_kelamin' => $row_siswa['jk'],
				'status_aktif' => 1
			);

			$this->db->insert('kelas_siswa', $data);
		}
	}

}
?>