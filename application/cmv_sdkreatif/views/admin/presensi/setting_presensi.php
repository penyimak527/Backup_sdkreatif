<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		    <div class="d-flex gap-2">
		<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
				class="ri-add-line"></i>Tambah</button>
</div>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-aturan" placeholder="Cari Nama Aturan"
							aria-describedby="inputGroupPrepend" onkeyup="presensi_setting()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_setting">

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
						<label for="nama" class="form-label">Nama Aturan</label>
						<input type="text" name="nama_aturan" class="form-control" placeholder="Nama Aturan ..." />
					</div>
					<div class="mb-3">
						<label for="jam" class="form-label">Jam Masuk</label>
						<input type="text" name="jam_masuk" class="form-control" placeholder="Jam Masuk ..." onkeyup="formatTimeInput(this)" maxlength="8"/>
					</div>
					<div class="mb-3">
						<label for="jam" class="form-label">Jam Pulang</label>
						<input type="text" name="jam_pulang" class="form-control" placeholder="Jam Pulang ..." onkeyup="formatTimeInput(this)" maxlength="8" />
					</div>
					<div class="mb-3">
						<label for="status" class="form-label">Status</label>
						<select name="status" class="form-control" id="status_peraturan">
							<option value="">Pilih Status</option>
							<option value="aktif">Aktif</option>
							<option value="tidak aktif">Tidak Aktif</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="status" class="form-label">Status Jam</label>
						<select name="status_jam" class="form-control" id="status_peraturan_jam">
							<option value="">Pilih Status Jam</option>
							<option value="Kepala Sekolah">Kepala Sekolah</option>
							<option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
							<option value="umum">Umum</option>
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

<script>
	$(document).ready(function () {
		presensi_setting();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').html('Sedang Diproses');
			var form = $("#form-tambah");
			var formData = form.serialize();
			const status = $('#status_peraturan').val();
			if (status == '') {
				Swal.fire({
							icon: 'warning',
							title: 'Status Kosong',
							text: 'Status Wajib diisi!',}).then((result) => {
				$('#btn-simpan').prop('disabled', false);
			$('#btn-simpan').html('Simpan');

							});
				return;
			}
			$.ajax({
				url: '<?= base_url('admin/presensi/setting_presensi/tambah'); ?>',
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
				url: '<?= base_url('admin/presensi/setting_presensi/edit'); ?>',
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
						presensi_setting();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_setting .card-mapel'), jumlah);
		});
	})

	function presensi_setting() {
		var cari = $('#cari-aturan').val();
		$.ajax({
			url: '<?= base_url('admin/presensi/setting_presensi/result_data'); ?>',
			type: 'POST',
			data: {
				search: cari
			},
			dataType: 'JSON',
			success: function (data) {
				$('#data_setting').empty();
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
						let statusbadge = '';
                        if (item.status == 'tidak aktif') {
                            statusbadge = `<span class="badge bg-danger">Tidak Aktif</span>`;
                        }else{
                            statusbadge = `<span class="badge bg-success">Aktif</span>`;
                        }
						table += `
							<div class="card-mapel">
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.nama_aturan}</h5>   
									  <p class="keterangan-jam-mapel">Status Pengaturan Presensi: ${statusbadge} </p>
									  <p class="keterangan-jam-mapel">Status Jam: ${(item.status_jam && item.status_jam.trim()) || '-'} </p>
									  <p class="keterangan-jam-mapel">Jam Masuk: ${item.jam_masuk}</p>
									  <p class="keterangan-jam-mapel">Jam Pulang: ${item.jam_pulang}</p>
								</div>
								 <div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										<a href="<?= base_url('admin/presensi/setting_presensi/view/'); ?>${item.id}" class="btn btn-sm btn-outline-warning w-50"><i class="ri-edit-2-line"></i></a>
											<button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
									</div>
								</div>
							</div>
						</div>
						`;
					});
				}
				$('#data_setting').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_setting .card-mapel'), jumlah_awal);
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
					url: `<?= base_url(); ?>admin/presensi/setting_presensi/hapus`,
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
							presensi_setting();
						}

					}
				})
			}
		})
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