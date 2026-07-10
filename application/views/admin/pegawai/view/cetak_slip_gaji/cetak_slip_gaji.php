<!DOCTYPE html>
<html>

<head>
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .container {
            width: 100%;
        }

        .slip {
            width: 48%;
            margin: 5px;
            border: 1px solid #000;
            display: inline-block;
            vertical-align: top;
            margin-bottom: 15px;
        }

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

            .slip {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<?php
$bulan_nama = [
    "01"=>"Januari", "02"=>"Februari", "03"=>"Maret", "04"=>"April", "05"=>"Mei", "06"=>"Juni","07"=>"Juli",
    "08"=>"Agustus", "09"=>"September", "10"=>"Oktober", "11"=>"November", "12"=>"Desember"];
?>
<body>
    <div class="container">
        <?php foreach ($slip_gaji as $gaji):
            $gaji_struktural = (int) $gaji['struktural'];
            $tunjangan_pendidikan = (int) $gaji['tunjangan_pendidikan'];
            $wali_kelas = (int) $gaji['wali_kelas'];
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
                        <td><?= $bulan_nama[$gaji['bulan']] ?> <?= $gaji['tahun'] ?></td>
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
                    <tr class="bold">
                        <td colspan="2" class="text-right">
                            Jumlah Pendapatan
                        </td>
                        <td class="text-right">
                            <?= number_format($gaji['total_pendapatan'], 0, ',', '.') ?>
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
                        <td>UIG/UIK 1%</td>
                        <td class="text-right">
                            <?= number_format($gaji['uig_uik'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td>Zakat 1,5%</td>
                        <td class="text-right">
                            <?= number_format($gaji['zakat'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td>SPP SISWA</td>
                        <td class="text-right">
                            <?= number_format($gaji['spp_siswa'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">5</td>
                        <td>DAFTAR ULANG</td>
                        <td class="text-right">
                            <?= number_format($gaji['daftar_ulang'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">6</td>
                        <td>ARISAN</td>
                        <td class="text-right">
                            <?= number_format($gaji['arisan'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">7</td>
                        <td>Angsuran Pinjaman</td>
                        <td class="text-right">
                            <?= number_format($gaji['angsuran_pinjaman'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">8</td>
                        <td>BPJS</td>
                        <td class="text-right">
                            <?= number_format($gaji['bpjs'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">9</td>
                        <td>Lain-lain</td>
                        <td class="text-right">
                            <?= number_format($gaji['lain_lain'], 0, ',', '.') ?>
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
                </table>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        window.print();
    </script>
</body>

</html>