<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    $adminExport = $this->session->userdata('admin');
    $petugasExport = is_array($adminExport)
        ? ($adminExport['nama'] ?? ($adminExport['nama_user'] ?? ($adminExport['username'] ?? '-')))
        : '-';
?>
<div class="card">
            <div class="card-header border-bottom border-dashed d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="header-title mb-1">Riwayat Tabungan</h4><small class="text-muted">Riwayat seluruh setoran dan penarikan siswa.</small>
                </div>
                <div class="d-flex gap-2"><button id="btnPrintHistory" class="btn btn-sm btn-outline-secondary"><i class="ri-printer-line me-1"></i>Cetak</button><button id="btnExportHistory" class="btn btn-sm btn-outline-success"><i class="ri-file-excel-2-line me-1"></i>Excel</button></div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <div class="col-lg-4 col-md-6"><label class="form-label">Cari</label><input id="historySearch" class="form-control" placeholder="Siswa / NIS / No transaksi"></div>
                    <div class="col-lg-3 col-md-6"><label class="form-label">Kelas</label><select id="historyClass" class="form-select">
                            <option value="">Semua Kelas</option><?php foreach ($kelas as $k): ?><option value="<?= (int)$k['id'] ?>"><?= html_escape($k['nama_kelas']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-lg-2 col-md-6"><label class="form-label">Jenis</label><select id="historyType" class="form-select">
                            <option value="">Semua</option>
                            <option>Setoran</option>
                            <option>Penarikan</option>
                        </select></div>
                    <div class="col-lg-3 col-md-6"><label class="form-label">Status</label><select id="historyStatus" class="form-select">
                            <option value="">Semua</option>
                            <option>Aktif</option>
                            <option>Dibatalkan</option>
                        </select></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-lg-3 col-md-6"><label class="form-label">Dari Tanggal</label><input id="historyStart" type="text" class="form-control" placeholder="Pilih tanggal"></div>
                    <div class="col-lg-3 col-md-6"><label class="form-label">Sampai Tanggal</label><input id="historyEnd" type="text" class="form-control" placeholder="Pilih tanggal"></div>
                    <div class="col-lg-2 col-md-4 d-grid align-self-end"><button type="button" id="btnHistoryFilter" class="btn btn-primary"><i class="ri-search-line me-1"></i>Cari</button></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100"><small class="text-muted">Total Setoran Aktif</small>
                            <h4 class="mb-0 text-primary" id="historySummaryDeposit">Rp0</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100"><small class="text-muted">Total Penarikan Aktif</small>
                            <h4 class="mb-0 text-warning" id="historySummaryWithdraw">Rp0</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100"><small class="text-muted">Selisih / Saldo Bersih</small>
                            <h4 class="mb-0 text-success" id="historySummaryBalance">Rp0</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded p-3 h-100"><small class="text-muted">Jumlah Transaksi</small>
                            <h4 class="mb-0" id="historySummaryCount">0</h4><small class="text-muted" id="historySummaryStatus">0 aktif · 0 dibatalkan</small>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Transaksi / Siswa</th>
                                <th>Jenis</th>
                                <th class="text-end">Nominal</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="historyRows">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="sdk-pager-wrap">
                    <div><small id="historyInfo" class="text-muted"></small>
                        <div class="mt-2" id="historyPagination"></div>
                    </div>
                    <div class="sdk-page-size"><label class="mb-0">Tampilkan</label><select id="historyLimit" class="form-select form-select-sm">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select><span>entri</span></div>
                </div>
            </div>
        </div>

<div class="modal fade" id="modalDetailTabungan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi Tabungan</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailTabunganBody"></div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalWhatsappTabungan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kirim WhatsApp Tabungan</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><input type="hidden" id="waJenis"><input type="hidden" id="waId">
                <div class="mb-3"><label class="form-label d-block">Sumber Nomor WhatsApp</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check"><input class="form-check-input wa-source-tabungan" type="radio" name="waSourceTabungan" id="waSourceParent" value="Orang Tua" checked><label class="form-check-label" for="waSourceParent">Orang Tua</label></div>
                        <div class="form-check"><input class="form-check-input wa-source-tabungan" type="radio" name="waSourceTabungan" id="waSourceOther" value="Nomor Lain"><label class="form-check-label" for="waSourceOther">Nomor Lain</label></div>
                    </div><small class="text-muted">Data wali dari Master Wali Siswa menjadi nilai awal dan dapat diedit sebelum dikirim.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Nama Penerima / Wali</label><input id="waName" class="form-control" maxlength="150"></div>
                    <div class="col-md-3"><label class="form-label">Hubungan</label><input id="waRelation" class="form-control" maxlength="50"></div>
                    <div class="col-md-5" id="waParentPhoneWrap"><label class="form-label">Nomor HP Orang Tua</label><input id="waPhone" class="form-control" inputmode="tel" maxlength="25"></div>
                    <div class="col-12 d-none" id="waOtherPhoneWrap"><label class="form-label">Nomor Lain</label><input id="waOtherPhone" class="form-control" inputmode="tel" maxlength="25" placeholder="Contoh: 081234567890"><small class="text-muted">Nomor alternatif hanya dipakai untuk pengiriman saat ini.</small></div>
                    <div class="col-12"><label class="form-label">Pesan</label><textarea id="waMessage" class="form-control" rows="6"></textarea><small id="waTemplateInfo" class="text-muted"></small></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button id="btnSendWhatsapp" class="btn btn-success"><i class="ri-whatsapp-line me-1"></i>Buka WhatsApp</button></div>
        </div>
    </div>
</div>

<script>
    $(function() {
        const base = <?= json_encode(site_url('admin/tabungan/riwayat_tabungan/')) ?>;
        const modalDetail = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailTabungan'));
        const modalWa = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalWhatsappTabungan'));
        let historyPage = 1,
            historyLimit = 10,
            waData = null;
        const money = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));
        const dateId = s => {
            if (!s) return '-';

            const value = String(s).trim();
            let match = value.match(/^(\d{2})-(\d{2})-(\d{4})$/);

            if (match) {
                return match[1] + '-' + match[2] + '-' + match[3];
            }

            match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);

            if (match) {
                return match[3] + '-' + match[2] + '-' + match[1];
            }

            return value;
        };
        const fail = x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Permintaan gagal diproses.', 'error');

        // Nilai input Flatpickr dan tanggal yang dikirim lewat AJAX sama-sama d-m-Y.
        const datePickerOptions = {
            dateFormat: 'd-m-Y',
            allowInput: false,
            locale: 'id'
        };
        const historyStartPicker = flatpickr('#historyStart', datePickerOptions);
        const historyEndPicker = flatpickr('#historyEnd', datePickerOptions);

        // Nilai tanggal tetap d-m-Y; perbandingan dilakukan dengan kunci numerik.
        function dateSortKey(value) {
            const match = String(value || '').trim().match(/^(\d{2})-(\d{2})-(\d{4})$/);
            return match ? Number(match[3]) * 10000 + Number(match[2]) * 100 + Number(match[1]) : 0;
        }

        function dateFilterValid(start, end) {
            if (start && end && dateSortKey(start) > dateSortKey(end)) {
                Swal.fire('Perhatian', 'Tanggal Dari tidak boleh melebihi Tanggal Sampai.', 'warning');
                return false;
            }
            return true;
        }

        function detailTx(jenis, id) {
            $.ajax({
                url: base + 'detail_transaksi',
                type: 'POST',
                dataType: 'json',
                data: {
                    jenis: jenis,
                    id: id
                },
                success: function(res) {
                    if (res.result !== 'true') return Swal.fire('Gagal', res.message, 'error');
                    let r = res.data;
                    $('#detailTabunganBody').html(`<dl class="row mb-0"><dt class="col-sm-4">Jenis</dt><dd class="col-sm-8">${esc(r.jenis)}</dd><dt class="col-sm-4">No Transaksi</dt><dd class="col-sm-8">${esc(r.no_transaksi)}</dd><dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">${dateId(r.tanggal_transaksi)}</dd><dt class="col-sm-4">Siswa</dt><dd class="col-sm-8">${esc(r.nama_siswa)} (${esc(r.nis||'-')})</dd><dt class="col-sm-4">Kelas</dt><dd class="col-sm-8">${esc(r.nama_kelas||'-')}</dd><dt class="col-sm-4">Metode</dt><dd class="col-sm-8">${esc(r.metode)}</dd><dt class="col-sm-4">Nominal</dt><dd class="col-sm-8"><strong>${money(r.nominal)}</strong></dd><dt class="col-sm-4">Catatan</dt><dd class="col-sm-8">${esc(r.catatan||'-')}</dd><dt class="col-sm-4">Petugas</dt><dd class="col-sm-8">${esc(r.nama_user||'-')}</dd><dt class="col-sm-4">Status</dt><dd class="col-sm-8">${esc(r.status_transaksi)}</dd>${r.status_transaksi==='Dibatalkan'?`<dt class="col-sm-4">Dibatalkan Oleh</dt><dd class="col-sm-8">${esc(r.nama_user_batal||'-')}</dd><dt class="col-sm-4">Alasan Batal</dt><dd class="col-sm-8">${esc(r.alasan_batal||'-')}</dd>`:''}</dl>`);
                    modalDetail.show();
                },
                error: function(xhr) {
                    fail(xhr);
                }
            });
        }

        function openWa(jenis, id) {
            $.ajax({
                url: base + 'whatsapp_preview',
                type: 'POST',
                dataType: 'json',
                data: {
                    jenis: jenis,
                    id: id
                },
                success: function(res) {
                    if (res.result !== 'true') return Swal.fire('Gagal', res.message, 'error');
                    waData = res.data;
                    let wali = res.data.wali || {};
                    $('#waJenis').val(jenis);
                    $('#waId').val(id);
                    $('#waSourceParent').prop('checked', true);
                    $('#waSourceOther').prop('checked', false);
                    $('#waName').val(wali.nama || '');
                    $('#waRelation').val(wali.hubungan || 'Wali');
                    $('#waPhone').val(wali.nomor || '');
                    $('#waOtherPhone').val('');
                    toggleTabunganWaSource();
                    $('#waMessage').val(res.data.pesan || '').data('edited', false);
                    $('#waTemplateInfo').text('Template: ' + (res.data.nama_template || 'Default'));
                    modalWa.show();
                },
                error: function(xhr) {
                    fail(xhr);
                }
            });
        }

        function toggleTabunganWaSource() {
            let src = $('input[name="waSourceTabungan"]:checked').val() || 'Orang Tua';
            $('#waParentPhoneWrap').toggleClass('d-none', src !== 'Orang Tua');
            $('#waOtherPhoneWrap').toggleClass('d-none', src !== 'Nomor Lain');
        }
        $(document).on('change', '.wa-source-tabungan', toggleTabunganWaSource);
        $('#waMessage').on('input', function() {
            $(this).data('edited', true);
        });
        $('#btnSendWhatsapp').on('click', function() {
            let src = $('input[name="waSourceTabungan"]:checked').val() || 'Orang Tua';
            let phone = src === 'Nomor Lain' ? $.trim($('#waOtherPhone').val()) : $.trim($('#waPhone').val());
            if (!$.trim($('#waName').val())) return Swal.fire('Perhatian', 'Nama penerima/wali wajib diisi.', 'warning');
            if (!phone) return Swal.fire('Perhatian', src === 'Nomor Lain' ? 'Nomor lain wajib diisi.' : 'Nomor HP orang tua/wali wajib diisi.', 'warning');
            let win = window.open('about:blank', '_blank');
            let edited = $('#waMessage').data('edited') === true;
            $.ajax({
                url: base + 'siapkan_whatsapp',
                type: 'POST',
                dataType: 'json',
                data: {
                    jenis: $('#waJenis').val(),
                    id: $('#waId').val(),
                    sumber_nomor: src,
                    nama_penerima: $('#waName').val(),
                    hubungan_penerima: $('#waRelation').val(),
                    nomor_whatsapp: phone,
                    pesan: edited ? $('#waMessage').val() : ''
                },
                success: function(res) {
                    if (res.result === 'true') {
                        modalWa.hide();
                        if (win) win.location = res.url;
                        else window.location = res.url;
                    } else {
                        if (win) win.close();
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    if (win) win.close();
                    fail(xhr);
                }
            });
        });

        $('#historyRows').on('click', '.tx-detail', function() {
            detailTx($(this).data('jenis'), $(this).data('id'));
        }).on('click', '.tx-wa', function() {
            openWa($(this).data('jenis'), $(this).data('id'));
        });

        function historyFilter() {
            return {
                search: $('#historySearch').val(),
                id_kelas_setting: $('#historyClass').val(),
                jenis: $('#historyType').val(),
                status: $('#historyStatus').val(),
                tanggal_mulai: $('#historyStart').val(),
                tanggal_selesai: $('#historyEnd').val(),
                page: historyPage,
                limit: historyLimit
            };
        }

        function loadHistory(rebuild) {
            $('#historyRows').html('<tr><td colspan="6" class="text-center text-muted">Memuat data...</td></tr>');
            $.ajax({
                url: base + 'riwayat',
                type: 'POST',
                dataType: 'json',
                data: historyFilter(),
                success: function(res) {
                    let html = '';
                    (res.data || []).forEach(function(r) {
                        html += `<tr><td>${dateId(r.tanggal_transaksi)}</td><td><strong>${esc(r.no_transaksi)}</strong><br><small>${esc(r.nama_siswa)} · ${esc(r.nis||'-')} · ${esc(r.nama_kelas||'-')}</small></td><td><span class="badge ${r.jenis==='Setoran'?'bg-primary-subtle text-primary':'bg-warning-subtle text-warning'}">${esc(r.jenis)}</span></td><td class="text-end fw-semibold">${money(r.nominal)}</td><td><span class="badge ${r.status_transaksi==='Aktif'?'bg-success':'bg-secondary'}">${esc(r.status_transaksi)}</span></td><td class="text-end"><div class="d-inline-flex gap-1"><button class="btn btn-sm btn-outline-info tx-detail" data-id="${r.id}" data-jenis="${r.jenis}" title="Detail"><i class="ri-eye-line"></i></button>${r.status_transaksi==='Aktif'?`<button class="btn btn-sm btn-outline-success tx-wa" data-id="${r.id}" data-jenis="${r.jenis}" title="WhatsApp"><i class="ri-whatsapp-line"></i></button>`:''}<a class="btn btn-sm btn-outline-secondary" target="_blank" href="${base}bukti/${r.jenis.toLowerCase()}/${r.id}" title="Cetak"><i class="ri-printer-line"></i></a></div></td></tr>`;
                    });
                    $('#historyRows').html(html || '<tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>');
                    let summary = res.summary || {};
                    $('#historySummaryDeposit').text(money(summary.setoran || 0));
                    $('#historySummaryWithdraw').text(money(summary.penarikan || 0));
                    $('#historySummaryBalance').text(money(summary.selisih || 0));
                    $('#historySummaryCount').text(Number(summary.jumlah || 0).toLocaleString('id-ID'));
                    $('#historySummaryStatus').text(Number(summary.aktif || 0).toLocaleString('id-ID') + ' aktif · ' + Number(summary.dibatalkan || 0).toLocaleString('id-ID') + ' dibatalkan');
                    historyPage = res.meta.page;
                    $('#historyInfo').text((res.meta.total || 0) + ' transaksi');
                    if (rebuild !== false) SDKreatifUI.serverPager('#historyPagination', res.meta, p => {
                        historyPage = p.currentPage;
                        loadHistory(false);
                    });
                },
                error: function(xhr) {
                    fail(xhr);
                }
            });
        }
        $('#btnHistoryFilter').on('click', function() {
            if (!dateFilterValid($('#historyStart').val(), $('#historyEnd').val())) return;
            historyPage = 1;
            loadHistory(true);
        });
        $('#historySearch').on('keydown', e => {
            if (e.key === 'Enter') {
                if (!dateFilterValid($('#historyStart').val(), $('#historyEnd').val())) return;
                historyPage = 1;
                loadHistory(true);
            }
        });
        $('#historyLimit').on('change', function() {
            historyLimit = Number(this.value) || 10;
            historyPage = 1;
            loadHistory(true);
        });

        function queryString() {
            let f = historyFilter();
            delete f.page;
            delete f.limit;
            return new URLSearchParams(f).toString();
        }

        function exportHistory() {
            let filter = historyFilter();
            delete filter.page;
            delete filter.limit;

            let button = $('#btnExportHistory');
            button.prop('disabled', true);

            $.ajax({
                url: base + 'export_riwayat',
                type: 'GET',
                data: filter,
                dataType: 'JSON',
                success: function(res) {
                    if (typeof XLSX === 'undefined') {
                        Swal.fire('Gagal', 'Library SheetJS belum tersedia.', 'error');
                        return;
                    }

                    let rows = res.data || [];
                    if (!rows.length) {
                        Swal.fire('Perhatian', 'Tidak ada data yang dapat diekspor.', 'warning');
                        return;
                    }

                    // Identitas laporan Excel mengikuti informasi Cetak Riwayat Tabungan.
                    const mulai = String(filter.tanggal_mulai || '').trim();
                    const selesai = String(filter.tanggal_selesai || '').trim();
                    const periode = mulai && selesai ? mulai + ' s.d. ' + selesai :
                        mulai ? 'Mulai ' + mulai : selesai ? 'Sampai ' + selesai : 'Semua Tanggal';
                    const now = new Date();
                    const pad = value => String(value).padStart(2, '0');
                    const tanggalEkspor = pad(now.getDate()) + '-' + pad(now.getMonth() + 1) + '-' +
                        now.getFullYear() + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                    const petugasExport = <?= json_encode($petugasExport, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

                    // Kolom dan isi transaksi mengikuti tabel Cetak Riwayat Tabungan.
                    let excel = [
                        ['RIWAYAT TABUNGAN SISWA'],
                        [],
                        ['Periode', periode],
                        ['Tanggal Ekspor', tanggalEkspor],
                        ['Petugas', petugasExport],
                        [],
                        [
                            'No', 'Jenis', 'No Transaksi', 'Tanggal', 'Siswa', 
                            'Kelas', 'Metode', 'Nominal', 'Status', 'Catatan/Alasan'
                        ]
                    ];
                    const barisDataPertama = excel.length + 1;

                    rows.forEach(function(r, index) {
                        const dibatalkan = r.status_transaksi === 'Dibatalkan';
                        excel.push([
                            index + 1,
                            r.jenis || '',
                            r.no_transaksi || '',
                            dateId(r.tanggal_transaksi),
                            r.nama_siswa || '',
                            // String(r.nis ?? ''),
                            // String(r.nisn ?? ''),
                            r.nama_kelas || '',
                            r.metode || '',
                            Number(r.nominal || 0),
                            r.status_transaksi || '',
                            dibatalkan ? (r.alasan_batal || '-') : (r.catatan || '-')
                        ]);
                    });

                    // Ringkasan diletakkan di bawah tabel, sesuai hasil cetak.
                    const summary = res.summary || {};
                    excel.push([]);
                    const summaryStart = excel.length + 1;
                    [
                        ['Total Setoran Aktif', Number(summary.setoran || 0)],
                        ['Total Penarikan Aktif', Number(summary.penarikan || 0)],
                        ['Jumlah Transaksi', Number(summary.jumlah || 0)],
                        ['Dibatalkan', Number(summary.dibatalkan || 0)]
                    ].forEach(function(item) {
                        const summaryRow = new Array(12).fill('');
                        summaryRow[8] = item[0];
                        summaryRow[9] = item[1];
                        excel.push(summaryRow);
                    });

                    let worksheet = XLSX.utils.aoa_to_sheet(excel);
                    worksheet['!cols'] = [
                        {wch: 6}, {wch: 12}, {wch: 24}, {wch: 14},
                        {wch: 32}, {wch: 14}, {wch: 18}, {wch: 12},
                        {wch: 24}, {wch: 18}, {wch: 15}, {wch: 40}
                    ];

                    // Kolom NIS dan NISN tetap bertipe teks (termasuk nol di depan).
                    for (let row = barisDataPertama; row < barisDataPertama + rows.length; row++) {
                        for (const col of ['F', 'G']) {
                            const cell = worksheet[col + row];
                            if (cell) {
                                cell.t = 's';
                                cell.z = '@';
                            }
                        }
                        const nominalCell = worksheet['J' + row];
                        if (nominalCell) nominalCell.z = '"Rp"#,##0';
                    }
                    for (let row = summaryStart; row < summaryStart + 2; row++) {
                        const nominalCell = worksheet['J' + row];
                        if (nominalCell) nominalCell.z = '"Rp"#,##0';
                    }

                    let workbook = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(workbook, worksheet, 'Riwayat Tabungan');

                    let filename = 'riwayat_tabungan_' +
                        now.getFullYear() + pad(now.getMonth() + 1) + pad(now.getDate()) + '_' +
                        pad(now.getHours()) + pad(now.getMinutes()) + pad(now.getSeconds()) + '.xlsx';

                    XLSX.writeFile(workbook, filename);
                },
                error: function(xhr) {
                    fail(xhr);
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        }

        $('#btnPrintHistory').on('click', function() {
            if (!dateFilterValid($('#historyStart').val(), $('#historyEnd').val())) return;
            window.open(base + 'cetak_riwayat?' + queryString(), '_blank');
        });
        $('#btnExportHistory').on('click', function() {
            if (!dateFilterValid($('#historyStart').val(), $('#historyEnd').val())) return;
            exportHistory();
        });
        loadHistory(true);
    });
</script>
