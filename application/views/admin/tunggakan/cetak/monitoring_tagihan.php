<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= html_escape($title) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 20px;
        }
        .toolbar {
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
        }
        .toolbar button {
            border: 1px solid #999;
            background: #fff;
            padding: 7px 12px;
            cursor: pointer;
            border-radius: 4px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .filter-table,
        .summary {
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .filter-table td,
        .summary td {
            padding: 2px 8px 2px 0;
            vertical-align: top;
        }
        .filter-table td:first-child,
        .summary td:first-child {
            font-weight: bold;
            min-width: 125px;
        }
        .data-table {
            border-collapse: collapse;
            width: 100%;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #333;
            padding: 5px 6px;
        }
        .data-table th {
            background: #f2f2f2;
            text-align: center;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 14px; }
        .footer-info { margin-top: 10px; font-size: 10px; }
        @page { size: A4 landscape; margin: 8mm; }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Cetak</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>

    <div class="title">MONITORING TAGIHAN</div>

    <table class="filter-table">
        <tr><td>Siswa</td><td>: <?= html_escape($filter['siswa']) ?></td></tr>
        <tr><td>Tahun Ajaran</td><td>: <?= html_escape($filter['tahun_ajaran']) ?></td></tr>
        <tr><td>Kelas</td><td>: <?= html_escape($filter['kelas']) ?></td></tr>
        <tr><td>Jenis Tagihan</td><td>: <?= html_escape($filter['jenis_tagihan']) ?></td></tr>
        <tr><td>Batch/Periode</td><td>: <?= html_escape($filter['batch_periode']) ?></td></tr>
        <tr><td>Tipe Tagihan</td><td>: <?= html_escape($filter['tipe']) ?></td></tr>
        <tr><td>Status</td><td>: <?= html_escape($filter['status']) ?></td></tr>
        <tr><td>Sampai Bulan</td><td>: <?= html_escape($filter['sampai_bulan']) ?></td></tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Jenis</th>
                <th>Tagihan</th>
                <th>Periode</th>
                <th>Wajib</th>
                <th>Tarif Akhir</th>
                <th>Dibayar</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $index => $row): ?>
                    <?php
                    $periodeTagihan = trim((!empty($row['nama_bulan']) ? $row['nama_bulan'] . ' ' : '') . (!empty($row['tahun']) ? $row['tahun'] : ''));
                    if ($periodeTagihan === '') {
                        $periodeTagihan = !empty($row['periode']) ? $row['periode'] : '-';
                    }
                    ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td>
                            <strong><?= html_escape($row['nama_siswa']) ?></strong><br>
                            <span style="font-size:10px;"><?= html_escape($row['nis']) ?></span>
                        </td>
                        <td><?= html_escape($row['nama_kelas']) ?></td>
                        <td><?= html_escape($row['nama_jenis_tagihan']) ?></td>
                        <td><?= html_escape($row['nama_tagihan']) ?></td>
                        <td><?= html_escape($periodeTagihan) ?></td>
                        <td class="text-center"><?= $row['dianggap_tunggakan'] === 'Ya' ? 'Ya' : 'Tidak' ?></td>
                        <td class="text-end">Rp<?= number_format((float) $row['nominal_tagihan'], 0, ',', '.') ?></td>
                        <td class="text-end">Rp<?= number_format((float) $row['nominal_dibayar'], 0, ',', '.') ?></td>
                        <td class="text-end">Rp<?= number_format((float) $row['sisa_tagihan'], 0, ',', '.') ?></td>
                        <td><?= html_escape($row['status_pembayaran']) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Total Tagihan</td>
            <td>: Rp<?= number_format((float) (isset($summary['tagihan']) ? $summary['tagihan'] : 0), 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Total Dibayar</td>
            <td>: Rp<?= number_format((float) (isset($summary['dibayar']) ? $summary['dibayar'] : 0), 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Total Sisa</td>
            <td>: Rp<?= number_format((float) (isset($summary['sisa']) ? $summary['sisa'] : 0), 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Realisasi</td>
            <td>: <?= number_format((float) (isset($summary['realisasi']) ? $summary['realisasi'] : 0), 2, ',', '.') ?>%</td>
        </tr>
    </table>

    <div class="footer-info">Dicetak: <?= html_escape($tanggal_cetak) ?></div>
</body>
</html>
