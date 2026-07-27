<style>
	.dropify-wrapper .dropify-message span {
		font-size: 14px;
		color: #333;
		/* bisa juga ubah warna */
		font-weight: 500;
		/* atau bold */
	}

	@media (max-width: 767.98px) {
		.tombol-edit {
			width: 100%;
		}
	}
</style>

<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>

	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Kegiatan"
							aria-describedby="inputGroupPrepend">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"
							onclick="riwayat_mengisi_result()"><i class="ri-calendar-line"></i></span>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div id="riwayat-mengisi"></div>
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
					<input type="hidden" name="id_jurnal_pegawai">

					<div class="col-md-12 mb-2">
						<label for="tanggal" class="form-label">Tanggal Kegiatan</label>
						<input name="tanggal" class="form-control" disabled></input>
					</div>
					<div class="col-md-12 mb-2">
						<label for="nama_pegawai" class="form-label">Nama Pegawai</label>
						<input name="nama_pegawai" class="form-control" disabled></input>
					</div>
					<div class="col-md-12 mb-2">
						<label for="kegiatan" class="form-label">Kegiatan</label>
						<textarea name="kegiatan" class="form-control"></textarea>
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
		riwayat_mengisi_result();

		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/jurnal/jurnal_kegiatan/edit'); ?>',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function (data) {
					$("#modal-edit").modal('hide');

					if (data.status == true) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupdate',
						})
						riwayat_mengisi_result();
					}
				}
			})
		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_riwayat_mengajar tbody tr'), jumlah);
		});
	})

	function riwayat_mengisi_result() {
		var tanggal = $('#flatpicker').val();
		var id_kelas = $('select[name="id_kelas"]').val();
		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_kegiatan/riwayat_mengisi_result'); ?>',
			type: 'POST',
			data: {
				tanggal,
				id_kelas
			},
			dataType: 'JSON',
			success: function (data) {

				var table = '';
				var no = 1;
				if (data.length == 0) {
					table = `<div class="card-mapel"><h5 class="text-center">Data Kosong</h5></div>`;
				} else {
					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));
						const tgl1 = parseDMY(item.tanggal);
						const tgl2 = parseDMY(item.tanggal_input);

						const selisihMs = tgl2 - tgl1;
						let selisihHari = selisihMs / (1000 * 60 * 60 * 24);
						selisihHari = Math.max(0, Math.floor(selisihHari));

						var tanggal_selisih = ''
						if (selisihHari == 0) {
							tanggal_selisih += '<span class="badge bg-danger">' + selisihHari + ' Hari </span>';

						} else {
							tanggal_selisih += '<span class="badge bg-danger">' + selisihHari + ' Hari </span>';
						}
						table += `
						<div class="card-mapel">
							<div>
								<div class="keterangan-mapel-kiri">
									<div class="keterangan-mapel-kiri-utama">
										<div>
											<p style="margin: 0; color: #188AE1;">Pegawai : ${item.nama_pegawai}</p>
											<h5 class="judul-mapel" style="margin: 0; font-size: 20px;">${item.kegiatan}</h5>
										</div>
										<span style="font-size: 12px;">Semester (${item.semester})</span>
									
									</div>
									</div>
									<div class="d-flex mt-2 gap-4">
										<div class="wrap-tanggal-kegiatan">
											<span style="font-size: 12px;color: #bbb;">Tanggal Kegiatan</span>
											<p style="font-size: 14px; margin: 0;">${item.tanggal}</p>
										</div>
										<div>
											<span style="font-size: 12px; color: #bbb;">Tanggal Input</span>
											<span class="text-tanggal-input">${item.tanggal_input}${tanggal_selisih}</span>
										</div>
									</div>
								</div> 
									<div class="mt-2 d-flex justify-content-end"> 
											<button type="button" class="btn btn-outline-warning tombol-edit" onclick="editPegawai('${detail}')">
												<i class="ri-edit-line"></i>
											</button>
										</div>
							</div>
						</div>
						`;
					})
				}
				$('#riwayat-mengisi').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#riwayat-mengisi'), jumlah_awal)
			}
		});
	}

	function editPegawai(detail) {
		$('#modal-edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#modal-edit input[name="id_jurnal_pegawai"]').val(item.id);
		$('#modal-edit textarea[name="kegiatan"]').val(item.kegiatan);
		$('#modal-edit input[name="tanggal"]').val(item.tanggal);
		$('#modal-edit input[name="nama_pegawai"]').val(item.nama_pegawai);


	}

	function paging($selector) {
		var jumlah_tampil = '10';

		if (typeof $selector == 'undefined') {
			$selector = $("#riwayat-mengisi .card-mapel");
		}

		window.tp = new Pagination('#pagination', {
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
					url: `<?= base_url(); ?>admin/kurikulum/guru/hapus`,
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
							guru();
						}

					}
				})
			}
		})
	} 
</script>