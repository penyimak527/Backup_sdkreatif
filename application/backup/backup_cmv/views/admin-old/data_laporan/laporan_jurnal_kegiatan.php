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
		<?php if ($pegawai['jabatan'] == 'Guru'): ?>
			<h5>Laporan Jurnal Guru</h5>
		<?php else: ?>
			<h5>Laporan Jurnal Pegawai</h5>
		<?php endif; ?>
	</center>

	<table style="width:100%;">
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;"><?= $status ?></td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px;"><?php echo $judul; ?></td>
		</tr>
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">Nama</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px; text-transform:uppercase;"><?php echo $pegawai['nama_pegawai']; ?>
			</td>
		</tr>
	</table>
	<?php if ($pegawai['jabatan'] == 'Guru'): ?>
		<table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
			<thead>
				<tr>
					<td>TANGGAL</td>
					<td>KELAS</td>
					<td>JAM</td>
					<td>MATA PELAJARAN</td>
					<td>KEGIATAN</td>
					<td>TEMA</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($grouped_by_tanggal as $tanggal => $kelas_data): ?>
					<?php foreach ($kelas_data as $kelas => $jurnals): ?>
						<?php $rowspan = count($jurnals); ?>
						<?php foreach ($jurnals as $index => $j): ?>
							<tr>
								<?php if ($index === 0): ?>
									<td rowspan="<?= $rowspan ?>"><?= $tanggal ?></td>
									<td rowspan="<?= $rowspan ?>"><?= $kelas ?></td>
								<?php endif; ?>
								<td><?= $j['jam_mulai_pelajaran'] . ' - ' . $j['jam_selesai_pelajaran'] ?></td>
								<td><?= $j['mapel'] ?></td>
								<td><?= $j['kegiatan'] ?></td>
								<td><?= $j['tema'] ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>

			</tbody>


		</table>
	<?php else: ?>
		<table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
			<thead>
				<tr>
					<td>TANGGAL</td>
					<td>KEGIATAN</td>
					<td>SEMESTER</td>
					<td>PERIODE</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($jurnal_pegawai as $jurnal): ?>
					<tr>
						<td><?= $jurnal['tanggal'] ?></td>
						<td><?= $jurnal['kegiatan'] ?></td>
						<td><?= $jurnal['semester'] ?></td>
						<td><?= $jurnal['periode'] ?></td>
					</tr>
				<?php endforeach; ?>

			</tbody>


		</table>

	<?php endif; ?>
	<script>
		window.print(); 
	</script>
</body>

</html>
