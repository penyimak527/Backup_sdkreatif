<style>
	.dropify-wrapper .dropify-message span {
		font-size: 14px;
		color: #333;
		/* bisa juga ubah warna */
		font-weight: 500;
		/* atau bold */
	}

	@media (max-width: 768px) {
		.presensi-siswa__content {
			display: block;
		}

		.presensi-siswa__item {
			margin-bottom: 15px;
		}
	}

	.card-absen-aktif {
		background-color: #198754 !important;
		/* Bootstrap bg-success */
		border: 1px solid #fff !important;
		color: #fff !important;
	}


	.card-absen-alfa {
		background-color: #dc3545 !important;
		/* merah */
		color: #fff !important;
		border: 1px solid #dc3545 !important;
	}

	.card-absen-dispen {
		background-color: #ffc107 !important;
		/* juga kuning */
		color: #000 !important;
		border: 1px solid #ffc107 !important;
	}

	.card-absen-sakit {
		background-color: #ffc107 !important;
		/* juga kuning */
		color: #000 !important;
		border: 1px solid #ffc107 !important;
	}

	#jurnal-siswa {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		gap: 10px;
	}
</style>
<form class="row" id="form-tambah" enctype="multipart/form-data">
	<div class="card">
		<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
			<h4 class="header-title"> <?= $title ?></h4>
			<a href="<?= base_url('admin/kurikulum/jurnal/jurnal_guru') ?>" class="btn btn-sm btn-outline-danger">
				Kembali
			</a>
		</div>
		<div class="card-body">
			<div class="row">

				<input type="hidden" name="id_kelas_jadwal_pelajaran">
				<input type="hidden" name="uuid">
				<div class="col-md-12">
					<div class="col-md-12 mb-2">
						<label for="tanggal" class="form-label">Tanggal Mengajar</label>
						<input type="text" name="tanggal_view" class="form-control" disabled
							value="<?= date('d-m-Y', strtotime($tanggal)) ?>">
						<input type="hidden" name="tanggal" class="form-control"
							value="<?= date('d-m-Y', strtotime($tanggal)) ?>">
					</div>
					<div class="col-md-12 mb-2">
						<label for="nama_guru" class="form-label">Nama Guru</label>
						<input type="text" name="nama_guru" class="form-control" disabled>
						<input type="hidden" name="id_guru">
					</div>
					<div class="col-md-12 mb-2">
						<label for="mapel" class="form-label">Mata Pelajaran</label>
						<input type="text" name="mapel" class="form-control" disabled>
						<input type="hidden" name="id_mapel">
					</div>
					<!-- <div class="col-md-12 mb-2">
						<label for="ruangan" class="form-label">Ruangan</label>
						<input type="text" name="ruangan" class="form-control">
					</div> -->
					<div class="col-md-12 mb-2">
						<label for="kegiatan" class="form-label">Kegiatan</label>
						<textarea type="text" name="kegiatan" class="form-control"
							placeholder="Kegiatan ..."></textarea>
					</div>
					<div class="col-md-12 mb-2">
						<label for="tema" class="form-label">Tema</label>
						<input type="text" name="tema" class="form-control" placeholder="Tema ...">
					</div>
				</div>
				<!-- <div class="col-md-6">
					<div class="col-md-12 mb-2">
						<label for="nis" class="form-label">Foto Kegiatan Awal</label>
						<input type="file" class="dropify" name="foto_kegiatan_awal" data-height="193">
					</div>
					<div class="col-md-12 mb-2">
						<label for="nama_lengkap" class="form-label">Foto Kegiatan Akhir</label>
						<input type="file" class="dropify" name="foto_kegiatan_akhir" data-height="193">
					</div>
				</div> -->
				<div class="col-md-3">
					<button type="button" class="btn btn-outline-primary" id="btn-simpan"><i
							class="ri-save-line me-1"></i>
						Simpan
					</button>
				</div>
			</div>
		</div>
	</div>
	<!-- <div class="card">
  <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
   <h4 class="header-title"> Jurnal Siswa</h4>

  </div>
  <div class="card-body">
   <div id="jurnal-siswa">
   </div>
  </div>
  
 </div> -->
</form>

<script>
	$(document).ready(function () {
		kelas_jadwal_pelajaran_result();
		// jurnal_siswa_result();
		$("#btn-simpan").click(function () {

			var form = $("#form-tambah")[0];
			var formData = new FormData(form);


			$.ajax({
				url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/tambah') ?>',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				cache: false,
				dataType: 'JSON',
				success: function (data) {
					$("#tambah").modal('hide');

					if (data.status == true) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil disimpan',
						}).then((result) => {
							if (result.isConfirmed) {
								window.location.href =
									"<?= base_url('admin/kurikulum/jurnal/jurnal_guru') ?>";
							}
						})
					}
				}
			})
		})


		let kode_unik = self.crypto.randomUUID();
		$("#form-tambah input[name='uuid']").val(kode_unik);
	})

	function kelas_jadwal_pelajaran_result() {
		var id_jadwal = "<?= $id_jadwal ?>";
		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/kelas_jadwal_pelajaran_result') ?>',
			type: 'POST',
			dataType: 'JSON',
			data: {
				id_jadwal
			},
			success: function (data) {

				console.log(data.nama_guru);
				$('#form-tambah input[name="id_kelas_jadwal_pelajaran"]').val(data.id);
				$('#form-tambah input[name="nama_guru"]').val(data.nama_guru);
				$('#form-tambah input[name="id_guru"]').val(data.id_guru);
				$('#form-tambah input[name="mapel"]').val(data.mapel);
				$('#form-tambah input[name="id_mapel"]').val(data.id_mapel);
				$('#form-tambah input[name="ruangan"]').val(data.ruangan);
			}
		});
	}
	// function jurnal_siswa_result() {
	// 	var id_jadwal = "<?= $id_jadwal ?>";
	// 	$.ajax({
	// 		url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/jurnal_siswa_result') ?>',
	// 		type: 'POST',
	// 		dataType: 'JSON',
	// 		data: {
	// 			id_jadwal
	// 		},
	// 		success: function (data) {

	// 			var card = '';
	// 			var no = 1;
	// 			data.forEach(el => {
	// 				card += `
	// 				<div class="item-presensi">
	// 					<div id="presensi-siswa-item-${el.id_siswa}" class="card-body position-relative d-flex align-items-center"
	// 						style="border:1px solid #ccc; border-radius:10px; cursor: pointer;"
	// 						onclick="absen_siswa(this, ${el.id_siswa})">

	// 						<a onclick="izin_siswa(${el.id_siswa})" class="dropdown-toggle drop-arrow-none card-drop position-absolute"
	// 							style="top: 10px; right: 10px;" data-bs-toggle="dropdown" aria-expanded="false">
	// 							<i class="ri-more-2-fill fs-20"></i>
	// 						</a>

	// 						<div class="circle-box d-flex justify-content-center align-items-center rounded-circle border border-dark flex-shrink-0"
	// 							style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; ">
	// 							${no++}
	// 						</div>
	// 						<div style="margin-left: 18px;">
	// 							<span style="font-size: 14px;">${el.nama_siswa}</span><br/>
	// 							<span id="status-hadir-siswa-${el.id_siswa}"  style="font-size: 16px; text-transform: uppercase;"></span>
	// 							<input type="checkbox" id="input-status-presensi-siswa-${el.id_siswa}" style="display: none;" name="status_presensi_siswa[]">
	// 							<input type="hidden" name="id_kelas_siswa[]" id="id-siswa-${el.id_siswa}" value="${el.id_siswa}">
	// 						</div>
	// 					</div>
	// 					<div class="modal fade" id="modal-siswa-${el.id_siswa}" role="dialog" aria-labelledby="myLargeModalLabel"
	// 					aria-hidden="true">
	// 						<div class="modal-dialog modal-lg">
	// 							<div class="modal-content">
	// 								<div class="modal-header">
	// 									<h4 class="modal-title" id="myLargeModalLabel">Keterangan Presensi</h4>
	// 									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	// 								</div>
	// 								<div class="modal-body">
	// 										<div class="mb-3">
	// 											<label for="izin" class="form-label">Presensi</label>
	// 											<select class="form-control"
	// 												id="input-status-presensi-${el.id_siswa}" onchange="tampilkan_surat_dokter(${el.id_siswa})">
	// 												<option value="hadir">HADIR</option>
	// 												<option value="sakit">SAKIT</option>
	// 												<option value="ijin">IJIN</option>
	// 												<option value="alfa">ALFA</option>
	// 												<option value="dispen">DISPEN</option>
	// 											</select>
	// 										</div>
	// 										<div id="form-ijin-${el.id_siswa}" style="display: none;">
	// 													<div class="form-group">
	// 														<label for="keterangan">Keterangan</label>
	// 														<input type="text" class="form-control" name="keterangan[]" id="input-keterangan-${el.id_siswa}">
	// 													</div>
	// 													<div class="form-group">
	// 														<label class="form-label mt-2">Surat Dokter/Bukti Surat Tidak Masuk</label>
	// 														<input type="file" class="dropify-surat-dokter" name="bukti_surat[]">
	// 													</div>
	// 												</div>
	// 								</div>
	// 								<div class="modal-footer">
	// 									<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
	// 									<button type="button" class="btn btn-primary" onclick="simpan_pilihan(${el.id_siswa})">Simpan</button>
	// 								</div>
	// 							</div>
	// 						</div>
	// 					</div>
	// 				</div>
	// 				`
	// 			})

	// 			$("#jurnal-siswa").html(card);

	// 			$('.dropify-surat-dokter').dropify();
	// 		}
	// 	});
	// }

	// function absen_siswa(el, id) {
	// 	const checkbox = document.getElementById(`input-status-presensi-siswa-${id}`);
	// 	const isActive = el.classList.contains('card-absen-aktif');

	// 	if (isActive) {

	// 		el.classList.remove('card-absen-aktif');


	// 		if (checkbox) checkbox.checked = false;

	// 		const circle = el.querySelector('.circle-box');
	// 		if (circle) {
	// 			circle.classList.remove('border-white');
	// 			circle.classList.add('border-dark');
	// 		}
	// 	} else {

	// 		el.classList.add('card-absen-aktif');


	// 		if (checkbox) checkbox.checked = true;
	// 		$(`#input-status-presensi-siswa-${id}`).val('hadir');
	// 		$(`#status-hadir-siswa-${id}`).text('hadir');
	// 		const circle = el.querySelector('.circle-box');
	// 		if (circle) {
	// 			circle.classList.remove('border-dark');
	// 			circle.classList.add('border-white');
	// 		}
	// 	}
	// }


	// function izin_siswa(id_siswa) {
	// 	$('#modal-siswa-' + id_siswa).modal('show');
	// }

	// function tampilkan_surat_dokter(id) {
	// 	let status_presensi = $(`#input-status-presensi-${id}`).val()
	// 	$(`#form-ijin-${id}`).hide()
	// 	if (status_presensi == 'ijin' || status_presensi == 'sakit' || status_presensi == 'dispen') {
	// 		$(`#form-ijin-${id}`).show()
	// 	}
	// }
	// function simpan_pilihan(id) {
	// 	ubah_status_aktif(id);

	// 	// Ambil nilai dari modal
	// 	const keterangan = $(`#input-keterangan-${id}`).val();
	// 	const fileInput = $(`#form-ijin-${id} input[type='file']`)[0];

	// 	// Cek apakah sudah ada input hidden sebelumnya (hindari duplikat)
	// 	if ($(`#input-hidden-keterangan-${id}`).length === 0) {
	// 		$("#form-tambah").append(`<input type="hidden" name="keterangan[]" id="input-hidden-keterangan-${id}" value="${keterangan || ''}">`);
	// 	} else {
	// 		$(`#input-hidden-keterangan-${id}`).val(keterangan || '');
	// 	}

	// 	// Tandai file yang ingin diupload
	// 	if (fileInput && fileInput.files.length > 0) {
	// 		$(fileInput).attr('data-upload', 'yes');
	// 	}

	// 	// Tutup modal
	// 	$('#modal-siswa-' + id).modal('hide');
	// }


	// function ubah_status_aktif(id) {


	// 	const status_presensi = $(`#input-status-presensi-${id}`).val();
	// 	const card = $(`#presensi-siswa-item-${id}`);

	// 	$(`#input-status-presensi-siswa-${id}`).val(status_presensi);

	// 	$(`#status-hadir-siswa-${id}`).html(status_presensi);


	// 	card.removeClass('card-absen-sakit card-absen-alfa card-absen-dispen card-absen-aktif');


	// 	const circle = card.find('.circle-box');
	// 	if (status_presensi === 'sakit' || status_presensi === 'dispen') {
	// 		circle.removeClass('border-white').addClass('border-dark');
	// 		card.addClass('card-absen-sakit');
	// 	} else if (status_presensi === 'alfa') {
	// 		circle.removeClass('border-dark').addClass('border-white');

	// 		card.addClass('card-absen-alfa');
	// 	} else {
	// 		card.addClass('card-absen-aktif');
	// 	}
	// }
</script>




</script>