<style>
	.border-box {
		border: 1px solid #000;
		border-radius: 10px;
		padding: 10px;
		min-height: 100px;
	}

	.border-box-kegiatan {
		border: 1px solid #000;
		border-radius: 10px;
		padding: 10px;
	}

	.border-box-tanggal {
		border: 1px solid #000;
		border-radius: 10px;
		padding: 10px;
		min-height: 20px;
	}

	.checkbox {
		display: inline-block;
		width: 30px;
		height: 30px;
		border: 1px solid #000;
		margin-right: 5px;
	}

	table thead tr {
		font-size: 17px;
	}

	table tbody tr {
		font-size: 15px;
	}




	.signature-box {
		min-height: 100px;
		border: 1px solid #000;
		padding: 10px;
	}

	.kegiatan-wrapper {
		display: grid;
		grid-template-columns: 70px 160px 1fr;
		gap: 10px;
		padding: 10px;
		border-bottom: 1px solid #ccc;
	}

	.kegiatan-header {
		font-weight: bold;
		background: #f0f0f0;
	}

	@media (max-width: 600px) {
		.kegiatan-wrapper {
			grid-template-columns: 1fr;
		}
	}

	.jurnal-box {
		padding: 3px;
		margin-bottom: 3px;
		justify-content: space-between;
	}

	.jurnal-item {
		display: flex;
		flex-direction: column;
		margin-bottom: 1px;
	}

	.jurnal-label {
		font-weight: bold;
		font-size: 14px;
		color: #555;
		display: none;
	}

	.jurnal-label-isi {
		font-weight: bold;
		font-size: 14px;
		color: #555;
	}

	.jurnal-value {
		font-size: 15px;
		color: #222;
		margin-bottom: 4px;
		word-wrap: break-word;
	}

	/* Untuk mobile */
	@media screen and (min-width: 768px) {
		.jurnal-box {
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			align-items: center;
		}

		.jurnal-label {
			display: block;
		}

		.jurnal-label-isi {
			display: none;
		}

		.jurnal-value {
			font-weight: normal;
		}


	}
</style>
<form id="form-edit">
	<div class="row">
		<div class="col-md-2">
			<div class="mb-2">
				<div class="input-group">
					<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Awal"
						aria-describedby="inputGroupPrepend" name="tanggal_awal">
					<span class="input-group-text bg-primary text-white"> <i class="ri-calendar-line"></i></span>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="mb-2">
				<div class="input-group">
					<input type="text" class="form-control" name="tanggal_akhir" id="flatpicker"
						placeholder="Tanggal Akhir" aria-describedby="inputGroupPrepend">
					<span class="input-group-text bg-primary text-white"> <i class="ri-calendar-line"></i></span>
				</div>
			</div>
		</div>
		<div class="col-md-8" style="margin-bottom: 15px;">
			<div class="d-flex justify-content-between gap-3">
				<button type="button" class="btn btn-primary" onclick="approval_jurnal_mengajar()"><i
						class="ri-search-line"></i></button>
				<div class="d-flex justify-content-end gap-2">
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
		</div>

	</div>

	<div id="jurnal_mengajar"></div>
</form>
<div class="d-flex justify-content-center align-items-center mb-3 w-100">
	<ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination"></ul>
</div>


<div class="modal fade" id="persetujuan" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">
					<?= $title; ?> Guru
				</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<h3 class="text-center fw-bold">Jurnal Harian</h3>
				<table class="mb-2">
					<tr>
						<th width="100px">Nama Guru</th>
						<td>: </td>
						<th id="data-tr-guru"></th>
					</tr>
					</thead>
				</table>

				<div class="row equal-height-row">
					<div class="col-md-8 mb-2">
						<div class="border-box-kegiatan mb-3 h-100"
							style="height: 300px; overflow-y: auto; scroll-behavior: smooth;">

							<div class="d-flex flex-row justify-content-between">
								<div class="jurnal-item">
									<div class="jurnal-label">Kelas</div>
								</div>

								<div class="jurnal-item">
									<div class="jurnal-label">Jam</div>
								</div>

								<div class="jurnal-item">
									<div class="jurnal-label">Kegiatan</div>
								</div>
							</div>
							<div id="table_kegiatan"></div>



						</div>
					</div>

					<div class="col-md-4">
						<div class="border-box-tanggal mb-2">
							<label><strong id="tanggal"></strong></label>
						</div>
						<div class="border-box mb-2">
							<label><strong>Kode Kelas:</strong></label>
							<br />
							<table id="kode_kelas">
								<thead>
									<tr>
										<th></th>
										<th></th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
						<label class="mb-1"><strong>Hari:</strong>
							<div class="btn-group mb-2" id="hari">

							</div>
						</label>
						<div id="tema" style="height: auto; overflow-y: auto; scroll-behavior: smooth;"></div>

					</div>
				</div>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="persetujuan_pegawai" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">
					<?= $title; ?> Pegawai
				</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table>
					<tr>
						<td width="100px;">Tanggal</td>
						<td>:</td>
						<td><span id="tanggal_pegawai"></span></td>
					</tr>
					<tr>
						<td>Nama</td>
						<td>:</td>
						<td><span id="nama_pegawai"></span></td>
					</tr>
					<tr>
						<td>Kegiatan</td>
						<td>:</td>
						<td><span id="data-kegiatan"></span></td>
					</tr>
					<tr>
						<td>Semester</td>
						<td>:</td>
						<td> <span id="data-semester"></span></td>
					</tr>
					<tr>
						<td>Tahun Ajaran</td>
						<td>:</td>
						<td><span id="data-periode"></span></td>
					</tr>
				</table>
			</div>
			<div class=" modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		approval_jurnal_mengajar();

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
						url: `<?= base_url(); ?>admin/approval/jurnal_mengajar_update`,
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
								approval_jurnal_mengajar();
							}
						}
					})
				}
			})

		})



		$(document).on('click', 'input[id^="check-all-"]', function () {
			let no = $(this).attr('id').split('-')[2]; // misal: dari "check-all-1", ambil "1"
			$(`input[id^="cek_tanggal-"][id$="-${no}"]`).prop('checked', $(this).is(':checked'));
		});

	})

	function approval_jurnal_mengajar() {
		var tanggal_awal = $("input[name='tanggal_awal']").val();
		var tanggal_akhir = $("input[name='tanggal_akhir']").val();

		$.ajax({
			url: '<?= base_url('admin/approval/jurnal_mengajar_result'); ?>',
			type: 'POST',
			data: {
				tanggal_awal,
				tanggal_akhir
			},
			dataType: 'JSON',
			success: function (data) {
				var no = 1;
				var table = '';

				if (Object.keys(data).length === 0) {
					table += `
					<div class="card">
						<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
							<span class="header-title">Tidak ada data</span> 
						</div>
					</div>
				`;
					$("#pagination").hide();
				} else {
					Object.entries(data).forEach(([tanggal, items]) => {
						no++;

						table += `
								<div class="card">
								<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
									<h4 class="header-title">Tanggal: ${tanggal}</h4>
							<input type="checkbox" id="check-all-${no}">
								</div>
								<div class="card-body">
							
						`;
						items.forEach(item => {

							var tipe = '';
							var detail = '';
							if (item.tipe == 'pegawai') {
								tipe += `<p class="keterangan-jam-mapel">Kegiatan : ${item.jam_selesai_pelajaran}</p>`
								detail += `<button type="button" class="btn btn-sm btn-primary w-100" onclick="persetujuan_pegawai('${item.id}')"><i class="ri-eye-line" style="font-size: 20px;"></i> Detail</button>`
							} else {
								tipe += `<p class="keterangan-jam-mapel">Waktu Mengajar : ${item.jam_mulai_pelajaran} - ${item.jam_selesai_pelajaran}</p>`
								detail += `<button type="button" class="btn btn-sm btn-primary w-100" onclick="persetujuan('${item.tanggal}',${item.id_guru},'${item.nama_guru}')"><i class="ri-eye-line" style="font-size: 20px;"></i> Detail</button>`;
							}
							table += `
							<div class="card-mapel">
								<p class="keterangan-hari">
									<span>Tahun Ajaran : ${item.periode}</span>
									 <span><input type="checkbox" id="cek_tanggal-${item.id}-${no}" name="id_jurnal[]" value="${item.id}"></span>
									 <input type="hidden" name="tipe[]" value="${item.tipe}">
								</p>
								<div class="keterangan-mapel">
									<div class="keterangan-mapel-kiri">
										<h5 class="judul-mapel">${item.nama_guru}</h5>
										<h6 class="judul-mapel">${item.mapel}</h6>
									${tipe}
									</div>
									 <div class="keterangan-mapel-kanan">
										${detail}
									</div>
								</div>
							</div>`;
						});
						table += ` 
							</div>
						</div>
						`;
					});
				}

				$('#jurnal_mengajar').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#jurnal_mengajar .card-mapel'), jumlah_awal);
			}
		});
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

	function persetujuan(tanggal, id_guru, nama_guru) {
		$('#persetujuan').modal('show');
		$('#tanggal').text(`Tanggal: ${tanggal}`);
		$('#data-tr-guru').text(`${nama_guru}`);

		const [dd, mm, yyyy] = tanggal.split("-");


		const tanggalISO = `${yyyy}-${mm}-${dd}`;

		const hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
		const d = new Date(tanggalISO);
		const hari = hariList[d.getDay()];


		$(`#hari`).html(`
					<button type="button" class="btn btn-sm  ${hari == 'Senin' ? 'btn-success' : 'btn-light'} ">S</button>
											<button type="button" class="btn btn-sm  ${hari == 'Selasa' ? 'btn-success' : 'btn-light'} ">S</button>
											<button type="button" class="btn  btn-sm   ${hari == 'Rabu' ? 'btn-success' : 'btn-light'} ">R</button>
											<button type="button" class="btn  btn-sm  ${hari == 'Kamis' ? 'btn-success' : 'btn-light'} ">K</button>
											<button type="button" class="btn  btn-sm  ${hari == 'Jumat' ? 'btn-success' : 'btn-light'}  btn-light">J</button>
											<button type="button" class="btn  btn-sm  ${hari == 'Sabtu' ? 'btn-success' : 'btn-light'}  btn-light">S</button>
				`)
		$.ajax({
			url: '<?= base_url('admin/approval/jurnal_tanggal_result'); ?>',
			type: 'POST',
			data: {
				tanggal,
				id_guru
			},
			dataType: 'JSON',
			success: function (data) {
				var table = '';
				var kode_kelas = '';
				var table_tema = '';
				var no = 1;
				data.forEach(function (item) {
					table += `
					<div class="jurnal-box">
								<div class="jurnal-item">
									<div class="jurnal-label-isi">Kelas</div>
									<div class="jurnal-value">${item.kelas}</div>
								</div>

								<div class="jurnal-item">
									<div class="jurnal-label-isi">Jam</div>
									<div class="jurnal-value">	${item.jam_mulai_pelajaran} - ${item.jam_selesai_pelajaran}</div>
								</div>

								<div class="jurnal-item">
									<div class="jurnal-label-isi">Kegiatan</div>
									<div class="jurnal-value">${item.kegiatan}</div>
								</div>
							</div>
			`;
					table_tema += `
				<div class="border-box mb-2">
						<label><strong>Tema Jam ke ${no++}:</strong></label>
						<div>${item.tema}</div>
					</div> 
			`;
					kode_kelas += `
								<tr>
									<td>
										<div style="border:1px solid black; padding: 7px; border-radius: 3px; margin-bottom: 8px;">
										${item.kode_kelas}
										</div>
									</td>
									<td >
										<div
											style="border-bottom: 1px solid #000; padding: 5px; width: 140px; font-size: 15px; margin-bottom: 8px;">
											 ${item.jam_mulai_pelajaran} - ${item.jam_selesai_pelajaran}
										</div>
									</td>
								</tr>
			`;
				})
				$('#table_kegiatan').html(table);
				$('#tema').html(table_tema);
				$('#kode_kelas').html(kode_kelas);

			}
		});
	}
	function persetujuan_pegawai(id) {
		console.log(id);
		$('#persetujuan_pegawai').modal('show');

		$.ajax({
			url: '<?= base_url('admin/approval/jurnal_pegawai_result'); ?>',
			type: 'POST',
			data: {
				id
			},
			dataType: 'JSON',
			success: function (data) {


				$('#nama_pegawai').html(data.nama_pegawai);
				$('#tanggal_pegawai').html(data.tanggal);
				$('#data-kegiatan').html(data.kegiatan);
				$('#data-semester').html(data.semester);
				$('#data-periode').html(data.periode);

			}
		});

	}


</script>