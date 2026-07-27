<!DOCTYPE html>
<html>

<head>
    <title><?= $title ?></title>
    <style>
        @page {
            /* size: landscape; */
            margin: 0.5cm;
        }

        .body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .slip {
            width: calc(50% - 20px);
            border: 1px solid #000;
            box-sizing: border-box;
            margin-left: 7px;
            margin-right: 7px;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        /* .container {
            width: 100%;
        }

        .slip {
            width: 48%;
            margin: 5px;
            border: 1px solid #000;
            display: inline-block;
            vertical-align: top;
            margin-bottom: 15px;
        } */

        .slip table {
            width: 100%;
            border-collapse: collapse;
        }

        .slip td,
        .slip th {
            border: 1px solid #000;
            padding: 3px;
        }

        .no-border td {
            border: none !important;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
            }

            /* .slip {
                page-break-inside: avoid;
            } */
        }
    </style>
</head>
<?php
$bulan_nama = [
    "01" => "Januari",
    "02" => "Februari",
    "03" => "Maret",
    "04" => "April",
    "05" => "Mei",
    "06" => "Juni",
    "07" => "Juli",
    "08" => "Agustus",
    "09" => "September",
    "10" => "Oktober",
    "11" => "November",
    "12" => "Desember"
];
?>

<body>
    <center>
        <h2>Cetak Slip Gaji</h2>
    </center>
    <table style="width:100%;">
        <tr>
            <td style="width:7%; font-size:11px; text-transform:uppercase;"><?= $status ?></td>
            <td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
            <td style="width:34%; font-size:11px;"><?php echo $judul; ?></td>
        </tr>
    </table>
    <div class="container">
        <?php if (!empty($slip_gaji)): ?>
            <?php foreach ($slip_gaji as $gaji):
                $gaji_struktural = (int) $gaji['struktural'];
                $tunjangan_pendidikan = (int) $gaji['tunjangan_pendidikan'];
                $wali_kelas = (int) $gaji['wali_kelas'];
                $bonus = (int) $gaji['bonus'];
                $persen_uig_uik = (float) $gaji['persen_uig_uik'];
                $persen_zakat = (float) $gaji['persen_zakat'];
                $hitung_tunjangan = $gaji_struktural + $tunjangan_pendidikan + $wali_kelas;
                ?>
                <div class="slip">
                    <table>
                        <tr class="no-border">
                            <td width="80">Nama</td>
                            <td width="10">:</td>
                            <td>
                                <b><?= $gaji['nama_pegawai'] ?></b>
                            </td>
                        </tr>
                        <tr class="no-border">
                            <td>Gaji</td>
                            <td>:</td>
                            <td><?= $bulan_nama[$gaji['bulan']] ?>         <?= $gaji['tahun'] ?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th width="40">No.</th>
                            <th>Uraian</th>
                            <th width="120">Jumlah</th>
                        </tr>
                        <tr>
                            <td colspan="3" class="bold">PENDAPATAN</td>
                        </tr>
                        <tr>
                            <td class="text-center">1</td>
                            <td>Gaji Pokok</td>
                            <td class="text-right">
                                <?= number_format($gaji['gaji_pokok'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td>Struktural</td>
                            <td class="text-right">
                                <?= number_format($hitung_tunjangan, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">3</td>
                            <td>Bonus</td>
                            <td class="text-right">
                                <?= number_format($bonus, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr class="bold">
                            <td colspan="2" class="text-right">
                                Jumlah Pendapatan
                            </td>
                            <td class="text-right">
                                <?= number_format($gaji['total_pendapatan'] + $bonus, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="bold">PENGELUARAN</td>
                        </tr>
                        <tr>
                            <td class="text-center">1</td>
                            <td>Potongan Tidak Masuk</td>
                            <td class="text-right">
                                <?= number_format($gaji['potongan_tidak_hadir'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td>UIG/UIK <?= $persen_uig_uik > 0 ? '(' . $persen_uig_uik . '%)' : ''; ?></td>
                            <td class="text-right">
                                <?= number_format($gaji['uig_uik'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">3</td>
                            <td>Zakat <?= $persen_zakat > 0 ? '(' . $persen_zakat . '%)' : ''; ?></td>
                            <td class="text-right">
                                <?= number_format($gaji['zakat'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php
                        $no = 4;

                        if (!empty($gaji['potongan_detail'])):
                            foreach ($gaji['potongan_detail'] as $potongan):
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= strtoupper($potongan['nama_potongan']) ?></td>
                                    <td class="text-right">
                                        <?= number_format($potongan['nominal'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>Potongan Pinjaman</td>
                            <td class="text-right">
                                <?= number_format($gaji['cicilan_pinjaman'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr class="bold">
                            <td colspan="2" class="text-right">Jumlah Pengeluaran</td>
                            <td class="text-right"><?= number_format($gaji['total_pengeluaran'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="bold">
                            <td colspan="2">Sisa</td>
                            <td class="text-right"><?= number_format($gaji['gaji_bersih'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="bold">
                            <td colspan="2">Sisa Angsuran</td>
                            <td class="text-right"><?= number_format($gaji['sisa_pinjaman'], 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style=" display: flex; justify-content: center; text-align:center; ">
                Gaji Belum Dihitung
            </div>
        <?php endif; ?>
    </div>
    <script>
        window.print();
    </script>
</body>

</html>