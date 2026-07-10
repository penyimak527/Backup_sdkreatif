<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center">
		<h4 class="header-title">Laporan Kegiatan Praktik Kerja Lapangan</h4>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-3">
				<div class="input-group flex-nowrap">
					<span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
					<input type="text" class="form-control" name="tanggal_dari" id="datepicker"
						placeholder="Tanggal Dari">
				</div>
			</div>
			<div class="col-md-3">
				<div class="input-group flex-nowrap">
					<span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
					<input type="text" class="form-control" name="tanggal_sampai" id="datepicker"
						placeholder="Tanggal Sampai">
				</div>
			</div>
			<div class="col-md-2">
				<button type="button" class="btn btn-primary" onclick="loadKegiatanPKL()"><i
						class="ri-printer-line"></i></button>
			</div>
		</div>

		</ol>
	</div>
</div>


<script>
	$(document).ready(function () {
		$('[id^="btn-simpan-"]').click(function () {
			let index = $(this).attr('id').replace('btn-simpan-', '');
			let formId = '#form-tambah-' + index;
			$.ajax({
				url: '<?php echo base_url(); ?>siswa/kegiatan_pkl/tambah',
				data: $(formId).serialize(),
				type: "POST",
				dataType: "json",
				success: function (result) {
					if (result.status == 'success') {
						Swal.fire({
							title: 'Berhasil',
							text: result.message,
							icon: 'success',
							confirmButtonText: 'Ok',
							buttonColor: '#3085d6',
						}).then((result) => {
							if (result.isConfirmed) {
								location.reload();
							}
						});

					}
				}
			})
		})

	});

	function tambahKegiatan(index, tanggal) {
		$('#data-kegiatan-' + index).modal('show')
		$('#showTanggal').val(tanggal)
	}

</script>
