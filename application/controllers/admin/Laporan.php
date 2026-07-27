<?php
class Laporan extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Laporan';
		$data['guru'] = $this->db->get('guru')->result_array();
		$this->db->where('jabatan !=', 'Guru');
		$data['pegawai'] = $this->db->get('pegawai_jabatan')->result_array();
		$data['pegawai_all'] = $this->db->get('pegawai')->result_array();
		$data['periode'] = $this->db->get('master_tahun_ajaran')->result_array();
		$data['kelas'] = $this->db->get('kelas')->result_array();
		$data['view'] = $this;
		$this->load->view('template/header', $data);
		$this->load->view('admin/laporan', $data);
		$this->load->view('template/footer');
	}

	public function excel()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Laporan';
		$this->load->view('template/header', $data);
		$this->load->view('admin/data_laporan/excel/laporan_izin_pegawai', $data);
		$this->load->view('template/footer');
	}

	public function laporan_result()
	{
		$id_level = $this->session->userdata('admin')['id_level'];
		$search = $this->input->post('search');

		if ($search != null) {
			$this->db->where('group', 'Laporan');
			$this->db->where('id_level', $id_level);
			$this->db->like('name', $search); // pakai LIKE agar pencarian fleksibel
			$data = $this->db->get('conf_list_menu')->result_array();
		} else {
			$data = $this->db->get_where('conf_list_menu', [
				'group' => 'Laporan',
				'id_level' => $id_level
			])->result_array();
		}
		echo json_encode($data);
	}

	public function laporan_jurnal_pegawai()
	{
		$dari_tanggal = $this->input->post('dari_tanggal');
		$sampai_tanggal = $this->input->post('sampai_tanggal');
		$id_pegawai = $this->input->post('id_pegawai');
		$filter = $this->input->post('filter');

		if ($filter === 'tanggal' && (empty($dari_tanggal) || empty($sampai_tanggal))) {
			echo json_encode([]);
			return;
		}

		if ($filter == 'tanggal') {
			$sql = "
            SELECT a.*
            FROM jurnal_pegawai a
            WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y')
                  BETWEEN STR_TO_DATE(?, '%Y-%m-%d')
                      AND STR_TO_DATE(?, '%Y-%m-%d')
        ";
			$params = [$dari_tanggal, $sampai_tanggal];

			if ($id_pegawai !== '') {
				$sql .= " AND a.id_pegawai = ? ";
				$params[] = $id_pegawai;
			}

			$sql .= " ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC";
			$rows = $this->db->query($sql, $params)->result_array();

		} elseif ($filter == 'bulan') {
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');

			$sql = "
            SELECT a.*
            FROM jurnal_pegawai a
            WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?
              AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?
        ";
			$params = [$bulan, $tahun];

			if ($id_pegawai !== '') {
				$sql .= " AND a.id_pegawai = ? ";
				$params[] = $id_pegawai;
			}

			$sql .= " ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC";
			$rows = $this->db->query($sql, $params)->result_array();

		} else { // tahun
			$single_filter_tahun = $this->input->post('single_filter_tahun');

			$sql = "
            SELECT a.*
            FROM jurnal_pegawai a
            WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?
        ";
			$params = [$single_filter_tahun];

			if ($id_pegawai !== '') {
				$sql .= " AND a.id_pegawai = ? ";
				$params[] = $id_pegawai;
			}

			$sql .= " ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC";
			$rows = $this->db->query($sql, $params)->result_array();
		}

		if ($id_pegawai === '') {
			$grouped = [];
			foreach ($rows as $row) {
				$nama_pegawai = $row['nama_pegawai']; // d-m-Y
				if (!isset($grouped[$nama_pegawai]))
					$grouped[$nama_pegawai] = [];
				$grouped[$nama_pegawai][] = $row;
			}
			$data = $grouped;
		} else {
			$data = $rows;
		}



		echo json_encode($data);
	}

	public function laporan_jurnal_guru_kelas()
	{
		$dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));

		$filter = $this->input->post('filter');
		$id_kelas = $this->input->post('id_kelas');
		$periode = $this->input->post('periode');
		$semester = $this->input->post('semester');
		$bulan = $this->input->post('bulan');
		$tahun = $this->input->post('tahun');
		$single_filter_tahun = $this->input->post('single_filter_tahun');
		if ($filter == 'tanggal') {

			if ($id_kelas == 'Semua') {

				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			  left join kelas d on b.id_kelas = d.id
				WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
				AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') 
				AND a.id_periode = '$periode' 
				AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			} else {
				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
				 left join kelas d on b.id_kelas = d.id
				WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
				AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') 
				AND a.id_periode = '$periode' 
				AND b.id_kelas = '$id_kelas' 
				AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			}
			$grouped_by_guru_mapel = [];


			foreach ($jurnal_guru as $row) {
				$nama_guru = $row['nama_guru'];
				$mapel = $row['mapel'];
				if (!isset($grouped_by_guru_mapel[$nama_guru])) {
					$grouped_by_guru_mapel[$nama_guru] = [];
				}
				if (!isset($grouped_by_guru_mapel[$nama_guru][$mapel])) {
					$grouped_by_guru_mapel[$nama_guru][$mapel] = [];
				}
				$grouped_by_guru_mapel[$nama_guru][$mapel][] = $row;
			}
			$data = $grouped_by_guru_mapel;


		} else if ($filter == 'bulan') {


			if ($id_kelas == 'Semua') {

				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
		  left join kelas d on b.id_kelas = d.id
			WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$bulan' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun'  
			AND a.id_periode = '$periode' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			} else {
				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			 left join kelas d on b.id_kelas = d.id
			WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$bulan' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$tahun'   
			AND a.id_periode = '$periode' 
			AND b.id_kelas = '$id_kelas' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			}
			$grouped_by_guru_mapel = [];

			foreach ($jurnal_guru as $row) {
				$nama_guru = $row['nama_guru'];
				$mapel = $row['mapel'];
				if (!isset($grouped_by_guru_mapel[$nama_guru])) {
					$grouped_by_guru_mapel[$nama_guru] = [];
				}
				if (!isset($grouped_by_guru_mapel[$nama_guru][$mapel])) {
					$grouped_by_guru_mapel[$nama_guru][$mapel] = [];
				}
				$grouped_by_guru_mapel[$nama_guru][$mapel][] = $row;
			}
			$data = $grouped_by_guru_mapel;
		} else {

			if ($id_kelas == 'Semua') {

				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
		  left join kelas d on b.id_kelas = d.id
			WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$single_filter_tahun'  
			AND a.id_periode = '$periode' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			} else {
				$jurnal_guru = $this->db->query("SELECT a.*,d.nama_kelas,d.kode_kelas FROM jurnal_guru a left join kelas_jadwal_pelajaran b on a.id_kelas_jadwal_pelajaran = b.id
			 left join kelas d on b.id_kelas = d.id
			WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = '$single_filter_tahun'   
			AND a.id_periode = '$periode' 
			AND b.id_kelas = '$id_kelas' 
			AND a.semester = '$semester' Order by a.jam_mulai_pelajaran asc")->result_array();
			}
			$grouped_by_guru_mapel = [];

			foreach ($jurnal_guru as $row) {
				$nama_guru = $row['nama_guru'];
				$mapel = $row['mapel'];
				if (!isset($grouped_by_guru_mapel[$nama_guru])) {
					$grouped_by_guru_mapel[$nama_guru] = [];
				}
				if (!isset($grouped_by_guru_mapel[$nama_guru][$mapel])) {
					$grouped_by_guru_mapel[$nama_guru][$mapel] = [];
				}
				$grouped_by_guru_mapel[$nama_guru][$mapel][] = $row;
			}
			$data = $grouped_by_guru_mapel;
		}
		echo json_encode($data);
	}

public function laporan_resume_tanggal_kegiatan_belum_diisi()
{
    // --- Ambil input ---
    $dari_tanggal        = $this->input->post('dari_tanggal') ? date('d-m-Y', strtotime($this->input->post('dari_tanggal'))) : null;
    $sampai_tanggal      = $this->input->post('sampai_tanggal') ? date('d-m-Y', strtotime($this->input->post('sampai_tanggal'))) : null;
    $filter              = $this->input->post('filter');           
    $periode             = $this->input->post('periode');          
    $semester            = $this->input->post('semester');          
    $bulan               = $this->input->post('bulan');
    $tahun               = $this->input->post('tahun');
    $single_filter_tahun = $this->input->post('single_filter_tahun');

    // --- Master pegawai (non-Guru) ---
    $sqlPegawai = "
        SELECT a.nama_pegawai
        FROM pegawai a
        LEFT JOIN pegawai_jabatan b ON a.id = b.id_pegawai
        WHERE (b.jabatan IS NULL OR b.jabatan <> 'Guru')
    ";
    $pegRows = $this->db->query($sqlPegawai)->result_array();
    $daftarPegawai = array_map(fn($x) => trim($x['nama_pegawai']), $pegRows);

    // --- Bangun allDates sesuai filter ---
    $allDates = [];
    if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
        $start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
        $end   = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
        if ($start && $end && $start <= $end) {
            $endInc = (clone $end)->modify('+1 day');
            $period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
            foreach ($period as $dt) $allDates[] = $dt->format('d-m-Y');
        }
        $periode_label = $dari_tanggal . ' s.d. ' . $sampai_tanggal;
    } elseif ($filter === 'bulan' && $bulan && $tahun) {
        $hari = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
        for ($d = 1; $d <= $hari; $d++) $allDates[] = sprintf('%02d-%02d-%04d', $d, $bulan, $tahun);
        $periode_label = sprintf('%02d-%04d', $bulan, $tahun);
    } else {
        $th = (int)($single_filter_tahun ?: $tahun);
        for ($m = 1; $m <= 12; $m++) {
            $hari = cal_days_in_month(CAL_GREGORIAN, $m, $th);
            for ($d = 1; $d <= $hari; $d++) $allDates[] = sprintf('%02d-%02d-%04d', $d, $m, $th);
        }
        $periode_label = (string)$th;
    }

    if (empty($allDates)) {
        echo json_encode(['data' => new stdClass(), 'meta' => ['kelas' => '-', 'semester' => ($semester ?: '-'), 'periode' => ($periode_label ?: '-')]]);
        return;
    }
 
    $params = [];
    $where  = [];

    if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
        $where[]  = "STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')";
        $params[] = $dari_tanggal;
        $params[] = $sampai_tanggal;
    } elseif ($filter === 'bulan' && $bulan && $tahun) {
        $where[]  = "MONTH(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
        $where[]  = "YEAR(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
        $params[] = $bulan;
        $params[] = $tahun;
    } else {
        $th = (int)($single_filter_tahun ?: $tahun);
        $where[]  = "YEAR(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
        $params[] = $th;
    }

    // jika perlu filter periode/semester dari kolom jurnal_pegawai, aktifkan baris berikut:
    if (!empty($periode))  { $where[] = "a.periode = ?";  $params[] = $periode; }
    if (!empty($semester)) { $where[] = "a.semester = ?"; $params[] = $semester; }

    $sqlRows = "
        SELECT a.nama_pegawai,
               a.tanggal_input,
               a.tanggal,
               a.semester,
               a.periode
        FROM jurnal_pegawai a
        " . (empty($where) ? "" : "WHERE " . implode(" AND ", $where)) . "
        ORDER BY STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
    ";
    $rows = $this->db->query($sqlRows, $params)->result_array();

    // --- Tandai tanggal yang sudah diisi per pegawai ---
    $filled = []; // [nama_pegawai => ['dd-mm-YYYY' => true]]
    $meta   = []; // [nama_pegawai => ['semester'=>'..','periode'=>'..']]
    foreach ($rows as $r) {
        $raw = $r['tanggal_input'] ?: $r['tanggal'];
        if (!$raw) continue;
        $dt = DateTime::createFromFormat('d-m-Y', trim($raw));
        if (!$dt) continue;

        $tgl  = $dt->format('d-m-Y');
        $nama = trim($r['nama_pegawai']);
        if ($nama === '') continue;

        $filled[$nama][$tgl] = true;
        // simpan meta sederhana (boleh diubah ke "pertama" atau "terakhir")
        $meta[$nama]['semester'] = $r['semester'] ?? ($semester ?: '-');
        $meta[$nama]['periode']  = $r['periode']  ?? $periode_label;
    }
 
    $missing = [];  
    $semester_label = $semester ?: '-';

    foreach ($daftarPegawai as $nama) {
        $nama   = trim($nama);
        $sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
        $kosong = array_values(array_diff($allDates, $sudah));
        sort($kosong);

        if (!empty($kosong)) { // tampilkan HANYA yang masih punya kekosongan
            $missing[$nama] = [
                'semester' => $meta[$nama]['semester'] ?? $semester_label,
                'periode'  => $meta[$nama]['periode']  ?? $periode_label,
                'tanggal'  => $kosong,
            ];
        }
    }
 
    $resp = [
        'data' => $missing,
        'meta' => [
            'kelas'    => '-',  
            'semester' => $semester_label,
            'periode'  => $periode_label,
        ],
    ];

    if (empty($missing)) {
        echo json_encode(['data' => new stdClass(), 'meta' => $resp['meta']]);
        return;
    }

    echo json_encode($resp);
}

public function laporan_resume_tanggal_guru_belum_diisi()
{
    // --- Ambil input ---
    $dari_tanggal        = $this->input->post('dari_tanggal') ? date('d-m-Y', strtotime($this->input->post('dari_tanggal'))) : null;
    $sampai_tanggal      = $this->input->post('sampai_tanggal') ? date('d-m-Y', strtotime($this->input->post('sampai_tanggal'))) : null;
    $filter              = $this->input->post('filter');           // 'tanggal' | 'bulan' | 'tahun'
    $periode             = $this->input->post('periode');          // a.id_periode (opsional)
    $semester            = $this->input->post('semester');         // label/kode semester (opsional)
    $bulan               = $this->input->post('bulan');
    $tahun               = $this->input->post('tahun');
    $single_filter_tahun = $this->input->post('single_filter_tahun');

    // --- Master GURU (jabatan = 'Guru') ---
    $sqlGuru = "
        SELECT a.nama_pegawai AS nama_guru
        FROM pegawai a
        LEFT JOIN pegawai_jabatan b ON a.id = b.id_pegawai
        WHERE b.jabatan = 'Guru'
    ";
    $guruRows   = $this->db->query($sqlGuru)->result_array();
    $daftarGuru = array_map(fn($x) => trim($x['nama_guru']), $guruRows);

    // --- Bangun allDates sesuai filter ---
    $allDates = [];
    if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
        $start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
        $end   = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
        if ($start && $end && $start <= $end) {
            $endInc = (clone $end)->modify('+1 day');
            $period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
            foreach ($period as $dt) $allDates[] = $dt->format('d-m-Y');
        }
        $periode_label = $dari_tanggal . ' s.d. ' . $sampai_tanggal;
    } elseif ($filter === 'bulan' && $bulan && $tahun) {
        $hari = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
        for ($d = 1; $d <= $hari; $d++) $allDates[] = sprintf('%02d-%02d-%04d', $d, $bulan, $tahun);
        $periode_label = sprintf('%02d-%04d', $bulan, $tahun);
    } else {
        $th = (int)($single_filter_tahun ?: $tahun);
        for ($m = 1; $m <= 12; $m++) {
            $hari = cal_days_in_month(CAL_GREGORIAN, $m, $th);
            for ($d = 1; $d <= $hari; $d++) $allDates[] = sprintf('%02d-%02d-%04d', $d, $m, $th);
        }
        $periode_label = (string)$th;
    }

    if (empty($allDates)) {
        echo json_encode(['data' => new stdClass(), 'meta' => ['kelas' => '-', 'semester' => ($semester ?: '-'), 'periode' => ($periode_label ?: '-')]]); 
        return;
    }

    // --- Query jurnal_guru + kelas, sesuai filter (pakai COALESCE tanggal_input/tanggal) ---
    $params = [];
    $where  = [];

    if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
        $where[]  = "STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')";
        $params[] = $dari_tanggal;
        $params[] = $sampai_tanggal;
    } elseif ($filter === 'bulan' && $bulan && $tahun) {
        $where[]  = "MONTH(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
        $where[]  = "YEAR(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
        $params[] = $bulan;
        $params[] = $tahun;
    } else {
        $th = (int)($single_filter_tahun ?: $tahun);
        $where[]  = "YEAR(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
        $params[] = $th;
    }

    if (!empty($periode))  { $where[] = "a.id_periode = ?"; $params[] = $periode; }
    if (!empty($semester)) { $where[] = "a.semester   = ?"; $params[] = $semester; }

    $sqlRows = "
        SELECT a.nama_guru,
               a.tanggal_input,
               a.tanggal,
               a.semester,
               a.id_periode,
               a.mapel,
               a.id_kelas,
               k.nama_kelas,
               k.kode_kelas
        FROM jurnal_guru a
        LEFT JOIN kelas k ON a.id_kelas = k.id
        " . (empty($where) ? "" : "WHERE " . implode(" AND ", $where)) . "
        ORDER BY STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
    ";
    $rows = $this->db->query($sqlRows, $params)->result_array();

    // --- Tandai tanggal yang sudah diisi per guru + simpan meta (mapel/kelas) ---
    $filled = []; // [nama_guru => ['dd-mm-YYYY' => true]]
    $meta   = []; // [nama_guru => ['semester','periode','mapel','nama_kelas','kode_kelas']]
    foreach ($rows as $r) {
        $raw = $r['tanggal_input'] ?: $r['tanggal'];
        if (!$raw) continue;

        $dt = DateTime::createFromFormat('d-m-Y', trim($raw));
        if (!$dt) continue;

        $tgl  = $dt->format('d-m-Y');
        $nama = trim($r['nama_guru']);
        if ($nama === '') continue;

        $filled[$nama][$tgl] = true;

        // Ambil meta. Jika guru mengajar banyak mapel/kelas, di sini akan “mengambil salah satu”
        // (terakhir yang ditemui). Jika perlu per-mapel/per-kelas, perlu dipecah per kombinasi.
        $meta[$nama]['semester']   = $r['semester']   ?? ($semester ?: '-');
        $meta[$nama]['periode']    = $periode_label;
        $meta[$nama]['mapel']      = $r['mapel']      ?? '-';
        $meta[$nama]['nama_kelas'] = $r['nama_kelas'] ?? '-';
        $meta[$nama]['kode_kelas'] = $r['kode_kelas'] ?? '-';
    }

    // --- Hitung tanggal KOSONG per guru master ---
    $missing = [];  
    $semester_label = $semester ?: '-';

    foreach ($daftarGuru as $nama) {
        $nama   = trim($nama);
        $sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
        $kosong = array_values(array_diff($allDates, $sudah));
        sort($kosong);

        if (!empty($kosong)) {
            $missing[$nama] = [
                'semester'   => $meta[$nama]['semester']   ?? $semester_label,
                'periode'    => $meta[$nama]['periode']    ?? $periode_label,
                'mapel'      => $meta[$nama]['mapel']      ?? '-',
                'nama_kelas' => $meta[$nama]['nama_kelas'] ?? '-',
                'kode_kelas' => $meta[$nama]['kode_kelas'] ?? '-',
                'tanggal'    => $kosong,
            ];
        }
    }

    // --- Meta header (opsional) ---
    $resp = [
        'data' => $missing,
        'meta' => [
            'kelas'    => '-',             // laporan agregat; bukan per kelas spesifik
            'semester' => $semester_label,
            'periode'  => $periode_label,
        ],
    ];

    echo json_encode( empty($missing) ? ['data' => new stdClass(), 'meta' => $resp['meta']] : $resp );
}


	public function laporan_izin_pegawai()
	{
		$dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));


		$filter = $this->input->post('filter');


		if ($filter == 'tanggal') {

			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  
					WHERE STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')   Order by a.tgl_tidak_hadir asc")->result_array();
			$grouped_by_tanggal = [];

			foreach ($pegawai as $data) {
				$tanggal = $data['tgl_tidak_hadir'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}

			$data = $grouped_by_tanggal;
		} else if ($filter == 'bulan') {
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');

			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  WHERE MONTH(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $tahun
	  Order by a.tgl_tidak_hadir asc")->result_array();
      $grouped_by_tanggal = [];
			foreach ($pegawai as $data) {
				$tanggal = $data['tgl_tidak_hadir'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}
			$data = $grouped_by_tanggal;
		} else {
			$tahun = $this->input->post('single_filter_tahun');
			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  WHERE YEAR(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $tahun
			Order by a.tgl_tidak_hadir asc")->result_array();
            $grouped_by_tanggal = [];
			foreach ($pegawai as $data) {
				$tanggal = $data['tgl_tidak_hadir'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}

			$data = $grouped_by_tanggal;
		}

		echo json_encode($data);
	}
public function laporan_presensi_pegawai()
	{
		$dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));
		$filter = $this->input->post('filter');
		if ($filter == 'tanggal') {
			$tampil = $this->input->post('tampil');
			$pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan")->result_array();
			// 			$grouped_by_tanggal = [];
			// $start = strtotime($dari_tanggal);
// $end = strtotime($sampai_tanggal);
// for ($current=$start; $current <= $end ; $current++) { 
// 	$tanggal = date('d-m-Y', $current);
// 	foreach ($pegawai_list as $pegawai) {
// 		$presensi = $this->db->query("SELECT * FROM presensi_pegawai WHERE id_pegawai = ? AND tanggal = ?", [$pegawai['id_pegawai'], $tanggal])->row_array();
// 		 $grouped_by_tanggal[$tanggal][] = [
//             'nama_pegawai' => $pegawai['nama_pegawai'],
//             'jabatan' => $pegawai['jabatan'],
//             'status' => $presensi ? '1' : '0',
//             'jam_masuk' => $presensi['waktu'] ?? null,
//             'jam_pulang' => $presensi['jam_pulang'] ?? null,
//             'status_absen' => $presensi['status'] ?? null,
//         ];
// 	}
// }
$grouped_by_tanggal = [];
if($tampil == 'tampil' || $tampil == ''){
$pegawai_map = [];
    foreach ($pegawai_list as $p) {
        $pegawai_map[$p['id_pegawai']] = $p;
    }

    $presensi_all = $this->db->query("
        SELECT * FROM presensi_pegawai
        WHERE STR_TO_DATE(tanggal,'%d-%m-%Y')
        BETWEEN STR_TO_DATE(?,'%d-%m-%Y')
        AND STR_TO_DATE(?,'%d-%m-%Y')
    ", [$dari_tanggal, $sampai_tanggal])->result_array();

    $presensi_map = [];
    foreach ($presensi_all as $p) {
        $presensi_map[$p['tanggal']][$p['id_pegawai']] = $p;
    }

    
    $start = strtotime($dari_tanggal);
    $end = strtotime($sampai_tanggal);

    for ($current = $start; $current <= $end; $current = strtotime('+1 day', $current)) {
        $tanggal = date('d-m-Y', $current);

        foreach ($pegawai_map as $id => $pegawai) {
            $presensi = $presensi_map[$tanggal][$id] ?? null;

            $grouped_by_tanggal[$tanggal][] = [
                'nama_pegawai' => $pegawai['nama_pegawai'],
                'jabatan' => $pegawai['jabatan'],
                'status' => $presensi ? '1' : '0',
                'jam_masuk' => $presensi['waktu'] ?? null,
				'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
                'jam_pulang' => $presensi['jam_pulang'] ?? null,
                'status_absen' => $presensi['status'] ?? null,
				'tampil_pegawai' => 'tampil'
            ];
        }
    }
}else {
	$presensi = $this->db->query("SELECT a.* FROM presensi_pegawai a WHERE STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') ORDER BY a.id asc")->result_array();
foreach ($presensi as $key => $data) {
		$tanggal = $data['tanggal'];
$grouped_by_tanggal[$tanggal][] = [
            'nama_pegawai' => $data['nama_pegawai'],
            'jabatan' => $data['jabatan'],
            'status' => $data ? '1' : '0',
            'jam_masuk' => $data['waktu'] ?? null,
			'jam_terlambat' => $data['jam_terlambat'] ?? null,
            'jam_pulang' => $data['jam_pulang'] ?? null,
            'status_absen' => $data['status'] ?? null,
			'tampil_pegawai' => 'absen'
        ];	
}
}
			$data = $grouped_by_tanggal;
		} else if ($filter == 'bulan') {
			$bulan = sprintf("%02d", $this->input->post('bulan'));
$tahun = $this->input->post('tahun');
$tampil = $this->input->post('tampil');
$grouped_by_tanggal = [];
if ($tampil == 'tampil' || $tampil == '') {
$pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan")->result_array();
$pegawai_map = [];
foreach ($pegawai_list as $p) {
    $pegawai_map[$p['id_pegawai']] = $p;
}

$presensi_all = $this->db->query("
    SELECT * FROM presensi_pegawai
    WHERE MONTH(STR_TO_DATE(tanggal,'%d-%m-%Y')) = ?
      AND YEAR(STR_TO_DATE(tanggal,'%d-%m-%Y')) = ?
", [$bulan, $tahun])->result_array();

$presensi_map = [];
foreach ($presensi_all as $p) {
    $presensi_map[$p['tanggal']][$p['id_pegawai']] = $p;
}
$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
for ($i = 1; $i <= $total_days; $i++) {
    $hari = sprintf("%02d", $i);
    $tanggal = "$hari-$bulan-$tahun";

    foreach ($pegawai_map as $id => $pegawai) {
        $presensi = $presensi_map[$tanggal][$id] ?? null;
        $grouped_by_tanggal[$tanggal][] = [
            'nama_pegawai' => $pegawai['nama_pegawai'],
            'jabatan' => $pegawai['jabatan'],
            'status' => $presensi ? '1' : '0',
            'jam_masuk' => $presensi['waktu'] ?? null,
			'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
            'jam_pulang' => $presensi['jam_pulang'] ?? null,
            'status_absen' => $presensi['status'] ?? null,
			'tampil_pegawai' => 'tampil'
        ];
    }
}
	
}else{
$presensi = $this->db->query("SELECT a.* FROM presensi_pegawai a WHERE MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ? AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ? ORDER BY a.id asc", [$bulan, $tahun])->result_array();
foreach ($presensi as $key => $data) {
		$tanggal = $data['tanggal'];
$grouped_by_tanggal[$tanggal][] = [
            'nama_pegawai' => $data['nama_pegawai'],
            'jabatan' => $data['jabatan'],
            'status' => $data ? '1' : '0',
            'jam_masuk' => $data['waktu'] ?? null,
			'jam_terlambat' => $data['jam_terlambat'] ?? null,
            'jam_pulang' => $data['jam_pulang'] ?? null,
            'status_absen' => $data['status'] ?? null,
			'tampil_pegawai' => 'absen'
        ];	
}
}
$data =   $grouped_by_tanggal;
		} else {
			$tahun = $this->input->post('single_filter_tahun');
			$tampil = $this->input->post('tampil');
$start = new DateTime("01-01-$tahun");
$end   = new DateTime("31-12-$tahun");
$end->modify('+1 day'); // agar 31-12 ikut
$pegawai_list = $this->db->query("SELECT * FROM pegawai_jabatan ")->result_array();
$presensi_raw = $this->db->query("
    SELECT id_pegawai, tanggal, waktu, jam_terlambat, jam_pulang, status
    FROM presensi_pegawai
    WHERE tanggal LIKE '%-$tahun'
")->result_array();
$presensi_index = [];
foreach ($presensi_raw as $p) {
    $presensi_index[$p['tanggal']][$p['id_pegawai']] = $p;
}

$grouped_by_tanggal = [];
if ($tampil == 'tampil' || $tampil == '') {
	$period = new DatePeriod($start, new DateInterval("P1D"), $end);
	foreach ($period as $date) {
    $tanggal = $date->format('d-m-Y');

    foreach ($pegawai_list as $pegawai) {
        $presensi = $presensi_index[$tanggal][$pegawai['id_pegawai']] ?? null;

        $grouped_by_tanggal[$tanggal][] = [
            'nama_pegawai'  => $pegawai['nama_pegawai'],
            'jabatan'       => $pegawai['jabatan'],
            'status'        => $presensi ? '1' : '0',
            'jam_masuk'     => $presensi['waktu'] ?? null,
            'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
            'jam_pulang'    => $presensi['jam_pulang'] ?? null,
            'status_absen'  => $presensi['status'] ?? null,
			'tampil_pegawai' => 'tampil'
        ];
    }
}

// foreach ($period as $date) {
// 	$tanggal = $date->format('d-m-Y');
// 	foreach ($pegawai_list as $pegawai) {
// 		$presensi = $this->db->query("SELECT * FROM presensi_pegawai WHERE id_pegawai = ? AND tanggal = ?", [$pegawai['id_pegawai'], $tanggal])->row_array();
// 		$grouped_by_tanggal[$tanggal][] = [
// 			 'nama_pegawai' => $pegawai['nama_pegawai'],
//             'jabatan' => $pegawai['jabatan'],
//             'status' => $presensi ? '1' : '0',
//             'jam_masuk' => $presensi['waktu'] ?? null,
// 			'jam_terlambat' => $presensi['jam_terlambat'] ?? null,
//             'jam_pulang' => $presensi['jam_pulang'] ?? null,
//             'status_absen' => $presensi['status'] ?? null
// 		];
// 	}
// 	}
}else {
	$presensi = $this->db->query("SELECT a.* FROM presensi_pegawai a WHERE YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun ORDER BY a.id asc")->result_array();
	foreach ($presensi as $key => $data) {
		$tanggal = $data['tanggal'];
$grouped_by_tanggal[$tanggal][] = [
            'nama_pegawai' => $data['nama_pegawai'],
            'jabatan' => $data['jabatan'],
            'status' => $data ? '1' : '0',
            'jam_masuk' => $data['waktu'] ?? null,
			'jam_terlambat' => $data['jam_terlambat'] ?? null,
            'jam_pulang' => $data['jam_pulang'] ?? null,
            'status_absen' => $data['status'] ?? null,
			'tampil_pegawai' => 'absen'
        ];	
}
}
			$data = $grouped_by_tanggal;
		}

		echo json_encode($data);
	}

	public function laporan_presensi_per_pegawai()
	{
		$dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));
		$id_pegawai =  $this->input->post('id_pegawai');
		$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan WHERE id_pegawai = ?", [$id_pegawai])->row_array();
		$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
		$jabatan = $pegawai['jabatan'] ?? '-';

		$filter = $this->input->post('filter');
		if ($filter == 'tanggal') {
$grouped_by_tanggal = [];
$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan WHERE id_pegawai = ? ",[$id_pegawai])->row_array();
$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
$jabatan = $pegawai['jabatan'] ?? '-';
$start = strtotime($dari_tanggal);
			$end = strtotime($sampai_tanggal);
for ($ulang=$start; $ulang <= $end; $ulang = strtotime('+1 day', $ulang)) { 
	$tanggal = date('d-m-Y', $ulang);
	$presensi_pegawai = $this->db->query("SELECT a.*, b.* FROM pegawai_jabatan a LEFT JOIN presensi_pegawai b on b.id_pegawai = a.id_pegawai WHERE a.id_pegawai = ? AND b.tanggal = ?", [$id_pegawai, $tanggal])->row_array();
	if (empty($presensi_pegawai) || empty($presensi_pegawai['tanggal'])) {
			$grouped_by_tanggal[$tanggal] = [
						'status' => '0',
						'tanggal' => $tanggal,
						'nama_pegawai' => $nama_pegawai,
						'jam_masuk' => null,
						'jam_terlambat' => null,
						'jam_pulang' => null,
						'status_absen' => 'Tidak Absen'
					];
	} else {
		$grouped_by_tanggal[$tanggal] = [
						'status' => '1',
						'tanggal' => $presensi_pegawai['tanggal'],
						'nama_pegawai' => $presensi_pegawai['nama_pegawai'],
						'jam_masuk' => $presensi_pegawai['waktu'] ?? null,
						'jam_terlambat' => $presensi_pegawai['jam_terlambat'] ?? null,
						'jam_pulang' => $presensi_pegawai['jam_pulang'] ?? null,
						'status_absen' => $presensi_pegawai['status'] ?? null,
					];
	}
	
}
			$response = [
    'meta' => [
        'nama_pegawai' => $nama_pegawai,
        'jabatan'      => $jabatan
    ],
    'data' => $grouped_by_tanggal
];

		} else if ($filter == 'bulan') {
			$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan WHERE id_pegawai = ?", [$id_pegawai])->row_array();
		$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
		$jabatan = $pegawai['jabatan'] ?? '-';
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
			$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$grouped_by_tanggal = [];
for ($i=1; $i <= $total_days; $i++) { 
					$hari = sprintf("%02d", $i);
				$tanggal = $hari . '-' . $bulan . '-' . $tahun;

				$presensi_pegawai = $this->db->query("
        SELECT a.*, b.*
        FROM pegawai_jabatan a LEFT JOIN presensi_pegawai b on b.id_pegawai = a.id_pegawai
        WHERE a.id_pegawai = ?
          AND b.tanggal = ?
    ", [$id_pegawai, $tanggal])->row_array();

				if (empty($presensi_pegawai)) {
					$grouped_by_tanggal[$tanggal] = [
						'status' => '0',
						'tanggal' => $tanggal
					];
				} else {
					$grouped_by_tanggal[$tanggal] = [
						'status' => '1',
						'tanggal' => $presensi_pegawai['tanggal'],
						'nama_pegawai' => $presensi_pegawai['nama_pegawai'],
						'jam_masuk' => $presensi_pegawai['waktu'],
						'jam_terlambat' => $presensi_pegawai['jam_terlambat'],
						'jam_pulang' => $presensi_pegawai['jam_pulang'],
						'status_absen' => $presensi_pegawai['status'],
					];
				}
}
			$response = [
    'meta' => [
        'nama_pegawai' => $nama_pegawai,
        'jabatan'      => $jabatan
    ],
    'data' => $grouped_by_tanggal
];

		} else {
			$pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan WHERE id_pegawai = ?", [$id_pegawai])->row_array();
		$nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
		$jabatan = $pegawai['jabatan'] ?? '-';
			$tahun = $this->input->post('single_filter_tahun');
		$grouped_by_tanggal = [];
		for ($bulan=1; $bulan <= 12; $bulan++) { 
			$bulan_formatted = sprintf("%02d", $bulan);
			$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
			for ($hari=1; $hari <= $total_days ; $hari++) { 
				$hari_formatted = sprintf('%02d', $hari);
				$tanggal = $hari_formatted . '-' . $bulan_formatted . '-' . $tahun;
				$presensi_pegawai = $this->db->query("SELECT a.*, b.* FROM pegawai_jabatan a LEFT JOIN presensi_pegawai b on b.id_pegawai = a.id_pegawai WHERE a.id_pegawai = ? AND b.tanggal = ? ", [$id_pegawai, $tanggal])->row_array();
				if (empty($presensi_pegawai)) {
					$grouped_by_tanggal[$tanggal] = [
						  'status' => '0',
                    'tanggal' => $tanggal,
                    'nama_pegawai' => $nama_pegawai,
                    'jam_masuk' => null,
                    'jam_terlambat' => null,
                    'jam_pulang' => null,
                    'status_absen' => 'Tidak Absen'
					];
				} else {
					$grouped_by_tanggal[$tanggal] = [
                    'status' => '1',
                    'tanggal' => $presensi_pegawai['tanggal'],
                    'nama_pegawai' => $presensi_pegawai['nama_pegawai'],
                    'jam_masuk' => $presensi_pegawai['waktu'] ?? null,
                    'jam_terlambat' => $presensi_pegawai['jam_terlambat'] ?? null,
                    'jam_pulang' => $presensi_pegawai['jam_pulang'] ?? null,
                    'status_absen' => $presensi_pegawai['status'] ?? null,
                ];
				}
				 
			
		}}
				$response = [
    'meta' => [
        'nama_pegawai' => $nama_pegawai,
        'jabatan'      => $jabatan
    ],
    'data' => $grouped_by_tanggal
];
		}
		echo json_encode($response);
	}
	public function laporan_rekap_keterlambatan_pegawai()
	{
		$dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));


		$filter = $this->input->post('filter');
		if ($filter == 'tanggal') {
			$presensi_pegawai = $this->db->query("SELECT a.* FROM presensi_pegawai a  
					WHERE a.status = 'Terlambat' AND STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')   Order by a.tanggal asc")->result_array();
			$grouped_by_tanggal = [];

			foreach ($presensi_pegawai as $data) {
				$tanggal = $data['tanggal'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}

			$data = $grouped_by_tanggal;
		} else if ($filter == 'bulan') {
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
			$presensi_pegawai = $this->db->query("SELECT a.* FROM presensi_pegawai a  WHERE a.status = 'Terlambat' AND MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
	  		Order by a.tanggal asc")->result_array();
			$grouped_by_tanggal = [];
			foreach ($presensi_pegawai as $data) {
				$tanggal = $data['tanggal'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}
			$data = $grouped_by_tanggal;
		} else {
			$tahun = $this->input->post('single_filter_tahun');
			$presensi_pegawai = $this->db->query("SELECT a.* FROM presensi_pegawai a  WHERE a.status = 'Terlambat' AND YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = $tahun
			Order by a.tanggal asc")->result_array();
			$grouped_by_tanggal = [];
			foreach ($presensi_pegawai as $data) {
				$tanggal = $data['tanggal'];
				if (!isset($grouped_by_tanggal[$tanggal])) {
					$grouped_by_tanggal[$tanggal] = [];
				}
				$grouped_by_tanggal[$tanggal][] = $data;
			}

			$data = $grouped_by_tanggal;
		}

		echo json_encode($data);
	}

	public function laporan_rekapitulasi_gaji()
	{
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
			$hari_kerja = 12;
        $this->db->select('a.id as id_pegawai, a.nama_pegawai, b.id as id_gaji, b.gaji_pokok, 
		b.struktural, b.tunjangan_pendidikan, b.wali_kelas');
        $this->db->from('pegawai a');
        $this->db->join('gaji b', 'a.id = b.id_pegawai', 'left');
        // $this->db->join('potongan_pegawai c', 'a.id = c.id_pegawai', 'left');
        $pegawai = $this->db->get()->result_array();

		$master_potongan = $this->db->order_by('id', 'ASC')->get('master_potongan')->result_array();
		$potongan = [];
		$result = [];
        foreach ($pegawai as $item) {
            $cek_penggajian = $this->db->get_where('penggajian', [
                'id_pegawai' => $item['id_pegawai'],
                'bulan' => $bulan,
                'tahun' => $tahun
            ])->row_array();
            if ($cek_penggajian) {
                $potongan_detail = $this->db->query("
    SELECT
        mp.id,
        mp.nama_potongan,
        pp.nominal
    FROM penggajian_potongan pp
    JOIN master_potongan mp
        ON mp.id = pp.id_master_potongan
    WHERE pp.id_penggajian = ?
", [$cek_penggajian['id']])->result_array();

                $potongan = [];

                // default semua 0
                foreach ($master_potongan as $mp) {
                    $potongan[$mp['id']] = 0;
                }

                // isi yang ada nilainya
                foreach ($potongan_detail as $pd) {
                    $potongan[$pd['id']] = $pd['nominal'];
                }
                $result[] = [
                    'nama_pegawai' => $item['nama_pegawai'],
                    'gaji_pokok' => $cek_penggajian['gaji_pokok'],

                    'struktural' => $cek_penggajian['struktural'],
                    'tunjangan_pendidikan' => $cek_penggajian['tunjangan_pendidikan'],
                    'wali_kelas' => $cek_penggajian['wali_kelas'],
                    'bonus' => $cek_penggajian['total_bonus'],

                    // 'total_pendapatan' => $cek_penggajian['total_pendapatan'],
                    'total_pendapatan' => $cek_penggajian['total_pendapatan'] + $cek_penggajian['total_bonus'],
                    'jumlah_hadir' => $cek_penggajian['jumlah_hadir'],
                    'jumlah_tidak_hadir' => $cek_penggajian['jumlah_tidak_hadir'],
                    'potongan_tidak_hadir' => $cek_penggajian['potongan_tidak_hadir'],
                    'uig_uik' => $cek_penggajian['uig_uik'],
                    'zakat' => $cek_penggajian['zakat'],
                    'potongan' => $potongan,
                    'cicilan_pinjaman' => $cek_penggajian['cicilan_pinjaman'],
                    'total_pengeluaran' => $cek_penggajian['total_pengeluaran'],
                    'gaji_bersih' => $cek_penggajian['gaji_bersih'],

                    'persen_potongan_tidak_hadir' => $cek_penggajian['persen_potongan_tidak_hadir'],
                    'persen_uig_uik' => $cek_penggajian['persen_uig_uik'],
                    'persen_zakat' => $cek_penggajian['persen_zakat'],
                ];
            }
        }
		
			$data = [
				'data_laporan' => $result,
				'master_potongan' => $master_potongan
			];
		echo json_encode($data);
	}

	public function laporan_kas()
	{
        $dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));
		$filter = $this->input->post('filter');
		if ($filter == 'tanggal') {
			$tahun = date('Y', strtotime($dari_tanggal));

		// 	$saldo_awal = $this->db->query("
		//     SELECT nominal 
		//     FROM saldo_awal 
		//     WHERE tahun = '$tahun'
		// 	ORDER BY bulan ASC
		// ")->row_array()['nominal'] ?? 0;
			// saldo_awal sebelum tahun dipilih
    $saldo_awal_lama = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun < '$tahun'
    ")->row()->total;

    // saldo_awal tahun yang dipilih
    $saldo_awal_sekarang = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun = '$tahun'
    ")->row()->total;

    // pemasukan sebelum tahun dipilih
    $pemasukan_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pemasukan
        WHERE tahun < '$tahun'
    ")->row()->total;

    // pengeluaran sebelum tahun dipilih
    $pengeluaran_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pengeluaran
        WHERE tahun < '$tahun'
    ")->row()->total;

			$saldo_awal = $saldo_awal_lama + $pemasukan_lama - $pengeluaran_lama + $saldo_awal_sekarang;

			$saldo_bulan_lalu = $this->db->query("
		SELECT 
		    (
		        (SELECT COALESCE(SUM(jumlah),0) FROM pemasukan 
		         WHERE STR_TO_DATE(tanggal_input, '%d-%m-%Y') < STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND tahun = '$tahun')
		        -
		        (SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran 
		         WHERE STR_TO_DATE(tanggal_input, '%d-%m-%Y') < STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') AND tahun = '$tahun')
		    ) as saldo
		")->row_array()['saldo'] ?? 0;


			$pemasukan = $this->db->query("
		    SELECT 
		        a.keterangan,
                COALESCE(SUM(p.jumlah),0) as total
		    FROM kode_akun a
		    LEFT JOIN pemasukan p 
		        ON a.id = p.id_kode_akun
				AND STR_TO_DATE(p.tanggal_input, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')
		    WHERE a.jenis = 'Pemasukan'
		    GROUP BY a.id
		")->result_array();

			$pengeluaran = $this->db->query("
		    SELECT 
		        a.keterangan,
                COALESCE(SUM(p.jumlah),0) as total
		    FROM kode_akun a
		    LEFT JOIN pengeluaran p 
		        ON a.id = p.id_kode_akun
				AND STR_TO_DATE(p.tanggal_input, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y')
		    WHERE a.jenis = 'Pengeluaran'
		    GROUP BY a.id
		")->result_array();

			$total_pemasukan = array_sum(array_column($pemasukan, 'total'));
			$total_pengeluaran = array_sum(array_column($pengeluaran, 'total'));
			$total_saldo_bulan_lalu = $saldo_awal + $saldo_bulan_lalu;

			$saldo_bulan_ini = $total_saldo_bulan_lalu + $total_pemasukan - $total_pengeluaran;
			$data = [
            'saldo_bulan_lalu' => $total_saldo_bulan_lalu,
			'pemasukan' => $pemasukan,
			'pengeluaran' => $pengeluaran,
			'total_pemasukan' => $total_pemasukan,
			'total_pengeluaran' => $total_pengeluaran,
			'saldo_bulan_ini' => $saldo_bulan_ini,
            'tanggal_laporan' => $sampai_tanggal
			];
        }else if($filter == 'bulan'){
            $bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
		// 	$saldo_awal = $this->db->query("
		//     SELECT nominal 
		//     FROM saldo_awal 
		//     WHERE tahun = '$tahun'
		// 	ORDER BY bulan ASC
		// ")->row_array()['nominal'] ?? 0;
			// saldo_awal sebelum tahun dipilih
    $saldo_awal_lama = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun < '$tahun'
    ")->row()->total;

    // saldo_awal tahun yang dipilih
    $saldo_awal_sekarang = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun = '$tahun'
    ")->row()->total;

    // pemasukan sebelum tahun dipilih
    $pemasukan_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pemasukan
        WHERE tahun < '$tahun'
    ")->row()->total;

    // pengeluaran sebelum tahun dipilih
    $pengeluaran_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pengeluaran
        WHERE tahun < '$tahun'
    ")->row()->total;

			$saldo_awal = $saldo_awal_lama + $pemasukan_lama - $pengeluaran_lama + $saldo_awal_sekarang;
		$saldo_bulan_lalu = $this->db->query("
		SELECT 
		    (
		        (SELECT COALESCE(SUM(jumlah),0) FROM pemasukan 
		         WHERE bulan < '$bulan'
		         AND tahun = '$tahun')
		        -
		        (SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran 
		         WHERE bulan < '$bulan'
		         AND tahun = '$tahun')
		    ) as saldo
		")->row_array()['saldo'] ?? 0;


		$pemasukan = $this->db->query("
		    SELECT 
		        a.keterangan,
		        SUM(p.jumlah) as total
		    FROM kode_akun a
		    LEFT JOIN pemasukan p 
		        ON a.id = p.id_kode_akun
		        AND p.bulan = '$bulan'
		        AND p.tahun = '$tahun'
		    WHERE a.jenis = 'Pemasukan'
		    GROUP BY a.id
		")->result_array();

		$pengeluaran = $this->db->query("
		    SELECT 
		        a.keterangan,
		        SUM(p.jumlah) as total
		    FROM kode_akun a
		    LEFT JOIN pengeluaran p 
		        ON a.id = p.id_kode_akun
		        AND p.bulan = '$bulan'
		        AND p.tahun = '$tahun'
		    WHERE a.jenis = 'Pengeluaran'
		    GROUP BY a.id
		")->result_array();

		$total_pemasukan = array_sum(array_column($pemasukan, 'total'));
		$total_pengeluaran = array_sum(array_column($pengeluaran, 'total'));
		$total_saldo_bulan_lalu = $saldo_awal + $saldo_bulan_lalu;

		$saldo_bulan_ini = $total_saldo_bulan_lalu + $total_pemasukan - $total_pengeluaran;
        $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
		$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
		$data = [
			'saldo_bulan_lalu' => $total_saldo_bulan_lalu,
			'pemasukan' => $pemasukan,
			'pengeluaran' => $pengeluaran,
			'total_pemasukan' => $total_pemasukan,
			'total_pengeluaran' => $total_pengeluaran,
			'saldo_bulan_ini' => $saldo_bulan_ini,
            'tanggal_laporan' => $tanggal_laporan
		];
        }else{
            $tahun = $this->input->post('single_filter_tahun');
			// SALDO AWAL TAHUN
			// $saldo_awal = $this->db->query("
 			//        SELECT nominal
 			//        FROM saldo_awal
 			//        WHERE tahun='$tahun'
 			//        ORDER BY bulan ASC
 			//        LIMIT 1
 			//    ")->row_array()['nominal'] ?? 0;
// saldo_awal sebelum tahun dipilih
    $saldo_awal_lama = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun < '$tahun'
    ")->row()->total;

    // saldo_awal tahun yang dipilih
    $saldo_awal_sekarang = $this->db->query("
        SELECT COALESCE(SUM(nominal),0) as total
        FROM saldo_awal
        WHERE tahun = '$tahun'
    ")->row()->total;

    // pemasukan sebelum tahun dipilih
    $pemasukan_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pemasukan
        WHERE tahun < '$tahun'
    ")->row()->total;

    // pengeluaran sebelum tahun dipilih
    $pengeluaran_lama = $this->db->query("
        SELECT COALESCE(SUM(jumlah),0) as total
        FROM pengeluaran
        WHERE tahun < '$tahun'
    ")->row()->total;

			$saldo_awal = $saldo_awal_lama + $pemasukan_lama - $pengeluaran_lama + $saldo_awal_sekarang;
			$pemasukan_tahun = $this->db->query("
		        SELECT id,keterangan
		        FROM kode_akun
		        WHERE jenis='Pemasukan'
		        ORDER BY keterangan
		    ")->result_array();
		
			foreach ($pemasukan_tahun as $key => $row) {
				for ($i = 1; $i <= 12; $i++) {
					$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
					$nominal = $this->db->query("
        		        SELECT COALESCE(SUM(jumlah),0) total
        		        FROM pemasukan
        		        WHERE id_kode_akun='" . $row['id'] . "'
        		        AND bulan='$bulan'
        		        AND tahun='$tahun'
        		    ")->row()->total ?? 0;
					$pemasukan_tahun[$key]['bulan'][$bulan] = $nominal;
				}
			}

			$pengeluaran_tahun = $this->db->query("
		        SELECT id,keterangan
		        FROM kode_akun
		        WHERE jenis='Pengeluaran'
		        ORDER BY keterangan
		    ")->result_array();

			foreach ($pengeluaran_tahun as $key => $row) {

				for ($i = 1; $i <= 12; $i++) {
					$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
					$nominal = $this->db->query("
                SELECT COALESCE(SUM(jumlah),0) total
                FROM pengeluaran
                WHERE id_kode_akun='" . $row['id'] . "'
                AND bulan='$bulan'
                AND tahun='$tahun'")->row()->total ?? 0;

					$pengeluaran_tahun[$key]['bulan'][$bulan] = $nominal;
				}
			}

			$total_pemasukan_bulan = [];
			for ($i = 1; $i <= 12; $i++) {
				$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
				$total_pemasukan_bulan[$bulan] = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) total
            FROM pemasukan
            WHERE bulan='$bulan'
            AND tahun='$tahun'")->row()->total ?? 0;
			}

			$total_pengeluaran_bulan = [];
			for ($i = 1; $i <= 12; $i++) {
				$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
				$total_pengeluaran_bulan[$bulan] = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) total
            FROM pengeluaran
            WHERE bulan='$bulan'
            AND tahun='$tahun'")->row()->total ?? 0;
			}
			$saldo_bulanan = [];
			$total_kas_tersedia = [];
			$saldo_akhir_bulan = [];

			$saldo_berjalan = $saldo_awal;

			for ($i = 1; $i <= 12; $i++) {
				$bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
				// saldo awal bulan
				$saldo_bulanan[$bulan] = $saldo_berjalan;
				// kas tersedia
				$total_kas_tersedia[$bulan] = $saldo_bulanan[$bulan] + $total_pemasukan_bulan[$bulan];
				// saldo akhir bulan
				$saldo_akhir_bulan[$bulan] = $total_kas_tersedia[$bulan] - $total_pengeluaran_bulan[$bulan];
				// saldo bulan berikutnya
				$saldo_berjalan = $saldo_akhir_bulan[$bulan];
			}

			$data = [
				'status' => 'Tahun',
				'saldo_awal' => $saldo_awal,
				'pemasukan_tahun' => $pemasukan_tahun,
				'pengeluaran_tahun' => $pengeluaran_tahun,
				'saldo_bulanan' => $saldo_bulanan,
				'total_pemasukan_bulan' => $total_pemasukan_bulan,
				'total_pengeluaran_bulan' => $total_pengeluaran_bulan,
				'total_kas_tersedia' => $total_kas_tersedia,
				'saldo_akhir_bulan' => $saldo_akhir_bulan,
				'tanggal_laporan' => '31 Desember ' . $tahun
			];
        }
		echo json_encode($data);
	}

	public function laporan_penggunaan_anggaran()
	{
			$bulan_raw = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');

	// 	if ($bulan >= 7) {
	// 		$semester = 'Ganjil';
	// 		$tahun_ajaran_text = $tahun . '/' . ($tahun + 1);
	// 	} else {
	// 		$semester = 'Genap';
	// 		$tahun_ajaran_text = ($tahun - 1) . '/' . $tahun;
	// 	}

	// 	$get_ta = $this->db->get_where('master_tahun_ajaran', [
	// 		'periode' => $tahun_ajaran_text
	// 	])->row_array();

	// 	$id_tahun_ajaran = $get_ta['id'] ?? 0;

	// 	$total_pengajuan = $this->db->query("
    //     SELECT COALESCE(SUM(rpd.nominal),0) as total
    //     FROM rencana_pengeluaran_detail rpd
    //     JOIN rencana_pengeluaran rp 
    //         ON rp.id = rpd.id_rencana_pengeluaran
    //     WHERE rp.tahun_ajaran = '$id_tahun_ajaran'
    //       AND rp.semester = '$semester'
    //       AND rpd.bulan = '$bulan'
    // ")->row_array()['total'];

	// 	$data_laporan = $this->db->query("
    //     SELECT 
    //         a.keterangan as kode_akun,
    //         COALESCE(SUM(p.jumlah),0) as realisasi
    //     FROM kode_akun a
    //     LEFT JOIN pengeluaran p 
    //         ON a.id = p.id_kode_akun
    //         AND p.bulan = '$bulan'
    //         AND p.tahun = '$tahun'
    //     WHERE a.jenis = 'Pengeluaran'
    //     GROUP BY a.id, a.keterangan
    //     ORDER BY a.id ASC
    // ")->result_array();

	// 	$total_realisasi = 0;
	// 	foreach ($data_laporan as $d) {
	// 		$total_realisasi += $d['realisasi'];
	// 	}

	// 	$bulan_sebelumnya_raw = $bulan_raw - 1;
	// 	$bulan_sebelumnya = str_pad($bulan_sebelumnya_raw, 2, '0', STR_PAD_LEFT);
	// 	$tahun_sebelumnya = $tahun;

	// 	if ($bulan_sebelumnya == 0) {
	// 		$bulan_sebelumnya = 12;
	// 		$tahun_sebelumnya = $tahun - 1;
	// 	}

	// 	if ($bulan_sebelumnya >= 7) {
	// 		$semester_lalu = 'Ganjil';
	// 		$tahun_ajaran_lalu_text = $tahun_sebelumnya . '/' . ($tahun_sebelumnya + 1);
	// 	} else {
	// 		$semester_lalu = 'Genap';
	// 		$tahun_ajaran_lalu_text = ($tahun_sebelumnya - 1) . '/' . $tahun_sebelumnya;
	// 	}

	// 	$get_ta_lalu = $this->db->get_where('master_tahun_ajaran', [
	// 		'periode' => $tahun_ajaran_lalu_text
	// 	])->row_array();

	// 	$id_ta_lalu = $get_ta_lalu['id'] ?? 0;

	// 	$total_rencana_lalu = $this->db->query("SELECT COALESCE(SUM(rpd.nominal),0) as total
    //     FROM rencana_pengeluaran_detail rpd
    //     JOIN rencana_pengeluaran rp ON rp.id = rpd.id_rencana_pengeluaran
    //     WHERE rp.tahun_ajaran = '$id_ta_lalu' AND rp.semester = '$semester_lalu' AND rpd.bulan <= '$bulan_sebelumnya'
    // ")->row_array()['total'];

	// 	$total_realisasi_lalu = $this->db->query("SELECT COALESCE(SUM(jumlah),0) as total
    //     FROM pengeluaran WHERE tahun = '$tahun_sebelumnya' AND bulan <= '$bulan_sebelumnya'
    // ")->row_array()['total'];
	// 	$saldo_bulan_lalu = $total_rencana_lalu - $total_realisasi_lalu;
	// 	$saldo_bulan_ini = $saldo_bulan_lalu + $total_pengajuan - $total_realisasi;
	// 	$tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
	// 	$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
    $bulan = str_pad($bulan_raw, 2, '0', STR_PAD_LEFT);
    $semester_rencana = 'Tahunan';
    if ($bulan_raw >= 7) {
        $tahun_ajaran_text = $tahun . '/' . ($tahun + 1);
    } else {
        $tahun_ajaran_text = ($tahun - 1) . '/' . $tahun;
    }

    $get_ta = $this->db->get_where('master_tahun_ajaran', [
        'periode' => $tahun_ajaran_text
    ])->row_array();

    $id_tahun_ajaran = $get_ta['id'] ?? 0;
    $total_pengajuan = $this->db->query("
        SELECT COALESCE(SUM(rpd.nominal), 0) AS total
        FROM rencana_pengeluaran_detail rpd
        JOIN rencana_pengeluaran rp 
            ON rp.id = rpd.id_rencana_pengeluaran
        WHERE rp.tahun_ajaran = " . $this->db->escape($id_tahun_ajaran) . "
          AND rp.semester = " . $this->db->escape($semester_rencana) . "
          AND rpd.bulan = " . $this->db->escape($bulan) . "
    ")->row_array()['total'];

    $data_laporan = $this->db->query("
        SELECT 
            a.keterangan AS kode_akun,
            COALESCE(SUM(p.jumlah), 0) AS realisasi
        FROM kode_akun a
        LEFT JOIN pengeluaran p 
            ON a.id = p.id_kode_akun
            AND p.bulan = " . $this->db->escape($bulan) . "
            AND p.tahun = " . $this->db->escape($tahun) . "
        WHERE a.jenis = 'Pengeluaran'
        GROUP BY a.id, a.keterangan
        ORDER BY a.id ASC
    ")->result_array();

    $total_realisasi = 0;
    foreach ($data_laporan as $d) {
        $total_realisasi += (float) $d['realisasi'];
    }

    $saldo_bulan_ini = 0;

    for ($i = 1; $i <= $bulan_raw; $i++) {
        $bulan_loop = str_pad($i, 2, '0', STR_PAD_LEFT);
        if ($i >= 7) {
            $periode_loop = $tahun . '/' . ($tahun + 1);
        } else {
            $periode_loop = ($tahun - 1) . '/' . $tahun;
        }

        $ta_loop = $this->db->get_where('master_tahun_ajaran', [
            'periode' => $periode_loop
        ])->row_array();

        $id_ta_loop = $ta_loop['id'] ?? 0;
        $rencana_loop = $this->db->query("
            SELECT COALESCE(SUM(rpd.nominal), 0) AS total
            FROM rencana_pengeluaran_detail rpd
            JOIN rencana_pengeluaran rp
                ON rp.id = rpd.id_rencana_pengeluaran
            WHERE rp.tahun_ajaran = " . $this->db->escape($id_ta_loop) . "
              AND rp.semester = " . $this->db->escape($semester_rencana) . "
              AND rpd.bulan = " . $this->db->escape($bulan_loop) . "
        ")->row_array();

        $total_rencana_loop = (float) ($rencana_loop['total'] ?? 0);
        $realisasi_loop = $this->db->query("
            SELECT COALESCE(SUM(p.jumlah), 0) AS total
            FROM pengeluaran p
            JOIN kode_akun a 
                ON a.id = p.id_kode_akun
            WHERE p.tahun = " . $this->db->escape($tahun) . "
              AND p.bulan = " . $this->db->escape($bulan_loop) . "
              AND a.jenis = 'Pengeluaran'
        ")->row_array();

        $total_realisasi_loop = (float) ($realisasi_loop['total'] ?? 0);
        $saldo_bulan_ini += ($total_rencana_loop - $total_realisasi_loop);
    }

    $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
    $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
        $result = [
			'data_laporan' => $data_laporan, 
			'total_pengajuan' => $total_pengajuan, 
			'total_realisasi' => $total_realisasi, 
			'saldo_bulan_ini' => $saldo_bulan_ini, 
			'tanggal_laporan' => $tanggal_laporan];
		// $result = [
		// 	'data_laporan' => $data_laporan,
		// 	'total_pengajuan' => $total_pengajuan,
		// 	'total_realisasi' => $total_realisasi,
		// 	'saldo_bulan_ini' => $saldo_bulan_ini,
        //     'tanggal_laporan'	=> $tanggal_laporan
		// ];
		
		$data = $result;
		echo json_encode($data);
	}

	public function laporan_rencana_anggaran()
	{
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
	// 	$pengeluaran = $this->db->query("
    //     SELECT 
    //         a.keterangan,
    //         COALESCE(SUM(rp.nominal),0) as total
    //     FROM kode_akun a
    //     LEFT JOIN rencana_pengeluaran rp 
    //         ON a.id = rp.kode_akun
    //         AND rp.bulan = '$bulan'
    //         AND rp.tahun = '$tahun'
    //     WHERE a.jenis = 'Pengeluaran'
    //     GROUP BY a.id
    //     ORDER BY a.id ASC
    // ")->result_array();

    //     $total_rencana = array_sum(array_column($pengeluaran, 'total'));
    //     $saldo_bulan_lalu = 0;
    //     for ($i = 1; $i < $bulan; $i++) {
	// 		$bulan_loop = str_pad($i, 2, "0", STR_PAD_LEFT);
    //         $rencana_loop = $this->db->query("
    //         SELECT COALESCE(SUM(nominal),0) as total
    //         FROM rencana_pengeluaran
    //         WHERE bulan = '$bulan_loop'
    //         AND tahun = '$tahun'
    //     ")->row_array()['total'] ?? 0;

    //         $realita_loop = $this->db->query("
    //         SELECT COALESCE(SUM(jumlah),0) as total
    //         FROM pengeluaran
    //         WHERE bulan = '$bulan_loop'
    //         AND tahun = '$tahun'
    //     ")->row_array()['total'] ?? 0;

    //         $saldo_bulan_lalu += ($rencana_loop - $realita_loop);
    //     }
// 	$bulan_int = (int)$bulan;

// if ($bulan_int >= 7) {
//     $semester = 'Ganjil';
//     $tahun_ajaran = $tahun . '/' . ($tahun + 1);
// } else {
//     $semester = 'Genap';
//     $tahun_ajaran = ($tahun - 1) . '/' . $tahun;
// }
// $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran',['periode' => $tahun_ajaran])->row_array();
 
//     $id_tahun_ajaran = $get_tahun_ajaran['id'] ?? '';
// $pengeluaran = $this->db->query("
//     SELECT
//         a.keterangan,
//         COALESCE(SUM(
//             CASE
//                 WHEN rp.id IS NOT NULL THEN rpd.nominal
//                 ELSE 0
//             END
//         ),0) as total
//     FROM kode_akun a
//     LEFT JOIN rencana_pengeluaran_detail rpd ON a.id = rpd.kode_akun AND rpd.bulan = '$bulan'
//     LEFT JOIN rencana_pengeluaran rp ON rp.id = rpd.id_rencana_pengeluaran AND rp.tahun_ajaran = '$id_tahun_ajaran' AND rp.semester = '$semester'
//     WHERE a.jenis = 'Pengeluaran' GROUP BY a.id ORDER BY a.id ASC
// ")->result_array();

//         $total_rencana = array_sum(array_column($pengeluaran, 'total'));
//         $saldo_bulan_lalu = 0;

// for ($i = 1; $i < $bulan_int; $i++) {

//     $bulan_loop = str_pad($i, 2, '0', STR_PAD_LEFT);

//     // Tentukan tahun ajaran & semester sesuai bulan yang sedang di-loop
//     if ($i >= 7) {
//         $semester_loop = 'Ganjil';
//         $periode_loop = $tahun . '/' . ($tahun + 1);
//     } else {
//         $semester_loop = 'Genap';
//         $periode_loop = ($tahun - 1) . '/' . $tahun;
//     }

//     $ta_loop = $this->db
//         ->get_where('master_tahun_ajaran', [
//             'periode' => $periode_loop
//         ])
//         ->row_array();

//     $id_ta_loop = $ta_loop['id'] ?? 0;

//     // Total rencana bulan tersebut
//     $rencana_loop = $this->db->query("
//         SELECT COALESCE(SUM(rpd.nominal),0) as total
//         FROM rencana_pengeluaran_detail rpd
//         JOIN rencana_pengeluaran rp
//             ON rp.id = rpd.id_rencana_pengeluaran
//         WHERE rpd.bulan = '$bulan_loop'
//         AND rp.tahun_ajaran = '$id_ta_loop'
//         AND rp.semester = '$semester_loop'
//     ")->row_array()['total'] ?? 0;

//     // Total realisasi bulan tersebut
//     $realita_loop = $this->db->query("
//         SELECT COALESCE(SUM(jumlah),0) as total
//         FROM pengeluaran
//         WHERE bulan = '$bulan_loop'
//         AND tahun = '$tahun'
//     ")->row_array()['total'] ?? 0;

//     $saldo_bulan_lalu += ($rencana_loop - $realita_loop);
// }

//         if ($total_rencana == 0) {
//             $pengajuan_anggaran = 0;
//         } else {
//             $pengajuan_anggaran = $total_rencana - $saldo_bulan_lalu;
//         }
//         $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
// 		$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
  $bulan_int = (int) $bulan;
    $tahun_int = (int) $tahun;
    $bulan = str_pad($bulan_int, 2, '0', STR_PAD_LEFT);

    $semester_rencana = 'Tahunan';

    if ($bulan_int >= 7) {
        $tahun_ajaran = $tahun_int . '/' . ($tahun_int + 1);
    } else {
        $tahun_ajaran = ($tahun_int - 1) . '/' . $tahun_int;
    }

    $get_tahun_ajaran = $this->db
        ->get_where('master_tahun_ajaran', [
            'periode' => $tahun_ajaran
        ])
        ->row_array();

    $id_tahun_ajaran = $get_tahun_ajaran['id'] ?? 0;

    $pengeluaran = $this->db->query("
        SELECT
            a.keterangan,
            COALESCE(SUM(
                CASE
                    WHEN rp.id IS NOT NULL THEN rpd.nominal
                    ELSE 0
                END
            ), 0) AS total
        FROM kode_akun a
        LEFT JOIN rencana_pengeluaran_detail rpd
            ON a.id = rpd.kode_akun
            AND rpd.bulan = " . $this->db->escape($bulan) . "
        LEFT JOIN rencana_pengeluaran rp
            ON rp.id = rpd.id_rencana_pengeluaran
            AND rp.tahun_ajaran = " . $this->db->escape($id_tahun_ajaran) . "
            AND rp.semester = " . $this->db->escape($semester_rencana) . "
        WHERE a.jenis = 'Pengeluaran'
        GROUP BY a.id
        ORDER BY a.id ASC
    ")->result_array();

    $total_rencana = array_sum(array_column($pengeluaran, 'total'));

    $saldo_bulan_lalu = 0;

    for ($i = 1; $i < $bulan_int; $i++) {
        $bulan_loop = str_pad($i, 2, '0', STR_PAD_LEFT);

        if ($i >= 7) {
            $periode_loop = $tahun_int . '/' . ($tahun_int + 1);
        } else {
            $periode_loop = ($tahun_int - 1) . '/' . $tahun_int;
        }

        $ta_loop = $this->db
            ->get_where('master_tahun_ajaran', [
                'periode' => $periode_loop
            ])
            ->row_array();

        $id_ta_loop = $ta_loop['id'] ?? 0;

        $rencana_loop = $this->db->query("
            SELECT COALESCE(SUM(rpd.nominal), 0) AS total
            FROM rencana_pengeluaran_detail rpd
            JOIN rencana_pengeluaran rp
                ON rp.id = rpd.id_rencana_pengeluaran
            WHERE rpd.bulan = " . $this->db->escape($bulan_loop) . "
            AND rp.tahun_ajaran = " . $this->db->escape($id_ta_loop) . "
            AND rp.semester = " . $this->db->escape($semester_rencana) . "
        ")->row_array();

        $total_rencana_loop = (float) ($rencana_loop['total'] ?? 0);

        $realita_loop = $this->db->query("
            SELECT COALESCE(SUM(p.jumlah), 0) AS total
            FROM pengeluaran p
            JOIN kode_akun a
                ON a.id = p.id_kode_akun
            WHERE p.bulan = " . $this->db->escape($bulan_loop) . "
            AND p.tahun = " . $this->db->escape($tahun_int) . "
            AND a.jenis = 'Pengeluaran'
        ")->row_array();

        $total_realita_loop = (float) ($realita_loop['total'] ?? 0);

        $saldo_bulan_lalu += ($total_rencana_loop - $total_realita_loop);
    }

    if ($total_rencana == 0) {
        $pengajuan_anggaran = 0;
    } else {
        $pengajuan_anggaran = $total_rencana - $saldo_bulan_lalu;
    }

    $tanggal_terakhir = date('t', strtotime($tahun_int . '-' . $bulan . '-01'));
    $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun_int;

        $result = [
            'pengeluaran' => $pengeluaran,
            'pengajuan_anggaran' => $pengajuan_anggaran,
            'saldo_bulan_lalu' => $saldo_bulan_lalu,
			'total_rencana' => $total_rencana,
            'tanggal_laporan' => $tanggal_laporan
        ];
		
		$data = $result;
		echo json_encode($data);
	}
	public function laporan_pos()
	{
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
		$data_pos = [];
        $akun = $this->db->query("SELECT id, keterangan FROM kode_akun WHERE jenis = 'Pemasukan' ORDER BY id ASC")->result_array();
         foreach ($akun as $a) {

        // SALDO BULAN LALU
        $saldo_lalu = $this->db->query("
            SELECT
                COALESCE(
                    (
                        SELECT SUM(jumlah)
                        FROM pemasukan
                        WHERE id_kode_akun = '".$a['id']."'
                        AND bulan < '$bulan'
                        AND tahun = '$tahun'
                    ),0
                )
                -
                COALESCE(
                    (
                        SELECT SUM(jumlah)
                        FROM pengeluaran
                        WHERE id_kode_akun = '".$a['id']."'
                        AND bulan < '$bulan'
                        AND tahun = '$tahun'
                    ),0
                ) as saldo
        ")->row_array()['saldo'] ?? 0;

        // MASUK BULAN INI
        $masuk = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pemasukan
            WHERE id_kode_akun = '".$a['id']."'
            AND bulan = '$bulan'
            AND tahun = '$tahun'
        ")->row_array()['total'] ?? 0;

        // KELUAR BULAN INI
        $keluar = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pengeluaran
            WHERE filter_kode_akun = '".$a['id']."'
            AND bulan = '$bulan'
            AND tahun = '$tahun'
        ")->row_array()['total'] ?? 0;
// WHERE id_kode_akun = '".$a['id']."'
        // SALDO
        $saldo = $saldo_lalu + $masuk - $keluar;
        $data_pos[] = [
            'uraian' => $a['keterangan'],
            'saldo_lalu' => $saldo_lalu,
            'masuk' => $masuk,
            'keluar' => $keluar,
            'saldo' => $saldo
        ];
    }
    $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
		$tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
        $result = [
            'data_pos' => $data_pos,
            'tanggal_laporan' => $tanggal_laporan
        ];
		
		$data = $result;
		echo json_encode($data);
	}
	public function laporan_rencana_asumsi_pemasukan()
	{
        $semester = 'Tahunan';
        $periode = $this->input->post('id_periode');
		$get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $periode])->row_array();
           if ($semester == 'Tahunan') {
    //     $list_bulan = ['07', '08', '09', '10', '11', '12'];
    // } elseif ($semester == 'Genap') {
    //     $list_bulan = ['01', '02', '03', '04', '05', '06'];
    // } else {
        $list_bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
    }
 $rencana_pemasukan = $this->db->query("
        SELECT
            ka.id,
            ka.keterangan AS kategori,

            COALESCE(MAX(x.total_asumsi_masuk), 0) AS total_asumsi_masuk,
            COALESCE(MAX(x.persen_masuk), 0) AS persen_masuk,
            COALESCE(MAX(x.asumsi_masuk), 0) AS asumsi_masuk,
            COALESCE(MAX(x.saving_normal), 0) AS saving_normal,
            COALESCE(MAX(x.saving_persen), 0) AS saving_persen

        FROM kode_akun ka

        LEFT JOIN (
            SELECT
                d.*
            FROM rencana_asumsi_pemasukan_detail d
            INNER JOIN rencana_asumsi_pemasukan h
                ON h.id = d.id_rencana_asumsi_pemasukan
            WHERE h.tahun_ajaran = ?
                AND h.semester = ?
        ) x ON x.kode_akun = ka.id

        WHERE ka.jenis = 'Pemasukan'

        GROUP BY ka.id, ka.keterangan
        ORDER BY ka.id ASC
    ", [$periode, $semester])->result_array();

    $detail_bulan = $this->db->query("
        SELECT
            d.kode_akun,
            d.bulan,
            SUM(d.nominal_bulan) AS nominal_bulan
        FROM rencana_asumsi_pemasukan_detail d
        INNER JOIN rencana_asumsi_pemasukan h
            ON h.id = d.id_rencana_asumsi_pemasukan
        WHERE h.tahun_ajaran = ?
            AND h.semester = ?
        GROUP BY d.kode_akun, d.bulan
    ", [$periode, $semester])->result_array();

    $bulan_per_akun = [];

    foreach ($detail_bulan as $db) {
        $kode_akun = $db['kode_akun'];
        $bulan = str_pad($db['bulan'], 2, '0', STR_PAD_LEFT);

        if (!isset($bulan_per_akun[$kode_akun])) {
            $bulan_per_akun[$kode_akun] = [];
        }

        $bulan_per_akun[$kode_akun][$bulan] = (float) $db['nominal_bulan'];
    }

    foreach ($rencana_pemasukan as &$r) {
        $kode_akun = $r['id'];

        $r['bulan'] = [];

        foreach ($list_bulan as $b) {
            $r['bulan'][$b] = $bulan_per_akun[$kode_akun][$b] ?? 0;
        }

        $r['total_asumsi_masuk'] = (float) $r['total_asumsi_masuk'];
        $r['persen_masuk'] = (float) $r['persen_masuk'];
        $r['asumsi_masuk'] = (float) $r['asumsi_masuk'];
        $r['saving_normal'] = (float) $r['saving_normal'];
        $r['saving_persen'] = (float) $r['saving_persen'];
    }
    unset($r);
    
        $result = [
            'rencana_pemasukan' => $rencana_pemasukan,
			'tahun_ajaran' => $get_tahun_ajaran['periode'],
			'list_bulan'=>$list_bulan
        ];
		
		$data = $result;
		echo json_encode($data);
	}
	public function laporan_rencana_pengeluaran()
	{
$semester = 'Tahunan';
        $periode = $this->input->post('id_periode');
        $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $periode])->row_array();

     if ($semester == 'Ganjil') {
        $list_bulan = ['07', '08', '09', '10', '11', '12'];
    } elseif($semester == 'Genap'){
        $list_bulan = ['01', '02', '03', '04', '05', '06'];
    } elseif ($semester == 'Tahunan') {
		$list_bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
	}

    $kolom_bulan = [];

    foreach ($list_bulan as $bulan) {
        $kolom_bulan[] = "
            COALESCE(
                SUM(
                    CASE
                        WHEN d.bulan = '$bulan'
                        THEN d.nominal
                        ELSE 0
                    END
                ),
            0) AS bulan_$bulan
        ";
    }

    $select_bulan = implode(',', $kolom_bulan);

    $data_laporan = $this->db->query("
        SELECT 
            a.id,
            a.keterangan,
            $select_bulan
        FROM kode_akun a
        LEFT JOIN (
            SELECT 
                d.kode_akun,
                d.bulan,
                d.nominal
            FROM rencana_pengeluaran_detail d
            INNER JOIN rencana_pengeluaran rp 
                ON rp.id = d.id_rencana_pengeluaran
            WHERE 
                rp.tahun_ajaran = ?
                AND rp.semester = ?
        ) d ON d.kode_akun = a.id
        WHERE a.jenis = 'Pengeluaran'
        GROUP BY a.id, a.keterangan
        ORDER BY a.id ASC
    ", [$periode, $semester])->result_array();

		$result = [
			'data_laporan' => $data_laporan,
			'list_bulan'	=> $list_bulan,
			'semester'	=> $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
		];
		
		$data = $result;
		echo json_encode($data);
	}

	public function laporan_rencana_pemasukan()
	{
        $semester = 'Tahunan';
        $periode = $this->input->post('id_periode');
        $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $periode])->row_array();

	// 	$jenis = $this->db->query("
    //     SELECT
    //         j.id,
    //         j.nama_jenis
    //     FROM rencana_pemasukan_jenis j
    //     INNER JOIN rencana_pemasukan p ON p.id=j.id_rencana_pemasukan
    //      WHERE p.bulan >= '$bulan_awal'
    //     AND p.bulan <= '$bulan_akhir'
    //     AND p.tahun = '$tahun'
    //     ORDER BY j.id ASC
    // ")->result_array();
    
    $jenis = $this->db->query("
        SELECT
            j.id,
            j.nama_jenis
        FROM rencana_pemasukan_jenis j
        INNER JOIN rencana_pemasukan p
            ON p.id=j.id_rencana_pemasukan
         WHERE p.semester = '$semester'
         AND p.tahun_ajaran = '$periode'
        ORDER BY j.id ASC
    ")->result_array();

    $grand_total = 0;
    foreach($jenis as &$j){
           $detail = $this->db->query("
            SELECT d.*
            FROM rencana_pemasukan_detail d
            INNER JOIN rencana_pemasukan_jenis j ON j.id = d.id_jenis
            INNER JOIN rencana_pemasukan p ON p.id = j.id_rencana_pemasukan
            WHERE d.id_jenis = '" . $j['id'] . "'
            AND p.semester = '$semester'
            AND p.tahun_ajaran = '$periode'
            ORDER BY d.id ASC
        ")->result_array();

        $j['detail']=$detail;
        $j['subtotal_volume']=0;
        $j['subtotal_jumlah']=0;
        $j['subtotal_total']=0;
        foreach($detail as $d){
            $j['subtotal_volume'] += $d['volume'];
            $j['subtotal_jumlah'] += $d['jumlah'];
            $j['subtotal_total'] += $d['total'];
        }
        $grand_total += $j['subtotal_total'];
    }
    $result=[
		'semester' => $semester,
        'tahun_ajaran' => $get_tahun_ajaran['periode'],
        'data_laporan'=>$jenis,
        'grand_total'=>$grand_total
    ];
		
		$data = $result;
		echo json_encode($data);
	}
	public function laporan_olah_pos()
	{
		$tahun = $this->input->post('single_filter_tahun');
        $akun = $this->db->query("SELECT * FROM kode_akun WHERE jenis = 'Pemasukan' ORDER BY id ASC")->result_array();
			
		$hasil = [];

        foreach ($akun as $a) {
            $row = [
                'kode_akun' => $a['keterangan']
            ];
            $saldo_sebelumnya = 0;
            // looping bulan 1 - 12
            for ($b = 1; $b <= 12; $b++) {
                // pemasukan
                $masuk = $this->db->query("
                SELECT COALESCE(SUM(jumlah),0) as total
                FROM pemasukan
                WHERE id_kode_akun = '" . $a['id'] . "'
                AND bulan = '$b'
                AND tahun = '$tahun'
            ")->row_array();

                // pengeluaran
                $keluar = $this->db->query("
                SELECT COALESCE(SUM(jumlah),0) as total
                FROM pengeluaran
                WHERE id_kode_akun = '" . $a['id'] . "'
                AND bulan = '$b'
                AND tahun = '$tahun'
            ")->row_array();

                $total_masuk = $masuk['total'];
                $total_keluar = $keluar['total'];

                // CEK ADA TRANSAKSI ATAU TIDAK
                if ($total_masuk == 0 && $total_keluar == 0) {
                    $row['bulan'][$b] = [
                        'masuk' => 0,
                        'keluar' => 0,
                        'saldo' => 0
                    ];
                } else {
                    // SALDO BERJALAN
                    $saldo = $saldo_sebelumnya + $total_masuk - $total_keluar;
                    // SIMPAN SALDO SEBELUMNYA
                    $saldo_sebelumnya = $saldo;
                    $row['bulan'][$b] = [
                        'masuk' => $total_masuk,
                        'keluar' => $total_keluar,
                        'saldo' => $saldo
                    ];
                }
            }

            $hasil[] = $row;
        }
    $result=[
        'data_laporan'=>$hasil,
    ];
		
		$data = $result;
		echo json_encode($data);
	}
	public function laporan_olah_in()
	{
		$tahun = $this->input->post('single_filter_tahun');
        $akun = $this->db->query("SELECT * FROM kode_akun WHERE jenis = 'Pemasukan' ORDER BY id ASC")->result_array();
			
		$data_laporan = [];
		foreach ($akun as $a) {

			$row = [
				'kode_akun' => $a['keterangan']
			];

			for ($b = 1; $b <= 12; $b++) {

				$masuk = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pemasukan
            WHERE id_kode_akun = '" . $a['id'] . "'
            AND bulan = '$b'
            AND tahun = '$tahun'
        ")->row_array();

				$row['bulan'][$b] = $masuk['total'];
			}

			$data_laporan[] = $row;
		}
        $result=[
            'data_laporan'=>$data_laporan,
        ];
		
		$data = $result;
		echo json_encode($data);
	}
	public function laporan_olah_out()
	{
		$tahun = $this->input->post('single_filter_tahun');
        $akun = $this->db->query("SELECT * FROM kode_akun WHERE jenis = 'Pengeluaran' ORDER BY id ASC")->result_array();
			
		$data_laporan = [];
		foreach ($akun as $a) {

			$row = [
				'kode_akun' => $a['keterangan']
			];

			for ($b = 1; $b <= 12; $b++) {

				$pengeluaran = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pengeluaran
            WHERE id_kode_akun = '" . $a['id'] . "'
            AND bulan = '$b'
            AND tahun = '$tahun'
        ")->row_array();

				$row['bulan'][$b] = $pengeluaran['total'];
			}

			$data_laporan[] = $row;
		}
        $result=[
            'data_laporan'=>$data_laporan,
        ];
		
		$data = $result;
		echo json_encode($data);
	}

    public function laporan_perbandingan_rencana_pengeluaran()
	{
		$semester = $this->input->post('semester');
		$tahun_ajaran = $this->input->post('id_periode');
	// 	$get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran',['id' => $tahun_ajaran])->row_array();
	// 	// pecah periode 2025/2026
	// 	list($tahun_awal, $tahun_akhir) = explode('/', $get_tahun_ajaran['periode']);
	// 	if (empty($semester)) {
	// 		$bulan_laporan = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
	// 	} elseif ($semester == 'Ganjil') {
	// 		$bulan_laporan = [7, 8, 9, 10, 11, 12];
	// 	} else {
	// 		$bulan_laporan = [1, 2, 3, 4, 5, 6];
	// 	}

	// 	$where_semester = '';

	// 	if (!empty($semester)) {
	// 		$where_semester = "AND r.semester = '" . $semester . "'";
	// 	}
	// 	$akun = $this->db->query("
    //     SELECT * 
    //     FROM kode_akun 
    //     WHERE jenis = 'Pengeluaran' 
    //     ORDER BY id ASC
    // ")->result_array();

	// 	$data_laporan = [];

	// 	foreach ($akun as $a) {

	// 		$row = [
	// 			'kode_akun' => $a['keterangan']
	// 		];

	// 		foreach ($bulan_laporan as $b) {
	// 			if ($b >= 7) {
	// 				$tahun_realisasi = $tahun_awal;   // Juli–Desember
	// 			} else {
	// 				$tahun_realisasi = $tahun_akhir;  // Jan–Juni
	// 			}
	// 			$bulan_db = str_pad($b, 2, '0', STR_PAD_LEFT);
	// 			$pengeluaran = $this->db->query("
    //             SELECT COALESCE(SUM(jumlah),0) as total
    //             FROM pengeluaran
    //             WHERE id_kode_akun = '" . $a['id'] . "'
    //             AND bulan = '$bulan_db'
    //             AND tahun = '$tahun_realisasi'
    //         ")->row_array();


	// 			$rencana_pengeluaran = $this->db->query("
    //             SELECT COALESCE(SUM(d.nominal),0) as total
    //             FROM rencana_pengeluaran_detail d
    //             JOIN rencana_pengeluaran r 
    //                 ON r.id = d.id_rencana_pengeluaran
    //             WHERE d.kode_akun = '" . $a['id'] . "'
    //             AND d.bulan = '$bulan_db'
    //             AND r.tahun_ajaran = '" . $tahun_ajaran . "'
    //             $where_semester
    //         ")->row_array();

	// 			$row['bulan'][$b] = [
	// 				'rencana' => $rencana_pengeluaran['total'],
	// 				'realisasi' => $pengeluaran['total']
	// 			];
	// 		}

	// 		$data_laporan[] = $row;
	// 	}
    $semester = 'Tahunan';
    $id_tahun_ajaran = $ambil['id_periode'] ?? '';

    if ($id_tahun_ajaran == '') {
        show_error('Tahun ajaran wajib dipilih.');
        return;
    }

    $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', [
        'id' => $id_tahun_ajaran
    ])->row_array();

    if (!$get_tahun_ajaran) {
        show_error('Tahun ajaran tidak ditemukan.');
        return;
    }

    list($tahun_awal, $tahun_akhir) = explode('/', $get_tahun_ajaran['periode']);

    /*
     * Karena laporan menggunakan Tahun Ajaran dan semester Tahunan,
     * maka bulan laporan adalah Juli - Juni.
     */
    if ($semester == 'Tahunan') {
        $bulan_laporan = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
    } elseif ($semester == 'Ganjil') {
        $bulan_laporan = [7, 8, 9, 10, 11, 12];
    } else {
        $bulan_laporan = [1, 2, 3, 4, 5, 6];
    }

    $akun = $this->db->query("
        SELECT *
        FROM kode_akun
        WHERE jenis = 'Pengeluaran'
        ORDER BY id ASC
    ")->result_array();

    $data_laporan = [];

    foreach ($akun as $a) {

        $row = [
            'kode_akun'    => $a['keterangan'],
            'total_rencana'=> 0,
            'bulan'        => []
        ];

        foreach ($bulan_laporan as $b) {
            $bulan_db = str_pad($b, 2, '0', STR_PAD_LEFT);

            /*
             * Mapping tahun realisasi:
             * Juli - Desember memakai tahun_awal.
             * Januari - Juni memakai tahun_akhir.
             */
            if ($b >= 7) {
                $tahun_realisasi = $tahun_awal;
            } else {
                $tahun_realisasi = $tahun_akhir;
            }

            /*
             * Ambil realisasi pengeluaran.
             */
            $pengeluaran = $this->db->query("
                SELECT COALESCE(SUM(jumlah), 0) AS total
                FROM pengeluaran
                WHERE id_kode_akun = '".$a['id']."'
                AND bulan = '$bulan_db'
                AND tahun = '$tahun_realisasi'
            ")->row_array();

            /*
             * Ambil rencana pengeluaran.
             * Karena rencana_pengeluaran_detail sudah per bulan,
             * maka tidak perlu dibagi rata seperti pemasukan.
             */
            $rencana_pengeluaran = $this->db->query("
                SELECT COALESCE(SUM(d.nominal), 0) AS total
                FROM rencana_pengeluaran_detail d
                JOIN rencana_pengeluaran r
                    ON r.id = d.id_rencana_pengeluaran
                WHERE d.kode_akun = '".$a['id']."'
                AND d.bulan = '$bulan_db'
                AND r.tahun_ajaran = '$id_tahun_ajaran'
                AND r.semester = '$semester'
            ")->row_array();

            $rencana = (float) ($rencana_pengeluaran['total'] ?? 0);
            $realisasi = (float) ($pengeluaran['total'] ?? 0);

            $row['bulan'][$b] = [
                'rencana'   => $rencana,
                'realisasi' => $realisasi
            ];

            $row['total_rencana'] += $rencana;
        }

        $data_laporan[] = $row;
    }
        $result=[
			'semester' => $semester,
			'tahun_ajaran' => $get_tahun_ajaran['periode'],
			'status' => 'Tahun Ajaran',
            'data_laporan'=>$data_laporan,
			'bulan_laporan' => $bulan_laporan,
        ];
		
		$data = $result;
		echo json_encode($data);
	}
    public function laporan_perbandingan_rencana_pemasukan()
	{
		$tahun_ajaran = $this->input->post('id_periode');
        $semester = 'Tahunan';

    $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', [
        'id' => $tahun_ajaran
    ])->row_array();

    $akun = $this->db->query("SELECT * FROM kode_akun WHERE jenis = 'Pemasukan' ORDER BY id ASC")->result_array();

    list($tahun_awal, $tahun_akhir) = explode('/', $get_tahun_ajaran['periode']);

    if ($semester == 'Tahunan') {
        $bulan_laporan = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
    } elseif ($semester == 'Ganjil') {
        $bulan_laporan = [7, 8, 9, 10, 11, 12];
    } else {
        $bulan_laporan = [1, 2, 3, 4, 5, 6];
    }

   $data_laporan = [];

    foreach ($akun as $a) {

        $row = [
            'kode_akun' => $a['keterangan'],
            'asumsi'    => 0,
            'bulan'     => []
        ];

        foreach ($bulan_laporan as $bln) {
            $row['bulan'][$bln] = [
                'rencana'   => 0,
                'realisasi' => 0
            ];
        }

        /*
         * 1. ASUMSI MASUK
         * Diambil dari total per kode akun rencana pemasukan.
         */
        $asumsi = $this->db->query("
            SELECT
                COALESCE(SUM(d.total * (j.persen / 100)), 0) AS total_asumsi
            FROM rencana_pemasukan_jenis j
            JOIN rencana_pemasukan_detail d
                ON d.id_jenis = j.id
            JOIN rencana_pemasukan r
                ON r.id = j.id_rencana_pemasukan
            WHERE j.kode_akun = '".$a['id']."'
            AND r.tahun_ajaran = '$tahun_ajaran'
            AND r.semester = '$semester'
        ")->row_array();

        $row['asumsi'] = (float) ($asumsi['total_asumsi'] ?? 0);

        /*
         * 2. RENCANA BULANAN
         * Diambil dari rencana_asumsi_pemasukan_detail.nominal_bulan.
         */
        $rencana_bulanan = $this->db->query("
            SELECT
                d.bulan,
                COALESCE(SUM(d.nominal_bulan), 0) AS total_rencana
            FROM rencana_asumsi_pemasukan_detail d
            JOIN rencana_asumsi_pemasukan r
                ON r.id = d.id_rencana_asumsi_pemasukan
            WHERE d.kode_akun = '".$a['id']."'
            AND r.tahun_ajaran = '$tahun_ajaran'
            AND r.semester = '$semester'
            GROUP BY d.bulan
        ")->result_array();

        foreach ($rencana_bulanan as $rb) {
            $bulan_int = (int) $rb['bulan'];

            if (isset($row['bulan'][$bulan_int])) {
                $row['bulan'][$bulan_int]['rencana'] += (float) $rb['total_rencana'];
            }
        }

        /*
         * 3. REALISASI PEMASUKAN
         * Dari tabel pemasukan berdasarkan tahun ajaran.
         */
        foreach ($bulan_laporan as $b) {
            $bulan_db = str_pad($b, 2, '0', STR_PAD_LEFT);

            if ($b >= 7) {
                $tahun_realisasi = $tahun_awal;
            } else {
                $tahun_realisasi = $tahun_akhir;
            }

            $pemasukan = $this->db->query("
                SELECT COALESCE(SUM(jumlah), 0) AS total
                FROM pemasukan
                WHERE id_kode_akun = '".$a['id']."'
                AND bulan = '$bulan_db'
                AND tahun = '$tahun_realisasi'
            ")->row_array();

            $row['bulan'][$b]['realisasi'] = (float) ($pemasukan['total'] ?? 0);
        }

        $data_laporan[] = $row;
    }
        $result=[
			// 'status'          => empty($semester) ? 'Tahun Ajaran' : 'Semester',
            'semester' => $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
			'bulan_laporan'   => $bulan_laporan,
            'data_laporan'=>$data_laporan,
        ];
		
		$data = $result;
		echo json_encode($data);
	}

    	public function laporan_penerimaan_honorarium()
	{
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
			 $hari_kerja = 15;

    $rumus_tidak_hadir = $this->db->where('LOWER(nama_potongan)', 'tidak_hadir')->get('rumus_potongan')->row_array();
    $persen_tidak_hadir = (float) ($rumus_tidak_hadir['persen'] ?? 5);

    $this->db->select('
        a.id as id_pegawai,
        a.nama_pegawai,
        a.tmt,
        a.pendidikan_terakhir,

        b.id as id_pegawai_jabatan,
        b.jabatan,

        c.id as id_gaji,
        c.gaji_pokok,
        c.struktural,
        c.tunjangan_pendidikan,
        c.wali_kelas,
        c.total_pendapatan
    ');
    $this->db->from('pegawai a');
    $this->db->join('pegawai_jabatan b', 'a.id = b.id_pegawai', 'left');
    $this->db->join('gaji c', 'a.id = c.id_pegawai', 'left');
    $this->db->order_by('a.id', 'ASC');
    $pegawai = $this->db->get()->result_array();

    $result = [];
    foreach ($pegawai as $item) {
        $cek_penggajian = $this->db->get_where('penggajian', [
            'id_pegawai' => $item['id_pegawai'],
            'bulan' => $bulan,
            'tahun' => $tahun
        ])->row_array();

        if ($cek_penggajian) {
            $gaji_pokok = (int) ($cek_penggajian['gaji_pokok'] ?? 0);
            $struktural = (int) ($cek_penggajian['struktural'] ?? 0);
            $tunjangan_pendidikan = (int) ($cek_penggajian['tunjangan_pendidikan'] ?? 0);
            $wali_kelas = (int) ($cek_penggajian['wali_kelas'] ?? 0);

            $jumlah_hadir = (int) ($cek_penggajian['jumlah_hadir'] ?? 0);
            $jumlah_tidak_hadir = (int) ($cek_penggajian['jumlah_tidak_hadir'] ?? 0);
            $jumlah_ijin = (int) ($cek_penggajian['jumlah_ijin'] ?? 0);
            $jumlah_alfa = (int) ($cek_penggajian['jumlah_alfa'] ?? 0);

            $jumlah = $struktural + $tunjangan_pendidikan + $wali_kelas;
            $jumlah_kotor = $gaji_pokok + $jumlah;

            $potongan_tidak_hadir = (float) ($cek_penggajian['potongan_tidak_hadir'] ?? 0);
            // $jumlah_penerimaan = (float) ($cek_penggajian['gaji_bersih'] ?? 0);
            $jumlah_penerimaan = $jumlah_kotor - $potongan_tidak_hadir;
            $status_penggajian = 'Sudah Dihitung';
        } else {

            $gaji_pokok = (int) ($item['gaji_pokok'] ?? 0);
            $struktural = (int) ($item['struktural'] ?? 0);
            $tunjangan_pendidikan = (int) ($item['tunjangan_pendidikan'] ?? 0);
            $wali_kelas = (int) ($item['wali_kelas'] ?? 0);
            $jumlah_hadir = $this->db->query("
                SELECT COUNT(*) AS jumlah_hadir 
                FROM presensi_pegawai 
                WHERE id_pegawai = ? 
                AND MONTH(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ? 
                AND YEAR(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ?
            ", [$item['id_pegawai'], $bulan, $tahun])->row()->jumlah_hadir ?? 0;

            $jumlah_ijin = $this->db->query("
                SELECT COUNT(*) AS jumlah_ijin 
                FROM izin_pegawai
                WHERE id_pegawai = ?
                AND status_approval = 1
                AND MONTH(STR_TO_DATE(tgl_tidak_hadir, '%d-%m-%Y')) = ?
                AND YEAR(STR_TO_DATE(tgl_tidak_hadir, '%d-%m-%Y')) = ?
            ", [$item['id_pegawai'], $bulan, $tahun])->row()->jumlah_ijin ?? 0;
            $jumlah_tidak_hadir = $hari_kerja - (int) $jumlah_hadir;
            if ($jumlah_tidak_hadir < 0) {
                $jumlah_tidak_hadir = 0;
            }
            $jumlah_alfa = $jumlah_tidak_hadir - (int) $jumlah_ijin;

            if ($jumlah_alfa < 0) {
                $jumlah_alfa = 0;
            }

            $jumlah = $struktural + $tunjangan_pendidikan + $wali_kelas;
            $jumlah_kotor = $gaji_pokok + $jumlah;

            $potongan_tidak_hadir = ($jumlah_kotor * $persen_tidak_hadir / 100) * $jumlah_alfa;
            $jumlah_penerimaan = $jumlah_kotor - $potongan_tidak_hadir;

            if ($jumlah_penerimaan < 0) {
                $jumlah_penerimaan = 0;
            }
            $status_penggajian = 'Belum Dihitung';
        }

        $jabatan = $item['jabatan'];
        // $masa_kerja = $this->hitung_masa_kerja($item['tmt'] ?? null, $bulan, $tahun);
        $masa_kerja = 0;
        $tmt = $item['tmt'] ?? null;
        if (!empty($tmt) && $tmt != '0000-00-00') {
            $tgl_tmt = DateTime::createFromFormat('d-m-Y', $tmt);
            if ($tgl_tmt) {
                if (!empty($bulan) && !empty($tahun)) {
                    $bulan_fix = str_pad((int) $bulan, 2, '0', STR_PAD_LEFT);
                    $tahun_fix = (int) $tahun;

                    $tanggal_terakhir = date('t', strtotime($tahun_fix . '-' . $bulan_fix . '-01'));

                    $tgl_acuan = DateTime::createFromFormat(
                        'Y-m-d',
                        $tahun_fix . '-' . $bulan_fix . '-' . $tanggal_terakhir
                    );
                } else {
                    $tgl_acuan = new DateTime(date('Y-m-d'));
                }
                if ($tgl_acuan && $tgl_tmt <= $tgl_acuan) {
                    $masa_kerja = $tgl_tmt->diff($tgl_acuan)->y;
                }
            }
        }

        $result[] = [
            'id_pegawai' => $item['id_pegawai'],
            'nama_pegawai' => $item['nama_pegawai'],
            'jabatan' => $jabatan,

            'jumlah_hadir' => (int) $jumlah_hadir,
            'jumlah_tidak_hadir' => (int) $jumlah_tidak_hadir,
            'jumlah_ijin' => (int) $jumlah_ijin,
            'jumlah_alfa' => (int) $jumlah_alfa,

            'tmt' => $item['tmt'] ?? '-',
            'masa_kerja' => $masa_kerja,
            'pendidikan_terakhir' => $item['pendidikan_terakhir'] ?? '-',

            'gaji_pokok' => $gaji_pokok,
            'struktural' => $struktural,
            'tunjangan_pendidikan' => $tunjangan_pendidikan,
            'wali_kelas' => $wali_kelas,

            'jumlah' => $jumlah,
            'jumlah_kotor' => $jumlah_kotor,
            'potongan_tidak_hadir' => $potongan_tidak_hadir,
            'jumlah_penerimaan' => $jumlah_penerimaan,

            'persen_tidak_hadir' => $persen_tidak_hadir,
            'status_penggajian' => $status_penggajian,
        ];
    }

    $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
    $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
		
			$data = [
				'data_laporan' => $result,
                'tanggal_laporan' => $tanggal_laporan,
                'persen_tidak_hadir' => $persen_tidak_hadir,
			];
		echo json_encode($data);
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
?>
