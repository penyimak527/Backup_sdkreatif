<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>
		<div>
			<?php if ($this->session->userdata('admin')['level'] != 'Admin'): ?>
				<button type="button" class="btn btn-sm btn-outline-success mb-2" data-bs-toggle="modal"
					data-bs-target="#upload">
					<i class="ri-file-excel-2-line me-1"></i>
					Import
				</button>
			<?php endif; ?>
			<button type="button" class="btn btn-sm btn-outline-primary mb-2" data-bs-toggle="modal"
				data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
		</div>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-kelas-jadwal-pelajaran"
							placeholder="Cari Jadwal Pelajaran" aria-describedby="inputGroupPrepend"
							onkeyup="jadwal_pelajaran()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>

		<div id="data_jadwal_pelajaran">

		</div>

		<div
			class="d-flex flex-column flex-sm-row justify-content-between align-items-center align-items-sm-center gap-2">
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

<div class="modal fade" id="upload" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Import <?= $title; ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table>
					<tr>
						<td>
							<li style="color: brown;">

							</li>
						</td>
						<td>
							<span style="color: brown;">Download fite template excel dari kami terlebih dahulu</span>
						</td>
					</tr>
					<tr>
						<td>
							<li style="color: brown;">

							</li>
						</td>
						<td>
							<span style="color: brown;"> Debelum import jadwal pelajaran pastikan data excel yang akan
								di import sama seperti template excel
								yang telah kita sediakan</span>
						</td>
					</tr>
					<tr>
						<td>
							<li style="color: brown;">

							</li>
						</td>
						<td>
							<span style="color: brown;">Untuk mata pelajaran pastikan sama untuk penulisan nya seperti
								pada data yang ada di menu master
								data -> mata pelajaran</span>
						</td>
					</tr>
				</table>
				<form id="form-upload" class="row">
					<div class="col-md-6">
						<div class="mb-2">
							<label for="id_kelas" class="form-label">Import</label>
							<input type="file" name="upload_excel" class="form-control" onchange="uploadFile(this);" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="mb-2">
							<label for="id_kelas" class="form-label">Template</label><br />
							<a href="<?= base_url(); ?>storage/upload_jadwal_mapel.xlsx"
								class="btn btn-sm btn-outline-success">
								<i class=" ri-download-2-line"></i>
								Download
							</a>
						</div>
					</div>
					<div style="display: none;" id="upload-data">

					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-upload">Upload</button>
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
					<input type="hidden" name="id_kelas_setting">
					<?php if ($level == 'Admin'): ?>
						<div class="mb-2">
							<label for="id_guru" class="form-label">Nama Guru</label>
							<select id="guru-jadwal-pelajaran" name="id_guru" class="form-control">
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="id_guru" class="form-control"
							value="<?= $this->session->userdata('admin')['id_pegawai']; ?>" />
					<?php endif; ?>
					<div class="mb-2">
						<label for="id_kelas" class="form-label">Kelas</label>
						<select type="text" name="id_kelas" class="form-control" onchange="mapel();">

						</select>
					</div>
					<div class="mb-2">
						<label for="id_mapel" class="form-label">Mata Pelajaran</label>
						<select type="text" name="id_mapel" class="form-control" disabled>

						</select>
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_awal" class="form-label">Jam Pelajaran Awal</label>
						<input type="text" name="jam_pelajaran_awal" class="form-control"
							placeholder="Jam Pelajaran Awal ..." onkeyup="formatTimeInput(this)" maxlength="8" />
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_akhir" class="form-label">Jam Pelajaran Akhir</label>
						<input type="text" name="jam_pelajaran_akhir" class="form-control"
							placeholder="Jam Pelajaran Akhir ..." onkeyup="formatTimeInput(this)" maxlength="8" />
					</div>
					<div class="mb-2">
						<label for="hari" class="form-label">Hari</label>
						<select class="form-control" data-choices name="hari">
							<option value="">Pilih Hari</option>
							<option value="Senin">Senin</option>
							<option value="Selasa">Selasa</option>
							<option value="Rabu">Rabu</option>
							<option value="Kamis">Kamis</option>
							<option value="Jumat">Jumat</option>
							<option value="Sabtu">Sabtu</option>
						</select>
					</div>
					<div class="mb-2">
						<label for="hari" class="form-label">Semester</label>
						<select class="form-control" name="semester">
							<option value="">Pilih Semester</option>
							<option value="Ganjil">Ganjil</option>
							<option value="Genap">Genap</option>
						</select>
					</div>
					<div class="mb-2">
						<label for="id_periode" class="form-label">Tahun Ajaran</label>
						<select type="text" name="id_periode" class="form-control">

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
				<h4 class="modal-title" id="myLargeModalLabel">Edit Jadwal Pelajaran</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-edit">
					<input type="hidden" name="id">
					<?php if ($level == 'Admin'): ?>
						<div class="mb-2">
							<label for="id_guru" class="form-label">Nama Guru</label>
							<select id="guru-jadwal-pelajaran" name="id_guru" class="form-control">
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="id_guru" class="form-control"
							value="<?= $this->session->userdata('admin')['id_pegawai']; ?>" />
					<?php endif; ?>
					<div class="mb-2">
						<label for="id_kelas" class="form-label">Kelas</label>
						<select type="text" name="id_kelas" class="form-control" onchange="mapel()">

						</select>
					</div>
					<div class="mb-2">
						<label for="id_mapel" class="form-label">Mata Pelajaran</label>
						<select type="text" name="id_mapel" class="form-control">

						</select>
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_awal" class="form-label">Jam Pelajaran Awal</label>
						<input type="text" name="jam_pelajaran_awal" onkeyup="formatTimeInput(this)" maxlength="8"
							class="form-control" />
					</div>
					<div class="mb-2">
						<label for="jam_pelajaran_akhir" class="form-label">Jam Pelajaran Akhir</label>
						<input type="text" name="jam_pelajaran_akhir" onkeyup="formatTimeInput(this)" maxlength="8"
							class="form-control" />
					</div>
					<div class="mb-2">
						<label for="hari" class="form-label">Hari</label>
						<select type="text" name="hari" class="form-control">

						</select>
					</div>
					<div class="mb-2">
						<label for="semester" class="form-label">Semester</label>
						<select name="semester" class="form-control">

						</select>
					</div>
					<div class="mb-2">
						<label for="id_periode" class="form-label">Tahun Ajaran</label>
						<select name="id_periode" class="form-control">

						</select>
					</div>

				</form>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" onclick="$('#edit').modal('hide');">Tutup</button>
				<button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
			</div>
		</div>
	</div>
</div>



<script>
	$(document).ready(function () {
		kelas();
		periode();
		guru();
		jadwal_pelajaran();
		$("#btn-simpan").click(function () {
			$('#btn-simpan').prop('disabled', true);
			$('#btn-simpan').text('Sedang Diproses');
			var form = $("#form-tambah");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/jadwal_pelajaran/tambah'); ?>',
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
						jadwal_pelajaran();
						$("#form-tambah")[0].reset();
						$('#btn-simpan').prop('disabled', false);
						$('#btn-simpan').text('Simpan');
					}
				}
			})
		})

		$("#btn-upload").click(function () {
			$('#btn-upload').prop('disabled', true);
			$('#btn-upload').text('Sedang Diproses');
			var form = $("#form-upload");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/jadwal_pelajaran/upload'); ?>',
				type: 'POST',
				data: data,
				success: function (data) {
					$("#upload").modal('hide');

					if (data == 'true') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diupload',
						})
						jadwal_pelajaran();
						$("#form-upload")[0].reset();
						$('#btn-upload').prop('disabled', false);
						$('#btn-upload').text('Upload');
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Gagal',
							text: 'Data mapel, kelas, kode kelas dan periode perlu dicek kembali, data harus sama dengan data master',
						})
						jadwal_pelajaran();
						$("#form-upload")[0].reset();
						$('#btn-upload').prop('disabled', false);
						$('#btn-upload').text('Upload');
					}
				}
			})
		})

		$("#btn-update").click(function () {
			var form = $("#form-edit");
			var data = form.serialize();
			$.ajax({
				url: '<?= base_url('admin/kurikulum/jadwal_pelajaran/edit'); ?>',
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
						jadwal_pelajaran();
					}
				}
			})
		})

		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#data_jadwal_pelajaran .card-mapel'), jumlah);
		});

	})

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
					url: `<?= base_url(); ?>admin/kurikulum/jadwal_pelajaran/hapus`,
					data: {
						id
					},
					dataType: 'json',
					success: function (data) {
						if (data == true) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Data berhasil dihapus',
							})
							jadwal_pelajaran();
						}

					}
				})
			}
		})
	}

	function kelas(id_kelas = null) {
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas/kelas_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Kelas</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_kelas ? 'selected' : ''}>${item.nama_kelas} ${item.kode_kelas}</option>
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
				var option = '<option value="">Pilih Tahun Ajaran</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {
						var selected = '';
						if (id_periode == null) {

							selected = item.status == 'Aktif' ? 'selected' : '';
						} else {
							selected = item.id == id_periode ? 'selected' : '';
						}

						option += `
						<option value="${item.id}" ${selected}>${item.periode}</option>
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

	function guru(id_guru = null) {

		$.ajax({
			url: '<?= base_url('admin/kurikulum/guru/guru_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Guru</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
						<option value="${item.id}" ${item.id == id_guru ? 'selected' : ''}>${item.nama_guru}</option>
						`;
					});
				}
				if (id_guru == null) {
					$('#tambah select[name="id_guru"]').html(option);

				} else {
					$('#edit select[name="id_guru"]').html(option);
				}
			}
		});
	}

	function mapel(id_mapel = null) {
		var id_kelas = $('#tambah select[name="id_kelas"]').val();
		$('#tambah select[name="id_mapel"]').attr('disabled', false)
		$.ajax({
			url: '<?= base_url('admin/kurikulum/kelas_setting/mata_pelajaran_result'); ?>',
			type: 'POST',
			data: {
				id_kelas
			},
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var option = '<option value="">Pilih Mata Pelajaran</option>';
				if (data.length == 0) {

				} else {
					data.forEach(function (item) {

						option += `
				<option value="${item.id}" ${item.id == id_mapel ? 'selected' : ''}>${item.mapel}</option>
				`;
					});
				}
				if (id_mapel == null) {
					$('#tambah select[name="id_mapel"]').html(option);

				} else {
					$('#edit select[name="id_mapel"]').html(option);
				}
			}
		});
	}

	function jadwal_pelajaran() {
		var search = $("#cari-kelas-jadwal-pelajaran").val();
		console.log(search);
		$.ajax({
			url: '<?= base_url('admin/kurikulum/jadwal_pelajaran/jadwal_pelajaran_result'); ?>',
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
							 <p class="keterangan-hari">
								<span>Hari : ${item.hari}</span>
								<span style="font-size:14px;">Mata Pelajaran: ${item.mapel}</span>
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.nama_guru}</h5>  
								  <p style="margin: 0; padding: 0; font-size: 12px; margin-bottom: 8px;"><b>Jam Pelajaran:</b> ${item.jam_pelajaran_awal} - ${item.jam_pelajaran_akhir}</p> 
								</div>
								 <div class="keterangan-mapel-kanan">
									<div class="d-flex justify-content-center gap-2">
										<button type="button" class="btn btn-outline-warning w-50" onclick="editJadwalPelajaran('${detail}')">
											<i class="ri-edit-line"></i>
										</button>
											<button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
									</div>
								</div>
							</div>
						</div>
						`;
					});
				}
				$('#data_jadwal_pelajaran').html(table);
				let jumlah_awal = parseInt($('#dt-length-1').val());
				paging($('#data_jadwal_pelajaran .card-mapel'), jumlah_awal);
			}
		});
	}

	function editJadwalPelajaran(detail) {

		$('#edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#edit input[name="id"]').val(item.id);


		guru(item.id_guru);
		kelas(item.id_kelas);
		mapel(item.id_mapel);
		periode(item.id_periode);
		$('#edit select[name="hari"]').html(
			`
			<option value="Senin" ${item.hari == 'Senin' ? 'selected' : ''} >Senin</option>
			<option value="Selasa" ${item.hari == 'Selasa' ? 'selected' : ''} >Selasa</option>
			<option value="Rabu" ${item.hari == 'Rabu' ? 'selected' : ''} >Rabu</option>
			<option value="Kamis" ${item.hari == 'Kamis' ? 'selected' : ''} >Kamis</option>
			<option value="Jumat" ${item.hari == 'Jumat' ? 'selected' : ''} >Jumat</option>
			<option value="Sabtu" ${item.hari == 'Sabtu' ? 'selected' : ''} >Sabtu</option>
			`
		);
		$('#edit input[name="jam_pelajaran_awal"]').val(item.jam_pelajaran_awal);
		$('#edit input[name="jam_pelajaran_akhir"]').val(item.jam_pelajaran_akhir);
		$('#edit select[name="semester"]').html(
			` 
							<option value="Ganjil" ${item.semester == 'Ganjil' ? 'selected' : ''}>Ganjil</option>
							<option value="Genap" ${item.semester == 'Genap' ? 'selected' : ''}>Genap</option>
			`
		)
		$('#edit input[name="id_kelas_setting"]').val(item.id_kelas_setting);
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

	function uploadFile(evt) {
		var files = evt.files[0];

		let reader = new FileReader()
		reader.onload = function (e) {
			let data = e.target.result
			let workbook = XLSX.read(data, {
				type: 'binary'
			})

			workbook.SheetNames.forEach(function (sheetName) {
				let XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
				let json_object = JSON.stringify(XL_row_object);
				let data_parse = JSON.parse(json_object)
				console.log(data_parse)
				for (const item of data_parse) {
					tambah_data(item.MATA_PELAJARAN, item.KELAS, item.KODE_KELAS, item.JAM_PELAJARAN_AWAL, item.JAM_PELAJARAN_AKHIR, item.HARI, item.SEMESTER, item.PERIODE)
				}
			})
		}

		reader.onerror = function (ex) {
			console.log(ex);
		};

		reader.readAsBinaryString(files);
	}

	function tambah_data(mapel, kelas, kode_kelas, jam_awal, jam_akhir, hari, semester, periode) {
		$('#upload-data').append(`
		<input type="text" class="form-control" name="mapel[]" value="${mapel}">
		<input type="text" class="form-control" name="kelas[]" value="${kelas}"> 
		<input type="text" class="form-control" name="kode_kelas[]" value="${kode_kelas}">
		<input type="text" class="form-control" name="jam_awal[]" value="${jam_awal}">
		<input type="text" class="form-control" name="jam_akhir[]" value="${jam_akhir}">
		<input type="text" class="form-control" name="hari[]" value="${hari}">
		<input type="text" class="form-control" name="semester[]" value="${semester}">
		<input type="text" class="form-control" name="periode[]" value="${periode}">
		`
		);
	}

</script>