<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Rencana Pemasukan</title>
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
		<h2>LAPORAN RENCANA PEMASUKAN</h2>
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
    <table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="28%">JENIS PENDAPATAN</th>
                <th>SATUAN</th>
                <th>VOL</th>
                <th>NILAI SATUAN</th>
                <th>JUMLAH</th>
                <th>SATUAN PENERIMAAN</th>
                <th>VOLUME PENERIMAAN</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($data_laporan)):
                $no = 1;
                foreach ($data_laporan as $r):
                    $total_all = 0;
                    $total_nilai_satuan = 0;
                    ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td colspan="8">
                            <b><?= strtoupper($r['nama_jenis']) ?></b>
                        </td>
                    </tr>
                    <?php foreach ($r['detail'] as $d):
                        $total_all += $d['total'];
                        $total_nilai_satuan += $d['nilai_satuan']; ?>
                        <tr>
                            <td></td>
                            <td>
                                <?= $d['nama_kategori'] ?>
                            </td>
                            <td align="center">
                                <?= $d['satuan'] ?>
                            </td>
                            <td align="center">
                                <?= $d['volume'] ?>
                            </td>
                            <td align="right"> <?= number_format($d['nilai_satuan'], 0, ",", ".") ?>
                            </td>
                            <td align="right"> <?= number_format($d['jumlah'], 0, ",", ".") ?>
                            </td>
                            <td align="center">
                                <?= $d['satuan_penerimaan'] ?>
                            </td>
                            <td align="center">
                                <?= $d['volume_penerimaan'] ?>
                            </td>
                            <td align="right"> <?= number_format($d['total'], 0, ",", ".") ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight:bold;background:#eee">
                        <td></td>
                        <td align="center">
                            Jumlah
                        </td>
                        <td></td>
                        <td></td>
                        <!-- <td align="center">
                        <= number_format($r['subtotal_volume']) ?>
                    </td> -->
                        <td align="right"><?= number_format($total_nilai_satuan, 0, ",", ".") ?></td>
                        <td align="right">
                            <?= number_format($r['subtotal_jumlah'], 0, ",", ".") ?>
                        </td>
                        <td></td>
                        <td></td>
                        <td align="right">
                            <?= number_format($total_all, 0, ",", ".") ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php
        if (!empty($data_laporan)): ?>
            <tfoot>
                <tr>
                    <td colspan="8" align="right">
                        <b>JUMLAH</b>
                    </td>
                    <td align="right"><b> <?= number_format($grand_total, 0, ",", ".") ?></b></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <script>
        window.print(); 
    </script>
</body>

</html>