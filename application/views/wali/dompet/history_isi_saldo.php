<?php
defined('BASEPATH') or exit('No direct script access allowed');
$money = static function ($value) { return 'Rp '.number_format((int) $value, 0, ',', '.'); };
$badge = static function ($status) {
    $status = strtoupper((string) $status);
    if (in_array($status, array('BERHASIL','DIVERIFIKASI','DISETUJUI'), true)) return 'bg-success';
    if (strpos($status, 'BATAL') !== false || $status === 'DITOLAK') return 'bg-danger';
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
.wali-mobile-record__details--full { grid-column: 1 / -1; }
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
            <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit"><i class="ri-filter-3-line me-1"></i> Terapkan</button><a class="btn btn-light" href="<?= base_url('wali/dompet/history-isi-saldo'.($anak ? '?id_siswa='.(int) $anak['id_siswa'] : '')) ?>">Reset</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom"><h4 class="header-title mb-1">History Isi Saldo</h4><small class="text-muted">Pengajuan dan status isi saldo untuk anak yang dipilih.</small></div>
    <div class="card-body p-0 d-none d-md-block">
        <div class="table-responsive">
            <table class="table sdk-data-table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>No. Pengajuan</th><th>Nominal</th><th>Biaya &amp; Total</th><th>Rekening</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$riwayat || empty($riwayat['rows'])): ?><tr><td colspan="6" class="sdk-empty">Belum ada pengajuan isi saldo.</td></tr><?php endif; ?>
                <?php if ($riwayat): foreach ($riwayat['rows'] as $row): ?>
                    <tr>
                        <td><?= html_escape($row['tanggal_pengajuan']) ?><span class="sdk-secondary"><?= html_escape($row['waktu_pengajuan']) ?></span></td>
                        <td><span class="sdk-primary"><?= html_escape($row['no_pengajuan']) ?></span><span class="sdk-secondary"><?= html_escape($row['metode_pembayaran'] ?: 'TRANSFER') ?></span></td>
                        <td class="fw-semibold"><?= $money($row['nominal_saldo']) ?></td>
                        <td><?= $money($row['total_transfer']) ?><span class="sdk-secondary">Biaya: <?= $money($row['biaya_layanan']) ?></span></td>
                        <td><?= html_escape($row['nama_bank'] ?: $row['nama_kasbank'] ?: '-') ?><span class="sdk-secondary"><?= html_escape($row['nomor_rekening'] ?: '-') ?></span></td>
                        <td><span class="badge <?= $badge($row['status_pengajuan']) ?>"><?= html_escape(ucwords(strtolower(str_replace('_',' ',$row['status_pengajuan'])))) ?></span><?php if ($row['alasan_penolakan']): ?><span class="sdk-secondary text-danger"><?= html_escape($row['alasan_penolakan']) ?></span><?php endif; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-body d-md-none p-3">
        <?php if (!$riwayat || empty($riwayat['rows'])): ?>
            <div class="sdk-record-empty">Belum ada pengajuan isi saldo.</div>
        <?php else: ?>
            <div class="wali-mobile-list">
                <?php foreach ($riwayat['rows'] as $row): ?>
                    <article class="wali-mobile-record">
                        <div class="wali-mobile-record__head">
                            <div class="min-w-0">
                                <h5 class="wali-mobile-record__title"><?= html_escape($row['no_pengajuan']) ?></h5>
                                <div class="wali-mobile-record__meta"><?= html_escape($row['tanggal_pengajuan'].' · '.$row['waktu_pengajuan']) ?></div>
                            </div>
                            <span class="badge <?= $badge($row['status_pengajuan']) ?>"><?= html_escape(ucwords(strtolower(str_replace('_', ' ', $row['status_pengajuan'])))) ?></span>
                        </div>
                        <div class="wali-mobile-record__meta mb-3">Metode: <?= html_escape($row['metode_pembayaran'] ?: 'TRANSFER') ?></div>
                        <div class="wali-mobile-record__details">
                            <div><span class="wali-mobile-record__label">Nominal saldo</span><span class="wali-mobile-record__value"><?= $money($row['nominal_saldo']) ?></span></div>
                            <div><span class="wali-mobile-record__label">Total transfer</span><span class="wali-mobile-record__value text-primary"><?= $money($row['total_transfer']) ?></span><div class="wali-mobile-record__meta">Biaya <?= $money($row['biaya_layanan']) ?></div></div>
                            <div class="wali-mobile-record__details--full"><span class="wali-mobile-record__label">Rekening tujuan</span><span class="wali-mobile-record__value"><?= html_escape($row['nama_bank'] ?: $row['nama_kasbank'] ?: '-') ?></span><div class="wali-mobile-record__meta"><?= html_escape($row['nomor_rekening'] ?: '-') ?></div></div>
                            <?php if ($row['alasan_penolakan']): ?><div class="wali-mobile-record__details--full"><span class="wali-mobile-record__label text-danger">Alasan</span><span class="wali-mobile-record__value text-danger"><?= html_escape($row['alasan_penolakan']) ?></span></div><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($riwayat && $riwayat['total_halaman'] > 1): $query = array('id_siswa' => (int) $anak['id_siswa'], 'tanggal_awal' => $filter['tanggal_awal'], 'tanggal_akhir' => $filter['tanggal_akhir']); ?>
<nav><ul class="pagination justify-content-end">
    <?php for ($i=1; $i <= $riwayat['total_halaman']; $i++): $query['halaman'] = $i; ?><li class="page-item <?= $i === $riwayat['halaman'] ? 'active' : '' ?>"><a class="page-link" href="<?= base_url('wali/dompet/history-isi-saldo').'?'.http_build_query(array_filter($query, static function ($value) { return $value !== null && $value !== ''; })) ?>"><?= $i ?></a></li><?php endfor; ?>
</ul></nav>
<?php endif; ?>
