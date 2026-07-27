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
    $tgl        = date('Y-m-d', strtotime($jurnal['tanggal']));
    $tgl_input  = date('Y-m-d', strtotime($jurnal['tanggal_input']));
 
    $tanggal        = strtotime($jurnal['tanggal']);
    $tanggal_input  = strtotime($jurnal['tanggal_input']);
    $selisih        = round(($tanggal_input - $tanggal) / 86400);
    $selisih_hari   = max(0, (int)$selisih);
    $badge_selisih  = $selisih_hari > 0 ? " ({$selisih_hari} hari)" : "";
 
    if ($tgl === $tgl_input): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d-m-Y', strtotime($jurnal['tanggal'])) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($jurnal['tanggal_input'])) . $badge_selisih ?></td>
            <td><?= htmlspecialchars($jurnal['kegiatan'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($jurnal['semester'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($jurnal['periode'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php else: ?>
        <tr>
            <td><?= $no++ ?></td>  
            <td colspan="5" class="text-muted" style="text-align: center;">
                <em>Belum diisi untuk tanggal <?= date('d-m-Y', strtotime($jurnal['tanggal'])) ?>.</em>
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
		
		<table style="width:100%; margin-bottom:10px; margin-top:3px;" id="table2" class="grid">
  <thead>
    <tr>
      <td style="width:140px;">NAMA</td>
      <td style="width:140px;">Tanggal</td>
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
 
  $parseDate = function ($dateStr) {
      if (!$dateStr) return null;
      $dt = DateTime::createFromFormat('d-m-Y', $dateStr);
      if (!$dt) $dt = DateTime::createFromFormat('Y-m-d', $dateStr);
      return $dt ?: null;
  };
  ?>

  <?php foreach ($tanggalKeys as $tgl): ?>
    <?php $items = $jurnal_pegawai[$tgl]; ?>
    <?php foreach ($items as $i => $jurnal): ?>
      <?php 
      $dtKeg = $parseDate($jurnal['tanggal'] ?? null);
      $dtIn  = $parseDate($jurnal['tanggal_input'] ?? null);

      $sameDay = false;
      if ($dtKeg && $dtIn) {
          $sameDay = ($dtKeg->format('Y-m-d') === $dtIn->format('Y-m-d'));
      }
  
      $selisihTxt = '';
      if ($dtKeg && $dtIn) {
          $diffDays = (int)(($dtIn->getTimestamp() - $dtKeg->getTimestamp()) / 86400);
          if ($diffDays > 0) $selisihTxt = " ({$diffDays} hari)";
      }

      // Teks aman & terformat
      $tanggal = htmlspecialchars($jurnal['tanggal'] ?? '-', ENT_QUOTES, 'UTF-8');
      $waktu = htmlspecialchars($jurnal['waktu'] ?? '-', ENT_QUOTES, 'UTF-8');
      $kegiatan    = htmlspecialchars($jurnal['kegiatan'] ?? '-', ENT_QUOTES, 'UTF-8');
      $semester    = htmlspecialchars($jurnal['semester'] ?? '-', ENT_QUOTES, 'UTF-8');
      $periode     = htmlspecialchars($jurnal['periode'] ?? '-', ENT_QUOTES, 'UTF-8');

      $tglLabel    = htmlspecialchars($tgl, ENT_QUOTES, 'UTF-8'); // key grup, sudah d-m-Y
      $tglInputLbl = $dtIn ? $dtIn->format('d-m-Y') : '-';
      ?>
      <tr>
        <?php if ($i === 0): ?>
          <td rowspan="<?= count($items) ?>"><?= $tglLabel ?></td>
        <?php endif; ?>

        <td><?= $tanggal ?> <?= $waktu ?></td> 

        <?php if ($sameDay): ?> 
          <td>
            <?= $kegiatan ?>
            <?php if ($selisihTxt): ?><span style="color:#888;"><?= $selisihTxt ?></span><?php endif; ?>
          </td>
          <td><?= $semester ?></td>
          <td><?= $periode ?></td>
        <?php else: ?> 
          <td colspan="3" class="text-muted"  >
            <em>
              Belum diisi 
              <?php if ($dtIn): ?> (tanggal input: <?= $tglInputLbl ?><?= $selisihTxt ?>)<?php endif; ?>
            </em>
          </td>
        <?php endif; ?>
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
