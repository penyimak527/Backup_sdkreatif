<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8" />
	<title>Jurnal Harian</title>
	<style>
		@page {
			margin: 0px;
			size: A4;
		}

		body {
			font-family: 'Arial', sans-serif;
			padding: 10px;
		}

		h1 {
			text-align: center;
		}

		.container {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
		}

		.left,
		.right {

			box-sizing: border-box;
		}

		table {
			width: 100%;
			border: none;
			border-collapse: collapse;
		}

		th,
		td {
			border: none;
			padding: 6px;
			text-align: left;
		}

		.hari span {
			width: 30px;
			height: 30px;
			line-height: 30px;
			text-align: center;
			border: 1px solid #000;
			border-radius: 50%;
			font-weight: bold;
			margin: 2px;
			display: inline-block;
		}

		.hari .active-day {
			background: #000;
			color: #fff;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}

		.form-box {
			border: 1px solid #000;
			padding: 8px;
			margin-top: 22px;
			min-height: 85px;
			border-radius: 5px;
		}

		.form-tanggal {
			border: 1px solid #000;
			border-radius: 5px;
			padding: 8px;
			margin-top: 10px;
			min-height: 20px;
		}

		.kode-kelas {
			border: 1px solid #000;
			padding: 10px;
			margin-top: 10px;
		}

		.kiri {
			top: 0;
			left: 0;
		}

		.kanan {
			bottom: 0;
			right: 0;
		}

		.ttd {
			display: flex;
			justify-content: space-between;
			gap: 20px;
			margin-top: 70px;
		}

		.ttd-group {
			width: 48%;
			text-align: center;
		}

		.ttd-box {
			border: 1px solid #000;
			padding: 20px;
			min-height: 100px;
			box-sizing: border-box;
		}

		.ttd-label {
			font-weight: bold;
			margin-bottom: 10px;
		}

		.ttd-name {
			margin-top: 90px;
		}
	</style>
</head>

<body>

	<?php if (!empty($jurnal_guru)): ?>
		<?php foreach ($jurnal_guru as $tanggal => $jurnal): ?>
			<div class="container">
				<div class="left" style="margin-top: -30px;">
					<center>
						<h1
							style="font-size: 58px; font-family:Georgia, 'Times New Roman', Times, serif; border-bottom:2px solid #000; width:220px;">
							Jurnal <br> Harian</h1>
					</center>
					<div style="border:1px solid #000; border-radius: 5px; padding: 10px; width: auto;">
						<table>
							<tr>
								<th width="40px">Kelas</th>
								<th width="130px">Jam</th>
								<th width="260px">Kegiatan Hari Ini</th>
							</tr>
							<?php
							$no = 1;
							for ($i = 0; $i < 11; $i++):
								$j = $jurnal[$i] ?? null;
								?>
								<tr>
									<td>
										<div style="
							border: 1px solid #000;
							padding: 5px;
							border-radius: 3px;
							width: 25px;
							height: 25px;
							display: flex;
							justify-content: center;
							align-items: center;">
											<?= $j['kelas'] ?? '' ?>
										</div>
									</td>

									<td>
										<div style="
							border-bottom: 1px solid #000;
							padding: 5px;
							text-align: left;   ">
											<?php if (isset($j['jam_mulai_pelajaran']) && isset($j['jam_selesai_pelajaran'])) {
												echo $j['jam_mulai_pelajaran'] . ' - ' . $j['jam_selesai_pelajaran'];
											} ?>
										</div>
									</td>
									<td>
										<div style="
							border-bottom: 1px solid #000;
							padding: 5px;
							text-align: left;   ">
											<?= $j['kegiatan'] ?? '' ?>
										</div>
									</td>
								</tr>
							<?php endfor; ?>
							<!-- Tambah baris sesuai kebutuhan -->
						</table>
					</div>
				</div>
				<div class="right" style="width: 240px;">
					<?php

					$tanggal_input = $tanggal ?? date('d-m-Y');
					$timestamp = DateTime::createFromFormat('d-m-Y', $tanggal_input);
					$hariIndex = (int) $timestamp->format('w');


					$hurufHari = ['S', 'S', 'R', 'K', 'J', 'S'];
					?>
					<div class="form-tanggal">
						<strong>Tanggal:</strong> <?= $tanggal_input ?>
					</div>


					<div class="hari" style="margin-top: 10px;">
						<?php foreach ($hurufHari as $i => $huruf): ?>
							<span class="<?= ($i == $hariIndex) ? 'active-day' : '' ?>"><?= $huruf ?></span>
						<?php endforeach; ?>
					</div>

					<?php
					$no = 1;
					for ($i = 0; $i < 3; $i++):
						$j = $jurnal[$i] ?? null;
						?>
						<div class="form-box">
							<strong>Tema Jam ke <?= $no++ ?>:</strong>
							<br>
							<span style="margin-top: 5px;">
								<?= $j['tema'] ?? '' ?>
							</span>
						</div>
					<?php endfor; ?>
					<div class="form-box">
						<strong>Kode Kelas</strong><br>
						<?php
						for ($i = 0; $i < 5; $i++):
							$j = $jurnal[$i] ?? null;
							?>
							<table>
								<tr>
									<td width="25px">
										<div style="
						  border: 1px solid #000;
						  padding: 5px;
						  border-radius: 3px;
						  width: 20px;
						  height: 20px;
						  display: flex;
						  justify-content: center;
						  align-items: center;
						  margin-bottom: 5px;
						  font-size: 12px;
						  margin-left: -5px;
						">
											<?= $j['kode_kelas'] ?? '' ?>
										</div>
									</td>
									<td>
										<div style="
						  border-bottom: 1px solid #000;
						  padding: 5px; 
						  margin-bottom: 5px;
						">
											<?= $j['nama_kelas'] ?? '' ?>
										</div>
									</td>
								</tr>
							</table>
						<?php endfor; ?>

					</div>
				</div>
			</div>
			<div class="ttd">
				<div class="ttd-group">
					<div class="ttd-label">Kepala Sekolah</div>
					<div class="ttd-box">
						<div class="ttd-name">( Dimas Doddy Priyambodho, S.Ag M.Pd )<br>NBM. 143 0 355</div>
					</div>
				</div>

				<div class="ttd-group">
					<div class="ttd-label">Guru Mapel</div>
					<div class="ttd-box">
						<div class="ttd-name">( <?= $guru['nama_guru'] ?> )<br>NBM. <?= $guru['nbm'] ?></div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else: ?>
		<center>
			<h3>Tidak ada data</h3>
		</center>
	<?php endif; ?>
	<script>
		window.print();
	</script>
</body>

</html>