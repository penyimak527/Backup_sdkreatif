<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title>Laporan Tagihan Per Kelas</title>
	<style>
		@page { margin: 1cm; }
		body { font-family: Arial, Helvetica, sans-serif; margin: 0; font-size: 11px; }
		h2 { text-align: center; font-size: 17px; margin: 5px 0 22px; }
		.meta { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
		.meta td { padding: 2px 0; vertical-align: top; }
		.grid { width: 100%; border-collapse: collapse; }
		.grid th, .grid td { border: 1px solid black; padding: 6px; font-size: 10px; }
		.grid th { text-align: center; background: #f3f3f3; }
		.right { text-align: right; }
		.center { text-align: center; }
		.summary { width: 380px; margin: 13px 0 0 auto; border-collapse: collapse; }
		.summary td { padding: 3px; }
		.summary td:first-child { font-weight: bold; }
	</style>
</head>
<body>
	<h2>LAPORAN TAGIHAN PER KELAS</h2>
	<table class="meta">
		<tr>
			<td style="width:66%;">
				<table>
					<tr><td style="width:130px;"><b>PERIODE</b></td><td>:</td><td><?= html_escape((string) $judul); ?></td></tr>
					<tr><td><b>TAHUN AJARAN</b></td><td>:</td><td><?= html_escape($tahun_ajaran); ?></td></tr>
					<tr><td><b>JENIS TAGIHAN</b></td><td>:</td><td><?= html_escape($jenis_tagihan); ?></td></tr>
				</table>
			</td>
			<td class="right" style="white-space:nowrap;">Dicetak: <?= date('d-m-Y H:i'); ?><br>Petugas: <?= html_escape($petugas); ?></td>
		</tr>
	</table>
	<table class="grid">
		<thead>
			<tr><th>No</th><th>Kelas</th><th>Jumlah Siswa</th><th>Target</th><th>Pembayaran</th><th>Tunggakan</th><th>Realisasi (%)</th></tr>
		</thead>
		<tbody>
			<?php if (empty($data_laporan)): ?>
				<tr><td colspan="7" class="center">Tidak ada data sesuai filter.</td></tr>
			<?php else: foreach ($data_laporan as $i => $row): ?>
				<tr>
					<td class="center"><?= $i + 1; ?></td>
					<td><?= html_escape($row['nama_kelas']); ?></td>
					<td class="center"><?= (int) $row['jumlah_siswa']; ?></td>
					<td class="right">Rp<?= number_format((float) $row['total_tagihan'], 0, ',', '.'); ?></td>
					<td class="right">Rp<?= number_format((float) $row['total_dibayar'], 0, ',', '.'); ?></td>
					<td class="right">Rp<?= number_format((float) $row['total_sisa'], 0, ',', '.'); ?></td>
					<td class="right"><?= number_format((float) $row['realisasi'], 2, ',', '.'); ?>%</td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
	<table class="summary">
		<tr><td>Total Target</td><td>:</td><td class="right">Rp<?= number_format((float) $total_tagihan, 0, ',', '.'); ?></td></tr>
		<tr><td>Total Pembayaran</td><td>:</td><td class="right">Rp<?= number_format((float) $total_dibayar, 0, ',', '.'); ?></td></tr>
		<tr><td>Total Tunggakan</td><td>:</td><td class="right">Rp<?= number_format((float) $total_sisa, 0, ',', '.'); ?></td></tr>
	</table>
	<script>window.print();</script>
</body>
</html>
