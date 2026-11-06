<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-body">
        <label class="form-label fw-semibold" for="id_siswa_tagihan">Pilih Anak</label>
        <select class="form-select" id="id_siswa_tagihan">
            <?php if (empty($anak_list)): ?>
                <option value="">Belum ada anak terhubung</option>
            <?php endif; ?>
            <?php foreach ($anak_list as $anak): ?>
                <option value="<?= (int) $anak['id_siswa'] ?>">
                    <?= html_escape($anak['nama_siswa']) ?> (<?= html_escape($anak['nis']) ?>)
                    <?= !empty($anak['nama_kelas']) ? ' - ' . html_escape($anak['nama_kelas']) : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h4 class="header-title mb-1">Tagihan Anak</h4>
        <small class="text-muted">Informasi tagihan Bulanan, Tahunan, Langsung, pembayaran, sisa, dan tunggakan.</small>
    </div>

    <div class="card-body border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="id_periode_tagihan">Tahun Pelajaran</label>
                <select class="form-select" id="id_periode_tagihan">
                    <option value="">Semua</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="tipe_tagihan">Jenis Tagihan</label>
                <select class="form-select" id="tipe_tagihan">
                    <option value="">Semua</option>
                    <option value="Bulanan">Bulanan</option>
                    <option value="Tahunan">Tahunan</option>
                    <option value="Langsung">Langsung</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="status_tagihan">Status</label>
                <select class="form-select" id="status_tagihan">
                    <option value="">Semua</option>
                    <option value="Belum Dibayar">Belum Dibayar</option>
                    <option value="Dibayar Sebagian">Dibayar Sebagian</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Dibebaskan">Dibebaskan</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold" for="search_tagihan">Cari Tagihan</label>
                <input type="text" class="form-control" id="search_tagihan" placeholder="No / nama tagihan">
            </div>
            <div class="col-lg-3 col-md-12 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="button" id="btn_terapkan_tagihan"><i class="ri-filter-3-line me-1"></i>Terapkan</button>
                <button class="btn btn-light" type="button" id="btn_reset_tagihan">Reset</button>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table sdk-data-table mb-0">
                <thead>
                    <tr>
                        <th>Tagihan</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Dibayar</th>
                        <th>Sisa</th>
                        <th>Jatuh Tempo</th>
                        <th>Tunggakan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-tagihan">
                    <tr><td colspan="8" class="sdk-empty">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <small class="text-muted d-block mb-1" id="info-tagihan">0 data</small>
                <div class="mt-2" id="pagination-tagihan"></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted small" for="per_halaman_tagihan">Tampilkan</label>
                <select class="form-select form-select-sm" id="per_halaman_tagihan" style="width:90px">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-muted small">entri</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail-tagihan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tagihan Anak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-tagihan">Memuat detail...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var halamanTagihan = 1;
var semuaTagihan = [];
var permintaanTagihan = 0;
var filterTagihanTerapan = {
    id_periode: '',
    tipe_tagihan: '',
    status_pembayaran: '',
    search: ''
};

$(document).ready(function () {
    tagihanResult(1, true);

    $('#id_siswa_tagihan').on('change', function () {
        resetFilterTagihan(false);
        tagihanResult(1, true);
    });

    $('#btn_terapkan_tagihan').on('click', function () {
        filterTagihanTerapan = ambilFilterTagihan();
        tagihanResult(1, false);
    });

    $('#btn_reset_tagihan').on('click', function () {
        resetFilterTagihan(true);
    });

    $('#per_halaman_tagihan').on('change', function () {
        halamanTagihan = 1;
        renderPaginationTagihan();
        tampilkanHalamanTagihan(1);
    });

    $('#search_tagihan').on('keyup', function (event) {
        if (event.key === 'Enter') {
            filterTagihanTerapan = ambilFilterTagihan();
            tagihanResult(1, false);
        }
    });
});


function ambilFilterTagihan() {
    return {
        id_periode: $('#id_periode_tagihan').val(),
        tipe_tagihan: $('#tipe_tagihan').val(),
        status_pembayaran: $('#status_tagihan').val(),
        search: $('#search_tagihan').val()
    };
}

function resetFilterTagihan(muatanUlang) {
    $('#id_periode_tagihan').val('');
    $('#tipe_tagihan').val('');
    $('#status_tagihan').val('');
    $('#search_tagihan').val('');

    filterTagihanTerapan = { id_periode: '', tipe_tagihan: '', status_pembayaran: '', search: '' };

    if (muatanUlang) {
        tagihanResult(1, false);
    }
}

function tagihanResult(halaman, refreshPeriode) {
    halamanTagihan = halaman || 1;
    var nomorPermintaan = ++permintaanTagihan;

    var idSiswa = $('#id_siswa_tagihan').val();
    if (!idSiswa) {
        semuaTagihan = [];
        $('#data-tagihan').html('<tr><td colspan="8" class="sdk-empty">Belum ada anak yang terhubung dengan akun Wali.</td></tr>');
        $('#info-tagihan').text('0 data');
        renderPaginationTagihan({});
        return;
    }

    semuaTagihan = [];
    renderPaginationTagihan();
    $('#data-tagihan').html('<tr><td colspan="8" class="sdk-empty">Memuat data tagihan...</td></tr>');

    $.ajax({
        url: '<?= base_url('wali/tagihan/tagihan/result') ?>',
        type: 'POST',
        data: {
            id_siswa: idSiswa,
            id_periode: filterTagihanTerapan.id_periode,
            tipe_tagihan: filterTagihanTerapan.tipe_tagihan,
            status_pembayaran: filterTagihanTerapan.status_pembayaran,
            search: filterTagihanTerapan.search,
            halaman: 1,
            per_halaman: $('#per_halaman_tagihan').val(),
            client_paging: 1
        },
        dataType: 'JSON',
        success: function (response) {
            if (nomorPermintaan !== permintaanTagihan) return;
            if (response.result !== 'true') {
                semuaTagihan = [];
                $('#data-tagihan').html('<tr><td colspan="8" class="sdk-empty text-danger">' + escapeHtml(response.message || 'Data tidak dapat dimuat.') + '</td></tr>');
                $('#info-tagihan').text('0 data');
                renderPaginationTagihan({});
                return;
            }

            if (refreshPeriode) {
                isiPeriode(response.periode_list || []);
            }

            tampilkanTagihan(response.data || {});
        },
        error: function (xhr) {
            if (nomorPermintaan !== permintaanTagihan) return;
            semuaTagihan = [];
            $('#data-tagihan').html('<tr><td colspan="8" class="sdk-empty text-danger">' + escapeHtml(ajaxErrorMessage(xhr)) + '</td></tr>');
            $('#info-tagihan').text('0 data');
            renderPaginationTagihan({});
        }
    });
}

function tampilkanTagihan(data) {
    semuaTagihan = Array.isArray(data.rows) ? data.rows : [];
    halamanTagihan = 1;
    renderPaginationTagihan();
    tampilkanHalamanTagihan(1);
}

function tampilkanHalamanTagihan(halaman) {
    halamanTagihan = halaman || 1;
    var perHalaman = parseInt($('#per_halaman_tagihan').val(), 10) || 10;
    var awal = (halamanTagihan - 1) * perHalaman;
    var rows = semuaTagihan.slice(awal, awal + perHalaman);
    var html = '';

    if (rows.length === 0) {
        html = '<tr><td colspan="8" class="sdk-empty">Tidak ada tagihan sesuai filter.</td></tr>';
    } else {
        rows.forEach(function (row) {
            var periodeTagihan = row.nama_bulan
                ? row.nama_bulan + ' ' + row.tahun
                : (row.periode || '-');

            var tunggakan = row.is_tunggakan === 'Ya'
                ? '<span class="badge bg-danger ms-1">Tunggakan</span>'
                : '';

            html += '<tr>' +
                '<td><span class="sdk-primary">' + escapeHtml(row.nama_tagihan || '-') + '</span>' +
                    '<span class="sdk-secondary">' + escapeHtml(row.no_tagihan || '-') + ' · ' + escapeHtml(row.tipe_tagihan || '-') + '</span></td>' +
                '<td>' + escapeHtml(periodeTagihan) + '<span class="sdk-secondary">' + escapeHtml(row.periode || '-') + '</span></td>' +
                '<td>' + formatRupiah(row.nominal_tagihan) + '</td>' +
                '<td class="text-success fw-semibold">' + formatRupiah(row.nominal_dibayar) + '</td>' +
                '<td class="text-danger fw-semibold">' + formatRupiah(row.sisa_tagihan) + '</td>' +
                '<td>' + escapeHtml(row.tanggal_jatuh_tempo || '-') + '</td>' +
                '<td>' + escapeHtml(row.is_tunggakan || '-') + '</td>' +
                '<td>' + statusBadge(row.status_pembayaran) + tunggakan + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-primary" onclick="detailTagihan(' + parseInt(row.id) + ')"><i class="ri-eye-line me-1"></i>Detail</button></td>' +
            '</tr>';
        });
    }

    $('#data-tagihan').html(html);
    $('#info-tagihan').text('Menampilkan ' + rows.length + ' dari ' + semuaTagihan.length + ' data');
}

function detailTagihan(id) {
    $('#detail-tagihan').html('Memuat detail...');
    $('#modal-detail-tagihan').modal('show');

    $.ajax({
        url: '<?= base_url('wali/tagihan/tagihan/detail') ?>',
        type: 'POST',
        data: {
            id: id,
            id_siswa: $('#id_siswa_tagihan').val()
        },
        dataType: 'JSON',
        success: function (response) {
            if (response.result !== 'true') {
                $('#detail-tagihan').html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Detail tidak ditemukan.') + '</div>');
                return;
            }

            var row = response.data;
            var periodeTagihan = row.nama_bulan
                ? row.nama_bulan + ' ' + row.tahun
                : (row.periode || '-');

            var html = '<div class="row g-3">' +
                detailItem('No. Tagihan', row.no_tagihan) +
                detailItem('Nama Tagihan', row.nama_tagihan) +
                detailItem('Jenis Tagihan', row.tipe_tagihan) +
                detailItem('Periode Tagihan', periodeTagihan) +
                detailItem('Tahun Pelajaran', row.periode) +
                detailItem('Jatuh Tempo', row.tanggal_jatuh_tempo) +
                detailItem('Nominal Tagihan', formatRupiah(row.nominal_tagihan)) +
                detailItem('Sudah Dibayar', formatRupiah(row.nominal_dibayar)) +
                detailItem('Sisa Tagihan', formatRupiah(row.sisa_tagihan)) +
                detailItem('Status Pembayaran', row.status_pembayaran) +
                detailItem('Tunggakan', row.is_tunggakan) +
                detailItem('Keterangan', row.keterangan || '-') +
            '</div>';

            $('#detail-tagihan').html(html);
        },
        error: function (xhr) {
            $('#detail-tagihan').html('<div class="alert alert-danger mb-0">' + escapeHtml(ajaxErrorMessage(xhr)) + '</div>');
        }
    });
}

function isiPeriode(rows) {
    var current = $('#id_periode_tagihan').val();
    var html = '<option value="">Semua</option>';

    rows.forEach(function (row) {
        html += '<option value="' + parseInt(row.id_periode) + '">' + escapeHtml(row.periode || '-') + '</option>';
    });

    $('#id_periode_tagihan').html(html);

    if (current && $('#id_periode_tagihan option[value="' + current + '"]').length) {
        $('#id_periode_tagihan').val(current);
    }
}

function detailItem(label, value) {
    return '<div class="col-md-6">' +
        '<div class="text-muted small">' + escapeHtml(label) + '</div>' +
        '<div class="fw-semibold">' + escapeHtml(value == null || value === '' ? '-' : value) + '</div>' +
    '</div>';
}

// Data hasil filter tersimpan di browser; klik halaman hanya menampilkan potongan array.
function renderPaginationTagihan() {
    var $pager = $('#pagination-tagihan').empty();
    if (semuaTagihan.length === 0) {
        $pager.hide();
        return;
    }
    $pager.show();
    new Pagination('#pagination-tagihan', {
        itemsCount: semuaTagihan.length,
        pageSize: parseInt($('#per_halaman_tagihan').val(), 10) || 10,
        currentPage: 1,
        onPageChange: function (paging) {
            tampilkanHalamanTagihan(paging.currentPage);
        }
    });
    $pager.find('.pagination').addClass('pagination-sm pagination-boxed mb-0');
}

function formatRupiah(value) {
    return 'Rp' + parseFloat(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function statusBadge(status) {
    var kelas = 'bg-secondary';
    if (status === 'Lunas' || status === 'Dibebaskan') kelas = 'bg-success';
    if (status === 'Dibayar Sebagian') kelas = 'bg-warning text-dark';
    if (status === 'Belum Dibayar' || status === 'Dibatalkan') kelas = 'bg-danger';
    return '<span class="badge ' + kelas + '">' + escapeHtml(status || '-') + '</span>';
}

function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

function ajaxErrorMessage(xhr) {
    if (xhr.responseJSON && xhr.responseJSON.message) {
        return xhr.responseJSON.message;
    }
    return 'Terjadi kesalahan saat mengambil data.';
}
</script>
