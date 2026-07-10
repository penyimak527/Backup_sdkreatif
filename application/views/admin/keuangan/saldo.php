<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-saldo" placeholder="Cari Bank"
                            aria-describedby="inputGroupPrepend" onkeyup="saldo()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="cari-tahun-saldo" class=" form-control">
                            <option value="">Pilih Tahun</option>
                            <?php
                            $now = date('Y');
                            for ($a = 2025; $a <= $now; $a++) {
                                $selected = ($a == $now) ? 'selected' : '';
                                echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="saldo()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div id="data_saldo">

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
        saldo();
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_saldo .card-mapel'), jumlah);
        });
    })

    function saldo() {
        var search = $("#cari-saldo").val();
        var tahun = $("#cari-tahun-saldo").val();
        $.ajax({
            url: '<?= base_url('admin/keuangan/saldo/saldo_result'); ?>',
            type: 'POST',
            data: {
                search: search,
                tahun: tahun
            },
            dataType: 'JSON',
            success: function (data) {
                $('#data_saldo').empty();

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
                                      <p class="keterangan-jam-mapel">Saldo : Rp. ${NumberToMoney(item.saldo_akhir)} </p>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                $('#data_saldo').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_saldo .card-mapel'), jumlah_awal);
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