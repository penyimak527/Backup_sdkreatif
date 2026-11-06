<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Tagihan Per Siswa</title>
    <style>
        @page { size: A4 landscape; margin: 11mm; }
        * { box-sizing: border-box; }
        body { font: 10px Arial, Helvetica, sans-serif; color: #111; margin: 0; }
        h1 { text-align: center; font-size: 16px; margin: 0 0 12px; }
        h2 { font-size: 12px; margin: 16px 0 6px; }
        .identitas { border-collapse: collapse; margin-bottom: 10px; }
        .identitas td { padding: 2px 10px 2px 0; vertical-align: top; }
        .identitas .nama-label { font-weight: bold; width: 105px; }
        .ringkasan { border-collapse: collapse; width: 100%; margin: 8px 0 13px; table-layout: fixed; }
        .ringkasan th,.ringkasan td { border: 1px solid #777; padding: 6px; text-align: left; }
        .ringkasan th { background: #f0f0f0; font-size: 9px; }
        .ringkasan td { font-size: 12px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin: 4px 0 12px; }
        .data th,.data td { border: 1px solid #555; padding: 5px 6px; vertical-align: top; }
        .data th { background: #f2f2f2; font-size: 9px; text-align: center; }
        .data td { font-size: 9px; }
        .angka { text-align: right; white-space: nowrap; }
        .tengah { text-align: center; }
        .no-data { text-align: center; color: #555; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        thead { display: table-header-group; }
        .per-siswa + .per-siswa { page-break-before: always; break-before: page; }
        .catatan { font-size: 9px; color: #555; margin-top: 8px; }
    </style>
</head>
<body>
<?php
$rupiah = function ($nilai) { return 'Rp' . number_format((float) $nilai, 0, ',', '.'); };
?>
<?php if (empty($siswa_data)): ?>
    <h1>LAPORAN TAGIHAN PER SISWA</h1>
    <p>Tahun Ajaran: <?= html_escape($tahun_ajaran) ?></p>
    <p class="no-data">Tidak ada data tagihan atau pembayaran untuk pilihan ini.</p>
<?php else: ?>
    <?php foreach ($siswa_data as $siswa): ?>
        <section class="per-siswa">
            <h1>LAPORAN TAGIHAN PER SISWA</h1>
            <table class="identitas">
                <tr><td class="nama-label">Tahun Ajaran</td><td>:</td><td><?= html_escape($tahun_ajaran) ?></td></tr>
                <tr><td class="nama-label">Nama Siswa</td><td>:</td><td><?= html_escape($siswa['nama_siswa']) ?></td></tr>
                <tr><td class="nama-label">NIS</td><td>:</td><td><?= html_escape($siswa['nis']) ?></td></tr>
                <tr><td class="nama-label">Kelas</td><td>:</td><td><?= html_escape($siswa['nama_kelas']) ?></td></tr>
            </table>
            <table class="ringkasan">
                <thead><tr><th>Total Tagihan</th><th>Total Pembayaran</th><th>Total Sisa Tagihan</th><th>Tunggakan Tahun Sebelumnya</th></tr></thead>
                <tbody><tr>
                    <td><?= $rupiah($siswa['ringkasan']['total_tagihan']) ?></td>
                    <td><?= $rupiah($siswa['ringkasan']['total_pembayaran']) ?></td>
                    <td><?= $rupiah($siswa['ringkasan']['total_sisa']) ?></td>
                    <td><?= $rupiah($siswa['ringkasan']['tunggakan_sebelumnya']) ?></td>
                </tr></tbody>
            </table>

            <h2>A. Seluruh Tagihan Siswa (Tahun Ajaran <?= html_escape($tahun_ajaran) ?>)</h2>
            <table class="data">
                <thead><tr><th>No</th><th>Nama Tagihan</th><th>Jenis Tagihan</th><th>Bulan</th><th>Status</th><th>Tagihan</th><th>Dibayar</th><th>Sisa</th></tr></thead>
                <tbody>
                <?php foreach ($siswa['tagihan'] as $i => $row): ?>
                    <tr>
                        <td class="tengah"><?= $i + 1 ?></td>
                        <td><?= html_escape($row['nama_tagihan']) ?></td>
                        <td><?= html_escape($row['nama_jenis_tagihan']) ?></td>
                        <td><?= html_escape($row['nama_bulan'] ?: '-') ?></td>
                        <td><?= html_escape($row['status_pembayaran']) ?></td>
                        <td class="angka"><?= $rupiah($row['target_efektif']) ?></td>
                        <td class="angka"><?= $rupiah($row['total_pembayaran']) ?></td>
                        <td class="angka"><?= $rupiah($row['sisa_efektif']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswa['tagihan'])): ?><tr><td colspan="8" class="no-data">Tidak ada tagihan pada tahun ajaran ini.</td></tr><?php endif; ?>
                </tbody>
            </table>

            <h2>B. Seluruh Pembayaran Siswa</h2>
            <table class="data">
                <thead><tr><th>No</th><th>Tanggal</th><th>No Transaksi</th><th>Nama Tagihan</th><th>Bulan</th><th>Metode</th><th>Nominal Pembayaran</th></tr></thead>
                <tbody>
                <?php foreach ($siswa['pembayaran'] as $i => $row): ?>
                    <tr>
                        <td class="tengah"><?= $i + 1 ?></td>
                        <td><?= html_escape($row['tanggal_transaksi']) ?></td>
                        <td><?= html_escape($row['no_transaksi']) ?></td>
                        <td><?= html_escape($row['nama_tagihan']) ?></td>
                        <td><?= html_escape($row['nama_bulan'] ?: '-') ?></td>
                        <td><?= html_escape($row['nama_metode_pembayaran']) ?></td>
                        <td class="angka"><?= $rupiah($row['nominal_bayar']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswa['pembayaran'])): ?><tr><td colspan="7" class="no-data">Belum ada pembayaran aktif.</td></tr><?php endif; ?>
                </tbody>
            </table>

            <h2>C. Riwayat Cicilan</h2>
            <table class="data">
                <thead><tr><th>No</th><th>Tanggal</th><th>No Transaksi</th><th>Nama Tagihan</th><th>Bulan</th><th>Cicilan Ke</th><th>Metode</th><th>Nominal Cicilan</th><th>Sisa Setelah Bayar</th></tr></thead>
                <tbody>
                <?php foreach ($siswa['cicilan'] as $i => $row): ?>
                    <tr>
                        <td class="tengah"><?= $i + 1 ?></td>
                        <td><?= html_escape($row['tanggal_transaksi']) ?></td>
                        <td><?= html_escape($row['no_transaksi']) ?></td>
                        <td><?= html_escape($row['nama_tagihan']) ?></td>
                        <td><?= html_escape($row['nama_bulan'] ?: '-') ?></td>
                        <td class="tengah"><?= (int) $row['cicilan_ke'] ?></td>
                        <td><?= html_escape($row['nama_metode_pembayaran']) ?></td>
                        <td class="angka"><?= $rupiah($row['nominal_bayar']) ?></td>
                        <td class="angka"><?= $rupiah($row['sisa_setelah']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswa['cicilan'])): ?><tr><td colspan="9" class="no-data">Tidak ada riwayat cicilan.</td></tr><?php endif; ?>
                </tbody>
            </table>

            <h2>D. Tunggakan Tahun Ajaran Sebelumnya</h2>
            <table class="data">
                <thead><tr><th>No</th><th>Tahun Asal</th><th>Kelas Asal</th><th>Nama Tagihan</th><th>Jatuh Tempo</th><th>Total Tunggakan</th></tr></thead>
                <tbody>
                <?php foreach ($siswa['tunggakan_sebelumnya'] as $i => $row): ?>
                    <tr>
                        <td class="tengah"><?= $i + 1 ?></td>
                        <td><?= html_escape($row['tahun_asal']) ?></td>
                        <td><?= html_escape($row['nama_kelas']) ?></td>
                        <td><?= html_escape($row['nama_tagihan']) ?></td>
                        <td><?= html_escape($row['tanggal_jatuh_tempo'] ?: '-') ?></td>
                        <td class="angka"><?= $rupiah($row['sisa_tagihan']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswa['tunggakan_sebelumnya'])): ?><tr><td colspan="6" class="no-data">Tidak ada tunggakan tahun ajaran sebelumnya.</td></tr><?php endif; ?>
                </tbody>
            </table>
            <!-- <div class="catatan">Ringkasan Tahun Ajaran terpilih tidak mencampurkan tunggakan tahun sebelumnya. Pembayaran yang ditampilkan hanya transaksi aktif. Riwayat cicilan merupakan rincian dari pembayaran yang sama, bukan pembayaran tambahan; tunggakan mengikuti tanggal jatuh tempo dan ketentuan dianggap sebagai tunggakan.</div> -->
        </section>
    <?php endforeach; ?>
<?php endif; ?>
<script> window.print();</script>
</body>
</html>
