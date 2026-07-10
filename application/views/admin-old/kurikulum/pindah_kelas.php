<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
	</div>
	<div class="card-body">
		<div class="row mb-2">
			<div class="col-md-2">
				<select name="id_kelas_kiri" class="form-select" id="id_kelas_kiri">
					<option value="">Pilih Kelas</option>
					<?php foreach ($kelas_kiri as $k): ?>
						<option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-2">
				<select name="id_periode_kiri" class="form-select" id="id_periode_kiri">
					<option value="">Pilih Tahun Ajaran</option>
					<?php foreach ($periode_kiri as $k): ?>
						<option value="<?= $k['id'] ?>"><?= $k['periode'] ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-3">

			</div>
			<div class="col-md-2">
				<select name="id_kelas_kanan" class="form-select" id="id_kelas_kanan">
					<option value="">Pilih Kelas</option>
					<?php foreach ($kelas_kiri as $k): ?>
						<option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-2">
				<select name="id_periode_kanan" class="form-select" id="id_periode_kanan">
					<option value="">Pilih Tahun Ajaran</option>
					<?php foreach ($periode_kiri as $k): ?>
						<option value="<?= $k['id'] ?>"><?= $k['periode'] ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-1">
				<button type="button" class="btn btn-sm btn-outline-primary" onclick="pindah_kelas()">Preview <i
						class="ri-eye-line"></i></button>
			</div>
		</div>

		<div class="row">
			<div class="col-md-5">

				<table class="table table-bordered m-b-0" id="tabel-kelas-kiri">
					<thead>
						<tr>
							<th style="text-align: center;">No</th>
							<th>NIS</th>
							<th>Nama Siswa</th>
							<th>Pilih Semua <input type="checkbox" name="id_siswa_all_kiri" id="id-siswa-all-kiri"
									value="1"></th>
						</tr>
					</thead>
					<tbody>
						<tr id="message-kiri">
							<td colspan="4" style="text-align:center;">
								<p style="margin-top:5px;">Silahkan pilih filter terlebih dahulu.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-2" style="text-align: center;">
				<button type="button" id="btn_pindah_ke_kanan" disabled=""
					class="btn btn-info waves-effect waves-light text-white"> Pindah <i class="fa fa-arrow-right"
						aria-hidden="true"></i></button></br></br>
				<button type="button" id="btn_pindah_ke_kiri" disabled=""
					class="btn btn-success waves-effect waves-light text-white"><i class="fa fa-arrow-left"
						aria-hidden="true"></i> Pindah </button>
			</div>
			<div class="col-md-5">

				<table class="table table-bordered m-b-0" id="tabel-kelas-kanan">
					<thead>
						<tr>
							<th style="text-align: center;">No</th>
							<th>NIS</th>
							<th>Nama Siswa</th>
							<th>Pilih Semua <input type="checkbox" name="id_siswa_all_kanan" id="id-siswa-all-kanan"
									value="1"></th>
						</tr>
					</thead>
					<tbody>
						<tr id="message-kanan">
							<td colspan="4" style="text-align:center;">
								<p style="margin-top:5px;">Silahkan pilih filter terlebih dahulu.</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function () {

		$(`#id-siswa-all-kiri`).change(function () {
			if ($(this).is(':checked')) {
				$(`.checkbox-kiri`).prop('checked', true)
			} else {
				$(`.checkbox-kiri`).prop('checked', false)
			}
		})

		$(`#id-siswa-all-kanan`).change(function () {
			if ($(this).is(':checked')) {
				$(`.checkbox-kanan`).prop('checked', true)
			} else {
				$(`.checkbox-kanan`).prop('checked', false)
			}
		})
		$('#btn_pindah_ke_kanan').click(function () {
			let id_kelas_kanan = $(`#id_kelas_kanan`).val()
			let id_periode_kanan = $(`#id_periode_kanan`).val()


			let selectedCheckboxes = $('input[name="checkbox_kiri[]"]:checked').map(function () {
				return $(this).val();
			}).get();

			if (selectedCheckboxes.length === 0) {
				alert('Pilih setidaknya satu siswa.');
				return;
			}

			$.ajax({
				url: '<?php echo base_url(); ?>admin/kurikulum/pindah_kelas/pindah_ke_kanan',
				data: {
					checkbox_kiri: selectedCheckboxes,
					id_kelas_kanan: id_kelas_kanan,
					id_periode_kanan: id_periode_kanan
				},
				type: "POST",
				dataType: "json",
				success: function (response) {
					if (response.status === "success") {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message
						}).then(() => {
							pindah_kelas();
							$(`#id-siswa-all-kiri`).prop('checked', false)
							$(`#id-siswa-all-kanan`).prop('checked', false)
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Gagal',
							text: response.message
						});
					}
				},
				error: function () {
					Swal.fire({
						icon: 'error',
						title: 'Kesalahan',
						text: 'Terjadi kesalahan. Silakan coba lagi.'
					});
				}
			});
		});

		$('#btn_pindah_ke_kiri').click(function () {
			let id_kelas_kiri = $(`#id_kelas_kiri`).val()
			let id_periode_kiri = $(`#id_periode_kiri`).val()

			let selectedCheckboxes = $('input[name="checkbox_kanan[]"]:checked').map(function () {
				return $(this).val();
			}).get();

			if (selectedCheckboxes.length === 0) {
				alert('Pilih setidaknya satu siswa.');
				return;
			}

			$.ajax({
				url: '<?php echo base_url(); ?>admin/kurikulum/pindah_kelas/pindah_ke_kiri',
				data: {
					checkbox_kanan: selectedCheckboxes,
					id_kelas_kiri: id_kelas_kiri,
					id_periode_kiri: id_periode_kiri
				},
				type: "POST",
				dataType: "json",
				success: function (response) {
					if (response.status === "success") {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message
						}).then(() => {
							pindah_kelas();
							$(`#id-siswa-all-kiri`).prop('checked', false)
							$(`#id-siswa-all-kanan`).prop('checked', false)
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Gagal',
							text: response.message
						});
					}
				},
				error: function () {
					Swal.fire({
						icon: 'error',
						title: 'Kesalahan',
						text: 'Terjadi kesalahan. Silakan coba lagi.'
					});
				}
			});
		});

	})

	function pindah_kelas() {
		var id_kelas_kiri = $('#id_kelas_kiri').val()
		var id_periode_kiri = $('#id_periode_kiri').val()
		var id_kelas_kanan = $('#id_kelas_kanan').val()
		var id_periode_kanan = $('#id_periode_kanan').val()


		$.ajax({
			url: '<?= base_url('admin/kurikulum/pindah_kelas/pindah_kelas_result'); ?>',
			type: 'POST',
			data: { id_kelas_kiri: id_kelas_kiri, id_periode_kiri: id_periode_kiri, id_kelas_kanan: id_kelas_kanan, id_periode_kanan: id_periode_kanan },
			dataType: 'JSON',
			success: function (result) {


				var no_kiri = 0, no_kanan = 0;
				var table_kiri = "", table_kanan = "";


				if (result.data_kiri.length === 0) {
					table_kiri = `<tr><td colspan="4" style="text-align:center;">Data Kosong</td></tr>`;
				} else {
					result.data_kiri.forEach(function (row) {
						table_kiri += `<tr>
																	<td style="text-align:center;">${++no_kiri}</td>
																	<td style="text-align:center;">${row.nis}</td>
																	<td style="text-align:center;">${row.nama_siswa}</td>
																	<td style="text-align:center;">
																			<input type="checkbox" class="checkbox-kiri" name="checkbox_kiri[]" value="${row.id}">
																	</td>
															</tr>`;
					});
				}

				if (result.data_kanan.length === 0) {
					table_kanan = `<tr><td colspan="4" style="text-align:center;">Data Kosong</td></tr>`;
				} else {
					result.data_kanan.forEach(function (row) {
						table_kanan += `<tr>
																	<td style="text-align:center;">${++no_kanan}</td>
																	<td style="text-align:center;">${row.nis}</td>
																	<td style="text-align:center;">${row.nama_siswa}</td>
																	<td style="text-align:center;">
																			<input type="checkbox" class="checkbox-kanan" name="checkbox_kanan[]" value="${row.id}">
																	</td>
															</tr>`;

					});
				}
				$('#message-kiri').remove();
				$('#message-kanan').remove();
				$('#tabel-kelas-kiri tbody').html(table_kiri);
				$('#tabel-kelas-kanan tbody').html(table_kanan);
				$(`#id-siswa-all-kiri`).prop('checked', false);
				$(`#id-siswa-all-kanan`).prop('checked', false);
				$('#btn_pindah_ke_kanan').prop('disabled', false);
				$('#btn_pindah_ke_kiri').prop('disabled', false);
			}
		});
	}

	function editKelas(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_kelas').val(item.id);
		$('#kelas').val(item.nama_kelas);
		$('#kode_kelas').val(item.kode_kelas);
	}

	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_kelas tbody tr");
		}

		window.tp = new Pagination('#pagination', {
			itemsCount: $selector.length,
			pageSize: parseInt(jumlah_tampil),
			onPageChange: function (paging) {
				let start = paging.pageSize * (paging.currentPage - 1);
				let end = start + paging.pageSize;
				let $rows = $selector;

				$rows.hide();
				for (let i = start; i < end; i++) {
					$rows.eq(i).show();
				}
			}
		});
	}

	function hapus(id) {
		Swal.fire({
			title: 'Hapus Data',
			text: 'Anda yakin ingin menghapus data ini?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Ya',
			cancelButtonText: 'Tidak'
		}).then((result) => {
			if (result.value) {
				$.ajax({
					type: 'POST',
					url: `<?= base_url(); ?>admin/kurikulum/kelas/hapus`,
					data: {
						id: id
					},
					dataType: 'json',
					success: function (data) {
						if (data == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Data berhasil dihapus',
							})
							kelas();
						}

					}
				})
			}
		})
	}


</script>