<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-body">
        <label for="id-siswa" class="form-label fw-semibold">Pilih Anak</label>
        <select class="form-select" id="id-siswa">
            <?php if (empty($anak_list)): ?>
                <option value="">Belum ada anak terhubung</option>
            <?php else: ?>
                <?php foreach ($anak_list as $anak): ?>
                    <option value="<?= (int) $anak['id_siswa'] ?>">
                        <?= html_escape($anak['nama_siswa']) ?> (<?= html_escape($anak['nis']) ?>)<?= !empty($anak['nama_kelas']) ? ' - ' . html_escape($anak['nama_kelas']) : '' ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
</div>

<div id="dashboard-loading" class="alert alert-info d-none">Memuat informasi anak...</div>
<div id="dashboard-error" class="alert alert-danger d-none"></div>

<div id="dashboard-content" class="d-none">
    <div class="card mb-3">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>
                <h4 class="mb-1" id="anak-nama">-</h4>
                <div class="text-muted" id="anak-identitas">-</div>
            </div>
            <div class="text-md-end">
                <span class="badge bg-primary-subtle text-primary" id="anak-kelas">-</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Tagihan</div>
                    <h4 class="mb-1" id="total-tagihan">Rp0</h4>
                    <small id="jumlah-tagihan">0 tagihan</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Pembayaran</div>
                    <h4 class="mb-1 text-success" id="total-pembayaran">Rp0</h4>
                    <small id="jumlah-lunas">0 tagihan lunas</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Sisa / Tunggakan</div>
                    <h4 class="mb-1 text-danger" id="total-sisa">Rp0</h4>
                    <small id="jumlah-belum-lunas">0 tagihan belum lunas</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Saldo Tabungan</div>
                    <h4 class="mb-1 text-primary" id="saldo-tabungan">Rp0</h4>
                    <small id="ringkasan-tabungan">Setoran Rp0 · Penarikan Rp0</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="header-title mb-1">Tagihan Belum Lunas</h4><small class="text-muted">Tagihan terdekat yang masih mempunyai sisa.</small>
                    </div>
                    <a href="<?= base_url('wali/tagihan/tagihan') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table sdk-data-table mb-0">
                            <thead>
                                <tr>
                                    <th>Tagihan</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Tunggakan</th>
                                    <th>Sisa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="tagihan-terdekat"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="header-title mb-1">Pembayaran Terakhir</h4><small class="text-muted">Transaksi pembayaran terbaru.</small>
                    </div>
                    <a href="<?= base_url('wali/pembayaran/riwayat_pembayaran') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table sdk-data-table mb-0">
                            <thead>
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="pembayaran-terakhir"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="header-title mb-1">Riwayat Tabungan Terakhir</h4><small class="text-muted">5 transaksi setoran atau penarikan tabungan terbaru.</small>
                    </div>
                    <a href="<?= base_url('wali/tabungan/tabungan') ?>" class="btn btn-sm btn-outline-primary">Lihat Tabungan</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table sdk-data-table mb-0">
                            <thead>
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="tabungan-terakhir"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        dashboardResult();

        $('#id-siswa').on('change', function() {
            dashboardResult();
        });
    });

    function dashboardResult() {
        var idSiswa = $('#id-siswa').val();

        $('#dashboard-error').addClass('d-none').text('');
        $('#dashboard-content').addClass('d-none');

        if (!idSiswa) {
            $('#dashboard-error').removeClass('d-none').text('Belum ada anak yang terhubung dengan akun Wali.');
            return;
        }

        $('#dashboard-loading').removeClass('d-none');

        $.ajax({
            url: '<?= base_url('wali/tagihan/dashboard/result') ?>',
            type: 'POST',
            data: {
                id_siswa: idSiswa
            },
            dataType: 'JSON',
            success: function(response) {
                $('#dashboard-loading').addClass('d-none');

                if (response.result !== 'true') {
                    $('#dashboard-error').removeClass('d-none').text(response.message || 'Data dashboard tidak dapat dimuat.');
                    return;
                }

                tampilkanDashboard(response.anak, response.data);
                $('#dashboard-content').removeClass('d-none');
            },
            error: function(xhr) {
                $('#dashboard-loading').addClass('d-none');
                $('#dashboard-error').removeClass('d-none').text(ajaxErrorMessage(xhr));
            }
        });
    }

    function tampilkanDashboard(anak, data) {
        var tagihan = data.tagihan || {};
        var tabungan = data.tabungan || {};

        $('#anak-nama').text(anak.nama_siswa || '-');
        $('#anak-identitas').text('NIS: ' + (anak.nis || '-') + (anak.status_siswa ? ' · ' + anak.status_siswa : ''));
        $('#anak-kelas').text(anak.nama_kelas || 'Kelas belum tersedia');

        $('#total-tagihan').text(formatRupiah(tagihan.total_tagihan));
        $('#total-pembayaran').text(formatRupiah(tagihan.total_pembayaran));
        $('#total-sisa').text(formatRupiah(tagihan.total_sisa));
        $('#saldo-tabungan').text(formatRupiah(tabungan.saldo));
        $('#jumlah-tagihan').text(parseInt(tagihan.jumlah_tagihan || 0) + ' tagihan');
        $('#jumlah-lunas').text(parseInt(tagihan.jumlah_lunas || 0) + ' tagihan lunas / dibebaskan');
        $('#jumlah-belum-lunas').text(parseInt(tagihan.jumlah_belum_lunas || 0) + ' tagihan belum lunas');
        $('#ringkasan-tabungan').text('Setoran ' + formatRupiah(tabungan.total_setoran) + ' · Penarikan ' + formatRupiah(tabungan.total_penarikan));

        renderTagihanTerdekat(data.tagihan_terdekat || []);
        renderPembayaranTerakhir(data.pembayaran_terakhir || []);
        renderTabunganTerakhir(data.riwayat_tabungan || []);
    }

    function renderTagihanTerdekat(rows) {
        var html = '';

        if (rows.length === 0) {
            html = '<tr><td colspan="4" class="sdk-empty">Tidak ada tagihan yang masih memiliki sisa.</td></tr>';
        } else {
            rows.forEach(function(row) {
                var periode = row.nama_bulan ? row.nama_bulan + ' ' + row.tahun : row.periode;
                html += '<tr>' +
                    '<td><span class="sdk-primary">' + escapeHtml(row.nama_tagihan) + '</span><span class="sdk-secondary">' + escapeHtml(periode || '-') + '</span></td>' +
                    '<td>' + escapeHtml(row.tanggal_jatuh_tempo || '-') + '</td>' +
                    '<td>' + escapeHtml(row.dianggap_tunggakan || '-') + '</td>' +
                    '<td class="fw-semibold text-danger">' + formatRupiah(row.sisa_tagihan) + '</td>' +
                    '<td>' + statusBadge(row.status_pembayaran) + '</td>' +
                    '</tr>';
            });
        }

        $('#tagihan-terdekat').html(html);
    }

    function renderPembayaranTerakhir(rows) {
        var html = '';

        if (rows.length === 0) {
            html = '<tr><td colspan="5" class="sdk-empty">Belum ada riwayat pembayaran.</td></tr>';
        } else {
            rows.forEach(function(row) {
                html += '<tr>' +
                    '<td><span class="sdk-primary">' + escapeHtml(row.no_transaksi) + '</span><span class="sdk-secondary">' + escapeHtml(row.nama_metode_pembayaran || '-') + '</span></td>' +
                    '<td>' + escapeHtml(row.tanggal_transaksi || '-') + '<span class="sdk-secondary">' + escapeHtml(row.waktu_transaksi || '') + '</span></td>' +
                    '<td class="fw-semibold">' + formatRupiah(row.total_pembayaran) + '</td>' +
                    '<td>' + statusBadge(row.status_transaksi) + '</td>' +
                    '<td><a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= base_url('wali/pembayaran/riwayat_pembayaran/bukti/') ?>' + parseInt(row.id) + '"><i class="ri-printer-line"></i></a></td>' +
                    '</tr>';
            });
        }

        $('#pembayaran-terakhir').html(html);
    }

    function renderTabunganTerakhir(rows) {
        var html = '';

        if (rows.length === 0) {
            html = '<tr><td colspan="5" class="sdk-empty">Belum ada transaksi tabungan.</td></tr>';
        } else {
            rows.forEach(function(row) {
                var nominalClass = row.jenis === 'Setoran' ? 'text-success' : 'text-danger';
                var prefix = row.jenis === 'Setoran' ? '+' : '-';
                html += '<tr>' +
                    '<td><span class="sdk-primary">' + escapeHtml(row.no_transaksi) + '</span></td>' +
                    '<td>' + escapeHtml(row.jenis) + '</td>' +
                    '<td>' + formatTanggal(row.tanggal_transaksi) + '</td>' +
                    '<td class="fw-semibold ' + nominalClass + '">' + prefix + formatRupiah(row.nominal) + '</td>' +
                    '<td>' + statusBadge(row.status_transaksi) + '</td>' +
                    '</tr>';
            });
        }

        $('#tabungan-terakhir').html(html);
    }

    function formatRupiah(value) {
        var number = parseFloat(value || 0);
        return 'Rp' + number.toLocaleString('id-ID', {
            maximumFractionDigits: 0
        });
    }

    function formatTanggal(value) {
        if (!value) return '-';
        var parts = String(value).split('-');
        if (parts.length !== 3) return value;
        var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var indexBulan = parseInt(parts[1], 10) - 1;
        if (indexBulan < 0 || indexBulan > 11) return value;
        return parts[2] + '-' + bulan[indexBulan] + '-' + parts[0];
    }

    function statusBadge(status) {
        status = status || '-';
        var lower = status.toLowerCase();
        var kelas = 'bg-secondary';

        if (lower === 'lunas' || lower === 'aktif' || lower === 'dibebaskan') kelas = 'bg-success';
        if (lower.indexOf('sebagian') !== -1) kelas = 'bg-warning text-dark';
        if (lower.indexOf('belum') !== -1) kelas = 'bg-danger';
        if (lower.indexOf('dibatalkan') !== -1) kelas = 'bg-danger';

        return '<span class="badge ' + kelas + '">' + escapeHtml(status) + '</span>';
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function ajaxErrorMessage(xhr) {
        if (xhr.responseJSON && xhr.responseJSON.message) return xhr.responseJSON.message;
        return 'Terjadi kesalahan saat mengambil data. Silakan muat ulang halaman.';
    }
</script>