<?php
defined('BASEPATH') or exit('No direct script access allowed');
$money = static function ($value) { return 'Rp '.number_format((int) $value, 0, ',', '.'); };
?>

<style>
.wali-mobile-list { display: grid; gap: .75rem; }
.wali-mobile-record { padding: 1rem; background: var(--ct-secondary-bg); border: 1px solid var(--ct-border-color); border-radius: .75rem; }
.wali-mobile-record__head { margin-bottom: .85rem; }
.wali-mobile-record__title { min-width: 0; margin: 0; color: var(--ct-body-color); font-size: .9rem; font-weight: 700; overflow-wrap: anywhere; }
.wali-mobile-record__meta { margin-top: .2rem; color: var(--ct-secondary-color); font-size: .78rem; }
.wali-mobile-record__details { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .75rem; padding-top: .85rem; border-top: 1px solid var(--ct-border-color); }
.wali-mobile-record__label { display: block; margin-bottom: .15rem; color: var(--ct-secondary-color); font-size: .72rem; }
.wali-mobile-record__value { color: var(--ct-body-color); font-size: .88rem; font-weight: 700; overflow-wrap: anywhere; }
</style>

<?php $this->load->view('wali/dompet/_pilih_anak', array('anak_list' => $anak_list, 'anak' => $anak)); ?>

<?php if (!$anak): ?>
    <div class="alert alert-info">Belum ada anak aktif yang terhubung.</div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header border-bottom"><h4 class="header-title mb-1">Atur Limit Transaksi</h4><small class="text-muted"><?= html_escape($anak['nama_siswa'].' · '.$anak['nis']) ?></small></div>
            <div class="card-body">
                <form id="form-limit">
                    <input type="hidden" name="web_csrf_token" value="<?= html_escape($csrf_token) ?>">
                    <input type="hidden" name="id_siswa" value="<?= (int) $anak['id_siswa'] ?>">
                    <div class="mb-3"><label class="form-label fw-semibold">Batas harian</label><div class="input-group"><span class="input-group-text">Rp</span><input class="form-control money-input" inputmode="numeric" value="<?= number_format((int) $anak['batas_harian'],0,',','.') ?>" required><input type="hidden" name="batas_harian" value="<?= (int) $anak['batas_harian'] ?>"></div><small class="text-muted">Maksimal total belanja siswa dalam satu hari.</small></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Batas per transaksi</label><div class="input-group"><span class="input-group-text">Rp</span><input class="form-control money-input" inputmode="numeric" value="<?= number_format((int) $anak['batas_per_transaksi'],0,',','.') ?>" required><input type="hidden" name="batas_per_transaksi" value="<?= (int) $anak['batas_per_transaksi'] ?>"></div><small class="text-muted">Tidak boleh lebih besar dari batas harian.</small></div>
                    <div class="alert alert-light border">Penggunaan hari ini: <strong><?= $money($anak['penggunaan_hari_ini']) ?></strong></div>
                    <button class="btn btn-primary w-100" id="btn-limit" type="submit"><i class="ri-save-line me-1"></i> Simpan Limit</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header border-bottom"><h4 class="header-title mb-1">Riwayat Perubahan Limit</h4><small class="text-muted">20 perubahan terakhir.</small></div>
            <div class="card-body p-0 d-none d-md-block"><div class="table-responsive"><table class="table sdk-data-table mb-0"><thead><tr><th>Tanggal</th><th>Limit Harian</th><th>Per Transaksi</th></tr></thead><tbody>
                <?php if (empty($histori)): ?><tr><td colspan="3" class="sdk-empty">Belum ada perubahan limit.</td></tr><?php endif; ?>
                <?php foreach ($histori as $row): ?><tr><td><?= html_escape($row['tanggal'].' '.$row['waktu']) ?><span class="sdk-secondary"><?= html_escape($row['no_riwayat']) ?></span></td><td><?= $money($row['batas_harian_baru']) ?><span class="sdk-secondary">Sebelumnya <?= $money($row['batas_harian_lama']) ?></span></td><td><?= $money($row['batas_transaksi_baru']) ?><span class="sdk-secondary">Sebelumnya <?= $money($row['batas_transaksi_lama']) ?></span></td></tr><?php endforeach; ?>
            </tbody></table></div></div>
            <div class="card-body d-md-none p-3">
                <?php if (empty($histori)): ?>
                    <div class="sdk-record-empty">Belum ada perubahan limit.</div>
                <?php else: ?>
                    <div class="wali-mobile-list">
                        <?php foreach ($histori as $row): ?>
                            <article class="wali-mobile-record">
                                <div class="wali-mobile-record__head"><h5 class="wali-mobile-record__title"><?= html_escape($row['tanggal'].' · '.$row['waktu']) ?></h5><div class="wali-mobile-record__meta"><?= html_escape($row['no_riwayat']) ?></div></div>
                                <div class="wali-mobile-record__details">
                                    <div><span class="wali-mobile-record__label">Limit harian baru</span><span class="wali-mobile-record__value"><?= $money($row['batas_harian_baru']) ?></span><div class="wali-mobile-record__meta">Sebelumnya <?= $money($row['batas_harian_lama']) ?></div></div>
                                    <div><span class="wali-mobile-record__label">Per transaksi baru</span><span class="wali-mobile-record__value"><?= $money($row['batas_transaksi_baru']) ?></span><div class="wali-mobile-record__meta">Sebelumnya <?= $money($row['batas_transaksi_lama']) ?></div></div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const form=document.getElementById('form-limit');
    form.querySelectorAll('.money-input').forEach(function(input){
        const hidden=input.parentElement.querySelector('input[type="hidden"]');
        input.addEventListener('input',function(){const value=parseInt(input.value.replace(/\D/g,''),10)||0;hidden.value=value;input.value=value?value.toLocaleString('id-ID'):'';});
    });
    form.addEventListener('submit',function(event){
        event.preventDefault(); const button=document.getElementById('btn-limit');button.disabled=true;button.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        fetch('<?= base_url('wali/dompet/simpan-limit') ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new FormData(form)})
            .then(async response=>{const body=await response.json().catch(()=>({message:'Respons server tidak valid.'}));if(!response.ok||!body.success)throw new Error(body.message||'Gagal menyimpan limit.');return body;})
            .then(body=>Swal.fire('Berhasil',body.message,'success').then(()=>window.location.reload()))
            .catch(error=>Swal.fire('Gagal',error.message,'error'))
            .finally(()=>{button.disabled=false;button.innerHTML='<i class="ri-save-line me-1"></i> Simpan Limit';});
    });
})();
</script>
<?php endif; ?>
