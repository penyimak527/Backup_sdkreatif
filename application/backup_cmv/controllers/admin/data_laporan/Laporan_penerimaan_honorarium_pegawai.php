<?php
class Laporan_penerimaan_honorarium_pegawai extends CI_Controller
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

    $bulan = str_pad((int) ($ambil['filter_bulan'] ?? 0), 2, '0', STR_PAD_LEFT);
    $tahun = (int) ($ambil['filter_tahun'] ?? 0);

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
        $masa_kerja = $this->hitung_masa_kerja($item['tmt'] ?? null, $bulan, $tahun);
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
        'judul' => $this->getBulan($bulan) . ' ' . $tahun,
        'status' => 'Bulan',
        'data_laporan' => $result,
        'tanggal_laporan' => $tanggal_laporan,
        'persen_tidak_hadir' => $persen_tidak_hadir,
    ];

    $this->load->view('admin/data_laporan/laporan_penerimaan_honorarium_pegawai', $data);
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

  private function hitung_masa_kerja($tmt, $bulan = null, $tahun = null)
{
    if (empty($tmt) || $tmt == '0000-00-00') {
        return 0;
    }
    $tgl_tmt = DateTime::createFromFormat('d-m-Y', $tmt);

    if (!$tgl_tmt) {
        return 0;
    }

    if (!empty($bulan) && !empty($tahun)) {
        $tanggal_terakhir = date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $tgl_acuan = DateTime::createFromFormat('Y-m-d', $tahun . '-' . $bulan . '-' . $tanggal_terakhir);
    } else {
        $tgl_acuan = new DateTime(date('Y-m-d'));
    }
    if (!$tgl_acuan) {
        return 0;
    }
    if ($tgl_tmt > $tgl_acuan) {
        return 0;
    }
    return $tgl_tmt->diff($tgl_acuan)->y;
}
}
