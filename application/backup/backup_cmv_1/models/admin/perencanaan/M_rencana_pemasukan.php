<?php
class M_rencana_pemasukan extends CI_Model
{
    protected $id_user;
    function __construct()
    {
        parent::__construct();
        $id_user = 1;
    }


    public function rencana_pemasukan_result()
    {

        $periode = $this->input->post('periode');
        // $semester = $this->input->post('semester');
        $this->db->select('a.*, mta.periode, pg.nama_pegawai,SUM(d.total) as total');
        $this->db->from('rencana_pemasukan a');
        $this->db->join('pegawai pg', 'a.id_pegawai = pg.id', 'left');
        $this->db->join('master_tahun_ajaran mta', 'a.tahun_ajaran = mta.id', 'left');
        $this->db->join('rencana_pemasukan_jenis j', 'j.id_rencana_pemasukan = a.id', 'left');
        $this->db->join('rencana_pemasukan_detail d', 'd.id_jenis = j.id', 'left');
        if ($periode != null) {
            $this->db->where('a.tahun_ajaran', $periode);
        }
        // if ($semester != null) {
        //     $this->db->where('a.semester', $semester);
        // }
        $this->db->group_by('a.id');

        $this->db->order_by('a.id', 'DESC');
        $rab_pemasukan = $this->db->get()->result_array();
        return $rab_pemasukan;
    }

    public function detail()
    {
        $id_rencana_pemasukan = $this->input->post('id');
        // ambil jenis dulu
        $this->db->from('rencana_pemasukan_jenis');
        $this->db->where('id_rencana_pemasukan', $id_rencana_pemasukan);
        $this->db->order_by('id', 'ASC');
        $jenis = $this->db->get()->result_array();

        $result = [];

        foreach ($jenis as $j) {

            // ambil detail per jenis
            $this->db->from('rencana_pemasukan_detail');
            $this->db->where('id_jenis', $j['id']);
            $detail = $this->db->get()->result_array();

            $result[] = [
                'nama_jenis' => $j['nama_jenis'],
                'persen_masuk' => $j['persen'],
                'detail' => $detail
            ];
        }
        return $result;
    }

    public function detail_edit()
    {
        $id = $this->input->post('id');

        // header
        $this->db->where('id', $id);
        $header = $this->db->get('rencana_pemasukan')->row_array();
        if (!$header) {
            return [];
        }

        $this->db->where('id_rencana_pemasukan', $id);
        $this->db->order_by('id', 'ASC');
        $jenis = $this->db->get('rencana_pemasukan_jenis')->result_array();

        $resultJenis = [];
        foreach ($jenis as $j) {
            $this->db->where('id_jenis', $j['id']);
            $this->db->order_by('id', 'ASC');
            $detail = $this->db->get('rencana_pemasukan_detail')->result_array();
            $resultJenis[] = [
                'id' => $j['id'],
                'kode_akun' => $j['kode_akun'],
                'nama_jenis' => $j['nama_jenis'],
                'persen_masuk' => $j['persen'],
                'detail' => $detail
            ];
        }

        return [
            'id' => $header['id'],
            'tahun_ajaran' => $header['tahun_ajaran'],
            'semester' => $header['semester'],
            'jenis' => $resultJenis
        ];
    }

    public function tambah()
    {
        $id_pegawai = $this->session->userdata('admin')['id_pegawai'];
        $jenis = $this->input->post('jenis');
        $semester = $this->input->post('semester');
        $tahun_ajaran = $this->input->post('tahun_ajaran');

        $this->db->trans_begin();

        $this->db->insert('rencana_pemasukan', [
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester,
            'id_pegawai' => $id_pegawai,
            'tanggal' => date('d-m-Y')
        ]);

        $id_rencana = $this->db->insert_id();

        if (!empty($jenis)) {
            foreach ($jenis as $key => $js) {
                $nama_jenis = null;
                if (!empty($js['kode_akun'])) {
                    $akun = $this->db->get_where('kode_akun', ['id' => $js['kode_akun']])->row_array();
                    $nama_jenis = $akun ? $akun['keterangan'] : null;
                }

                $this->db->insert('rencana_pemasukan_jenis', [
                    'id_rencana_pemasukan' => $id_rencana,
                    'kode_akun' => $js['kode_akun'],
                    'nama_jenis' => $nama_jenis,
                    'persen' => $js['persen_masuk']
                ]);

                $id_jenis = $this->db->insert_id();
                if (!empty($js['detail'])) {
                    foreach ($js['detail'] as $d) {
                        $volume = (int) $d['volume'];
                        $nilai_satuan = (int) str_replace(',', '', $d['nilai_satuan']);
                        $volume_penerimaan = (int) $d['volume_penerimaan'];
                        $jumlah = $volume * $nilai_satuan;
                        $total = $jumlah * $volume_penerimaan;
                        $this->db->insert('rencana_pemasukan_detail', [
                            'id_rencana_pemasukan' => $id_rencana,
                            'id_jenis' => $id_jenis,
                            'nama_kategori' => $d['nama_kategori'],
                            'satuan' => $d['satuan'],
                            'volume' => $volume,
                            'nilai_satuan' => $nilai_satuan,
                            'jumlah' => $jumlah,
                            'satuan_penerimaan' => $d['satuan_penerimaan'],
                            'volume_penerimaan' => $volume_penerimaan,
                            'total' => $total,
                            'semester' => $semester,
                            'tahun_ajaran' => $tahun_ajaran
                        ]);
                    }
                }
            }
        }

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


    public function edit()
    {
        $id_rencana = $this->input->post('id');
        $semester = $this->input->post('semester');
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $jenis = $this->input->post('jenis');
        $this->db->trans_begin();

        // update header
        $this->db->where('id', $id_rencana);
        $this->db->update('rencana_pemasukan', [
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester,
        ]);

        $this->db->select('id');
        $this->db->from('rencana_pemasukan_jenis');
        $this->db->where('id_rencana_pemasukan', $id_rencana);
        $jenisLama = $this->db->get()->result_array();
        $idsJenis = array_column($jenisLama, 'id');

        // hapus detail lama
        if (!empty($idsJenis)) {
            $this->db->where_in('id_jenis', $idsJenis);
            $this->db->delete('rencana_pemasukan_detail');
        }
        $this->db->delete('rencana_pemasukan_jenis', ['id_rencana_pemasukan' => $id_rencana]);
        if (!empty($jenis)) {
            foreach ($jenis as $js) {
                $nama_jenis = null;
                if (!empty($js['kode_akun'])) {
                    $akun = $this->db->get_where('kode_akun', ['id' => $js['kode_akun']])->row_array();
                    $nama_jenis = $akun ? $akun['keterangan'] : null;
                }

                $this->db->insert(
                    'rencana_pemasukan_jenis',
                    [
                        'id_rencana_pemasukan' => $id_rencana,
                        'kode_akun' => $js['kode_akun'],
                        'nama_jenis' => $nama_jenis,
                        'persen' => $js['persen_masuk']
                    ]
                );

                $id_jenis = $this->db->insert_id();
                if (!empty($js['detail'])) {
                    foreach ($js['detail'] as $d) {
                        $volume = (int) str_replace(',', '', $d['volume']);
                        $nilai = (int) str_replace(',', '', $d['nilai_satuan']);
                        $volumePenerimaan = (int) str_replace(',', '', $d['volume_penerimaan']);
                        $jumlah = $volume * $nilai;
                        $total = $jumlah * $volumePenerimaan;

                        $this->db->insert(
                            'rencana_pemasukan_detail',
                            [
                                'id_rencana_pemasukan' => $id_rencana,
                                'id_jenis' => $id_jenis,
                                'nama_kategori' => $d['nama_kategori'],
                                'satuan' => $d['satuan'],
                                'volume' => $volume,
                                'nilai_satuan' => $nilai,
                                'jumlah' => $jumlah,
                                'satuan_penerimaan' => $d['satuan_penerimaan'],
                                'volume_penerimaan' => $volumePenerimaan,
                                'total' => $total,
                                'semester' => $semester,
                                'tahun_ajaran' => $tahun_ajaran
                            ]
                        );
                    }
                }
            }
        }

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

    public function cek_rab_pemasukan()
    {
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $get_tahun_ajaran = $this->db->get_where('master_tahun_ajaran', ['id' => $tahun_ajaran])->row_array();
        $semester = $this->input->post('semester');
        $cek = $this->db->where('tahun_ajaran', $tahun_ajaran)->where('semester', $semester)->count_all_results('rencana_pemasukan');

        if ($cek > 0) {
            return [
                'status' => 'ada',
                'nama_tahun_ajaran' => $get_tahun_ajaran['periode']
            ];
        } else {
            return [
                'status' => 'tidak_ada',
                'nama_tahun_ajaran' => ''
            ];
        }
    }

    public function get_data_asumsi_pemasukan()
    {
        $id_rencana_pemasukan = $this->input->post('id_rencana_pemasukan');

        if (!$id_rencana_pemasukan) {
            return [
                'status' => false,
                'message' => 'ID rencana pemasukan tidak ditemukan'
            ];
        }

        $header = $this->db->query("
        SELECT 
            rp.id AS id_rencana_pemasukan,
            rp.id_pegawai,
            rp.tahun_ajaran,
            rp.semester,
            rp.tanggal,
            mta.periode
        FROM rencana_pemasukan rp
        LEFT JOIN master_tahun_ajaran mta 
            ON mta.id = rp.tahun_ajaran
        WHERE rp.id = ?
    ", [$id_rencana_pemasukan])->row_array();

        if (!$header) {
            return [
                'status' => false,
                'message' => 'Data rencana pemasukan tidak ditemukan'
            ];
        }

        if ($header['semester'] == 'Tahunan') {
            //     $list_bulan = ['07', '08', '09', '10', '11', '12'];
            // } elseif ($header['semester'] == 'Genap') {
            //     $list_bulan = ['01', '02', '03', '04', '05', '06'];
            // } else {
            $list_bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        }

        $asumsi_header = $this->db->get_where('rencana_asumsi_pemasukan', [
            'id_rencana_pemasukan' => $id_rencana_pemasukan,
            'tahun_ajaran' => $header['tahun_ajaran'],
            'semester' => $header['semester']
        ])->row_array();

        $detail_asumsi_tersimpan = [];

        if ($asumsi_header) {
            $detail_lama = $this->db->get_where('rencana_asumsi_pemasukan_detail', [
                'id_rencana_asumsi_pemasukan' => $asumsi_header['id']
            ])->result_array();

            foreach ($detail_lama as $d) {
                $kode_akun = $d['kode_akun'];
                // $bulan = (int) $d['bulan'];
                $bulan = str_pad($d['bulan'], 2, '0', STR_PAD_LEFT);

                if (!isset($detail_asumsi_tersimpan[$kode_akun])) {
                    $detail_asumsi_tersimpan[$kode_akun] = [
                        'total_asumsi_masuk' => (float) $d['total_asumsi_masuk'],
                        'persen_masuk' => (float) $d['persen_masuk'],
                        'asumsi_masuk' => (float) $d['asumsi_masuk'],
                        'saving_normal' => (float) $d['saving_normal'],
                        'saving_persen' => (float) $d['saving_persen'],
                        'bulan' => []
                    ];
                }

                $detail_asumsi_tersimpan[$kode_akun]['bulan'][$bulan] = (float) $d['nominal_bulan'];
            }
        }

        /**
         * Ambil semua kode akun pemasukan.
         * Semua kode akun jenis Pemasukan tetap tampil,
         * meskipun tidak ada di rencana_pemasukan.
         */
        $kode_akun = $this->db->query("
        SELECT
            ka.id AS kode_akun,
            ka.keterangan AS kategori,

            COALESCE(MAX(rpj.persen), 0) AS persen_masuk,
            COALESCE(SUM(rpd.total), 0) AS total_asumsi_masuk,

            COALESCE(MAX(rpd.satuan_penerimaan), '') AS satuan_penerimaan,
            COALESCE(MAX(rpd.volume_penerimaan), 0) AS volume_penerimaan

        FROM kode_akun ka

        LEFT JOIN rencana_pemasukan_jenis rpj
            ON rpj.kode_akun = ka.id
            AND rpj.id_rencana_pemasukan = ?

        LEFT JOIN rencana_pemasukan_detail rpd
            ON rpd.id_jenis = rpj.id
            AND rpd.id_rencana_pemasukan = ?

        WHERE ka.jenis = 'Pemasukan'

        GROUP BY ka.id, ka.keterangan
        ORDER BY ka.id ASC
    ", [$id_rencana_pemasukan, $id_rencana_pemasukan])->result_array();

        /**
         * Ambil detail rencana pemasukan untuk mendeteksi apakah nama_kategori adalah nama bulan.
         * Contoh:
         * - Juli
         * - Agustus
         * - September
         *
         * Ini berguna untuk kasus BOS/PPDB yang detailnya memang sudah berupa bulan.
         */
        $detail_rencana = $this->db->query("
        SELECT
            rpj.kode_akun,
            rpd.nama_kategori,
            rpd.total
        FROM rencana_pemasukan_jenis rpj
        INNER JOIN rencana_pemasukan_detail rpd
            ON rpd.id_jenis = rpj.id
            AND rpd.id_rencana_pemasukan = rpj.id_rencana_pemasukan
        WHERE rpj.id_rencana_pemasukan = ?
    ", [$id_rencana_pemasukan])->result_array();

        $detail_bulan_dari_rencana = [];

        foreach ($detail_rencana as $dr) {
            $kode = $dr['kode_akun'];
            $bulan = $this->nama_bulan_ke_angka($dr['nama_kategori']);

            if ($bulan) {
                if (!isset($detail_bulan_dari_rencana[$kode])) {
                    $detail_bulan_dari_rencana[$kode] = [];
                }

                if (!isset($detail_bulan_dari_rencana[$kode][$bulan])) {
                    $detail_bulan_dari_rencana[$kode][$bulan] = 0;
                }

                $detail_bulan_dari_rencana[$kode][$bulan] += (float) $dr['total'];
            }
        }

        $data = [];

        foreach ($kode_akun as $row) {
            $kode = $row['kode_akun'];
            $kategori = $row['kategori'];

            $total_asumsi_masuk = (float) $row['total_asumsi_masuk'];
            $persen_masuk = (float) $row['persen_masuk'];

            $asumsi_masuk = $total_asumsi_masuk * ($persen_masuk / 100);
            $saving_normal = $total_asumsi_masuk - $asumsi_masuk;
            // $saving_persen = $total_asumsi_masuk > 0 ? (100 - $persen_masuk) : 0;
            $saving_persen = $total_asumsi_masuk > 0 ? $saving_normal / $total_asumsi_masuk : 0;

            $bulan_data = [];

            foreach ($list_bulan as $b) {
                $bulan_data[$b] = 0;
            }

            /**
             * PRIORITAS 1:
             * Kalau sudah ada data rencana_asumsi_pemasukan_detail,
             * gunakan data tersimpan.
             */
            if (isset($detail_asumsi_tersimpan[$kode])) {
                $total_asumsi_masuk = $detail_asumsi_tersimpan[$kode]['total_asumsi_masuk'];
                $persen_masuk = $detail_asumsi_tersimpan[$kode]['persen_masuk'];
                $asumsi_masuk = $detail_asumsi_tersimpan[$kode]['asumsi_masuk'];
                $saving_normal = $detail_asumsi_tersimpan[$kode]['saving_normal'];
                $saving_persen = $detail_asumsi_tersimpan[$kode]['saving_persen'];

                foreach ($list_bulan as $b) {
                    $bulan_data[$b] = $detail_asumsi_tersimpan[$kode]['bulan'][$b] ?? 0;
                }
            } elseif (isset($detail_bulan_dari_rencana[$kode])) {
                foreach ($list_bulan as $b) {
                    $bulan_data[$b] = $detail_bulan_dari_rencana[$kode][$b] ?? 0;
                }
            } else {
                // $satuan_penerimaan = strtolower(trim($row['satuan_penerimaan']));
                // $volume_penerimaan = (float) $row['volume_penerimaan'];

                $is_dana_partisipasi = strtolower(trim($kategori)) == 'dana partisipasi';

                if ($is_dana_partisipasi) {
                    $jumlah_bulan_isi = (int) $row['volume_penerimaan'];

                    if ($jumlah_bulan_isi <= 0) {
                        $jumlah_bulan_isi = count($list_bulan);
                    }

                    if ($jumlah_bulan_isi > count($list_bulan)) {
                        $jumlah_bulan_isi = count($list_bulan);
                    }

                    $bulan_terisi = array_slice($list_bulan, 0, $jumlah_bulan_isi);

                    // Dana Partisipasi dibagi dari hasil persen
                    $nilai_bulan = $jumlah_bulan_isi > 0 ? $asumsi_masuk / $jumlah_bulan_isi : 0;

                    foreach ($list_bulan as $b) {
                        if (in_array($b, $bulan_terisi)) {
                            $bulan_data[$b] = $nilai_bulan;
                        } else {
                            $bulan_data[$b] = 0;
                        }
                    }
                } else {
                    // Selain Dana Partisipasi tidak otomatis isi bulan
                    foreach ($list_bulan as $b) {
                        $bulan_data[$b] = 0;
                    }
                }
                // if ($satuan_penerimaan == 'keg' && $volume_penerimaan <= 1) {
                //     foreach ($list_bulan as $b) {
                //         $bulan_data[$b] = 0;
                //     }
                // } else {
                //     $jumlah_bulan_isi = (int) $volume_penerimaan;
                //     if ($jumlah_bulan_isi <= 0) {
                //         $jumlah_bulan_isi = count($list_bulan);
                //     }
                //     if ($jumlah_bulan_isi > count($list_bulan)) {
                //         $jumlah_bulan_isi = count($list_bulan);
                //     }
                //     $bulan_terisi = array_slice($list_bulan, 0, $jumlah_bulan_isi);
                //     $dasar_nilai_bulan = $asumsi_masuk;
                //     // if (strtolower(trim($kategori)) == 'dana partisipasi') {
                //     //     $dasar_nilai_bulan = $total_asumsi_masuk;
                //     // } else {
                //     //     $dasar_nilai_bulan = $asumsi_masuk;
                //     // }
                //     $nilai_bulan = $jumlah_bulan_isi > 0 ? $dasar_nilai_bulan / $jumlah_bulan_isi : 0;
                //     foreach ($list_bulan as $b) {
                //         if (in_array($b, $bulan_terisi)) {
                //             $bulan_data[$b] = $nilai_bulan;
                //         } else {
                //             $bulan_data[$b] = 0;
                //         }
                //     }
                // }
            }

            $data[] = [
                'kode_akun' => $kode,
                'kategori' => $kategori,
                'total_asumsi_masuk' => $total_asumsi_masuk,
                'persen_masuk' => $persen_masuk,
                'asumsi_masuk' => $asumsi_masuk,
                'saving_normal' => $saving_normal,
                'saving_persen' => $saving_persen,
                'satuan_penerimaan' => $row['satuan_penerimaan'],
                'volume_penerimaan' => $row['volume_penerimaan'],
                'bulan' => $bulan_data
            ];
        }

        return [
            'status' => true,
            'header' => [
                'id_rencana_pemasukan' => $header['id_rencana_pemasukan'],
                'id_pegawai' => $header['id_pegawai'],
                'tahun_ajaran' => $header['tahun_ajaran'],
                'periode' => $header['periode'],
                'semester' => $header['semester'],
                'tanggal' => $header['tanggal'],
            ],
            'list_bulan' => $list_bulan,
            'data' => $data
        ];
    }
    private function nama_bulan_ke_angka($nama_bulan)
    {
        $nama_bulan = strtolower(trim($nama_bulan));

        $map = [
            'januari' => '01',
            'jan' => '01',

            'februari' => '02',
            'feb' => '02',

            'maret' => '03',
            'mar' => '03',

            'april' => '04',
            'apr' => '04',

            'mei' => '05',

            'juni' => '06',
            'jun' => '06',

            'juli' => '07',
            'jul' => '07',

            'agustus' => '08',
            'agu' => '08',
            'aug' => '08',

            'september' => '09',
            'sep' => '09',

            'oktober' => '10',
            'october' => '10',
            'okt' => '10',
            'oct' => '10',

            'november' => '11',
            'nopember' => '11',
            'nov' => '11',
            'nop' => '11',

            'desember' => '12',
            'december' => '12',
            'des' => '12',
            'dec' => '12',
        ];

        return $map[$nama_bulan] ?? null;
    }

    public function simpan_asumsi_pemasukan()
    {
        $id_rencana_pemasukan = $this->input->post('id_rencana_pemasukan');
        $tahun_ajaran = $this->input->post('tahun_ajaran');
        $semester = $this->input->post('semester');
        $detail = $this->input->post('detail');

        if (!$id_rencana_pemasukan || !$tahun_ajaran || !$semester) {
            return [
                'status' => false,
                'message' => 'Data header asumsi pemasukan belum lengkap'
            ];
        }

        if (empty($detail)) {
            return [
                'status' => false,
                'message' => 'Detail asumsi pemasukan masih kosong'
            ];
        }

        $this->db->trans_begin();

        // Cek apakah asumsi pemasukan untuk rencana ini sudah pernah dibuat
        $cek = $this->db->get_where('rencana_asumsi_pemasukan', [
            'id_rencana_pemasukan' => $id_rencana_pemasukan,
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester
        ])->row_array();

        if ($cek) {
            // Jika sudah ada, update header
            $id_rencana_asumsi_pemasukan = $cek['id'];

            $this->db->where('id', $id_rencana_asumsi_pemasukan);
            $this->db->update('rencana_asumsi_pemasukan', [
                'id_pegawai' => $this->session->userdata('admin')['id_pegawai'],
                'tanggal' => date('d-m-Y')
            ]);

            // Hapus detail lama agar diganti dengan data terbaru
            $this->db->where('id_rencana_asumsi_pemasukan', $id_rencana_asumsi_pemasukan);
            $this->db->delete('rencana_asumsi_pemasukan_detail');

        } else {
            // Jika belum ada, insert header baru
            $this->db->insert('rencana_asumsi_pemasukan', [
                'id_rencana_pemasukan' => $id_rencana_pemasukan,
                'id_pegawai' => $this->session->userdata('admin')['id_pegawai'],
                'tahun_ajaran' => $tahun_ajaran,
                'semester' => $semester,
                'tanggal' => date('Y-m-d')
            ]);

            $id_rencana_asumsi_pemasukan = $this->db->insert_id();
        }

        // Insert ulang detail per kode akun dan per bulan
        foreach ($detail as $d) {
            $kode_akun = $d['kode_akun'] ?? null;

            if (!$kode_akun) {
                continue;
            }

            $total_asumsi_masuk = str_replace(',', '', $d['total_asumsi_masuk'] ?? 0);
            $persen_masuk = $d['persen_masuk'] ?? 0;
            $asumsi_masuk = str_replace(',', '', $d['asumsi_masuk'] ?? 0);
            $saving_normal = str_replace(',', '', $d['saving_normal'] ?? 0);
            $saving_persen = str_replace(',', '.', $d['saving_persen'] ?? 0);

            if (!empty($d['bulan'])) {
                foreach ($d['bulan'] as $bulan => $nominal_bulan) {
                    $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
                    $this->db->insert('rencana_asumsi_pemasukan_detail', [
                        'id_rencana_asumsi_pemasukan' => $id_rencana_asumsi_pemasukan,
                        'kode_akun' => $kode_akun,
                        'bulan' => $bulan,
                        'total_asumsi_masuk' => $total_asumsi_masuk,
                        'persen_masuk' => $persen_masuk,
                        'asumsi_masuk' => $asumsi_masuk,
                        'saving_normal' => $saving_normal,
                        'saving_persen' => $saving_persen,
                        'nominal_bulan' => str_replace(',', '', $nominal_bulan)
                    ]);
                }
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return [
                'status' => false,
                'message' => 'Gagal menyimpan rencana asumsi pemasukan'
            ];
        }

        $this->db->trans_commit();
        return [
            'status' => true,
            'message' => 'Rencana asumsi pemasukan berhasil disimpan'
        ];
    }

    public function hapus()
    {
        $id_rencana_pemasukan = $this->input->post('id');

        if (!$id_rencana_pemasukan) {
            return [
                'status' => false,
                'message' => 'ID rencana pemasukan tidak ditemukan'
            ];
        }

        $this->db->trans_begin();

        $asumsi = $this->db->get_where('rencana_asumsi_pemasukan', [
            'id_rencana_pemasukan' => $id_rencana_pemasukan
        ])->result_array();

        $ids_asumsi = array_column($asumsi, 'id');

        if (!empty($ids_asumsi)) {
            $this->db->where_in('id_rencana_asumsi_pemasukan', $ids_asumsi);
            $this->db->delete('rencana_asumsi_pemasukan_detail');
        }

        $this->db->where('id_rencana_pemasukan', $id_rencana_pemasukan);
        $this->db->delete('rencana_asumsi_pemasukan');

        $this->db->select('id');
        $this->db->from('rencana_pemasukan_jenis');
        $this->db->where('id_rencana_pemasukan', $id_rencana_pemasukan);
        $jenis = $this->db->get()->result_array();

        $ids_jenis = array_column($jenis, 'id');

        if (!empty($ids_jenis)) {
            $this->db->where_in('id_jenis', $ids_jenis);
            $this->db->delete('rencana_pemasukan_detail');
        }

        $this->db->where('id_rencana_pemasukan', $id_rencana_pemasukan);
        $this->db->delete('rencana_pemasukan_jenis');

        $this->db->where('id', $id_rencana_pemasukan);
        $this->db->delete('rencana_pemasukan');
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'message' => 'Gagal menghapus data'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Data berhasil dihapus'
        ];
    }
}
?>