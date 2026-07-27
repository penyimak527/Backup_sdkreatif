<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Perbandingan Rencana Pengeluaran </title>
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
        /* background: white; */
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
        <h5>Laporan Perbandingan Rencana Pengeluaran</h5>
    </center>
    <table style="width:100%;">
        <tr>
            <td style="width:7%; font-size:11px; text-transform:uppercase;">Tahun Ajaran</td>
            <td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
            <td style="width:34%; font-size:11px;"><?php echo $tahun_ajaran; ?></td>
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
    
   $total_rencana = [];
$total_realisasi = [];
$total_selisih = [];

foreach ($bulan_laporan as $b) {
    $total_rencana[$b] = 0;
    $total_realisasi[$b] = 0;
    $total_selisih[$b] = 0;
}
    ?>
    <table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
        <thead>
            <tr><th rowspan="2">KATEGORI</th>
       <?php foreach ($bulan_laporan as $b): ?>
            <th colspan="4">
                <?= $bulanArr[$b] ?>
            </th>
        <?php endforeach; ?>
    </tr>
    <tr>
        <?php foreach ($bulan_laporan as $b): ?>
            <th>Rencana</th>
            <th>Realisasi</th>
            <th>Selisih</th>
            <th>Status</th>
        <?php endforeach; ?>
    </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_laporan)): ?>
                <?php foreach ($data_laporan as $row): ?>
                    <tr>
                        <td><?= $row['kode_akun'] ?></td>
                      <?php foreach ($bulan_laporan as $b): ?>
                            <?php
                            $rencana = $row['bulan'][$b]['rencana'] ?? 0;
                            $realisasi = $row['bulan'][$b]['realisasi'] ?? 0;
                            $selisih = $rencana - $realisasi;

                            $total_rencana[$b] += $rencana;
                            $total_realisasi[$b] += $realisasi;
                            $total_selisih[$b] += $selisih;
                            $status = '';
                            if(!empty($rencana) || !empty($realisasi)){
                                if($selisih == 0){
                                    $status = 'Aman';
                                }else if($selisih > 0){
                                    $status = 'Under';
                                }else{
                                    $status = 'Over Budget';
                                }
                            }
                            ?>
                            <td align="right">
                                <?= $rencana > 0 ? number_format($rencana, 0, ",", ".") : '' ?>
                            </td>
                            <td align="right">
                                <?= $realisasi > 0 ? number_format($realisasi, 0, ",", ".") : '' ?>
                            </td>
                            <td align="right">
                                <?= $selisih != 0 ? number_format($selisih, 0, ",", ".") : '' ?>
                            </td>
                            <td align="right">
                                <?= $status?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>

        <tfoot>
            <tr>
                <td class="text-center">
                    Jumlah
                </td>
                 <?php foreach ($bulan_laporan as $b) { ?>
                    <td class="text-right">
                        <?= number_format($total_rencana[$b], 0, ',', '.') ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($total_realisasi[$b], 0, ',', '.') ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($total_selisih[$b], 0, ',', '.') ?>
                    </td>
                    <td class="text-right"></td>
                <?php } ?>
            </tr>
        </tfoot>
    </table>

    <script>
        window.print(); 
    </script>
</body>

</html>