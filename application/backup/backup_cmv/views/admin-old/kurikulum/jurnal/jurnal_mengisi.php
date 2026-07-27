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
			<h4 class="header-title"> <?= $title; ?></h4>
			<a href="<?= base_url('admin/kurikulum/jurnal/jurnal_guru'); ?>" class="btn btn-sm btn-outline-danger">
				Kembali
			</a>
		</div>
		<div class="card-body">
			<div class="row">
				<input type="hidden" name="uuid">

				<div class="col-md-12 mb-2">
					<label for="tanggal" class="form-label">Tanggal Mengisi Kegiatan</label>
					<input type="text" name="tanggal_view" class="form-control" disabled
						value="<?= date('d-m-Y', strtotime($tanggal)); ?>">
					<input type="hidden" name="tanggal" class="form-control"
						value="<?= date('d-m-Y', strtotime($tanggal)); ?>">
				</div>
				<div class="col-md-12 mb-2">
					<label for="nama_pegawai" class="form-label">Nama Pegawai</label>
					<input type="text" name="nama_pegawai" class="form-control" disabled>
					<input type="hidden" name="id_pegawai">
				</div>
				<div class="col-md-12 mb-2">
					<label for="kegiatan" class="form-label">Kegiatan</label>
					<textarea type="text" name="kegiatan" class="form-control" placeholder="Kegiatan ..."></textarea>
				</div>


			</div>
		</div>
	</div>
</form>

<script>
	$(document).ready(function () {
		pegawai_result();
		jurnal_siswa_result();
		$("#btn-simpan").click(function () {

			var form = $("#form-tambah")[0];
			var formData = new FormData(form);


			$.ajax({
				url: '<?= base_url('admin/kurikulum/jurnal/jurnal_guru/tambah'); ?>',
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
								window.location.href = "<?= base_url('admin/kurikulum/jurnal/jurnal_guru'); ?>";
							}
						})
					}
				}
			})
		})


		let kode_unik = self.crypto.randomUUID();
		$("#form-tambah input[name='uuid']").val(kode_unik);
	})

	function pegawai_result() {
		var id_jadwal = "<?= $id_jadwal; ?>";
		$.ajax({
			url: '<?= base_url('admin/kurikulum/jurnal/jurnal_kegiatan/pegawai_result'); ?>',
			type: 'POST',
			dataType: 'JSON',
			data: {
				id_jadwal
			},
			success: function (data) {

				console.log(data.nama_guru);
				$('#form-tambah input[name="id_kelas_jadwal_pelajaran"]').val(data.id);
				$('#form-tambah input[name="nama_pegawai"]').val(data.nama_pegawai);
				$('#form-tambah input[name="id_pegawai"]').val(data.id);
			}
		});
	}


</script>




</script>
