<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="card" id="studentSearchCard">
            <div class="card-header border-bottom border-dashed">
                <h4 class="header-title mb-1">Cari Siswa</h4><small class="text-muted">Pilih siswa untuk melihat saldo, setoran, penarikan, dan riwayat tabungannya.</small>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-lg-10"><input id="studentSearch" class="form-control" placeholder="Nama / NIS / NISN"></div>
                    <div class="col-lg-2 d-grid"><button id="btnStudentSearch" class="btn btn-primary"><i class="ri-search-line me-1"></i>Cari</button></div>
                </div>
                <div id="studentResults" class="sdk-record-list mt-3">
                    <div class="sdk-record-empty">Cari siswa untuk memulai.</div>
                </div>
                <div class="sdk-pager-wrap mt-3" id="studentSearchFooter" style="display:none">
                    <div><small id="studentSearchInfo" class="text-muted"></small>
                        <div class="mt-2" id="studentSearchPagination"></div>
                    </div>
                    <div class="sdk-page-size"><label class="mb-0">Tampilkan</label><select id="studentSearchLimit" class="form-select form-select-sm">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                        </select><span>entri</span></div>
                </div>
            </div>
        </div>

        <div id="studentPanel" class="d-none">
            <div class="card">
                <div class="card-header border-bottom border-dashed d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="header-title mb-1" id="selectedName">-</h4><small class="text-muted" id="selectedMeta">-</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap"><button id="btnChangeStudent" class="btn btn-outline-secondary btn-sm"><i class="ri-user-search-line me-1"></i>Ganti Siswa</button><button id="btnDeposit" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i>Setoran</button><button id="btnWithdraw" class="btn btn-outline-warning btn-sm"><i class="ri-subtract-line me-1"></i>Penarikan</button></div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3"><small class="text-muted">Total Setoran Aktif</small>
                                <h4 class="mb-0 text-primary" id="sumDeposit">Rp0</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3"><small class="text-muted">Total Penarikan Aktif</small>
                                <h4 class="mb-0 text-warning" id="sumWithdraw">Rp0</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3"><small class="text-muted">Saldo Saat Ini</small>
                                <h4 class="mb-0 text-success" id="sumBalance">Rp0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-1">Riwayat Siswa</h4><small class="text-muted">Filter riwayat berdasarkan jenis, status, dan rentang tanggal transaksi.</small>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Dari Tanggal</label>
                            <input id="studentHistoryStart" type="text" class="form-control" placeholder="Pilih tanggal">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Sampai Tanggal</label>
                            <input id="studentHistoryEnd" type="text" class="form-control" placeholder="Pilih tanggal">
                        </div>
                        <div class="col-lg-3 col-md-6"><label class="form-label">Jenis Transaksi</label><select id="studentHistoryType" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="Setoran">Setoran</option>
                                <option value="Penarikan">Penarikan</option>
                            </select></div>
                        <div class="col-lg-3 col-md-6"><label class="form-label">Status Transaksi</label><select id="studentHistoryStatus" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select></div>
                        <div class="col-lg-2 col-md-4 d-grid align-self-end"><button type="button" id="btnStudentHistoryFilter" class="btn btn-primary"><i class="ri-search-line me-1"></i>Cari</button></div>
                    </div>
                    <div id="studentHistory" class="sdk-record-list">
                        <div class="sdk-record-empty">Belum ada transaksi.</div>
                    </div>
                    <div class="sdk-pager-wrap mt-3">
                        <div><small id="studentHistoryInfo" class="text-muted"></small>
                            <div class="mt-2" id="studentHistoryPagination"></div>
                        </div>
                        <div class="sdk-page-size"><label class="mb-0">Tampilkan</label><select id="studentHistoryLimit" class="form-select form-select-sm">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select><span>entri</span></div>
                    </div>
                </div>
            </div>
        </div>

<div class="modal fade" id="modalTransaction" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="transactionTitle">Transaksi Tabungan</h5><small class="text-muted" id="transactionHint"></small>
                </div><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm">
                <div class="modal-body"><input type="hidden" name="id_siswa" id="transactionStudent"><input type="hidden" name="jenis" id="transactionType"><input type="hidden" name="token" id="transactionToken"><input type="hidden" name="id" id="transactionId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal *</label>
                            <input type="text" class="form-control" name="tanggal" id="transactionDate" placeholder="Pilih tanggal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominal *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input class="form-control rupiah-input" name="nominal" id="transactionAmount" onkeyup="FormatCurrency(this);" required placeholder="Nominal ...">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Metode Pembayaran *</label>
                            <select class="form-select" name="id_metode_pembayaran" id="transactionMethod" required>
                                <option value="">Pilih Metode Pembayaran</option>
                                <?php foreach (($metode ?? array()) as $item): ?>
                                    <option value="<?= (int) $item['id']; ?>" data-nama="<?= html_escape($item['nama_metode']); ?>"><?= html_escape($item['nama_metode']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="catatan" id="transactionNote" rows="3" maxlength="500" placeholder="Catatan ..."></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="btnSaveTransaction">Simpan</button></div>
            </form>
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
        const base = <?= json_encode(site_url('admin/tabungan/tabungan_siswa/')) ?>;
        const modalTransaction = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTransaction'));
        const modalDetail = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailTabungan'));
        const modalWa = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalWhatsappTabungan'));
        let selected = null,
            studentRows = [],
            studentPage = 1,
            studentSearchPage = 1,
            studentSearchLimit = 10,
            waData = null,
            editMode = false;
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

        function token() {
            let a = new Uint8Array(16);
            crypto.getRandomValues(a);
            return Array.from(a, x => x.toString(16).padStart(2, '0')).join('');
        }

        function today() {
            let d = new Date(),
                z = n => String(n).padStart(2, '0');
            return z(d.getDate()) + '-' + z(d.getMonth() + 1) + '-' + d.getFullYear();
        }

        // Nilai input Flatpickr dan tanggal yang dikirim lewat AJAX sama-sama d-m-Y.
        const datePickerOptions = {
            dateFormat: 'd-m-Y',
            allowInput: false,
            locale: 'id'
        };
        const txDatePicker = flatpickr('#transactionDate', datePickerOptions);
        const studentStartPicker = flatpickr('#studentHistoryStart', datePickerOptions);
        const studentEndPicker = flatpickr('#studentHistoryEnd', datePickerOptions);

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

        function searchStudent(page) {
            let q = $('#studentSearch').val().trim();
            if (q.length < 2) return Swal.fire('Perhatian', 'Masukkan minimal 2 karakter.', 'warning');
            studentSearchPage = Math.max(1, Number(page || studentSearchPage || 1));
            studentSearchLimit = Number($('#studentSearchLimit').val()) || 10;
            $('#studentResults').removeClass('d-none').html('<div class="sdk-record-empty">Mencari siswa...</div>');
            $.ajax({
                url: base + 'cari_siswa',
                type: 'POST',
                dataType: 'json',
                data: {
                    q: q,
                    page: studentSearchPage,
                    limit: studentSearchLimit
                },
                success: function(res) {
                    let html = '',
                        meta = res.meta || {
                            page: 1,
                            limit: studentSearchLimit,
                            total: 0,
                            total_pages: 1
                        };
                    studentSearchPage = Number(meta.page) || 1;
                    (res.data || []).forEach(function(r, i) {
                        html += `<div class="sdk-record-card"><div class="sdk-record-head"><span>NIS: <strong>${esc(r.nis||'-')}</strong>${r.nisn?` · NISN: <strong>${esc(r.nisn)}</strong>`:''}</span>${SDKreatifUI.statusBadge(r.status_aktif||'AKTIF')}</div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(studentSearchPage-1)*studentSearchLimit+i+1}. ${esc(r.nama_lengkap)}</h5><div class="sdk-record-meta"><span>Kelas: <strong>${esc(r.nama_kelas||'Belum ditempatkan')}</strong></span><span>Tahun Ajaran: <strong>${esc(r.periode||'-')}</strong></span></div></div><div class="sdk-record-actions"><button class="btn btn-sm btn-outline-primary choose-student" data-id="${r.id}" title="Pilih siswa"><i class="ri-check-line"></i></button></div></div></div>`;
                    });
                    $('#studentResults').html(html || '<div class="sdk-record-empty">Siswa tidak ditemukan.</div>');
                    $('#studentSearchFooter').toggle((Number(meta.total) || 0) > 0);
                    $('#studentSearchInfo').text((Number(meta.total) || 0) + ' siswa');
                    SDKreatifUI.serverPager('#studentSearchPagination', meta, p => {
                        studentSearchPage = p.currentPage;
                        searchStudent(studentSearchPage);
                    });
                    $('#studentResults .choose-student').on('click', function() {
                        selected = (res.data || []).find(x => Number(x.id) === Number($(this).data('id')));
                        loadStudent();
                    });
                },
                error: function(xhr) {
                    fail(xhr);
                }
            });
        }
        $('#btnStudentSearch').on('click', function() {
            studentSearchPage = 1;
            searchStudent(1);
        });
        $('#studentSearch').on('keydown', e => {
            if (e.key === 'Enter') {
                studentSearchPage = 1;
                searchStudent(1);
            }
        });
        $('#studentSearchLimit').on('change', function() {
            studentSearchLimit = Number(this.value) || 10;
            studentSearchPage = 1;
            if ($('#studentSearch').val().trim().length >= 2) searchStudent(1);
        });

        function loadStudent() {
            if (!selected) return;
            $.ajax({
                url: base + 'detail',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_siswa: selected.id
                },
                success: function(res) {
                    if (res.result !== 'true') return Swal.fire('Gagal', res.message, 'error');
                    selected = res.siswa;
                    studentRows = res.riwayat || [];
                    studentPage = 1;
                    $('#studentSearchCard').addClass('d-none');
                    $('#studentResults').addClass('d-none').empty();
                    $('#studentPanel').removeClass('d-none');
                    $('#selectedName').text(res.siswa.nama_lengkap);
                    $('#selectedMeta').text('NIS ' + (res.siswa.nis || '-') + ' · NISN ' + (res.siswa.nisn || '-') + ' · Kelas ' + (res.siswa.nama_kelas || '-'));
                    $('#sumDeposit').text(money(res.rekap.setoran));
                    $('#sumWithdraw').text(money(res.rekap.penarikan));
                    $('#sumBalance').text(money(res.rekap.saldo));
                    renderStudentHistory();
                },
                error: function(xhr) {
                    fail(xhr);
                }
            });
        }

        function actionButtons(r) {
            let active = r.status_transaksi === 'Aktif';
            return `<button class="btn btn-sm btn-outline-info tx-detail" data-id="${r.id}" data-jenis="${r.jenis}" title="Detail"><i class="ri-eye-line"></i></button>${active?`<button class="btn btn-sm btn-outline-primary tx-edit" data-id="${r.id}" data-jenis="${r.jenis}" title="Edit"><i class="ri-edit-line"></i></button><button class="btn btn-sm btn-outline-success tx-wa" data-id="${r.id}" data-jenis="${r.jenis}" title="WhatsApp"><i class="ri-whatsapp-line"></i></button>`:''}<a class="btn btn-sm btn-outline-secondary" target="_blank" href="${base}bukti/${r.jenis.toLowerCase()}/${r.id}" title="Cetak"><i class="ri-printer-line"></i></a>${active?`<button class="btn btn-sm btn-outline-danger tx-cancel" data-id="${r.id}" data-jenis="${r.jenis}" title="Batalkan"><i class="ri-forbid-line"></i></button>`:''}`;
        }

        function renderStudentHistory() {
            let jenis = $('#studentHistoryType').val() || '';
            let status = $('#studentHistoryStatus').val() || '';
            const start = $('#studentHistoryStart').val();
            const end = $('#studentHistoryEnd').val();
            let filteredRows = studentRows.filter(function(r) {
                if (jenis && r.jenis !== jenis) return false;
                if (status && r.status_transaksi !== status) return false;
                const tanggal = dateSortKey(dateId(r.tanggal_transaksi));
                if (start && tanggal < dateSortKey(start)) return false;
                if (end && tanggal > dateSortKey(end)) return false;
                return true;
            });
            let limit = Number($('#studentHistoryLimit').val()) || 10,
                total = filteredRows.length,
                pages = Math.max(1, Math.ceil(total / limit));
            studentPage = Math.min(studentPage, pages);
            let rows = filteredRows.slice((studentPage - 1) * limit, studentPage * limit),
                html = '';
            rows.forEach((r, i) => {
                html += `<div class="sdk-record-card"><div class="sdk-record-head"><span>${dateId(r.tanggal_transaksi)} · <strong>${esc(r.no_transaksi)}</strong></span><span class="badge ${r.status_transaksi==='Aktif'?'bg-success':'bg-secondary'}">${esc(r.status_transaksi)}</span></div><div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title">${(studentPage-1)*limit+i+1}. ${esc(r.jenis)} — ${money(r.nominal)}</h5><div class="sdk-record-meta"><span>Metode: <strong>${esc(r.metode)}</strong></span><span>Petugas: <strong>${esc(r.nama_user||'-')}</strong></span>${r.catatan?`<span>Catatan: <strong>${esc(r.catatan)}</strong></span>`:''}</div></div><div class="sdk-record-actions">${actionButtons(r)}</div></div></div>`;
            });
            $('#studentHistory').html(html || '<div class="sdk-record-empty">Tidak ada transaksi sesuai filter.</div>');
            let meta = {
                total: total,
                page: studentPage,
                limit: limit
            };
            $('#studentHistoryInfo').text(total + ' transaksi');
            SDKreatifUI.serverPager('#studentHistoryPagination', meta, p => {
                studentPage = p.currentPage;
                renderStudentHistory();
            });
            SDKreatifUI.refreshTooltips('#studentHistory');
        }
        $('#studentHistoryLimit').on('change', function() {
            studentPage = 1;
            renderStudentHistory();
        });
        $('#btnStudentHistoryFilter').on('click', function() {
            if (!dateFilterValid($('#studentHistoryStart').val(), $('#studentHistoryEnd').val())) return;
            studentPage = 1;
            renderStudentHistory();
        });
        $('#btnChangeStudent').on('click', function() {
            selected = null;
            studentRows = [];
            studentPage = 1;
            $('#studentPanel').addClass('d-none');
            $('#studentSearchCard').removeClass('d-none');
            $('#studentSearch').val('').trigger('focus');
            $('#studentResults').removeClass('d-none').html('<div class="sdk-record-empty">Cari siswa untuk memulai.</div>');
            $('#studentSearchFooter').hide();
            $('#studentSearchPagination').empty();
            $('#studentSearchInfo').text('');
            studentSearchPage = 1;
            $('#studentHistoryType, #studentHistoryStatus').val('');
            studentStartPicker.clear();
            studentEndPicker.clear();
        });
        $('#btnDeposit').on('click', () => openTransaction('Setoran'));
        $('#btnWithdraw').on('click', () => openTransaction('Penarikan'));

        function openTransaction(jenis, row) {
            editMode = !!row;
            document.getElementById('transactionForm').reset();
            $('#transactionId').val(row ? row.id : '');
            $('#transactionStudent').val(row ? row.id_siswa : selected.id);
            $('#transactionType').val(jenis);
            $('#transactionToken').val(row ? '' : token());
            txDatePicker.setDate(row ? dateId(row.tanggal_transaksi) : today(), false, 'd-m-Y');
            $('#transactionAmount').val(row ? Number(row.nominal).toLocaleString('id-ID') : '');
            const $metodeSelect = $('#transactionMethod');
            $metodeSelect.find('option[data-metode-lama]').remove();
            if (row) {
                const idMetode = String(row.id_metode_pembayaran || '');
                let pilihan = $metodeSelect.find('option').filter(function() {
                    return idMetode !== '' && this.value === idMetode;
                }).first();
                // Transaksi lama dengan ID NULL masih bisa dicocokkan berdasarkan namanya.
                if (!pilihan.length && idMetode === '') {
                    pilihan = $metodeSelect.find('option').filter(function() {
                        return $(this).attr('data-nama') === row.metode;
                    }).first();
                }
                if (pilihan.length) {
                    $metodeSelect.val(pilihan.val());
                } else {
                    $metodeSelect.prepend($('<option>', {
                        value: '',
                        text: 'Metode lama: ' + (row.metode || '-') + ' (tidak aktif, pilih metode aktif)'
                    }).attr('data-metode-lama', '1'));
                    $metodeSelect.val('');
                }
            } else {
                const tunai = $metodeSelect.find('option').filter(function() {
                    return $(this).attr('data-nama') === 'Tunai';
                }).first();
                $metodeSelect.val(tunai.length ? tunai.val() : ($metodeSelect.find('option[value!=""]').first().val() || ''));
            }
            $('#transactionNote').val(row ? row.catatan : '');
            $('#transactionTitle').text((row ? 'Edit ' : '') + jenis + ' Tabungan');
            $('#transactionHint').text(row ? 'Nomor transaksi tetap ' + row.no_transaksi : 'Siswa: ' + selected.nama_lengkap);
            modalTransaction.show();
        }
        $('#transactionForm').on('submit', function(e) {
            e.preventDefault();
            let data = $(this).serializeArray();
            data.forEach(x => {
                if (x.name === 'nominal') x.value = x.value.replace(/\D/g, '');
            });
            $('#btnSaveTransaction').prop('disabled', true);
            $.ajax({
                url: base + (editMode ? 'edit' : 'simpan'),
                type: 'POST',
                dataType: 'json',
                data: $.param(data),
                success: function(res) {
                    if (res.result === 'true') {
                        modalTransaction.hide();
                        Swal.fire('Berhasil', res.message, 'success');
                        loadStudent();
                    } else Swal.fire('Gagal', res.message, 'error');
                },
                error: function(xhr) {
                    fail(xhr);
                },
                complete: function() {
                    $('#btnSaveTransaction').prop('disabled', false);
                }
            });
        });

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

        function editTx(jenis, id) {
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
                    openTransaction(jenis, res.data);
                },
                error: function(xhr) {
                    fail(xhr);
                }
            });
        }

        function cancelTx(jenis, id) {
            Swal.fire({
                title: 'Batalkan transaksi?',
                input: 'textarea',
                inputLabel: 'Alasan pembatalan',
                inputPlaceholder: 'Masukkan alasan...',
                showCancelButton: true,
                confirmButtonText: 'Batalkan Transaksi',
                confirmButtonColor: '#dc3545',
                preConfirm: v => {
                    if (!String(v || '').trim()) Swal.showValidationMessage('Alasan wajib diisi.');
                    return v;
                }
            }).then(r => {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: base + 'batalkan',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        jenis: jenis,
                        id: id,
                        alasan: r.value
                    },
                    success: function(res) {
                        Swal.fire(res.result === 'true' ? 'Berhasil' : 'Gagal', res.message, res.result === 'true' ? 'success' : 'error');
                        if (res.result === 'true') {
                            if (selected) loadStudent();
                            }
                    },
                    error: function(xhr) {
                        fail(xhr);
                    }
                });
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

        function bindActions(root) {
            $(root).off('click.tab').on('click.tab', '.tx-detail', function() {
                detailTx($(this).data('jenis'), $(this).data('id'));
            }).on('click.tab', '.tx-edit', function() {
                editTx($(this).data('jenis'), $(this).data('id'));
            }).on('click.tab', '.tx-wa', function() {
                openWa($(this).data('jenis'), $(this).data('id'));
            }).on('click.tab', '.tx-cancel', function() {
                cancelTx($(this).data('jenis'), $(this).data('id'));
            });
        }
        bindActions('#studentHistory');
    });
</script>
