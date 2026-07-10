<form id="form-edit">

	<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
		<div class="row">
			<div class="col-md-4">
				<div class="mb-2">
					<div class="input-group">
						<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Awal"
							aria-describedby="inputGroupPrepend" name="tanggal_awal">
						<span class="input-group-text bg-primary text-white"> <i class="ri-calendar-line"></i></span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="mb-2">
					<div class="input-group">
						<input type="text" class="form-control" name="tanggal_akhir" id="flatpicker"
							placeholder="Tanggal Akhir" aria-describedby="inputGroupPrepend">
						<span class="input-group-text bg-primary text-white"> <i class="ri-calendar-line"></i></span>
					</div>
				</div>
			</div>
			<div class="col-md-2">
				<button type="button" class="btn btn-primary" onclick="approval_izin()"><i
						class="ri-search-line"></i></button>
			</div>
		</div>
		<div class="d-flex flex-row gap-2 mb-3">
			<button type="button" class="btn btn-sm btn-success" id="btn-update">
				<i class="ri-check-line me-2"></i> Setujui
			</button>
			<select class="form-select" id="status-filter" name="status">
				<option value="">Pilih Status</option>
				<option value="1">Disetujui</option>
				<option value="2">Ditolak</option>
			</select>
		</div>
	</div>
	<div id="data_approval_izin"></div>
</form>
<div class="d-flex justify-content-center align-items-center mb-3 w-100">
	<ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination"></ul>

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
						<label for="level" class="form-label">Level</label>
						<input type="text" name="level" class="form-control" placeholder="Level ..." />
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
					<input type="hidden" name="id_level">
					<div class="mb-3">
						<label for="level" class="form-label">level</label>
						<input type="text" name="level" class="form-control" />
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
		approval_izin();

		$(document).on('click', 'input[id^="check-all-"]', function () {
			let no = $(this).attr('id').split('-')[2]; // misal: dari "check-all-1", ambil "1"
			$(`input[id^="cek_tanggal-"][id$="-${no}"]`).prop('checked', $(this).is(':checked'));
		});



		$("#btn-update").click(function () {

			Swal.fire({
				title: 'Notifikasi',
				text: 'Anda yakin ingin mengubah status izin?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Setujui',
				cancelButtonText: 'Tolak'
			}).then((result) => {

				if (result.isConfirmed) {
					var form = $("#form-edit");
					var formData = form.serialize();
					$.ajax({
						url: `<?= base_url(); ?>admin/approval/update_approval`,
						type: 'POST',
						data: formData,
						dataType: 'json',
						success: function (data) {
							if (data.status == true) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: 'Data berhasil diupdate',
								})
								approval_izin();
							}
						}
					})
				}
			})

		})
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_approval_izin tbody tr'), jumlah);
		});



	})

	function approval_izin() {
		var tanggal_awal = $(`#flatpicker input[name="tanggal_awal"]`).val();
		var tanggal_akhir = $(`#flatpicker input[name="tanggal_akhir"]`).val();
		$.ajax({
			url: '<?= base_url('admin/approval/izin_result'); ?>',
			type: 'POST',
			data: {
				tanggal_awal,
				tanggal_akhir
			},
			dataType: 'JSON',
			success: function (data) {


				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<div class="card">
						<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
							<span class="header-title">Tidak ada data</span> 
						</div>
					</div>
				`;
					$("#pagination").hide()
				} else {


					var table = "";
					var no = 1;
					Object.entries(data).forEach(([tanggal, items]) => {
						no++;
						table += `
								<div class="card">
						<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
							<h4 class="header-title">${tanggal}</h4>
							<input type="checkbox" id="check-all-${no}">
						</div>
						<div class="card-body">
							
						`;
						items.forEach(function (item) {
							let detail = btoa(JSON.stringify(item));
							table += `
								<div class="card-mapel">
									<p class="keterangan-hari">
										<span></span> 
										<span><input type="checkbox" id="cek_tanggal-${item.id}-${no}" name="id_pegawai[]" value="${item.id}"></span> 
									</p>
									<div class="keterangan-mapel">
										<div class="keterangan-mapel-kiri">
											<h5 class="judul-mapel">${item.nama_pegawai}</h5>
											<p class="keterangan-jam-mapel">Keterangan : ${item.keterangan}</p>
											<p class="keterangan-jam-mapel">Alasan : ${item.alasan_tidak_hadir}</p>
										</div> 
									</div>
								</div>
							`;
						});
						table += ` 
							</div>
						</div>
						`;
					});

				}
				$('#data_approval_izin').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#data_approval_izin'), jumlah_awal);
			}
		});
	}

	function editlevel(detail) {
		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id_level"]').val(item.id);
		$('#edit input[name="level"]').val(item.level);

	}


	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#data_approval_izin tbody tr");
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

	function persetujuan(id) {
		Swal.fire({
			title: 'Notifikasi',
			text: 'Anda yakin ingin setujui izin ini?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Setujui',
			cancelButtonText: 'Tolak'
		}).then((result) => {

			if (result.isConfirmed) {
				$.ajax({
					type: 'POST',
					url: `<?= base_url(); ?>admin/approval/update_approval`,
					data: {
						status: 1,
						id: id
					},
					dataType: 'json',
					success: function (data) {
						if (data.status == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Izin berhasil diupdate',
							})
							approval_izin();
						}

					}
				});

			} else if (result.dismiss === Swal.DismissReason.cancel) {

				$.ajax({
					type: 'POST',
					url: `<?= base_url(); ?>admin/approval/update_approval`,
					data: {
						status: 2,
						id: id
					},
					dataType: 'json',
					success: function (data) {
						if (data.status == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Izin berhasil diupdate',
							})
							approval_izin();
						}

					}
				});

			}
		})
	}
</script>