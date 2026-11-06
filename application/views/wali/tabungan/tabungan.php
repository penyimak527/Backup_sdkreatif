<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-body">
        <label class="form-label fw-semibold" for="id_siswa_tabungan">Pilih Anak</label>
        <select class="form-select" id="id_siswa_tabungan">
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

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="tanggal_awal_tabungan">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal_tabungan">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="tanggal_akhir_tabungan">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir_tabungan">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="jenis_tabungan">Jenis</label>
                <select class="form-select" id="jenis_tabungan">
                    <option value="">Semua</option>
                    <option value="Setoran">Setoran</option>
                    <option value="Penarikan">Penarikan</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-semibold" for="status_tabungan">Status</label>
                <select class="form-select" id="status_tabungan">
                    <option value="">Semua</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div class="col-lg-4 col-md-12 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="button" id="btn_terapkan_tabungan">
                    <i class="ri-filter-3-line me-1"></i>Terapkan
                </button>
                <button class="btn btn-light" type="button" id="btn_reset_tabungan">Reset</button>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Total Setoran Sesuai Filter</div>
                <h4 class="text-success mb-1" id="total-setoran">Rp0</h4>
                <small id="jumlah-setoran">0 transaksi</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Total Penarikan Sesuai Filter</div>
                <h4 class="text-danger mb-1" id="total-penarikan">Rp0</h4>
                <small id="jumlah-penarikan">0 transaksi</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Saldo Tabungan Saat Ini</div>
                <h4 class="text-primary mb-1" id="saldo-tabungan">Rp0</h4>
                <small>Saldo aktif seluruh transaksi. Terpisah dari saldo Dompet Digital.</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h4 class="header-title mb-1">Riwayat Tabungan Anak</h4>
        <small class="text-muted">Informasi setoran dan penarikan tabungan. Tidak dapat diubah dari Portal Wali.</small>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table sdk-data-table mb-0">
                <thead>
                    <tr>
                        <th>Transaksi</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Metode</th>
                        <!-- <th>Kas / Bank</th> -->
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-tabungan">
                    <tr><td colspan="8" class="sdk-empty">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <small class="text-muted d-block mb-1" id="info-tabungan">0 data</small>
                <div class="mt-2" id="pagination-tabungan"></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted small" for="per_halaman_tabungan">Tampilkan</label>
                <select class="form-select form-select-sm" id="per_halaman_tabungan" style="width:90px">
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

<div class="modal fade" id="modal-detail-tabungan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tabungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-tabungan">Memuat detail...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var halamanTabungan = 1;
var semuaTabungan = [];
var permintaanTabungan = 0;
var filterTabunganTerapan = {
    jenis: '',
    status: '',
    tanggal_awal: '',
    tanggal_akhir: ''
};

$(document).ready(function () {
    tabunganResult(1);

    $('#id_siswa_tabungan').on('change', function () {
        resetFilterTabungan(false);
        tabunganResult(1);
    });

    $('#btn_terapkan_tabungan').on('click', function () {
        filterTabunganTerapan = ambilFilterTabungan();
        tabunganResult(1);
    });

    $('#btn_reset_tabungan').on('click', function () {
        resetFilterTabungan(true);
    });

    $('#per_halaman_tabungan').on('change', function () {
        halamanTabungan = 1;
        renderPaginationTabungan();
        tampilkanHalamanTabungan(1);
    });
});


function ambilFilterTabungan() {
    return {
        jenis: $('#jenis_tabungan').val(),
        status: $('#status_tabungan').val(),
        tanggal_awal: $('#tanggal_awal_tabungan').val(),
        tanggal_akhir: $('#tanggal_akhir_tabungan').val()
    };
}

function resetFilterTabungan(muatanUlang) {
    $('#tanggal_awal_tabungan').val('');
    $('#tanggal_akhir_tabungan').val('');
    $('#jenis_tabungan').val('');
    $('#status_tabungan').val('');

    filterTabunganTerapan = { jenis: '', status: '', tanggal_awal: '', tanggal_akhir: '' };

    if (muatanUlang) {
        tabunganResult(1);
    }
}

function tabunganResult(halaman) {
    halamanTabungan = halaman || 1;
    var nomorPermintaan = ++permintaanTabungan;

    var idSiswa = $('#id_siswa_tabungan').val();
    if (!idSiswa) {
        semuaTabungan = [];
        $('#data-tabungan').html('<tr><td colspan="8" class="sdk-empty">Belum ada anak yang terhubung dengan akun Wali.</td></tr>');
        tampilkanRingkasan({});
        $('#info-tabungan').text('0 data');
        renderPaginationTabungan({});
        return;
    }

    semuaTabungan = [];
    renderPaginationTabungan();
    $('#data-tabungan').html('<tr><td colspan="8" class="sdk-empty">Memuat riwayat tabungan...</td></tr>');

    $.ajax({
        url: '<?= base_url('wali/tabungan/tabungan/result') ?>',
        type: 'POST',
        data: {
            id_siswa: idSiswa,
            jenis: filterTabunganTerapan.jenis,
            status: filterTabunganTerapan.status,
            tanggal_awal: filterTabunganTerapan.tanggal_awal,
            tanggal_akhir: filterTabunganTerapan.tanggal_akhir,
            halaman: 1,
            per_halaman: $('#per_halaman_tabungan').val(),
            client_paging: 1
        },
        dataType: 'JSON',
        success: function (response) {
            if (nomorPermintaan !== permintaanTabungan) return;
            if (response.result !== 'true') {
                semuaTabungan = [];
                $('#data-tabungan').html('<tr><td colspan="8" class="sdk-empty text-danger">' + escapeHtml(response.message || 'Data tidak dapat dimuat.') + '</td></tr>');
                tampilkanRingkasan({});
                $('#info-tabungan').text('0 data');
                renderPaginationTabungan({});
                return;
            }

            tampilkanRingkasan(response.ringkasan || {});
            tampilkanTabungan(response.data || {});
        },
        error: function (xhr) {
            if (nomorPermintaan !== permintaanTabungan) return;
            semuaTabungan = [];
            $('#data-tabungan').html('<tr><td colspan="8" class="sdk-empty text-danger">' + escapeHtml(ajaxErrorMessage(xhr)) + '</td></tr>');
            tampilkanRingkasan({});
            $('#info-tabungan').text('0 data');
            renderPaginationTabungan({});
        }
    });
}

function tampilkanRingkasan(data) {
    $('#total-setoran').text(formatRupiah(data.total_setoran));
    $('#total-penarikan').text(formatRupiah(data.total_penarikan));
    $('#saldo-tabungan').text(formatRupiah(data.saldo));
    $('#jumlah-setoran').text(parseInt(data.jumlah_setoran || 0) + ' transaksi');
    $('#jumlah-penarikan').text(parseInt(data.jumlah_penarikan || 0) + ' transaksi');
}

function tampilkanTabungan(data) {
    semuaTabungan = Array.isArray(data.rows) ? data.rows : [];
    halamanTabungan = 1;
    renderPaginationTabungan();
    tampilkanHalamanTabungan(1);
}

function tampilkanHalamanTabungan(halaman) {
    halamanTabungan = halaman || 1;
    var perHalaman = parseInt($('#per_halaman_tabungan').val(), 10) || 10;
    var awal = (halamanTabungan - 1) * perHalaman;
    var rows = semuaTabungan.slice(awal, awal + perHalaman);
    var html = '';

    if (rows.length === 0) {
        html = '<tr><td colspan="8" class="sdk-empty">Belum ada transaksi tabungan sesuai filter.</td></tr>';
    } else {
        rows.forEach(function (row) {
            var nominalClass = row.jenis === 'Setoran' ? 'text-success' : 'text-danger';
            var prefix = row.jenis === 'Setoran' ? '+' : '-';
            var jenisBadge = row.jenis === 'Setoran'
                ? 'bg-success-subtle text-success'
                : 'bg-danger-subtle text-danger';

                // '<td>' + escapeHtml(row.nama_kasbank || '-') + '</td>' +
            html += '<tr>' +
                '<td><span class="sdk-primary">' + escapeHtml(row.no_transaksi || '-') + '</span></td>' +
                '<td>' + formatTanggal(row.tanggal_transaksi) + '</td>' +
                '<td><span class="badge ' + jenisBadge + '">' + escapeHtml(row.jenis || '-') + '</span></td>' +
                '<td>' + escapeHtml(row.metode_pembayaran || '-') + '</td>' +
                '<td class="fw-semibold ' + nominalClass + '">' + prefix + formatRupiah(row.nominal) + '</td>' +
                '<td>' + statusBadge(row.status_transaksi) + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-primary" onclick="detailTabungan(\'' + escapeJs(row.jenis) + '\',' + parseInt(row.id) + ')"><i class="ri-eye-line me-1"></i>Detail</button></td>' +
            '</tr>';
        });
    }

    $('#data-tabungan').html(html);
    $('#info-tabungan').text('Menampilkan ' + rows.length + ' dari ' + semuaTabungan.length + ' data');
}

function detailTabungan(jenis, id) {
    $('#detail-tabungan').html('Memuat detail...');
    $('#modal-detail-tabungan').modal('show');

    $.ajax({
        url: '<?= base_url('wali/tabungan/tabungan/detail') ?>',
        type: 'POST',
        data: {
            id: id,
            jenis: jenis,
            id_siswa: $('#id_siswa_tabungan').val()
        },
        dataType: 'JSON',
        success: function (response) {
            if (response.result !== 'true') {
                $('#detail-tabungan').html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Detail tidak ditemukan.') + '</div>');
                return;
            }

            var row = response.data;
            var html = '<div class="row g-3">' +
                detailItem('Jenis Transaksi', row.jenis) +
                detailItem('No. Transaksi', row.no_transaksi) +
                detailItem('Tanggal', formatTanggal(row.tanggal_transaksi)) +
                detailItem('Siswa', row.nama_siswa) +
                detailItem('Kelas', row.nama_kelas) +
                detailItem('Tahun Pelajaran', row.periode) +
                detailItem('Metode Pembayaran', row.metode_pembayaran) +
                // detailItem('Kas / Bank', row.nama_kasbank) +
                detailItem('Nominal', formatRupiah(row.nominal)) +
                detailItem('Status', row.status_transaksi) +
                detailItem('Catatan', row.catatan || '-') +
                detailItem('Alasan Pembatalan', row.alasan_batal || '-') +
            '</div>';

            $('#detail-tabungan').html(html);
        },
        error: function (xhr) {
            $('#detail-tabungan').html('<div class="alert alert-danger mb-0">' + escapeHtml(ajaxErrorMessage(xhr)) + '</div>');
        }
    });
}

function detailItem(label, value) {
    return '<div class="col-md-6">' +
        '<div class="text-muted small">' + escapeHtml(label) + '</div>' +
        '<div class="fw-semibold">' + escapeHtml(value == null || value === '' ? '-' : value) + '</div>' +
    '</div>';
}

// Data hasil filter tersimpan di browser; klik halaman hanya menampilkan potongan array.
function renderPaginationTabungan() {
    var $pager = $('#pagination-tabungan').empty();
    if (semuaTabungan.length === 0) {
        $pager.hide();
        return;
    }
    $pager.show();
    new Pagination('#pagination-tabungan', {
        itemsCount: semuaTabungan.length,
        pageSize: parseInt($('#per_halaman_tabungan').val(), 10) || 10,
        currentPage: 1,
        onPageChange: function (paging) {
            tampilkanHalamanTabungan(paging.currentPage);
        }
    });
    $pager.find('.pagination').addClass('pagination-sm pagination-boxed mb-0');
}

function formatRupiah(value) {
    return 'Rp' + parseFloat(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function formatTanggal(value) {
    if (!value) return '-';

    var parts = String(value).split('-');
    if (parts.length !== 3) return value;

    var bulan = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var indexBulan = parseInt(parts[1], 10) - 1;
    if (indexBulan < 0 || indexBulan > 11) return value;

    return parts[2] + '-' + bulan[indexBulan] + '-' + parts[0];
}

function statusBadge(status) {
    var kelas = status === 'Aktif' ? 'bg-success' : 'bg-danger';
    return '<span class="badge ' + kelas + '">' + escapeHtml(status || '-') + '</span>';
}

function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

function escapeJs(value) {
    return String(value == null ? '' : value)
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'");
}

function ajaxErrorMessage(xhr) {
    if (xhr.responseJSON && xhr.responseJSON.message) {
        return xhr.responseJSON.message;
    }
    return 'Terjadi kesalahan saat mengambil data.';
}
</script>
