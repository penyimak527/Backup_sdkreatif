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
						<input type="text" class="form-control" id="cari-siswa" placeholder="Cari siswa"
							aria-describedby="inputGroupPrepend" onkeyup="siswa()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div class="table-responsive-sm">
			<table class="table table-bordered m-b-0" id="table_siswa">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>NIS</th>
						<th>Nama Siswa</th>
						<th>Jenis Kelamin</th>
						<th>Tempat,Tanggal Lahir</th>
						<th>Aksi</th>
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
				<form class="row" id="form-tambah">
					<div class="col-md-6 mb-2">
						<label for="nis" class="form-label">Nomor Induk Siswa</label>
						<input type="text" name="nis" class="form-control" placeholder="NIS ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="nama_lengkap" class="form-label">Nama Lengkap</label>
						<input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="jk" class="form-label">Jenis Kelamin</label>
						<select name="jk" class="form-control">
							<option value="Laki-laki">Laki-Laki</option>
							<option value="Perempuan">Perempuan"></option>
						</select>
					</div>
					<div class="col-md-6 mb-2">
						<label for="tempat_lahir" class="form-label">Tempat Lahir</label>
						<input type="text" name="tempat_lahir" class="form-control" placeholder="Tempat Lahir ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
						<input type="text" id="flatpicker" name="tanggal_lahir" class="form-control"
							placeholder="Tanggal Lahir ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="alamat_siswa" class="form-label">Alamat Lengkap</label>
						<input type="text" name="alamat_siswa" class="form-control" placeholder="Alamat Lengkap ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="nama_ayah" class="form-label">Nama Ayah</label>
						<input type="text" name="nama_ayah" class="form-control" placeholder="Nama Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
						<input type="text" name="pekerjaan_ayah" class="form-control" placeholder="Pekerjaan Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="telepon_ayah" class="form-label">Telepon Ayah</label>
						<input type="text" name="telepon_ayah" class="form-control" placeholder="Telepon Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="alamat_ayah" class="form-label">Alamat Ayah</label>
						<input type="text" name="alamat_ayah" class="form-control" placeholder="Alamat Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="usia_ayah" class="form-label">Usia Ayah</label>
						<input type="text" name="usia_ayah" class="form-control" placeholder="Usia Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="nama_ibu" class="form-label">Nama Ibu</label>
						<input type="text" name="nama_ibu" class="form-control" placeholder="Nama Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
						<input type="text" name="pekerjaan_ibu" class="form-control" placeholder="Pekerjaan Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="telepon_ibu" class="form-label">Telepon Ibu</label>
						<input type="text" name="telepon_ibu" class="form-control" placeholder="Telepon Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="alamat_ibu" class="form-label">Alamat Ibu</label>
						<input type="text" name="alamat_ibu" class="form-control" placeholder="Alamat Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="usia_ibu" class="form-label">Usia Ibu</label>
						<input type="text" name="usia_ibu" class="form-control" placeholder="Usia Ibu ...">
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
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Edit <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row" id="form-edit">
					<input type="hidden" id="id_siswa" name="id" class="form-control">
					<div class="col-md-6 mb-2">
						<label for="nis" class="form-label">Nomor Induk Siswa</label>
						<input type="text" name="nis" class="form-control" placeholder="NIS ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="nama_lengkap" class="form-label">Nama Lengkap</label>
						<input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="jk" class="form-label">Jenis Kelamin</label>
						<select name="jk" class="form-control">
							<option value="Laki-laki">Laki-Laki</option>
							<option value="Perempuan">Perempuan"></option>
						</select>
					</div>
					<div class="col-md-6 mb-2">
						<label for="tempat_lahir" class="form-label">Tempat Lahir</label>
						<input type="text" name="tempat_lahir" class="form-control" placeholder="Tempat Lahir ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
						<input type="text" id="flatpicker" name="tanggal_lahir" class="form-control">
					</div>
					<div class="col-md-6 mb-2">
						<label for="alamat_siswa" class="form-label">Alamat Lengkap</label>
						<input type="text" name="alamat_siswa" class="form-control" placeholder="Alamat Lengkap ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="nama_ayah" class="form-label">Nama Ayah</label>
						<input type="text" name="nama_ayah" class="form-control" placeholder="Nama Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
						<input type="text" name="pekerjaan_ayah" class="form-control" placeholder="Pekerjaan Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="telepon_ayah" class="form-label">Telepon Ayah</label>
						<input type="text" name="telepon_ayah" class="form-control" placeholder="Telepon Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="alamat_ayah" class="form-label">Alamat Ayah</label>
						<input type="text" name="alamat_ayah" class="form-control" placeholder="Alamat Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="usia_ayah" class="form-label">Usia Ayah</label>
						<input type="text" name="usia_ayah" class="form-control" placeholder="Usia Ayah ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="nama_ibu" class="form-label">Nama Ibu</label>
						<input type="text" name="nama_ibu" class="form-control" placeholder="Nama Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
						<input type="text" name="pekerjaan_ibu" class="form-control" placeholder="Pekerjaan Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="telepon_ibu" class="form-label">Telepon Ibu</label>
						<input type="text" name="telepon_ibu" class="form-control" placeholder="Telepon Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="alamat_ibu" class="form-label">Alamat Ibu</label>
						<input type="text" name="alamat_ibu" class="form-control" placeholder="Alamat Ibu ...">
					</div>
					<div class="col-md-6 mb-2">
						<label for="usia_ibu" class="form-label">Usia Ibu</label>
						<input type="text" name="usia_ibu" class="form-control" placeholder="Usia Ibu ...">
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
<div class="modal fade" id="detail-siswa" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Detail Data <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Nama Lengkap</label>
					<div class="col-sm-9" id="nama_lengkap"> </div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">NIS</label>
					<div class="col-sm-9" id="nis"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Jenis Kelamin</label>
					<div class="col-sm-9" id="jk"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Tempat, Tanggal Lahir</label>
					<div class="col-sm-9" id="ttl"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Alamat</label>
					<div class="col-sm-9" id="alamat_siswa"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Nama Ayah</label>
					<div class="col-sm-9" id="nama_ayah"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Pekerjaan Ayah</label>
					<div class="col-sm-9" id="pekerjaan_ayah"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Telepon Ayah</label>
					<div class="col-sm-9" id="telepon_ayah"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Alamat Ayah</label>
					<div class="col-sm-9" id="alamat_ayah"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Usia Ayah</label>
					<div class="col-sm-9" id="usia_ayah"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Nama Ibu</label>
					<div class="col-sm-9" id="nama_ibu"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Pekerjaan Ibu</label>
					<div class="col-sm-9" id="pekerjaan_ibu"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Telepon Ibu</label>
					<div class="col-sm-9" id="telepon_ibu"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Alamat Ibu</label>
					<div class="col-sm-9" id="alamat_ibu"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Usia Ibu</label>
					<div class="col-sm-9" id="usia_ibu"></div>
				</div>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		siswa();

		$("#btn-simpan").click(function () {
			var form = $("#form-tambah");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/siswa/tambah'); ?>',
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
						siswa();
						$("#form-tambah")[0].reset();
					}
				}
			})
		})
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/siswa/edit'); ?>',
				type: 'POST',
				data: data,
				success: function (data) {
					$("#modal-edit").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						siswa();
					}
				}
			})
		})

		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_siswa tbody tr'), jumlah);
		});

	})

	function siswa() {
		var search = $("#cari-siswa").val();
		$.ajax({
			url: '<?= base_url('admin/kurikulum/siswa/siswa_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#siswa').empty();

				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="4" style="text-align: center;">Tidak ada data</td>
					</tr>
				`;
				} else {
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						table += `
						<tr>
							<td width="5%" style="text-align: center;"> ${no++}</td>
							<td>${item.nis}</td> 
							<td>${item.nama_lengkap}</td> 
							<td>${item.jk}</td> 
							<td>${item.tempat_lahir}, ${item.tanggal_lahir}</td>  
							<td> 
								<button type="button" class="btn btn-sm btn-outline-primary " onclick="detailSiswa('${detail}')"><i class="ri-eye-line"></i></button>
								<button type="button" class="btn btn-sm btn-outline-warning " onclick="editSiswa('${detail}')"><i class="ri-edit-2-line"></i></button>
								<button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
							</td>
						</tr>
						`;
					});
				}
				$('#table_siswa tbody').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#table_siswa tbody tr'), jumlah_awal);
			}
		});
	}

	function editSiswa(detail) {
		$('#modal-edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_siswa').val(item.id);
		$('#modal-edit input[name="nama_lengkap"]').val(item.nama_lengkap);
		$('#modal-edit input[name="nis"]').val(item.nis);
		$('#modal-edit select[name="jk"]').html(`
		<option value="Laki-Laki"${item.jk == 'Laki-Laki' ? 'selected' : ''}>Laki-Laki</option>
		<option value="Perempuan"${item.jk == 'Perempuan' ? 'selected' : ''}>Perempuan</option>
		`);
		$('#modal-edit input[name="tempat_lahir"]').val(item.tempat_lahir);
		$('#modal-edit input[name="tanggal_lahir"]')[0]._flatpickr.setDate(item.tanggal_lahir, true);
		$('#modal-edit input[name="alamat_siswa"]').val(item.alamat_siswa);

		$('#modal-edit input[name="nama_ayah"]').val(item.nama_ayah);
		$('#modal-edit input[name="pekerjaan_ayah"]').val(item.pekerjaan_ayah);
		$('#modal-edit input[name="telepon_ayah"]').val(item.telepon_ayah);
		$('#modal-edit input[name="alamat_ayah"]').val(item.alamat_ayah);
		$('#modal-edit input[name="usia_ayah"]').val(item.usia_ayah);

		$('#modal-edit input[name="nama_ibu"]').val(item.nama_ibu);
		$('#modal-edit input[name="pekerjaan_ibu"]').val(item.pekerjaan_ibu);
		$('#modal-edit input[name="telepon_ibu"]').val(item.telepon_ibu);
		$('#modal-edit input[name="alamat_ibu"]').val(item.alamat_ibu);
		$('#modal-edit input[name="usia_ibu"]').val(item.usia_ibu);

	}
	function detailSiswa(detail) {
		$('#detail-siswa').modal('show');
		var item = JSON.parse(atob(detail));
		console.log(item);
		$('#nama_lengkap').text(item.nama_lengkap);
		$('#nis').text(item.nis);
		$('#jk').text(item.jk);
		$('#ttl').text(item.tempat_lahir + ', ' + item.tanggal_lahir);
		$('#alamat_siswa').text(item.alamat_siswa);
		$('#nama_ayah').text(item.nama_ayah);
		$('#pekerjaan_ayah').text(item.pekerjaan_ayah);
		$('#telepon_ayah').text(item.telepon_ayah);
		$('#alamat_ayah').text(item.alamat_ayah);
		$('#usia_ayah').text(item.usia_ayah);

		$('#nama_ibu').text(item.nama_ibu);
		$('#pekerjaan_ibu').text(item.pekerjaan_ibu);
		$('#telepon_ibu').text(item.telepon_ibu);
		$('#alamat_ibu').text(item.alamat_ibu);
		$('#usia_ibu').text(item.usia_ibu);
	}


	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_siswa tbody tr");
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
					url: `<?= base_url(); ?>admin/kurikulum/siswa/hapus`,
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
							siswa();
						}

					}
				})
			}
		})
	} 
</script>
