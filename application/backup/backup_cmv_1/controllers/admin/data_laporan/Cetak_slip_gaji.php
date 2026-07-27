<?php
class Cetak_slip_gaji extends CI_Controller
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
        $id_pegawai = $ambil['id_pegawai_all'];

        $this->db->select('a.id as id_pegawai, a.nama_pegawai, b.id as id_gaji, b.gaji_pokok, 
		b.struktural, b.tunjangan_pendidikan, b.wali_kelas');
        $this->db->from('pegawai a');
        $this->db->join('gaji b', 'a.id = b.id_pegawai', 'left');
        // $this->db->join('potongan_pegawai c', 'a.id = c.id_pegawai', 'left');
        if ($id_pegawai) {
            $this->db->where('a.id', $id_pegawai);
        }
        $pegawai = $this->db->get()->result_array();

        $result = [];
        foreach ($pegawai as $item) {
            $cek_penggajian = $this->db->get_where('penggajian', [
                'id_pegawai' => $item['id_pegawai'],
                'bulan' => $bulan,
                'tahun' => $tahun
            ])->row_array();
            $pinjaman = $this->db->query("SELECT pj.sisa_pinjaman, pd.nominal_tagihan, pd.status_bayar 
           FROM pinjaman pj LEFT JOIN pinjaman_detail pd ON pj.id = pd.id_pinjaman WHERE pj.id_pegawai=? 
           AND pd.bulan=? AND pd.tahun=?", [$item['id_pegawai'], $bulan, $tahun])->row_array();


            if ($cek_penggajian) {
                $potongan_detail = $this->db->query("SELECT mp.nama_potongan, pp.nominal
                    FROM penggajian_potongan pp
                    JOIN master_potongan mp ON mp.id = pp.id_master_potongan
                    WHERE pp.id_penggajian = ? ", [$cek_penggajian['id']])->result_array();
                $result[] = [
                    'id_pegawai' => $item['id_pegawai'],
                    'nama_pegawai' => $item['nama_pegawai'],
                    'bulan' => $cek_penggajian['bulan'],
                    'tahun' => $cek_penggajian['tahun'],
                    'gaji_pokok' => $cek_penggajian['gaji_pokok'],

                    'struktural' => $cek_penggajian['struktural'],
                    'tunjangan_pendidikan' => $cek_penggajian['tunjangan_pendidikan'],
                    'wali_kelas' => $cek_penggajian['wali_kelas'],

                    'bonus' => $cek_penggajian['total_bonus'],
                    'total_pendapatan' => $cek_penggajian['total_pendapatan'],
                    'jumlah_hadir' => $cek_penggajian['jumlah_hadir'],
                    'jumlah_tidak_hadir' => $cek_penggajian['jumlah_tidak_hadir'],
                    'potongan_tidak_hadir' => $cek_penggajian['potongan_tidak_hadir'],
                    'uig_uik' => $cek_penggajian['uig_uik'],
                    'zakat' => $cek_penggajian['zakat'],
                    'potongan_detail' => $potongan_detail ?? [],
                    'cicilan_pinjaman' => $cek_penggajian['cicilan_pinjaman'],
                    'total_pengeluaran' => $cek_penggajian['total_pengeluaran'],
                    'gaji_bersih' => $cek_penggajian['gaji_bersih'],
                    'sisa_pinjaman' => $pinjaman['sisa_pinjaman'] ?? 0,

                    'persen_potongan_tidak_hadir' => $cek_penggajian['persen_potongan_tidak_hadir'],
                    'persen_uig_uik' => $cek_penggajian['persen_uig_uik'],
                    'persen_zakat' => $cek_penggajian['persen_zakat'],
                ];
            }
        }

        $data = [
            'judul' => $this->getBulan($bulan) . " " . $tahun,
            'title' => 'Cetak Slip Gaji',
            'status' => 'Bulan',
            'slip_gaji' => $result
        ];

        $this->load->view('admin/data_laporan/cetak_slip_gaji', $data);
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