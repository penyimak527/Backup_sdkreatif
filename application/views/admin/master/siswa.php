<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <div>
            <h4 class="header-title mb-1">Master Data Siswa</h4>
            <small class="text-muted">Biodata siswa dikelola di sini. Aktivasi dompet dilakukan dari modul Dompet Digital.</small>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('admin/master/siswa/alumni') ?>"><i class="ri-graduation-cap-line me-1"></i>Alumni</a>
            <a class="btn btn-sm btn-outline-success" href="<?= base_url('admin/master/siswa/export') ?>"><i class="ri-file-excel-2-line me-1"></i>Export</a>
            <button class="btn btn-sm btn-outline-primary" type="button" id="btnImport"><i class="ri-upload-2-line me-1"></i>Import</button>
            <button class="btn btn-sm btn-outline-danger" type="button" id="btnTrash"><i class="ri-delete-bin-6-line me-1"></i>Trash</button>
            <button class="btn btn-sm btn-primary" type="button" id="btnTambah"><i class="ri-add-line me-1"></i>Tambah</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-lg-4"><input class="form-control" id="filterSearch" placeholder="Cari NIS, NISN, atau nama"></div>
            <div class="col-lg-2"><select class="form-select js-filter-select" id="filterStatus"><option value="">Semua status</option><option>AKTIF</option><option>NONAKTIF</option><option>ALUMNI</option></select></div>
            <div class="col-lg-2"><select class="form-select js-filter-select" id="filterPeriode"><option value="">Semua tahun ajaran</option><?php foreach ($periode as $row): ?><option value="<?= (int) $row['id'] ?>"><?= html_escape($row['periode']) ?><?= strcasecmp($row['status'], 'Aktif') === 0 ? ' (Aktif)' : '' ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-3"><select class="form-select js-filter-select" id="filterKelas"><option value="">Semua kelas</option><?php foreach ($kelas as $row): ?><option value="<?= (int) $row['id'] ?>" data-periode="<?= (int) $row['id_periode'] ?>"><?= html_escape($row['nama_kelas'] . ' - ' . $row['periode']) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-1"><button class="btn btn-outline-secondary w-100" id="btnFilter" type="button"><i class="ri-search-line"></i></button></div>
        </div>
        <div id="tableBody" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div>
        <div class="sdk-pager-wrap"><div><small id="tableInfo" class="text-muted"></small><div class="mt-2" id="studentPagination"></div></div><div class="sdk-page-size"><label for="studentPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="studentPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="formTitle">Tambah Siswa</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="studentForm"><input type="hidden" name="id" id="studentId"><input type="hidden" name="web_csrf_token" value="<?= html_escape($csrf_token) ?>">
        <div class="row g-2">
            <div class="col-md-4"><label class="form-label">NIS *</label><input class="form-control" name="nis" maxlength="50" required></div>
            <div class="col-md-4"><label class="form-label">NISN</label><input class="form-control" name="nisn" maxlength="50" inputmode="numeric"></div>
            <div class="col-md-4"><label class="form-label">Jenis Kelamin *</label><select class="form-select js-form-select" name="jk" required><option value="">Pilih</option><option value="Laki-laki">Laki-laki</option><option value="Perempuan">Perempuan</option></select></div>
            <div class="col-12"><label class="form-label">Nama Lengkap *</label><input class="form-control" name="nama_lengkap" maxlength="225" required></div>
            <div class="col-md-6"><label class="form-label">Tempat Lahir</label><input class="form-control" name="tempat_lahir" maxlength="225"></div>
            <div class="col-md-6"><label class="form-label">Tanggal Lahir</label><input class="form-control" type="date" name="tanggal_lahir"></div>
            <div class="col-12"><label class="form-label">Alamat Siswa</label><textarea class="form-control" name="alamat_siswa" maxlength="255"></textarea></div>
            <div class="col-12"><label class="form-label" for="studentClass">Kelas *</label><select class="form-select" name="id_kelas" id="studentClass" required><option value="">Pilih kelas</option><?php foreach ($kelas_master as $row): ?><option value="<?= (int) $row['id'] ?>"><?= html_escape(trim($row['nama_kelas'].' '.$row['kode_kelas'])) ?></option><?php endforeach; ?></select><small class="text-muted">Pilihan berasal langsung dari master Data Kelas.</small></div>
            <div class="col-12"><label class="form-label" for="studentGuardians">Orang Tua/Wali Siswa</label><select class="form-select" name="id_wali_siswa" id="studentGuardians" data-placeholder="Pilih orang tua atau wali"><option value="">Pilih orang tua atau wali</option><?php foreach ($wali_options as $wali): ?><option value="<?= (int) $wali['id'] ?>"><?= html_escape($wali['nama_wali'].($wali['hubungan'] ? ' - '.$wali['hubungan'] : '').($wali['no_telp'] ? ' · '.$wali['no_telp'] : '')) ?></option><?php endforeach; ?></select><small class="text-muted">Satu siswa hanya dapat memilih satu wali. Data wali tetap dikelola melalui menu Wali Siswa.</small></div>
        </div><div class="text-danger small mt-2" id="formErrors"></div>
    </form></div>
    <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-primary" id="saveStudent">Simpan</button></div>
</div></div></div>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Detail Siswa</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="detailBody"></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div></div></div></div>

<div class="modal fade" id="modalStatus" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Ubah Status Siswa</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="statusId"><label class="form-label">Status</label><select class="form-select" id="statusValue"><option>AKTIF</option><option>NONAKTIF</option><option>ALUMNI</option></select><label class="form-label mt-2">Alasan/Catatan</label><textarea class="form-control" id="statusReason" maxlength="1000"></textarea></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-primary" id="saveStatus">Simpan</button></div></div></div></div>

<div class="modal fade" id="modalClass" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Pindah Kelas atau Kelulusan</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="classStudentId"><div class="mb-3"><label class="form-label">Proses</label><div class="d-flex gap-4"><div class="form-check"><input class="form-check-input" type="radio" name="classMode" id="modeMove" value="PINDAH" checked><label class="form-check-label" for="modeMove">Pindah kelas</label></div><div class="form-check d-none" id="graduateModeWrap"><input class="form-check-input" type="radio" name="classMode" id="modeGraduate" value="LULUS"><label class="form-check-label" for="modeGraduate">Luluskan siswa</label></div></div></div><div id="moveClassFields"><label class="form-label">Kelas Tujuan pada Periode Aktif</label><select class="form-select" id="classValue"><option value="">Pilih kelas tujuan</option><?php foreach ($kelas as $row): ?><?php if (strcasecmp((string) $row['status_periode'], 'Aktif') === 0): ?><option value="<?= (int) $row['id'] ?>"><?= html_escape($row['nama_kelas'] . ' - ' . $row['periode'] . ' ' . $row['semester']) ?></option><?php endif; ?><?php endforeach; ?></select></div><div id="graduationFields" class="d-none"><label class="form-label" for="graduationYear">Tahun Kelulusan</label><select class="form-select" id="graduationYear"><option value="">Pilih tahun kelulusan</option><?php for ($year = (int) date('Y') + 1; $year >= (int) date('Y') - 15; $year--): ?><option value="<?= $year ?>" <?= $year === (int) date('Y') ? 'selected' : '' ?>><?= $year ?></option><?php endfor; ?></select><div class="alert alert-warning mt-3 mb-0">Kelulusan mengubah status siswa menjadi ALUMNI dan menonaktifkan penempatan kelas aktif.</div></div><small class="text-muted d-block mt-2" id="currentClassInfo">Kelas aktif saat ini akan langsung terpilih.</small></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-primary" id="saveClass">Simpan</button></div></div></div></div>

<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Import Data Siswa</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-info">Gunakan susunan kolom dari file contoh. NIS yang sudah ada akan dilewati, bukan ditimpa.</div><label class="form-label" for="importFile">File Excel (.xlsx)</label><input class="form-control" type="file" id="importFile" accept=".xlsx"><a class="btn btn-sm btn-link px-0 mt-2" href="<?= base_url('admin/master/siswa/contoh_import') ?>"><i class="ri-download-line me-1"></i>Unduh contoh import data siswa</a><div id="importResult" class="small mt-3"></div></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-primary" id="saveImport"><i class="ri-upload-2-line me-1"></i>Import</button></div></div></div></div>

<div class="modal fade" id="modalTrash" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title mb-0">Trash Siswa</h5><small class="text-muted">Data dapat dipulihkan dan tidak dihapus permanen.</small></div><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="trashRows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div></div></div></div>

<script>
$(function () {
    const base = <?= json_encode(base_url('admin/master/siswa/'), JSON_UNESCAPED_SLASHES) ?>;
    const csrf = <?= json_encode($csrf_token) ?>;
    let page = 1, limit = 10, editMode = false;
    const modalForm = new bootstrap.Modal(document.getElementById('modalForm'));
    const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
    const modalStatus = new bootstrap.Modal(document.getElementById('modalStatus'));
    const modalClass = new bootstrap.Modal(document.getElementById('modalClass'));
    const modalImport = new bootstrap.Modal(document.getElementById('modalImport'));
    const modalTrash = new bootstrap.Modal(document.getElementById('modalTrash'));
    const esc = value => $('<div>').text(value == null || value === '' ? '-' : value).html();

    $('.js-filter-select').select2({width: '100%', minimumResultsForSearch: 8});
    $('.js-form-select').select2({width: '100%', dropdownParent: $('#modalForm'), minimumResultsForSearch: 8});
    $('#studentClass').select2({width: '100%', dropdownParent: $('#modalForm'), placeholder: 'Pilih kelas'});
    $('#studentGuardians').select2({width: '100%', dropdownParent: $('#modalForm'), placeholder: 'Pilih orang tua atau wali', allowClear: true});
    $('#statusValue').select2({width: '100%', dropdownParent: $('#modalStatus'), minimumResultsForSearch: Infinity});
    $('#classValue').select2({width: '100%', dropdownParent: $('#modalClass'), placeholder: 'Pilih kelas'});
    $('#graduationYear').select2({width: '100%', dropdownParent: $('#modalClass'), minimumResultsForSearch: Infinity});

    function request(path, data) {
        return $.ajax({url: base + path, type: 'POST', dataType: 'json', data: data || {}});
    }
    function alertError(xhr) {
        const res = xhr.responseJSON || {};
        Swal.fire({icon: 'error', title: 'Tidak dapat diproses', text: res.message || 'Terjadi kesalahan pada server.'});
    }
    function normalizeDate(value) {
        value = String(value == null ? '' : value).trim();
        let match = value.match(/^(\d{2})-(\d{2})-(\d{4})$/);
        if (match) return `${match[3]}-${match[2]}-${match[1]}`;
        return /^\d{4}-\d{2}-\d{2}$/.test(value) && value !== '0000-00-00' ? value : '';
    }
    function normalizeGender(value) {
        value = String(value == null ? '' : value).trim().toLowerCase();
        if (value.indexOf('laki') !== -1) return 'Laki-laki';
        if (value.indexOf('perempuan') !== -1) return 'Perempuan';
        return '';
    }
    function setSelectValue($select, value) {
        value = String(value == null ? '' : value).trim();
        if (value !== '' && !$select.find('option').filter(function () { return String(this.value) === value; }).length) {
            $select.append(new Option(value, value));
        }
        $select.val(value).trigger('change');
    }
    function resetStudentForm() {
        $('#studentForm')[0].reset();
        $('#studentId').val('');
        setSelectValue($('#studentForm [name="jk"]'), '');
        $('#studentClass').val('').trigger('change');
        $('#studentGuardians').val(null).trigger('change');
    }
    function guardianList(guardians) {
        guardians = guardians || [];
        if (!guardians.length) return '<span class="text-muted">Belum ada wali aktif yang dihubungkan.</span>';
        return guardians.map(function (guardian) {
            const relation = guardian.hubungan ? ` <span class="text-muted">(${esc(guardian.hubungan)})</span>` : '';
            const phone = guardian.no_telp ? ` · ${esc(guardian.no_telp)}` : '';
            const primary = guardian.status_utama === 'YA' ? ' <span class="badge bg-primary ms-1">Utama</span>' : '';
            return `<div class="mb-1"><strong class="fw-bold" style="color:#000">${esc(guardian.nama_wali)}</strong>${relation}${phone}${primary}</div>`;
        }).join('');
    }
    function fillStudentForm(student) {
        const fields = ['nis','nisn','nama_lengkap','tempat_lahir','alamat_siswa'];
        resetStudentForm();
        fields.forEach(function (field) { $('#studentForm [name="' + field + '"]').val(student[field] == null ? '' : student[field]); });
        const guardian = (student.wali_terhubung || [])[0];
        $('#studentGuardians').val(guardian ? String(guardian.id) : '').trigger('change');
        $('#studentClass').val(student.kelas_aktif ? String(student.kelas_aktif.id_kelas) : '').trigger('change');
        $('#studentForm [name="tanggal_lahir"]').val(normalizeDate(student.tanggal_lahir));
        setSelectValue($('#studentForm [name="jk"]'), normalizeGender(student.jk));
        $('#studentId').val(student.id);
    }
    function load(rebuildPager) {
        request('result', {search: $('#filterSearch').val(), status: $('#filterStatus').val(), id_periode: $('#filterPeriode').val(), id_kelas_setting: $('#filterKelas').val(), page: page, limit: limit})
            .done(function (res) {
                const rows = res.data || []; const meta = res.meta || {};
                page = meta.page || 1;
                let html = '';
                rows.forEach(function (r, index) {
                    const wallet = r.indikator_dompet === 'AKTIF' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-warning text-dark">Belum diaktifkan</span>';
                    html += `<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${esc(r.nis)}</strong>${r.nisn?` · NISN: ${esc(r.nisn)}`:''}</span>${SDKreatifUI.statusBadge(r.status_siswa)}</div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page-1)*limit+index+1}. ${esc(r.nama_lengkap)}</h5><div class="sdk-record-meta"><span>Jenis Kelamin: <strong>${esc(r.jk)}</strong></span><span>Kelas: <strong>${esc(r.nama_kelas)}</strong></span><span>Tahun Ajaran: <strong>${esc(r.periode)}</strong></span><span>Dompet: ${wallet}</span></div></div><div class="sdk-record-actions"><button class="btn btn-sm btn-outline-info detail" data-id="${r.id}" title="Detail" aria-label="Detail"><i class="ri-eye-line"></i></button><button class="btn btn-sm btn-outline-primary edit" data-id="${r.id}" title="Edit" aria-label="Edit"><i class="ri-edit-line"></i></button><button class="btn btn-sm btn-outline-secondary status" data-id="${r.id}" data-status="${esc(r.status_siswa)}" title="Ubah status" aria-label="Ubah status"><i class="ri-toggle-line"></i></button><button class="btn btn-sm btn-outline-success class" data-id="${r.id}" title="Penempatan kelas" aria-label="Penempatan kelas"><i class="ri-group-line"></i></button><button class="btn btn-sm btn-outline-danger trash" data-id="${r.id}" title="Pindahkan ke Trash" aria-label="Pindahkan ke Trash"><i class="ri-delete-bin-6-line"></i></button></div></div></div>`;
                });
                $('#tableBody').html(html || '<div class="sdk-record-empty">Tidak ada data.</div>');
                SDKreatifUI.refreshTooltips('#tableBody');
                $('#tableInfo').text(`${meta.total || 0} siswa`);
                if (rebuildPager !== false) SDKreatifUI.serverPager('#studentPagination', meta, function(paging){page=paging.currentPage;load(false);});
            }).fail(alertError);
    }
    function getDetail(id, callback) { request('detail', {id: id}).done(function (res) { callback(res.data); }).fail(alertError); }
    $('#btnFilter').on('click', function(){page=1;load(true);});
    $('#filterSearch').on('keydown', function(e){if(e.key==='Enter'){page=1;load(true);}});
    $('#filterPeriode').on('change', function(){const p=this.value; $('#filterKelas option').each(function(){ $(this).prop('disabled', !!p && !!this.value && $(this).data('periode') != p); }); $('#filterKelas').val('').trigger('change.select2');});
    $('#studentPageSize').on('change', function(){limit=parseInt(this.value,10)||10;page=1;load(true);});
    $('#btnTambah').on('click', function(){editMode=false; resetStudentForm(); $('#formTitle').text('Tambah Siswa'); $('#formErrors').empty(); modalForm.show();});
    $('#tableBody').on('click', '.detail', function(){getDetail($(this).data('id'), function(r){let classes=(r.kelas||[]).map(k=>`${esc(k.nama_kelas)} - ${esc(k.periode)} (${esc(k.status_aktif)})`).join('<br>')||'-'; $('#detailBody').html(`<dl class="row"><dt class="col-sm-3">NIS/NISN</dt><dd class="col-sm-9">${esc(r.nis)} / ${esc(r.nisn)}</dd><dt class="col-sm-3">Nama</dt><dd class="col-sm-9">${esc(r.nama_lengkap)}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">${esc(r.status_siswa)}</dd><dt class="col-sm-3">Kelas</dt><dd class="col-sm-9">${classes}</dd><dt class="col-sm-3">Dompet</dt><dd class="col-sm-9">${r.dompet ? esc(r.dompet.status_dompet) : 'DOMPET BELUM DIAKTIFKAN'}</dd><dt class="col-sm-3">Alamat</dt><dd class="col-sm-9">${esc(r.alamat_siswa)}</dd><dt class="col-sm-3">Wali Terhubung</dt><dd class="col-sm-9">${guardianList(r.wali_terhubung)}</dd><dt class="col-sm-3">Catatan</dt><dd class="col-sm-9">${esc(r.catatan)}</dd></dl>`); modalDetail.show();});});
    $('#tableBody').on('click', '.edit', function(){getDetail($(this).data('id'), function(r){editMode=true; fillStudentForm(r); $('#formTitle').text('Edit Siswa'); $('#formErrors').empty(); modalForm.show();});});
    $('#tableBody').on('click', '.status', function(){ $('#statusId').val($(this).data('id')); $('#statusValue').val($(this).data('status')).trigger('change'); $('#statusReason').val(''); modalStatus.show(); });
    $('#tableBody').on('click', '.class', function(){const id=$(this).data('id');getDetail(id,function(r){const active=r.kelas_aktif||null,isGradeSix=!!(active&&/^6(?:\D|$)/i.test(String(active.nama_kelas).trim()));$('#classStudentId').val(id);$('#classValue').val(active?String(active.id_kelas_setting):'').trigger('change');$('#graduateModeWrap').toggleClass('d-none',!isGradeSix);$('#modeMove').prop('checked',true).trigger('change');$('#currentClassInfo').text(active?'Kelas aktif saat ini: '+active.nama_kelas+' - '+active.periode+' '+active.semester:'Siswa belum mempunyai kelas pada periode aktif.');modalClass.show();});});
    $('#saveStudent').on('click', function(){const path=editMode?'edit':'tambah'; const data=$('#studentForm').serialize(); request(path,data).done(function(res){modalForm.hide(); Swal.fire({icon:'success',title:'Berhasil',text:res.message});load();}).fail(function(xhr){const res=xhr.responseJSON||{}; $('#formErrors').html(Object.values(res.errors||{}).map(esc).join('<br>')); alertError(xhr);});});
    $('#saveStatus').on('click', function(){request('status',{id:$('#statusId').val(),status:$('#statusValue').val(),alasan:$('#statusReason').val(),web_csrf_token:csrf}).done(function(res){modalStatus.hide();Swal.fire({icon:'success',title:'Berhasil',text:res.message});load();}).fail(alertError);});
    $('input[name="classMode"]').on('change',function(){const graduate=$('input[name="classMode"]:checked').val()==='LULUS';$('#moveClassFields').toggleClass('d-none',graduate);$('#graduationFields').toggleClass('d-none',!graduate);});
    $('#saveClass').on('click', function(){const mode=$('input[name="classMode"]:checked').val();request('pindah_kelas',{id_siswa:$('#classStudentId').val(),mode:mode,id_kelas_setting:$('#classValue').val(),tahun_lulus:$('#graduationYear').val(),web_csrf_token:csrf}).done(function(res){modalClass.hide();Swal.fire({icon:'success',title:'Berhasil',text:res.message});load();}).fail(alertError);});
    $('#tableBody').on('click', '.trash', function(){const id=$(this).data('id');Swal.fire({title:'Pindahkan siswa ke Trash?',text:'Data kelas, wali, dompet, dan transaksi tetap tersimpan.',icon:'warning',showCancelButton:true,confirmButtonText:'Pindahkan'}).then(function(result){if(result.isConfirmed)request('hapus',{id:id,web_csrf_token:csrf}).done(function(res){Swal.fire('Berhasil',res.message,'success');load(true);}).fail(alertError);});});
    function loadTrash(){request('trash',{}).done(function(res){let html='';(res.data||[]).forEach(function(row,index){html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${esc(row.nis)}</strong></span><span class="badge bg-danger">Trash</span></div><div class="sdk-record-main"><div class="flex-grow-1"><h6 class="sdk-record-title">${index+1}. ${esc(row.nama_lengkap)}</h6><div class="sdk-record-meta"><span>Dihapus: <strong>${esc(row.tanggal)} ${esc(row.waktu)}</strong></span><span>Oleh: <strong>${esc(row.nama_user)}</strong></span></div></div><div class="sdk-record-actions"><button class="btn btn-sm btn-outline-success restore-student" data-id="${row.id}" title="Pulihkan" aria-label="Pulihkan"><i class="ri-arrow-go-back-line"></i></button></div></div></div>`;});$('#trashRows').html(html||'<div class="sdk-record-empty">Trash siswa kosong.</div>');}).fail(alertError);}
    $('#btnTrash').on('click',function(){loadTrash();modalTrash.show();});
    $('#trashRows').on('click','.restore-student',function(){const id=$(this).data('id');request('restore',{id:id,web_csrf_token:csrf}).done(function(res){Swal.fire('Berhasil',res.message,'success');loadTrash();load(true);}).fail(alertError);});
    $('#btnImport').on('click',function(){$('#importFile').val('');$('#importResult').empty();modalImport.show();});
    $('#saveImport').on('click',function(){const file=$('#importFile')[0].files[0];if(!file)return Swal.fire('File belum dipilih','Pilih file Excel .xlsx.','warning');const button=this,data=new FormData();data.append('file_excel',file);data.append('web_csrf_token',csrf);button.disabled=true;$.ajax({url:base+'import',type:'POST',data:data,dataType:'json',processData:false,contentType:false}).done(function(res){const result=res.data||{},errors=result.errors||[],accounts=result.generated_accounts||[];const accountHtml=accounts.length?'<hr class="my-2"><strong>Akun wali yang dibuat otomatis (simpan sekarang):</strong><div class="mt-1" style="max-height:180px;overflow:auto">'+accounts.map(a=>`${esc(a.nis)} · ${esc(a.nama_wali)}: <code>${esc(a.username)}</code> / <code>${esc(a.password)}</code>`).join('<br>')+'</div>':'';$('#importResult').html(`<div class="alert alert-${errors.length?'warning':'success'} mb-0"><strong>${result.imported||0}</strong> siswa berhasil diimport, <strong>${result.skipped||0}</strong> dilewati.${errors.length?'<hr class="my-2"><div style="max-height:180px;overflow:auto">'+errors.map(esc).join('<br>')+'</div>':''}${accountHtml}</div>`);load(true);}).fail(alertError).always(function(){button.disabled=false;});});
    load(true);
});
</script>
