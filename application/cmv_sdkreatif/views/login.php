<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Halaman <?= $title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
	<meta content="Coderthemes" name="author" />

	<!-- App favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">

	<!-- Theme Config Js -->
	<script src="<?= base_url(); ?>assets/js/config.js"></script>

	<!-- Vendor css -->
	<link href="<?= base_url(); ?>assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

	<!-- App css -->
	<link href="<?= base_url(); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

	<!-- Icons css -->
	<link href="<?= base_url(); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>

	<div class="auth-bg d-flex min-vh-100">
		<div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
			<div class="col-xxl-4 col-lg-5 col-md-6" style="margin: auto 0;">
				<div class="">
					<a href="index.html" class="auth-brand d-flex justify-content-center mb-2">
						<img src="assets/sdkreative.png" alt="dark logo" height="200" class="logo-dark">
						<img src="assets/sdkreative.png" alt="logo light" height="200" class="logo-light">
					</a>

					<p class="fw-semibold mb-4 text-center text-muted fs-15">SD KREATIF MUHAMMADIYAH LUMAJANG</p>

					<div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

						<h4 class="fw-semibold mb-3 fs-18">Silahkan login dengan akun anda</h4>
						<?php echo $this->session->flashdata('message'); ?>
						<form action="<?= base_url('login/masuk') ?>" method="post" class="text-start mb-3">
							<div class="mb-3">
								<label class="form-label" for="username">Username</label>
								<input type="text" name="username" class="form-control" placeholder="Username">
							</div>

							<div class="mb-3">
								<label class="form-label" for="password">Password</label>
								<input type="password" name="password" class="form-control" placeholder="Password">
							</div>
							<div class="d-grid">
								<button class="btn btn-primary fw-semibold" type="submit">Masuk</button>
							</div>
						</form>

					</div>
					<p class="mt-4 text-center mb-0">
						<script>document.write(new Date().getFullYear())</script> © SD Kreatif - By <span
							class="fw-bold text-decoration-underline text-uppercase text-reset fs-12">Pyramid
							Soft</span>
					</p>
				</div>

			</div>
		</div>
	</div>

	<!-- Vendor js -->
	<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

	<!-- App js -->
	<script src="<?= base_url(); ?>assets/js/app.js"></script>

</body>

</html>