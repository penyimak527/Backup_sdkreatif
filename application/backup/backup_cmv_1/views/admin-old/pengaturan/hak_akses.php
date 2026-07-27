<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<div>
			<button type="button" class="btn btn-sm btn-outline-primary" onclick="tambah_hak_akses()"><i
					class="ri-add-line"></i>Tambah</button>
			<button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus_hak_akses()">
				<i class="ri-delete-bin-line"></i> Hapus

			</button>
		</div>

	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<select class="form-control" id="cari-level" aria-describedby="inputGroupPrepend"
							onchange="hak_akses()"></select>
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div class="table-responsive-sm">
			<table class="table table-bordered m-b-0" id="table_hak_akses">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>Menu</th>
						<th>Group Menu</th>
						<th>Group Menu 2</th>
						<th>Hapus Menu</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
			<div class="d-flex justify-content-between align-items-center flex-wrap">
				<ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination"></ul>
				<div class="d-flex align-items-center gap-2">
					<label for="dt-length-0" class="mb-0">Tampilkan</label>
					<select class="form-select form-select-sm" id="dt-length-0">
						<option value="10" selected>10</option>
						<option value="25">25</option>
						<option value="50">50</option>
						<option value="100">100</option>
					</select>
					<span>entri</span>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Tambah <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-tambah">
					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<div class="input-group">
									<input type="text" class="form-control" id="cari-level" placeholder="Cari Menu"
										aria-describedby="inputGroupPrepend" onkeyup="hak_akses()" />
									<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
											class="ri-search-line"></i></span>
								</div>
							</div>
						</div>
					</div>

					<div class="table-responsive-sm">
						<table class="table table-bordered m-b-0" id="table_pilih_menu">
							<thead>
								<tr>
									<th style="text-align: center;">No</th>
									<th>Menu</th>
									<th>Group Menu</th>
									<th>Group Menu 2</th>
									<th>Semua</th>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>

						<div class="d-flex justify-content-between align-items-center flex-wrap">
							<ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-pilih-menu"></ul>
							<div class="d-flex align-items-center gap-2">
								<label for="dt-length-0" class="mb-0">Tampilkan</label>
								<select class="form-select form-select-sm" id="dt-length-0">
									<option value="10" selected>10</option>
									<option value="25">25</option>
									<option value="50">50</option>
									<option value="100">100</option>
								</select>
								<span>entri</span>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-simpan">Simpan</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Edit <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-edit" enctype="multipart/form-data">
					<input type="hidden" name="id_level">
					<div class="mb-3">
						<label for="level" class="form-label">level</label>
						<input type="text" name="level" class="form-control" />
					</div>
				</form>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		level();
		hak_akses();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/pengaturan/hak_akses/tambah'); ?>',
				type: 'POST',
				data: formData,
				dataType: 'JSON',
				success: function (data) {
					$("#tambah").modal('hide');


					if (data.status == true) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						})
						var id_level = $("#cari-level").val();
						hak_akses(id_level);
						$("#form-tambah")[0].reset();
						$('#btn-simpan').prop('disabled', false);
						$('#btn-simpan').html('Simpan');
					}
				}
			})
		})
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/pengaturan/level/edit'); ?>',
				type: 'POST',
				data: formData,
				success: function (data) {
					$("#edit").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						level();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_hak_akses tbody tr'), jumlah);
		});
	})

	function level() {
		var search = $("#cari-level").val();
		$.ajax({
			url: '<?= base_url('admin/pengaturan/level/level_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#level').empty();

				var option = '<option value="">Pilih Level</option>';
				data.forEach(function (item) {

					option += `
						 <option value="${item.id}">${item.level}</option>
						`;
				});

				$('#cari-level').html(option);
				paging()
			}
		});
	}

	function hak_akses() {
		var search = $("#cari-level").val();

		$.ajax({
			url: '<?= base_url('admin/pengaturan/hak_akses/hak_akses_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {

				var table = '';
				var no = 1;
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="7" style="text-align: center;">Tidak ada data</td>
					</tr>
					`;
				} else {
					data.forEach(function (item) {

						table += `
							<tr>
							 <td style="text-align: center;" width="5%">${no++}</td>
							 <td>${item.name}</td>
							 <td>${item.group}</td>
							 <td>${item.group2 ?? '-'}</td>
							 <td><input type="checkbox" class="menu-checkbox"  value="${item.id}"></td>
							</tr>
							`;
					});
				}

				$('#table_hak_akses tbody').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#table_hak_akses tbody tr'), jumlah_awal);
			}
		});
	}

	function tambah_hak_akses() {

		var id_level = $("#cari-level").val();

		if (id_level == '') {
			Swal.fire({
				title: 'Peringatan',
				text: 'Level Belum Dipilih',
				icon: 'warning',
				confirmButtonText: 'OK'
			})
		} else {
			$('#tambah').modal('show');

			$.ajax({
				url: '<?= base_url('admin/pengaturan/hak_akses/pilih_menu_result'); ?>',
				type: 'POST',
				data: {
					id_level
				},
				dataType: 'JSON',
				success: function (data) {

					var table = '';
					var no = 1;
					if (data.length == 0) {
						table += `
					<tr>
						<td colspan="7" style="text-align: center;">Tidak ada data</td>
					</tr>
					`;
					} else {
						data.forEach(function (item) {

							table += `
							<tr>
							 <td>${no++}</td>
							 <td>${item.name}</td>
							 <td>${item.group}</td>
							 <td>${item.group2 ?? '-'}</td>
							 <td><input type="checkbox" name="id_menu[]" value="${item.id}">
								 <input type="hidden" name="id_level" value="${id_level}">
							 </td>
							</tr>
							`;
						});
					}

					$('#table_pilih_menu tbody').html(table);
					let jumlah_awal = parseInt($('#dt-length-0').val());
					paging_pilih_menu($('#table_pilih_menu tbody tr'), jumlah_awal);
				}
			});
		}

	}

	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_hak_akses tbody tr");
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
	function paging_pilih_menu($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_hak_akses tbody tr");
		}

		window.tp = new Pagination('#pagination-pilih-menu', {
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

	function hapus_hak_akses(id) {
		const selectedPaths = $('.menu-checkbox:checked').map(function () {
			return $(this).val();
		}).get();
		var level = $('#data-level').val();
		Swal.fire({
			title: 'Peringatan',
			text: 'Anda yakin ingin menghapus data ini?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Ya',
			cancelButtonText: 'Tidak'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: '<?php echo base_url(); ?>admin/pengaturan/hak_akses/hapus',
					data: { id_menu: selectedPaths },
					type: "POST",
					dataType: "json",
					success: function (result) {
						if (result.status == true) {
							Swal.fire({
								icon: 'success',
								title: result.message,
								showConfirmButton: false,
								timer: 1500
							})
							hak_akses(level)
							dataHakAkses();
							$('#data-level').trigger('change');
						}
					}
				});
			}
		})
	}

</script>
