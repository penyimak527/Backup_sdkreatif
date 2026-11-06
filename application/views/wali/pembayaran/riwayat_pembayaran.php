<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-body">
        <label class="form-label fw-semibold" for="id_siswa_pembayaran">Pilih Anak</label>
        <select class="form-select" id="id_siswa_pembayaran">
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
        <h4 class="header-title mb-1">Riwayat Pembayaran</h4>
        <small class="text-muted">Riwayat transaksi pembayaran anak. Data hanya dapat dilihat dari Portal Wali.</small>
    </div>

    <div class="card-body border-bottom">
        <div class="row g-2">
            <div class="col-lg-2 col-md-2">
                <label class="form-label fw-semibold" for="id_periode_pembayaran">Tahun Pelajaran</label>
                <select class="form-select" id="id_periode_pembayaran">
                    <option value="">Semua</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label class="form-label fw-semibold" for="status_pembayaran">Status</label>
                <select class="form-select" id="status_pembayaran">
                    <option value="">Semua</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label class="form-label fw-semibold" for="tanggal_awal_pembayaran">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal_pembayaran">
            </div>
            <div class="col-lg-2 col-md-2">
                <label class="form-label fw-semibold" for="tanggal_akhir_pembayaran">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir_pembayaran">
            </div>
             <div class="col-lg-3 col-md-3">
                <label class="form-label fw-semibold" for="search_pembayaran">Cari Nomor Transaksi</label>
                <input type="text" class="form-control" id="search_pembayaran" placeholder="Contoh: BYR/202610/00004">
            </div>
        </div>

        <div class="row g-2 align-items-end mt-1">
            <div class="col-lg-4 col-md-4">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary " type="button" id="btn_terapkan_pembayaran"><i class="ri-filter-3-line me-1"></i>Terapkan</button>
                    <button class="btn btn-light" type="button" id="btn_reset_pembayaran">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table sdk-data-table mb-0">
                <thead>
                    <tr>
                        <th>Transaksi</th>
                        <th>Tanggal</th>
                        <th>Kelas</th>
                        <th>Metode</th>
                        <th>Total Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-pembayaran">
                    <tr><td colspan="7" class="sdk-empty">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <small class="text-muted d-block mb-1" id="info-pembayaran">0 data</small>
                <div class="mt-2" id="pagination-pembayaran"></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted small" for="per_halaman_pembayaran">Tampilkan</label>
                <select class="form-select form-select-sm" id="per_halaman_pembayaran" style="width:90px">
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

<div class="modal fade" id="modal-detail-pembayaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-pembayaran">Memuat detail...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var halamanPembayaran = 1;
var semuaPembayaran = [];
var permintaanPembayaran = 0;
var filterPembayaranTerapan = {
    id_periode: '',
    status: '',
    tanggal_awal: '',
    tanggal_akhir: '',
    search: ''
};

$(document).ready(function () {
    pembayaranResult(1, true);

    $('#id_siswa_pembayaran').on('change', function () {
        resetFilterPembayaran(false);
        pembayaranResult(1, true);
    });

    $('#btn_terapkan_pembayaran').on('click', function () {
        filterPembayaranTerapan = ambilFilterPembayaran();
        pembayaranResult(1, false);
    });

    $('#btn_reset_pembayaran').on('click', function () {
        resetFilterPembayaran(true);
    });

    $('#per_halaman_pembayaran').on('change', function () {
        halamanPembayaran = 1;
        renderPaginationPembayaran();
        tampilkanHalamanPembayaran(1);
    });

    $('#search_pembayaran').on('keyup', function (event) {
        if (event.key === 'Enter') {
            filterPembayaranTerapan = ambilFilterPembayaran();
            pembayaranResult(1, false);
        }
    });
});


function ambilFilterPembayaran() {
    return {
        id_periode: $('#id_periode_pembayaran').val(),
        status: $('#status_pembayaran').val(),
        tanggal_awal: $('#tanggal_awal_pembayaran').val(),
        tanggal_akhir: $('#tanggal_akhir_pembayaran').val(),
        search: $('#search_pembayaran').val()
    };
}

function resetFilterPembayaran(muatanUlang) {
    $('#id_periode_pembayaran').val('');
    $('#status_pembayaran').val('');
    $('#tanggal_awal_pembayaran').val('');
    $('#tanggal_akhir_pembayaran').val('');
    $('#search_pembayaran').val('');

    filterPembayaranTerapan = { id_periode: '', status: '', tanggal_awal: '', tanggal_akhir: '', search: '' };

    if (muatanUlang) {
        pembayaranResult(1, false);
    }
}

function pembayaranResult(halaman, refreshPeriode) {
    halamanPembayaran = halaman || 1;
    var nomorPermintaan = ++permintaanPembayaran;

    var idSiswa = $('#id_siswa_pembayaran').val();
    if (!idSiswa) {
        semuaPembayaran = [];
        $('#data-pembayaran').html('<tr><td colspan="7" class="sdk-empty">Belum ada anak yang terhubung dengan akun Wali.</td></tr>');
        $('#info-pembayaran').text('0 data');
        renderPaginationPembayaran();
        return;
    }

    semuaPembayaran = [];
    renderPaginationPembayaran();
    $('#data-pembayaran').html('<tr><td colspan="7" class="sdk-empty">Memuat riwayat pembayaran...</td></tr>');

    $.ajax({
        url: '<?= base_url('wali/pembayaran/riwayat_pembayaran/result') ?>',
        type: 'POST',
        data: {
            id_siswa: idSiswa,
            id_periode: filterPembayaranTerapan.id_periode,
            status: filterPembayaranTerapan.status,
            tanggal_awal: filterPembayaranTerapan.tanggal_awal,
            tanggal_akhir: filterPembayaranTerapan.tanggal_akhir,
            search: filterPembayaranTerapan.search,
            halaman: 1,
            per_halaman: $('#per_halaman_pembayaran').val(),
            client_paging: 1
        },
        dataType: 'JSON',
        success: function (response) {
            if (nomorPermintaan !== permintaanPembayaran) return;
            if (response.result !== 'true') {
                semuaPembayaran = [];
                $('#data-pembayaran').html('<tr><td colspan="7" class="sdk-empty text-danger">' + escapeHtml(response.message || 'Data tidak dapat dimuat.') + '</td></tr>');
                $('#info-pembayaran').text('0 data');
                renderPaginationPembayaran();
                return;
            }

            if (refreshPeriode) {
                isiPeriode(response.periode_list || []);
            }

            tampilkanPembayaran(response.data || {});
        },
        error: function (xhr) {
            if (nomorPermintaan !== permintaanPembayaran) return;
            semuaPembayaran = [];
            $('#data-pembayaran').html('<tr><td colspan="7" class="sdk-empty text-danger">' + escapeHtml(ajaxErrorMessage(xhr)) + '</td></tr>');
            $('#info-pembayaran').text('0 data');
            renderPaginationPembayaran();
        }
    });
}

function tampilkanPembayaran(data) {
    semuaPembayaran = Array.isArray(data.rows) ? data.rows : [];
    halamanPembayaran = 1;
    renderPaginationPembayaran();
    tampilkanHalamanPembayaran(1);
}

function tampilkanHalamanPembayaran(halaman) {
    halamanPembayaran = halaman || 1;
    var perHalaman = parseInt($('#per_halaman_pembayaran').val(), 10) || 10;
    var awal = (halamanPembayaran - 1) * perHalaman;
    var rows = semuaPembayaran.slice(awal, awal + perHalaman);
    var html = '';

    if (rows.length === 0) {
        html = '<tr><td colspan="7" class="sdk-empty">Belum ada riwayat pembayaran sesuai filter.</td></tr>';
    } else {
        rows.forEach(function (row) {
            html += '<tr>' +
                '<td><span class="sdk-primary">' + escapeHtml(row.no_transaksi || '-') + '</span>' +
                    '<span class="sdk-secondary">' + escapeHtml(row.periode || '-') + '</span></td>' +
                '<td>' + escapeHtml(row.tanggal_transaksi || '-') + '<span class="sdk-secondary">' + escapeHtml(row.waktu_transaksi || '') + '</span></td>' +
                '<td>' + escapeHtml(row.nama_kelas || '-') + '</td>' +
                '<td>' + escapeHtml(row.nama_metode_pembayaran || '-') + '</td>' +
                '<td class="fw-semibold">' + formatRupiah(row.total_pembayaran) + '</td>' +
                '<td>' + statusBadge(row.status_transaksi) + '</td>' +
                '<td><div class="d-flex gap-1">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary" onclick="detailPembayaran(' + parseInt(row.id) + ')"><i class="ri-eye-line"></i></button>' +
                    '<a target="_blank" class="btn btn-sm btn-outline-success" href="<?= base_url('wali/pembayaran/riwayat_pembayaran/bukti/') ?>' + parseInt(row.id) + '"><i class="ri-printer-line"></i></a>' +
                '</div></td>' +
            '</tr>';
        });
    }

    $('#data-pembayaran').html(html);
    $('#info-pembayaran').text('Menampilkan ' + rows.length + ' dari ' + semuaPembayaran.length + ' data');
}

function detailPembayaran(id) {
    $('#detail-pembayaran').html('Memuat detail...');
    $('#modal-detail-pembayaran').modal('show');

    $.ajax({
        url: '<?= base_url('wali/pembayaran/riwayat_pembayaran/detail') ?>',
        type: 'POST',
        data: {
            id: id,
            id_siswa: $('#id_siswa_pembayaran').val()
        },
        dataType: 'JSON',
        success: function (response) {
            if (response.result !== 'true') {
                $('#detail-pembayaran').html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Detail tidak ditemukan.') + '</div>');
                return;
            }

            var header = response.data.header;
            var detail = response.data.detail || [];
            var rows = '';

            detail.forEach(function (item) {
                var periodeTagihan = item.nama_bulan
                    ? item.nama_bulan + ' ' + item.tahun
                    : '-';

                rows += '<tr>' +
                    '<td><span class="sdk-primary">' + escapeHtml(item.nama_tagihan || '-') + '</span>' +
                        '<span class="sdk-secondary">' + escapeHtml(periodeTagihan) + '</span></td>' +
                    '<td>' + formatRupiah(item.nominal_tagihan) + '</td>' +
                    '<td class="text-success fw-semibold">' + formatRupiah(item.nominal_bayar) + '</td>' +
                    '<td>' + formatRupiah(item.sisa_setelah) + '</td>' +
                    '<td>' + statusBadge(item.status_setelah || item.status_detail) + '</td>' +
                '</tr>';
            });

            if (!rows) {
                rows = '<tr><td colspan="5" class="sdk-empty">Tidak ada rincian pembayaran.</td></tr>';
            }

            var html = '<div class="row g-3 mb-3">' +
                detailItem('No. Transaksi', header.no_transaksi) +
                detailItem('Tanggal', (header.tanggal_transaksi || '-') + ' ' + (header.waktu_transaksi || '')) +
                detailItem('Siswa', header.nama_siswa) +
                detailItem('Kelas', header.nama_kelas) +
                detailItem('Tahun Pelajaran', header.periode) +
                detailItem('Metode Pembayaran', header.nama_metode_pembayaran) +
                detailItem('Total Pembayaran', formatRupiah(header.total_pembayaran)) +
                detailItem('Status Transaksi', header.status_transaksi) +
            '</div>' +
            '<div class="table-responsive">' +
                '<table class="table sdk-data-table mb-0">' +
                    '<thead><tr><th>Tagihan</th><th>Nominal</th><th>Dibayar</th><th>Sisa</th><th>Status</th></tr></thead>' +
                    '<tbody>' + rows + '</tbody>' +
                '</table>' +
            '</div>' +
            '<div class="mt-3 text-end">' +
                '<a target="_blank" class="btn btn-primary" href="<?= base_url('wali/pembayaran/riwayat_pembayaran/bukti/') ?>' + parseInt(header.id) + '">' +
                    '<i class="ri-printer-line me-1"></i>Buka Bukti Pembayaran' +
                '</a>' +
            '</div>';

            $('#detail-pembayaran').html(html);
        },
        error: function (xhr) {
            $('#detail-pembayaran').html('<div class="alert alert-danger mb-0">' + escapeHtml(ajaxErrorMessage(xhr)) + '</div>');
        }
    });
}

function isiPeriode(rows) {
    var current = $('#id_periode_pembayaran').val();
    var html = '<option value="">Semua</option>';

    rows.forEach(function (row) {
        html += '<option value="' + parseInt(row.id_periode) + '">' + escapeHtml(row.periode || '-') + '</option>';
    });

    $('#id_periode_pembayaran').html(html);

    if (current && $('#id_periode_pembayaran option[value="' + current + '"]').length) {
        $('#id_periode_pembayaran').val(current);
    }
}

function detailItem(label, value) {
    return '<div class="col-md-6">' +
        '<div class="text-muted small">' + escapeHtml(label) + '</div>' +
        '<div class="fw-semibold">' + escapeHtml(value == null || value === '' ? '-' : value) + '</div>' +
    '</div>';
}

// Data hasil filter diambil sekali, kemudian halaman dibagi di browser tanpa request baru.
function renderPaginationPembayaran() {
    var $pager = $('#pagination-pembayaran').empty();
    if (semuaPembayaran.length === 0) {
        $pager.hide();
        return;
    }
    $pager.show();
    new Pagination('#pagination-pembayaran', {
        itemsCount: semuaPembayaran.length,
        pageSize: parseInt($('#per_halaman_pembayaran').val(), 10) || 10,
        currentPage: 1,
        onPageChange: function (paging) {
            tampilkanHalamanPembayaran(paging.currentPage);
        }
    });
    $pager.find('.pagination').addClass('pagination-sm pagination-boxed mb-0');
}

function formatRupiah(value) {
    return 'Rp' + parseFloat(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function statusBadge(status) {
    var kelas = 'bg-secondary';
    if (status === 'Aktif' || status === 'Lunas' || status === 'Dibebaskan') kelas = 'bg-success';
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
