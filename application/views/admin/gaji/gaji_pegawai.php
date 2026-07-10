<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<div class="d-flex gap-2">
			<button type="button" class="btn btn-sm btn-outline-primary" onclick="gaji_pokok()"><i
					class="ri-loop-left-line"></i>Menghitung Gaji Pokok</button>
			<button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
				data-bs-target="#gaji-rendah"><i class="ri-cash-line"></i>Gaji Awal</button>
		</div>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
							aria-describedby="inputGroupPrepend" onkeyup="gaji_pegawai()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div class="table-responsive">
			<table class="table table-bordered table-hover table-sm" id="table_gaji_pegawai">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama Pegawai</th>
						<th>Gaji Pokok</th>
						<th>Struktural</th>
						<th>Pendidikan</th>
						<th>Wali Kelas</th>
						<th>Aksi</th>
					</tr>

					<tr>
						<th class="text-center">#</th>
						<th>
							<select id="pegawai_tambah" class="form-control form-control-sm">

							</select>
						</th>

						<th>
							<input type="text" id="gaji_pokok_tambah" class="form-control form-control-sm" readonly>
						</th>

						<th>
							<input type="text" id="struktur_tambah" class="form-control form-control-sm"
								onkeyup="FormatCurrency(this)">
						</th>

						<th>
							<input type="text" id="pendidikan_tambah" class="form-control form-control-sm"
								onkeyup="FormatCurrency(this)">
						</th>

						<th>
							<input type="text" id="wali_tambah" class="form-control form-control-sm"
								onkeyup="FormatCurrency(this)">
						</th>
						<th>
							<button class="btn btn-primary btn-sm" onclick="simpanBaru()">Simpan</button>
						</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
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

<div class="modal fade" id="gaji-rendah" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Setting Gaji</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-rendah-gaji">
					<div class="mb-3">
						<label for="pegawai" class="form-label">Gaji Terendah</label>
						<div class="input-group">
							<span class="input-group-text">Rp</span>
							<input type="text" id="gaji_awal" class="form-control" name="gaji_rendah"
								onkeyup="FormatCurrency(this)" placeholder="Gaji ...">
						</div>
					</div>
					<small class="text-muted">Nilai ini digunakan sebagai acuan kenaikan gaji pegawai.</small>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-simpan-gaji-rendah">Simpan</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		pegawai();
		gaji_pegawai();
		$("#btn-simpan-gaji-rendah").click(function () {
			$('#btn-simpan-gaji-rendah').prop('disabled', true);
			$('#btn-simpan-gaji-rendah').html('Diproses');
			var form = $("#form-rendah-gaji");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/gaji/gaji_pegawai/edit_gaji_rendah'); ?>',
				type: 'POST',
				data: formData,
				dataType: 'JSON',
				success: function (data) {
					$("#gaji_rendah").modal('hide');
					if (data.status == true) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						}).then((result) => {
							if (result.value) {
								location.reload();
							}
						})

						$("#form-rendah-gaji")[0].reset();
						$('#btn-simpan-gaji-rendah').prop('disabled', false);
						$('#btn-simpan-gaji-rendah').html('Simpan');
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_gaji_pegawai tbody tr'), jumlah);
		});
		$('#gaji-rendah').on('show.bs.modal', function () {
			$.ajax({
				url: '<?= base_url("admin/gaji/gaji_pegawai/get_gaji_awal") ?>',
				dataType: 'json',
				success: function (res) {
					$('#gaji_awal').val(NumberToMoney(res.gaji_terendah));
				}
			});
		});
		$('#pegawai_tambah').change(function () {
			$.ajax({
				url: '<?= base_url("admin/gaji/gaji_pegawai/hitung_gaji_pokok") ?>',
				type: 'POST',
				data: {
					id_pegawai: $(this).val()
				},
				dataType: 'json',
				success: function (res) {
					$('#gaji_pokok_tambah').val(NumberToMoney(res ?? 0));
				}
			});

		});
		$(document).on('change', '[id^="pegawai_edit_"]', function () {
			let id = $(this).attr('id').replace('pegawai_edit_', '');
			$.ajax({
				url: '<?= base_url("admin/gaji/gaji_pegawai/hitung_gaji_pokok") ?>',
				type: 'POST',
				data: {
					id_pegawai: $(this).val()
				},
				dataType: 'json',
				success: function (res) {
					$('#gaji_pokok_edit_' + id).val(NumberToMoney(res ?? 0));
				}
			});

		});
	})
	function simpanBaru() {
		$.ajax({
			url: '<?= base_url("admin/gaji/gaji_pegawai/tambah") ?>',
			type: 'POST',
			data: {
				id_pegawai: $('#pegawai_tambah').val(),
				gaji_pokok: $('#gaji_pokok_tambah').val(),
				struktural: $('#struktur_tambah').val(),
				pendidikan: $('#pendidikan_tambah').val(),
				wali_kelas: $('#wali_tambah').val()
			},
			dataType: 'json',
			success: function (res) {
				$('#pegawai_tambah').val('');
				$('#gaji_pokok_tambah').val('');
				$('#struktur_tambah').val('');
				$('#pendidikan_tambah').val('');
				$('#wali_tambah').val('');
				$('#total_tambah').val('');
				if (res.status) {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil',
						text: 'Data berhasil disimpan'
					});
					location.reload();
				}
			}
		});
	}
	function pegawai(id_pegawai = null, id_t = null) {
		$.ajax({
			url: '<?= base_url('admin/gaji/gaji_pegawai/pegawai_result'); ?>',
			type: 'POST',
			data: { id_pegawai },
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Nama Pegawai</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {
						option += `
						<option value="${item.id}" ${item.id == id_pegawai ? 'selected' : ''}>${item.nama_pegawai}</option>`;
					});
				}
				if (id_pegawai == null) {
					$('#pegawai_tambah').html(option);
					// $('#tambah select[name="id_pegawai"]').html(option);
				} else {
					$('#pegawai_edit_' + id_t).html(option);
					// $('#edit select[name="id_pegawai"]').html(option);
				}
			}
		});
	}

	function gaji_pegawai() {
		var cari = $('#cari-pegawai').val();
		$.ajax({
			url: '<?= base_url('admin/gaji/gaji_pegawai/gaji_pegawai_result'); ?>',
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
					<tr>
						<td colspan="8" style="text-align: center;">Tidak ada data</td>
					</tr>`;
				} else {
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						table += `
<tr id="row_${item.id}">
	<td class="text-center">${no++}</td>
	<td>${item.nama_pegawai}</td>
	<td class="text-end">
		Rp. ${NumberToMoney(item.gaji_pokok)}
	</td>
	<td class="text-end">
		Rp. ${NumberToMoney(item.struktural)}
	</td>
	<td class="text-end">
		Rp. ${NumberToMoney(item.tunjangan_pendidikan)}
	</td>
	<td class="text-end">
		Rp. ${NumberToMoney(item.wali_kelas)}
	</td>
	<td>
		<button class="btn btn-warning btn-sm" style="width: 35px; height: 35px;" onclick="editGaji('${no}','${item.id}','${detail}')">
			<i class="ri-edit-line"></i>
		</button>

		<button class="btn btn-danger btn-sm" style="width: 35px; height: 35px;" onclick="hapus(${item.id})">
			<i class="ri-delete-bin-line"></i>
		</button>
	</td>
</tr>`;
					});
				}
				$('#table_gaji_pegawai tbody').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#table_gaji_pegawai tbody tr'), jumlah_awal);
			}
		});
	}
	let originalRows = {};
	function editGaji(no, id, detail) {
		let item = JSON.parse(atob(detail));
		let row = $('#row_' + id);

		// if (!originalRows[id]) {
		originalRows[id] = row.html();
		// }
		pegawai(item.id_pegawai, id);
		row.html(`

<td>${no}</td>

<td>
<select id="pegawai_edit_${id}" class="form-control form-control-sm">

</select>
</td>

<td>
<input id="gaji_pokok_edit_${id}" class="form-control form-control-sm" onkeyup="FormatCurrency(this);" readonly value="${NumberToMoney(item.gaji_pokok)}">
</td>

<td>
<input id="struktur_edit_${id}" class="form-control form-control-sm" onkeyup="FormatCurrency(this);" value="${NumberToMoney(item.struktural)}">
</td>

<td>
<input id="pendidikan_edit_${id}" class="form-control form-control-sm" onkeyup="FormatCurrency(this);" value="${NumberToMoney(item.tunjangan_pendidikan)}">
</td>

<td>
<input id="wali_edit_${id}" class="form-control form-control-sm" onkeyup="FormatCurrency(this);" value="${NumberToMoney(item.wali_kelas)}">
</td>
<td>
<button onclick="simpanEdit(${id})" class="btn btn-primary btn-sm " style="height: 30px; width : 60px;margin-top: 1px;">
Simpan
</button>

<button onclick="batalEdit(${id})" class="btn btn-secondary btn-sm " style="height: 30px; width : 60px;margin-top: 1px;">
Batal
</button>
</td>`);
	}
	function batalEdit(id) {
		$('#row_' + id).html(originalRows[id]);
		delete originalRows[id];
	}
	function simpanEdit(id) {
		$.ajax({
			url: '<?= base_url("admin/gaji/gaji_pegawai/edit") ?>',
			type: 'POST',
			data: {
				id_gaji: id,
				id_pegawai: $('#pegawai_edit_' + id).val(),
				gaji_pokok: $('#gaji_pokok_edit_' + id).val(),
				struktural: $('#struktur_edit_' + id).val(),
				pendidikan: $('#pendidikan_edit_' + id).val(),
				wali_kelas: $('#wali_edit_' + id).val()
			},
			dataType: 'json',
			success: function (res) {
				if (res.status) {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil'
					});
					location.reload();
				}
			}
		});
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

	function gaji_pokok(){
		Swal.fire({
			title: 'Hitung Gaji Pokok',
			text: 'Anda yakin ingin menghitung gaji pokok?',
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
					url: `<?= base_url(); ?>admin/gaji/gaji_pegawai/gaji_pokok`,
					data: { },
					dataType: 'json',
					beforeSend: function () {
                        Swal.fire({
                            title: 'Menghitung Gaji Pokok...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
					success: function (data) {
						if (data.status == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: data.message,
							})
							gaji_pegawai();
						}else{
							Swal.fire({
								icon: 'error',
								title: 'Gagal',
								text: data.message,
							})
							gaji_pegawai();
						}

					}
				})
			}
		})
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
					url: `<?= base_url(); ?>admin/gaji/gaji_pegawai/hapus`,
					data: {
						id: id
					},
					dataType: 'json',
					success: function (data) {
						if (data.status == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Data berhasil dihapus',
							})
							gaji_pegawai();
						}

					}
				})
			}
		})
	}

</script>