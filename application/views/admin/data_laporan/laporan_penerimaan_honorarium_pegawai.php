<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Laporan Rekapitulasi Gaji </title>
</head>
<style type="text/css" media="print">
	@page {
		size: landscape;
		margin: 1cm;
	}

	.body {
		font-family: Arial, Helvetica, sans-serif;
		margin: 0;
		padding: 0;
		background: white;
		overflow: hidden;
	}

	.judul {
		text-align: center;
		margin-bottom: 15px;
	}

	.judul h2,
	.judul h3,
	.judul p {
		margin: 0;
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
		/* background: black; */
		background: #fff;
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
<?php
	function rupiah_honorarium($angka)
	{
		$angka = (float) ($angka ?? 0);

		if ($angka == 0) {
			return '-';
		}

		return number_format($angka, 0, ',', '.');
	}

	$total_jumlah_hadir = 0;
	$total_gaji_pokok = 0;
	$total_struktural = 0;
	$total_tunjangan_pendidikan = 0;
	$total_wali_kelas = 0;
	$total_jumlah = 0;
	$total_jumlah_kotor = 0;
	$total_jumlah_penerimaan = 0;
	?>

	<div class="judul">
		<h2>LAPORAN PENERIMAAN HONORARIUM GURU DAN KARYAWAN</h2>
		<h3>SD KREATIF MUHAMMADIYAH LUMAJANG</h3>
		<br>
		<p>Bulan <?= $judul; ?></p>
	</div>

	<table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
			<table class="grid">
		<thead>
			<tr>
				<th rowspan="2" style="width:35px;">No</th>
				<th rowspan="2" style="width:195px;">Nama</th>
				<th rowspan="2" style="width:110px;">Jabatan</th>
                
				<th colspan="3">Gaji</th>
				<th colspan="3">Tunjangan</th>
                
				<th rowspan="2" style="width:80px;">Jumlah</th>
				<th rowspan="2" style="width:95px;">Jumlah Kotor</th>
				<th rowspan="2" style="width:60px;">Jumlah Hadir</th>
				<th rowspan="2" style="width:105px;">Jumlah Penerimaan</th>
			</tr>
			<tr>
				<th style="width:65px;">Masa Kerja</th>
				<th style="width:75px;">Pend. Terakhir</th>
				<th style="width:90px;">Gaji Pokok</th>

				<th style="width:90px;">Struktural</th>
				<th style="width:90px;">Pendidikan</th>
				<th style="width:90px;">Wali Kelas</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($data_laporan)) : ?>
				<?php $no = 1; ?>
				<?php foreach ($data_laporan as $data) : ?>

					<?php
					$jumlah_hadir = (int) ($data['jumlah_hadir'] ?? 0);

					$gaji_pokok = (float) ($data['gaji_pokok'] ?? 0);
					$struktural = (float) ($data['struktural'] ?? 0);
					$tunjangan_pendidikan = (float) ($data['tunjangan_pendidikan'] ?? 0);
					$wali_kelas = (float) ($data['wali_kelas'] ?? 0);

					$jumlah = isset($data['jumlah'])
						? (float) $data['jumlah']
						: ($struktural + $tunjangan_pendidikan + $wali_kelas);

					$jumlah_kotor = isset($data['jumlah_kotor'])
						? (float) $data['jumlah_kotor']
						: ($gaji_pokok + $jumlah);

					$jumlah_penerimaan = isset($data['jumlah_penerimaan'])
						? (float) $data['jumlah_penerimaan']
						: $jumlah_kotor;

					$total_jumlah_hadir += $jumlah_hadir;
					$total_gaji_pokok += $gaji_pokok;
					$total_struktural += $struktural;
					$total_tunjangan_pendidikan += $tunjangan_pendidikan;
					$total_wali_kelas += $wali_kelas;
					$total_jumlah += $jumlah;
					$total_jumlah_kotor += $jumlah_kotor;
					$total_jumlah_penerimaan += $jumlah_penerimaan;
					?>

					<tr>
						<td style="text-align: center;"><?= $no++; ?></td>
						<td class="text-left"><?= htmlspecialchars($data['nama_pegawai'] ?? '-'); ?></td>
						<td class="text-left"><?= htmlspecialchars($data['jabatan'] ?? '-'); ?></td>
                        
						<td style="text-align: center;"><?= htmlspecialchars($data['masa_kerja'] ?? '-'); ?></td>
						<td style="text-align: center;"><?= htmlspecialchars($data['pendidikan_terakhir'] ?? '-'); ?></td>
						<td class="text-right"><?= rupiah_honorarium($gaji_pokok); ?></td>
                        
						<td class="text-right"><?= rupiah_honorarium($struktural); ?></td>
						<td class="text-right"><?= rupiah_honorarium($tunjangan_pendidikan); ?></td>
						<td class="text-right"><?= rupiah_honorarium($wali_kelas); ?></td>
                        
						<td class="text-right"><?= rupiah_honorarium($jumlah); ?></td>
						<td class="text-right"><?= rupiah_honorarium($jumlah_kotor); ?></td>
						<td style="text-align: center;"><?= $jumlah_hadir; ?></td>
						<td class="text-right"><?= rupiah_honorarium($jumlah_penerimaan); ?></td>
					</tr>

				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="13" class="text-center">Data tidak tersedia.</td>
				</tr>
			<?php endif; ?>
		</tbody>

		<?php if (!empty($data_laporan)) : ?>
			<tfoot>
				<tr>
					<td colspan="3" class="text-center">JUMLAH</td>
                    
					<td class="text-center">-</td>
					<td class="text-center">-</td>
					<td class="text-right"><?= rupiah_honorarium($total_gaji_pokok); ?></td>
                    
					<td class="text-right"><?= rupiah_honorarium($total_struktural); ?></td>
					<td class="text-right"><?= rupiah_honorarium($total_tunjangan_pendidikan); ?></td>
					<td class="text-right"><?= rupiah_honorarium($total_wali_kelas); ?></td>
                    
					<td class="text-right"><?= rupiah_honorarium($total_jumlah); ?></td>
					<td class="text-right"><?= rupiah_honorarium($total_jumlah_kotor); ?></td>
					<td class="text-center"><?= $total_jumlah_hadir; ?></td>
					<td class="text-right"><?= rupiah_honorarium($total_jumlah_penerimaan); ?></td>
				</tr>
			</tfoot>
		<?php endif; ?>
	</table>

	<script>
		window.print(); 
	</script>
</body>

</html>