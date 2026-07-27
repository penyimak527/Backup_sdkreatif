<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
                class="ri-add-line"></i>Tambah</button>

    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-kode_akun" placeholder="Cari Kode Akun"
                            aria-describedby="inputGroupPrepend" onkeyup="kode_akun()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="cari-jenis-kode-akun" class=" form-control">
                            <option value="">Pilih Jenis</option>
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="kode_akun()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div id="data_kode_akun">

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
                        <label for="jenis" class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="">Pilih Jenis</option>
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" placeholder="Keterangan ..."></textarea>
                        <!-- <input type="text" name="keterangan" class="form-control" placeholder="Keterangan ..." /> -->
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
                    <input type="hidden" name="id_kode_akun">
                    <div class="mb-3">
                        <label for="jenis" class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="">Pilih Jenis</option>
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" placeholder="Keterangan ..."></textarea>
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
        kode_akun();
        $("#btn-simpan").click(function () {
            $('#btn-simpan').prop('disabled', true);
            $('#btn-simpan').html('Sedang Diproses');
            var form = $("#form-tambah");
            var formData = form.serialize();
            $.ajax({
                url: "<?= base_url('admin/keuangan/kode_akun/tambah'); ?>",
                type: 'POST',
                data: formData,
                success: function (data) {
                    $("#tambah").modal('hide');
                    if (data == 'true') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil disimpan',
                        })
                        kode_akun();
                        $("#form-tambah")[0].reset();
                        $('#btn-simpan').prop('disabled', false);
                        $('#btn-simpan').html('Simpan');
                    }
                }
            })
        })
        $("#btn-update").click(function () {
            var form = $("#form-edit");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/keuangan/kode_akun/edit'); ?>',
                type: 'POST',
                data: formData,
                success: function (data) {
                    $("#edit").modal('hide');

                    if (data == 'true') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil diupdate',
                        })
                        kode_akun();
                    }
                }
            })
        })
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_kode_akun .card-mapel'), jumlah);
        });
    })

    function kode_akun() {
        var search = $("#cari-kode_akun").val();
        var jenis = $("#cari-jenis-kode-akun").val();
        $.ajax({
            url: '<?= base_url('admin/keuangan/kode_akun/kode_akun_result'); ?>',
            type: 'POST',
            data: {
                search: search,
                jenis: jenis,
            },
            dataType: 'JSON',
            success: function (data) {
                $('#data_kode_akun').empty();

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
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.keterangan}</h5>   
                                      <p class="keterangan-jam-mapel">Jenis : ${item.jenis} </p>
                                </div>
                                 <div class="keterangan-mapel-kanan">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning " onclick="editkode_akun('${detail}')"><i class="ri-edit-2-line"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                $('#data_kode_akun').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_kode_akun .card-mapel'), jumlah_awal);
            }
        });
    }

    function editkode_akun(detail) {
        $('#edit').modal('show');
        var item = JSON.parse(atob(detail));
        $('#edit input[name="id_kode_akun"]').val(item.id);
        $('#edit select[name="jenis"]').val(item.jenis);
        $('#edit textarea[name="keterangan"]').val(item.keterangan);

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
                    url: `<?= base_url(); ?>admin/keuangan/kode_akun/hapus`,
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
                            kode_akun();
                        }

                    }
                })
            }
        })
    }
</script>