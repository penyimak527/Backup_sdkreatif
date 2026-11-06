<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="card">
    <div class="card-header border-bottom border-dashed">
        <h4 class="header-title mb-1">Riwayat Pembayaran</h4><small class="text-muted">Lihat detail, cetak bukti, kirim
            WhatsApp, atau batalkan transaksi langsung dari daftar.</small>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-xl-3 col-lg-4">
                <label class="form-label">Cari transaksi / siswa / NIS</label>
                <input class="form-control" id="history-search" placeholder="Cari transaksi / siswa / NIS">
            </div>
            <div class="col-xl-2 col-lg-4"><label class="form-label">Dari tanggal</label><input class="form-control"
                    id="history-start" placeholder="dd-mm-yyyy"></div>
            <div class="col-xl-2 col-lg-4"><label class="form-label">Sampai tanggal</label><input class="form-control"
                    id="history-end" placeholder="dd-mm-yyyy"></div>
            <div class="col-xl-2 col-lg-4"><label class="form-label">Metode</label><select class="form-select"
                    id="history-method">
                    <option value="">Semua</option><?php foreach ($metode as $m): ?>
                        <option value="<?= (int) $m['id'] ?>"><?= html_escape($m['nama_metode']) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="col-xl-2 col-lg-4"><label class="form-label">Status</label>
            <select class="form-select" id="history-status">
                    <option value="">Semua</option>
                    <option selected>Aktif</option>
                    <option >Dibatalkan</option>
                </select></div>
            <div class="col-xl-1 col-lg-2 d-grid align-self-end"><button class="btn btn-primary" id="history-filter"><i
                        class="ri-search-line"></i></button></div>
        </div>
        <div id="history-list" class="sdk-record-list"></div>
        <div class="sdk-pager-wrap">
            <div><small id="history-info" class="text-muted"></small>
                <div class="mt-2" id="history-pages"></div>
            </div>
            <div class="sdk-page-size"><label class="mb-0">Tampilkan</label><select id="history-limit"
                    class="form-select form-select-sm">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select><span>entri</span></div>
        </div>
    </div>
</div>

<div class="modal fade" id="history-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pembayaran</h5><button class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="history-detail"></div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>
<div class="modal fade" id="history-wa-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kirim Bukti Pembayaran via WhatsApp</h5><button class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><input type="hidden" id="wa-payment-id">
                <div class="mb-3"><label class="form-label d-block">Sumber Nomor WhatsApp</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check"><input class="form-check-input wa-source-history" type="radio"
                                name="wa-source-history" id="wa-history-parent" value="Orang Tua" checked><label
                                class="form-check-label" for="wa-history-parent">Orang Tua</label></div>
                        <div class="form-check"><input class="form-check-input wa-source-history" type="radio"
                                name="wa-source-history" id="wa-history-other" value="Nomor Lain"><label
                                class="form-check-label" for="wa-history-other">Nomor Lain</label></div>
                    </div><small class="text-muted">Data wali dari Master Wali Siswa digunakan sebagai nilai awal dan
                        dapat diubah sebelum dikirim.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Penerima / Wali</label>
                        <input id="wa-name" class="form-control" maxlength="150">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hubungan</label>
                        <input id="wa-relation" class="form-control" maxlength="50">
                    </div>
                    <div class="col-md-5" id="wa-history-parent-wrap">
                        <label class="form-label">Nomor HP Orang Tua</label>
                        <input id="wa-phone" class="form-control" inputmode="tel" maxlength="25">
                    </div>
                    <div class="col-12 d-none" id="wa-history-other-wrap">
                        <label class="form-label">Nomor Lain</label>
                        <input id="wa-other-phone" class="form-control" inputmode="tel" maxlength="25" placeholder="Contoh: 081234567890">
                        <small class="text-muted">Nomor alternatif hanya digunakan untuk pengiriman saat ini.</small>
                    </div>
                    <div class="col-12"><label class="form-label">Pesan</label>
                        <textarea id="wa-message" class="form-control" rows="6"></textarea>
                        <small id="wa-template" class="text-muted"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button
                    id="wa-send" class="btn btn-success"><i class="ri-whatsapp-line me-1"></i>Buka WhatsApp</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        if (typeof flatpickr === 'function') {
            flatpickr('#history-start, #history-end', {
                dateFormat: 'd-m-Y',
                allowInput: true
            });
        }
        const base = <?= json_encode($history_base ?? site_url('admin/transaksi/riwayat_pembayaran/')) ?>;
        const receipt = <?= json_encode(site_url('admin/transaksi/pembayaran/bukti/')) ?>;
        const modalDetail = bootstrap.Modal.getOrCreateInstance(document.getElementById('history-modal'));
        const modalWa = bootstrap.Modal.getOrCreateInstance(document.getElementById('history-wa-modal'));
        let rows = [],
            page = 1,
            limit = 10,
            waStudent = null;
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));
        const money = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');
        const request = (path, data) => $.ajax({
            url: base + path,
            type: 'POST',
            dataType: 'json',
            data: data || {}
        });
        const fail = x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Permintaan gagal diproses.', 'error');

        function render(rebuild) {
            let total = rows.length,
                max = Math.max(1, Math.ceil(total / limit));
            page = Math.min(page, max);
            let html = '';
            rows.slice((page - 1) * limit, page * limit).forEach(function(r, i) {
                let actions = `<button class="btn btn-sm btn-outline-info pay-detail" data-id="${r.id}" title="Detail"><i class="ri-eye-line"></i></button><a class="btn btn-sm btn-outline-secondary" target="_blank" href="${receipt}${r.id}" title="Bukti"><i class="ri-printer-line"></i></a>`;
                if (r.status_transaksi === 'Aktif') {
                    actions += `<button class="btn btn-sm btn-outline-success pay-wa" data-id="${r.id}" title="WhatsApp"><i class="ri-whatsapp-line"></i></button><button class="btn btn-sm btn-outline-danger pay-cancel" data-id="${r.id}" title="Batalkan"><i class="ri-forbid-line"></i></button>`;
                }
                html += `<div class="sdk-record-card"><div class="sdk-record-head"><span>No Transaksi: <strong>${esc(r.no_transaksi)}</strong></span><span class="badge ${r.status_transaksi === 'Aktif' ? 'bg-success' : 'bg-secondary'}">${esc(r.status_transaksi)}</span></div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(page - 1) * limit + i + 1}. ${esc(r.nama_siswa)}</h5><div class="sdk-record-meta"><span>NIS: <strong>${esc(r.nis || '-')}</strong></span><span>Kelas: <strong>${esc(r.nama_kelas || '-')}</strong></span><span>Tanggal: <strong>${esc(r.tanggal_transaksi)} ${esc(r.waktu_transaksi || '')}</strong></span><span>Total: <strong>${money(r.total_pembayaran)}</strong></span></div></div><div class="sdk-record-actions">${actions}</div></div></div>`;
            });
            $('#history-list').html(html || '<div class="sdk-record-empty">Tidak ada data pembayaran.</div>');
            $('#history-info').text(total + ' transaksi');
            if (rebuild !== false) SDKreatifUI.serverPager('#history-pages', {
                total: total,
                page: page,
                limit: limit
            }, p => {
                page = p.currentPage;
                render(false);
            });
            SDKreatifUI.refreshTooltips('#history-list');
        }

        function load() {
            request('result', {
                search: $('#history-search').val(),
                awal: $('#history-start').val(),
                akhir: $('#history-end').val(),
                id_metode: $('#history-method').val(),
                status: $('#history-status').val()
            }).done(function(r) {
                rows = Array.isArray(r) ? r : [];
                page = 1;
                render(true);
            }).fail(fail);
        }

        function detail(id) {
            request('detail', {
                id: id
            }).done(function(r) {
                if (r.result !== 'true') return Swal.fire('Gagal', r.message, 'error');
                let h = r.header,
                    parts = (r.detail || []).map(d => `<div class="border rounded p-2 mb-2"><div class="d-flex justify-content-between gap-2"><strong>${esc(d.nama_tagihan)}</strong><strong>${money(d.nominal_bayar)}</strong></div><small>${esc((d.nama_bulan || '') + ' ' + (d.tahun || ''))} · Sisa setelah bayar ${money(d.sisa_setelah)}</small></div>`).join('');
                let html = `<dl class="row"><dt class="col-sm-4">No Transaksi</dt><dd class="col-sm-8">${esc(h.no_transaksi)}</dd><dt class="col-sm-4">Siswa</dt><dd class="col-sm-8">${esc(h.nama_siswa)} (${esc(h.nis || '-')})</dd><dt class="col-sm-4">Kelas</dt><dd class="col-sm-8">${esc(h.nama_kelas || '-')}</dd><dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">${esc(h.tanggal_transaksi)} ${esc(h.waktu_transaksi || '')}</dd><dt class="col-sm-4">Metode</dt><dd class="col-sm-8">${esc(h.nama_metode_pembayaran || '-')}</dd><dt class="col-sm-4">Petugas</dt><dd class="col-sm-8">${esc(h.nama_user || '-')}</dd><dt class="col-sm-4">Status</dt><dd class="col-sm-8">${esc(h.status_transaksi)}</dd></dl><h6>Detail Tagihan</h6>${parts}<div class="d-flex justify-content-between border-top pt-2"><strong>Total</strong><strong>${money(h.total_pembayaran)}</strong></div>`;
                if (r.pembatalan) html += `<div class="alert alert-secondary mt-3 mb-0"><strong>Pembatalan:</strong> ${esc(r.pembatalan.alasan_pembatalan || '-')}<br><small>${esc(r.pembatalan.nama_user_pembatalan || '-')} · ${esc(r.pembatalan.tanggal_pembatalan || '')} ${esc(r.pembatalan.waktu_pembatalan || '')}</small></div>`;
                $('#history-detail').html(html);
                modalDetail.show();
            }).fail(fail);
        }

        function cancelPayment(id) {
            Swal.fire({
                title: 'Batalkan pembayaran?',
                input: 'textarea',
                inputLabel: 'Alasan pembatalan',
                inputPlaceholder: 'Masukkan alasan pembatalan...',
                showCancelButton: true,
                confirmButtonText: 'Batalkan Transaksi',
                confirmButtonColor: '#dc3545',
                preConfirm: v => {
                    if (!String(v || '').trim()) Swal.showValidationMessage('Alasan wajib diisi.');
                    return v;
                }
            }).then(r => {
                if (!r.isConfirmed) return;
                request('batalkan', {
                    id: id,
                    alasan: r.value
                }).done(function(res) {
                    Swal.fire(res.result === 'true' ? 'Berhasil' : 'Gagal', res.message, res.result === 'true' ? 'success' : 'error');
                    if (res.result === 'true') load();
                }).fail(fail);
            });
        }

        function toggleHistoryWaSource() {
            let src = $('input[name="wa-source-history"]:checked').val() || 'Orang Tua';
            $('#wa-history-parent-wrap').toggleClass('d-none', src !== 'Orang Tua');
            $('#wa-history-other-wrap').toggleClass('d-none', src !== 'Nomor Lain');
        }

        function openWa(id) {
            request('detail', {
                id: id
            }).done(function(d) {
                if (d.result !== 'true') return Swal.fire('Gagal', d.message, 'error');
                waStudent = d.siswa || {};
                $('#wa-payment-id').val(id);
                $('#wa-history-parent').prop('checked', true);
                $('#wa-history-other').prop('checked', false);
                $('#wa-name').val(waStudent.nama_wali || '');
                $('#wa-relation').val(waStudent.hubungan_wali || 'Wali');
                $('#wa-phone').val(waStudent.no_telp_wali || '');
                $('#wa-other-phone').val('');
                toggleHistoryWaSource();
                request('preview_whatsapp', {
                    id: id
                }).done(function(p) {
                    if (p.result === 'true') {
                        $('#wa-message').val(p.pesan || '').data('edited', false);
                        $('#wa-template').text('Template: ' + (p.nama_template || 'Default'));
                        modalWa.show();
                    } else Swal.fire('Gagal', p.message, 'error');
                }).fail(fail);
            }).fail(fail);
        }
        $('#wa-message').on('input', function() {
            $(this).data('edited', true);
        });
        $(document).on('change', '.wa-source-history', toggleHistoryWaSource);
        $('#wa-send').on('click', function() {
            let src = $('input[name="wa-source-history"]:checked').val() || 'Orang Tua';
            let phone = src === 'Nomor Lain' ? $.trim($('#wa-other-phone').val()) : $.trim($('#wa-phone').val());
            if (!$.trim($('#wa-name').val())) return Swal.fire('Perhatian', 'Nama penerima/wali wajib diisi.', 'warning');
            if (!phone) return Swal.fire('Perhatian', src === 'Nomor Lain' ? 'Nomor lain wajib diisi.' : 'Nomor HP orang tua/wali wajib diisi.', 'warning');
            let win = window.open('about:blank', '_blank');
            request('siapkan_whatsapp', {
                id: $('#wa-payment-id').val(),
                sumber_nomor: src,
                nama_penerima: $('#wa-name').val(),
                hubungan_penerima: $('#wa-relation').val(),
                nomor_whatsapp: phone,
                pesan: $('#wa-message').data('edited') ? $('#wa-message').val() : ''
            }).done(function(r) {
                if (r.result === 'true') {
                    modalWa.hide();
                    if (win) win.location = r.url;
                    else window.location = r.url;
                } else {
                    if (win) win.close();
                    Swal.fire('Gagal', r.message, 'error');
                }
            }).fail(function(x) {
                if (win) win.close();
                fail(x);
            });
        });
        $('#history-filter').on('click', load);
        $('#history-search').on('keydown', e => {
            if (e.key === 'Enter') load();
        });
        $('#history-limit').on('change', function() {
            limit = Number(this.value) || 10;
            page = 1;
            render(true);
        });
        $('#history-list').on('click', '.pay-detail', function() {
            detail($(this).data('id'));
        }).on('click', '.pay-wa', function() {
            openWa($(this).data('id'));
        }).on('click', '.pay-cancel', function() {
            cancelPayment($(this).data('id'));
        });
        load();
    });
</script>