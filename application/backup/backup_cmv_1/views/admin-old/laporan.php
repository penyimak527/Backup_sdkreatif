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
					<div id="form-hari" class="row">
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
								<label class="mb-1">Tanggal</label>
								<input type="date" name="tanggal_jurnal" class="form-control">
							</div>
						</div>
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
								<select type="date" name="semester" class="form-control">
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
									<option value="">Pilih Kelas</option>
									<?php
									foreach ($kelas as $ke): ?>
										?>
										<option value="<?php echo $ke['id']; ?>">
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
										<option value="<?php echo $pe['id']; ?>">
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
								<select type="date" name="id_pegawai" class="form-control">
									<option value="">Pilih Pegawai</option>
									<?php
									foreach ($pegawai as $ke): ?>
										?>
										<option value="<?php echo $ke['id']; ?>">
											<?php echo $ke['nama_pegawai']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

					</div>
				</form>
				<div class="modal-footer" style="margin-right:-22px;">
					<button type="button" class="btn btn-info waves-effect" id="btn_print_laporan"><i
							class="fa fa-print me-1"></i>
						Print</button>
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
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
				$('#filter-data').hide();
				$('#form-hari').hide();
				$('#form-bulan').hide();
				$('#form-tahun').hide();
				$('#form-jurnal-harian').show();


			} else if (nama == 'Laporan Jurnal Guru Per Kelas') {
				$('#filter-data').show();
				$('#form-hari').click();
				$('#form-bulan').hide();
				$('#form-tahun').hide();
				$('#form-jurnal-harian').hide();
				$('#form-jurnal-guru').show();


			} else if (nama == 'Laporan Jurnal Kegiatan') {
				$('#filter-data').show();
				$('#form-hari').click();
				$('#form-bulan').hide();
				$('#form-tahun').hide();
				$('#form-jurnal-harian').hide();
				$('#form-jurnal-guru').hide();
				$('#form-jurnal-kegiatan').show();


			} else if (nama == 'Laporan Izin Pegawai') {
				$('#filter-data').show();
				$('#form-hari').click();
				$('#form-bulan').hide();
				$('#form-tahun').hide();
				$('#form-jurnal-harian').hide();
				$('#form-jurnal-guru').hide();
				$('#form-jurnal-kegiatan').hide();


			} else {
				$('#form-laporan-presensi-siswa').hide();
				$('#form-pegawai').hide();
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
	</script>