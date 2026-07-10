<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Laporan Rencana Anggaran </title>
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
		<h2>LAPORAN RENCANA ANGGARAN</h2>
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
				<td>JUMLAH</td>
				<td>URAIAN</td>
				<td>JUMLAH</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="bold">Saldo bulan lalu</td>
				<td class="text-right bold">
					<?= number_format($saldo_bulan_lalu, 0, ',', '.') ?>
				</td>
				<td><?= $pengeluaran[0]['keterangan'] ?? '' ?></td>
				<td class="text-right">
					<?= !empty($pengeluaran[0]['total'])
						? number_format($pengeluaran[0]['total'], 0, ',', '.')
						: '' ?>
				</td>
			</tr>
			<tr>
				<td class="bold">Pengajuan Anggaran</td>
				<td class="text-right bold">
					<?= number_format($pengajuan_anggaran, 0, ',', '.') ?>
				</td>
				<td><?= $pengeluaran[1]['keterangan'] ?? '' ?></td>
				<td class="text-right">
					<?= !empty($pengeluaran[1]['total'])
						? number_format($pengeluaran[1]['total'], 0, ',', '.')
						: '' ?>
				</td>
			</tr>
			<?php if (!empty($pengeluaran)): ?>
				<?php foreach ($pengeluaran as $key => $row): ?>
					<?php
					if ($key < 2)
						continue;
					?>
					<tr>
						<td></td>
						<td></td>
						<td><?= $row['keterangan'] ?></td>
						<td class="text-right">
							<?= $row['total'] > 0
								? number_format($row['total'], 0, ',', '.')
								: '' ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<?php
			$total_jumlah = $saldo_bulan_lalu + $pengajuan_anggaran;
			?>
			<tr>
				<td class="text-center bold">
					Jumlah
				</td>
				<td class="text-right bold">
					<?= number_format($total_jumlah, 0, ',', '.') ?>
				</td>
				<td class="text-center bold">
					Jumlah
				</td>
				<td class="text-right bold">
					<?= number_format($total_rencana, 0, ',', '.') ?>
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

		<!-- <tr>
			<td colspan="2" style="padding-top:60px;">
				Mengetahui<br>
				Koordinator Majlis Dikdasmen
				<br><br><br><br><br><br>

				<b>Drs. Agus Siswantono, M.Psi.</b>
			</td>
		</tr> -->
	</table>
	<script>
		window.print(); 
	</script>
</body>

</html>