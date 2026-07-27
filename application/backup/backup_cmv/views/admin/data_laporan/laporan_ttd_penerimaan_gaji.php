<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penggunaan Anggaran </title>
</head>
<style type="text/css" media="print">
    @page {
        /* size: landscape; */
        margin: 1cm;
    }

    .body {
        font-family: Arial, Helvetica, sans-serif;
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
        background: black;
        /* background: white; */
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

    .ttd {
        width: 100%;
        margin-top: 50px;
    }

    .ttd td {
        text-align: center;
        vertical-align: top;
        font-size: 12px;
    }

    .nama-ttd {
        margin-top: 80px;
        font-weight: 700;
        /* text-decoration: underline; */
    }
</style>

<body class="body">

  <!-- KOP LAPORAN -->
<div style="
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:5px;
">

    <!-- LOGO -->
    <div style="margin-right:10px;">
        <img src="<?= base_url('assets/Picture1.jpg') ?>"
             style="width:80px;height:auto;">
    </div>

    <!-- TEKS -->
    <div style="text-align:center;line-height:1.1;">
        <div style="font-size:12pt;font-weight:bold;">
            MAJELIS PENDIDIKAN DASAR DAN MENENGAH
        </div>

        <div style="font-size:12pt;font-weight:bold;">
            PIMPINAN DAERAH MUHAMMADIYAH LUMAJANG
        </div>

        <div style="font-size:15pt;font-weight:bold;">
            SD Kreatif MUHAMMADIYAH LUMAJANG
        </div>

        <div style="font-size:10pt;">
            Jl. Brantas No. 7 Lumajang 67315 Telp. (0334) 894619
        </div>
    </div>

</div>

<div style="margin-bottom:10px;">
    <div style="border-top:1px solid #000;"></div>
    <div style="border-top:2px solid #000;margin-top:1px;"></div>
</div>

    <table class="grid" width="100%">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="35%">NAMA</th>
                <th width="30%">JABATAN</th>
                <th width="35%">TANDA TANGAN</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $no = 1;
            foreach ($penerimaan_gaji as $row):
                ?>
                <tr>
                    <td style="text-align:center;">
                        <?= $no; ?>
                    </td>

                    <td>
                        <?= $row['nama_pegawai']; ?>
                    </td>

                    <td>
                        <?= $row['jabatan']; ?>
                    </td>

                    <td style="padding:0;">
    <table width="100%" style="border:none;">
        <tr>
            <?php if ($no % 2 == 1) : ?>
                <td style="border:none;width:50%;text-align:left;">
                    <?= $no ?>
                </td>
                <td style="border:none;width:50%;"></td>
            <?php else : ?>
                <td style="border:none;width:50%;"></td>
                <td style="border:none;width:50%;text-align:left;">
                    <?= $no ?>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</td>
                </tr>
                <?php
                $no++;
            endforeach;
            ?>
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <table class="ttd">
        <tr>
            <td><br>Mengetahui
        <br>Kepala Sekolah</td>
            <td>
                Lumajang, <?= $tanggal_laporan ?><br>
                Bendahara
            </td>
        </tr>

        <tr>
            <td class="nama-ttd">
                <br><br><br><br>
                Dimas Doddy Priyambodho, S.Ag, M.Pd
            </td>

            <td class="nama-ttd">
                <br><br><br><br>
                Nurlaili Budi Indahwati, S.Psi
            </td>
        </tr>
    </table>

    <script>
        window.print(); 
    </script>
</body>

</html>