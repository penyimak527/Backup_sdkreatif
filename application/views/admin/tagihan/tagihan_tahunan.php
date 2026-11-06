<?php
$nama_bulan_lokal = function ($bulan) {
    $list = array(1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember');
    return isset($list[(int) $bulan]) ? $list[(int) $bulan] : '-';
};
?>
<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title mb-0">Langkah 1 - Informasi Jenis Biaya</h4>

        <a href="<?= base_url('admin/tagihan/daftar_tagihan'); ?>"
            class="btn btn-outline-danger">
            <i class="ri-arrow-left-line me-1"></i>
            Kembali
        </a>
    </div>

    <div class="card-body">
        <form id="form_tagihan">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Tahun Ajaran</label><select name="id_periode" id="id_periode" class="form-select" required>
                        <option value="">Pilih tahun ajaran</option><?php foreach ($periode as $r): ?><option value="<?= $r['id'] ?>" data-periode="<?= html_escape($r['periode']) ?>"><?= html_escape($r['periode']) ?><?= $r['status'] === 'Aktif' ? ' - Aktif' : '' ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-3"><label class="form-label">Pos Penerimaan</label><select name="id_jenis_tagihan" id="id_jenis" class="form-select" required>
                        <option value="">Pilih pos penerimaan</option><?php foreach ($jenis as $r): ?><option value="<?= $r['id'] ?>"><?= html_escape($r['nama_jenis']) ?></option><?php endforeach; ?>
                    </select></div>
                <input type="hidden" name="dianggap_tunggakan" value="Ya">
                <div class="col-md-8"><label class="form-label">Nama Jenis Biaya</label><input name="nama_tagihan" class="form-control" placeholder="Nama jenis biaya" required></div>
                <div class="col-md-4"><label class="form-label">Nominal Umum</label>
                    <div class="input-group"><span class="input-group-text">Rp</span><input name="nominal_default" id="nominal_default" type="text" inputmode="numeric" autocomplete="off" class="form-control money-input" required></div>
                </div>
                <div class="col-12"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2"></textarea></div>
            </div>

            <div class="card border mt-4 mb-0">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-0">Langkah 2 - Target Jenis Biaya</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Target</label><select name="target_tagihan" id="target_tagihan" class="form-select">
                                <option value="Semua">Semua Siswa Aktif</option>
                                <option value="Kelas">Kelas Tertentu</option>
                                <option value="Siswa">Siswa Tertentu</option>
                            </select></div>
                    </div>
                    <div id="target_kelas_area" class="mt-3 d-none"><label class="form-label">Pilih Kelas</label>
                        <div class="row g-2" id="kelas_options"><?php foreach ($kelas as $r): ?><div class="col-md-4 kelas-item" data-periode="<?= $r['id_periode'] ?>"><label class="border rounded p-2 w-100"><input type="checkbox" class="form-check-input me-1" name="target_kelas[]" value="<?= $r['id'] ?>"> <?= html_escape($r['nama_kelas']) ?></label></div><?php endforeach; ?></div>
                    </div>
                    <div id="target_siswa_area" class="mt-3 d-none"><label class="form-label">Cari dan Tambahkan Siswa</label>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-10"><input id="cari_siswa" class="form-control" placeholder="Nama/NIS/NISN"></div>
                            <div class="col-md-2 d-grid"><button type="button" class="btn btn-primary" id="btn_cari_siswa"><i class="ri-search-line me-1"></i>Cari</button></div>
                        </div>
                        <div id="hasil_siswa" class="mt-2"></div>
                        <div id="siswa_terpilih" class="mt-3 d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>

            <div class="card border mt-4 mb-0">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-0">Langkah 3 - Periode Penagihan</h4>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Bulan Mulai Tampil</label><select name="bulan_penagihan" id="bulan_penagihan" class="form-select"><?php foreach (range(1, 12) as $m): ?><option value="<?= $m ?>"><?= $nama_bulan_lokal($m) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Tahun</label><select name="tahun_penagihan" id="tahun_penagihan" class="form-select"></select></div>
                        <div class="col-md-4"><label class="form-label">Jatuh Tempo</label><input name="tanggal_jatuh_tempo" class="form-control tanggal"><small class="text-muted">Otomatis akhir bulan, tetap dapat diubah.</small></div>
                    </div>

                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-light" onclick="document.getElementById('form_tagihan').reset();location.reload();">Reset</button>
                <!-- <button type="button" class="btn btn-outline-secondary" onclick="simpan('Draft')">Simpan Draft</button> -->
                <button type="button" class="btn btn-outline-primary" id="btn_preview">Preview Jenis Biaya</button>
                <button type="button" class="btn btn-primary" onclick="simpan('Terbitkan')">Buat Jenis Biaya</button></div>
        </form>
    </div>
</div>
<div class="modal fade" id="modalPreview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Jenis Biaya</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="preview_content"></div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button class="btn btn-primary" id="btn_terbitkan_preview" onclick="simpan('Terbitkan')">Terbitkan Jenis Biaya</button></div>
        </div>
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


    var previewModal;
    var selectedStudents = {};

    $(document).ready(function() {
        previewModal = new bootstrap.Modal('#modalPreview');

        flatpickr('.tanggal,.jatuh_tempo', {
            dateFormat: 'd-m-Y'
        });

        $('#id_periode').change(function() {
            updatePeriodTarget();
        });

        $('#bulan_penagihan, #tahun_penagihan').on('change', function() {
            isiDefaultJatuhTempo();
        });

        $('#target_tagihan').change(function() {
            toggleTarget();
        });

        $('.cek_bulan').change(toggleMonth);

        $('#terapkan_nominal').click(function() {
            applyNominal();
        });

        $('#nominal_default').on('input', function() {
            if ($('#mode_tarif').val() === 'Sama') {
                applyNominal();
            }
        });

        $('#btn_cari_siswa').click(function() {
            cariSiswa();
        });

        $('#cari_siswa').keyup(function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                cariSiswa();
            }
        });

        $('#btn_preview').click(function() {
            preview();
        });

        toggleTarget();
        updatePeriodTarget();
    });

    function updatePeriodTarget() {
        var periode = $('#id_periode').val();
        var periodeText = $('#id_periode :selected').data('periode') || '';
        var years = String(periodeText).split('/');

        $('#tahun_penagihan').html(
            years.map(function(tahun) {
                return '<option>' + escapeHtml(tahun) + '</option>';
            }).join('')
        );

        isiDefaultJatuhTempo();

        $('.kelas-item').each(function() {
            var sesuai = String($(this).data('periode')) === String(periode);

            $(this).toggle(sesuai);
            if (!sesuai) {
                $(this).find('input').prop('checked', false);
            }
        });

        $('#bulan_rows tr').each(function() {
            var bulan = Number($(this).data('bulan'));
            $(this).find('.tahun_label').text(bulan >= 7 ? (years[0] || '-') : (years[1] || '-'));
        });

        selectedStudents = {};
        renderSelected();
    }

    function tanggalAkhirBulan(bulan, tahun) {
        bulan = Number(bulan);
        tahun = Number(tahun);

        if (!bulan || !tahun) {
            return '';
        }

        var hariTerakhir = new Date(tahun, bulan, 0).getDate();
        return String(hariTerakhir).padStart(2, '0') + '-' +
            String(bulan).padStart(2, '0') + '-' + tahun;
    }

    function isiDefaultJatuhTempo() {
        var bulan = Number($('#bulan_penagihan').val());
        var tahun = Number($('#tahun_penagihan').val());
        var tanggal = tanggalAkhirBulan(bulan, tahun);
        var element = $('[name="tanggal_jatuh_tempo"]')[0];

        if (!element) {
            return;
        }

        if (element._flatpickr) {
            element._flatpickr.setDate(tanggal || null, false, 'd-m-Y');
        } else {
            $(element).val(tanggal || '');
        }
    }

    function toggleTarget() {
        var target = $('#target_tagihan').val();
        $('#target_kelas_area').toggleClass('d-none', target !== 'Kelas');
        $('#target_siswa_area').toggleClass('d-none', target !== 'Siswa');
    }

    function toggleMonth() {
        var row = $(this).closest('tr');
        var aktif = this.checked;

        row.find('input:not(.cek_bulan)').prop('disabled', !aktif);

        if (aktif && $('#mode_tarif').val() === 'Sama') {
            row.find('.nominal_bulan').val($('#nominal_default').val());
        }
    }

    function applyNominal() {
        $('.cek_bulan:checked').each(function() {
            $(this).closest('tr').find('.nominal_bulan').val($('#nominal_default').val());
        });
    }

    function cariSiswa() {
        if (!$('#id_periode').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih tahun ajaran terlebih dahulu.'
            });
            return;
        }

        $.ajax({
            url: '<?= base_url('admin/tagihan/tagihan_tahunan/cari_siswa'); ?>',
            type: 'POST',
            data: {
                q: $('#cari_siswa').val(),
                id_periode: $('#id_periode').val()
            },
            dataType: 'JSON',
            success: function(data) {
                var html = '';

                if (data.length == 0) {
                    html = '<div class="text-muted">Siswa tidak ditemukan.</div>';
                } else {
                    data.forEach(function(item) {
                        html += '<button type="button" class="btn btn-sm btn-outline-primary me-1 mb-1" onclick=\'addStudent(' + JSON.stringify(item) + ')\'>' +
                            escapeHtml(item.nama_lengkap) + ' - ' + escapeHtml(item.nama_kelas) +
                            '</button>';
                    });
                }

                $('#hasil_siswa').html(html);
            },
            error: function(xhr, status, error) {
                ajaxError(xhr);
            }
        });
    }

    function addStudent(item) {
        selectedStudents[item.id] = item;
        renderSelected();
    }

    function removeStudent(id) {
        delete selectedStudents[id];
        renderSelected();
    }

    function renderSelected() {
        var html = '';

        Object.keys(selectedStudents).forEach(function(id) {
            var item = selectedStudents[id];
            html += '<span class="badge bg-primary-subtle text-primary p-2">' +
                escapeHtml(item.nama_lengkap) +
                ' <button type="button" class="btn-close btn-close-sm ms-1" onclick="removeStudent(' + id + ')"></button>' +
                '<input type="hidden" name="target_siswa[]" value="' + id + '">' +
                '</span>';
        });

        $('#siswa_terpilih').html(html);
    }

    function formData(mode) {
        var data = serializeMoneyForm('#form_tagihan');
        return data + '&' + $.param({
            mode_simpan: mode
        });
    }

    function preview() {
        $('#btn_preview').prop('disabled', true);

        $.ajax({
            url: '<?= base_url('admin/tagihan/tagihan_tahunan/preview'); ?>',
            type: 'POST',
            data: formData('Preview'),
            dataType: 'JSON',
            success: function(data) {
                if (data.result != 'true') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                    return;
                }

                var html = '<div class="row g-3">' +
                    '<div class="col-md-6"><div class="alert alert-info"><small>Jumlah Siswa</small><h4>' + data.jumlah_siswa + '</h4></div></div>' +
                    '<div class="col-md-6"><div class="alert alert-success"><small>Total Nominal</small><h4>' + formatRupiah(data.total_nominal) + '</h4></div></div>' +
                    '</div><h6>Periode Jenis Biaya</h6><ul>';

                data.periods.forEach(function(item) {
                    html += '<li>' + escapeHtml(item.nama_bulan) + ' ' + item.tahun + ' - ' + formatRupiah(item.nominal) + '</li>';
                });
                html += '</ul>';

                if (data.has_duplicate) {
                    html += '<div class="alert alert-danger mt-3 mb-0">' +
                        '<h6 class="mb-2">Jenis Biaya ganda ditemukan</h6>' +
                        '<div class="mb-2">' + escapeHtml(data.message || 'Jenis Biaya tidak dapat diterbitkan karena data yang sama sudah ada.') + '</div>';

                    if (Array.isArray(data.duplicates) && data.duplicates.length > 0) {
                        html += '<ul class="mb-0">';
                        data.duplicates.slice(0, 10).forEach(function(item) {
                            html += '<li>' +
                                escapeHtml(item.nama_siswa || '-') +
                                ' - ' + escapeHtml(item.nama_bulan || '-') + ' ' + escapeHtml(item.tahun || '') +
                                ' - ' + escapeHtml(item.nama_tagihan || '-') +
                                '</li>';
                        });
                        html += '</ul>';

                        if (Number(data.duplicate_count || 0) > 10) {
                            html += '<div class="mt-2"><small>Dan ' + (Number(data.duplicate_count) - 10) + ' data lainnya.</small></div>';
                        }
                    }

                    html += '</div>';
                    $('#btn_terbitkan_preview').prop('disabled', true);
                } else {
                    $('#btn_terbitkan_preview').prop('disabled', false);
                }

                $('#preview_content').html(html);
                previewModal.show();
            },
            error: function(xhr, status, error) {
                ajaxError(xhr);
            },
            complete: function() {
                $('#btn_preview').prop('disabled', false);
            }
        });
    }

    function simpan(mode) {
        confirmAction(
            mode === 'Draft' ? 'Simpan draft jenis biaya?' : 'Terbitkan jenis biaya?',
            'Pastikan target dan tarif sudah benar.',
            function() {
                $('button').prop('disabled', true);
                $.ajax({
                    url: '<?= base_url('admin/tagihan/tagihan_tahunan/simpan'); ?>',
                    type: 'POST',
                    data: formData(mode),
                    dataType: 'JSON',
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
                    success: function(data) {
                        Swal.fire({
                            icon: data.result == 'true' ? 'success' : 'error',
                            title: data.result == 'true' ? 'Berhasil' : 'Gagal',
                            text: data.message
                        }).then(function() {
                            if (data.result == 'true') {
                                window.location.href = '<?= base_url('admin/tagihan/daftar_tagihan'); ?>';
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        ajaxError(xhr);
                    },
                    complete: function() {
                        $('button').prop('disabled', false);
                    }
                });
            }
        );
    }
</script>