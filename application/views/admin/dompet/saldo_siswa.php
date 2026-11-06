<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info">Saldo hanya dapat berubah melalui transaksi sumber. Halaman ini tidak menyediakan edit angka saldo.</div>
<div class="card"><div class="card-body">
    <div class="row g-2 mb-3"><div class="col-md-5"><input id="search" class="form-control" placeholder="Cari NIS atau nama"></div><div class="col-md-3"><select id="walletLegacyStatusFilter" class="form-select"><option value="">Semua status</option><option>AKTIF</option><option>NONAKTIF</option></select></div><div class="col-md-2"><button id="filter" class="btn btn-outline-primary w-100">Terapkan</button></div></div>
    <div id="rows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div>
    <div class="sdk-pager-wrap"><div><small id="info" class="text-muted"></small><div class="mt-2" id="legacyWalletPagination"></div></div><div class="sdk-page-size"><label for="legacyWalletPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="legacyWalletPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
</div></div>
<script>
$(function(){
    const base=<?=json_encode(base_url('admin/dompet/saldo_siswa/'),JSON_UNESCAPED_SLASHES)?>,csrf=<?=json_encode($csrf_token)?>,escapeHtml=value=>$('<div>').text(value??'-').html(),money=value=>new Intl.NumberFormat('id-ID').format(+value||0),request=(url,data)=>$.post(base+url,data,null,'json'),fail=xhr=>Swal.fire('Gagal',(xhr.responseJSON||{}).message||'Terjadi kesalahan.','error');
    let page=1,limit=10;
    function load(rebuildPager){
        request('result',{search:$('#search').val(),status:$('#walletLegacyStatusFilter').val(),page:page,limit:limit}).done(response=>{
            let html='';
            (response.data||[]).forEach((row,index)=>{
                const individualDaily=+row.batas_harian||0,individualTransaction=+row.batas_per_transaksi||0,source=(individualDaily||individualTransaction)?'Individual':'Global';
                html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${escapeHtml(row.nis)}</strong> · Kelas: <strong>${escapeHtml(row.nama_kelas)}</strong></span>${SDKreatifUI.statusBadge(row.status_dompet)}</div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page-1)*limit+index+1}. ${escapeHtml(row.nama_siswa)}</h5><div class="sdk-record-details"><div class="sdk-record-detail">Saldo<strong>Rp ${money(row.saldo)}</strong></div><div class="sdk-record-detail">Limit ${source}<strong>Harian Rp ${money(individualDaily||row.batas_harian_default)} · Transaksi Rp ${money(individualTransaction||row.batas_transaksi_default)}</strong></div></div></div><div class="sdk-record-actions"><button class="btn btn-sm btn-outline-primary limit" data-id="${row.id_siswa}" data-h="${individualDaily}" data-t="${individualTransaction}" title="Atur limit" aria-label="Atur limit" data-bs-toggle="tooltip"><i class="ri-settings-3-line"></i></button><button class="btn btn-sm btn-outline-secondary toggle" data-id="${row.id_siswa}" data-status="${row.status_dompet==='AKTIF'?'NONAKTIF':'AKTIF'}" title="Ubah layanan" aria-label="Ubah layanan" data-bs-toggle="tooltip"><i class="ri-toggle-line"></i></button><button class="btn btn-sm btn-outline-info hist" data-id="${row.id_siswa}" title="Lihat histori" aria-label="Lihat histori" data-bs-toggle="tooltip"><i class="ri-history-line"></i></button></div></div></div>`;
            });
            $('#rows').html(html||'<div class="sdk-record-empty">Tidak ada data.</div>');SDKreatifUI.refreshTooltips('#rows');page=response.meta.page;$('#info').text(response.meta.total+' wallet');
            if(rebuildPager!==false)SDKreatifUI.serverPager('#legacyWalletPagination',response.meta,paging=>{page=paging.currentPage;load(false);});
        });
    }
    $('#filter').on('click',()=>{page=1;load(true);});
    $('#legacyWalletPageSize').on('change',function(){limit=parseInt(this.value,10)||10;page=1;load(true);});
    $('#search').on('keydown',event=>{if(event.key==='Enter'){page=1;load(true);}});
    $('#rows').on('click','.limit',function(){const id=$(this).data('id'),daily=$(this).data('h'),transaction=$(this).data('t');Swal.fire({title:'Limit individual',html:`<input id="lh" class="swal2-input" type="number" min="0" value="${daily}" placeholder="Batas harian"><input id="lt" class="swal2-input" type="number" min="0" value="${transaction}" placeholder="Batas per transaksi">`,showCancelButton:true,preConfirm:()=>[$('#lh').val(),$('#lt').val()]}).then(result=>{if(result.isConfirmed)request('atur_limit',{id_siswa:id,batas_harian:result.value[0],batas_transaksi:result.value[1],web_csrf_token:csrf}).done(()=>load(true)).fail(fail);});});
    $('#rows').on('click','.toggle',function(){const id=$(this).data('id'),status=$(this).data('status');Swal.fire({title:'Alasan perubahan',input:'text',showCancelButton:true}).then(result=>{if(result.isConfirmed)request('status',{id_siswa:id,status:status,alasan:result.value||'Diaktifkan kembali',web_csrf_token:csrf}).done(()=>load(true)).fail(fail);});});
    $('#rows').on('click','.hist',function(){request('histori',{id_siswa:$(this).data('id')}).done(response=>Swal.fire({title:'Histori',html:`Top-up: ${response.data.topup.length}<br>Transaksi kantin: ${response.data.kantin.length}<br><small>Ditampilkan maksimal 100 item per jenis.</small>`})).fail(fail);});
    load(true);
});
</script>
