<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
				class="ri-add-line"></i>Tambah</button>

	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-user" placeholder="Cari user"
							aria-describedby="inputGroupPrepend" onkeyup="user()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_user">
			<!-- <table class="table table-bordered m-b-0" id="data_user">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>Nama User</th>
						<th>Username</th>
						<th>Level</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table> -->
		</div>
		<div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3">

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

<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Tambah <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-tambah">
					<div class="mb-3">
						<label for="id_pegawai" class="form-label">Nama Pegawai</label>
						<select name="id_pegawai" class="form-control" data-choices name="choices-single-default"
							id="choices-single-default">
							<option value="">Pilih Pegawai</option>
							<?php foreach ($pegawai as $p): ?>
								<option value="<?= $p['id']; ?>"><?= $p['nama_pegawai']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label for="id_pegawai" class="form-label">Level</label>
						<select name="id_level" class="select2 form-control select2-multiple" data-toggle="select2"
							multiple="multiple" data-placeholder="Choose ...">
							<?php foreach ($level as $l): ?>
								<option value="<?= $l['id']; ?>"><?= $l['level']; ?></option>
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
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-simpan">Simpan</button>
			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function () {
		user();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			console.log('test');
			var form = $("#form-tambah");
			var formData = form.serialize();
			var password = $('input[name=password]').val();
			var konfirmasi_password = $('input[name=konfirmasi_password]').val();
			if (password != konfirmasi_password) {
				Swal.fire({
					icon: 'error',
					title: 'Gagal',
					text: 'Password Tidak sama',
				})
				$('#btn-simpan').prop('disabled', false);
				$('#btn-simpan').html('Simpan');
				return false;
			}
			$.ajax({
				url: '<?= base_url('admin/pengaturan/user/tambah'); ?>',
				type: 'POST',
				data: formData,
				success: function (data) {
					$("#tambah").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						}).then((result) => {
							if (result.value) {
								location.reload();
							}
						})

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
						})
						user();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_user .card-mapel'), jumlah);
		});
	})

	function user() {
		var search = $("#cari-user").val();
		$.ajax({
			url: '<?= base_url('admin/pengaturan/user/user_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<div class="card-mapel" style="">
						 
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">Tidak ada data</h5>
								</div>
								 
							</div>
						</div>
				`;
				} else {
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						table += `
					 

						<div class="card-mapel">
							  <p class="keterangan-hari">
							<span>  Username : ${item.username} </span>
							 <span> Level : ${item.level} </span>
							  </p> 
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.nama_user}</h5>    
								</div>
								 <div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										<a href="<?= base_url('admin/pengaturan/user/view/'); ?>${item.id}" class="btn btn-sm btn-outline-warning w-50"><i class="ri-edit-2-line"></i></a>
											<button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
									</div>
								</div>
							</div>
						</div>
						`;
					});
				}
				$('#data_user').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_user .card-mapel'), jumlah_awal);
			}
		});
	}


	function paging($selector, jumlah_tampil = 10) {


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
					url: `<?= base_url(); ?>admin/pengaturan/user/hapus`,
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
							user();
						}

					}
				})
			}
		})
	}

</script>