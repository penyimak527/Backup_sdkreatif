<?php
$format_rupiah = function ($n) { return 'Rp' . number_format((float)$n, 0, ',', '.'); };
$f = is_array($format ?? null) ? $format : array();
$cfg = is_array($format_config ?? null) ? $format_config : array();

$paper = $f['ukuran_kertas'] ?? 'A4';
$orientation = strtolower($f['orientasi'] ?? 'Portrait');
$width = (float)($f['lebar_kertas'] ?? 0);
$height = (float)($f['tinggi_kertas'] ?? 0);
if ($paper === 'Custom' && $width > 0 && $height > 0) {
    if ($orientation === 'landscape') { $tmp = $width; $width = $height; $height = $tmp; }
    $pageSize = $width . 'mm ' . $height . 'mm';
} elseif (in_array($paper, array('A4', 'A5'), true)) {
    $pageSize = $paper . ' ' . ($orientation === 'landscape' ? 'landscape' : 'portrait');
} else {
    $pageSize = ($paper === '80mm' ? '80mm auto' : '58mm auto');
}

$marginTop = (float)($cfg['margin_atas'] ?? 15);
$marginBottom = (float)($cfg['margin_bawah'] ?? 15);
$marginLeft = (float)($cfg['margin_kiri'] ?? 15);
$marginRight = (float)($cfg['margin_kanan'] ?? 15);
$title = trim((string)($f['judul'] ?? '')) ?: 'SURAT PEMBERITAHUAN TUNGGAKAN';
$schoolName = trim((string)($cfg['nama_sekolah'] ?? ''));
$schoolAddress = trim((string)($cfg['alamat_sekolah'] ?? ''));
$schoolPhone = trim((string)($cfg['telepon_sekolah'] ?? ''));
$showLogo = ($cfg['tampilkan_logo'] ?? 'Tidak') === 'Ya';
$logo = trim((string)($cfg['logo_sekolah'] ?? ''));
$signName = trim((string)($f['nama_penandatangan'] ?? '')) ?: ($header['nama_penandatangan'] ?? '');
$signRole = trim((string)($f['jabatan_penandatangan'] ?? '')) ?: ($header['jabatan_penandatangan'] ?? '');
$pos = $cfg['posisi_tanda_tangan'] ?? 'Kanan';
$align = $pos === 'Kiri' ? 'left' : ($pos === 'Tengah' ? 'center' : 'right');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= html_escape($header['no_surat']) ?></title>
<style>
@page{size:<?= html_escape($pageSize) ?>;margin:<?= $marginTop ?>mm <?= $marginRight ?>mm <?= $marginBottom ?>mm <?= $marginLeft ?>mm}
*{box-sizing:border-box}body{font:14px 'Times New Roman',serif;line-height:1.5;color:#111;margin:0}.paper{width:100%;max-width:800px;margin:auto}.center{text-align:center}.right{text-align:right}.header{border-bottom:3px double #111;padding-bottom:10px;margin-bottom:18px}.identity{display:flex;align-items:center;justify-content:center;gap:12px}.identity img{max-width:70px;max-height:70px;object-fit:contain}.identity-text{text-align:center}.identity-name{font-size:18px;font-weight:700}.header-text,.footer-text{white-space:pre-line}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{border:1px solid #444;padding:6px}.signature{margin-top:28px;text-align:<?= $align ?>}.signature-inner{display:inline-block;min-width:240px;text-align:center}.btn{padding:8px 15px;border:0;background:#1677ff;color:#fff;border-radius:5px;cursor:pointer}.muted{color:#555}@media print{html,body{width:100%;margin:0;padding:0}.paper{width:100%;max-width:none;margin:0}.no-print{display:none!important}}
</style>
</head>
<body><div class="paper">
<div class="header center">
    <?php if (($showLogo && $logo) || $schoolName || $schoolAddress || $schoolPhone): ?>
    <div class="identity">
        <?php if ($showLogo && $logo): ?><img src="<?= html_escape(base_url(ltrim($logo, '/'))) ?>" alt="Logo"><?php endif; ?>
        <div class="identity-text">
            <?php if ($schoolName): ?><div class="identity-name"><?= html_escape($schoolName) ?></div><?php endif; ?>
            <?php if ($schoolAddress): ?><div><?= html_escape($schoolAddress) ?></div><?php endif; ?>
            <?php if ($schoolPhone): ?><div>Telp. <?= html_escape($schoolPhone) ?></div><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <h2 style="margin:<?= ($schoolName || $logo) ? '10px 0 0' : '0' ?>"><?= html_escape($title) ?></h2>
    <?php if (!empty($f['header_text'])): ?><div class="header-text muted"><?= nl2br(html_escape($f['header_text'])) ?></div><?php endif; ?>
</div>
<table style="margin:0 0 15px"><tr><td style="border:0;width:100px">Nomor</td><td style="border:0">: <?= html_escape($header['no_surat']) ?></td><td style="border:0" class="right"><?= html_escape($header['tanggal_surat']) ?></td></tr><tr><td style="border:0">Perihal</td><td colspan="2" style="border:0">: Pemberitahuan Tunggakan</td></tr></table>
<p>Yth. Bapak/Ibu Wali dari <strong><?= html_escape($header['nama_siswa']) ?></strong> (<?= html_escape($header['nama_kelas'] ?: '-') ?>)</p>
<p>Dengan hormat, berdasarkan catatan administrasi sekolah, terdapat tagihan yang belum diselesaikan sebagai berikut:</p>
<table><thead><tr><th>No</th><th>Tagihan</th><th>Periode</th><th class="right">Nominal</th><th class="right">Dibayar</th><th class="right">Sisa</th></tr></thead><tbody>
<?php foreach ($detail as $i => $d): ?><tr><td><?= $i + 1 ?></td><td><?= html_escape($d['nama_tagihan']) ?></td><td><?= html_escape(trim(($d['nama_bulan'] ?: '') . ' ' . $d['tahun'])) ?></td><td class="right"><?= $format_rupiah($d['nominal_tagihan']) ?></td><td class="right"><?= $format_rupiah($d['nominal_dibayar']) ?></td><td class="right"><?= $format_rupiah($d['sisa_tagihan']) ?></td></tr><?php endforeach; ?>
<tr><th colspan="5" class="right">Total Tunggakan</th><th class="right"><?= $format_rupiah($header['total_tunggakan']) ?></th></tr></tbody></table>
<?php if (!empty($header['catatan_surat'])): ?><p><?= nl2br(html_escape($header['catatan_surat'])) ?></p><?php endif; ?>
<p>Demikian pemberitahuan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami mengucapkan terima kasih.</p>
<div class="signature"><div class="signature-inner"><div><?= html_escape($signRole ?: 'Bendahara') ?></div><div style="height:70px"></div><strong><u><?= html_escape($signName ?: '-') ?></u></strong></div></div>
<?php if (!empty($f['footer_text'])): ?><div class="footer-text center muted" style="margin-top:25px;border-top:1px solid #ccc;padding-top:8px"><?= nl2br(html_escape($f['footer_text'])) ?></div><?php endif; ?>
<div class="center no-print" style="margin-top:30px"><button class="btn" onclick="window.print()">Cetak / Simpan PDF</button></div>
</div></body></html>
