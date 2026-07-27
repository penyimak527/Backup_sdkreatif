<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JURNAL PRESENSI PEGAWAI</title>
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
		position: absolute;
		/* right:0; */
		bottom: 0;
	}
</style>

<body class="body">
	<center>
		<h2>Laporan Presensi Pegawai</h2>
	</center>
	<div class="clear:both;"></div>
	<br>
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td style="width: 15%;"><?php echo $status ?></td>
				<td style="width: 2%;">:</td>
				<td><?php echo $judul; ?></td>
			</tr>
			<tr>
						<?php if ($tampil_pegawai == 'tampil' || $tampil_pegawai == '') { ?>
							<td style="width: 15%;"><?php echo $tampil ?></td>
							<td style="width: 2%;">:</td>
							<td>Semua Pegawai</td>
			
		<?php } else {?>
							<td style="width: 15%;"><?php echo $tampil ?></td>
							<td style="width: 2%;">:</td>
							<td>Pegawai Absen</td>
		<?php }?>
			</tr>
		</tbody>
	</table>
	<br>
	<div class="clear:both;"></div>
	<table style="width: 100%;" class="grid">
		<thead>
			<th style="text-align: center;">Tanggal</th>
			<th style="text-align: center;">Nama</th>
			<th style="text-align: center;">Jam Masuk</th>
			<th style="text-align: center;">Menit Terlambat</th>
			<th style="text-align: center;">Jam Pulang</th>
			<th style="text-align: center;">Status</th>
		</thead>
		<tbody>
			<?php if(!empty($grouped_by_tanggal)):?>
			<?php foreach ($grouped_by_tanggal as $tanggal => $pegawai_list): ?>
				<?php $rowspan = count($pegawai_list); ?>
				<?php foreach ($pegawai_list as $index => $p): ?>
					<tr>
						<?php if ($index === 0): ?>
							<td rowspan="<?= $rowspan ?>"><?= $tanggal ?></td>
						<?php endif; ?>

						<td><?= $p['nama_pegawai'] ?></td>

						<?php if ($p['status'] == '0'): ?>
							<td colspan="4" style="text-align:center;">Tidak ada data</td>
						<?php else: ?>
							<td><?= $p['jam_masuk'] ?? '-' ?></td>
							<td><?= $p['jam_terlambat'] ?? '-' ?></td>
							<td><?= empty($p['jam_pulang']) ? '-' : $p['jam_pulang'];?></td>
							<td><?= $p['status_absen'] ?? '-' ?></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="6" style="text-align: center;">Tidak ada data</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<script>
		window.print();
		// window.onfocus = function () { window.close(); }
	</script>
</body>

</html>