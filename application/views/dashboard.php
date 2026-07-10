<?php if ($this->session->userdata('admin')['level'] != 'Bendahara'): ?>
<div class="row row-cols-1 row-cols-md-3 g-4">
	<div class="col">
		<div class="card">
			<div class="d-flex card-header justify-content-between align-items-center">
				<div>
					<h4 class="header-title"> Mapel</h4>
				</div>

			</div>

			<div class="card-body pt-0">
				<div class="d-flex align-items-end gap-2 justify-content-between">
					<div class="text-end flex-shrink-0">
						<div id="chart-one" data-colors="#ff5b5b,#F6F7FB"></div>
					</div>
					<div class="text-end">
						<h3 class="fw-semibold"><?= $total_mapel ?></h3>
						<p class="text-muted mb-0">Total Mapel</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="d-flex card-header justify-content-between align-items-center">
				<div>
					<h4 class="header-title">Kelas</h4>
				</div>

			</div>

			<div class="card-body pt-0">
				<div class="d-flex align-items-end gap-2 justify-content-between">
					<div class="text-end flex-shrink-0">
						<div id="chart-two" data-colors="#f9c851,#F6F7FB"></div>
					</div>
					<div class="text-end">
						<h3 class="fw-semibold"><?= $total_kelas ?></h3>
						<p class="text-muted mb-0">Total Kelas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card">
			<div class="d-flex card-header justify-content-between align-items-center">
				<div>
					<h4 class="header-title">Pegawai</h4>
				</div>

			</div>

			<div class="card-body pt-0">
				<div class="d-flex align-items-end gap-2 justify-content-between">
					<div class="text-end flex-shrink-0">
						<div id="chart-three" data-colors="#ff5b5b,#F6F7FB"></div>
					</div>
					<div class="text-end">
						<h3 class="fw-semibold"><?= $total_pegawai ?></h3>
						<p class="text-muted mb-0">Total Pegawai</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif;?>
<?php if ($this->session->userdata('admin')['level'] == 'Guru'): ?>
	<div class="card">
		<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
			<h4 class="header-title">Jadwal Mengajar Hari Ini</h4>
		</div>
		<div class="card-body">

			<div id="card-container"></div>
		</div>
	</div>
<?php endif; ?>
<?php if($this->session->userdata('admin')['level'] == 'Bendahara'):?>
<div class="card">
	<div class="card-header border-bottom border-dashed">
		<div class="row">
			<div class="col-md-4"><h4 class="header-title">Saldo</h4></div>
		<div class="col-md-3">
				<div class="input-group flex-nowrap">
					<span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
					<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Dari"
						aria-describedby="inputGroupPrepend" name="tanggal_awal_saldo">
				</div>
			</div>
<div class="col-md-3">
				<div class="input-group flex-nowrap">
					<span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
					<input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Sampai"
						aria-describedby="inputGroupPrepend" name="tanggal_akhir_saldo">
				</div>
			</div>
				<div class="col-md-2">
					<button class="btn btn-primary w-100" onclick="saldo()">
						Preview
					</button>
				</div>
		</div>
	</div>
	<div class="card-body">
		<div id="card-container-saldo"></div>
	</div>
</div>
<div class="col-md-12">
	<div class="card">
		<div class="card-header border-bottom">
			<div class="row">
				<div class="col-md-4">
					<h5>Grafik Pemasukan vs Pengeluaran</h5>
				</div>

				<div class="col-md-4">
					<select id="tahun_perbandingan" class="form-control">
						<?php
						$now = date('Y');
						for ($i = 2025; $i <= $now; $i++) {
							$selected = ($i == $now) ? 'selected' : '';
							echo "<option value='$i' $selected>$i</option>";
						}
						?>
					</select>
				</div>

				<div class="col-md-4">
					<button class="btn btn-primary w-100" onclick="grafikPerbandingan()">
						Preview
					</button>
				</div>
			</div>
		</div>

		<div class="card-body">
			<div id="chart-perbandingan"></div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="card">
			<div class="card-header border-bottom border-dashed ">
				<div class="row">
					<div class="col-md-4">
						<h5 class="header-title">Top Pengeluaran Terbesar</h5>
					</div>
					<div class="col-md-4">
						<select id="bulan_top" class="form-control">
							<option value="semua">Semua</option>
							<?php
							$bulan_now = date('m');
							$bulan = array(
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
								"Desember"
							);
							$jlh_bln = count($bulan);
							$no = 0;
							for ($c = 0; $c < $jlh_bln; $c += 1) {
								$no++;
								$no_pas = sprintf("%02s", $no);
								$selected = ($no_pas == $bulan_now) ? 'selected' : '';
								echo '<option value="' . $no_pas . '" ' . $selected . '> ' . $bulan[$c] . ' </option>';
							} ?>
						</select>
					</div>
					<div class="col-md-4">
						<select class="form-control" id="tahun_top">
							<?php
							$now = date('Y');
							for ($a = 2025; $a <= $now; $a++) {
								$selected = ($a == $now) ? 'selected' : '';
								echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div id="chart-top"></div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="row">
			<!-- <div class="col-md-4"> -->
			<div class="card shadow-sm h-100">
				<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
					<h5 class="mb-0 header-title">Notifikasi Keuangan</h5>
					<span class="badge bg-danger" id="jumlah-notif">0</span>
				</div>
				<div class="card-body p-2" style="max-height:300px;overflow-y:auto;">
					<div id="notif-keuangan">
						<div class="text-center text-muted p-3">
							Memuat data...
						</div>
					</div>
				</div>
			</div>
			<!-- </div> -->
		</div>
	</div>
</div>


<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Perbandingan Rencana Pemasukan</h4>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-4">
				<select class="form-control" id="tahun_rencana_pemasukan">
					<?php
					$now = date('Y');
					for ($a = 2024; $a <= $now; $a++) {
						$selected = ($a == $now) ? 'selected' : '';
						echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
					}
					?>
				</select>
			</div>
			<div class="col-md-4">
				<button class="btn btn-primary w-100" onclick="grafikRencanaPemasukan()">
					Preview
				</button>
			</div>
		</div>
		<div id="chart-rencana-pemasukan"></div>
	</div>
</div>
<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Perbandingan Rencana Pengeluaran</h4>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-4">
				<select class="form-control" id="tahun_rencana_pengeluaran">
					<?php
					$now = date('Y');
					for ($a = 2024; $a <= $now; $a++) {
						$selected = ($a == $now) ? 'selected' : '';
						echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
					}
					?>
				</select>
			</div>
			<div class="col-md-4">
				<button class="btn btn-primary w-100" onclick="grafikRencanaPengeluaran()">
					Preview
				</button>
			</div>
		</div>
		<div id="chart-rencana-pengeluaran"></div>
	</div>
</div>
<?php endif;?>
<script>
	let topChart = null;
	$(document).ready(function () {
		jadwal_hari_ini();
		setTimeout(function () {
			grafikPerbandingan();
		}, 300);
		setTimeout(function () {
			grafikRencanaPemasukan();
			grafikRencanaPengeluaran();
		}, 300);

		top_pengeluaran_terbesar();
		notifKeuangan();
		$('#bulan_top,#tahun_top').change(function () { top_pengeluaran_terbesar(); });
if ($('input[name="tanggal_awal_saldo"]').length) {
	flatpickr('input[name="tanggal_awal_saldo"]', {
		dateFormat: "d-m-Y",
		defaultDate: "<?= date('01-m-Y') ?>",
		onChange: function () {
			let tanggal_awal = $('input[name="tanggal_awal_saldo"]').val();
			let tanggal_akhir = $('input[name="tanggal_akhir_saldo"]').val();

			if (tanggal_awal != '' && tanggal_akhir != '') {
				saldo();
			}
		}
	});
}

if ($('input[name="tanggal_akhir_saldo"]').length) {
	flatpickr('input[name="tanggal_akhir_saldo"]', {
		dateFormat: "d-m-Y",
		defaultDate: "<?= date('t-m-Y') ?>",
		onChange: function () {
			let tanggal_awal = $('input[name="tanggal_awal_saldo"]').val();
			let tanggal_akhir = $('input[name="tanggal_akhir_saldo"]').val();

			if (tanggal_awal != '' && tanggal_akhir != '') {
				saldo();
			}
		}
	});
}
		saldo();
	})

	function jadwal_hari_ini() {
		$.ajax({
			url: '<?= base_url('dashboard/jadwal_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {
				console.log(data)
				var tanggal = '<?= date('d-m-Y') ?>'
				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="7" style="text-align: center;">Tidak ada jadwal </td>
					</tr>
				`;
				} else {
					data.forEach(function (item) {
						// let detail = btoa(JSON.stringify(item));
						// table += `
						// <tr> 
						// 	<td>${item.mapel ?? ''}</td>
						// 	<td>${item.jam_pelajaran_awal ?? ''}- ${item.jam_pelajaran_akhir ?? ''}</td>
						// 	<td>${item.nama_kelas ?? ''}</td> 

						// 	<td>
						// 		<a href="<?= base_url(); ?>admin/kurikulum/jurnal/jurnal_guru/jurnal_mengajar/${item.id_jadwal}/${tanggal}" type="button" class="btn btn-sm btn-info" style="width:100%;"><i class="ri-error-warning-line"></i> Jurnal Kehadiran</a>
						// 	</td>
						// </tr>
						// `;
						table += `
							<div class="card-mapel">
								<p class="keterangan-hari">
									<span>Hari : ${item.hari}</span>
									<span>Semester (${item.semester})</span>
								</p>
								<div class="keterangan-mapel">
									<div class="keterangan-mapel-kiri">
										<h5 class="judul-mapel">${item.mapel}</h5>
										<h6 class="judul-mapel"> Kelas ${item.kelas} ${item.kode_kelas}</h6>
										<p class="keterangan-jam-mapel">Jam Pelajaran : ${item.jam_pelajaran_awal} - ${item.jam_pelajaran_akhir}</p>
									</div>
									<div class="keterangan-mapel-kanan">
										<a href="<?= base_url(); ?>admin/kurikulum/jurnal/jurnal_guru/jurnal_mengajar/${item.id_jadwal}/${tanggal}" type="button" class="btn btn-sm btn-info" style="width:100%;"><i class="ri-error-warning-line"></i> Jurnal Kehadiran</a>
									</div>
														</div>
													</div>`;
					});
				}
				$('#card-container').html(table);
			}
		});
	}
	function saldo() {
		let tanggal_awal = $('input[name="tanggal_awal_saldo"]').val();
		let tanggal_akhir = $('input[name="tanggal_akhir_saldo"]').val();
		$.ajax({
			url: '<?= base_url('dashboard/saldo_result'); ?>',
			type: 'POST',
			data: {
				tanggal_awal: tanggal_awal,
				tanggal_akhir: tanggal_akhir
			},
			dataType: 'JSON',
			success: function (data) {
				var table = `
				<div class="row">
					<div class="col-md-12 mb-3">
						<div class="card shadow-sm border">
							<div class="card-body p-2">
								<div class="row">
									<div class="col-md-4">
										<small>Awal Transaksi</small>
										<div class="fw-semibold">${data.tanggal_awal}</div>
									</div>
									<div class="col-md-4">
										<small>Akhir Transaksi</small>
										<div class="fw-semibold">${data.tanggal_akhir}</div>
									</div>
									<div class="col-md-4">
										<small>Saldo Awal</small>
										<div class="fw-semibold">Rp ${NumberToMoney(data.saldo_awal)}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card border-success shadow-sm">
							<div class="card-body p-2 text-center">
								<small class="text-muted">PEMASUKAN</small>
								<h5 class="fw-bold text-success mb-0">Rp ${NumberToMoney(data.pemasukan)}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card border-danger shadow-sm">
							<div class="card-body p-2 text-center">
								<small class="text-muted">PENGELUARAN</small>
								<h5 class="fw-bold text-danger mb-0">Rp ${NumberToMoney(data.pengeluaran)}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card border-primary shadow-sm">
							<div class="card-body p-2 text-center">
								<small class="text-muted">
									SALDO AKHIR
								</small>
								<h5 class="fw-bold text-primary mb-0">Rp ${NumberToMoney(data.saldo_akhir)}</h5>
							</div>
						</div>
					</div>
				</div>`;
				$('#card-container-saldo').html(table);
			}
		});
	}
	function top_pengeluaran_terbesar() {
		let bulan = $('#bulan_top').val();
		let tahun = $('#tahun_top').val();
		$.ajax({
			url: '<?= base_url("dashboard/pengeluaran_terbesar_result") ?>',
			type: 'POST',
			data: {
				bulan: bulan,
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (data) {
				renderTop(data);
			}
		});
	}
	function grafikPerbandingan() {
		let tahun = $("#tahun_perbandingan").val();
		$.ajax({
			url: '<?= base_url("dashboard/grafik_perbandingan") ?>',
			type: 'POST',
			data: {
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (res) {

				chartPerbandingan(res);

			}
		})

	}

	function grafikRencanaPemasukan() {
		let tahun = $('#tahun_rencana_pemasukan').val();
		$.ajax({
			url: '<?= base_url("dashboard/grafik_rencana_pemasukan_result") ?>',
			type: 'POST',
			data: {
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (data) {
				chartRencanaPemasukan(data);
			}
		});
	}

	function grafikRencanaPengeluaran() {
		let tahun = $('#tahun_rencana_pengeluaran').val();
		$.ajax({
			url: '<?= base_url("dashboard/grafik_rencana_pengeluaran_result") ?>',
			type: 'POST',
			data: {
				tahun: tahun
			},
			dataType: 'JSON',
			success: function (data) {
				chartRencanaPengeluaran(data);
			}
		});
	}
	document.addEventListener("DOMContentLoaded", function () {
		const defaultColors = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00"];

		function renderRadialChart(selector, seriesValue) {
			const dataColors = document.querySelector(selector).dataset.colors;
			const colors = dataColors ? dataColors.split(",") : defaultColors;

			const options = {
				series: [seriesValue],
				chart: {
					type: "radialBar",
					height: 81,
					width: 81,
					sparkline: {
						enabled: false
					}
				},
				plotOptions: {
					radialBar: {
						offsetY: 0,
						hollow: {
							margin: 0,
							size: "50%"
						},
						dataLabels: {
							name: {
								show: false
							},
							value: {
								offsetY: 5,
								fontSize: "14px",
								fontWeight: "600",
								formatter: function (val) {
									return val;
								}
							}
						}
					}
				},
				grid: {
					padding: {
						top: -18,
						bottom: -20,
						left: -20,
						right: -20
					}
				},
				colors: colors
			};

			new ApexCharts(document.querySelector(selector), options).render();
		}

		// Panggil render untuk tiap chart
		renderRadialChart("#chart-one", <?= $total_mapel ?>);
		renderRadialChart("#chart-two", <?= $total_kelas ?>);
		renderRadialChart("#chart-three", <?= $total_pegawai ?>);
		renderRadialChart("#chart-four", 75);
	});


	let chartGabungan = null;
	function chartPerbandingan(data) {
		let bulanNama = [
			"Januari", "Februari", "Maret", "April",
			"Mei", "Juni", "Juli", "Agustus",
			"September", "Oktober", "November", "Desember"
		];

		let pemasukan = [];
		let pengeluaran = [];
		data.forEach(item => {

			pemasukan.push(parseFloat(item.pemasukan));
			pengeluaran.push(parseFloat(item.pengeluaran));
		});


		if (chartGabungan) {
			chartGabungan.destroy();
		}


		let options = {
			series: [
				{
					name: 'Pemasukan',
					data: pemasukan
				},
				{
					name: 'Pengeluaran',
					data: pengeluaran
				}
			],

			chart: {
				type: 'bar',
				height: 400,
				toolbar: {
					show: true
				}
			},

			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					borderRadius: 5
				}
			},

			colors: [
				'#22c55e',
				'#ef4444'
			],

			dataLabels: { enabled: false },
			xaxis: { categories: bulanNama },
			yaxis: {
				labels: {
					formatter: function (val) {
						return 'Rp ' + NumberToMoney(val)
					}
				}
			},

			tooltip: {
				y: {
					formatter: function (val) {
						return 'Rp ' + NumberToMoney(val)
					}
				}
			},

			legend: { position: 'top' }

		};

		chartGabungan = new ApexCharts(
			document.querySelector("#chart-perbandingan"),
			options
		);
		chartGabungan.render();
	}
	function renderTop(data) {
		let label = [];
		let total = [];
		if (data.length === 0) {
			label.push("Tidak ada data");
			total.push(0);
		}
		data.forEach(function (item) {
			label.push(
				item.keterangan
			);
			total.push(
				parseFloat(item.total)
			);
		});

		if (topChart) {
			topChart.destroy();
		}
		topChart = new ApexCharts(
			document.querySelector("#chart-top"),
			{
				series: total,
				labels: label,
				chart: {
					type: 'donut',
					height: 350
				},
				legend: {
					position: 'bottom'
				},
				tooltip: {
					y: {
						formatter: function (value) {
							return 'Rp ' + NumberToMoney(value);
						}
					}
				},
			}
		);
		topChart.render();
	}
	function notifKeuangan() {
		$.ajax({
			url: '<?= base_url("dashboard/notif_keuangan") ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {
				let html = '';
				if (data.length == 0) {
					html = `
				<div class="alert alert-success mb-1 py-2">
					Tidak ada notifikasi
				</div>
				`;

				} else {
					data.forEach(function (item) {
						let icon = '⚠';
						if (item.tipe == 'danger') {
							icon = '⛔';
						}
						html += `
					<div class="alert alert-${item.tipe} mb-2 py-2">
						<div class="fw-bold">
							${icon}
							${item.judul}
						</div>
						<small>${item.pesan}</small>
					</div>`;
					});
				}
				$('#jumlah-notif').html(data.length);
				$('#notif-keuangan').html(html);
			}
		});
	}

	let chartPengeluaran_Rencana = null;

	function chartRencanaPengeluaran(data) {
		let bulan = [];
		let rencana = [];
		let realisasi = [];
		let serapan = [];
		const namaBulan = {
			"01": "Januari",
			"02": "Februari",
			"03": "Maret",
			"04": "April",
			"05": "Mei",
			"06": "Juni",
			"07": "Juli",
			"08": "Agustus",
			"09": "September",
			"10": "Oktober",
			"11": "November",
			"12": "Desember"
		};

		data.forEach(item => {
			bulan.push(namaBulan[item.bulan]);
			rencana.push(parseFloat(item.rencana));
			realisasi.push(parseFloat(item.realisasi));
			serapan.push(parseFloat(item.serapan));
		});
		if (chartPengeluaran_Rencana) {
			chartPengeluaran_Rencana.destroy();
		}

		let options = {
			series: [
				{
					name: 'Rencana',
					data: rencana
				},
				{
					name: 'Realisasi',
					data: realisasi
				}
			],

			chart: {
				type: 'bar',
				height: 350
			},

			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					borderRadius: 5
				}
			},

			colors: [
				'#3b82f6',
				'#ef4444'
			],

			// MATIKAN ANGKA DI BATANG
			dataLabels: { enabled: false },
			xaxis: { categories: bulan },
			yaxis: {
				labels: {
					formatter: function (val) {
						if (val >= 1000000) {
							return (val / 1000000).toFixed(0) + ' jt';
						}
						if (val >= 1000) {
							return (val / 1000).toFixed(0) + ' rb';
						}
						return val;
					}
				}
			},

			tooltip: {
				shared: true,
				intersect: false, // tambahkan ini

				custom: function ({
					dataPointIndex
				}) {
					let persen = serapan[dataPointIndex];
					return `<div style="padding:10px; min-width:200px">
			<b>${bulan[dataPointIndex]}</b>
			<hr>
			Rencana :
			Rp ${NumberToMoney(rencana[dataPointIndex])}
			<br>
			Realisasi :
			Rp ${NumberToMoney(realisasi[dataPointIndex])}
			<br><br>
			<b>
			Serapan:
			${persen}%
			</b>
			</div>`;
				}
			},

			legend: { position: 'top' }
		};

		chartPengeluaran_Rencana = new ApexCharts(document.querySelector("#chart-rencana-pengeluaran"), options);
		chartPengeluaran_Rencana.render();
	}

	let chartPemasukan_Rencana = null;
	function chartRencanaPemasukan(data) {
		let bulan = [];
		let rencana = [];
		let realisasi = [];
		let serapan = [];
		const namaBulan = {
			"01": "Januari",
			"02": "Februari",
			"03": "Maret",
			"04": "April",
			"05": "Mei",
			"06": "Juni",
			"07": "Juli",
			"08": "Agustus",
			"09": "September",
			"10": "Oktober",
			"11": "November",
			"12": "Desember"
		};

		data.forEach(item => {
			bulan.push(namaBulan[item.bulan]);
			rencana.push(parseFloat(item.rencana));
			realisasi.push(parseFloat(item.realisasi));
			serapan.push(parseFloat(item.serapan));
		});
		if (chartPemasukan_Rencana) {
			chartPemasukan_Rencana.destroy();
		}

		let options = {
			series: [
				{
					name: 'Rencana',
					data: rencana
				},
				{
					name: 'Realisasi',
					data: realisasi
				}
			],

			chart: {
				type: 'bar',
				height: 350
			},

			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					borderRadius: 5
				}
			},

			colors: [
				'#3b82f6',
				'#ef4444'
			],

			// MATIKAN ANGKA DI BATANG
			dataLabels: { enabled: false },
			xaxis: { categories: bulan },

			yaxis: {
				labels: {
					formatter: function (val) {
						if (val >= 1000000) {
							return (val / 1000000).toFixed(0) + ' jt';
						}
						if (val >= 1000) {
							return (val / 1000).toFixed(0) + ' rb';
						}
						return val;
					}
				}
			},

			tooltip: {
				shared: true,
				intersect: false, // tambahkan ini
				custom: function ({
					dataPointIndex
				}) {
					let persen =
						serapan[dataPointIndex];
					return `<div style="padding:10px; min-width:200px">
			<b>${bulan[dataPointIndex]}</b>
			<hr>
			Rencana :
			Rp ${NumberToMoney(rencana[dataPointIndex])}
			<br>
			Realisasi :
			Rp ${NumberToMoney(realisasi[dataPointIndex])}
			<br><br>
			<b>
			Serapan:
			${persen}%
			</b>
			</div>`;
				}
			},
			legend: { position: 'top' }
		};
		chartPemasukan_Rencana = new ApexCharts(document.querySelector("#chart-rencana-pemasukan"), options);
		chartPemasukan_Rencana.render();
	}
</script>