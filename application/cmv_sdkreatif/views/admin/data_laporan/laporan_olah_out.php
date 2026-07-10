<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Olah Out</title>
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

    .ttd {
        width: 100%;
        margin-top: 50px;
    }

    .ttd td {
        text-align: center;
        vertical-align: top;
        font-size: 12px;
    }

    .nama-ttd {
        margin-top: 80px;
        font-weight: 700;
        /* text-decoration: underline; */
    }
</style>

<body class="body">

    <center>
        <h5>Laporan Olah Out</h5>
    </center>

    <table style="width:100%;">
        <tr>
            <td style="width:7%; font-size:11px; text-transform:uppercase;"><?= $status ?></td>
            <td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
            <td style="width:34%; font-size:11px;"><?php echo $judul; ?></td>
        </tr>
    </table>
    <table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
        <thead>
            <tr>
                <th>KATEGORI</th>
                <?php
                $bulan = [
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
                foreach ($bulan as $b) {
                    echo "<th>$b</th>";
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = array_fill(1, 12, 0);
            ?>
            <?php foreach ($data_laporan as $d): ?>
                <tr>
                    <td><?= $d['kode_akun'] ?></td>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <?php
                        $nilai = $d['bulan'][$i];
                        $grand_total[$i] += $nilai; ?>
                        <td class="text-right">
                            <?= $nilai != 0 ? number_format($nilai, 0, ',', '.') : '' ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><b>JUMLAH</b></td>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <td class="text-right">
                        <b><?= number_format($grand_total[$i], 0, ',', '.') ?></b>
                    </td>
                <?php endfor; ?>
            </tr>
        </tfoot>
    </table>


    <script>
        window.print(); 
    </script>
</body>

</html>