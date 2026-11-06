<?php
defined('BASEPATH') or exit('No direct script access allowed');
$r = $ringkasan;
$money = static function ($value) { return 'Rp '.number_format((int) $value, 0, ',', '.'); };
$pengaturan = isset($topup['pengaturan']) && is_array($topup['pengaturan']) ? $topup['pengaturan'] : null;
$rekening = isset($topup['rekening']) && is_array($topup['rekening']) ? $topup['rekening'] : array();
$topup_aktif = $anak && $pengaturan && strtoupper(trim((string) $pengaturan['status_layanan'])) === 'AKTIF'
    && !empty($rekening) && !empty($topup['whatsapp']);
?>

<style>
.wali-dashboard .hero { background: linear-gradient(135deg,#0d4b82 0%,#1264a7 100%); border-radius: .85rem; color:#fff; overflow:hidden; }
.wali-dashboard .hero .balance { font-size:2.1rem; font-weight:800; letter-spacing:-.03em; }
.wali-dashboard .quick-link { min-height:92px; border:1px solid rgba(255,255,255,.25); background:rgba(255,255,255,.12); color:#fff; border-radius:.75rem; }
.wali-dashboard .quick-link:hover { background:rgba(255,255,255,.2); color:#fff; }
.wali-dashboard .metric-card { height:100%; border:0; box-shadow:0 .2rem .8rem rgba(42,55,74,.08); }
.wali-dashboard .metric-icon { width:46px;height:46px;display:grid;place-items:center;border-radius:.65rem;background:#e7f2ff;color:#1769aa;font-size:1.35rem; }
.wali-dashboard .transaction-row { display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 0;border-bottom:1px solid var(--ct-border-color); }
.wali-dashboard .transaction-row:last-child { border-bottom:0; }
.topup-account { border:1px solid #d7e2ee;border-radius:.75rem;background:#f8fbff;padding:1rem;text-align:center; }
@media(max-width:575.98px){.wali-dashboard .hero .balance{font-size:1.7rem}.wali-dashboard .quick-link{min-height:78px}}
</style>

<div class="wali-dashboard">
    <?php $this->load->view('wali/dompet/_pilih_anak', array('anak_list' => $anak_list, 'anak' => $anak)); ?>

    <?php if (!$anak): ?>
        <div class="alert alert-info"><i class="ri-information-line me-1"></i> Belum ada anak aktif yang terhubung dengan akun Wali ini.</div>
    <?php else: ?>
        <div class="hero p-3 p-md-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <div class="text-white-50 mb-1">Saldo <?= html_escape($anak['nama_siswa']) ?></div>
                    <div class="balance"><?= $money($anak['saldo']) ?></div>
                    <span class="badge bg-light text-primary mt-2"><?= html_escape($anak['status_dompet'] ?: 'BELUM AKTIF') ?></span>
                </div>
                <div class="text-md-end">
                    <div class="fw-semibold"><?= html_escape($anak['nama_siswa']) ?></div>
                    <div class="text-white-50"><?= html_escape($anak['nis']) ?><?= $anak['nama_kelas'] ? ' · '.html_escape($anak['nama_kelas']) : '' ?></div>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-6 col-lg-3"><button type="button" class="btn quick-link w-100 d-flex flex-column align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modal-topup" <?= $topup_aktif ? '' : 'disabled' ?>><i class="ri-add-circle-line fs-3"></i><span>Topup Sekarang</span></button></div>
                <div class="col-6 col-lg-3"><a class="btn quick-link w-100 d-flex flex-column align-items-center justify-content-center" href="<?= base_url('wali/dompet/history-belanja?id_siswa='.(int) $anak['id_siswa']) ?>"><i class="ri-shopping-bag-3-line fs-3"></i><span>History Belanja</span></a></div>
                <div class="col-6 col-lg-3"><a class="btn quick-link w-100 d-flex flex-column align-items-center justify-content-center" href="<?= base_url('wali/dompet/history-isi-saldo?id_siswa='.(int) $anak['id_siswa']) ?>"><i class="ri-history-line fs-3"></i><span>History Isi Saldo</span></a></div>
                <div class="col-6 col-lg-3"><a class="btn quick-link w-100 d-flex flex-column align-items-center justify-content-center" href="<?= base_url('wali/dompet/setting-limit?id_siswa='.(int) $anak['id_siswa']) ?>"><i class="ri-equalizer-line fs-3"></i><span>Setting Limit</span></a></div>
            </div>
        </div>

        <?php if (!$topup_aktif): ?>
            <div class="alert alert-warning">Topup belum dapat diajukan. Pastikan layanan, rekening tujuan, dan nomor WhatsApp admin sudah aktif.</div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center"><div class="metric-icon"><i class="ri-shopping-cart-line"></i></div><div><div class="text-muted">Belanja bulan ini</div><h4 class="mb-0"><?= $money($r['belanja_bulan_ini']['total']) ?></h4><small><?= (int) $r['belanja_bulan_ini']['jumlah'] ?> transaksi</small></div></div></div></div>
            <div class="col-md-4"><div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center"><div class="metric-icon"><i class="ri-wallet-3-line"></i></div><div><div class="text-muted">Topup bulan ini</div><h4 class="mb-0"><?= $money($r['topup_bulan_ini']['total']) ?></h4><small><?= (int) $r['topup_bulan_ini']['jumlah'] ?> transaksi berhasil</small></div></div></div></div>
            <div class="col-md-4"><div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center"><div class="metric-icon"><i class="ri-shield-check-line"></i></div><div><div class="text-muted">Limit transaksi</div><h4 class="mb-0"><?= $money($anak['batas_per_transaksi']) ?></h4><small>Harian <?= $money($anak['batas_harian']) ?></small></div></div></div></div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between border-bottom"><div><h4 class="header-title mb-1">Transaksi Terakhir</h4><small class="text-muted">Belanja terbaru milik anak yang dipilih.</small></div><a href="<?= base_url('wali/dompet/history-belanja?id_siswa='.(int) $anak['id_siswa']) ?>">Lihat semua</a></div>
            <div class="card-body py-1">
                <?php if (empty($r['transaksi_terbaru'])): ?><div class="text-center text-muted py-5">Belum ada transaksi belanja.</div><?php endif; ?>
                <?php foreach ($r['transaksi_terbaru'] as $item): ?>
                    <div class="transaction-row"><div><div class="fw-semibold"><?= html_escape($item['keterangan'] ?: 'Transaksi kantin') ?></div><small class="text-muted"><?= html_escape($item['no_transaksi']) ?> · <?= html_escape($item['tanggal'].' '.$item['waktu']) ?></small></div><div class="text-end"><div class="fw-bold text-danger">-<?= $money($item['nominal']) ?></div><small class="text-muted"><?= html_escape(ucwords(strtolower(str_replace('_',' ',$item['status_transaksi'])))) ?></small></div></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($anak): ?>
<div class="modal fade" id="modal-topup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-topup">
                <div class="modal-header"><div><h4 class="modal-title">Pengajuan Isi Saldo</h4><small class="text-muted"><?= html_escape($anak['nama_siswa']) ?></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="web_csrf_token" value="<?= html_escape($csrf_token) ?>">
                    <input type="hidden" name="id_siswa" value="<?= (int) $anak['id_siswa'] ?>">
                    <div class="mb-3"><label class="form-label fw-semibold">Nominal isi saldo</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="numeric" class="form-control form-control-lg" id="nominal-tampil" placeholder="0" autocomplete="off" required><input type="hidden" name="nominal_saldo" id="nominal-asli"></div><small class="text-muted">Minimal <?= $money($pengaturan ? $pengaturan['minimal_isi_saldo'] : 0) ?>, maksimal <?= $money($pengaturan ? $pengaturan['maksimal_isi_saldo'] : 0) ?>.</small></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Rekening tujuan</label><select class="form-select" name="id_rekening_dompet" id="rekening-topup" required><?php foreach ($rekening as $item): ?><option value="<?= (int) $item['id'] ?>" data-bank="<?= html_escape($item['nama_bank']) ?>" data-number="<?= html_escape($item['nomor_rekening']) ?>" data-owner="<?= html_escape($item['nama_pemilik']) ?>"><?= html_escape($item['nama_bank'].' - '.$item['nomor_rekening']) ?></option><?php endforeach; ?></select></div>
                    <div class="border-top border-bottom py-3 mb-3"><div class="d-flex justify-content-between mb-2"><span>Biaya layanan</span><strong id="biaya-label"><?= $money($pengaturan ? $pengaturan['biaya_layanan'] : 0) ?></strong></div><div class="d-flex justify-content-between fs-5"><span class="fw-semibold">Total transfer</span><strong class="text-primary" id="total-label"><?= $money(0) ?></strong></div></div>
                    <div class="topup-account">
                        <div class="fw-bold fs-5" id="bank-label">-</div>
                        <div class="text-primary fw-bold fs-4 my-1" id="rekening-label">-</div>
                        <div class="text-muted" id="pemilik-label">-</div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="btn-salin-rekening"><i class="ri-file-copy-line me-1"></i> Salin Nomor Rekening</button>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><i class="ri-information-line me-1"></i> Pengajuan akan disimpan terlebih dahulu, lalu WhatsApp admin dibuka dengan pesan yang sudah terisi.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="btn-topup" <?= $topup_aktif ? '' : 'disabled' ?>><i class="ri-whatsapp-line me-1"></i> Ajukan &amp; Kirim WhatsApp</button></div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    const biaya = <?= (int) ($pengaturan ? $pengaturan['biaya_layanan'] : 0) ?>;
    const format = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    const nominalTampil = document.getElementById('nominal-tampil');
    const nominalAsli = document.getElementById('nominal-asli');
    const rekening = document.getElementById('rekening-topup');
    function updateNominal(){ const value = parseInt((nominalTampil.value || '').replace(/\D/g,''),10) || 0; nominalAsli.value=value; nominalTampil.value=value ? value.toLocaleString('id-ID') : ''; document.getElementById('total-label').textContent=format(value+biaya); }
    function updateRekening(){ const option=rekening && rekening.options[rekening.selectedIndex]; if(!option)return; document.getElementById('bank-label').textContent=option.dataset.bank||'-'; document.getElementById('rekening-label').textContent=option.dataset.number||'-'; document.getElementById('pemilik-label').textContent=option.dataset.owner ? 'a.n. '+option.dataset.owner : '-'; }
    function fallbackCopy(value){ const input=document.createElement('textarea');input.value=value;input.setAttribute('readonly','');input.style.position='fixed';input.style.opacity='0';document.body.appendChild(input);input.select();const copied=document.execCommand('copy');document.body.removeChild(input);return copied; }
    function notifyCopied(){ Swal.fire({toast:true,position:'top-end',icon:'success',title:'Nomor rekening disalin',showConfirmButton:false,timer:1800}); }
    if(nominalTampil) nominalTampil.addEventListener('input',updateNominal);
    if(rekening){rekening.addEventListener('change',updateRekening);updateRekening();}
    document.getElementById('btn-salin-rekening').addEventListener('click',function(){
        const option=rekening && rekening.options[rekening.selectedIndex];
        const number=option ? (option.dataset.number||'').trim() : '';
        if(!number){Swal.fire('Nomor rekening belum tersedia','','warning');return;}
        if(navigator.clipboard && window.isSecureContext){navigator.clipboard.writeText(number).then(notifyCopied).catch(function(){if(fallbackCopy(number))notifyCopied();});}
        else if(fallbackCopy(number)){notifyCopied();}
        else{Swal.fire('Gagal menyalin','Silakan salin nomor rekening secara manual.','error');}
    });
    document.getElementById('form-topup').addEventListener('submit',function(event){
        event.preventDefault(); updateNominal();
        const button=document.getElementById('btn-topup');
        if(!nominalAsli.value || Number(nominalAsli.value)<=0){Swal.fire('Nominal belum valid','Masukkan nominal isi saldo.','warning');return;}
        const waWindow=window.open('about:blank','sdkreatif_whatsapp');
        button.disabled=true; button.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        fetch('<?= base_url('wali/dompet/buat-pengajuan') ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new FormData(this)})
            .then(async response=>{const body=await response.json().catch(()=>({message:'Respons server tidak valid.'}));if(!response.ok||!body.success)throw new Error(body.message||'Pengajuan gagal.');return body;})
            .then(body=>{if(waWindow){waWindow.location.href=body.data.whatsapp_url;}else{window.location.href=body.data.whatsapp_url;} Swal.fire({icon:'success',title:'Pengajuan tersimpan',text:body.message}).then(()=>window.location.href='<?= base_url('wali/dompet/history-isi-saldo?id_siswa='.(int) $anak['id_siswa']) ?>');})
            .catch(error=>{if(waWindow)waWindow.close();Swal.fire('Pengajuan gagal',error.message,'error');})
            .finally(()=>{button.disabled=false;button.innerHTML='<i class="ri-whatsapp-line me-1"></i> Ajukan & Kirim WhatsApp';});
    });
})();
</script>
<?php endif; ?>
