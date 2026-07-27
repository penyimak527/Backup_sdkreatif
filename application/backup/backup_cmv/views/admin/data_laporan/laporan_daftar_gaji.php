<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Daftar Gaji Guru dan Karyawan</title>
</head>

<style type="text/css">
    @page {
        size: A4 portrait;
        margin: 1cm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        margin: 0;
    }

    .judul {
        margin-top : 20px;
        text-align: center;
        margin-bottom: 35px;
        line-height: 1.25;
    }

    .judul div {
        font-size: 15px;
        font-weight: bold;
    }

    .grid {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
    }

    .grid th,
    .grid td {
        border: 1px solid #000;
        padding: 5px 7px;
        font-size: 11px;
        vertical-align: middle;
    }

    .grid th {
        text-align: center;
        font-weight: bold;
    }

    .grid .no {
        width: 6%;
        text-align: center;
    }

    .grid .nama {
        width: 44%;
    }

    .grid .rekening {
        width: 20%;
        text-align: center;
    }
    .grid .isi_rekening {
        width: 20%;
        text-align: right;
    }

    .grid .nominal {
        width: 20%;
        text-align: center;
    }
    .grid .isi_nominal {
        width: 20%;
        text-align: right;
    }

    .grid tfoot td {
        font-weight: bold;
    }

    .ttd {
        width: 100%;
        margin-top: 38px;
        border-collapse: collapse;
    }

    .ttd td {
        width: 50%;
        text-align: center;
        vertical-align: top;
        font-size: 12px;
    }

    .nama-ttd {
        padding-top: 72px;
        font-weight: bold;
        text-decoration: underline;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<body>
    <div class="judul">
        <div>DAFTAR GAJI GURU DAN KARYAWAN</div>
        <div>SD KREATIF MUHAMMADIYAH 1 LUMAJANG</div>
        <div>BULAN : <?= strtoupper(htmlspecialchars($judul, ENT_QUOTES, 'UTF-8')); ?></div>
    </div>

    <table class="grid">
        <thead>
            <tr>
                <th class="no">NO</th>
                <th class="nama">NAMA</th>
                <th class="rekening">NO REKENING</th>
                <th class="nominal">NOMINAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_laporan)): ?>
                <?php $no = 1; ?>
                <?php foreach ($data_laporan as $row): ?>
                    <tr>
                        <td class="no"><?= $no++; ?></td>
                        <td class="nama"><?= htmlspecialchars($row['nama_pegawai'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="isi_rekening">
                            <?= !empty($row['no_rekening'])
                                ? htmlspecialchars($row['no_rekening'], ENT_QUOTES, 'UTF-8')
                                : '-'; ?>
                        </td>
                        <td class="isi_nominal">
                            <?= number_format((float) ($row['gaji_bersih'] ?? 0), 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">Data penggajian belum tersedia</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:center;">JUMLAH</td>
                <td class="isi_nominal">
                    <?= number_format((float) $total_gaji_bersih, 0, ',', '.'); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
            </td>
            <td>
                Bendahara
            </td>
        </tr>
        <tr>
            <td class="nama-ttd">
                Dimas Doddy Priyambodho, S.Ag, M.Pd
            </td>
            <td class="nama-ttd">
                Nurlaili Budi Indahwati, S.Psi
            </td>
        </tr>
    </table>

    <script>
        window.print();
    </script>
</body>

</html>
