<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Edit Data <?= $title; ?></h4>
		<a href="<?= base_url('admin/user/user'); ?>" class="btn btn-sm btn-outline-danger"><i
				class="ri-arrow-left-line"></i>Kembali</a>
	</div>
	<div class="card-body">
		<form id="form-edit" enctype="multipart/form-data">
			<input type="hidden" id="id_user" name="id_user" value="<?= $id_user; ?>">
			<div class="mb-3">
				<label for="id_user" class="form-label">Nama Pegawair</label>
				<select name="id_pegawai" class="form-control" data-choices name="choices-single-default"
					id="choices-single-default">
					<option value="">Pilih Pegawai</option>
					<?php foreach ($pegawai as $p): ?>
						<option value="<?= $p['id']; ?>" <?php if ($p['id'] == $user['id_pegawai'])
							  echo "selected"; ?>>
							<?= $p['nama_pegawai']; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mb-3">
				<label for="id_user" class="form-label">Level</label>
				<select name="id_level" class="select2 form-control select2-multiple" data-toggle="select2"
					multiple="multiple" data-placeholder="Choose ...">
					<?php foreach ($level as $l): ?>
						<option value="<?= $l['id']; ?>" <?= $l['id'] == $user['id_level'] ? 'selected' : ''; ?>>
							<?= $l['level']; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mb-3">
				<label for="username" class="form-label">Username</label>
				<input type="text" name="username" class="form-control" />
			</div>
			<div class="mb-3">
				<label for="password" class="form-label">Password</label>
				<input type="password" name="password" class="form-control" placeholder="Password" />
			</div>
			<div class="mb-3">
				<label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
				<input type="password" name="konfirmasi_password" class="form-control"
					placeholder="Konfirmasi Password" />
			</div>
			<button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
		</form>
	</div>
</div>



<script>
	$(document).ready(function () {
		user();
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/pengaturan/user/edit'); ?>',
				type: 'POST',
				data: formData,
				success: function (data) {
					$("#edit").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						}).then((result) => {
							if (result.value) {
								window.location.href = "<?= base_url('admin/pengaturan/user'); ?>";
							}
						})

					}
				}
			})
		})

	})

	function user() {

		var id_user = $('#id_user').val();
		console.log(id_user);
		$.ajax({
			url: '<?= base_url('admin/pengaturan/user/user_edit'); ?>',
			type: 'POST',
			data: {
				id_user
			},
			dataType: 'JSON',
			success: function (data) {

				$('#form-edit input[name="nama_user"]').val(data.nama_user);
				$('#form-edit input[name="username"]').val(data.username);
			}
		});
	}


</script>
