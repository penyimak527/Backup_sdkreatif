<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                data-bs-target="#persen"><i class="ri-percent-line"></i>Persen Gaji</button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="periode-rencana-pengeluaran" class=" form-control ">
                            <option value="">Pilih Periode</option>
                            <?php foreach ($tahun_ajaran as $tahunajaran): ?>
                                <option value="<?= $tahunajaran['id'] ?>"><?= $tahunajaran['periode'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="semester-rencana-pengeluaran" class=" form-control ">
                            <option value="">Pilih Semester</option>
                            <option value="Tahunan">Tahunan</option>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>
            </div> -->
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="rencana_pengeluaran()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div id="data_rencana_pengeluaran">

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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Tambah <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-tambah">
                    <input type="hidden" name="total_asumsi" id="input-total-asumsi">
                    <input type="hidden" name="id_rencana_asumsi_pemasukan" id="input-id-rencana-asumsi-pemasukan">
                    <input type="hidden" name="persen_gaji" id="input-persen-gaji-rencana">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Tahun Ajaran</label>
                            <select name="tahun_ajaran" class="form-control" onchange="ambilAsumsi()">
                                <option value="">Pilih Tahun Ajaran</option>
                                <?php foreach ($tahun_ajaran as $ta) { ?>
                                    <option value="<?= $ta['id']; ?>">
                                        <?= $ta['periode']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <input type="hidden" name="semester" value="Tahunan" readonly>
                        <!-- <div class="col-md-6">
                            <label>Semester</label>
                            <select name="semester" class="form-control" onchange="ambilAsumsi()">
                                <option value="">Pilih Semester</option>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div> -->
                    </div>
                    <hr>
                    <div class="alert alert-info">
                        <div>
                            <strong>Total Asumsi Masuk :</strong>
                            Rp <span id="total-asumsi">0</span>
                        </div>

                        <div>
                            <strong>Total Pengeluaran :</strong>
                            Rp <span id="total-pengeluaran">0</span>
                        </div>

                        <div>
                            <strong>Sisa Asumsi :</strong>
                            Rp <span id="sisa-asumsi">0</span>
                        </div>
                    </div>
                    <div id="list-pengeluaran"></div>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Edit <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit" enctype="multipart/form-data">
                    <input type="hidden" name="id">
                    <input type="hidden" name="id_rencana_asumsi_pemasukan" id="id-rencana-asumsi-pemasukan-edit">
                    <input type="hidden" name="total_asumsi" id="input-total-asumsi-edit">
                    <input type="hidden" name="persen_gaji" id="input-persen-gaji-rencana-edit">
                    <div class="row">
                        <div class="col-md-6"><select name="tahun_ajaran" class="form-control"
                                onchange="ambilAsumsiEdit()">
                                <option value="">Pilih Tahun Ajaran</option>
                                <?php foreach ($tahun_ajaran as $ta) { ?>
                                    <option value="<?= $ta['id']; ?>">
                                        <?= $ta['periode']; ?>
                                    </option>
                                <?php } ?>
                            </select></div>
                        <input type="hidden" name="semester" value="Tahunan" readonly>
                        <!-- <div class="col-md-6"><select name="semester" class="form-control" onchange="ambilAsumsiEdit()">
                                <option value="">Pilih Semester</option>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select></div> -->
                    </div>
                    <hr>
                    <div class="alert alert-info">
                        <div>
                            <strong>Total Asumsi Masuk :</strong>
                            Rp <span id="total-asumsi-edit">0</span>
                        </div>
                        <div>
                            <strong>Total Pengeluaran :</strong>
                            Rp <span id="total-pengeluaran-edit">0</span>
                        </div>
                        <div>
                            <strong>Sisa Asumsi :</strong>
                            Rp <span id="sisa-asumsi-edit">0</span>
                        </div>
                    </div>
                    <div id="list-pengeluaran-edit"></div>
                </form>
            </div>
            <div class=" modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Detail Rencana Pengeluaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">
                <div id="detail-content"></div>
            </div>
            <div class=" modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="persen" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Persen Gaji</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-persen-gaji">
                    <div class="mb-3">
                        <label for="rumus" class="form-label">Persen Gaji</label>
                        <div class="input-group">
                            <input type="text" id="nominal-persen" class="form-control" name="persen"
                                onkeyup="batasiPersen(this);" placeholder="Persen Gaji ..." maxlength="4">
                            <span class="input-group-text"><i class="ri-percent-line"></i></span>
                        </div>
                    </div>
                    <small class="text-muted">Persen gaji ini digunakan untuk mengubah rumus gaji pada rencana
                        pengeluaran.</small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-simpan-persen-gaji">Persen Gaji</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        rencana_pengeluaran();
        $("#btn-simpan").click(function () {
            $('#btn-simpan').prop('disabled', true);
            $('#btn-simpan').html('Sedang Diproses');
            let tahun_ajaran = $('#form-tambah select[name="tahun_ajaran"]').val();
            let semester = $('#form-tambah input[name="semester"]').val();
            if (tahun_ajaran == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tahun ajaran wajib dipilih.'
                });
                $('#btn-simpan').prop('disabled', false); $('#btn-simpan').html('Simpan');
                return;
            }

            var form = $("#form-tambah");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/perencanaan/rencana_pengeluaran/tambah'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                  beforeSend: function () {
                        Swal.fire({
                            title: 'Proses...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
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
                    $('#btn-simpan').prop('disabled', false); $('#btn-simpan').html('Simpan');
                }
            })
        })
        $("#btn-update").click(function () {
            var form = $("#form-edit");
            var formData = form.serialize();
            let tahun_ajaran = $('#form-edit select[name="tahun_ajaran"]').val();
            let semester = $('#form-edit input[name="semester"]').val();

            if (id == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'ID rencana pengeluaran tidak ditemukan.'
                });
                $('#btn-update').prop('disabled', false);
                $('#btn-update').html('Simpan');
                return;
            }

            if (tahun_ajaran == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tahun ajaran wajib dipilih.'
                });
                $('#btn-update').prop('disabled', false);
                $('#btn-update').html('Simpan');
                return;
            }
            $('#btn-update').prop('disabled', true);
            $('#btn-update').html('Sedang Diproses');
            $.ajax({
                url: '<?= base_url('admin/perencanaan/rencana_pengeluaran/edit'); ?>',
                type: 'POST',
                data: formData,
                cache: false,
                dataType: 'JSON',
                  beforeSend: function () {
                        Swal.fire({
                            title: 'Proses...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                success: function (data) {
                    if (data.status == true) {
                        $("#edit").modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil disimpan',
                        }).then(() => {
                            location.reload();
                        }); $("#form-edit")[0].reset();
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
            paging($('#data_rencana_pengeluaran .card-mapel'), jumlah);
        });
        $("#btn-simpan-persen-gaji").click(function () {
            $('#btn-simpan-persen-gaji').prop('disabled', true);
            $('#btn-simpan-persen-gaji').html('Diproses');
            var form = $("#form-persen-gaji");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/perencanaan/rencana_pengeluaran/edit_persen_gaji'); ?>',
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
                        $('#btn-simpan-persen-gaji').prop('disabled', false);
                        $('#btn-simpan-persen-gaji').html('Persen Gaji');
                    }
                }
            })
        });
        $('#persen').on('show.bs.modal', function () {
            $.ajax({
                url: '<?= base_url("admin/perencanaan/rencana_pengeluaran/get_persen_gaji") ?>',
                dataType: 'json',
                success: function (res) {
                    $('#nominal-persen').val(res.nominal_persen);
                }
            });
        });
    })

    var total_asumsi_global = 0;
    function ambilAsumsi() {
        let tahun_ajaran = $('#tambah select[name="tahun_ajaran"]').val();
        let semester = $('#tambah input[name="semester"]').val();

        if (tahun_ajaran == '') {
            return;
        }

        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pengeluaran/ambilAsumsi'); ?>',
            type: 'POST',
            data: {
                tahun_ajaran: tahun_ajaran,
                semester: semester
            },
            dataType: 'JSON',
            success: function (res) {
                if (!res.status) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Ada',
                        text: res.message
                    });

                    $('#list-pengeluaran').html('');
                    $('#total-asumsi').html('0');
                    $('#total-pengeluaran').html('0');
                    $('#sisa-asumsi').html('0');
                    $('#input-total-asumsi').val('');
                    $('#input-id-rencana-asumsi-pemasukan').val('');

                    return;
                }

                total_asumsi_global = parseFloat(res.total_asumsi || 0);

                $('#input-total-asumsi').val(total_asumsi_global);
                $('#input-id-rencana-asumsi-pemasukan').val(res.id_rencana_asumsi_pemasukan);

                $('#total-asumsi').html(NumberToMoney(total_asumsi_global));
                $('#total-pengeluaran').html('0');
                $('#sisa-asumsi').html(NumberToMoney(total_asumsi_global));
                $('#input-persen-gaji-rencana').val(res.persen_gaji || 0);

                generateTable(res);
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengambil data asumsi pemasukan.'
                });
            }
        });
    }

    const namaBulan = {
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

    function generateTable(res) {
        let bulan = res.bulan || [];
        let akun = res.akun || [];
        let defaultPengeluaran = res.default_pengeluaran || {};

        let minWidth = bulan.length >= 12 ? '1800px' : '1200px';

        let html = `
        <div class="table-responsive rencana-table-wrapper">
            <table class="table table-bordered table-sm rencana-table" style="min-width:${minWidth};">
                <thead>
                    <tr>
                        <th class="kolom-kategori">Kategori</th>`;

        bulan.forEach(function (b) {
            html += `<th class="kolom-bulan text-center">${namaBulan[b] || b}</th>`;
        });
        html += `
                    </tr>
                </thead>
                <tbody>`;

        akun.forEach(function (row) {
            let idAkun = row.id;
            let namaAkun = row.keterangan;
            let isGaji = namaAkun.toLowerCase().trim() === 'gaji';
            let persenGaji = parseFloat(res.persen_gaji || 0);
            html += `
            <tr>
                <td class="kolom-kategori">${namaAkun} ${isGaji ? `<br><small class="text-success">Rumus: ${persenGaji}% dari rata-rata asumsi bulanan</small>` : ''}</td>`;

            bulan.forEach(function (bln) {
                let nominal = 0;

                if (
                    defaultPengeluaran[idAkun] &&
                    defaultPengeluaran[idAkun][bln] !== undefined
                ) {
                    nominal = parseFloat(defaultPengeluaran[idAkun][bln] || 0);
                }

                // let readonly = '';
                // if (namaAkun.toLowerCase().trim().includes('gaji')) {
                //     readonly = 'readonly';
                // }

                html += `
                <td class="kolom-bulan">
                    <input type="text" class="form-control form-control-sm nominal text-end input-nominal-pengeluaran"
                        name="pengeluaran[${idAkun}][${bln}]" value="${nominal > 0 ? NumberToMoney(Math.round(nominal)) : ''}" onkeyup="FormatCurrency(this); hitungTotal();">
                </td>`;
            });

            html += `</tr>`;
        });

        html += `
                </tbody>
            </table>
        </div>`;

        $('#list-pengeluaran').html(html);
        hitungTotal();
    }
    function angka(nilai) {
        if (nilai === null || nilai === undefined || nilai === '') {
            return 0;
        }

        nilai = String(nilai).trim();
        nilai = nilai.replace(/[^\d.,-]/g, '');

        if (nilai === '') {
            return 0;
        }

        let lastComma = nilai.lastIndexOf(',');
        let lastDot = nilai.lastIndexOf('.');

        if (lastComma > -1 && lastDot > -1) {
            if (lastComma > lastDot) {
                nilai = nilai.replace(/\./g, '');
                nilai = nilai.replace(',', '.');
            } else {
                nilai = nilai.replace(/,/g, '');
            }
        } else if (lastComma > -1) {
            let parts = nilai.split(',');

            if (parts.length > 1 && parts[parts.length - 1].length <= 2) {
                nilai = nilai.replace(',', '.');
            } else {
                nilai = nilai.replace(/,/g, '');
            }
        } else if (lastDot > -1) {
            let parts = nilai.split('.');

            if (parts.length > 1 && parts[parts.length - 1].length <= 2) {
            } else {
                nilai = nilai.replace(/\./g, '');
            }
        }

        let hasil = parseFloat(nilai);

        return isNaN(hasil) ? 0 : hasil;
    }

    function hitungTotal() {
        let total = 0;

        $('#list-pengeluaran tbody tr').each(function () {
            let totalBaris = 0;

            $(this).find('.nominal').each(function () {
                totalBaris += angka($(this).val());
            });
            total += totalBaris;
        });

        let sisa = total_asumsi_global - total;
        console.log(total);
        $('#total-pengeluaran').html(NumberToMoney(Math.round(total)));
        $('#sisa-asumsi').html(NumberToMoney(Math.round(sisa)));
    }
    function rencana_pengeluaran() {
        var tahun_ajaran = $('#periode-rencana-pengeluaran').val();

        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pengeluaran/rencana_pengeluaran_result'); ?>',
            type: 'POST',
            data: {
                tahun_ajaran: tahun_ajaran
            },
            dataType: 'JSON',
            success: function (data) {
                $('#data_rencana_pengeluaran').empty();

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
                        // let detail = btoa(JSON.stringify(item));
                        table += `
                            <div class="card-mapel">
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.periode}</h5>   
                                      <p class="keterangan-jam-mapel">Total : Rp. ${NumberToMoney(item.total_rencana_pengeluaran)} </p>
                                </div>
                                 <div class="keterangan-mapel-kanan">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="detail('${item.id}')"><i class="ri-eye-line"></i></button>
                                        <button type="button" onclick="editpengeluaran('${item.id}')" class="btn btn-sm btn-outline-warning"><i class="ri-edit-2-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                }
                $('#data_rencana_pengeluaran').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_rencana_pengeluaran .card-mapel'), jumlah_awal);
            }
        });
    }
    function detail(id) {
        $.ajax({
            url: '<?= base_url("admin/perencanaan/rencana_pengeluaran/detail") ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function (res) {
                let header = res.header;
                let data = res.detail;

                let bulan = [];

                if (header.semester == 'Tahunan') {
                    //     bulan = ['07', '08', '09', '10', '11', '12'];
                    // } else if (header.semester == 'Genap') {
                    //     bulan = ['01', '02', '03', '04', '05', '06'];
                    // } else {
                    bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
                }

                // let grup = {};

                // data.forEach(function (row) {

                //     if (!grup[row.nama_akun]) {
                //         grup[row.nama_akun] = {};
                //     }

                //     // grup[row.nama_akun][row.bulan] = parseInt(row.nominal);
                //     grup[row.nama_akun][row.bulan] = {
                //         nominal: parseInt(row.nominal || 0),
                //         persen_gaji: row.persen_gaji
                //     };

                // });
                let grup = {};

                data.forEach(function (row) {

                    if (!grup[row.nama_akun]) {
                        grup[row.nama_akun] = {
                            persen_gaji: row.persen_gaji || null,
                            bulan: {}
                        };
                    }

                    // Simpan persen gaji cukup sekali di level akun
                    if (row.persen_gaji && parseFloat(row.persen_gaji) > 0) {
                        grup[row.nama_akun].persen_gaji = row.persen_gaji;
                    }

                    // Simpan nominal per bulan
                    grup[row.nama_akun].bulan[row.bulan] = parseInt(row.nominal || 0);

                });

                let html = `
<div class="alert alert-info">
    <div>
        <b>Pegawai :</b>
        ${res.header.nama_pegawai}
    </div>

    <div>
        <b>Tahun Ajaran :</b>
        ${res.header.nama_tahun_ajaran}
    </div>

    <hr>

    <div>
        <b>Total Asumsi Masuk :</b>
        Rp ${NumberToMoney(res.header.total_asumsi_pemasukan)}
    </div>

    <div>
        <b>Total Pengeluaran :</b>
        Rp ${NumberToMoney(res.total_pengeluaran)}
    </div>

    <div>
        <b>Sisa Asumsi :</b>
        Rp ${NumberToMoney(res.sisa_asumsi)}
    </div>

</div>
<div class="table-responsive rencana-table-wrapper">
    <table class="table table-bordered table-sm rencana-table" style="min-width:${bulan.length >= 12 ? '1800px' : '1200px'};">
                    <thead>
                        <tr>
                            <th>Kategori</th>`;

                bulan.forEach(function (bln) {
                    html += `<th>${namaBulan[bln]}</th>`;
                });

                html += `</tr></thead><tbody>`;
                let grandTotal = 0;
                Object.keys(grup).forEach(function (akun) {
                    let persenGaji = grup[akun].persen_gaji;
                    let isGaji = akun.toLowerCase().includes('gaji');
                    html += `<tr>`;
                    html += `<td>${akun} ${isGaji && persenGaji ? `<br><small class="text-success">Rumus: ${persenGaji}% dari rata-rata asumsi bulanan</small>` : ''}</td>`;
                    bulan.forEach(function (bln) {
                        let nominal = grup[akun].bulan[bln] || 0;
                        grandTotal += nominal;
                        html += `<td>Rp ${NumberToMoney(nominal)}</td>`;
                    });
                    html += `</tr>`;
                });
                html += `
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold;background:#f8f9fa">
                            <td colspan="${bulan.length + 1}">
                                Total Rencana Pengeluaran :
                                Rp ${NumberToMoney(grandTotal)}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>`;

                $('#detail-content').html(html);
                $('#modal-detail').modal('show');
            }
        });
    }
    var total_asumsi_global_edit = 0;
    function editpengeluaran(id) {
        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pengeluaran/ambil_edit') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function (res) {
                console.log(res);
                $('#edit').modal('show');

                $('#form-edit input[name="id"]').val(res.id);
                $('#form-edit select[name="tahun_ajaran"]').val(res.tahun_ajaran);
                $('#form-edit input[name="semester"]').val(res.semester);

                total_asumsi_global_edit = parseInt(res.total_asumsi || 0);
                $('#total-asumsi-edit').html(NumberToMoney(total_asumsi_global_edit));
                // $('#input-total-asumsi-edit').val(total_asumsi_global_edit);
                let persenGajiEdit = 0;

                res.detail.forEach(function (d) {
                    if (d.persen_gaji && parseFloat(d.persen_gaji) > 0) {
                        persenGajiEdit = d.persen_gaji;
                    }
                });

                $('#input-persen-gaji-rencana-edit').val(persenGajiEdit);

                generateTableEdit(
                    res.akun,
                    res.detail,
                    res.semester
                );
                hitungTotalEdit();
            }
        });
    }
    //     function generateTableEdit(akun, detail, semester) {
    //         let bulan = [];
    //         if (semester == 'Tahunan') {
    //             //     bulan = ['07', '08', '09', '10', '11', '12'];
    //             // } else if (semester == 'Genap') {
    //             //     bulan = ['01', '02', '03', '04', '05', '06'];
    //             // } else {
    //             bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
    //         }

    //         let minWidth = bulan.length >= 12 ? '1800px' : '1200px';
    //         let html = `
    //         <div class="table-responsive rencana-table-wrapper">
    // <table class="table table-bordered table-sm rencana-table" style="min-width:${minWidth};">
    //             <thead>
    //                 <tr>
    //                     <th>Kategori</th>`;

    //         bulan.forEach(function (bln) {
    //             html += `<th class="kolom-bulan text-center">${namaBulan[bln]}</th>`;
    //         });

    //         html += `</tr></thead><tbody>`;
    //         akun.forEach(function (row) {
    //             let namaAkun = row.keterangan || '';
    //             // let readonly = '';
    //             // if (namaAkun.toLowerCase().trim().includes('gaji')) {
    //             //     readonly = 'readonly';
    //             // }

    //             html += `<tr><td class="kolom-kategori">${namaAkun} ${isGaji ? `<br><small class="text-success">Rumus Gaji</small>` : ''}</td>`;
    //             bulan.forEach(function (bln) {
    //                 let persenGaji = '';

    //                 detail.forEach(function (d) {
    //                     if (d.kode_akun == row.id && d.bulan == bln) {
    //                         nominal = d.nominal;
    //                         persenGaji = d.persen_gaji || '';
    //                     }
    //                 });

    //                 let isGaji = namaAkun.toLowerCase().includes('gaji');

    //                 html += `
    //                 <td>
    //                     <input type="text"  class="form-control nominal" name="pengeluaran[${row.id}][${bln}]" value="${NumberToMoney(nominal)}" onkeyup="FormatCurrency(this); hitungTotalEdit();">
    //                 </td>`;
    //             });
    //             html += `</tr>`;
    //         });
    //         html += `</tbody></table></div>`;
    //         $('#list-pengeluaran-edit').html(html);
    //         hitungTotalEdit();
    //     }
    function generateTableEdit(akun, detail, semester) {
        let bulan = [];

        if (semester == 'Tahunan') {
            bulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        }

        let minWidth = bulan.length >= 12 ? '1800px' : '1200px';

        let html = `
    <div class="table-responsive rencana-table-wrapper">
        <table class="table table-bordered table-sm rencana-table" style="min-width:${minWidth};">
            <thead>
                <tr>
                    <th>Kategori</th>`;

        bulan.forEach(function (bln) {
            html += `<th class="kolom-bulan text-center">${namaBulan[bln]}</th>`;
        });

        html += `</tr></thead><tbody>`;

        akun.forEach(function (row) {
            let namaAkun = row.keterangan || '';
            let isGaji = namaAkun.toLowerCase().includes('gaji');

            let persenGajiBaris = '';

            detail.forEach(function (d) {
                if (d.kode_akun == row.id && d.persen_gaji) {
                    persenGajiBaris = d.persen_gaji;
                }
            });

            html += `
        <tr>
            <td class="kolom-kategori">
                ${namaAkun}
                ${isGaji && persenGajiBaris ? `<br><small class="text-success">Rumus: ${persenGajiBaris}% dari rata-rata asumsi bulanan</small>` : ''}
            </td>`;

            bulan.forEach(function (bln) {
                let nominal = 0;

                detail.forEach(function (d) {
                    if (d.kode_akun == row.id && d.bulan == bln) {
                        nominal = d.nominal;
                    }
                });

                html += `
            <td>
                <input type="text" class="form-control nominal" name="pengeluaran[${row.id}][${bln}]" value="${nominal > 0 ? NumberToMoney(nominal) : ''}" onkeyup="FormatCurrency(this); hitungTotalEdit();">
            </td>`;
            });

            html += `</tr>`;
        });

        html += `</tbody></table></div>`;

        $('#list-pengeluaran-edit').html(html);
        hitungTotalEdit();
    }

    function ambilAsumsiEdit() {
        let id = $('#form-edit input[name="id"]').val();
        let tahun_ajaran = $('#form-edit select[name="tahun_ajaran"]').val();
        let semester = $('#form-edit input[name="semester"]').val();

        if (tahun_ajaran == '') {
            return;
        }

        $.ajax({
            url: '<?= base_url("admin/perencanaan/rencana_pengeluaran/ambilAsumsiEdit") ?>',
            type: 'POST',
            data: {
                id: id,
                tahun_ajaran: tahun_ajaran,
                semester: semester
            },
            dataType: 'json',
            success: function (res) {
                if (!res.status) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: res.message
                    });
                    return;
                }

                total_asumsi_global_edit = parseFloat(res.total_asumsi || 0);

                $('#id-rencana-asumsi-pemasukan-edit').val(res.id_rencana_asumsi_pemasukan);
                $('#input-total-asumsi-edit').val(total_asumsi_global_edit);

                $('#total-asumsi-edit').html(NumberToMoney(Math.round(total_asumsi_global_edit)));
                $('#total-pengeluaran-edit').html('0');
                $('#sisa-asumsi-edit').html(NumberToMoney(Math.round(total_asumsi_global_edit)));
                $('#input-persen-gaji-rencana-edit').val(res.persen_gaji || 0);
                generateTableEditBaru(res);
                hitungTotalEdit();
            }
        });
    }
    function generateTableEditBaru(res) {
        let bulan = res.bulan || [];
        let akun = res.akun || [];
        let defaultPengeluaran = res.default_pengeluaran || {};
        let minWidth = bulan.length >= 12 ? '1800px' : '1200px';

        let html = `
        <div class="table-responsive rencana-table-wrapper">
            <table class="table table-bordered table-sm rencana-table" style="min-width:${minWidth};">
                <thead>
                    <tr>
                        <th class="kolom-kategori">Kategori</th>`;

        bulan.forEach(function (bln) {
            html += `<th class="kolom-bulan text-center">${namaBulan[bln] || bln}</th>`;
        });

        html += `</tr></thead><tbody>`;

        // akun.forEach(function (row) {
        //     let idAkun = row.id;
        //     let namaAkun = row.keterangan;

        //     html += `<tr><td class="kolom-kategori">${namaAkun}</td>`;

        //     bulan.forEach(function (bln) {
        //         let nominal = 0;
        //         if (
        //             defaultPengeluaran[idAkun] &&
        //             defaultPengeluaran[idAkun][bln] !== undefined
        //         ) {
        //             nominal = parseFloat(defaultPengeluaran[idAkun][bln] || 0);
        //         }

        //         // let readonly = '';
        //         // if (namaAkun.toLowerCase().trim().includes('gaji')) {
        //         //     readonly = 'readonly';
        //         // }

        //         html += `
        //         <td class="kolom-bulan">
        //             <input type="text"  class="form-control form-control-sm nominal text-end input-nominal-pengeluaran" name="pengeluaran[${idAkun}][${bln}]" value="${nominal > 0 ? NumberToMoney(Math.round(nominal)) : ''}" onkeyup="FormatCurrency(this); hitungTotalEdit();">
        //         </td>`;
        //     });
        //     html += `</tr>`;
        // });
        akun.forEach(function (row) {
            let idAkun = row.id;
            let namaAkun = row.keterangan || '';
            let isGaji = namaAkun.toLowerCase().includes('gaji');
            let persenGaji = parseFloat(res.persen_gaji || 0);

            html += `
    <tr>
        <td class="kolom-kategori">
            ${namaAkun}
            ${isGaji ? `<br><small class="text-success">Rumus: ${persenGaji}% dari rata-rata asumsi bulanan</small>` : ''}
        </td>`;

            bulan.forEach(function (bln) {
                let nominal = 0;

                if (
                    defaultPengeluaran[idAkun] &&
                    defaultPengeluaran[idAkun][bln] !== undefined
                ) {
                    nominal = parseFloat(defaultPengeluaran[idAkun][bln] || 0);
                }

                html += `
        <td class="kolom-bulan">
            <input type="text"
                class="form-control form-control-sm nominal text-end input-nominal-pengeluaran"
                name="pengeluaran[${idAkun}][${bln}]"
                value="${nominal > 0 ? NumberToMoney(Math.round(nominal)) : ''}"
                onkeyup="FormatCurrency(this); hitungTotalEdit();">
        </td>`;
            });

            html += `</tr>`;
        });

        html += `</tbody></table></div>`;

        $('#list-pengeluaran-edit').html(html);
        hitungTotalEdit();
    }
    function hitungTotalEdit() {
        let total = 0;

        $('#list-pengeluaran-edit tbody tr').each(function () {
            let totalBaris = 0;

            $(this).find('.nominal').each(function () {
                totalBaris += angka($(this).val());
            });
            total += totalBaris;
        });

        let sisa = total_asumsi_global_edit - total;

        $('#total-pengeluaran-edit').html(NumberToMoney(Math.round(total)));
        $('#sisa-asumsi-edit').html(NumberToMoney(Math.round(sisa)));
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
                    url: `<?= base_url(); ?>admin/perencanaan/rencana_pengeluaran/hapus`,
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
                            }).then(() => {
                                //  location.reload(); 
                                rencana_pengeluaran();
                            });
                        }

                    }
                })
            }
        })
    }

</script>