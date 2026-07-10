<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<?php if ($level == 'Admin'): ?>
			<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
					class="ri-add-line"></i>Tambah</button>
		<?php endif; ?>


	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-kelas-setting" placeholder="Cari Kelas"
							aria-describedby="inputGroupPrepend" onkeyup="kelas_setting()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div class="table-responsive-sm">
			<table class="table table-bordered m-b-0" id="table_kelas_setting">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>Nama Kelas</th>
						<th>Periode</th>
						<th>Semester</th>
						<th>Wali Kelas</th>
						<th>Jadwal Mengajar</th>
						<?php if ($level == 'Admin'): ?>
							<th>Aksi</th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
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

<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Tambah <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-tambah">
					<div class="mb-2">
						<label for="id_kelas" class="form-label">Kelas</label>
						<select name="id_kelas" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="id_guru" class="form-label">Wali Kelas</label>
						<select name="id_guru" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="id_periode" class="form-label">Tahun Ajaran</label>
						<select name="id_periode" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="semester" class="form-label">Semester</label>
						<select name="semester" class="form-control">
							<option value="">Semester</option>
							<option value="Ganjil">Ganjil</option>
							<option value="Genap">Genap</option>
						</select>
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
					<input type="hidden" id="id_kelas_setting" name="id">
					<div class="mb-2">
						<label for="id_kelas" class="form-label">Kelas</label>
						<select name="id_kelas" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="id_guru" class="form-label">Wali Kelas</label>
						<select name="id_guru" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="id_periode" class="form-label">Tahun Ajaran</label>
						<select name="id_periode" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="semester" class="form-label">Semester</label>
						<select name="semester" class="form-control">

						</select>
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

<div class="modal fade" id="view-jadwal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-full-width">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Data Jadwal Pelajaran</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row justify-content-between">
					<div class="col-md-3">
						<div class="mb-3">
							<div class="input-group">
								<input type="text" class="form-control" id="cari-kelas-jadwal-pelajaran"
									placeholder="Cari Jadwal Pelajaran" aria-describedby="inputGroupPrepend"
									onkeyup="kelas_jadwal_pelajaran()">
								<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
										class="ri-search-line"></i></span>
							</div>
						</div>
					</div>
					<div class="col-md-1">
						<div class="mb-3" style="padding-left: 7px;">
							<button type="button" class="btn btn-sm btn-outline-primary"
								onclick="tambah_jadwal_pelajaran()"><i class="ri-add-line"></i>Tambah</button>
						</div>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table table-bordered m-b-0" id="table-jadwal-pelajaran">
						<thead>
							<tr>
								<th style="text-align: center;">No</th>
								<th>Kelas</th>
								<th>Mata Pelajaran</th>
								<th>Guru</th>
								<th>Jam Awal</th>
								<th>Jam Akhir</th>
								<th>Hari</th>
								<th>Ruangan</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>

						</tbody>
					</table>
				</div>
				<div
					class="d-flex flex-column flex-sm-row justify-content-between align-items-center align-items-sm-center gap-2">
					<ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-jadwal"></ul>
					<div class="d-flex align-items-center gap-2">
						<label for="dt-length-1" class="mb-0">Tampilkan</label>
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
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="tambah-jadwal-pelajaran" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Tambah Jadwal Pelajaran</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-tambah-jadwal-pelajaran">
					<input type="hidden" name="id_kelas_setting">
					<?php if ($level == 'Admin'): ?>
						<div class="mb-2">
							<label for="id_guru" class="form-label">Nama Guru</label>
							<select id="guru-jadwal-pelajaran" name="id_guru" class="form-control">
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="id_guru" class="form-control"
							value="<?= $this->session->userdata('admin')['id_pegawai']; ?>" />
					<?php endif; ?>
					<div class="mb-2">
						<label for="id_mapel" class="form-label">Mata Pelajaran</label>
						<select type="text" name="id_mapel" class="form-control">

						</select>
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_awal" class="form-label">Jam Pelajaran Awal</label>
						<input type="text" name="jam_pelajaran_awal" class="form-control"
							placeholder="Jam Pelajaran Awal ..." onkeyup="formatTimeInput(this)" maxlength="8" />
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_akhir" class="form-label">Jam Pelajaran Akhir</label>
						<input type="text" name="jam_pelajaran_akhir" class="form-control"
							placeholder="Jam Pelajaran Akhir ..." onkeyup="formatTimeInput(this)" maxlength="8" />
					</div>
					<div class="mb-2">
						<label for="hari" class="form-label">Hari</label>
						<select class="form-control" data-choices name="hari">
							<option value="">Pilih Hari</option>
							<option value="Senin">Senin</option>
							<option value="Selasa">Selasa</option>
							<option value="Rabu">Rabu</option>
							<option value="Kamis">Kamis</option>
							<option value="Jumat">Jumat</option>
							<option value="Sabtu">Sabtu</option>
						</select>
					</div>
					<!-- <div class="mb-2">
						<label for="ruangan" class="form-label">Ruangan</label>
						<input type="text" name="ruangan" class="form-control" placeholder="Ruangan ..." />
					</div> -->
				</form>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light"
					onclick="$('#tambah-jadwal-pelajaran').modal('hide'); $('#view-jadwal').modal('show');">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-simpan-jadwal-pelajaran">Simpan</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="edit-jadwal-pelajaran" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Edit Jadwal Pelajaran</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-edit-jadwal-pelajaran">
					<input type="hidden" name="id_jadwal_pelajaran">
					<input type="hidden" name="id_kelas_setting">
					<div class="mb-2">
						<label for="id_guru" class="form-label">Nama Guru</label>
						<select id="guru-jadwal-pelajaran" name="id_guru" class="form-control">
						</select>
					</div>
					<div class="mb-2">
						<label for="id_mapel" class="form-label">Mata Pelajaran</label>
						<select type="text" name="id_mapel" class="form-control">

						</select>
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_awal" class="form-label">Jam Pelajaran Awal</label>
						<input type="text" name="jam_pelajaran_awal" class="form-control" />
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_akhir" class="form-label">Jam Pelajaran Akhir</label>
						<input type="text" name="jam_pelajaran_akhir" class="form-control" />
					</div>
					<div class="mb-2">
						<label for="hari" class="form-label">Hari</label>
						<select type="text" name="hari" class="form-control">

						</select>
					</div>
					<!-- <div class="mb-2">
						<label for="ruangan" class="form-label">Ruangan</label>
						<input type="text" name="ruangan" class="form-control" />
					</div> -->
				</form>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light"
					onclick="$('#edit-jadwal-pelajaran').modal('hide'); $('#view-jadwal').modal('show');">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-update-jadwal-pelajaran">Simpan</button>
			</div>
		</div>
	</div>
</div>



<script>
	$(document).ready(function () {
		kelas_setting();
		kelas();
		periode();
		guru();
		kelas_jadwal_pelajaran();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').text('Sedang Diproses');
			var form = $("#form-tambah");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/kelas_setting/tambah'); ?>',
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
						kelas_setting();
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
				url: '<?= base_url('admin/kurikulum/kelas_setting/edit'); ?>',
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
						kelas_setting();
					}
				}
			})
		})
		$("#btn-update-jadwal-pelajaran").click(function () {

			var form = $("#form-edit-jadwal-pelajaran");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/kelas_jadwal_pelajaran/edit'); ?>',
				type: 'POST',
				data: data,
				dataType: 'JSON',
				success: function (data) {
					$("#edit-jadwal-pelajaran").modal('hide');


					if (data.status == true) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						kelas_jadwal_pelajaran(data.id_jadwal);
						$("#view-jadwal").modal('show');
					}
				}
			})
		})
		$("#btn-simpan-jadwal-pelajaran").click(function () {
			$('#btn-simpan-jadwal-pelajaran').prop('disabled', true);
			$('#btn-simpan-jadwal-pelajaran').text('Sedang Diproses');
			var form = $("#form-tambah-jadwal-pelajaran");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/kelas_jadwal_pelajaran/tambah'); ?>',
				type: 'POST',
				data: data,
				dataType: 'JSON',
				success: function (data) {
					$("#tambah-jadwal-pelajaran").modal('hide');


					if (data.status == true) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						kelas_jadwal_pelajaran(data.id_jadwal);
						$('#form-tambah-jadwal-pelajaran')[0].reset();
						$('#btn-simpan-jadwal-pelajaran').prop('disabled', false);
						$('#btn-simpan-jadwal-pelajaran').text('Simpan');
						$("#view-jadwal").modal('show');
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_kelas_setting tbody tr'), jumlah);
		});
		$('#dt-length-1').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table-jadwal-pelajaran tbody tr'), jumlah);
		});
	})

	function kelas_setting() {
		var search = $("#cari-kelas-setting").val();
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas_setting/kelas_setting_result'); ?>',
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
					<tr>
						<td colspan="4" style="text-align: center;">Tidak ada data</td>
					</tr>
				`;
				} else {
					var level = '<?= $level ?>';
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						var aksi = '';

						if (level == "Admin") {
							aksi += `
							<td> 
								
								<button type="button" class="btn btn-sm btn-outline-warning " onclick="editKelasSetting('${detail}')"><i class="ri-edit-2-line"></i></button>
								<button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus(${item.id},${item.id_kelas_setting})"><i class="ri-delete-bin-line"></i></button> 
							</td>
							`;
						}
						table += `
						<tr>
							<td width="5%" style="text-align: center;"> ${no++}</td>
							<td>${item.nama_kelas ?? ''}</td> 
							<td>${item.periode}</td> 
							<td>${item.semester}</td> 
							<td>${item.wali_kelas ?? ''}</td> 
							<td style="text-align: center;"><button type="button" class="btn btn-sm btn-outline-primary " onclick="viewJadwal(${item.id})"><i class="ri-eye-line"></i></button></td> 
							${aksi}
						</tr>
						`;
					});
				}
				$('#table_kelas_setting tbody').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#table_kelas_setting tbody tr'), jumlah_awal);
			}
		});
	}

	function editKelasSetting(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_kelas_setting').val(item.id);
		kelas(item.id_kelas);
		periode(item.id_periode);
		guru(item.id_guru);
		$('#edit select[name="semester"]').html(
			` 
							<option value="Ganjil" ${item.semester == 'Ganjil' ? 'selected' : ''}>Ganjil</option>
							<option value="Genap" ${item.semester == 'Genap' ? 'selected' : ''}>Genap</option>
			`
		)
	}
	function viewJadwal(id_jadwal) {
		$('#view-jadwal').modal('show');
		kelas_jadwal_pelajaran(id_jadwal);
		$("#tambah-jadwal-pelajaran input[name='id_kelas_setting']").val(id_jadwal);
		mapel();

	}

	function tambah_jadwal_pelajaran() {
		$('#view-jadwal').modal('hide');
		$('#tambah-jadwal-pelajaran').modal('show');
		guru_jadwal();
		mapel();
	}


	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_kelas_setting tbody tr");
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

	function paging_jadwal($selector) {
		var jumlah_tampil = '10';

		if (typeof $selector == 'undefined') {
			$selector = $("#table-jadwal-pelajaran tbody tr");
		}

		window.tp = new Pagination('#pagination-jadwal', {
			itemsCount: $selector.length,
			pageSize: parseInt(jumlah_tampil),
			onPageSizeChange: function (ps) {
				console.log('changed to ' + ps);
			},
			onPageChange: function (paging) {
				var start = paging.pageSize * (paging.currentPage - 1),
					end = start + paging.pageSize,
					$rows = $selector;
				$rows.hide();
				for (var i = start; i < end; i++) {
					$rows.eq(i).show();
				}
			}
		});
	}

	function hapus(id, id_kelas_setting) {
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
					url: `<?= base_url(); ?>admin/kurikulum/kelas_setting/hapus`,
					data: {
						id,
						id_kelas_setting
					},
					dataType: 'json',
					success: function (data) {
						if (data == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Data berhasil dihapus',
							})
							kelas_setting();
						}

					}
				})
			}
		})
	}
	function hapus_jadwal_pelajaran(id, id_kelas_setting) {
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
					url: `<?= base_url(); ?>admin/kurikulum/kelas_jadwal_pelajaran/hapus`,
					data: {
						id,
						id_kelas_setting
					},
					dataType: 'json',
					success: function (data) {
						if (data.status == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Data berhasil dihapus',
							})

							kelas_jadwal_pelajaran(data.id_jadwal);

						}

					}
				})
			}
		})
	}

	function kelas(id_kelas = null) {
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas/kelas_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Kelas</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_kelas ? 'selected' : ''}>${item.nama_kelas}</option>
						`;
					});
				}
				if (id_kelas == null) {
					$('#tambah select[name="id_kelas"]').html(option);
				} else {
					$('#edit select[name="id_kelas"]').html(option);
				}
			}
		});
	}
	function periode(id_periode = null) {
		console.log(id_periode);
		$.ajax({
			url: '<?= base_url('admin/master/tahun_ajaran/tahun_ajaran_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Tahun Ajaran</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {
						var selected = '';
						if (id_periode == null) {

							selected = item.status == 'Aktif' ? 'selected' : '';
						} else {
							selected = item.id == id_periode ? 'selected' : '';

						}

						option += `
						<option value="${item.id}" ${selected}>${item.periode}</option>
						`;
					});
				}
				if (id_periode == null) {

					$('#tambah select[name="id_periode"]').html(option);
				} else {
					$('#edit select[name="id_periode"]').html(option);
				}
			}
		});
	}

	function guru(id_guru = null) {

		$.ajax({
			url: '<?= base_url('admin/kurikulum/guru/guru_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Wali Kelas</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_guru ? 'selected' : ''}>${item.nama_guru}</option>
						`;
					});
				}
				if (id_guru == null) {
					$('#tambah select[name="id_guru"]').html(option);
					$('#edit-jadwal-pelajaran select[name="id_guru"]').html(option);

				} else {
					$('#edit select[name="id_guru"]').html(option);
				}
			}
		});
	}
	function guru_jadwal(id_guru = null) {

		$.ajax({
			url: '<?= base_url('admin/kurikulum/guru/guru_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Guru</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_guru ? 'selected' : ''}>${item.nama_guru}</option>
						`;
					});
				}
				if (id_guru == null) {
					$('#tambah-jadwal-pelajaran select[name="id_guru"]').html(option);
				} else {
					$('#edit-jadwal-pelajaran select[name="id_guru"]').html(option);
				}
			}
		});
	}

	function mapel(id_mapel = null) {

		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas_setting/mata_pelajaran_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Mata Pelajaran</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
				<option value="${item.id}" ${item.id == id_mapel ? 'selected' : ''}>${item.mapel}</option>
				`;
					});
				}
				if (id_mapel == null) {
					$('#tambah-jadwal-pelajaran select[name="id_mapel"]').html(option);

				} else {
					$('#edit-jadwal-pelajaran select[name="id_mapel"]').html(option);
				}
			}
		});
	}

	function kelas_jadwal_pelajaran(id_jadwal) {
		var search = $("#cari-kelas-jadwal-pelajaran").val();
		console.log(search);
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas_jadwal_pelajaran/kelas_jadwal_pelajaran_result'); ?>',
			type: 'POST',
			data: {
				search,
				id_jadwal
			},
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="9" style="text-align: center;">Tidak ada data</td>
					</tr>
				`;
				} else {
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						table += `
						<tr>
							<td width="5%" style="text-align: center;"> ${no++}</td>
							<td>${item.nama_kelas}</td> 
							<td>${item.mapel}</td> 
							<td>${item.nama_guru}</td> 
							<td>${item.jam_pelajaran_awal}</td> 
							<td>${item.jam_pelajaran_akhir}</td> 
							<td>${item.hari}</td> 
							<td>${item.ruangan}</td> 
							<td>  
								<button type="button" class="btn btn-sm btn-outline-warning " onclick="editJadwalPelajaran('${detail}')"><i class="ri-edit-2-line"></i></button>
								<button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus_jadwal_pelajaran(${item.id},${item.id_kelas_setting})"><i class="ri-delete-bin-line"></i></button> 
							</td>
						</tr>
						`;
					});
				}
				$('#table-jadwal-pelajaran tbody').html(table);
				let jumlah_awal = parseInt($('#dt-length-1').val());
				paging($('#table-jadwal-pelajaran tbody tr'), jumlah_awal);
			}
		});
	}

	function editJadwalPelajaran(detail) {
		$('#view-jadwal').modal('hide');
		$('#edit-jadwal-pelajaran').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit-jadwal-pelajaran input[name="id_jadwal_pelajaran"]').val(item.id);


		guru_jadwal(item.id_guru);
		mapel(item.id_mapel);
		$('#edit-jadwal-pelajaran select[name="hari"]').html(
			`
			<option value="Senin" ${item.hari == 'Senin' ? 'selected' : ''} >Senin</option>
			<option value="Selasa" ${item.hari == 'Selasa' ? 'selected' : ''} >Selasa</option>
			<option value="Rabu" ${item.hari == 'Rabu' ? 'selected' : ''} >Rabu</option>
			<option value="Kamis" ${item.hari == 'Kamis' ? 'selected' : ''} >Kamis</option>
			<option value="Jumat" ${item.hari == 'Jumat' ? 'selected' : ''} >Jumat</option>
			<option value="Sabtu" ${item.hari == 'Sabtu' ? 'selected' : ''} >Sabtu</option>
			`
		);
		$('#edit-jadwal-pelajaran input[name="jam_pelajaran_awal"]').val(item.jam_pelajaran_awal);
		$('#edit-jadwal-pelajaran input[name="jam_pelajaran_akhir"]').val(item.jam_pelajaran_akhir);
		$('#edit-jadwal-pelajaran input[name="ruangan"]').val(item.ruangan);
		$('#edit-jadwal-pelajaran input[name="id_kelas_setting"]').val(item.id_kelas_setting);
	}

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