<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JURNAL MENGAJAR</title>
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
		<h5>JURNAL MENGAJAR</h5>
	</center>

	<table style="width:100%;">
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">Tanggal</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px;"><?php echo $judul; ?></td>
		</tr>
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">Kelas</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px; text-transform:uppercase;"><?php echo $kelas; ?></td>
		</tr>
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">Semester</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px; text-transform:uppercase;"><?php echo $semester; ?></td>
		</tr>
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">Tahun Ajaran</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px; text-transform:uppercase;"><?php echo $periode; ?></td>
		</tr>
	</table>
	<table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
		<thead>
			<tr>
				<td>GURU</td>
				<td>MATAPELAJARAN</td>
				<td>JAM</td>
				<td>KEGIATAN</td>
				<td>TEMA</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($grouped_by_guru_mapel as $nama_guru => $mapels): ?>
				<?php
				$rowspan_guru = array_sum(array_map('count', $mapels)); // Total semua jurnal guru ini
				$first_guru = true;
				?>
				<?php foreach ($mapels as $mapel => $jurnals): ?>
					<?php $rowspan_mapel = count($jurnals); ?>
					<?php foreach ($jurnals as $index => $jurnal): ?>
						<tr>
							<?php if ($first_guru): ?>
								<td rowspan="<?= $rowspan_guru ?>"><?= $nama_guru; ?></td>
								<?php $first_guru = false; ?>
							<?php endif; ?>

							<?php if ($index === 0): ?>
								<td rowspan="<?= $rowspan_mapel ?>"><?= $mapel; ?></td>
							<?php endif; ?>

							<td><?= $jurnal['jam_mulai_pelajaran'] . ' - ' . $jurnal['jam_selesai_pelajaran']; ?></td>
							<td><?= $jurnal['kegiatan']; ?></td>
							<td><?= $jurnal['tema']; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</tbody>

	</table>

	<script>
		window.print(); 
	</script>
</body>

</html>
