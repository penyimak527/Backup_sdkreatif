<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<div class="d-flex gap-2">
			<a type="button" class="btn btn-sm btn-outline-success"
				href="<?= base_url() ?>admin/pegawai/pegawai_jabatan/print_qr_user"><i class="ri-qr-code-fill"></i>Print
				Id Card</a>
			<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
				data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
		</div>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
							aria-describedby="inputGroupPrepend" onkeyup="pegawai()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_pegawai">

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
						<label for="pegawai" class="form-label">Nama Pegawai</label>
						<input type="text" name="nama_pegawai" class="form-control" placeholder="Nama Pegawai ..." />
					</div>
					<div class="mb-3">
						<label for="pegawai" class="form-label">Jabatan</label>
						<select name="id_jabatan[]" class="select2 form-control select2-multiple" data-toggle="select2"
							multiple="multiple" onchange="mapel()">
							<?php foreach ($jabatan as $j) { ?>
								<option value="<?= $j['id']; ?>"><?= $j['jabatan']; ?></option> <?php } ?>
						</select>
					</div>
					<div class="mb-3" id="nip" style="display: none;">
						<label for="pegawai" class="form-label">NBM</label>
						<input type="text" name="nbm" class="form-control" placeholder="NBM ..." />
					</div>
					<div class="mb-3" id="mapel" style="display: none;">
						<label for="id_mapel" class="form-label">Mata Pelajaran yang diajarkan</label>
						<select name="id_mapel[]" class="select2 form-control select2-multiple" data-toggle="select2"
							multiple="multiple">
							<?php foreach ($mapel as $m) { ?>
								<option value="<?= $m['id']; ?>"><?= $m['mapel']; ?></option> <?php } ?>
						</select>
					</div>
					<div class="mb-3">
						<label for="pegawai" class="form-label">Jenis Kelamin</label>
						<select name="jk" class="form-control">
							<option value=""> Pilih Jenis Kelamin</option>
							<option value="Laki-laki">Laki-laki</option>
							<option value="Perempuan">Perempuan</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="tempat_lahir" class="form-label">Tempat Lahir</label>
						<input type="text" name="tempat_lahir" class="form-control" placeholder="Tempat Lahir ..." />
					</div>
					<div class="mb-3">
						<label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
						<input type="text" id="flatpicker" name="tanggal_lahir" class="form-control"
							placeholder="Tanggal Lahir ..." />
					</div>
					<div class="mb-3">
						<label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir</label>
						<select name="pendk_terakhir" class="form-control">
							<option value="">Pilih Pendidikan Terakhir</option>
							<option value="S3">S3</option>
							<option value="S2">S2</option>
							<option value="S1">S1</option>
							<option value="SMA">SMA</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="tmt" class="form-label">Mulai Tanggal </label>
						<input type="text" id="flatpicker" name="tmt" class="form-control"
							placeholder="Mulai Tanggal ..." />
					</div>
					<div class="mb-3">
						<label for="no_tlp" class="form-label">No Telepon</label>
						<input type="text" name="no_tlp" class="form-control" placeholder="No Telepon ..." />
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
		pegawai();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/pegawai/pegawai_jabatan/tambah'); ?>',
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
				url: '<?= base_url('admin/pegawai/pegawai_jabatan/edit'); ?>',
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
						pegawai();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_pegawai .card-mapel'), jumlah);
		});
	})

	function pegawai() {
		var cari = $('#cari-pegawai').val();
		$.ajax({
			url: '<?= base_url('admin/pegawai/pegawai_jabatan/pegawai_result'); ?>',
			type: 'POST',
			data: {
				search: cari
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
						table += `
						 
							<div class="card-mapel">
							  <p class="keterangan-hari">No Telepon : ${item.no_tlp} </p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.nama_pegawai}</h5>   
									  <p class="keterangan-jam-mapel">Jenis Kelamin : ${item.jk} </p>
									  <p class="keterangan-jam-mapel">TTL : ${item.tempat_lahir}, ${item.tanggal_lahir} </p>
								</div>
								 <div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										<a href="<?= base_url('admin/pegawai/pegawai_jabatan/print_qr_user/'); ?>${item.id}" class="btn btn-sm btn-outline-success w-50"><i class="ri-qr-code-fill"></i></a>
										<a href="<?= base_url('admin/pegawai/pegawai_jabatan/view/'); ?>${item.id}" class="btn btn-sm btn-outline-warning w-50"><i class="ri-edit-2-line"></i></a>
											<button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
									</div>
								</div>
							</div>
						</div>
						`;
					});
				}
				$('#data_pegawai').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_pegawai .card-mapel'), jumlah_awal);
			}
		});
	}

	function editpegawai(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_pegawai"]').val(item.id);
		$('#edit select[name="jk"]').html(`
		<option value="Laki-laki" ${item.jk == 'Laki-laki' ? 'selected' : ''}>Laki-laki</option>
		<option value="Perempuan" ${item.jk == 'Perempuan' ? 'selected' : ''}>Perempuan</option>
		`);
		$('#form-edit select[name="pendk_terakhir"]').html(`
					<option value="S3" ${data.pendidikan_terakhir == 'S3' ? 'selected' : ''}>S3</option>
					<option value="S2" ${data.pendidikan_terakhir == 'S2' ? 'selected' : ''}>S2</option>
					<option value="S1" ${data.pendidikan_terakhir == 'S1' ? 'selected' : ''}>S1</option>
					<option value="SMA" ${data.pendidikan_terakhir == 'SMA' ? 'selected' : ''}>SMA</option>
					`);
		$('#edit input[name="nama_pegawai"]').val(item.nama_pegawai);
		$('#edit input[name="tempat_lahir"]').val(item.tempat_lahir);
		$('#edit input[name="tanggal_lahir"]')[0]._flatpickr.setDate(item.tanggal_lahir, true);
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
					url: `<?= base_url(); ?>admin/pegawai/pegawai_jabatan/hapus`,
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
							pegawai();
						}

					}
				})
			}
		})
	}

	function mapel() {
		let selected = $('select[name="id_jabatan[]"]').val();
		if (selected.includes("1")) {
			$('#mapel').show();
			$('#nip').show();
		} else {
			$('#mapel').hide();
			$('#nip').hide();
		}
	}

</script>