<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="cari-bulan-penggajian" class=" form-control " onchange="penggajian_pegawai()">
                            <option value="">Pilih Bulan</option>
                            <?php
                            $bulan_now = date('m');
                            $bulan = array(
                                "Januari",
                                "Februari",
                                "Maret",
                                "April",
                                "Mei",
                                "Juni",
                                "Juli",
                                "Agustus",
                                "September",
                                "Oktober",
                                "November",
                                "Desember"
                            );
                            $jlh_bln = count($bulan);
                            $no = 0;

                            for ($c = 0; $c < $jlh_bln; $c += 1) {
                                $no++;
                                $no_pas = sprintf("%02s", $no);
                                $selected = ($no_pas == $bulan_now) ? 'selected' : '';
                                echo '<option value="' . $no_pas . '" ' . $selected . '> ' . $bulan[$c] . ' </option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="cari-tahun-penggajian" class=" form-control " onchange="penggajian_pegawai()">
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
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
                            aria-describedby="inputGroupPrepend" onkeyup="penggajian_pegawai()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="mb-3">
                    <div class="d-flex gap-2 float-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#persen"><i class="ri-percent-line"></i>Potongan Persen</button>
                        <button type="button" class="btn  btn-outline-success" onclick="hitung_semua()"><i
                                class="ri-cash-line"></i> Hitung</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive-sm">
            <table class="table table-bordered m-b-0" id="table_penggajian">
                <thead>
                    <tr>
                        <th style="text-align: center;">No</th>
                        <th>Nama Pegawai</th>
                        <th>Gaji Pokok</th>
                        <th>Gaji Bersih</th>
                        <th>Status</th>
                        <th>Aksi</th>
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

<div class="modal fade" id="detailPenggajian" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Detail Hitung Gaji
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-7">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%">Nama Pegawai</td>
                                <td>: <span id="detail_nama"></span></td>
                            </tr>

                            <tr>
                                <td>Bulan</td>
                                <td>: <span id="detail_bulan"></span></td>
                            </tr>

                            <tr>
                                <td>Tahun</td>
                                <td>: <span id="detail_tahun"></span></td>
                            </tr>

                            <tr>
                                <td>Jumlah Hadir</td>
                                <td>: <span id="detail_hadir"></span></td>
                            </tr>
                            <tr>
                                <td>Tidak Hadir</td>
                                <td>: <span id="detail_tidak_hadir"></span></td>
                            </tr>
                            <tr>
                                <td>Ijin</td>
                                <td>: <span id="detail_ijin"></span></td>
                            </tr>
                            <tr>
                                <td>Alfa</td>
                                <td>: <span id="detail_alfa"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th width="30%">Jumlah</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr class="table-success">
                                <th colspan="2">
                                    Pendapatan
                                </th>
                            </tr>

                            <tr>
                                <td>Gaji Pokok</td>
                                <td id="detail_gaji_pokok"></td>
                            </tr>

                            <tr>
                                <td>Struktural</td>
                                <td id="detail_struktural"></td>
                            </tr>

                            <tr>
                                <td>Tunjangan Pendidikan</td>
                                <td id="detail_pendidikan"></td>
                            </tr>

                            <tr>
                                <td>Wali Kelas</td>
                                <td id="detail_wali_kelas"></td>
                            </tr>

                            <tr>
                                <td>Bonus</td>
                                <td id="detail_total_bonus"></td>
                            </tr>

                            <tr>
                                <th>Jumlah Pendapatan</th>
                                <th id="detail_total_pendapatan"></th>
                            </tr>

                            <tr class="table-danger">
                                <th colspan="2">Potongan</th>
                            </tr>

                            <tr>
                                <td>Potongan Tidak Hadir</td>
                                <td id="detail_potongan_tidak_hadir"></td>
                            </tr>

                            <tr>
                                <td>UIG/UIK</td>
                                <td id="detail_uig"></td>
                            </tr>

                            <tr>
                                <td>Zakat</td>
                                <td id="detail_zakat"></td>
                            </tr>
                        </tbody>
                        <tbody id="detail_potongan_dinamis"></tbody>
                        <tbody>
                            <tr>
                                <td>Cicilan Pinjaman</td>
                                <td id="detail_potongan_pinjaman"></td>
                            </tr>
                            <tr>
                                <td>Sisa Pinjaman</td>
                                <td id="detail_sisa_pinjaman"></td>
                            </tr>
                            <tr>
                                <th>Total Pengeluaran</th>
                                <th id="detail_total_pengeluaran"></th>
                            </tr>

                            <tr class="table-primary">
                                <th>Gaji Bersih</th>
                                <th id="detail_gaji_bersih"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="persen" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Potongan Gaji</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-persen-gaji">
                    <div class="mb-3">
                        <label for="rumus" class="form-label">Potongan Tidak Masuk</label>
                        <div class="input-group">
                            <input type="text" id="nominal-potongan-tidak-masuk" class="form-control"
                                name="potongan_tidak_masuk" onkeyup="batasiPersen(this);"
                                placeholder="Persen Potongan Tidak Masuk ..." maxlength="4">
                            <span class="input-group-text"><i class="ri-percent-line"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="rumus" class="form-label">UIG/UIK</label>
                        <div class="input-group">
                            <input type="text" id="nominal-uig-uik" class="form-control" name="uig_uik"
                                onkeyup="batasiPersen(this);" placeholder="Persen UIG/UIK ..." maxlength="4">
                            <span class="input-group-text"><i class="ri-percent-line"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="rumus" class="form-label">Zakat</label>
                        <div class="input-group">
                            <input type="text" id="nominal-zakat" class="form-control" name="zakat"
                                onkeyup="batasiPersen(this);" placeholder="Persen Zakat ..." maxlength="4">
                            <span class="input-group-text"><i class="ri-percent-line"></i></span>
                        </div>
                    </div>
                    <small class="text-muted">Persen potongan gaji ini digunakan untuk mengubah rumus potongan gaji pada
                        semua pegawai.</small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-simpan-potongan-gaji">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        penggajian_pegawai();
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#table_penggajian tbody tr'), jumlah);
        });
        $("#btn-simpan-potongan-gaji").click(function () {
            $('#btn-simpan-potongan-gaji').prop('disabled', true);
            $('#btn-simpan-potongan-gaji').html('Diproses');
            var form = $("#form-persen-gaji");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/gaji/hitung_gaji/edit_potongan_gaji'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                success: function (data) {
                    $("#persen").modal('hide');
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

                        $("#form-persen-gaji")[0].reset();
                        $('#btn-simpan-potongan-gaji').prop('disabled', false);
                        $('#btn-simpan-potongan-gaji').html('Simpan');
                    }
                }
            })
        });
        $('#persen').on('show.bs.modal', function () {
            $.ajax({
                url: '<?= base_url("admin/gaji/hitung_gaji/get_potongan_gaji") ?>',
                dataType: 'json',
                success: function (res) {
                    console.log(res);
                    res.forEach(item => {
                        let nama = item.nama_potongan;
                        let persen = item.nominal_persen;
                        if (nama === 'zakat') {
                            $('#nominal-zakat').val(persen);
                        }
                        if (nama === 'uig_uik') {
                            $('#nominal-uig-uik').val(persen);
                        }
                        if (nama === 'tidak_hadir') {
                            $('#nominal-potongan-tidak-masuk').val(persen);
                        }
                    });
                }
            });
        });
    })


    function penggajian_pegawai() {
        var cari = $('#cari-pegawai').val();
        var bulan = $('#cari-bulan-penggajian').val();
        var tahun = $('#cari-tahun-penggajian').val();
        $.ajax({
            url: '<?= base_url('admin/gaji/hitung_gaji/hitung_gaji_result'); ?>',
            type: 'POST',
            data: {
                search: cari,
                bulan: bulan,
                tahun: tahun
            },
            dataType: 'JSON',
            beforeSend: function () {
                $('#table_penggajian tbody').html(`
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
            },
            success: function (data) {
                $('#pegawai').empty();
                var no = 1;
                var table = '';
                if (data.length == 0) {
                    table += `
                   <tr>
                        <td colspan="7" style="text-align: center;">Tidak ada data</td>
                    </tr>
                `;
                } else {
                    data.forEach(function (item) {
                        let detail = btoa(JSON.stringify(item));

                        let status_penggajian = '';
                        if (item.status_penggajian == 'Belum Dihitung') {
                            status_penggajian = `<span class="badge bg-danger">${item.status_penggajian}</span>`;
                        } else {
                            status_penggajian = `<span class="badge bg-success">${item.status_penggajian}</span>`;
                        }
                        table += `
                        <tr>
                            <td width="5%" style="text-align: center;"> ${no++}</td>
                            <td>${item.nama_pegawai}</td> 
                            <td>Rp. ${NumberToMoney(item.gaji_pokok)}</td> 
                            <td>Rp. ${NumberToMoney(item.gaji_bersih)}</td> 
                            <td>${status_penggajian}</td> 
                            <td> 
                                <button class="btn btn-sm btn-outline-primary" onclick="detail('${detail}')"><i class="ri-eye-line"></i></button>
                                <button class="btn btn-sm btn-outline-success " onclick="hitung(${item.id_pegawai})"><i class="ri-cash-line"></i></button>
                            </td>
                        </tr>
                        `;
                    });
                }
                $('#table_penggajian tbody').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#table_penggajian tbody tr'), jumlah_awal);
            }
        });
    }

    function detail(detail) {
        $('#detailPenggajian').modal('show');
        var item = JSON.parse(atob(detail));

        $('#detail_nama').html(item.nama_pegawai);
        var namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        // Ambil nama bulan
        var bulanIndex = parseInt(item.bulan) - 1;
        var bulanNama = namaBulan[bulanIndex] || item.bulan;
        $('#detail_bulan').html(bulanNama);
        $('#detail_tahun').html(item.tahun);
        $('#detail_hadir').html(item.jumlah_hadir);
        $('#detail_tidak_hadir').html(item.jumlah_tidak_hadir);
        $('#detail_ijin').html(item.jumlah_ijin);
        $('#detail_alfa').html(item.jumlah_alfa);
        $('#detail_gaji_pokok').html('Rp. ' + NumberToMoney(item.gaji_pokok));
        $('#detail_struktural').html('Rp. ' + NumberToMoney(item.struktural));
        $('#detail_pendidikan').html('Rp. ' + NumberToMoney(item.tunjangan_pendidikan));
        $('#detail_wali_kelas').html('Rp. ' + NumberToMoney(item.wali_kelas));
        $('#detail_total_bonus').html('Rp. ' + NumberToMoney(item.total_bonus));
        $('#detail_total_pendapatan').html('Rp. ' + NumberToMoney(item.total_pendapatan));
        // $('#detail_potongan_tidak_hadir').html('Rp. ' + NumberToMoney(item.potongan_tidak_hadir));
        // $('#detail_uig').html('Rp. ' + NumberToMoney(item.uig_uik));
        // $('#detail_zakat').html('Rp. ' + NumberToMoney(item.zakat));

        $('#detail_potongan_tidak_hadir').html(
            'Rp. ' + NumberToMoney(item.potongan_tidak_hadir) +
            ' <small class="text-muted">(' + (item.persen_potongan_tidak_hadir ?? '') + '% x '+ item.jumlah_alfa +' alfa)</small>'
        );

        $('#detail_uig').html(
            'Rp. ' + NumberToMoney(item.uig_uik) +
            ' <small class="text-muted">(' + (item.persen_uig_uik ?? '') + '%)</small>'
        );

        $('#detail_zakat').html(
            'Rp. ' + NumberToMoney(item.zakat) +
            ' <small class="text-muted">(' + (item.persen_zakat ?? '') + '%)</small>'
        );

        let htmlPotongan = '';
        if (item.potongan_detail != null) {
            item.potongan_detail.forEach(function (row) {
                htmlPotongan += `
            <tr>
                <td>${row.nama_potongan}</td>
                <td>Rp. ${NumberToMoney(row.nominal)}</td>
            </tr>
        `;
            });
        }
        $('#detail_potongan_dinamis').html(htmlPotongan);

        $('#detail_potongan_pinjaman').html('Rp. ' + NumberToMoney(item.cicilan_pinjaman));
        $('#detail_sisa_pinjaman').html('Rp. ' + NumberToMoney(item.sisa_pinjaman));
        $('#detail_total_pengeluaran').html('Rp. ' + NumberToMoney(item.total_pengeluaran));
        $('#detail_gaji_bersih').html('Rp. ' + NumberToMoney(item.gaji_bersih));
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

    function hitung(id) {
        var bulan = $('#cari-bulan-penggajian').val();
        var tahun = $('#cari-tahun-penggajian').val();
        Swal.fire({
            title: 'Hitung Data',
            text: 'Anda yakin ingin menghitung data ini?',
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
                    url: `<?= base_url(); ?>admin/gaji/hitung_gaji/hitung`,
                    data: {
                        id: id,
                        bulan: bulan,
                        tahun: tahun,
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Menghitung...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    success: function (data) {
                        if (data == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil dihitung',
                            })
                            penggajian_pegawai();
                        }
                    }
                })
            }
        })
    }

    function hitung_semua() {
        var bulan = $('#cari-bulan-penggajian').val();
        var tahun = $('#cari-tahun-penggajian').val();
        Swal.fire({
            title: 'Hitung Semua Penggajian?',
            text: 'Semua data pegawai akan dihitung',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    type: 'POST',
                    url: `<?= base_url(); ?>admin/gaji/hitung_gaji/hitung_semua`,
                    data: {
                        bulan: bulan,
                        tahun: tahun
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Menghitung Semua...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    success: function (data) {
                        if (data == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Semua penggajian berhasil dihitung'
                            });
                            penggajian_pegawai();
                        }
                    }
                });
            }
        });
    }
    function batasiPersen(el) {
        let nilai = el.value;

        // izinkan angka, koma, dan titik
        nilai = nilai.replace(/[^0-9,.]/g, '');

        // ubah titik menjadi koma agar format input konsisten
        nilai = nilai.replace(/\./g, ',');

        // hanya boleh ada 1 koma
        let parts = nilai.split(',');
        if (parts.length > 2) {
            nilai = parts[0] + ',' + parts.slice(1).join('');
        }

        // batasi angka desimal maksimal 2 digit
        parts = nilai.split(',');
        if (parts.length === 2) {
            nilai = parts[0] + ',' + parts[1].substring(0, 2);
        }

        // cek nilai angka untuk batas maksimal 100
        let angka = parseFloat(nilai.replace(',', '.')) || 0;

        if (angka > 100) {
            nilai = '100';
        }

        el.value = nilai;
    }
</script>