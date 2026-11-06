<?php
defined('BASEPATH') or exit('No direct script access allowed');
$r = $ringkasan;
$status_badge = static function ($status) {
    $status = strtoupper(trim((string) $status));
    $colors = [
        'BERHASIL' => 'bg-success',
        'DIVERIFIKASI' => 'bg-success',
        'MENUNGGU_VERIFIKASI' => 'bg-warning text-dark',
        'MENUNGGU' => 'bg-warning text-dark',
        'DITOLAK' => 'bg-danger',
        'GAGAL' => 'bg-danger',
        'DIBATALKAN_ADMIN' => 'bg-danger',
        'DIBATALKAN_WALI' => 'bg-secondary',
        'DIBATALKAN' => 'bg-secondary',
    ];
    return $colors[$status] ?? 'bg-info text-dark';
};
$status_label = static function ($status) {
    return ucwords(strtolower(str_replace('_', ' ', (string) $status)));
};
$date_time = static function ($date, $time) {
    $timestamp = strtotime(trim((string) $date . ' ' . (string) $time));
    return $timestamp ? date('d-m-Y H:i', $timestamp) : trim((string) $date . ' ' . (string) $time);
};
?>

<style>
.dashboard-dompet .summary-card { height: 100%; }
.dashboard-dompet .summary-card .card-body { padding: 1.5rem 1.75rem; }
.dashboard-dompet .latest-card { height: 100%; overflow: hidden; }
.dashboard-dompet .latest-card .card-header { padding: 1.25rem 1.5rem; background: #fff; }
.dashboard-dompet .latest-list { padding: 1rem; }
.dashboard-dompet .student-name { color: #263b5e; font-weight: 600; line-height: 1.35; }
.dashboard-dompet .transaction-time { display: block; margin-top: .35rem; color: #8a94a6; font-size: .76rem; white-space: nowrap; }
.dashboard-dompet .status-badge { display: inline-block; padding: .42rem .62rem; border-radius: .4rem; font-size: .7rem; font-weight: 700; letter-spacing: .025em; white-space: nowrap; }
.dashboard-dompet .pending-alert { min-height: 72px; margin: 1.5rem 0; padding: 1rem 1.5rem; border-radius: .5rem; }
@media (max-width: 767.98px) {
    .dashboard-dompet .pending-alert { align-items: flex-start !important; flex-direction: column; gap: .75rem; }
    .dashboard-dompet .pending-alert .btn { width: 100%; }
}
</style>

<div class="dashboard-dompet">
    <div class="row g-3">
        <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><small class="text-muted">Total Saldo Siswa</small><h3 class="my-2">Rp <?= number_format((int) $r['wallet']['total_saldo'], 0, ',', '.') ?></h3><span class="badge bg-success"><?= (int) $r['wallet']['wallet_aktif'] ?> wallet aktif</span></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><small class="text-muted">Siswa Belum Aktivasi</small><h3 class="my-2"><?= (int) $r['siswa']['belum_wallet'] ?></h3><span class="text-muted">dari <?= (int) $r['siswa']['total_siswa'] ?> siswa aktif</span></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><small class="text-muted">Top-up Hari Ini</small><h3 class="my-2">Rp <?= number_format((int) $r['topup_hari_ini']['nominal'], 0, ',', '.') ?></h3><span class="text-muted"><?= (int) $r['topup_hari_ini']['jumlah'] ?> transaksi</span></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><small class="text-muted">Kantin Hari Ini</small><h3 class="my-2">Rp <?= number_format((int) $r['kantin_hari_ini']['nominal'], 0, ',', '.') ?></h3><span class="text-muted"><?= (int) $r['kantin_hari_ini']['jumlah'] ?> transaksi</span></div></div></div>
    </div>

    <div class="alert <?= (int) $r['pengajuan_menunggu'] > 0 ? 'alert-warning' : 'alert-success' ?> pending-alert d-flex align-items-center justify-content-between" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="<?= (int) $r['pengajuan_menunggu'] > 0 ? 'ri-time-line' : 'ri-checkbox-circle-line' ?> fs-5"></i>
            <span><strong><?= (int) $r['pengajuan_menunggu'] ?></strong> pengajuan isi saldo menunggu verifikasi.</span>
        </div>
        <?php if ((int) $r['pengajuan_menunggu'] > 0): ?>
            <a class="btn btn-primary btn-sm" href="<?= base_url('admin/dompet/isi_saldo') ?>?status=MENUNGGU_VERIFIKASI"><i class="ri-eye-line me-1"></i> Cek Pengajuan</a>
        <?php endif; ?>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-xl-6">
            <div class="card latest-card">
                <div class="card-header border-bottom"><h4 class="header-title mb-1">Transaksi Kantin Terbaru</h4><small class="text-muted">Aktivitas transaksi siswa yang terakhir tercatat.</small></div>
                <div class="sdk-record-list latest-list">
                    <?php if (empty($r['transaksi_terbaru'])): ?><div class="sdk-record-empty">Belum ada transaksi kantin.</div><?php endif; ?>
                    <?php foreach ($r['transaksi_terbaru'] as $index => $row): ?>
                        <div class="sdk-record-card">
                            <div class="sdk-record-head"><span>Transaksi kantin</span><span class="badge status-badge <?= $status_badge($row['status_transaksi']) ?>"><?= html_escape($status_label($row['status_transaksi'])) ?></span></div>
                            <div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title"><?= (int) $index + 1 ?>. <?= html_escape($row['nama_siswa']) ?></h5><div class="sdk-record-meta"><span><i class="ri-calendar-line me-1"></i><?= html_escape($date_time($row['tanggal'], $row['waktu'])) ?></span><span>Nominal: <strong>Rp <?= number_format((int) $row['nominal'], 0, ',', '.') ?></strong></span></div></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card latest-card">
                <div class="card-header border-bottom"><h4 class="header-title mb-1">Pengajuan Isi Saldo Terbaru</h4><small class="text-muted">Pengajuan terkini beserta status prosesnya.</small></div>
                <div class="sdk-record-list latest-list">
                    <?php if (empty($r['pengajuan_terbaru'])): ?><div class="sdk-record-empty">Belum ada pengajuan isi saldo.</div><?php endif; ?>
                    <?php foreach ($r['pengajuan_terbaru'] as $index => $row): ?>
                        <div class="sdk-record-card">
                            <div class="sdk-record-head"><span>Pengajuan isi saldo</span><span class="badge status-badge <?= $status_badge($row['status_pengajuan']) ?>"><?= html_escape($status_label($row['status_pengajuan'])) ?></span></div>
                            <div class="sdk-record-main"><div class="flex-grow-1"><h5 class="sdk-record-title"><?= (int) $index + 1 ?>. <?= html_escape($row['nama_siswa']) ?></h5><div class="sdk-record-meta"><span><i class="ri-calendar-line me-1"></i><?= html_escape($date_time($row['tanggal_pengajuan'], $row['waktu_pengajuan'])) ?></span><span>Pokok: <strong>Rp <?= number_format((int) $row['nominal_saldo'], 0, ',', '.') ?></strong></span><?php if ((int) $row['biaya_layanan'] > 0): ?><span>Fee: <strong>Rp <?= number_format((int) $row['biaya_layanan'], 0, ',', '.') ?></strong></span><?php endif; ?></div></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
