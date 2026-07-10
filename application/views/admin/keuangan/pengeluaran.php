<style>
    /* #table_pengeluaran {
        min-width: 1500px !important;
    } */
.select2-container .select2-selection--single {
    height: 31px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 29px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 31px !important;
}

	.select2-normal + .select2-container .select2-selection--single {
    height: 38px !important;
}

.select2-normal + .select2-container .select2-selection__rendered {
    line-height: 36px !important;
}

.select2-normal + .select2-container .select2-selection__arrow {
    height: 36px !important;
}
    .img-preview {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid #ddd;
        transition: .2s;
    }

    #previewImageModal {
        max-width: 100%;
        max-height: 75vh;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .img-preview:hover {
        transform: scale(1.1);
    }

    .img-preview-edit {
        max-width: 200px;
        max-height: 150px;
        width: auto;
        height: auto;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 3px;
        background: #fff;
    }
        #table_pengeluaran {
    font-size: 12px;
}
</style>
<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <div class="d-flex gap-2">
            <!-- <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button> -->
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Dari"
                        aria-describedby="inputGroupPrepend" name="tanggal-cari-dari">
                </div>
            </div>
            <div class="col-md-2">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Sampai"
                        aria-describedby="inputGroupPrepend" name="tanggal-cari-sampai">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-keterangan" placeholder="Cari Keterangan"
                            aria-describedby="inputGroupPrepend" onkeyup="pengeluaran()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="cari-pengeluaran-keterangan" class=" form-control select2-normal">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kode_akun as $k) { ?>
                                <option value="<?= $k['id']; ?>"><?= $k['keterangan']; ?></option> <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="pengeluaran()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm" id="table_pengeluaran">
                <thead>
                    <tr>
                        <th style="text-align: center;" width="60">No</th>
                        <th width="120" onclick="urutTanggal()" style="cursor:pointer">Tanggal <i class="ri-arrow-up-down-line"></i></th>
                        <th width="150">Kode Akun</th>
                        <th width="150">Nominal</th>
                        <th width="250">Keterangan</th>
                        <th width="140">Sumber Dana</th>
                        <th width="170">Sumber Kode Akun</th>
                        <th width="160">Bukti</th>
                        <th width="85">Aksi</th>
                    </tr>
                    <tr>
                        <th class="text-center align-top">#</th>
                        <th class="align-top">
                            <input type="text" id="flatpicker" name="tanggal_tambah"
                                class="form-control form-control-sm" placeholder="Tanggal ..." />
                        </th>

                        <th class="align-top">
                            <select id="id_kode_akun_tambah" class="form-control form-control-sm select2">
                                <option value="">Pilih Kode Akun</option>
                                <?php foreach ($kode_akun as $k) { ?>
                                    <option value="<?= $k['id'] ?>">
                                        <?= $k['keterangan'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </th>

                        <th class="align-top">
                            <input type="text" id="nominal_tambah" class="form-control form-control-sm"
                                onkeyup="FormatCurrency(this);" placeholder="Nominal ...">
                        </th>

                        <th class="align-top">
                            <!-- <input type="text" id="keterangan_tambah" class="form-control form-control-sm"> -->
                            <textarea type="text" id="keterangan_tambah" class="form-control form-control-sm"
                                placeholder="Keterangan ..."></textarea>
                        </th>

                        <th class="align-top">
                            <select id="sumber_dana_tambah" class="form-control form-control-sm ">
                                <option value="">Pilih Kas Bank</option>
                                <?php foreach ($kasbank as $s) { ?>
                                    <option value="<?= $s['id']; ?>"><?= $s['keterangan']; ?></option> <?php } ?>
                            </select>
                        </th>

                        <th class="align-top">
                            <select id="filter_kode_akun_tambah" class="form-control form-control-sm select2">
                                <option value="">Pilih Sumber Kode Akun</option>
                                <?php foreach ($fkode_akun as $fk) { ?>
                                    <option value="<?= $fk['id']; ?>"><?= $fk['keterangan']; ?></option> <?php } ?>
                            </select>
                        </th>
                        <th class="align-top">
                            <input type="file" id="bukti_tambah" class="form-control form-control-sm" accept="image/*">
                            <small class="text-muted">(Opsional)</small>
                        </th>
                        <th class="align-top">
                            <button class="btn btn-primary btn-sm" style="height: 30px; width: 60px;" id="btn-simpan" onclick="simpanBaru()">
                                Simpan
                            </button>
                        </th>
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

<div class="modal fade" id="view-pengeluaran" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Detail <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-1">
                    <label class="col-sm-3 fw-bold">Tanggal</label>
                    <div class="col-sm-9" id="tanggal_detail"></div>
                </div>
                <div class="row mb-1">
                    <label class="col-sm-3 fw-bold">Keterangan</label>
                    <div class="col-sm-9" id="keterangan_detail"></div>
                </div>
                <div class="row mb-1">
                    <label class="col-sm-3 fw-bold">Pengeluaran</label>
                    <div class="col-sm-9" id="nama_kategori"></div>
                </div>
                <div class="row mb-1">
                    <label class="col-sm-3 fw-bold">Sumber Dana</label>
                    <div class="col-sm-9" id="kas"></div>
                </div>
                <div class="row mb-1">
                    <label class="col-sm-3 fw-bold">Nominal</label>
                    <div class="col-sm-9" id="nominal_detail"></div>
                </div>
                <div class="row mb-1">
                    <label class="col-sm-3 fw-bold">Bukti</label>
                    <div class="col-sm-9" id="bukti_pengeluaran"></div>
                </div>
            </div>
            <div class=" modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewImage">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Preview Bukti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImageModal" class="img-fluid rounded">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        flatpickr("input[name='tanggal_tambah']", {
            dateFormat: "d-m-Y",
            defaultDate: "today",
            allowInput: true
        });

        pengeluaran();

        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#table_pengeluaran tbody tr'), jumlah);
        });
        $('.select2').select2({
			width: '100%'
		});
        $('.select2-normal').select2({
    width: '100%'
});

    })
    function simpanBaru() {
        let formData = new FormData();
        formData.append('tanggal', $('input[name="tanggal_tambah"]').val());
        formData.append('id_kode_akun', $('#id_kode_akun_tambah').val());
        formData.append('nominal', $('#nominal_tambah').val());
        if ($('#nominal_tambah').val() == '') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Nominal tidak boleh kosong'
            });
            return;
        }
        // Disable tombol
        $('#btn-simpan').prop('disabled', true);
        $('#btn-simpan').html('Diproses');
        formData.append('keterangan', $('#keterangan_tambah').val());
        formData.append('id_sumber_dana', $('#sumber_dana_tambah').val());
        formData.append('filter_kode_akun', $('#filter_kode_akun_tambah').val());
        let file = $('#bukti_tambah')[0].files[0];
        if (file) {
            formData.append('bukti', file);
        }
        $.ajax({
            url: '<?= base_url('admin/keuangan/pengeluaran/tambah') ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (res) {
                if (res.status == true) {
                    $('input[name="tanggal_tambah"]').val('');
                    $('#nominal_tambah').val('');
                    $('#keterangan_tambah').val('');
                    $('#id_kode_akun_tambah').val('');
					$('#filter_kode_akun_tambah').val('');
                    $('#bukti_tambah').val('');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data berhasil diupdate'
                    });
                    flatpickr("input[name='tanggal_tambah']", {
                        dateFormat: "d-m-Y",
                        defaultDate: "today",
                        allowInput: true
                    });
                    pengeluaran();
                    // Aktif kembali tombol
                    $('#btn-simpan').prop('disabled', false);
                    $('#btn-simpan').html('Simpan');
                }
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message
                    });
                }
            }
        });
    }
    let sortTanggal = '';
function urutTanggal() {
    sortTanggal = (sortTanggal === 'DESC') ? 'ASC' : 'DESC';
    pengeluaran(sortTanggal);
}

    function pengeluaran(sortTanggal = null) {
        var keterangan = $('#cari-keterangan').val();
        var kategori = $('#cari-pengeluaran-keterangan').val();
        var tanggal_dari = $('input[name="tanggal-cari-dari"]').val();
        var tanggal_sampai = $('input[name="tanggal-cari-sampai"]').val();
        $.ajax({
            url: '<?= base_url('admin/keuangan/pengeluaran/pengeluaran_result'); ?>',
            type: 'POST',
            data: {
                keterangan: keterangan,
                search: kategori,
                tanggal_dari: tanggal_dari,
                tanggal_sampai: tanggal_sampai,
                sort_tanggal: sortTanggal
            },
            dataType: 'JSON',
            success: function (data) {
                $('#table_pengeluaran tbody').empty();
                var no = 1;
                var table = '';
                if (data.length == 0) {
                    table += `
                    <tr>
                        <td colspan="9" style="text-align: center;">Tidak ada data</td>
                    </tr>`;
                } else {
                    data.forEach(function (item) {
                        let detail = btoa(JSON.stringify(item));
                        table += `
            <tr id="row_${item.id}">
                <td class="text-center">${no}</td>

                <td class="tanggal" data-value="${item.tanggal_input}">
                    ${item.tanggal_input}
                </td>

                <td class="kode_akun" data-value="${item.id_kode_akun}">
                    ${item.nama_keterangan}
                </td>

                <td class="nominal text-end" data-value="${item.jumlah}">
                    Rp. ${NumberToMoney(item.jumlah)}
                </td>

                <td class="keterangan" data-value="${item.keterangan}">
                    ${item.keterangan}
                </td>

                <td class="sumber_dana" data-value="${item.sumber_dana}">
                    ${item.nama_sumber_dana ?? '-'}
                </td>

                <td class="filter_kode_akun" data-value="${item.filter_kode_akun}">
                    ${item.nama_filter_kode_akun ?? '-'}
                </td>

                <td>
                 ${item.bukti ? `<img src="<?= base_url(); ?>${item.bukti}" 
                 class="img-preview" onclick="previewImage('<?= base_url(); ?>${item.bukti}')">` : '-'}
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" style="width: 30px; height: 30px;" onclick="editpengeluaran('${no}','${item.id}','${detail}')">
                <i class="ri-edit-line"></i>
            </button>
                    <button class="btn btn-sm btn-danger" style="width: 30px; height: 30px;" onclick="hapus(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>`; no++;
                    });
                }
                $('#table_pengeluaran tbody').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#table_pengeluaran tbody tr'), jumlah_awal);

            }
        });
    }

    function previewImage(src) {
        $('#previewImageModal').attr('src', src);
        let modal = new bootstrap.Modal(
            document.getElementById('modalPreviewImage')
        );
        modal.show();
    }

    let originalRows = {};
    function editpengeluaran(no, id, detail) {
        let item = JSON.parse(atob(detail));
        let row = $('#row_' + id);

        // if (!originalRows[id]) {
        originalRows[id] = row.html();
        // }
        row.html(`
        <td class="text-center">${no}</td>
        <td>
            <input type="text" id="tanggal_edit_${id}" value="${item.tanggal_input}" class="form-control form-control-sm flatpickr-edit">
        </td>
        <td>
            <select id="kode_akun_edit_${id}" class="form-control form-control-sm select2-edit">
                <option value="">Pilih Kode Akun</option>
                <?php foreach ($kode_akun as $k) { ?>
                    <option value="<?= $k['id'] ?>">
                        <?= $k['keterangan'] ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <td>
            <input type="text" id="nominal_edit_${id}" value="${NumberToMoney(item.jumlah)}" class="form-control form-control-sm" onkeyup="FormatCurrency(this);" >
        </td>
        <td>
            <textarea type="text" id="keterangan_edit_${id}" class="form-control form-control-sm">${item.keterangan}</textarea>
        </td>
        <td>
            <select id="sumber_dana_edit_${id}" class="form-control form-control-sm">
                <option value="">Pilih Kas Bank</option>
                <?php foreach ($kasbank as $s) { ?>
                    <option value="<?= $s['id'] ?>">
                        <?= $s['keterangan'] ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <td>
            <select id="filter_kode_akun_edit_${id}" class="form-control form-control-sm select2-edit">
                <option value="">Pilih Sumber Kode Akun</option>
                <?php foreach ($fkode_akun as $fk) { ?>
                    <option value="<?= $fk['id'] ?>">
                        <?= $fk['keterangan'] ?>
                    </option>
                <?php } ?>
            </select>
        </td>
<td>
    ${item.bukti ? `
        <div id="preview_container_${id}">
            <img src="<?= base_url(); ?>${item.bukti}" class="img-preview-edit mb-2">
            <br>
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusPreviewGambar(${id})" style="height: 30px; width: 30px;">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>` : ''}

    <input type="hidden" id="old_image_${id}" value="${item.bukti ?? ''}">
    <input type="hidden" id="hapus_gambar_${id}" value="0">
    <input type="file" id="bukti_edit_${id}" class="form-control form-control-sm mt-2">
    <small class="text-muted">(Opsional)</small>
</td>
        <td>
            <button class="btn btn-primary btn-sm " style="height: 30px; width: 60px; margin-top: 1px;" onclick="simpanEdit(${id})">Simpan</button>
            <button class="btn btn-secondary btn-sm " style="height: 30px; width: 60px; margin-top: 1px;" onclick="batalEdit(${id})">Batal</button>
        </td>
    `);

        $('#kode_akun_edit_' + id).val(item.id_kode_akun);
        $('#sumber_dana_edit_' + id).val(item.sumber_dana);
        $('#filter_kode_akun_edit_' + id).val(item.filter_kode_akun);
        row.find('.select2-edit').select2({
			width: '100%'
		});
		$('#kode_akun_edit_' + id).select2({
			width: '100%'
		});

		$('#filter_kode_akun_edit_' + id).select2({
			width: '100%'
		});
        flatpickr(`#tanggal_edit_${id}`, {
            dateFormat: "d-m-Y",
            allowInput: true,
            defaultDate: item.tanggal_input
        });
    }
    function hapusPreviewGambar(id) {
        $('#preview_container_' + id).remove();
        $('#hapus_gambar_' + id).val(1);
        // $('#old_image_' + id).val('');
    }
    function batalEdit(id) {
        if (originalRows[id]) {
            $('#row_' + id).html(originalRows[id]);
            delete originalRows[id];
        }
    }
    function simpanEdit(id) {
        let formData = new FormData();
        formData.append('id_pengeluaran', id);
        formData.append('tanggal', $('#tanggal_edit_' + id).val());
        formData.append('id_kode_akun', $('#kode_akun_edit_' + id).val());
        formData.append('nominal', $('#nominal_edit_' + id).val());
        formData.append('keterangan', $('#keterangan_edit_' + id).val());
        formData.append('id_sumber_dana', $('#sumber_dana_edit_' + id).val());
        formData.append('filter_kode_akun', $('#filter_kode_akun_edit_' + id).val());
        formData.append('oldImage', $('#old_image_' + id).val());
        let file = $('#bukti_edit_' + id)[0].files[0];
        if (file) {
            formData.append('bukti', file);
        }
        formData.append(
            'hapus_gambar',
            $('#hapus_gambar_' + id).val()
        );

        $.ajax({
            url: '<?= base_url("admin/keuangan/pengeluaran/edit") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data berhasil diupdate'
                    });
                    pengeluaran();
                }
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message
                    });
                }
            }
        });
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
                    url: `<?= base_url(); ?>admin/keuangan/pengeluaran/hapus`,
                    data: {
                        id: id
                    },
                    success: function (data) {
                        if (data == 'true') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil dihapus',
                            })
                            pengeluaran();
                        }

                    }
                })
            }
        })
    }
</script>