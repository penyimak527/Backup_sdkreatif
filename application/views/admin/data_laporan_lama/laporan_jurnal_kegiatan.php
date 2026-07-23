<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>JURNAL KEGIATAN</title>
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


</head>

<body class="body">

	<center>
		<h5>Laporan Jurnal Pegawai</h5>
	</center>

	<table style="width:100%; line-height: 1.5;" id="table1">
		<tr>
			<td style="width:7%; font-size:11px; text-transform:uppercase;"><?= $status ?></td>
			<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
			<td style="width:34%; font-size:11px;"><?php echo $judul; ?></td>
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
						$tanggal = strtotime($jurnal['tanggal']);
						$tanggal_input = strtotime($jurnal['tanggal_input']);


						$selisih = round(($tanggal_input - $tanggal) / (60 * 60 * 24));

						$selisih_tanggal = max(0, $selisih);

						if ($selisih_tanggal <= 0) {
							$selisih_tanggal = '';
						} else {
							$selisih_tanggal = ' (' . $selisih_tanggal . ' hari)';
						}
						?>
						<tr>
							<td><?= $no++ ?></td>
							<td><?= $jurnal['tanggal'] . $selisih_tanggal; ?> </td>
							<td><?= $jurnal['kegiatan'] ?></td>
							<td><?= $jurnal['semester'] ?></td>
							<td><?= $jurnal['periode'] ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="5" style="text-align: center;">Tidak ada data</td>
					</tr>
				<?php endif; ?>

			</tbody>


		</table>
	<?php else: ?>
		
		<table style="width:100%; margin-bottom:10px; margin-top:3px;" id="table2" class="grid">
  <thead>
    <tr>
      <td style="width:140px;">TANGGAL</td>
      <td>NAMA</td>
      <td>KEGIATAN</td>
      <td style="width:110px;">SEMESTER</td>
      <td style="width:110px;">PERIODE</td>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($jurnal_pegawai)): ?>
      <?php
      $tanggalKeys = array_keys($jurnal_pegawai);
      usort($tanggalKeys, fn($a, $b) =>
        (DateTime::createFromFormat('d-m-Y', $a) <=> DateTime::createFromFormat('d-m-Y', $b))
      );
      ?>

      <?php foreach ($tanggalKeys as $tgl): ?>
        <?php $items = $jurnal_pegawai[$tgl]; ?>
        <?php foreach ($items as $i => $jurnal): ?>
          <?php
          // hitung selisih hari
          $selisihTxt = '';
          $dtKeg = DateTime::createFromFormat('d-m-Y', $jurnal['tanggal']);
          $dtIn  = DateTime::createFromFormat('d-m-Y', $jurnal['tanggal_input']);
          if (!$dtIn) $dtIn = DateTime::createFromFormat('Y-m-d', $jurnal['tanggal_input']);
          if ($dtKeg && $dtIn) {
            $diff = (int)(($dtIn->getTimestamp() - $dtKeg->getTimestamp()) / 86400);
            if ($diff > 0) $selisihTxt = " ({$diff} hari)";
          }
          ?>
          <tr>
            <?php if ($i === 0): ?>
              <td rowspan="<?= count($items) ?>"><?= htmlspecialchars($tgl) ?></td>
            <?php endif; ?>
            <td><?= htmlspecialchars($jurnal['nama_pegawai'] ?? '-') ?></td>
            <td>
              <?= htmlspecialchars($jurnal['kegiatan']) ?>
              <?php if ($selisihTxt): ?><span style="color:#888;"><?= $selisihTxt ?></span><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($jurnal['semester']) ?></td>
            <td><?= htmlspecialchars($jurnal['periode']) ?></td>
          </tr>
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
	<script>
		window.print()
	</script>


</body>

</html>