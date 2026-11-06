<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="card">
    <div class="card-header app-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="header-title mb-1">Template WhatsApp</h4>
            <small class="text-muted">Template untuk bukti pembayaran, surat tunggakan, dan transaksi tabungan.</small>
        </div>
        <button type="button" class="btn btn-outline-primary" id="btnTambah"><i class="ri-add-line me-1"></i>Tambah</button>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-xl-5 col-lg-5 col-md-6"><label class="form-label">Pencarian</label><input type="text" id="search" class="form-control" placeholder="Cari nama atau isi template ..."></div>
            <div class="col-xl-3 col-lg-3 col-md-6"><label class="form-label">Jenis Template</label><select id="filterJenis" class="form-select"><option value="">Semua Jenis</option><option value="Bukti Pembayaran">Bukti Pembayaran</option><option value="Surat Tunggakan">Surat Tunggakan</option><option value="Setoran Tabungan">Setoran Tabungan</option><option value="Penarikan Tabungan">Penarikan Tabungan</option></select></div>
            <div class="col-xl-2 col-lg-2 col-md-6"><label class="form-label">Status</label><select id="filterStatus" class="form-select"><option value="">Semua Status</option><option value="Aktif">Aktif</option><option value="Nonaktif">Tidak Aktif</option></select></div>
            <div class="col-xl-2 col-lg-2 col-md-6 d-grid"><button type="button" class="btn btn-primary" id="btnCari"><i class="ri-search-line me-1"></i>Cari</button></div>
        </div>
        <div id="templateList" class="sdk-record-list"><div class="sdk-record-empty">Memuat template...</div></div>
    </div>
</div>

<div class="modal fade" id="modalTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Template WhatsApp</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="formTemplate">
                    <input type="hidden" name="id" id="templateId">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Jenis Template</label><select name="jenis_template" id="jenisTemplate" class="form-select"><option value="Bukti Pembayaran">Bukti Pembayaran</option><option value="Surat Tunggakan">Surat Tunggakan</option><option value="Setoran Tabungan">Setoran Tabungan</option><option value="Penarikan Tabungan">Penarikan Tabungan</option></select></div>
                        <div class="col-md-6"><label class="form-label">Nama Template</label><input type="text" name="nama_template" id="namaTemplate" class="form-control" placeholder="Nama template ..." required></div>
                        <div class="col-12"><label class="form-label">Pesan</label><textarea name="isi_template" id="isiTemplate" class="form-control" rows="8" required></textarea></div>
                        <div class="col-12"><div class="alert alert-info py-2 mb-0"><small id="variableInfo"></small></div></div>
                        <div class="col-md-6"><label class="form-label">Jadikan Default</label><select name="status_default" id="templateDefault" class="form-select"><option value="Tidak">Tidak</option><option value="Ya">Ya</option></select><small class="text-muted">Hanya satu template default untuk setiap jenis.</small></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select name="status" id="templateStatus" class="form-select"><option value="Aktif">Aktif</option><option value="Nonaktif">Tidak Aktif</option></select><small class="text-muted">Template tidak aktif tidak dipakai saat pengiriman.</small></div>
                    </div>
                    <div id="previewBox" class="alert alert-light border d-none mt-3 mb-0" style="white-space:pre-wrap"></div>
                </form>
            </div>
            <div class="modal-footer justify-content-between"><button type="button" id="btnPreview" class="btn btn-outline-secondary"><i class="ri-eye-line me-1"></i>Preview</button><div class="d-flex gap-2"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button type="button" id="btnSimpan" class="btn btn-primary"><i class="ri-save-line me-1"></i>Simpan</button></div></div>
        </div>
    </div>
</div>

<script>
$(function(){
    const base=<?= json_encode(site_url('admin/spp/pengaturan_whatsapp/')) ?>;
    const modal=bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTemplate'));
    let rows=[];
    const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const request=(path,data)=>$.ajax({url:base+path,type:'POST',dataType:'json',data:data||{}});
    const fail=x=>Swal.fire('Gagal',(x.responseJSON&&x.responseJSON.message)||'Permintaan gagal diproses.','error');
    const vars={
        'Bukti Pembayaran':['{nama_wali}','{nama_siswa}','{kelas}','{tanggal}','{no_transaksi}','{total_bayar}','{metode_pembayaran}','{nama_sekolah}','{nama_petugas}'],
        'Surat Tunggakan':['{nama_wali}','{nama_siswa}','{kelas}','{tanggal}','{no_surat}','{total_tunggakan}','{jumlah_tagihan}','{nama_sekolah}','{nama_petugas}'],
        'Setoran Tabungan':['{nama_wali}','{nama_siswa}','{kelas}','{tanggal}','{no_transaksi}','{nominal}','{saldo}','{metode_pembayaran}','{nama_sekolah}','{nama_petugas}'],
        'Penarikan Tabungan':['{nama_wali}','{nama_siswa}','{kelas}','{tanggal}','{no_transaksi}','{nominal}','{saldo}','{metode_pembayaran}','{nama_sekolah}','{nama_petugas}']
    };
    const samples={'{nama_wali}':'Bapak/Ibu Contoh','{nama_siswa}':'Ahmad Fauzan','{kelas}':'4A','{tanggal}':'09-10-2026','{no_transaksi}':'BYR/202610/00001','{total_bayar}':'Rp250.000','{metode_pembayaran}':'Tunai','{nama_sekolah}':'SD Kreatif Muhammadiyah','{nama_petugas}':'Admin Jurnal','{no_surat}':'STG/202610/00001','{total_tunggakan}':'Rp350.000','{jumlah_tagihan}':'3 tagihan','{nominal}':'Rp100.000','{saldo}':'Rp450.000'};
    function defaultMessage(type){if(type==='Surat Tunggakan')return 'Yth. Bapak/Ibu {nama_wali}, kami menginformasikan tagihan {nama_siswa} kelas {kelas} yang masih tertunggak sebesar {total_tunggakan}. Terima kasih. - {nama_sekolah}';if(type==='Setoran Tabungan')return 'Yth. Bapak/Ibu {nama_wali}, setoran tabungan {nama_siswa} sebesar {nominal} pada {tanggal} telah tercatat. Saldo saat ini {saldo}. Terima kasih.';if(type==='Penarikan Tabungan')return 'Yth. Bapak/Ibu {nama_wali}, penarikan tabungan {nama_siswa} sebesar {nominal} pada {tanggal} telah tercatat. Saldo saat ini {saldo}. Terima kasih.';return 'Yth. Bapak/Ibu {nama_wali}, pembayaran {nama_siswa} sebesar {total_bayar} telah kami terima pada {tanggal}. Nomor transaksi: {no_transaksi}. Terima kasih.';}
    function refreshVariables(){let list=vars[$('#jenisTemplate').val()]||[];$('#variableInfo').text('Variabel: '+list.join(', '));}
    function resetForm(){document.getElementById('formTemplate').reset();$('#templateId').val('');$('#jenisTemplate').val('Bukti Pembayaran');$('#isiTemplate').val(defaultMessage('Bukti Pembayaran'));$('#templateDefault').val('Tidak');$('#templateStatus').val('Aktif');$('#previewBox').addClass('d-none').empty();$('#modalTitle').text('Tambah Template WhatsApp');refreshVariables();}
    function render(){let search=String($('#search').val()||'').toLowerCase(),jenis=$('#filterJenis').val(),status=$('#filterStatus').val();let data=rows.filter(r=>(!search||String(r.nama_template||'').toLowerCase().includes(search)||String(r.isi_template||'').toLowerCase().includes(search))&&(!jenis||r.jenis_template===jenis)&&(!status||r.status===status));$('#templateList').html(data.length?data.map((r,i)=>`<div class="sdk-record-card"><div class="sdk-record-head"><div><span>Status: </span><span class="badge ${r.status==='Aktif'?'bg-success':'bg-secondary'}">${r.status==='Aktif'?'Aktif':'Tidak Aktif'}</span>${r.status_default==='Ya'?' <span class="badge bg-primary">Default</span>':''}</div></div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${i+1}. ${esc(r.nama_template)}</h5><div class="sdk-record-meta"><span>Jenis: <strong>${esc(r.jenis_template)}</strong></span></div><div class="text-muted mt-1">${esc(String(r.isi_template||'').substring(0,220))}</div></div><div class="sdk-record-actions"><button class="btn btn-sm btn-outline-warning btn-edit" data-id="${r.id}" title="Edit"><i class="ri-edit-line"></i></button>${r.status_default!=='Ya'&&r.status==='Aktif'?`<button class="btn btn-sm btn-outline-primary btn-default" data-id="${r.id}" title="Jadikan Default"><i class="ri-star-line"></i></button>`:''}</div></div></div>`).join(''):'<div class="sdk-record-empty">Belum ada template WhatsApp sesuai filter.</div>');}
    function load(){request('data',{}).done(r=>{rows=r.data||[];render();}).fail(fail);}
    function openForm(row){resetForm();if(row){$('#modalTitle').text('Edit Template WhatsApp');$('#templateId').val(row.id);$('#jenisTemplate').val(row.jenis_template);$('#namaTemplate').val(row.nama_template);$('#isiTemplate').val(row.isi_template);$('#templateDefault').val(row.status_default);$('#templateStatus').val(row.status);refreshVariables();}modal.show();}
    function preview(){let txt=$('#isiTemplate').val()||'';Object.keys(samples).forEach(k=>txt=txt.split(k).join(samples[k]));$('#previewBox').removeClass('d-none').text(txt||'Isi pesan belum ditulis.');}
    function setDefault(id){Swal.fire({title:'Jadikan template default?',text:'Template default pada jenis yang sama akan diganti.',icon:'question',showCancelButton:true,confirmButtonText:'Ya',cancelButtonText:'Batal'}).then(x=>{if(!x.isConfirmed)return;request('set_default',{id:id}).done(r=>{Swal.fire(r.result==='true'?'Berhasil':'Gagal',r.message,r.result==='true'?'success':'error');if(r.result==='true')load();}).fail(fail);});}
    $('#btnTambah').on('click',()=>openForm(null));$('#btnCari').on('click',render);$('#search').on('keydown',e=>{if(e.key==='Enter')render();});$('#filterJenis,#filterStatus').on('change',render);$('#jenisTemplate').on('change',function(){refreshVariables();if(!$('#templateId').val())$('#isiTemplate').val(defaultMessage(this.value));$('#previewBox').addClass('d-none').empty();});$('#templateDefault').on('change',function(){if(this.value==='Ya')$('#templateStatus').val('Aktif');});$('#btnPreview').on('click',preview);
    $('#templateList').on('click','.btn-edit',function(){let id=Number($(this).data('id')),row=rows.find(x=>Number(x.id)===id);if(row)openForm(row);}).on('click','.btn-default',function(){setDefault(Number($(this).data('id')));});
    $('#btnSimpan').on('click',function(){if(!$.trim($('#namaTemplate').val())||!$.trim($('#isiTemplate').val()))return Swal.fire('Perhatian','Nama dan isi template wajib diisi.','warning');let btn=$(this).prop('disabled',true);request('simpan',$('#formTemplate').serialize()).done(r=>{if(r.result==='true'){modal.hide();load();}Swal.fire(r.result==='true'?'Berhasil':'Gagal',r.message,r.result==='true'?'success':'error');}).fail(fail).always(()=>btn.prop('disabled',false));});
    load();
});
</script>
