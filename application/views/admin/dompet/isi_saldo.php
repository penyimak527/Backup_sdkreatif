<?php
defined('BASEPATH') or exit('No direct script access allowed');

$transfer_accounts = $transfer_accounts ?? [];
$cash_accounts = $cash_accounts ?? [];
$setting = $setting ?? [];
$initial_status = $initial_status ?? '';
?>

<div class="alert alert-info">
    <b>Isi saldo terintegrasi.</b>
    Pengajuan wali tetap melalui verifikasi. Admin dapat menambahkan saldo langsung;
    saldo dan pencatatan keuangan diposting atomik saat tombol simpan ditekan.
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="header-title mb-0">Isi Saldo</h4>
            <small class="text-muted">
                Pokok masuk ke wallet siswa, biaya layanan dicatat terpisah.
            </small>
        </div>

        <?php if (!empty($can_direct_topup)): ?>
            <button id="openDirect" class="btn btn-primary">
                <i class="mdi mdi-plus"></i> Tambah Isi Saldo
            </button>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <select id="jenis" class="form-select">
                    <option value="PENGAJUAN">Pengajuan</option>
                    <option value="RIWAYAT">Riwayat</option>
                </select>
            </div>

            <div class="col-md-4">
                <input
                    id="search"
                    class="form-control"
                    placeholder="Cari nomor, NIS, atau nama"
                >
            </div>

            <div class="col-md-3">
                <select id="topupStatusFilter" class="form-select">
                    <option value="">Semua status</option>
                    <option
                        value="MENUNGGU_VERIFIKASI"
                        <?= $initial_status === 'MENUNGGU_VERIFIKASI' ? 'selected' : '' ?>
                    >
                        Menunggu Verifikasi
                    </option>
                    <option value="DIVERIFIKASI">Diverifikasi</option>
                    <option value="DISETUJUI">Disetujui</option>
                    <option value="DITOLAK">Ditolak</option>
                    <option value="DIBATALKAN_WALI">Dibatalkan Wali</option>
                    <option value="DIBATALKAN_ADMIN">Dibatalkan Admin</option>
                    <option value="BERHASIL">Berhasil</option>
                    <option value="DIBATALKAN">Dibatalkan</option>
                </select>
            </div>

            <div class="col-md-2">
                <button id="filter" class="btn btn-outline-primary w-100">
                    Terapkan
                </button>
            </div>
        </div>

        <div id="rows" class="sdk-record-list">
            <div class="sdk-record-empty">Memuat data...</div>
        </div>

        <div class="sdk-pager-wrap">
            <div>
                <small id="info" class="text-muted"></small>
                <div class="mt-2" id="topupPagination"></div>
            </div>

            <div class="sdk-page-size">
                <label for="topupPageSize" class="mb-0">Tampilkan</label>
                <select class="form-select form-select-sm" id="topupPageSize">
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

<div class="modal fade" id="topupDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Detail Isi Saldo</h5>
                    <small class="text-muted" id="topupDetailNumber"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="topupDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Verifikasi Isi Saldo</h5>
                    <small class="text-muted">Pilih metode pembayaran sebelum memproses saldo.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="verifySubmissionId">

                <div class="mb-3">
                    <label for="verifyPaymentMethod" class="form-label">
                        Metode pembayaran
                    </label>
                    <select id="verifyPaymentMethod" class="form-select">
                        <option value="TRANSFER" selected>Transfer</option>
                        <option value="CASH">Cash</option>
                    </select>
                </div>

                <div id="verifyTransferGroup" class="mb-3 d-none">
                    <label for="verifyBankAccount" class="form-label">Bank penerima</label>
                    <select id="verifyBankAccount" class="form-select">
                        <?php if (empty($transfer_accounts)): ?>
                            <option value="">Rekening bank aktif belum tersedia</option>
                        <?php else: ?>
                            <?php foreach ($transfer_accounts as $account): ?>
                                <option value="<?= (int) $account['id'] ?>">
                                    <?= html_escape($account['nama_bank']) ?>
                                    - <?= html_escape($account['nomor_rekening']) ?>
                                    - <?= html_escape($account['nama_pemilik']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">BNI otomatis menjadi pilihan pertama bila aktif.</small>
                </div>

                <div id="verifyCashGroup" class="mb-3 d-none">
                    <label for="verifyCashAccount" class="form-label">Kas penerima</label>
                    <select id="verifyCashAccount" class="form-select">
                        <?php if (empty($cash_accounts)): ?>
                            <option value="">Kas tunai belum tersedia</option>
                        <?php else: ?>
                            <?php foreach ($cash_accounts as $account): ?>
                                <option value="<?= (int) $account['id'] ?>">
                                    <?= html_escape($account['nama_kasbank']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="alert alert-warning mb-0 py-2">
                    Pokok saldo dan biaya layanan akan diposting atomik ke tujuan yang dipilih.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Batal
                </button>
                <button id="confirmVerification" type="button" class="btn btn-success">
                    Verifikasi dan Posting
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($can_direct_topup)): ?>
    <div class="modal fade" id="directModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Isi Saldo Langsung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cari siswa</label>
                        <div class="input-group">
                            <input
                                id="studentSearch"
                                class="form-control"
                                placeholder="NIS atau nama siswa"
                            >
                            <button
                                id="searchStudent"
                                type="button"
                                class="btn btn-outline-secondary"
                            >
                                Cari
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="directStudent" class="form-label">
                            Siswa dengan wallet aktif
                        </label>
                        <select id="directStudent" class="form-select">
                            <option value="">Cari dan pilih siswa</option>
                        </select>
                        <small id="currentBalance" class="text-muted"></small>
                    </div>

                    <div class="mb-3">
                        <label for="directPaymentMethod" class="form-label">
                            Metode pembayaran
                        </label>
                        <select id="directPaymentMethod" class="form-select">
                            <option value="TRANSFER" selected>Transfer</option>
                            <option value="CASH">Cash</option>
                        </select>
                    </div>

                    <div id="directTransferGroup" class="mb-3">
                        <label for="directBankAccount" class="form-label">Bank penerima</label>
                        <select id="directBankAccount" class="form-select">
                            <?php if (empty($transfer_accounts)): ?>
                                <option value="">Rekening bank aktif belum tersedia</option>
                            <?php else: ?>
                                <?php foreach ($transfer_accounts as $account): ?>
                                    <option value="<?= (int) $account['id'] ?>">
                                        <?= html_escape($account['nama_bank']) ?>
                                        - <?= html_escape($account['nomor_rekening']) ?>
                                        - <?= html_escape($account['nama_pemilik']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">BNI otomatis dipilih bila rekeningnya aktif.</small>
                    </div>

                    <div id="directCashGroup" class="mb-3 d-none">
                        <label for="directCashAccount" class="form-label">Kas penerima</label>
                        <select id="directCashAccount" class="form-select">
                            <?php if (empty($cash_accounts)): ?>
                                <option value="">Kas tunai belum tersedia</option>
                            <?php else: ?>
                                <?php foreach ($cash_accounts as $account): ?>
                                    <option value="<?= (int) $account['id'] ?>">
                                        <?= html_escape($account['nama_kasbank']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="directAmount" class="form-label">Nominal masuk ke saldo</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                id="directAmount"
                                type="text"
                                inputmode="numeric"
                                class="form-control rupiah-input"
                                autocomplete="off"
                            >
                        </div>
                        <small class="text-muted">
                            Minimal Rp <?= number_format((int) ($setting['minimal_isi_saldo'] ?? 0), 0, ',', '.') ?>,
                            maksimal Rp <?= number_format((int) ($setting['maksimal_isi_saldo'] ?? 0), 0, ',', '.') ?>.
                        </small>
                    </div>

                    <div class="rounded border p-3 bg-light">
                        <div class="d-flex justify-content-between">
                            <span>Saldo siswa bertambah</span>
                            <b id="directPrincipal">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Biaya layanan</span>
                            <b>
                                Rp <?= number_format((int) ($setting['biaya_layanan'] ?? 0), 0, ',', '.') ?>
                            </b>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span>Total penerimaan</span>
                            <b id="directTotal">
                                Rp <?= number_format((int) ($setting['biaya_layanan'] ?? 0), 0, ',', '.') ?>
                            </b>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0 py-2">
                        Transaksi langsung berhasil setelah disimpan dan tidak masuk antrean verifikasi.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button id="saveDirect" type="button" class="btn btn-primary">
                        Simpan dan Posting
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
$(function () {
    const baseUrl = <?= json_encode(base_url('admin/dompet/isi_saldo/'), JSON_UNESCAPED_SLASHES) ?>;
    const csrfToken = <?= json_encode($csrf_token) ?>;
    const serviceFee = <?= (int) ($setting['biaya_layanan'] ?? 0) ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('topupDetailModal'));
    const verifyModal = new bootstrap.Modal(document.getElementById('verifyModal'));

    let page = 1;
    let limit = 10;

    function escapeHtml(value) {
        return $('<div>').text(value ?? '-').html();
    }

    function formatMoney(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
    }

    function paymentMethodLabel(value) {
        const method = String(value || '').toUpperCase();

        if (method === 'TRANSFER') {
            return 'Transfer';
        }

        if (method === 'CASH') {
            return 'Cash';
        }

        return '';
    }

    function request(path, data) {
        return $.post(baseUrl + path, data, null, 'json');
    }

    function showFailure(xhr) {
        const response = xhr.responseJSON || {};
        Swal.fire('Gagal', response.message || 'Terjadi kesalahan.', 'error');
    }

    function togglePaymentFields(method, transferSelector, cashSelector) {
        $(transferSelector).toggleClass('d-none', method !== 'TRANSFER');
        $(cashSelector).toggleClass('d-none', method !== 'CASH');
    }

    function paymentPayload(methodSelector, bankSelector, cashSelector) {
        const method = $(methodSelector).val();

        return {
            metode_pembayaran: method,
            id_rekening_dompet: method === 'TRANSFER' ? $(bankSelector).val() : '',
            id_kasbank: method === 'CASH' ? $(cashSelector).val() : '',
        };
    }

    function validatePayment(payload) {
        if (!payload.metode_pembayaran) {
            Swal.fire('Belum lengkap', 'Pilih metode pembayaran terlebih dahulu.', 'warning');
            return false;
        }

        if (payload.metode_pembayaran === 'TRANSFER' && !payload.id_rekening_dompet) {
            Swal.fire('Belum lengkap', 'Pilih rekening bank penerima.', 'warning');
            return false;
        }

        if (payload.metode_pembayaran === 'CASH' && !payload.id_kasbank) {
            Swal.fire('Belum lengkap', 'Pilih kas penerima.', 'warning');
            return false;
        }

        return true;
    }

    function load(rebuildPager) {
        request('result', {
            jenis: $('#jenis').val(),
            search: $('#search').val(),
            status: $('#topupStatusFilter').val(),
            page: page,
            limit: limit,
        }).done(function (response) {
            let html = '';
            const isHistory = response.meta.jenis === 'RIWAYAT';

            (response.data || []).forEach(function (item, index) {
                let actions = `
                    <button
                        class="btn btn-sm btn-outline-info detail"
                        data-id="${item.id}"
                        data-kind="${isHistory ? 'RIWAYAT' : 'PENGAJUAN'}"
                        title="Detail"
                        aria-label="Detail"
                        data-bs-toggle="tooltip"
                    >
                        <i class="ri-eye-line"></i>
                    </button>
                `;

                if (!isHistory && item.status_pengajuan === 'MENUNGGU_VERIFIKASI') {
                    actions += `
                        <button
                            class="btn btn-sm btn-success verify"
                            data-id="${item.id}"
                            title="Verifikasi"
                            aria-label="Verifikasi"
                            data-bs-toggle="tooltip"
                        >
                            <i class="ri-checkbox-circle-line"></i>
                        </button>
                        <button
                            class="btn btn-sm btn-outline-danger reject"
                            data-id="${item.id}"
                            title="Tolak"
                            aria-label="Tolak"
                            data-bs-toggle="tooltip"
                        >
                            <i class="ri-close-circle-line"></i>
                        </button>
                    `;
                }

                if (isHistory && item.status_transaksi === 'BERHASIL') {
                    actions += `
                        <button
                            class="btn btn-sm btn-outline-danger reverse"
                            data-id="${item.id}"
                            title="Reversal"
                            aria-label="Reversal"
                            data-bs-toggle="tooltip"
                        >
                            <i class="ri-arrow-go-back-line"></i>
                        </button>
                    `;
                }

                const number = isHistory ? item.no_transaksi : item.no_pengajuan;
                const date = isHistory ? item.tanggal : item.tanggal_pengajuan;
                const status = isHistory ? item.status_transaksi : item.status_pengajuan;
                const sourceBadge = isHistory && item.sumber_transaksi === 'ADMIN_LANGSUNG'
                    ? '<span class="badge bg-primary ms-2">Admin langsung</span>'
                    : '';

                html += `
                    <div class="sdk-record-card">
                        <div class="sdk-record-head">
                            <span>
                                ${isHistory ? 'Transaksi' : 'Pengajuan'}:
                                <strong>${escapeHtml(number)}</strong>
                                ${sourceBadge}
                            </span>
                            ${SDKreatifUI.statusBadge(status)}
                        </div>
                        <div class="sdk-record-main">
                            <div class="flex-grow-1">
                                <h5 class="sdk-record-title">
                                    ${(page - 1) * limit + index + 1}.
                                    ${escapeHtml(item.nama_siswa)}
                                </h5>
                                <div class="sdk-record-meta">
                                    <span>
                                        <i class="ri-calendar-line me-1"></i>
                                        ${escapeHtml(date)}
                                    </span>
                                    <span>NIS: <strong>${escapeHtml(item.nis)}</strong></span>
                                    <span>
                                        Pokok: <strong>Rp ${formatMoney(item.nominal_saldo)}</strong>
                                    </span>
                                    <span>
                                        Biaya Layanan:
                                        <strong>Rp ${formatMoney(item.biaya_layanan)}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="sdk-record-actions">${actions}</div>
                        </div>
                    </div>
                `;
            });

            $('#rows').html(html || '<div class="sdk-record-empty">Tidak ada data.</div>');
            SDKreatifUI.refreshTooltips('#rows');

            page = response.meta.page;
            $('#info').text(response.meta.total + ' data');

            if (rebuildPager !== false) {
                SDKreatifUI.serverPager(
                    '#topupPagination',
                    response.meta,
                    function (paging) {
                        page = paging.currentPage;
                        load(false);
                    }
                );
            }
        }).fail(showFailure);
    }

    $('#filter').on('click', function () {
        page = 1;
        load(true);
    });

    $('#jenis').on('change', function () {
        page = 1;
        load(true);
    });

    $('#topupPageSize').on('change', function () {
        limit = parseInt(this.value, 10) || 10;
        page = 1;
        load(true);
    });

    $('#search').on('keydown', function (event) {
        if (event.key === 'Enter') {
            page = 1;
            load(true);
        }
    });

    $('#rows').on('click', '.detail', function () {
        const kind = $(this).data('kind');

        request('detail', {
            id: $(this).data('id'),
            jenis: kind,
        }).done(function (response) {
            const item = response.data || {};
            const isHistory = kind === 'RIWAYAT';
            const number = isHistory ? item.no_transaksi : item.no_pengajuan;
            const status = isHistory ? item.status_transaksi : item.status_pengajuan;
            const date = isHistory
                ? `${item.tanggal || '-'} ${item.waktu || ''}`
                : `${item.tanggal_pengajuan || '-'} ${item.waktu_pengajuan || ''}`;
            const total = item.total_pembayaran ?? item.total_transfer;
            const proof = item.bukti ?? item.bukti_transfer;
            const note = item.keterangan ?? item.catatan_wali;
            const source = isHistory ? item.sumber_transaksi : 'Pengajuan Wali';
            const processor = item.nama_user ?? item.nama_user_verifikasi;
            const showCashBank = isHistory
                || status === 'DIVERIFIKASI'
                || status === 'DISETUJUI';
            const paymentMethod = showCashBank
                ? paymentMethodLabel(item.metode_pembayaran)
                : '';
            const cashBank = showCashBank ? (item.nama_kasbank || '') : '';

            $('#topupDetailNumber').text(number || '-');
            $('#topupDetailBody').html(`
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 border rounded p-3 mb-3">
                    <div>
                        <small class="text-muted">${isHistory ? 'Transaksi' : 'Pengajuan'}</small>
                        <h5 class="mb-1">${escapeHtml(number)}</h5>
                        <div class="text-muted">
                            <i class="ri-calendar-line me-1"></i>${escapeHtml(date)}
                        </div>
                    </div>
                    ${SDKreatifUI.statusBadge(status)}
                </div>

                <div class="border rounded p-3 mb-3">
                    <small class="text-muted">Siswa</small>
                    <h5 class="mb-1 mt-1">${escapeHtml(item.nama_siswa)}</h5>
                    <span class="text-muted">NIS ${escapeHtml(item.nis)}</span>
                    ${item.nama_wali
                        ? `<span class="text-muted d-block mt-1">Diajukan oleh ${escapeHtml(item.nama_wali)}</span>`
                        : ''}
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">Pokok Saldo</small>
                            <h5 class="mt-1 mb-0">Rp ${formatMoney(item.nominal_saldo)}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">Biaya Layanan</small>
                            <h5 class="mt-1 mb-0">Rp ${formatMoney(item.biaya_layanan)}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">Total Pembayaran</small>
                            <h5 class="mt-1 mb-0">Rp ${formatMoney(total)}</h5>
                        </div>
                    </div>
                </div>

                ${isHistory ? `
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <small class="text-muted">Saldo Sebelum</small>
                                <strong class="d-block mt-1">Rp ${formatMoney(item.saldo_sebelum)}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <small class="text-muted">Saldo Sesudah</small>
                                <strong class="d-block mt-1">Rp ${formatMoney(item.saldo_sesudah)}</strong>
                            </div>
                        </div>
                    </div>
                ` : ''}

                <dl class="row mb-0">
                    <dt class="col-sm-4">Sumber</dt>
                    <dd class="col-sm-8">${escapeHtml(source)}</dd>
                    <dt class="col-sm-4">Metode Pembayaran</dt>
                    <dd class="col-sm-8">
                        ${paymentMethod === '' ? '' : escapeHtml(paymentMethod)}
                    </dd>
                    <dt class="col-sm-4">Kas/Bank</dt>
                    <dd class="col-sm-8">${cashBank === '' ? '' : escapeHtml(cashBank)}</dd>
                    <dt class="col-sm-4">Bukti Transfer</dt>
                    <dd class="col-sm-8">${escapeHtml(proof)}</dd>
                    <dt class="col-sm-4">Catatan</dt>
                    <dd class="col-sm-8">${escapeHtml(note)}</dd>
                    <dt class="col-sm-4">Diproses Oleh</dt>
                    <dd class="col-sm-8">${escapeHtml(processor)}</dd>
                    ${!isHistory && item.tanggal_verifikasi ? `
                        <dt class="col-sm-4">Waktu Verifikasi</dt>
                        <dd class="col-sm-8">
                            ${escapeHtml(item.tanggal_verifikasi)}
                            ${escapeHtml(item.waktu_verifikasi)}
                        </dd>
                    ` : ''}
                    ${item.alasan_penolakan ? `
                        <dt class="col-sm-4">Alasan</dt>
                        <dd class="col-sm-8 text-danger">
                            ${escapeHtml(item.alasan_penolakan)}
                        </dd>
                    ` : ''}
                </dl>
            `);

            detailModal.show();
        }).fail(showFailure);
    });

    $('#rows').on('click', '.verify', function () {
        $('#verifySubmissionId').val($(this).data('id'));
        $('#verifyPaymentMethod').val('TRANSFER');
        $('#verifyBankAccount').prop('selectedIndex', 0);
        $('#verifyCashAccount').prop('selectedIndex', 0);
        togglePaymentFields('TRANSFER', '#verifyTransferGroup', '#verifyCashGroup');
        verifyModal.show();
    });

    $('#verifyPaymentMethod').on('change', function () {
        togglePaymentFields(this.value, '#verifyTransferGroup', '#verifyCashGroup');
    });

    $('#confirmVerification').on('click', function () {
        const button = this;
        const payment = paymentPayload(
            '#verifyPaymentMethod',
            '#verifyBankAccount',
            '#verifyCashAccount'
        );

        if (!validatePayment(payment)) {
            return;
        }

        button.disabled = true;

        request('verifikasi', {
            id: $('#verifySubmissionId').val(),
            ...payment,
            web_csrf_token: csrfToken,
        }).done(function (response) {
            verifyModal.hide();
            Swal.fire('Berhasil', response.message, 'success');
            load(true);
        }).fail(showFailure).always(function () {
            button.disabled = false;
        });
    });

    $('#rows').on('click', '.reject', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Alasan penolakan',
            input: 'text',
            showCancelButton: true,
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            request('tolak', {
                id: id,
                alasan: result.value,
                web_csrf_token: csrfToken,
            }).done(function (response) {
                Swal.fire('Berhasil', response.message, 'success');
                load(true);
            }).fail(showFailure);
        });
    });

    $('#rows').on('click', '.reverse', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Alasan reversal',
            input: 'text',
            text: 'Pokok dan fee akan direfund; saldo tidak boleh negatif.',
            showCancelButton: true,
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            request('reversal', {
                id: id,
                alasan: result.value,
                web_csrf_token: csrfToken,
            }).done(function (response) {
                Swal.fire('Berhasil', response.message, 'success');
                load(true);
            }).fail(showFailure);
        });
    });

    <?php if (!empty($can_direct_topup)): ?>
        const directModal = new bootstrap.Modal(document.getElementById('directModal'));
        let requestKey = '';

        function createRequestKey() {
            requestKey = window.crypto && crypto.randomUUID
                ? crypto.randomUUID()
                : Date.now()
                    + '-'
                    + Math.random().toString(16).slice(2)
                    + '-'
                    + Math.random().toString(16).slice(2);
        }

        function findStudents() {
            request('cari_siswa', {
                search: $('#studentSearch').val(),
            }).done(function (response) {
                let options = '<option value="">Pilih siswa</option>';

                (response.data || []).forEach(function (student) {
                    options += `
                        <option value="${student.id}" data-saldo="${student.saldo}">
                            ${escapeHtml(student.nis)} - ${escapeHtml(student.nama_lengkap)}
                        </option>
                    `;
                });

                $('#directStudent').html(options);
            }).fail(showFailure);
        }

        $('#openDirect').on('click', function () {
            createRequestKey();
            $('#studentSearch').val('');
            $('#directAmount').val('');
            $('#directStudent').html('<option value="">Cari dan pilih siswa</option>');
            $('#currentBalance').text('');
            $('#directPrincipal').text('Rp 0');
            $('#directTotal').text('Rp ' + formatMoney(serviceFee));
            $('#directPaymentMethod').val('TRANSFER');
            $('#directBankAccount').prop('selectedIndex', 0);
            $('#directCashAccount').prop('selectedIndex', 0);
            togglePaymentFields('TRANSFER', '#directTransferGroup', '#directCashGroup');
            directModal.show();
        });

        $('#directPaymentMethod').on('change', function () {
            togglePaymentFields(this.value, '#directTransferGroup', '#directCashGroup');
        });

        $('#searchStudent').on('click', findStudents);

        $('#studentSearch').on('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                findStudents();
            }
        });

        $('#directStudent').on('change', function () {
            const balance = $(this).find(':selected').data('saldo');
            $('#currentBalance').text(
                this.value ? 'Saldo saat ini: Rp ' + formatMoney(balance) : ''
            );
        });

        $('#directAmount').on('input', function () {
            const amount = window.SDKreatifMoney
                ? SDKreatifMoney.parse(this.value)
                : Number(this.value.replace(/\D/g, ''));

            $('#directPrincipal').text('Rp ' + formatMoney(amount));
            $('#directTotal').text('Rp ' + formatMoney(amount + serviceFee));
        });

        $('#saveDirect').on('click', function () {
            const button = this;
            const studentId = $('#directStudent').val();
            const amount = $('#directAmount').val();
            const payment = paymentPayload(
                '#directPaymentMethod',
                '#directBankAccount',
                '#directCashAccount'
            );

            if (!studentId || !amount) {
                Swal.fire('Belum lengkap', 'Pilih siswa dan isi nominal.', 'warning');
                return;
            }

            if (!validatePayment(payment)) {
                return;
            }

            button.disabled = true;

            request('tambah_langsung', {
                id_siswa: studentId,
                nominal: amount,
                request_key: requestKey,
                ...payment,
                web_csrf_token: csrfToken,
            }).done(function (response) {
                directModal.hide();
                Swal.fire(
                    'Berhasil',
                    response.message
                        + ' Saldo sekarang Rp '
                        + formatMoney(response.data.saldo_sesudah)
                        + '.',
                    'success'
                );
                $('#jenis').val('RIWAYAT');
                page = 1;
                load(true);
            }).fail(showFailure).always(function () {
                button.disabled = false;
            });
        });
    <?php endif; ?>

    load(true);
});
</script>
