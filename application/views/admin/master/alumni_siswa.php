<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="card">
    <div class="card-header border-bottom border-dashed d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div><h4 class="header-title mb-1">Data Alumni Siswa</h4><small class="text-muted">Siswa yang telah diluluskan dari kelas 6 beserta tahun kelulusannya.</small></div>
        <div class="d-flex gap-2"><button class="btn btn-sm btn-outline-success" id="exportAlumni"><i class="ri-file-excel-2-line me-1"></i>Export Hasil Filter</button><a class="btn btn-sm btn-light" href="<?= base_url('admin/master/siswa') ?>"><i class="ri-arrow-left-line me-1"></i>Kembali</a></div>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-lg-6"><input class="form-control" id="alumniSearch" placeholder="Cari NIS, NISN, atau nama alumni"></div>
            <div class="col-lg-4"><select class="form-select" id="graduationFilter"><option value="">Semua tahun kelulusan</option><?php foreach ($tahun_lulus as $year): ?><option value="<?= html_escape($year['tahun_lulus']) ?>"><?= html_escape($year['tahun_lulus']) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2"><button class="btn btn-outline-primary w-100" id="applyAlumniFilter"><i class="ri-filter-3-line me-1"></i>Terapkan</button></div>
        </div>
        <div id="alumniRows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data alumni...</div></div>
        <div class="sdk-pager-wrap"><div><small id="alumniInfo" class="text-muted"></small><div class="mt-2" id="alumniPagination"></div></div><div class="sdk-page-size"><label for="alumniPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="alumniPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
    </div>
</div>
<script>
$(function(){
    const base=<?=json_encode(base_url('admin/master/siswa/'),JSON_UNESCAPED_SLASHES)?>,escapeHtml=value=>$('<div>').text(value==null||value===''?'-':value).html();
    let page=1,limit=10;
    $('#graduationFilter').select2({width:'100%',minimumResultsForSearch:Infinity});
    function fail(xhr){Swal.fire({icon:'error',title:'Gagal',text:(xhr.responseJSON||{}).message||'Data alumni tidak dapat dimuat.'});}
    function load(rebuildPager){$.post(base+'alumni_result',{search:$('#alumniSearch').val(),tahun_lulus:$('#graduationFilter').val(),page:page,limit:limit},null,'json').done(response=>{let html='';(response.data||[]).forEach((row,index)=>{html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${escapeHtml(row.nis)}</strong>${row.nisn?` · NISN: <strong>${escapeHtml(row.nisn)}</strong>`:''}</span><span class="badge bg-secondary">Lulus ${escapeHtml(row.tahun_lulus)}</span></div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page-1)*limit+index+1}. ${escapeHtml(row.nama_lengkap)}</h5><div class="sdk-record-meta"><span>Jenis Kelamin: <strong>${escapeHtml(row.jk)}</strong></span><span>Kelas Terakhir: <strong>${escapeHtml(row.nama_kelas)}</strong></span><span>Tahun Ajaran: <strong>${escapeHtml(row.periode)}</strong></span><span>Wali: <strong>${escapeHtml(row.nama_wali)}</strong></span></div></div></div></div>`;});$('#alumniRows').html(html||'<div class="sdk-record-empty">Data alumni tidak ditemukan.</div>');page=response.meta.page;$('#alumniInfo').text(response.meta.total+' alumni');if(rebuildPager!==false)SDKreatifUI.serverPager('#alumniPagination',response.meta,paging=>{page=paging.currentPage;load(false);});}).fail(fail);}
    $('#applyAlumniFilter').on('click',()=>{page=1;load(true);});$('#alumniSearch').on('keydown',event=>{if(event.key==='Enter'){page=1;load(true);}});$('#alumniPageSize').on('change',function(){limit=parseInt(this.value,10)||10;page=1;load(true);});
    $('#exportAlumni').on('click',()=>{const query=$.param({search:$('#alumniSearch').val(),tahun_lulus:$('#graduationFilter').val()});window.location.href=base+'export_alumni?'+query;});
    load(true);
});
</script>
