<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kartu QR Siswa</title>
<style>
*{box-sizing:border-box}html,body{margin:0;padding:0;font-family:Arial,sans-serif;color:#111}.toolbar{position:sticky;top:0;z-index:5;display:flex;align-items:center;gap:12px;padding:12px 16px;background:#fff;border-bottom:1px solid #ddd}.toolbar button{padding:8px 14px;border:0;border-radius:5px;background:#1769aa;color:#fff;font-weight:700;cursor:pointer}.toolbar span{font-size:13px;color:#555}.sheet{padding:8mm;background:#eceff2}.card-pair{display:flex;gap:4mm;width:112mm;margin:0 auto 5mm;break-inside:avoid;page-break-inside:avoid}.card-page{position:relative;flex:0 0 54mm;width:54mm;height:85.6mm;overflow:hidden;background-color:#fff;background-position:center;background-repeat:no-repeat;background-size:100% 100%;box-shadow:0 1mm 3mm rgba(0,0,0,.12)}.front{background-image:url('<?=html_escape($front_image)?>')}.back{background-image:url('<?=html_escape($back_image)?>')}.identity{position:absolute;left:11.5%;top:28.5%;width:46%;height:5%;padding:0;text-align:left;color:#24548a;font-size:7.2pt;font-weight:700;line-height:1.05;white-space:nowrap;overflow:hidden}.qr{position:absolute;left:34.2%;top:49.4%;width:53.8%;height:35.8%;padding:0;background:#fff;text-align:center;overflow:hidden}.qr svg{display:block;width:100%;height:100%;margin:0}.empty{padding:30px;text-align:center;background:#fff}
<?php if(!empty($pdf_mode)):?>
@page{size:54mm 85.6mm;margin:0}.sheet{display:block;padding:0;background:#fff}.card-pair{display:block;width:54mm;margin:0}.card-page{display:block;box-shadow:none;page-break-after:always}.card-pair:last-child .back{page-break-after:auto}
<?php else:?>
@page{size:A4 portrait;margin:8mm}@media print{.toolbar{display:none}.sheet{padding:0;background:#fff}.card-pair{margin-bottom:5mm}.card-page{box-shadow:none;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
<?php endif;?>
</style>
</head>
<body>
<?php if(empty($pdf_mode)):?><div class="toolbar"><button type="button" onclick="window.print()">Cetak Kartu</button><span>Gunakan skala 100%, kertas A4, dan aktifkan background graphics agar desain kartu tercetak.</span></div><?php endif;?>
<main class="sheet">
    <?php if(!$rows):?><div class="empty">Data QR siswa tidak ditemukan.</div><?php endif;?>
    <?php foreach($rows as $row):?>
        <div class="card-pair" data-nis="<?=html_escape($row['nis'])?>">
            <section class="card-page front"><div class="identity"><?=html_escape($row['nama_lengkap'])?></div><div class="qr"><?=$row['qr_svg']?></div></section>
            <section class="card-page back"></section>
        </div>
    <?php endforeach;?>
</main>
</body>
</html>
