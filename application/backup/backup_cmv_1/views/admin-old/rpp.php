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
						<input type="text" class="form-control" id="cari-rpp" placeholder="Cari RPP"
							aria-describedby="inputGroupPrepend" onkeyup="rpp()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>


		<div class="container-data">

		</div>
		<div
			class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2">
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
				<form id="form-tambah" enctype="multipart/form-data">
					<?php if ($level == 'Admin'): ?>
						<div class="mb-3">
							<label for="id_guru" class="form-label">Nama Guru</label>
							<select type="text" name="id_guru" class="form-control"></select>
						</div>
					<?php else: ?>
						<input type="hidden" name="id_guru" value="<?= $this->session->userdata('admin')['id_pegawai']; ?>"
							class="form-control">
					<?php endif; ?>
					<div class="mb-3">
						<label for="judul" class="form-label">Judul</label>
						<input type="text" name="judul" class="form-control" placeholder="Judul ..." />
					</div>
					<div class="mb-3">
						<label for="id_kelas" class="form-label">Kelas</label>
						<select type="text" name="id_kelas" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label for="semester" class="form-label">Semester</label>
						<select type="text" name="semester" class="form-control">
							<option value="">Pilih Semester</option>
							<option value="Ganjil">Ganjil</option>
							<option value="Genap">Genap</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="id_periode" class="form-label">Tahun Ajaran</label>
						<select type="text" name="id_periode" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label for="file_rpp" class="form-label">Upload Rpp</label>
						<input type="file" name="file_rpp" class="dropify" placeholder="Judul ..." />
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
					<input type="hidden" name="id_rpp">
					<input type="hidden" name="oldImage">
					<div class="mb-3">
						<label for="id_guru" class="form-label">Nama Guru</label>
						<select type="text" name="id_guru" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label for="judul" class="form-label">Judul</label>
						<input type="text" name="judul" class="form-control" placeholder="Judul ..." />
					</div>
					<div class="mb-3">
						<label for="id_kelas" class="form-label">Kelas</label>
						<select type="text" name="id_kelas" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label for="semester" class="form-label">Semester</label>
						<select type="text" name="semester" class="form-control">
							<option value="">Semester</option>
							<option value="Ganjil">Ganjil</option>
							<option value="Genap">Genap</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="id_periode" class="form-label">Tahun Ajaran</label>
						<select type="text" name="id_periode" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label for="file_rpp" class="form-label">Upload Rpp</label>
						<input id="edit-rpp" type="file" name="file_rpp" class="form-control">
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

<div class="modal fade" id="view-rpp" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Detail <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3" id="data-rpp">

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
		rpp();
		kelas();
		guru();
		periode();

		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah")[0];
			var formData = new FormData(form);
			$.ajax({
				url: '<?= base_url('admin/rpp/tambah'); ?>',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				cache: false,
				success: function (data) {
					$("#tambah").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						})
						rpp();
						$("#form-tambah")[0].reset();
						$('#btn-simpan').prop('disabled', false);
						$('#btn-simpan').html('Simpan');
					}
				}
			})
		})
		$("#btn-update").click(function () {
			var form = $("#form-edit")[0];
			var formData = new FormData(form);
			$.ajax({
				url: '<?= base_url('admin/rpp/edit'); ?>',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				cache: false,
				success: function (data) {
					$("#edit").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						rpp();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_rpp tbody tr'), jumlah);
		});
	})

	function rpp() {
		var search = $("#cari-rpp").val();
		$.ajax({
			url: '<?= base_url('admin/rpp/rpp_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#rpp').empty();

				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<div class="card-mapel">
							 
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel">Tidak Ada Data</h5>
								 
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
								<span>Periode : ${item.periode}</span>
								<span>${item.semester}</span>
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel">${item.nama_guru}</h5>
									<p class="keterangan-jam-mapel">Judul : ${item.judul}</p>
									<p class="keterangan-jam-mapel">Kelas : ${item.kelas}</p>
								</div>
								<div class="keterangan-mapel-kanan-custom">
									<button type="button" class="btn btn-sm btn-outline-primary " onclick="viewRpp('${item.file}')"><i class="ri-eye-line"></i></button>
									<button type="button" onclick="editRpp('${detail}')" class="btn btn-sm btn-outline-warning"><i class="ri-edit-2-line"></i></button>
									<button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
								</div>
							</div>
						</div>
						
						`;
					});
				}
				$('.container-data').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('.container-data'), jumlah_awal);
			}
		});
	}

	function editRpp(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_rpp"]').val(item.id);
		guru(item.id_guru);
		$('#edit input[name="judul"]').val(item.judul);
		kelas(item.id_kelas);
		$('#edit input[name="semester"]').html(
			`
			<option value="Ganjil" ${item.semester == 'Ganjil' ? 'selected' : ''}>Ganjil</option>
			<option value="Genap" ${item.semester == 'Genap' ? 'selected' : ''}>Genap</option>
			`
		)
		periode(item.id_periode);

		$('#edit-rpp').dropify({
			defaultFile: item.file,
			dictDefaultMessage: 'Upload Rpp'
		});
		$('#edit input[name="oldImage"]').val(item.file);

	}
	function viewRpp(file) {
		$('#view-rpp').modal('show');

		if (file == '') {
			$('#data-rpp').text('Belum ada file');
		} else {

			$('#data-rpp').html(`
				  <iframe src="<?= base_url(); ?>storage/guru/rpp/${file}#toolbar=0&navpanes=0&scrollbar=0" 
					  width="100%" 
					  height="600px"  ></iframe>`);
		}
	}

	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $(".container-data");
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
					url: `<?= base_url(); ?>admin/rpp/hapus`,
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
							rpp();
						}

					}
				})
			}
		})
	}



	function guru(id_guru = null) {
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
					$('#tambah select[name="id_guru"]').html(option);
					$('#edit-jadwal-pelajaran select[name="id_guru"]').html(option);

				} else {
					$('#edit select[name="id_guru"]').html(option);
				}
			}
		});
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
						option += `
				<option value="${item.id}" ${item.id == id_periode ? 'selected' : ''}>${item.periode}</option>
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
</script>