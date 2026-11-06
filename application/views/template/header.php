<?php
$CI = &get_instance();
$CI->load->library('web_guard');
$web_logout_csrf_token = $CI->web_guard->csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-sidenav-size="sm-hover">

<head>
	<meta charset="utf-8" />
	<title>Halaman <?= $title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
	<meta content="Coderthemes" name="author" />


	<link rel="shortcut icon" href="<?= base_url(); ?>assets/favicon.ico">


	<link href="<?= base_url(); ?>assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">


	<script src="<?= base_url(); ?>assets/js/config.js"></script>


	<link href="<?= base_url(); ?>assets/css/vendor.min.css" rel="stylesheet" type="text/css" />


	<link href="<?= base_url(); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

	<link href="<?= base_url(); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url(); ?>assets/css/lightbox.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="<?= base_url(); ?>assets/vendor/select2/css/select2.min.css" type="text/css">
	<link rel="stylesheet" href="https://unpkg.com/@tabler/icons-webfont@latest/tabler-icons.min.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" rel="stylesheet"
		type="text/css" />

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@3.1.3/build/jodit.min.css" />
	<link href="https://smkryoyuwaraja.sch.id/assets/admin/css/lightbox.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/jodit@3.1.3/build/jodit.min.js"></script>
	<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
</head>


<style>
	.dropify-wrapper .dropify-message span.file-icon {
		font-size: 20px;
		color: #CCC;
	}

	@media (min-width: 320px) and (max-width: 767.98px) {
		.app-topbar .logo {
			display: none;
		}
	}

	@media (min-width: 767.98px) {
		.app-topbar .logo {
			display: block;
		}
	}

	.card-mapel {
		background-color: #F5F5F5FF;
		border: 1px solid #CACACAFF;
		padding: 12px;
		border-radius: 8px;
		margin-bottom: 12px;
	}

	.card-mapel .keterangan-hari {
		padding: 0;
		margin: 0;
		font-size: 12px;
		margin-bottom: 4px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-direction: row;
	}

	.card-mapel .keterangan-mapel {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.card-mapel .judul-mapel {
		padding: 0;
		margin-bottom: 8px;
		font-size: 18px;
	}

	.card-mapel .keterangan-jam-mapel {
		padding: 0;
		margin: 0;
		font-size: 12px;
		color: #199BE2;
	}

	.keterangan-mapel-kiri {
		margin-bottom: 8px;
	}

	.text-tanggal-input {
		font-size: 14px;
		margin: 0;
		display: flex;
		align-items: center;
		column-gap: 1rem;
	}

	.wrap-tanggal-kegiatan {
		position: relative;
	}

	.wrap-tanggal-kegiatan::after {
		content: '';
		height: 40px;
		width: 1px;
		background-color: #C2C2C2FF;
		position: absolute;
		right: -18px;
		top: 0;
	}

	.keterangan-mapel-kiri-utama {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
	}

	@media (min-width: 320px) and (max-width: 767px) {
		.card-mapel .keterangan-mapel {
			flex-direction: column;
			align-items: flex-start;
		}

		.keterangan-mapel-kiri-utama {
			flex-direction: column;
			align-items: flex-start;
			row-gap: 8px;
		}

		.card-mapel .keterangan-hari {
			font-size: 12px;
			align-items: flex-start;
		}

		.keterangan-mapel-kanan {
			width: 100%;
		}

		.text-tanggal-input {
			flex-direction: column;
			align-items: flex-start;
			row-gap: .2rem;
		}
	}

	/* Komponen data SDKreatif: konsisten dengan tinggi dan tabel Bootstrap. */
	.select2-container {
		width: 100% !important;
	}

	.select2-container--default .select2-selection--single {
		height: calc(1.5em + .9rem + 2px);
		padding: .45rem 2.7rem .45rem .9rem;
		background-color: var(--ct-secondary-bg);
		border: var(--ct-border-width) solid var(--ct-border-color);
		border-radius: var(--ct-border-radius);
	}

	.select2-container--default .select2-selection--single .select2-selection__rendered {
		padding: 0;
		color: var(--ct-body-color);
		line-height: 1.5;
	}

	.select2-container--default .select2-selection--single .select2-selection__arrow {
		top: 0;
		right: .6rem;
		height: 100%;
	}

	.select2-container--default .select2-selection--multiple {
		min-height: calc(1.5em + .9rem + 2px);
		padding: .2rem .45rem;
		background-color: var(--ct-secondary-bg);
		border-color: var(--ct-border-color);
		border-radius: var(--ct-border-radius);
	}

	.select2-container--default.select2-container--focus .select2-selection--single,
	.select2-container--default.select2-container--focus .select2-selection--multiple,
	.select2-container--default.select2-container--open .select2-selection--single {
		border-color: #86b7fe;
		box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .12);
	}

	.sdk-data-table {
		min-width: 720px;
		margin-bottom: .5rem;
	}

	.sdk-data-table thead th {
		padding: .85rem 1rem;
		color: var(--ct-secondary-color);
		font-size: .75rem;
		font-weight: 700;
		letter-spacing: .035em;
		text-transform: uppercase;
		white-space: nowrap;
	}

	.sdk-data-table tbody td {
		padding: 1rem;
		vertical-align: middle;
	}

	.sdk-data-table tbody tr:last-child td {
		border-bottom-width: 0;
	}

	.sdk-data-table .sdk-primary {
		color: var(--ct-body-color);
		font-weight: 600;
		line-height: 1.35;
	}

	.sdk-data-table .sdk-secondary {
		display: block;
		margin-top: .25rem;
		color: var(--ct-secondary-color);
		font-size: .78rem;
		line-height: 1.4;
	}

	.sdk-data-table .sdk-actions {
		display: flex;
		flex-wrap: wrap;
		gap: .35rem;
		min-width: max-content;
	}

	.sdk-data-table .sdk-empty {
		padding: 3rem 1rem;
		color: var(--ct-secondary-color);
		text-align: center;
	}

	.sdk-record-list {
		display: grid;
		gap: .75rem;
	}

	.sdk-record-card {
		margin-bottom: 0;
		padding: 1rem;
		background: #f5f5f5;
		border: 1px solid var(--ct-border-color);
		border-radius: .65rem;
	}

	.sdk-record-card:hover {
		border-color: #b8c2cc;
		box-shadow: 0 .15rem .45rem rgba(49, 58, 70, .06);
	}

	.sdk-record-head,
	.sdk-record-main {
		display: flex;
		justify-content: space-between;
		gap: 1rem;
	}

	.sdk-record-head {
		align-items: flex-start;
		margin-bottom: .65rem;
		color: var(--ct-secondary-color);
		font-size: .78rem;
	}

	.sdk-record-main {
		align-items: center;
	}

	.sdk-record-title {
		margin: 0 0 .35rem;
		color: var(--ct-body-color);
		font-size: 1rem;
		font-weight: 700;
	}

	.sdk-record-meta {
		display: flex;
		flex-wrap: wrap;
		gap: .35rem 1rem;
		margin: 0;
		color: var(--ct-secondary-color);
		font-size: .8rem;
	}

	.sdk-record-meta strong {
		color: var(--ct-body-color);
		font-weight: 600;
	}

	.sdk-record-details {
		display: grid;
		grid-template-columns: repeat(3, minmax(120px, 1fr));
		gap: .6rem 1.25rem;
		flex: 1;
	}

	.sdk-record-detail {
		color: var(--ct-secondary-color);
		font-size: .78rem;
	}

	.sdk-record-detail strong {
		display: block;
		margin-top: .15rem;
		color: var(--ct-body-color);
		font-size: .86rem;
		font-weight: 600;
	}

	.sdk-record-actions {
		display: flex;
		flex-wrap: wrap;
		justify-content: flex-end;
		gap: .45rem;
		flex: 0 0 auto;
	}

	.sdk-record-empty {
		padding: 2.5rem 1rem;
		color: var(--ct-secondary-color);
		text-align: center;
		background: var(--ct-secondary-bg);
		border: 1px dashed var(--ct-border-color);
		border-radius: .65rem;
	}

	.sdk-pager-wrap {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		margin-top: 1rem;
	}

	.sdk-pager-wrap .pagination {
		margin-bottom: 0;
	}

	.sdk-page-size {
		display: flex;
		align-items: center;
		gap: .5rem;
		white-space: nowrap;
	}

	.sdk-page-size .form-select {
		width: auto;
		min-width: 74px;
	}

	@media (max-width: 767.98px) {

		.sdk-record-head,
		.sdk-record-main {
			flex-direction: column;
		}

		.sdk-record-details {
			width: 100%;
			grid-template-columns: 1fr;
		}

		.sdk-record-actions {
			width: 100%;
			justify-content: flex-start;
		}

		.sdk-record-actions .btn {
			flex: 1 1 auto;
		}

		.sdk-pager-wrap {
			align-items: stretch;
			flex-direction: column;
		}

		.sdk-pager-wrap .pagination {
			justify-content: center;
		}

		.sdk-page-size {
			justify-content: center;
		}
	}
</style>

<body>
	<!-- Begin page -->
	<div class="wrapper">

		<!-- Menu -->
		<!-- Sidenav Menu Start -->
		<div class="sidenav-menu">

			<?php
			$header_session = $this->session->userdata('admin');
			if (!is_array($header_session) || empty($header_session['id_user'])) {
				$header_session = $this->session->userdata('wali');
			}
			$header_is_wali = is_array($header_session) && (
				(int) ($header_session['id_level'] ?? 0) === 8
				|| strtoupper(trim((string) ($header_session['level'] ?? ''))) === 'WALI MURID'
			);
			?>
			<a href="<?= base_url($header_is_wali ? 'wali/dompet' : 'dashboard'); ?>" class="logo">
				<span class="logo-light">
					<span class="logo-lg"><img src="<?= base_url(); ?>assets/sdkreative.png" alt="logo"></span>
					<span class="logo-sm"><img src="<?= base_url(); ?>assets/sdkreative.png" alt="small logo"></span>
				</span>

				<span class="logo-dark">
					<span class="logo-lg"><img src="<?= base_url(); ?>assets/sdkreative.png" alt="dark logo"></span>
					<span class="logo-sm"><img src="<?= base_url(); ?>assets/sdkreative.png" alt="small logo"></span>
				</span>
			</a>

			<!-- Sidebar Hover Menu Toggle Button -->
			<button class="button-sm-hover">
				<i class="ri-circle-line align-middle"></i>
			</button>

			<!-- Sidebar Menu Toggle Button -->
			<button class="sidenav-toggle-button">
				<i class="ri-menu-5-line fs-20"></i>
			</button>

			<!-- Full Sidebar Menu Close Button -->
			<button class="button-close-fullsidebar">
				<i class="ti ti-x align-middle"></i>
			</button>

			<div data-simplebar>

				<!-- User -->
				<div class="sidenav-user">
					<div class="dropdown-center text-center">
						<a class="topbar-link dropdown-toggle text-reset drop-arrow-none px-2" data-bs-toggle="dropdown"
							type="button" aria-haspopup="false" aria-expanded="false">
							<img src="<?= base_url(); ?>assets/user.png" width="46" class="rounded-circle"
								alt="user-image">
							<span class="d-flex gap-1 sidenav-user-name my-2">
								<span>
									<span class="mb-0 fw-semibold lh-base fs-15" onclick="logout()">
										<i class="ri-shut-down-line"></i>
										Keluar
									</span>
								</span>
							</span>
						</a>
						<form id="web-logout-form" method="post" action="<?= base_url('login/keluar'); ?>" class="d-none">
							<input type="hidden" name="web_csrf_token" value="<?= html_escape($web_logout_csrf_token); ?>">
						</form>

					</div>
				</div>

				<?php
				$multi_level_session = $header_session;
				$multi_level_values = is_array($multi_level_session) ? (
				    !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
				        ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
				) : array();
				$unique_menu = static function ($rows) {
					$seen = array();
					$result = array();
					foreach ($rows as $row) {
						$key = (string) ($row['id_menu'] ?? ($row['path'] ?? ''));
						if (isset($seen[$key])) continue;
						$seen[$key] = true;
						$result[] = $row;
					}
					return $result;
				};
				$id_levels = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
				    $value = trim((string) $value);
				    return $value !== '' && ctype_digit($value) && (int) $value > 0;
				}))));
				$id_level_placeholders = implode(',', array_fill(0, max(1, count($id_levels)), '?'));
				if (!$id_levels) $id_levels = array(0);
				$menuDashboard = $menuAgenda = $menuRpp = $menuLaporan = null;
				$menuMasterData = $menuKurikulum = $menuPegawai = $menuPresensi = array();
				$menuKeuangan = $menuGaji = $menuPerencanaan = $menuDompet = $menuTagihanPembayaran = $menuTabungan = array();
				$menuPengaturan = $menuJurnal = $menuJurnalPegawai = $menuApproval = array();

				if (!$header_is_wali) {
					$menuDashboard = $this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Dashboard'", $id_levels)->row_array();
					$menuMasterData = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a LEFT JOIN conf_menu b ON a.id_menu=b.id WHERE a.id_level IN ($id_level_placeholders) AND a.group='Master' ORDER BY CAST(b.urut AS UNSIGNED) ASC, b.id ASC", $id_levels)->result_array());
					$menuKurikulum = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a left join conf_menu b on a.id_menu = b.id where id_level IN ($id_level_placeholders) and a.group = 'Kurikulum' order by b.urut asc", $id_levels)->result_array());
					$menuPegawai = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Pegawai'", $id_levels)->result_array());
					$menuPresensi = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Presensi'", $id_levels)->result_array());
					$menuKeuangan = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a LEFT JOIN conf_menu b on a.id_menu = b.id where id_level IN ($id_level_placeholders) and a.group = 'Keuangan' ORDER BY b.urut ASC", $id_levels)->result_array());
					$menuTagihanPembayaran = $unique_menu($this->db->query("SELECT a.*, b.group_2 AS menu_subgroup FROM conf_list_menu a LEFT JOIN conf_menu b ON a.id_menu=b.id WHERE a.id_level IN ($id_level_placeholders) AND a.group='Tagihan & Pembayaran' ORDER BY CASE b.group_2 WHEN 'Tagihan' THEN 1 WHEN 'Transaksi' THEN 2 WHEN 'Tunggakan' THEN 3 ELSE 4 END, CAST(b.urut AS UNSIGNED) ASC, b.id ASC", $id_levels)->result_array());
					$menuTabungan = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a LEFT JOIN conf_menu b ON a.id_menu=b.id WHERE a.id_level IN ($id_level_placeholders) AND a.group='Tabungan' ORDER BY CAST(b.urut AS UNSIGNED) ASC, b.id ASC", $id_levels)->result_array());
					$menuGaji = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a LEFT JOIN conf_menu b on a.id_menu = b.id where id_level IN ($id_level_placeholders) and a.group = 'Gaji' ORDER BY b.urut ASC", $id_levels)->result_array());
					$menuPerencanaan = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Perencanaan'", $id_levels)->result_array());
					$menuDompet = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a LEFT JOIN conf_menu b on a.id_menu = b.id where id_level IN ($id_level_placeholders) and a.group = 'Dompet Digital' ORDER BY b.urut ASC", $id_levels)->result_array());
					$menuPengaturan = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a LEFT JOIN conf_menu b ON a.id_menu=b.id WHERE a.id_level IN ($id_level_placeholders) AND a.group='Pengaturan' ORDER BY CAST(b.urut AS UNSIGNED) ASC, b.id ASC", $id_levels)->result_array());
					$menuAgenda = $this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Agenda'", $id_levels)->row_array();
					$menuRpp = $this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Rpp'", $id_levels)->row_array();
					$menuJurnal = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a left join conf_menu b on a.id_menu = b.id where a.id_level IN ($id_level_placeholders) and a.group = 'E-Jurnal' order by b.urut asc", $id_levels)->result_array());
					$menuJurnalPegawai = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a left join conf_menu b on a.id_menu = b.id where a.id_level IN ($id_level_placeholders) and a.group = 'Jurnal Pegawai' order by b.urut asc", $id_levels)->result_array());
					$menuApproval = $unique_menu($this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Approval'", $id_levels)->result_array());
					$menuLaporan = $this->db->query("SELECT a.* FROM conf_list_menu a where id_level IN ($id_level_placeholders) and a.group = 'Laporan'", $id_levels)->row_array();
				}


				?>

				<ul class="side-nav">
					<?php if ($header_is_wali): ?>
						<li class="side-nav-item px-3 pt-2 pb-1"><span class="text-uppercase text-muted fs-12 fw-bold">Dompet</span></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/dompet') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-dashboard-line"></i></span><span class="menu-text">Dashboard Dompet</span></a></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/dompet/history-belanja') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-shopping-bag-3-line"></i></span><span class="menu-text">History Belanja Siswa</span></a></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/dompet/history-isi-saldo') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-history-line"></i></span><span class="menu-text">History Isi Saldo</span></a></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/dompet/setting-limit') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-equalizer-line"></i></span><span class="menu-text">Setting Limit Transaksi</span></a></li>

						<li class="side-nav-item px-3 pt-3 pb-1"><span class="text-uppercase text-muted fs-12 fw-bold">Tagihan dan Tabungan</span></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/tagihan/dashboard') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-dashboard-3-line"></i></span><span class="menu-text">Dashboard </span></a></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/tagihan/tagihan') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-file-list-3-line"></i></span><span class="menu-text">Tagihan Anak</span></a></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/pembayaran/riwayat_pembayaran') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-receipt-line"></i></span><span class="menu-text">Riwayat Pembayaran</span></a></li>
						<li class="side-nav-item"><a href="<?= base_url('wali/tabungan/tabungan') ?>" class="side-nav-link"><span class="menu-icon"><i class="ri-safe-2-line"></i></span><span class="menu-text">Tabungan Anak</span></a></li>
					<?php endif; ?>
					<?php if ($menuDashboard): ?>
						<li class="side-nav-item ">
							<a href="<?= base_url("/" . $menuDashboard['path']) ?>" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-dashboard"></i></span>
								<span class="menu-text"> Dashboard </span>
							</a>
						</li>
					<?php endif; ?>
					<?php if ($menuMasterData): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#sidebarPages" aria-expanded="false"
								aria-controls="sidebarPages" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
								<span class="menu-text"> Master Data </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="sidebarPages">
								<ul class="sub-menu">
									<?php foreach ($menuMasterData as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuKurikulum): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#kurikulum" aria-expanded="false" aria-controls="kurikulum"
								class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-folder"></i></span>
								<span class="menu-text"> Kurikulum </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="kurikulum">
								<ul class="sub-menu">
									<?php foreach ($menuKurikulum as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuPegawai): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#data-pegawai" aria-expanded="false"
								aria-controls="data-pegawai" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-users"></i></span>
								<span class="menu-text"> Kepegawaian </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="data-pegawai">
								<ul class="sub-menu">
									<?php foreach ($menuPegawai as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuGaji): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#gaji" aria-expanded="false" aria-controls="gaji"
								class="side-nav-link">
								<span class="menu-icon"><i class="ri-wallet-line"></i></span>
								<span class="menu-text"> Gaji </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="gaji">
								<ul class="sub-menu">
									<?php foreach ($menuGaji as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuPresensi): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#data-presensi" aria-expanded="false"
								aria-controls="data-presensi" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-clock"></i></span>
								<span class="menu-text"> Presensi </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="data-presensi">
								<ul class="sub-menu">
									<?php foreach ($menuPresensi as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuJurnal): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#jurnal" aria-expanded="false" aria-controls="jurnal"
								class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-file"></i></span>
								<span class="menu-text"> Jurnal Guru </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="jurnal">
								<ul class="sub-menu">
									<?php foreach ($menuJurnal as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuJurnalPegawai): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#jurnal_pegawai" aria-expanded="false"
								aria-controls="jurnal_pegawai" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-folder"></i></span>
								<span class="menu-text"> Jurnal Pegawai </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="jurnal_pegawai">
								<ul class="sub-menu">
									<?php foreach ($menuJurnalPegawai as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>

					<?php if ($menuApproval): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#approval" aria-expanded="false" aria-controls="approval"
								class="side-nav-link">
								<span class="menu-icon"><i class="ri-chat-check-line"></i></span>
								<span class="menu-text"> Approval </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="approval">
								<ul class="sub-menu">
									<?php foreach ($menuApproval as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuRpp): ?>
						<li class="side-nav-item ">
							<a href="<?= base_url("/" . $menuRpp['path']) ?>" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-file"></i></span>
								<span class="menu-text"> <?= $menuRpp['name'] ?> </span>
							</a>
						</li>
					<?php endif; ?>
					<?php if ($menuAgenda): ?>
						<li class="side-nav-item ">
							<a href="<?= base_url("/" . $menuAgenda['path']) ?>" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-activity"></i></span>
								<span class="menu-text"> <?= $menuAgenda['name'] ?> </span>
							</a>
						</li>
					<?php endif; ?>
					<?php if ($menuKeuangan): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#keuangan" aria-expanded="false" aria-controls="keuangan"
								class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-cash"></i></span>
								<span class="menu-text"> Keuangan </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="keuangan">
								<ul class="sub-menu">
									<?php foreach ($menuKeuangan as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuTagihanPembayaran): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#tagihan-pembayaran" aria-expanded="false" aria-controls="tagihan-pembayaran" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-receipt"></i></span>
								<span class="menu-text"> Tagihan & Pembayaran </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="tagihan-pembayaran">
								<ul class="sub-menu">
									<?php $subgroupAktif = '';
									foreach ($menuTagihanPembayaran as $menu): ?>
										<?php $sg = trim((string)($menu['menu_subgroup'] ?? ''));
										if ($sg !== '' && $sg !== $subgroupAktif): $subgroupAktif = $sg; ?>
											<li class="px-3 pt-2 pb-1"><small class="text-uppercase text-muted fw-semibold"><?= html_escape($sg) ?></small></li>
										<?php endif; ?>
										<li class="side-nav-item">
											<a href="<?= base_url('/' . html_escape($menu['path'])) ?>" class="side-nav-link"><span class="menu-text"><?= html_escape($menu['name']) ?></span></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</li>
					<?php endif; ?>
					<!-- <php if ($menuTabungan): $menuTabunganUtama = $menuTabungan[0]; ?>
						<li class="side-nav-item">
							<a href="<= base_url('/' . html_escape($menuTabunganUtama['path'])) ?>" class="side-nav-link">
								<span class="menu-icon"><i class="ri-safe-2-line"></i></span>
								<span class="menu-text"> <= html_escape($menuTabunganUtama['name']) ?> </span>
							</a>
						</li>
					<php endif; ?> -->
					<?php if ($menuTabungan): ?>
						<?php if (count($menuTabungan) === 1): $menuTabunganUtama = $menuTabungan[0]; ?>
							<li class="side-nav-item">
								<a href="<?= base_url('/' . html_escape($menuTabunganUtama['path'])) ?>" class="side-nav-link">
									<span class="menu-icon"><i class="ri-safe-2-line"></i></span>
									<span class="menu-text"> <?= html_escape($menuTabunganUtama['name']) ?> </span>
								</a>
							</li>
						<?php else: ?>
							<li class="side-nav-item">
								<a data-bs-toggle="collapse" href="#menuTabunganSiswa"
									aria-expanded="false" aria-controls="menuTabunganSiswa"
									class="side-nav-link">
									<span class="menu-icon"><i class="ri-safe-2-line"></i></span>
									<span class="menu-text">Tabungan</span>
									<span class="menu-arrow"></span>
								</a>
								<div class="collapse" id="menuTabunganSiswa">
									<ul class="sub-menu">
										<?php foreach ($menuTabungan as $menu): ?>
											<li class="side-nav-item">
												<a href="<?= base_url('/' . html_escape($menu['path'])) ?>"
													class="side-nav-link">
													<span class="menu-text"><?= html_escape($menu['name']) ?></span>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ($menuPerencanaan): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#perencanaan" aria-expanded="false" aria-controls="perencanaan"
								class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-calendar-stats"></i></span>
								<span class="menu-text"> Perencanaan </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="perencanaan">
								<ul class="sub-menu">
									<?php foreach ($menuPerencanaan as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuDompet): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#dompet-digital" aria-expanded="false"
								aria-controls="dompet-digital" class="side-nav-link">
								<span class="menu-icon"><i class="ri-wallet-3-line"></i></span>
								<span class="menu-text"> Dompet Digital </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="dompet-digital">
								<ul class="sub-menu">
									<?php foreach ($menuDompet as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url('/' . html_escape($menu['path'])) ?>" class="side-nav-link">
												<span class="menu-text"><?= html_escape($menu['name']) ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</li>
					<?php endif; ?>
					<?php if ($menuLaporan): ?>
						<li class="side-nav-item ">
							<a href="<?= base_url("/" . $menuLaporan['path']) ?>" class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-file"></i></span>
								<span class="menu-text"> <?= $menuLaporan['name'] ?> </span>
							</a>
						</li>
					<?php endif; ?>
					<?php if ($menuPengaturan): ?>
						<li class="side-nav-item">
							<a data-bs-toggle="collapse" href="#pengaturan" aria-expanded="false" aria-controls="pengaturan"
								class="side-nav-link">
								<span class="menu-icon"><i class="ti ti-settings"></i></span>
								<span class="menu-text"> Pengaturan </span>
								<span class="menu-arrow"></span>
							</a>
							<div class="collapse" id="pengaturan">
								<ul class="sub-menu">
									<?php foreach ($menuPengaturan as $menu): ?>
										<li class="side-nav-item">
											<a href="<?= base_url("/" . $menu['path']) ?>" class="side-nav-link">
												<span class="menu-text"><?= $menu['name'] ?></span>
											</a>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>
						</li>
					<?php endif; ?>


				</ul>


				<div class="clearfix"></div>
			</div>
		</div>
		<!-- Sidenav Menu End -->


		<!-- Topbar Start -->
		<header class="app-topbar" id="header">
			<div class="page-container topbar-menu">
				<div class="d-flex align-items-center gap-2">

					<!-- Brand Logo -->
					<!-- <a href="index.html" class="logo">
						<span class="logo-light">
							<span class="logo-lg"><img src="<?= base_url(); ?>assets/sdkreative.png" alt="logo"></span>
							<span class="logo-sm"><img src="<?= base_url(); ?>assets/sdkreative.png"
									alt="small logo"></span>
						</span>

						<span class="logo-dark">
							<span class="logo-lg"><img src="<?= base_url(); ?>assets/sdkreative.png"
									alt="dark logo"></span>
							<span class="logo-sm"><img src="<?= base_url(); ?>assets/sdkreative.png"
									alt="small logo"></span>
						</span>
					</a> -->

					<!-- Sidebar Menu Toggle Button -->
					<button class="sidenav-toggle-button px-2">
						<i class="ri-menu-5-line fs-24"></i>
					</button>

					<!-- Horizontal Menu Toggle Button -->
					<button class="topnav-toggle-button px-2" data-bs-toggle="collapse"
						data-bs-target="#topnav-menu-content">
						<i class="ri-menu-5-line fs-24"></i>
					</button>

					<!-- Topbar Page Title -->
					<div class="topbar-item d-none d-md-flex px-2">

						<div>
							<h4 class="page-title fs-20 fw-semibold mb-0"><?= $title; ?></h4>

						</div>



					</div>

				</div>

				<div class="d-flex align-items-center gap-2">
					<div class="topbar-item d-none d-sm-flex">
						<button class="topbar-link" id="light-dark-mode" type="button">
							<i class="ri-moon-line light-mode-icon fs-22"></i>
							<i class="ri-sun-line dark-mode-icon fs-22"></i>
						</button>
					</div>

					<!-- User Dropdown -->
					<div class="topbar-item nav-user">
						<div class="dropdown">

							<span class="d-lg-flex flex-column gap-1 d-none">
								<h5 class="my-0"><?= html_escape($header_session['nama_lengkap'] ?? 'Pengguna'); ?></h5>
							</span>

						</div>
					</div>
				</div>
			</div>
		</header>
		<!-- Topbar End -->

		<!-- Search Modal -->
		<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content bg-transparent">
					<form>
						<div class="card mb-1">
							<div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
								<i class="ri-search-line fs-22"></i>
								<input type="search" class="form-control border-0" id="search-modal-input"
									placeholder="Search for actions, people,">
								<button type="submit" class="btn p-0" data-bs-dismiss="modal"
									aria-label="Close">[esc]</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="page-content">
			<div class="page-container">

				<script>
					function logout() {
						Swal.fire({
							title: 'Yakin ingin keluar?',
							icon: 'warning',
							showCancelButton: true,
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
							confirmButtonText: 'Ya',
							cancelButtonText: 'Tidak'
						}).then((result) => {
							if (result.isConfirmed) {
								document.getElementById('web-logout-form').submit();
							}
						})
					}
				</script>