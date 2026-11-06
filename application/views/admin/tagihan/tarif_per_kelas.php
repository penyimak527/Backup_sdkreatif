<div class="card">
    <div class="card-header app-card-header">
        <h4 class="header-title">Tarif Per Kelas</h4>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-0">
            <div class="col-md-8">
                <label for="tagihan" class="form-label">Tagihan</label>
                <select id="tagihan" class="form-select">
                    <option value="">Pilih tagihan</option>
                    <?php foreach ($tagihan as $row): ?>
                        <option value="<?= (int) $row['id'] ?>" <?= (int) $id_tagihan === (int) $row['id'] ? 'selected' : '' ?>>
                            <?= html_escape($row['periode'] . ' - ' . $row['nama_tagihan'] . ' [' . $row['status'] . ']') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_kelas" class="form-label">Kelas</label>
                <select id="filter_kelas" class="form-select" disabled>
                    <option value="">Pilih Kelas</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="button" id="btn_tampilkan" class="btn btn-primary">Tampilkan</button>
            </div>
        </div>
    </div>
</div>

<div class="card d-none" id="card_tarif">
    <div class="card-header app-card-header">
        <div>
            <h4 class="header-title">Daftar Tarif Kelas</h4>
            <small class="text-muted">Tarif kelas hanya berlaku jika siswa tidak memiliki tarif khusus.</small>
        </div>
    </div>
    <div class="card-body">
        <form id="form_tarif">
            <input type="hidden" name="id_tagihan">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th class="text-end">Tarif Umum</th>
                            <th style="min-width:180px">Tarif Kelas</th>
                            <th class="text-center">Jumlah Siswa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="kelas_rows">
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">Pilih tagihan terlebih dahulu.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-outline-primary" id="btn_terapkan_umum">
                    Terapkan Tarif Umum ke Semua
                </button>
                <button type="button" class="btn btn-primary" id="btn_simpan">
                    <i class="ri-save-line me-1"></i>Simpan Tarif Per Kelas
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Helper lokal modul Tagihan/Pembayaran.
    // Sengaja ditempatkan di view agar tidak mengubah helper, library, header, atau footer existing SD Kreatif.
    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    function formatRupiah(value) {
        var number = Number(value || 0);
        if (!Number.isFinite(number)) number = 0;
        return 'Rp' + new Intl.NumberFormat('id-ID').format(number);
    }

    function ajaxError(xhr) {
        var message = 'Terjadi kesalahan saat memproses data.';
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        Swal.fire('Gagal', message, 'error');
    }

    function confirmAction(title, text, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed && typeof callback === 'function') callback();
        });
    }

    function parseMoneyInput(value) {
        if (typeof value === 'number') return Number.isFinite(value) ? value : 0;

        var text = String(value == null ? '' : value)
            .replace(/Rp/gi, '')
            .replace(/\s/g, '')
            .trim();

        // Nilai database dapat berbentuk 125000.00, sedangkan input tampilan dapat 125.000.
        if (/^-?\d+\.\d{1,2}$/.test(text)) {
            return Number(text);
        }

        var raw = text.replace(/[^0-9-]/g, '');
        return raw === '' || raw === '-' ? 0 : Number(raw);
    }

    function formatMoneyInput(value) {
        return new Intl.NumberFormat('id-ID').format(parseMoneyInput(value));
    }

    function serializeMoneyForm(form) {
        var $form = $(form);
        var original = [];

        $form.find('.money-input').each(function() {
            original.push({
                element: this,
                value: this.value
            });
            this.value = String(parseMoneyInput(this.value));
        });

        var data = $form.serialize();

        original.forEach(function(item) {
            item.element.value = item.value;
        });

        return data;
    }

    $(document)
        .off('input.tagihanMoney', '.money-input:not([readonly]):not([disabled])')
        .on('input.tagihanMoney', '.money-input:not([readonly]):not([disabled])', function() {
            var raw = String(this.value || '').replace(/[^0-9]/g, '');
            this.value = raw === '' ? '' : new Intl.NumberFormat('id-ID').format(Number(raw));
        });

    function paging(selector, jumlahTampil, targetPagination) {
        var $rows = selector instanceof jQuery ? selector : $(selector);
        var pageSize = parseInt(jumlahTampil, 10) || 10;
        var target = targetPagination || '#pagination';
        var $target = $(target);

        if (!$target.length) return null;

        $rows.show();
        $target.empty();

        if (!$rows.length) return null;

        if (typeof Pagination !== 'function') {
            $rows.slice(pageSize).hide();
            return null;
        }

        return new Pagination(target, {
            itemsCount: $rows.length,
            pageSize: pageSize,
            onPageChange: function(page) {
                var start = page.pageSize * (page.currentPage - 1);
                var end = start + page.pageSize;
                $rows.hide();
                $rows.slice(start, end).show();
            }
        });
    }


    var tarifMaster = null;

    $(document).ready(function() {
        $('#tagihan').on('change', function() {
            loadFilterKelas();
        });

        $('#btn_tampilkan').on('click', function() {
            loadTarifKelas();
        });

        $('#btn_simpan').on('click', simpanTarifKelas);

        $('#btn_terapkan_umum').on('click', function() {
            if (!tarifMaster) return;

            $('.input-tarif-kelas:visible')
                .val(formatMoneyInput(tarifMaster.nominal_default || 0));
        });

        $(document).on('click', '.btn-salin-tarif', function() {
            var nominal = $(this).data('nominal');

            $(this)
                .closest('tr')
                .find('.input-tarif-kelas')
                .val(formatMoneyInput(nominal));
        });

        /*
         * Jika halaman dibuka dengan tagihan yang sudah terpilih,
         * cukup isi filter Kelas terlebih dahulu.
         * Tabel tarif tetap baru ditampilkan setelah tombol Tampilkan diklik.
         */
        if ($('#tagihan').val()) {
            loadFilterKelas();
        }
    });

    function resetFilterKelas() {
        $('#filter_kelas')
            .prop('disabled', true)
            .html('<option value="">Pilih Kelas</option>');
    }

    function loadFilterKelas() {
        var idTagihan = $('#tagihan').val();

        tarifMaster = null;
        $('#card_tarif').addClass('d-none');
        $('#form_tarif [name="id_tagihan"]').val('');
        $('#kelas_rows').html(
            '<tr><td colspan="5"><div class="empty-state">Pilih tagihan dan kelas, lalu klik Tampilkan.</div></td></tr>'
        );

        if (!idTagihan) {
            resetFilterKelas();
            return;
        }

        $('#filter_kelas')
            .prop('disabled', true)
            .html('<option value="">Memuat kelas...</option>');

        $.ajax({
            url: '<?= base_url('admin/tagihan/tarif_per_kelas/result') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                id_tagihan: idTagihan
            },
            success: function(response) {
                if (response.result !== 'true') {
                    resetFilterKelas();
                    Swal.fire('Gagal', response.message || 'Data kelas gagal dimuat.', 'error');
                    return;
                }

                var rows = response.classes || [];

                if (!rows.length) {
                    $('#filter_kelas')
                        .prop('disabled', true)
                        .html('<option value="">Tidak ada kelas</option>');
                    return;
                }

                var kelasOptions = '<option value="">Semua Kelas</option>';

                rows.forEach(function(row) {
                    kelasOptions +=
                        '<option value="' + Number(row.id_kelas_setting || 0) + '">' +
                        escapeHtml(row.nama_kelas || '-') +
                        '</option>';
                });

                $('#filter_kelas')
                    .html(kelasOptions)
                    .prop('disabled', false);
            },
            error: function(xhr, status, error) {
                resetFilterKelas();
                ajaxError(xhr, status, error);
            }
        });
    }

    function loadTarifKelas() {
        var idTagihan = $('#tagihan').val();
        var idKelas = String($('#filter_kelas').val() || '');

        if (!idTagihan) {
            return Swal.fire('Perhatian', 'Pilih tagihan terlebih dahulu.', 'warning');
        }

        if ($('#filter_kelas').prop('disabled')) {
            return Swal.fire('Perhatian', 'Kelas belum tersedia untuk tagihan yang dipilih.', 'warning');
        }

        $('#card_tarif').removeClass('d-none');
        $('#kelas_rows').html(
            '<tr><td colspan="5"><div class="empty-state">Memuat tarif kelas...</div></td></tr>'
        );

        $.ajax({
            url: '<?= base_url('admin/tagihan/tarif_per_kelas/result') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                id_tagihan: idTagihan
            },
            success: function(response) {
                if (response.result !== 'true') {
                    $('#card_tarif').addClass('d-none');
                    Swal.fire('Gagal', response.message || 'Data tarif kelas gagal dimuat.', 'error');
                    return;
                }

                tarifMaster = response.master || {};
                $('#form_tarif [name="id_tagihan"]').val(idTagihan);

                var rows = response.classes || [];

                /*
                 * Filter kelas dilakukan setelah tombol Tampilkan diklik.
                 * Perubahan pilihan dropdown Kelas sendiri tidak menjalankan AJAX
                 * dan tidak langsung mengubah hasil.
                 */
                if (idKelas) {
                    rows = rows.filter(function(row) {
                        return String(Number(row.id_kelas_setting || 0)) === idKelas;
                    });
                }

                if (!rows.length) {
                    $('#kelas_rows').html(
                        '<tr><td colspan="5"><div class="empty-state">' +
                        '<div class="empty-state-title">Tidak ada data tarif kelas</div>' +
                        '<div>Tidak ada kelas yang sesuai dengan filter yang dipilih.</div>' +
                        '</div></td></tr>'
                    );
                    return;
                }

                var html = rows.map(function(row) {
                    var value = formatMoneyInput(
                        row.nominal_kelas || tarifMaster.nominal_default || 0
                    );

                    return '<tr>' +
                        '<td><strong>' + escapeHtml(row.nama_kelas || '-') + '</strong></td>' +
                        '<td class="text-end">' +
                        formatRupiah(tarifMaster.nominal_default || 0) +
                        '</td>' +
                        '<td>' +
                        '<div class="input-group input-group-sm">' +
                        '<span class="input-group-text">Rp</span>' +
                        '<input type="text" ' +
                        'inputmode="numeric" ' +
                        'autocomplete="off" ' +
                        'class="form-control money-input input-tarif-kelas" ' +
                        'name="tarif[' + row.id + ']" ' +
                        'value="' + value + '">' +
                        '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                        Number(row.jumlah_siswa || 0).toLocaleString('id-ID') +
                        '</td>' +
                        '<td>' +
                        '<button type="button" ' +
                        'class="btn btn-sm btn-outline-primary btn-salin-tarif" ' +
                        'data-nominal="' + Number(tarifMaster.nominal_default || 0) + '">' +
                        'Salin' +
                        '</button>' +
                        '</td>' +
                        '</tr>';
                }).join('');

                $('#kelas_rows').html(html);
            },
            error: function(xhr, status, error) {
                $('#card_tarif').addClass('d-none');
                ajaxError(xhr, status, error);
            }
        });
    }

    function simpanTarifKelas() {
        if (!$('#form_tarif [name="id_tagihan"]').val()) {
            return Swal.fire(
                'Perhatian',
                'Tampilkan data tarif terlebih dahulu.',
                'warning'
            );
        }

        confirmAction(
            'Simpan tarif per kelas?',
            'Perubahan diterapkan pada tarif normal siswa. Tarif khusus siswa tetap memiliki prioritas tertinggi dan perubahan dicatat pada log aktivitas.',
            function() {
                var button = $('#btn_simpan').prop('disabled', true);

                $.ajax({
                    url: '<?= base_url('admin/tagihan/tarif_per_kelas/simpan') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: serializeMoneyForm('#form_tarif'),
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Sedang Memproses Data',
                            text: 'Mohon tunggu hingga proses selesai.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        var ok = response.result === 'true';

                        Swal.fire(
                            ok ? 'Berhasil' : 'Gagal',
                            response.message,
                            ok ? 'success' : 'error'
                        );

                        if (ok) {
                            loadTarifKelas();
                        }
                    },
                    error: ajaxError,
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            }
        );
    }
</script>