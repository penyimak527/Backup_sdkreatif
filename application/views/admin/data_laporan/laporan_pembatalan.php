<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<title>Riwayat Pembatalan</title>
	<style>
		@page {
			size: A4 landscape;
			margin: 10mm;
		}

		body {
			font-family: Arial, Helvetica, sans-serif;
			margin: 0;
			font-size: 10px;
			color: #111;
		}

		h3 {
			text-align: center;
			font-size: 17px;
			margin: 0 0 16px;
		}

		.meta {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 14px;
		}

		.meta td {
			vertical-align: top;
		}

		.meta-label {
			font-weight: bold;
			width: 120px;
		}

		.meta-separator {
			width: 12px;
		}

		.print-info {
			text-align: right;
			white-space: nowrap;
		}

		.grid {
			width: 100%;
			border-collapse: collapse;
			table-layout: auto;
		}

		.grid th,
		.grid td {
			border: 1px solid #222;
			padding: 5px;
			font-size: 9px;
		}

		.grid th {
			background: #f2f2f2;
			text-align: center;
		}

		.text-center {
			text-align: center;
		}

		.text-right {
			text-align: right;
		}

		.summary {
			margin: 12px 0 0 auto;
			width: 340px;
			border-collapse: collapse;
		}

		.summary td {
			padding: 3px 5px;
		}

		.summary td:first-child {
			font-weight: bold;
		}

		.summary td:nth-child(2) {
			width: 12px;
		}
	</style>
</head>

<body>
	<h3>RIWAYAT PEMBATALAN</h3>
	<table class="meta">
		<tr>
			<td>
				<table>
					<tr>
						<td class="meta-label">TAHUN AJARAN</td>
						<td class="meta-separator">:</td>
						<td><?= html_escape($tahun_ajaran_laporan) ?></td>
					</tr>
					<tr>
						<td class="meta-label">SISWA</td>
						<td class="meta-separator">:</td>
						<td><?= html_escape($siswa_laporan) ?></td>
					</tr>
				</table>
			</td>
			<td class="print-info">
				Dicetak: <?= date('d-m-Y H:i') ?><br>
				Petugas: <?= html_escape($petugas_cetak) ?>
			</td>
		</tr>
	</table>
	<table class="grid">
		<thead>
			<tr>
				<th>No</th>
				<th>No Transaksi</th>
				<th>Siswa</th>
				<th>NIS</th>
				<th>Nominal</th>
				<th>Petugas Transaksi</th>
				<th>Petugas Pembatalan</th>
				<th>Tanggal Pembatalan</th>
				<th>Alasan</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($data_laporan)): ?>
				<tr>
					<td colspan="9" class="text-center">Tidak ada data sesuai filter.</td>
				</tr>
			<?php else: ?>
				<?php foreach ($data_laporan as $index => $row): ?>
					<tr>
						<td class="text-center"><?= $index + 1 ?></td>
						<td><?= html_escape($row['no_transaksi']) ?></td>
						<td><?= html_escape($row['nama_siswa']) ?></td>
						<td><?= html_escape($row['nis']) ?></td>
						<td class="text-right">Rp<?= number_format((float) $row['total_pembayaran'], 0, ',', '.') ?></td>
						<td><?= html_escape($row['nama_user_transaksi']) ?></td>
						<td><?= html_escape($row['nama_user_pembatalan']) ?></td>
						<td><?= html_escape(trim($row['tanggal_pembatalan'] . ' ' . $row['waktu_pembatalan'])) ?></td>
						<td><?= html_escape($row['alasan_pembatalan']) ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<table class="summary">
		<tr>
			<td>Jumlah Pembatalan</td>
			<td>:</td>
			<td class="text-right"><?= (int) $jumlah_pembatalan ?></td>
		</tr>
		<tr>
			<td>Total Dibatalkan</td>
			<td>:</td>
			<td class="text-right">Rp<?= number_format((float) $total_dibatalkan, 0, ',', '.') ?></td>
		</tr>
	</table>
	<script>
			window.print();
	</script>
</body>

</html>