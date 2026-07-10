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

	<div class="judul">
		<h2>LAPORAN REKAPITULASI GAJI GURU DAN KARYAWAN</h2>
		<h3>SD KREATIF MUHAMMADIYAH LUMAJANG</h3>
		<br>
		<p>Bulan <?= $judul; ?></p>
	</div>
<?php
$persen_tidak_hadir = $data_laporan[0]['persen_potongan_tidak_hadir'] ?? 0;
$persen_uig_uik = $data_laporan[0]['persen_uig_uik'] ?? 0;
$persen_zakat = $data_laporan[0]['persen_zakat'] ?? 0;
?>
	<table style="width:100%; margin-bottom:10px; margin-top:3px;" class="grid">
		<thead>
			<tr>
				<td>NO</td>
				<td>NAMA PESERTA</td>
				<td>GAJI POKOK</td>
				<td>STRUKTURAL</td>
				<td>BONUS</td>
				<td>JUMLAH PENDAPATAN</td>
				<td>POTONGAN TIDAK MASUK</td>
				<td>UIG/UIK 
				 <?= $persen_uig_uik > 0 ? '(' . $persen_uig_uik . '%)' : ''; ?>
				</td>
				<td>ZAKAT 
					<?= $persen_zakat > 0 ? '(' . $persen_zakat . '%)' : ''; ?>
				</td>
				<?php foreach ($master_potongan as $mp): ?>
					<td><?= strtoupper($mp['nama_potongan']); ?></td>
				<?php endforeach; ?>
				<td>POTONGAN PINJAMAN</td>
				<td>JUMLAH PENGELUARAN</td>
				<td>SISA</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$total_gaji = 0;
			$total_struktural = 0;
			$total_bonus = 0;
			$total_jumlah_pendapatan = 0;
			$total_potongan_tidak_hadir = 0;
			$total_uig_uik = 0;
			$total_zakat = 0;

			$total_potongan = [];

			foreach ($master_potongan as $mp) {
				$total_potongan[$mp['id']] = 0;
			}
			$total_potongan_pinjaman = 0;
			$total_total_pengeluaran = 0;
			$total_gaji_bersih = 0;
			$no = 1;
			if ($data_laporan):
				foreach ($data_laporan as $data):
					$hitung_tunjangan = $data['struktural'] + $data['tunjangan_pendidikan'] + $data['wali_kelas'];
					$total_gaji += $data['gaji_pokok'];
					$total_bonus += $data['bonus'];
					$total_struktural += $hitung_tunjangan;
					$total_jumlah_pendapatan += $data['total_pendapatan'];
					$total_potongan_tidak_hadir += $data['potongan_tidak_hadir'];
					$total_uig_uik += $data['uig_uik'];
					$total_zakat += $data['zakat'];
					foreach ($master_potongan as $mp) {
						$total_potongan[$mp['id']] += $data['potongan'][$mp['id']] ?? 0;
					}
					$total_potongan_pinjaman += $data['cicilan_pinjaman'];
					$total_total_pengeluaran += $data['total_pengeluaran'];
					$total_gaji_bersih += $data['gaji_bersih'];
					?>
					<tr>
						<td align="center"><?php echo $no++; ?></td>
						<td><?= $data['nama_pegawai']; ?></td>
						<td style="text-align:right;"><?= number_format($data['gaji_pokok'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($hitung_tunjangan, 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['bonus'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['total_pendapatan'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['potongan_tidak_hadir'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['uig_uik'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['zakat'], 0, ',', '.') ?></td>
						<?php foreach ($master_potongan as $mp): ?>
							<td style="text-align:right;">
								<?= number_format($data['potongan'][$mp['id']] ?? 0,0,',','.'); ?>
							</td>
						<?php endforeach; ?>
						<td style="text-align:right;"><?= number_format($data['cicilan_pinjaman'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['total_pengeluaran'], 0, ',', '.') ?></td>
						<td style="text-align:right;"><?= number_format($data['gaji_bersih'], 0, ',', '.') ?></td>
					</tr>
				<?php endforeach; else: ?>
				<tr>
					<td colspan="<?= 12 + count($master_potongan) + 3; ?>" style="text-align: center;">Gaji Belum Dihitung
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
		<?php if ($data_laporan): ?>
			<tfoot>
				<tr>
					<td colspan="2" style="text-align:center;"><b>Jumlah</b></td>
					<td style="text-align:right;"><?= number_format($total_gaji, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_struktural, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_bonus, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_jumlah_pendapatan, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_potongan_tidak_hadir, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_uig_uik, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_zakat, 0, ',', '.') ?></td>
					<?php foreach ($master_potongan as $mp): ?>
						<td style="text-align:right;">
							<?= number_format($total_potongan[$mp['id']],0,',','.'); ?>
						</td>
					<?php endforeach; ?>
					<td style="text-align:right;"><?= number_format($total_potongan_pinjaman, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_total_pengeluaran, 0, ',', '.') ?></td>
					<td style="text-align:right;"><?= number_format($total_gaji_bersih, 0, ',', '.') ?></td>
				</tr>
			</tfoot>
		<?php endif; ?>
	</table>

	<script>
		window.print(); 
	</script>
</body>

</html>