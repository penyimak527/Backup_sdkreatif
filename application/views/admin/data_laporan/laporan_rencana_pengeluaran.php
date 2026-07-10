<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Rencana Pengeluaran</title>
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

    <!-- KOP LAPORAN -->
    <div class="judul">
        <h2>LAPORAN RENCANA PENGELUARAN</h2>
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
                <th>KATEGORI</th>
                <?php foreach ($list_bulan as $bulan) { ?>
                    <th><?= $bulanArr[$bulan] ?></th>
                <?php } ?>
                <th>TOTAL</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $total_bulan = [];
            $grand_total = 0;

            foreach ($list_bulan as $bulan) {
                $total_bulan[$bulan] = 0;
            }

            foreach ($data_laporan as $r) {
                $total_baris = 0;
                ?>
                <tr>
                    <td><?= $r['keterangan']; ?></td>

                    <?php foreach ($list_bulan as $bulan) {
                        $nilai = isset($r['bulan_' . $bulan]) ? (float) $r['bulan_' . $bulan] : 0;

                        $total_bulan[$bulan] += $nilai;
                        $total_baris += $nilai;
                        ?>
                        <td style="text-align:right;">
                            <?= $nilai > 0 ? number_format($nilai, 0, ',', '.') : '-'; ?>
                        </td>
                    <?php } ?>

                    <td style="text-align:right;">
                        <b><?= $total_baris > 0 ? number_format($total_baris, 0, ',', '.') : '-'; ?></b>
                    </td>
                </tr>
                <?php $grand_total += $total_baris;
            } ?>
        </tbody>
        <tfoot>
            <tr>
                <td><b>Jumlah</b></td>

                <?php foreach ($list_bulan as $bulan) { ?>
                    <td style="text-align:right;">
                        <b><?= $total_bulan[$bulan] > 0 ? number_format($total_bulan[$bulan], 0, ',', '.') : '-'; ?></b>
                    </td>
                <?php } ?>

                <td style="text-align:right;">
                    <b><?= $grand_total > 0 ? number_format($grand_total, 0, ',', '.') : '-'; ?></b>
                </td>
            </tr>
        </tfoot>
    </table>

    <script>
        window.print(); 
    </script>
</body>

</html>