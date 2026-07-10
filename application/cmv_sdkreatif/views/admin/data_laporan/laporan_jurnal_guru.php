<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JURNAL MENGAJAR</title>
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
		page-break-after: always;
	}
</style>

<body class="body">

	<center>
		<h5>JURNAL MENGAJAR</h5>
	</center>

	<table style="width:100%; line-height: 1.5;">
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;"><?= $status ?></td>
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
				<td>No</td>
				<td>GURU</td>
				<td>MATAPELAJARAN</td>
				<td>KELAS</td>
				<td width="8%">JAM</td>
				<td>KEGIATAN</td>
				<td>TEMA</td>
				<td width="9%">Tanggal Mengajar</td>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($grouped_by_guru_mapel)): ?>

				<?php
				$no = 1;

				$parseDate = function ($str) {
					if (!$str)
						return null;
					$dt = DateTime::createFromFormat('d-m-Y', $str);
					if (!$dt)
						$dt = DateTime::createFromFormat('Y-m-d', $str);
					if (!$dt) {
						$ts = strtotime($str);
						if ($ts)
							$dt = (new DateTime())->setTimestamp($ts);
					}
					return $dt ?: null;
				};

				foreach ($grouped_by_guru_mapel as $nama_guru => $mapels):
					$rowspan_guru = array_sum(array_map('count', $mapels)); // total semua jurnal guru ini
					$first_guru = true;

					foreach ($mapels as $mapel => $jurnals):
						$rowspan_mapel = count($jurnals);

						foreach ($jurnals as $index => $jurnal):

							$dtKeg = $parseDate($jurnal['tanggal'] ?? null);
							$dtIn = $parseDate($jurnal['tanggal_input'] ?? null);

							$sameDay = false;
							if ($dtKeg && $dtIn) {
								$sameDay = ($dtKeg->format('Y-m-d') === $dtIn->format('Y-m-d'));
							}

							$selisihTxt = '';
							if ($dtKeg && $dtIn) {
								$diff = (int) (($dtIn->getTimestamp() - $dtKeg->getTimestamp()) / 86400);
								if ($diff > 0)
									$selisihTxt = " ({$diff} hari)";
							}

							$tglLabel = $dtKeg ? $dtKeg->format('d-m-Y') : htmlspecialchars($jurnal['tanggal'] ?? '-', ENT_QUOTES, 'UTF-8');
							$tglInLabel = $dtIn ? $dtIn->format('d-m-Y') : '-';

							?>
							<tr>
								<?php if ($first_guru): ?>
									<td rowspan="<?= $rowspan_guru ?>"><?= $no++; ?></td>
									<td rowspan="<?= $rowspan_guru ?>"><?= htmlspecialchars($nama_guru, ENT_QUOTES, 'UTF-8'); ?></td>
									<?php $first_guru = false; ?>
								<?php endif; ?>

								<?php if ($index === 0): ?>
									<td rowspan="<?= $rowspan_mapel ?>"><?= htmlspecialchars($mapel, ENT_QUOTES, 'UTF-8'); ?></td>
								<?php endif; ?>
								<td><?= htmlspecialchars($jurnal['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?>
									<?= $jurnal['kode_kelas'] ?>
								</td>
								<?php

								if ($sameDay): ?>
									<td><?= htmlspecialchars(($jurnal['jam_mulai_pelajaran'] ?? '-') . ' - ' . ($jurnal['jam_selesai_pelajaran'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
									</td>


									<td><?= htmlspecialchars($jurnal['kegiatan'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?= htmlspecialchars($jurnal['tema'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?= $tglLabel ?><?= $selisihTxt ?></td>

								<?php else: ?>
									<td colspan="4" class="text-muted" style="text-align: center;">
										<em>Belum diisi untuk tanggal <?= $tglLabel ?>.</em>
										<?php if ($dtIn): ?>
											<span>(tanggal input: <?= $tglInLabel ?><?= $selisihTxt ?>)</span>
										<?php endif; ?>
									</td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>

			<?php else: ?>
				<tr>
					<td colspan="8" style="text-align: center;">Tidak ada data</td>
				</tr>
			<?php endif; ?>
		</tbody>


	</table>

	<script>
		window.print(); 
	</script>
</body>

</html>