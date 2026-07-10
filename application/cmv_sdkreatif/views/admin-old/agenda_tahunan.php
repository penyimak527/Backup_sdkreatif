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
						<input type="text" class="form-control" id="cari-agenda_tahunan" placeholder="Cari Kegiatan"
							aria-describedby="inputGroupPrepend" onkeyup="agenda_tahunan()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<!-- <div class="table-responsive-sm">
			<table class="table table-bordered m-b-0" id="table_agenda_tahunan">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>Kegiatan</th>
						<th>Jenis Kegiatan</th>
						<th>Tanggal Mulai dan Selesai</th>
						<th>Tempat</th>
						<th>Penanggung Jawab</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div> -->
		<div class="container-data"></div>
		<div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center
			flex-wrap gap-2">
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
					<div class="mb-3">
						<label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
						<input type="text" name="nama_kegiatan" class="form-control" placeholder="Nama Kegiatan ..." />
					</div>
					<div class="mb-3">
						<label for="jenis_kegiatan" class="form-label">Jenis Kegiatan</label>
						<input type="text" name="jenis_kegiatan" class="form-control"
							placeholder="Jenis Kegiatan ..." />
					</div>
					<div class="mb-3">
						<label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
						<input type="text" id="flatpicker" name="tanggal_mulai" class="form-control"
							placeholder="Tanggal Mulai ..." />
					</div>
					<div class="mb-3">
						<label for="tanggal_makhir" class="form-label">Tanggal Akhir</label>
						<input type="text" id="flatpicker" name="tanggal_selesai" class="form-control"
							placeholder="Tanggal Akhir ..." />
					</div>
					<div class="mb-3">
						<label for="tempat" class="form-label">Tempat</label>
						<input type="text" name="tempat" class="form-control" placeholder="Tempat ..." />
					</div>
					<div class="mb-3">
						<label for="keterangan" class="form-label">Keterangan</label>
						<input type="text" name="keterangan" class="form-control" placeholder="Keterangan ..." />
					</div>
					<div class="mb-3">
						<label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
						<input type="text" name="penanggung_jawab" class="form-control"
							placeholder="Penanggung Jawab ..." />
					</div>
					<div class="mb-3">
						<label for="file" class="form-label">Upload Dokumen</label>
						<input type="file" name="file" class="form-control dropify"
							placeholder="Penanggung Jawab ..." />
					</div>
					<div class="mb-3">
						<label for="status" class="form-label">Status</label>
						<select name="status" class="form-control" placeholder="Penanggung Jawab ...">
							<option value="">Status</option>
							<option value="Rencana">Rencana</option>
							<option value="Berjalan">Berjalan</option>
							<option value="Selesai">Selesai</option>
							<option value="Dibatalkan">Dibatalkan</option>
							<option value="Tertunda">Tertunda</option>
							<option value="Menunggu Persetujuan">Menunggu Persetujuan</option>

						</select>
					</div>
					<div class="mb-3">
						<label for="id_periode" class="form-label">Periode</label>
						<select name="id_periode" class="form-control" placeholder="Penanggung Jawab ...">
							<option value="">Periode</option>
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
				<form id="form-edit" enctype="multipart/form-data">
					<input type="hidden" name="id_agenda">
					<input type="hidden" name="oldImage">
					<div class="mb-3">
						<label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
						<input type="text" name="nama_kegiatan" class="form-control" placeholder="Nama Kegiatan ..." />
					</div>
					<div class="mb-3">
						<label for="jenis_kegiatan" class="form-label">Jenis Kegiatan</label>
						<input type="text" name="jenis_kegiatan" class="form-control"
							placeholder="Jenis Kegiatan ..." />
					</div>
					<div class="mb-3">
						<label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
						<input type="text" id="flatpicker" name="tanggal_mulai" class="form-control"
							placeholder="Tanggal Mulai ..." />
					</div>
					<div class="mb-3">
						<label for="tanggal_selesai																																																										"
							class="form-label">Tanggal Akhir</label>
						<input type="text" id="flatpicker" name="tanggal_selesai" class="form-control"
							placeholder="Tanggal Akhir ..." />
					</div>
					<div class="mb-3">
						<label for="tempat" class="form-label">Tempat</label>
						<input type="text" name="tempat" class="form-control" placeholder="Tempat ..." />
					</div>
					<div class="mb-3">
						<label for="keterangan" class="form-label">Keterangan</label>
						<input type="text" name="keterangan" class="form-control" placeholder="Keterangan ..." />
					</div>
					<div class="mb-3">
						<label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
						<input type="text" name="penanggung_jawab" class="form-control"
							placeholder="Penanggung Jawab ..." />
					</div>
					<div class="mb-3">
						<label for="id_periode" class="form-label">Upload Dokumen</label>
						<input type="file" id="edit-file" name="file" class="form-control">
					</div>
					<div class="mb-3">
						<label for="status" class="form-label">Status</label>
						<select name="status" class="form-control" placeholder="Penanggung Jawab ...">

						</select>
					</div>
					<div class="mb-3">
						<label for="id_periode" class="form-label">Periode</label>
						<select name="id_periode" class="form-control" placeholder="Penanggung Jawab ...">
							<option value="">Periode</option>
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

<div class="modal fade" id="view-agenda" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Detail <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Kegiatan</label>
					<div class="col-sm-9" id="nama_kegiatan"> </div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Jenis Kegiatan</label>
					<div class="col-sm-9" id="jenis_kegiatan"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Tanggal Mulai</label>
					<div class="col-sm-9" id="tanggal_mulai"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Tanggal Selesai</label>
					<div class="col-sm-9" id="tanggal_selesai"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Tempat</label>
					<div class="col-sm-9" id="tempat"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Penanggung Jawab</label>
					<div class="col-sm-9" id="penanggung_jawab"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Keterangan</label>
					<div class="col-sm-9" id="keterangan"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Semester</label>
					<div class="col-sm-9" id="semester"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Periode</label>
					<div class="col-sm-9" id="periode"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Status</label>
					<div class="col-sm-9" id="status-data"></div>
				</div>
				<div class="row mb-1">
					<label class="col-sm-3 fw-bold">Dokumen</label>
					<div class="col-sm-9" id="data-agenda_tahunan"></div>
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
		agenda_tahunan();
		kelas();
		periode();

		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah")[0];
			var formData = new FormData(form);
			$.ajax({
				url: '<?= base_url('admin/agenda_tahunan/tambah'); ?>',
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
						agenda_tahunan();
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
				url: '<?= base_url('admin/agenda_tahunan/edit'); ?>',
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
						agenda_tahunan();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_agenda_tahunan tbody tr'), jumlah);
		});
	})

	function agenda_tahunan() {
		var search = $("#cari-agenda_tahunan").val();
		$.ajax({
			url: '<?= base_url('admin/agenda_tahunan/agenda_tahunan_result'); ?>',
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
						<td colspan="7" style="text-align: center;">Tidak ada data</td>
					</tr>
				`;
				} else {
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						table += `
						<div class="card-mapel">
							<p class="keterangan-hari">
								<span>Tanggal Mulai : ${item.tanggal_mulai}</span>
								<span>Tanggal Selesai : ${item.tanggal_selesai}</span>
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel">${item.nama_kegiatan}</h5>
									<h6 class="judul-mapel">${item.jenis_kegiatan}</h6>
									<p class="keterangan-jam-mapel">Tempat : ${item.tempat}</p>
									<p class="keterangan-jam-mapel">Penanggung Jawab : ${item.penanggung_jawab}</p>
								</div>
								<div class="keterangan-mapel-kanan-custom">
									<button type="button" class="btn btn-sm btn-outline-primary " onclick="viewAgenda('${detail}')"><i class="ri-eye-line"></i></button>
									<button type="button" onclick="editAgenda('${detail}')" class="btn btn-sm btn-outline-warning"><i class="ri-edit-2-line"></i></button>
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

	function editAgenda(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_agenda"]').val(item.id);
		$('#edit input[name="nama_kegiatan"]').val(item.nama_kegiatan);
		$('#edit input[name="jenis_kegiatan"]').val(item.jenis_kegiatan);
		$('#edit input[name="tanggal_mulai"]')[0]._flatpickr.setDate(item.tanggal_mulai, true);
		$('#edit input[name="tanggal_selesai"]')[0]._flatpickr.setDate(item.tanggal_selesai, true);

		$('#edit input[name="tempat"]').val(item.tempat);
		$('#edit input[name="keterangan"]').val(item.keterangan);
		$('#edit input[name="penanggung_jawab"]').val(item.penanggung_jawab);
		periode(item.id_periode);

		$('#edit select[name="status"]').html(` 
				<option value="Rencana" ${item.status == 'Rencana' ? 'selected' : ''}>Rencana</option>
							<option value="Berjalan" ${item.status == 'Berjalan' ? 'selected' : ''}>Berjalan</option>
							<option value="Selesai" ${item.status == 'Selesai' ? 'selected' : ''}>Selesai</option>
							<option value="Dibatalkan" ${item.status == 'Dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
							<option value="Tertunda" ${item.status == 'Tertunda' ? 'selected' : ''}>Tertunda</option>
							<option value="Menunggu Persetujuan" ${item.status == 'Menunggu Persetujuan' ? 'selected' : ''}>Menunggu Persetujuan</option>`);
		$('#edit-file').dropify({
			defaultFile: "<?php echo base_url('storage/agenda_tahunan/'); ?>" + item.file,
			dictDefaultMessage: 'Upload Agenda Tahunan'
		});
		$('#edit input[name="oldImage"]').val(item.file);

	}
	function viewAgenda(detail) {
		var item = JSON.parse(atob(detail));
		$('#view-agenda').modal('show');

		$('#nama_kegiatan').text(item.nama_kegiatan);
		$('#jenis_kegiatan').text(item.jenis_kegiatan);
		$('#tanggal_mulai').text(item.tanggal_mulai);
		$('#tanggal_selesai').text(item.tanggal_selesai);
		$('#semester').text(item.semester);
		$('#periode').text(item.periode);

		$('#status-data').text(item.status);

		$('#tempat').text(item.tempat);
		$('#keterangan').text(item.keterangan);
		$('#penanggung_jawab').text(item.penanggung_jawab);
		if (item.file == '') {
			$('#data-agenda_tahunan').text('Belum ada file');
		} else {
			$('#data-agenda_tahunan').html(`
				  <iframe src="<?= base_url(); ?>storage/agenda_tahunan/${item.file}#toolbar=0&navpanes=0&scrollbar=0" 
			  width="100%" 
			  height="600px"  ></iframe>`);
		}
	}


	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_agenda_tahunan tbody tr");
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
					url: `<?= base_url(); ?>admin/agenda_tahunan/hapus`,
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
							agenda_tahunan();
						}

					}
				})
			}
		})
	}



	function periode(id_periode = null) {
		$.ajax({
			url: '<?= base_url('admin/master/tahun_ajaran/tahun_ajaran_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Periode</option>';
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
	function kelas(id_kelas = null) {
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas/kelas_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Kelas</option>';
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
				var option = '<option value="">Tahun Ajaran</option>';
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