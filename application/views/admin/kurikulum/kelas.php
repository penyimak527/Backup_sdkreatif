<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-danger" id="btn-trash-kelas"><i class="ri-delete-bin-6-line"></i> Trash</button><button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
				class="ri-add-line"></i>Tambah</button></div>

	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-kelas" placeholder="Cari Kelas"
							aria-describedby="inputGroupPrepend" onkeyup="kelas()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_kelas">

		</div>
		<div
			class="d-flex flex-column flex-sm-row justify-content-between align-items-center align-items-sm-center gap-2">
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
					<input type="hidden" name="web_csrf_token" value="<?= html_escape($csrf_token) ?>">
					<div class="mb-2">
						<label for="nama_kelas" class="form-label">Nama Kelas</label>
						<input type="text" name="nama_kelas" class="form-control" placeholder="Nama Kelas ..." />
					</div>
					<div class="mb-2">
						<label for="kode_kelas" class="form-label">Kode Kelas</label>
						<input type="text" name="kode_kelas" class="form-control" placeholder="Kode Kelas ..." />
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
					<input type="hidden" name="id_kelas">
					<input type="hidden" name="web_csrf_token" value="<?= html_escape($csrf_token) ?>">
					<div class="mb-2">
						<label for="nama_kelas" class="form-label">Nama Kelas</label>
						<input type="text" name="nama_kelas" class="form-control" />
					</div>
					<div class="mb-2">
						<label for="kode_kelas" class="form-label">Kode Kelas</label>
						<input type="text" name="kode_kelas" class="form-control" />
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

<div class="modal fade" id="trash-kelas" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h4 class="modal-title mb-0">Trash Kelas</h4><small class="text-muted">Kelas dapat dipulihkan tanpa menghapus relasi historis.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="trash-kelas-list"></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div></div></div></div>



<script>
	const kelasCsrf = <?= json_encode($csrf_token) ?>;
	const kelasSoftDeleteAvailable = <?= $soft_delete_available ? 'true' : 'false' ?>;
	$(document).ready(function () {
		kelas();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').text('Sedang Diproses');
			var form = $("#form-tambah");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/kelas/tambah'); ?>',
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
						kelas();
						$("#form-tambah")[0].reset();
						$('#btn-simpan').prop('disabled', false);
						$('#btn-simpan').text('Simpan');
					}
				}
			})
		})
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/kelas/edit'); ?>',
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
						kelas();
					}
				}
			})
		})

		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_kelas .card-mapel'), jumlah);
		});
		$('#btn-trash-kelas').on('click', function () {
			if (!kelasSoftDeleteAvailable) return Swal.fire('Migrasi belum diterapkan','Kolom deleted_at pada tabel kelas perlu disetujui dan dijalankan terlebih dahulu.','info');
			loadTrashKelas();
			$('#trash-kelas').modal('show');
		});
	})

	function loadTrashKelas() {
		$.post('<?= base_url('admin/kurikulum/kelas/trash') ?>', {}, function (data) {
			let html = '';
			(data || []).forEach(function (item, index) {
				html += `<div class="card-mapel"><div class="keterangan-mapel"><div class="keterangan-mapel-kiri"><h5 class="judul-mapel mb-1">${index + 1}. Kelas ${item.nama_kelas}${item.kode_kelas ? ' ' + item.kode_kelas : ''}</h5><small class="text-muted">Dihapus ${item.deleted_at} · ${item.jumlah_setting} setting · ${item.jumlah_jadwal} jadwal</small></div><div class="keterangan-mapel-kanan"><button type="button" class="btn btn-sm btn-outline-success" onclick="restoreKelas(${item.id})" title="Pulihkan"><i class="ri-arrow-go-back-line"></i></button></div></div></div>`;
			});
			$('#trash-kelas-list').html(html || '<div class="text-center text-muted py-4">Trash kelas kosong.</div>');
		}, 'json');
	}

	function restoreKelas(id) {
		$.post('<?= base_url('admin/kurikulum/kelas/restore') ?>', {id, web_csrf_token: kelasCsrf}, function (data) {
			if (data == true) { Swal.fire('Berhasil','Kelas berhasil dipulihkan.','success'); loadTrashKelas(); kelas(); }
		}, 'json');
	}

	function kelas() {
		var search = $("#cari-kelas").val();
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas/kelas_result'); ?>',
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
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. Kelas ${item.nama_kelas} - Kode ${item.kode_kelas}</h5>   
								</div>
								 <div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										<button type="button" class="btn btn-outline-warning w-50" onclick="editKelas('${detail}')">
											<i class="ri-edit-line"></i>
										</button>
											<button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
									</div>
								</div>
							</div>
						</div>
						`;
					});
				}
				$('#data_kelas').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_kelas .card-mapel'), jumlah_awal);
			}
		});
	}

	function editKelas(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_kelas"]').val(item.id);
		$('#edit input[name="nama_kelas"]').val(item.nama_kelas);
		$('#edit input[name="kode_kelas"]').val(item.kode_kelas);
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



	function hapus(id, id_kelas) {
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
						id,
						id_kelas,
						web_csrf_token: kelasCsrf
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
