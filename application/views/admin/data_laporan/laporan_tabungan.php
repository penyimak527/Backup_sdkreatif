<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tabungan</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; color: #000; font-size: 11px; }
        h3 { margin: 4px 0 24px; text-align: center; font-size: 17px; }
        .header { width: 100%; margin-bottom: 17px; border-collapse: collapse; }
        .header td { vertical-align: top; font-size: 11px; }
        .meta { border-collapse: collapse; }
        .meta td { padding: 2px 5px 2px 0; }
        .meta-label { font-weight: bold; text-transform: uppercase; white-space: nowrap; min-width: 110px; }
        .printer { text-align: right; white-space: nowrap; line-height: 1.5; }
        .grid { width: 100%; border-collapse: collapse; table-layout: auto; }
        .grid th, .grid td { border: 1px solid #222; padding: 5px; font-size: 10px; }
        .grid th { background: #f3f3f3; text-align: center; }
        .grid td { vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .ringkasan { width: 36%; margin: 13px 0 0 auto; border-collapse: collapse; font-size: 11px; }
        .ringkasan td { padding: 3px 0; }
        .ringkasan td:first-child { font-weight: bold; }
        .ringkasan td:last-child { text-align: right; white-space: nowrap; }
        @media print { .grid thead { display: table-header-group; } .grid tr { break-inside: avoid; page-break-inside: avoid; } }
    </style>
</head>
<body>
    <h3>LAPORAN TABUNGAN</h3>
    <table class="header">
        <tr>
            <td>
                <table class="meta">
                    <?php foreach ($filters as $label => $value): ?>
                        <tr>
                            <td class="meta-label"><?= html_escape($label); ?></td>
                            <td>:</td>
                            <td><?= html_escape((string) $value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
            <td class="printer">
                Dicetak: <?= date('d-m-Y H:i'); ?><br>
                Petugas: <?= html_escape($petugas ?? '-'); ?>
            </td>
        </tr>
    </table>
    <table class="grid">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Transaksi</th>
                <th>Jenis Transaksi</th>
                <th>Siswa</th>
                <!-- <th>NIS</th> -->
                <th>Kelas</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Petugas</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_laporan)): ?>
                <?php foreach ($data_laporan as $index => $row): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1; ?></td>
                        <td><?= html_escape($row['tanggal_transaksi']); ?></td>
                        <td><?= html_escape($row['no_transaksi']); ?></td>
                        <td><?= html_escape($row['jenis']); ?></td>
                        <td><?= html_escape($row['nama_siswa']); ?></td>
                        <!-- <td><?= html_escape($row['nis']); ?></td> -->
                        <td><?= html_escape($row['nama_kelas']); ?></td>
                        <td><?= html_escape($row['metode_pembayaran']); ?></td>
                        <td><?= html_escape($row['status_transaksi']); ?></td>
                        <td><?= html_escape($row['nama_user']); ?></td>
                        <td class="text-right">Rp<?= number_format((float) $row['nominal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="11" class="text-center">Tidak ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <table class="ringkasan">
        <tr><td>TOTAL SETORAN AKTIF</td><td>:</td><td>Rp<?= number_format((float) $total_setoran, 0, ',', '.'); ?></td></tr>
        <tr><td>TOTAL PENARIKAN AKTIF</td><td>:</td><td>Rp<?= number_format((float) $total_penarikan, 0, ',', '.'); ?></td></tr>
        <!-- <tr><td>SALDO BERSIH</td><td>:</td><td>Rp<?= number_format((float) $saldo, 0, ',', '.'); ?></td></tr> -->
    </table>
    <script>window.print();</script>
</body>
</html>
