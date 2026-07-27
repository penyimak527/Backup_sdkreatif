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
						<input type="text" class="form-control" id="cari-nama-kas" placeholder="Cari Nama Kas"
							aria-describedby="inputGroupPrepend" onkeyup="kas_bank()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>
		<div id="data_kasbank">

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
						<label for="nama_kas" class="form-label">Nama Kas</label>
						<input type="text" name="nama_kas" class="form-control" placeholder="Nama Kas ..." />
					</div>
					<div class="mb-3">
						<label for="kategori" class="form-label">Kategori</label>
						<select name="kategori" class="form-control">
							<option value="">Pilih Kategori</option>
							<option value="Tunai">Tunai</option>
							<option value="Non Tunai">Non Tunai</option>
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
					<input type="hidden" name="id_kasbank">
					<div class="mb-3">
						<label for="nama_kas" class="form-label">Nama Kas</label>
						<input type="text" name="nama_kas" class="form-control" />
					</div>
					<div class="mb-3">
						<label for="kategori" class="form-label">Kategori</label>
						<select name="kategori" class="form-control">
							<option value="">Pilih Kategori</option>
							<option value="Tunai">Tunai</option>
							<option value="Non Tunai">Non Tunai</option>
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

<div class="modal fade" id="modalSaldo">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<h5>Saldo Awal Kas</h5>
			</div>
			<div class="modal-body">
				<input type="hidden" id="id_kasbank_saldo">
				<div class="table-responsive mb-3">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>Bulan</th>
								<th>Tahun</th>
								<th>Nominal</th>
							</tr>
						</thead>
						<tbody id="data-saldo">
						</tbody>
					</table>
					<button type="button" class="btn btn-success btn-sm" onclick="showFormSaldo()"><i
							class="ri-add-line"></i>Tambah Saldo</button>
				</div>
				<div id="formTambahSaldo" style="display:none">
					<form id="form-saldo">
						<input type="hidden" name="id_kasbank">
						<div class="row">
							<div class="col-md-6">
								<label>Bulan</label>
								<select name="bulan" class="form-control">
									<option value="">Pilih Bulan</option>
									<?php
									$bulan = array(
										"Januari",
										"Februari",
										"Maret",
										"April",
										"Mei",
										"Juni",
										"Juli",
										"Agustus",
										"September",
										"Oktober",
										"November",
										"Desember"
									);

									$currentMonth = date('m'); 
									foreach ($bulan as $k => $v):
										$bln = sprintf("%02d", $k + 1);
										$selected = ($bln == $currentMonth) ? 'selected' : ''; 
										?>
										<option value="<?= $bln ?>" <?= $selected ?>><?= $v ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-6">
								<label>Tahun</label>
								<select name="tahun" class="form-control">
									<?php
									$currentYear = date('Y');
									$startYear = $currentYear - 5; 
									$endYear = $currentYear + 5;   
									
									for ($i = $startYear; $i <= $endYear; $i++):
										$selected = ($i == $currentYear) ? 'selected' : ''; 
										?>
										<option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
									<?php endfor; ?>
								</select>
							</div>
						</div>
						<div class="mt-3">
							<label>Nominal</label>
							<div class="input-group">
								<span class="input-group-text">Rp</span>
								<input type="text" name="nominal" class="form-control" onkeyup="FormatCurrency(this)">
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-light" data-bs-dismiss="modal"> Tutup </button>
				<button id="btn-simpan-saldo" class="btn btn-primary" style="display:none">Simpan</button>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function () {
		kas_bank();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah");
			var formData = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/keuangan/kas_bank/tambah'); ?>',
				type: 'POST',
				data: formData,
				success: function (data) {
					$("#tambah").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						})
						kas_bank();
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
				url: '<?= base_url('admin/keuangan/kas_bank/edit'); ?>',
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
						kas_bank();
					}
				}
			})
		});
		$('#btn-simpan-saldo').click(function () {
			$.ajax({
				url: '<?= base_url('admin/keuangan/kas_bank/simpan_saldo_awal') ?>',
				type: 'POST',
				data: $('#form-saldo').serialize(),
				dataType: 'JSON',
				success: function (res) {
					if (!res.status) {
						Swal.fire(
							'Gagal', res.message, 'error'
						);
						return;
					}
					Swal.fire(
						'Berhasil',res.message,'success'
					);
					loadSaldo(
						$('#id_kasbank_saldo').val()
					);
					$('#form-saldo')[0].reset();
					$('#formTambahSaldo').hide();
					$('#btn-simpan-saldo').hide();
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_kasbank .card-mapel'), jumlah);
		});
	})
	function showFormSaldo() {
		$('#formTambahSaldo').slideDown();
		$('#btn-simpan-saldo').show();
	}
	function kas_bank() {
		var search = $("#cari-nama-kas").val();
		$.ajax({
			url: '<?= base_url('admin/keuangan/kas_bank/kas_bank_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#kas_bank').empty();

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
						let tombolSaldo = '';
						let tombolHapus = '';
						tombolSaldo = `
						<button class="btn btn-sm btn-success"
						onclick="setSaldoAwal(${item.id})">
							<i class="ri-add-line"></i>
						</button>
						`;
						if (item.saldo_awal == null) {
							tombolHapus = `
							<button class="btn btn-sm btn-danger" onclick="hapuskasbank(${item.id})">
								<i class="ri-delete-bin-line"></i>
							</button>
						`;
						} else {
							tombolHapus = ``;
						}
						let saldo = ''
						if (item.saldo_awal == null || item.saldo_awal == '') {
							saldo = '0';
						} else {
							saldo = NumberToMoney(item.saldo_awal);
						}
						table += `
						<div class="card-mapel">
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.keterangan}</h5>   
									  <p class="keterangan-jam-mapel">Kategori : ${item.kategori} </p>
								</div>
								 <div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										${tombolSaldo}
										 <button type="button" class="btn btn-sm btn-outline-warning " onclick="editkasbank('${detail}')"><i class="ri-edit-2-line"></i></button>
										 ${tombolHapus}
									</div>
								</div>
							</div>
						</div>`;
					});
				}
				$('#data_kasbank').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_kasbank .card-mapel'), jumlah_awal);
			}
		});
	}

	// function setSaldoAwal(id) {
	// 	$('#modalSaldo').modal('show');
	// 	$('select[name="tahun"]').val(new Date().getFullYear());
	// 	$('#form-saldo input[name="id_kasbank"]').val(id);
	// 	// $('#form-saldo input[name="tahun"]').val(tahun);
	// 	$('#form-saldo input[name="nominal"]').val('0');
	// }
	function setSaldoAwal(id) {
		$('#modalSaldo').modal('show');
		$('#formTambahSaldo').hide();
		$('#btn-simpan-saldo').hide();
		$('#id_kasbank_saldo').val(id);
		$('#form-saldo input[name=id_kasbank]').val(id);
		loadSaldo(id);
	}

	function loadSaldo(id) {
		$.ajax({
			url: '<?= base_url('admin/keuangan/kas_bank/get_saldo') ?>',
			type: 'POST',
			data: {
				id: id
			},
			dataType: 'JSON',
			success: function (res) {
				let html = '';
				if (res.length == 0) {
					html = `<tr><td colspan="3" class="text-center">Belum ada saldo</td></tr>`;
				} else {
					res.forEach(e => {
						html += `<tr>
						<td>${namaBulan(e.bulan)}</td>
						<td>${e.tahun}</td>
						<td>Rp. ${NumberToMoney(e.nominal)}</td>
						</tr>`;
					});
				}
				$('#data-saldo').html(html);
			}
		});
	}

	function namaBulan(bln) {
		const bulan = [
			'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		];
		return bulan[
			parseInt(bln) - 1
		];
	}

	function editkasbank(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_kasbank"]').val(item.id);
		$('#edit input[name="nama_kas"]').val(item.keterangan);
		$('#edit select[name="kategori"]').val(item.kategori);
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

	function hapuskasbank(id) {
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
					url: `<?= base_url(); ?>admin/keuangan/kas_bank/hapus`,
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
							kas_bank();
						}

					}
				})
			}
		})
	}
</script>