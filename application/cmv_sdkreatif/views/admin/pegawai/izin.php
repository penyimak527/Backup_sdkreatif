<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
				class="ri-add-line"></i>Tambah</button>

	</div>
	<div class="card-body">
		<?php if ($this->session->userdata('admin')['level'] == 'Admin'): ?>
			<div class="row">
				<div class="col-md-3">
					<div class="mb-3">
						<div class="input-group">
							<input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
								aria-describedby="inputGroupPrepend" onkeyup="pegawai_izin()">
							<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
									class="ri-search-line"></i></span>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="container-data">

		</div>
		<div>
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

					<?php if ($level == 'Admin'): ?>
						<div class="mb-3">
							<label for="pegawai" class="form-label">Nama Pegawai</label>
							<select name="id_pegawai" class="form-control">
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="id_pegawai" value="<?= $id_pegawai; ?>">
					<?php endif; ?>
					<div class="mb-3">
						<label for="pegawai" class="form-label">Tanggal Izin</label>
						<input type="text" id="flatpicker" name="tgl_tidak_hadir" class="form-control"
							placeholder="Tanggal Izin ..." />
					</div>

					<div class="mb-3">
						<label for="id_izin" class="form-label">Keterangan</label>
						<select name="id_izin" class="form-control">

						</select>
					</div>
					<div class="mb-3">
						<label for="alasan_tidak_hadir" class="form-label">Alasan Tidak Hadir</label>
						<textarea name="alasan_tidak_hadir" class="form-control"
							placeholder="Alasan Tidak Hadir ..."></textarea>
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
					<input type="hidden" name="id_izin_pegawai">

					<div class="mb-3">
						<label for="pegawai" class="form-label">Nama Pegawai</label>
						<select name="id_pegawai" class="form-control">

						</select>
					</div>
					<div class="mb-3">
						<label for="pegawai" class="form-label">Tanggal Izin</label>
						<input type="text" id="flatpicker" name="tgl_tidak_hadir" class="form-control"
							placeholder="Tanggal Izin ..." />
					</div>

					<div class="mb-3">
						<label for="id_izin" class="form-label">Keterangan</label>
						<select name="id_izin" class="form-control">
							<option value="">Pilih Keterangan</option>
							<?php foreach ($izin as $m) { ?>
								<option value="<?= $m['id']; ?>"><?= $m['nama_izin']; ?></option> <?php } ?>
						</select>
					</div>
					<div class="mb-3">
						<label for="alasan_tidak_hadir" class="form-label">Alasan Tidak Hadir</label>
						<textarea name="alasan_tidak_hadir" class="form-control"
							placeholder="Alasan Tidak Hadir ..."></textarea>
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
		pegawai_izin();
		pegawai();
		izin();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/pegawai/izin/tambah'); ?>',
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
						pegawai_izin();
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
						})
						pegawai_izin();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('.container-data .card-mapel'), jumlah);
		});
	})

	function pegawai_izin() {
		var search = $('#cari-pegawai').val();

		$.ajax({
			url: '<?= base_url('admin/pegawai/izin/izin_pegawai_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#pegawai').empty();

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


						if (item.status_approval == 0) {
							status_approval = '<span class="badge bg-warning">Belum Disetujui</span>';
						} else if (item.status_approval == 1) {
							status_approval = '<span class="badge bg-success">Disetujui</span>';
						} else {
							status_approval = '<span class="badge bg-danger">Ditolak</span>';
						}
						table += `<div class="card-mapel">
							<p class="keterangan-hari">
								<span>Tanggal Izin : ${item.tgl_tidak_hadir}</span>
								<span>${status_approval}</span>
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel">${item.nama_pegawai}</h5>
									<p class="keterangan-jam-mapel">Keterangan : ${item.keterangan}</p>
									<p class="keterangan-jam-mapel">Alasan : ${item.alasan_tidak_hadir}</p>
								</div>
								<div class="keterangan-mapel-kanan-custom">
									<button type="button" onclick="editIzin('${detail}')" class="btn btn-sm btn-outline-warning me-1"><i class="ri-edit-2-line"></i></button>
									<button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
								</div>
							</div>
						</div>
						`;
					});
				}

				$('.container-data').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('.container-data .card-mapel'), jumlah_awal);
			}
		});
	}

	function editIzin(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_izin_pegawai"]').val(item.id);
		pegawai(item.id_pegawai);
		$('#edit input[name="tgl_tidak_hadir"]').val(item.nama_pegawai);
		$('#edit input[name="tgl_tidak_hadir"]')[0]._flatpickr.setDate(item.tgl_tidak_hadir, true);
		$('#edit input[name="no_tlp"]').val(item.no_tlp);

	}

	function paging(selector, jumlah_tampil = 10) {


		window.tp = new Pagination('#pagination', {
			itemsCount: selector.length,
			pageSize: parseInt(jumlah_tampil),
			onPageChange: function (paging) {
				let start = paging.pageSize * (paging.currentPage - 1);
				let end = start + paging.pageSize;
				let $rows = selector;

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
					url: `<?= base_url(); ?>admin/pegawai/izin/hapus`,
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
							pegawai_izin();
						}

					}
				})
			}
		})
	}

	function pegawai(id_pegawai = null) {
		$.ajax({
			url: '<?= base_url('admin/pegawai/pegawai_jabatan/pegawai_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Nama Pegawai</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_pegawai ? 'selected' : ''}>${item.nama_pegawai}</option>
						`;
					});
				}
				if (id_pegawai == null) {
					$('#tambah select[name="id_pegawai"]').html(option);
				} else {
					$('#edit select[name="id_pegawai"]').html(option);
				}
			}
		});
	}
	function izin(id_izin = null) {
		$.ajax({
			url: '<?= base_url('admin/master/izin/izin_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Keterangan</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_izin ? 'selected' : ''}>${item.nama_izin}</option>
						`;
					});
				}
				if (id_izin == null) {
					$('#tambah select[name="id_izin"]').html(option);
				} else {
					$('#edit select[name="id_izin"]').html(option);
				}
			}
		});
	}

</script>