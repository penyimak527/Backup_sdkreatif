<style>
	.scroll-wrapper {

		width: 100%;
		overflow: auto;
		max-height: 400px;
	}

	.excel-container {
		display: flex;
		flex-direction: column;
		gap: 12px;
		margin-top: 20px;
		font-family: 'Segoe UI', sans-serif;
		font-size: 14px;
	}


	.excel-header,
	.excel-row {
		display: grid;
		grid-template-columns: 50px 1fr 300px 80px 130px;
		gap: 15px;
		padding: 10px;
		border: 1px solid #ced4da;
		border-radius: 6px;
		align-items: center;
	}

	.excel-container.has-nama-col .excel-header,
	.excel-container.has-nama-col .excel-row {
		grid-template-columns: 50px 110px 200px 1fr 80px 130px;
		/* No, Tgl, Nama, Kegiatan, Semester, Periode */
	}

	.excel-header {
		border: 2px solid #009b4b;
		color: #009b4b;
		font-weight: 600;
	}

	.excel-row {
		border: 1px solid #6c757d;
		color: #343a40;
		margin-bottom: 10px;
	}

	.status-lulus {
		color: green;
		font-weight: 600;
	}

	.status-tidak {
		color: red;
		font-weight: 600;
	}

	@media screen and (max-width: 768px) {

		.excel-header,
		.excel-row {
			grid-template-columns: 1fr;
			padding: 10px;
		}

		.excel-header div::before,
		.excel-row div::before {
			content: attr(data-label) ": ";
			font-weight: 600;
			color: #6c757d;
		}

		.excel-header {
			display: none;
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
						<input type="text" class="form-control" id="cari-data-laporan" placeholder="Cari Laporan"
							aria-describedby="inputGroupPrepend" onkeyup="data_laporan()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>
		<div style="height: 500px; overflow-y: auto; scroll-behavior: smooth;" id="data-laporan">
		</div>
	</div>
</div>

<div class="modal fade" id="printLaporan" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form_laporan">
					<input type="hidden" id="path">
					<div class="row" id="filter-data" style="margin-bottom: 20px;">
						<div class="col-md-4">
							<div class="radio">
								<input type="radio" name="filter" id="filter_hari" value="tanggal" checked>
								<label for="filter_hari"> Hari </label>
							</div>
						</div>
						<div class="col-md-4">
							<div class="radio">
								<input type="radio" name="filter" id="filter_bulan" value="bulan">
								<label for="filter_bulan"> Bulan </label>
							</div>
						</div>
						<div class="col-md-4">
							<div class="radio">
								<input type="radio" name="filter" id="filter_tahun" value="tahun">
								<label for="filter_tahun"> Tahun </label>
							</div>
						</div>
					</div>
					<div id="form-hari" class="row mb-2">
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Start</label>
								<input type="date" class="form-control" name="dari_tanggal">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">End</label>
								<input type="date" class="form-control" name="sampai_tanggal">
							</div>
						</div>
					</div>

					<div id="form-bulan" class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Bulan</label>
								<select class="form-control" data-width="100%" name="filter_bulan">
									<?php
									$bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
									$jlh_bln = count($bulan);
									$no = 0;
									for ($c = 0; $c < $jlh_bln; $c += 1) {
										$no++;
										$no_pas = sprintf("%02s", $no);
										?>
										<option value="<?php echo $no_pas; ?>">
											<?php echo $bulan[$c]; ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Tahun</label>
								<select class="form-control" data-width="100%" name="filter_tahun">
									<?php
									$now = date('Y');
									for ($a = 2025; $a <= $now; $a++) {
										?>
										<option value="<?php echo $a; ?>">
											<?php echo $a; ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
					</div>
					<div id="form-tahun" class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Tahun</label>
								<select class="form-control" data-width="100%" name="single_filter_tahun">
									<?php
									$now = date('Y');
									for ($a = 2025; $a <= $now; $a++) {
										?>
										<option value="<?php echo $a; ?>">
											<?php echo $a; ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
					</div>
					<div id="form-jurnal-harian" class="row g-2" style="display: none;">

						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Guru</label>
								<select type="date" name="id_guru" class="form-control">
									<option value="">Pilih Guru</option>
									<?php
									foreach ($guru as $g): ?>
										?>
										<option value="<?php echo $g['id']; ?>">
											<?php echo $g['nama_guru']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Semester</label>
								<select name="semester" class="form-control">
									<option value="">Pilih Semester</option>
									<option value="Ganjil">Ganjil</option>
									<option value="Genap">Genap</option>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Tahun Ajaran</label>
								<select type="date" name="id_periode" class="form-control">
									<option value="">Pilih Tahun Ajaran</option>
									<?php
									foreach ($periode as $pe): ?>
										?>
										<option value="<?php echo $pe['id']; ?>">
											<?php echo $pe['periode']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div id="form-jurnal-guru" class="row g-2" style="display: none; margin-top: 10px;">
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Kelas</label>
								<select type="date" name="id_kelas" class="form-control">
									<option data-kelas="Semua Kelas" value="Semua">Semua Kelas</option>
									<?php
									foreach ($kelas as $ke): ?>
										?>
										<option data-kelas="<?= $ke['nama_kelas']; ?>" value="<?php echo $ke['id']; ?>">
											<?php echo $ke['nama_kelas']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Semester</label>
								<select type="date" name="semester_jurnal" class="form-control">
									<option value="">Pilih Semester</option>
									<option value="Ganjil">Ganjil</option>
									<option value="Genap">Genap</option>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Tahun Ajaran</label>
								<select type="date" name="id_periode_jurnal" class="form-control">
									<option value="">Pilih Tahun Ajaran</option>
									<?php
									foreach ($periode as $pe): ?>
										?>
										<option data-tahun="<?= $pe['periode']; ?>" value="<?php echo $pe['id']; ?>">
											<?php echo $pe['periode']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>

					<div id="form-jurnal-kegiatan" class="row g-2" style="display: none; margin-top: 10px;">
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Pegawai</label>
								<select type="date" name="id_pegawai" class="form-control"
									onchange="data_jurnal_pegawai()">
									<option value="">Semua Pegawai</option>
									<?php
									foreach ($pegawai as $ke): ?>
										?>
										<option data-label="<?= $ke['nama_pegawai']; ?>"
											data-jabatan="<?= $ke['jabatan']; ?>" value="<?php echo $ke['id_pegawai']; ?>">
											<?php echo $ke['nama_pegawai']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

					</div>
				</form>
				<div class="modal-footer" style="margin-right:-22px;">
					<button type="button" class="btn btn-success waves-effect" name="print" value="excel"
						id="btn_print_laporan_excel"><i class="fa fa-file-excel me-1"></i>
						Excel</button>
					<button type="button" class="btn btn-info waves-effect" name="print" value="pdf"
						id="btn_print_laporan"><i class="fa fa-print me-1"></i>
						Print</button>
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="cek_excel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<table id="data_izin_pegawai" class="grid">
					<thead>
						<tr>
							<td>No</td>
							<td>TANGGAL</td>
							<td>NAMA PEGAWAI</td>
							<td>KETERANGAN</td>
							<td>ALASAN TIDAK HADIR</td>
						</tr>
					</thead>
					<tbody>

					</tbody>

				</table>
			</div>
		</div>
	</div>
</div>

<div class="jurnal_kegiatan" style="display: none;">
	<table>
		<tr>
			<td>Nama</td>
			<td>:</td>
			<td id="nama_pegawai"></td>
		</tr>
		<tr>
			<td>Jabatan</td>
			<td>:</td>
			<td id="jabatan"></td>
		</tr>
	</table>
	<div class="scroll-wrapper">
		<div class="excel-container">
			<div class="excel-header">
				<div data-label="No">No</div>
				<div data-label="Tanggal">Tanggal</div>
				<div data-label="Nama">Nama</div>
				<div data-label="Kegiatan">Kegiatan</div>
				<div data-label="Semester">Semester</div>
				<div data-label="Periode">Periode</div>
			</div>

			<div id="data_jurnal_pegawai">

			</div>
		</div>
	</div>
</div>

<div class="jurnal_guru_kelas" style="display: none;">
	<table style="width:100%; line-height: 1.5;">
		<tbody>
			<tr>
				<td style="width:7%; font-size:11px; text-transform:uppercase;">Kelas</td>
				<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
				<td id="kelas"></td>
			</tr>
			<tr>
				<td style="width:7%; font-size:11px; text-transform:uppercase;">Semester</td>
				<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
				<td id="semester"></td>
			</tr>
			<tr>
				<td style="width:7%; font-size:11px; text-transform:uppercase;">Tahun Ajaran</td>
				<td style="width:1%; font-size:11px; text-transform:uppercase;">:</td>
				<td id="periode"></td>
			</tr>
		</tbody>
	</table>
	<table id="data_jurnal_guru_kelas" class="grid">
		<thead>
			<tr>
				<td>No</td>
				<td>GURU</td>
				<td>MATAPELAJARAN</td>
				<td>KELAS</td>
				<td>JAM</td>
				<td>KEGIATAN</td>
				<td>TEMA</td>
				<td>Tanggal Mengajar</td>
			</tr>
		</thead>
		<tbody>

		</tbody>

	</table>
</div>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
	$(document).ready(function () {
		data_laporan();
		$('#filter_hari').click(function () {
			$('#form-hari').show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
		});

		$('#filter_bulan').click(function () {
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
		});

		$('#filter_tahun').click(function () {
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').show();
		});

		$('#btn_print_laporan').click(function () {
			var unindexed_array = $('#form_laporan').serializeArray();
			var indexed_array = {};

			$.map(unindexed_array, function (n, i) {
				indexed_array[n['name']] = n['value'];
			});
			indexed_array['print'] = 'pdf';
			let path = $('#path').val();
			$.ajax({
				url: '<?php echo base_url(); ?>' + path + '/print_laporan',
				data: JSON.stringify(indexed_array),
				contentType: "application/json",
				type: "POST",
				async: false,
				beforeSend: () => {
					$('#popup_load').show();
				},
				success: function (result) {
					let myWindow = window.open('', '_blank');
					myWindow.document.write(result);
				},
				complete: () => {
					$('#popup_load').fadeOut();
				}
			});
		});
		$('#btn_print_laporan_excel').click(function () {
			data_jurnal_pegawai();
			data_jurnal_guru_kelas();
			data_izin_pegawai();
			// $('#printLaporan').modal('hide');
			// $('#cek_excel').modal('show');
			$('#btn_print_laporan_excel').attr('disabled', true);
			$('#btn_print_laporan_excel').html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...');

		});

	})

	function data_laporan() {
		var search = $("#cari-data-laporan").val();

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {

				var table = '';
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="7" style="text-align: center;">Tidak ada data</td>
					</tr>
				`;
				} else {
					data.forEach(function (item) {
						if (item.name == "Laporan") {
							return;
						}
						table += `
						<div class="panel"
					style="box-shadow: 0 1px 4px 0 rgba(0,0,0,.1); border: 0.2px solid #E3E3E3; border-radius: 5px; margin-bottom: 25px;">
					<div class="card-header border-bottom order-dashed d-flex align-items-center  ">
						<h3>${item.name}  </h3>
					</div>
					<div class="card-body">
						<button type="button" class="btn btn-primary" name="button" onclick="klik_laporan('${item.name}','${item.path}')"><i class="fa fa-bookmark me-1"></i> Buka
							Laporan</button>
						</div>
					</div>
						`;
					});
				}
				$('#data-laporan').html(table);
			}
		});
	}

	function klik_laporan(nama, path) {
		$("#printLaporan").modal('show');
		$('#myLargeModalLabel').html(nama);
		$('#path').val(path);

		let link = '<?php echo base_url(); ?>' + path;
		$('#form_laporan').attr('action', link);

		if (nama == 'Laporan Jurnal Harian') {
			$('#filter-data').show();
			$('#form-hari').show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').show();
			$('#btn_print_laporan_excel').hide();


		} else if (nama == 'Laporan Jurnal Guru Per Kelas') {
			$('#filter-data').show();
			$('#form-hari').click();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').show();
			$('#btn_print_laporan_excel').show();
			$('#form-jurnal-kegiatan').hide();
			$('#btn_print_laporan_excel').val('jurnal_guru_kelas');



		} else if (nama == 'Laporan Jurnal Kegiatan') {
			$('#filter-data').show();
			$('#form-hari').click();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').show();

			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('jurnal_kegiatan');

		} else if (nama == 'Laporan Izin Pegawai') {
			$('#filter-data').show();
			$('#form-hari').click();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('izin_pegawai');

		} else {
			$('#form-laporan-presensi-siswa').hide();
			$('#form-jurnal-guru-kelas').hide();
			$('#form-jurnal-guru-tanggal').hide();
		}

		$('#laporan_presensi_siswa_filter').change(function () {
			var val = this.value;
			if (val == 1) {
				$("#laporan_presensi_siswa_filter_bulan").hide();
			} else {
				$("#laporan_presensi_siswa_filter_bulan").show();
			}
		});
	}

	function data_jurnal_pegawai() {
		// ambil id_pegawai lebih dulu
		var id_pegawai = $('select[name="id_pegawai"]').val();
		const isSemua = !id_pegawai; // '' atau null/undefined = semua

		// ambil label/jabatan dari option (kalau ada)
		const select = document.querySelector('select[name="id_pegawai"]');
		const selectedOption = select && select.options[select.selectedIndex];
		const namaPegawai = selectedOption ? (selectedOption.getAttribute('data-label') || '') : '';
		const jabatan = selectedOption ? (selectedOption.getAttribute('data-jabatan') || '') : '';

		// toggle info table di atas daftar
		const infoTable = document.querySelector('.jurnal_kegiatan > table');
		if (infoTable) {
			if (isSemua) {
				infoTable.style.display = '';
				$('#nama_pegawai').text('SEMUA PEGAWAI');
				$('#jabatan').text('-');
			} else {
				// JANGAN tampilin nama saat pilih 1 pegawai
				infoTable.style.display = 'none';
				$('#nama_pegawai').text('');
				$('#jabatan').text('');
			}
		}

		// kumpulkan filter
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();

		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			id_pegawai,
			filter,
			bulan,
			tahun,
			single_filter_tahun
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_jurnal_pegawai'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let no = 1;
				let html = '';

				// cek kosong
				const isEmptyArray = Array.isArray(data) && data.length === 0;
				const isEmptyObject = (data && typeof data === 'object' && !Array.isArray(data) && Object.keys(data).length === 0);
				if (!data || isEmptyArray || isEmptyObject) {
					$('#data_jurnal_pegawai').html('<div style="padding:10px; text-align:center;">Tidak ada data</div>');
					// set header default sesuai mode terakhir
					if (isSemua) {
						$('.excel-header').html(`
			<div data-label="No">No</div>
			<div data-label="Tanggal">Tanggal</div>
			<div data-label="Nama">Nama</div>
			<div data-label="Kegiatan">Kegiatan</div>
			<div data-label="Semester">Semester</div>
			<div data-label="Periode">Periode</div>
		  `);
						$('.excel-container').addClass('has-nama-col');
					} else {
						$('.excel-header').html(`
			<div data-label="No">No</div>
			<div data-label="Tanggal">Tanggal</div>
			<div data-label="Kegiatan">Kegiatan</div>
			<div data-label="Semester">Semester</div>
			<div data-label="Periode">Periode</div>
		  `);
						$('.excel-container').removeClass('has-nama-col');
					}
					return;
				}

				if (isSemua) {
					// Header 6 kolom (dengan Nama)
					$('.excel-header').html(`
		  <div data-label="No">No</div>
		  <div data-label="Tanggal">Tanggal</div>
		  <div data-label="Nama">Nama</div>
		  <div data-label="Kegiatan">Kegiatan</div>
		  <div data-label="Semester">Semester</div>
		  <div data-label="Periode">Periode</div>
		`);
					$('.excel-container').addClass('has-nama-col');

					// data = { 'dd-mm-YYYY': [ {...}, {...} ], ... }
					const sortedDates = Object.keys(data).sort((a, b) => {
						const da = a.split('-').reverse().join(''); // YYYYMMDD
						const db = b.split('-').reverse().join('');
						return da.localeCompare(db);
					});

					sortedDates.forEach((tgl) => {
						const items = data[tgl] || [];
						items.forEach((item, idx) => {
							// tanggal hanya di baris pertama grup
							const tanggalCell = (idx === 0) ? (item.tanggal || '-') : '';
							const groupStartAttr = (idx === 0) ? 'data-group-start="1"' : '';
							html += `
			  <div class="excel-row" ${groupStartAttr} data-tanggal="${tgl}">
				<div>${no++}</div>
				<div class="cell-tanggal">${tanggalCell}</div>
				<div>${item.nama_pegawai || '-'}</div>
				<div>${item.kegiatan || '-'}</div>
				<div>${item.semester || '-'}</div>
				<div>${item.periode || '-'}</div>
			  </div>
			`;
						});
					});

				} else {
					// Header 5 kolom (tanpa Nama)
					$('.excel-header').html(`
		  <div data-label="No">No</div>
		  <div data-label="Tanggal">Tanggal</div>
		  <div data-label="Kegiatan">Kegiatan</div>
		  <div data-label="Semester">Semester</div>
		  <div data-label="Periode">Periode</div>
		`);
					$('.excel-container').removeClass('has-nama-col');

					// data = array of rows
					data.forEach(function (item) {
						html += `
			<div class="excel-row">
			  <div>${no++}</div>
			  <div>${item.tanggal || '-'}</div>
			  <div>${item.kegiatan || '-'}</div>
			  <div>${item.semester || '-'}</div>
			  <div>${item.periode || '-'}</div>
			</div>
		  `;
					});
				}

				$('#data_jurnal_pegawai').html(html);
			},
			error: function (xhr, status, error) {
				console.error('AJAX Error:', status, error);
				$('#data_jurnal_pegawai').html('<div style="padding:10px; text-align:center; color:red;">Gagal memuat data</div>');
			}
		});
	}

	function data_jurnal_guru_kelas() {
		const select = document.querySelector('select[name="id_kelas"]');
		const selectedOption = select.options[select.selectedIndex];
		const kelas = selectedOption.getAttribute('data-kelas');
		$('#kelas').text(kelas);

		const selectTahun = document.querySelector('select[name="id_periode_jurnal"]');
		const selectedOptionTahun = selectTahun.options[selectTahun.selectedIndex];
		const jurnal_periode = selectedOptionTahun.getAttribute('data-tahun');

		$('#periode').text(jurnal_periode);

		var filter = $('input[name="filter"]:checked').val();

		var id_kelas = $('select[name="id_kelas"]').val();
		var semester = $('select[name="semester_jurnal"]').val();
		$('#semester').text(semester);

		var periode = $('select[name="id_periode_jurnal"]').val();

		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			id_kelas,
			semester,
			periode
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_jurnal_guru_kelas'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var table = '';


				if (Object.keys(data).length === 0) {
					table = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
				} else {
					Object.keys(data).forEach(function (namaGuru) {
						let mapelList = data[namaGuru];
						let totalBaris = 0;

						Object.keys(mapelList).forEach(mapel => {
							totalBaris += mapelList[mapel].length;
						});

						let guruRowspanAdded = false;

						Object.keys(mapelList).forEach(function (mapel) {
							let jadwalList = mapelList[mapel];

							jadwalList.forEach(function (item, index) {

								var selisih = hitungSelisihHariDMY(item.tanggal, item.tanggal_input);


								table += '<tr>';

								// Tambah kolom No & Nama Guru hanya sekali
								if (!guruRowspanAdded) {
									table += `<td rowspan="${totalBaris}">${no++}</td>`;
									table += `<td rowspan="${totalBaris}">${namaGuru}</td>`;
									guruRowspanAdded = true;
								}

								table += `<td>${mapel}</td>`;
								table += `<td>${item.nama_kelas}</td>`;
								table += `<td>${item.jam_mulai_pelajaran} - ${item.jam_selesai_pelajaran}</td>`;
								table += `<td>${item.kegiatan}</td>`;
								table += `<td>${item.tema}</td>`;
								table += `<td>${item.tanggal} ${selisih}</td>`;

								table += '</tr>';
							});
						});
					});
				}

				$('#data_jurnal_guru_kelas tbody').html(table);

			}
		});
	}


	function data_izin_pegawai() {

		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_izin_pegawai'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {

				var no = 1;
				var table = '';
				if (Object.keys(data).length === 0) {
					table = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
				} else {

					Object.keys(data).forEach(function (tanggal) {
						let jadwalList = data[tanggal];
						let totalBaris = jadwalList.length;
						let guruRowspanAdded = false;

						jadwalList.forEach(function (item, index) {
							table += '<tr>';

							if (!guruRowspanAdded) {
								table += `<td rowspan="${totalBaris}">${no++}</td>`;
								table += `<td rowspan="${totalBaris}">${tanggal}</td>`;
								guruRowspanAdded = true;
							}

							table += `<td>${item.nama_pegawai}</td>`;
							table += `<td>${item.keterangan}</td>`;
							table += `<td>${item.alasan_tidak_hadir}</td>`;
							table += '</tr>';
						});
					});
				}

				$('#data_izin_pegawai tbody').html(table);

			}
		});
	}
</script>

<!-- laporan_excel -->
<script>
	document.getElementById("btn_print_laporan_excel").addEventListener("click", function () {

		var cek_btn = $('#btn_print_laporan_excel').val();


		if (cek_btn == 'jurnal_kegiatan') {
			const rows = document.querySelectorAll(".excel-container .excel-header, .excel-container .excel-row");
			const filter = document.querySelector('input[name="filter"]:checked').value;

			function doExport(filename, judul, subheaders) {
				let data = [];
				data.push([judul]);
				(subheaders || []).forEach(h => data.push([h]));
				data.push([]);

				const rows = document.querySelectorAll(".excel-container .excel-header, .excel-container .excel-row");
				rows.forEach(row => {
					const rowData = Array.from(row.querySelectorAll("div")).map(c => c.textContent.trim());
					data.push(rowData);
				});

				const ws = XLSX.utils.aoa_to_sheet(data);

				const headerCols = document.querySelectorAll(".excel-container .excel-header div").length;
				ws["!cols"] = (headerCols === 6)
					? [{ wch: 6 }, { wch: 14 }, { wch: 28 }, { wch: 40 }, { wch: 12 }, { wch: 12 }]
					: [{ wch: 6 }, { wch: 14 }, { wch: 40 }, { wch: 12 }, { wch: 12 }];


				if (headerCols === 6) {
					const merges = [];


					const headerRowIndex = 1 + (subheaders ? subheaders.length : 0) + 1;
					const firstDataRow = headerRowIndex + 1;
					const lastRow = data.length - 1;

					let spanStart = null;

					for (let r = firstDataRow; r <= lastRow; r++) {
						const tanggalVal = (data[r] && data[r][1]) ? data[r][1] : '';
						const prevTanggalVal = (data[r - 1] && data[r - 1][1]) ? data[r - 1][1] : '';

						if (tanggalVal) {
							if (spanStart !== null && (r - 1) > spanStart) {
								merges.push({ s: { r: spanStart, c: 1 }, e: { r: r - 1, c: 1 } });
							}
							spanStart = r;
						}


						if (r === lastRow && spanStart !== null && r > spanStart) {
							merges.push({ s: { r: spanStart, c: 1 }, e: { r: r, c: 1 } });
						}
					}

					if (merges.length) ws["!merges"] = (ws["!merges"] || []).concat(merges);
				}

				const wb = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(wb, ws, "Laporan Pegawai");
				XLSX.writeFile(wb, filename);

				$('#btn_print_laporan_excel').prop('disabled', false)
					.html('<i class="fa fa-file-excel me-1"></i> Excel');
			}





			if (filter === 'tanggal') {

				setTimeout(() => {
					let data = [];
					var tanggal_dari = document.querySelector('input[name="dari_tanggal"]') ? document.querySelector('input[name="dari_tanggal"]').value : '';
					var tanggal_sampai = document.querySelector('input[name="sampai_tanggal"]') ? document.querySelector('input[name="sampai_tanggal"]').value : '';
					var nama_pegawai = document.querySelector('#nama_pegawai') ? document.querySelector('#nama_pegawai').textContent : '';
					var jabatan = document.querySelector('#jabatan') ? document.querySelector('#jabatan').textContent : '';

					if (!tanggal_dari || !tanggal_sampai) {
						alert("Belum pilih tanggal.");
						return;
					}

					const { nama, jab } = getPegawaiInfo();
					const sub = [
						`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`,
						`Nama Pegawai: ${nama}`,
						`Jabatan: ${jab}`
					];
					doExport(
						`laporan_jurnal_pegawai_tanggal_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`,
						"Laporan Jurnal Pegawai",
						sub
					);
				}, 1500);
			} else if (filter === 'bulan') {

				setTimeout(() => {
					let data = [];
					var bulan = document.querySelector('select[name="filter_bulan"]').value;
					var tahun = document.querySelector('select[name="filter_tahun"]').value;
					const { nama, jab } = getPegawaiInfo();
					const sub = [
						`Bulan: ${getNamaBulan(bulan)} ${tahun}`,
						`Nama Pegawai: ${nama}`,
						`Jabatan: ${jab}`
					];


					doExport(
						`laporan_jurnal_pegawai_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`,
						"Laporan Jurnal Pegawai",
						sub
					);
				}, 1500);
			} else {
				setTimeout(() => {
					var tahun = document.querySelector('select[name="single_filter_tahun"]').value;
					const { nama, jab } = getPegawaiInfo();
					const sub = [
						`Tahun: ${tahun}`,
						`Nama Pegawai: ${nama}`,
						`Jabatan: ${jab}`
					];


					doExport(
						`laporan_jurnal_pegawai_tahun_${tahun}.xlsx`,
						"Laporan Jurnal Pegawai",
						sub
					);
				}, 1500);
			}

		} else if (cek_btn == 'jurnal_guru_kelas') {
			const filter = document.querySelector('input[name="filter"]:checked')?.value;
			if (filter === 'tanggal') {
				setTimeout(() => {
					const tanggal_dari = document.querySelector('input[name="dari_tanggal"]')?.value || '';
					const tanggal_sampai = document.querySelector('input[name="sampai_tanggal"]')?.value || '';


					let data = [];
					var kelas = $('#kelas').text() || '';
					var semester = $('#semester').text() || '';
					var periode = $('#periode').text() || '';
					if (!tanggal_dari || !tanggal_sampai || !kelas || !semester || !periode) {
						alert("Pastikan tanggal, kelas, semester dan tahun ajaran sudah dipilih.");
						return;
					}

					// Header info
					data.push(["Laporan Jurnal Guru Kelas"]);
					data.push([`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					data.push([`Kelas: ${kelas}`]);
					data.push([`Semester: ${semester}`]);
					data.push([`Tahun Ajaran: ${periode}`]);
					data.push([]);
					data.push(["No", "Guru", "Mata Pelajaran", "Kelas", "Jam", "Kegiatan", "Tema", "Tanggal Mengajar"]);

					const tableRows = document.querySelectorAll("#data_jurnal_guru_kelas tbody tr");

					let lastNo = '';
					let lastNamaGuru = '';
					let dataIndex = data.length;
					let mergeInstructions = [];

					let dataOffset = 7;
					let currentRowIndex = dataOffset;
					let spanStartIndex = null;
					let currentNo = '';
					let currentGuru = '';

					tableRows.forEach((row, idx) => {
						const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						let finalRow = [];

						if (cells.length === 6) {
							finalRow.push(lastNo);
							finalRow.push(lastNamaGuru);
							finalRow.push(...cells);
						} else {
							lastNo = cells[0];
							lastNamaGuru = cells[1];
							finalRow = cells;

							// Deteksi merge
							if (currentNo === lastNo && currentGuru === lastNamaGuru) {

							} else {
								// Tutup merge sebelumnya
								if (spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
									// Merge kolom 0 (No) dan 1 (Guru)
									mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
									mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
								}

								// Reset merge tracker
								spanStartIndex = currentRowIndex;
								currentNo = lastNo;
								currentGuru = lastNamaGuru;
							}
						}

						data.push(finalRow);
						currentRowIndex++;
					});


					if (spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
						mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
					}


					const worksheet = XLSX.utils.aoa_to_sheet(data);


					worksheet["!merges"] = mergeInstructions;

					// Set lebar kolom
					worksheet["!cols"] = [
						{ wch: 5 },   // No
						{ wch: 20 },  // Guru
						{ wch: 20 },  // Mapel
						{ wch: 10 },  // Kelas
						{ wch: 20 },  // Jam
						{ wch: 30 },  // Kegiatan
						{ wch: 30 },  // Tema
						{ wch: 25 }   // Tanggal Mengajar
					];

					// Wrap text
					const range = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = range.s.r; R <= range.e.r; ++R) {
						for (let C = range.s.c; C <= range.e.c; ++C) {
							const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
							const cell = worksheet[cell_address];
							if (cell) {
								cell.s = cell.s || {};
								cell.s.alignment = { wrapText: true, vertical: "top" };
							}
						}
					}

					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Jurnal Guru Kelas");
					XLSX.writeFile(workbook, `laporan_jurnal_guru_kelas_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);
					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);

			} else if (filter === 'bulan') {

				setTimeout(() => {
					var bulan = document.querySelector('select[name="filter_bulan"]').value;
					var tahun = document.querySelector('select[name="filter_tahun"]').value;


					let data = [];
					var kelas = $('#kelas').text() || '';
					var semester = $('#semester').text() || '';
					var periode = $('#periode').text() || '';
					if (!kelas || !semester || !periode) {
						alert("Pastikan kelas, semester dan tahun ajaran sudah dipilih.");
						return;
					}

					// Header info
					data.push(["Laporan Jurnal Guru Kelas"]);
					data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					data.push([`Kelas: ${kelas}`]);
					data.push([`Semester: ${semester}`]);
					data.push([`Tahun Ajaran: ${periode}`]);
					data.push([]);
					data.push(["No", "Guru", "Mata Pelajaran", "Kelas", "Jam", "Kegiatan", "Tema", "Tanggal Mengajar"]);

					const tableRows = document.querySelectorAll("#data_jurnal_guru_kelas tbody tr");

					let lastNo = '';
					let lastNamaGuru = '';
					let dataIndex = data.length;
					let mergeInstructions = [];

					let dataOffset = 7;
					let currentRowIndex = dataOffset;
					let spanStartIndex = null;
					let currentNo = '';
					let currentGuru = '';

					tableRows.forEach((row, idx) => {
						const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						let finalRow = [];

						if (cells.length === 6) {
							finalRow.push(lastNo);
							finalRow.push(lastNamaGuru);
							finalRow.push(...cells);
						} else {
							lastNo = cells[0];
							lastNamaGuru = cells[1];
							finalRow = cells;

							// Deteksi merge
							if (currentNo === lastNo && currentGuru === lastNamaGuru) {

							} else {

								if (spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {

									mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
									mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
								}


								spanStartIndex = currentRowIndex;
								currentNo = lastNo;
								currentGuru = lastNamaGuru;
							}
						}

						data.push(finalRow);
						currentRowIndex++;
					});


					if (spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
						mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
					}


					const worksheet = XLSX.utils.aoa_to_sheet(data);


					worksheet["!merges"] = mergeInstructions;


					worksheet["!cols"] = [
						{ wch: 5 },
						{ wch: 20 },
						{ wch: 20 },
						{ wch: 10 },
						{ wch: 20 },
						{ wch: 30 },
						{ wch: 30 },
						{ wch: 25 }
					];


					const range = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = range.s.r; R <= range.e.r; ++R) {
						for (let C = range.s.c; C <= range.e.c; ++C) {
							const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
							const cell = worksheet[cell_address];
							if (cell) {
								cell.s = cell.s || {};
								cell.s.alignment = { wrapText: true, vertical: "top" };
							}
						}
					}

					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Jurnal Guru Kelas");
					XLSX.writeFile(workbook, `laporan_jurnal_guru_kelas_${getNamaBulan(bulan)}_${tahun}.xlsx`);
					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else {
				setTimeout(() => {
					var tahun = document.querySelector('select[name="single_filter_tahun"]').value;


					let data = [];
					var kelas = $('#kelas').text() || '';
					var semester = $('#semester').text() || '';
					var periode = $('#periode').text() || '';
					if (!kelas || !semester || !periode) {
						alert("Pastikan kelas, semester dan tahun ajaran sudah dipilih.");
						return;
					}

					// Header info
					data.push(["Laporan Jurnal Guru Kelas"]);
					data.push([`Tahun: ${tahun}`]);
					data.push([`Kelas: ${kelas}`]);
					data.push([`Semester: ${semester}`]);
					data.push([`Tahun Ajaran: ${periode}`]);
					data.push([]);
					data.push(["No", "Guru", "Mata Pelajaran", "Kelas", "Jam", "Kegiatan", "Tema", "Tanggal Mengajar"]);

					const tableRows = document.querySelectorAll("#data_jurnal_guru_kelas tbody tr");

					let lastNo = '';
					let lastNamaGuru = '';
					let dataIndex = data.length;
					let mergeInstructions = [];

					let dataOffset = 7;
					let currentRowIndex = dataOffset;
					let spanStartIndex = null;
					let currentNo = '';
					let currentGuru = '';

					tableRows.forEach((row, idx) => {
						const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						let finalRow = [];

						if (cells.length === 6) {
							finalRow.push(lastNo);
							finalRow.push(lastNamaGuru);
							finalRow.push(...cells);
						} else {
							lastNo = cells[0];
							lastNamaGuru = cells[1];
							finalRow = cells;

							// Deteksi merge
							if (currentNo === lastNo && currentGuru === lastNamaGuru) {

							} else {

								if (spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {

									mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
									mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
								}


								spanStartIndex = currentRowIndex;
								currentNo = lastNo;
								currentGuru = lastNamaGuru;
							}
						}

						data.push(finalRow);
						currentRowIndex++;
					});


					if (spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
						mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
					}


					const worksheet = XLSX.utils.aoa_to_sheet(data);


					worksheet["!merges"] = mergeInstructions;


					worksheet["!cols"] = [
						{ wch: 5 },
						{ wch: 20 },
						{ wch: 20 },
						{ wch: 10 },
						{ wch: 20 },
						{ wch: 30 },
						{ wch: 30 },
						{ wch: 25 }
					];


					const range = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = range.s.r; R <= range.e.r; ++R) {
						for (let C = range.s.c; C <= range.e.c; ++C) {
							const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
							const cell = worksheet[cell_address];
							if (cell) {
								cell.s = cell.s || {};
								cell.s.alignment = { wrapText: true, vertical: "top" };
							}
						}
					}

					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Jurnal Guru Kelas");
					XLSX.writeFile(workbook, `laporan_jurnal_guru_kelas_tahun_${tahun}.xlsx`);
					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			}

		} else if (cek_btn === 'izin_pegawai') {
			const filter = document.querySelector('input[name="filter"]:checked')?.value;
			if (filter === 'tanggal') {
				setTimeout(() => {
					const tanggal_dari = document.querySelector('input[name="dari_tanggal"]')?.value || '';
					const tanggal_sampai = document.querySelector('input[name="sampai_tanggal"]')?.value || '';

					if (!tanggal_dari || !tanggal_sampai) {
						alert("Pastikan tanggal sudah dipilih.");
						return;
					}

					let data = [];

					// Header laporan
					data.push(["Laporan Izin Pegawai"]);
					data.push([`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Keterangan", "Alasan Tidak Hadir"]);

					const tableRows = document.querySelectorAll("#data_izin_pegawai tbody tr");

					if (tableRows.length === 0) {
						alert("Tidak ada data untuk diekspor.");
						return;
					}

					let lastNo = '';
					let lastTanggal = '';
					let currentNo = '';
					let currentTanggal = '';
					let spanStartIndex = null;
					let currentRowIndex = data.length; // Start row index di Excel
					let mergeInstructions = [];

					tableRows.forEach((row, idx) => {
						const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						const isRowMerged = row.cells.length < 5;

						let finalRow = [];

						if (isRowMerged) {
							// Tambahkan nilai dari baris sebelumnya
							finalRow.push(lastNo);
							finalRow.push(lastTanggal);
							finalRow.push(...cells);
						} else {
							lastNo = cells[0];
							lastTanggal = cells[1];
							finalRow = cells;

							const isNewGroup = currentNo !== lastNo || currentTanggal !== lastTanggal;

							if (isNewGroup && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
								mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
								mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
							}

							if (isNewGroup) {
								spanStartIndex = currentRowIndex;
								currentNo = lastNo;
								currentTanggal = lastTanggal;
							}
						}

						data.push(finalRow);
						currentRowIndex++;

						if (idx === tableRows.length - 1 && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
							mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
							mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
						}
					});

					const worksheet = XLSX.utils.aoa_to_sheet(data);
					worksheet["!merges"] = mergeInstructions;

					worksheet["!cols"] = [
						{ wch: 5 },   // No
						{ wch: 20 },  // Tanggal
						{ wch: 25 },  // Nama Pegawai
						{ wch: 15 },  // Keterangan
						{ wch: 30 },  // Alasan Tidak Hadir
					];


					const range = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = range.s.r; R <= range.e.r; ++R) {
						for (let C = range.s.c; C <= range.e.c; ++C) {
							const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
							const cell = worksheet[cell_address];
							if (cell) {
								cell.s = cell.s || {};
								cell.s.alignment = { wrapText: true, vertical: "top" };
							}
						}
					}


					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Izin Pegawai");
					XLSX.writeFile(workbook, `laporan_izin_pegawai_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);


			} else if (filter === 'bulan') {

				setTimeout(() => {
					const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
					const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';



					let data = [];

					// Header laporan
					data.push(["Laporan Izin Pegawai"]);
					data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Keterangan", "Alasan Tidak Hadir"]);

					const tableRows = document.querySelectorAll("#data_izin_pegawai tbody tr");

					if (tableRows.length === 0) {
						alert("Tidak ada data untuk diekspor.");
						return;
					}

					let lastNo = '';
					let lastTanggal = '';
					let currentNo = '';
					let currentTanggal = '';
					let spanStartIndex = null;
					let currentRowIndex = data.length; // Start row index di Excel
					let mergeInstructions = [];

					tableRows.forEach((row, idx) => {
						const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						const isRowMerged = row.cells.length < 5;

						let finalRow = [];

						if (isRowMerged) {
							// Tambahkan nilai dari baris sebelumnya
							finalRow.push(lastNo);
							finalRow.push(lastTanggal);
							finalRow.push(...cells);
						} else {
							lastNo = cells[0];
							lastTanggal = cells[1];
							finalRow = cells;

							const isNewGroup = currentNo !== lastNo || currentTanggal !== lastTanggal;

							if (isNewGroup && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
								mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
								mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
							}

							if (isNewGroup) {
								spanStartIndex = currentRowIndex;
								currentNo = lastNo;
								currentTanggal = lastTanggal;
							}
						}

						data.push(finalRow);
						currentRowIndex++;

						if (idx === tableRows.length - 1 && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
							mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
							mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
						}
					});

					const worksheet = XLSX.utils.aoa_to_sheet(data);
					worksheet["!merges"] = mergeInstructions;

					worksheet["!cols"] = [
						{ wch: 5 },   // No
						{ wch: 20 },  // Tanggal
						{ wch: 25 },  // Nama Pegawai
						{ wch: 15 },  // Keterangan
						{ wch: 30 },  // Alasan Tidak Hadir
					];


					const range = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = range.s.r; R <= range.e.r; ++R) {
						for (let C = range.s.c; C <= range.e.c; ++C) {
							const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
							const cell = worksheet[cell_address];
							if (cell) {
								cell.s = cell.s || {};
								cell.s.alignment = { wrapText: true, vertical: "top" };
							}
						}
					}


					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Izin Pegawai");
					XLSX.writeFile(workbook, `laporan_izin_pegawai_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else {
				setTimeout(() => {
					const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';



					let data = [];

					// Header laporan
					data.push(["Laporan Izin Pegawai"]);
					data.push([`Tahun: ${tahun}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Keterangan", "Alasan Tidak Hadir"]);

					const tableRows = document.querySelectorAll("#data_izin_pegawai tbody tr");

					if (tableRows.length === 0) {
						alert("Tidak ada data untuk diekspor.");
						return;
					}

					let lastNo = '';
					let lastTanggal = '';
					let currentNo = '';
					let currentTanggal = '';
					let spanStartIndex = null;
					let currentRowIndex = data.length; // Start row index di Excel
					let mergeInstructions = [];

					tableRows.forEach((row, idx) => {
						const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						const isRowMerged = row.cells.length < 5;

						let finalRow = [];

						if (isRowMerged) {
							// Tambahkan nilai dari baris sebelumnya
							finalRow.push(lastNo);
							finalRow.push(lastTanggal);
							finalRow.push(...cells);
						} else {
							lastNo = cells[0];
							lastTanggal = cells[1];
							finalRow = cells;

							const isNewGroup = currentNo !== lastNo || currentTanggal !== lastTanggal;

							if (isNewGroup && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
								mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
								mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
							}

							if (isNewGroup) {
								spanStartIndex = currentRowIndex;
								currentNo = lastNo;
								currentTanggal = lastTanggal;
							}
						}

						data.push(finalRow);
						currentRowIndex++;

						if (idx === tableRows.length - 1 && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
							mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
							mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
						}
					});

					const worksheet = XLSX.utils.aoa_to_sheet(data);
					worksheet["!merges"] = mergeInstructions;

					worksheet["!cols"] = [
						{ wch: 5 },   // No
						{ wch: 20 },  // Tanggal
						{ wch: 25 },  // Nama Pegawai
						{ wch: 15 },  // Keterangan
						{ wch: 30 },  // Alasan Tidak Hadir
					];


					const range = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = range.s.r; R <= range.e.r; ++R) {
						for (let C = range.s.c; C <= range.e.c; ++C) {
							const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
							const cell = worksheet[cell_address];
							if (cell) {
								cell.s = cell.s || {};
								cell.s.alignment = { wrapText: true, vertical: "top" };
							}
						}
					}


					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Izin Pegawai");
					XLSX.writeFile(workbook, `laporan_izin_pegawai_tahun_${tahun}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			}
		}



	});

	function formatTanggal(tanggal) {
		if (!tanggal) return '';
		const parts = tanggal.split('-');
		return `${parts[2]}-${parts[1]}-${parts[0]}`;
	}

	function getNamaBulan(bulan) {
		const namaBulan = [
			"Januari", "Februari", "Maret", "April", "Mei", "Juni",
			"Juli", "Agustus", "September", "Oktober", "November", "Desember"
		];


		const index = parseInt(bulan, 10) - 1;

		return namaBulan[index] || "";
	}

	function hitungSelisihHariDMY(tanggalStr, tanggalInputStr) {
		const tanggal = parseDMYToDate(tanggalStr);
		const tanggalInput = parseDMYToDate(tanggalInputStr);

		const selisihMs = tanggalInput - tanggal;
		const selisihHari = Math.round(selisihMs / (1000 * 60 * 60 * 24));

		const selisih = Math.max(0, selisihHari);

		return selisih <= 0 ? '' : ` (${selisih} hari)`;
	}

	function parseDMYToDate(dmyStr) {
		const [day, month, year] = dmyStr.split('-').map(Number);
		return new Date(year, month - 1, day);
	}

	function getPegawaiInfo() {
		const sel = document.querySelector('select[name="id_pegawai"]');
		const val = sel?.value || '';
		const isSemua = !val; // value "" = Semua Pegawai
		const opt = sel?.options[sel.selectedIndex];
		const nama = isSemua ? 'SEMUA PEGAWAI' : (opt?.getAttribute('data-label') || '-');
		const jab = isSemua ? '-' : (opt?.getAttribute('data-jabatan') || '-');
		return { isSemua, nama, jab };
	}

</script>