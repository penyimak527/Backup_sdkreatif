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
						<input type="text" class="form-control" id="cari-angkatan" placeholder="Cari Angkatan"
							aria-describedby="inputGroupPrepend" onkeyup="angkatan()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_angkatan">
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
					<?php $tahun_awal = 2009;
					$tahun_akhir = date('Y') + 5; ?>
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="simpleinput" class="form-label">Rentang Tahun Awal</label>
								<select name="rentang_tahun_awal" class="form-control">
									<option value="">Pilih Tahun</option>
									<?php for ($i = $tahun_awal; $i <= $tahun_akhir; $i++): ?>
										<option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label for="simpleinput" class="form-label">Rentang Tahun Akhir</label>
								<select name="rentang_tahun_akhir" class="form-control">
									<option value="">Pilih Tahun</option>
									<?php for ($i = $tahun_awal; $i <= $tahun_akhir; $i++): ?>
										<option value="<?= $i ?>"><?= $i ?></option> <?php endfor; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="mb-3">
						<label for="simpleinput" class="form-label">Kenaikan Gaji</label>
						<div class="input-group">
							<span class="input-group-text">Rp</span>
							<input type="text" name="kenaikan_gaji" class="form-control" placeholder="Nominal ..."
								onkeyup="FormatCurrency(this);">
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
				<form id="form-edit">
					<input type="hidden" id="id-angkatan" name="id_angkatan" class="form-control">
					<?php $tahun_awal = 2009;
					$tahun_akhir = date('Y') + 5; ?>
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="simpleinput" class="form-label">Rentang Tahun Awal</label>
								<select name="rentang_tahun_awal" class="form-control">
									<option value="">Pilih Tahun</option>
									<?php for ($i = $tahun_awal; $i <= $tahun_akhir; $i++): ?>
										<option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label for="simpleinput" class="form-label">Rentang Tahun Akhir</label>
								<select name="rentang_tahun_akhir" class="form-control">
									<option value="">Pilih Tahun</option>
									<?php for ($i = $tahun_awal; $i <= $tahun_akhir; $i++): ?>
										<option value="<?= $i ?>"><?= $i ?></option> <?php endfor; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="mb-3">
						<label for="simpleinput" class="form-label">Kenaikan Gaji</label>
						<div class="input-group">
							<span class="input-group-text">Rp</span>
							<input type="text" name="kenaikan_gaji" class="form-control" placeholder="Nominal ..."
								onkeyup="FormatCurrency(this);">
						</div>
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
		angkatan();

		$("#btn-simpan").click(function () {
			var form = $("#form-tambah");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/master/angkatan/tambah'); ?>',
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
						angkatan();
						$("#form-tambah")[0].reset();
					}
				}
			})
		})
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/master/angkatan/edit'); ?>',
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
						angkatan();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_angkatan .card-mapel'), jumlah);
		});
	})

	function angkatan() {
		var search = $("#cari-angkatan").val();
		$.ajax({
			url: '<?= base_url('admin/master/angkatan/angkatan_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#data_angkatan').empty();

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
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.tahun_awal} - ${item.tahun_akhir}</h5> 
									<p class="keterangan-jam-mapel">Kenaikan Gaji : Rp. ${NumberToMoney(item.kenaikan_gaji)}</p>
								</div>
								<div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
									<button type="button" class="btn btn-outline-warning w-50" onclick="editAngkatan('${detail}')">
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
				$('#data_angkatan').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_angkatan .card-mapel'), jumlah_awal);
			}
		});
	}

	function editAngkatan(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id-angkatan').val(item.id);
		$('#form-edit select[name="rentang_tahun_awal"]').val(item.tahun_awal);
		$('#form-edit select[name="rentang_tahun_akhir"]').val(item.tahun_akhir);
		$('#form-edit input[name="kenaikan_gaji"]').val(NumberToMoney(item.kenaikan_gaji));
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
					url: `<?= base_url(); ?>admin/master/angkatan/hapus`,
					data: {
						id: id
					},
					dataType: 'json',
					success: function (data) {
						console.log(data);
						if (data.status) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: data.message,
							});
							angkatan();
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Tidak Dapat Dihapus',
								text: data.message,
							});
						}
					}
				})
			}
		})
	}
</script>