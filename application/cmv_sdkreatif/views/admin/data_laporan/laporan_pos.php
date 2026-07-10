<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Laporan Pos </title>
</head>
<style type="text/css" media="print">
	@page {
		/* size: landscape; */
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

	<!-- KOP LAPORAN -->
	<div class="judul">
		<h2>LAPORAN POS</h2>
		<h3>SD KREATIF MUHAMMADIYAH LUMAJANG</h3>
		<br>
	</div>

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
				<td>URAIAN</td>
				<td>SALDO BULAN LALU</td>
				<td>MASUK</td>
				<td>KELUAR</td>
				<td>SALDO</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$total_saldo_lalu = 0;
			$total_masuk = 0;
			$total_keluar = 0;
			$total_saldo = 0;
			?>
			<?php foreach ($data_pos as $d):
				$total_saldo_lalu += $d['saldo_lalu'];
				$total_masuk += $d['masuk'];
				$total_keluar += $d['keluar'];
				$total_saldo += $d['saldo']; ?>
				<tr>
					<td><?= $d['uraian'] ?></td>

					<td style="text-align:right;">
						<?= $d['saldo_lalu'] != 0 ? number_format($d['saldo_lalu'], 0, ',', '.') : '' ?>
					</td>

					<td style="text-align:right;">
						<?= $d['masuk'] != 0 ? number_format($d['masuk'], 0, ',', '.') : '' ?>
					</td>

					<td style="text-align:right;">
						<?= $d['keluar'] != 0 ? number_format($d['keluar'], 0, ',', '.') : '' ?>
					</td>

					<td style="text-align:right;">
						<?= $d['saldo'] != 0 ? number_format($d['saldo'], 0, ',', '.') : 0 ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td><b>Jumlah</b></td>
				<td style="text-align:right;">
					<b><?= number_format($total_saldo_lalu, 0, ',', '.') ?></b>
				</td>
				<td style="text-align:right;">
					<b><?= number_format($total_masuk, 0, ',', '.') ?></b>
				</td>
				<td style="text-align:right;">
					<b><?= number_format($total_keluar, 0, ',', '.') ?></b>
				</td>
				<td style="text-align:right;">
					<b><?= number_format($total_saldo, 0, ',', '.') ?></b>
				</td>
			</tr>
		</tfoot>
	</table>

	<!-- TANDA TANGAN -->
	<table class="ttd">
		<tr>
			<td><br>Ketua</td>
			<td>
				Lumajang, <?= $tanggal_laporan ?><br>
				Bendahara
			</td>
		</tr>

		<tr>
			<td class="nama-ttd">
				<br><br><br><br>
				Dimas Doddy Priyambodho, S.Ag, M.Pd
			</td>

			<td class="nama-ttd">
				<br><br><br><br>
				Nurlaili Budi Indahwati, S.Psi
			</td>
		</tr>
	</table>
	<script>
		window.print(); 
	</script>
</body>

</html>