<?php
class M_riwayat_presensi_pegawai extends CI_Model
{
    public function pegawai_user($id_pegawai)
    {
        $this->db->from('pegawai a');
        $this->db->select('a.*, b.jabatan');
        $this->db->join('pegawai_jabatan b', 'a.id = b.id_pegawai');
        if (!empty($id_pegawai)) {
            $this->db->where('a.id', $id_pegawai);
            return $this->db->get()->row_array();
        } else {
            return $this->db->get()->result_array();
        }
    }

    public function presensi_pegawai_tambah($id_pegawai)
    {

        $pegawai = $this->db->get_where('pegawai', ['id' => $id_pegawai])->row_array();
        $jabatan = $this->db->get_where('pegawai_jabatan', ['id_pegawai' => $id_pegawai])->row_array();

        if ($jabatan['jabatan'] == 'Kepala Sekolah') {
            $status_jam = 'Kepala Sekolah';
        } elseif ($jabatan['jabatan'] == 'Wakil Kepala Sekolah') {
            $status_jam = 'Wakil Kepala Sekolah';
        } elseif ($jabatan['jabatan'] == 'Guru Ngaji') {
            $status_jam = 'Guru Ngaji';
        } else {
            $status_jam = 'Umum';
        }


        $setting_presensi = $this->db
            ->get_where('presensi_setting', ['status' => 'aktif', 'status_jam' => $status_jam])
            ->row_array();


        $tanggal = date('d-m-Y'); // PENTING
        $waktu = date('H:i:s');
        $jam_batas = $setting_presensi['jam_masuk'];

        // Cek presensi hari ini
        $presensi = $this->db
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal', $tanggal)
            ->get('presensi_pegawai')
            ->row_array();


        if (!$presensi) {

            $jam_masuk = strtotime($waktu);
            $jam_batas = strtotime($jam_batas);

            if ($jam_masuk > $jam_batas) {
                $status = 'Terlambat';
                $jam_terlambat = gmdate('H:i:s', $jam_masuk - $jam_batas);
            } else {
                $status = 'Tepat Waktu';
                $jam_terlambat = '00:00:00';
            }

            $insert = [
                'id_pegawai' => $pegawai['id'],
                'nama_pegawai' => $pegawai['nama_pegawai'],
                'id_jabatan' => $jabatan['id_jabatan'],
                'jabatan' => $jabatan['jabatan'],
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'status' => $status,
                'jam_terlambat' => $jam_terlambat
            ];

            $this->db->insert('presensi_pegawai', $insert);

            return ['status' => true, 'msg' => 'Presensi masuk berhasil'];
        }

        // PRESENSI PULANG
        if ($presensi && empty($presensi['jam_pulang'])) {
            $jam_saatini = date('H:i:s');
            $jam_pulang = $setting_presensi['jam_pulang'];

            $jam_saatini_ts = strtotime($jam_saatini);
            $jam_pulang_ts = strtotime($jam_pulang);

            if ($jam_saatini_ts >= $jam_pulang_ts) {
                $this->db
                    ->where('id', $presensi['id'])
                    ->update('presensi_pegawai', [
                        'jam_pulang' => $waktu
                    ]);
                $response = [
                    'status' => true,
                    'msg' => 'Presensi pulang berhasil'
                ];
            } else {
                $response = [
                    'status' => false,
                    'msg' => 'Belum waktunya presensi pulang'
                ];
            }
            return $response;
        }


        return ['status' => false, 'msg' => 'Presensi hari ini sudah lengkap'];
    }

    public function riwayatpresensi_pegawai()
    {
        $search = $this->input->post('search');
        $tanggal_dari = $this->input->post('tanggal_dari');
        $tanggal_sampai = $this->input->post('tanggal_sampai');
        if ($search != null) {
            $this->db->like('nama_pegawai', $search);
        }
        if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
            $this->db->where("STR_TO_DATE(tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE('$tanggal_dari', '%d-%m-%Y') AND STR_TO_DATE('$tanggal_sampai', '%d-%m-%Y')", null, false);
        }
        $this->db->order_by('id', 'DESC');
        $result = $this->db->get('presensi_pegawai')->result_array();
        return $result;
    }
    public function result_presensi_hariini()
    {
        $tanggal = date('d-m-Y');
        $this->db->select('*');
        $this->db->from('presensi_pegawai');
        $this->db->where('tanggal', $tanggal);
        $this->db->order_by('id', 'DESC');
        $result = $this->db->get()->result_array();
        return $result;
    }

    // public function cek_absensi()
    // {


    //     $id_pegawai = $this->input->post('id_pegawai');
    //     $tanggal = date('d-m-Y');

    //     $presensi = $this->db
    //         ->where('id_pegawai', $id_pegawai)
    //         ->where('tanggal', $tanggal)
    //         ->get('presensi_pegawai')
    //         ->num_rows();

    //     if ($presensi > 0) {
    //         return ['status' => true, 'msg' => 'Sudah Melakukab Absensi masuk'];
    //     } else {
    //         return ['status' => false, 'msg' => 'Belum Melakukan Absensi Masuk'];
    //     }
    // }

    public function cek_jabatan()
    {

        $id_pegawai = $this->input->post('id_pegawai');
        $presensi = $this->db
            ->where('id_pegawai', $id_pegawai)
            ->get('pegawai_jabatan')
            ->row_array();

        if (!empty($presensi)) {
            return ['status' => true, 'data' => $presensi];
        } else {
            return ['status' => false, 'data' => 'Tidak ada data'];
        }

    }


}
?>