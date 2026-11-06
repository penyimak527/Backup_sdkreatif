<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h4 class="header-title mb-0">QR Code Siswa</h4><small class="text-muted">Cetak dan unduh memakai desain kartu depan/belakang sekolah.</small></div>
        <button id="printClass" class="btn btn-sm btn-outline-primary"><i class="ri-printer-line me-1"></i>Cetak Daftar per Kelas</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-5"><input id="search" class="form-control" placeholder="Cari NIS, nama, atau kode"></div>
            <div class="col-md-4"><select id="kelas" class="form-select"><option value="">Semua kelas</option><?php foreach($kelas as $x):?><option value="<?=(int)$x['id']?>"><?=html_escape($x['nama_kelas'])?></option><?php endforeach;?></select></div>
            <div class="col-md-2"><button id="filter" class="btn btn-outline-primary w-100">Terapkan</button></div>
        </div>
        <div id="rows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div>
        <div class="sdk-pager-wrap"><div><small id="info" class="text-muted"></small><div class="mt-2" id="qrPagination"></div></div><div class="sdk-page-size"><label for="qrPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="qrPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
    </div>
</div>
<script>
$(function(){
    const base=<?=json_encode(base_url('admin/dompet/qr_siswa/'),JSON_UNESCAPED_SLASHES)?>,csrf=<?=json_encode($csrf_token)?>,escapeHtml=value=>$('<div>').text(value??'-').html();
    let page=1,limit=10;
    $('#kelas').select2({width:'100%',placeholder:'Semua kelas',allowClear:true});
    function load(rebuildPager){
        $.post(base+'result',{search:$('#search').val(),kelas:$('#kelas').val(),page:page,limit:limit},null,'json').done(response=>{
            let html='';
            (response.data||[]).forEach((row,index)=>{
                html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${escapeHtml(row.nis)}</strong> · Kelas: <strong>${escapeHtml(row.nama_kelas)}</strong></span><span class="badge bg-info text-dark">Versi ${escapeHtml(row.versi_qr)}</span></div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page-1)*limit+index+1}. ${escapeHtml(row.nama_lengkap)}</h5><div class="sdk-record-meta"><span>Kode Tampil: <strong><code>${escapeHtml(row.kode_tampil)}</code></strong></span><span><i class="ri-calendar-line me-1"></i>Dibuat: <strong>${escapeHtml(row.tanggal_buat)} ${escapeHtml(row.waktu_buat)}</strong></span></div></div><div class="sdk-record-actions"><a class="btn btn-sm btn-outline-info" target="_blank" href="${base}cetak?id=${row.id_siswa}" title="Cetak" aria-label="Cetak"><i class="ri-printer-line"></i></a><a class="btn btn-sm btn-primary" target="_blank" href="${base}download?id=${row.id_siswa}" title="Buka kartu PNG" aria-label="Buka kartu PNG"><i class="ri-image-line"></i></a><button class="btn btn-sm btn-outline-warning regen" data-id="${row.id_siswa}" title="Regenerate QR" aria-label="Regenerate QR"><i class="ri-refresh-line"></i></button></div></div></div>`;
            });
            $('#rows').html(html||'<div class="sdk-record-empty">Tidak ada data.</div>');
            SDKreatifUI.refreshTooltips('#rows');
            page=response.meta.page;
            $('#info').text(response.meta.total+' QR aktif');
            if(rebuildPager!==false)SDKreatifUI.serverPager('#qrPagination',response.meta,paging=>{page=paging.currentPage;load(false);});
        });
    }
    $('#filter').on('click',()=>{page=1;load(true);});
    $('#qrPageSize').on('change',function(){limit=parseInt(this.value,10)||10;page=1;load(true);});
    $('#search').on('keydown',event=>{if(event.key==='Enter'){page=1;load(true);}});
    $('#printClass').on('click',()=>window.open(base+'cetak?kelas='+encodeURIComponent($('#kelas').val()),'_blank'));
    $('#rows').on('click','.regen',function(){
        const id=$(this).data('id');
        Swal.fire({title:'Regenerate QR?',text:'QR lama langsung dinonaktifkan.',icon:'warning',showCancelButton:true}).then(result=>{
            if(result.isConfirmed)$.post(base+'regenerate',{id_siswa:id,web_csrf_token:csrf},null,'json').done(response=>{Swal.fire('Berhasil',response.message,'success');load(true);}).fail(xhr=>Swal.fire('Gagal',(xhr.responseJSON||{}).message||'Terjadi kesalahan.','error'));
        });
    });
    load(true);
});
</script>
