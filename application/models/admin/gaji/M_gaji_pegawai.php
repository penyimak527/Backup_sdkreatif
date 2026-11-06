<?php

class M_gaji_pegawai extends CI_Model
{
    public function gaji_pegawai_result()
    {
        $search = $this->input->post('search');
        $this->db->select('gj.*, pg.nama_pegawai');
        $this->db->join('pegawai pg', 'gj.id_pegawai = pg.id');
        if ($search != null) {
            $this->db->like('pg.nama_pegawai', $search);
        }
         $this->db->order_by("
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM pegawai_jabatan pj
                WHERE pj.id_pegawai = pg.id
                AND LOWER(TRIM(pj.jabatan)) = 'kepala sekolah'
            ) THEN 0
            ELSE 1
        END ", 'ASC', false);

    $this->db->order_by('pg.id', 'ASC');
        $gaji = $this->db->get('gaji gj')->result_array();
        return $gaji;
    }
    public function pegawai_result()
    {
        $id_pegawai = $this->input->post('id_pegawai');

        $this->db->select('p.*');
        $this->db->from('pegawai p');
        $this->db->where("p.id NOT IN (SELECT id_pegawai FROM gaji)", null, false);
        if ($id_pegawai != null) {
            $this->db->or_where('p.id', $id_pegawai);
        }

        $pegawai = $this->db->get()->result_array();
        return $pegawai;
    }

    public function get_gaji_awal()
    {
        $get = $this->db->query("SELECT * FROM setting_gaji ");
        return $get->row_array();
    }
    public function tambah()
    {
        $id_pegawai = $this->input->post('id_pegawai');
        $gaji_pokok = (int) str_replace(',', '', $this->input->post('gaji_pokok'));
        $struktur = (int) str_replace(',', '', $this->input->post('struktural'));
        $pendidikan = (int) str_replace(',', '', $this->input->post('pendidikan'));
        $wali_kelas = (int) str_replace(',', '', $this->input->post('wali_kelas'));
        $hitung_kotor = $gaji_pokok + $struktur + $pendidikan + $wali_kelas;
        $tanggal = date('d-m-Y');
        $data = [
            'id_pegawai' => $id_pegawai,
            'gaji_pokok' => $gaji_pokok,
            'struktural' => $struktur,
            'tunjangan_pendidikan' => $pendidikan,
            'wali_kelas' => $wali_kelas,
            'total_pendapatan' => $hitung_kotor,
            'tanggal' => $tanggal
        ];
        $this->db->trans_begin();

        $this->db->insert('gaji', $data);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menyimpan data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil disimpan'
            );
        }
        return $response;
    }
    public function edit_gaji_rendah()
    {
        $nominal = (int) str_replace(',', '', $this->input->post('gaji_rendah'));
        $cek = $this->db->get('setting_gaji')->row_array();
        $tanggal = date('d-m-Y');
        $data = [
            'gaji_terendah' => $nominal,
            'tanggal' => $tanggal
        ];
        $this->db->trans_begin();

        if ($cek) {
            $this->db->where('id', $cek['id']);
            $this->db->update('setting_gaji', $data);
        } else {
            $this->db->insert('setting_gaji', $data);
        }
        // $this->update_gaji_pokok_pegawai($nominal);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menyimpan data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil disimpan'
            );
        }
        return $response;
    }

  public function gaji_pokok()
    {
        $pegawai = $this->db->get_where('pegawai')->result_array();
        $max = $this->db->select_max('angkatan')->get('master_angkatan')->row_array();
        $gaji_terendah_get = $this->db->get('setting_gaji')->row_array();
        $this->db->trans_begin();
        foreach ($pegawai as $key => $lspegawai) {
            $angkatan = $this->get_angkatan_berdasarkan_tmt($lspegawai['tmt']);
            if (!$angkatan) {
                continue;
            }
            $total_kenaikan = $this->hitung_total_kenaikan_angkatan($angkatan['angkatan'], $max['angkatan']);
            $gaji_pokok = (int) $gaji_terendah_get['gaji_terendah'] + $total_kenaikan;
            $cek_gaji = $this->db->get_where('gaji', ['id_pegawai' => $lspegawai['id']])->row_array();
            if (!$cek_gaji) {
                continue;
            }
            $total_pendapatan = $gaji_pokok + $cek_gaji['struktural'] + $cek_gaji['tunjangan_pendidikan'] + $cek_gaji['wali_kelas'];
            if ($lspegawai['angkatan'] != $angkatan['angkatan']) {
                $this->db->where('id', $lspegawai['id']);
                $this->db->update('pegawai', [
                    'angkatan' => $angkatan['angkatan']
                ]);
            }
            $this->db->where('id_pegawai', $lspegawai['id']);
            $this->db->update('gaji', [
                'gaji_pokok' => $gaji_pokok,
                'total_pendapatan' => $total_pendapatan
            ]);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menghitung gaji pokok'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil menghitung gaji pokok'
            );
        }
        return $response;
    }

    private function update_gaji_pokok_pegawai($gaji_terendah)
    {
        $max = $this->db->select_max('angkatan')->get('master_angkatan')->row_array();
        $pegawai = $this->db->get('pegawai')->result_array();
        foreach ($pegawai as $p) {
            $angkatan = $this->get_angkatan_berdasarkan_tmt($p['tmt']);
            if (!$angkatan) {
                continue;
            }
            $total_kenaikan = $this->hitung_total_kenaikan_angkatan($angkatan['angkatan'], $max['angkatan']);
            $gaji_pokok = (int) $gaji_terendah + $total_kenaikan;
            $cek_gaji = $this->db->get_where('gaji', ['id_pegawai' => $p['id']])->row_array();
            if (!$cek_gaji) {
                continue;
            }
            $total_pendapatan = $gaji_pokok + $cek_gaji['struktural'] + $cek_gaji['tunjangan_pendidikan'] + $cek_gaji['wali_kelas'];

            if ($p['angkatan'] != $angkatan['angkatan']) {
                $this->db->where('id', $p['id']);
                $this->db->update('pegawai', [
                    'angkatan' => $angkatan['angkatan']
                ]);
            }
            $this->db->where('id_pegawai', $p['id']);
            $this->db->update('gaji', [
                'gaji_pokok' => $gaji_pokok,
                'total_pendapatan' => $total_pendapatan
            ]);
        }
    }

    public function hitung_gaji_pokok()
    {
        $id_pegawai = $this->input->post('id_pegawai');
        // pegawai
        $pegawai = $this->db->where('id', $id_pegawai)->get('pegawai')->row();
        if (!$pegawai || empty($pegawai->tmt)) {
            return 0;
        }
        // setting gaji
        $setting = $this->db->get('setting_gaji')->row();
        $max = $this->db->select_max('angkatan')->get('master_angkatan')->row();

        $angkatan = $this->get_angkatan_berdasarkan_tmt($pegawai->tmt);
        if (!$angkatan) {
            return 0;
        }
        if ($pegawai->angkatan != $angkatan['angkatan']) {
            $this->db->where('id', $pegawai->id);
            $this->db->update('pegawai', [
                'angkatan' => $angkatan['angkatan']
            ]);
        }
        $total_kenaikan = $this->hitung_total_kenaikan_angkatan($angkatan['angkatan'], $max->angkatan);
        $gaji = (int) $setting->gaji_terendah + $total_kenaikan;
        return $gaji;
    }

    // private function hitung_total_kenaikan_angkatan($angkatan_pegawai, $angkatan_tertinggi)
    // {
    //     $angkatan_pegawai = (int) $angkatan_pegawai;
    //     $angkatan_tertinggi = (int) $angkatan_tertinggi;

    //     if ($angkatan_pegawai >= $angkatan_tertinggi) {
    //         return 0;
    //     }

    //     $total = $this->db
    //         ->select('SUM(CAST(kenaikan_gaji AS UNSIGNED)) AS total_kenaikan', false)
    //         ->where('CAST(angkatan AS UNSIGNED) >=', $angkatan_pegawai)
    //         ->where('CAST(angkatan AS UNSIGNED) <', $angkatan_tertinggi)
    //         ->get('master_angkatan')
    //         ->row_array();

    //     return (int) ($total['total_kenaikan'] ?? 0);
    // }

       private function hitung_total_kenaikan_angkatan(
    $angkatan_pegawai,
    $angkatan_tertinggi
) {
    $angkatan_pegawai = (int) $angkatan_pegawai;
    $angkatan_tertinggi = (int) $angkatan_tertinggi;

    if ($angkatan_pegawai >= $angkatan_tertinggi) {
        return 0;
    }

    /*
     * Gaji terendah dimiliki angkatan tertinggi.
     *
     * Untuk menghitung gaji angkatan yang lebih lama,
     * jumlahkan kenaikan dari angkatan setelah pegawai
     * sampai angkatan tertinggi.
     *
     * Contoh:
     * Pegawai angkatan 8, tertinggi 9
     * yang diambil adalah kenaikan angkatan 9.
     */
    $total = $this->db
        ->select(
            'SUM(CAST(kenaikan_gaji AS UNSIGNED)) AS total_kenaikan',
            false
        )
        ->where(
            'CAST(angkatan AS UNSIGNED) >',
            $angkatan_pegawai
        )
        ->where(
            'CAST(angkatan AS UNSIGNED) <=',
            $angkatan_tertinggi
        )
        ->get('master_angkatan')
        ->row_array();

    return (int) ($total['total_kenaikan'] ?? 0);
}

    private function get_angkatan_berdasarkan_tmt($tmt)
    {
        $tahun_tmt = $this->get_tahun_tmt($tmt);
        if (!$tahun_tmt) {
            return null;
        }

        return $this->db
            ->where('tahun_awal <=', $tahun_tmt)
            ->where('tahun_akhir >=', $tahun_tmt)
            ->order_by('tahun_awal', 'ASC')
            ->get('master_angkatan')
            ->row_array();
    }

    private function get_tahun_tmt($tmt)
    {
        if (empty($tmt)) {
            return null;
        }

        $tanggal = DateTime::createFromFormat('d-m-Y', $tmt);
        if ($tanggal) {
            return (int) $tanggal->format('Y');
        }

        $timestamp = strtotime($tmt);
        if (!$timestamp) {
            return null;
        }

        return (int) date('Y', $timestamp);
    }
    public function edit()
    {
        $id_gaji = $this->input->post('id_gaji');
        $id_pegawai = $this->input->post('id_pegawai');
        $gaji_pokok = (int) str_replace(',', '', $this->input->post('gaji_pokok'));
        $struktur = (int) str_replace(',', '', $this->input->post('struktural'));
        $pendidikan = (int) str_replace(',', '', $this->input->post('pendidikan'));
        $wali_kelas = (int) str_replace(',', '', $this->input->post('wali_kelas'));
        $tanggal = date('d-m-Y');

        $hitung_kotor = $gaji_pokok + $struktur + $pendidikan + $wali_kelas;

        $data = [
            'id_pegawai' => $id_pegawai,
            'gaji_pokok' => $gaji_pokok,
            'struktural' => $struktur,
            'tunjangan_pendidikan' => $pendidikan,
            'wali_kelas' => $wali_kelas,
            'total_pendapatan' => $hitung_kotor,
            'tanggal' => $tanggal
        ];

        $this->db->trans_begin();

        $this->db->where('id', $id_gaji);
        $this->db->update('gaji', $data);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal mengupdate data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil diupdate'
            );
        }
        return $response;
    }

   
    public function hapus_semua()
    {
        $id_gaji = $this->input->post('id');

        if (!is_array($id_gaji) || count($id_gaji) == 0) {
            return array(
                'status' => false,
                'code' => 400,
                'message' => 'Pilih data yang akan dihapus'
            );
        }

        $id_gaji = array_filter(array_map('intval', $id_gaji));
        if (count($id_gaji) == 0) {
            return array(
                'status' => false,
                'code' => 400,
                'message' => 'Data tidak valid'
            );
        }

        $this->db->trans_begin();
        $this->db->where_in('id', $id_gaji);
        $this->db->delete('gaji');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
                'message' => 'Gagal menghapus data'
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
                'message' => 'Data berhasil dihapus'
            );
        }
        return $response;
    }
}