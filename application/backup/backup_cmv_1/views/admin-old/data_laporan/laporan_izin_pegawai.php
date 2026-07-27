<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JURNAL KEGIATAN</title>
</head>
<style type="text/css" media="print">
	@page {
		/* size: landscape; */
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
		page-break-after: always;
	}
</style>

<body class="body">

	<center>
		<h5>Laporan Izin Pegawai</h5>
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
				<td>TANGGAL</td>
				<td>Nama Pegawai</td>
				<td>KETERANGAN</td>
				<td>ALASAN TIDAK HADIR</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($grouped_by_tanggal as $tanggal => $kelas_data): ?>
				<?php $rowspan = count($kelas_data); ?>
				<?php foreach ($kelas_data as $index => $j): ?>
					<tr>
						<?php if ($index === 0): ?>
							<td rowspan="<?= $rowspan ?>"><?= $tanggal ?></td>
						<?php endif; ?>
						<td><?= $j['nama_pegawai'] ?? '-' ?></td>
						<td><?= $j['keterangan'] ?? '-' ?></td>
						<td><?= $j['alasan_tidak_hadir'] ?? '-' ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</tbody>



	</table>

	<script>
		window.print(); 
	</script>
</body>

</html>