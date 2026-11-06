<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info">Reversal menyimpan transaksi asal, memisahkan refund pokok dan fee, serta menolak proses yang membuat saldo siswa negatif.</div>
<div class="card"><div class="card-body">
    <div class="row g-2 mb-3">
        <div class="col-md-4"><input id="search" class="form-control" placeholder="Nomor pembatalan/transaksi"></div>
        <div class="col-md-3"><select id="jenis" class="form-select"><option value="">Semua jenis transaksi</option><?php foreach($jenis_options as $option):?><option value="<?=html_escape($option['value'])?>"><?=html_escape(ucwords(strtolower(str_replace('_',' ',$option['value']))))?></option><?php endforeach;?></select></div>
        <div class="col-md-3"><select id="cancelStatusFilter" class="form-select"><option value="">Semua status</option><?php foreach($status_options as $option):?><option value="<?=html_escape($option['value'])?>"><?=html_escape(ucwords(strtolower(str_replace('_',' ',$option['value']))))?></option><?php endforeach;?></select></div>
        <div class="col-md-2"><button id="filter" class="btn btn-outline-primary w-100">Terapkan</button></div>
    </div>
    <div id="rows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div>
    <div class="sdk-pager-wrap"><div><small id="info" class="text-muted"></small><div class="mt-2" id="cancelPagination"></div></div><div class="sdk-page-size"><label for="cancelPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="cancelPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
</div></div>
<script>
$(function(){
    const base=<?=json_encode(base_url('admin/dompet/pembatalan_dompet/'),JSON_UNESCAPED_SLASHES)?>,escapeHtml=value=>$('<div>').text(value??'-').html(),money=value=>new Intl.NumberFormat('id-ID').format(+value||0);
    let page=1,limit=10;
    $('#jenis,#cancelStatusFilter').select2({width:'100%',minimumResultsForSearch:Infinity});
    function load(rebuildPager){
        $.post(base+'result',{search:$('#search').val(),jenis:$('#jenis').val(),status:$('#cancelStatusFilter').val(),page:page,limit:limit},null,'json').done(response=>{
            let html='';
            (response.data||[]).forEach((row,index)=>{
                html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>Pembatalan: <strong>${escapeHtml(row.no_pembatalan)}</strong></span>${SDKreatifUI.statusBadge(row.status_pembatalan)}</div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page-1)*limit+index+1}. ${escapeHtml(row.no_transaksi)}</h5><div class="sdk-record-meta mb-3"><span><i class="ri-calendar-line me-1"></i>${escapeHtml(row.tanggal_pengajuan)}</span><span>Jenis: <strong>${escapeHtml(row.jenis_transaksi)}</strong></span><span>ID Siswa: <strong>${escapeHtml(row.id_siswa)}</strong></span></div><div class="sdk-record-details"><div class="sdk-record-detail">Nominal Koreksi<strong>Rp ${money(row.nominal_koreksi)}</strong></div><div class="sdk-record-detail">Alasan<strong>${escapeHtml(row.alasan)}</strong></div><div class="sdk-record-detail">Diproses Oleh<strong>${escapeHtml(row.nama_user_penyetuju)}</strong></div></div></div></div></div>`;
            });
            $('#rows').html(html||'<div class="sdk-record-empty">Tidak ada data.</div>');
            page=response.meta.page;
            $('#info').text(response.meta.total+' pembatalan');
            if(rebuildPager!==false)SDKreatifUI.serverPager('#cancelPagination',response.meta,paging=>{page=paging.currentPage;load(false);});
        }).fail(xhr=>Swal.fire('Gagal',(xhr.responseJSON||{}).message||'Riwayat pembatalan gagal dimuat.','error'));
    }
    $('#filter').on('click',()=>{page=1;load(true);});
    $('#cancelPageSize').on('change',function(){limit=parseInt(this.value,10)||10;page=1;load(true);});
    $('#search').on('keydown',event=>{if(event.key==='Enter'){page=1;load(true);}});
    load(true);
});
</script>
