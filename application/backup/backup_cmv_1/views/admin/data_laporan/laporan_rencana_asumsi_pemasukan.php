<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Rencana Asumsi Pemasukan</title>
</head>
<style type="text/css" media="print">
    @page {
        size: landscape;
        margin: 1cm;
    }

    .body {
        font-family: Arial, Helvetica, sans-serif;
    }

    .judul {
        text-align: center;
        margin-bottom: 15px;
    }

    .judul h2,
    .judul h3,
    .judul p {
        margin: 0;
    }

    .grid th {
        background: white;
        vertical-align: middle;
        border: 1px solid black;
        color: black;
        text-align: center;
        height: 30px;
        font-size: 13px;
    }

    .grid td {
        background: #FFFFFF;
        vertical-align: middle;
        border: 1px solid black;
        font: 11px/15px sans-serif;
        font-size: 11px;
        height: 20px;
        padding-left: 5px;
        padding-right: 5px;
    }

    .grid {
        background: black;
        border-collapse: collapse;
        border: 1px solid black;
        border-spacing: 0;
    }

    .grid tfoot td {
        background: white;
        vertical-align: middle;
        color: black;
        text-align: center;
        height: 20px;
    }

    .footer {
        page-break-after: always;
    }
</style>

<body class="body">

    <div class="judul">
        <h2>LAPORAN RENCANA ASUMSI PEMASUKAN</h2>
        <h3>SD KREATIF MUHAMMADIYAH LUMAJANG</h3>
        <br>
    </div>

    <table style="width:100%;">
        <tr>
            <td style="width:7%; font-size:11px; text-transform:uppercase;">Tahun Ajaran</td>
            <td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
            <td style="width:34%; font-size:11px;"><?php echo $tahun_ajaran; ?></td>
        </tr>
    </table>
    <?php
    $bulanArr = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];
    ?>
    <table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
        <thead>
            <tr>
                <th rowspan="2">Kategori</th>
                <th rowspan="2">Asumsi Pemasukan Total</th>
                <th rowspan="2">% Masuk</th>
                <th rowspan="2">Asumsi Masuk</th>
                <th rowspan="2">Saving</th>
                <th rowspan="2">% Saving</th>
                <?php foreach ($list_bulan as $b): ?>
                    <th>
                        <?= $bulanArr[$b] ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php

            // $total_semua = 0;
            // $total_asumsi = 0;
            // $total_saving = 0;
            $total_per_bulan = [];
            $total_semua = 0;
            $total_persen_masuk = 0;
            $total_asumsi = 0;
            $total_saving = 0;
            $total_persen_saving = 0;

            foreach ($list_bulan as $b) {
                $total_per_bulan[$b] = 0;
            }
            
            foreach ($rencana_pemasukan as $r):
                $total_asumsi_masuk = $r['total_asumsi_masuk'];
                $asumsi_masuk = $r['asumsi_masuk'];
                $saving_normal = $r['saving_normal'];
                $saving_persen = $r['saving_persen'];
                $persen_masuk = $r['persen_masuk'];

                $total_semua += $total_asumsi_masuk;
                $total_persen_masuk += $persen_masuk;
                $total_asumsi += $asumsi_masuk;
                $total_saving += $saving_normal;
                $total_persen_saving += $saving_persen;
                ?>
                <tr>
                    <td>
                        <?= $r['kategori'] ?>
                    </td>

                    <td style="text-align:right">
                        <?= number_format($total_asumsi_masuk, 0, ',', '.') ?>
                    </td>

                    <td style="text-align:center">
                        <?= number_format($persen_masuk, 0, ',', '.') ?>%
                    </td>

                    <td style="text-align:right">
                        <?= number_format($asumsi_masuk, 0, ',', '.') ?>
                    </td>

                    <td style="text-align:right">
                        <?= number_format($saving_normal, 0, ',', '.') ?>
                    </td>

                    <td style="text-align:right">
                        <?= number_format($saving_persen, 2, ',', '.') ?>
                    </td>

                    <?php foreach ($list_bulan as $b): ?>
                        <?php
                        $nilai_bulan = $r['bulan'][$b] ?? 0;
                        $total_per_bulan[$b] += $nilai_bulan;
                        ?>
                        <td style="text-align:right">
                            <?= number_format($nilai_bulan, 0, ',', '.') ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><b>Jumlah</b></td>
                <td style="text-align:right">
                    <b><?= number_format($total_semua, 0, ',', '.') ?></b>
                </td>

                <td style="text-align:center">
                    <b>-</b>
                </td>

                <td style="text-align:right">
                    <b><?= number_format($total_asumsi, 0, ',', '.') ?></b>
                </td>

                <td style="text-align:right">
                    <b><?= number_format($total_saving, 0, ',', '.') ?></b>
                </td>

                <td style="text-align:center">
                    <b>-</b>
                </td>

                <?php foreach ($list_bulan as $b): ?>
                    <td style="text-align:right">
                        <b><?= number_format($total_per_bulan[$b], 0, ',', '.') ?></b>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tfoot>
    </table>

    <script>
        window.print(); 
    </script>
</body>

</html>