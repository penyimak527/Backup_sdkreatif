<style>
	.dropify-wrapper .dropify-message span {
		font-size: 14px;
		color: #333;
		/* bisa juga ubah warna */
		font-weight: 500;
		/* atau bold */
	}
</style>
<?php if ($level == 'Admin'): ?>
	<div id="jadwal-mengajar"></div>
<?php else: ?>
	<div class="card">
		<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
			<h4 class="header-title">Data <?= $title; ?></h4>

		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-sm-12" id="container-mapel">

				</div>
			</div>
		</div>
	</div>

<?php endif; ?>

<script>
	$(document).ready(function () {

		jadwal_mengajar_result();
		jadwal_mengajar_result_guru();
	})

	function jadwal_mengajar_result() {
		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/jadwal_mengajar_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var table = '';
				var no = 1;
				if (data.length == 0) {
					table = `<tr>
						<td colspan="9" style="text-align: center;">Tidak ada data</td>
					</tr>`;
				} else {
					for (const hari in data) {

						table += `
						<div class="card">
						<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
							<h4 class="header-title">${hari}</h4>

						</div>
						<div class="card-body">
					`;

						// Iterasi per jadwal di hari tersebut
						data[hari].forEach(function (item) {
							table += `
							<div class="card-mapel">
								<p class="keterangan-hari">
									<span>Hari : ${item.hari}</span>
									<span>Semester (${item.periode}/${item.semester})</span>
								</p>
								<div class="keterangan-mapel">
									<div class="keterangan-mapel-kiri">
										<h5 class="judul-mapel">${item.mapel} Kelas ${item.kelas} ${item.kode_kelas}</h5>
										<p>${item.nama_guru}</p>
										<p class="keterangan-jam-mapel">Jam Pelajaran : ${item.jam_pelajaran_awal} - ${item.jam_pelajaran_akhir}</p>
									</div>
								</div>
							</div>`;
						});
						table += `
						</div>
					</div>
						`;
					}
				}
				$('#jadwal-mengajar').html(table);
			}
		});
	}
	function jadwal_mengajar_result_guru() {
		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/jadwal_mengajar_result_guru'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var table = '';
				if (data.length == 0) {
					table = `<div class="card-mapel">
								<h5 class="text-center">Data Kosong</h5>
							</div>`;
				} else {
					data.forEach(function (item) {
						table += `<div class="card-mapel">
							<p class="keterangan-hari"> 
								<span>Hari ${item.hari}</span>
								<span>Semester (${item.periode}/${item.semester})</span>
							</p>
							<div class="keterangan-mapel">
								<div class="keterangan-mapel-kiri">
									<h5 class="judul-mapel">${item.mapel} Kelas ${item.kelas} ${item.kode_kelas}</h5>
									<p class="keterangan-jam-mapel">Jam Pelajaran : ${item.jam_pelajaran_awal} - ${item.jam_pelajaran_akhir}</p>
								</div>
							</div>
						</div>`;
					})
				}
				$('#container-mapel').html(table);
			}
		});
	}

	function detailNgajar(detail) {
		$('#modal-edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_guru').val(item.id);
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

	}

	function paging($selector) {
		var jumlah_tampil = '10';

		if (typeof $selector == 'undefined') {
			$selector = $("#table_riwayat_mengajar tbody tr");
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