<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title">Data <?= $title; ?></h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#tambah"><i class="ri-add-line"></i>Tambah</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cari-pegawai" placeholder="Cari Pegawai"
                            aria-describedby="inputGroupPrepend" onkeyup="potongan_pegawai()">
                        <span class="input-group-text bg-primary text-white" id="inputGroupPrepend"><i
                                class="ri-search-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="bulan-pegawai-potongan" class="form-control">
                            <option value="">Pilih Bulan</option>
                            <?php
                            $bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
                            $no = 0;
                            foreach ($bulan as $nama_bulan) {
                                $no++;
                                $no_pas = sprintf("%02s", $no);
                                echo '<option value="' . $no_pas . '">' . $nama_bulan . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <div class="input-group">
                        <select id="tahun-pegawai-potongan" class="form-control">
                            <option value="">Pilih Tahun</option>
                            <?php
                            $now = date('Y');
                            for ($a = 2025; $a <= $now; $a++) {
                                echo '<option value="' . $a . '">' . $a . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" onclick="potongan_pegawai()">
                        <i class="ri-search-line"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="data_potongan_pegawai"></div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2 mt-2">
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

<div class="modal fade" id="tambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-tambah">
                    <div class="mb-3">
                        <label class="form-label">Nama Pegawai</label>
                        <select name="id_pegawai" class="form-control"></select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="mb-1">Bulan</label>
                            <select class="form-control" name="bulan_potongan">
                                <?php
                                $bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
                                $no = 0;
                                foreach ($bulan as $nama_bulan) {
                                    $no++;
                                    $no_pas = sprintf("%02s", $no);
                                ?>
                                    <option value="<?= $no_pas; ?>"><?= $nama_bulan; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="mb-1">Tahun</label>
                            <select class="form-control" name="tahun_potongan">
                                <?php
                                $now = date('Y');
                                for ($a = 2025; $a <= $now; $a++) {
                                ?>
                                    <option value="<?= $a; ?>"><?= $a; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Daftar Potongan</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-potongan">
                            <i class="ri-add-line"></i> Tambah Potongan
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="45%">Potongan</th>
                                    <th width="40%">Nominal</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detail-potongan"></tbody>
                        </table>
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

<div class="modal fade" id="edit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit <?= $title; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit">
                    <input type="hidden" name="id_potong_pegawai">

                    <div class="mb-3">
                        <label>Nama Pegawai</label>
                        <select name="id_pegawai" class="form-control"></select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="mb-1">Bulan</label>
                            <select class="form-control" name="bulan_potongan">
                                <?php
                                $bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
                                $no = 0;
                                foreach ($bulan as $nama_bulan) {
                                    $no++;
                                    $no_pas = sprintf("%02s", $no);
                                ?>
                                    <option value="<?= $no_pas; ?>"><?= $nama_bulan; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="mb-1">Tahun</label>
                            <select class="form-control" name="tahun_potongan">
                                <?php
                                $now = date('Y');
                                for ($a = 2025; $a <= $now; $a++) {
                                ?>
                                    <option value="<?= $a; ?>"><?= $a; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Daftar Potongan</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-potongan-edit">
                            Tambah Potongan
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="45%">Potongan</th>
                                    <th width="40%">Nominal</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detail-potongan-edit"></tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-update">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        pegawai();
        potongan_pegawai();

        $('#btn-simpan').click(function () {
            let pegawai = $('#tambah select[name="id_pegawai"]').val();

            if (pegawai == '' || pegawai == null) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Pegawai tidak boleh kosong'
                });
                return;
            }

            if (!validasiPotongan('#form-tambah')) {
                return;
            }

            $('#btn-simpan').prop('disabled', true).html('Sedang Diproses');

            $.ajax({
                url: '<?= base_url('admin/gaji/pegawai_potongan/tambah'); ?>',
                type: 'POST',
                data: $('#form-tambah').serialize(),
                dataType: 'JSON',
                success: function (data) {
                    if (data.status == true) {
                        $('#tambah').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message || 'Data berhasil disimpan'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message
                        });
                    }
                },
                complete: function () {
                    $('#btn-simpan').prop('disabled', false).html('Simpan');
                }
            });
        });

        $('#btn-update').click(function () {
            if (!validasiPotongan('#form-edit')) {
                return;
            }

            $('#btn-update').prop('disabled', true).html('Sedang Diproses');

            $.ajax({
                url: '<?= base_url('admin/gaji/pegawai_potongan/edit'); ?>',
                type: 'POST',
                data: $('#form-edit').serialize(),
                dataType: 'JSON',
                success: function (data) {
                    if (data.status == true) {
                        $('#edit').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message || 'Data berhasil diupdate'
                        });
                        potongan_pegawai();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message
                        });
                    }
                },
                complete: function () {
                    $('#btn-update').prop('disabled', false).html('Simpan');
                }
            });
        });

        $('#dt-length-0').on('change', function () {
            paging($('#data_potongan_pegawai .card-mapel'), parseInt($(this).val()));
        });

        $(document).on('click', '#btn-tambah-potongan', function () {
            tambahBarisPotongan();
        });

        $(document).on('click', '#btn-tambah-potongan-edit', function () {
            tambahBarisPotonganEdit();
        });

        $(document).on('click', '.hapus-detail', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '.master-potongan', function () {
            aturKeteranganLainLain($(this));
        });
    });

    function optionsMasterPotongan() {
        return `
            <option value="">Pilih Potongan</option>
            <?php foreach ($master_potongan as $mp) { ?>
                <option value="<?= $mp['id']; ?>" data-nama="<?= htmlspecialchars($mp['nama_potongan'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars($mp['nama_potongan'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php } ?>
        `;
    }

    function tambahBarisPotongan() {
        // <input type="text"
        //     name="keterangan_potongan[]"
        //     class="form-control keterangan-potongan"
        //     placeholder="Masukkan keterangan potongan lain-lain">
        let html = `
            <tr>
                <td>
                    <select name="id_master_potongan[]" class="form-control master-potongan">
                        ${optionsMasterPotongan()}
                    </select>

                    <div class="mt-2 area-keterangan-potongan" style="display:none;">
                    <textarea name="keterangan_potongan[]"  cols="10" rows="2" class="form-control keterangan-potongan" placeholder="Masukkan keterangan potongan lain-lain"></textarea>
                    </div>
                </td>

                <td>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text"
                            name="nominal[]"
                            class="form-control nominal"
                            onkeyup="FormatCurrency(this)">
                    </div>
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm hapus-detail">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#detail-potongan').append(html);
    }

    function tambahBarisPotonganEdit(id_master_potongan = '', nominal = '', keterangan_potongan = '') {
        //    <input type="text"
        //                     name="keterangan_potongan[]"
        //                     class="form-control keterangan-potongan"
        //                     placeholder="Masukkan keterangan potongan lain-lain">
        
        let html = `
            <tr>
                <td>
                    <select name="id_master_potongan[]" class="form-control master-potongan">
                        ${optionsMasterPotongan()}
                    </select>

                    <div class="mt-2 area-keterangan-potongan" style="display:none;">
                     <textarea name="keterangan_potongan[]" cols="10" rows="2" class="form-control keterangan-potongan" placeholder="Masukkan keterangan potongan lain-lain"></textarea>
                    </div>
                </td>

                <td>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text"
                            name="nominal[]"
                            class="form-control nominal"
                            value="${nominal}"
                            onkeyup="FormatCurrency(this)">
                    </div>
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm hapus-detail">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#detail-potongan-edit').append(html);

        let $row = $('#detail-potongan-edit tr:last');
        $row.find('.master-potongan').val(id_master_potongan);
        $row.find('.keterangan-potongan').val(keterangan_potongan);
        aturKeteranganLainLain($row.find('.master-potongan'));
    }

    function aturKeteranganLainLain($select) {
        let $row = $select.closest('tr');
        let namaPotongan = String($select.find('option:selected').data('nama') || '').trim().toLowerCase();
        let $area = $row.find('.area-keterangan-potongan');
        let $input = $row.find('.keterangan-potongan');

        if (namaPotongan === 'lain-lain') {
            $area.show();
        } else {
            $area.hide();
            $input.val('');
        }
    }

    function validasiPotongan(formSelector) {
        let valid = true;
        let pesan = '';

        $(formSelector + ' tbody tr').each(function () {
            let $row = $(this);
            let idMaster = $row.find('.master-potongan').val();
            let namaPotongan = String($row.find('.master-potongan option:selected').data('nama') || '').trim().toLowerCase();
            let nominal = $row.find('input[name="nominal[]"]').val();
            let keterangan = $.trim($row.find('.keterangan-potongan').val());

            if (!idMaster) {
                valid = false;
                pesan = 'Jenis potongan wajib dipilih.';
                return false;
            }

            if (!nominal || parseInt(String(nominal).replace(/,/g, '')) <= 0) {
                valid = false;
                pesan = 'Nominal potongan wajib diisi.';
                return false;
            }

            if (namaPotongan === 'lain-lain' && keterangan === '') {
                valid = false;
                pesan = 'Keterangan potongan wajib diisi jika memilih Lain-lain.';
                return false;
            }
        });

        if ($(formSelector + ' tbody tr').length === 0) {
            valid = false;
            pesan = 'Tambahkan minimal satu potongan.';
        }

        if (!valid) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: pesan
            });
        }

        return valid;
    }

    function pegawai(id_pegawai = null) {
        $.ajax({
            url: '<?= base_url('admin/gaji/pegawai_potongan/pegawai_result'); ?>',
            type: 'POST',
            dataType: 'JSON',
            data: { id_pegawai: id_pegawai },
            success: function (data) {
                let option = '<option value="">Nama Pegawai</option>';

                data.forEach(function (item) {
                    option += `<option value="${item.id}" ${item.id == id_pegawai ? 'selected' : ''}>${item.nama_pegawai}</option>`;
                });

                if (id_pegawai == null) {
                    $('#tambah select[name="id_pegawai"]').html(option);
                } else {
                    $('#edit select[name="id_pegawai"]').html(option);
                }
            }
        });
    }

    function namaBulan(bulan) {
        const bulanIndonesia = {
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

        return bulanIndonesia[bulan] || bulan;
    }

    function potongan_pegawai() {
        let cari = $('#cari-pegawai').val();
        let bulan = $('#bulan-pegawai-potongan').val();
        let tahun = $('#tahun-pegawai-potongan').val();

        $.ajax({
            url: '<?= base_url('admin/gaji/pegawai_potongan/pegawai_potongan_result'); ?>',
            type: 'POST',
            data: {
                search: cari,
                bulan: bulan,
                tahun: tahun
            },
            dataType: 'JSON',
            success: function (data) {
                let no = 1;
                let table = '';

                if (data.length == 0) {
                    table = `
                        <div class="card-mapel">
                            <div class="keterangan-mapel">
                                <div class="keterangan-mapel-kiri">
                                    <h5 class="judul-mapel" style="margin:0; margin-top:8px;">Tidak ada data</h5>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    data.forEach(function (item) {
                        let detail = btoa(JSON.stringify(item));
                        let badgePotongan = '';

                        if (item.detail_potongan && item.detail_potongan.length > 0) {
                            item.detail_potongan.forEach(function (p) {
                                let namaTampil = p.nama_potongan;

                                if (
                                    String(p.nama_potongan || '').trim().toLowerCase() === 'lain-lain' &&
                                    p.keterangan_potongan
                                ) {
                                    namaTampil = `Lain-lain (${p.keterangan_potongan})`;
                                }

                                badgePotongan += `
                                    <span class="badge bg-success me-1 mb-1">
                                        ${namaTampil} : Rp. ${NumberToMoney(p.nominal)}
                                    </span>
                                `;
                            });
                        } else {
                            badgePotongan = `<span class="badge bg-secondary">Tidak ada potongan</span>`;
                        }

                        table += `
                            <div class="card-mapel">
                                <p class="keterangan-hari">Bulan : ${namaBulan(item.bulan)} ${item.tahun}</p>
                                <div class="keterangan-mapel">
                                    <div class="keterangan-mapel-kiri">
                                        <h5 class="judul-mapel" style="margin:0; margin-top:8px;">
                                            ${no++}. ${item.nama_pegawai}
                                        </h5>
                                        <p class="keterangan-jam-mapel">Potongan :</p>
                                        ${badgePotongan}
                                    </div>

                                    <div class="keterangan-mapel-kanan">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button"
                                                onclick="editPotonganPegawai('${detail}')"
                                                class="btn btn-sm btn-outline-warning me-1">
                                                <i class="ri-edit-2-line"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger w-50"
                                                onclick="hapus(${item.id})">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }

                $('#data_potongan_pegawai').html(table);
                paging($('#data_potongan_pegawai .card-mapel'), parseInt($('#dt-length-0').val()));
            }
        });
    }

    function editPotonganPegawai(detail) {
        $('#edit').modal('show');

        let item = JSON.parse(atob(detail));

        $('#detail-potongan-edit').html('');
        $('#edit input[name="id_potong_pegawai"]').val(item.id);

        pegawai(item.id_pegawai);

        $('#edit select[name="bulan_potongan"]').val(item.bulan);
        $('#edit select[name="tahun_potongan"]').val(item.tahun);

        if (item.detail_potongan && item.detail_potongan.length > 0) {
            item.detail_potongan.forEach(function (p) {
                tambahBarisPotonganEdit(
                    p.id_master_potongan,
                    NumberToMoney(p.nominal),
                    p.keterangan_potongan || ''
                );
            });
        } else {
            tambahBarisPotonganEdit();
        }
    }

    function paging(selector, jumlah_tampil = 10) {
        window.tp = new Pagination('#pagination', {
            itemsCount: selector.length,
            pageSize: parseInt(jumlah_tampil),
            onPageChange: function (paging) {
                let start = paging.pageSize * (paging.currentPage - 1);
                let end = start + paging.pageSize;

                selector.hide();

                for (let i = start; i < end; i++) {
                    selector.eq(i).show();
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
                    url: `<?= base_url(); ?>admin/gaji/pegawai_potongan/hapus`,
                    data: { id: id },
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil dihapus'
                            });
                            potongan_pegawai();
                        }
                    }
                });
            }
        });
    }
</script>
