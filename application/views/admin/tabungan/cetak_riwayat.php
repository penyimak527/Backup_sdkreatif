<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Tabungan Siswa</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; color: #111; font-size: 10px; background: #fff; }
        .report-title { text-align: center; font-size: 15px; font-weight: 700; margin: 0 0 14px; }
        .meta { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .meta td { border: 0; vertical-align: top; }
        .filter-info { width: auto; border-collapse: collapse; }
        .filter-info td { border: 0; padding: 1px 4px 1px 0; line-height: 1.35; }
        .filter-info .label { width: 125px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .filter-info .separator { width: 10px; }
        .print-info { text-align: right; line-height: 1.5; white-space: nowrap; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: auto; margin-top: 5px; }
        .report-table th, .report-table td { border: 1px solid #222; padding: 4px 5px; vertical-align: middle; }
        .report-table th { font-size: 9px; font-weight: 700; text-align: center; background: #f4f4f4; }
        .report-table td { font-size: 9px; }
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }
        .empty { text-align: center; padding: 12px !important; }
        .cancelled { color: #b4232d; }
        .summary { width: 330px; border-collapse: collapse; margin: 10px 0 0 auto; }
        .summary td { border: 0; padding: 2px 0 2px 8px; }
        .summary td:first-child { font-weight: 700; }
        .summary td:nth-child(2) { width: 10px; text-align: center; }
        .no-print { text-align: center; margin: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="report-title">RIWAYAT TABUNGAN SISWA</div>
    <table class="meta">
        <tr>
            <td>
                <table class="filter-info">
                    <tr>
                        <td class="label">Periode</td>
                        <td class="separator">:</td>
                        <td><?= html_escape($periode_cetak) ?></td>
                    </tr>
                </table>
            </td>
            <td class="print-info">
                Dicetak: <?= html_escape($tanggal_cetak) ?><br>
                Petugas: <?= html_escape($petugas) ?>
            </td>
        </tr>
    </table>
    <table class="report-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Metode</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Catatan/Alasan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows): ?>
                <?php foreach ($rows as $i => $r): ?>
                    <?php $cancelled = ($r['status_transaksi'] ?? 'Aktif') === 'Dibatalkan'; ?>
                    <tr class="<?= $cancelled ? 'cancelled' : '' ?>">
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= html_escape($r['jenis']) ?></td>
                        <td><?= html_escape($r['no_transaksi']) ?></td>
                        <td><?= html_escape(date('d-m-Y', strtotime(str_replace(' ', '', $r['tanggal_transaksi'])))) ?></td>
                        <td><?= html_escape($r['nama_siswa']) ?></td>
                        <td><?= html_escape($r['nama_kelas']) ?></td>
                        <td><?= html_escape($r['metode']) ?></td>
                        <td class="text-end">Rp<?= number_format((float)$r['nominal'], 0, ',', '.') ?></td>
                        <td><?= html_escape($r['status_transaksi']) ?></td>
                        <td><?= html_escape($cancelled ? ($r['alasan_batal'] ?: '-') : ($r['catatan'] ?: '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="12" class="empty">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <table class="summary">
        <tr><td>Total Setoran Aktif</td><td>:</td><td class="text-end">Rp<?= number_format((float)$summary['setoran'], 0, ',', '.') ?></td></tr>
        <tr><td>Total Penarikan Aktif</td><td>:</td><td class="text-end">Rp<?= number_format((float)$summary['penarikan'], 0, ',', '.') ?></td></tr>
        <tr><td>Jumlah Transaksi</td><td>:</td><td class="text-end"><?= (int)$summary['jumlah'] ?></td></tr>
        <tr><td>Dibatalkan</td><td>:</td><td class="text-end"><?= (int)($summary['dibatalkan'] ?? 0) ?></td></tr>
    </table>
    <div class="no-print"><button onclick="window.print()">Cetak</button></div>
</body>
</html>
