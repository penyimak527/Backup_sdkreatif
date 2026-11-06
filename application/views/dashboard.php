<?php
 if ((!(is_array($this->session->userdata('admin')) && in_array('bendahara', array_map('strtolower', array_map('trim', (!empty($this->session->userdata('admin')['levels']) && is_array($this->session->userdata('admin')['levels']) ? $this->session->userdata('admin')['levels'] : explode(',', (string) ($this->session->userdata('admin')['level'] ?? ''))))), true)) || count(array_unique(array_map('intval', array_filter((!empty($this->session->userdata('admin')['id_levels']) && is_array($this->session->userdata('admin')['id_levels']) ? $this->session->userdata('admin')['id_levels'] : explode(',', (string) ($this->session->userdata('admin')['id_level'] ?? ''))), static function ($value) { $value = trim((string) $value); return $value !== '' && ctype_digit($value) && (int) $value > 0; })))) > 1)): ?>
<div class="row row-cols-1 row-cols-md-3 g-4">
	<div class="col">
		<div class="card">
			<div class="d-flex card-header justify-content-between align-items-center">
				<div>
					<h4 class="header-title"> Mapel</h4>
				</div>

			</div>

			<div class="card-body pt-0">
				<div class="d-flex align-items-end gap-2 justify-content-between">
					<div class="text-end flex-shrink-0">
						<div id="chart-one" data-colors="#ff5b5b,#F6F7FB"></div>
					</div>
					<div class="text-end">
						<h3 class="fw-semibold"><?= $total_mapel ?></h3>
						<p class="text-muted mb-0">Total Mapel</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="d-flex card-header justify-content-between align-items-center">
				<div>
					<h4 class="header-title">Kelas</h4>
				</div>

			</div>

			<div class="card-body pt-0">
				<div class="d-flex align-items-end gap-2 justify-content-between">
					<div class="text-end flex-shrink-0">
						<div id="chart-two" data-colors="#f9c851,#F6F7FB"></div>
					</div>
					<div class="text-end">
						<h3 class="fw-semibold"><?= $total_kelas ?></h3>
						<p class="text-muted mb-0">Total Kelas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="d-flex card-header justify-content-between align-items-center">
				<div>
					<h4 class="header-title">Pegawai</h4>
				</div>

			</div>

			<div class="card-body pt-0">
				<div class="d-flex align-items-end gap-2 justify-content-between">
					<div class="text-end flex-shrink-0">
						<div id="chart-three" data-colors="#ff5b5b,#F6F7FB"></div>
					</div>
					<div class="text-end">
						<h3 class="fw-semibold"><?= $total_pegawai ?></h3>
						<p class="text-muted mb-0">Total Pegawai</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif;?>
<?php if ((is_array($this->session->userdata('admin')) && in_array('guru', array_map('strtolower', array_map('trim', (!empty($this->session->userdata('admin')['levels']) && is_array($this->session->userdata('admin')['levels']) ? $this->session->userdata('admin')['levels'] : explode(',', (string) ($this->session->userdata('admin')['level'] ?? ''))))), true))): ?>
	<div class="card">
		<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
			<h4 class="header-title">Jadwal Mengajar Hari Ini</h4>
		</div>
		<div class="card-body">

			<div id="card-container"></div>
		</div>
	</div>
<?php endif; ?>
<?php if((is_array($this->session->userdata('admin')) && in_array('bendahara', array_map('strtolower', array_map('trim', (!empty($this->session->userdata('admin')['levels']) && is_array($this->session->userdata('admin')['levels']) ? $this->session->userdata('admin')['levels'] : explode(',', (string) ($this->session->userdata('admin')['level'] ?? ''))))), true))):?>
<div class="card">
	<div class="card-header border-bottom border-dashed">
		<div class="row">
			<div class="col-md-4"><h4 class="header-title">Saldo</h4></div>
		<div class="col-md-3">
				<div class="input-group flex-nowrap">
					<span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
					<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Dari"
						aria-describedby="inputGroupPrepend" name="tanggal_awal_saldo">
				</div>
			</div>
<div class="col-md-3">
				<div class="input-group flex-nowrap">
					<span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
					<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Sampai"
						aria-describedby="inputGroupPrepend" name="tanggal_akhir_saldo">
				</div>
			</div>
				<div class="col-md-2">
					<button class="btn btn-primary w-100" onclick="saldo()">
						Preview
					</button>
				</div>
		</div>
	</div>
	<div class="card-body">
		<div id="card-container-saldo"></div>
	</div>
</div>
<div class="col-md-12">
	<div class="card">
		<div class="card-header border-bottom">
			<div class="row">
				<div class="col-md-4">
					<h5>Grafik Pemasukan vs Pengeluaran</h5>
				</div>

				<div class="col-md-4">
					<select id="tahun_perbandingan" class="form-control">
						<?php
						$now = date('Y');
						for ($i = 2025; $i <= $now; $i++) {
							$selected = ($i == $now) ? 'selected' : '';
							echo "<option value='$i' $selected>$i</option>";
						}
						?>
					</select>
				</div>

				<div class="col-md-4">
					<button class="btn btn-primary w-100" onclick="grafikPerbandingan()">
						Preview
					</button>
				</div>
			</div>
		</div>

		<div class="card-body">
			<div id="chart-perbandingan"></div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="card">
			<div class="card-header border-bottom border-dashed ">
				<div class="row">
					<div class="col-md-4">
						<h5 class="header-title">Top Pengeluaran Terbesar</h5>
					</div>
					<div class="col-md-4">
						<select id="bulan_top" class="form-control">
							<option value="semua">Semua</option>
							<?php
							$bulan_now = date('m');
							$bulan = array(
								"Januari",
								"Februari",
								"Maret",
								"April",
								"Mei",
								"Juni",
								"Juli",
								"Agustus",
								"September",
								"Oktober",
								"November",
								"Desember"
							);
							$jlh_bln = count($bulan);
							$no = 0;
							for ($c = 0; $c < $jlh_bln; $c += 1) {
								$no++;
								$no_pas = sprintf("%02s", $no);
								$selected = ($no_pas == $bulan_now) ? 'selected' : '';
								echo '<option value="' . $no_pas . '" ' . $selected . '> ' . $bulan[$c] . ' </option>';
							} ?>
						</select>
					</div>
					<div class="col-md-4">
						<select class="form-control" id="tahun_top">
							<?php
							$now = date('Y');
							for ($a = 2025; $a <= $now; $a++) {
								$selected = ($a == $now) ? 'selected' : '';
								echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div id="chart-top"></div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="row">
			<!-- <div class="col-md-4"> -->
			<div class="card shadow-sm h-100">
				<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
					<h5 class="mb-0 header-title">Notifikasi Keuangan</h5>
					<span class="badge bg-danger" id="jumlah-notif">0</span>
				</div>
				<div class="card-body p-2" style="max-height:300px;overflow-y:auto;">
					<div id="notif-keuangan">
						<div class="text-center text-muted p-3">
							Memuat data...
						</div>
					</div>
				</div>
			</div>
			<!-- </div> -->
		</div>
	</div>
</div>


<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Perbandingan Rencana Pemasukan</h4>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-4">
				<select class="form-control" id="tahun_rencana_pemasukan">
					<?php
					$now = date('Y');
					for ($a = 2024; $a <= $now; $a++) {
						$selected = ($a == $now) ? 'selected' : '';
						echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
					}
					?>
				</select>
			</div>
			<div class="col-md-4">
				<button class="btn btn-primary w-100" onclick="grafikRencanaPemasukan()">
					Preview
				</button>
			</div>
		</div>
		<div id="chart-rencana-pemasukan"></div>
	</div>
</div>
<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Perbandingan Rencana Pengeluaran</h4>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-4">
				<select class="form-control" id="tahun_rencana_pengeluaran">
					<?php
					$now = date('Y');
					for ($a = 2024; $a <= $now; $a++) {
						$selected = ($a == $now) ? 'selected' : '';
						echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
					}
					?>
				</select>
			</div>
			<div class="col-md-4">
				<button class="btn btn-primary w-100" onclick="grafikRencanaPengeluaran()">
					Preview
				</button>
			</div>
		</div>
		<div id="chart-rencana-pengeluaran"></div>
	</div>
</div>
<?php endif;?>
<?php
// Dashboard SPP dipisahkan dari seluruh card Dashboard lama.
$spp_access = isset($spp_dashboard_akses) && is_array($spp_dashboard_akses) ? $spp_dashboard_akses : array('tagihan'=>false,'tabungan'=>false);
$spp_tagihan = !empty($spp_access['tagihan']);
$spp_tabungan = !empty($spp_access['tabungan']);
if ($spp_tagihan || $spp_tabungan):
?>
<style>
    #spp-dashboard { margin-top: 28px; }
    #spp-dashboard .spp-page-title { font-weight: 700; }
    #spp-dashboard .spp-page-subtitle { color: var(--ct-secondary-color,#727c8a); }
    #spp-dashboard .spp-filter-card .card-body { padding-bottom: 12px; }
    #spp-dashboard .spp-section-card { border: 1px solid rgba(0,0,0,.05); box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.04); }
    #spp-dashboard .spp-section-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
    #spp-dashboard .spp-section-title { margin:0; font-size:1.05rem; font-weight:700; }
    #spp-dashboard .spp-section-note { margin:2px 0 0; color: var(--ct-secondary-color,#727c8a); font-size:.85rem; }
    #spp-dashboard .spp-summary-grid { display:grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap:12px; }
    @media (min-width: 768px) { #spp-dashboard .spp-summary-grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1200px) { #spp-dashboard .spp-summary-grid.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    #spp-dashboard .spp-metric-box { border:1px solid rgba(0,0,0,.06); border-radius:14px; padding:16px 18px; background:#fff; height:100%; }
    #spp-dashboard .spp-metric-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    #spp-dashboard .spp-metric-label { color: var(--ct-secondary-color,#727c8a); font-size:.86rem; line-height:1.45; margin:0; }
    #spp-dashboard .spp-metric-value { margin-top:10px; font-size:clamp(1.08rem,1.7vw,1.5rem); font-weight:700; color:#25396f; word-break:break-word; }
    #spp-dashboard .spp-icon-badge { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:rgba(60,110,255,.10); color:#3c6eff; font-size:1.1rem; flex:0 0 auto; }
    #spp-dashboard .spp-chart-box { border:1px solid rgba(0,0,0,.06); border-radius:14px; background:#fff; height:100%; }
    #spp-dashboard .spp-chart-header { padding:16px 18px 0; }
    #spp-dashboard .spp-chart-header h6 { margin:0; font-size:.98rem; font-weight:700; }
    #spp-dashboard .spp-chart-header p { margin:4px 0 0; color: var(--ct-secondary-color,#727c8a); font-size:.82rem; }
    #spp-dashboard .spp-chart { min-height:300px; padding:4px 6px 0; }
    #spp-dashboard .spp-info-text { color: var(--ct-secondary-color,#727c8a); font-size:.85rem; }
</style>
<section id="spp-dashboard" aria-labelledby="spp-dashboard-heading">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 id="spp-dashboard-heading" class="spp-page-title mb-1">Dashboard SPP</h4>
            <!-- <div class="spp-page-subtitle">Ringkasan tagihan, pembayaran, tunggakan, dan tabungan siswa dalam satu tampilan.</div> -->
        </div>
    </div>

    <div class="card spp-filter-card mb-3">
        <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h5 class="header-title mb-0">Filter Dashboard</h5>
            <span class="badge bg-light text-dark">Filter sesuai kebutuhan ringkasan</span>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6 col-xl">
                    <label for="spp-periode" class="form-label">Tahun Pelajaran</label>
                    <select id="spp-periode" class="form-select"><option value="">Memuat...</option></select>
                </div>
                <div class="col-12 col-md-6 col-xl">
                    <label for="spp-kelas" class="form-label">Kelas</label>
                    <select id="spp-kelas" class="form-select"><option value="0">Semua Kelas</option></select>
                </div>
                <div class="col-12 col-md-6 col-xl">
                    <label for="spp-tingkat" class="form-label">Tingkat</label>
                    <select id="spp-tingkat" class="form-select"><option value="0">Semua Tingkat</option><?php for ($spp_i=1;$spp_i<=6;$spp_i++): ?><option value="<?= $spp_i ?>">Kelas <?= $spp_i ?></option><?php endfor; ?></select>
                </div>
                <div class="col-12 col-md-6 col-xl">
                    <label for="spp-bulan" class="form-label">Bulan/Periode</label>
                    <select id="spp-bulan" class="form-select"><option value="0" selected>Seluruh Tahun Ajaran</option></select>
                </div>
                <div class="col-12 col-md-6 col-xl">
                    <label for="spp-jenis" class="form-label">Jenis Tagihan</label>
                    <select id="spp-jenis" class="form-select"><option value="0">Semua Jenis</option></select>
                </div>
                <div class="col-12 col-md-6 col-xl-auto">
                    <button id="spp-apply" type="button" class="btn btn-primary w-100"><i class="ri-filter-3-line me-1"></i> Tampilkan Ringkasan</button>
                </div>
            </div>
            <div id="spp-dashboard-info" class="small text-danger mt-2" aria-live="polite" hidden></div>
        </div>
    </div>

    <?php if($spp_tagihan): ?>
    <div class="card spp-section-card mb-3">
        <div class="card-body">
            <div class="spp-section-header">
                <div>
                    <h5 class="spp-section-title">Ringkasan Tagihan Bulanan</h5>
                    <!-- <p class="spp-section-note">Ringkasan kumulatif tagihan bulanan sejak Juli sampai bulan/periode yang dipilih.</p> -->
                </div>
            </div>
            <div class="spp-summary-grid cols-3">
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Jumlah siswa yang sudah membayar</p><span class="spp-icon-badge"><i class="ri-user-follow-line"></i></span></div><div class="spp-metric-value" id="spp-value-bulanan-sudah" data-format="siswa">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Jumlah siswa yang belum membayar</p><span class="spp-icon-badge"><i class="ri-user-unfollow-line"></i></span></div><div class="spp-metric-value" id="spp-value-bulanan-belum" data-format="siswa">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Persentase pembayaran tagihan bulanan</p><span class="spp-icon-badge"><i class="ri-pie-chart-2-line"></i></span></div><div class="spp-metric-value" id="spp-value-bulanan-persen" data-format="persen">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total tagihan sampai periode yang dipilih</p><span class="spp-icon-badge"><i class="ri-file-list-3-line"></i></span></div><div class="spp-metric-value" id="spp-value-bulanan-target" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total pembayaran yang sudah diterima</p><span class="spp-icon-badge"><i class="ri-wallet-3-line"></i></span></div><div class="spp-metric-value" id="spp-value-bulanan-dibayar" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total sisa tagihan</p><span class="spp-icon-badge"><i class="ri-hourglass-line"></i></span></div><div class="spp-metric-value" id="spp-value-bulanan-sisa" data-format="rupiah">—</div></div>
            </div>
        </div>
    </div>

    <div class="card spp-section-card mb-3">
        <div class="card-body">
            <div class="spp-section-header">
                <div>
                    <h5 class="spp-section-title">Ringkasan Tagihan Tahunan dan Langsung</h5>
                    <!-- <p class="spp-section-note">Merangkum tagihan tahunan dan tagihan langsung berdasarkan filter yang dipilih.</p> -->
                </div>
            </div>
            <div class="spp-summary-grid cols-3">
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Jumlah siswa yang sudah lunas</p><span class="spp-icon-badge"><i class="ri-check-double-line"></i></span></div><div class="spp-metric-value" id="spp-value-tahunan-lunas" data-format="siswa">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Jumlah siswa yang masih memiliki sisa tagihan</p><span class="spp-icon-badge"><i class="ri-time-line"></i></span></div><div class="spp-metric-value" id="spp-value-tahunan-belum" data-format="siswa">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total kekurangan</p><span class="spp-icon-badge"><i class="ri-money-dollar-circle-line"></i></span></div><div class="spp-metric-value" id="spp-value-tahunan-sisa" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total nominal tagihan</p><span class="spp-icon-badge"><i class="ri-file-paper-2-line"></i></span></div><div class="spp-metric-value" id="spp-value-tahunan-target" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total pembayaran</p><span class="spp-icon-badge"><i class="ri-secure-payment-line"></i></span></div><div class="spp-metric-value" id="spp-value-tahunan-dibayar" data-format="rupiah">—</div></div>
            </div>
        </div>
    </div>

    <div class="card spp-section-card mb-3">
        <div class="card-body">
            <div class="spp-section-header">
                <div>
                    <h5 class="spp-section-title">Ringkasan Tunggakan</h5>
                    <!-- <p class="spp-section-note">Menggambarkan nominal tunggakan dan jumlah siswa yang masih memiliki tunggakan.</p> -->
                </div>
            </div>
            <div class="spp-summary-grid cols-2">
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total tunggakan seluruh siswa</p><span class="spp-icon-badge"><i class="ri-alarm-warning-line"></i></span></div><div class="spp-metric-value" id="spp-value-tunggakan-total" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Jumlah siswa yang memiliki tunggakan</p><span class="spp-icon-badge"><i class="ri-group-line"></i></span></div><div class="spp-metric-value" id="spp-value-tunggakan-siswa" data-format="siswa">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Tunggakan tahun pelajaran aktif</p><span class="spp-icon-badge"><i class="ri-calendar-check-line"></i></span></div><div class="spp-metric-value" id="spp-value-tunggakan-aktif" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Tunggakan tahun pelajaran sebelumnya</p><span class="spp-icon-badge"><i class="ri-calendar-event-line"></i></span></div><div class="spp-metric-value" id="spp-value-tunggakan-sebelumnya" data-format="rupiah">—</div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card spp-section-card mb-3">
        <div class="card-body">
            <div class="spp-section-header">
                <div>
                    <h5 class="spp-section-title">Ringkasan Pembayaran dan Tabungan</h5>
                    <!-- <p class="spp-section-note">Berisi transaksi pembayaran hari ini serta posisi transaksi tabungan siswa.</p> -->
                </div>
            </div>
            <div class="spp-summary-grid cols-3">
                <?php if($spp_tagihan): ?>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total pembayaran hari ini</p><span class="spp-icon-badge"><i class="ri-hand-coin-line"></i></span></div><div class="spp-metric-value" id="spp-value-hari_ini-total" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Jumlah transaksi pembayaran hari ini</p><span class="spp-icon-badge"><i class="ri-bank-card-line"></i></span></div><div class="spp-metric-value" id="spp-value-hari_ini-jumlah" data-format="transaksi">—</div></div>
                <?php endif; ?>
                <?php if($spp_tabungan): ?>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total setoran tabungan hari ini</p><span class="spp-icon-badge"><i class="ri-arrow-down-circle-line"></i></span></div><div class="spp-metric-value" id="spp-value-tabungan-setoran_hari_ini" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total penarikan tabungan hari ini</p><span class="spp-icon-badge"><i class="ri-arrow-up-circle-line"></i></span></div><div class="spp-metric-value" id="spp-value-tabungan-penarikan_hari_ini" data-format="rupiah">—</div></div>
                <div class="spp-metric-box"><div class="spp-metric-top"><p class="spp-metric-label">Total saldo tabungan seluruh siswa</p><span class="spp-icon-badge"><i class="ri-safe-2-line"></i></span></div><div class="spp-metric-value" id="spp-value-tabungan-saldo" data-format="rupiah">—</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($spp_tagihan): ?>
    <div class="card spp-section-card mb-3">
        <div class="card-body">
            <div class="spp-section-header">
                <div>
                    <h5 class="spp-section-title">Grafik Realisasi Pembayaran</h5>
                    <!-- <p class="spp-section-note">Menampilkan realisasi pembayaran bulanan, target tagihan, dan perbandingan per jenis maupun per kelas.</p> -->
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <div class="spp-chart-box">
                        <div class="spp-chart-header">
                            <h6>Grafik penerimaan pembayaran siswa per bulan</h6>
                            <!-- <p>Perbandingan target tagihan dengan pembayaran yang sudah diterima.</p> -->
                        </div>
                        <div id="spp-chart-bulanan" class="spp-chart"></div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="spp-chart-box">
                        <div class="spp-chart-header">
                            <h6>Perbandingan realisasi antarjenis tagihan</h6>
                            <!-- <p>Memudahkan melihat kontribusi pembayaran per jenis tagihan.</p> -->
                        </div>
                        <div id="spp-chart-jenis" class="spp-chart"></div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="spp-chart-box">
                        <div class="spp-chart-header">
                            <h6>Perbandingan realisasi antarkelas</h6>
                            <!-- <p>Memudahkan melihat realisasi pembayaran per kelas.</p> -->
                        </div>
                        <div id="spp-chart-kelas" class="spp-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>
<script>
(function () {
    'use strict';
    const root = document.getElementById('spp-dashboard');
    if (!root) return;
    const urlFilters = '<?= base_url('dashboard/spp_dashboard_filters'); ?>';
    const urlSummary = '<?= base_url('dashboard/spp_dashboard_result'); ?>';
    const canTagihan = <?= $spp_tagihan ? 'true' : 'false' ?>;
    const $id = id => document.getElementById('spp-' + id);
    // Informasi sukses/waktu refresh tidak ditampilkan; kegagalan AJAX tetap terlihat.
    const dashboardError = message => {
        const el = $id('dashboard-info');
        el.textContent = message || '';
        el.hidden = !message;
    };
    const angka = v => Number(v || 0).toLocaleString('id-ID', {maximumFractionDigits: 0});
    const safe = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const charts = {};
    let master = {periode: [], kelas: [], jenis: [], active:0};
    let request = 0;
    function option(value, label) { return '<option value="' + Number(value) + '">' + safe(label) + '</option>'; }
    function periodMonths() {
        const periodId = Number($id('periode').value);
        const period = (master.periode || []).find(p => Number(p.id) === periodId);
        const text = String(period && period.periode ? period.periode : '');
        const match = text.match(/(20\d{2})\s*[\/-]\s*(20\d{2})/);
        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const options = ['<option value="0">Seluruh Tahun Ajaran</option>'];
        if (match && Number(match[2]) === Number(match[1]) + 1) {
            const year = Number(match[1]);
            for (let i = 0; i < 12; i++) {
                const index = 6 + i;
                const y = year + Math.floor(index / 12);
                const m = index % 12;
                options.push(option(y * 100 + m + 1, 'Sampai ' + monthNames[m] + ' ' + y));
            }
        }
        $id('bulan').innerHTML = options.join('');
    }
    function classes() {
        const p = Number($id('periode').value);
        const old = Number($id('kelas').value);
        const tingkat = Number($id('tingkat').value);
        // Samakan pengenalan tingkat dengan filter kelas model (contoh: 1A, 1B, Kelas 1A).
        const tingkatPattern = tingkat > 0 ? new RegExp('(^|[^0-9])' + tingkat + '([^0-9]|$)') : null;
        const available = (master.kelas || []).filter(k =>
            Number(k.id_periode) === p &&
            (!tingkatPattern || tingkatPattern.test(String(k.nama_kelas || '')))
        );
        $id('kelas').innerHTML = '<option value="0">Semua Kelas</option>' + available.map(k => option(k.id, k.nama_kelas)).join('');
        if (available.some(k => Number(k.id) === old)) $id('kelas').value = String(old);
    }
    function put(name, values) {
        Object.entries(values || {}).forEach(([key, value]) => {
            const el = document.getElementById('spp-value-' + name + '-' + key);
            if (!el) return;
            const fmt = el.dataset.format;
            el.textContent = fmt === 'rupiah' ? 'Rp ' + NumberToMoney(value || 0) : (fmt === 'persen' ? Number(value || 0).toLocaleString('id-ID',{maximumFractionDigits:2}) + '%' : angka(value));
        });
    }
    function plot(id, rows, mode) {
        const target = document.getElementById(id);
        if (!target) return;
        if (charts[id]) { charts[id].destroy(); delete charts[id]; }
        if (typeof ApexCharts === 'undefined') { target.textContent = 'Library grafik belum tersedia.'; return; }
        const values = Array.isArray(rows) ? rows : [];
        const labels = values.map(x => String(x && x.label != null ? x.label : 'Tanpa Data').replace(/[<>]/g, ''));
        const isLine = mode === 'line';
        const isColumn = mode === 'column';
        // Grafik bulanan: label sumbu X horizontal dan dipendekkan bila perlu.
        // Nama bulan dan tahun lengkap tetap digunakan pada tooltip.
        const shortMonths = {
            'Januari': 'Jan', 'Februari': 'Feb', 'Agustus': 'Agu',
            'September': 'Sep', 'Oktober': 'Okt',
            'November': 'Nov', 'Desember': 'Des'
        };
        const axisLabels = isLine
            ? labels.map(label => {
                const match = label.match(/^([A-Za-z]+)\s+(20\d{2})$/);
                if (!match) return label;
                return (shortMonths[match[1]] || match[1]) + ' ' + match[2].slice(-2);
            })
            : labels;
        const options = {
            chart: {type: isLine ? 'line' : 'bar', height:320, toolbar:{show:false}, fontFamily:'inherit'},
            series: [
                {name:'Target Tagihan', data: values.map(x => Number(x && x.target ? x.target : 0))},
                {name:'Realisasi Pembayaran', data: values.map(x => Number(x && x.bayar ? x.bayar : 0))}
            ],
            xaxis: {
                categories: axisLabels,
                labels: {
                    rotate: isLine ? 0 : -35,
                    rotateAlways: isLine,
                    trim: false,
                    hideOverlappingLabels: false,
                    maxHeight: isLine ? 45 : 120,
                    style: {fontSize: isLine ? '11px' : '12px'}
                }
            },
            yaxis: {
                labels:{formatter:n => n >= 1000000 ? (n/1000000).toFixed(1)+' jt' : n >= 1000 ? (n/1000).toFixed(0)+' rb' : String(Math.round(n))}
            },
            colors:['#6b93dd','#24ad86'],
            dataLabels:{enabled:false},
            stroke:{width: isLine ? 3 : 0, curve:'smooth'},
            plotOptions:{bar:{horizontal:false, borderRadius:4, columnWidth:isColumn ? '55%' : '45%'}},
            legend:{position:'top'},
            noData:{text:'Belum ada data'},
            tooltip:{shared:true, intersect:false, x:{formatter:(_, ctx) => labels[ctx.dataPointIndex] || ''}, y:{formatter:n => 'Rp ' + NumberToMoney(n)}}
        };
        charts[id] = new ApexCharts(target, options);
        charts[id].render();
    }
    // AJAX Dashboard SPP mengikuti pola Dashboard Admin lainnya (jQuery $.ajax).
    function spp_dashboard_result() {
        const current = ++request;
        dashboardError('');
        $id('apply').disabled = true;
        $.ajax({
            url: urlSummary,
            type: 'POST',
            dataType: 'JSON',
            data: {
                periode: $id('periode').value,
                kelas: $id('kelas').value,
                tingkat: $id('tingkat').value,
                bulan: $id('bulan').value,
                jenis: $id('jenis').value
            },
            success: function (res) {
                if (current !== request) return;
                if (!res || res.result !== 'true' || !res.data) {
                    dashboardError('Gagal mengambil Dashboard SPP: ' + (res && res.message ? res.message : 'Respons dashboard tidak valid'));
                    return;
                }
                const data = res.data;
                put('bulanan', data.bulanan);
                put('tahunan', data.tahunan);
                put('tunggakan', data.tunggakan);
                put('hari_ini', data.hari_ini);
                put('tabungan', data.tabungan);
                if (canTagihan) {
                    plot('spp-chart-bulanan', data.grafik, 'line');
                    plot('spp-chart-jenis', data.perbandingan_jenis, 'column');
                    plot('spp-chart-kelas', data.perbandingan_kelas, 'column');
                }
                dashboardError('');
            },
            error: function (xhr) {
                if (current !== request) return;
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'HTTP ' + xhr.status;
                dashboardError('Gagal mengambil Dashboard SPP: ' + message);
            },
            complete: function () {
                if (current === request) $id('apply').disabled = false;
            }
        });
    }
    $id('periode').addEventListener('change', function () { classes(); periodMonths(); });
    $id('tingkat').addEventListener('change', classes);
    $id('apply').addEventListener('click', spp_dashboard_result);

    function spp_dashboard_filters() {
        $.ajax({
            url: urlFilters,
            type: 'POST',
            dataType: 'JSON',
            success: function (res) {
                if (!res || res.result !== 'true' || !res.data) {
                    dashboardError('Gagal memuat filter dashboard: ' + (res && res.message ? res.message : 'Respons dashboard tidak valid'));
                    return;
                }
                master = res.data;
                $id('periode').innerHTML = (master.periode || []).map(p => option(p.id, p.periode)).join('');
                $id('periode').value = String(master.active || 0);
                $id('jenis').innerHTML = '<option value="0">Semua Jenis</option>' + (master.jenis || []).map(j => option(j.id, j.nama_jenis)).join('');
                classes();
                periodMonths();
                spp_dashboard_result();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'HTTP ' + xhr.status;
                dashboardError('Gagal memuat filter dashboard: ' + message);
            }
        });
    }
    if (document.readyState === 'complete') spp_dashboard_filters();
    else window.addEventListener('load', spp_dashboard_filters, {once:true});
})();
</script>
<?php endif; ?>

<script>
	let topChart = null;
	$(document).ready(function () {
		jadwal_hari_ini();
		setTimeout(function () {
			grafikPerbandingan();
		}, 300);
		setTimeout(function () {
			grafikRencanaPemasukan();
			grafikRencanaPengeluaran();
		}, 300);

		top_pengeluaran_terbesar();
		notifKeuangan();
		$('#bulan_top,#tahun_top').change(function () { top_pengeluaran_terbesar(); });
if ($('input[name="tanggal_awal_saldo"]').length) {
	flatpickr('input[name="tanggal_awal_saldo"]', {
		dateFormat: "d-m-Y",
		defaultDate: "<?= date('01-m-Y') ?>",
		onChange: function () {
			let tanggal_awal = $('input[name="tanggal_awal_saldo"]').val();
			let tanggal_akhir = $('input[name="tanggal_akhir_saldo"]').val();
		}
	});
}

if ($('input[name="tanggal_akhir_saldo"]').length) {
	flatpickr('input[name="tanggal_akhir_saldo"]', {
		dateFormat: "d-m-Y",
		defaultDate: "<?= date('t-m-Y') ?>",
		onChange: function () {
			let tanggal_awal = $('input[name="tanggal_awal_saldo"]').val();
			let tanggal_akhir = $('input[name="tanggal_akhir_saldo"]').val();
		}
	});
}
		saldo();
	})

	function jadwal_hari_ini() {
		$.ajax({
			url: '<?= base_url('dashboard/jadwal_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {
				console.log(data)
				var tanggal = '<?= date('d-m-Y') ?>'
				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="7" style="text-align: center;">Tidak ada jadwal </td>
					</tr>
				`;
				} else {
					data.forEach(function (item) {
						// let detail = btoa(JSON.stringify(item));
						// table += `
						// <tr> 
						// 	<td>${item.mapel ?? ''}</td>
						// 	<td>${item.jam_pelajaran_awal ?? ''}- ${item.jam_pelajaran_akhir ?? ''}</td>
						// 	<td>${item.nama_kelas ?? ''}</td> 

						// 	<td>
						// 		<a href="<?= base_url(); ?>admin/kurikulum/jurnal/jurnal_guru/jurnal_mengajar/${item.id_jadwal}/${tanggal}" type="button" class="btn btn-sm btn-info" style="width:100%;"><i class="ri-error-warning-line"></i> Jurnal Kehadiran</a>
						// 	</td>
						// </tr>
						// `;
						table += `
							<div class="card-mapel">
								<p class="keterangan-hari">
									<span>Hari : ${item.hari}</span>
									<span>Semester (${item.semester})</span>
								</p>
								<div class="keterangan-mapel">
									<div class="keterangan-mapel-kiri">
										<h5 class="judul-mapel">${item.mapel}</h5>
										<h6 class="judul-mapel"> Kelas ${item.kelas} ${item.kode_kelas}</h6>
										<p class="keterangan-jam-mapel">Jam Pelajaran : ${item.jam_pelajaran_awal} - ${item.jam_pelajaran_akhir}</p>
									</div>
									<div class="keterangan-mapel-kanan">
										<a href="<?= base_url(); ?>admin/kurikulum/jurnal/jurnal_guru/jurnal_mengajar/${item.id_jadwal}/${tanggal}" type="button" class="btn btn-sm btn-info" style="width:100%;"><i class="ri-error-warning-line"></i> Jurnal Kehadiran</a>
									</div>
														</div>
													</div>`;
					});
				}
				$('#card-container').html(table);
			}
		});
	}
	function saldo() {
		let tanggal_awal = $('input[name="tanggal_awal_saldo"]').val();
		let tanggal_akhir = $('input[name="tanggal_akhir_saldo"]').val();
		$.ajax({
			url: '<?= base_url('dashboard/saldo_result'); ?>',
			type: 'POST',
			data: {
				tanggal_awal: tanggal_awal,
				tanggal_akhir: tanggal_akhir
			},
			dataType: 'JSON',
			success: function (data) {
				var table = `
				<div class="row">
					<div class="col-md-12 mb-3">
						<div class="card shadow-sm border">
							<div class="card-body p-2">
								<div class="row">
									<div class="col-md-4">
										<small>Awal Transaksi</small>
										<div class="fw-semibold">${data.tanggal_awal}</div>
									</div>
									<div class="col-md-4">
										<small>Akhir Transaksi</small>
										<div class="fw-semibold">${data.tanggal_akhir}</div>
									</div>
									<div class="col-md-4">
										<small>Saldo Awal</small>
										<div class="fw-semibold">Rp ${NumberToMoney(data.saldo_awal)}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card border-success shadow-sm">
							<div class="card-body p-2 text-center">
								<small class="text-muted">PEMASUKAN</small>
								<h5 class="fw-bold text-success mb-0">Rp ${NumberToMoney(data.pemasukan)}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card border-danger shadow-sm">
							<div class="card-body p-2 text-center">
								<small class="text-muted">PENGELUARAN</small>
								<h5 class="fw-bold text-danger mb-0">Rp ${NumberToMoney(data.pengeluaran)}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card border-primary shadow-sm">
							<div class="card-body p-2 text-center">
								<small class="text-muted">
									SALDO AKHIR
								</small>
								<h5 class="fw-bold text-primary mb-0">Rp ${NumberToMoney(data.saldo_akhir)}</h5>
							</div>
						</div>
					</div>
				</div>`;
				$('#card-container-saldo').html(table);
			}
		});
	}
	function top_pengeluaran_terbesar() {
		let bulan = $('#bulan_top').val();
		let tahun = $('#tahun_top').val();
		$.ajax({
			url: '<?= base_url("dashboard/pengeluaran_terbesar_result") ?>',
			type: 'POST',
			data: {
				bulan: bulan,
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (data) {
				renderTop(data);
			}
		});
	}
	function grafikPerbandingan() {
		let tahun = $("#tahun_perbandingan").val();
		$.ajax({
			url: '<?= base_url("dashboard/grafik_perbandingan") ?>',
			type: 'POST',
			data: {
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (res) {

				chartPerbandingan(res);

			}
		})

	}

	function grafikRencanaPemasukan() {
		let tahun = $('#tahun_rencana_pemasukan').val();
		$.ajax({
			url: '<?= base_url("dashboard/grafik_rencana_pemasukan_result") ?>',
			type: 'POST',
			data: {
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (data) {
				chartRencanaPemasukan(data);
			}
		});
	}

	function grafikRencanaPengeluaran() {
		let tahun = $('#tahun_rencana_pengeluaran').val();
		$.ajax({
			url: '<?= base_url("dashboard/grafik_rencana_pengeluaran_result") ?>',
			type: 'POST',
			data: {
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (data) {
				chartRencanaPengeluaran(data);
			}
		});
	}
	document.addEventListener("DOMContentLoaded", function () {
		const defaultColors = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00"];

		function renderRadialChart(selector, seriesValue) {
			const dataColors = document.querySelector(selector).dataset.colors;
			const colors = dataColors ? dataColors.split(",") : defaultColors;

			const options = {
				series: [seriesValue],
				chart: {
					type: "radialBar",
					height: 81,
					width: 81,
					sparkline: {
						enabled: false
					}
				},
				plotOptions: {
					radialBar: {
						offsetY: 0,
						hollow: {
							margin: 0,
							size: "50%"
						},
						dataLabels: {
							name: {
								show: false
							},
							value: {
								offsetY: 5,
								fontSize: "14px",
								fontWeight: "600",
								formatter: function (val) {
									return val;
								}
							}
						}
					}
				},
				grid: {
					padding: {
						top: -18,
						bottom: -20,
						left: -20,
						right: -20
					}
				},
				colors: colors
			};

			new ApexCharts(document.querySelector(selector), options).render();
		}

		// Panggil render untuk tiap chart
		renderRadialChart("#chart-one", <?= $total_mapel ?>);
		renderRadialChart("#chart-two", <?= $total_kelas ?>);
		renderRadialChart("#chart-three", <?= $total_pegawai ?>);
		renderRadialChart("#chart-four", 75);
	});


	let chartGabungan = null;
	function chartPerbandingan(data) {
		let bulanNama = [
			"Januari", "Februari", "Maret", "April",
			"Mei", "Juni", "Juli", "Agustus",
			"September", "Oktober", "November", "Desember"
		];

		let pemasukan = [];
		let pengeluaran = [];
		data.forEach(item => {

			pemasukan.push(parseFloat(item.pemasukan));
			pengeluaran.push(parseFloat(item.pengeluaran));
		});


		if (chartGabungan) {
			chartGabungan.destroy();
		}


		let options = {
			series: [
				{
					name: 'Pemasukan',
					data: pemasukan
				},
				{
					name: 'Pengeluaran',
					data: pengeluaran
				}
			],

			chart: {
				type: 'bar',
				height: 400,
				toolbar: {
					show: true
				}
			},

			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					borderRadius: 5
				}
			},

			colors: [
				'#22c55e',
				'#ef4444'
			],

			dataLabels: { enabled: false },
			xaxis: { categories: bulanNama },
			yaxis: {
				labels: {
					formatter: function (val) {
						return 'Rp ' + NumberToMoney(val)
					}
				}
			},

			tooltip: {
				y: {
					formatter: function (val) {
						return 'Rp ' + NumberToMoney(val)
					}
				}
			},

			legend: { position: 'top' }

		};

		chartGabungan = new ApexCharts(
			document.querySelector("#chart-perbandingan"),
			options
		);
		chartGabungan.render();
	}
	function renderTop(data) {
		let label = [];
		let total = [];
		if (data.length === 0) {
			label.push("Tidak ada data");
			total.push(0);
		}
		data.forEach(function (item) {
			label.push(
				item.keterangan
			);
			total.push(
				parseFloat(item.total)
			);
		});

		if (topChart) {
			topChart.destroy();
		}
		topChart = new ApexCharts(
			document.querySelector("#chart-top"),
			{
				series: total,
				labels: label,
				chart: {
					type: 'donut',
					height: 350
				},
				legend: {
					position: 'bottom'
				},
				tooltip: {
					y: {
						formatter: function (value) {
							return 'Rp ' + NumberToMoney(value);
						}
					}
				},
			}
		);
		topChart.render();
	}
	function notifKeuangan() {
		$.ajax({
			url: '<?= base_url("dashboard/notif_keuangan") ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {
				let html = '';
				if (data.length == 0) {
					html = `
				<div class="alert alert-success mb-1 py-2">
					Tidak ada notifikasi
				</div>
				`;

				} else {
					data.forEach(function (item) {
						let icon = '⚠';
						if (item.tipe == 'danger') {
							icon = '⛔';
						}
						html += `
					<div class="alert alert-${item.tipe} mb-2 py-2">
						<div class="fw-bold">
							${icon}
							${item.judul}
						</div>
						<small>${item.pesan}</small>
					</div>`;
					});
				}
				$('#jumlah-notif').html(data.length);
				$('#notif-keuangan').html(html);
			}
		});
	}

	let chartPengeluaran_Rencana = null;

	function chartRencanaPengeluaran(data) {
		let bulan = [];
		let rencana = [];
		let realisasi = [];
		let serapan = [];
		const namaBulan = {
			"01": "Januari",
			"02": "Februari",
			"03": "Maret",
			"04": "April",
			"05": "Mei",
			"06": "Juni",
			"07": "Juli",
			"08": "Agustus",
			"09": "September",
			"10": "Oktober",
			"11": "November",
			"12": "Desember"
		};

		data.forEach(item => {
			bulan.push(namaBulan[item.bulan]);
			rencana.push(parseFloat(item.rencana));
			realisasi.push(parseFloat(item.realisasi));
			serapan.push(parseFloat(item.serapan));
		});
		if (chartPengeluaran_Rencana) {
			chartPengeluaran_Rencana.destroy();
		}

		let options = {
			series: [
				{
					name: 'Rencana',
					data: rencana
				},
				{
					name: 'Realisasi',
					data: realisasi
				}
			],

			chart: {
				type: 'bar',
				height: 350
			},

			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					borderRadius: 5
				}
			},

			colors: [
				'#3b82f6',
				'#ef4444'
			],

			// MATIKAN ANGKA DI BATANG
			dataLabels: { enabled: false },
			xaxis: { categories: bulan },
			yaxis: {
				labels: {
					formatter: function (val) {
						if (val >= 1000000) {
							return (val / 1000000).toFixed(0) + ' jt';
						}
						if (val >= 1000) {
							return (val / 1000).toFixed(0) + ' rb';
						}
						return val;
					}
				}
			},

			tooltip: {
				shared: true,
				intersect: false, // tambahkan ini

				custom: function ({
					dataPointIndex
				}) {
					let persen = serapan[dataPointIndex];
					return `<div style="padding:10px; min-width:200px">
			<b>${bulan[dataPointIndex]}</b>
			<hr>
			Rencana :
			Rp ${NumberToMoney(rencana[dataPointIndex])}
			<br>
			Realisasi :
			Rp ${NumberToMoney(realisasi[dataPointIndex])}
			<br><br>
			<b>
			Serapan:
			${persen}%
			</b>
			</div>`;
				}
			},

			legend: { position: 'top' }
		};

		chartPengeluaran_Rencana = new ApexCharts(document.querySelector("#chart-rencana-pengeluaran"), options);
		chartPengeluaran_Rencana.render();
	}

	let chartPemasukan_Rencana = null;
	function chartRencanaPemasukan(data) {
		let bulan = [];
		let rencana = [];
		let realisasi = [];
		let serapan = [];
		const namaBulan = {
			"01": "Januari",
			"02": "Februari",
			"03": "Maret",
			"04": "April",
			"05": "Mei",
			"06": "Juni",
			"07": "Juli",
			"08": "Agustus",
			"09": "September",
			"10": "Oktober",
			"11": "November",
			"12": "Desember"
		};

		data.forEach(item => {
			bulan.push(namaBulan[item.bulan]);
			rencana.push(parseFloat(item.rencana));
			realisasi.push(parseFloat(item.realisasi));
			serapan.push(parseFloat(item.serapan));
		});
		if (chartPemasukan_Rencana) {
			chartPemasukan_Rencana.destroy();
		}

		let options = {
			series: [
				{
					name: 'Rencana',
					data: rencana
				},
				{
					name: 'Realisasi',
					data: realisasi
				}
			],

			chart: {
				type: 'bar',
				height: 350
			},

			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					borderRadius: 5
				}
			},

			colors: [
				'#3b82f6',
				'#ef4444'
			],

			// MATIKAN ANGKA DI BATANG
			dataLabels: { enabled: false },
			xaxis: { categories: bulan },

			yaxis: {
				labels: {
					formatter: function (val) {
						if (val >= 1000000) {
							return (val / 1000000).toFixed(0) + ' jt';
						}
						if (val >= 1000) {
							return (val / 1000).toFixed(0) + ' rb';
						}
						return val;
					}
				}
			},

			tooltip: {
				shared: true,
				intersect: false, // tambahkan ini
				custom: function ({
					dataPointIndex
				}) {
					let persen =
						serapan[dataPointIndex];
					return `<div style="padding:10px; min-width:200px">
			<b>${bulan[dataPointIndex]}</b>
			<hr>
			Rencana :
			Rp ${NumberToMoney(rencana[dataPointIndex])}
			<br>
			Realisasi :
			Rp ${NumberToMoney(realisasi[dataPointIndex])}
			<br><br>
			<b>
			Serapan:
			${persen}%
			</b>
			</div>`;
				}
			},
			legend: { position: 'top' }
		};
		chartPemasukan_Rencana = new ApexCharts(document.querySelector("#chart-rencana-pemasukan"), options);
		chartPemasukan_Rencana.render();
	}
</script>