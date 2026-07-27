<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JURNAL KEGIATAN</title>
	<style type="text/css" media="print">
		@page {

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


</head>

<body class="body">

	<center>
		<?php if ($laporan == 'Karyawan'): ?>
			<h5>Laporan Resume Tanggal Belum Input Jurnal Kegiatan</h5>
		<?php else: ?>
			<h5>Laporan Resume Tanggal Belum Input Jurnal Guru</h5>
		<?php endif; ?>
	</center>

	<table style="width:100%; line-height: 1.5;" id="table1">
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">
				<?= $status ?>
			</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px;">
				<?php echo $judul; ?>
			</td>
		</tr>
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;">Nama</td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px; text-transform:uppercase;">
				<?php echo $pegawai['nama_pegawai'] ?? 'Semua Pegawai'; ?>
			</td>
		</tr>
	</table>
	<?php if (!empty($pegawai['nama_pegawai'])): ?>
		<table style="width:100%; margin-bottom:10px; margin-top:3px;" id="table2" class="grid">
			<thead>
				<tr>
					<td>NO</td>
					<td>TANGGAL</td>
					<td>KEGIATAN</td>
					<td>SEMESTER</td>
					<td>PERIODE</td>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($jurnal_pegawai)): ?>
					<?php
					$no = 1;
					foreach ($jurnal_pegawai as $jurnal):
						$tgl = date('Y-m-d', strtotime($jurnal['tanggal']));
						$tgl_input = date('Y-m-d', strtotime($jurnal['tanggal_input']));

						$tanggal = strtotime($jurnal['tanggal']);
						$tanggal_input = strtotime($jurnal['tanggal_input']);
						$selisih = round(($tanggal_input - $tanggal) / 86400);
						$selisih_hari = max(0, (int) $selisih);
						$badge_selisih = $selisih_hari > 0 ? " ({$selisih_hari} hari)" : "";

						if ($tgl === $tgl_input): ?>
							<tr>
								<td>
									<?= $no++ ?>
								</td>
								<td>
									<?= date('d-m-Y', strtotime($jurnal['tanggal'])) ?>
								</td>
								<td>
									<?= date('d-m-Y H:i', strtotime($jurnal['tanggal_input'])) . $badge_selisih ?>
								</td>
								<td>
									<?= htmlspecialchars($jurnal['kegiatan'], ENT_QUOTES, 'UTF-8') ?>
								</td>
								<td>
									<?= htmlspecialchars($jurnal['semester'], ENT_QUOTES, 'UTF-8') ?>
								</td>
								<td>
									<?= htmlspecialchars($jurnal['periode'], ENT_QUOTES, 'UTF-8') ?>
								</td>
							</tr>
						<?php else: ?>
							<tr>
								<td>
									<?= $no++ ?>
								</td>
								<td colspan="5" class="text-muted" style="text-align: center;">
									<em>Belum diisi untuk tanggal
										<?= date('d-m-Y', strtotime($jurnal['tanggal'])) ?>.
									</em>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>

				<?php else: ?>
					<tr>
						<td colspan="5" style="text-align: center;">Tidak ada data</td>
					</tr>
				<?php endif; ?>

			</tbody>


		</table>
	<?php else: ?>
		<?php if ($laporan == 'Karyawan'): ?>
			<table style="width:100%; margin-bottom:10px; margin-top:3px;" id="table2" class="grid">
				<thead>
					<tr>
						<td style="width:20px; text-align:center;">No</td>
						<td style="width:140px;">NAMA</td>
						<td style="width:140px; text-align: center;">TANGGAL BELUM DIISI</td>
						<td style="width:110px; text-align: center;">SEMESTER</td>
						<td style="width:110px; text-align: center;">PERIODE</td>
					</tr>
				</thead>
				<tbody>


					<?php if (!empty($jurnal_pegawai)): ?>
						<?php
						$pegawaiKeys = array_keys($jurnal_pegawai);
						sort($pegawaiKeys, SORT_NATURAL | SORT_FLAG_CASE);
						$no = 1;
						?>

						<?php foreach ($pegawaiKeys as $nama): ?>
							<?php
							$data = $jurnal_pegawai[$nama] ?? [];
							$dates = $data['tanggal'] ?? [];
							// Urutkan tanggal
							usort($dates, function ($a, $b) {
								$da = DateTime::createFromFormat('d-m-Y', $a);
								$db = DateTime::createFromFormat('d-m-Y', $b);
								if (!$da || !$db)
									return strcmp($a, $b);
								return $da <=> $db;
							});

							$rowspan = max(1, count($dates));
							$nama_safe = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
							$semester = htmlspecialchars($data['semester'] ?? '-', ENT_QUOTES, 'UTF-8');
							$periode = htmlspecialchars($data['periode'] ?? '-', ENT_QUOTES, 'UTF-8');
							?>

							<?php if (!empty($dates)): ?>
								<?php foreach ($dates as $i => $tglKosong): ?>
									<?php
									// Cek apakah tanggal ini hari Minggu
									$dt = DateTime::createFromFormat('d-m-Y', $tglKosong);
									$isSunday = $dt && $dt->format('w') == 0; // 0 = Minggu
			
									$labelTanggal = $isSunday ? $tglKosong . ' (Libur)' : $tglKosong;
									$tgl_html = htmlspecialchars($labelTanggal, ENT_QUOTES, 'UTF-8');
									$styleTanggal = 'text-align: center;' . ($isSunday ? 'color:red; font-weight:bold;' : '');
									?>
									<tr>
										<?php if ($i === 0): ?>
											<td rowspan="<?= $rowspan ?>" style="text-align:center;">
												<?= $no++; ?>
											</td>
											<td rowspan="<?= $rowspan ?>">
												<?= $nama_safe; ?>
											</td>
										<?php endif; ?>

										<td style="<?= $styleTanggal ?>">
											<?= $tgl_html; ?>
										</td>

										<?php if ($i === 0): ?>
											<td rowspan="<?= $rowspan ?>" style="text-align: center;">
												<?= $semester; ?>
											</td>
											<td rowspan="<?= $rowspan ?>" style="text-align: center;">
												<?= $periode; ?>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>

							<?php endif; ?>

						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5" style="text-align:center;">Tidak ada data</td>
						</tr>
					<?php endif; ?>


				</tbody>
			</table>
		<?php else: ?>

			<table style="width:100%; margin-bottom:10px; margin-top:3px;" id="table2" class="grid">
				<thead>
					<tr>
						<td style="width:20px; text-align:center;">No</td>
						<td style="width:140px;">NAMA</td>
						<td style="width:140px; text-align: center;">KELAS</td>
						<td style="width:140px; text-align: center;">MATA PELAJARAN</td>
						<td style="width:140px; text-align: center;">TANGGAL BELUM DIISI</td>
						<td style="width:110px; text-align: center;">SEMESTER</td>
						<td style="width:110px; text-align: center;">PERIODE</td>
					</tr>
				</thead>
				<tbody>


					<?php if (!empty($jurnal_pegawai)): ?>
						<?php
						$pegawaiKeys = array_keys($jurnal_pegawai);
						sort($pegawaiKeys, SORT_NATURAL | SORT_FLAG_CASE);
						$no = 1;
						?>

						<?php foreach ($pegawaiKeys as $nama): ?>
							<?php
							$entries = $jurnal_pegawai[$nama] ?? [];
							if (empty($entries)) {
								continue;
							}
							$nama_safe = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
							$rows = [];
							foreach ($entries as $entry) {
								$dates = $entry['tanggal'] ?? [];
								usort($dates, function ($a, $b) {
									$da = DateTime::createFromFormat('d-m-Y', $a);
									$db = DateTime::createFromFormat('d-m-Y', $b);
									if (!$da || !$db)
										return strcmp($a, $b);
									return $da <=> $db;
								});
								if (empty($dates)) {
									$dates = ['-'];
								}

								$rows[] = [
									'dates' => $dates,
									'semester' => htmlspecialchars($entry['semester'] ?? '-', ENT_QUOTES, 'UTF-8'),
									'periode' => htmlspecialchars($entry['periode'] ?? '-', ENT_QUOTES, 'UTF-8'),
									'mapel' => htmlspecialchars($entry['mapel'] ?? '-', ENT_QUOTES, 'UTF-8'),
									'nama_kelas' => htmlspecialchars($entry['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8'),
									'kode_kelas' => htmlspecialchars($entry['kode_kelas'] ?? '-', ENT_QUOTES, 'UTF-8'),
								];
							}

							$totalRows = 0;
							foreach ($rows as $row) {
								$totalRows += count($row['dates']);
							}
							?>

							<?php foreach ($rows as $rowIndex => $row): ?>
								<?php
								$dates = $row['dates'];
								$rowspan = count($dates);
								?>

								<?php foreach ($dates as $i => $tglKosong): ?>
									<?php
									$dt = DateTime::createFromFormat('d-m-Y', $tglKosong);
									$isSunday = $dt && $dt->format('w') == 0;
									if ($dt) {
										$labelTanggal = $isSunday ? $tglKosong . ' (Libur)' : $tglKosong;
									} else {
										$labelTanggal = $tglKosong;
									}
									$tgl_html = htmlspecialchars($labelTanggal, ENT_QUOTES, 'UTF-8');
									$styleTanggal = 'text-align: center;' . ($isSunday ? 'color:red; font-weight:bold;' : '');
									?>
									<tr>
										<?php if ($i === 0 && $rowIndex === 0): ?>
											<td rowspan="<?= $totalRows ?>" style="text-align:center;">
												<?= $no++; ?>
											</td>
											<td rowspan="<?= $totalRows ?>">
												<?= $nama_safe; ?>
											</td>
										<?php endif; ?>

										<td style="text-align: center;">
											<?= $row['nama_kelas']; ?>
											<?= $row['kode_kelas']; ?>
										</td>
										<td style="text-align: center;">
											<?= $row['mapel']; ?>
										</td>
										<td style="<?= $styleTanggal ?>">
											<?= $tgl_html; ?>
										</td>

										<?php if ($i === 0): ?>
											<td rowspan="<?= $rowspan ?>" style="text-align: center;">
												<?= $row['semester']; ?>
											</td>
											<td rowspan="<?= $rowspan ?>" style="text-align: center;">
												<?= $row['periode']; ?>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							<?php endforeach; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5" style="text-align:center;">Tidak ada data</td>
						</tr>
					<?php endif; ?>


				</tbody>
			</table>
		<?php endif; ?>


	<?php endif; ?>
	<script>
		window.print()
	</script>


</body>

</html>