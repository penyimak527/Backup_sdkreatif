<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Edit Data <?= $title; ?></h4>
		<a href="<?= base_url('admin/presensi/setting_presensi'); ?>" class="btn btn-sm btn-outline-danger"><i
				class="ri-arrow-left-line"></i>Kembali</a>
	</div>
	<div class="card-body">
		<form id="form-edit" enctype="multipart/form-data">
			<input type="hidden" id="id_setting" name="id_setting" value="<?= $id; ?>">
			<div class="mb-3">
				<label for="aturan" class="form-label">Nama Aturan</label>
				<input type="text" name="nama_aturan" class="form-control" placeholder="Nama Aturan ..."
					value="<?php echo $data_row['nama_aturan'] ?>" />
			</div>
			<div class="mb-3">
				<label for="jam" class="form-label">Jam Masuk</label>
				<input type="text" name="jam_masuk" class="form-control" placeholder="Jam Masuk ..."
					onkeyup="formatTimeInput(this)" maxlength="8" value="<?php echo $data_row['jam_masuk']; ?>" />
			</div>
			<div class="mb-3">
				<label for="jam" class="form-label">Jam Pulang</label>
				<input type="text" name="jam_pulang" class="form-control" placeholder="Jam Pulang ..."
					onkeyup="formatTimeInput(this)" maxlength="8" value="<?php echo $data_row['jam_pulang'] ?>" />
			</div>
			<div class="mb-3">
				<label for="status" class="form-label">Status</label>
				<select name="status" class="form-control" id="status_peraturan">
					<option value="">Status</option>
					<option value="aktif" <?= ($data_row['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
					<option value="tidak aktif" <?= ($data_row['status'] == 'tidak aktif') ? 'selected' : '' ?>>Tidak Aktif
					</option>
				</select>
			</div>
			<div class="mb-3">
				<label for="status" class="form-label">Status Jam</label>
				<select name="status_jam" class="form-control" id="status_peraturan_jam">
					<option value="">Pilih Status Jam</option>
					<option value="Kepala Sekolah" <?= ($data_row['status_jam'] == 'Kepala Sekolah') ? 'selected' : ''?>>Kepala Sekolah</option>
					<option value="Wakil Kepala Sekolah" <?= ($data_row['status_jam'] == 'Wakil Kepala Sekolah') ? 'selected' : ''?>>Wakil Kepala Sekolah</option>
					<option value="umum" <?= ($data_row['status_jam'] == 'umum') ? 'selected' : ''?>>Umum</option>
				</select>
			</div>
			<button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
		</form>
	</div>
</div>



<script>
	$(document).ready(function () {
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var formData = form.serialize();
			const status = $('#status_peraturan').val();
			if (status == '') {
				Swal.fire({
					icon: 'warning',
					title: 'Status Kosong',
					text: 'Status Wajib diisi!',
				}).then((result) => {
					$('#btn-simpan').prop('disabled', false);
					$('#btn-simpan').html('Simpan');

				});
				return;
			}
			$.ajax({
				url: '<?= base_url('admin/presensi/setting_presensi/edit'); ?>',
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
								window.location.href = "<?= base_url('admin/presensi/setting_presensi'); ?>";
							}
						})

					}
				}
			})
		})
	})
	function formatTimeInput(inputElement) {
		let val = inputElement.value.replace(/\D/g, ''); // Hanya angka

		if (val.length > 6) val = val.slice(0, 6); // Maksimal 6 digit

		let jam = val.slice(0, 2);
		let menit = val.slice(2, 4);
		let detik = val.slice(4, 6);

		let formatted = jam;
		if (val.length >= 3) formatted += ':' + menit;
		if (val.length >= 5) formatted += ':' + detik;

		inputElement.value = formatted;
	}

</script>