<div class="card">
	<div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
		<h4 class="header-title">Data <?= $title; ?></h4>

	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<div class="input-group">
						<input type="text" class="form-control" id="cari-guru" placeholder="Cari guru"
							aria-describedby="inputGroupPrepend" onkeyup="guru()">
						<span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
								class="ri-search-line"></i></span>
					</div>
				</div>
			</div>
		</div>
		<div class="table-responsive-sm">
			<table class="table table-bordered m-b-0" id="table_guru">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th>NIP</th>
						<th>Nama Guru</th>
						<th>Jenis Kelamin</th>
						<th>Tempat, Tanggal Lahir</th>
						<th>No Telepon</th>
						<th>Mapel</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
		<div
			class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2 mt-2">
			<ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination"></ul>
			<div class="d-flex align-items-center gap-2">
				<label for="dt-length-0" class="mb-0">Tampilkan</label>
				<select class="form-select form-select-sm" id="dt-length-0">
					<option value="10" selected>10</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="100">100</option>
				</select>
				<span>entri</span>
			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function () {
		guru()
		$('#dt-length-0').on('change', function () {
			const jumlah = parseInt($(this).val());
			paging($('#table_guru tbody tr'), jumlah);
		});
	})

	function guru() {
		var search = $("#cari-guru").val();
		$.ajax({
			url: '<?= base_url('admin/kurikulum/guru/guru_result'); ?>',
			type: 'POST',
			data: {
				search
			},
			dataType: 'JSON',
			success: function (data) {
				$('#guru').empty();

				var no = 1;
				var table = '';
				if (data.length == 0) {
					table += `
					<tr>
						<td colspan="4" style="text-align: center;">Tidak ada data</td>
					</tr>
				`;
				} else {

					data.forEach(function (item) {
						let detail = btoa(JSON.stringify(item));

						table += `
						<tr>
							<td width="5%" style="text-align: center;"> ${no++}</td>
							<td>${item.nbm}</td> 
							<td>${item.nama_guru}</td> 
							<td>${item.jk}</td> 
							<td>${item.tempat_lahir}, ${item.tanggal_lahir}</td> 
							<td>${item.no_telp}</td> 
							<td> 
							 ${item.mapel.map(m => `<span class="badge bg-primary me-1" style="font-size: 12px;">${m.mapel}</span>`).join('')}
							</td>
						</tr>
						`;
					});
				}
				$('#table_guru tbody').html(table);
				let jumlah_awal = parseInt($('#dt-length-0').val());
				paging($('#table_guru tbody tr'), jumlah_awal);
			}
		});
	}



	function paging($selector, jumlah_tampil = 10) {
		if (typeof $selector === 'undefined') {
			$selector = $("#table_guru tbody tr");
		}

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


</script>