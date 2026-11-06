<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title) ?> - Laporan Dompet Digital</title>
    <style>
        :root {
            --primary: #174d84;
            --primary-dark: #123d66;
            --soft-blue: #eaf2fb;
            --border: #d8e3ef;
            --muted: #62788f;
            --paper: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef3f8;
            color: #183b5b;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--border);
        }

        .toolbar button {
            padding: 9px 16px;
            border: 0;
            border-radius: 7px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
        }

        .paper {
            width: min(1400px, calc(100% - 32px));
            margin: 24px auto;
            padding: 28px;
            background: var(--paper);
            border-radius: 10px;
            box-shadow: 0 8px 28px rgba(18, 61, 102, 0.12);
        }

        .report-header {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-bottom: 18px;
            border-bottom: 3px solid var(--primary);
        }

        .report-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .report-heading {
            flex: 1;
        }

        .report-heading h1 {
            margin: 0 0 5px;
            color: var(--primary-dark);
            font-size: 22px;
        }

        .report-heading h2 {
            margin: 0;
            color: var(--primary);
            font-size: 15px;
            font-weight: 600;
        }

        .report-meta {
            min-width: 210px;
            color: var(--muted);
            line-height: 1.7;
            text-align: right;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin: 15px 0;
        }

        .filter-chip {
            padding: 6px 10px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--soft-blue);
            color: var(--primary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 8px 7px;
            border: 1px solid var(--border);
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        th {
            background: var(--primary);
            color: #fff;
            font-size: 10px;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f7fafd;
        }

        td.number {
            text-align: right;
            white-space: nowrap;
        }

        td.center {
            text-align: center;
        }

        tfoot td {
            background: var(--soft-blue);
            color: var(--primary-dark);
            font-weight: 700;
        }

        .empty {
            padding: 28px;
            color: var(--muted);
            text-align: center;
        }

        .footer-note {
            margin-top: 12px;
            color: var(--muted);
            font-size: 9px;
            text-align: right;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        @media print {
            body {
                background: #fff;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .toolbar {
                display: none;
            }

            .paper {
                width: 100%;
                margin: 0;
                padding: 0;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak Laporan</button>
    </div>

    <main class="paper">
        <header class="report-header">
            <img
                class="report-logo"
                src="<?= base_url('assets/sdkreative.png') ?>"
                alt="Logo SD Kreatif"
            >
            <div class="report-heading">
                <h1><?= html_escape($title) ?></h1>
                <h2>Dompet Digital SD Kreatif Muhammadiyah 1 Lumajang</h2>
            </div>
            <div class="report-meta">
                <div>Dicetak: <?= html_escape($printed_at) ?></div>
                <div>Oleh: <?= html_escape($printed_by) ?></div>
                <div>Jumlah data: <?= count($rows) ?></div>
            </div>
        </header>

        <section class="filters" aria-label="Filter laporan">
            <?php foreach ($filter_summary as $filter): ?>
                <span class="filter-chip"><?= html_escape($filter) ?></span>
            <?php endforeach; ?>
        </section>

        <table>
            <colgroup>
                <col style="width: 4%">
                <col style="width: 15%">
                <col style="width: 18%">
                <col style="width: 13%">
                <col style="width: 12%">
                <col style="width: 12%">
                <col style="width: 12%">
                <col style="width: 8%">
                <col style="width: 12%">
            </colgroup>
            <thead>
                <tr>
                    <th>No.</th>
                    <?php foreach ($columns as $label): ?>
                        <th><?= html_escape($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="9" class="empty">Tidak ada data sesuai filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $index => $row): ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= html_escape($row['label_1'] ?? '-') ?></td>
                            <td><?= html_escape($row['label_2'] ?? '-') ?></td>
                            <td><?= html_escape($row['label_3'] ?? '-') ?></td>
                            <td class="number">
                                Rp <?= number_format((float) ($row['nilai_1'] ?? 0), 0, ',', '.') ?>
                            </td>
                            <td class="number">
                                Rp <?= number_format((float) ($row['nilai_2'] ?? 0), 0, ',', '.') ?>
                            </td>
                            <td class="number">
                                Rp <?= number_format((float) ($row['nilai_3'] ?? 0), 0, ',', '.') ?>
                            </td>
                            <td class="center">
                                <?= html_escape(ucwords(strtolower(str_replace('_', ' ', $row['status'] ?? '-')))) ?>
                            </td>
                            <td class="center"><?= html_escape($row['tanggal'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="center">TOTAL</td>
                    <td class="number">
                        Rp <?= number_format((float) ($totals['nilai_1'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td class="number">
                        Rp <?= number_format((float) ($totals['nilai_2'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td class="number">
                        Rp <?= number_format((float) ($totals['nilai_3'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td colspan="2" class="center"><?= count($rows) ?> data</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-note">
            Laporan dibuat otomatis oleh Sistem Dompet Digital SD Kreatif.
        </div>
    </main>
</body>
</html>
