<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Laporan Kas </title>
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
		<h2>LAPORAN KAS</h2>
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
	<?php if ($status == 'Tahun'): ?>
		<table width="100%" class="grid" style="margin-top:10px;">
			<thead>
				<tr>
					<th style="width:180px;">Tahun Mulai</th>
					<th style="width:180px;">Bulan Aktif</th>
					<?php
					$bulan_nama = [
						'01' => 'JAN',
						'02' => 'FEB',
						'03' => 'MAR',
						'04' => 'APR',
						'05' => 'MEI',
						'06' => 'JUN',
						'07' => 'JUL',
						'08' => 'AGU',
						'09' => 'SEP',
						'10' => 'OKT',
						'11' => 'NOV',
						'12' => 'DES'
					];
					foreach ($bulan_nama as $nama) {
						echo "<th>$nama</th>";
					}
					?>
				</tr>
			</thead>
			<tbody>
				<!-- SALDO AWAL -->
				<tr>
					<td>
						01/01/<?= $judul ?>
					</td>

					<td>
						<b>Saldo Awal</b>
					</td>
				</tr>

				<!-- SALDO KAS -->
				<tr>
					<td>
						<b>Saldo Kas</b>
					</td>
					<td align="right">
						<?= number_format($saldo_awal) ?>
					</td>

					<?php foreach (array_keys($bulan_nama) as $bulan): ?>
						<td align="right">
							<?= number_format($saldo_bulanan[$bulan]) ?>
						</td>
					<?php endforeach; ?>
				</tr>

				<!-- HEADER PENERIMAAN -->

				<tr>
					<td colspan="14" style="background:#d9d9d9;">
						<b>PENERIMAAN KAS</b>
					</td>
				</tr>

				<?php foreach ($pemasukan_tahun as $row): ?>
					<tr>
						<td colspan="2">
							<?= $row['keterangan']; ?>
						</td>
						<?php foreach (array_keys($bulan_nama) as $bulan): ?>
							<td align="right">
								<?= $row['bulan'][$bulan] > 0 ? number_format($row['bulan'][$bulan]) : ''; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>

				<!-- TOTAL PENERIMAAN -->

				<tr style="font-weight:bold;background:#e6e6e6;">
					<td colspan="2">
						Total Penerimaan
					</td>
					<?php foreach (array_keys($bulan_nama) as $bulan): ?>
						<td align="right">
							<?= number_format($total_pemasukan_bulan[$bulan]); ?>
						</td>
					<?php endforeach; ?>
				</tr>

				<!-- TOTAL KAS TERSEDIA -->

				<tr style="font-weight:bold;background:#cfe2f3;">
					<td colspan="2">
						Total Kas Tersedia
					</td>
					<?php foreach (array_keys($bulan_nama) as $bulan): ?>
						<td align="right">
							<?= number_format($total_kas_tersedia[$bulan]); ?>
						</td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
		<table width="100%" class="grid" style="margin-top:15px;">
			<tbody>
				<tr>
					<td colspan="14" style="background:#d9d9d9;">
						<b>PENGELUARAN KAS</b>
					</td>
				</tr>
				<?php foreach ($pengeluaran_tahun as $row): ?>
					<tr>
						<td colspan="2">
							<?= $row['keterangan']; ?>
						</td>
						<?php foreach (array_keys($bulan_nama) as $bulan): ?>
							<td align="right">
								<?= $row['bulan'][$bulan] > 0 ? number_format($row['bulan'][$bulan]) : ''; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				<tr style="font-weight:bold;background:#e6e6e6;">
					<td colspan="2">
						Total Pengeluaran
					</td>
					<?php foreach (array_keys($bulan_nama) as $bulan): ?>
						<td align="right">
							<?= number_format($total_pengeluaran_bulan[$bulan]); ?>
						</td>
					<?php endforeach; ?>
				</tr>
				<tr style="font-weight:bold;background:#cfe2f3;">
					<td colspan="2">
						Saldo Akhir Bulan
					</td>
					<?php foreach (array_keys($bulan_nama) as $bulan): ?>
						<td align="right">
							<?= number_format($saldo_akhir_bulan[$bulan]); ?>
						</td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
	<?php else: ?>
		<table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
			<thead>
				<tr>
					<td>URAIAN</td>
					<td>JUMLAH</td>
					<td>URAIAN</td>
					<td>JUMLAH</td>
				</tr>
			</thead>
			<?php $max = max(count($pemasukan), count($pengeluaran)); ?>
			<tbody>
				<tr>
					<td><b>Saldo bulan lalu</b></td>
					<td><b><?= number_format($saldo_bulan_lalu) ?></b></td>
					<td><?= $pengeluaran[0]['keterangan'] ?? '' ?></td>
					<td><?= isset($pengeluaran[0]['total']) ? number_format($pengeluaran[0]['total']) : '' ?></td>
				</tr>
				<?php for ($i = 0; $i < $max; $i++): ?>
					<tr>
						<td><?= $pemasukan[$i]['keterangan'] ?? '' ?></td>
						<td><?= isset($pemasukan[$i]['total']) ? number_format($pemasukan[$i]['total']) : '' ?></td>
						<td><?= $pengeluaran[$i + 1]['keterangan'] ?? '' ?></td>
						<td>
							<?= isset($pengeluaran[$i + 1]['total']) ? number_format($pengeluaran[$i + 1]['total']) : '' ?>
						</td>

					</tr>
				<?php endfor; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3" style="text-align:right;"><b>Saldo bulan ini</b></td>
					<td><b><?= number_format($saldo_bulan_ini) ?></b></td>
				</tr>
				<tr>
					<td style="text-align:center;"><b>Jumlah</b></td>
					<td><b><?= number_format($total_pemasukan + $saldo_bulan_lalu) ?></b></td>

					<td style="text-align:center;"><b>Jumlah</b></td>
					<td><b><?= number_format($total_pengeluaran + $saldo_bulan_ini) ?></b></td>
				</tr>
			</tfoot>
		</table>
	<?php endif; ?>
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