<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <!-- <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
        </div> -->
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Dari"
                        aria-describedby="inputGroupPrepend" name="tanggal_dari">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" name="tanggal_sampai" id="flatpicker"
                        placeholder="Tanggal Sampai" aria-describedby="inputGroupPrepend">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-pegawai" placeholder="Cari"
                            aria-describedby="inputGroupPrepend" onkeyup="riwayatpresensi_pegawai()">
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary" onclick="riwayatpresensi_pegawai()"><i
                        class="ri-search-line"></i></button>
            </div>
        </div>

        <div id="data_pegawai">

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

<!-- <div class="modal fade" id="tambah" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Tambah <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-tambah">
                    <div class="mb-3">
                        <label for="pegawai" class="form-label">Id Pegawai</label>
                        <input type="text" name="id_pegawai" class="form-control" placeholder="Id Pegawai ..." />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-simpan">Simpan</button>
            </div>
        </div>
    </div>
</div> -->

<script>
    $(document).ready(function () {
        riwayatpresensi_pegawai();
        // $("#btn-simpan").click(function () {
        //     $('#btn-simpan').prop('disabled', true);
        //     $('#btn-simpan').html('Sedang Diproses');
        //     var form = $("#form-tambah");
        //     var formData = form.serialize();
        //     $.ajax({
        //         url: '<?= base_url('admin/presensi/riwayat_presensi_pegawai/presensi_pegawai_tambah'); ?>',
        //         type: 'POST',
        //         data: formData,
        //         success: function (data) {
        //             console.log(data);
        //             $("#tambah").modal('hide');
        //             if (data.status == true) {
        //                 Swal.fire({
        //                     icon: 'success',
        //                     title: 'Berhasil',
        //                     text: 'Data berhasil disimpan',
        //                 }).then((result) => {
        //                     if (result.value) {
        //                         location.reload();
        //                     }
        //                 })
        //                 $("#form-tambah")[0].reset();
        //                 $('#btn-simpan').prop('disabled', false);
        //                 $('#btn-simpan').html('Simpan');
        //             }
        //         }
        //     })
        // })
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_pegawai .card-mapel'), jumlah);
        });
    })

    function riwayatpresensi_pegawai() {
        var cari = $('#cari-pegawai').val();
        var tanggal_dari = $('input[name="tanggal_dari"]').val();
        var tanggal_sampai = $('input[name="tanggal_sampai"]').val();
        $.ajax({
            url: '<?= base_url('admin/presensi/riwayat_presensi_pegawai/riwayatpresensi_pegawai'); ?>',
            type: 'POST',
            data: {
                search: cari,
                tanggal_dari: tanggal_dari,
                tanggal_sampai: tanggal_sampai
            },
            dataType: 'JSON',
            success: function (data) {
                $('#pegawai').empty();
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
                        let statusbadge = '';
                        if (item.status == 'Terlambat') {
                            statusbadge = `<span class="badge bg-danger">${item.status}</span>`;
                        } else {
                            statusbadge = `<span class="badge bg-success">${item.status}</span>`;
                        }
                        let jamPulang = '';
                        if (item.jam_pulang != '') {
                            jamPulang = `${item.jam_pulang}`;
                        } else {
                            jamPulang = `-`;
                        }
                        table += `
                            <div class="card-mapel">
                              <p class="keterangan-hari">Nama Jabatan : ${item.jabatan} ${statusbadge}</p>
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.nama_pegawai}</h5>   
                                      <p class="keterangan-jam-mapel mt-1">Tanggal: ${item.tanggal}</p>
                                      <p class="keterangan-jam-mapel">Jam Masuk:  ${item.waktu} </p>
                                      <p class="keterangan-jam-mapel">Jam Pulang : ${jamPulang}</p>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                }
                $('#data_pegawai').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_pegawai .card-mapel'), jumlah_awal);
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
</script>