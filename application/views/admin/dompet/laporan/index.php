<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="header-title mb-1">Laporan Dompet Digital</h4>
            <small class="text-muted">
                Data, total, cetak, dan Excel mengikuti filter yang sedang aktif.
            </small>
        </div>
        <div class="d-flex gap-2">
            <button id="printReport" type="button" class="btn btn-sm btn-outline-primary">
                <i class="ri-printer-line me-1"></i>Cetak
            </button>
            <button id="exportExcel" type="button" class="btn btn-sm btn-success">
                <i class="ri-file-excel-2-line me-1"></i>Export Excel
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <label for="jenis" class="form-label">Jenis laporan</label>
                <select id="jenis" class="form-select report-select">
                    <?php foreach ($report_types as $value => $label): ?>
                        <option value="<?= html_escape($value) ?>">
                            <?= html_escape($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <label for="search" class="form-label">Pencarian</label>
                <input
                    id="search"
                    type="search"
                    class="form-control"
                    placeholder="NIS, siswa, atau nomor transaksi"
                >
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6">
                <label for="kelas" class="form-label">Kelas</label>
                <select id="kelas" class="form-select report-select">
                    <option value="">Semua kelas</option>
                    <?php foreach ($kelas as $class): ?>
                        <option value="<?= (int) $class['id'] ?>">
                            <?= html_escape($class['nama_kelas'].' - '.$class['periode']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6">
                <label for="reportStatusFilter" class="form-label">Status</label>
                <select id="reportStatusFilter" class="form-select report-select">
                    <option value="">Semua status</option>
                </select>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6">
                <label for="awal" class="form-label">Tanggal awal</label>
                <input id="awal" type="date" class="form-control">
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6">
                <label for="akhir" class="form-label">Tanggal akhir</label>
                <input id="akhir" type="date" class="form-control">
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 d-flex align-items-end">
                <button id="filter" type="button" class="btn btn-primary w-100">
                    <i class="ri-filter-3-line me-1"></i>Terapkan Filter
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <small id="totalLabel1" class="text-muted">Total 1</small>
                    <h5 id="total1" class="mt-2 mb-0">Rp 0</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <small id="totalLabel2" class="text-muted">Total 2</small>
                    <h5 id="total2" class="mt-2 mb-0">Rp 0</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <small id="totalLabel3" class="text-muted">Total 3</small>
                    <h5 id="total3" class="mt-2 mb-0">Rp 0</h5>
                </div>
            </div>
        </div>

        <div id="rows" class="sdk-record-list">
            <div class="sdk-record-empty">Memuat data...</div>
        </div>

        <div class="sdk-pager-wrap">
            <div>
                <small id="info" class="text-muted"></small>
                <div class="mt-2" id="reportPagination"></div>
            </div>
            <div class="sdk-page-size">
                <label for="reportPageSize" class="mb-0">Tampilkan</label>
                <select class="form-select form-select-sm" id="reportPageSize">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entri</span>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const base = <?= json_encode(
        base_url('admin/dompet/laporan_dompet/'),
        JSON_UNESCAPED_SLASHES
    ) ?>;
    const statusOptions = {
        saldo: {
            AKTIF: 'Aktif',
            NONAKTIF: 'Nonaktif'
        },
        isi_saldo: {
            BERHASIL: 'Berhasil',
            DIBATALKAN: 'Dibatalkan'
        },
        transaksi_kantin: {
            BERHASIL: 'Berhasil',
            DIBATALKAN: 'Dibatalkan'
        },
        biaya_layanan: {
            BERHASIL: 'Berhasil',
            DIBATALKAN: 'Dibatalkan'
        }
    };
    const escapeHtml = value => $('<div>').text(value ?? '-').html();
    const money = value => new Intl.NumberFormat('id-ID').format(+value || 0);

    let page = 1;
    let limit = 10;

    $('#jenis').select2({
        width: '100%',
        minimumResultsForSearch: Infinity
    });
    $('#kelas').select2({
        width: '100%',
        placeholder: 'Semua kelas',
        allowClear: true
    });
    $('#reportStatusFilter').select2({
        width: '100%',
        placeholder: 'Semua status',
        allowClear: true,
        minimumResultsForSearch: Infinity
    });

    function refreshStatusOptions() {
        const type = $('#jenis').val();
        const options = statusOptions[type] || {};
        const status = $('#reportStatusFilter');

        status.empty().append(new Option('Semua status', '', true, true));
        Object.entries(options).forEach(([value, label]) => {
            status.append(new Option(label, value));
        });
        status.val('').trigger('change.select2');
    }

    function filters() {
        return {
            jenis: $('#jenis').val(),
            search: $('#search').val(),
            id_kelas_setting: $('#kelas').val(),
            status: $('#reportStatusFilter').val(),
            tanggal_awal: $('#awal').val(),
            tanggal_akhir: $('#akhir').val()
        };
    }

    function reportUrl(action) {
        const query = new URLSearchParams(filters());
        return base + action + '?' + query.toString();
    }

    function setLoading() {
        $('#rows').html(
            '<div class="sdk-record-empty">'
            + '<span class="spinner-border spinner-border-sm me-2"></span>'
            + 'Memuat laporan...'
            + '</div>'
        );
    }

    function load(rebuildPager) {
        setLoading();

        $.post(
            base + 'result',
            {...filters(), page: page, limit: limit},
            null,
            'json'
        ).done(response => {
            const columns = response.meta.columns;
            const totals = response.meta.totals;
            let html = '';

            (response.data || []).forEach((row, index) => {
                html += `
                    <div class="sdk-record-card">
                        <div class="sdk-record-head">
                            <span>
                                ${escapeHtml(columns.label_1)}:
                                <strong>${escapeHtml(row.label_1)}</strong>
                            </span>
                            ${SDKreatifUI.statusBadge(row.status)}
                        </div>
                        <div class="sdk-record-main">
                            <div class="flex-grow-1">
                                <h5 class="sdk-record-title">
                                    ${(page - 1) * limit + index + 1}.
                                    ${escapeHtml(columns.label_2)}:
                                    ${escapeHtml(row.label_2)}
                                </h5>
                                <div class="sdk-record-meta mb-3">
                                    <span>
                                        ${escapeHtml(columns.label_3)}:
                                        <strong>${escapeHtml(row.label_3)}</strong>
                                    </span>
                                    <span>
                                        <i class="ri-calendar-line me-1"></i>
                                        ${escapeHtml(row.tanggal)}
                                    </span>
                                </div>
                                <div class="sdk-record-details">
                                    <div class="sdk-record-detail">
                                        ${escapeHtml(columns.nilai_1)}
                                        <strong>Rp ${money(row.nilai_1)}</strong>
                                    </div>
                                    <div class="sdk-record-detail">
                                        ${escapeHtml(columns.nilai_2)}
                                        <strong>Rp ${money(row.nilai_2)}</strong>
                                    </div>
                                    <div class="sdk-record-detail">
                                        ${escapeHtml(columns.nilai_3)}
                                        <strong>Rp ${money(row.nilai_3)}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });

            $('#rows').html(
                html || '<div class="sdk-record-empty">Tidak ada data sesuai filter.</div>'
            );
            $('#totalLabel1').text(columns.nilai_1);
            $('#totalLabel2').text(columns.nilai_2);
            $('#totalLabel3').text(columns.nilai_3);
            $('#total1').text('Rp ' + money(totals.nilai_1));
            $('#total2').text('Rp ' + money(totals.nilai_2));
            $('#total3').text('Rp ' + money(totals.nilai_3));

            page = response.meta.page;
            $('#info').text(response.meta.total + ' baris');

            if (rebuildPager !== false) {
                SDKreatifUI.serverPager(
                    '#reportPagination',
                    response.meta,
                    paging => {
                        page = paging.currentPage;
                        load(false);
                    }
                );
            }
        }).fail(xhr => {
            const message = (xhr.responseJSON || {}).message
                || 'Laporan gagal dimuat.';
            $('#rows').html('<div class="sdk-record-empty">Laporan gagal dimuat.</div>');
            Swal.fire('Gagal', message, 'error');
        });
    }

    $('#filter').on('click', () => {
        page = 1;
        load(true);
    });

    $('#jenis').on('change', () => {
        refreshStatusOptions();
        page = 1;
        load(true);
    });

    $('#reportPageSize').on('change', function () {
        limit = parseInt(this.value, 10) || 10;
        page = 1;
        load(true);
    });

    $('#search').on('keydown', event => {
        if (event.key === 'Enter') {
            page = 1;
            load(true);
        }
    });

    $('#printReport').on('click', () => {
        window.open(reportUrl('cetak'), '_blank', 'noopener');
    });

    $('#exportExcel').on('click', () => {
        window.location = reportUrl('export');
    });

    refreshStatusOptions();
    load(true);
});
</script>
