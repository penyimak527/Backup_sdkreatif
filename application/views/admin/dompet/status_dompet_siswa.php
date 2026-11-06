<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="alert alert-info">Saldo hanya berubah melalui transaksi sumber. Halaman gabungan ini mengelola status wallet, limit, dan histori tanpa menyediakan edit bebas angka saldo.</div>
<div class="card">
    <div class="card-header border-bottom border-dashed">
        <h4 class="header-title mb-1">Status &amp; Saldo Siswa</h4>
        <small class="text-muted">Biodata siswa tetap dikelola melalui Master Data &gt; Siswa.</small>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input class="form-control" id="search" placeholder="Cari NIS atau nama"></div>
            <div class="col-md-3"><select class="form-select js-select2" id="walletStatusFilter"><option value="">Semua status dompet</option><option value="AKTIF">Aktif</option><option value="NONAKTIF">Nonaktif</option><option value="BELUM_DIAKTIFKAN">Belum diaktifkan</option></select></div>
            <div class="col-md-3"><select class="form-select js-select2" id="kelas"><option value="">Semua kelas aktif</option><?php foreach ($kelas as $row): ?><option value="<?= (int) $row['id'] ?>"><?= html_escape($row['nama_kelas'] . ' - ' . $row['periode']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100" id="filter">Terapkan</button></div>
        </div>
        <div id="rows" class="sdk-record-list"><div class="sdk-record-empty">Memuat data...</div></div>
        <div class="sdk-pager-wrap"><div><small id="info" class="text-muted"></small><div class="mt-2" id="walletPagination"></div></div><div class="sdk-page-size"><label for="walletPageSize" class="mb-0">Tampilkan</label><select class="form-select form-select-sm" id="walletPageSize"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span>entri</span></div></div>
    </div>
</div>

<div class="modal fade" id="limitModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><div><h5 class="modal-title mb-0">Atur Limit Individual</h5><small class="text-muted" id="limitStudentName"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><input type="hidden" id="limitStudentId"><div class="mb-3"><label class="form-label">Batas Harian</label><div class="input-group"><span class="input-group-text">Rp</span><input id="limitDaily" class="form-control rupiah-input" inputmode="numeric" autocomplete="off"></div></div><div class="mb-2"><label class="form-label">Batas per Transaksi</label><div class="input-group"><span class="input-group-text">Rp</span><input id="limitTransaction" class="form-control rupiah-input" inputmode="numeric" autocomplete="off"></div></div><div class="alert alert-info py-2 mb-0"><i class="ri-information-line me-1"></i>Isi keduanya dengan 0 untuk kembali memakai limit global.</div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="saveLimit">Simpan Limit</button></div>
</div></div></div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><div><h5 class="modal-title mb-0">Histori Saldo</h5><small class="text-muted" id="historyStudentName"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3 mb-4"><div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">Saldo Saat Ini</small><h5 class="mb-0 mt-1" id="historyBalance">Rp 0</h5></div></div><div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">Isi Saldo (50 terakhir)</small><h5 class="mb-0 mt-1" id="historyTopup">0 transaksi</h5><span class="text-muted" id="historyTopupNominal">Rp 0</span></div></div><div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">Transaksi Kantin (50 terakhir)</small><h5 class="mb-0 mt-1" id="historyCanteen">0 transaksi</h5><span class="text-muted" id="historyCanteenNominal">Rp 0</span></div></div></div><div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">50 transaksi terakhir</h6><small class="text-muted" id="historyCount"></small></div><div id="historyEntries" class="sdk-record-list"><div class="sdk-record-empty">Memuat histori...</div></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>

<script>
$(function () {
    const base = <?= json_encode(base_url('admin/dompet/status_dompet_siswa/'), JSON_UNESCAPED_SLASHES) ?>;
    const csrf = <?= json_encode($csrf_token) ?>;
    const canManage = <?= $can_manage ? 'true' : 'false' ?>;
    let current = 1, limit = 10;
    const limitModal = new bootstrap.Modal(document.getElementById('limitModal'));
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    const esc = value => $('<div>').text(value == null || value === '' ? '-' : value).html();
    const money = value => new Intl.NumberFormat('id-ID').format(Number(value) || 0);
    const request = (path, data) => $.post(base + path, data || {}, null, 'json');
    const fail = xhr => Swal.fire({icon: 'error', title: 'Tidak dapat diproses', text: (xhr.responseJSON || {}).message || 'Terjadi kesalahan.'});

    $('.js-select2').select2({width: '100%'});

    function load(rebuildPager) {
        request('result', {search: $('#search').val(), status_wallet: $('#walletStatusFilter').val(), id_kelas_setting: $('#kelas').val(), page: current, limit: limit})
            .done(function (response) {
                let html = '';
                (response.data || []).forEach(function (row, index) {
                    const wallet = row.id_wallet
                        ? `<span class="badge ${row.status_dompet === 'AKTIF' ? 'bg-success' : 'bg-secondary'}">${esc(row.status_dompet)}</span>`
                        : '<span class="badge bg-warning text-dark">BELUM DIAKTIFKAN</span>';
                    let actions = '';
                    if (!row.id_wallet) {
                        actions = canManage ? `<button class="btn btn-sm btn-primary activate" data-id="${row.id}" data-name="${esc(row.nama_lengkap)}" title="Aktifkan dompet" aria-label="Aktifkan dompet" data-bs-toggle="tooltip"><i class="ri-wallet-3-line"></i></button>` : '-';
                    } else {
                        if (canManage) {
                            actions += `<button class="btn btn-sm btn-outline-primary limit" data-id="${row.id}" data-name="${esc(row.nama_lengkap)}" data-h="${row.batas_harian_individual || 0}" data-t="${row.batas_transaksi_individual || 0}" title="Atur limit" aria-label="Atur limit" data-bs-toggle="tooltip"><i class="ri-settings-3-line"></i></button> `;
                            actions += `<button class="btn btn-sm btn-outline-secondary toggle" data-id="${row.id}" data-status="${row.status_dompet === 'AKTIF' ? 'NONAKTIF' : 'AKTIF'}" title="Ubah layanan" aria-label="Ubah layanan" data-bs-toggle="tooltip"><i class="ri-toggle-line"></i></button> `;
                        }
                        actions += `<button class="btn btn-sm btn-outline-info hist" data-id="${row.id}" data-name="${esc(row.nama_lengkap)}" title="Lihat histori" aria-label="Lihat histori" data-bs-toggle="tooltip"><i class="ri-history-line"></i></button>`;
                    }
                    html += `<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${esc(row.nis)}</strong> · Kelas: <strong>${esc(row.nama_kelas)}</strong></span>${wallet}</div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(current-1)*limit+index+1}. ${esc(row.nama_lengkap)}</h5><div class="sdk-record-details"><div class="sdk-record-detail">Saldo<strong>Rp ${money(row.saldo)}</strong></div><div class="sdk-record-detail">Limit ${esc(row.sumber_limit)}<strong>Harian Rp ${money(row.batas_harian)} · Transaksi Rp ${money(row.batas_transaksi)}</strong></div><div class="sdk-record-detail">QR / Wali<strong>${row.id_qr?esc(row.kode_tampil):'-'} · ${row.wali_aktif||0} wali aktif</strong></div></div></div><div class="sdk-record-actions">${actions}</div></div></div>`;
                });
                $('#rows').html(html || '<div class="sdk-record-empty">Tidak ada data.</div>');
                SDKreatifUI.refreshTooltips('#rows');
                current = response.meta.page || 1; $('#info').text((response.meta.total || 0) + ' siswa');
                if (rebuildPager !== false) SDKreatifUI.serverPager('#walletPagination', response.meta, function(paging){current=paging.currentPage;load(false);});
            }).fail(fail);
    }

    $('#filter').on('click', function () { current = 1; load(true); });
    $('#search').on('keydown', function (event) { if (event.key === 'Enter') { current = 1; load(true); } });
    $('#walletPageSize').on('change', function(){limit=parseInt(this.value,10)||10;current=1;load(true);});
    $('#rows').on('click', '.activate', function () {
        const id = $(this).data('id'), name = $(this).data('name');
        Swal.fire({icon: 'question', title: 'Aktifkan dompet?', text: 'Saldo awal ' + name + ' akan dibuat Rp 0 bersama satu QR aktif.', showCancelButton: true, confirmButtonText: 'Aktifkan', cancelButtonText: 'Batal'})
            .then(result => { if (result.isConfirmed) request('aktifkan', {id_siswa: id, web_csrf_token: csrf}).done(function (response) { Swal.fire('Berhasil', response.message, 'success'); load(); }).fail(fail); });
    });
    $('#rows').on('click', '.limit', function () {
        $('#limitStudentId').val($(this).data('id'));
        $('#limitStudentName').text($(this).data('name'));
        $('#limitDaily').val(SDKreatifMoney.format($(this).data('h') || 0));
        $('#limitTransaction').val(SDKreatifMoney.format($(this).data('t') || 0));
        limitModal.show();
    });
    $('#saveLimit').on('click', function () {
        const button=this,daily=SDKreatifMoney.parse($('#limitDaily').val()),transaction=SDKreatifMoney.parse($('#limitTransaction').val());
        if(daily>0&&transaction>daily)return Swal.fire('Limit tidak valid','Batas per transaksi tidak boleh melebihi batas harian.','warning');
        button.disabled=true;
        request('atur_limit',{id_siswa:$('#limitStudentId').val(),batas_harian:daily,batas_transaksi:transaction,web_csrf_token:csrf}).done(function(response){limitModal.hide();Swal.fire('Berhasil',response.message,'success');load();}).fail(fail).always(function(){button.disabled=false;});
    });
    $('#rows').on('click', '.toggle', function () {
        const id = $(this).data('id'), status = $(this).data('status');
        if (status === 'AKTIF') {
            request('status', {id_siswa: id, status: status, alasan: 'Diaktifkan kembali oleh Admin', web_csrf_token: csrf}).done(function (response) { Swal.fire('Berhasil', response.message, 'success'); load(); }).fail(fail);
            return;
        }
        Swal.fire({title: 'Alasan menonaktifkan layanan', input: 'text', inputAttributes: {maxlength: 255}, showCancelButton: true, inputValidator: value => (!value || value.trim().length < 5) ? 'Alasan minimal 5 karakter.' : null})
            .then(result => { if (result.isConfirmed) request('status', {id_siswa: id, status: status, alasan: result.value, web_csrf_token: csrf}).done(function (response) { Swal.fire('Berhasil', response.message, 'success'); load(); }).fail(fail); });
    });
    $('#rows').on('click', '.hist', function () {
        $('#historyStudentName').text($(this).data('name'));
        $('#historyEntries').html('<div class="sdk-record-empty">Memuat histori...</div>');
        historyModal.show();
        request('histori', {id_siswa: $(this).data('id')}).done(function (response) {
            const data=response.data||{},summary=data.summary||{},wallet=data.wallet||{};
            $('#historyStudentName').text((wallet.nis?wallet.nis+' · ':'')+(wallet.nama_lengkap||wallet.nama_siswa||$('#historyStudentName').text()));
            $('#historyBalance').text('Rp '+money(wallet.saldo));
            $('#historyTopup').text((summary.topup_count||0)+' transaksi');$('#historyTopupNominal').text('Rp '+money(summary.topup_nominal));
            $('#historyCanteen').text((summary.kantin_count||0)+' transaksi');$('#historyCanteenNominal').text('Rp '+money(summary.kantin_nominal));
            $('#historyCount').text((summary.total||0)+' data ditampilkan');
            let html='';(data.entries||[]).forEach(function(entry,index){const topup=entry.jenis==='ISI_SALDO',sign=topup?'+':'-',color=topup?'text-success':'text-danger',type=topup?'Isi Saldo':'Transaksi Kantin',balance=entry.saldo_sebelum==null||entry.saldo_sesudah==null?'Tidak tersedia':`Rp ${money(entry.saldo_sebelum)} → Rp ${money(entry.saldo_sesudah)}`;html+=`<div class="sdk-record-card"><div class="sdk-record-head"><span>${index+1}. <strong>${esc(entry.no_transaksi)}</strong> · ${esc(entry.tanggal)} ${esc(entry.waktu)}</span>${SDKreatifUI.statusBadge(entry.status)}</div><div class="sdk-record-main"><div class="flex-grow-1"><h6 class="sdk-record-title mb-2">${type}</h6><div class="sdk-record-meta"><span>Nominal: <strong class="${color}">${sign} Rp ${money(entry.nominal)}</strong></span><span>Saldo: <strong>${balance}</strong></span><span>Sumber: <strong>${esc(entry.sumber)}</strong></span>${entry.keterangan?`<span>Keterangan: <strong>${esc(entry.keterangan)}</strong></span>`:''}</div></div></div></div>`;});
            $('#historyEntries').html(html||'<div class="sdk-record-empty">Belum ada histori transaksi.</div>');
        }).fail(fail);
    });
    load(true);
});
</script>
