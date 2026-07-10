<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
                class="ri-add-line"></i>Tambah</button>

    </div>
    <div class="card-body">
        <div class="row">
             <div class="col-md-2">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" id="flatpicker" placeholder="Tanggal"
                        aria-describedby="inputGroupPrepend" name="tanggal-cari">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-mutasi-kas" placeholder="Cari Keterangan"
                            aria-describedby="inputGroupPrepend" onkeyup="mutasi_kas()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <select class="form-control" id="cari-urutan">
                            <option value="DESC">Terbaru</option>
                            <option value="ASC">Terlama</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="mutasi_kas()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div id="data_mutasi">

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
                        <label for="tanggal" class="form-label">Tanggal </label>
                        <input type="text" id="flatpicker" name="tanggal" class="form-control"
                            placeholder="Tanggal ..." />
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" placeholder="Keterangan ..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nominal" class="form-label">Nominal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="nominal" class="form-control" placeholder="Nominal ..."
                                onkeyup="FormatCurrency(this);" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="kas-masuk" class="form-label">Kas Masuk</label>
                        <select name="kas_masuk" class="form-select">
                            <option value="">Pilih Kas Masuk</option>
                            <?php foreach ($kasbank as $kb) { ?>
                                <option value="<?= $kb['id']; ?>"><?= $kb['keterangan']; ?></option> <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kas-keluar" class="form-label">Kas Keluar</label>
                        <select name="kas_keluar" class="form-select">
                            <option value="">Pilih Kas Keluar</option>
                            <?php foreach ($kasbank as $kb) { ?>
                                <option value="<?= $kb['id']; ?>"><?= $kb['keterangan']; ?></option> <?php } ?>
                        </select>
                    </div>
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
                <form id="form-edit" enctype="multipart/form-data">
                    <input type="hidden" name="id_mutasi_kas">
                     <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal </label>
                        <input type="text" id="flatpicker" name="tanggal" class="form-control"
                            placeholder="Tanggal ..." />
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" placeholder="Keterangan ..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nominal" class="form-label">Nominal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="nominal" class="form-control" placeholder="Nominal ..."
                                onkeyup="FormatCurrency(this);" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="kas-masuk" class="form-label">Kas Masuk</label>
                        <select name="kas_masuk" class="form-select">
                            <option value="">Pilih Kas Masuk</option>
                            <?php foreach ($kasbank as $kb) { ?>
                                <option value="<?= $kb['id']; ?>"><?= $kb['keterangan']; ?></option> <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kas-keluar" class="form-label">Kas Keluar</label>
                        <select name="kas_keluar" class="form-select">
                            <option value="">Pilih Kas Keluar</option>
                            <?php foreach ($kasbank as $kb) { ?>
                                <option value="<?= $kb['id']; ?>"><?= $kb['keterangan']; ?></option> <?php } ?>
                        </select>
                    </div>
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
        flatpickr("#tambah #flatpicker", {
            dateFormat: "d-m-Y",
            defaultDate: "today",
            allowInput: true
        });
        flatpickr("#edit #flatpicker", {
            dateFormat: "d-m-Y",
            defaultDate: "today",
            allowInput: true
        });
        mutasi_kas();
        $("#btn-simpan").click(function () {
            $('#btn-simpan').prop('disabled', true);
            $('#btn-simpan').html('Sedang Diproses');
            var form = $("#form-tambah");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/keuangan/mutasi_kas/tambah'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                success: function (data) {
                    if (data.status == true) {
                        $("#tambah").modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil disimpan',
                        }).then(() => { location.reload(); }); $("#form-tambah")[0].reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan'
                        });
                    }
                    $('#btn-simpan').prop('disabled', false);
                    $('#btn-simpan').html('Simpan');
                }
            })
        })
        $("#btn-update").click(function () {
            var form = $("#form-edit");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/keuangan/mutasi_kas/edit'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                success: function (data) {
                    if (data.status == true) {
                        $("#edit").modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil diupdate',
                        }).then(() => { location.reload(); }); $("#form-edit")[0].reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan'
                        });
                    }
                    $('#btn-update').prop('disabled', false);
                    $('#btn-update').html('Simpan');
                }
            })
        })
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_pengeluaran .card-mapel'), jumlah);
        });
    })

    function mutasi_kas() {
        var search = $("#cari-mutasi_kas").val();
        var tanggal = $("input[name='tanggal-cari']").val();
        var sort_tanggal = $("#cari-urutan").val();
        $.ajax({
            url: '<?= base_url('admin/keuangan/mutasi_kas/mutasi_kas_result'); ?>',
            type: 'POST',
            data: {
                search: search,
                tanggal: tanggal,
                sort_tanggal:sort_tanggal
            },
            dataType: 'JSON',
            success: function (data) {
                $('#kode_akun').empty();

                var no = 1;
                var table = '';
                if (data.length == 0) {
                    table += `
                     <div class="card-mapel" style="">
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">Tidak ada data</h5>
                                </div>
                                 
                            </div>
                        </div>
                `;
                } else {
                    data.forEach(function (item) {
                        let detail = btoa(JSON.stringify(item));
                        table += `
                            <div class="card-mapel">
                            <p class="keterangan-hari">
                                <span>Tanggal : ${item.tanggal_input}</span>
                                <span>Waktu : ${item.waktu}</span>
                            </p>
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.keterangan}</h5>   
                                      <p class="keterangan-jam-mapel">Pegawai : ${item.nama_pegawai ?? '-'} </p>
                                      <p class="keterangan-jam-mapel">Nominal : Rp. ${NumberToMoney(item.nominal)} </p>
                                      <p class="keterangan-jam-mapel">Kas Masuk : ${item.nama_kas_masuk} </p>
                                      <p class="keterangan-jam-mapel">Kas Keluar : ${item.nama_kas_keluar} </p>
                                </div>
                                 <div class="keterangan-mapel-kanan">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning " onclick="editmutasi_kas('${detail}')"><i class="ri-edit-2-line"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                }
                $('#data_mutasi').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_mutasi .card-mapel'), jumlah_awal);
            }
        });
    }

    function editmutasi_kas(detail) {
        $('#edit').modal('show');
        var item = JSON.parse(atob(detail));
        console.log(item);
        $('#edit input[name="id_mutasi_kas"]').val(item.id);
        $('#edit input[name="tanggal"]')[0]._flatpickr.setDate(item.tanggal_input, true);
        $('#edit textarea[name="keterangan"]').val(item.keterangan);
        $('#edit input[name="nominal"]').val(NumberToMoney(item.nominal));
        $('#edit select[name="kas_masuk"]').val(item.kas_masuk);
        $('#edit select[name="kas_keluar"]').val(item.kas_keluar);

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
                    url: `<?= base_url(); ?>admin/keuangan/mutasi_kas/hapus`,
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
                            }).then(() => { mutasi_kas(); });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message || 'Terjadi kesalahan'
                            });
                        }

                    }
                })
            }
        })
    }
</script>