<div class="row g-3">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Buat Surat Tunggakan</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-10">
                        <input type="text" id="q" class="form-control" placeholder="Cari siswa nama / NIS / NISN">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" id="cari" class="btn btn-primary"><i class="ri-search-line me-1"></i>Cari</button>
                    </div>
                </div>

                <div id="hasil" class="mt-3"></div>

                <div id="pagination_siswa_area" class="d-none flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2 mt-2">
                    <ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-siswa"></ul>
                    <div class="d-flex align-items-center gap-2">
                        <label for="dt-length-siswa" class="mb-0">Tampilkan</label>
                        <select class="form-select form-select-sm" id="dt-length-siswa">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>entri</span>
                    </div>
                </div>

                <div id="identitas" class="alert alert-primary mt-3 d-none"></div>

                <div id="formArea" class="d-none">
                    <div class="row g-2 mb-3">
                        <div class="col-md-9">
                            <select id="periode" class="form-select">
                                <option value="">Semua Tahun Ajaran</option>
                                <?php foreach ($periode as $p): ?>
                                    <option value="<?= $p['id']; ?>"><?= html_escape($p['periode']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="button" id="muat" class="btn btn-secondary">Muat</button>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 mb-3">
                        Tagihan yang tampil hanya tagihan yang sudah mencapai atau melewati tanggal jatuh tempo sampai Tanggal Surat, masih memiliki sisa, dan dianggap sebagai tunggakan.
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="all"></th>
                                    <th>Tagihan</th>
                                    <th>Periode</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-end">Dibayar</th>
                                    <th class="text-end">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="tagihan">
                                <tr>
                                    <td colspan="6" class="empty-state">Pilih siswa untuk melihat tunggakan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card sticky-summary">
            <div class="card-header">
                <h5 class="mb-0">Informasi Surat</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total dalam Surat</strong>
                    <strong class="text-danger fs-18" id="total">Rp0</strong>
                </div>

                <div class="mb-2">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="text" id="tanggal" class="form-control tanggal-picker" value="<?= date('d-m-Y'); ?>" autocomplete="off">
                </div>

                <div class="mb-2">
                    <label class="form-label">Nama Penandatangan</label>
                    <input type="text" id="namaTtd" class="form-control" value="Bendahara Sekolah">
                </div>

                <div class="mb-2">
                    <label class="form-label">Jabatan</label>
                    <input type="text" id="jabatanTtd" class="form-control" value="Bendahara">
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea id="catatan" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-grid">
                    <button type="button" id="simpan" class="btn btn-success" disabled>Simpan & Preview Surat</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Riwayat Surat</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No Surat</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th class="text-end">Total</th>
                        <th>Status WA</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="riwayat"></tbody>
            </table>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2 mt-2">
            <ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-riwayat"></ul>
            <div class="d-flex align-items-center gap-2">
                <label for="dt-length-riwayat" class="mb-0">Tampilkan</label>
                <select class="form-select form-select-sm" id="dt-length-riwayat">
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

<style>
    #pagination-siswa .fa-angle-double-left::before,
    #pagination-riwayat .fa-angle-double-left::before {
        content: "\00AB" !important;
        font-family: Arial, sans-serif !important;
    }

    #pagination-siswa .fa-angle-left::before,
    #pagination-riwayat .fa-angle-left::before {
        content: "\2039" !important;
        font-family: Arial, sans-serif !important;
    }

    #pagination-siswa .fa-angle-right::before,
    #pagination-riwayat .fa-angle-right::before {
        content: "\203A" !important;
        font-family: Arial, sans-serif !important;
    }

    #pagination-siswa .fa-angle-double-right::before,
    #pagination-riwayat .fa-angle-double-right::before {
        content: "\00BB" !important;
        font-family: Arial, sans-serif !important;
    }
</style>

<script>
    let siswa_dipilih = null;
    let data_tagihan = [];

    $(document).ready(function() {
        flatpickr('.tanggal-picker', {
            dateFormat: 'd-m-Y',
            allowInput: true,
            disableMobile: true
        });

        riwayat();

        $('#cari').click(function() {
            cari_siswa();
        });

        $('#q').keypress(function(e) {
            if (e.which == 13) {
                cari_siswa();
            }
        });

        $('#muat').click(function() {
            tagihan();
        });

    
        $('#tanggal').change(function() {
            tagihan();
        });

        $('#all').change(function() {
            $('.cek').prop('checked', this.checked);
            hitung_total();
        });

        $(document).on('change', '.cek', function() {
            hitung_total();
        });

        $('#simpan').click(function() {
            simpan();
        });

        $('#dt-length-siswa').change(function() {
            const jumlah = parseInt($(this).val());
            paging($('#hasil .data-siswa-surat'), jumlah, '#pagination-siswa');
        });

        $('#dt-length-riwayat').change(function() {
            const jumlah = parseInt($(this).val());
            paging($('#riwayat .data-riwayat-surat'), jumlah, '#pagination-riwayat');
        });

        let preset = new URLSearchParams(location.search).get('siswa');
        if (preset) {
            pilih_siswa_by_id(preset);
        }
    });

    function rupiah(nominal) {
        return 'Rp' + Number(nominal || 0).toLocaleString('id-ID');
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    function cari_siswa() {
        var q = $('#q').val();

        if ($.trim(q).length < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan minimal 2 karakter.'
            });
            return;
        }

        $.ajax({
            url: '<?= base_url('admin/tunggakan/surat_tunggakan/cari_siswa'); ?>',
            type: 'POST',
            data: {
                q: q
            },
            dataType: 'JSON',
            success: function(data) {
                var html = '';

                if (data.length == 0) {
                    html += `
                        <div class="data-siswa-surat">
                            <div class="alert alert-warning mb-0">Tidak ada data</div>
                        </div>
                    `;
                } else {
                    html += '<div class="list-group">';
                    data.forEach(function(item) {
                        let detail = btoa(unescape(encodeURIComponent(JSON.stringify(item))));
                        html += `
                            <button type="button" class="list-group-item list-group-item-action data-siswa-surat" onclick="pilih_siswa('${detail}')">
                                <strong>${item.nama_lengkap}</strong><br>
                                <small>${item.nis} | ${item.nama_kelas || '-'}</small>
                            </button>
                        `;
                    });
                    html += '</div>';
                }

                $('#hasil').html(html);
                $('#pagination_siswa_area').removeClass('d-none').addClass('d-flex');

                let jumlah_awal = parseInt($('#dt-length-siswa').val());
                paging($('#hasil .data-siswa-surat'), jumlah_awal, '#pagination-siswa');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data siswa gagal dimuat.'
                });
            }
        });
    }

    function pilih_siswa(detail) {
        siswa_dipilih = JSON.parse(decodeURIComponent(escape(atob(detail))));

        $('#hasil').empty();
        $('#pagination-siswa').empty();
        $('#pagination_siswa_area').addClass('d-none').removeClass('d-flex');

        $('#identitas').removeClass('d-none').html(`
            <strong>${siswa_dipilih.nama_lengkap}</strong><br>
            ${siswa_dipilih.nis} | ${siswa_dipilih.nama_kelas || '-'}<br>
            Wali: ${escapeHtml(siswa_dipilih.nama_wali || '-')} (${escapeHtml(siswa_dipilih.hubungan_wali || '-')}) | WhatsApp: ${escapeHtml(siswa_dipilih.no_telp_wali || '-')}
        `);

        $('#formArea').removeClass('d-none');
        tagihan();
    }

    function pilih_siswa_by_id(id) {
        $.ajax({
            url: '<?= base_url('admin/tunggakan/surat_tunggakan/siswa/'); ?>' + id,
            type: 'GET',
            dataType: 'JSON',
            success: function(data) {
                if (data.result == 'true') {
                    let detail = btoa(unescape(encodeURIComponent(JSON.stringify(data.siswa))));
                    pilih_siswa(detail);
                }
            }
        });
    }

    function tagihan() {
        if (siswa_dipilih == null) {
            return;
        }

        var tanggal_surat = $('#tanggal').val();

        if (tanggal_surat == '') {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Tanggal surat wajib diisi.'
            });
            return;
        }

        $.ajax({
            url: '<?= base_url('admin/tunggakan/surat_tunggakan/tagihan'); ?>',
            type: 'POST',
            data: {
                id_siswa: siswa_dipilih.id,
                id_periode: $('#periode').val(),
                tanggal_surat: tanggal_surat
            },
            dataType: 'JSON',
            success: function(data) {
                data_tagihan = data;
                var table = '';

                if (data.length == 0) {
                    table += `
                        <tr>
                            <td colspan="6" class="empty-state">Tidak ada data</td>
                        </tr>
                    `;
                } else {
                    data.forEach(function(item) {
                        table += `
                            <tr>
                                <td><input type="checkbox" class="form-check-input cek" value="${item.id}"></td>
                                <td>
                                    <strong>${item.nama_tagihan}</strong><br>
                                    <small>${item.no_tagihan}</small>
                                </td>
                                <td>
                                    ${(item.nama_bulan || '')} ${item.tahun}<br>
                                    <small>${item.periode}<br>Jatuh tempo: ${item.tanggal_jatuh_tempo || '-'}</small>
                                </td>
                                <td class="text-end">${rupiah(item.nominal_tagihan)}</td>
                                <td class="text-end">${rupiah(item.nominal_dibayar)}</td>
                                <td class="text-end fw-semibold">${rupiah(item.sisa_tagihan)}</td>
                            </tr>
                        `;
                    });
                }

                $('#tagihan').html(table);
                $('#all').prop('checked', false);
                hitung_total();
            },
            error: function() {
                data_tagihan = [];
                $('#tagihan').html('<tr><td colspan="6" class="empty-state text-danger">Data tagihan gagal dimuat.</td></tr>');
                hitung_total();
            }
        });
    }

    function hitung_total() {
        var ids = [];
        var total = 0;

        $('.cek:checked').each(function() {
            ids.push(parseInt($(this).val()));
        });

        data_tagihan.forEach(function(item) {
            if (ids.includes(parseInt(item.id))) {
                total += parseFloat(item.sisa_tagihan || 0);
            }
        });

        $('#total').text(rupiah(total));
        $('#simpan').prop('disabled', ids.length == 0 || siswa_dipilih == null);
    }

    function simpan() {
        var tagihan = [];

        $('.cek:checked').each(function() {
            tagihan.push(parseInt($(this).val()));
        });

        if (siswa_dipilih == null) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih siswa terlebih dahulu.'
            });
            return;
        }

        if (tagihan.length == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih minimal satu tagihan.'
            });
            return;
        }

        $.ajax({
            url: '<?= base_url('admin/tunggakan/surat_tunggakan/simpan'); ?>',
            type: 'POST',
            data: {
                id_siswa: siswa_dipilih.id,
                tagihan: JSON.stringify(tagihan),
                tanggal_surat: $('#tanggal').val(),
                nama_penandatangan: $('#namaTtd').val(),
                jabatan_penandatangan: $('#jabatanTtd').val(),
                catatan: $('#catatan').val()
            },
            dataType: 'JSON',
            success: function(data) {
                if (data.result == 'true') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message
                    }).then(function() {
                        window.open('<?= base_url('admin/tunggakan/surat_tunggakan/cetak/'); ?>' + data.id, '_blank');
                    });

                    riwayat();
                    tagihan();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Surat gagal disimpan.'
                });
            }
        });
    }

    function riwayat() {
        $.ajax({
            url: '<?= base_url('admin/tunggakan/surat_tunggakan/riwayat'); ?>',
            type: 'POST',
            dataType: 'JSON',
            success: function(data) {
                var table = '';

                if (data.length == 0) {
                    table += `
                        <tr class="data-riwayat-surat">
                            <td colspan="7" class="empty-state">Tidak ada data</td>
                        </tr>
                    `;
                } else {
                    data.forEach(function(item) {
                        let detail = btoa(unescape(encodeURIComponent(JSON.stringify(item))));
                        table += `
                            <tr class="data-riwayat-surat">
                                <td><strong>${item.no_surat}</strong></td>
                                <td>${item.tanggal_surat}</td>
                                <td>${item.nama_siswa}<br><small>${item.nis}</small></td>
                                <td>${item.nama_kelas || '-'}</td>
                                <td class="text-end fw-semibold">${rupiah(item.total_tunggakan)}</td>
                                <td>${item.status_kirim_whatsapp}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a target="_blank" href="<?= base_url('admin/tunggakan/surat_tunggakan/cetak/'); ?>${item.id}" class="btn btn-sm btn-primary">Preview/Cetak</a>
                                        <button type="button" class="btn btn-sm btn-success" onclick="whatsapp('${detail}')">WhatsApp</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#riwayat').html(table);

                let jumlah_awal = parseInt($('#dt-length-riwayat').val());
                paging($('#riwayat .data-riwayat-surat'), jumlah_awal, '#pagination-riwayat');
            },
            error: function() {
                $('#riwayat').html('<tr><td colspan="7" class="empty-state text-danger">Data riwayat surat gagal dimuat.</td></tr>');
            }
        });
    }

    function paging($selector, jumlah_tampil = 10, pagination_selector = '#pagination') {
        window.tp = new Pagination(pagination_selector, {
            itemsCount: $selector.length,
            pageSize: parseInt(jumlah_tampil),
            onPageChange: function(paging) {
                let start = paging.pageSize * (paging.currentPage - 1);
                let end = start + paging.pageSize;
                let $rows = $selector;

                $rows.hide();
                for (let i = start; i < end; i++) {
                    $rows.eq(i).show();
                }
            }
        });
    }

    function whatsapp(detail) {
        var item = JSON.parse(decodeURIComponent(escape(atob(detail))));
        var namaWali = item.nama_wali || '';
        var hubungan = item.hubungan_wali || 'Wali';
        var nomor = item.no_telp_wali || '';

        Swal.fire({
            title: 'Kirim Surat WhatsApp',
            width: 680,
            html: `
                <div class="text-start">
                    <label class="form-label d-block">Sumber Nomor WhatsApp</label>
                    <div class="d-flex gap-3 flex-wrap mb-2">
                        <div class="form-check"><input class="form-check-input" type="radio" name="swal-wa-source" id="swal-wa-parent" value="Orang Tua" checked><label class="form-check-label" for="swal-wa-parent">Orang Tua</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="swal-wa-source" id="swal-wa-other" value="Nomor Lain"><label class="form-check-label" for="swal-wa-other">Nomor Lain</label></div>
                    </div>
                    <small class="text-muted d-block mb-3">Data wali dari Master Wali Siswa menjadi nilai awal dan dapat diubah sebelum dikirim.</small>
                    <div class="row g-2">
                        <div class="col-md-7"><label class="form-label">Nama Penerima / Wali</label><input id="swal-wa-name" type="text" class="form-control" value="${escapeHtml(namaWali)}"></div>
                        <div class="col-md-5"><label class="form-label">Hubungan</label><input id="swal-wa-relation" type="text" class="form-control" value="${escapeHtml(hubungan)}"></div>
                        <div class="col-12" id="swal-wa-parent-wrap"><label class="form-label mt-1">Nomor HP Orang Tua</label><input id="swal-wa-phone" type="text" inputmode="tel" class="form-control" value="${escapeHtml(nomor)}"></div>
                        <div class="col-12 d-none" id="swal-wa-other-wrap"><label class="form-label mt-1">Nomor Lain</label><input id="swal-wa-other-phone" type="text" inputmode="tel" class="form-control" placeholder="Contoh: 081234567890"><small class="text-muted">Nomor alternatif hanya dipakai untuk pengiriman saat ini.</small></div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Buka WhatsApp',
            cancelButtonText: 'Batal',
            didOpen: function() {
                function toggleSource() {
                    var src = document.querySelector('input[name="swal-wa-source"]:checked').value;
                    document.getElementById('swal-wa-parent-wrap').classList.toggle('d-none', src !== 'Orang Tua');
                    document.getElementById('swal-wa-other-wrap').classList.toggle('d-none', src !== 'Nomor Lain');
                }
                document.querySelectorAll('input[name="swal-wa-source"]').forEach(function(el){ el.addEventListener('change', toggleSource); });
                toggleSource();
            },
            preConfirm: function() {
                var src = document.querySelector('input[name="swal-wa-source"]:checked').value;
                var nama = document.getElementById('swal-wa-name').value.trim();
                var hub = document.getElementById('swal-wa-relation').value.trim();
                var phone = src === 'Nomor Lain' ? document.getElementById('swal-wa-other-phone').value.trim() : document.getElementById('swal-wa-phone').value.trim();
                if (!nama) { Swal.showValidationMessage('Nama penerima/wali wajib diisi.'); return false; }
                if (!phone) { Swal.showValidationMessage(src === 'Nomor Lain' ? 'Nomor lain wajib diisi.' : 'Nomor HP orang tua/wali wajib diisi.'); return false; }
                return {sumber_nomor:src,nama_penerima:nama,hubungan_penerima:hub,nomor_whatsapp:phone};
            }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            var waWindow = window.open('about:blank', '_blank');
            $.ajax({
                url: '<?= base_url('admin/tunggakan/surat_tunggakan/siapkan_whatsapp'); ?>',
                type: 'POST',
                data: Object.assign({ id: item.id }, result.value || {}),
                dataType: 'JSON',
                success: function(data) {
                    if (data.result == 'true') {
                        if (waWindow) waWindow.location = data.url; else window.location.href = data.url;
                        riwayat();
                    } else {
                        if (waWindow) waWindow.close();
                        Swal.fire({icon:'error',title:'Gagal',text:data.message});
                    }
                },
                error: function(xhr) {
                    if (waWindow) waWindow.close();
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'WhatsApp gagal disiapkan.';
                    Swal.fire({icon:'error',title:'Gagal',text:msg});
                }
            });
        });
    }
</script>
