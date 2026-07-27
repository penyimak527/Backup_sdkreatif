<?php
class Laporan_rekap_gaji extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        header('Access-Control-Allow-Origin: *');
    }

    public function print_laporan()
    {
        $json = file_get_contents('php://input');
        $ambil = json_decode($json, true);

        $bulan = $ambil['filter_bulan'];
        $tahun = $ambil['filter_tahun'];

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
                $potongan_detail = $this->db->query("SELECT mp.id, mp.nama_potongan, pp.nominal
                    FROM penggajian_potongan pp
                    JOIN master_potongan mp ON mp.id = pp.id_master_potongan
                    WHERE pp.id_penggajian = ?", [$cek_penggajian['id']])->result_array();

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
        $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $tanggal_laporan = $tanggal_terakhir . ' ' . $this->getBulan($bulan) . ' ' . $tahun;
        $data = [
            'judul' => $this->getBulan($bulan) . " " . $tahun,
            'status' => 'Bulan',
            'data_laporan' => $result,
            'tanggal_laporan' => $tanggal_laporan,
            'master_potongan' => $master_potongan,
        ];

        $this->load->view('admin/data_laporan/laporan_rekapitulasi_gaji', $data);
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
