<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_laporan_dompet extends CI_Model
{
    private $types = [
        'saldo',
        'isi_saldo',
        'transaksi_kantin',
        'pembatalan',
        'rekap_siswa',
        'rekap_kelas',
        'rekap_periode',
        'biaya_layanan',
        'rekonsiliasi',
    ];

    public function types()
    {
        return $this->types;
    }

    public function report($type, $filter, $page = 1, $limit = 50)
    {
        if (!in_array($type, $this->types, true)) {
            return null;
        }

        $query = $this->build($type, $filter);

        $count_sql = 'SELECT COUNT(*) total
                      FROM ('.$query['sql'].') report_count';

        $count = $this->db
            ->query($count_sql, $query['params'])
            ->row_array();

        $totals_sql = 'SELECT
                           COALESCE(SUM(nilai_1), 0) nilai_1,
                           COALESCE(SUM(nilai_2), 0) nilai_2,
                           COALESCE(SUM(nilai_3), 0) nilai_3
                       FROM ('.$query['sql'].') report_total';

        $totals = $this->db
            ->query($totals_sql, $query['params'])
            ->row_array();

        $offset = (int) (($page - 1) * $limit);
        $data_sql = $query['sql']
            .' ORDER BY '.$query['order']
            .' LIMIT ? OFFSET ?';

        $data_params = array_merge(
            $query['params'],
            [(int) $limit, $offset]
        );

        $rows = $this->db
            ->query($data_sql, $data_params)
            ->result_array();

        return [
            'rows' => $rows,
            'totals' => $totals,
            'columns' => $query['columns'],
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int) $count['total'],
                'pages' => max(1, (int) ceil($count['total'] / $limit)),
            ],
        ];
    }

    private function build($type, $f)
    {
        $params = [];
        $where = ['1=1'];

        $date = function ($column) use (&$where, &$params, $f) {
            if ($f['tanggal_awal'] !== '') {
                $where[] = $column.' >= ?';
                $params[] = $f['tanggal_awal'];
            }

            if ($f['tanggal_akhir'] !== '') {
                $where[] = $column.' <= ?';
                $params[] = $f['tanggal_akhir'];
            }
        };

        $search = function ($search_columns) use (&$where, &$params, $f) {
            if ($f['search'] === '') {
                return;
            }

            $parts = [];
            foreach ($search_columns as $column) {
                $parts[] = $column.' LIKE ?';
                $params[] = '%'.$f['search'].'%';
            }

            $where[] = '('.implode(' OR ', $parts).')';
        };

        $class = function ($column) use (&$where, &$params, $f) {
            if ($f['id_kelas_setting'] > 0) {
                $where[] = $column.' = ?';
                $params[] = $f['id_kelas_setting'];
            }
        };

        $status = function ($column) use (&$where, &$params, $f) {
            if ($f['status'] !== '') {
                $where[] = $column.' = ?';
                $params[] = $f['status'];
            }
        };

        $columns = [
            'label_1' => 'Referensi',
            'label_2' => 'Siswa',
            'label_3' => 'Keterangan',
            'nilai_1' => 'Nilai 1',
            'nilai_2' => 'Nilai 2',
            'nilai_3' => 'Nilai 3',
            'status' => 'Status',
            'tanggal' => 'Tanggal',
        ];

        if ($type === 'saldo') {
            $search(['sd.nis', 'sd.nama_siswa']);
            $class('k.id');
            $status('sd.status_dompet');

            $sql = "
                SELECT
                    sd.nis label_1,
                    sd.nama_siswa label_2,
                    COALESCE(k.nama_kelas, '-') label_3,
                    sd.saldo nilai_1,
                    COALESCE(
                        pds.batas_harian,
                        pd.batas_harian_default,
                        0
                    ) nilai_2,
                    COALESCE(
                        pds.batas_per_transaksi,
                        pd.batas_transaksi_default,
                        0
                    ) nilai_3,
                    sd.status_dompet status,
                    sd.tanggal_update tanggal
                FROM saldo_dompet_siswa sd
                LEFT JOIN kelas_siswa ks
                    ON ks.id = (
                        SELECT MAX(k2.id)
                        FROM kelas_siswa k2
                        JOIN kelas_setting st2
                            ON st2.id = k2.id_kelas_setting
                        JOIN master_tahun_ajaran ta2
                            ON ta2.id = st2.id_periode
                            AND ta2.status = 'Aktif'
                        WHERE k2.id_siswa = sd.id_siswa
                            AND k2.status_aktif IN ('1', 'AKTIF')
                    )
                LEFT JOIN kelas_setting k
                    ON k.id = ks.id_kelas_setting
                LEFT JOIN pengaturan_dompet_siswa pds
                    ON pds.id_siswa = sd.id_siswa
                LEFT JOIN (
                    SELECT *
                    FROM pengaturan_dompet
                    ORDER BY id DESC
                    LIMIT 1
                ) pd ON 1 = 1
                WHERE ".implode(' AND ', $where);

            $columns = [
                'label_1' => 'NIS',
                'label_2' => 'Nama Siswa',
                'label_3' => 'Kelas',
                'nilai_1' => 'Saldo',
                'nilai_2' => 'Limit Harian',
                'nilai_3' => 'Limit Transaksi',
                'status' => 'Status Wallet',
                'tanggal' => 'Update',
            ];
            $order = 'label_2 ASC';
        } elseif ($type === 'isi_saldo') {
            $date('r.tanggal');
            $search([
                'r.no_transaksi',
                'r.nis',
                'r.nama_siswa',
                'r.metode_pembayaran',
                'r.nama_kasbank',
            ]);
            $class('k.id');
            $status('r.status_transaksi');

            $sql = "
                SELECT
                    r.no_transaksi label_1,
                    r.nama_siswa label_2,
                    CONCAT(
                        CASE UPPER(TRIM(COALESCE(r.metode_pembayaran, '')))
                            WHEN 'TRANSFER' THEN 'Transfer'
                            WHEN 'CASH' THEN 'Cash'
                            ELSE '-'
                        END,
                        ' / ',
                        COALESCE(NULLIF(r.nama_kasbank, ''), '-')
                    ) label_3,
                    r.nominal_saldo nilai_1,
                    r.biaya_layanan nilai_2,
                    r.total_pembayaran nilai_3,
                    r.status_transaksi status,
                    r.tanggal tanggal
                FROM riwayat_isi_saldo r
                LEFT JOIN kelas_siswa ks
                    ON ks.id = (
                        SELECT MAX(k2.id)
                        FROM kelas_siswa k2
                        JOIN kelas_setting st2
                            ON st2.id = k2.id_kelas_setting
                        JOIN master_tahun_ajaran ta2
                            ON ta2.id = st2.id_periode
                            AND ta2.status = 'Aktif'
                        WHERE k2.id_siswa = r.id_siswa
                            AND k2.status_aktif IN ('1', 'AKTIF')
                    )
                LEFT JOIN kelas_setting k
                    ON k.id = ks.id_kelas_setting
                WHERE ".implode(' AND ', $where);

            $columns = [
                'label_1' => 'No. Transaksi',
                'label_2' => 'Siswa',
                'label_3' => 'Metode / Kas/Bank',
                'nilai_1' => 'Pokok',
                'nilai_2' => 'Fee',
                'nilai_3' => 'Total',
                'status' => 'Status',
                'tanggal' => 'Tanggal',
            ];
            $order = 'tanggal DESC, label_1 DESC';
        } elseif ($type === 'transaksi_kantin') {
            $date('t.tanggal');
            $search(['t.no_transaksi', 't.nis', 't.nama_siswa']);
            $class('t.id_kelas_setting');
            $status('t.status_transaksi');

            $sql = "
                SELECT
                    t.no_transaksi label_1,
                    t.nama_siswa label_2,
                    COALESCE(t.nama_kelas, '-') label_3,
                    t.nominal nilai_1,
                    COALESCE(t.saldo_sebelum, 0) nilai_2,
                    COALESCE(t.saldo_sesudah, 0) nilai_3,
                    t.status_transaksi status,
                    t.tanggal tanggal
                FROM transaksi_kantin t
                WHERE ".implode(' AND ', $where);

            $columns = [
                'label_1' => 'No. Transaksi',
                'label_2' => 'Siswa',
                'label_3' => 'Kelas',
                'nilai_1' => 'Nominal',
                'nilai_2' => 'Saldo Sebelum',
                'nilai_3' => 'Saldo Sesudah',
                'status' => 'Status',
                'tanggal' => 'Tanggal',
            ];
            $order = 'tanggal DESC, label_1 DESC';
        } elseif ($type === 'pembatalan') {
            $date('p.tanggal_pengajuan');
            $search(['p.no_pembatalan', 'p.no_transaksi']);
            $status('p.status_pembatalan');

            $sql = "
                SELECT
                    p.no_pembatalan label_1,
                    p.jenis_transaksi label_2,
                    COALESCE(p.no_transaksi, '-') label_3,
                    p.nominal_koreksi nilai_1,
                    COALESCE(p.saldo_sebelum_koreksi, 0) nilai_2,
                    COALESCE(p.saldo_sesudah_koreksi, 0) nilai_3,
                    p.status_pembatalan status,
                    p.tanggal_pengajuan tanggal
                FROM pembatalan_dompet p
                WHERE ".implode(' AND ', $where);

            $columns = [
                'label_1' => 'No. Pembatalan',
                'label_2' => 'Jenis',
                'label_3' => 'Transaksi Asal',
                'nilai_1' => 'Koreksi',
                'nilai_2' => 'Saldo Sebelum',
                'nilai_3' => 'Saldo Sesudah',
                'status' => 'Status',
                'tanggal' => 'Tanggal',
            ];
            $order = 'tanggal DESC, label_1 DESC';
        } elseif ($type === 'biaya_layanan') {
            $date('r.tanggal');
            $search(['r.no_transaksi', 'r.nis', 'r.nama_siswa']);
            $class('k.id');
            $status('r.status_transaksi');

            $sql = "
                SELECT
                    r.no_transaksi label_1,
                    r.nama_siswa label_2,
                    COALESCE(r.nama_kasbank, '-') label_3,
                    r.biaya_layanan nilai_1,
                    r.nominal_saldo nilai_2,
                    r.total_pembayaran nilai_3,
                    r.status_transaksi status,
                    r.tanggal tanggal
                FROM riwayat_isi_saldo r
                LEFT JOIN kelas_siswa ks
                    ON ks.id = (
                        SELECT MAX(k2.id)
                        FROM kelas_siswa k2
                        JOIN kelas_setting st2
                            ON st2.id = k2.id_kelas_setting
                        JOIN master_tahun_ajaran ta2
                            ON ta2.id = st2.id_periode
                            AND ta2.status = 'Aktif'
                        WHERE k2.id_siswa = r.id_siswa
                            AND k2.status_aktif IN ('1', 'AKTIF')
                    )
                LEFT JOIN kelas_setting k
                    ON k.id = ks.id_kelas_setting
                WHERE ".implode(' AND ', $where);

            $columns = [
                'label_1' => 'No. Transaksi',
                'label_2' => 'Siswa',
                'label_3' => 'Kas/Bank',
                'nilai_1' => 'Fee',
                'nilai_2' => 'Pokok',
                'nilai_3' => 'Total Transfer',
                'status' => 'Status',
                'tanggal' => 'Tanggal',
            ];
            $order = 'tanggal DESC, label_1 DESC';
        } elseif ($type === 'rekonsiliasi') {
            $date('r.tanggal');
            $search(['r.no_transaksi', 'r.nis', 'r.nama_siswa']);

            $sql = "
                SELECT
                    r.no_transaksi label_1,
                    r.nama_siswa label_2,
                    COALESCE(r.nama_kasbank, '-') label_3,
                    r.nominal_saldo nilai_1,
                    COALESCE(
                        CAST(pt.jumlah AS DECIMAL(18, 2)),
                        0
                    ) nilai_2,
                    COALESCE(
                        CAST(pf.jumlah AS DECIMAL(18, 2)),
                        0
                    ) nilai_3,
                    CASE
                        WHEN r.id_pemasukan_titipan IS NULL
                            AND r.id_pemasukan_biaya IS NULL
                            THEN 'HISTORI_MIGRASI'
                        WHEN COALESCE(
                                CAST(pt.jumlah AS DECIMAL(18, 2)),
                                0
                             ) = r.nominal_saldo
                            AND COALESCE(
                                CAST(pf.jumlah AS DECIMAL(18, 2)),
                                0
                            ) = r.biaya_layanan
                            THEN 'COCOK'
                        ELSE 'SELISIH'
                    END status,
                    r.tanggal tanggal
                FROM riwayat_isi_saldo r
                LEFT JOIN pemasukan pt
                    ON pt.id = r.id_pemasukan_titipan
                LEFT JOIN pemasukan pf
                    ON pf.id = r.id_pemasukan_biaya
                WHERE ".implode(' AND ', $where);

            $columns = [
                'label_1' => 'No. Transaksi',
                'label_2' => 'Siswa',
                'label_3' => 'Kas/Bank',
                'nilai_1' => 'Pokok Wallet',
                'nilai_2' => 'Posting Pokok',
                'nilai_3' => 'Posting Fee',
                'status' => 'Rekonsiliasi',
                'tanggal' => 'Tanggal',
            ];
            $order = 'tanggal DESC, label_1 DESC';
        } elseif ($type === 'rekap_siswa') {
            $date('x.tanggal');
            $search(['x.nis', 'x.nama_siswa']);

            $sql = "
                SELECT
                    x.nis label_1,
                    x.nama_siswa label_2,
                    '' label_3,
                    SUM(x.topup) nilai_1,
                    SUM(x.kantin) nilai_2,
                    SUM(x.topup - x.kantin) nilai_3,
                    'REKAP' status,
                    MAX(x.tanggal) tanggal
                FROM (
                    SELECT
                        nis,
                        nama_siswa,
                        nominal_saldo topup,
                        0 kantin,
                        tanggal
                    FROM riwayat_isi_saldo
                    WHERE status_transaksi = 'BERHASIL'

                    UNION ALL

                    SELECT
                        nis,
                        nama_siswa,
                        0 topup,
                        nominal kantin,
                        tanggal
                    FROM transaksi_kantin
                    WHERE status_transaksi = 'BERHASIL'
                ) x
                WHERE ".implode(' AND ', $where)."
                GROUP BY x.nis, x.nama_siswa";

            $columns = [
                'label_1' => 'NIS',
                'label_2' => 'Siswa',
                'label_3' => '-',
                'nilai_1' => 'Top-up',
                'nilai_2' => 'Kantin',
                'nilai_3' => 'Selisih',
                'status' => 'Jenis',
                'tanggal' => 'Transaksi Terakhir',
            ];
            $order = 'label_2 ASC';
        } elseif ($type === 'rekap_kelas') {
            $date('x.tanggal');
            $search(['x.nama_kelas']);

            if ($f['id_kelas_setting'] > 0) {
                $where[] = 'x.id_kelas_setting = ?';
                $params[] = $f['id_kelas_setting'];
            }

            $sql = "
                SELECT
                    COALESCE(x.nama_kelas, 'Tanpa Kelas') label_1,
                    '' label_2,
                    '' label_3,
                    SUM(x.topup) nilai_1,
                    SUM(x.kantin) nilai_2,
                    SUM(x.topup - x.kantin) nilai_3,
                    'REKAP' status,
                    MAX(x.tanggal) tanggal
                FROM (
                    SELECT
                        k.id id_kelas_setting,
                        k.nama_kelas,
                        r.nominal_saldo topup,
                        0 kantin,
                        r.tanggal
                    FROM riwayat_isi_saldo r
                    LEFT JOIN kelas_siswa ks
                        ON ks.id = (
                            SELECT MAX(k2.id)
                            FROM kelas_siswa k2
                            JOIN kelas_setting st2
                                ON st2.id = k2.id_kelas_setting
                            JOIN master_tahun_ajaran ta2
                                ON ta2.id = st2.id_periode
                                AND ta2.status = 'Aktif'
                            WHERE k2.id_siswa = r.id_siswa
                                AND k2.status_aktif IN ('1', 'AKTIF')
                        )
                    LEFT JOIN kelas_setting k
                        ON k.id = ks.id_kelas_setting
                    WHERE r.status_transaksi = 'BERHASIL'

                    UNION ALL

                    SELECT
                        id_kelas_setting,
                        nama_kelas,
                        0 topup,
                        nominal kantin,
                        tanggal
                    FROM transaksi_kantin
                    WHERE status_transaksi = 'BERHASIL'
                ) x
                WHERE ".implode(' AND ', $where)."
                GROUP BY x.id_kelas_setting, x.nama_kelas";

            $columns = [
                'label_1' => 'Kelas',
                'label_2' => '-',
                'label_3' => '-',
                'nilai_1' => 'Top-up',
                'nilai_2' => 'Kantin',
                'nilai_3' => 'Selisih',
                'status' => 'Jenis',
                'tanggal' => 'Transaksi Terakhir',
            ];
            $order = 'label_1 ASC';
        } else {
            $date('x.tanggal');

            $sql = "
                SELECT
                    LEFT(x.tanggal, 7) label_1,
                    '' label_2,
                    '' label_3,
                    SUM(x.topup) nilai_1,
                    SUM(x.kantin) nilai_2,
                    SUM(x.fee) nilai_3,
                    'REKAP' status,
                    MAX(x.tanggal) tanggal
                FROM (
                    SELECT
                        tanggal,
                        nominal_saldo topup,
                        0 kantin,
                        biaya_layanan fee
                    FROM riwayat_isi_saldo
                    WHERE status_transaksi = 'BERHASIL'

                    UNION ALL

                    SELECT
                        tanggal,
                        0 topup,
                        nominal kantin,
                        0 fee
                    FROM transaksi_kantin
                    WHERE status_transaksi = 'BERHASIL'
                ) x
                WHERE ".implode(' AND ', $where)."
                GROUP BY LEFT(x.tanggal, 7)";

            $columns = [
                'label_1' => 'Periode',
                'label_2' => '-',
                'label_3' => '-',
                'nilai_1' => 'Top-up',
                'nilai_2' => 'Kantin',
                'nilai_3' => 'Fee',
                'status' => 'Jenis',
                'tanggal' => 'Tanggal Terakhir',
            ];
            $order = 'label_1 DESC';
        }

        return [
            'sql' => $sql,
            'params' => $params,
            'columns' => $columns,
            'order' => $order,
        ];
    }
}
