<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title) ?></title>
    <style>
        @page { size: A4 <?= $orientation === 'landscape' ? 'landscape' : 'portrait' ?>; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; color: #111; font-size: 10px; background: #fff; }
        .report-title { text-align: center; font-size: 15px; font-weight: 700; text-transform: uppercase; margin: 0 0 14px; }
        .meta { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .meta td { border: 0; vertical-align: top; }
        .filter-info { width: auto; border-collapse: collapse; }
        .filter-info td { border: 0; padding: 1px 4px 1px 0; line-height: 1.35; }
        .filter-info .label { width: 125px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .filter-info .separator { width: 10px; }
        .print-info { text-align: right; line-height: 1.5; white-space: nowrap; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: auto; margin-top: 5px; }
        .report-table th,.report-table td { border: 1px solid #222; padding: 4px 5px; vertical-align: middle; }
        .report-table th { font-size: 9px; font-weight: 700; text-align: center; background: #f4f4f4; }
        .report-table td { font-size: 9px; }
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }
        .empty { text-align: center; padding: 12px !important; }
        .summary { width: 330px; border-collapse: collapse; margin: 10px 0 0 auto; }
        .summary td { border: 0; padding: 2px 0 2px 8px; }
        .summary td:first-child { font-weight: 700; }
        .summary td:nth-child(2) { width: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="report-title"><?= html_escape($title) ?></div>

    <table class="meta">
        <tr>
            <td>
                <?php if (!empty($filter_laporan)): ?>
                    <table class="filter-info">
                        <?php foreach ($filter_laporan as $label => $value): ?>
                            <tr>
                                <td class="label"><?= html_escape($label) ?></td>
                                <td class="separator">:</td>
                                <td><?= html_escape($value === '' ? '-' : $value) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </td>
            <td class="print-info">
                Dicetak: <?= date('d-m-Y H:i') ?><br>
                Petugas: <?= html_escape($petugas) ?>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width:34px;">No</th>
                <?php foreach ($laporan['columns'] as $key => $label): ?>
                    <th class="<?= in_array($key, $laporan['money'], true) ? 'text-end' : '' ?>"><?= html_escape($label) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($laporan['rows'])): ?>
                <tr><td colspan="<?= count($laporan['columns']) + 1 ?>" class="empty">Tidak ada data sesuai filter.</td></tr>
            <?php else: ?>
                <?php foreach ($laporan['rows'] as $index => $row): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <?php foreach ($laporan['columns'] as $key => $label): ?>
                            <?php
                            $value = isset($row[$key]) ? $row[$key] : '';
                            $isMoney = in_array($key, $laporan['money'], true);
                            $isPercent = preg_match('/realisasi|\(%\)/i', $label);
                            ?>
                            <td class="<?= $isMoney || $isPercent ? 'text-end' : '' ?>">
                                <?php if ($isMoney): ?>
                                    Rp<?= number_format((float)$value, 0, ',', '.') ?>
                                <?php elseif ($isPercent): ?>
                                    <?= number_format((float)$value, 2, ',', '.') ?>%
                                <?php else: ?>
                                    <?= html_escape($value) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($laporan['summary'])): ?>
        <table class="summary">
            <?php foreach ($laporan['summary'] as $label => $value): ?>
                <?php
                $isMoney = preg_match('/target|pembayaran|sisa|tunggakan|dibatalkan|nominal|tagihan|dibayar|total bayar/i', $label);
                $isPercent = preg_match('/realisasi|\(%\)/i', $label);
                ?>
                <tr>
                    <td><?= html_escape($label) ?></td><td>:</td>
                    <td class="text-end">
                        <?php if ($isMoney): ?>
                            Rp<?= number_format((float)$value, 0, ',', '.') ?>
                        <?php elseif ($isPercent): ?>
                            <?= number_format((float)$value, 2, ',', '.') ?>%
                        <?php else: ?>
                            <?= html_escape($value) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <script>
            window.print();
    </script>
</body>
</html>
