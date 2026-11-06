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

        $this->db->where('status', 'Aktif');
        $this->db->order_by('nama_metode', 'ASC');
        $data['spp_metode'] = $this->db->get('tagihan_metode_pembayaran')->result_array();

        $this->db->order_by('keterangan', 'ASC');
        $data['spp_kasbank'] = $this->db->get('kasbank')->result_array();

        $this->db->where('status', 'Aktif');
        $this->db->order_by('nama_jenis', 'ASC');
        $data['spp_jenis_tagihan'] = $this->db->get('tagihan_jenis')->result_array();

        $this->db->select('s.id, s.nis, s.nama_lengkap, kst.nama_kelas');
        $this->db->from('siswa s');
        $this->db->join('kelas_siswa ks', "ks.id = (
			SELECT MAX(ks2.id)
			FROM kelas_siswa ks2
			JOIN kelas_setting kst2 ON kst2.id = ks2.id_kelas_setting
			JOIN master_tahun_ajaran ta2 ON ta2.id = kst2.id_periode AND ta2.status = 'Aktif'
			WHERE ks2.id_siswa = s.id AND ks2.status_aktif IN ('1', 'AKTIF')
		)", 'left', false);
        $this->db->join('kelas_setting kst', 'kst.id = ks.id_kelas_setting', 'left');
        $this->db->where('s.status_aktif', 'AKTIF');
        $this->db->order_by('s.nama_lengkap', 'ASC');
        $data['spp_siswa'] = $this->db->get()->result_array();

        $this->db->order_by('id_periode', 'DESC');
        $this->db->order_by('nama_kelas', 'ASC');
        $data['spp_kelas'] = $this->db->get('kelas_setting')->result_array();
        $data['spp_siswa_pembatalan'] = $this->db->query("
			SELECT c.id_siswa, p.id_periode,
			       MAX(c.nama_siswa) AS nama_siswa, MAX(c.nis) AS nis,
			       MAX(COALESCE(p.nama_kelas, '')) AS nama_kelas
			FROM tagihan_pembatalan_transaksi c
			JOIN tagihan_pembayaran p ON p.id = c.id_pembayaran
			WHERE c.id_siswa > 0 AND p.id_periode > 0
			GROUP BY c.id_siswa, p.id_periode
			ORDER BY nama_siswa ASC
		")->result_array();

        $data['spp_petugas_pembatalan'] = $this->db->query("SELECT DISTINCT id_user_pembatalan AS id_user, nama_user_pembatalan AS nama_user FROM tagihan_pembatalan_transaksi WHERE id_user_pembatalan>0 AND nama_user_pembatalan IS NOT NULL AND nama_user_pembatalan<>'' ORDER BY nama_user_pembatalan ASC")->result_array();

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
        
        $multi_level_session = $this->session->userdata('admin');
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
                ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $id_levels = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (!$id_levels) { echo json_encode(array()); return; }
        $search = $this->input->post('search');
        $this->db->where('group', 'Laporan');
        $this->db->where_in('id_level', $id_levels);
        if ($search !== null && $search !== '') $this->db->like('name', $search);
        $menu_rows = $this->db->get('conf_list_menu')->result_array();
        $seen_menu = array();
        $data = array();
        foreach ($menu_rows as $menu_row) {
            $menu_key = (string) ($menu_row['id_menu'] ?? ($menu_row['path'] ?? ''));
            if (isset($seen_menu[$menu_key])) continue;
            $seen_menu[$menu_key] = true;
            $data[] = $menu_row;
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
        $dari_tanggal = $this->input->post('dari_tanggal')
            ? date('d-m-Y', strtotime($this->input->post('dari_tanggal')))
            : null;

        $sampai_tanggal = $this->input->post('sampai_tanggal')
            ? date('d-m-Y', strtotime($this->input->post('sampai_tanggal')))
            : null;

        $filter = (string) $this->input->post('filter');
        $id_periode = trim((string) $this->input->post('id_periode'));
        $bulan = (int) $this->input->post('bulan');
        $tahun = (int) $this->input->post('tahun');
        $single_filter_tahun = (int) $this->input->post('single_filter_tahun');

        $responseKosong = function ($namaPeriode = '-') {
            echo json_encode([
                'data' => new stdClass(),
                'meta' => [
                    'kelas' => '-',
                    'semester' => '-',
                    'periode' => $namaPeriode ?: '-'
                ]
            ]);
        };

        if ($id_periode === '') {
            $responseKosong();
            return;
        }

        $dataPeriode = $this->db->get_where('master_tahun_ajaran', [
            'id' => $id_periode
        ])->row_array();

        $namaPeriode = trim((string) ($dataPeriode['periode'] ?? ''));

        if ($namaPeriode === '') {
            $responseKosong();
            return;
        }

        $pegawaiRows = $this->db->query("\n        SELECT DISTINCT\n            a.id,\n            a.nama_pegawai\n        FROM pegawai a\n        LEFT JOIN pegawai_jabatan b\n            ON b.id_pegawai = a.id\n        WHERE b.jabatan IS NULL\n           OR LOWER(TRIM(b.jabatan)) <> 'guru'\n        ORDER BY a.nama_pegawai ASC\n    ")->result_array();

        $daftarPegawai = [];
        foreach ($pegawaiRows as $pegawai) {
            $namaPegawai = trim((string) ($pegawai['nama_pegawai'] ?? ''));
            if ($namaPegawai !== '') {
                $daftarPegawai[] = $namaPegawai;
            }
        }

        if (empty($daftarPegawai)) {
            $responseKosong($namaPeriode);
            return;
        }

        $allDates = [];

        if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
            $start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
            $end = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);

            if ($start && $end && $start <= $end) {
                $endInc = clone $end;
                $endInc->modify('+1 day');

                $datePeriod = new DatePeriod($start, new DateInterval('P1D'), $endInc);
                foreach ($datePeriod as $tanggal) {
                    if ($this->tanggalSesuaiTahunAjaranResumeExcel($tanggal, $namaPeriode)) {
                        $allDates[] = $tanggal->format('d-m-Y');
                    }
                }
            }
        } elseif ($filter === 'bulan' && $bulan >= 1 && $bulan <= 12 && $tahun > 0) {
            $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

            for ($d = 1; $d <= $jumlahHari; $d++) {
                $tanggal = DateTime::createFromFormat(
                    'd-m-Y',
                    sprintf('%02d-%02d-%04d', $d, $bulan, $tahun)
                );

                if ($tanggal && $this->tanggalSesuaiTahunAjaranResumeExcel($tanggal, $namaPeriode)) {
                    $allDates[] = $tanggal->format('d-m-Y');
                }
            }
        } elseif ($filter === 'tahun') {
            $tahunFilter = $single_filter_tahun ?: $tahun;

            if ($tahunFilter > 0) {
                for ($m = 1; $m <= 12; $m++) {
                    $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $m, $tahunFilter);

                    for ($d = 1; $d <= $jumlahHari; $d++) {
                        $tanggal = DateTime::createFromFormat(
                            'd-m-Y',
                            sprintf('%02d-%02d-%04d', $d, $m, $tahunFilter)
                        );

                        if ($tanggal && $this->tanggalSesuaiTahunAjaranResumeExcel($tanggal, $namaPeriode)) {
                            $allDates[] = $tanggal->format('d-m-Y');
                        }
                    }
                }
            }
        }

        if (empty($allDates)) {
            $responseKosong($namaPeriode);
            return;
        }

        $params = [$id_periode];
        $where = ['a.id_periode = ?'];

        if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
            $where[] = "STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')";
            $params[] = $dari_tanggal;
            $params[] = $sampai_tanggal;
        } elseif ($filter === 'bulan' && $bulan >= 1 && $tahun > 0) {
            $where[] = "MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?";
            $where[] = "YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?";
            $params[] = $bulan;
            $params[] = $tahun;
        } elseif ($filter === 'tahun') {
            $tahunFilter = $single_filter_tahun ?: $tahun;
            $where[] = "YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?";
            $params[] = $tahunFilter;
        }

        $sqlJurnal = "\n        SELECT\n            a.nama_pegawai,\n            a.tanggal_input,\n            a.semester,\n            a.periode\n        FROM jurnal_pegawai a\n        WHERE " . implode(' AND ', $where) . "\n        ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC\n    ";

        $jurnalRows = $this->db->query($sqlJurnal, $params)->result_array();

        $filled = [];
        $meta = [];

        foreach ($jurnalRows as $jurnal) {
            $tanggalInput = trim((string) ($jurnal['tanggal_input'] ?? ''));
            $namaPegawai = trim((string) ($jurnal['nama_pegawai'] ?? ''));

            if ($tanggalInput === '' || $namaPegawai === '') {
                continue;
            }

            $tanggal = DateTime::createFromFormat('d-m-Y', $tanggalInput);
            if (!$tanggal) {
                continue;
            }

            $tanggalKey = $tanggal->format('d-m-Y');
            $filled[$namaPegawai][$tanggalKey] = true;
            $meta[$namaPegawai] = [
                'semester' => $jurnal['semester'] ?? '-',
                'periode' => $jurnal['periode'] ?? $namaPeriode
            ];
        }

        $missing = [];

        foreach ($daftarPegawai as $namaPegawai) {
            $sudah = isset($filled[$namaPegawai])
                ? array_keys($filled[$namaPegawai])
                : [];

            $kosong = array_values(array_diff($allDates, $sudah));
            $this->urutkanTanggalResumeExcel($kosong);

            if (empty($kosong)) {
                continue;
            }

            $missing[$namaPegawai] = [
                'semester' => $meta[$namaPegawai]['semester'] ?? '-',
                'periode' => $meta[$namaPegawai]['periode'] ?? $namaPeriode,
                'tanggal' => $kosong
            ];
        }

        echo json_encode([
            'data' => empty($missing) ? new stdClass() : $missing,
            'meta' => [
                'kelas' => '-',
                'semester' => '-',
                'periode' => $namaPeriode
            ]
        ]);
    }

    // public function laporan_resume_tanggal_guru_belum_diisi()
    // {
    //     // --- Ambil input ---
    //     $dari_tanggal        = $this->input->post('dari_tanggal') ? date('d-m-Y', strtotime($this->input->post('dari_tanggal'))) : null;
    //     $sampai_tanggal      = $this->input->post('sampai_tanggal') ? date('d-m-Y', strtotime($this->input->post('sampai_tanggal'))) : null;
    //     $filter              = $this->input->post('filter');           // 'tanggal' | 'bulan' | 'tahun'
    //     $periode             = $this->input->post('periode');          // a.id_periode (opsional)
    //     $semester            = $this->input->post('semester');         // label/kode semester (opsional)
    //     $bulan               = $this->input->post('bulan');
    //     $tahun               = $this->input->post('tahun');
    //     $single_filter_tahun = $this->input->post('single_filter_tahun');

    //     // --- Master GURU (jabatan = 'Guru') ---
    //     $sqlGuru = "
    //         SELECT a.nama_pegawai AS nama_guru
    //         FROM pegawai a
    //         LEFT JOIN pegawai_jabatan b ON a.id = b.id_pegawai
    //         WHERE b.jabatan = 'Guru'
    //     ";
    //     $guruRows   = $this->db->query($sqlGuru)->result_array();
    //     $daftarGuru = array_map(fn($x) => trim($x['nama_guru']), $guruRows);

    //     // --- Bangun allDates sesuai filter ---
    //     $allDates = [];
    //     if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
    //         $start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
    //         $end   = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);
    //         if ($start && $end && $start <= $end) {
    //             $endInc = (clone $end)->modify('+1 day');
    //             $period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
    //             foreach ($period as $dt) $allDates[] = $dt->format('d-m-Y');
    //         }
    //         $periode_label = $dari_tanggal . ' s.d. ' . $sampai_tanggal;
    //     } elseif ($filter === 'bulan' && $bulan && $tahun) {
    //         $hari = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
    //         for ($d = 1; $d <= $hari; $d++) $allDates[] = sprintf('%02d-%02d-%04d', $d, $bulan, $tahun);
    //         $periode_label = sprintf('%02d-%04d', $bulan, $tahun);
    //     } else {
    //         $th = (int)($single_filter_tahun ?: $tahun);
    //         for ($m = 1; $m <= 12; $m++) {
    //             $hari = cal_days_in_month(CAL_GREGORIAN, $m, $th);
    //             for ($d = 1; $d <= $hari; $d++) $allDates[] = sprintf('%02d-%02d-%04d', $d, $m, $th);
    //         }
    //         $periode_label = (string)$th;
    //     }

    //     if (empty($allDates)) {
    //         echo json_encode(['data' => new stdClass(), 'meta' => ['kelas' => '-', 'semester' => ($semester ?: '-'), 'periode' => ($periode_label ?: '-')]]); 
    //         return;
    //     }

    //     // --- Query jurnal_guru + kelas, sesuai filter (pakai COALESCE tanggal_input/tanggal) ---
    //     $params = [];
    //     $where  = [];

    //     if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
    //         $where[]  = "STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')";
    //         $params[] = $dari_tanggal;
    //         $params[] = $sampai_tanggal;
    //     } elseif ($filter === 'bulan' && $bulan && $tahun) {
    //         $where[]  = "MONTH(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
    //         $where[]  = "YEAR(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
    //         $params[] = $bulan;
    //         $params[] = $tahun;
    //     } else {
    //         $th = (int)($single_filter_tahun ?: $tahun);
    //         $where[]  = "YEAR(STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y')) = ?";
    //         $params[] = $th;
    //     }

    //     if (!empty($periode))  { $where[] = "a.id_periode = ?"; $params[] = $periode; }
    //     if (!empty($semester)) { $where[] = "a.semester   = ?"; $params[] = $semester; }

    //     $sqlRows = "
    //         SELECT a.nama_guru,
    //                a.tanggal_input,
    //                a.tanggal,
    //                a.semester,
    //                a.id_periode,
    //                a.mapel,
    //                a.id_kelas,
    //                k.nama_kelas,
    //                k.kode_kelas
    //         FROM jurnal_guru a
    //         LEFT JOIN kelas k ON a.id_kelas = k.id
    //         " . (empty($where) ? "" : "WHERE " . implode(" AND ", $where)) . "
    //         ORDER BY STR_TO_DATE(COALESCE(a.tanggal_input, a.tanggal), '%d-%m-%Y') ASC
    //     ";
    //     $rows = $this->db->query($sqlRows, $params)->result_array();

    //     // --- Tandai tanggal yang sudah diisi per guru + simpan meta (mapel/kelas) ---
    //     $filled = []; // [nama_guru => ['dd-mm-YYYY' => true]]
    //     $meta   = []; // [nama_guru => ['semester','periode','mapel','nama_kelas','kode_kelas']]
    //     foreach ($rows as $r) {
    //         $raw = $r['tanggal_input'] ?: $r['tanggal'];
    //         if (!$raw) continue;

    //         $dt = DateTime::createFromFormat('d-m-Y', trim($raw));
    //         if (!$dt) continue;

    //         $tgl  = $dt->format('d-m-Y');
    //         $nama = trim($r['nama_guru']);
    //         if ($nama === '') continue;

    //         $filled[$nama][$tgl] = true;

    //         // Ambil meta. Jika guru mengajar banyak mapel/kelas, di sini akan “mengambil salah satu”
    //         // (terakhir yang ditemui). Jika perlu per-mapel/per-kelas, perlu dipecah per kombinasi.
    //         $meta[$nama]['semester']   = $r['semester']   ?? ($semester ?: '-');
    //         $meta[$nama]['periode']    = $periode_label;
    //         $meta[$nama]['mapel']      = $r['mapel']      ?? '-';
    //         $meta[$nama]['nama_kelas'] = $r['nama_kelas'] ?? '-';
    //         $meta[$nama]['kode_kelas'] = $r['kode_kelas'] ?? '-';
    //     }

    //     // --- Hitung tanggal KOSONG per guru master ---
    //     $missing = [];  
    //     $semester_label = $semester ?: '-';

    //     foreach ($daftarGuru as $nama) {
    //         $nama   = trim($nama);
    //         $sudah  = isset($filled[$nama]) ? array_keys($filled[$nama]) : [];
    //         $kosong = array_values(array_diff($allDates, $sudah));
    //         sort($kosong);

    //         if (!empty($kosong)) {
    //             $missing[$nama] = [
    //                 'semester'   => $meta[$nama]['semester']   ?? $semester_label,
    //                 'periode'    => $meta[$nama]['periode']    ?? $periode_label,
    //                 'mapel'      => $meta[$nama]['mapel']      ?? '-',
    //                 'nama_kelas' => $meta[$nama]['nama_kelas'] ?? '-',
    //                 'kode_kelas' => $meta[$nama]['kode_kelas'] ?? '-',
    //                 'tanggal'    => $kosong,
    //             ];
    //         }
    //     }

    //     // --- Meta header (opsional) ---
    //     $resp = [
    //         'data' => $missing,
    //         'meta' => [
    //             'kelas'    => '-',             // laporan agregat; bukan per kelas spesifik
    //             'semester' => $semester_label,
    //             'periode'  => $periode_label,
    //         ],
    //     ];

    //     echo json_encode( empty($missing) ? ['data' => new stdClass(), 'meta' => $resp['meta']] : $resp );
    // }
    public function laporan_resume_tanggal_guru_belum_diisi()
    {
        $dari_tanggal = $this->input->post('dari_tanggal')
            ? date('d-m-Y', strtotime($this->input->post('dari_tanggal')))
            : null;

        $sampai_tanggal = $this->input->post('sampai_tanggal')
            ? date('d-m-Y', strtotime($this->input->post('sampai_tanggal')))
            : null;

        $filter = (string) $this->input->post('filter');
        $id_periode = trim((string) $this->input->post('id_periode'));
        $bulan = (int) $this->input->post('bulan');
        $tahun = (int) $this->input->post('tahun');
        $single_filter_tahun = (int) $this->input->post('single_filter_tahun');

        $responseKosong = function ($namaPeriode = '-') {
            echo json_encode([
                'data' => new stdClass(),
                'meta' => [
                    'kelas' => '-',
                    'semester' => '-',
                    'periode' => $namaPeriode ?: '-'
                ]
            ]);
        };

        if ($id_periode === '') {
            $responseKosong();
            return;
        }

        $dataPeriode = $this->db->get_where('master_tahun_ajaran', [
            'id' => $id_periode
        ])->row_array();

        $namaPeriode = trim((string) ($dataPeriode['periode'] ?? ''));

        if ($namaPeriode === '') {
            $responseKosong();
            return;
        }

        /*
     * Master Guru harus membawa ID pegawai karena jadwal dan jurnal
     * dicocokkan menggunakan id_guru, bukan hanya nama.
     */
        $guruRows = $this->db->query("        SELECT DISTINCT            a.id,            a.nama_pegawai AS nama_guru        FROM pegawai a        INNER JOIN pegawai_jabatan b            ON b.id_pegawai = a.id        WHERE LOWER(TRIM(b.jabatan)) = 'guru'        ORDER BY a.nama_pegawai ASC    ")->result_array();

        $guruIds = [];
        $guruById = [];

        foreach ($guruRows as $guru) {
            $idGuru = $guru['id'] ?? null;
            $namaGuru = trim((string) ($guru['nama_guru'] ?? ''));

            if (empty($idGuru) || $namaGuru === '') {
                continue;
            }

            $guruIds[] = $idGuru;
            $guruById[$idGuru] = $namaGuru;
        }

        if (empty($guruIds)) {
            $responseKosong($namaPeriode);
            return;
        }

        /*
     * Buat daftar tanggal seperti kode print. Tanggal hanya dipakai
     * apabila sesuai Tahun Ajaran yang dipilih.
     */
        $allDates = [];

        if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
            $start = DateTime::createFromFormat('d-m-Y', $dari_tanggal);
            $end = DateTime::createFromFormat('d-m-Y', $sampai_tanggal);

            if ($start && $end && $start <= $end) {
                $endInc = clone $end;
                $endInc->modify('+1 day');

                $datePeriod = new DatePeriod(
                    $start,
                    new DateInterval('P1D'),
                    $endInc
                );

                foreach ($datePeriod as $tanggal) {
                    if ($this->tanggalSesuaiTahunAjaranResumeExcel($tanggal, $namaPeriode)) {
                        $allDates[] = $tanggal->format('d-m-Y');
                    }
                }
            }
        } elseif ($filter === 'bulan' && $bulan >= 1 && $bulan <= 12 && $tahun > 0) {
            $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

            for ($d = 1; $d <= $jumlahHari; $d++) {
                $tanggal = DateTime::createFromFormat(
                    'd-m-Y',
                    sprintf('%02d-%02d-%04d', $d, $bulan, $tahun)
                );

                if ($tanggal && $this->tanggalSesuaiTahunAjaranResumeExcel($tanggal, $namaPeriode)) {
                    $allDates[] = $tanggal->format('d-m-Y');
                }
            }
        } elseif ($filter === 'tahun') {
            $tahunFilter = $single_filter_tahun ?: $tahun;

            if ($tahunFilter > 0) {
                for ($m = 1; $m <= 12; $m++) {
                    $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $m, $tahunFilter);

                    for ($d = 1; $d <= $jumlahHari; $d++) {
                        $tanggal = DateTime::createFromFormat(
                            'd-m-Y',
                            sprintf('%02d-%02d-%04d', $d, $m, $tahunFilter)
                        );

                        if ($tanggal && $this->tanggalSesuaiTahunAjaranResumeExcel($tanggal, $namaPeriode)) {
                            $allDates[] = $tanggal->format('d-m-Y');
                        }
                    }
                }
            }
        }

        if (empty($allDates)) {
            $responseKosong($namaPeriode);
            return;
        }

        /* Ambil jadwal Guru sesuai Tahun Ajaran pilihan. */
        $jadwalByGuru = $this->getJadwalResumeExcel($guruIds, $id_periode);

        if (empty($jadwalByGuru)) {
            $responseKosong($namaPeriode);
            return;
        }

        $jadwalByGuruHari = $this->indexJadwalResumeExcel($jadwalByGuru);

        /* Ambil jurnal yang sudah diisi pada filter kalender dan periode. */
        $params = [];
        $where = ['a.id_periode = ?'];
        $params[] = $id_periode;

        if ($filter === 'tanggal' && $dari_tanggal && $sampai_tanggal) {
            $where[] = "STR_TO_DATE(a.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')";
            $params[] = $dari_tanggal;
            $params[] = $sampai_tanggal;
        } elseif ($filter === 'bulan' && $bulan >= 1 && $tahun > 0) {
            $where[] = "MONTH(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?";
            $where[] = "YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?";
            $params[] = $bulan;
            $params[] = $tahun;
        } elseif ($filter === 'tahun') {
            $tahunFilter = $single_filter_tahun ?: $tahun;
            $where[] = "YEAR(STR_TO_DATE(a.tanggal, '%d-%m-%Y')) = ?";
            $params[] = $tahunFilter;
        }

        $sqlJurnal = "        SELECT            a.*,            k.nama_kelas,            k.kode_kelas        FROM jurnal_guru a        LEFT JOIN kelas k            ON k.id = a.id_kelas        WHERE " . implode(' AND ', $where) . "        ORDER BY STR_TO_DATE(a.tanggal, '%d-%m-%Y') ASC    ";

        $jurnalRows = $this->db->query($sqlJurnal, $params)->result_array();

        /*
     * Tandai jurnal terisi berdasarkan ID jadwal. Untuk jurnal lama yang
     * belum menyimpan id_kelas_jadwal_pelajaran, gunakan pencocokan
     * guru + hari + mapel + kelas seperti controller print.
     */
        $filled = [];

        foreach ($jurnalRows as $jurnal) {
            $tanggalRaw = trim((string) ($jurnal['tanggal'] ?? ''));

            if ($tanggalRaw === '') {
                continue;
            }

            $tanggal = DateTime::createFromFormat('d-m-Y', $tanggalRaw);

            if (!$tanggal) {
                continue;
            }

            $tanggalKey = $tanggal->format('d-m-Y');
            $jadwalId = $jurnal['id_kelas_jadwal_pelajaran'] ?? null;

            if (!empty($jadwalId)) {
                $filled[$jadwalId][$tanggalKey] = true;
                continue;
            }

            $idGuru = $jurnal['id_guru'] ?? null;

            if (empty($idGuru)) {
                continue;
            }

            $hari = $this->hariDariTanggalResumeExcel($tanggalKey);
            $matchIds = $this->matchJadwalResumeExcel(
                $jadwalByGuruHari,
                $idGuru,
                $jurnal['mapel'] ?? '',
                $jurnal['id_kelas'] ?? null,
                $jurnal['nama_kelas'] ?? '',
                $hari
            );

            foreach ($matchIds as $matchId) {
                $filled[$matchId][$tanggalKey] = true;
            }
        }

        $datesByDow = $this->kelompokTanggalResumeExcel($allDates);
        $missing = [];
        $jumlahKeyNama = [];

        foreach ($jadwalByGuru as $idGuru => $jadwalList) {
            $namaGuru = trim((string) ($guruById[$idGuru] ?? ''));

            if ($namaGuru === '') {
                continue;
            }

            foreach ($jadwalList as $jadwal) {
                $expectedDates = $this->tanggalSesuaiHariResumeExcel(
                    $datesByDow,
                    [$jadwal['hari'] ?? '']
                );

                if (empty($expectedDates)) {
                    continue;
                }

                $jadwalId = $jadwal['id'] ?? null;
                $sudah = !empty($jadwalId) && isset($filled[$jadwalId])
                    ? array_keys($filled[$jadwalId])
                    : [];

                $kosong = array_values(array_diff($expectedDates, $sudah));
                $this->urutkanTanggalResumeExcel($kosong);

                if (empty($kosong)) {
                    continue;
                }

                /*
             * View lama mengharapkan satu object per key nama. Agar satu Guru
             * yang memiliki banyak jadwal tetap dapat tampil tanpa mengubah
             * view, key berikutnya diberi spasi di belakang. Pada HTML spasi
             * belakang tidak terlihat, tetapi key JSON tetap unik.
             */
                $jumlahKeyNama[$namaGuru] = ($jumlahKeyNama[$namaGuru] ?? 0) + 1;
                $keyNama = $namaGuru . str_repeat(' ', $jumlahKeyNama[$namaGuru] - 1);

                $missing[$keyNama] = [
                    'semester' => $jadwal['semester'] ?? '-',
                    'periode' => $jadwal['periode'] ?: $namaPeriode,
                    'mapel' => $jadwal['mapel'] ?? '-',
                    'nama_kelas' => $jadwal['nama_kelas'] ?? '-',
                    'kode_kelas' => $jadwal['kode_kelas'] ?? '-',
                    'tanggal' => $kosong
                ];
            }
        }

        echo json_encode([
            'data' => empty($missing) ? new stdClass() : $missing,
            'meta' => [
                'kelas' => '-',
                'semester' => '-',
                'periode' => $namaPeriode
            ]
        ]);
    }

    private function getJadwalResumeExcel(array $guruIds, $idPeriode): array
    {
        if (empty($guruIds) || empty($idPeriode)) {
            return [];
        }

        $this->db->select("a.id, a.id_guru, a.hari, a.mapel, a.id_mapel, a.id_kelas, a.kelas, a.semester, a.id_periode, a.periode,k.kode_kelas,COALESCE(a.kelas, k.nama_kelas) AS nama_kelas");
        $this->db->from('kelas_jadwal_pelajaran a');
        $this->db->join('kelas k', 'k.id = a.id_kelas', 'left');
        $this->db->where_in('a.id_guru', $guruIds);
        $this->db->where('a.id_periode', $idPeriode);

        $rows = $this->db->get()->result_array();
        $jadwalByGuru = [];

        foreach ($rows as $row) {
            $idGuru = $row['id_guru'] ?? null;

            if (empty($idGuru)) {
                continue;
            }

            $row['hari'] = $this->normalisasiHariResumeExcel($row['hari'] ?? '');
            $jadwalByGuru[$idGuru][] = $row;
        }

        return $jadwalByGuru;
    }

    private function indexJadwalResumeExcel(array $jadwalByGuru): array
    {
        $index = [];

        foreach ($jadwalByGuru as $idGuru => $jadwalList) {
            foreach ($jadwalList as $jadwal) {
                $hari = $this->normalisasiHariResumeExcel($jadwal['hari'] ?? '');

                if ($hari === '') {
                    continue;
                }

                $index[$idGuru][$hari][] = $jadwal;
            }
        }

        return $index;
    }

    private function matchJadwalResumeExcel(
        array $jadwalByGuruHari,
        $idGuru,
        $mapel,
        $idKelas,
        $namaKelas,
        $hari
    ): array {
        if (empty($idGuru)) {
            return [];
        }

        $hari = $this->normalisasiHariResumeExcel((string) $hari);

        if ($hari === '') {
            return [];
        }

        $mapel = strtolower(trim((string) $mapel));
        $namaKelas = strtolower(trim((string) $namaKelas));
        $list = $jadwalByGuruHari[$idGuru][$hari] ?? [];
        $matches = [];

        foreach ($list as $jadwal) {
            $jadwalMapel = strtolower(trim((string) ($jadwal['mapel'] ?? '')));

            if ($mapel !== '' && $jadwalMapel !== $mapel) {
                continue;
            }

            $jadwalIdKelas = $jadwal['id_kelas'] ?? null;

            if (
                !empty($idKelas) &&
                !empty($jadwalIdKelas) &&
                (string) $jadwalIdKelas !== (string) $idKelas
            ) {
                continue;
            }

            $jadwalNamaKelas = strtolower(trim((string) ($jadwal['nama_kelas'] ?? '')));

            if (
                $namaKelas !== '' &&
                $jadwalNamaKelas !== '' &&
                $jadwalNamaKelas !== $namaKelas
            ) {
                continue;
            }

            if (!empty($jadwal['id'])) {
                $matches[] = $jadwal['id'];
            }
        }

        return $matches;
    }

    private function kelompokTanggalResumeExcel(array $allDates): array
    {
        $byDow = [];

        foreach ($allDates as $date) {
            $tanggal = DateTime::createFromFormat('d-m-Y', $date);

            if (!$tanggal) {
                continue;
            }

            $byDow[(int) $tanggal->format('N')][] = $tanggal->format('d-m-Y');
        }

        return $byDow;
    }

    private function tanggalSesuaiHariResumeExcel(array $datesByDow, array $hariList): array
    {
        $hariMap = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7
        ];

        $expected = [];

        foreach ($hariList as $hari) {
            $hari = $this->normalisasiHariResumeExcel((string) $hari);
            $dow = $hariMap[$hari] ?? null;

            if ($dow !== null && !empty($datesByDow[$dow])) {
                $expected = array_merge($expected, $datesByDow[$dow]);
            }
        }

        $expected = array_values(array_unique($expected));
        $this->urutkanTanggalResumeExcel($expected);

        return $expected;
    }

    private function hariDariTanggalResumeExcel(string $tanggal): string
    {
        $date = DateTime::createFromFormat('d-m-Y', $tanggal);

        if (!$date) {
            return '';
        }

        $map = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];

        return $map[(int) $date->format('N')] ?? '';
    }

    private function tanggalSesuaiTahunAjaranResumeExcel(DateTime $tanggal, string $periode): bool
    {
        if (!preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', trim($periode), $match)) {
            return false;
        }

        $tahunAwal = (int) $match[1];
        $tahunAkhir = (int) $match[2];
        $tahunTanggal = (int) $tanggal->format('Y');
        $bulanTanggal = (int) $tanggal->format('n');

        if ($bulanTanggal >= 7) {
            return $tahunTanggal === $tahunAwal;
        }

        return $tahunTanggal === $tahunAkhir;
    }

    private function normalisasiHariResumeExcel(string $hari): string
    {
        $hari = trim($hari);
        return $hari === '' ? '' : ucfirst(strtolower($hari));
    }

    private function urutkanTanggalResumeExcel(array &$tanggalList): void
    {
        usort($tanggalList, function ($a, $b) {
            $tanggalA = DateTime::createFromFormat('d-m-Y', $a);
            $tanggalB = DateTime::createFromFormat('d-m-Y', $b);

            if (!$tanggalA || !$tanggalB) {
                return strcmp($a, $b);
            }

            return $tanggalA <=> $tanggalB;
        });
    }



  public function laporan_izin_pegawai()
	{
		$dari_tanggal = date('d-m-Y', strtotime($this->input->post('dari_tanggal')));
		$sampai_tanggal = date('d-m-Y', strtotime($this->input->post('sampai_tanggal')));

		$filter = $this->input->post('filter');
        $id_pegawai = $this->input->post('id_pegawai');

		if ($filter == 'tanggal') {
            $perpegawai = '';
			if ($id_pegawai) {
				$perpegawai = "AND a.id_pegawai = '$id_pegawai'";
			}
			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  
					WHERE STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y') BETWEEN STR_TO_DATE('$dari_tanggal', '%d-%m-%Y') 
					AND STR_TO_DATE('$sampai_tanggal', '%d-%m-%Y') AND a.status_approval !=0 $perpegawai  Order by a.tgl_tidak_hadir asc")->result_array();
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
            $perpegawai = '';
			if ($id_pegawai) {
				$perpegawai = "AND a.id_pegawai = '$id_pegawai'";
			}
			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  WHERE MONTH(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $bulan AND YEAR(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $tahun AND a.status_approval !=0 $perpegawai
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
            $perpegawai = '';
			if ($id_pegawai) {
				$perpegawai = "AND a.id_pegawai = '$id_pegawai'";
			}
			$pegawai = $this->db->query("SELECT a.* FROM izin_pegawai a  WHERE YEAR(STR_TO_DATE(a.tgl_tidak_hadir, '%d-%m-%Y')) = $tahun AND a.status_approval !=0 $perpegawai
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
            if ($tampil == 'tampil' || $tampil == '') {
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
            } else {
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
            } else {
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
            } else {
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
            $pegawai = $this->db->query("SELECT nama_pegawai, jabatan FROM pegawai_jabatan WHERE id_pegawai = ? ", [$id_pegawai])->row_array();
            $nama_pegawai = $pegawai['nama_pegawai'] ?? '-';
            $jabatan = $pegawai['jabatan'] ?? '-';
            $start = strtotime($dari_tanggal);
            $end = strtotime($sampai_tanggal);
            for ($ulang = $start; $ulang <= $end; $ulang = strtotime('+1 day', $ulang)) {
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
            for ($i = 1; $i <= $total_days; $i++) {
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
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $bulan_formatted = sprintf("%02d", $bulan);
                $total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
                for ($hari = 1; $hari <= $total_days; $hari++) {
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
                }
            }
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

        $this->db->select('a.id as id_pegawai, a.nama_pegawai, b.id as id_gaji, b.gaji_pokok, 
		b.struktural, b.tunjangan_pendidikan, b.wali_kelas');
        $this->db->from('pegawai a');
        $this->db->join('gaji b', 'a.id = b.id_pegawai', 'inner');
        $this->db->order_by("
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM pegawai_jabatan pj
            WHERE pj.id_pegawai = a.id
            AND LOWER(TRIM(pj.jabatan)) = 'kepala sekolah'
        ) THEN 0
        ELSE 1
    END", 'ASC', false);
        $this->db->order_by("STR_TO_DATE(a.tmt, '%d-%m-%Y')", 'ASC', false);
        $this->db->order_by('a.no_urut', 'ASC');
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
                    $potongan[$pd['id']] += $pd['nominal'];
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
        } else if ($filter == 'bulan') {
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
        } else {
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
            'tanggal_laporan' => $tanggal_laporan
        ];
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
                        WHERE id_kode_akun = '" . $a['id'] . "'
                        AND bulan < '$bulan'
                        AND tahun = '$tahun'
                    ),0
                )
                -
                COALESCE(
                    (
                        SELECT SUM(jumlah)
                        FROM pengeluaran
                        WHERE id_kode_akun = '" . $a['id'] . "'
                        AND bulan < '$bulan'
                        AND tahun = '$tahun'
                    ),0
                ) as saldo
        ")->row_array()['saldo'] ?? 0;

            // MASUK BULAN INI
            $masuk = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pemasukan
            WHERE id_kode_akun = '" . $a['id'] . "'
            AND bulan = '$bulan'
            AND tahun = '$tahun'
        ")->row_array()['total'] ?? 0;

            // KELUAR BULAN INI
            $keluar = $this->db->query("
            SELECT COALESCE(SUM(jumlah),0) as total
            FROM pengeluaran
            WHERE filter_kode_akun = '" . $a['id'] . "'
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
            'list_bulan' => $list_bulan
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
        } elseif ($semester == 'Genap') {
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
            'list_bulan'    => $list_bulan,
            'semester'    => $semester,
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
        foreach ($jenis as &$j) {
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
        $result = [
            'semester' => $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
            'data_laporan' => $jenis,
            'grand_total' => $grand_total
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
        $result = [
            'data_laporan' => $hasil,
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
        $result = [
            'data_laporan' => $data_laporan,
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
        $result = [
            'data_laporan' => $data_laporan,
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
                'total_rencana' => 0,
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
                WHERE id_kode_akun = '" . $a['id'] . "'
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
                WHERE d.kode_akun = '" . $a['id'] . "'
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
        $result = [
            'semester' => $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
            'status' => 'Tahun Ajaran',
            'data_laporan' => $data_laporan,
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
            WHERE j.kode_akun = '" . $a['id'] . "'
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
            WHERE d.kode_akun = '" . $a['id'] . "'
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
                WHERE id_kode_akun = '" . $a['id'] . "'
                AND bulan = '$bulan_db'
                AND tahun = '$tahun_realisasi'
            ")->row_array();

                $row['bulan'][$b]['realisasi'] = (float) ($pemasukan['total'] ?? 0);
            }

            $data_laporan[] = $row;
        }
        $result = [
            // 'status'          => empty($semester) ? 'Tahun Ajaran' : 'Semester',
            'semester' => $semester,
            'tahun_ajaran' => $get_tahun_ajaran['periode'],
            'bulan_laporan'   => $bulan_laporan,
            'data_laporan' => $data_laporan,
        ];

        $data = $result;
        echo json_encode($data);
    }

    public function laporan_penerimaan_gaji_guru_karyawan()
    {
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');
        $data_hari_efektif = $this->db->get_where('hari_efektif', ['bulan' => $bulan, 'tahun' => $tahun])->row_array();
        $hari_kerja = (int) ($data_hari_efektif['hari_efektif'] ?? 0);

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
        $this->db->join('gaji c', 'a.id = c.id_pegawai', 'inner');
        $this->db->order_by("
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM pegawai_jabatan pj
            WHERE pj.id_pegawai = a.id
            AND LOWER(TRIM(pj.jabatan)) = 'kepala sekolah'
        ) THEN 0
        ELSE 1
    END", 'ASC', false);
        $this->db->order_by("STR_TO_DATE(a.tmt, '%d-%m-%Y')", 'ASC', false);
        $this->db->order_by('a.no_urut', 'ASC');
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

    public function laporan_payroll_gaji_guru_karyawan()
    {
        $bulan = str_pad((int) $this->input->post('bulan'), 2, '0', STR_PAD_LEFT);
        $tahun = (int) $this->input->post('tahun');

        $data_laporan = [];
        $total_gaji_bersih = 0;
        $tanggal_laporan = '';

        if ((int) $bulan >= 1 && (int) $bulan <= 12 && $tahun > 0) {
            $data_laporan = $this->db->query("
            SELECT 
                p.id AS id_pegawai,
                p.nama_pegawai,
                p.no_rekening,
                pg.gaji_bersih
            FROM penggajian pg
            JOIN pegawai p ON p.id = pg.id_pegawai
            WHERE pg.bulan = ?
              AND pg.tahun = ?
            ORDER BY
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM pegawai_jabatan pj
                        WHERE pj.id_pegawai = p.id
                          AND LOWER(TRIM(pj.jabatan)) = 'kepala sekolah'
                    ) THEN 0
                    ELSE 1
                END ASC, STR_TO_DATE(p.tmt, '%d-%m-%Y') ASC, p.id ASC", [$bulan, $tahun])->result_array();

            $total_gaji_bersih = array_sum(array_column($data_laporan, 'gaji_bersih'));

            $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
            $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
        }

        $result = [
            'data_laporan' => $data_laporan,
            'total_gaji_bersih' => $total_gaji_bersih,
            'tanggal_laporan' => $tanggal_laporan,
        ];

        echo json_encode($result);
    }

    // Laporan Pembayaran: satu fungsi untuk Tanggal, Bulan, dan Tahun seperti Presensi Pegawai.
    public function laporan_pembayaran_spp()
    {
        $input = $this->input->post(NULL, TRUE);
        $input = $input ?? array();
        $filter = $input['filter'] ?? 'tanggal';
        $opsi = array(
            'periode' => max(0, (int) ($input['spp_id_periode_pembayaran'] ?? 0)),
            'kelas' => max(0, (int) ($input['spp_id_kelas_setting'] ?? 0)),
            'jenis' => max(0, (int) ($input['spp_id_jenis_tagihan'] ?? 0)),
            'metode' => max(0, (int) ($input['spp_id_metode'] ?? 0)),
            'kasbank' => max(0, (int) ($input['spp_id_kasbank'] ?? 0)),
            'status' => $input['spp_status_pembayaran'] ?? 'Aktif'
        );
        if (!in_array($opsi['status'], array('Aktif', 'Dibatalkan', 'Semua'), true)) {
            $opsi['status'] = 'Aktif';
        }

        // Tanggal, Bulan, Tahun mengikuti percabangan laporan_presensi_pegawai.
        // Batas selalu berdasarkan tanggal pembayaran, bukan bulan tagihan.
        $error = '';
        if ($filter === 'tanggal') {
            $dari = trim((string) ($input['dari_tanggal'] ?? ''));
            $sampai = trim((string) ($input['sampai_tanggal'] ?? ''));
            $awal = null;
            $akhir = null;
            foreach (array('!Y-m-d', '!d-m-Y') as $format) {
                $a = DateTime::createFromFormat($format, $dari);
                $b = DateTime::createFromFormat($format, $sampai);
                if ($a && $a->format(substr($format, 1)) === $dari) $awal = $a;
                if ($b && $b->format(substr($format, 1)) === $sampai) $akhir = $b;
            }
            if (!$awal || !$akhir || $awal > $akhir) {
                $error = 'Rentang tanggal tidak valid.';
            } else {
                $mulai = $awal->format('Y-m-d');
                $selesai = $akhir->format('Y-m-d');
                $judul = $awal->format('d-m-Y') . ' s/d ' . $akhir->format('d-m-Y');
                $status_filter = 'Tanggal';
            }
        } else if ($filter === 'bulan') {
            $bulan = (int) ($input['filter_bulan'] ?? $input['bulan'] ?? 0);
            $tahun = (int) ($input['filter_tahun'] ?? $input['tahun'] ?? 0);
            if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
                $error = 'Bulan atau tahun tidak valid.';
            } else {
                $mulai = sprintf('%04d-%02d-01', $tahun, $bulan);
                $selesai = date('Y-m-t', strtotime($mulai));
                $nama_bulan = array(
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
                    12 => 'Desember'
                );
                $judul = $nama_bulan[$bulan] . ' ' . $tahun;
                $status_filter = 'Bulan';
            }
        } else if ($filter === 'tahun') {
            $tahun = (int) ($input['single_filter_tahun'] ?? $input['tahun'] ?? 0);
            if ($tahun < 2000 || $tahun > 2100) {
                $error = 'Tahun tidak valid.';
            } else {
                $mulai = sprintf('%04d-01-01', $tahun);
                $selesai = sprintf('%04d-12-31', $tahun);
                $judul = (string) $tahun;
                $status_filter = 'Tahun';
            }
        } else {
            $error = 'Jenis filter tidak valid.';
        }

        $grouped_by_tanggal = array();
        $jumlah_transaksi = 0;
        $total_pembayaran = 0;
        $total_dibatalkan = 0;
        if ($error === '') {
            $sql = "SELECT tp.id, tp.tanggal_transaksi, tp.waktu_transaksi, tp.no_transaksi,
                    tp.nama_siswa, tp.nama_kelas, tp.nama_metode_pembayaran, tp.nama_user,
                    tp.status_transaksi,
                    GROUP_CONCAT(DISTINCT pd.nama_jenis_tagihan ORDER BY pd.nama_jenis_tagihan SEPARATOR ', ') AS jenis_tagihan,
                    SUM(CASE WHEN tp.status_transaksi='Aktif' THEN CAST(pd.nominal_bayar AS DECIMAL(18,2)) ELSE 0 END) AS total_pembayaran,
                    SUM(CASE WHEN tp.status_transaksi='Dibatalkan' THEN CAST(pd.nominal_bayar AS DECIMAL(18,2)) ELSE 0 END) AS nominal_dibatalkan
                FROM tagihan_pembayaran tp
                JOIN tagihan_pembayaran_detail pd ON pd.id_pembayaran=tp.id
                    AND pd.status_detail=tp.status_transaksi
                JOIN tagihan_siswa ts ON ts.id=pd.id_tagihan_siswa
                WHERE tp.status_transaksi IN ('Aktif','Dibatalkan')
                  AND STR_TO_DATE(tp.tanggal_transaksi,'%d-%m-%Y') BETWEEN ? AND ?";
            $params = array($mulai, $selesai);
            if ($opsi['periode'] > 0) {
                $sql .= ' AND ts.id_periode=?';
                $params[] = $opsi['periode'];
            }
            if ($opsi['kelas'] > 0) {
                $sql .= ' AND ts.id_kelas_setting=?';
                $params[] = $opsi['kelas'];
            }
            if ($opsi['jenis'] > 0) {
                $sql .= ' AND pd.id_jenis_tagihan=?';
                $params[] = $opsi['jenis'];
            }
            if ($opsi['metode'] > 0) {
                $sql .= ' AND tp.id_metode_pembayaran=?';
                $params[] = $opsi['metode'];
            }
            if ($opsi['kasbank'] > 0) {
                $sql .= ' AND tp.id_kasbank_penerima=?';
                $params[] = $opsi['kasbank'];
            }
            if ($opsi['status'] !== 'Semua') {
                $sql .= ' AND tp.status_transaksi=?';
                $params[] = $opsi['status'];
            }
            $sql .= " GROUP BY tp.id, tp.tanggal_transaksi, tp.waktu_transaksi,
                        tp.no_transaksi, tp.nama_siswa, tp.nama_kelas,
                        tp.nama_metode_pembayaran, tp.nama_user, tp.status_transaksi
                      ORDER BY STR_TO_DATE(tp.tanggal_transaksi,'%d-%m-%Y') ASC,
                               tp.waktu_transaksi ASC, tp.id ASC";
            $rows = $this->db->query($sql, $params)->result_array();
            foreach ($rows as $row) {
                $tanggal = $row['tanggal_transaksi'];
                if (!isset($grouped_by_tanggal[$tanggal])) $grouped_by_tanggal[$tanggal] = array();
                $grouped_by_tanggal[$tanggal][] = $row;
                $jumlah_transaksi++;
                $total_pembayaran += (float) $row['total_pembayaran'];
                $total_dibatalkan += (float) $row['nominal_dibatalkan'];
            }
        }

        $summary = array('Jumlah Transaksi' => $jumlah_transaksi);
        if ($opsi['status'] !== 'Dibatalkan') $summary['Total Pembayaran'] = $total_pembayaran;
        if ($opsi['status'] !== 'Aktif') $summary['Total Dibatalkan'] = $total_dibatalkan;

        // Hanya metadata yang terlihat pada laporan; pilihan filter lainnya tetap berlaku di query.
        $filters = array('Periode' => $error === '' ? $judul : '-');
        if (!$opsi['periode']) {
            $filters['Tahun Ajaran'] = 'Semua Tahun Ajaran';
        } else {
            $tahun_ajaran = $this->db->select('periode')->where('id', $opsi['periode'])->get('master_tahun_ajaran')->row_array();
            $filters['Tahun Ajaran'] = $tahun_ajaran['periode'] ?? '-';
        }
        if ($error !== '') {
            echo json_encode(array('result' => 'false', 'message' => $error));
            return;
        }
        echo json_encode(array(
            'result' => 'true',
            'grouped_by_tanggal' => $grouped_by_tanggal,
            'summary' => $summary,
            'filters' => $filters
        ));
    }

     public function laporan_per_siswa_spp()
    {
        $id_periode = (int) $this->input->post('spp_id_periode_siswa');
        $id_siswa = (int) $this->input->post('spp_id_siswa');
        if ($id_periode <= 0 || $id_siswa <= 0) {
            $this->output->set_status_header(422)->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'message' => 'Pilih Tahun Ajaran dan satu Siswa terlebih dahulu.')));
            return;
        }
        $data = $this->dataTagihanPerSiswa($id_periode, $id_siswa);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /** Data khusus Laporan Tagihan Per Siswa; tidak mengubah transaksi atau saldo. */
    private function dataTagihanPerSiswa($id_periode, $id_siswa)
    {
        $tahun = $this->db->select('periode')->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
        $nama_tahun = trim((string) ($tahun['periode'] ?? ''));
        if ($nama_tahun === '') {
            return array('tahun_ajaran' => '-', 'siswa_data' => array(), 'ringkasan_total' => array());
        }

        $sql = "SELECT ts.*, COALESCE(bayar.jumlah_cicilan,0) AS jumlah_cicilan,
                       COALESCE(bayar.total_bayar,0) AS pembayaran_aktif
                FROM tagihan_siswa ts
                LEFT JOIN (
                    SELECT pd.id_tagihan_siswa, COUNT(pd.id) AS jumlah_cicilan,
                           SUM(CAST(pd.nominal_bayar AS DECIMAL(18,2))) AS total_bayar
                    FROM tagihan_pembayaran_detail pd
                    JOIN tagihan_pembayaran p ON p.id=pd.id_pembayaran
                    WHERE pd.status_detail='Aktif' AND p.status_transaksi='Aktif'
                    GROUP BY pd.id_tagihan_siswa
                ) bayar ON bayar.id_tagihan_siswa=ts.id
                WHERE ts.id_periode=? AND ts.id_siswa=? AND ts.status_pembayaran<>'Dibatalkan'
                  AND (ts.status_tagihan='Aktif' OR COALESCE(bayar.total_bayar,0)>0)";
        $params = array($id_periode, $id_siswa);
        $sql .= ' ORDER BY ts.nama_siswa ASC, ts.id_siswa ASC, ts.tahun ASC, ts.bulan ASC, ts.nama_tagihan ASC, ts.id ASC';
        $tagihan = $this->db->query($sql, $params)->result_array();

        // Rincian asli transaksi: hanya pembayaran dan detail yang masih aktif.
        $sql_bayar = "SELECT ts.id_siswa, ts.nis, ts.nama_siswa, ts.nama_kelas, ts.id_periode,
                             ts.id AS id_tagihan_siswa, ts.no_tagihan, ts.nama_tagihan, ts.nama_bulan,
                             p.no_transaksi, p.tanggal_transaksi, p.waktu_transaksi,
                             p.nama_metode_pembayaran, pd.nominal_bayar,
                             pd.sisa_sebelum, pd.sisa_setelah, pd.status_setelah
                      FROM tagihan_pembayaran_detail pd
                      JOIN tagihan_pembayaran p ON p.id=pd.id_pembayaran
                      JOIN tagihan_siswa ts ON ts.id=pd.id_tagihan_siswa
                      WHERE ts.id_periode=? AND ts.id_siswa=? AND p.status_transaksi='Aktif'
                        AND pd.status_detail='Aktif' AND ts.status_pembayaran<>'Dibatalkan'";
        $params_bayar = array($id_periode, $id_siswa);
        $sql_bayar .= " ORDER BY ts.nama_siswa ASC, ts.id_siswa ASC,
                        STR_TO_DATE(p.tanggal_transaksi,'%d-%m-%Y') ASC,
                        p.waktu_transaksi ASC, p.id ASC, pd.id ASC";
        $pembayaran = $this->db->query($sql_bayar, $params_bayar)->result_array();

        // Tahun asal diambil dari master, bukan menganggap ID tahun ajaran selalu berurutan.
        $tahun_awal = preg_match('/^(\d{4})/', $nama_tahun, $match) ? (int) $match[1] : 0;
        $tunggakan_lama = array();
        if ($tahun_awal > 0) {
            $tanggal_jatuh_tempo = "STR_TO_DATE(COALESCE(NULLIF(ts.tanggal_jatuh_tempo,''),ts.tanggal_generate),'%d-%m-%Y')";
            $sql_lama = "SELECT ts.id_siswa, ts.nis, ts.nama_siswa, ts.nama_kelas,
                                ts.periode AS tahun_asal, ts.nama_tagihan, ts.no_tagihan,
                                ts.tanggal_jatuh_tempo, ts.sisa_tagihan
                         FROM tagihan_siswa ts
                         JOIN master_tahun_ajaran ta ON ta.id=ts.id_periode
                         WHERE CAST(LEFT(TRIM(ta.periode),4) AS UNSIGNED) < ?
                           AND ts.status_tagihan='Aktif' AND ts.dianggap_tunggakan='Ya'
                           AND ts.status_pembayaran NOT IN ('Lunas','Dibebaskan','Dibatalkan')
                           AND CAST(ts.sisa_tagihan AS DECIMAL(18,2))>0
                           AND $tanggal_jatuh_tempo <= CURDATE() AND ts.id_siswa=?";
            $params_lama = array($tahun_awal, $id_siswa);
            $sql_lama .= ' ORDER BY ts.nama_siswa ASC, ts.id_siswa ASC, ta.periode ASC, ts.nama_tagihan ASC';
            $tunggakan_lama = $this->db->query($sql_lama, $params_lama)->result_array();
        }

        $siswa_data = array();
        $buat_siswa = function ($row) use (&$siswa_data) {
            $id = (int) $row['id_siswa'];
            if (!isset($siswa_data[$id])) {
                $siswa_data[$id] = array(
                    'id_siswa' => $id, 'nama_siswa' => (string) ($row['nama_siswa'] ?? ''),
                    'nis' => (string) ($row['nis'] ?? ''),
                    'nama_kelas' => (string) ($row['nama_kelas'] ?? '-'),
                    'tagihan' => array(), 'pembayaran' => array(), 'cicilan' => array(), 'tunggakan_sebelumnya' => array(),
                    'ringkasan' => array('total_tagihan' => 0, 'total_pembayaran' => 0,
                                         'total_sisa' => 0, 'tunggakan_sebelumnya' => 0,
                                         'jumlah_tagihan' => 0, 'jumlah_pembayaran' => 0)
                );
            }
            return $id;
        };
        foreach ($tagihan as $row) {
            $id = $buat_siswa($row);
            $bayar = max(0, (float) $row['pembayaran_aktif']);
            $sisa = ($row['status_tagihan'] === 'Aktif' && !in_array($row['status_pembayaran'], array('Dibebaskan','Dibatalkan'), true))
                ? max(0, (float) $row['sisa_tagihan']) : 0;
            $row['target_efektif'] = $bayar + $sisa;
            $row['total_pembayaran'] = $bayar;
            $row['sisa_efektif'] = $sisa;
            $siswa_data[$id]['tagihan'][] = $row;
            $siswa_data[$id]['ringkasan']['jumlah_tagihan']++;
            $siswa_data[$id]['ringkasan']['total_tagihan'] += $row['target_efektif'];
            $siswa_data[$id]['ringkasan']['total_sisa'] += $sisa;
        }
        foreach ($pembayaran as $row) {
            $id = $buat_siswa($row);
            $siswa_data[$id]['pembayaran'][] = $row;
            $siswa_data[$id]['ringkasan']['jumlah_pembayaran']++;
            $siswa_data[$id]['ringkasan']['total_pembayaran'] += (float) $row['nominal_bayar'];
        }
        // Riwayat cicilan adalah bagian dari pembayaran, bukan transaksi tambahan.
        // Gunakan kondisi saat transaksi disimpan (sisa_setelah/status_setelah),
        // sehingga pembayaran yang langsung lunas tidak keliru diberi nomor cicilan.
        foreach ($siswa_data as &$data_siswa) {
            $jumlah_per_tagihan = array();
            $ada_pembayaran_sebagian = array();
            foreach ($data_siswa['pembayaran'] as $baris_bayar) {
                $kunci = (string) $baris_bayar['id_tagihan_siswa'];
                $jumlah_per_tagihan[$kunci] = ($jumlah_per_tagihan[$kunci] ?? 0) + 1;
                if ((float) ($baris_bayar['sisa_setelah'] ?? 0) > 0
                    || strcasecmp(trim((string) ($baris_bayar['status_setelah'] ?? '')), 'Dibayar Sebagian') === 0) {
                    $ada_pembayaran_sebagian[$kunci] = true;
                }
            }
            $urutan_cicilan = array();
            foreach ($data_siswa['pembayaran'] as $baris_bayar) {
                $kunci = (string) $baris_bayar['id_tagihan_siswa'];
                if ($jumlah_per_tagihan[$kunci] > 1 || !empty($ada_pembayaran_sebagian[$kunci])) {
                    $urutan_cicilan[$kunci] = ($urutan_cicilan[$kunci] ?? 0) + 1;
                    $baris_bayar['cicilan_ke'] = $urutan_cicilan[$kunci];
                    $data_siswa['cicilan'][] = $baris_bayar;
                }
            }
        }
        unset($data_siswa);

        foreach ($tunggakan_lama as $row) {
            $id = $buat_siswa($row);
            $siswa_data[$id]['tunggakan_sebelumnya'][] = $row;
            $siswa_data[$id]['ringkasan']['tunggakan_sebelumnya'] += max(0, (float) $row['sisa_tagihan']);
        }
        if (!isset($siswa_data[$id_siswa])) {
            $siswa = $this->db->select('id AS id_siswa, nama_lengkap AS nama_siswa, nis')
                ->where('id', $id_siswa)->get('siswa')->row_array();
            if ($siswa) $buat_siswa($siswa);
        }
        uasort($siswa_data, function ($a, $b) {
            return strcasecmp($a['nama_siswa'], $b['nama_siswa']);
        });
        $ringkasan_total = array('total_tagihan' => 0, 'total_pembayaran' => 0,
                                 'total_sisa' => 0, 'tunggakan_sebelumnya' => 0);
        foreach ($siswa_data as $item) {
            foreach ($ringkasan_total as $key => $nilai) {
                $ringkasan_total[$key] += $item['ringkasan'][$key];
            }
        }
        return array('tahun_ajaran' => $nama_tahun, 'siswa_data' => array_values($siswa_data),
                     'ringkasan_total' => $ringkasan_total);
    }

    public function laporan_per_kelas_spp()
    {
        $filter = $this->input->post('filter');
        $dari_tanggal = $this->input->post('dari_tanggal');
        $sampai_tanggal = $this->input->post('sampai_tanggal');
        $bulan = (int) $this->input->post('bulan');
        $tahun = (int) $this->input->post('tahun');
        $single_filter_tahun = (int) $this->input->post('single_filter_tahun');
        $id_periode = (int) $this->input->post('spp_id_periode_kelas');
        $id_jenis = (int) $this->input->post('spp_id_jenis_tagihan_kelas');

        $sql = "SELECT ts.id_kelas_setting, ks.nama_kelas,
			COUNT(DISTINCT ts.id_siswa) AS jumlah_siswa,
			SUM(CAST(ts.nominal_tagihan AS DECIMAL(15,2))) AS total_tagihan,
			SUM(CAST(ts.nominal_dibayar AS DECIMAL(15,2))) AS total_dibayar,
			SUM(CAST(ts.sisa_tagihan AS DECIMAL(15,2))) AS total_sisa,
			CASE WHEN SUM(CAST(ts.nominal_tagihan AS DECIMAL(15,2))) > 0
			THEN ROUND(SUM(CAST(ts.nominal_dibayar AS DECIMAL(15,2))) /
				SUM(CAST(ts.nominal_tagihan AS DECIMAL(15,2))) * 100, 2)
			ELSE 0 END AS realisasi
			FROM tagihan_siswa ts
			INNER JOIN kelas_setting ks ON ks.id = ts.id_kelas_setting AND ks.id_periode = ts.id_periode
			WHERE ts.status_tagihan = 'Aktif'";
        $params = array();

        if ($filter == 'tanggal') {
            if (empty($dari_tanggal) || empty($sampai_tanggal)) {
                echo json_encode(array('data_laporan' => array(), 'total_tagihan' => 0, 'total_dibayar' => 0, 'total_sisa' => 0, 'total_realisasi' => 0));
                return;
            }
            $sql .= " AND STR_TO_DATE(ts.tanggal_generate, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
            $params[] = $dari_tanggal;
            $params[] = $sampai_tanggal;
        } else if ($filter == 'bulan') {
            $sql .= ' AND ts.bulan = ? AND ts.tahun = ?';
            $params[] = $bulan;
            $params[] = $tahun;
        } else {
            $sql .= ' AND ts.tahun = ?';
            $params[] = $single_filter_tahun;
        }
        if ($id_periode > 0) {
            $sql .= ' AND ts.id_periode = ?';
            $params[] = $id_periode;
        }
        if ($id_jenis > 0) {
            $sql .= ' AND ts.id_jenis_tagihan = ?';
            $params[] = $id_jenis;
        }
        $sql .= ' GROUP BY ts.id_kelas_setting, ks.nama_kelas ORDER BY ks.nama_kelas ASC';

        $rows = $this->db->query($sql, $params)->result_array();
        $total_tagihan = 0;
        $total_dibayar = 0;
        $total_sisa = 0;
        foreach ($rows as $row) {
            $total_tagihan += (float) $row['total_tagihan'];
            $total_dibayar += (float) $row['total_dibayar'];
            $total_sisa += (float) $row['total_sisa'];
        }
        $total_realisasi = $total_tagihan > 0 ? round($total_dibayar / $total_tagihan * 100, 2) : 0;
        echo json_encode(array(
            'data_laporan' => $rows,
            'total_tagihan' => $total_tagihan,
            'total_dibayar' => $total_dibayar,
            'total_sisa' => $total_sisa,
            'total_realisasi' => $total_realisasi
        ));
    }

 public function laporan_per_jenis_tagihan_spp()
    {
        $id_periode = max(0, (int) $this->input->post('spp_id_periode_jenis'));
        $id_kelas = max(0, (int) $this->input->post('spp_id_kelas_jenis'));
        if (!$id_periode || !$this->db->where('id', $id_periode)->count_all_results('master_tahun_ajaran')) {
            echo json_encode(array('data_laporan' => array(), 'total_target' => 0,
                'total_pembayaran' => 0, 'total_sisa' => 0, 'total_realisasi' => 0));
            return;
        }

        // Pembayaran sah tetap masuk meskipun siswa kemudian dibebaskan.
        // Target efektif = pembayaran yang sah + sisa kewajiban aktif.
        $sql = "SELECT ts.id_jenis_tagihan, ts.id_tagihan_master,
    MAX(ts.nama_jenis_tagihan) AS nama_jenis_tagihan,
    MAX(ts.nama_tagihan) AS nama_tagihan,
    COUNT(DISTINCT CASE WHEN
        COALESCE(bayar.total_bayar, 0) > 0 OR
        (ts.status_tagihan = 'Aktif' AND
         ts.status_pembayaran NOT IN ('Dibebaskan', 'Dibatalkan') AND
         CAST(ts.sisa_tagihan AS DECIMAL(18,2)) > 0)
        THEN ts.id_siswa ELSE NULL END) AS jumlah_siswa,
    COALESCE(SUM(COALESCE(bayar.total_bayar, 0)),0) AS total_pembayaran,
    COALESCE(SUM(CASE
        WHEN ts.status_tagihan = 'Aktif' AND
             ts.status_pembayaran NOT IN ('Dibebaskan','Dibatalkan')
        THEN GREATEST(CAST(ts.sisa_tagihan AS DECIMAL(18,2)), 0)
        ELSE 0 END),0) AS total_sisa
FROM tagihan_siswa ts
LEFT JOIN (
    SELECT d.id_tagihan_siswa,
           SUM(CAST(d.nominal_bayar AS DECIMAL(18,2))) AS total_bayar
    FROM tagihan_pembayaran_detail d
    JOIN tagihan_pembayaran p ON p.id = d.id_pembayaran
    WHERE p.status_transaksi = 'Aktif' AND d.status_detail = 'Aktif'
    GROUP BY d.id_tagihan_siswa
) bayar ON bayar.id_tagihan_siswa = ts.id
WHERE ts.id_periode = ?
  AND ts.status_pembayaran <> 'Dibatalkan'
  AND (ts.status_tagihan = 'Aktif' OR COALESCE(bayar.total_bayar, 0) > 0)";
        $params = array($id_periode);
        if ($id_kelas > 0) {
            $sql .= ' AND ts.id_kelas_setting = ?';
            $params[] = $id_kelas;
        }
        $sql .= " GROUP BY ts.id_jenis_tagihan, ts.id_tagihan_master
                  HAVING total_pembayaran > 0 OR total_sisa > 0
                  ORDER BY nama_jenis_tagihan ASC, nama_tagihan ASC";
        $rows = $this->db->query($sql, $params)->result_array();
        $total_target = 0;
        $total_pembayaran = 0;
        $total_sisa = 0;
        foreach ($rows as &$row) {
            $row['total_pembayaran'] = (float) $row['total_pembayaran'];
            $row['total_sisa'] = (float) $row['total_sisa'];
            $row['total_target'] = $row['total_pembayaran'] + $row['total_sisa'];
            $row['realisasi'] = $row['total_target'] > 0
                ? round($row['total_pembayaran'] / $row['total_target'] * 100, 2) : 0;
            $total_target += $row['total_target'];
            $total_pembayaran += $row['total_pembayaran'];
            $total_sisa += $row['total_sisa'];
        }
        unset($row);
        echo json_encode(array('data_laporan' => $rows, 'total_target' => $total_target,
            'total_pembayaran' => $total_pembayaran, 'total_sisa' => $total_sisa,
            'total_realisasi' => $total_target > 0 ? round($total_pembayaran / $total_target * 100, 2) : 0));
    }



    public function laporan_tunggakan_spp()
    {
        $id_periode = (int) $this->input->post('spp_id_periode_tunggakan');
        $id_kelas = (int) $this->input->post('spp_id_kelas_tunggakan');
        $id_jenis = (int) $this->input->post('spp_id_jenis_tunggakan');

        $tanggalField = "STR_TO_DATE(COALESCE(NULLIF(ts.tanggal_jatuh_tempo,''),ts.tanggal_generate),'%d-%m-%Y')";
        $sql = "SELECT ts.id_siswa, MAX(ts.nis) AS nis, MAX(ts.nama_siswa) AS nama_siswa,
				COALESCE(MAX(ks.nama_kelas),MAX(ts.nama_kelas),'-') AS nama_kelas,
				ts.periode AS tahun_asal, COUNT(ts.id) AS jumlah_tagihan,
				COALESCE(SUM(CAST(ts.sisa_tagihan AS DECIMAL(15,2))),0) AS total_tunggakan
			FROM tagihan_siswa ts
			JOIN siswa s ON s.id=ts.id_siswa
			LEFT JOIN kelas_setting ks ON ks.id=ts.id_kelas_setting
			WHERE ts.status_tagihan='Aktif'
			AND ts.dianggap_tunggakan='Ya'
			AND CAST(ts.sisa_tagihan AS DECIMAL(15,2))>0
			AND ts.status_pembayaran NOT IN ('Lunas','Dibebaskan','Dibatalkan')
			AND $tanggalField<=CURDATE()";
        $params = array();
        if ($id_periode > 0) {
            $sql .= ' AND ts.id_periode=?';
            $params[] = $id_periode;
        }
        if ($id_kelas > 0) {
            $sql .= ' AND ts.id_kelas_setting=?';
            $params[] = $id_kelas;
        }
        if ($id_jenis > 0) {
            $sql .= ' AND ts.id_jenis_tagihan=?';
            $params[] = $id_jenis;
        }
        // Pertahankan cakupan siswa Aktif seperti default laporan sebelumnya.
        $sql .= " AND UPPER(TRIM(s.status_aktif))='AKTIF'";
        $sql .= ' GROUP BY ts.id_siswa, ts.id_periode, ts.periode ORDER BY total_tunggakan DESC, nama_siswa ASC';
        $rows = $this->db->query($sql, $params)->result_array();
        $total = 0;
        foreach ($rows as $row) $total += (float) $row['total_tunggakan'];

        $nama_tahun = 'Semua Tahun Ajaran';
        if ($id_periode > 0) {
            $periode = $this->db->select('periode')->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
            $nama_tahun = $periode['periode'] ?? '-';
        }
        $nama_kelas = 'Semua Kelas';
        if ($id_kelas > 0) {
            $kelas = $this->db->select('nama_kelas')->where('id', $id_kelas)->get('kelas_setting')->row_array();
            $nama_kelas = $kelas['nama_kelas'] ?? '-';
        }
        $nama_jenis = 'Semua Jenis';
        if ($id_jenis > 0) {
            $jenis = $this->db->select('nama_jenis')->where('id', $id_jenis)->get('tagihan_jenis')->row_array();
            $nama_jenis = $jenis['nama_jenis'] ?? '-';
        }
        echo json_encode(array(
            'result' => 'true',
            'columns' => array('nama_siswa' => 'Siswa', 'nis' => 'NIS', 'nama_kelas' => 'Kelas', 'tahun_asal' => 'Tahun Asal', 'jumlah_tagihan' => 'Jumlah Tagihan', 'total_tunggakan' => 'Total Tunggakan'),
            'money' => array('total_tunggakan'),
            'rows' => $rows,
            'summary' => array('Jumlah Siswa' => count($rows), 'Total Tunggakan' => $total),
            'filters' => array('Tahun Ajaran' => $nama_tahun, 'Kelas' => $nama_kelas, 'Jenis Tagihan' => $nama_jenis)
        ));
    }


    public function laporan_tabungan_spp()
    {
        $filter = (string) $this->input->post('filter');
        $id_periode = (int) $this->input->post('spp_id_periode_tabungan');
        $id_kelas = (int) $this->input->post('spp_id_kelas_tabungan');
        $jenis = trim((string) $this->input->post('spp_jenis_tabungan'));
        $status = trim((string) $this->input->post('spp_status_tabungan'));

        // Kedua tabel Tabungan menyimpan tanggal transaksi dalam format d-m-Y.
        $where = array('1=1');
        $paramsFilter = array();
        $nama_bulan = array(1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember');

        if ($filter === 'tanggal') {
            $dari = (string) $this->input->post('dari_tanggal');
            $sampai = (string) $this->input->post('sampai_tanggal');
            if ($dari === '' || $sampai === '') {
                echo json_encode(array('result' => 'false', 'message' => 'Pilih rentang tanggal laporan.'));
                return;
            }
            $where[] = "STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
            $paramsFilter[] = $dari;
            $paramsFilter[] = $sampai;
            $nama_periode = date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai));
        } elseif ($filter === 'bulan') {
            $bulan = (int) $this->input->post('bulan');
            $tahun = (int) $this->input->post('tahun');
            $where[] = "MONTH(STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y'))=? AND YEAR(STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y'))=?";
            $paramsFilter[] = $bulan;
            $paramsFilter[] = $tahun;
            $nama_periode = ($nama_bulan[$bulan] ?? '-') . ' ' . $tahun;
        } else {
            $tahun = (int) $this->input->post('single_filter_tahun');
            $where[] = "YEAR(STR_TO_DATE(tanggal_transaksi, '%d-%m-%Y'))=?";
            $paramsFilter[] = $tahun;
            $nama_periode = (string) $tahun;
        }

        if ($id_periode > 0) {
            $where[] = 'id_periode=?';
            $paramsFilter[] = $id_periode;
        }
        if ($id_kelas > 0) {
            $where[] = 'id_kelas_setting=?';
            $paramsFilter[] = $id_kelas;
        }
        if ($status !== '') {
            $where[] = 'status_transaksi=?';
            $paramsFilter[] = $status;
        }

        // UNION ALL tidak menggabungkan setoran dan penarikan yang bernomor ID sama.
        $columns = 'id, no_transaksi, tanggal_transaksi, id_siswa, nis, nama_siswa, nama_kelas, metode_pembayaran, status_transaksi, nama_user, nominal';
        $parts = array();
        $params = array();
        if ($jenis === '' || $jenis === 'Setoran') {
            $parts[] = "SELECT $columns, 'Setoran' AS jenis FROM tabungan_siswa_setoran WHERE " . implode(' AND ', $where);
            $params = array_merge($params, $paramsFilter);
        }
        if ($jenis === '' || $jenis === 'Penarikan') {
            $parts[] = "SELECT $columns, 'Penarikan' AS jenis FROM tabungan_siswa_penarikan WHERE " . implode(' AND ', $where);
            $params = array_merge($params, $paramsFilter);
        }

        $rows = array();
        if (!empty($parts)) {
            $sql = 'SELECT * FROM (' . implode(' UNION ALL ', $parts) . ') AS transaksi '
                . "ORDER BY STR_TO_DATE(transaksi.tanggal_transaksi,'%d-%m-%Y') ASC, transaksi.id ASC";
            $rows = $this->db->query($sql, $params)->result_array();
        }

        // Ringkasan hanya menghitung transaksi aktif yang termasuk hasil filter.
        $total_setoran = 0;
        $total_penarikan = 0;
        foreach ($rows as $row) {
            if ($row['status_transaksi'] !== 'Aktif') continue;
            if ($row['jenis'] === 'Setoran') {
                $total_setoran += (float) $row['nominal'];
            } else {
                $total_penarikan += (float) $row['nominal'];
            }
        }

        $nama_tahun = 'Semua Tahun Ajaran';
        if ($id_periode > 0) {
            $periode = $this->db->select('periode')->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
            $nama_tahun = $periode['periode'] ?? '-';
        }
        $nama_kelas = 'Semua Kelas';
        if ($id_kelas > 0) {
            $kelas = $this->db->select('nama_kelas')->where('id', $id_kelas)->get('kelas_setting')->row_array();
            $nama_kelas = $kelas['nama_kelas'] ?? '-';
        }

        echo json_encode(array(
            'data_laporan' => $rows,
            'total_setoran' => $total_setoran,
            'total_penarikan' => $total_penarikan,
            'saldo' => $total_setoran - $total_penarikan,
            'filters' => array(
                'Periode' => $nama_periode,
                'Tahun Ajaran' => $nama_tahun,
                'Kelas' => $nama_kelas,
                'Jenis Transaksi' => $jenis ?: 'Semua Jenis',
                'Status' => $status ?: 'Semua Status'
            )
        ));
    }

    public function laporan_pembatalan_spp()
    {
        $id_periode = max(0, (int) $this->input->post('spp_id_periode_pembatalan'));
        $id_siswa = max(0, (int) $this->input->post('spp_id_siswa_pembatalan'));
        $tahun_ajaran = $this->db->select('periode')->where('id', $id_periode)
            ->get('master_tahun_ajaran')->row_array();
        if (!$tahun_ajaran) {
            echo json_encode(array('result' => 'false', 'message' => 'Silakan pilih tahun ajaran yang valid.'));
            return;
        }

        $nama_siswa = 'Semua Siswa';
        if ($id_siswa > 0) {
            $siswa = $this->db->select('nama_siswa')->where('id_siswa', $id_siswa)
                ->get('tagihan_pembatalan_transaksi')->row_array();
            $nama_siswa = $siswa['nama_siswa'] ?? '-';
        }

        $sql = "SELECT c.no_transaksi, c.nama_siswa, c.nis, c.total_pembayaran,
		               c.nama_user_transaksi, c.nama_user_pembatalan,
		               c.tanggal_pembatalan, c.waktu_pembatalan, c.alasan_pembatalan
		        FROM tagihan_pembatalan_transaksi c
		        JOIN tagihan_pembayaran p ON p.id = c.id_pembayaran
		        WHERE p.id_periode = ?";
        $params = array($id_periode);
        if ($id_siswa > 0) {
            $sql .= ' AND c.id_siswa = ?';
            $params[] = $id_siswa;
        }
        $sql .= " ORDER BY STR_TO_DATE(c.tanggal_pembatalan, '%d-%m-%Y') DESC,
		          c.waktu_pembatalan DESC, c.id DESC";
        $rows = $this->db->query($sql, $params)->result_array();
        $total = 0;
        foreach ($rows as $row) {
            $total += (float) $row['total_pembayaran'];
        }
        echo json_encode(array(
            'data_laporan' => $rows,
            'jumlah_pembatalan' => count($rows),
            'total_dibatalkan' => $total,
            'filters' => array('Tahun Ajaran' => $tahun_ajaran['periode'], 'Siswa' => $nama_siswa)
        ));
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
