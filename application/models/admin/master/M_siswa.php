<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_siswa extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dompet/M_transaksi_dompet', 'wallet_transaction');
    }

    public function periode_options()
    {
        return $this->db->select('id, periode, status')->order_by('id', 'DESC')->get('master_tahun_ajaran')->result_array();
    }

    public function kelas_options()
    {
        $this->db
            ->select("ks.id, COALESCE(NULLIF(CONCAT(TRIM(k.nama_kelas), TRIM(COALESCE(k.kode_kelas, ''))), ''), ks.nama_kelas) AS nama_kelas, ks.id_periode, ks.periode, ks.semester, ta.status AS status_periode", false)
            ->from('kelas_setting ks')
            ->join('kelas k', 'k.id = ks.id_kelas', 'left')
            ->join('master_tahun_ajaran ta', 'ta.id = ks.id_periode', 'left')
            ->order_by("CASE WHEN ta.status = 'Aktif' THEN 0 ELSE 1 END", '', false);
        if ($this->db->field_exists('deleted_at', 'kelas')) {
            $this->db->where('k.deleted_at IS NULL', null, false);
        }
        return $this->db
            ->order_by('ks.periode', 'DESC')
            ->order_by('ks.nama_kelas', 'ASC')
            ->get()->result_array();
    }

    public function master_class_options()
    {
        $this->db->select('id,nama_kelas,kode_kelas')->from('kelas');
        if ($this->db->field_exists('deleted_at', 'kelas')) {
            $this->db->where('deleted_at IS NULL', null, false);
        }
        return $this->db->order_by('nama_kelas', 'ASC')->order_by('kode_kelas', 'ASC')->get()->result_array();
    }

    public function active_class_setting($idKelas)
    {
        $idKelas = (int) $idKelas;
        if ($idKelas < 1) return null;
        $period = $this->db->where('status', 'Aktif')->order_by('id', 'DESC')->get('master_tahun_ajaran')->row_array();
        if (!$period) return null;

        $today = date('Y-m-d');
        $preferredSemester = 'Ganjil';
        if (!empty($period['tanggal_awal_semester_genap']) && !empty($period['tanggal_akhir_semester_genap'])
            && $today >= $period['tanggal_awal_semester_genap'] && $today <= $period['tanggal_akhir_semester_genap']) {
            $preferredSemester = 'Genap';
        }

        $this->db
            ->select('ks.id,ks.id_kelas,ks.nama_kelas,ks.id_periode,ks.periode,ks.semester')
            ->from('kelas_setting ks')
            ->join('kelas k', 'k.id=ks.id_kelas', 'inner')
            ->where('ks.id_kelas', $idKelas)
            ->where('ks.id_periode', (int) $period['id']);
        if ($this->db->field_exists('deleted_at', 'kelas')) {
            $this->db->where('k.deleted_at IS NULL', null, false);
        }
        return $this->db
            ->order_by("CASE WHEN ks.semester=".$this->db->escape($preferredSemester)." THEN 0 ELSE 1 END", '', false)
            ->order_by('ks.id', 'DESC')->limit(1)->get()->row_array();
    }

    public function sumber_options()
    {
        $defaults = ['WEB', 'MIGRASI', 'API', 'DOMPETDIGITAL'];
        $rows = $this->db->select('sumber_data')->where('sumber_data IS NOT NULL', null, false)->where('sumber_data !=', '')->group_by('sumber_data')->order_by('sumber_data')->get('siswa')->result_array();
        foreach ($rows as $row) {
            $value = trim((string) ($row['sumber_data'] ?? ''));
            if ($value !== '' && !in_array($value, $defaults, true)) $defaults[] = $value;
        }
        return $defaults;
    }

    public function guardian_options()
    {
        return $this->db
            ->select('id, nama_wali, hubungan, no_telp')
            ->where('status_aktif', 'AKTIF')
            ->order_by('nama_wali', 'ASC')
            ->get('wali_siswa')->result_array();
    }

    public function result($filter)
    {
        $limit = $filter['limit'];
        $page = $filter['page'];
        $this->build_result_query($filter, true);
        $total = (int) $this->db->count_all_results();

        $this->build_result_query($filter, false);
        $rows = $this->db->limit($limit, ($page - 1) * $limit)->get()->result_array();

        return [
            'rows' => $rows,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => max(1, (int) ceil($total / $limit)),
            ],
        ];
    }

    private function build_result_query($filter, $count)
    {
        $id_periode = (int) $filter['id_periode'];
        $period_clause = $id_periode > 0 ? ' AND kst2.id_periode = ' . $id_periode : '';
        if ($count) {
            $this->db->select('s.id');
        } else {
            $this->db->select("s.id, s.nis, s.nisn, s.nama_lengkap, s.jk, s.status_siswa, s.status_aktif,
                s.sumber_data, kst.id AS id_kelas_setting, kst.nama_kelas, kst.id_periode,
                kst.periode, kst.semester, CASE WHEN sd.id IS NULL THEN 'BELUM' ELSE 'AKTIF' END AS indikator_dompet", false);
        }
        $this->db->from('siswa s');
        $this->db->join(
            'kelas_siswa ks',
            "ks.id = (SELECT MAX(ks2.id) FROM kelas_siswa ks2 JOIN kelas_setting kst2 ON kst2.id = ks2.id_kelas_setting WHERE ks2.id_siswa = s.id AND ks2.status_aktif IN ('1','AKTIF'){$period_clause})",
            'left',
            false
        );
        $this->db->join('kelas_setting kst', 'kst.id = ks.id_kelas_setting', 'left');
        $this->db->join('saldo_dompet_siswa sd', 'sd.id_siswa = s.id', 'left');
        $this->db->where('s.status_siswa !=', 'TERHAPUS');

        if ($filter['search'] !== '') {
            $this->db->group_start()
                ->like('s.nis', $filter['search'])
                ->or_like('s.nisn', $filter['search'])
                ->or_like('s.nama_lengkap', $filter['search'])
                ->group_end();
        }
        if (in_array($filter['status'], ['AKTIF', 'NONAKTIF', 'ALUMNI'], true)) {
            $this->db->where('s.status_siswa', $filter['status']);
        }
        if ($filter['id_kelas_setting'] > 0) {
            $this->db->where('kst.id', $filter['id_kelas_setting']);
        }
        if ($id_periode > 0) {
            $this->db->where('kst.id_periode', $id_periode);
        }
        if ($filter['sumber_data'] !== '') {
            if ($filter['sumber_data'] === 'KOSONG') {
                $this->db->group_start()->where('s.sumber_data IS NULL', null, false)->or_where('s.sumber_data', '')->group_end();
            } else {
                $this->db->where('s.sumber_data', $filter['sumber_data']);
            }
        }
        if (!$count) {
            $this->db->order_by('s.nama_lengkap', 'ASC')->order_by('s.id', 'ASC');
        }
    }

    public function detail($id)
    {
        if ($id < 1) {
            return null;
        }
        $student = $this->db->select('s.*')->get_where('siswa s', ['s.id' => $id])->row_array();
        if (!$student) {
            return null;
        }

        $student['wali_terhubung'] = $this->linked_guardians($id);

        $student['kelas'] = $this->db
            ->select('ks.id, ks.id_kelas_setting, ks.status_aktif, kst.nama_kelas, kst.id_periode, kst.periode, kst.semester')
            ->from('kelas_siswa ks')
            ->join('kelas_setting kst', 'kst.id = ks.id_kelas_setting', 'left')
            ->where('ks.id_siswa', $id)
            ->order_by('kst.id_periode', 'DESC')->order_by('ks.id', 'DESC')
            ->get()->result_array();
        $student['kelas_aktif'] = $this->db
            ->select('ks.id_kelas_setting, kst.id_kelas, kst.nama_kelas, kst.periode, kst.semester')
            ->from('kelas_siswa ks')
            ->join('kelas_setting kst', 'kst.id = ks.id_kelas_setting', 'inner')
            ->join('master_tahun_ajaran ta', "ta.id = kst.id_periode AND ta.status = 'Aktif'", 'inner')
            ->where('ks.id_siswa', $id)
            ->where_in('ks.status_aktif', ['1', 'AKTIF'])
            ->order_by('ks.id', 'DESC')->limit(1)->get()->row_array();
        $student['dompet'] = $this->db
            ->select('status_dompet, saldo')
            ->get_where('saldo_dompet_siswa', ['id_siswa' => $id])
            ->row_array();
        return $student;
    }

    private function linked_guardians($id_siswa)
    {
        return $this->db
            ->select('w.id, w.nama_wali, w.hubungan, w.no_telp, w.alamat, d.status_utama')
            ->from('wali_siswa_detail d')
            ->join('wali_siswa w', 'w.id = d.id_wali_siswa', 'inner')
            ->where('d.id_siswa', (int) $id_siswa)
            ->where('d.status_aktif', 'AKTIF')
            ->where('w.status_aktif', 'AKTIF')
            ->order_by("CASE WHEN d.status_utama = 'YA' THEN 0 ELSE 1 END", '', false)
            ->order_by('d.id', 'ASC')
            ->get()->result_array();
    }

    public function create($payload, $user, $guardianIds = [], $idKelasSetting = 0)
    {
        if ($this->nis_exists($payload['nis'])) {
            return $this->error('NIS sudah digunakan.', 409, ['nis' => 'NIS harus unik.']);
        }
        $now = $this->audit_fields($user);
        $data = array_merge($payload, $now, ['status_siswa' => 'AKTIF', 'status_aktif' => 'AKTIF']);
        $this->db->trans_begin();
        $this->db->insert('siswa', $data);
        $id = (int) $this->db->insert_id();
        $sync = $this->sync_guardians($id, $guardianIds, $user);
        $classSync = !empty($sync['success']) ? $this->assign_class($id, (int) $idKelasSetting) : $sync;
        $wallet = !empty($classSync['success']) ? $this->wallet_transaction->aktifkan_dompet($id, $user) : $classSync;
        if (empty($sync['success']) || empty($classSync['success']) || empty($wallet['success']) || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            if (empty($sync['success'])) return $sync;
            if (empty($classSync['success'])) return $classSync;
            if (empty($wallet['success'])) return $wallet;
            return $this->error('Data siswa, kelas, dan dompet gagal disimpan.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true, 'data' => array_merge(['id' => $id, 'id_kelas_setting' => (int) $idKelasSetting], $wallet['data'] ?? [])];
    }

    public function update_student($id, $payload, $user, $guardianIds = [], $idKelasSetting = 0)
    {
        if (!$this->db->where('id', $id)->count_all_results('siswa')) {
            return $this->error('Data siswa tidak ditemukan.', 404);
        }
        if ($this->nis_exists($payload['nis'], $id)) {
            return $this->error('NIS sudah digunakan.', 409, ['nis' => 'NIS harus unik.']);
        }
        $data = array_merge($payload, $this->audit_fields($user));
        $this->db->trans_begin();
        $this->db->where('id', $id)->update('siswa', $data);
        $sync = $this->sync_guardians($id, $guardianIds, $user);
        $classSync = !empty($sync['success']) ? $this->sync_selected_class($id, (int) $idKelasSetting) : $sync;
        if (empty($sync['success']) || empty($classSync['success']) || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            if (empty($sync['success'])) return $sync;
            if (empty($classSync['success'])) return $classSync;
            return $this->error('Data siswa dan kelas gagal diperbarui.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true, 'data' => ['id' => $id]];
    }

    private function sync_guardians($idSiswa, $guardianIds, $user)
    {
        $guardianIds = array_slice(array_values(array_unique(array_filter(array_map('intval', is_array($guardianIds) ? $guardianIds : [])))), 0, 1);
        if ($guardianIds) {
            $guardians = $this->db->select('id,nama_wali')->where_in('id', $guardianIds)->where('status_aktif', 'AKTIF')->get('wali_siswa')->result_array();
            if (count($guardians) !== count($guardianIds)) {
                return $this->error('Salah satu wali yang dipilih tidak aktif atau tidak ditemukan.', 422, ['id_wali_siswa' => 'Pilih wali aktif yang tersedia.']);
            }
            $byId = [];
            foreach ($guardians as $guardian) $byId[(int) $guardian['id']] = $guardian;
        } else {
            $byId = [];
        }

        $audit = $this->audit_fields($user);
        $this->db->where('id_siswa', (int) $idSiswa)->update('wali_siswa_detail', array_merge($audit, ['status_aktif' => 'NONAKTIF', 'status_utama' => 'TIDAK']));
        $student = $this->db->select('nis,nama_lengkap')->get_where('siswa', ['id' => (int) $idSiswa])->row_array();
        foreach ($guardianIds as $index => $guardianId) {
            $guardian = $byId[$guardianId];
            $relation = $this->db->get_where('wali_siswa_detail', ['id_wali_siswa' => $guardianId, 'id_siswa' => (int) $idSiswa])->row_array();
            $data = array_merge($audit, [
                'nama_wali' => $guardian['nama_wali'], 'nis' => $student['nis'], 'nama_siswa' => $student['nama_lengkap'],
                'status_utama' => $index === 0 ? 'YA' : 'TIDAK', 'status_aktif' => 'AKTIF',
            ]);
            if ($relation) {
                $this->db->where('id', $relation['id'])->update('wali_siswa_detail', $data);
            } else {
                $this->db->insert('wali_siswa_detail', array_merge($data, ['id_wali_siswa' => $guardianId, 'id_siswa' => (int) $idSiswa]));
            }
        }
        return ['success' => true];
    }

    public function change_status($id, $status, $reason, $user)
    {
        if (!$this->db->where('id', $id)->count_all_results('siswa')) {
            return $this->error('Data siswa tidak ditemukan.', 404);
        }
        $data = array_merge($this->audit_fields($user), [
            'status_siswa' => $status,
            'status_aktif' => $status === 'AKTIF' ? 'AKTIF' : 'NONAKTIF',
            'catatan' => $reason,
        ]);
        if (!$this->db->where('id', $id)->update('siswa', $data)) {
            return $this->error('Status siswa gagal diperbarui.', 500);
        }
        return ['success' => true, 'data' => ['id' => $id, 'status' => $status]];
    }

    public function trash_rows()
    {
        return $this->db
            ->select('id, nis, nisn, nama_lengkap, jk, tanggal, waktu, nama_user')
            ->where('status_siswa', 'TERHAPUS')
            ->order_by('tanggal', 'DESC')->order_by('waktu', 'DESC')->order_by('nama_lengkap', 'ASC')
            ->get('siswa')->result_array();
    }

    public function soft_delete($id, $user)
    {
        $student = $this->db->select('id, status_siswa')->get_where('siswa', ['id' => $id])->row_array();
        if (!$student || $student['status_siswa'] === 'TERHAPUS') {
            return $this->error('Data siswa tidak ditemukan atau sudah berada di Trash.', 404);
        }
        $data = array_merge($this->audit_fields($user), [
            'status_siswa' => 'TERHAPUS',
            'status_aktif' => 'TERHAPUS',
            'catatan' => 'Dipindahkan ke Trash. Status sebelumnya: '.$student['status_siswa'],
        ]);
        if (!$this->db->where('id', $id)->update('siswa', $data)) {
            return $this->error('Data siswa gagal dipindahkan ke Trash.', 500);
        }
        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function restore($id, $user)
    {
        if (!$this->db->where('id', $id)->where('status_siswa', 'TERHAPUS')->count_all_results('siswa')) {
            return $this->error('Data siswa tidak ditemukan di Trash.', 404);
        }
        $data = array_merge($this->audit_fields($user), [
            'status_siswa' => 'AKTIF',
            'status_aktif' => 'AKTIF',
            'catatan' => 'Dipulihkan dari Trash dan diaktifkan kembali.',
        ]);
        if (!$this->db->where('id', $id)->update('siswa', $data)) {
            return $this->error('Data siswa gagal dipulihkan.', 500);
        }
        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function export_rows()
    {
        return $this->db
            ->select("s.nis, s.nama_lengkap, s.jk, s.tempat_lahir, s.tanggal_lahir, COALESCE(kst.nama_kelas, '') nama_kelas, COALESCE(u.username, '') username, COALESCE(w.nama_wali, '') nama_wali", false)
            ->from('siswa s')
            ->join('kelas_siswa ks', "ks.id = (SELECT MAX(ks2.id) FROM kelas_siswa ks2 JOIN kelas_setting kst2 ON kst2.id=ks2.id_kelas_setting JOIN master_tahun_ajaran ta2 ON ta2.id=kst2.id_periode AND ta2.status='Aktif' WHERE ks2.id_siswa=s.id AND ks2.status_aktif IN ('1','AKTIF'))", 'left', false)
            ->join('kelas_setting kst', 'kst.id=ks.id_kelas_setting', 'left')
            ->join('wali_siswa_detail wd', "wd.id = (SELECT MIN(wd2.id) FROM wali_siswa_detail wd2 WHERE wd2.id_siswa=s.id AND wd2.status_aktif='AKTIF' ORDER BY CASE WHEN wd2.status_utama='YA' THEN 0 ELSE 1 END, wd2.id)", 'left', false)
            ->join('wali_siswa w', "w.id=wd.id_wali_siswa AND w.status_aktif!='TERHAPUS'", 'left')
            ->join('user u', "u.id=(SELECT MIN(u2.id) FROM user u2 WHERE u2.id_wali_siswa=w.id AND u2.id_level='8')", 'left', false)
            ->where('s.status_siswa !=', 'TERHAPUS')
            ->order_by('kst.nama_kelas', 'ASC')->order_by('s.nama_lengkap', 'ASC')
            ->get()->result_array();
    }

    public function alumni_years()
    {
        return $this->db->select('tahun_lulus')->where('status_siswa', 'ALUMNI')->where('tahun_lulus IS NOT NULL', null, false)->where('tahun_lulus !=', '')->group_by('tahun_lulus')->order_by('tahun_lulus', 'DESC')->get('siswa')->result_array();
    }

    public function alumni_result($filter, $paginate = true)
    {
        $this->build_alumni_query($filter, true);
        $total = (int) $this->db->count_all_results();
        $this->build_alumni_query($filter, false);
        if ($paginate) $this->db->limit($filter['limit'], ($filter['page'] - 1) * $filter['limit']);
        $rows = $this->db->get()->result_array();
        return ['rows' => $rows, 'meta' => ['page' => $filter['page'], 'limit' => $filter['limit'], 'total' => $total, 'pages' => max(1, (int) ceil($total / $filter['limit']))]];
    }

    private function build_alumni_query($filter, $count)
    {
        $this->db->select($count ? 's.id' : "s.id,s.nis,s.nisn,s.nama_lengkap,s.jk,s.tempat_lahir,s.tanggal_lahir,s.tahun_lulus,kst.nama_kelas,kst.periode,w.nama_wali,w.no_telp", false)
            ->from('siswa s')
            ->join('kelas_siswa ks', 'ks.id=(SELECT MAX(ks2.id) FROM kelas_siswa ks2 WHERE ks2.id_siswa=s.id)', 'left', false)
            ->join('kelas_setting kst', 'kst.id=ks.id_kelas_setting', 'left')
            ->join('wali_siswa_detail wd', "wd.id=(SELECT wd2.id FROM wali_siswa_detail wd2 WHERE wd2.id_siswa=s.id AND wd2.status_aktif='AKTIF' ORDER BY CASE WHEN wd2.status_utama='YA' THEN 0 ELSE 1 END,wd2.id LIMIT 1)", 'left', false)
            ->join('wali_siswa w', "w.id=wd.id_wali_siswa AND w.status_aktif!='TERHAPUS'", 'left')
            ->where('s.status_siswa', 'ALUMNI');
        if ($filter['search'] !== '') {
            $this->db->group_start()->like('s.nis', $filter['search'])->or_like('s.nisn', $filter['search'])->or_like('s.nama_lengkap', $filter['search'])->group_end();
        }
        if ($filter['tahun_lulus'] !== '') $this->db->where('s.tahun_lulus', $filter['tahun_lulus']);
        if (!$count) $this->db->order_by('s.tahun_lulus', 'DESC')->order_by('s.nama_lengkap', 'ASC');
    }

    public function import_students($rows, $user)
    {
        $summary = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'generated_accounts' => []];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $nis = trim((string) ($row[0] ?? ''));
            $name = trim((string) ($row[1] ?? ''));
            $gender = strtoupper(trim((string) ($row[2] ?? '')));
            $birthPlace = trim((string) ($row[3] ?? ''));
            $birthDate = $this->parse_import_date($row[4] ?? '');
            $className = trim((string) ($row[5] ?? ''));
            $username = trim((string) ($row[6] ?? ''));
            $password = (string) ($row[7] ?? '');
            $guardianName = trim((string) ($row[8] ?? ''));

            $problems = [];
            if (!preg_match('/^[A-Za-z0-9.\/-]{2,50}$/', $nis)) $problems[] = 'NISN/NIS tidak valid';
            if (mb_strlen($name) < 2) $problems[] = 'nama siswa wajib diisi';
            if (!in_array($gender, ['L', 'P'], true)) $problems[] = 'jenis kelamin harus L atau P';
            if (trim((string) ($row[4] ?? '')) !== '' && $birthDate === '') $problems[] = 'tanggal lahir tidak dikenali';
            $class = $this->active_class_by_name($className);
            if (!$class) $problems[] = 'kelas aktif "'.$className.'" tidak ditemukan';
            if ($username !== '' && !preg_match('/^[A-Za-z0-9._-]{4,100}$/', $username)) $problems[] = 'username wali tidak valid';
            if ($username !== '' && strlen($password) < 6) $problems[] = 'kata sandi wali minimal 6 karakter bila username diisi';
            if ($guardianName === '') $problems[] = 'nama wali wajib diisi';
            if ($this->nis_exists($nis)) {
                $summary['skipped']++;
                $summary['errors'][] = 'Baris '.$line.': NISN/NIS '.$nis.' sudah tersedia.';
                continue;
            }
            if ($problems) {
                $summary['skipped']++;
                $summary['errors'][] = 'Baris '.$line.': '.implode('; ', $problems).'.';
                continue;
            }

            $generatedAccountCount = count($summary['generated_accounts']);
            $this->db->trans_begin();
            $studentData = array_merge($this->audit_fields($user), [
                'nis' => $nis,
                'nisn' => '',
                'nama_lengkap' => $name,
                'jk' => $gender === 'L' ? 'Laki-laki' : 'Perempuan',
                'tempat_lahir' => $birthPlace,
                'tanggal_lahir' => $birthDate,
                'alamat_siswa' => '',
                'sumber_data' => 'WEB',
                'status_siswa' => 'AKTIF',
                'status_aktif' => 'AKTIF',
            ]);
            $this->db->insert('siswa', $studentData);
            $studentId = (int) $this->db->insert_id();
            $this->db->insert('kelas_siswa', [
                'id_kelas_setting' => (string) $class['id'], 'nis' => $nis, 'id_siswa' => (string) $studentId,
                'nama_siswa' => $name, 'jenis_kelamin' => $studentData['jk'], 'status_aktif' => '1',
            ]);

            $guardian = null;
            if ($username !== '') {
                $guardian = $this->db->select('w.*')->from('user u')->join('wali_siswa w', 'w.id=u.id_wali_siswa')->where('u.username', $username)->get()->row_array();
            }
            if (!$guardian) {
                $guardian = $this->db->where('nama_wali', $guardianName)->where('status_aktif !=', 'TERHAPUS')->order_by('id')->get('wali_siswa')->row_array();
            }
            if (!$guardian) {
                $this->db->insert('wali_siswa', array_merge($this->audit_fields($user), ['nama_wali' => $guardianName, 'hubungan' => '', 'status_aktif' => 'AKTIF']));
                $guardian = ['id' => (int) $this->db->insert_id(), 'nama_wali' => $guardianName];
            }
            $existingAccount = $this->db->select('id,username')->where('id_wali_siswa', (int) $guardian['id'])->where('id_level', '8')->order_by('id')->get('user')->row_array();
            $generatedPassword = '';
            if (!$existingAccount && $username === '') {
                $username = $this->generate_guardian_username($name);
                $generatedPassword = 'Portal!'.random_int(100000, 999999);
                $password = $generatedPassword;
            }
            if (!$existingAccount && !$this->db->where('username', $username)->count_all_results('user')) {
                $this->db->insert('user', [
                    'nama_user' => $guardianName, 'username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT),
                    'password_text' => $password, 'id_level' => '8', 'level' => 'Wali Murid', 'id_wali_siswa' => (int) $guardian['id'], 'status_aktif' => 'AKTIF',
                ]);
                if ($generatedPassword !== '') {
                    $summary['generated_accounts'][] = ['nis' => $nis, 'nama_wali' => $guardianName, 'username' => $username, 'password' => $generatedPassword];
                }
            }
            $this->db->insert('wali_siswa_detail', array_merge($this->audit_fields($user), [
                'id_wali_siswa' => (int) $guardian['id'], 'nama_wali' => $guardian['nama_wali'], 'id_siswa' => $studentId,
                'nis' => $nis, 'nama_siswa' => $name, 'status_utama' => 'YA', 'status_aktif' => 'AKTIF',
            ]));

            $wallet = $this->wallet_transaction->aktifkan_dompet($studentId, $user);
            if (empty($wallet['success'])) {
                $this->db->trans_rollback();
                $summary['generated_accounts'] = array_slice($summary['generated_accounts'], 0, $generatedAccountCount);
                $summary['skipped']++;
                $summary['errors'][] = 'Baris '.$line.': '.$wallet['message'];
                continue;
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $summary['skipped']++;
                $summary['errors'][] = 'Baris '.$line.': gagal disimpan ke database.';
            } else {
                $this->db->trans_commit();
                $summary['imported']++;
            }
        }
        return ['success' => true, 'data' => $summary];
    }

    private function active_class_by_name($name)
    {
        if ($name === '') return null;
        return $this->db->select('ks.id')->from('kelas_setting ks')->join('master_tahun_ajaran ta', "ta.id=ks.id_periode AND ta.status='Aktif'", 'inner')->where('ks.nama_kelas', $name)->order_by('ks.id', 'DESC')->get()->row_array();
    }

    private function generate_guardian_username($studentName)
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $studentName)));
        $slug = substr($slug !== '' ? $slug : 'wali', 0, 18);
        do {
            $username = 'p.'.$slug.random_int(100000, 999999);
        } while ($this->db->where('username', $username)->count_all_results('user'));
        return $username;
    }

    private function parse_import_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') return '';
        if (is_numeric($value) && (float) $value > 1000) return gmdate('Y-m-d', ((int) $value - 25569) * 86400);
        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $format) {
            $date = DateTime::createFromFormat('!'.$format, $value);
            if ($date && $date->format($format) === $value) return $date->format('Y-m-d');
        }
        $months = ['januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12];
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/u', $value, $match)) {
            $month = $months[mb_strtolower($match[2])] ?? 0;
            if ($month && checkdate($month, (int) $match[1], (int) $match[3])) return sprintf('%04d-%02d-%02d', $match[3], $month, $match[1]);
        }
        return '';
    }

    public function assign_class($id_siswa, $id_kelas_setting)
    {
        $student = $this->db->select('id, nis, nama_lengkap, jk, status_siswa')->get_where('siswa', ['id' => $id_siswa])->row_array();
        $class = $this->db
            ->select('ks.id, ks.id_periode, ks.nama_kelas, ta.status AS status_periode')
            ->from('kelas_setting ks')
            ->join('master_tahun_ajaran ta', 'ta.id = ks.id_periode', 'inner')
            ->where('ks.id', $id_kelas_setting)->get()->row_array();
        if (!$student || !$class) {
            return $this->error('Siswa atau kelas tidak ditemukan.', 404);
        }
        if (strcasecmp((string) $class['status_periode'], 'Aktif') !== 0) {
            return $this->error('Penempatan hanya boleh ke periode aktif.', 422);
        }
        if ($student['status_siswa'] !== 'AKTIF') {
            return $this->error('Siswa nonaktif tidak dapat ditempatkan ke kelas.', 422);
        }

        $this->db->trans_begin();
        $this->db->query('SELECT id FROM siswa WHERE id = ? FOR UPDATE', [$id_siswa]);
        $this->db->query(
            "UPDATE kelas_siswa ksw JOIN kelas_setting kst ON kst.id = ksw.id_kelas_setting SET ksw.status_aktif = '0' WHERE ksw.id_siswa = ? AND kst.id_periode = ?",
            [$id_siswa, $class['id_periode']]
        );
        $existing = $this->db->get_where('kelas_siswa', [
            'id_siswa' => $id_siswa,
            'id_kelas_setting' => $id_kelas_setting,
        ])->row_array();
        $row = [
            'id_kelas_setting' => (string) $id_kelas_setting,
            'nis' => $student['nis'],
            'id_siswa' => (string) $id_siswa,
            'nama_siswa' => $student['nama_lengkap'],
            'jenis_kelamin' => $student['jk'],
            'status_aktif' => '1',
        ];
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('kelas_siswa', $row);
        } else {
            $this->db->insert('kelas_siswa', $row);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->error('Penempatan kelas gagal disimpan.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true, 'data' => ['id_siswa' => $id_siswa, 'id_kelas_setting' => $id_kelas_setting]];
    }

    private function sync_selected_class($idSiswa, $idKelasSetting)
    {
        if ($idKelasSetting < 1) {
            return $this->error('Kelas siswa wajib dipilih.', 422, ['id_kelas_setting' => 'Pilih kelas pada periode aktif.']);
        }
        $current = $this->db
            ->select('ks.id_kelas_setting')
            ->from('kelas_siswa ks')
            ->join('kelas_setting kst', 'kst.id=ks.id_kelas_setting', 'inner')
            ->join('master_tahun_ajaran ta', "ta.id=kst.id_periode AND ta.status='Aktif'", 'inner')
            ->where('ks.id_siswa', (int) $idSiswa)
            ->where_in('ks.status_aktif', ['1', 'AKTIF'])
            ->order_by('ks.id', 'DESC')->limit(1)->get()->row_array();
        if ($current && (int) $current['id_kelas_setting'] === $idKelasSetting) {
            return ['success' => true, 'data' => ['id_siswa' => (int) $idSiswa, 'id_kelas_setting' => $idKelasSetting]];
        }
        return $this->assign_class((int) $idSiswa, $idKelasSetting);
    }

    public function graduate_student($idSiswa, $graduationYear, $user)
    {
        $student = $this->db->select('id,nis,nama_lengkap,status_siswa')->get_where('siswa', ['id' => (int) $idSiswa])->row_array();
        if (!$student || $student['status_siswa'] === 'TERHAPUS') {
            return $this->error('Data siswa tidak ditemukan.', 404);
        }
        $currentClass = $this->db
            ->select('kst.nama_kelas,kst.periode')
            ->from('kelas_siswa ks')
            ->join('kelas_setting kst', 'kst.id=ks.id_kelas_setting', 'inner')
            ->join('master_tahun_ajaran ta', "ta.id=kst.id_periode AND ta.status='Aktif'", 'inner')
            ->where('ks.id_siswa', (int) $idSiswa)->where_in('ks.status_aktif', ['1', 'AKTIF'])
            ->order_by('ks.id', 'DESC')->limit(1)->get()->row_array();
        if (!$currentClass || !preg_match('/^6(?:\D|$)/i', trim((string) $currentClass['nama_kelas']))) {
            return $this->error('Kelulusan hanya dapat diproses untuk siswa yang sedang berada di kelas 6.', 422);
        }
        $currentYear = (int) date('Y');
        if ($graduationYear < 2000 || $graduationYear > $currentYear + 1) {
            return $this->error('Tahun kelulusan tidak valid.', 422, ['tahun_lulus' => 'Pilih tahun kelulusan yang tersedia.']);
        }

        $this->db->trans_begin();
        $this->db->query('SELECT id FROM siswa WHERE id = ? FOR UPDATE', [(int) $idSiswa]);
        $this->db->where('id_siswa', (int) $idSiswa)->where_in('status_aktif', ['1', 'AKTIF'])->update('kelas_siswa', ['status_aktif' => '0']);
        $this->db->where('id', (int) $idSiswa)->update('siswa', array_merge($this->audit_fields($user), [
            'status_siswa' => 'ALUMNI', 'status_aktif' => 'NONAKTIF', 'tahun_lulus' => (string) $graduationYear,
            'catatan' => 'Lulus dari kelas '.$currentClass['nama_kelas'].' tahun '.$graduationYear.'.',
        ]));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->error('Kelulusan siswa gagal disimpan.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true, 'data' => ['id_siswa' => (int) $idSiswa, 'tahun_lulus' => $graduationYear]];
    }

    private function nis_exists($nis, $except_id = null)
    {
        $this->db->where('nis', $nis);
        if ($except_id !== null) {
            $this->db->where('id !=', $except_id);
        }
        return $this->db->count_all_results('siswa') > 0;
    }

    private function audit_fields($user)
    {
        return [
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
            'id_user' => (int) ($user['id'] ?? 0),
            'nama_user' => (string) ($user['nama_user'] ?? ''),
        ];
    }

    private function error($message, $status, $errors = [])
    {
        return ['success' => false, 'message' => $message, 'status' => $status, 'errors' => $errors];
    }
}
