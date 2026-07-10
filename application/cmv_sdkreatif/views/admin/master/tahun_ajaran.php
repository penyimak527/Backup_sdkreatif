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
						<input type="text" class="form-control" id="cari-tahun-ajaran" placeholder="Cari Tahun Ajaran"
							aria-describedby="inputGroupPrepend" onkeyup="tahun_ajaran()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_tahun_ajaran">
			<!-- <table class="table table-bordered m-b-0" id="data_tahun_ajaran	<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>Tahun Ajaran</th>
						<th>Status</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table> -->
		</div>
		<div
			class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2 mt-2">
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
						<label for="simpleinput" class="form-label">Tahun Ajaran</label>
						<input type="text" name="periode" class="form-control" placeholder="Tahun Ajaran ...">
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
				<form id="form-edit">
					<div class="mb-3">
						<label for="simpleinput" class="form-label">Tahun Ajaran</label>
						<input type="text" id="periode" name="periode" class="form-control">
						<input type="hidden" id="id_periode" name="id_periode" class="form-control">
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
		tahun_ajaran();

		$("#btn-simpan").click(function () {
			var form = $("#form-tambah");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/master/tahun_ajaran/tambah'); ?>',
				type: 'POST',
				data: data,
				success: function (data) {
					$("#tambah").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						})
						tahun_ajaran();
						$("#form-tambah")[0].reset();
					}
				}
			})
		})
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/master/tahun_ajaran/edit'); ?>',
				type: 'POST',
				data: data,
				success: function (data) {
					$("#edit").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						tahun_ajaran();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_tahun_ajaran .card-mapel'), jumlah);
		});
	})

	function tahun_ajaran() {
		var search = $("#cari-tahun-ajaran").val();
		$.ajax({
			url: '<?= base_url('admin/master/tahun_ajaran/tahun_ajaran_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#tahun_ajaran').empty();

				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
						<div class="card-mapel">
						 
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
								<span>Status: <span class="badge bg-${item.status == 'Aktif' ? 'success' : 'danger'}">${item.status}</span> </span> 
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.periode}</h5> 
									 
								</div>
								<div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
									<button type="button" class="btn btn-outline-info w-50" onclick="update_status('${item.id}')">
										<i class="ri-file-warning-line"></i>
									</button>
									<button type="button" class="btn btn-outline-warning w-50" onclick="editTahun('${detail}')">
										<i class="ri-edit-line"></i>
									</button>
										<button type="button" class="btn btn-outline-danger w-50" onclick="hapus('${item.id}')">
										<i class="ri-delete-bin-line"></i>
									</button>
									</div>
								</div>
							</div>
						</div>
						`;
					});
				}
				$('#data_tahun_ajaran').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_tahun_ajaran .card-mapel'), jumlah_awal);
			}
		});
	}

	function editTahun(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_periode').val(item.id);
		$('#periode').val(item.periode);
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
					url: `<?= base_url(); ?>admin/master/tahun_ajaran/hapus`,
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
							tahun_ajaran();
						}

					}
				})
			}
		})
	}
	function update_status(id) {
		Swal.fire({
			title: 'Ubah Status',
			text: 'Anda yakin ingin mengaktifkan tahun ajaran ini?',
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
					url: `<?= base_url(); ?>admin/master/tahun_ajaran/update_status`,
					data: {
						id: id
					},
					dataType: 'json',
					success: function (data) {
						if (data == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Data berhasil diupdate',
							})
							tahun_ajaran();
						}

					}
				})
			}
		})

	}
</script>