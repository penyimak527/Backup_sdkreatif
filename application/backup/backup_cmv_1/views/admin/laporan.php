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
										<option value="<?php echo $pe['id']; ?>">
											<?php echo $pe['periode']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div id="form-semester-tahun-ajaran" class="row g-2" style="display: none;">
						<!-- <div class="col-md-12">
							<div class="form-group">
								<label class="mb-1 mt-1">Semester</label>
								<select name="semester" class="form-control">
									<option value="">Pilih Semester</option>
									<option value="Tahunan">Tahunan</option>
									<option value="Ganjil">Ganjil</option>
									<option value="Genap">Genap</option>
								</select>
							</div>
						</div> -->
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Tahun Ajaran</label>
								<select type="date" name="id_periode" class="form-control">
									<option value="">Pilih Tahun Ajaran</option>
									<?php foreach ($periode as $pe): ?>
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
											<?= $ke['nama_kelas']; ?> 	<?= $ke['kode_kelas']; ?>
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
									<?php foreach ($periode as $pe): ?>
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
					<div id="form-pegawai-all" class="row g-2" style="display: none; margin-top: 10px;">
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Pegawai</label>
								<select type="date" name="id_pegawai_all" class="form-control"
									onchange="data_presensi_per_pegawai()">
									<option value="">Pilih Pegawai</option>
									<?php foreach ($pegawai_all as $ke1): ?>
										<option data-label="<?= $ke1['nama_pegawai']; ?>" value="<?php echo $ke1['id']; ?>">
											<?php echo $ke1['nama_pegawai']; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div id="form-pegawai-all-absen" class="row g-2" style="display: none; margin-top: 10px;">
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Tampil Pegawai</label>
								<select type="date" name="pegawai_all_absen" class="form-control"
									onchange="data_presensi_pegawai()">
									<!-- <option value="">Pilih Tampil </option> -->
									<option value="tampil">Semua Pegawai</option>
									<option value="absen">Pegawai Absen</option>
								</select>
							</div>
						</div>
					</div>

					<div id="form-resume-tanggal" class="row g-2" style="display: none; margin-top: 10px;">
						<div class="col-md-12">
							<div class="form-group">
								<label class="mb-1">Pilih Laporan</label>
								<select name="laporan" class="form-control" onchange="updateValExcel()">
									<option value="Guru">Jurnal Guru</option>
									<option value="Karyawan">Jurnal Karyawan</option>
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
<div class="resume_tanggal_jurnal_kegiatan" style="display: none;">
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
	<table id="data_resume_tanggal_kegiatan" class="grid">
		<thead>
			<tr>
				<td>NO</td>
				<td>NAMA PEGAWAI</td>
				<td>TANGGAL BELUM DIISI</td>
				<td>SEMESTER</td>
				<td>PERIODE</td>
			</tr>
		</thead>
		<tbody>

		</tbody>

	</table>
</div>
<div class="resume_tanggal_jurnal_guru" style="display: none
;">
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
	<table id="data_resume_tanggal_guru" class="grid">
		<thead>
			<tr>
				<td>NO</td>
				<td>NAMA</td>
				<td>KELAS</td>
				<td>MATA PELAJARAN</td>
				<td>TANGGAL BELUM DIISI</td>
				<td>SEMESTER</td>
				<td>PERIODE</td>
			</tr>
		</thead>
		<tbody>

		</tbody>

	</table>
</div>
<div class="modal fade" id="cek_excel_pegawai" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="tampil_pegawai" disabled readonly>
				<table id="data_presensi_pegawai" class="grid">
					<thead>
						<tr>
							<td>No</td>
							<td>TANGGAL</td>
							<td>NAMA PEGAWAI</td>
							<td>JAM MASUK</td>
							<td>JAM TERLAMBAT</td>
							<td>JAM PULANG</td>
							<td>STATUS</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_pegawai_terlambat" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<table id="data_presensi_keterlambatan_pegawai" class="grid">
					<thead>
						<tr>
							<td>No</td>
							<td>TANGGAL</td>
							<td>NAMA PEGAWAI</td>
							<td>JABATAN</td>
							<td>JAM MASUK</td>
							<td>MENIT TERLAMBAT</td>
							<td>STATUS</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_per_pegawai" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				<input type="hidden" id="nama_per_pegawai" disabled readonly>
				<input type="hidden" id="pegawai_per_jabatan" disabled readonly>
			</div>
			<div class="modal-body">

				<table id="data_presensi_per_pegawai" class="grid">
					<thead>
						<tr>
							<td>No</td>
							<td>TANGGAL</td>
							<td>JAM MASUK</td>
							<td>MENIT TERLAMBAT</td>
							<td>JAM PULANG</td>
							<td>STATUS</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_rekapitulasi_gaji" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<table id="data_rekapitulasi_gaji" class="grid">
					<thead>
						<tr id="header_rekapitulasi_gaji">

						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_kas" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<table id="data_laporan_kas" class="grid">
					<thead>
						<tr>
							<td>Uraian</td>
							<td>Jumlah</td>
							<td>Uraian</td>
							<td>Jumlah</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
				<table id="table_tahunan" class="grid" style="display:none;">
					<thead></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_penggunaan_anggaran" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_penggunaan_anggaran" class="grid">
					<thead>
						<tr>
							<td>Uraian</td>
							<td>Jumlah</td>
							<td>Uraian</td>
							<td>Jumlah</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_rencana_anggaran" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_rencana_anggaran" class="grid">
					<thead>
						<tr>
							<td>Uraian</td>
							<td>Jumlah</td>
							<td>Uraian</td>
							<td>Jumlah</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_pos" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_pos" class="grid">
					<thead>
						<tr>
							<td>Uraian</td>
							<td>Saldo Bulan Lalu</td>
							<td>Masuk</td>
							<td>Keluar</td>
							<td>Saldo</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="cek_excel_laporan_rencana_asumsi_pemasukan" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_rencana_asumsi_pemasukan" class="grid">
					<thead>

					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_rencana_pengeluaran" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_rencana_pengeluaran" class="grid">
					<thead>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_rencana_pemasukan" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_rencana_pemasukan" class="grid">
					<thead>
						<tr>
							<td widtd="5%">No.</td>
							<td widtd="28%">Jenis Pendapatan</td>
							<td>Satuan</td>
							<td>Vol</td>
							<td>Nilai Satuan</td>
							<td>Jumlah</td>
							<td>Satuan Penerimaan</td>
							<td>Volume Penerimaan</td>
							<td>Total</td>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_olah_pos" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_olah_pos" class="grid">
					<thead>
						<tr>
							<td>Akun</td>
							<th colspan="3">Jan</th>
							<th colspan="3">Feb</th>
							<th colspan="3">Mar</th>
							<th colspan="3">Apr</th>
							<th colspan="3">Mei</th>
							<th colspan="3">Jun</th>
							<th colspan="3">Jul</th>
							<th colspan="3">Ags</th>
							<th colspan="3">Sep</th>
							<th colspan="3">Okt</th>
							<th colspan="3">Nov</th>
							<th colspan="3">Des</th>
						</tr>
						<tr>

							<?php for ($b = 1; $b <= 12; $b++) { ?>

								<th>Masuk</th>
								<th>Keluar</th>
								<th>Saldo</th>

							<?php } ?>

						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_olah_in" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_olah_in" class="grid">
					<thead>
						<tr>
							<td>Akun</td>
							<th>Jan</th>
							<th>Feb</th>
							<th>Mar</th>
							<th>Apr</th>
							<th>Mei</th>
							<th>Jun</th>
							<th>Jul</th>
							<th>Agu</th>
							<th>Sep</th>
							<th>Okt</th>
							<th>Nov</th>
							<th>Des</th>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_laporan_olah_out" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_olah_out" class="grid">
					<thead>
						<tr>
							<td>Akun</td>
							<th>Jan</th>
							<th>Feb</th>
							<th>Mar</th>
							<th>Apr</th>
							<th>Mei</th>
							<th>Jun</th>
							<th>Jul</th>
							<th>Agu</th>
							<th>Sep</th>
							<th>Okt</th>
							<th>Nov</th>
							<th>Des</th>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="cek_excel_laporan_perbandingan_rencana_pengeluaran" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_perbandingan_rencana_pengeluaran" class="grid">
					<thead>

					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="cek_excel_laporan_perbandingan_rencana_pemasukan" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="data_laporan_perbandingan_rencana_pemasukan" class="grid">
					<thead>

					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cek_excel_penerimaan_honorarium" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<table id="data_penerimaan_honorarium" class="grid table table-bordered table-sm">
					<thead>
						<tr>
							<th rowspan="2" style="width:35px;">No</th>
							<th rowspan="2" style="width:195px;">Nama</th>
							<th rowspan="2" style="width:110px;">Jabatan</th>

							<th colspan="3">Gaji</th>
							<th colspan="3">Tunjangan</th>

							<th rowspan="2" style="width:80px;">Jumlah</th>
							<th rowspan="2" style="width:95px;">Jumlah Kotor</th>
							<th rowspan="2" style="width:60px;">Jumlah Hadir</th>
							<th rowspan="2" style="width:105px;">Jumlah Penerimaan</th>
						</tr>
						<tr>
							<th style="width:65px;">Masa Kerja</th>
							<th style="width:75px;">Pend. Terakhir</th>
							<th style="width:90px;">Gaji Pokok</th>

							<th style="width:90px;">Struktural</th>
							<th style="width:90px;">Pendidikan</th>
							<th style="width:90px;">Wali Kelas</th>
						</tr>
					</thead>

					<tbody></tbody>

					<tfoot>
						<tr>
							<th colspan="3" class="text-center">JUMLAH</th>

							<th class="text-center">-</th>
							<th class="text-center">-</th>
							<th class="text-end" id="total_gaji_pokok">0</th>

							<th class="text-end" id="total_struktural">0</th>
							<th class="text-end" id="total_pendidikan">0</th>
							<th class="text-end" id="total_wali_kelas">0</th>

							<th class="text-end" id="total_jumlah">0</th>
							<th class="text-end" id="total_jumlah_kotor">0</th>
							<th class="text-center" id="total_hadir">0</th>
							<th class="text-end" id="total_jumlah_penerimaan">0</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
	$(document).ready(function () {
		let today = new Date();

		// Format YYYY-MM-DD
		let tanggalSekarang = today.getFullYear() + '-' +
			String(today.getMonth() + 1).padStart(2, '0') + '-' +
			String(today.getDate()).padStart(2, '0');

		// Format bulan 01-12
		let bulanSekarang = String(today.getMonth() + 1).padStart(2, '0');

		// Tahun
		let tahunSekarang = today.getFullYear();

		// Set default value
		$('input[name="dari_tanggal"]').val(tanggalSekarang);
		$('input[name="sampai_tanggal"]').val(tanggalSekarang);

		$('select[name="filter_bulan"]').val(bulanSekarang);
		$('select[name="filter_tahun"]').val(tahunSekarang);

		$('select[name="single_filter_tahun"]').val(tahunSekarang);

		// Tampilkan form hari sebagai default
		$('#form-hari').show();
		$('#form-bulan').hide();
		$('#form-tahun').hide();
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
			const cek_btn = $(this).val();
			if (cek_btn == 'Guru') {
				data_resume_tanggal_jurnal();
			} else if (cek_btn == 'laporan_rencana_pemasukan') {
				data_laporan_rencana_pemasukan();
			} else if (cek_btn == 'laporan_rencana_asumsi_pemasukan') {
				data_laporan_rencana_asumsi_pemasukan();
			} else if (cek_btn == 'laporan_perbandingan_rencana_pemasukan') {
				data_laporan_perbandingan_rencana_pemasukan();
			} else if (cek_btn == 'laporan_kas') {
				data_laporan_kas();
			} else if (cek_btn == 'laporan_penggunaan_anggaran') {
				data_laporan_penggunaan_anggaran();
			} else if (cek_btn == 'laporan_rencana_anggaran') {
				data_laporan_rencana_anggaran();
			} else if (cek_btn == 'laporan_pos') {
				data_laporan_pos();
			} else if (cek_btn == 'laporan_rekapitulasi_gaji') {
				data_rekapitulasi_gaji();
			} else if (cek_btn == 'laporan_penerimaan_honorarium') {
				data_penerimaan_honorarium();
			} else if (cek_btn == 'laporan_rencana_pengeluaran') {
				data_laporan_rencana_pengeluaran();
			} else if (cek_btn == 'laporan_olah_pos') {
				data_laporan_olah_pos();
			} else if (cek_btn == 'laporan_olah_in') {
				data_laporan_olah_in();
			} else if (cek_btn == 'laporan_olah_out') {
				data_laporan_olah_out();
			} else if (cek_btn == 'laporan_perbandingan_rencana_pengeluaran') {
				data_laporan_perbandingan_rencana_pengeluaran();
			} else if (cek_btn == 'rekap_presensi_keterlambatan_pegawai') {
				data_presensi_keterlambatan_pegawai();
			}
			data_jurnal_pegawai();
			data_jurnal_guru_kelas();
			data_resume_tanggal_kegiatan();
			data_izin_pegawai();
			data_presensi_pegawai();
			data_presensi_per_pegawai();

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
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').show();
			$('#btn_print_laporan_excel').hide();
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
			$('#form-pegawai-all-absen').hide();
		} else if (nama == 'Laporan Jurnal Guru Per Kelas') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').show();
			$('#btn_print_laporan_excel').show();
			$('#form-pegawai-all-absen').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#btn_print_laporan_excel').val('jurnal_guru_kelas');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Jurnal Kegiatan') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').show();
			$('#form-resume-tanggal').hide();
			$('#form-pegawai-all-absen').hide();
			$('#form-semester-tahun-ajaran').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('jurnal_kegiatan');

		} else if (nama == 'Laporan Izin Pegawai') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-pegawai-all-absen').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('izin_pegawai');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();

		} else if (nama == 'Laporan Resume Tanggal Belum Diisi') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-resume-tanggal').show();
			$('#form-jurnal-guru').hide();
			$('#form-pegawai-all-absen').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-semester-tahun-ajaran').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('Guru');

		} else if (nama == 'Laporan Presensi Pegawai') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').show();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('presensi_pegawai');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();

		} else if (nama == 'Laporan Rekap Keterlambatan Pegawai') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('rekap_presensi_keterlambatan_pegawai');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Presensi Per Pegawai') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').show();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('presensi_per_pegawai');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Penggunaan Anggaran') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_penggunaan_anggaran');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Kas') {
			$('#filter-data').show();
			$('#form-hari').click().show();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_kas');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Rencana Anggaran') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_rencana_anggaran');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Pos') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_pos');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Rencana Asumsi Pemasukan') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-semester-tahun-ajaran').show();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_rencana_asumsi_pemasukan');
			$('#form-resume-tanggal').hide();
		} else if (nama == 'Laporan Rekapitulasi Gaji') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_rekapitulasi_gaji');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Penerimaan Honorarium Guru Dan Karyawan') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_penerimaan_honorarium');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Slip Gaji') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').show();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').hide();
			$('#btn_print_laporan_excel').val('slip_gaji');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Rencana Pengeluaran') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_rencana_pengeluaran');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').show();
		} else if (nama == 'Laporan Rencana Pemasukan') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_rencana_pemasukan');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').show();
		} else if (nama == 'Laporan Olah Pos') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').show();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_olah_pos');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Olah In') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').show();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_olah_in');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Olah Out') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').show();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_olah_out');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
		} else if (nama == 'Laporan Perbandingan Rencana Pemasukan') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-semester-tahun-ajaran').show();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_perbandingan_rencana_pemasukan');
			$('#form-resume-tanggal').hide();
		} else if (nama == 'Laporan Perbandingan Rencana Pengeluaran') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').hide();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').show();
			$('#btn_print_laporan_excel').val('laporan_perbandingan_rencana_pengeluaran');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').show();
		} else if (nama == 'Daftar Penerimaan Gaji') {
			$('#filter-data').hide();
			$('#form-hari').hide();
			$('#form-bulan').show();
			$('#form-tahun').hide();
			$('#form-jurnal-harian').hide();
			$('#form-jurnal-guru').hide();
			$('#form-jurnal-kegiatan').hide();
			$('#form-pegawai-all').hide();
			$('#form-pegawai-all-absen').hide();
			$('#btn_print_laporan_excel').hide();
			$('#btn_print_laporan_excel').val('daftar_penerimaan_gaji');
			$('#form-resume-tanggal').hide();
			$('#form-semester-tahun-ajaran').hide();
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

				// Helper tanggal
				const parseDate = (str) => {
					if (!str) return null;
					// dukung "d-m-Y" dan "Y-m-d"
					const dmy = str.split('-');
					if (dmy.length === 3) {
						if (dmy[2].length === 4) { // d-m-Y
							const [d, m, y] = dmy.map(x => parseInt(x, 10));
							const dt = new Date(y, m - 1, d); // local
							return isNaN(dt) ? null : dt;
						} else { // Y-m-d
							const [y, m, d] = dmy.map(x => parseInt(x, 10));
							const dt = new Date(y, m - 1, d);
							return isNaN(dt) ? null : dt;
						}
					}
					// fallback Date()
					const dt = new Date(str);
					return isNaN(dt) ? null : dt;
				};
				const fmtDMY = (dt) => {
					const pad = (n) => String(n).padStart(2, '0');
					return `${pad(dt.getDate())}-${pad(dt.getMonth() + 1)}-${dt.getFullYear()}`;
				};
				const diffDays = (a, b) => {
					const ms = a.getTime() - b.getTime();
					return Math.floor(ms / 86400000);
				};

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
							const tanggalCell = (idx === 0) ? (item.nama_pegawai || '-') : '';
							const groupStartAttr = (idx === 0) ? 'data-group-start="1"' : '';

							// === LOGIKA SAMA DENGAN TABEL PHP ===
							const dtKeg = parseDate(item.tanggal);
							let dtIn = parseDate(item.tanggal_input);
							const sameDay = (dtKeg && dtIn) ? (fmtDMY(dtKeg) === fmtDMY(dtIn)) : false;

							let selisihTxt = '';
							if (dtKeg && dtIn) {
								const d = diffDays(dtIn, dtKeg);
								if (d > 0) selisihTxt = ` (${d} hari)`;
							}

							if (sameDay) {
								// normal
								html += `
			<div class="excel-row" ${groupStartAttr} data-tanggal="${tgl}">
			  <div>${no++}</div>
			  <div class="cell-tanggal">${tanggalCell}</div>
			  <div>${item.tanggal || '-'} ${item.waktu || '-'}</div>
			  <div>${(item.kegiatan || '-')}<span class="muted">${selisihTxt}</span></div>
			  <div>${item.semester || '-'}</div>
			  <div>${item.periode || '-'}</div>
			</div>
		  `;
							} else {
								// merge 3 kolom terakhir
								const tInLbl = dtIn ? fmtDMY(dtIn) : '-';
								html += `
			<div class="excel-row" ${groupStartAttr} data-tanggal="${tgl}">
			  <div>${no++}</div>
			  <div class="cell-tanggal">${tanggalCell}</div>
			  <div>${item.tanggal || '-'} ${item.waktu || '-'}</div>
			  <div class="merge-3">
				<em>Belum diisi</em>
				${dtIn ? ` (tanggal input: ${tInLbl}${selisihTxt})` : ''}
			  </div>
			  <div class="merged-placeholder"></div>
			  <div class="merged-placeholder"></div>
			</div>
		  `;
							}
						});
					});

				} else {

					$('.excel-header').html(`
						<div data-label="No">No</div>
						<div data-label="Tanggal">Tanggal</div>
						<div data-label="Kegiatan">Kegiatan</div>
						<div data-label="Semester">Semester</div>
						<div data-label="Periode">Periode</div>
					`);
					$('.excel-container').removeClass('has-nama-col');


					data.forEach(function (item) {
						const dtKeg = parseDate(item.tanggal);
						const dtIn = parseDate(item.tanggal_input);
						const sameDay = (dtKeg && dtIn) ? (fmtDMY(dtKeg) === fmtDMY(dtIn)) : false;

						let selisihTxt = '';
						if (dtKeg && dtIn) {
							const d = diffDays(dtIn, dtKeg);
							if (d > 0) selisihTxt = ` (${d} hari)`;
						}

						if (sameDay) {
							html += `
		  <div class="excel-row">
			<div>${no++}</div>
			<div>${item.tanggal || '-'}</div>
			<div>${(item.kegiatan || '-')}<span class="muted">${selisihTxt}</span></div>
			<div>${item.semester || '-'}</div>
			<div>${item.periode || '-'}</div>
		  </div>
		`;
						} else {
							const tInLbl = dtIn ? fmtDMY(dtIn) : '-';
							html += `
		  <div class="excel-row">
			<div>${no++}</div>
			<div>${item.tanggal || '-'}</div>
			<div class="merge-3">
			  <em>Belum diisi </em>
			  ${dtIn ? ` (tanggal input: ${tInLbl}${selisihTxt})` : ''}
			</div>
			<div class="merged-placeholder"></div>
			<div class="merged-placeholder"></div>
		  </div>
		`;
						}
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

	function parseDMY(str) {

		if (!str || typeof str !== 'string') return null;
		const parts = str.trim().split('-');
		if (parts.length !== 3) return null;
		const [d, m, y] = parts.map(Number);
		if (!d || !m || !y) return null;
		const dt = new Date(y, m - 1, d);

		if (dt.getFullYear() !== y || dt.getMonth() !== m - 1 || dt.getDate() !== d) return null;
		return dt;
	}

	function formatDMY(date) {
		if (!(date instanceof Date)) return '';
		const d = String(date.getDate()).padStart(2, '0');
		const m = String(date.getMonth() + 1).padStart(2, '0');
		const y = date.getFullYear();
		return `${d}-${m}-${y}`;
	}

	function diffDays(a, b) {

		if (!(a instanceof Date) || !(b instanceof Date)) return 0;
		const aUTC = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
		const bUTC = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
		const ms = Math.abs(bUTC - aUTC);
		return Math.floor(ms / (1000 * 60 * 60 * 24));
	}

	function data_jurnal_guru_kelas() {

		const select = document.querySelector('select[name="id_kelas"]');
		const kelas = select?.options[select.selectedIndex]?.getAttribute('data-kelas') || '-';
		$('#kelas').text(kelas);

		const selectTahun = document.querySelector('select[name="id_periode_jurnal"]');
		const jurnal_periode = selectTahun?.options[selectTahun.selectedIndex]?.getAttribute('data-tahun') || '-';
		$('#periode').text(jurnal_periode);

		const filter = $('input[name="filter"]:checked').val();
		const id_kelas = $('select[name="id_kelas"]').val();
		const semester = $('select[name="semester_jurnal"]').val() || '-';
		$('#semester').text(semester);
		const periode = $('select[name="id_periode_jurnal"]').val();

		const dari_tanggal = $('input[name="dari_tanggal"]').val();
		const sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		const bulan = $('select[name="filter_bulan"]').val();
		const tahun = $('select[name="filter_tahun"]').val();
		const single_filter_tahun = $('select[name="single_filter_tahun"]').val();

		const dataPost = { dari_tanggal, sampai_tanggal, bulan, tahun, single_filter_tahun, filter, id_kelas, semester, periode };

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_jurnal_guru_kelas'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let no = 1, table = '';

				if (!data || Object.keys(data).length === 0) {
					table = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
					$('#data_jurnal_guru_kelas tbody').html(table);
					return;
				}

				Object.keys(data).forEach(function (namaGuru) {
					const mapelList = data[namaGuru] || {};

					let totalBaris = 0;
					Object.keys(mapelList).forEach(mapel => {
						(mapelList[mapel] || []).forEach(item => {
							const tglMengajar = parseDMY((item.tanggal || '').trim());
							const tglInput = item.tanggal_input ? parseDMY(item.tanggal_input.trim()) : null;

							let isLate = false;
							if (tglMengajar && tglInput) {
								isLate = (diffDays(tglMengajar, tglInput) > 0) && (tglInput > tglMengajar);
							} else if (tglMengajar && !tglInput) {
								isLate = true;
							}
							totalBaris += 1 + (isLate ? 1 : 0);
						});
					});

					let guruRowspanAdded = false;

					Object.keys(mapelList).forEach(function (mapel) {
						const jadwalList = mapelList[mapel] || [];

						jadwalList.forEach(function (item) {
							const tglMengajarStr = (item.tanggal || '').trim();
							const tglInputStr = (item.tanggal_input || '').trim();
							const tglMengajar = parseDMY(tglMengajarStr);
							const tglInput = tglInputStr ? parseDMY(tglInputStr) : null;

							const expectedFillDate = tglMengajar ? new Date(tglMengajar.getTime()) : null;

							let daysLate = 0, isLate = false;
							if (tglMengajar && tglInput) {
								daysLate = diffDays(tglMengajar, tglInput);
								isLate = (daysLate > 0) && (tglInput > tglMengajar);
							} else if (tglMengajar && !tglInput) {
								const today = new Date();
								daysLate = diffDays(tglMengajar, today);
								isLate = true;
							}

							table += '<tr>';

							if (!guruRowspanAdded) {
								table += `<td rowspan="${totalBaris}">${no++}</td>`;
								table += `<td rowspan="${totalBaris}">${namaGuru}</td>`;
								guruRowspanAdded = true;
							}

							table += `<td>${mapel || '-'}</td>`;
							table += `<td>${item.nama_kelas || '-'}</td>`;
							table += `<td>${(item.jam_mulai_pelajaran || '-') + ' - ' + (item.jam_selesai_pelajaran || '-')}</td>`;
							table += `<td>${item.kegiatan || '-'}</td>`;
							table += `<td>${item.tema || '-'}</td>`;
							table += `<td>${tlg(tglMengajar, tglInput, daysLate)}</td>`;
							table += '</tr>';

							if (isLate) {
								const expectedStr = expectedFillDate ? formatDMY(expectedFillDate) : (tglMengajarStr || '-');
								const inputStr = tglInput ? formatDMY(tglInput) : '-';

								const note = `Seharusnya diisi: <b>${expectedStr}</b> <span class="badge-late">BELUM DIISI</span>`
									+ (tglInput ? ` — <span class="text-muted">Diinput: ${inputStr}</span>` : '');


								table += '<tr class="row-note">';

								table += '<td></td>';         // Mata Pelajaran (kosong)
								table += '<td></td>';         // Kelas (kosong)
								table += `<td colspan="4" style="text-align: center;">${note}</td>`; // Jam + Kegiatan + Tema + Tanggal Mengajar
								table += '</tr>';
							}
						});
					});
				});

				$('#data_jurnal_guru_kelas tbody').html(table);
			},
			error: function () {
				$('#data_jurnal_guru_kelas tbody').html(
					'<tr><td colspan="8" class="text-center">Gagal memuat data</td></tr>'
				);
			}
		});
	}

	function tlg(tglMengajar, tglInput, daysLate) {
		if (!tglMengajar) return '-';
		const base = formatDMY(tglMengajar);
		if (!tglInput) return base;
		if (daysLate > 0) return `${base} <span class="text-muted">(${daysLate} hari)</span>`;
		return base;
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
					table = '<tr><td colspan="8" class="text-center">Tidak ada data</td ></tr > ';
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
	function data_resume_tanggal_kegiatan() {
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
			url: '<?= base_url('admin/laporan/laporan_resume_tanggal_kegiatan_belum_diisi'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (resp) {
				// Jika server kirim wrapper { data: {...}, meta: {...} }
				var data = resp.data || resp;

				// (Opsional) Meta untuk header kecil di atas tabel
				if (resp.meta) {
					$('#kelas').text(resp.meta.kelas || '-');
					$('#semester').text(resp.meta.semester || '-');
					$('#periode').text(resp.meta.periode || '-');
				}

				var esc = function (s) {
					return String(s ?? '').replace(/[&<>"']/g, function (m) {
						return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]);
					});
				};

				var names = Object.keys(data).filter(function (k) { return k !== 'meta'; });

				var table = '';
				var no = 1;

				if (!names.length) {
					table = '<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>';
				} else {
					// Urutkan nama pegawai (natural)
					names.sort(function (a, b) { return a.localeCompare(b, 'id', { numeric: true, sensitivity: 'base' }); });

					names.forEach(function (nama) {
						var item = data[nama] || {};
						var semester = item.semester ?? '-';
						var periode = item.periode ?? '-';
						var dates = Array.isArray(item.tanggal) ? item.tanggal.slice() : [];

						// Tampilkan HANYA pegawai yang masih punya tanggal kosong
						if (!dates.length) return;

						// Urutkan tanggal dd-mm-YYYY
						dates.sort(function (a, b) {
							var pa = a.split('-'), pb = b.split('-');
							// y-m-d untuk pembandingan
							var da = new Date(pa[2] + '-' + pa[1] + '-' + pa[0]);
							var db = new Date(pb[2] + '-' + pb[1] + '-' + pb[0]);
							return da - db;
						});

						var rowspan = dates.length;

						dates.forEach(function (tgl, idx) {
							table += '<tr>';
							if (idx === 0) {
								table += '<td rowspan="' + rowspan + '">' + (no++) + '</td>';
								table += '<td rowspan="' + rowspan + '">' + esc(nama) + '</td>';
							}

							table += '<td>' + esc(tgl) + '</td>';

							if (idx === 0) {
								table += '<td rowspan="' + rowspan + '">' + esc(semester) + '</td>';
								table += '<td rowspan="' + rowspan + '">' + esc(periode) + '</td>';
							}
							table += '</tr>';
						});
					});

					if (!table) {
						// Kalau semua pegawai lengkap (dates kosong semua)
						table = '<tr><td colspan="5" class="text-center">Semua pegawai sudah mengisi pada rentang ini.</td></tr>';
					}
				}

				$('#data_resume_tanggal_kegiatan tbody').html(table);
			}
		});
	}

	function data_resume_tanggal_jurnal() {
		const filter = $('input[name="filter"]:checked').val();
		const dari_tanggal = $('input[name="dari_tanggal"]').val();
		const sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		const bulan = $('select[name="filter_bulan"]').val();
		const tahun = $('select[name="filter_tahun"]').val();
		const single_filter_tahun = $('select[name="single_filter_tahun"]').val();

		const dataPost = { dari_tanggal, sampai_tanggal, bulan, tahun, single_filter_tahun, filter };

		// Validasi sederhana
		if (filter === 'tanggal' && (!dari_tanggal || !sampai_tanggal)) {
			alert('Pastikan tanggal sudah dipilih 1.');
			return;
		}
		if (filter === 'bulan' && (!bulan || !tahun)) {
			alert('Pastikan bulan dan tahun dipilih.');
			return;
		}
		if (filter !== 'tanggal' && filter !== 'bulan' && !single_filter_tahun) {
			alert('Pastikan tahun dipilih.');
			return;
		}

		const $tbody = $('#data_resume_tanggal_guru tbody');
		$tbody.html('<tr><td colspan="7" class="text-center">Memuat...</td></tr>');

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_resume_tanggal_guru_belum_diisi'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (resp) {
				const payload = (resp && typeof resp === 'object') ? (resp.data || resp) : {};
				const meta = resp && resp.meta ? resp.meta : null;

				if (meta) {
					$('#kelas').text(meta.kelas || '-');
					$('#semester').text(meta.semester || '-');
					$('#periode').text(meta.periode || '-');
				}

				const esc = (s) =>
					String(s ?? '').replace(/[&<>"']/g, (m) =>
						({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])
					);

				let names = Object.keys(payload).filter((k) => k !== 'meta');
				if (!names.length) {
					$tbody.html('<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>');
					return;
				}

				names.sort((a, b) => a.localeCompare(b, 'id', { numeric: true, sensitivity: 'base' }));

				let html = '';
				let no = 1;

				names.forEach((nama) => {
					const item = payload[nama] || {};
					const dates = Array.isArray(item.tanggal) ? item.tanggal.slice() : [];
					const sem = item.semester ?? '-';
					const per = item.periode ?? '-';
					const mapel = item.mapel ?? '-';
					const kelas = (item.nama_kelas ?? '-') + (item.kode_kelas ? ` (${item.kode_kelas})` : '');

					if (!dates.length) return;

					dates.sort((a, b) => {
						const pa = a.split('-'), pb = b.split('-');
						const da = new Date(pa[2] + '-' + pa[1] + '-' + pa[0]);
						const db = new Date(pb[2] + '-' + pb[1] + '-' + pb[0]);
						return da - db;
					});

					const rowspan = dates.length;

					dates.forEach((tgl, idx) => {
						html += '<tr>';
						if (idx === 0) {
							html += `<td rowspan="${rowspan}">${no++}</td>`;
							html += `<td rowspan="${rowspan}">${esc(nama)}</td>`;
						}

						html += `<td>${esc(kelas)}</td>`;
						html += `<td>${esc(mapel)}</td>`;
						html += `<td>${esc(tgl)}</td>`;
						if (idx === 0) {
							html += `<td rowspan="${rowspan}">${esc(sem)}</td>`;
							html += `<td rowspan="${rowspan}">${esc(per)}</td>`;
						}
						html += '</tr>';
					});
				});

				if (!html) {
					html = '<tr><td colspan="7" class="text-center">Semua guru sudah mengisi pada rentang ini.</td></tr>';
				}

				$tbody.html(html);
			},
			error: function () {
				$tbody.html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
			},
		});
	}

	function data_presensi_pegawai() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var tampil = $('select[name="pegawai_all_absen"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			tampil,
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_presensi_pegawai'); ?>',
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
							$('#tampil_pegawai').val(item.tampil_pegawai);
							table += '<tr>';

							if (!guruRowspanAdded) {
								table += `<td rowspan="${totalBaris}">${no++}</td>`;
								table += `<td rowspan="${totalBaris}">${tanggal}</td>`;
								guruRowspanAdded = true;
							}
							if (item.status == '1') {
								table += `<td>${item.nama_pegawai}</td>`;
								// table += `<td>${item.jabatan}</td>`;
								table += `<td>${item.jam_masuk}</td>`;
								table += `<td>${item.jam_terlambat}</td>`;
								table += `<td>${item.jam_pulang?.trim() || '-'}</td>`;
								table += `<td>${item.status_absen}</td>`;
							} else {
								table += `<td>${item.nama_pegawai}</td>`;
								table += `<td></td>`; // Jam Masuk
								table += `<td class="text-center">Tidak ada data</td>`;
								table += `<td></td>`; // Menit Terlambat
								table += `<td></td>`; // Jam Pulang
							}
							// table += `<td>${item.nama_pegawai}</td>`;
							// table += `<td>${item.jabatan}</td>`;
							// table += `<td>${item.jam_masuk}</td>`;
							// table += `<td>${item.jam_pulang?.trim() || '-'}</td>`;
							// table += `<td>${item.jam_terlambat}</td>`;
							// table += `<td>${item.status_absen}</td>`;
							table += '</tr>';
						});
					});
				}

				$('#data_presensi_pegawai tbody').html(table);

			}
		});
	}
	function data_presensi_keterlambatan_pegawai() {
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
			url: '<?= base_url('admin/laporan/laporan_rekap_keterlambatan_pegawai'); ?>',
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
							table += `<td>${item.jabatan}</td>`;
							table += `<td>${item.waktu}</td>`;
							table += `<td>${item.jam_terlambat}</td>`;
							table += `<td>${item.status}</td>`;
							table += '</tr>';
						});
					});
				}

				$('#data_presensi_keterlambatan_pegawai tbody').html(table);

			}
		});
	}

	function data_presensi_per_pegawai() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var id_pegawai = $('select[name="id_pegawai_all"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			id_pegawai,
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_presensi_per_pegawai'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				var no = 1;
				var table = '';
				const presensi = data.data;
				const nama_jabatan = data.meta;
				$('#nama_per_pegawai').val(nama_jabatan.nama_pegawai);
				$('#pegawai_per_jabatan').val(nama_jabatan.jabatan);

				if (!presensi || Object.keys(presensi).length === 0) {
					table = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
				} else {

					Object.keys(presensi).forEach(function (tanggal) {
						const item = presensi[tanggal];

						table += '<tr>';
						table += `<td>${no++}</td>`; // nomor urut
						table += `<td>${tanggal}</td>`; // tanggal

						if (item.status === "1") {
							// Ada presensi
							table += `<td>${nama_jabatan.nama_pegawai}</td>`;
							table += `<td>${nama_jabatan.jabatan}</td>`; // jabatan dari nama_jabatan
							table += `<td>${item.jam_masuk}</td>`;
							table += `<td>${item.jam_terlambat}</td>`;
							table += `<td>${item.jam_pulang?.trim() || '-'}</td>`;
							table += `<td>${item.status_absen}</td>`;
						} else {
							// Tidak ada presensi
							table += `<td>${nama_jabatan.nama_pegawai}</td>`;
							table += `<td>${nama_jabatan.jabatan}</td>`;
							table += `<td></td>`; // Jam Masuk
							table += `<td  class="text-center">Tidak ada data</td>`;
							table += `<td></td>`; // Menit Terlambat
							table += `<td></td>`; // Jam Pulang
						}

						table += '</tr>';
					});
				}

				$('#data_presensi_per_pegawai tbody').html(table);

			}
		});
	}
	let masterPotonganGlobal = [];
	let rekap_persenUigUik = 0;
	let rekap_persenZakat = 0;
	function formatPersen(value) {
		value = parseFloat(value) || 0;

		if (value % 1 === 0) {
			return value.toString();
		}

		return value.toString().replace('.', ',');
	}
	function data_rekapitulasi_gaji() {
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
			url: '<?= base_url('admin/laporan/laporan_rekapitulasi_gaji'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				var no = 1;
				var table = '';

				var laporan = data.data_laporan;
				var masterPotongan = data.master_potongan;
				masterPotonganGlobal = masterPotongan; // Simpan di variabel global

				rekap_persenUigUik = 0;
				rekap_persenZakat = 0;
				if (laporan.length > 0) {
					rekap_persenUigUik = laporan[0].persen_uig_uik || 0;
					rekap_persenZakat = laporan[0].persen_zakat || 0;
				}

				let header = `
	<td>No</td>
	<td>Nama Peserta</td>
	<td>Gaji Pokok</td>
	<td>Struktural</td>
	<td>Bonus</td>
	<td>Jumlah Pendapatan</td>
	<td>Potongan Tidak Masuk </td>
	<td>UIG/UIK ${formatPersen(rekap_persenUigUik)}%</td>
	<td>Zakat ${formatPersen(rekap_persenZakat)}%</td>
`;

				masterPotongan.forEach(mp => {
					header += `<td>${mp.nama_potongan}</td>`;
				});

				header += `
	<td>Potongan Pinjaman</td>
	<td>Jumlah Pengeluaran</td>
	<td>Sisa</td>
`;

				$('#header_rekapitulasi_gaji').html(header);
				if (laporan.length === 0) {
					table = '<tr><td colspan="10" class="text-center">Tidak ada data</td></tr> ';
				} else {
					laporan.forEach(items => {
						let hitung_tunjangan = parseFloat(items.struktural) + parseFloat(items.tunjangan_pendidikan) + parseFloat(items.wali_kelas);
						table += `<tr>`;
						table += `<td>${no++}</td>`;
						table += `<td>${items.nama_pegawai}</td>`;
						table += `<td style="text-align: right;">${items.gaji_pokok}</td>`;
						table += `<td style="text-align: right;">${hitung_tunjangan}</td>`;
						table += `<td style="text-align: right;">${items.bonus}</td>`;
						table += `<td style="text-align: right;">${items.total_pendapatan}</td>`;
						table += `<td style="text-align: right;">${items.potongan_tidak_hadir}</td>`;
						table += `<td style="text-align: right;">${items.uig_uik}</td>`;
						table += `<td style="text-align: right;">${items.zakat}</td>`;
						masterPotongan.forEach(mp => {

							let nominal = 0;

							if (items.potongan &&
								items.potongan[mp.id] !== undefined) {
								nominal = items.potongan[mp.id];
							}

							table += `<td style="text-align:right;">${nominal}</td>`;
						});
						table += `<td style="text-align: right;">${items.cicilan_pinjaman}</td>`;
						table += `<td style="text-align: right;">${items.total_pengeluaran}</td>`;
						table += `<td style="text-align: right;">${items.gaji_bersih}</td>`;
						table += `</tr>`;
					});
				}
				$('#data_rekapitulasi_gaji tbody').html(table);
			}
		});
	}

	let tanggal_laporan_kas = '';
	function data_laporan_kas() {
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
			url: '<?= base_url('admin/laporan/laporan_kas'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				tanggal_laporan_kas = data.tanggal_laporan;
				if (data.status == 'Tahun') {
					$('#data_laporan_kas').hide();
					$('#table_tahunan').show();
					const bulan = [
						'01', '02', '03', '04', '05', '06',
						'07', '08', '09', '10', '11', '12'
					];
					let thead = `
	<tr>
		<th>Tahun Mulai</th>
		<th>Bulan Aktif</th>
		<th>JAN</th>
		<th>FEB</th>
		<th>MAR</th>
		<th>APR</th>
		<th>MEI</th>
		<th>JUN</th>
		<th>JUL</th>
		<th>AGU</th>
		<th>SEP</th>
		<th>OKT</th>
		<th>NOV</th>
		<th>DES</th>
	</tr>
	`;

					$('#table_tahunan thead').html(thead);

					let tbody = '';
					tbody += `<tr>
					<td>01/01/${$('select[name="single_filter_tahun"]').val()}</td>
					<td>Saldo Awal</td>`;

					// bulan.forEach(b => {
					// 	tbody += `<td>${Number(data.saldo_awal).toLocaleString()}</td>`;
					// });

					bulan.forEach(() => {
						tbody += `<td></td>`;
					});

					tbody += '</tr>';
					tbody += `<tr><td>Saldo Kas</td>
					<td>${Number(data.saldo_awal).toLocaleString()}</td>`;

					bulan.forEach(b => {
						tbody += `<td>${Number(data.saldo_bulanan[b] || 0).toLocaleString()}</td>`;
					});

					tbody += '</tr>';
					tbody += `<tr><td colspan="14"><b>PENERIMAAN KAS</b></td></tr>`;
					data.pemasukan_tahun.forEach(row => {
						tbody += `<tr>`;
						tbody += `<td colspan="2">${row.keterangan}</td>`;
						bulan.forEach(b => {
							tbody += `<td>${Number(row.bulan[b] || 0).toLocaleString()}</td>`;
						});
						tbody += `</tr>`;
					});
					tbody += `<tr><td colspan="2"><b>Total Penerimaan</b></td>`;
					bulan.forEach(b => {
						tbody += `<td><b>${Number(data.total_pemasukan_bulan[b] || 0).toLocaleString()}</b></td>`;
					});

					tbody += '</tr>';
					tbody += `<tr style="background:#cfe2f3;font-weight:bold;"><td colspan="2">Total Kas Tersedia</td>`;

					bulan.forEach(b => {
						tbody += `
						<td>
							${Number(data.total_kas_tersedia[b] || 0).toLocaleString()}
						</td>`;
					});

					tbody += `</tr>`;
					tbody += `<tr></tr>`;
					tbody += `<tr><td colspan="14"><b>PENGELUARAN KAS</b></td></tr>`;
					data.pengeluaran_tahun.forEach(row => {
						tbody += `<tr>`;
						tbody += `
							<td colspan="2">${row.keterangan}</td>`;

						bulan.forEach(b => {
							tbody += `
							<td>
								${Number(row.bulan[b] || 0).toLocaleString()}
							</td>`;
						});
						tbody += `</tr>`;
					});
					tbody += `
						<tr style="background:#f4cccc;font-weight:bold;"><td colspan="2">Total Pengeluaran</td>`;

					bulan.forEach(b => {
						tbody += `
						<td>
							${Number(data.total_pengeluaran_bulan[b] || 0).toLocaleString()}
						</td>`;
					});
					tbody += `</tr>`;
					tbody += `
					<tr style="background:#d9ead3;font-weight:bold;"><td colspan="2">Saldo Akhir Bulan</td>`;

					bulan.forEach(b => {
						tbody += `<td>${Number(data.saldo_akhir_bulan[b] || 0).toLocaleString()}</td>`;
					});

					tbody += `</tr>`;
					$('#table_tahunan tbody').html(tbody);
				} else {
					$('#table_tahunan').hide();
					$('#data_laporan_kas').show();
					let max = Math.max(
						data.pemasukan.length,
						data.pengeluaran.length
					);

					// saldo awal
					table += `
						  <tr>
							  <td><b>Saldo bulan lalu</b></td>
							  <td>
								  <b>${data.saldo_bulan_lalu}</b>
							  </td>
							  <td>${data.pengeluaran[0]?.keterangan || ''}</td>
							  <td>${data.pengeluaran[0]?.total || ''}</td>
						  </tr>`;


					for (let i = 0; i < max; i++) {
						let pemasukanNama = data.pemasukan[i]?.keterangan || '';
						let pemasukanTotal = Number(data.pemasukan[i]?.total) || '';

						let pengeluaranNama = data.pengeluaran[i + 1]?.keterangan || '';
						let pengeluaranTotal = Number(data.pengeluaran[i + 1]?.total) || '';

						table += `
						  <tr>
							  <td>${pemasukanNama}</td>
							  <td>
							  ${pemasukanTotal ? pemasukanTotal : ''}
							  </td>
							  <td>${pengeluaranNama}</td>
							  <td>
							  ${pengeluaranTotal ? pengeluaranTotal : ''}
							  </td>
						  </tr>`;
					}

					table += `
					  <tr>
						  <td colspan="3" style="text-align:right"><b>Saldo bulan ini</b></td>
						  <td>
							  <b>${data.saldo_bulan_ini}</b>
						  </td>
					  </tr>
					  <tr>
						  <td style="text-align:center">
							  <b>Jumlah</b>
						  </td>
						  <td>
							  <b>${Number(data.total_pemasukan) + Number(data.saldo_bulan_lalu)}</b>
						  </td>
						  <td style="text-align:center">
							  <b>Jumlah</b>
						  </td>
						  <td>
							  <b>${data.total_pengeluaran + data.saldo_bulan_ini}</b>
						  </td>
					  </tr>`;
					$('#data_laporan_kas tbody').html(table);
				}
			}
		});
	}
	let tanggal_laporan_penggunaan_anggaran = '';
	function data_laporan_penggunaan_anggaran() {
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
			url: '<?= base_url('admin/laporan/laporan_penggunaan_anggaran'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				tanggal_laporan_penggunaan_anggaran = data.tanggal_laporan;
				if (data.data_laporan.length > 0) {
					data.data_laporan.forEach((item, index) => {
						table += `<tr>`;
						// kolom kiri
						if (index == 0) {
							table += `
					<td><b>Pengajuan Anggaran</b></td>
					<td style="text-align:right">
						<b>${data.total_pengajuan}</b>
					</td>`;
						} else {
							table += `
								<td></td>
								<td></td>
							`;
						}

						table += `
				<td>${item.kode_akun}</td>
				<td style="text-align:right">
				${parseInt(item.realisasi) > 0 ? parseInt(item.realisasi) : ''}
				</td>`;
						table += `</tr>`;
					});

					// saldo
					table += `
					<tr>
						<td colspan="3" style="text-align:right">
							<b>Saldo bulan ini</b>
						</td>
						<td style="text-align:right">
							<b>${parseInt(data.saldo_bulan_ini)}</b>
						</td>
					</tr>
					`;

					let totalAkhir = parseInt(data.total_realisasi) + parseInt(data.saldo_bulan_ini);
					table += `
					<tr>
						<td style="text-align:center">
							<b>Jumlah</b>
						</td>
						<td style="text-align:right">
							<b>${parseInt(data.total_pengajuan)}</b>
						</td>
						<td style="text-align:center">
							<b>Jumlah</b>
						</td>
						<td style="text-align:right">
							<b>${parseInt(totalAkhir)}</b>
						</td>
					</tr>`;
				}
				$('#data_laporan_penggunaan_anggaran tbody').html(table);
			}
		});
	}
	let tanggal_laporan_rencana_anggaran = '';
	function data_laporan_rencana_anggaran() {
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
			url: '<?= base_url('admin/laporan/laporan_rencana_anggaran'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				tanggal_laporan_rencana_anggaran = data.tanggal_laporan;
				if (data.pengeluaran.length > 0) {

					data.pengeluaran.forEach((item, index) => {

						table += `<tr>`;

						if (index == 0) {

							table += `
						<td><b>Saldo bulan lalu</b></td>

						<td style="text-align:right">
							<b>
							${parseInt(data.saldo_bulan_lalu || 0)}
							</b>
						</td>`;
						}

						else if (index == 1) {

							table += `
						<td><b>Pengajuan Anggaran</b></td>

						<td style="text-align:right">
							<b>
							${parseInt(data.pengajuan_anggaran || 0)}
							</b>
						</td>`;
						}

						else {

							table += `
						<td></td>
						<td></td>`;
						}

						table += `
					<td>${item.keterangan}</td>

					<td style="text-align:right">
						${parseInt(item.total) > 0
								? parseInt(item.total)
								: ''}
					</td>`;

						table += `</tr>`;
					});

					let totalJumlah = parseInt(data.saldo_bulan_lalu || 0) + parseInt(data.pengajuan_anggaran || 0);
					table += `
				<tr>
					<td align="center">
						<b>Jumlah</b>
					</td>

					<td align="right">
						<b>
						${parseInt(totalJumlah)}
						</b>
					</td>

					<td align="center">
						<b>Jumlah</b>
					</td>

					<td align="right">
						<b>
						${parseInt(data.total_rencana || 0)}
						</b>
					</td>
				</tr>`;
				}
				$('#data_laporan_rencana_anggaran tbody').html(table);
			}
		});
	}
	let tanggal_laporan_pos = '';
	function data_laporan_pos() {
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
			url: '<?= base_url('admin/laporan/laporan_pos'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				tanggal_laporan_pos = data.tanggal_laporan;
				if (data.data_pos.length == 0) {
					table = `
				<tr>
					<td colspan="5" align="center">
						Tidak ada data
					</td>
				</tr>
				`;
				} else {
					let totalSaldoLalu = 0;
					let totalMasuk = 0;
					let totalKeluar = 0;
					let totalSaldo = 0;
					data.data_pos.forEach(item => {
						totalSaldoLalu += parseInt(item.saldo_lalu) || 0;
						totalMasuk += parseInt(item.masuk) || 0;
						totalKeluar += parseInt(item.keluar) || 0;
						totalSaldo += parseInt(item.saldo) || 0;
						table += `
					<tr>
						<td>${item.uraian}</td>
						<td align="right">
							${item.saldo_lalu}
						</td>
						<td align="right">
							${item.masuk}
						</td>
						<td align="right">
							${item.keluar}
						</td>
						<td align="right">
							${item.saldo}
						</td>
					</tr>`;
					});
					table += `
					<tr>
						<td align="center">
							<b>Jumlah</b>
						</td>
						<td align="right">
							<b>${totalSaldoLalu}</b>
						</td>
						<td align="right">
							<b>${totalMasuk}</b>
						</td>
						<td align="right">
							<b>${totalKeluar}</b>
						</td>
						<td align="right">
							<b>${totalSaldo}</b>
						</td>
					</tr>`;
				}
				$('#data_laporan_pos tbody').html(table);
			}
		});
	}

	function getNamaBulanString(bulan) {
		const bulanArr = {
			'01': 'Januari',
			'02': 'Februari',
			'03': 'Maret',
			'04': 'April',
			'05': 'Mei',
			'06': 'Juni',
			'07': 'Juli',
			'08': 'Agustus',
			'09': 'September',
			'10': 'Oktober',
			'11': 'November',
			'12': 'Desember'
		};

		return bulanArr[bulan] || bulan;
	}
	let tahun_ajaran_asumsi = '';
	function data_laporan_rencana_asumsi_pemasukan() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();

		var semester = $('#form-semester-tahun-ajaran select[name="semester"]').val();
		var id_periode = $('#form-semester-tahun-ajaran select[name="id_periode"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			semester,
			id_periode
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_rencana_asumsi_pemasukan'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				tahun_ajaran_asumsi = data.tahun_ajaran || '';
				let header = '';
				header += `
				<tr>
					<th rowspan="2">Kategori</th>
					<th rowspan="2">Asumsi Pemasukan Total</th>
					<th rowspan="2">% Masuk</th>
					<th rowspan="2">Asumsi Masuk</th>
					<th rowspan="2">Saving</th>
					<th rowspan="2">% Saving</th>
					<th colspan="${data.list_bulan.length}">
						Distribusi Bulanan
					</th>
				</tr>
				<tr>`;

				data.list_bulan.forEach(b => {
					header += `<th>${getNamaBulanString(b)}</th>`;
				});

				header += `</tr>`;

				$('#data_laporan_rencana_asumsi_pemasukan thead').html(header);

				let table = '';

				let total_semua = 0;
				let total_asumsi = 0;
				let total_saving = 0;

				let total_per_bulan = {};

				data.list_bulan.forEach(b => {
					total_per_bulan[b] = 0;
				});

				data.rencana_pemasukan.forEach(r => {
					let total = parseFloat(r.total_asumsi_masuk) || 0;
					let persenMasuk = parseFloat(r.persen_masuk) || 0;
					let asumsi = parseFloat(r.asumsi_masuk) || 0;
					let savingNormal = parseFloat(r.saving_normal) || 0;
					let savingPersen = parseFloat(r.saving_persen) || 0;

					total_semua += total;
					total_asumsi += asumsi;
					total_saving += savingNormal;

					table += `
					<tr>
						<td>${r.kategori}</td>

						<td align="right">
							${Math.round(total).toLocaleString('id-ID')}
						</td>

						<td align="center">
							${persenMasuk.toFixed(0)}%
						</td>

						<td align="right">
							${Math.round(asumsi).toLocaleString('id-ID')}
						</td>

						<td align="right">
							${Math.round(savingNormal).toLocaleString('id-ID')}
						</td>

						<td align="center">
							${savingPersen.toFixed(2).replace('.', ',')}
						</td>`;

					data.list_bulan.forEach(b => {
						let nilaiBulan = 0;

						if (r.bulan && r.bulan[b]) {
							nilaiBulan = parseFloat(r.bulan[b]) || 0;
						}

						total_per_bulan[b] += nilaiBulan;

						table += `
						<td align="right">
							${Math.round(nilaiBulan).toLocaleString('id-ID')}
						</td>`;
					});

					table += `</tr>`;
				});

				table += `
				<tr>
					<td><b>Jumlah</b></td>

					<td align="right">
						<b>${Math.round(total_semua).toLocaleString('id-ID')}</b>
					</td>

					<td align="center">
						<b>-</b>
					</td>

					<td align="right">
						<b>${Math.round(total_asumsi).toLocaleString('id-ID')}</b>
					</td>

					<td align="right">
						<b>${Math.round(total_saving).toLocaleString('id-ID')}</b>
					</td>

					<td align="center">
						<b>-</b>
					</td>`;

				data.list_bulan.forEach(b => {
					table += `
					<td align="right">
						<b>${Math.round(total_per_bulan[b]).toLocaleString('id-ID')}</b>
					</td>`;
				});

				table += `</tr>`;

				$('#data_laporan_rencana_asumsi_pemasukan tbody').html(table);
			}
		});
	}

	let tahun_ajaran_rencana_pengeluaran = '';
	function data_laporan_rencana_pengeluaran() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var semester = $('#form-semester-tahun-ajaran select[name="semester"]').val();
		var id_periode = $('#form-semester-tahun-ajaran select[name="id_periode"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			semester,
			id_periode,
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_rencana_pengeluaran'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				tahun_ajaran_rencana_pengeluaran = data.tahun_ajaran || '';
				let table = '';
				let bulanNama = {
					'01': 'Januari',
					'02': 'Februari',
					'03': 'Maret',
					'04': 'April',
					'05': 'Mei',
					'06': 'Juni',
					'07': 'Juli',
					'08': 'Agustus',
					'09': 'September',
					'10': 'Oktober',
					'11': 'November',
					'12': 'Desember'
				};

				// HEADER
				let thead = `<tr><th>Kategori</th>`;

				data.list_bulan.forEach(function (bulan) {
					thead += `<th>${bulanNama[bulan]}</th>`;
				});
				thead += `<th>Total</th>`;
				thead += `</tr>`;

				$('#data_laporan_rencana_pengeluaran thead').html(thead);

				// BODY
				let tbody = '';

				let total_bulan = {};

				data.list_bulan.forEach(function (bulan) {
					total_bulan[bulan] = 0;
				});

				if (data.data_laporan.length == 0) {

					tbody = `
			<tr>
				<td colspan="${data.list_bulan.length + 2}" align="center">
					Tidak ada data
				</td>
			</tr>`;

				} else {
					data.data_laporan.forEach(function (r) {
						tbody += `<tr><td>${r.keterangan}</td>`;

						let total_baris = 0;
						data.list_bulan.forEach(function (bulan) {
							let nilai = parseFloat(r['bulan_' + bulan]) || 0;

							total_bulan[bulan] += nilai;
							total_baris += nilai;

							tbody += `
			<td align="right">
				${nilai > 0 ? nilai.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
			</td>`;
						});

						tbody += `
		<td align="right">
			<b>
				${total_baris > 0 ? total_baris.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
			</b>
		</td>`;

						tbody += `</tr>`;
					});

					// TOTAL
					let grand_total = 0;
					tbody += `<tr><td align="center"><b>Jumlah</b></td>`;
					data.list_bulan.forEach(function (bulan) {
						grand_total += total_bulan[bulan];
						tbody += `
		<td align="right">
			<b>
				${total_bulan[bulan] > 0 ? total_bulan[bulan].toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
			</b>
		</td>`;
					});
					tbody += `
	<td align="right">
		<b>
			${grand_total > 0 ? grand_total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
		</b>
	</td>`;
					tbody += `</tr>`;
				}
				$('#data_laporan_rencana_pengeluaran tbody').html(tbody);
			}
		});
	}

	let tahun_ajaran = '';
	function data_laporan_rencana_pemasukan() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var semester = $('#form-semester-tahun-ajaran select[name="semester"]').val();
		var id_periode = $('#form-semester-tahun-ajaran select[name="id_periode"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			semester,
			id_periode,
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_rencana_pemasukan'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				let total_bulan = 0;
				let no = 1;
				if (data.data_laporan.length == 0) {
					table = '<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>';
				} else {
					tahun_ajaran = data.tahun_ajaran || '';
					data.data_laporan.forEach(r => {
						// HEADER JENIS
						table += `
			<tr class="header-jenis">
				<td>${no++}</td>
				<td><b>${r.nama_jenis}</b></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>`;
						let total_nilai_satuan = 0;
						// DETAIL
						r.detail.forEach(d => {
							total_nilai_satuan += parseFloat(d.nilai_satuan || 0);
							table += `
				<tr>
					<td></td>
					<td>${d.nama_kategori}</td>
					<td align="center">
						${d.satuan || ''}
					</td>
					<td align="center">
						${d.volume}
					</td>
					<td align="right">
						${NumberToMoney(d.nilai_satuan)}
					</td>
					<td align="right">
						${NumberToMoney(d.jumlah)}
					</td>
					<td align="center">
						${d.satuan_penerimaan || ''}
					</td>
					<td align="center">
						${d.volume_penerimaan}
					</td>
					<td align="right">
						${NumberToMoney(d.total)}
					</td>
				</tr>`;
						});

						// SUBTOTAL
						table += `
			<tr class="subtotal">
					<td></td>
					<td><b>Jumlah</b></td>
					<td></td>
					<td></td>

					<td align="right">${NumberToMoney(total_nilai_satuan)}</td>
					<td align="right"><b>${NumberToMoney(r.subtotal_jumlah)}</b></td>
					<td></td>
					<td></td>
					<td align="right"><b>${NumberToMoney(r.subtotal_total)}</b></td>
					</tr>`;
					});
					// GRAND TOTAL
					table += `
		<tr class="grand-total">
		<td></td>
			<td colspan="8" align="right">
				<b>JUMLAH</b>
			</td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
			<td align="right">
				<b>
					${NumberToMoney(data.grand_total)}
				</b>
			</td>
		</tr>`;
				}
				$('#data_laporan_rencana_pemasukan tbody').html(table);
			}
		});
	}

	function data_laporan_olah_pos() {
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
			url: '<?= base_url('admin/laporan/laporan_olah_pos'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';

				if (data.data_laporan.length == 0) {

					table = `
					<tr>
						<td colspan="37" class="text-center">
							Tidak ada data
						</td>
					</tr>`;
				} else {
					data.data_laporan.forEach((item) => {

						table += `
						<tr>
							<td>
								${item.kode_akun}
							</td>`;
						for (let b = 1; b <= 12; b++) {
							let bulan = item.bulan[b];
							let masuk = bulan.masuk != 0 ? parseInt(bulan.masuk).toLocaleString('id-ID') : '';
							let keluar = bulan.keluar != 0 ? parseInt(bulan.keluar).toLocaleString('id-ID') : '';
							let saldo = bulan.saldo != 0 ? parseInt(bulan.saldo).toLocaleString('id-ID') : '';
							table += `
							<td class="text-end">${masuk}</td>
							<td class="text-end">${keluar}</td>
							<td class="text-end">${saldo}</td>`;
						}
						table += `</tr>`;
					});
				}
				$('#data_laporan_olah_pos tbody').html(table);
			}
		});
	}
	function data_laporan_olah_in() {
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
			url: '<?= base_url('admin/laporan/laporan_olah_in'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				if (data.data_laporan.length == 0) {

					table = `
			<tr>
				<td colspan="13" class="text-center">
					Tidak ada data
				</td>
			</tr>
		`;
				} else {

					let totalBulanan = {
						1: 0,
						2: 0,
						3: 0,
						4: 0,
						5: 0,
						6: 0,
						7: 0,
						8: 0,
						9: 0,
						10: 0,
						11: 0,
						12: 0
					};

					data.data_laporan.forEach(item => {
						table += `<tr>`;
						table += `<td>${item.kode_akun}</td>`;
						for (let i = 1; i <= 12; i++) {
							let nominal = parseInt(item.bulan[String(i)] || 0);
							totalBulanan[i] += nominal;
							table += `
				<td data-nominal="${nominal}">
					${nominal.toLocaleString('id-ID')}
				</td>`;
						}

						table += `</tr>`;
					});

					// TOTAL
					table += `
		<tr style="font-weight:bold; background:#f1f1f1; text-align:center;">
			<td>Jumlah</td>`;

					for (let i = 1; i <= 12; i++) {

						table += `
			<td data-nominal="${totalBulanan[i]}">
				${totalBulanan[i].toLocaleString('id-ID')}
			</td>
		`;
					}

					table += `</tr>`;
				}
				$('#data_laporan_olah_in tbody').html(table);
			}
		});
	}
	function data_laporan_olah_out() {
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
			url: '<?= base_url('admin/laporan/laporan_olah_out'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				let table = '';
				if (data.data_laporan.length == 0) {

					table = `
			<tr>
				<td colspan="13" class="text-center">
					Tidak ada data
				</td>
			</tr>
		`;
				} else {
					let totalBulanan = {
						1: 0,
						2: 0,
						3: 0,
						4: 0,
						5: 0,
						6: 0,
						7: 0,
						8: 0,
						9: 0,
						10: 0,
						11: 0,
						12: 0
					};

					data.data_laporan.forEach(item => {
						table += `<tr>`;
						table += `<td>${item.kode_akun}</td>`;
						for (let i = 1; i <= 12; i++) {
							let nominal = parseInt(item.bulan[String(i)] || 0);
							totalBulanan[i] += nominal;
							table += `
				<td data-nominal="${nominal}">
					${nominal.toLocaleString('id-ID')}
				</td>`;
						}
						table += `</tr>`;
					});

					// TOTAL
					table += `
		<tr style="font-weight:bold; background:#f1f1f1; text-align:center;">
			<td>Jumlah</td>
	`;

					for (let i = 1; i <= 12; i++) {

						table += `
			<td data-nominal="${totalBulanan[i]}">
				${totalBulanan[i].toLocaleString('id-ID')}
			</td>
		`;
					}

					table += `</tr>`;
				}
				$('#data_laporan_olah_out tbody').html(table);
			}
		});
	}
	let tahun_ajaran_perbandingan_rab_pengeluaran = '';
	function data_laporan_perbandingan_rencana_pengeluaran() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var id_periode = $('#form-semester-tahun-ajaran select[name="id_periode"]').val();
		var semester = $('#form-semester-tahun-ajaran select[name="semester"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			id_periode,
			semester
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_perbandingan_rencana_pengeluaran'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				tahun_ajaran_perbandingan_rab_pengeluaran = data.tahun_ajaran || '';
				const namaBulan = {
					1: 'Januari',
					2: 'Februari',
					3: 'Maret',
					4: 'April',
					5: 'Mei',
					6: 'Juni',
					7: 'Juli',
					8: 'Agustus',
					9: 'September',
					10: 'Oktober',
					11: 'November',
					12: 'Desember'
				};
				let thead = `<tr><th rowspan="2">Kategori</th>`;
				let bulanLaporan = data.bulan_laporan || [];
				bulanLaporan.forEach((b) => {
					thead += `
						<th colspan="4">
							${namaBulan[b]}
						</th>`;
				});
				thead += `</tr><tr>`;
				bulanLaporan.forEach((b) => {
					thead += `
		<th>Rencana</th>
		<th>Realisasi</th>
		<th>Selisih</th>
		<th>Status</th>`;
				});
				thead += `</tr>`;
				$('#data_laporan_perbandingan_rencana_pengeluaran thead').html(thead);

				let totalRencana = {};
				let totalRealisasi = {};
				let totalSelisih = {};
				bulanLaporan.forEach((b) => {
					totalRencana[b] = 0;
					totalRealisasi[b] = 0;
					totalSelisih[b] = 0;
				});
				let table = '';

				if (!data.data_laporan || data.data_laporan.length == 0) {

					table = `
		<tr>
			<td colspan="${1 + (bulanLaporan.length * 4)}" class="text-center">
				Tidak ada data
			</td>
		</tr>`;

				} else {
					data.data_laporan.forEach((item) => {
						table += `
		<tr>
			<td>${item.kode_akun}</td>`;
						bulanLaporan.forEach((b) => {
							let bulan = item.bulan[b] || {
								rencana: 0,
								realisasi: 0
							};

							let rencana = parseInt(bulan.rencana) || 0;
							let realisasi = parseInt(bulan.realisasi) || 0;
							let selisih = rencana - realisasi;

							totalRencana[b] += rencana;
							totalRealisasi[b] += realisasi;
							totalSelisih[b] += selisih;

							let status = '';

							if (rencana != 0 || realisasi != 0) {
								if (selisih == 0) {
									status = 'Aman';
								} else if (selisih > 0) {
									status = 'Under';
								} else {
									status = 'Over Budget';
								}
							}

							table += `
				<td class="text-end">
					${rencana ? rencana.toLocaleString('id-ID') : ''}
				</td>
				<td class="text-end">
					${realisasi ? realisasi.toLocaleString('id-ID') : ''}
				</td>
				<td class="text-end">
					${selisih ? selisih.toLocaleString('id-ID') : ''}
				</td>
				<td>
					${status}
				</td>`;
						});
						table += `</tr>`;
					});

					// TOTAL
					table += `
	<tr class="fw-bold bg-light">
		<td>JUMLAH</td>`;

					bulanLaporan.forEach((b) => {
						table += `
			<td class="text-end">
				${totalRencana[b].toLocaleString('id-ID')}
			</td>
			<td class="text-end">
				${totalRealisasi[b].toLocaleString('id-ID')}
			</td>
			<td class="text-end">
				${totalSelisih[b].toLocaleString('id-ID')}
			</td>

			<td></td>`;
					});
					table += `</tr>`;
				}
				$('#data_laporan_perbandingan_rencana_pengeluaran tbody').html(table);
			}
		});
	}

	let tahun_ajaran_perbandingan_rab_pemasukan = '';
	function data_laporan_perbandingan_rencana_pemasukan() {
		var filter = $('input[name="filter"]:checked').val();
		var dari_tanggal = $('input[name="dari_tanggal"]').val();
		var sampai_tanggal = $('input[name="sampai_tanggal"]').val();
		var bulan = $('select[name="filter_bulan"]').val();
		var tahun = $('select[name="filter_tahun"]').val();

		var semester = $('#form-semester-tahun-ajaran select[name="semester"]').val();
		var id_periode = $('#form-semester-tahun-ajaran select[name="id_periode"]').val();
		var single_filter_tahun = $('select[name="single_filter_tahun"]').val();
		var dataPost = {
			dari_tanggal,
			sampai_tanggal,
			bulan,
			tahun,
			single_filter_tahun,
			filter,
			semester,
			id_periode
		};

		$.ajax({
			url: '<?= base_url('admin/laporan/laporan_perbandingan_rencana_pemasukan'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				const namaBulan = {
					1: 'Januari',
					2: 'Februari',
					3: 'Maret',
					4: 'April',
					5: 'Mei',
					6: 'Juni',
					7: 'Juli',
					8: 'Agustus',
					9: 'September',
					10: 'Oktober',
					11: 'November',
					12: 'Desember'
				};

				let header1 = `
				<tr>
					<th rowspan="2">Kategori</th>
					<th rowspan="2">Asumsi Masuk</th>`;
				data.bulan_laporan.forEach(b => {
					header1 += `
					<th colspan="4">${namaBulan[b]}</th>
				`;
				});

				header1 += '</tr>';
				let header2 = '<tr>';
				data.bulan_laporan.forEach(() => {
					header2 += `
					<th>Rencana</th>
					<th>Realisasi</th>
					<th>Selisih</th>
					<th>Status</th>`;
				});

				header2 += '</tr>';
				$('#data_laporan_perbandingan_rencana_pemasukan thead').html(header1 + header2);

				let table = '';
				let total_asumsi_masuk = 0;
				let totalRencana = {};
				let totalRealisasi = {};
				let totalSelisih = {};
				data.bulan_laporan.forEach(b => {
					totalRencana[b] = 0;
					totalRealisasi[b] = 0;
					totalSelisih[b] = 0;
				});

				if (data.data_laporan.length == 0) {
					let colspan = 2 + (data.bulan_laporan.length * 4);
					table = `
					<tr>
						<td colspan="${colspan}" class="text-center">
							Tidak ada data
						</td>
					</tr>`;
				} else {
					tahun_ajaran_perbandingan_rab_pemasukan = data.tahun_ajaran || '';
					data.data_laporan.forEach((item) => {
						let asumsi = parseInt(item.asumsi || 0);
						total_asumsi_masuk += asumsi;
						table += `
						<tr>
							<td>${item.kode_akun}</td>
							<td class="text-end">
								${asumsi.toLocaleString('id-ID')}
							</td>`;
						data.bulan_laporan.forEach(b => {
							let bulan = item.bulan[b] || {};
							let rencana = parseInt(bulan.rencana || 0);
							let realisasi = parseInt(bulan.realisasi || 0);
							let selisih = rencana - realisasi;
							totalRencana[b] += rencana;
							totalRealisasi[b] += realisasi;
							totalSelisih[b] += selisih;

							let status = '';
							if (rencana == 0 && realisasi == 0) {
								status = '';
							} else if (selisih == 0) {
								status = 'Aman';
							} else if (selisih > 0) {
								status = 'Under';
							} else {
								status = 'Over Budget';
							}

							table += `
							<td class="text-end">
								${rencana ? rencana.toLocaleString('id-ID') : ''}
							</td>

							<td class="text-end">
								${realisasi ? realisasi.toLocaleString('id-ID') : ''}
							</td>

							<td class="text-end">
								${selisih ? selisih.toLocaleString('id-ID') : ''}
							</td>

							<td class="text-center">
								${status}
							</td>`;
						});

						table += '</tr>';
					});

					table += `
					<tr class="fw-bold bg-light">
						<td>JUMLAH</td>

						<td class="text-end">
							${total_asumsi_masuk.toLocaleString('id-ID')}
						</td>`;

					data.bulan_laporan.forEach(b => {
						table += `
						<td class="text-end">
							${totalRencana[b].toLocaleString('id-ID')}
						</td>
						<td class="text-end">
							${totalRealisasi[b].toLocaleString('id-ID')}
						</td>
						<td class="text-end">
							${totalSelisih[b].toLocaleString('id-ID')}
						</td>
						<td></td>`;
					});

					table += '</tr>';
				}

				$('#data_laporan_perbandingan_rencana_pemasukan tbody').html(table);
			}
		});
	}
	function data_penerimaan_honorarium() {
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
			url: '<?= base_url('admin/laporan/laporan_penerimaan_honorarium'); ?>',
			type: 'POST',
			data: dataPost,
			dataType: 'JSON',
			success: function (data) {
				var laporan = data.data_laporan || [];
				var table = '';
				var no = 1;

				var total_gaji_pokok = 0;
				var total_struktural = 0;
				var total_pendidikan = 0;
				var total_wali_kelas = 0;
				var total_jumlah = 0;
				var total_jumlah_kotor = 0;
				var total_jumlah_hadir = 0;
				var total_jumlah_penerimaan = 0;

				if (laporan.length === 0) {
					table = `
					<tr>
						<td colspan="13" class="text-center">Tidak ada data</td>
					</tr>
				`;
				} else {
					laporan.forEach(function (items) {
						var gaji_pokok = parseFloat(items.gaji_pokok) || 0;
						var struktural = parseFloat(items.struktural) || 0;
						var pendidikan = parseFloat(items.tunjangan_pendidikan) || 0;
						var wali_kelas = parseFloat(items.wali_kelas) || 0;

						var jumlah = parseFloat(items.jumlah);
						if (isNaN(jumlah)) {
							jumlah = struktural + pendidikan + wali_kelas;
						}

						var jumlah_kotor = parseFloat(items.jumlah_kotor);
						if (isNaN(jumlah_kotor)) {
							jumlah_kotor = gaji_pokok + jumlah;
						}

						var jumlah_hadir = parseInt(items.jumlah_hadir) || 0;
						var jumlah_penerimaan = parseFloat(items.jumlah_penerimaan) || 0;

						total_gaji_pokok += gaji_pokok;
						total_struktural += struktural;
						total_pendidikan += pendidikan;
						total_wali_kelas += wali_kelas;
						total_jumlah += jumlah;
						total_jumlah_kotor += jumlah_kotor;
						total_jumlah_hadir += jumlah_hadir;
						total_jumlah_penerimaan += jumlah_penerimaan;

						table += `
						<tr>
							<td style="text-align:center;">${no++}</td>
							<td>${items.nama_pegawai || '-'}</td>
							<td>${items.jabatan || '-'}</td>

							<td style="text-align:center;">${items.masa_kerja || 0}</td>
							<td style="text-align:center;">${items.pendidikan_terakhir || '-'}</td>
							<td style="text-align:right;">${gaji_pokok}</td>

							<td style="text-align:right;">${struktural}</td>
							<td style="text-align:right;">${pendidikan}</td>
							<td style="text-align:right;">${wali_kelas}</td>

							<td style="text-align:right;">${jumlah}</td>
							<td style="text-align:right;">${jumlah_kotor}</td>
							<td style="text-align:center;">${jumlah_hadir}</td>
							<td style="text-align:right;">${jumlah_penerimaan}</td>
						</tr>
					`;
					});
				}

				$('#data_penerimaan_honorarium tbody').html(table);

				$('#total_gaji_pokok').text(total_gaji_pokok);
				$('#total_struktural').text(total_struktural);
				$('#total_pendidikan').text(total_pendidikan);
				$('#total_wali_kelas').text(total_wali_kelas);
				$('#total_jumlah').text(total_jumlah);
				$('#total_jumlah_kotor').text(total_jumlah_kotor);
				$('#total_jumlah_hadir').text(total_jumlah_hadir);
				$('#total_jumlah_penerimaan').text(total_jumlah_penerimaan);

				$('#info_persen_tidak_hadir').text((data.persen_tidak_hadir || 0) + '%');
			},
			error: function (xhr) {
				console.log(xhr.responseText);
				alert('Gagal mengambil data penerimaan honorarium.');
			}
		});
	}
</script>

<!-- laporan_excel -->
<script>
	function updateValExcel() {

		var val = $('select[name="laporan"]').val();


		$('#btn_print_laporan_excel').val(val);
	}
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
						{ wch: 30 },
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
		} else if (cek_btn === 'presensi_pegawai') {
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
					const tampil_pegawai = $('#tampil_pegawai').val();
					// Header laporan
					data.push(["Laporan Presensi Pegawai"]);
					data.push([`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					if (tampil_pegawai == 'tampil' || tampil_pegawai == '') {
						data.push(["Tampil: Semua Pegawai"]);
					} else {
						data.push(["Tampil: Pegawai Absen"]);
					}
					data.push([""]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Jam Masuk", "Menit Terlambat", "Jam Pulang", "Status"]);
					const tableRows = document.querySelectorAll("#data_presensi_pegawai tbody tr");

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
						const isRowMerged = row.cells.length < 7;

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
						{ wch: 30 },  // Nama Pegawai
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Presensi Pegawai");
					if (tampil_pegawai == 'tampil' || tampil_pegawai == '') {
						XLSX.writeFile(workbook, `laporan_presensi_pegawai_semua_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);
					} else {
						XLSX.writeFile(workbook, `laporan_presensi_pegawai_absen_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);
					}


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);


			} else if (filter === 'bulan') {
				setTimeout(() => {
					const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
					const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';
					const tampil_pegawai = $('#tampil_pegawai').val();
					let data = [];

					// Header laporan
					data.push(["Laporan Presensi Pegawai"]);
					data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					if (tampil_pegawai == 'tampil' || tampil_pegawai == '') {
						data.push(["Tampil: Semua Pegawai"]);
					} else {
						data.push(["Tampil: Pegawai Absen"]);
					}
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Jam Masuk", "Menit Terlambat", "Jam Pulang", "Status"]);

					const tableRows = document.querySelectorAll("#data_presensi_pegawai tbody tr");

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
						const isRowMerged = row.cells.length < 7;

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
						{ wch: 30 },  // Nama Pegawai
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
						{ wch: 30 },  // Waktu
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Presensi Pegawai");
					if (tampil_pegawai == 'tampil' || tampil_pegawai == '') {
						XLSX.writeFile(workbook, `laporan_presensi_pegawai_semua_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);
					} else {
						XLSX.writeFile(workbook, `laporan_presensi_pegawai_absen_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);
					}

					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else {
				setTimeout(() => {
					const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
					const tampil_pegawai = $('#tampil_pegawai').val();
					let data = [];

					// Header laporan
					data.push(["Laporan Presensi Pegawai"]);
					data.push([`Tahun: ${tahun}`]);
					if (tampil_pegawai == 'tampil' || tampil_pegawai == '') {
						data.push(["Tampil: Semua Pegawai"]);
					} else {
						data.push(["Tampil: Pegawai Absen"]);
					}
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Jam Masuk", "Menit Terlambat", "Jam Pulang", "Status"]);

					const tableRows = document.querySelectorAll("#data_presensi_pegawai tbody tr");

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
						const isRowMerged = row.cells.length < 7;

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
						{ wch: 30 },  // Nama Pegawai
						{ wch: 30 },  // Jam Masuk
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Status
						{ wch: 30 },  // Status
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Presensi Pegawai");
					if (tampil_pegawai == 'tampil' || tampil_pegawai == '') {
						XLSX.writeFile(workbook, `laporan_presensi_pegawai_semua_tahun_${tahun}.xlsx`);
					} else {
						XLSX.writeFile(workbook, `laporan_presensi_pegawai_absen_tahun_${tahun}.xlsx`);
					}
					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			}
		} else if (cek_btn === 'rekap_presensi_keterlambatan_pegawai') {
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
					data.push(["Laporan Rekap Keterlambatan Pegawai"]);
					data.push([`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Jabatan", "Jam Masuk", "Menit Terlambat", "Status",]);
					const tableRows = document.querySelectorAll("#data_presensi_keterlambatan_pegawai tbody tr");

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
						const isRowMerged = row.cells.length < 7;

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
						{ wch: 15 },  // Jabatan
						{ wch: 30 },  // Status
						{ wch: 30 },  //  Terlambat
						{ wch: 30 },  // Waktu
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Rekap Keterlambatan Pegawai");
					XLSX.writeFile(workbook, `laporan_rekap_keterlambatan_pegawai_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);


			} else if (filter === 'bulan') {
				setTimeout(() => {
					const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
					const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';



					let data = [];

					// Header laporan
					data.push(["Laporan Rekap Keterlambatan Pegawai"]);
					data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Jabatan", "Jam Masuk", "Menit Terlambat", "Status"]);

					const tableRows = document.querySelectorAll("#data_presensi_keterlambatan_pegawai tbody tr");

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
						const isRowMerged = row.cells.length < 7;

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
						{ wch: 15 },  // Jabatan
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Rekap Keterlambatan Pegawai");
					XLSX.writeFile(workbook, `laporan_rekap_keterlambatan_pegawai_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else {
				setTimeout(() => {
					const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
					let data = [];

					// Header laporan
					data.push(["Laporan Rekap Keterlambatan Pegawai"]);
					data.push([`Tahun: ${tahun}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Nama Pegawai", "Jabatan", "Jam Masuk", "Menit Terlambat", "Status"]);

					const tableRows = document.querySelectorAll("#data_presensi_keterlambatan_pegawai tbody tr");

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
						const isRowMerged = row.cells.length < 7;

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
						{ wch: 15 },  // Jabatan
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Rekap Keterlambatan Pegawai");
					XLSX.writeFile(workbook, `laporan_rekap_keterlambatan_pegawai_tahun_${tahun}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			}
		} else if (cek_btn === 'presensi_per_pegawai') {
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
					const firstRow = document.querySelector("#data_presensi_per_pegawai tbody tr");
					// const namaPegawai = firstRow.cells[2]?.textContent?.trim() || 'Tidak Diketahui';
					// const jabatan = firstRow.cells[3]?.textContent?.trim() || 'Tidak Diketahui';
					const namaPegawai = $('#nama_per_pegawai').val().trim() || 'Tidak Diketahui';
					const jabatan = $('#pegawai_per_jabatan').val().trim() || 'Tidak Diketahui';
					// Header laporan
					data.push(["Laporan Presensi Per Pegawai"]);
					data.push([`Nama Pegawai : ${namaPegawai}`]);
					data.push([`Jabatan      : ${jabatan}`]);
					data.push([`Tanggal      : ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Jam Masuk", "Menit Terlambat", "Jam Pulang", "Status"]);
					// data.push([`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					// data.push(["No", "Tanggal", "Nama Pegawai", "Jabatan", "Status", "Jam Terlambat", "Waktu", "Jam Pulang"]);
					const tableRows = document.querySelectorAll("#data_presensi_per_pegawai tbody tr");

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
						// const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						let cells = Array.from(row.cells).map(cell => cell.textContent.trim());

						// Hapus Nama Pegawai & Jabatan
						cells.splice(2, 2);
						const isRowMerged = row.cells.length < 6;

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
						// { wch: 25 },  // Nama Pegawai
						// { wch: 15 },  // Jabatan
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
						{ wch: 30 },  // Jam Pulang
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Presensi Per Pegawai");
					XLSX.writeFile(workbook, `laporan_presensi_per_pegawai_${namaPegawai}_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);


			} else if (filter === 'bulan') {
				const firstRow = document.querySelector("#data_presensi_per_pegawai tbody tr");
				setTimeout(() => {
					const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
					const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';



					let data = [];

					// Header laporan
					data.push(["Laporan Presensi Per Pegawai"]);
					// const namaPegawai = firstRow.cells[2]?.textContent?.trim() || 'Tidak Diketahui';
					// const jabatan = firstRow.cells[3]?.textContent?.trim() || 'Tidak Diketahui';
					const namaPegawai = $('#nama_per_pegawai').val().trim() || 'Tidak Diketahui';
					const jabatan = $('#pegawai_per_jabatan').val().trim() || 'Tidak Diketahui';
					// data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					// data.push([]);
					// data.push(["No", "Tanggal", "Nama Pegawai", "Jabatan", "Status", "Jam Terlambat", "Waktu", "Jam Pulang"]);
					data.push([`Nama Pegawai : ${namaPegawai}`]);
					data.push([`Jabatan      : ${jabatan}`]);
					data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Jam Masuk", "Menit Terlambat", "Jam Pulang", "Status"]);
					const tableRows = document.querySelectorAll("#data_presensi_per_pegawai tbody tr");

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
						// const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						let cells = Array.from(row.cells).map(cell => cell.textContent.trim());

						// Hapus Nama Pegawai & Jabatan
						cells.splice(2, 2);
						const isRowMerged = row.cells.length < 6;

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
						// { wch: 25 },  // Nama Pegawai
						// { wch: 15 },  // Jabatan
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
						{ wch: 30 },  // Jam Pulang
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Presensi Per Pegawai");
					XLSX.writeFile(workbook, `laporan_presensi_per_pegawai_${namaPegawai}_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else {
				setTimeout(() => {
					const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
					const firstRow = document.querySelector("#data_presensi_per_pegawai tbody tr");
					let data = [];
					// Header laporan
					data.push(["Laporan Presensi Per Pegawai"]);
					// const namaPegawai = firstRow.cells[2]?.textContent?.trim() || 'Tidak Diketahui';
					// const jabatan = firstRow.cells[3]?.textContent?.trim() || 'Tidak Diketahui';
					const namaPegawai = $('#nama_per_pegawai').val().trim() || 'Tidak Diketahui';
					const jabatan = $('#pegawai_per_jabatan').val().trim() || 'Tidak Diketahui';
					data.push([`Nama Pegawai : ${namaPegawai}`]);
					data.push([`Jabatan      : ${jabatan}`]);
					data.push([`Tahun: ${tahun}`]);
					data.push([]);
					data.push(["No", "Tanggal", "Jam Masuk", "Menit Terlambat", "Jam Pulang", "Status"]);
					// data.push([]);
					// data.push(["No", "Tanggal", "Nama Pegawai", "Jabatan", "Status", "Jam Terlambat", "Waktu", "Jam Pulang"]);

					const tableRows = document.querySelectorAll("#data_presensi_per_pegawai tbody tr");

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
						// const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						let cells = Array.from(row.cells).map(cell => cell.textContent.trim());
						// Hapus Nama Pegawai & Jabatan
						cells.splice(2, 2);
						const isRowMerged = row.cells.length < 7;

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
						// { wch: 25 },  // Nama Pegawai
						// { wch: 15 },  // Jabatan
						{ wch: 30 },  // Status
						{ wch: 30 },  // Jam Terlambat
						{ wch: 30 },  // Waktu
						{ wch: 30 },  // Jam Pulang
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
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Presensi Per Pegawai");
					XLSX.writeFile(workbook, `laporan_presensi_per_pegawai_${namaPegawai}_tahun_${tahun}.xlsx`);


					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			}
		} else if (cek_btn === 'laporan_rekapitulasi_gaji') {
			setTimeout(() => {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';

				let data = [];

				// Header laporan
				data.push(["Rekapitulasi Gaji Guru Dan Karyawan"]);
				data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
				data.push([]);
				let header = [
					"No",
					"Nama Pegawai",
					"Gaji Pokok",
					"Struktural",
					"Bonus",
					"Jumlah Pendapatan",
					"Potongan Tidak Masuk",
					`UIG/UIK ${formatPersen(rekap_persenUigUik)}%`,
					`Zakat ${formatPersen(rekap_persenZakat)}%`
				];
				masterPotonganGlobal.forEach(mp => {
					header.push(mp.nama_potongan);
				});
				header.push("Potongan Pinjaman");
				header.push("Jumlah Pengeluaran");
				header.push("Sisa");
				data.push(header);

				const tableRows = document.querySelectorAll("#data_rekapitulasi_gaji tbody tr");
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

				let total_gaji_pokok = 0;
				let total_struktural = 0;
				let total_bonus = 0;
				let total_pendapatan = 0;
				let total_tidak_masuk = 0;
				let total_uig = 0;
				let total_zakat = 0;
				let totalPotongan = {};

				masterPotonganGlobal.forEach(mp => {
					totalPotongan[mp.id] = 0;
				});
				let total_potongan_pinjaman = 0;
				let total_pengeluaran = 0;
				let total_sisa = 0;
				function toNumber(value) {

					if (value === '' || value == null) {
						return 0;
					}

					return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || 0;
				}
				function excelValue(val) {
					let num = toNumber(val);
					return num === 0 ? 0 : num;
				}
				tableRows.forEach((row, idx) => {
					const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
					const totalKolom = 9 + masterPotonganGlobal.length + 3;
					const isRowMerged = row.cells.length < totalKolom;

					let finalRow = [];

					if (isRowMerged) {
						// Tambahkan nilai dari baris sebelumnya
						finalRow.push(lastNo);
						finalRow.push(lastTanggal);
						finalRow.push(...cells);
					} else {
						lastNo = cells[0];
						lastTanggal = cells[1];
						finalRow = [];

						finalRow.push(cells[0]);
						finalRow.push(cells[1]);

						for (let i = 2; i < cells.length; i++) {
							finalRow.push(excelValue(cells[i]));
						}

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

						total_gaji_pokok += toNumber(cells[2]);
						total_struktural += toNumber(cells[3]);
						total_bonus += toNumber(cells[4]);
						total_pendapatan += toNumber(cells[5]);
						total_tidak_masuk += toNumber(cells[6]);
						total_uig += toNumber(cells[7]);
						total_zakat += toNumber(cells[8]);
						let startPotongan = 9;

						masterPotonganGlobal.forEach((mp, index) => {

							totalPotongan[mp.id] +=
								toNumber(cells[startPotongan + index]);

						});
						let idxPotonganPinjaman = 9 + masterPotonganGlobal.length;
						let idxPengeluaran = idxPotonganPinjaman + 1;
						let idxSisa = idxPotonganPinjaman + 2;

						total_potongan_pinjaman += toNumber(cells[idxPotonganPinjaman]);
						total_pengeluaran += toNumber(cells[idxPengeluaran]);
						total_sisa += toNumber(cells[idxSisa]);
					}

					data.push(finalRow);
					currentRowIndex++;

					if (idx === tableRows.length - 1 && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
						mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
					}
				});
				let totalRow = [
					'',
					'Jumlah',
					total_gaji_pokok,
					total_struktural,
					total_bonus,
					total_pendapatan,
					total_tidak_masuk,
					total_uig,
					total_zakat
				];

				masterPotonganGlobal.forEach(mp => {
					totalRow.push(totalPotongan[mp.id]);
				});

				totalRow.push(total_potongan_pinjaman);
				totalRow.push(total_pengeluaran);
				totalRow.push(total_sisa);

				data.push(totalRow);
				const worksheet = XLSX.utils.aoa_to_sheet(data);
				const range = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= range.e.r; R++) {
					for (let C = 2; C <= range.e.c; C++) {

						let address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});

						if (worksheet[address]) {
							worksheet[address].z = '#,##0';
						}

					}

				}
				worksheet["!merges"] = mergeInstructions;

				let cols = [
					{ wch: 5 },   // No
					{ wch: 35 }   // Nama Pegawai
				];

				for (let i = 2; i <= range.e.c; i++) {
					cols.push({ wch: 20 });
				}
				worksheet["!cols"] = cols;

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
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Rekapitulasi Gaji");
				XLSX.writeFile(workbook, `laporan_rekapitulasi_gaji_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 2500);
		} else if (cek_btn === 'laporan_kas') {
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
					data.push(["LAPORAN KAS"]);
					data.push(["SD KREATIF MUHAMMADIYAH LUMAJANG"]);
					data.push([]);
					data.push([`Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`]);
					data.push(["Uraian", "Jumlah", "Uraian", "Jumlah"]);

					const tableRows = document.querySelectorAll("#data_laporan_kas tbody tr");

					if (tableRows.length === 0) {
						alert("Tidak ada data untuk diekspor.");
						return;
					}

					function toNumber(value) {
						if (value === '' || value == null) { return ''; }
						return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || '';
					}

					tableRows.forEach(row => {
						let cells = Array.from(row.cells).map(cell => cell.innerText.trim());
						/*
						Struktur normal:
						[uraian1,jumlah1,uraian2,jumlah2]
						*/

						if (cells.length == 4) {
							data.push([
								cells[0],
								toNumber(cells[1]),
								cells[2],
								toNumber(cells[3])]);
						}

						// saldo bulan ini (colspan=3)
						else if (cells.length == 2) {
							data.push(['', '', cells[0], toNumber(cells[1])]);
						}
					});

					const startTtdRow = data.length;

					// jarak
					data.push([]);
					data.push([]);

					// tanggal
					data.push([
						'',
						'',
						`Lumajang, ${tanggal_laporan_kas}`,
						''
					]);

					// jabatan
					data.push([
						'Ketua',
						'',
						'Bendahara',
						''
					]);

					// ruang tanda tangan
					data.push([]);
					data.push([]);
					data.push([]);
					data.push([]);

					// nama
					data.push([
						'Dimas Doddy Priyambodho, S.Ag, M.Pd',
						'',
						'Nurlaili Budi Indahwati, S.Psi',
						''
					]);

					const worksheet = XLSX.utils.aoa_to_sheet(data);
					let excelRange = XLSX.utils.decode_range(worksheet['!ref']);
					for (
						let R = 0;
						R <= excelRange.e.r;
						R++
					) {
						[1, 3].forEach(C => {
							let addr = XLSX.utils.encode_cell({
								r: R,
								c: C
							});
							if (worksheet[addr]) {
								worksheet[addr].z = '#,##0';
							}
						});
					}

					worksheet["!cols"] = [
						{ wch: 35 },
						{ wch: 20 },
						{ wch: 35 },
						{ wch: 20 },
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

					worksheet['!merges'] = [
						// judul laporan
						{
							s: { r: 0, c: 0 },
							e: { r: 0, c: 3 }
						},

						// nama sekolah
						{
							s: { r: 1, c: 0 },
							e: { r: 1, c: 3 }
						},
						// tanggal
						{
							s: { r: startTtdRow + 2, c: 2 },
							e: { r: startTtdRow + 2, c: 3 }
						},

						// ketua
						{
							s: { r: startTtdRow + 3, c: 0 },
							e: { r: startTtdRow + 3, c: 1 }
						},

						// bendahara
						{
							s: { r: startTtdRow + 3, c: 2 },
							e: { r: startTtdRow + 3, c: 3 }
						},

						// nama ketua
						{
							s: { r: startTtdRow + 8, c: 0 },
							e: { r: startTtdRow + 8, c: 1 }
						},

						// nama bendahara
						{
							s: { r: startTtdRow + 8, c: 2 },
							e: { r: startTtdRow + 8, c: 3 }
						},

					];


					['A1', 'A2', 'A4'].forEach(cell => {

						if (worksheet[cell]) {

							worksheet[cell].s = {

								font: {
									bold: true
								},

								alignment: {
									horizontal: "center",
									vertical: "center"
								}

							};

						}

					});

					[
						`A${startTtdRow + 4}`,
						`C${startTtdRow + 4}`,
						`A${startTtdRow + 9}`,
						`C${startTtdRow + 9}`
					].forEach(cell => {

						if (worksheet[cell]) {

							worksheet[cell].s = {

								alignment: {
									horizontal: "center"
								}

							};

						}

					});

					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Kas");
					XLSX.writeFile(workbook, `laporan_kas_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}.xlsx`);

					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else if (filter === 'bulan') {
				setTimeout(() => {
					const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
					const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';

					let data = [];

					// Header laporan
					data.push(["LAPORAN KAS"]);
					data.push(["SD KREATIF MUHAMMADIYAH LUMAJANG"]);
					data.push([]);
					data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
					data.push(["Uraian", "Jumlah", "Uraian", "Jumlah"]);

					const tableRows = document.querySelectorAll("#data_laporan_kas tbody tr");

					if (tableRows.length === 0) {
						alert("Tidak ada data untuk diekspor.");
						return;
					}

					function toNumber(value) {
						if (value === '' || value == null) { return ''; }
						return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || '';
					}

					tableRows.forEach(row => {
						let cells = Array.from(row.cells).map(cell => cell.innerText.trim());
						/*
						Struktur normal:
						[uraian1,jumlah1,uraian2,jumlah2]
						*/

						if (cells.length == 4) {
							data.push([
								cells[0],
								toNumber(cells[1]),
								cells[2],
								toNumber(cells[3])]);
						}

						// saldo bulan ini (colspan=3)
						else if (cells.length == 2) {
							data.push(['', '', cells[0], toNumber(cells[1])]);
						}
					});

					const startTtdRow = data.length;

					// jarak
					data.push([]);
					data.push([]);

					// tanggal
					data.push([
						'',
						'',
						`Lumajang, ${tanggal_laporan_kas}`,
						''
					]);

					// jabatan
					data.push([
						'Ketua',
						'',
						'Bendahara',
						''
					]);

					// ruang tanda tangan
					data.push([]);
					data.push([]);
					data.push([]);
					data.push([]);

					// nama
					data.push([
						'Dimas Doddy Priyambodho, S.Ag, M.Pd',
						'',
						'Nurlaili Budi Indahwati, S.Psi',
						''
					]);

					const worksheet = XLSX.utils.aoa_to_sheet(data);
					let excelRange = XLSX.utils.decode_range(worksheet['!ref']);
					for (
						let R = 0;
						R <= excelRange.e.r;
						R++
					) {
						[1, 3].forEach(C => {
							let addr = XLSX.utils.encode_cell({
								r: R,
								c: C
							});
							if (worksheet[addr]) {
								worksheet[addr].z = '#,##0';
							}
						});
					}

					worksheet["!cols"] = [
						{ wch: 35 },
						{ wch: 20 },
						{ wch: 35 },
						{ wch: 20 },
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

					worksheet['!merges'] = [
						// judul laporan
						{
							s: { r: 0, c: 0 },
							e: { r: 0, c: 3 }
						},

						// nama sekolah
						{
							s: { r: 1, c: 0 },
							e: { r: 1, c: 3 }
						},
						// tanggal
						{
							s: { r: startTtdRow + 2, c: 2 },
							e: { r: startTtdRow + 2, c: 3 }
						},

						// ketua
						{
							s: { r: startTtdRow + 3, c: 0 },
							e: { r: startTtdRow + 3, c: 1 }
						},

						// bendahara
						{
							s: { r: startTtdRow + 3, c: 2 },
							e: { r: startTtdRow + 3, c: 3 }
						},

						// nama ketua
						{
							s: { r: startTtdRow + 8, c: 0 },
							e: { r: startTtdRow + 8, c: 1 }
						},

						// nama bendahara
						{
							s: { r: startTtdRow + 8, c: 2 },
							e: { r: startTtdRow + 8, c: 3 }
						},

					];


					['A1', 'A2', 'A4'].forEach(cell => {
						if (worksheet[cell]) {
							worksheet[cell].s = {
								font: {
									bold: true
								},

								alignment: {
									horizontal: "center",
									vertical: "center"
								}
							};
						}
					});
					[
						`A${startTtdRow + 4}`,
						`C${startTtdRow + 4}`,
						`A${startTtdRow + 9}`,
						`C${startTtdRow + 9}`
					].forEach(cell => {
						if (worksheet[cell]) {
							worksheet[cell].s = {
								alignment: { horizontal: "center" }
							};
						}
					});

					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Kas");
					XLSX.writeFile(workbook, `laporan_kas_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);

					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			} else {
				setTimeout(() => {
					const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
					let data = [];

					// Header laporan
					data.push(["LAPORAN KAS"]);
					data.push(["SD KREATIF MUHAMMADIYAH LUMAJANG"]);
					data.push([]);
					data.push([`Tahun: ${tahun}`]);
					const tableRows = document.querySelectorAll("#table_tahunan tr");

					if (tableRows.length === 0) {
						alert("Tidak ada data untuk diekspor.");
						return;
					}

					function toNumber(value) {
						if (value === '' || value == null) { return ''; }
						return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || '';
					}

					let merges = [];

					tableRows.forEach((row, rowIndex) => {
						let rowData = [];
						let colIndex = 0;
						row.querySelectorAll('th,td').forEach(cell => {
							let text = cell.innerText.trim();
							let angka = text.replace(/\./g, '').replace(/,/g, '');
							let value = text;
							if (!isNaN(angka) && angka !== '') {
								value = Number(angka);
							}
							rowData.push(value);
							let colspan = parseInt(cell.getAttribute('colspan')) || 1;
							if (colspan > 1) {
								merges.push({
									s: { r: rowIndex + 4, c: colIndex },
									e: { r: rowIndex + 4, c: colIndex + colspan - 1 }
								});
								for (let i = 1; i < colspan; i++) {
									rowData.push('');
								}
							}

							colIndex += colspan;
						});

						data.push(rowData);
					});
					const startTtdRow = data.length;

					// jarak
					data.push([]);
					data.push([]);

					// tanggal
					data.push([
						'', '', '', '', '', '', '', '',
						`Lumajang, ${tanggal_laporan_kas}`
					]);

					// jabatan
					data.push([
						'', '', '',
						'Ketua',
						'',
						'',
						'',
						'',
						'Bendahara'
					]);
					// ruang tanda tangan
					data.push([]);
					data.push([]);
					data.push([]);
					data.push([]);

					// nama
					data.push([
						'', '', '',
						'Dimas Doddy Priyambodho, S.Ag, M.Pd',
						'',
						'',
						'',
						'',
						'Nurlaili Budi Indahwati, S.Psi'
					]);

					const worksheet = XLSX.utils.aoa_to_sheet(data);
					worksheet['!merges'] = worksheet['!merges'] || [];
					let excelRange = XLSX.utils.decode_range(worksheet['!ref']);
					for (let R = 0; R <= excelRange.e.r; R++) {
						for (let C = 1; C <= 13; C++) {
							let addr = XLSX.utils.encode_cell({
								r: R,
								c: C
							});
							if (worksheet[addr] && typeof worksheet[addr].v === 'number') {
								worksheet[addr].z = '#,##0';
							}
						}
					}

					worksheet["!cols"] = [
						{ wch: 20 }, // Tahun Mulai
						{ wch: 30 }, // Bulan Aktif
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 },
						{ wch: 15 }
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

					worksheet['!merges'] = [
						...merges,
						{
							s: { r: startTtdRow + 2, c: 8 },
							e: { r: startTtdRow + 2, c: 10 }
						},

						// Ketua
						{
							s: { r: startTtdRow + 3, c: 3 },
							e: { r: startTtdRow + 3, c: 5 }
						},

						// Bendahara
						{
							s: { r: startTtdRow + 3, c: 8 },
							e: { r: startTtdRow + 3, c: 10 }
						},

						// Nama Ketua
						{
							s: { r: startTtdRow + 8, c: 3 },
							e: { r: startTtdRow + 8, c: 5 }
						},

						// Nama Bendahara
						{
							s: { r: startTtdRow + 8, c: 8 },
							e: { r: startTtdRow + 8, c: 10 }
						}
					];


					['A1', 'A2', 'A4'].forEach(cell => {
						if (worksheet[cell]) {
							worksheet[cell].s = {
								font: { bold: true },
								alignment: {
									horizontal: "center",
									vertical: "center"
								}
							};
						}
					});

					[
						`A${startTtdRow + 4}`,
						`C${startTtdRow + 4}`,
						`A${startTtdRow + 9}`,
						`C${startTtdRow + 9}`
					].forEach(cell => {
						if (worksheet[cell]) {
							worksheet[cell].s = {
								alignment: { horizontal: "center" }
							};
						}
					});

					const workbook = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Kas");
					XLSX.writeFile(workbook, `laporan_kas_tahun_${tahun}.xlsx`);

					$('#btn_print_laporan_excel').attr('disabled', false);
					$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
				}, 1500);
			}
		} else if (cek_btn === 'laporan_penggunaan_anggaran') {
			setTimeout(() => {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';

				let data = [];
				// Header laporan
				data.push(["LAPORAN PENGGUNAAN ANGGARAN"]);
				data.push(["SD KREATIF MUHAMMADIYAH LUMAJANG"]);
				data.push([]);
				data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
				data.push(["Uraian", "Jumlah", "Uraian", "Jumlah"]);

				const tableRows = document.querySelectorAll("#data_laporan_penggunaan_anggaran tbody tr");

				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}
				function toNumber(value) {
					if (value == '' || value == null) {
						return '';
					}
					return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || '';
				}
				tableRows.forEach(row => {
					let cells = Array.from(row.cells).map(cell => cell.innerText.trim());

					// normal
					if (cells.length == 4) {
						data.push([
							cells[0],
							toNumber(cells[1]),
							cells[2],
							toNumber(cells[3])
						]);
					}
					// saldo bulan ini
					else if (cells.length == 2) {
						data.push([]);
						data.push([]);
						data.push([
							'',
							'',
							cells[0],
							toNumber(cells[1])
						]);
					}
				});

				const startTtdRow = data.length;

				// jarak
				data.push([]);
				data.push([]);

				// tanggal
				data.push([
					'',
					'',
					`Lumajang, ${tanggal_laporan_penggunaan_anggaran}`,
					''
				]);

				// jabatan
				data.push([
					'Ketua',
					'',
					'Bendahara',
					''
				]);

				// ruang tanda tangan
				data.push([]);
				data.push([]);
				data.push([]);
				data.push([]);

				// nama
				data.push([
					'Dimas Doddy Priyambodho, S.Ag, M.Pd',
					'',
					'Nurlaili Budi Indahwati, S.Psi',
					''
				]);

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= excelRange.e.r; R++) {
					[1, 3].forEach(C => {
						let addr = XLSX.utils.encode_cell({
							r: R,
							c: C
						});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0';
						}
					});
				}
				worksheet["!cols"] = [
					{ wch: 35 },
					{ wch: 20 },
					{ wch: 35 },
					{ wch: 20 }
				];
				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						const cell = worksheet[address];

						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "top"
							};
						}
					}
				}

				worksheet['!merges'] = [

					// tanggal
					{
						s: { r: startTtdRow + 2, c: 2 },
						e: { r: startTtdRow + 2, c: 3 }
					},

					// ketua
					{
						s: { r: startTtdRow + 3, c: 0 },
						e: { r: startTtdRow + 3, c: 1 }
					},

					// bendahara
					{
						s: { r: startTtdRow + 3, c: 2 },
						e: { r: startTtdRow + 3, c: 3 }
					},

					// nama ketua
					{
						s: { r: startTtdRow + 8, c: 0 },
						e: { r: startTtdRow + 8, c: 1 }
					},

					// nama bendahara
					{
						s: { r: startTtdRow + 8, c: 2 },
						e: { r: startTtdRow + 8, c: 3 }
					},
				];


				['A1', 'A2', 'A4'].forEach(cell => {
					if (worksheet[cell]) {
						worksheet[cell].s = {
							font: {
								bold: true
							},
							alignment: {
								horizontal: "center"
							}
						};
					}
				});

				[
					`A${startTtdRow + 4}`,
					`C${startTtdRow + 4}`,
					`A${startTtdRow + 9}`,
					`C${startTtdRow + 9}`
				].forEach(cell => {
					if (worksheet[cell]) {
						worksheet[cell].s = {
							alignment: {
								horizontal: "center"
							}
						};
					}
				});
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Penggunaan Anggaran");
				XLSX.writeFile(workbook, `laporan_penggunaan_anggaran_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);


				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_rencana_anggaran') {
			setTimeout(() => {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';

				let data = [];

				// Header laporan
				data.push(["LAPORAN RENCANA ANGGARAN"]);
				data.push(["SD KREATIF MUHAMMADIYAH LUMAJANG"]);
				data.push([]);
				data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
				data.push(["Uraian", "Jumlah", "Uraian", "Jumlah"]);
				const tableRows = document.querySelectorAll("#data_laporan_rencana_anggaran tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}
				function toNumber(value) {
					if (value == '' || value == null) {
						return '';
					}
					return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || '';
				}
				tableRows.forEach(row => {
					let cells = Array.from(row.cells).map(cell => cell.innerText.trim());

					// normal
					if (cells.length == 4) {
						data.push([
							cells[0],
							toNumber(cells[1]),
							cells[2],
							toNumber(cells[3])
						]);
					}
				});

				const startTtdRow = data.length;

				// jarak
				data.push([]);
				data.push([]);

				// tanggal
				data.push([
					'',
					'',
					`Lumajang, ${tanggal_laporan_rencana_anggaran}`,
					''
				]);

				// jabatan
				data.push([
					'Ketua',
					'',
					'Bendahara',
					''
				]);

				// ruang tanda tangan
				data.push([]);
				data.push([]);
				data.push([]);
				data.push([]);

				// nama
				data.push([
					'Dimas Doddy Priyambodho, S.Ag, M.Pd',
					'',
					'Nurlaili Budi Indahwati, S.Psi',
					''
				]);

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= excelRange.e.r; R++) {
					[1, 3].forEach(C => {
						let addr = XLSX.utils.encode_cell({
							r: R,
							c: C
						});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0';
						}
					});
				}
				worksheet["!cols"] = [
					{ wch: 35 },
					{ wch: 20 },
					{ wch: 35 },
					{ wch: 20 }
				];
				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						const cell = worksheet[address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "top"
							};
						}
					}
				}

				worksheet['!merges'] = [

					// tanggal
					{
						s: { r: startTtdRow + 2, c: 2 },
						e: { r: startTtdRow + 2, c: 3 }
					},

					// ketua
					{
						s: { r: startTtdRow + 3, c: 0 },
						e: { r: startTtdRow + 3, c: 1 }
					},

					// bendahara
					{
						s: { r: startTtdRow + 3, c: 2 },
						e: { r: startTtdRow + 3, c: 3 }
					},

					// nama ketua
					{
						s: { r: startTtdRow + 8, c: 0 },
						e: { r: startTtdRow + 8, c: 1 }
					},

					// nama bendahara
					{
						s: { r: startTtdRow + 8, c: 2 },
						e: { r: startTtdRow + 8, c: 3 }
					},
				];


				['A1', 'A2', 'A4'].forEach(cell => {
					if (worksheet[cell]) {
						worksheet[cell].s = {
							font: {
								bold: true
							},
							alignment: {
								horizontal: "center"
							}
						};
					}
				});

				[
					`A${startTtdRow + 4}`,
					`C${startTtdRow + 4}`,
					`A${startTtdRow + 9}`,
					`C${startTtdRow + 9}`
				].forEach(cell => {
					if (worksheet[cell]) {
						worksheet[cell].s = {
							alignment: {
								horizontal: "center"
							}
						};
					}
				});
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Rencana Anggaran");
				XLSX.writeFile(workbook, `laporan_rencana_anggaran_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);


				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_pos') {
			setTimeout(() => {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';

				let data = [];

				// Header laporan
				data.push(["LAPORAN POS"]);
				data.push(["SD KREATIF MUHAMMADIYAH LUMAJANG"]);
				data.push([]);
				data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
				data.push(["Uraian", "Saldo Bulan Lalu", "Masuk", "Keluar", "Saldo"]);
				const tableRows = document.querySelectorAll("#data_laporan_pos tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}
				function toNumber(value) {
					if (value == '' || value == null) {
						return '';
					}
					return Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim()) || '';
				}
				function toNumberSaldo(value) {
					if (value == null || value.toString().trim() === '') {
						return 0;
					}
					let angka = Number(value.toString().replace(/\./g, '').replace(/,/g, '').trim());
					return isNaN(angka) ? 0 : angka;
				}
				tableRows.forEach(row => {
					let cells = Array.from(row.cells).map(cell => cell.innerText.trim());

					// normal
					if (cells.length == 5) {
						data.push([
							cells[0],
							toNumber(cells[1]),
							toNumber(cells[2]),
							toNumber(cells[3]),
							toNumberSaldo(cells[4]),
						]);
					}
				});

				const startTtdRow = data.length;
				// jarak
				data.push([]);
				data.push([]);
				// tanggal
				data.push([
					'',
					'',
					`Lumajang, ${tanggal_laporan_pos}`,
					''
				]);

				// jabatan
				data.push([
					'Ketua',
					'',
					'Bendahara',
					''
				]);
				// ruang tanda tangan
				data.push([]);
				data.push([]);
				data.push([]);
				data.push([]);
				// nama
				data.push([
					'Dimas Doddy Priyambodho, S.Ag, M.Pd',
					'',
					'Nurlaili Budi Indahwati, S.Psi',
					''
				]);
				// jarak bawah
				// data.push([]);
				// data.push([]);
				// mengetahui
				// data.push([
				// 	'',
				// 	'Mengetahui',
				// 	'',
				// 	''
				// ]);
				// jabatan mengetahui
				// data.push([
				// 	'',
				// 	'Koordinator Majlis Dikdasmen',
				// 	'',
				// 	''
				// ]);
				// ruang tanda tangan
				// data.push([]);
				// data.push([]);
				// data.push([]);
				// data.push([]);
				// nama mengetahui
				// data.push([
				// 	'',
				// 	'Drs. Agus Siswantono, M.Psi.',
				// 	'',
				// 	''
				// ]);
				const worksheet = XLSX.utils.aoa_to_sheet(data);
				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= excelRange.e.r; R++) {
					[1, 2, 3, 4].forEach(C => {
						let addr = XLSX.utils.encode_cell({
							r: R,
							c: C
						});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0';
						}
					});
				}
				worksheet["!cols"] = [
					{ wch: 25 },
					{ wch: 15 },
					{ wch: 15 },
					{ wch: 15 },
					{ wch: 15 }
				];
				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						const cell = worksheet[address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "top"
							};
						}
					}
				}

				worksheet['!merges'] = [

					// tanggal
					{
						s: { r: startTtdRow + 2, c: 2 },
						e: { r: startTtdRow + 2, c: 3 }
					},

					// ketua
					{
						s: { r: startTtdRow + 3, c: 0 },
						e: { r: startTtdRow + 3, c: 1 }
					},

					// bendahara
					{
						s: { r: startTtdRow + 3, c: 2 },
						e: { r: startTtdRow + 3, c: 3 }
					},

					// nama ketua
					{
						s: { r: startTtdRow + 8, c: 0 },
						e: { r: startTtdRow + 8, c: 1 }
					},

					// nama bendahara
					{
						s: { r: startTtdRow + 8, c: 2 },
						e: { r: startTtdRow + 8, c: 3 }
					},

					// mengetahui
					// {
					// 	s: { r: startTtdRow + 11, c: 1 },
					// 	e: { r: startTtdRow + 11, c: 2 }
					// },

					// koordinator
					// {
					// 	s: { r: startTtdRow + 12, c: 1 },
					// 	e: { r: startTtdRow + 12, c: 2 }
					// },

					// nama koordinator
					// {
					// 	s: { r: startTtdRow + 17, c: 1 },
					// 	e: { r: startTtdRow + 17, c: 2 }
					// }

				];


				['A1', 'A2', 'A4'].forEach(cell => {

					if (worksheet[cell]) {

						worksheet[cell].s = {

							font: {
								bold: true
							},

							alignment: {
								horizontal: "center"
							}

						};

					}

				});

				[
					`A${startTtdRow + 4}`,
					`C${startTtdRow + 4}`,
					`A${startTtdRow + 9}`,
					`C${startTtdRow + 9}`
				].forEach(cell => {
					if (worksheet[cell]) {
						worksheet[cell].s = {
							alignment: {
								horizontal: "center"
							}
						};
					}
				});
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan POS");
				XLSX.writeFile(workbook, `laporan_pos_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);


				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_rencana_asumsi_pemasukan') {
			setTimeout(() => {
				const semester = document.querySelector('#form-semester-tahun-ajaran select[name="semester"]')?.value || '';

				let data = [];
				data.push(["Laporan Rencana Asumsi Pemasukan"]);
				data.push([`Tahun Ajaran : ${tahun_ajaran_asumsi}`]);
				data.push([]);

				let header = ["Kategori", "Asumsi Pemasukan Total", "% Masuk", "Asumsi Masuk", "Saving", "%Saving"];
				document.querySelectorAll("#data_laporan_rencana_asumsi_pemasukan thead tr:last-child th").forEach(th => {
					header.push(th.innerText);
				});
				data.push(header);

				const tableRows = document.querySelectorAll("#data_laporan_rencana_asumsi_pemasukan tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}

				function toNumber(value, index) {

					if (value == '' || value == null) {
						return '';
					}
					value = value.toString().trim();
					if (index == 2 || index == 5) {
						value = value.replace('%', '');
						value = value.replace(',', '.');
						return parseFloat(value) || 0;
					}
					value = value.replace(/\./g, '');
					value = value.replace(',', '.');

					return Number(value) || 0;
				}

				tableRows.forEach(row => {
					let cells = Array.from(row.cells).map(x => x.innerText.trim());
					let rowData = [];
					cells.forEach((cell, index) => {
						if (index == 0) {
							rowData.push(cell);
						} else {
							rowData.push(toNumber(cell, index))
							// rowData.push(toNumber(cell));
						}
					});
					data.push(rowData);
				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= excelRange.e.r; R++) {
					for (let C = 1; C <= excelRange.e.c; C++) {
						let addr = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						if (worksheet[addr]) {
							if (C == 2) {
								worksheet[addr].z = '0"%"';
							} else if (C == 5) {
								worksheet[addr].z = '0.00';
							} else {
								worksheet[addr].z = '#,##0';
							}
						}
					}
				}
				worksheet["!cols"] =
					header.map((h, i) => {
						return { wch: i == 0 ? 30 : 20 }
					});
				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						const cell = worksheet[address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "top"
							};
						}
					}
				}
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Rencana Asumsi Pemasukan");
				XLSX.writeFile(workbook, `laporan_rencana_asumsi_pemasukan_tahun_ajaran_${tahun_ajaran_asumsi}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 5000);
		} else if (cek_btn === 'laporan_rencana_pengeluaran') {
			setTimeout(() => {
				const semester = document.querySelector('#form-semester-tahun-ajaran select[name="semester"]')?.value || '';
				let data = [];
				// Header laporan
				data.push(["Laporan Rencana Pengeluaran"]);
				data.push([`Tahun Ajaran : ${tahun_ajaran_rencana_pengeluaran}`]);
				data.push([]);
				// Ambil header tabel
				const headerCells = document.querySelectorAll('#data_laporan_rencana_pengeluaran thead th');
				let header = [];
				headerCells.forEach(th => {
					header.push(th.innerText.trim());
				});
				data.push(header)

				const tableRows = document.querySelectorAll("#data_laporan_rencana_pengeluaran tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}
				function toNumber(value) {

					if (
						value === '' ||
						value === '-' ||
						value == null
					) {
						return '';
					}

					return Number(
						value
							.toString()
							.replace(/\./g, '')
							.replace(/,/g, '.')
							.trim()
					) || 0;
				}

				tableRows.forEach(row => {

					let rowData = [];

					Array.from(row.cells).forEach((cell, index) => {

						let value = cell.innerText.trim();

						if (index === 0) {
							rowData.push(value);
						} else {
							rowData.push(toNumber(value));
						}

					});

					data.push(rowData);

				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);

				// Format angka excel
				const range = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= range.e.r; R++) {

					for (let C = 1; C <= range.e.c; C++) {

						const addr = XLSX.utils.encode_cell({
							r: R,
							c: C
						});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0.00';
						}
					}
				}

				// Auto width kolom
				let cols = [];

				cols.push({
					wch: 35
				});

				for (let i = 1; i <= range.e.c; i++) {
					cols.push({
						wch: 18
					});
				}

				worksheet["!cols"] = cols;

				// Wrap text
				for (let R = range.s.r; R <= range.e.r; ++R) {

					for (let C = range.s.c; C <= range.e.c; ++C) {

						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});

						const cell = worksheet[address];

						if (cell) {

							cell.s = cell.s || {};

							cell.s.alignment = {
								wrapText: true,
								vertical: "top"
							};

						}
					}
				}
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Rencana Pengeluaran");
				XLSX.writeFile(workbook, `laporan_rencana_pengeluaran_tahun_ajaran_${tahun_ajaran_rencana_pengeluaran}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_rencana_pemasukan') {
			setTimeout(() => {
				const semester = document.querySelector('#form-semester-tahun-ajaran select[name="semester"]')?.value || '';
				let data = [];
				// Header
				data.push(["Laporan Rencana Pemasukan"]);
				data.push([`Tahun Ajaran : ${tahun_ajaran}`]);

				data.push([]);
				data.push([
					"No",
					"Jenis Pendapatan",
					"Satuan",
					"Vol",
					"Nilai Satuan",
					"Jumlah",
					"Satuan Penerimaan",
					"Volume Penerimaan",
					"Total"
				]);
				const tableRows = $('#data_laporan_rencana_pemasukan tbody tr');

				if (tableRows.length == 0) {
					alert(
						"Tidak ada data untuk diekspor"
					);
					return;
				}
				function toNumber(value) {
					if (!value) return '';
					let angka = value.toString().replace(/[^\d]/g, '');
					return angka == '' ? '' : parseFloat(angka);
				}

				tableRows.each(function () {
					let cells = [];
					$(this).find('td').each(function () {
						cells.push($(this).text().trim());
					});


					// jika lebih dari 9 kolom potong
					if (cells.length > 9) {
						cells = cells.slice(0, 9);
					}

					// jika kurang dari 9 kolom tambah kosong
					while (cells.length < 9) {
						cells.push('');
					}
					// ubah kolom angka
					cells[3] = toNumber(cells[3]); // vol
					cells[4] = toNumber(cells[4]); // nilai satuan
					cells[5] = toNumber(cells[5]); // jumlah
					cells[7] = toNumber(cells[7]); // volume penerimaan
					cells[8] = toNumber(cells[8]); // total
					data.push(cells);
				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = 0; R <= excelRange.e.r; R++) {
					[3, 4, 5, 7, 8]
						.forEach(C => {
							let addr = XLSX.utils.encode_cell({ r: R, c: C });
							if (worksheet[addr]) {
								worksheet[addr].z = '#,##0';
							}
						});
				}

				worksheet["!cols"] = [
					{ wch: 5 },
					{ wch: 35 },
					{ wch: 12 },
					{ wch: 10 },
					{ wch: 18 },
					{ wch: 18 },
					{ wch: 20 },
					{ wch: 20 },
					{ wch: 20 }
				];

				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address =
							XLSX.utils.encode_cell({
								r: R,
								c: C
							});
						const cell = worksheet[address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "top"
							};
						}
					}
				}
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Rencana Pemasukan");
				XLSX.writeFile(workbook, `laporan_rencana_pemasukan_tahun_ajaran_${tahun_ajaran}.xlsx`);
				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 3000);
		} else if (cek_btn === 'laporan_olah_pos') {
			setTimeout(() => {
				const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				let data = [];
				data.push(["Laporan Olah POS"]);
				data.push([`Tahun : ${tahun}`]);
				data.push([]);

				let header1 = ['Kategori'];

				const bulanArr = [
					'Januari', 'Februari', 'Maret', 'April',
					'Mei', 'Juni', 'Juli', 'Agustus',
					'September', 'Oktober', 'November', 'Desember'
				];
				const tableRows = document.querySelectorAll("#data_laporan_olah_pos tbody tr");

				if (tableRows.length === 0) {
					alert(
						"Tidak ada data untuk diekspor."
					);
					return;
				}
				bulanArr.forEach((bulan) => {

					header1.push(bulan);
					header1.push('');
					header1.push('');

				});

				data.push(header1);

				let header2 = [''];
				for (let i = 1; i <= 12; i++) {
					header2.push('Masuk');
					header2.push('Keluar');
					header2.push('Saldo');

				}
				data.push(header2);

				function toNumber(value) {
					if (!value) return '';
					let angka = value.toString().replace(/[^\d]/g, '');
					return angka == '' ? '' : parseFloat(angka);
				}

				tableRows.forEach((row) => {
					let cells = Array.from(row.cells).map(cell => cell.textContent.trim());

					while (cells.length < 37) {
						cells.push('');
					}


					for (let i = 1; i <= 36; i++) {
						cells[i] = toNumber(cells[i]);
					}
					data.push(cells);
				});

				let totalRow = ['JUMLAH'];
				for (let col = 1; col <= 36; col++) {
					let total = 0;
					for (let row = 5; row < data.length; row++) {
						let value = data[row][col];
						if (typeof value === 'number') {
							total += value;
						}
					}

					totalRow.push(total || '');
				}
				data.push(totalRow);
				const worksheet = XLSX.utils.aoa_to_sheet(data);

				let mergeInstructions = [];
				for (let i = 0; i < 12; i++) {
					let startCol = 1 + (i * 3);
					mergeInstructions.push({

						s: {
							r: 3,
							c: startCol
						},

						e: {
							r: 3,
							c: startCol + 2
						}
					});
				}
				worksheet["!merges"] = mergeInstructions;
				for (let i = 0; i < 12; i++) {
					let startCol = 1 + (i * 3);
					let cellAddress =
						XLSX.utils.encode_cell({
							r: 3,
							c: startCol
						});

					if (worksheet[cellAddress]) {
						worksheet[cellAddress].s = worksheet[cellAddress].s || {};
						worksheet[cellAddress].s.alignment = {
							horizontal: "center",
							vertical: "center"
						};
					}
				}

				worksheet["!cols"] = [];
				worksheet["!cols"].push({
					wch: 35
				});

				for (let i = 1; i <= 36; i++) {

					worksheet["!cols"].push({
						wch: 15
					});

				}

				const excelRange = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = 0; R <= excelRange.e.r; R++) {
					for (let C = 1; C <= 36; C++) {
						let addr =
							XLSX.utils.encode_cell({
								r: R,
								c: C
							});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0';
						}
					}
				}


				const range = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const cell_address =
							XLSX.utils.encode_cell({
								r: R,
								c: C
							});

						const cell = worksheet[cell_address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "center",
								horizontal: C == 0 ? "left" : "right"
							};
						}
					}
				}

				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Olah POS");
				XLSX.writeFile(workbook, `laporan_olah_pos_tahun_${tahun}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_olah_in') {
			setTimeout(() => {
				const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				const firstRow = document.querySelector("#data_laporan_olah_in tbody tr");
				let data = [];
				// Header laporan
				data.push(["Laporan Olah In"]);

				data.push([`Tahun: ${tahun}`]);
				data.push([]);
				data.push([
					"Kategori",
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
					"Desember"]);

				const tableRows = document.querySelectorAll("#data_laporan_olah_in tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}

				tableRows.forEach((row) => {
					let cells = Array.from(row.cells).map((cell, index) => {
						// Kolom pertama = akun
						if (index === 0) {
							return cell.textContent.trim();
						}

						// Ambil angka asli
						return Number(cell.dataset.nominal || 0);
					});

					data.push(cells);
				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = 4; R <= range.e.r; ++R) {
					for (let C = 1; C <= 12; ++C) {
						const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
						if (worksheet[cellAddress]) {
							worksheet[cellAddress].t = 'n';
							worksheet[cellAddress].z = '#,##0';
						}
					}
				}

				worksheet["!cols"] = [
					// { wch: 5 },   // No
					{ wch: 35 },  // Akun
					{ wch: 15 }, // Jan
					{ wch: 15 }, // Feb
					{ wch: 15 }, // Mar
					{ wch: 15 }, // Apr
					{ wch: 15 }, // Mei
					{ wch: 15 }, // Jun
					{ wch: 15 }, // Jul
					{ wch: 15 }, // Agu
					{ wch: 15 }, // Sep
					{ wch: 15 }, // Okt
					{ wch: 15 }, // Nov
					{ wch: 15 }, // Des
				];

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
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Olah In");
				XLSX.writeFile(workbook, `laporan_olah_in_tahun_${tahun}.xlsx`);


				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_olah_out') {
			setTimeout(() => {
				const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				const firstRow = document.querySelector("#data_laporan_olah_out tbody tr");
				let data = [];
				// Header laporan
				data.push(["Laporan Olah Out"]);

				data.push([`Tahun: ${tahun}`]);
				data.push([]);
				data.push([
					"Kategoru",
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
					"Desember"]);

				const tableRows = document.querySelectorAll("#data_laporan_olah_out tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}

				tableRows.forEach((row) => {
					let cells = Array.from(row.cells).map((cell, index) => {
						// Kolom pertama = akun
						if (index === 0) {
							return cell.textContent.trim();
						}

						// Ambil angka asli
						return Number(cell.dataset.nominal || 0);
					});

					data.push(cells);
				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				const range = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 4; R <= range.e.r; ++R) {
					for (let C = 1; C <= 12; ++C) {
						const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
						if (worksheet[cellAddress]) {
							worksheet[cellAddress].t = 'n';
							worksheet[cellAddress].z = '#,##0';
						}
					}
				}

				worksheet["!cols"] = [
					// { wch: 5 },   // No
					{ wch: 35 },  // Akun
					{ wch: 15 }, // Jan
					{ wch: 15 }, // Feb
					{ wch: 15 }, // Mar
					{ wch: 15 }, // Apr
					{ wch: 15 }, // Mei
					{ wch: 15 }, // Jun
					{ wch: 15 }, // Jul
					{ wch: 15 }, // Agu
					{ wch: 15 }, // Sep
					{ wch: 15 }, // Okt
					{ wch: 15 }, // Nov
					{ wch: 15 }, // Des
				];

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
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Olah Out");
				XLSX.writeFile(workbook, `laporan_olah_out_tahun_${tahun}.xlsx`);


				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 1500);
		} else if (cek_btn === 'laporan_perbandingan_rencana_pengeluaran') {
			// belum
			setTimeout(() => {
				// const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				const semester = document.querySelector('#form-semester-tahun-ajaran select[name="semester"]')?.value || '';
				let data = [];
				// Header laporan
				data.push(["Laporan Perbandingan Rencana Pengeluaran"]);
				data.push([`Tahun Ajaran : ${tahun_ajaran_perbandingan_rab_pengeluaran}`]);
				data.push([]);
				let header1 = ['Kategori'];

				// ambil bulan dari header tabel yang tampil
				const bulanArr = [];
				document.querySelectorAll('#data_laporan_perbandingan_rencana_pengeluaran thead tr:first-child th[colspan="4"]').forEach(th => {
					bulanArr.push(th.innerText.trim());
				});

				const tableRows = document.querySelectorAll("#data_laporan_perbandingan_rencana_pengeluaran tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}


				bulanArr.forEach((bulan) => {
					header1.push(bulan);
					header1.push('');
					header1.push('');
					header1.push('');
				});

				data.push(header1);

				let header2 = [''];
				bulanArr.forEach(() => {
					header2.push('Rencana');
					header2.push('Realisasi');
					header2.push('Selisih');
					header2.push('Status');
				});
				data.push(header2);
				const jumlahKolomBulan = bulanArr.length * 4;
				const totalKolom = jumlahKolomBulan + 1;

				function toNumber(value) {
					if (!value) return '';
					let angka = value.toString().replace(/[^\d-]/g, '');
					return angka == '' ? '' : parseFloat(angka);
				}
				tableRows.forEach(row => {

					let cells = Array.from(row.cells).map(
						cell => cell.textContent.trim()
					);

					while (cells.length < totalKolom) {
						cells.push('');
					}
					for (let i = 1; i < totalKolom; i++) {
						// kolom status
						if ((i % 4) === 0) {
							continue;
						}
						cells[i] = toNumber(cells[i]);
					}
					data.push(cells);
				});


				const worksheet = XLSX.utils.aoa_to_sheet(data);

				let mergeInstructions = [];
				for (let i = 0; i < bulanArr.length; i++) {

					let startCol = 1 + (i * 4);

					mergeInstructions.push({
						s: {
							r: 3,
							c: startCol
						},
						e: {
							r: 3,
							c: startCol + 3
						}
					});
				}
				worksheet["!merges"] = mergeInstructions;
				for (let i = 0; i < bulanArr.length; i++) {
					let startCol = 1 + (i * 4);
					let cellAddress = XLSX.utils.encode_cell({
						r: 3,
						c: startCol
					});

					if (worksheet[cellAddress]) {
						worksheet[cellAddress].s = worksheet[cellAddress].s || {};

						worksheet[cellAddress].s.alignment = {
							horizontal: "center",
							vertical: "center"
						};
					}
				}
				worksheet["!cols"] = [];
				worksheet["!cols"].push({
					wch: 35
				});

				for (let i = 1; i < totalKolom; i++) {
					worksheet["!cols"].push({
						wch: 15
					});
				}

				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = 0; R <= excelRange.e.r; R++) {
					for (let C = 1; C < totalKolom; C++) {
						// skip kolom status
						if ((C % 4) == 0) continue;

						let addr =
							XLSX.utils.encode_cell({
								r: R,
								c: C
							});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0';
						}
					}
				}

				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						const cell = worksheet[address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "center",
								horizontal: C == 0 ? "left" : "right"
							};
						}
					}
				}
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Perbandingan RAB Pengeluaran");
				XLSX.writeFile(workbook, `laporan_perbandingan_rencana_pengeluaran_tahun_ajaran_${tahun_ajaran_perbandingan_rab_pengeluaran}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 3000);
		} else if (cek_btn === 'laporan_perbandingan_rencana_pemasukan') {
			setTimeout(() => {
				// const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				const semester = document.querySelector('#form-semester-tahun-ajaran select[name="semester"]')?.value || '';
				let data = [];
				// Header laporan
				data.push(["Laporan Perbandingan Rencana Pemasukan"]);
				data.push([`Tahun Ajaran : ${tahun_ajaran_perbandingan_rab_pemasukan}`]);
				data.push([]);
				let header1 = ['Kategori', 'Asumsi Masuk'];

				const bulanHeader = [];
				document.querySelectorAll('#data_laporan_perbandingan_rencana_pemasukan thead tr:first-child th[colspan="4"]').forEach(th => {
					bulanHeader.push(th.innerText.trim());
				});
				const bulanArr = bulanHeader;
				const tableRows = document.querySelectorAll("#data_laporan_perbandingan_rencana_pemasukan tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}

				bulanArr.forEach((bulan) => {
					header1.push(bulan);
					header1.push('');
					header1.push('');
					header1.push('');
				});

				data.push(header1);

				let header2 = ['', ''];
				bulanArr.forEach(() => {
					header2.push('Rencana');
					header2.push('Realisasi');
					header2.push('selisih');
					header2.push('status');
				});
				data.push(header2);
				const jumlahKolomBulan = bulanArr.length * 4;
				const totalKolom = jumlahKolomBulan + 2;
				function toNumber(value) {
					if (!value) return '';
					let angka = value.toString().replace(/[^\d-]/g, '');
					return angka == '' ? '' : parseFloat(angka);
				}
				tableRows.forEach(row => {
					let cells = Array.from(row.cells).map(cell => cell.textContent.trim());
					while (cells.length < totalKolom) {
						cells.push('');
					}

					cells[1] = toNumber(cells[1]);
					for (let i = 2; i < cells.length; i++) {
						if ((i - 5) % 4 == 0) {
							continue;
						}
						cells[i] = toNumber(cells[i]);
					}
					data.push(cells);
				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);

				let mergeInstructions = [];
				for (let i = 0; i < bulanArr.length; i++) {
					let startCol = 2 + (i * 4);
					mergeInstructions.push({

						s: {
							r: 3,
							c: startCol
						},

						e: {
							r: 3,
							c: startCol + 3
						}
					});
				}
				worksheet["!merges"] = mergeInstructions;
				for (let i = 0; i < bulanArr.length; i++) {
					let startCol = 2 + (i * 4);
					let cellAddress =
						XLSX.utils.encode_cell({
							r: 3,
							c: startCol
						});

					if (worksheet[cellAddress]) {
						worksheet[cellAddress].s = worksheet[cellAddress].s || {};
						worksheet[cellAddress].s.alignment = {
							horizontal: "center",
							vertical: "center"
						};
					}
				}

				worksheet["!cols"] = [];
				worksheet["!cols"].push({
					wch: 35
				});

				worksheet["!cols"].push({
					wch: 15
				});

				for (let i = 1; i <= jumlahKolomBulan; i++) {

					worksheet["!cols"].push({
						wch: 15
					});

				}

				// format angka excel
				let excelRange = XLSX.utils.decode_range(worksheet['!ref']);

				for (let R = 0; R <= excelRange.e.r; R++) {
					for (let C = 1; C < totalKolom; C++) {
						let addr =
							XLSX.utils.encode_cell({
								r: R,
								c: C
							});

						if (worksheet[addr]) {
							worksheet[addr].z = '#,##0';
						}
					}
				}


				const range = XLSX.utils.decode_range(worksheet['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const address = XLSX.utils.encode_cell({
							r: R,
							c: C
						});
						const cell = worksheet[address];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "center",
								horizontal: C == 0 ? "left" : "right"
							};
						}
					}
				}
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Perbandingan RAB Pemasukann");
				XLSX.writeFile(workbook, `laporan_perbandingan_rencana_pemasukan_tahun_ajaran_${tahun_ajaran_perbandingan_rab_pemasukan}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 5000);
		} else if (cek_btn === 'laporan_penerimaan_honorarium') {
			setTimeout(() => {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';

				let data = [];

				// Header laporan
				data.push(["Laporan Penerimaan Honorarium Guru Dan Karyawan"]);
				data.push(["SD Kreatif Muhammadiyah Lumajang"]);
				data.push([`Bulan: ${getNamaBulan(bulan)} ${tahun}`]);
				data.push([]);
				data.push([
					"No",
					"Nama",
					"Jabatan",
					"Gaji",
					"",
					"",
					"Tunjangan",
					"",
					"",
					"Jumlah",
					"Jumlah Kotor",
					"Jumlah Hadir",
					"Jumlah Penerimaan"
				]);

				data.push([
					"",
					"",
					"",
					"Masa Kerja",
					"Pend. Terakhir",
					"Gaji Pokok",
					"Struktural",
					"Pendidikan",
					"Wali Kelas",
					"",
					"",
					"",
					""
				]);

				const tableRows = document.querySelectorAll("#data_penerimaan_honorarium tbody tr");
				if (tableRows.length === 0) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}

				function toNumber(value) {
					if (value === '' || value == null || value === '-') {
						return 0;
					}

					return Number(
						value.toString()
							.replace(/\./g, '')
							.replace(/,/g, '')
							.trim()
					) || 0;
				}

				function excelValue(value) {
					let num = toNumber(value);
					return num === 0 ? 0 : num;
				}

				let total_gaji_pokok = 0;
				let total_struktural = 0;
				let total_pendidikan = 0;
				let total_wali_kelas = 0;
				let total_jumlah = 0;
				let total_jumlah_kotor = 0;
				let total_jumlah_hadir = 0;
				let total_jumlah_penerimaan = 0;

				tableRows.forEach(row => {
					const cells = Array.from(row.cells).map(cell => cell.textContent.trim());

					/*
					 * Urutan kolom HTML:
					 * 0 No
					 * 1 Nama
					 * 2 Jabatan
					 * 3 Masa Kerja
					 * 4 Pend. Terakhir
					 * 5 Gaji Pokok
					 * 6 Struktural
					 * 7 Pendidikan
					 * 8 Wali Kelas
					 * 9 Jumlah
					 * 10 Jumlah Kotor
					 * 11 Jumlah Hadir
					 * 12 Jumlah Penerimaan
					 */

					if (cells.length < 13) {
						return;
					}

					let no = cells[0];
					let nama = cells[1];
					let jabatan = cells[2];
					let masa_kerja = cells[3];
					let pendidikan_terakhir = cells[4];

					let gaji_pokok = toNumber(cells[5]);
					let struktural = toNumber(cells[6]);
					let pendidikan = toNumber(cells[7]);
					let wali_kelas = toNumber(cells[8]);
					let jumlah = toNumber(cells[9]);
					let jumlah_kotor = toNumber(cells[10]);
					let jumlah_hadir = toNumber(cells[11]);
					let jumlah_penerimaan = toNumber(cells[12]);

					total_gaji_pokok += gaji_pokok;
					total_struktural += struktural;
					total_pendidikan += pendidikan;
					total_wali_kelas += wali_kelas;
					total_jumlah += jumlah;
					total_jumlah_kotor += jumlah_kotor;
					total_jumlah_hadir += jumlah_hadir;
					total_jumlah_penerimaan += jumlah_penerimaan;

					data.push([
						no,
						nama,
						jabatan,
						masa_kerja,
						pendidikan_terakhir,
						gaji_pokok,
						struktural,
						pendidikan,
						wali_kelas,
						jumlah,
						jumlah_kotor,
						jumlah_hadir,
						jumlah_penerimaan
					]);
				});

				data.push([
					"",
					"JUMLAH",
					"",
					"",
					"",
					total_gaji_pokok,
					total_struktural,
					total_pendidikan,
					total_wali_kelas,
					total_jumlah,
					total_jumlah_kotor,
					total_jumlah_hadir,
					total_jumlah_penerimaan
				]);

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				const range = XLSX.utils.decode_range(worksheet['!ref']);

				/*
				 * Merge title dan header group.
				 */
				worksheet["!merges"] = [
					// Judul
					{ s: { r: 0, c: 0 }, e: { r: 0, c: 12 } },
					{ s: { r: 1, c: 0 }, e: { r: 1, c: 12 } },
					{ s: { r: 2, c: 0 }, e: { r: 2, c: 12 } },

					// Header rowspan
					{ s: { r: 4, c: 0 }, e: { r: 5, c: 0 } }, // No
					{ s: { r: 4, c: 1 }, e: { r: 5, c: 1 } }, // Nama
					{ s: { r: 4, c: 2 }, e: { r: 5, c: 2 } }, // Jabatan

					// Header colspan
					{ s: { r: 4, c: 3 }, e: { r: 4, c: 5 } }, // Gaji
					{ s: { r: 4, c: 6 }, e: { r: 4, c: 8 } }, // Tunjangan

					// Header rowspan akhir
					{ s: { r: 4, c: 9 }, e: { r: 5, c: 9 } },   // Jumlah
					{ s: { r: 4, c: 10 }, e: { r: 5, c: 10 } }, // Jumlah Kotor
					{ s: { r: 4, c: 11 }, e: { r: 5, c: 11 } }, // Jumlah Hadir
					{ s: { r: 4, c: 12 }, e: { r: 5, c: 12 } }  // Jumlah Penerimaan
				];

				/*
				 * Format angka mulai kolom Gaji Pokok sampai Jumlah Penerimaan.
				 * Kolom:
				 * 5 = Gaji Pokok
				 * 6 = Struktural
				 * 7 = Pendidikan
				 * 8 = Wali Kelas
				 * 9 = Jumlah
				 * 10 = Jumlah Kotor
				 * 11 = Jumlah Hadir
				 * 12 = Jumlah Penerimaan
				 */
				for (let R = 6; R <= range.e.r; R++) {
					for (let C = 5; C <= 12; C++) {
						let address = XLSX.utils.encode_cell({ r: R, c: C });

						if (worksheet[address]) {
							worksheet[address].z = '#,##0';
						}
					}
				}

				/*
				 * Lebar kolom.
				 */
				worksheet["!cols"] = [
					{ wch: 5 },   // No
					{ wch: 35 },  // Nama
					{ wch: 25 },  // Jabatan
					{ wch: 12 },  // Masa Kerja
					{ wch: 15 },  // Pend. Terakhir
					{ wch: 18 },  // Gaji Pokok
					{ wch: 18 },  // Struktural
					{ wch: 18 },  // Pendidikan
					{ wch: 18 },  // Wali Kelas
					{ wch: 18 },  // Jumlah
					{ wch: 18 },  // Jumlah Kotor
					{ wch: 14 },  // Jumlah Hadir
					{ wch: 20 }   // Jumlah Penerimaan
				];

				/*
				 * Style sederhana.
				 * Catatan: style hanya bekerja kalau library XLSX yang bos pakai mendukung cell style.
				 */
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
						const cell = worksheet[cell_address];

						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = {
								wrapText: true,
								vertical: "center",
								horizontal: R <= 5 ? "center" : undefined
							};
						}
					}
				}
				const workbook = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan Penerimaan Honorarium");
				XLSX.writeFile(workbook, `laporan_penerimaan_honorarium_bulan_${getNamaBulan(bulan)}_${tahun}.xlsx`);

				$('#btn_print_laporan_excel').attr('disabled', false);
				$('#btn_print_laporan_excel').html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 2500);
		} else if (cek_btn === 'resume_tanggal_kegiatan' || cek_btn === 'Karyawan') {
			const filter = document.querySelector('input[name="filter"]:checked')?.value;

			// Helper buat set header laporan & nama file
			let headerTitle = "Resume Tanggal Jurnal Kegiatan - Belum Diisi";
			let subTitle = "";
			let fileName = "resume_tanggal_kegiatan_belum_diisi";

			if (filter === 'tanggal') {
				const tanggal_dari = document.querySelector('input[name="dari_tanggal"]')?.value || '';
				const tanggal_sampai = document.querySelector('input[name="sampai_tanggal"]')?.value || '';
				if (!tanggal_dari || !tanggal_sampai) {
					alert("Pastikan tanggal sudah dipilih.");
					return;
				}
				subTitle = `Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`;
				fileName += `_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}`;
			} else if (filter === 'bulan') {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';
				if (!bulan || !tahun) {
					alert("Pastikan bulan dan tahun dipilih.");
					return;
				}
				subTitle = `Bulan: ${getNamaBulan(bulan)} ${tahun}`;
				fileName += `_bulan_${getNamaBulan(bulan)}_${tahun}`;
			} else {
				const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				if (!tahun) {
					alert("Pastikan tahun dipilih.");
					return;
				}
				subTitle = `Tahun: ${tahun}`;
				fileName += `_tahun_${tahun}`;
			}

			setTimeout(() => {
				const tableRows = document.querySelectorAll("#data_resume_tanggal_kegiatan tbody tr");
				if (!tableRows.length) {
					alert("Tidak ada data untuk diekspor.");
					return;
				}

				let data = [];
				// Header laporan
				data.push([headerTitle]);
				if (subTitle) data.push([subTitle]);
				data.push([]);
				data.push(["No", "Nama Pegawai", "Tanggal Belum Diisi", "Semester", "Periode"]);

				// Variabel untuk tracking merge
				let lastNo = "";
				let lastNama = "";
				let lastSemester = "";
				let lastPeriode = "";

				let currentNo = "";
				let currentNama = "";
				let currentSemester = "";
				let currentPeriode = "";

				let spanStartIndex = null;              // index baris awal group (di worksheet)
				let currentRowIndex = data.length;      // row index Excel saat ini (0-based pada array data)
				let mergeInstructions = [];

				// Helper escape (kalau mau aman dari karakter spesial)
				const getText = (cell) => (cell?.textContent || "").trim();

				tableRows.forEach((row, idx) => {
					// Untuk tabel ini total kolom = 5 pada baris pertama group:
					// [No, Nama, Tanggal, Semester, Periode]
					// Baris berikutnya untuk group yang sama biasanya hanya kolom Tanggal → cells.length < 5
					const cells = Array.from(row.cells).map(td => getText(td));
					const isRowMerged = row.cells.length < 5;

					let finalRow = [];

					if (isRowMerged) {
						// Baris kelanjutan dari group yang sama → pakai lastNo, lastNama, lastSemester, lastPeriode
						finalRow.push(lastNo);
						finalRow.push(lastNama);
						finalRow.push(cells[0] || ""); // kolom Tanggal
						finalRow.push(lastSemester);
						finalRow.push(lastPeriode);
					} else {
						// Baris pertama untuk group baru
						lastNo = cells[0] || "";
						lastNama = cells[1] || "";
						lastSemester = cells[3] || "";
						lastPeriode = cells[4] || "";

						finalRow = cells; // ["No","Nama","Tanggal","Semester","Periode"]

						const isNewGroup =
							currentNo !== lastNo ||
							currentNama !== lastNama ||
							currentSemester !== lastSemester ||
							currentPeriode !== lastPeriode;

						// Tutup merge group sebelumnya (kalau panjangnya > 1 baris)
						if (isNewGroup && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
							// Merge kolom No (c=0), Nama (c=1), Semester (c=3), Periode (c=4)
							mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
							mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
							mergeInstructions.push({ s: { r: spanStartIndex, c: 3 }, e: { r: currentRowIndex - 1, c: 3 } });
							mergeInstructions.push({ s: { r: spanStartIndex, c: 4 }, e: { r: currentRowIndex - 1, c: 4 } });
						}

						if (isNewGroup) {
							spanStartIndex = currentRowIndex;
							currentNo = lastNo;
							currentNama = lastNama;
							currentSemester = lastSemester;
							currentPeriode = lastPeriode;
						}
					}

					data.push(finalRow);
					currentRowIndex++;

					// Jika ini baris terakhir, tutup merge terakhir jika perlu
					if (idx === tableRows.length - 1 && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
						mergeInstructions.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 3 }, e: { r: currentRowIndex - 1, c: 3 } });
						mergeInstructions.push({ s: { r: spanStartIndex, c: 4 }, e: { r: currentRowIndex - 1, c: 4 } });
					}
				});

				const worksheet = XLSX.utils.aoa_to_sheet(data);
				worksheet["!merges"] = mergeInstructions;

				// Set lebar kolom
				worksheet["!cols"] = [
					{ wch: 5 },   // No
					{ wch: 28 },  // Nama Pegawai
					{ wch: 22 },  // Tanggal Belum Diisi
					{ wch: 14 },  // Semester
					{ wch: 16 },  // Periode
				];

				// Wrap text & align top
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
				XLSX.utils.book_append_sheet(workbook, worksheet, "Resume Belum Diisi");
				XLSX.writeFile(workbook, `${fileName}.xlsx`);


				$('#btn_print_laporan_excel').attr('disabled', false).html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 500);
		} else if (cek_btn == 'Guru') {
			const filter = document.querySelector('input[name="filter"]:checked')?.value;

			// Header & nama file
			let headerTitle = "Resume Tanggal Jurnal Guru - Belum Diisi";
			let subTitle = "";
			let fileName = "resume_tanggal_jurnal_guru_belum_diisi";

			if (filter === 'tanggal') {
				const tanggal_dari = document.querySelector('input[name="dari_tanggal"]')?.value || '';
				const tanggal_sampai = document.querySelector('input[name="sampai_tanggal"]')?.value || '';
				if (!tanggal_dari || !tanggal_sampai) return alert("Pastikan tanggal sudah dipilih.");
				subTitle = `Tanggal: ${formatTanggal(tanggal_dari)} s/d ${formatTanggal(tanggal_sampai)}`;
				fileName += `_${formatTanggal(tanggal_dari)}_${formatTanggal(tanggal_sampai)}`;
			} else if (filter === 'bulan') {
				const bulan = document.querySelector('select[name="filter_bulan"]')?.value || '';
				const tahun = document.querySelector('select[name="filter_tahun"]')?.value || '';
				if (!bulan || !tahun) return alert("Pastikan bulan dan tahun dipilih.");
				subTitle = `Bulan: ${getNamaBulan(bulan)} ${tahun}`;
				fileName += `_bulan_${getNamaBulan(bulan)}_${tahun}`;
			} else {
				const tahun = document.querySelector('select[name="single_filter_tahun"]')?.value || '';
				if (!tahun) return alert("Pastikan tahun dipilih.");
				subTitle = `Tahun: ${tahun}`;
				fileName += `_tahun_${tahun}`;
			}

			setTimeout(() => {
				// TABEL dengan thead: [No, Nama, Kelas, Mapel, Tanggal, Semester, Periode]
				const tableRows = document.querySelectorAll("#data_resume_tanggal_guru tbody tr");
				if (!tableRows.length) return alert("Tidak ada data untuk diekspor.");

				const txt = (td) => (td?.textContent || '').trim();

				const data = [];
				data.push([headerTitle]);
				if (subTitle) data.push([subTitle]);
				data.push([]);
				data.push(["No", "Nama Guru", "Kelas", "Mapel", "Tanggal Belum Diisi", "Semester", "Periode"]);

				// Merge hanya untuk: No(0), Nama(1), Semester(5), Periode(6)
				let lastNo = "", lastNama = "", lastSemester = "", lastPeriode = "";
				let currentNo = "", currentNama = "", currentSemester = "", currentPeriode = "";
				let spanStartIndex = null;
				let currentRowIndex = data.length;
				const merges = [];

				tableRows.forEach((row, idx) => {
					const cells = Array.from(row.cells).map(txt);
					// Baris awal grup = 7 sel (No, Nama, Kelas, Mapel, Tanggal, Semester, Periode)
					// Baris lanjutan = 3 sel (Kelas, Mapel, Tanggal) karena No, Nama, Semester, Periode di-rowspan di HTML
					const isContinuation = row.cells.length < 7;

					let out = [];

					if (isContinuation) {
						const kelas = cells[0] || "";
						const mapel = cells[1] || "";
						const tanggal = cells[2] || "";
						out = [lastNo, lastNama, kelas, mapel, tanggal, lastSemester, lastPeriode];
					} else {
						lastNo = cells[0] || "";
						lastNama = cells[1] || "";
						const kelas = cells[2] || "";
						const mapel = cells[3] || "";
						const tanggal = cells[4] || "";
						lastSemester = cells[5] || "";
						lastPeriode = cells[6] || "";

						out = [lastNo, lastNama, kelas, mapel, tanggal, lastSemester, lastPeriode];

						const isNewGroup =
							currentNo !== lastNo ||
							currentNama !== lastNama ||
							currentSemester !== lastSemester ||
							currentPeriode !== lastPeriode;

						// Tutup merge grup sebelumnya (kalau ada >1 baris)
						if (isNewGroup && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
							merges.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } }); // No
							merges.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } }); // Nama
							merges.push({ s: { r: spanStartIndex, c: 5 }, e: { r: currentRowIndex - 1, c: 5 } }); // Semester
							merges.push({ s: { r: spanStartIndex, c: 6 }, e: { r: currentRowIndex - 1, c: 6 } }); // Periode
						}

						if (isNewGroup) {
							spanStartIndex = currentRowIndex;
							currentNo = lastNo;
							currentNama = lastNama;
							currentSemester = lastSemester;
							currentPeriode = lastPeriode;
						}
					}

					data.push(out);
					currentRowIndex++;

					// Tutup grup terakhir bila perlu
					if (idx === tableRows.length - 1 && spanStartIndex !== null && currentRowIndex > spanStartIndex + 1) {
						merges.push({ s: { r: spanStartIndex, c: 0 }, e: { r: currentRowIndex - 1, c: 0 } });
						merges.push({ s: { r: spanStartIndex, c: 1 }, e: { r: currentRowIndex - 1, c: 1 } });
						merges.push({ s: { r: spanStartIndex, c: 5 }, e: { r: currentRowIndex - 1, c: 5 } });
						merges.push({ s: { r: spanStartIndex, c: 6 }, e: { r: currentRowIndex - 1, c: 6 } });
					}
				});

				const ws = XLSX.utils.aoa_to_sheet(data);
				ws["!merges"] = merges;

				ws["!cols"] = [
					{ wch: 5 },   // No
					{ wch: 26 },  // Nama
					{ wch: 22 },  // Kelas
					{ wch: 18 },  // Mapel
					{ wch: 22 },  // Tanggal
					{ wch: 12 },  // Semester
					{ wch: 14 },  // Periode
				];

				// Wrap & align
				const range = XLSX.utils.decode_range(ws['!ref']);
				for (let R = range.s.r; R <= range.e.r; ++R) {
					for (let C = range.s.c; C <= range.e.c; ++C) {
						const addr = XLSX.utils.encode_cell({ r: R, c: C });
						const cell = ws[addr];
						if (cell) {
							cell.s = cell.s || {};
							cell.s.alignment = { wrapText: true, vertical: "top" };
						}
					}
				}

				const wb = XLSX.utils.book_new();
				XLSX.utils.book_append_sheet(wb, ws, "Resume Belum Diisi");
				XLSX.writeFile(wb, `${fileName}.xlsx`);

				$('#btn_print_laporan_excel')
					.attr('disabled', false)
					.html('<i class="fa fa-file-excel me-1"></i> Excel');
			}, 400);
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