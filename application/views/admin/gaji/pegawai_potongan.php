<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
                            aria-describedby="inputGroupPrepend" onkeyup="potongan_pegawai()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
             <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="bulan-pegawai-potongan" class=" form-control ">
                            <option value="">Pilih Bulan</option>
                            <?php
                            $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
                            $jlh_bln = count($bulan);
                            $no = 0;
                            for ($c = 0; $c < $jlh_bln; $c += 1) {
                                $no++;
                                $no_pas = sprintf("%02s", $no);
                                echo '<option value="' . $no_pas . '"> ' . $bulan[$c] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="tahun-pegawai-potongan" class=" form-control ">
                            <option value="">Pilih Tahun</option>
                            <?php
                            $now = date('Y');
                            $periode_tahun_selected = '';
                            for ($a = 2025; $a <= $now; $a++) {
                                echo '<option value="' . $a . '">' . $a . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="potongan_pegawai()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div id="data_potongan_pegawai">

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

<div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Tambah <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-tambah">
                    <div class="mb-3">
                        <label for="pegawai" class="form-label">Nama Pegawai</label>
                        <select name="id_pegawai" class="form-control">
                        </select>
                    </div>
                    <div class="row mb-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Bulan</label>
								<select class="form-control" data-width="100%" name="bulan_potongan">
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
										<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Tahun</label>
								<select class="form-control" data-width="100%" name="tahun_potongan">
									<?php
									$now = date('Y');
									for ($a = 2025; $a <= $now; $a++) {
										?>
										<option value="<?php echo $a; ?>">
											<?php echo $a; ?>
										</option>
										<?php
									}?>
								</select>
							</div>
						</div>
					</div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Daftar Potongan</h5>

                        <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-potongan">
                            <i class="ri-add-line"></i>
                            Tambah Potongan
                        </button>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="45%">Potongan</th>
                                <th width="40%">Nominal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-potongan">

                        </tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-simpan">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Edit <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit">
                    <input type="hidden" name="id_potong_pegawai">
                    <div class="mb-3">
                        <label>Nama Pegawai</label>
                        <select name="id_pegawai" class="form-control">
                        </select>
                    </div>
<div class="row mb-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Bulan</label>
								<select class="form-control" data-width="100%" name="bulan_potongan">
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
										<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="mb-1">Tahun</label>
								<select class="form-control" data-width="100%" name="tahun_potongan">
									<?php
									$now = date('Y');
									for ($a = 2025; $a <= $now; $a++) {
										?>
										<option value="<?php echo $a; ?>">
											<?php echo $a; ?>
										</option>
										<?php
									}?>
								</select>
							</div>
						</div>
					</div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Daftar Potongan</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-potongan-edit">
                            Tambah Potongan
                        </button>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Potongan</th>
                                <th>Nominal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-potongan-edit">
                        </tbody>
                    </table>
                </form>
            </div>
            <div class=" modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        pegawai();
        potongan_pegawai();
        $("#btn-simpan").click(function () {
            $('#btn-simpan').prop('disabled', true);
            $('#btn-simpan').html('Sedang Diproses');
            let pegawai = $('#tambah select[name="id_pegawai"]').val();
            if (pegawai == '' || pegawai == null) {
                Swal.fire({
				icon: 'error',
				title: 'Gagal',
				text: 'Pegawai tidak boleh kosong'
			});
            $('#btn-simpan').prop('disabled', false);
			$('#btn-simpan').html('Simpan');
			return;
            }
            var form = $("#form-tambah");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/gaji/pegawai_potongan/tambah'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                success: function (data) {
                    $("#tambah").modal('hide');

                    if (data.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil disimpan',
                        }).then((result) => {
                            if (result.value) {
                                location.reload();
                            }
                        })

                        $("#form-tambah")[0].reset();
                        $('#btn-simpan').prop('disabled', false);
                        $('#btn-simpan').html('Simpan');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                        }).then((result) => {
                            if (result.value) {
                                location.reload();
                            }
                        })
                    }
                }
            })
        })
        $("#btn-update").click(function () {
            var form = $("#form-edit");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/gaji/pegawai_potongan/edit'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                success: function (data) {
                    $("#edit").modal('hide');

                    if (data.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil diupdate',
                        })
                        potongan_pegawai();
                    }
                }
            })
        })
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_potongan_pegawai .card-mapel'), jumlah);
        });
        $(document).on('click', '#btn-tambah-potongan', function () {
            tambahBarisPotongan();
        });

        $(document).on('click', '.hapus-detail', function () {
            $(this).closest('tr').remove();
        });
        $(document).on('click', '#btn-tambah-potongan-edit', function () {
            tambahBarisPotonganEdit();
        });
    })
    let no = 0;

    function tambahBarisPotongan() {
        no++;

        let html = `
    <tr>
        <td>
            <select name="id_master_potongan[]" class="form-control">
                <option value="">Pilih Potongan</option>

                <?php foreach ($master_potongan as $mp) { ?>
                    <option value="<?= $mp['id'] ?>">
                        <?= $mp['nama_potongan'] ?>
                    </option>
                <?php } ?>
            </select>
        </td>

        <td>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text"
                    name="nominal[]"
                    class="form-control nominal"
                    onkeyup="FormatCurrency(this)">
            </div>
        </td>

        <td class="text-center">
            <button type="button"
                class="btn btn-danger btn-sm hapus-detail">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    </tr>`;
        $('#detail-potongan').append(html);
    }
    function tambahBarisPotonganEdit(id_master_potongan = '', nominal = '') {
        let html = `
    <tr>
        <td>
            <select name="id_master_potongan[]" class="form-control">
                <option value="">Pilih Potongan</option>
                <?php foreach ($master_potongan as $mp) { ?>
                    <option value="<?= $mp['id'] ?>" ${id_master_potongan == '<?= $mp['id'] ?>' ? 'selected' : ''}>
                        <?= $mp['nama_potongan'] ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">
                    Rp
                </span>
                <input type="text" name="nominal[]" class="form-control" value="${nominal}" onkeyup="FormatCurrency(this)">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm hapus-detail">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    </tr>`;
        $('#detail-potongan-edit').append(html);
    }
    function pegawai(id_pegawai = null) {
        $.ajax({
            url: '<?= base_url('admin/gaji/pegawai_potongan/pegawai_result'); ?>',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id_pegawai: id_pegawai
            },
            success: function (data) {

                var no = 1;
                var option = '<option value="">Nama Pegawai</option>';
                if (data.length == 0) {

                } else {
                    data.forEach(function (item) {

                        option += `
                        <option value="${item.id}" ${item.id == id_pegawai ? 'selected' : ''}>${item.nama_pegawai}</option>
                        `;
                    });
                }
                if (id_pegawai == null) {
                    $('#tambah select[name="id_pegawai"]').html(option);
                } else {
                    $('#edit select[name="id_pegawai"]').html(option);
                }
            }
        });
    }
    function namaBulan(bulan) {
        const bulanIndonesia = {
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

        return bulanIndonesia[bulan] || bulan;
    }
    function potongan_pegawai() {
        var cari = $('#cari-pegawai').val();
        var bulan = $('#bulan-pegawai-potongan').val();
        var tahun = $('#tahun-pegawai-potongan').val();
        $.ajax({
            url: '<?= base_url('admin/gaji/pegawai_potongan/pegawai_potongan_result'); ?>',
            type: 'POST',
            data: {
                search: cari,
                bulan: bulan,
                tahun: tahun,
            },
            dataType: 'JSON',
            success: function (data) {

                var no = 1;
                var table = '';

                if (data.length == 0) {

                    table += `
                    <div class="card-mapel">
                        <div class="keterangan-mapel">
                            <div class="keterangan-mapel-kiri">
                                <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">
                                    Tidak ada data
                                </h5>
                            </div>
                        </div>
                    </div>
                `;

                } else {

                    data.forEach(function (item) {
                        let detail = btoa(JSON.stringify(item));
                        let badgePotongan = '';
                        if (item.detail_potongan && item.detail_potongan.length > 0) {
                            item.detail_potongan.forEach(function (p) {
                                badgePotongan += `
                                <span class="badge bg-success me-1 mb-1">
                                    ${p.nama_potongan} : Rp. ${NumberToMoney(p.nominal)}
                                </span>`;
                            });

                        } else {
                            badgePotongan =
                                `<span class="badge bg-secondary">
                                Tidak ada potongan
                            </span>`;
                        }
                        table += `
                        <div class="card-mapel">
                            <p class="keterangan-hari">
                                Bulan : ${namaBulan(item.bulan)} ${item.tahun}
                            </p>
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri"> <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">
                                        ${no++}. ${item.nama_pegawai}
                                    </h5>
                                    <p class="keterangan-jam-mapel">
                                        Potongan :
                                    </p>
                                    ${badgePotongan}
                                </div>
                                <div class="keterangan-mapel-kanan">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button"onclick="editPotonganPegawai('${detail}')"class="btn btn-sm btn-outline-warning me-1"><i class="ri-edit-2-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                $('#data_potongan_pegawai').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_potongan_pegawai .card-mapel'), jumlah_awal);
            }
        });
    }

    function editPotonganPegawai(detail) {
        $('#edit').modal('show');
        var item = JSON.parse(atob(detail));
        $('#detail-potongan-edit').html('');
        $('#edit input[name="id_potong_pegawai"]').val(item.id);
        pegawai(item.id_pegawai);
        $('#edit select[name="bulan_potongan"]').val(item.bulan);
        $('#edit select[name="tahun_potongan"]').val(item.tahun);
        if (item.detail_potongan && item.detail_potongan.length > 0) {
            item.detail_potongan.forEach(function (p) {
                tambahBarisPotonganEdit(
                    p.id_master_potongan,
                    NumberToMoney(p.nominal)
                );
            });
        } else {
            tambahBarisPotonganEdit();
        }
    }

    function paging(selector, jumlah_tampil = 10) {

        window.tp = new Pagination('#pagination', {
            itemsCount: selector.length,
            pageSize: parseInt(jumlah_tampil),
            onPageChange: function (paging) {
                let start = paging.pageSize * (paging.currentPage - 1);
                let end = start + paging.pageSize;
                let $rows = selector;

                $rows.hide();
                for (let i = start; i < end; i++) {
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
                    url: `<?= base_url(); ?>admin/gaji/pegawai_potongan/hapus`,
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil dihapus',
                            })
                            potongan_pegawai();
                        }

                    }
                })
            }
        })
    }

</script>