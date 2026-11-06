<div class="card">
    <div class="card-header app-card-header"><h4 class="header-title mb-0">Tagihan & Tunggakan</h4></div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-lg-3 col-md-6"><label class="form-label">Cari</label><input id="tg-search" class="form-control" placeholder="Nama / NIS / tagihan"></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Tahun Ajaran</label><select id="tg-periode" class="form-select"><option value="">Semua Tahun Ajaran</option><?php foreach($periode as $p): ?><option value="<?= (int)$p['id'] ?>"><?= html_escape($p['periode']) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Kelas</label><select id="tg-kelas" class="form-select"><option value="">Semua Kelas</option></select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Jenis Tagihan</label><select id="tg-jenis" class="form-select"><option value="">Semua Jenis</option><?php foreach($jenis as $j): ?><option value="<?= (int)$j['id'] ?>"><?= html_escape($j['nama_jenis']) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Data</label><select id="tg-mode" class="form-select"><option value="">Semua Belum Lunas</option><option value="Tunggakan">Tunggakan Saja</option></select></div>
            <div class="col-lg-1 d-grid"><button id="tg-show" class="btn btn-primary"><i class="ri-search-line"></i></button></div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><div class="border rounded p-3"><small class="text-muted">Jumlah Tagihan Belum Lunas</small><h5 class="mb-0 mt-1" id="tg-count">0</h5></div></div>
            <div class="col-md-6"><div class="border rounded p-3"><small class="text-muted">Total Sisa</small><h5 class="mb-0 mt-1 text-danger" id="tg-total">Rp0</h5></div></div>
        </div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Siswa</th><th>Kelas</th><th>Jenis Tagihan</th><th>Tagihan</th><th>Periode</th><th class="text-end">Tagihan</th><th class="text-end">Dibayar</th><th class="text-end">Sisa</th><th>Status</th></tr></thead><tbody id="tg-data"><tr><td colspan="9" class="text-center text-muted">Memuat data...</td></tr></tbody></table></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><ul id="tg-page" class="pagination pagination-sm pagination-boxed mb-0"></ul><select id="tg-size" class="form-select form-select-sm" style="width:90px"><option>10</option><option>25</option><option>50</option><option>100</option></select></div>
    </div>
</div>
<script>
(function(){const base=<?= json_encode(site_url('admin/tagihan/tagihan_tunggakan/')) ?>,e=id=>document.getElementById(id),esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])),money=n=>'Rp'+Number(n||0).toLocaleString('id-ID');let rows=[],page=1;
function loadKelas(){const p=e('tg-periode').value;$.post(base+'kelas_result',{id_periode:p},r=>{e('tg-kelas').innerHTML='<option value="">Semua Kelas</option>'+(r||[]).map(x=>`<option value="${x.id}">${esc(x.nama_kelas)}</option>`).join('');},'json');}
function load(){e('tg-data').innerHTML='<tr><td colspan="9" class="text-center text-muted">Memuat data...</td></tr>';$.post(base+'result',{search:e('tg-search').value,id_periode:e('tg-periode').value,id_kelas_setting:e('tg-kelas').value,id_jenis_tagihan:e('tg-jenis').value,mode:e('tg-mode').value},r=>{rows=r.rows||[];page=1;e('tg-count').textContent=Number(r.summary?.jumlah||0).toLocaleString('id-ID');e('tg-total').textContent=money(r.summary?.total_sisa);draw();},'json').fail(x=>Swal.fire('Gagal',x?.responseJSON?.message||'Data gagal dimuat','error'));}
function periode(r){return r.nama_bulan?`${esc(r.nama_bulan)} ${esc(r.tahun||'')}`:esc(r.periode||'-');}
function draw(){const size=+e('tg-size').value,pages=Math.max(1,Math.ceil(rows.length/size));page=Math.min(page,pages);const data=rows.slice((page-1)*size,page*size);e('tg-data').innerHTML=data.length?data.map(r=>`<tr><td>${esc(r.nama_siswa)}<br><small>${esc(r.nis||'-')}</small></td><td>${esc(r.nama_kelas||'-')}</td><td>${esc(r.nama_jenis_tagihan||'-')}</td><td>${esc(r.nama_tagihan||'-')}</td><td>${periode(r)}</td><td class="text-end">${money(r.nominal_tagihan)}</td><td class="text-end">${money(r.nominal_dibayar)}</td><td class="text-end fw-semibold text-danger">${money(r.sisa_tagihan)}</td><td><span class="badge ${r.status_tunggakan==='Tunggakan'?'bg-danger':'bg-warning text-dark'}">${esc(r.status_tunggakan)}</span><br><small>${esc(r.status_pembayaran||'')}</small></td></tr>`).join(''):'<tr><td colspan="9" class="text-center text-muted">Tidak ada data.</td></tr>';e('tg-page').innerHTML=rows.length>size?Array.from({length:pages},(_,i)=>`<li class="page-item ${page===i+1?'active':''}"><button class="page-link" data-page="${i+1}">${i+1}</button></li>`).join(''):'';e('tg-page').querySelectorAll('[data-page]').forEach(b=>b.onclick=()=>{page=+b.dataset.page;draw();});}
e('tg-periode').onchange=()=>{loadKelas();};e('tg-show').onclick=load;e('tg-size').onchange=()=>{page=1;draw();};e('tg-search').onkeyup=x=>{if(x.key==='Enter')load();};loadKelas();load();})();
</script>
