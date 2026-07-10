<?php
class M_rencana_pengeluaran extends CI_Model
{
    protected $id_user;
    function __construct()
    {
        parent::__construct();
        $id_user = 1;
    }


    public function rencana_pengeluaran_result()
    {
        $periode = $this->input->post('tahun_ajaran');
        // $semester = $this->input->post('semester');
        $this->db->select('a.*, pg.nama_pegawai, mta.periode, sum(rd.nominal) AS total_rencana_pengeluaran');
        $this->db->from('rencana_pengeluaran a');
        $this->db->join('pegawai pg', 'a.id_pegawai = pg.id', 'left');
        $this->db->join('master_tahun_ajaran mta', 'a.tahun_ajaran = mta.id', 'left');
        $this->db->join('rencana_pengeluaran_detail rd', 'a.id = rd.id_rencana_pengeluaran', 'left');
        if ($periode != null) {
            $this->db->where('a.tahun_ajaran', $periode);
        }
        // if ($semester != null) {
        //     $this->db->where('a.semester', $semester);
        // }
        $this->db->order_by('a.id', 'DESC');
        $this->db->group_by('a.id');
        $rab_pengeluaran = $this->db->get()->result_array();
        return $rab_pengeluaran;
    }
    public function detail()
    {
        $id = $this->input->post('id');
        $header = $this->db
            ->select("rp.*,ta.periode as nama_tahun_ajaran,p.nama_pegawai ")
            ->from('rencana_pengeluaran rp')
            ->join('master_tahun_ajaran ta', 'ta.id = rp.tahun_ajaran')
            ->join('pegawai p', 'p.id = rp.id_pegawai')
            ->where('rp.id', $id)
            ->get()
            ->row_array();

        $detail = $this->db
            ->select("rpd.*,a.keterangan as nama_akun")
            ->from('rencana_pengeluaran_detail rpd')
            ->join('kode_akun a', 'a.id = rpd.kode_akun')
            ->where('rpd.id_rencana_pengeluaran', $id)
            ->order_by('a.id', 'ASC')
            ->get()
            ->result_array();

        $total_pengeluaran = $this->db->select_sum('nominal')->where('id_rencana_pengeluaran', $id)
            ->get('rencana_pengeluaran_detail')->row()->nominal;
        $sisa_asumsi = $header['total_asumsi_pemasukan'] - $total_pengeluaran;
        return [
            'header' => $header,
            'detail' => $detail,
            'total_pengeluaran' => $total_pengeluaran,
            'sisa_asumsi' => $sisa_asumsi
        ];
    }
    public function detail_edit()
    {
        $id = $this->input->post('id');
        $header = $this->db->where('id', $id)->get('rencana_pengeluaran')->row_array();
        $detail = $this->db->where('id_rencana_pengeluaran', $id)->get('rencana_pengeluaran_detail')->result_array();
        $akun = $this->db->where('jenis', 'Pengeluaran')->order_by('id', 'ASC')->get('kode_akun')->result_array();
        return [
            'id' => $header['id'],
            'tahun_ajaran' => $header['tahun_ajaran'],
            'semester' => $header['semester'],
            'total_asumsi' => $header['total_asumsi_pemasukan'],
            'akun' => $akun,
            'detail' => $detail
        ];
    }
    // public function ambilAsumsi()
    // {
    //     $tahun_ajaran = $this->input->post('tahun_ajaran');
    //     $semester = $this->input->post('semester');
    //     // cek apakah rencana pemasukan sudah dibuat
    //     $cek = $this->db->where('tahun_ajaran', $tahun_ajaran)->where('semester', $semester)->get('rencana_pemasukan')->num_rows();
    //     $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $tahun_ajaran])->row_array();
    //     if ($cek == 0) {
    //         return [
    //             'status' => false,
    //             'message' => 'Data asumsi pemasukan untuk Tahun Ajaran ' . $get_tahun_ajaran['periode'] . ' Semester ' . $semester . ' belum dibuat.'
    //         ];
    //     }
    //      // CEK RENCANA PENGELUARAN
    // $cek_pengeluaran = $this->db
    //     ->where('tahun_ajaran', $tahun_ajaran)
    //     ->where('semester', $semester)
    //     ->count_all_results('rencana_pengeluaran');

    // if ($cek_pengeluaran > 0) {
    //     return [
    //         'status' => false,
    //         'message' => 'Data Rencana Pengeluaran Tahun Ajaran ' . $get_tahun_ajaran['periode'] . ' Semester ' . $semester. ' sudah dibuat.'
    //     ];
    // }


    //     $asumsi = $this->db->query("
    //     SELECT COALESCE(SUM(d.total * j.persen / 100),0) total_asumsi
    //     FROM rencana_pemasukan p
    //     INNER JOIN rencana_pemasukan_jenis j
    //         ON j.id_rencana_pemasukan=p.id
    //     INNER JOIN rencana_pemasukan_detail d
    //         ON d.id_jenis=j.id
    //     WHERE p.tahun_ajaran='$tahun_ajaran'
    //     AND p.semester='$semester'
    // ")->row_array();

    //     $akun = $this->db->where('jenis', 'Pengeluaran')
    //         ->order_by('id', 'ASC')
    //         ->get('kode_akun')
    //         ->result_array();
    //     $total_asumsi = (float) $asumsi['total_asumsi'];
    //     return [
    //         'status' => true,
    //         'total_asumsi' => $total_asumsi,
    //         'akun' => $akun
    //     ];
    // }

    public function ambilAsumsi()
    {
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $semester = $this->input->post('semester');

        $get_tahun_ajaran = $this->db
            ->get_where('master_tahun_ajaran', ['id' => $tahun_ajaran])
            ->row_array();

        // CEK RENCANA ASUMSI PEMASUKAN
        $header_asumsi = $this->db
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->get('rencana_asumsi_pemasukan')
            ->row_array();

        if (!$header_asumsi) {
            return [
                'status' => false,
                'message' => 'Data rencana asumsi pemasukan untuk Tahun Ajaran ' .
                    $get_tahun_ajaran['periode'] .
                    ' Semester ' . $semester .
                    ' belum dibuat.'
            ];
        }

        // CEK RENCANA PENGELUARAN SUDAH ADA ATAU BELUM
        $cek_pengeluaran = $this->db
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->count_all_results('rencana_pengeluaran');

        if ($cek_pengeluaran > 0) {
            return [
                'status' => false,
                'message' => 'Data Rencana Pengeluaran Tahun Ajaran ' .
                    $get_tahun_ajaran['periode'] .
                    ' Semester ' . $semester .
                    ' sudah dibuat.'
            ];
        }

        // TENTUKAN BULAN LANGSUNG DI SINI
        if ($semester == 'Ganjil') {
            $bulan = ['07', '08', '09', '10', '11', '12'];
        } elseif ($semester == 'Genap') {
            $bulan = ['01', '02', '03', '04', '05', '06'];
        } elseif ($semester == 'Tahunan') {
            $bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        } else {
            $bulan = [];
        }

        $jumlah_bulan = count($bulan);

        // AMBIL TOTAL NOMINAL BULAN DARI RENCANA ASUMSI PEMASUKAN
        $total_nominal = $this->db
            ->select('COALESCE(SUM(nominal_bulan), 0) AS total_nominal', false)
            ->where('id_rencana_asumsi_pemasukan', $header_asumsi['id'])
            ->where_in('bulan', $bulan)
            ->get('rencana_asumsi_pemasukan_detail')
            ->row_array();

        $total_nominal_semester = (float) $total_nominal['total_nominal'];

        // HITUNG RATA-RATA PER BULAN
        $rata_rata_bulanan = 0;
        if ($jumlah_bulan > 0) {
            $rata_rata_bulanan = $total_nominal_semester / $jumlah_bulan;
        }

        $get_potongan_persen_gaji = $this->db->get_where('rumus_potongan', ['nama_potongan' => 'gaji'])->row_array();
        // $persen_gaji = 35;
        $persen_gaji = $get_potongan_persen_gaji['nominal_persen'];
        $nominal_gaji_per_bulan = $rata_rata_bulanan * $persen_gaji / 100;

        // AMBIL AKUN PENGELUARAN
        $akun = $this->db
            ->where('jenis', 'Pengeluaran')
            ->order_by('id', 'ASC')
            ->get('kode_akun')
            ->result_array();

        // BUAT DEFAULT PENGELUARAN
        $default_pengeluaran = [];

        foreach ($akun as $row) {
            foreach ($bulan as $nama_bulan) {

                $nominal = 0;

                // KHUSUS AKUN GAJI DIISI OTOMATIS
                if (strtolower(trim($row['keterangan'])) == 'gaji') {
                    $nominal = $nominal_gaji_per_bulan;
                }

                $default_pengeluaran[$row['id']][$nama_bulan] = $nominal;
            }
        }

        return [
            'status' => true,
            'id_rencana_asumsi_pemasukan' => $header_asumsi['id'],
            'bulan' => $bulan,
            'jumlah_bulan' => $jumlah_bulan,
            'total_asumsi' => $total_nominal_semester,
            'total_nominal_semester' => $total_nominal_semester,
            'rata_rata_bulanan' => $rata_rata_bulanan,
            'persen_gaji' => $persen_gaji,
            'nominal_gaji_per_bulan' => $nominal_gaji_per_bulan,
            'total_gaji_semester' => $nominal_gaji_per_bulan * $jumlah_bulan,
            'akun' => $akun,
            'default_pengeluaran' => $default_pengeluaran
        ];
    }

    public function ambilAsumsiEdit()
    {
        $id = $this->input->post('id');
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $semester = $this->input->post('semester');

        $get_tahun_ajaran = $this->db
            ->get_where('master_tahun_ajaran', ['id' => $tahun_ajaran])
            ->row_array();

        // CEK RENCANA ASUMSI PEMASUKAN
        $header_asumsi = $this->db
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->get('rencana_asumsi_pemasukan')
            ->row_array();

        if (!$header_asumsi) {
            return [
                'status' => false,
                'message' => 'Data rencana asumsi pemasukan untuk Tahun Ajaran ' .
                    $get_tahun_ajaran['periode'] .
                    ' Semester ' . $semester .
                    ' belum dibuat.'
            ];
        }

        // CEK RENCANA PENGELUARAN SUDAH ADA ATAU BELUM
        // KHUSUS EDIT: KECUALIKAN ID YANG SEDANG DIEDIT
        $cek_pengeluaran = $this->db
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->where('id !=', $id)
            ->count_all_results('rencana_pengeluaran');

        if ($cek_pengeluaran > 0) {
            return [
                'status' => false,
                'message' => 'Data Rencana Pengeluaran Tahun Ajaran ' .
                    $get_tahun_ajaran['periode'] .
                    ' Semester ' . $semester .
                    ' sudah dibuat.'
            ];
        }

        // TENTUKAN BULAN
        if ($semester == 'Ganjil') {
            $bulan = ['07', '08', '09', '10', '11', '12'];
        } elseif ($semester == 'Genap') {
            $bulan = ['01', '02', '03', '04', '05', '06'];
        } elseif ($semester == 'Tahunan') {
            $bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        } else {
            return [
                'status' => false,
                'message' => 'Semester tidak valid.'
            ];
        }

        $jumlah_bulan = count($bulan);

        // AMBIL TOTAL NOMINAL BULAN DARI RENCANA ASUMSI PEMASUKAN
        $total_nominal = $this->db
            ->select('COALESCE(SUM(nominal_bulan), 0) AS total_nominal', false)
            ->where('id_rencana_asumsi_pemasukan', $header_asumsi['id'])
            ->where_in('bulan', $bulan)
            ->get('rencana_asumsi_pemasukan_detail')
            ->row_array();

        $total_nominal_semester = (float) $total_nominal['total_nominal'];

        // HITUNG RATA-RATA PER BULAN
        $rata_rata_bulanan = 0;
        if ($jumlah_bulan > 0) {
            $rata_rata_bulanan = $total_nominal_semester / $jumlah_bulan;
        }

        // $persen_gaji = 35;
        $get_potongan_persen_gaji = $this->db->get_where('rumus_potongan', ['nama_potongan' => 'gaji'])->row_array();
        // $persen_gaji = $get_potongan_persen_gaji['nominal_persen'];
        $persen_gaji = $get_potongan_persen_gaji ? (float) $get_potongan_persen_gaji['nominal_persen'] : 0;
        $nominal_gaji_per_bulan = $rata_rata_bulanan * $persen_gaji / 100;

        // AMBIL AKUN PENGELUARAN
        $akun = $this->db
            ->where('jenis', 'Pengeluaran')
            ->order_by('id', 'ASC')
            ->get('kode_akun')
            ->result_array();

        // BUAT DEFAULT PENGELUARAN
        $default_pengeluaran = [];

        foreach ($akun as $row) {
            foreach ($bulan as $nama_bulan) {

                $nominal = 0;

                // KHUSUS AKUN GAJI DIISI OTOMATIS
                if (strpos(strtolower(trim($row['keterangan'])), 'gaji') !== false) {
                    $nominal = $nominal_gaji_per_bulan;
                }

                $default_pengeluaran[$row['id']][$nama_bulan] = $nominal;
            }
        }

        return [
            'status' => true,
            'id_rencana_asumsi_pemasukan' => $header_asumsi['id'],
            'bulan' => $bulan,
            'jumlah_bulan' => $jumlah_bulan,
            'total_asumsi' => $total_nominal_semester,
            'total_nominal_semester' => $total_nominal_semester,
            'rata_rata_bulanan' => $rata_rata_bulanan,
            'persen_gaji' => $persen_gaji,
            'nominal_gaji_per_bulan' => $nominal_gaji_per_bulan,
            'total_gaji_semester' => $nominal_gaji_per_bulan * $jumlah_bulan,
            'akun' => $akun,
            'default_pengeluaran' => $default_pengeluaran
        ];
    }
    // public function tambah()
    // {
    //     $id_pegawai = $this->session->userdata('admin')['id_pegawai'];
    //     $tahun_ajaran = $this->input->post('tahun_ajaran');
    //     $id_rencana_asumsi_pemasukan = $this->input->post('id_rencana_asumsi_pemasukan');
    //     $semester = $this->input->post('semester');
    //     $total_asumsi = str_replace(',', '', $this->input->post('total_asumsi'));

    //     $tanggal = date('d-m-Y');

    //     $header = [
    //         'id_pegawai' => $id_pegawai,
    //         'id_rencana_asumsi_pemasukan' => $id_rencana_asumsi_pemasukan,
    //         'tahun_ajaran' => $tahun_ajaran,
    //         'semester' => $semester,
    //         'total_asumsi_pemasukan' => $total_asumsi,
    //         'tanggal' => $tanggal
    //     ];
    //     $this->db->trans_begin();
    //     $this->db->insert('rencana_pengeluaran', $header);
    //     $id_rencana = $this->db->insert_id();

    //     $pengeluaran = $this->input->post('pengeluaran');
    //     $persen_gaji_global = (float) str_replace(',', '.', $this->input->post('persen_gaji'));

    //     $akun_gaji = $this->db
    //         ->select('id')
    //         ->from('kode_akun')
    //         ->where('jenis', 'Pengeluaran')
    //         ->like('LOWER(keterangan)', 'gaji')
    //         ->get()
    //         ->result_array();

    //     $kode_akun_gaji = array_column($akun_gaji, 'id');
    //     foreach ($pengeluaran as $kode_akun => $bulan) {
    //         foreach ($bulan as $nama_bulan => $nominal) {
    //             $nominal = str_replace(',', '', $nominal);

    //             if ($nominal == '') {
    //                 $nominal = 0;
    //             }

    //             $persen_gaji = in_array($kode_akun, $kode_akun_gaji)
    //                 ? $persen_gaji_global
    //                 : null;

    //             $detail = [
    //                 'id_rencana_pengeluaran' => $id_rencana,
    //                 'kode_akun' => $kode_akun,
    //                 'bulan' => $nama_bulan,
    //                 'nominal' => $nominal,
    //                 'persen_gaji' => $persen_gaji
    //             ];

    //             $this->db->insert('rencana_pengeluaran_detail', $detail);
    //         }

    //     }
    //     $this->db->trans_complete();
    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();

    //         return [
    //             'status' => false,
    //             'message' => 'Gagal menyimpan data rencana pengeluaran.'
    //         ];
    //     }

    //     $this->db->trans_commit();

    //     return [
    //         'status' => true,
    //         'message' => 'Data rencana pengeluaran berhasil disimpan.'
    //     ];
    // }
    public function tambah()
    {
        $id_pegawai = $this->session->userdata('admin')['id_pegawai'];
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $id_rencana_asumsi_pemasukan = $this->input->post('id_rencana_asumsi_pemasukan');
        $semester = $this->input->post('semester');
        $total_asumsi = str_replace(',', '', $this->input->post('total_asumsi'));
        $pengeluaran = $this->input->post('pengeluaran');

        $persen_gaji_global = (float) str_replace(',', '.', $this->input->post('persen_gaji'));

        if (empty($pengeluaran)) {
            return [
                'status' => false,
                'message' => 'Detail rencana pengeluaran belum dibuat.'
            ];
        }

        $tanggal = date('d-m-Y');

        $header = [
            'id_pegawai' => $id_pegawai,
            'id_rencana_asumsi_pemasukan' => $id_rencana_asumsi_pemasukan,
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester,
            'total_asumsi_pemasukan' => $total_asumsi,
            'tanggal' => $tanggal
        ];

        $this->db->trans_begin();

        $this->db->insert('rencana_pengeluaran', $header);
        $id_rencana = $this->db->insert_id();

        $akun_gaji = $this->db
            ->select('id')
            ->from('kode_akun')
            ->where('jenis', 'Pengeluaran')
            ->like('LOWER(keterangan)', 'gaji')
            ->get()
            ->result_array();

        $kode_akun_gaji = array_map('intval', array_column($akun_gaji, 'id'));
        foreach ($pengeluaran as $kode_akun => $bulan) {
            foreach ($bulan as $nama_bulan => $nominal) {
                $nominal = str_replace(',', '', $nominal);

                if ($nominal == '') {
                    $nominal = 0;
                }
                $persen_gaji = in_array((int) $kode_akun, $kode_akun_gaji) ? $persen_gaji_global : null;
                $detail = [
                    'id_rencana_pengeluaran' => $id_rencana,
                    'kode_akun' => $kode_akun,
                    'bulan' => $nama_bulan,
                    'nominal' => $nominal,
                    'persen_gaji' => $persen_gaji
                ];

                $this->db->insert('rencana_pengeluaran_detail', $detail);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'message' => 'Gagal menyimpan data rencana pengeluaran.'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Data rencana pengeluaran berhasil disimpan.'
        ];
    }
    // public function edit()
    // {
    //     $id = $this->input->post('id');
    //     $tahun_ajaran = $this->input->post('tahun_ajaran');
    //     $semester = $this->input->post('semester');
    //     $pengeluaran = $this->input->post('pengeluaran');
    //     $persen_gaji_global = (float) str_replace(',', '.', $this->input->post('persen_gaji'));


    //     $this->db->trans_begin();

    //     // update header
    //     $this->db->where('id', $id);
    //     $this->db->update(
    //         'rencana_pengeluaran',
    //         [
    //             'tahun_ajaran' => $tahun_ajaran,
    //             'semester' => $semester
    //         ]
    //     );

    //     // hapus detail lama
    //     $this->db->where('id_rencana_pengeluaran', $id);
    //     $this->db->delete('rencana_pengeluaran_detail');

    //     // insert ulang detail baru
    //     foreach ($pengeluaran as $kode_akun => $bulanData) {
    //         foreach ($bulanData as $bulan => $nominal) {
    //             $nominal = str_replace(',', '', $nominal);

    //             // $this->db->insert(
    //             //     'rencana_pengeluaran_detail',
    //             //     [
    //             //         'id_rencana_pengeluaran' => $id,
    //             //         'kode_akun' => $kode_akun,
    //             //         'bulan' => $bulan,
    //             //         'nominal' => $nominal
    //             //     ]
    //             // );
    //             $persen_gaji = null;

    //             if (
    //                 isset($persen_gaji_input[$kode_akun]) &&
    //                 isset($persen_gaji_input[$kode_akun][$bulan]) &&
    //                 $persen_gaji_input[$kode_akun][$bulan] !== ''
    //             ) {
    //                 $persen_gaji = (float) str_replace(',', '.', $persen_gaji_input[$kode_akun][$bulan]);
    //             }

    //             $this->db->insert(
    //                 'rencana_pengeluaran_detail',
    //                 [
    //                     'id_rencana_pengeluaran' => $id,
    //                     'kode_akun' => $kode_akun,
    //                     'bulan' => $bulan,
    //                     'nominal' => $nominal,
    //                     'persen_gaji' => $persen_gaji
    //                 ]
    //             );
    //         }
    //     }

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();
    //         return [
    //             'status' => false,
    //             'message' => 'Gagal mengupdate data'
    //         ];
    //     }
    //     $this->db->trans_commit();
    //     return [
    //         'status' => true,
    //         'message' => 'Data berhasil diupdate'
    //     ];
    // }

    public function edit()
    {
        $id = $this->input->post('id');
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $semester = $this->input->post('semester');
        $pengeluaran = $this->input->post('pengeluaran');

        $persen_gaji_global = (float) str_replace(',', '.', $this->input->post('persen_gaji'));

        if ($id == '') {
            return [
                'status' => false,
                'message' => 'ID rencana pengeluaran tidak ditemukan.'
            ];
        }

        if (empty($pengeluaran)) {
            return [
                'status' => false,
                'message' => 'Detail rencana pengeluaran belum dibuat.'
            ];
        }

        $this->db->trans_begin();

        $this->db->where('id', $id);
        $this->db->update('rencana_pengeluaran', [
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester
        ]);

        $this->db->where('id_rencana_pengeluaran', $id);
        $this->db->delete('rencana_pengeluaran_detail');

        $akun_gaji = $this->db
            ->select('id')
            ->from('kode_akun')
            ->where('jenis', 'Pengeluaran')
            ->like('LOWER(keterangan)', 'gaji')
            ->get()
            ->result_array();

        $kode_akun_gaji = array_map('intval', array_column($akun_gaji, 'id'));

        foreach ($pengeluaran as $kode_akun => $bulanData) {
            foreach ($bulanData as $bulan => $nominal) {
                $nominal = str_replace(',', '', $nominal);

                if ($nominal == '') {
                    $nominal = 0;
                }

                $persen_gaji = in_array((int) $kode_akun, $kode_akun_gaji) ? $persen_gaji_global : null;
                $this->db->insert('rencana_pengeluaran_detail', [
                    'id_rencana_pengeluaran' => $id,
                    'kode_akun' => $kode_akun,
                    'bulan' => $bulan,
                    'nominal' => $nominal,
                    'persen_gaji' => $persen_gaji
                ]);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'message' => 'Gagal mengupdate data'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Data berhasil diupdate'
        ];
    }

    public function hapus()
    {
        $id_rencana_pengeluaran = $this->input->post('id');

        $this->db->trans_begin();
        $this->db->delete('rencana_pengeluaran_detail', ['id_rencana_pengeluaran' => $id_rencana_pengeluaran]);
        $this->db->delete('rencana_pengeluaran', ['id' => $id_rencana_pengeluaran]);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = array(
                'status' => false,
                'code' => 401,
            );
        } else {
            $this->db->trans_commit();
            $response = array(
                'status' => true,
                'code' => 200,
            );
        }
        return $response;
    }

    public function get_persen_gaji()
    {
        $get_potongan_gaji = $this->db->query("SELECT * FROM rumus_potongan WHERE nama_potongan = 'gaji'");
        return $get_potongan_gaji->row_array();
    }
   public function edit_persen_gaji()
{
    $nominal = (float) str_replace(',', '.', $this->input->post('persen'));

    $this->db->trans_begin();

    $cek = $this->db
        ->where('nama_potongan', 'gaji')
        ->get('rumus_potongan')
        ->row_array();

    if ($cek) {
        $this->db
            ->where('id', $cek['id'])
            ->update('rumus_potongan', [
                'nominal_persen' => $nominal
            ]);
    } else {
        $this->db->insert('rumus_potongan', [
            'nama_potongan' => 'gaji',
            'nominal_persen' => $nominal
        ]);
    }

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
}
?>