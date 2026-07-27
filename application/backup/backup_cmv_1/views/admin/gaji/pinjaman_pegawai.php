<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row" id="pencarian">
            <div class="col-md-3">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" id="flatpicker" placeholder="Tanggal Dari"
                        aria-describedby="inputGroupPrepend" name="tanggal_mulai">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-line"></i></span>
                    <input type="text" class="form-control" name="tanggal_akhir" id="flatpicker"
                        placeholder="Tanggal Sampai" aria-describedby="inputGroupPrepend">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
                            aria-describedby="inputGroupPrepend" onkeyup="pinjaman_pegawai()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary" onclick="pinjaman_pegawai()"><i
                        class="ri-search-line"></i></button>
            </div>
        </div>

        <div id="data_pinjaman_pegawai">

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
                    <div class="form-group">
                        <label for="bulan-awal" class="form-label">Bulan Awal</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <select id="bulan_awal" name="bulan_awal" class="form-control">
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
                                            for ($i = 1; $i <= 12; $i++) {
                                                $val = sprintf("%02d", $i);
                                                $selected = ($val == $bulan_now) ? 'selected' : '';

                                                echo '<option value="' . $val . '" ' . $selected . '>' . $bulan[$i - 1] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <select id="tahun_awal" name="tahun_awal" class="form-control">
                                            <option value="">Pilih Tahun</option>
                                            <?php
                                            $now = date('Y');
                                            $periode_tahun_selected = '';
                                            for ($a = 2025; $a <= $now; $a++) {
                                                $selected = ($a == $now) ? 'selected' : '';
                                                echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bulan-akhir" class="form-label">Bulan Akhir</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <select id="bulan_akhir" name="bulan_akhir" class="form-control">
                                            <option value="">Pilih Bulan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <select id="tahun_akhir" name="tahun_akhir" class="form-control">
                                            <option value="">Pilih Tahun</option>
                                            <?php
                                            $now = date('Y');
                                            $periode_tahun_selected = '';
                                            for ($a = 2025; $a <= $now; $a++) {
                                                $selected = ($a == $now) ? 'selected' : '';
                                                echo '<option value="' . $a . '" ' . $selected . '>' . $a . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lama-pinjaman" class="form-label">Lama Pinjaman</label>
                                <div class="input-group">
                                    <input type="text" name="lama_pinjaman" class="form-control" readonly
                                        onkeyup="FormatCurrency(this);" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nilai_pinjaman" class="form-label">Total Pinjaman</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="nilai_pinjaman" class="form-control"
                                        placeholder="Total Pinjaman ..." onkeyup="FormatCurrency(this);" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="generate">Generate Pembayaran</button>
                    <div id="hasil_generate"></div>
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
                    <input type="hidden" name="id_pinjaman">
                    <div>
                        <label>Nama Pegawai</label>
                        <select class="form-control" disabled name="id_pegawai"></select>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label>Bulan Awal</label>
                            <input type="text" name="bulan_awal_text" class="form-control" disabled>
                        </div>
                        <div class="col-md-3">
                            <label>Tahun Awal</label>
                            <input type="text" name="tahun_awal" class="form-control" disabled>
                        </div>

                        <div class="col-md-3">
                            <label>Bulan Akhir</label>
                            <input type="text" name="bulan_akhir_text" class="form-control" disabled>
                        </div>
                        <div class="col-md-3">
                            <label>Tahun Akhir</label>
                            <input type="text" name="tahun_akhir" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Lama Pinjaman</label>
                            <input type="text" name="lama_pinjaman" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label>Total Pinjaman</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="nilai_pinjaman" class="form-control" disabled>
                            </div>
                        </div>
                    </div>
                    <div id="hasil_generate_edit"></div>
                </form>
            </div>
            <div class=" modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailPinjaman" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Detail Pinjaman
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <div class="detail_informasi"></div>
                <div class="mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Bulan</th>
                                <th class="text-center">Nominal Tagihan</th>
                            </tr>
                        </thead>

                        <tbody id="detail_table">

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

<script>
    const namaBulan = [
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
    ];
    $(document).ready(function () {
        pegawai();
        pinjaman_pegawai();
        $("#btn-simpan").click(function () {
            $('#btn-simpan').prop('disabled', true);
            $('#btn-simpan').html('Sedang Diproses');
            var form = $("#form-tambah");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/gaji/pinjaman_pegawai/tambah'); ?>',
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
                    }
                }
            })
        });
        $("#btn-update").click(function () {
            $('#btn-update').prop('disabled', true);
            $('#btn-update').html('Sedang Diproses');
            var form = $("#form-edit");
            var formData = form.serialize();
            $.ajax({
                url: '<?= base_url('admin/gaji/pinjaman_pegawai/edit'); ?>',
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
                        pinjaman_pegawai();
                        $('#btn-simpan').html('Simpan');
                    }
                }
            })
        });
        $("#tahun_awal").on("change", function () {
            let tahun = $(this).val();
            $("#tahun_akhir").val(tahun);
            generateBulanAkhir();
            hitungLamaPinjaman();
        });

        // saat modal dibuka
        $('#tambah').on('shown.bs.modal', function () {
            generateBulanAkhir();
            hitungLamaPinjaman();
        });
        $("#generate").click(function () {
            let bulanAkhir = parseInt($("#bulan_akhir").val());
            if (!bulanAkhir) {
                return;
            }
            generatespembayaran();
        });
        $("#bulan_awal,#tahun_awal,#tahun_akhir").on("change", function () { generateBulanAkhir(); });
        $("#bulan_akhir").on("change", function () { hitungLamaPinjaman(); });
        $('#dt-length-0').on('change', function () {
            const jumlah = parseInt($(this).val());
            paging($('#data_pinjaman_pegawai .card-mapel'), jumlah);
        });
    })

    function generateBulanAkhir() {

        let bulanAwal = parseInt($("#bulan_awal").val());
        let tahunAwal = parseInt($("#tahun_awal").val());
        let tahunAkhir = parseInt($("#tahun_akhir").val());

        let html = '<option value="">Pilih Bulan</option>';

        if (!bulanAwal || !tahunAwal || !tahunAkhir) {
            $("#bulan_akhir").html(html);
            return;
        }

        let mulai = 1;

        // jika tahun sama
        if (tahunAwal == tahunAkhir) {
            mulai = bulanAwal;
        }

        for (let i = mulai; i <= 12; i++) {
            let value = i.toString().padStart(2, '0');
            html += `
        <option value="${value}">
            ${namaBulan[i - 1]}
        </option>
        `;
        }

        $("#bulan_akhir").html(html);
        // reset lama pinjaman
        $('input[name="lama_pinjaman"]').val('');
    }

    function hitungLamaPinjaman() {

        let bulanAwal = parseInt($("#bulan_awal").val());
        let tahunAwal = parseInt($("#tahun_awal").val());

        let bulanAkhir = parseInt($("#bulan_akhir").val());
        let tahunAkhir = parseInt($("#tahun_akhir").val());

        if (
            !bulanAwal ||
            !tahunAwal ||
            !bulanAkhir ||
            !tahunAkhir
        ) {
            return;
        }

        let totalAwal = (tahunAwal * 12) + bulanAwal;
        let totalAkhir = (tahunAkhir * 12) + bulanAkhir;
        let lama = (totalAkhir - totalAwal) + 1;
        $('input[name="lama_pinjaman"]').val(lama);
    }

    function generatespembayaran() {
        let lama = parseInt($('input[name="lama_pinjaman"]').val()) || 0;
        // ambil nilai pinjaman
        let totalText = $('input[name="nilai_pinjaman"]').val();
        // hapus semua karakter selain angka
        let total = totalText.replace(/[^\d]/g, '');
        total = parseInt(total) || 0;
        let bulan = parseInt($("#bulan_awal").val()) || 0;
        let tahun = parseInt($("#tahun_awal").val()) || 0;
        // if (
        //     lama <= 0 ||
        //     total <= 0 ||
        //     bulan <= 0 ||
        //     tahun <= 0
        // ) {

        //     Swal.fire({
        //         icon: 'warning',
        //         text: 'Lengkapi data dahulu'
        //     });
        //     return;
        // }
        let cicilan = Math.floor(total / lama);
        let sisa = total - (cicilan * lama);
        let html = `
        <hr>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Bulan</th>
                    <th width="35%">Nominal</th>
                </tr>
            </thead>
            <tbody>
    `;

        for (let i = 0; i < lama; i++) {
            if (bulan > 12) {
                bulan = 1;
                tahun++;
            }
            let nominal = cicilan;
            // sisa dimasukkan ke cicilan terakhir
            if (i == (lama - 1)) {
                nominal += sisa;
            }
            let bulanFormat = bulan.toString().padStart(2, '0');
            html += `
        <tr>
            <td>${i + 1}</td>
            <td>
                ${namaBulan[bulan - 1]} ${tahun}
                <input type="hidden" name="detail_bulan[]" value="${bulanFormat}">
                <input type="hidden" name="detail_tahun[]" value="${tahun}">
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">Rp </span>
                    <input type="text" class="form-control nominal" name="nominal_tagihan[]" onkeyup="FormatCurrency(this);hitungTotalPinjaman();" value="${NumberToMoney(nominal)}">
                </div>
            </td>
        </tr>`;
            bulan++;
        }
        html += `
</tbody>
<tfoot>
<tr>
    <th colspan="2" class="text-end">
        Total
    </th>
    <th id="total_angsuran">
        Rp 0
    </th>
</tr>
</tfoot>
</table>`;

        $("#hasil_generate").html(html);
        setTimeout(function () {
            hitungTotalPinjaman();
        }, 100);
    }

    function hitungTotalPinjaman() {

        let totalPinjaman = $('input[name="nilai_pinjaman"]')
            .val()
            .replace(/[^\d]/g, '');

        totalPinjaman = parseInt(totalPinjaman) || 0;

        let total = 0;

        $('#hasil_generate .nominal').each(function () {

            let nilai = $(this)
                .val()
                .replace(/[^\d]/g, '');

            total += parseInt(nilai) || 0;
        });

        let selisih = totalPinjaman - total;

        if (selisih == 0) {

            $("#total_angsuran").html(
                '<span class="text-success">Rp ' +
                NumberToMoney(total) +
                '</span>'
            );

            $("#btn-simpan")
                .prop('disabled', false);

        } else {

            $("#total_angsuran").html(
                '<span class="text-danger">Rp ' +
                NumberToMoney(total) +
                ' (Selisih : ' +
                (selisih > 0 ? '-' : '+') +
                NumberToMoney(Math.abs(selisih)) +
                ')</span>'
            );

            $("#btn-simpan")
                .prop('disabled', true);
        }
    }

    function pinjaman_pegawai() {
        var cari = $('#cari-pegawai').val();
        var tanggal_mulai = $('#pencarian input[name=tanggal_mulai]').val();
        var tanggal_akhir = $('#pencarian input[name=tanggal_akhir]').val();
        $.ajax({
            url: '<?= base_url('admin/gaji/pinjaman_pegawai/pinjaman_pegawai_result'); ?>',
            type: 'POST',
            data: {
                search: cari,
                tanggal_mulai: tanggal_mulai,
                tanggal_akhir: tanggal_akhir
            },
            dataType: 'JSON',
            success: function (data) {
                $('#data_pinjaman_pegawai').empty();
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
                        let bulanAwal = parseInt(item.bulan_awal);
                        let bulanAkhir = parseInt(item.bulan_akhir);
                        // let detail = btoa(JSON.stringify(item));
                        table += `
                         
                            <div class="card-mapel">
                              <p class="keterangan-hari">Tanggal : ${item.tanggal} </p>
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top: 8px;">${no++}. ${item.nama_pegawai}</h5>   
                                      <p class="keterangan-jam-mapel">Durasi Pinjaman : ${namaBulan[bulanAwal - 1]} ${item.tahun_awal} - ${namaBulan[bulanAkhir - 1]} ${item.tahun_akhir}</p>
                                </div>
                                 <div class="keterangan-mapel-kanan">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="detail(${item.id})"><i class="ri-eye-line"></i></button>
                                        <button type="button" onclick="editPinjamanPegawai(${item.id})" class="btn btn-sm btn-outline-warning me-1"><i class="ri-edit-2-line"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger w-50" onclick="hapus(${item.id})"><i class="ri-delete-bin-line"></i></button> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                }
                $('#data_pinjaman_pegawai').html(table);
                let jumlah_awal = parseInt($('#dt-length-0').val());
                paging($('#data_pinjaman_pegawai .card-mapel'), jumlah_awal);
            }
        });
    }

    function pegawai(id_pegawai = null) {
        $.ajax({
            url: '<?= base_url('admin/gaji/pinjaman_pegawai/pegawai_result'); ?>',
            type: 'POST',
            data: { id_pegawai },
            dataType: 'JSON',
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

    function detail(id) {
        $.ajax({
            type: 'POST',
            url: `<?= base_url(); ?>admin/gaji/pinjaman_pegawai/row_pinjaman`,
            data: {
                id_pinjaman: id,
            },
            dataType: 'json',
            success: function (data) {
                let pinjaman = data.pinjaman;
                let detail = data.detail;

                let bulanAwal = parseInt(pinjaman.bulan_awal);
                let bulanAkhir = parseInt(pinjaman.bulan_akhir);

                $("#detail_nama").html(pinjaman.nama_pegawai);
                $("#detail_bulan").html(namaBulan[bulanAwal - 1] + ' - ' + namaBulan[bulanAkhir - 1]);
                $("#detail_tahun").html(pinjaman.tahun_awal + ' - ' + pinjaman.tahun_akhir);
                $("#detail_hadir").html('Rp ' + NumberToMoney(pinjaman.nilai_pinjaman));

                let html = '';
                let informasi = '';
                detail.forEach(function (item, index) {
                    html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>
                        ${namaBulan[
                        parseInt(item.bulan) - 1
                        ]} ${item.tahun}
                    </td>
                    <td>
                        Rp ${NumberToMoney(
                            item.nominal_tagihan
                        )}
                    </td>
                </tr>
                `;
                });
                informasi += `<p>Nama Pegawai: ${pinjaman.nama_pegawai}<br>Nominal: Rp. ${NumberToMoney(pinjaman.nilai_pinjaman)}<br>Sisa Pinjaman: Rp. ${NumberToMoney(pinjaman.sisa_pinjaman)}<br>Lama Pinjaman: ${pinjaman.lama_pinjaman} bulan</p>`;
                $("#detail_table").html(html);
                    $(".detail_informasi").html(informasi);
                $("#detailPinjaman").modal('show');
            }
        });
    }
    function editPinjamanPegawai(id) {

        $('#edit').modal('show');

        $.ajax({
            type: 'POST',
            url: "<?= base_url() ?>admin/gaji/pinjaman_pegawai/row_pinjaman",
            data: {
                id_pinjaman: id
            },
            dataType: 'json',
            success: function (data) {
                let p = data.pinjaman;
                let detail = data.detail;
                pegawai(p.id_pegawai);
                $('#edit input[name=id_pinjaman]').val(p.id);
                // $('#edit input[name=id_pegawai]').val(p.id_pegawai);
                // $('#edit select[name=id_pegawai]').val(p.id_pegawai);
                $('#edit input[name=lama_pinjaman]').val(p.lama_pinjaman);
                $('#edit input[name=nilai_pinjaman]').val(NumberToMoney(p.nilai_pinjaman));
                $('#edit input[name=bulan_awal_text]').val(namaBulan[parseInt(p.bulan_awal) - 1]);
                $('#edit input[name=tahun_awal]').val(p.tahun_awal);
                $('#edit input[name=bulan_akhir_text]').val(namaBulan[parseInt(p.bulan_akhir) - 1]);
                $('#edit input[name=tahun_akhir]').val(p.tahun_akhir);

                let html = `
            <hr>
            <h5>
                Detail Cicilan
            </h5>
            <table class="table table-bordered">
            <thead>
            <tr>
                <th width="5%">No</th>
                <th>Bulan</th>
                <th width="35%">
                    Nominal
                </th>
            </tr>
            </thead>

            <tbody>
            `;


                detail.forEach(function (item, index) {

                    html += `
                <tr>
                <td class="text-center">
                    ${index + 1}
                </td>
                <td>
                    ${namaBulan[parseInt(item.bulan) - 1]}
                    ${item.tahun}
                </td>
                <td>
                <div class="input-group">
                <span class="input-group-text">Rp </span>
                <input type="text" class="form-control nominal" name="nominal_tagihan[]" value="${NumberToMoney(item.nominal_tagihan)}" onkeyup=" FormatCurrency(this); hitungTotalEdit(); ">
                </div>
                <input type="hidden" name="detail_id[]" value="${item.id}">
                </td>
                </tr>
                `;
                });

                html += `
            </tbody>
            <tfoot>
            <tr>
            <th colspan="2" class="text-end">Total</th>
            <th id="total_edit">
            Rp 0
            </th>
            </tr>
            </tfoot>
            </table>`;
                $('#hasil_generate_edit').html(html);
                hitungTotalEdit();
            }
        });
    }
    function hitungTotalEdit() {

        let totalPinjaman = $('#edit input[name="nilai_pinjaman"]')
            .val()
            .replace(/[^\d]/g, '');

        totalPinjaman = parseInt(totalPinjaman) || 0;

        let total = 0;

        $('#edit .nominal').each(function () {

            let nilai = $(this)
                .val()
                .replace(/[^\d]/g, '');

            total += parseInt(nilai) || 0;
        });

        let selisih = totalPinjaman - total;

        if (selisih == 0) {

            $('#edit #total_edit').html(
                '<span class="text-success">Rp ' +
                NumberToMoney(total) +
                '</span>'
            );

            $('#btn-update')
                .prop('disabled', false);

        } else {

            $('#edit #total_edit').html(
                '<span class="text-danger">Rp ' +
                NumberToMoney(total) +
                ' (Selisih : ' +
                (selisih > 0 ? '-' : '+') +
                NumberToMoney(Math.abs(selisih)) +
                ')</span>'
            );

            $('#btn-update')
                .prop('disabled', true);
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
                    url: `<?= base_url(); ?>admin/gaji/pinjaman_pegawai/hapus`,
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
                            pinjaman_pegawai();
                        }

                    }
                })
            }
        })
    }
</script>