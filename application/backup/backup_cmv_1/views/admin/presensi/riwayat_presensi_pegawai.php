<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>

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


<script>
    $(document).ready(function () {
        riwayatpresensi_pegawai();
    
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