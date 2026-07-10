<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tambah"><i
                class="ri-add-line"></i>Tambah</button>

    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="periode-rencana-pemasukan" class=" form-control ">
                            <option value="">Pilih Periode</option>
                            <?php foreach ($tahun_ajaran as $tahunajaran): ?>
                                <option value="<?= $tahunajaran['id'] ?>"><?= $tahunajaran['periode'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-4">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="semester-rencana-pemasukan" class=" form-control ">
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
                    <button type="button" class="btn btn-sm btn-primary" onclick="rencana_pemasukan()"><i
                            class="ri-search-line"></i></button>
                </div>
            </div>
        </div>

        <div id="data_rencana_pemasukan">

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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran" class="form-control" onchange="cek_rab_pemasukan()">
                                    <option value="">Pilih Tahun Ajaran</option>
                                    <?php foreach ($tahun_ajaran as $tahunajaran): ?>
                                        <option value="<?= $tahunajaran['id'] ?>"><?= $tahunajaran['periode'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-control" onchange="cek_rab_pemasukan()">
                                    <option value="">Pilih Semester</option>
                                    <option value="Tahunan">Tahunan</option>
                                    <option value="Ganjil">Ganjil</option>
                                    <option value="Genap">Genap</option>
                                </select>
                            </div>
                        </div> -->
                        <input type="hidden" name="semester" value="Tahunan">
                    </div>

                    <!-- LIST JENIS -->
                    <div id="list-jenis"></div>

                    <button type="button" class="btn btn-primary btn-sm mt-2" onclick="tambahJenis()">
                        + Tambah Jenis
                    </button>

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
                <h4>Edit Rencana Pemasukan</h4>
            </div>
            <div class="modal-body">
                <form id="form-edit">
                    <input type="hidden" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran" class="form-control">
                                    <option value="">Pilih Tahun Ajaran</option>
                                    <?php foreach ($tahun_ajaran as $tahunajaran): ?>
                                        <option value="<?= $tahunajaran['id'] ?>"><?= $tahunajaran['periode'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="semester" value="Tahunan">
                        <!-- <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-control">
                                    <option value="">Pilih Semester</option>
                                    <option value="Tahunan">Tahunan</option>
                                    <option value="Ganjil">Ganjil</option>
                                    <option value="Genap">Genap</option>
                                </select>
                            </div>
                        </div> -->
                    </div>
                    <div id="list-jenis-edit"></div>
                    <button type="button" class="btn btn-primary" onclick="tambahJenis('edit')">
                        + Tambah Jenis
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary" id="btn-update">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Detail Rencana Pemasukan</h4>
            </div>
            <div class="modal-body" id="detail-content"></div>
            <div class=" modal-footer">
                <button type="button" class="btn btn-primary" onclick="printDetail()">Print</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-asumsi-pemasukan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-full-width modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Rencana Asumsi Pemasukan</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-asumsi-pemasukan">
                    <input type="hidden" name="id_rencana_pemasukan" id="asumsi-id-rencana-pemasukan">
                    <input type="hidden" name="tahun_ajaran" id="asumsi-tahun-ajaran">
                    <input type="hidden" name="semester" id="asumsi-semester">
                    <div class="row mb-3">
                        <div class=" me-3">
                            Tahun Ajaran : <span id="header-tahun-ajaran-asumsi"></span>
                        </div>
                        <!-- <div class="col-md-4">
                            <label class="form-label">Tahun Ajaran</label>
                        </div> -->
                        <!-- <div class="col-md-4">
                            <label class="form-label">Semester</label> -->
                        <input type="hidden" class="form-control" id="asumsi-label-tahun-ajaran" readonly>
                        <input type="hidden" class="form-control" id="asumsi-label-semester" readonly>
                        <!-- </div> -->
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead id="thead-asumsi-pemasukan"></thead>
                            <tbody id="tbody-asumsi-pemasukan"></tbody>
                            <tfoot id="tfoot-asumsi-pemasukan"></tfoot>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-simpan-asumsi-pemasukan">
                    Simpan Asumsi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        flatpickr("#flatpicker", {
            dateFormat: "d-m-Y",
            defaultDate: "today",
            allowInput: true
        });
        rencana_pemasukan();
        $("#btn-simpan").click(function () {
            let tahun_ajaran = $('#form-tambah select[name="tahun_ajaran"]').val();

            if (tahun_ajaran == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tahun ajaran wajib dipilih.'
                });
                return;
            }
            $('#btn-simpan').prop('disabled', true);
            $('#btn-simpan').html('Sedang Diproses');
            var form = $("#form-tambah");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/perencanaan/rencana_pemasukan/tambah'); ?>',
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
                    $('#btn-simpan').prop('disabled', false);
                    $('#btn-simpan').html('Simpan');
                }
            })
        });
        $("#btn-update").click(function () {
            let tahun_ajaran = $('#form-edit select[name="tahun_ajaran"]').val();

            if (tahun_ajaran == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tahun ajaran wajib dipilih.'
                });
                return;
            }
            var form = $("#form-edit");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/perencanaan/rencana_pemasukan/edit'); ?>',
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
        });
        $("#btn-simpan-asumsi-pemasukan").click(function () {
            $('#btn-simpan-asumsi-pemasukan').prop('disabled', true);
            $('#btn-simpan-asumsi-pemasukan').html('Sedang Diproses');

            let formData = $("#form-asumsi-pemasukan").serialize();

            $.ajax({
                url: '<?= base_url('admin/perencanaan/rencana_pemasukan/simpan_asumsi_pemasukan'); ?>',
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
                success: function (res) {
                    if (res.status == true) {
                        $('#modal-asumsi-pemasukan').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Rencana asumsi pemasukan berhasil disimpan'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Gagal menyimpan asumsi pemasukan'
                        });
                    }

                    $('#btn-simpan-asumsi-pemasukan').prop('disabled', false);
                    $('#btn-simpan-asumsi-pemasukan').html('Simpan Asumsi');
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan data'
                    });

                    $('#btn-simpan-asumsi-pemasukan').prop('disabled', false);
                    $('#btn-simpan-asumsi-pemasukan').html('Simpan Asumsi');
                }
            });
        });
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_rencana_pemasukan .card-mapel'), jumlah);
        });
    })

    let indexJenis = 0;
    let editIndexJenis = 0;
    function hitungBaris(el) {
        let tr = $(el).closest('tr');
        let volume = angka(tr.find('.volume').val());
        let nilai = angka(tr.find('.nilai_satuan').val());
        let volumePenerimaan = angka(tr.find('.volume_penerimaan').val());

        // jumlah
        let jumlah = volume * nilai;

        // total
        let total = jumlah * volumePenerimaan;
        tr.find('.jumlah').val(NumberToMoney(jumlah));
        tr.find('.total').val(NumberToMoney(total));

        hitungFooter(tr.closest('.card-body'));
    }
    function angka(num) {
        return parseInt((num || '0').toString().replace(/[.,]/g, '')) || 0;
    }

    function hitungFooter(card) {
        // let jumlah = 0;
        // let total = 0;
        // card.find('.jumlah').each(function () {
        //     jumlah += angka($(this).val());
        // });

        // card.find('.total').each(function () {
        //     total += angka($(this).val());
        // });

        // card.find('.footer-jumlah').html(NumberToMoney(jumlah));
        // card.find('.footer-total').html(NumberToMoney(total));
        let jumlah = 0;
        let totalPotensi = 0;

        card.find('.jumlah').each(function () { jumlah += angka($(this).val()); });
        card.find('.total').each(function () { totalPotensi += angka($(this).val()); });
        let persenMasuk = parseFloat(card.find('.persen-masuk').val().replace(',', '.')) || 0;
        let totalRencana = totalPotensi * (persenMasuk / 100);
        card.find('.footer-jumlah').html(NumberToMoney(jumlah));
        card.find('.footer-total').html(NumberToMoney(totalPotensi));
        card.find('.footer-total-rencana').html(NumberToMoney(totalRencana));
    }

    function batasiPersen(el) {
        let nilai = el.value;
        // hanya izinkan angka
        nilai = nilai.replace(/[^0-9]/g, '');
        // batasi maksimal 100
        if (parseInt(nilai || 0) > 100) {
            nilai = '100';
        }
        el.value = nilai;
    }

    function tambahJenis(mode = 'tambah', data = {}) {
        let container = mode == 'edit' ? '#list-jenis-edit' : '#list-jenis';
        let currentIndex = mode == 'edit' ? editIndexJenis : indexJenis;
        let html = `
    <div class="card mt-3 jenis-item">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4">
                    <label>Kode Akun</label>
                    <select name="jenis[${currentIndex}][kode_akun]" class="form-control kode-akun">
                        <option value="">Pilih Kode Akun</option>
                        <?php foreach ($kode_akun as $ka) { ?>
                        <option value="<?= $ka['id']; ?>"><?= $ka['keterangan']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Persen Masuk %</label>
                    <input type="text" name="jenis[${currentIndex}][persen_masuk]" class="form-control persen-masuk" value="${data.persen_masuk || ''}" onkeyup="batasiPersen(this);hitungFooter($(this).closest('.card-body'))" maxlength="3">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger w-100" onclick="$(this).closest('.jenis-item').remove()">Hapus</button>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Volume</th>
                        <th>Nilai Satuan</th>
                        <th>Jumlah</th>
                        <th>Satuan Penerimaan</th>
                        <th>Volume Penerimaan</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="list-detail"></tbody>
                <tfoot>
                  <tr>
        <th colspan="4" class="text-end">
            Total :
        </th>
        <th class="footer-jumlah">0</th>
        <th></th>
        <th></th>
        <th class="footer-total">0</th>
        <th></th>
    </tr>

    <tr>
        <th colspan="7" class="text-end">
            Total Setelah Persen Masuk :
        </th>
        <th class="footer-total-rencana">0</th>
        <th></th>
    </tr>
                </tfoot>
            </table>
            <button type="button" class="btn btn-success btn-sm" onclick="tambahDetail(this,${currentIndex})"> + Detail </button>
        </div>
    </div>`;

        $(container).append(html);
        let card = $(container).find('.jenis-item').last();
        if (data.kode_akun) {
            card.find('.kode-akun').val(data.kode_akun);
        }
        if (data.detail) {
            data.detail.forEach(function (d) {
                tambahDetail(card.find('.btn-success'), currentIndex, d);
            });
        }

        if (mode == 'edit') {
            editIndexJenis++;
        } else {
            indexJenis++;
        }

    }
    function tambahDetail(btn, indexJenis, data = {}) {
        let tbody = $(btn).closest('.card-body').find('.list-detail');
        let indexDetail = tbody.children().length;

        let html = `
    <tr>
        <td>
            <input type="text" name="jenis[${indexJenis}][detail][${indexDetail}][nama_kategori]" class="form-control" value="${data.nama_kategori || ''}">
        </td>

        <td>
            <input type="text" name="jenis[${indexJenis}][detail][${indexDetail}][satuan]" class="form-control" value="${data.satuan || ''}">
        </td>
        <td>
            <input type="text" name="jenis[${indexJenis}][detail][${indexDetail}][volume]" class="form-control volume" value="${NumberToMoney(data.volume || 0)}" onkeyup="FormatCurrency(this);hitungBaris(this);">
        </td>
        <td>
            <input type="text" name="jenis[${indexJenis}][detail][${indexDetail}][nilai_satuan]" class="form-control nilai_satuan" value="${NumberToMoney(data.nilai_satuan || 0)}" onkeyup="FormatCurrency(this);hitungBaris(this);">
        </td>
        <td>
            <input readonly type="text" name="jenis[${indexJenis}][detail][${indexDetail}][jumlah]" class="form-control jumlah" value="${NumberToMoney(data.jumlah || 0)}">
        </td>
        <td>
            <input type="text" name="jenis[${indexJenis}][detail][${indexDetail}][satuan_penerimaan]" class="form-control" value="${data.satuan_penerimaan || ''}" placeholder="bulan, keg">
        </td>
        <td>
            <input type="text" name="jenis[${indexJenis}][detail][${indexDetail}][volume_penerimaan]" class="form-control volume_penerimaan" value="${NumberToMoney(data.volume_penerimaan || 0)}" onkeyup="FormatCurrency(this); hitungBaris(this);">
        </td>

        <td>
            <input readonly type="text" name="jenis[${indexJenis}][detail][${indexDetail}][total]" class="form-control total" value="${NumberToMoney(data.total || 0)}"></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove(); hitungFooter($(this).closest('.card-body'));">X</button>
        </td>
    </tr>`;
        tbody.append(html);
        hitungFooter(tbody.closest('.card-body'));
    }

    function rencana_pemasukan() {
        var periode = $("#periode-rencana-pemasukan").val();
        // var semester = $("#semester-rencana-pemasukan").val();
        $.ajax({
            url: '<?= base_url("admin/perencanaan/rencana_pemasukan/rencana_pemasukan_result"); ?>',
            type: "POST",
            data: {
                periode: periode
            },
            dataType: "JSON",
            success: function (data) {
                $('#data_rencana_pemasukan').empty();
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
                        </div>`;
                } else {
                    data.forEach(function (item) {
                        // let detail = btoa(JSON.stringify(item));
                        table += `
                            <div class="card-mapel">
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.periode}</h5>   
                                    <p class="keterangan-jam-mapel">Total : Rp ${NumberToMoney(item.total ?? 0)} </p>
                                </div>
                                 <div class="keterangan-mapel-kanan">
                                    <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="detail('${item.id}', '${item.nama_pegawai ?? '-'}', '${item.periode ?? '-'}', '${item.semester ?? '-'}','${item.tahun_ajaran ?? '-'}')"><i class="ri-eye-line"></i></button>
                                    <button type="button" onclick="bukaAsumsiPemasukan(${item.id})"  class="btn btn-sm btn-outline-success"><i class="ri-money-dollar-circle-line"></i></button>
                                    <button type="button" onclick="editrencana_pemasukan(${item.id})" class="btn btn-sm btn-outline-warning"><i class="ri-edit-2-line"></i></button>
                                    <button class="btn btn-sm btn-outline-danger " onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                }
                $('#data_rencana_pemasukan').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_rencana_pemasukan .card-mapel'), jumlah_awal);
            }
        });
    }

    let listBulanAsumsi = [];

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

    function bukaAsumsiPemasukan(id) {
        $('#tbody-asumsi-pemasukan').html(`
        <tr>
            <td colspan="20" class="text-center">Memuat data...</td>
        </tr>
    `);

        $('#modal-asumsi-pemasukan').modal('show');
        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pemasukan/get_data_asumsi_pemasukan'); ?>',
            type: 'POST',
            data: {
                id_rencana_pemasukan: id
            },
            dataType: 'JSON',
            success: function (res) {
                if (res.status == false) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Data tidak ditemukan'
                    });
                    $('#modal-asumsi-pemasukan').modal('hide');
                    return;
                }

                $('#asumsi-id-rencana-pemasukan').val(res.header.id_rencana_pemasukan);
                $('#asumsi-tahun-ajaran').val(res.header.tahun_ajaran);
                $('#asumsi-semester').val(res.header.semester);

                $('#asumsi-label-tahun-ajaran').val(res.header.periode);
                $('#header-tahun-ajaran-asumsi').text(res.header.periode);
                $('#asumsi-label-semester').val(res.header.semester);

                listBulanAsumsi = res.list_bulan;
                renderTabelAsumsi(res.data, res.list_bulan);
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengambil data asumsi pemasukan'
                });
                $('#modal-asumsi-pemasukan').modal('hide');
            }
        });
    }

    function renderTabelAsumsi(data, listBulan) {
        let thead = `<tr>
            <th style="min-width:180px;">Kategori</th>
            <th style="min-width:130px;">Total Asumsi Masuk</th>
            <th style="min-width:90px;">% Masuk</th>
            <th style="min-width:130px;">Asumsi Masuk</th>
            <th style="min-width:130px;">Saving</th>
            <th style="min-width:100px;">% Saving</th>`;

        listBulan.forEach(function (b) {
            thead += `<th style="min-width:130px;">${namaBulan[b]}</th>`;
        });

        thead += `<th style="min-width:130px;">Total Bulan</th>`;
        thead += `</tr>`;

        $('#thead-asumsi-pemasukan').html(thead);

        let tbody = '';

        data.forEach(function (item, index) {
            let totalAsumsiMasuk = angka(item.total_asumsi_masuk || item.total_rencana || 0);
            let persenMasuk = parseFloat(item.persen_masuk || 0);
            let asumsiMasuk = totalAsumsiMasuk * (persenMasuk / 100);
            let savingNormal = totalAsumsiMasuk - asumsiMasuk;
            let savingPersen = totalAsumsiMasuk > 0 ? savingNormal / totalAsumsiMasuk : 0;

            tbody += `
            <tr class="baris-asumsi" data-index="${index}">
                <td>
                    <input type="hidden" name="detail[${index}][kode_akun]" value="${item.kode_akun}">
                    <input type="hidden" name="detail[${index}][total_asumsi_masuk]" class="total-asumsi-masuk-raw" value="${totalAsumsiMasuk}">
                    <input type="hidden" name="detail[${index}][asumsi_masuk]" class="asumsi-masuk-raw" value="${asumsiMasuk}">
                    <input type="hidden" name="detail[${index}][saving_normal]" class="saving-normal-raw" value="${savingNormal}">
                    <input type="hidden" name="detail[${index}][saving_persen]" class="saving-persen-raw" value="${savingPersen}">

                    <b>${item.kategori}</b>
                    <br>
                    <small class="text-muted">
                        ${item.satuan_penerimaan || '-'} ${item.volume_penerimaan || ''}
                    </small>
                </td>

                <td class="text-end">
                    ${NumberToMoney(totalAsumsiMasuk)}
                </td>

                <td>
                    ${persenMasuk}%
                    <input type="hidden" name="detail[${index}][persen_masuk]" class="form-control form-control-sm persen-asumsi" value="${persenMasuk}" readonly>
                </td>

                <td class="text-end asumsi-masuk-label">
                    ${NumberToMoney(asumsiMasuk)}
                </td>

                <td class="text-end saving-normal-label">
                    ${NumberToMoney(savingNormal)}
                </td>

                <td class="text-end saving-persen-label">
                    ${savingPersen.toFixed(2)}
                </td>`;

            listBulan.forEach(function (b) {
                let nilaiBulan = 0;
                if (item.bulan && item.bulan[b]) {
                    nilaiBulan = angka(item.bulan[b]);
                }

                tbody += `
                <td>
                    <input type="text"
                        name="detail[${index}][bulan][${b}]"
                        class="form-control form-control-sm nominal-bulan"
                        data-bulan="${b}"
                        value="${NumberToMoney(nilaiBulan)}"
                        onkeyup="FormatCurrency(this); hitungTotalBarisAsumsi($(this).closest('tr'));">
                </td>`;
            });

            tbody += `
                <td class="text-end total-baris-asumsi">0</td>
            </tr>
        `;
        });

        $('#tbody-asumsi-pemasukan').html(tbody);

        $('#tbody-asumsi-pemasukan .baris-asumsi').each(function () {
            hitungTotalBarisAsumsi($(this));
        });

        renderFooterAsumsi(listBulan);
        hitungTotalFooterAsumsi();
    }

    function hitungBarisAsumsi(el) {
        let tr = $(el).closest('tr');

        let totalAsumsiMasuk = angka(tr.find('.total-asumsi-masuk-raw').val());
        let persenMasuk = parseFloat((tr.find('.persen-asumsi').val() || '0').replace(',', '.')) || 0;

        let asumsiMasuk = totalAsumsiMasuk * (persenMasuk / 100);
        let savingNormal = totalAsumsiMasuk - asumsiMasuk;
        let savingPersen = totalAsumsiMasuk > 0 ? savingNormal / totalAsumsiMasuk : 0;
        // let savingPersen = totalAsumsiMasuk > 0 ? (100 - persenMasuk) : 0;

        tr.find('.asumsi-masuk-raw').val(asumsiMasuk);
        tr.find('.saving-normal-raw').val(savingNormal);
        tr.find('.saving-persen-raw').val(savingPersen);

        tr.find('.asumsi-masuk-label').html(NumberToMoney(asumsiMasuk));
        tr.find('.saving-normal-label').html(NumberToMoney(savingNormal));
        tr.find('.saving-persen-label').html(savingPersen.toFixed(2).replace('.', ','));
        // tr.find('.saving-persen-label').html(savingPersen.toFixed(2) + '%');

        hitungTotalFooterAsumsi();
    }
    function hitungTotalBarisAsumsi(tr) {
        let total = 0;

        tr.find('.nominal-bulan').each(function () {
            total += angka($(this).val());
        });

        tr.find('.total-baris-asumsi').html(NumberToMoney(total));

        hitungTotalFooterAsumsi();
    }

    function renderFooterAsumsi(listBulan) {
        let html = `
        <tr><th colspan="6" class="text-end">Jumlah</th>`;

        listBulan.forEach(function (b) {
            html += `<th class="text-end total-footer-bulan" data-bulan="${b}">0</th>`;
        });

        html += `<th class="text-end" id="grand-total-asumsi">0</th>`;
        html += `</tr>`;

        $('#tfoot-asumsi-pemasukan').html(html);
    }
    function hitungTotalFooterAsumsi() {
        let grandTotal = 0;

        listBulanAsumsi.forEach(function (b) {
            let totalBulan = 0;

            $(`.nominal-bulan[data-bulan="${b}"]`).each(function () {
                totalBulan += angka($(this).val());
            });

            $(`.total-footer-bulan[data-bulan="${b}"]`).html(NumberToMoney(totalBulan));
            grandTotal += totalBulan;
        });

        $('#grand-total-asumsi').html(NumberToMoney(grandTotal));
    }
    // perlu penyesuaian nya
    let printData = {};
    function detail(id, nama_pegawai, periode, semester, tahun_ajaran) {
        printData = {
            semester: semester,
            tahun_ajaran: tahun_ajaran
        };
        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pemasukan/detail'); ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'JSON',
            success: function (res) {

                let html = '';
                html += `<p>Pegawai: ${nama_pegawai}<br>Periode: ${periode}</p>`;
                res.forEach(jenis => {
                    let totalJumlah = 0;
                    let totalAkhir = 0;
                    let totalNilai_satuan = 0;
                    let totalRencana = 0;

                    let total_setelah_persen = '';
                    let persen = ``;
                    if (jenis.persen_masuk) {
                        persen = ` - Persen Masuk: ${jenis.persen_masuk}%`;
                    }
                    html += `
                <h5>${jenis.nama_jenis}</h5>
                <p>${persen}</p>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Vol</th>
                        <th>Nilai Satuan</th>
                        <th>Jumlah</th>
                        <th>Satuan Penerimaan</th>
                        <th>Volume Penerimaan</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>`;

                    jenis.detail.forEach(d => {
                        totalJumlah += parseInt(d.jumlah || 0);
                        totalNilai_satuan += parseInt(d.nilai_satuan || 0);
                        totalAkhir += parseInt(d.total || 0);
                        if (jenis.persen_masuk) {
                            totalRencana = totalAkhir * (parseFloat(jenis.persen_masuk) / 100);
                            total_setelah_persen = `<tr style="font-weight:bold;background:#e8f5e9">
                            <td colspan="7" class="text-end">
                                Total Setelah Persen Masuk :
                            </td>
                            <td>
                                Rp ${NumberToMoney(totalRencana)}
                            </td>
                        </tr>`;
                        } else {
                            totalRencana = totalAkhir;
                            total_setelah_persen = ``;
                        }

                        html += `
                    <tr>
                        <td>${d.nama_kategori}</td>
                        <td>${d.satuan}</td>
                        <td>${d.volume}</td>
                        <td>Rp ${NumberToMoney(d.nilai_satuan)}</td>
                        <td>Rp ${NumberToMoney(d.jumlah)}</td>
                        <td>${d.satuan_penerimaan}</td>
                        <td>${d.volume_penerimaan}</td>
                        <td>Rp ${NumberToMoney(d.total)}</td>
                    </tr>
                    `;
                    });
                    html += `
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold;background:#f8f9fa">
                            <td colspan="3" class="text-end">
                                Total :
                            </td>
                            <td>
                                Rp ${NumberToMoney(totalNilai_satuan)}
                            </td>
                            <td>
                                Rp ${NumberToMoney(totalJumlah)}
                            </td>
                            <td></td>
                            <td></td>
                            <td>
                                Rp ${NumberToMoney(totalAkhir)}
                            </td>
                        </tr>

                        ${total_setelah_persen}
                    </tfoot>`;
                    html += `</table>`;
                });

                $("#detail-content").html(html);
                $("#modal-detail").modal('show');
            }
        });
    }

    function printDetail() {
        let form = $('<form>', {
            method: 'POST',
            action: '<?= base_url("admin/perencanaan/rencana_pemasukan/print_laporan") ?>',
            target: '_blank'
        });

        // form.append('<input type="hidden" name="single_filter_tahun" value="' + printData.tahun + '">');
        form.append('<input type="hidden" name="semester" value="' + printData.semester + '">');
        form.append('<input type="hidden" name="id_periode" value="' + printData.tahun_ajaran + '">');

        $('body').append(form);
        form.submit();
        form.remove();
    }

    function editrencana_pemasukan(id) {
        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pemasukan/detail_edit') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function (res) {
                $('#list-jenis-edit').html('');
                $('#edit').modal('show');
                $('#form-edit input[name=id]').val(res.id);
                $('#form-edit select[name=tahun_ajaran]').val(res.tahun_ajaran);
                $('#form-edit input[name=semester]').val(res.semester);
                editIndexJenis = 0;
                res.jenis.forEach(function (jenis) {
                    tambahJenis('edit', jenis);
                });
            }
        });
    }

    function cek_rab_pemasukan() {
        let tahun_ajaran = $('#tambah select[name=tahun_ajaran]').val();
        if (tahun_ajaran == '') {
            $('#list-jenis').html('');
            return;
        }
        let semester = $('#tambah input[name=semester]').val();
        $.ajax({
            url: '<?= base_url('admin/perencanaan/rencana_pemasukan/cek_rab_pemasukan') ?>',
            type: 'POST',
            data: {
                tahun_ajaran: tahun_ajaran,
                semester: semester,
            },
            dataType: 'json',
            success: function (res) {
                if (res.status == 'ada') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data sudah ada',
                        text: `Rencana pemasukan untuk tahun ajaran ${res.nama_tahun_ajaran} dan semester ${semester} ini sudah dibuat. Silahkan mengeditnya`
                    });
                    // kosongkan dulu
                    $('#list-jenis').html('');
                } else {
                    // kosongkan dulu
                    $('#list-jenis').html('');
                    tambahJenis();
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
                    url: '<?= base_url(); ?>admin/perencanaan/rencana_pemasukan/hapus',
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
                            }).then(() => { rencana_pemasukan(); });
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