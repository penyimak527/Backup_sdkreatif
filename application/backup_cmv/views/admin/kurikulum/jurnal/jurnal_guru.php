<style>
	.dropify-wrapper .dropify-message span {
		font-size: 14px;
		color: #333;
		/* bisa juga ubah warna */
		font-weight: 500;
		/* atau bold */
	}
</style>

<div id="jurnal_guru">

</div>



<script>
	$(document).ready(function () {
		jurnal_guru_result();
	})

	function jurnal_guru_result() {

		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/jurnal_guru_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {

				var table = '';
				Object.entries(data).forEach(([tanggal, items]) => {
					let [y, m, d] = tanggal.split('-');
					let tanggalConverted = `${d}-${m}-${y}`;
					table += `
				<div class="card mb-3">
					<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
						<h4 class="header-title mb-0">Tanggal: ${tanggalConverted}</h4>
					</div>
					<div class="card-body">
						`;
					items.forEach(item => {
						table += `
							<div class="card-mapel">
								<p class="keterangan-hari">
									<span>Hari : ${item.hari}</span>
									<span>Semester (${item.semester})</span>
								</p>
								<div class="keterangan-mapel">
									<div class="keterangan-mapel-kiri">
										<h5 class="judul-mapel">${item.mapel} Kelas ${item.kelas} ${item.kode_kelas}</h5>
										<p class="keterangan-jam-mapel">Jam Pelajaran : ${item.jam_pelajaran_awal} - ${item.jam_pelajaran_akhir}</p>
									</div>
									<div class="keterangan-mapel-kanan">
										<a href="<?= base_url(); ?>admin/kurikulum/jurnal/jurnal_guru/jurnal_mengajar/${item.id}/${tanggalConverted}" type="button" class="btn btn-sm btn-info" style="width:100%;"><i class="ri-error-warning-line"></i> Jurnal Kehadiran</a>
									</div>
								</div>
							</div>`;
					});

					table += `
						</div>
					</div>
				</div>`;
				});
				$('#jurnal_guru').html(table);
			}
		});
	}

	function editguru(detail) {
		$('#modal-edit').modal('show');
		var item = JSON.parse(atob(detail));
		$('#id_guru').val(item.id);
		$('#modal-edit input[name="nama_guru"]').val(item.nama_guru);
		$('#modal-edit input[name="nip"]').val(item.nip);
		$('#modal-edit select[name="jk"]').html(`
		<option value="Laki-Laki"${item.jk == 'Laki-Laki' ? 'selected' : ''}>Laki-Laki</option>
		<option value="Perempuan"${item.jk == 'Perempuan' ? 'selected' : ''}>Perempuan</option>
		`);
		$('#modal-edit input[name="tempat_lahir"]').val(item.tempat_lahir);
		$('#modal-edit input[name="tanggal_lahir"]')[0]._flatpickr.setDate(item.tanggal_lahir, true);
		$('#modal-edit input[name="no_telp"]').val(item.no_telp);

	}

	function paging($selector) {
		var jumlah_tampil = '10';

		if (typeof $selector == 'undefined') {
			$selector = $("#table_guru tbody tr");
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