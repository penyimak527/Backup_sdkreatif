

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>JURNAL PRESENSI PER PEGAWAI</title>
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
    	color : black;
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

    .grid tfoot td{
    	background: white;
    	vertical-align: middle;
    	color : black;
        text-align: center;
        height: 20px;
    }

   .footer{
    position:absolute;
    /* right:0; */
    bottom:0;
  }
  </style>
  <body class="body">
    <center><h2>Laporan Presensi Per Pegawai</h2></center>
    <div class="clear:both;"></div>
    <br>
    <table style="width: 100%;">
      <tbody>
        <tr>
          <td style="width: 15%;"><?php echo $status?></td>
          <td style="width: 2%;">:</td>
          <td><?php echo $judul; ?></td>
        </tr>
      </tbody>
    </table>
    <br>
    <div class="clear:both;"></div>
    <table style="width: 100%;" class="grid">
      <thead>
		  <th style="text-align: center;">Nama</th>
		  <th style="text-align: center;">Tanggal</th>
        <th style="text-align: center;">Jam Masuk</th>
        <th style="text-align: center;">Menit Terlambat</th>
        <th style="text-align: center;">Jam Pulang</th>
        <th style="text-align: center;">Status</th>
      </thead>
      <tbody>
        <?php foreach ($grouped_by_tanggal as $r): ?>
          <?php if ($r['status'] === '0'): ?>
            <tr>
              <td colspan="7" style="text-align: center;">Tanggal <?php echo $r['tanggal']; ?> Kosong</td>
            </tr>
          <?php else: ?>
            <tr>
              <td><?php echo $r['nama_pegawai']; ?></td>
              <td><?php echo $r['tanggal']; ?></td>
              <td><?php echo $r['jam_masuk']; ?></td>
              <td><?php echo $r['jam_terlambat']; ?></td>
              <td><?= empty($r['jam_pulang']) ? '-' : $r['jam_pulang']; ?></td>
              <td><?php echo $r['status_absen'] ?? '-';?></td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
    <script>
      window.print();
      // window.onfocus = function () { window.close(); }
    </script>
  </body>
</html>
