<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Edit Data <?= $title; ?></h4>
		<a href="<?= base_url('admin/pegawai/pegawai_jabatan'); ?>" class="btn btn-sm btn-outline-danger"><i
				class="ri-arrow-left-line"></i>Kembali</a>
	</div>
	<div class="card-body">
		<form id="form-edit" enctype="multipart/form-data">
			<input type="hidden" id="id_pegawai" name="id_pegawai" value="<?= $id_pegawai; ?>">
			<div class="mb-3">
				<label for="pegawai" class="form-label">Nama Pegawai</label>
				<input type="text" name="nama_pegawai" class="form-control" placeholder="Nama Pegawai ..." />
			</div>
			<div class="mb-3">
				<label for="pegawai" class="form-label">Jabatan</label>
				<select name="id_jabatan[]" class="select2 form-control select2-multiple" data-toggle="select2"
					multiple="multiple" onchange="mapel()">
					<?php

					foreach ($jabatan as $j) { ?>
						<option value="<?= $j['id']; ?>" <?= in_array($j['id'], $select_jabatan) ? 'selected' : '' ?>>
							<?= $j['jabatan']; ?>
						</option>
					<?php } ?>
				</select>
			</div>
			<div class="mb-3" id="nip" style="display: none;">
				<label for="pegawai" class="form-label">NIP</label>
				<input type="text" name="nip" class="form-control" value="<?= $nip['nip'] ?? '' ?>" />
			</div>
			<div class="mb-3" id="mapel" style="display: none;">
				<label for="id_mapel" class="form-label">Mata Pelajaran yang diajarkan</label>
				<select name="id_mapel[]" class="select2 form-control select2-multiple" data-toggle="select2"
					multiple="multiple">
					<?php foreach ($mapel as $m) { ?>
						<option value="<?= $m['id']; ?>" <?= in_array($m['id'], $select_mapel) ? 'selected' : '' ?>>
							<?= $m['mapel']; ?>
						</option> <?php } ?>
				</select>
			</div>
			<div class="mb-3">
				<label for="pegawai" class="form-label">Jenis Kelamin</label>
				<select name="jk" class="form-control">
					<option value=""> Jenis Kelamin</option>
					<option value="Laki-laki">Laki-laki</option>
					<option value="Perempuan">Perempuan</option>
				</select>
			</div>
			<div class="mb-3">
				<label for="tempat_lahir" class="form-label">Tempat Lahir</label>
				<input type="text" name="tempat_lahir" class="form-control" placeholder="Tempat Lahir ..." />
			</div>
			<div class="mb-3">
				<label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
				<input type="text" id="flatpicker" name="tanggal_lahir" class="form-control"
					placeholder="Tanggal Lahir ..." />
			</div>
			<div class="mb-3">
				<label for="no_tlp" class="form-label">No Telepon</label>
				<input type="text" name="no_tlp" class="form-control" placeholder="No Telepon ..." />
			</div>
			<button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
		</form>
	</div>
</div>



<script>
	$(document).ready(function () {
		pegawai();
		mapel();
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/pegawai/izin/edit'); ?>',
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
								window.location.href = "<?= base_url('admin/pegawai/izin'); ?>";
							}
						})

					}
				}
			})
		})

	})

	function pegawai() {

		var id_pegawai = $('#id_pegawai').val();

		$.ajax({
			url: '<?= base_url('admin/pegawai/izin/pegawai_edit'); ?>',
			type: 'POST',
			data: {
				id_pegawai
			},
			dataType: 'JSON',
			success: function (data) {

				console.log(data);
				$('#form-edit select[name="jk"]').html(`
					<option value="Laki-laki" ${data.jk == 'Laki-laki' ? 'selected' : ''}>Laki-laki</option>
					<option value="Perempuan" ${data.jk == 'Perempuan' ? 'selected' : ''}>Perempuan</option>
					`);
				$('#form-edit input[name="nama_pegawai"]').val(data.nama_pegawai);
				$('#form-edit input[name="tempat_lahir"]').val(data.tempat_lahir);
				$('#form-edit input[name="tanggal_lahir"]')[0]._flatpickr.setDate(data.tanggal_lahir, true);
				$('#form-edit input[name="no_tlp"]').val(data.no_tlp);
			}
		});
	}

	function mapel() {
		let selected = $('select[name="id_jabatan[]"]').val();
		if (selected.includes("1")) {
			$('#mapel').show();
			$('#nip').show();
		} else {
			$('#mapel').hide();
			$('#nip').hide();
		}
	}

</script>
