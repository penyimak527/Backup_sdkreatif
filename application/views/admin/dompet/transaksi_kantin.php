<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info">Modul web ini hanya untuk monitoring. Transaksi kasir tetap berasal dari sistem yang sudah ada.</div>
<div class="card"><div class="card-body">
    <div class="row g-2 mb-3">
        <div class="col-md-4"><input id="search" class="form-control" placeholder="Nomor, NIS, atau nama"></div>
        <div class="col-md-2"><select id="canteenStatusFilter" class="form-select"><option value="">Semua status</option><option>BERHASIL</option><option>DIBATALKAN</option></select></div>
        <div class="col-md-2"><input id="awal" type="date" class="form-control"></div>
        <div class="col-md-2"><input id="akhir" type="date" class="form-control"></div>
        <div class="col-md-2"><button id="filter" class="btn btn-outline-primary w-100">Terapkan</button></div>
    </div>
    <div id="rows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div>
    <div class="sdk-pager-wrap"><div><small id="info" class="text-muted"></small><div class="mt-2" id="canteenPagination"></div></div><div class="sdk-page-size"><label for="canteenPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="canteenPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
</div></div>
<script>
$(function(){
    const base=<?=json_encode(base_url('admin/dompet/transaksi_kantin/'),JSON_UNESCAPED_SLASHES)?>,escapeHtml=value=>$('<div>').text(value??'-').html(),money=value=>new Intl.NumberFormat('id-ID').format(+value||0);
    let page=1,limit=10;
    function load(rebuildPager){
        $.post(base+'result',{search:$('#search').val(),status:$('#canteenStatusFilter').val(),tanggal_awal:$('#awal').val(),tanggal_akhir:$('#akhir').val(),page:page,limit:limit},null,'json').done(response=>{
            let html='';
            (response.data||[]).forEach((row,index)=>{
                html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>Transaksi: <strong>${escapeHtml(row.no_transaksi)}</strong></span>${SDKreatifUI.statusBadge(row.status_transaksi)}</div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page-1)*limit+index+1}. ${escapeHtml(row.nama_siswa)}</h5><div class="sdk-record-meta mb-3"><span><i class="ri-calendar-line me-1"></i>${escapeHtml(row.tanggal)} ${escapeHtml(row.waktu)}</span><span>NIS: <strong>${escapeHtml(row.nis)}</strong></span><span>Kelas: <strong>${escapeHtml(row.nama_kelas)}</strong></span></div><div class="sdk-record-details"><div class="sdk-record-detail">Nominal<strong>Rp ${money(row.nominal)}</strong></div><div class="sdk-record-detail">Perubahan Saldo<strong>Rp ${money(row.saldo_sebelum)} → Rp ${money(row.saldo_sesudah)}</strong></div><div class="sdk-record-detail">Petugas / Sumber<strong>${escapeHtml(row.nama_user)} · ${escapeHtml(row.sumber_transaksi)}</strong></div></div></div></div></div>`;
            });
            $('#rows').html(html||'<div class="sdk-record-empty">Tidak ada data.</div>');
            page=response.meta.page;
            $('#info').text(response.meta.total+' transaksi');
            if(rebuildPager!==false)SDKreatifUI.serverPager('#canteenPagination',response.meta,paging=>{page=paging.currentPage;load(false);});
        }).fail(xhr=>Swal.fire('Gagal',(xhr.responseJSON||{}).message||'Data transaksi gagal dimuat.','error'));
    }
    $('#filter').on('click',()=>{page=1;load(true);});
    $('#canteenPageSize').on('change',function(){limit=parseInt(this.value,10)||10;page=1;load(true);});
    $('#search').on('keydown',event=>{if(event.key==='Enter'){page=1;load(true);}});
    load(true);
});
</script>
