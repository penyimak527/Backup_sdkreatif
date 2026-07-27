<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Olah POS </title>
</head>
<style type="text/css" media="print">
    @page {
        size: landscape;
        margin: 1cm;
    }

    .body {
        font-family: Arial, Helvetica, sans-serif;
    }

    .grid th {
        background: white;
        vertical-align: middle;
        border: 1px solid black;
        color: black;
        text-align: center;
        height: 30px;
        font-size: 10px;
    }

    .grid td {
        background: #FFFFFF;
        vertical-align: middle;
        border: 1px solid black;
        font: 11px/15px sans-serif;
        font-size: 9px;
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
    <center>
        <h5>Laporan Olah Pos</h5>
    </center>
    <table style="width:100%;">
        <tr>
            <td style="width:7%; font-size:11px; text-transform:uppercase;"><?= $status ?></td>
            <td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
            <td style="width:34%; font-size:11px;"><?php echo $judul; ?></td>
        </tr>
    </table>

     <?php
    $bulanArr = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    // TOTAL FOOTER
    $total_masuk = [];
    $total_keluar = [];
    $total_saldo = [];
    for ($b = 1; $b <= 12; $b++) {
        $total_masuk[$b] = 0;
        $total_keluar[$b] = 0;
        $total_saldo[$b] = 0;
    }?>
    <table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
        <thead>
            <tr>
                <th rowspan="2">KATEGORI</th>
                <?php
                for ($b = 1; $b <= 12; $b++) { ?>
                    <th colspan="3">
                        <?= $bulanArr[$b] ?>
                    </th>
                <?php } ?>
            </tr>

            <tr>
                <?php for ($b = 1; $b <= 12; $b++) { ?>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Saldo</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_laporan as $d) { ?>
                <tr>
                    <td>
                        <?= $d['kode_akun'] ?>
                    </td>
                    <?php for ($b = 1; $b <= 12; $b++) { ?>
                        <?php
                        $masuk = $d['bulan'][$b]['masuk'];
                        $keluar = $d['bulan'][$b]['keluar'];
                        $saldo = $d['bulan'][$b]['saldo'];

                        // TOTAL FOOTER
                        $total_masuk[$b] += $masuk;
                        $total_keluar[$b] += $keluar;
                        $total_saldo[$b] += $saldo;
                        ?>
                        <td class="text-right">
                            <?= $masuk == 0 ? '' : number_format($masuk, 0, ',', '.') ?>
                        </td>
                        <td class="text-right">
                            <?= $keluar == 0 ? '' : number_format($keluar, 0, ',', '.') ?>
                        </td>
                        <td class="text-right">
                            <?= $masuk == 0 ? '' : number_format($saldo, 0, ',', '.') ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
       <tfoot>
            <tr>
                <td>
                    <b>JUMLAH</b>
                </td>
                <?php for ($b = 1; $b <= 12; $b++) { ?>
                    <td class="text-right">
                        <?= number_format($total_masuk[$b], 0, ',', '.') ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($total_keluar[$b], 0, ',', '.') ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($total_saldo[$b], 0, ',', '.') ?>
                    </td>
                <?php } ?>
            </tr>
        </tfoot>
    </table>

    <script>
        window.print(); 
    </script>
</body>

</html>