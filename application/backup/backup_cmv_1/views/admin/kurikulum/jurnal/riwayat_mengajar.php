<style>
	.dropify-wrapper .dropify-message span {
		font-size: 14px;
		color: #333;
		/* bisa juga ubah warna */
		font-weight: 500;
		/* atau bold */
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
						<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Mengajar"
							aria-describedby="inputGroupPrepend">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-calendar-line"></i></span>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="mb-3">
					<select name="id_kelas" type="text" class="form-control">
						<option value="">Pilih Kelas</option>
						<?php foreach ($kelas as $k): ?>
							<option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?> 	<?= $k['kode_kelas'] ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="col-md-3">
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-primary" onclick="riwayat_mengajar_result()"><i
							class="ri-search-line"></i></button>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div id="riwayat-mengajar"></div>
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

<div class="modal fade" id="modal-view" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Detail <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" name="id_kelas_jadwal_pelajaran">
				<input type="hidden" name="uuid">
				<div class="col-md-12">
					<div class="col-md-12 mb-2">
						<label for="tanggal" class="form-label">Tanggal Mengajar</label>
						<input type="text" name="tanggal" class="form-control" disabled>
					</div>
					<div class="col-md-12 mb-2">
						<label for="nama_guru" class="form-label">Nama Guru</label>
						<input type="text" name="nama_guru" class="form-control" disabled>
					</div>
					<div class="col-md-12 mb-2">
						<label for="mapel" class="form-label">Mata Pelajaran</label>
						<input type="text" name="mapel" class="form-control" disabled>
					</div>

					<div class="col-md-12 mb-2">
						<label for="kegiatan" class="form-label">Kegiatan</label>
						<textarea type="text" name="kegiatan" class="form-control" disabled
							placeholder="Kegiatan ..."></textarea>
					</div>
					<div class="col-md-12 mb-2">
						<label for="tema" class="form-label">Tema</label>
						<input type="text" name="tema" class="form-control" disabled>
					</div>
				</div>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
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
				<form class="row" id="form-edit" enctype="multipart/form-data">
					<input type="hidden" name="id_jurnal_guru">
					<div class="col-md-12">
						<div class="col-md-12 mb-2">
							<label for="tanggal" class="form-label">Tanggal Mengajar</label>
							<input type="text" name="tanggal" class="form-control" disabled>
						</div>
						<div class="col-md-12 mb-2">
							<label for="nama_guru" class="form-label">Nama Guru</label>
							<input type="text" name="nama_guru" class="form-control" disabled>
						</div>
						<div class="col-md-12 mb-2">
							<label for="mapel" class="form-label">Mata Pelajaran</label>
							<input type="text" name="mapel" class="form-control" disabled>
						</div>

						<div class="col-md-12 mb-2">
							<label for="kegiatan" class="form-label">Kegiatan</label>
							<textarea type="text" name="kegiatan" class="form-control"></textarea>
						</div>
						<div class="col-md-12 mb-2">
							<label for="tema" class="form-label">Tema</label>
							<input type="text" name="tema" class="form-control">
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
		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/edit'); ?>',
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
						riwayat_mengajar_result();
					}
				}
			})
		})
		riwayat_mengajar_result();
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#riwayat-mengajar .card-mapel'), jumlah);
		});
	})

	function riwayat_mengajar_result() {
		var tanggal = $('#flatpicker').val();
		var id_kelas = $('select[name="id_kelas"]').val();


		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/riwayat_mengajar_result'); ?>',
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
						let detail = btoa(unescape(encodeURIComponent(JSON.stringify(item))));
						const tgl1 = parseDMY(item.tanggal);
						const tgl2 = parseDMY(item.tanggal_input);

						const selisihMs = tgl2 - tgl1;
						let selisihHari = selisihMs / (1000 * 60 * 60 * 24);
						selisihHari = Math.max(0, Math.floor(selisihHari));

						var tanggal_selisih = ''
						if (selisihHari == 0) {
							tanggal_selisih += '';
						} else {
							tanggal_selisih += '<span class="badge bg-danger">' + selisihHari + ' Hari </span>';
						}
						table += `
						<div class="card-mapel">
							<p class="keterangan-hari">
								<span>Hari : ${item.hari}, ${item.tanggal}</span>
								<span>Semester (${item.semester})</span>
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${item.mapel} Kelas ${item.nama_kelas} ${item.kode_kelas}</h5>
									<p style="margin: 0; padding: 0; font-size: 12px; margin-bottom: 8px;">Guru : ${item.nama_guru}</p>
									<p class="d-flex gap-2 m-0 mb-1">
										<span>Tanggal Mengisi: ${item.tanggal_input}</span>
										<span>${tanggal_selisih}</span>
									</p>
									<p class="keterangan-jam-mapel">Jam Pelajaran : ${item.jam_mulai_pelajaran + ' - ' + item.jam_selesai_pelajaran}</p>
								</div>
								<div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										<button type="button" class="btn btn-outline-primary w-50" onclick="detailNgajar('${detail}')">
											<i class="ri-eye-line"></i>
										</button>
										<button type="button" class="btn btn-outline-warning w-50" onclick="editNgajar('${detail}')">
											<i class="ri-edit-line"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
						`;
					})
				}
				$('#riwayat-mengajar').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#riwayat-mengajar .card-mapel'), jumlah_awal);
			}
		});
	}

	function detailNgajar(detail) {
		$('#modal-view').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_guru').val(item.id);
		$('#modal-view input[name="nama_guru"]').val(item.nama_guru);
		$('#modal-view input[name="tanggal"]').val(item.tanggal);
		$('#modal-view input[name="mapel"]').val(item.mapel);
		$('#modal-view input[name="ruangan"]').val(item.ruangan);
		$('#modal-view textarea[name="kegiatan"]').val(item.kegiatan);
		$('#modal-view input[name="tema"]').val(item.tema);
		const baseUrl = '<?= base_url('storage/guru/jurnal/') ?>';
		$('#foto_kegiatan_awal').attr('src', item.foto_kegiatan_awal ? baseUrl + item.foto_kegiatan_awal : '');
		$('#foto_kegiatan_akhir').attr('src', item.foto_kegiatan_akhir ? baseUrl + item.foto_kegiatan_akhir : '');
		$('#href-kegiatan_awal').attr('href', item.foto_kegiatan_awal ? baseUrl + item.foto_kegiatan_awal : '');
		$('#href-kegiatan_akhir').attr('href', item.foto_kegiatan_akhir ? baseUrl + item.foto_kegiatan_akhir : '');

		if (item.data.length > 0) {
			let item_siswa = ''
			let no = 1
			for (const siswa of item.data) {
				var status_presensi = '';
				if (siswa.status_presensi == 'hadir') {
					status_presensi = '<span class="badge bg-success">Hadir</span>'
				}
				if (siswa.status_presensi == 'sakit') {
					status_presensi = `<span class="badge bg-warning">Sakit</span>`
				}
				if (siswa.status_presensi == 'alfa') {
					status_presensi = '<span class="badge bg-danger">Alfa</span>'
				}
				if (siswa.status_presensi == 'ijin') {
					status_presensi = `<span class="badge bg-warning">Ijin</span>`
				}
				if (siswa.status_presensi == 'dispen') {
					status_presensi = `<span class="badge bg-secondary">Dispen</span>`
				}

				item_siswa += `
				<tr>
					<td class="text-center">${no}</td>
					<td class="text-left"><p style="font-size: 12px;">${siswa.nama_siswa}</p></td>
					<td class="text-center">
						${status_presensi}
					</td>
				</tr>
				`;

				no++
			}

			$(`#table-jurnal-siswa tbody`).html(item_siswa);
		}
	}
	function editNgajar(detail) {
		$('#modal-edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#modal-edit input[name="id_jurnal_guru"]').val(item.id);
		$('#modal-edit input[name="nama_guru"]').val(item.nama_guru);
		$('#modal-edit input[name="tanggal"]').val(item.tanggal);
		$('#modal-edit input[name="mapel"]').val(item.mapel);
		$('#modal-edit input[name="ruangan"]').val(item.ruangan);
		$('#modal-edit textarea[name="kegiatan"]').val(item.kegiatan);
		$('#modal-edit input[name="tema"]').val(item.tema);
		const baseUrl = '<?= base_url('storage/guru/jurnal/') ?>';
		$('#foto_kegiatan_awal').attr('src', item.foto_kegiatan_awal ? baseUrl + item.foto_kegiatan_awal : '');
		$('#foto_kegiatan_akhir').attr('src', item.foto_kegiatan_akhir ? baseUrl + item.foto_kegiatan_akhir : '');
		$('#href-kegiatan_awal').attr('href', item.foto_kegiatan_awal ? baseUrl + item.foto_kegiatan_awal : '');
		$('#href-kegiatan_akhir').attr('href', item.foto_kegiatan_akhir ? baseUrl + item.foto_kegiatan_akhir : '');

		if (item.data.length > 0) {
			let item_siswa = ''
			let no = 1
			for (const siswa of item.data) {
				var status_presensi = '';
				if (siswa.status_presensi == 'hadir') {
					status_presensi = '<span class="badge bg-success">Hadir</span>'
				}
				if (siswa.status_presensi == 'sakit') {
					status_presensi = `<span class="badge bg-warning">Sakit</span>`
				}
				if (siswa.status_presensi == 'alfa') {
					status_presensi = '<span class="badge bg-danger">Alfa</span>'
				}
				if (siswa.status_presensi == 'ijin') {
					status_presensi = `<span class="badge bg-warning">Ijin</span>`
				}
				if (siswa.status_presensi == 'dispen') {
					status_presensi = `<span class="badge bg-secondary">Dispen</span>`
				}

				item_siswa += `
				<tr>
					<td class="text-center">${no}</td>
					<td class="text-left"><p style="font-size: 12px;">${siswa.nama_siswa}</p></td>
					<td class="text-center">
						${status_presensi}
					</td>
				</tr>
				`;

				no++
			}

			console.log(item_siswa)
			$(`#table-jurnal-siswa tbody`).html(item_siswa);
		}
	}

	function paging(selector, jumlah_tampil = 10) {

		window.tp = new Pagination('#pagination', {
			itemsCount: selector.length,
			pageSize: parseInt(jumlah_tampil),
			onPageSizeChange: function (ps) {
				console.log('changed to ' + ps);
			},
			onPageChange: function (paging) {
				var start = paging.pageSize * (paging.currentPage - 1),
					end = start + paging.pageSize,
					$rows = selector;
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