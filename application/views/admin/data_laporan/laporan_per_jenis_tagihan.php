<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Laporan Per Jenis Tagihan</title>
	<style>
		@page {
			size: A4 landscape;
			margin: 10mm;
		}

		* {
			box-sizing: border-box;
		}

		body {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 10px;
			color: #111;
			margin: 0;
			background: #fff;
		}

		.report-title {
			margin: 0 0 14px;
			text-align: center;
			font-size: 15px;
			font-weight: bold;
			text-transform: uppercase;
		}

		.meta {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 10px;
		}

		.meta td {
			vertical-align: top;
			border: 0;
		}

		.filter-info {
			width: auto;
			border-collapse: collapse;
		}

		.filter-info td {
			padding: 1px 4px 1px 0;
			line-height: 1.35;
			border: 0;
		}

		.filter-info .label {
			width: 125px;
			font-weight: bold;
			text-transform: uppercase;
			white-space: nowrap;
		}

		.filter-info .separator {
			width: 10px;
		}

		.print-info {
			text-align: right;
			white-space: nowrap;
			line-height: 1.5;
		}

		.report-table {
			border-collapse: collapse;
			width: 100%;
			margin-top: 5px;
		}

		.report-table th,
		.report-table td {
			border: 1px solid #222;
			padding: 4px 5px;
			vertical-align: middle;
		}

		.report-table th {
			background: #f4f4f4;
			font-size: 9px;
			text-align: center;
		}

		.report-table td {
			font-size: 9px;
		}

		.text-end {
			text-align: right;
		}

		.text-center {
			text-align: center;
		}

		.summary {
			border-collapse: collapse;
			width: 330px;
			margin: 10px 0 0 auto;
		}

		.summary td {
			border: 0;
			padding: 2px 0 2px 8px;
		}

		.summary td:first-child {
			font-weight: bold;
		}

		.summary td:nth-child(2) {
			width: 10px;
			text-align: center;
		}

		.empty {
			padding: 12px !important;
			text-align: center;
		}
	</style>
</head>

<body>
	<div class="report-title">Laporan Per Jenis Tagihan</div>
	<table class="meta">
		<tr>
			<td>
				<table class="filter-info">
					<tr>
						<td class="label">Tahun Ajaran</td>
						<td class="separator">:</td>
						<td><?= html_escape($tahun_ajaran) ?></td>
					</tr>
					<tr>
						<td class="label">Kelas</td>
						<td class="separator">:</td>
						<td><?= html_escape($nama_kelas) ?></td>
					</tr>
				</table>
			</td>
			<td class="print-info">Dicetak: <?= date('d-m-Y H:i') ?><br>Petugas: <?= html_escape($petugas ?? '-') ?></td>
		</tr>
	</table>
	<table class="report-table">
		<thead>
			<tr>
				<th style="width:34px;">No</th>
				<th>Jenis Tagihan</th>
				<th>Nama Tagihan</th>
				<th>Jumlah Siswa</th>
				<th>Total Target Tagihan</th>
				<th>Total Pembayaran</th>
				<th>Total Sisa</th>
				<th>Realisasi (%)</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($data_laporan)): ?>
				<tr>
					<td colspan="8" class="empty">Tidak ada data sesuai filter.</td>
				</tr>
			<?php else: ?>
				<?php foreach ($data_laporan as $i => $row): ?>
					<tr>
						<td class="text-center"><?= $i + 1 ?></td>
						<td><?= html_escape($row['nama_jenis_tagihan']) ?></td>
						<td><?= html_escape($row['nama_tagihan'] ?? '-') ?></td>
						<td class="text-center"><?= (int)$row['jumlah_siswa'] ?></td>
						<td class="text-end">Rp<?= number_format((float)$row['total_target'], 0, ',', '.') ?></td>
						<td class="text-end">Rp<?= number_format((float)$row['total_pembayaran'], 0, ',', '.') ?></td>
						<td class="text-end">Rp<?= number_format((float)$row['total_sisa'], 0, ',', '.') ?></td>
						<td class="text-end"><?= number_format((float)$row['realisasi'], 2, ',', '.') ?>%</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<table class="summary">
		<tr>
			<td>Total Target Tagihan</td>
			<td>:</td>
			<td class="text-end">Rp<?= number_format((float)$total_target, 0, ',', '.') ?></td>
		</tr>
		<tr>
			<td>Total Pembayaran</td>
			<td>:</td>
			<td class="text-end">Rp<?= number_format((float)$total_pembayaran, 0, ',', '.') ?></td>
		</tr>
		<tr>
			<td>Total Sisa</td>
			<td>:</td>
			<td class="text-end">Rp<?= number_format((float)$total_sisa, 0, ',', '.') ?></td>
		</tr>
		<tr>
			<td>Persentase Realisasi</td>
			<td>:</td>
			<td class="text-end"><?= number_format((float)$total_realisasi, 2, ',', '.') ?>%</td>
		</tr>
	</table>
	<script>
				window.print();
	</script>
</body>

</html>