<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Bukti <?= html_escape($row['jenis']) ?> Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 0;
            padding: 24px
        }

        .wrap {
            max-width: 700px;
            margin: auto
        }

        .head {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 16px
        }

        .title {
            font-size: 20px;
            font-weight: 700
        }

        .meta {
            width: 100%;
            border-collapse: collapse
        }

        .meta td {
            padding: 5px 4px;
            vertical-align: top
        }

        .meta td:first-child {
            width: 190px
        }

        .amount {
            font-size: 22px;
            font-weight: 700;
            margin: 18px 0;
            padding: 12px;
            border: 1px solid #aaa;
            text-align: center
        }

        .status {
            padding: 10px 12px;
            margin: 12px 0;
            border-radius: 6px;
            text-align: center;
            font-weight: 700
        }

        .status-ok {
            background: #eaf8ef;
            color: #167744;
            border: 1px solid #bfe8ce
        }

        .status-cancel {
            background: #fff0f1;
            color: #b4232d;
            border: 1px solid #f2b8bd
        }

        .foot {
            margin-top: 32px;
            display: flex;
            justify-content: flex-end
        }

        .sign {
            text-align: center;
            min-width: 200px
        }

        .no-print {
            text-align: center;
            margin-top: 20px
        }

        @media print {
            .no-print {
                display: none
            }

            body {
                padding: 0
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="head">
            <div class="title">BUKTI <?= strtoupper(html_escape($row['jenis'])) ?> TABUNGAN SISWA</div>
            <div>SD Kreatif</div>
        </div>
        <?php $status = isset($row['status_transaksi']) ? $row['status_transaksi'] : 'Aktif'; ?>
        <div class="status <?= $status === 'Dibatalkan' ? 'status-cancel' : 'status-ok' ?>"><?= html_escape($status) ?>
        </div>
        <table class="meta">
            <tr>
                <td>No. Transaksi</td>
                <td>: <?= html_escape($row['no_transaksi']) ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: <?= html_escape(date('d-m-Y', strtotime(str_replace(' ', '', $row['tanggal_transaksi'])))) ?></td>
            </tr>
            <tr>
                <td>Nama Siswa</td>
                <td>: <?= html_escape($row['nama_siswa']) ?></td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>: <?= html_escape($row['nis']) ?> / <?= html_escape($row['nisn'] ?: '-') ?></td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>: <?= html_escape($row['nama_kelas']) ?></td>
            </tr>
            <tr>
                <td>Metode</td>
                <td>: <?= html_escape($row['metode']) ?></td>
            </tr>
            <tr>
                <td>Catatan</td>
                <td>: <?= html_escape($row['catatan'] ?: '-') ?></td>
            </tr>
            <?php if ($status === 'Dibatalkan'): ?>
                <tr>
                    <td>Dibatalkan oleh</td>
                    <td>: <?= html_escape($row['nama_user_batal'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td>Tanggal batal</td>
                    <td>:
                        <?= !empty($row['tanggal_batal']) ? html_escape(date('d-m-Y', strtotime(str_replace(' ', '', $row['tanggal_batal'])))) : '-' ?>
                        <?= html_escape($row['waktu_batal'] ?: '') ?></td>
                </tr>
                <tr>
                    <td>Alasan batal</td>
                    <td>: <?= html_escape($row['alasan_batal'] ?: '-') ?></td>
                </tr>
            <?php endif; ?>
        </table>
        <div class="amount"><?= html_escape($row['jenis']) ?>: Rp<?= number_format((float) $row['nominal'], 0, ',', '.') ?>
        </div>
        <div class="foot">
            <div class="sign">Petugas,<br><br><br><strong><?= html_escape($row['nama_user']) ?></strong></div>
        </div>
        <div class="no-print"><button onclick="window.print()">Cetak</button></div>
    </div>
</body>

</html>