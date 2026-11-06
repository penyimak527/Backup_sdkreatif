<?php
defined('BASEPATH') or exit('No direct script access allowed');
$money = static function ($value) { return 'Rp '.number_format((int) $value, 0, ',', '.'); };
$badge = static function ($status) {
    $status = strtoupper((string) $status);
    if ($status === 'BERHASIL') return 'bg-success';
    if (strpos($status, 'BATAL') !== false) return 'bg-danger';
    if (strpos($status, 'MENUNGGU') !== false) return 'bg-warning text-dark';
    return 'bg-secondary';
};
?>

<style>
.wali-mobile-list { display: grid; gap: .75rem; }
.wali-mobile-record { padding: 1rem; background: var(--ct-secondary-bg); border: 1px solid var(--ct-border-color); border-radius: .75rem; }
.wali-mobile-record__head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: .85rem; }
.wali-mobile-record__head > div { min-width: 0; }
.wali-mobile-record__head .badge { white-space: nowrap; }
.wali-mobile-record__title { min-width: 0; margin: 0; color: var(--ct-body-color); font-size: 1rem; font-weight: 700; overflow-wrap: anywhere; }
.wali-mobile-record__meta { margin-top: .2rem; color: var(--ct-secondary-color); font-size: .78rem; overflow-wrap: anywhere; }
.wali-mobile-record__details { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .75rem; padding-top: .85rem; border-top: 1px solid var(--ct-border-color); }
.wali-mobile-record__label { display: block; margin-bottom: .15rem; color: var(--ct-secondary-color); font-size: .72rem; }
.wali-mobile-record__value { color: var(--ct-body-color); font-size: .88rem; font-weight: 700; overflow-wrap: anywhere; }
</style>

<?php $this->load->view('wali/dompet/_pilih_anak', array('anak_list' => $anak_list, 'anak' => $anak)); ?>

<?php if (!$filter['valid']): ?><div class="alert alert-danger"><?= html_escape($filter['pesan']) ?></div><?php endif; ?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <?php if ($anak): ?><input type="hidden" name="id_siswa" value="<?= (int) $anak['id_siswa'] ?>"><?php endif; ?>
            <div class="col-md-4"><label class="form-label fw-semibold">Tanggal awal</label><input type="date" class="form-control" name="tanggal_awal" value="<?= html_escape($filter['tanggal_awal'] ?: '') ?>"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Tanggal akhir</label><input type="date" class="form-control" name="tanggal_akhir" value="<?= html_escape($filter['tanggal_akhir'] ?: '') ?>"></div>
            <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit"><i class="ri-filter-3-line me-1"></i> Terapkan</button><a class="btn btn-light" href="<?= base_url('wali/dompet/history-belanja'.($anak ? '?id_siswa='.(int) $anak['id_siswa'] : '')) ?>">Reset</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom"><h4 class="header-title mb-1">History Belanja Siswa</h4><small class="text-muted"><?= $anak ? html_escape($anak['nama_siswa'].' · '.$anak['nis']) : 'Belum ada anak terhubung' ?></small></div>
    <div class="card-body p-0 d-none d-md-block">
        <div class="table-responsive">
            <table class="table sdk-data-table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Keterangan</th><th>Nominal</th><th>Saldo</th><th>Status</th></tr></thead>
                <tbody id="history-belanja-tbody">
                <?php if (!$riwayat || empty($riwayat['rows'])): ?><tr><td colspan="6" class="sdk-empty">Belum ada transaksi belanja.</td></tr><?php endif; ?>
                <?php if ($riwayat): foreach ($riwayat['rows'] as $row): ?>
                    <tr>
                        <td><?= html_escape($row['tanggal']) ?><span class="sdk-secondary"><?= html_escape($row['waktu']) ?></span></td>
                        <td><span class="sdk-primary"><?= html_escape($row['no_transaksi']) ?></span></td>
                        <td><?= html_escape($row['keterangan'] ?: 'Transaksi kantin') ?></td>
                        <td class="fw-bold text-danger">-<?= $money($row['nominal']) ?></td>
                        <td><?= $row['saldo_sesudah'] !== null ? $money($row['saldo_sesudah']) : '-' ?><span class="sdk-secondary">Sebelum: <?= $row['saldo_sebelum'] !== null ? $money($row['saldo_sebelum']) : '-' ?></span></td>
                        <td><span class="badge <?= $badge($row['status_transaksi']) ?>"><?= html_escape(ucwords(strtolower(str_replace('_',' ',$row['status_transaksi'])))) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-body d-md-none p-3" id="history-belanja-mobile">
        <?php if (!$riwayat || empty($riwayat['rows'])): ?>
            <div class="sdk-record-empty">Belum ada transaksi belanja.</div>
        <?php else: ?>
            <div class="wali-mobile-list">
                <?php foreach ($riwayat['rows'] as $row): ?>
                    <article class="wali-mobile-record">
                        <div class="wali-mobile-record__head">
                            <div class="min-w-0">
                                <h5 class="wali-mobile-record__title"><?= html_escape($row['keterangan'] ?: 'Transaksi kantin') ?></h5>
                                <div class="wali-mobile-record__meta"><?= html_escape($row['tanggal'].' · '.$row['waktu']) ?></div>
                            </div>
                            <span class="badge <?= $badge($row['status_transaksi']) ?>"><?= html_escape(ucwords(strtolower(str_replace('_', ' ', $row['status_transaksi'])))) ?></span>
                        </div>
                        <div class="wali-mobile-record__meta mb-3">No. transaksi: <?= html_escape($row['no_transaksi']) ?></div>
                        <div class="wali-mobile-record__details">
                            <div><span class="wali-mobile-record__label">Nominal</span><span class="wali-mobile-record__value text-danger">-<?= $money($row['nominal']) ?></span></div>
                            <div><span class="wali-mobile-record__label">Saldo sesudah</span><span class="wali-mobile-record__value"><?= $row['saldo_sesudah'] !== null ? $money($row['saldo_sesudah']) : '-' ?></span><div class="wali-mobile-record__meta">Sebelum <?= $row['saldo_sebelum'] !== null ? $money($row['saldo_sebelum']) : '-' ?></div></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="history-belanja-pager" class="sdk-pager-wrap <?= !$riwayat || empty($riwayat['total']) ? 'd-none' : '' ?>">
    <div>
        <small class="text-muted" id="history-belanja-info"><?= $riwayat ? (int) $riwayat['total'] : 0 ?> transaksi</small>
        <div class="mt-2" id="history-belanja-pages"></div>
    </div>
    <div class="sdk-page-size">
        <label for="history-belanja-limit" class="mb-0">Tampilkan</label>
        <select id="history-belanja-limit" class="form-select form-select-sm">
            <?php foreach (array(10, 25, 50, 100) as $jumlah): ?>
                <option value="<?= $jumlah ?>" <?= $riwayat && (int) $riwayat['per_halaman'] === $jumlah ? 'selected' : '' ?>><?= $jumlah ?></option>
            <?php endforeach; ?>
        </select>
        <span>entri</span>
    </div>
</div>

<script>
$(function () {
    var idSiswa = <?= $anak ? (int) $anak['id_siswa'] : 0 ?>;
    var total = <?= $riwayat ? (int) $riwayat['total'] : 0 ?>;
    var halaman = <?= $riwayat ? (int) $riwayat['halaman'] : 1 ?>;
    var perHalaman = <?= $riwayat ? (int) $riwayat['per_halaman'] : 10 ?>;
    var requestBelanja = null;

    function escapeBelanja(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function uangBelanja(value) {
        var nominal = parseInt(value, 10);
        return 'Rp ' + (isNaN(nominal) ? 0 : nominal).toLocaleString('id-ID');
    }

    function badgeBelanja(status) {
        status = String(status || '').toUpperCase();
        if (status === 'BERHASIL') return 'bg-success';
        if (status.indexOf('BATAL') !== -1) return 'bg-danger';
        if (status.indexOf('MENUNGGU') !== -1) return 'bg-warning text-dark';
        return 'bg-secondary';
    }

    function labelBelanja(status) {
        return escapeBelanja(String(status || '').replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, function (c) { return c.toUpperCase(); }));
    }

    function renderBelanja(rows) {
        if (!rows.length) {
            $('#history-belanja-tbody').html('<tr><td colspan="6" class="sdk-empty">Belum ada transaksi belanja.</td></tr>');
            $('#history-belanja-mobile').html('<div class="sdk-record-empty">Belum ada transaksi belanja.</div>');
            return;
        }

        var desktop = '';
        var mobile = '<div class="wali-mobile-list">';
        rows.forEach(function (item) {
            var tanggal = escapeBelanja(item.tanggal);
            var waktu = escapeBelanja(item.waktu);
            var nomor = escapeBelanja(item.no_transaksi);
            var keterangan = escapeBelanja(item.keterangan || 'Transaksi kantin');
            var nominal = uangBelanja(item.nominal);
            var saldoSesudah = item.saldo_sesudah != null ? uangBelanja(item.saldo_sesudah) : '-';
            var saldoSebelum = item.saldo_sebelum != null ? uangBelanja(item.saldo_sebelum) : '-';
            var status = '<span class="badge ' + badgeBelanja(item.status_transaksi) + '">' + labelBelanja(item.status_transaksi) + '</span>';

            desktop += '<tr>' +
                '<td>' + tanggal + '<span class="sdk-secondary">' + waktu + '</span></td>' +
                '<td><span class="sdk-primary">' + nomor + '</span></td>' +
                '<td>' + keterangan + '</td>' +
                '<td class="fw-bold text-danger">-' + nominal + '</td>' +
                '<td>' + saldoSesudah + '<span class="sdk-secondary">Sebelum: ' + saldoSebelum + '</span></td>' +
                '<td>' + status + '</td></tr>';

            mobile += '<article class="wali-mobile-record">' +
                '<div class="wali-mobile-record__head"><div class="min-w-0">' +
                '<h5 class="wali-mobile-record__title">' + keterangan + '</h5>' +
                '<div class="wali-mobile-record__meta">' + tanggal + ' · ' + waktu + '</div>' +
                '</div>' + status + '</div>' +
                '<div class="wali-mobile-record__meta mb-3">No. transaksi: ' + nomor + '</div>' +
                '<div class="wali-mobile-record__details">' +
                '<div><span class="wali-mobile-record__label">Nominal</span><span class="wali-mobile-record__value text-danger">-' + nominal + '</span></div>' +
                '<div><span class="wali-mobile-record__label">Saldo sesudah</span><span class="wali-mobile-record__value">' + saldoSesudah + '</span>' +
                '<div class="wali-mobile-record__meta">Sebelum ' + saldoSebelum + '</div></div>' +
                '</div></article>';
        });
        $('#history-belanja-tbody').html(desktop);
        $('#history-belanja-mobile').html(mobile + '</div>');
    }

    function tampilkanPager() {
        if (total < 1) {
            $('#history-belanja-info, #history-belanja-pages').empty();
            $('#history-belanja-pager').addClass('d-none');
            return;
        }
        $('#history-belanja-info').text(total + ' transaksi');
        $('#history-belanja-pager').removeClass('d-none');
        SDKreatifUI.serverPager('#history-belanja-pages', {
            total: total,
            page: halaman,
            limit: perHalaman
        }, function (paging) {
            muatBelanja(paging.currentPage);
        });
    }

    function muatBelanja(page) {
        if (!idSiswa) return;
        if (requestBelanja && requestBelanja.readyState !== 4) requestBelanja.abort();

        $('#history-belanja-tbody, #history-belanja-mobile').attr('aria-busy', 'true');
        $('#history-belanja-limit').prop('disabled', true);
        requestBelanja = $.ajax({
            url: <?= json_encode(base_url('wali/dompet/history-belanja')) ?>,
            type: 'GET',
            dataType: 'json',
            data: {
                id_siswa: idSiswa,
                tanggal_awal: $('input[name="tanggal_awal"]').val(),
                tanggal_akhir: $('input[name="tanggal_akhir"]').val(),
                halaman: page,
                limit: $('#history-belanja-limit').val()
            },
            success: function (response) {
                if (!response || !response.success || !response.data) {
                    $('#history-belanja-info').text('Gagal memuat riwayat belanja.');
                    return;
                }
                var data = response.data;
                total = Number(data.total || 0);
                halaman = Number(data.page || 1);
                perHalaman = Number(data.limit || 10);
                renderBelanja(data.rows || []);
                tampilkanPager();
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    $('#history-belanja-info').text('Gagal memuat riwayat belanja.');
                }
            },
            complete: function (xhr) {
                if (requestBelanja === xhr) {
                    requestBelanja = null;
                    $('#history-belanja-tbody, #history-belanja-mobile').removeAttr('aria-busy');
                    $('#history-belanja-limit').prop('disabled', false);
                }
            }
        });
    }

    $('#history-belanja-limit').on('change', function () {
        muatBelanja(1);
    });
    if (idSiswa) tampilkanPager();
});
</script>
