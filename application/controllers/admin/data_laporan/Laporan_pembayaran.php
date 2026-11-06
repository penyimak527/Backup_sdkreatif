<?php
class Laporan_pembayaran extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
    }

    public function print_laporan()
    {
        $json = file_get_contents('php://input');
        $ambil = json_decode($json, true);
        $ambil = is_array($ambil) ? $ambil : array();
        $filter = $ambil['filter'] ?? 'tanggal';
        $id_periode = max(0, (int) ($ambil['spp_id_periode_pembayaran'] ?? 0));
        $id_kelas = max(0, (int) ($ambil['spp_id_kelas_setting'] ?? 0));
        $id_jenis = max(0, (int) ($ambil['spp_id_jenis_tagihan'] ?? 0));
        $id_metode = max(0, (int) ($ambil['spp_id_metode'] ?? 0));
        $id_kasbank = max(0, (int) ($ambil['spp_id_kasbank'] ?? 0));
        $status_pembayaran = $ambil['spp_status_pembayaran'] ?? 'Aktif';
        if (!in_array($status_pembayaran, array('Aktif', 'Dibatalkan', 'Semua'), true)) {
            $status_pembayaran = 'Aktif';
        }

        // Tanggal, Bulan, Tahun mengikuti percabangan laporan_presensi_pegawai.
        // Batas selalu berdasarkan tanggal pembayaran, bukan bulan tagihan.
        $error = '';
        if ($filter === 'tanggal') {
            $dari = trim((string) ($ambil['dari_tanggal'] ?? ''));
            $sampai = trim((string) ($ambil['sampai_tanggal'] ?? ''));
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
            $bulan = (int) ($ambil['filter_bulan'] ?? $ambil['bulan'] ?? 0);
            $tahun = (int) ($ambil['filter_tahun'] ?? $ambil['tahun'] ?? 0);
            if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
                $error = 'Bulan atau tahun tidak valid.';
            } else {
                $mulai = sprintf('%04d-%02d-01', $tahun, $bulan);
                $selesai = date('Y-m-t', strtotime($mulai));
                $nama_bulan = array(1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                    10 => 'Oktober', 11 => 'November', 12 => 'Desember');
                $judul = $nama_bulan[$bulan] . ' ' . $tahun;
                $status_filter = 'Bulan';
            }
        } else if ($filter === 'tahun') {
            $tahun = (int) ($ambil['single_filter_tahun'] ?? $ambil['tahun'] ?? 0);
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
            if ($id_periode > 0) { $sql .= ' AND ts.id_periode=?'; $params[] = $id_periode; }
            if ($id_kelas > 0) { $sql .= ' AND ts.id_kelas_setting=?'; $params[] = $id_kelas; }
            if ($id_jenis > 0) { $sql .= ' AND pd.id_jenis_tagihan=?'; $params[] = $id_jenis; }
            if ($id_metode > 0) { $sql .= ' AND tp.id_metode_pembayaran=?'; $params[] = $id_metode; }
            if ($id_kasbank > 0) { $sql .= ' AND tp.id_kasbank_penerima=?'; $params[] = $id_kasbank; }
            if ($status_pembayaran !== 'Semua') { $sql .= ' AND tp.status_transaksi=?'; $params[] = $status_pembayaran; }
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
        if ($status_pembayaran !== 'Dibatalkan') $summary['Total Pembayaran'] = $total_pembayaran;
        if ($status_pembayaran !== 'Aktif') $summary['Total Dibatalkan'] = $total_dibatalkan;

        // Hanya metadata yang ditampilkan pada cetak; filter lain tetap dipakai pada query.
        $filters = array('Periode' => $error === '' ? $judul : '-');
        if (!$id_periode) {
            $filters['Tahun Ajaran'] = 'Semua Tahun Ajaran';
        } else {
            $tahun_ajaran = $this->db->select('periode')->where('id', $id_periode)->get('master_tahun_ajaran')->row_array();
            $filters['Tahun Ajaran'] = $tahun_ajaran['periode'] ?? '-';
        }
        // Respons untuk view cetak mengikuti tabel laporan pembayaran existing.
        $rows = array();
        foreach ($grouped_by_tanggal as $transaksi_list) {
            foreach ($transaksi_list as $item) {
                $rows[] = $item;
            }
        }
        $kolom = array(
            'tanggal_transaksi' => 'Tanggal',
            'waktu_transaksi' => 'Waktu',
            'no_transaksi' => 'No Transaksi',
            'nama_siswa' => 'Siswa',
            'nama_kelas' => 'Kelas',
            'jenis_tagihan' => 'Jenis Tagihan',
            'nama_metode_pembayaran' => 'Metode',
            'nama_user' => 'Petugas'
        );
        if ($status_pembayaran !== 'Dibatalkan') $kolom['total_pembayaran'] = 'Total Pembayaran';
        if ($status_pembayaran !== 'Aktif') $kolom['nominal_dibatalkan'] = 'Nominal Dibatalkan';

        $admin = $this->session->userdata('admin');
        $admin = is_array($admin) ? $admin : array();
        $data = array(
            'title' => 'Laporan Pembayaran',
            'laporan' => array(
                'columns' => $kolom,
                'money' => array('total_pembayaran', 'nominal_dibatalkan'),
                'rows' => $rows,
                'summary' => $summary,
                'filters' => $filters
            ),
            'filter_laporan' => $filters,
            'orientation' => 'landscape',
            'petugas' => $admin['nama'] ?? ($admin['nama_user'] ?? '-')
        );
        $this->load->view('admin/data_laporan/laporan_pembayaran', $data);
    }
}
