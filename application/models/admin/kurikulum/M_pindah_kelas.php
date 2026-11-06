<?php defined('BASEPATH') or exit('No direct script access allowed');

class M_pindah_kelas extends CI_Model
{
    public function class_settings()
    {
        return $this->db
            ->select('ks.id,ks.nama_kelas,ks.periode,ks.semester,ks.id_periode,ks.wali_kelas,ta.status AS status_periode')
            ->from('kelas_setting ks')
            ->join('master_tahun_ajaran ta', 'ta.id=ks.id_periode', 'left')
            ->order_by("CASE WHEN ta.status='Aktif' THEN 0 ELSE 1 END", '', false)
            ->order_by('ks.id_periode', 'DESC')->order_by('ks.nama_kelas', 'ASC')->order_by('ks.semester', 'ASC')
            ->get()->result_array();
    }

    public function preview($sourceId, $target, $graduationYear)
    {
        $source = $this->class_setting($sourceId);
        if (!$source) return $this->error('Kelas sumber tidak ditemukan di Kelas Setting.', 404);
        $destinationRows = [];
        $destination = null;
        if ($target !== '') {
            if ($target === 'LULUS_'.$graduationYear) {
                if (!$this->is_grade_six($source['nama_kelas'])) return $this->error('Tujuan kelulusan hanya tersedia untuk kelas 6.', 422);
                $destination = ['id' => $target, 'nama_kelas' => 'Alumni', 'periode' => (string) $graduationYear, 'semester' => 'Lulus'];
                $destinationRows = $this->alumni_by_year($graduationYear);
            } else {
                $destination = $this->class_setting((int) $target);
                if (!$destination) return $this->error('Kelas tujuan tidak ditemukan di Kelas Setting.', 404);
                $destinationRows = $this->students_by_setting((int) $destination['id']);
            }
        }
        return ['success' => true, 'data' => [
            'source' => $source, 'destination' => $destination,
            'source_rows' => $this->students_by_setting($sourceId), 'destination_rows' => $destinationRows,
            'can_graduate' => $this->is_grade_six($source['nama_kelas']), 'graduation_year' => $graduationYear,
        ]];
    }

    public function move($sourceId, $targetId, $studentIds)
    {
        $source = $this->class_setting($sourceId);
        $target = $this->class_setting($targetId);
        if (!$source || !$target) return $this->error('Kelas sumber atau tujuan tidak ditemukan di Kelas Setting.', 404);
        if ((int) $sourceId === (int) $targetId) return $this->error('Kelas tujuan harus berbeda dari kelas sumber.', 422);

        $this->db->trans_begin();
        foreach ($studentIds as $studentId) {
            $student = $this->locked_student_in_source($studentId, $sourceId);
            if (!$student) {
                $this->db->trans_rollback();
                return $this->error('Ada siswa yang tidak lagi aktif pada kelas sumber. Muat ulang data lalu coba kembali.', 409);
            }
            $this->db->where('id_siswa', (int) $studentId)->where('id_kelas_setting', (int) $sourceId)->where_in('status_aktif', ['1', 'AKTIF'])->update('kelas_siswa', ['status_aktif' => '0']);
            $this->db->query("UPDATE kelas_siswa ksw JOIN kelas_setting kst ON kst.id=ksw.id_kelas_setting SET ksw.status_aktif='0' WHERE ksw.id_siswa=? AND kst.id_periode=? AND ksw.status_aktif IN ('1','AKTIF')", [(int) $studentId, $target['id_periode']]);
            $existing = $this->db->get_where('kelas_siswa', ['id_siswa' => (int) $studentId, 'id_kelas_setting' => (int) $targetId])->row_array();
            $row = ['id_kelas_setting' => (string) $targetId, 'nis' => $student['nis'], 'id_siswa' => (string) $studentId, 'nama_siswa' => $student['nama_lengkap'], 'jenis_kelamin' => $student['jk'], 'status_aktif' => '1'];
            if ($existing) $this->db->where('id', $existing['id'])->update('kelas_siswa', $row);
            else $this->db->insert('kelas_siswa', $row);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->error('Pemindahan kelas gagal disimpan.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true, 'data' => ['moved' => count($studentIds), 'target' => $target]];
    }

    public function graduate($sourceId, $studentIds, $year, $user)
    {
        $source = $this->class_setting($sourceId);
        if (!$source) return $this->error('Kelas sumber tidak ditemukan di Kelas Setting.', 404);
        if (!$this->is_grade_six($source['nama_kelas'])) return $this->error('Kelulusan hanya dapat dilakukan dari kelas 6.', 422);
        if ($year !== (int) date('Y')) return $this->error('Tahun kelulusan harus menggunakan tahun berjalan.', 422);

        $this->db->trans_begin();
        foreach ($studentIds as $studentId) {
            $student = $this->locked_student_in_source($studentId, $sourceId);
            if (!$student) {
                $this->db->trans_rollback();
                return $this->error('Ada siswa yang tidak lagi aktif pada kelas 6 sumber. Muat ulang data lalu coba kembali.', 409);
            }
            $this->db->where('id_siswa', (int) $studentId)->where_in('status_aktif', ['1', 'AKTIF'])->update('kelas_siswa', ['status_aktif' => '0']);
            $this->db->where('id', (int) $studentId)->update('siswa', [
                'status_siswa' => 'ALUMNI', 'status_aktif' => 'NONAKTIF', 'tahun_lulus' => (string) $year,
                'catatan' => 'Lulus dari kelas '.$source['nama_kelas'].' tahun '.$year.'.',
                'tanggal' => date('Y-m-d'), 'waktu' => date('H:i:s'), 'id_user' => (int) ($user['id'] ?? 0), 'nama_user' => (string) ($user['nama_user'] ?? ''),
            ]);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->error('Kelulusan siswa gagal disimpan.', 500);
        }
        $this->db->trans_commit();
        return ['success' => true, 'data' => ['graduated' => count($studentIds), 'year' => $year]];
    }

    private function class_setting($id)
    {
        return $this->db->select('ks.id,ks.id_kelas,ks.nama_kelas,ks.id_periode,ks.periode,ks.semester,ks.id_guru,ks.wali_kelas,ta.status AS status_periode')->from('kelas_setting ks')->join('master_tahun_ajaran ta', 'ta.id=ks.id_periode', 'left')->where('ks.id', (int) $id)->get()->row_array();
    }

    private function students_by_setting($id)
    {
        return $this->db->select('s.id,s.nis,s.nisn,s.nama_lengkap AS nama_siswa,s.jk')->from('kelas_siswa ks')->join('siswa s', 's.id=ks.id_siswa', 'inner')->where('ks.id_kelas_setting', (int) $id)->where_in('ks.status_aktif', ['1', 'AKTIF'])->where('s.status_siswa', 'AKTIF')->group_by('s.id')->order_by('s.nama_lengkap', 'ASC')->get()->result_array();
    }

    private function alumni_by_year($year)
    {
        return $this->db->select('id,nis,nisn,nama_lengkap AS nama_siswa,jk')->where('status_siswa', 'ALUMNI')->where('tahun_lulus', (string) $year)->order_by('nama_lengkap', 'ASC')->get('siswa')->result_array();
    }

    private function locked_student_in_source($studentId, $sourceId)
    {
        $rows = $this->db->query("SELECT s.id,s.nis,s.nama_lengkap,s.jk FROM siswa s JOIN kelas_siswa ks ON ks.id_siswa=s.id WHERE s.id=? AND s.status_siswa='AKTIF' AND ks.id_kelas_setting=? AND ks.status_aktif IN ('1','AKTIF') FOR UPDATE", [(int) $studentId, (int) $sourceId])->result_array();
        return $rows ? $rows[0] : null;
    }

    private function is_grade_six($name)
    {
        return (bool) preg_match('/^6(?:\D|$)/i', trim((string) $name));
    }

    private function error($message, $status)
    {
        return ['success' => false, 'message' => $message, 'status' => $status];
    }
}
