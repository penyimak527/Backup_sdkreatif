<div class="card">
    <div class="card-header app-card-header">
        <div>
            <h4 class="header-title mb-1">Pembebasan</h4>
            <p class="text-muted mb-0">Pilih tagihan, lalu kelola siswa yang menjadi penerima tagihan.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label" for="tagihan">Tagihan</label>
                <select id="tagihan" class="form-select">
                    <option value="">Pilih tagihan</option>
                    <?php foreach ($tagihan as $row): ?>
                        <option value="<?= (int) $row['id'] ?>" <?= (int) $id_tagihan === (int) $row['id'] ? 'selected' : '' ?>>
                            <?= html_escape($row['periode'] . ' - ' . $row['nama_tagihan'] . ' [' . $row['status'] . ']') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label" for="kelas_filter">Kelas</label>
                <select id="kelas_filter" class="form-select">
                    <option value="">Semua Kelas</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label" for="search">Nama/NIS/NISN</label>
                <input id="search" class="form-control" placeholder="Cari siswa ...">
            </div>
            <!-- <div class="col-lg-2 col-md-6">
                <label class="form-label" for="status_filter">Status Tagihan</label>
                <select id="status_filter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Belum Ditambahkan">Belum Ditambahkan</option>
                </select>
            </div> -->
            <div class="col-lg-1 d-grid">
                <button type="button" class="btn btn-primary" id="btn_tampilkan">
                    <i class="ri-search-line"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="content" class="d-none">
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header app-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="header-title mb-1">Siswa Pembayar</h4>
                        <small class="text-muted">Siswa yang sudah menjadi penerima tagihan.</small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge bg-primary" id="count_current">0</span>
                        <!-- <button type="button" class="btn btn-sm btn-light" id="btn_pilih_semua_current">Pilih Semua Hasil Filter</button> -->
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn_keluarkan_terpilih">Keluarkan Siswa Terpilih</button>
                        <!-- <a href="#" class="btn btn-sm btn-outline-success" id="btn_export_siswa">
                            <i class="ri-file-excel-2-line me-1"></i>Ekspor Daftar
                        </a> -->
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="42"><input type="checkbox" class="form-check-input" id="check_all_current" title="Pilih semua hasil filter"></th>
                                    <th>Siswa</th>
                                    <th>NIS/NISN</th>
                                    <th>Kelas</th>
                                    <th class="text-end">Tarif</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Penerima</th>
                                    <!-- <th class="text-center">Aksi</th> -->
                                </tr>
                            </thead>
                            <tbody id="current"></tbody>
                        </table>
                    </div>
                    <div class="px-3 pb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-current"></ul>
                            <div class="d-flex align-items-center gap-2">
                                <label for="dt-length-current" class="mb-0">Tampilkan</label>
                                <select class="form-select form-select-sm" id="dt-length-current">
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
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header app-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="header-title mb-1">Calon Siswa</h4>
                        <small class="text-muted">Siswa aktif pada tahun ajaran tagihan yang belum menjadi penerima.</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- <button type="button" class="btn btn-sm btn-light" id="btn_pilih_semua">Pilih Semua Hasil Filter</button> -->
                        <button type="button" class="btn btn-sm btn-primary" id="btn_tambah">Tambah Siswa Terpilih</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="42"><input type="checkbox" class="form-check-input" id="check_all_candidates" title="Pilih semua hasil filter"></th>
                                    <th>Siswa</th>
                                    <th>NIS/NISN</th>
                                    <th>Kelas</th>
                                    <th class="text-end">Tarif</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Penerima</th>
                                </tr>
                            </thead>
                            <tbody id="candidates"></tbody>
                        </table>
                    </div>
                    <div class="px-3 pb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-candidates"></ul>
                            <div class="d-flex align-items-center gap-2">
                                <label for="dt-length-candidates" class="mb-0">Tampilkan</label>
                                <select class="form-select form-select-sm" id="dt-length-candidates">
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
            </div>
        </div>
    </div>
</div>

<script>
    // Helper lokal khusus halaman Siswa Pembayar.
    // Dibuat lokal agar tidak bergantung pada helper global Almahbaro yang tidak ada di template SD Kreatif.
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
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    }

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

    $(document).ready(function() {
        $('#btn_tampilkan').on('click', loadData);
        $('#btn_tambah').on('click', tambah);
        $('#btn_keluarkan_terpilih').on('click', keluarkanTerpilih);

        $('#btn_pilih_semua').on('click', function() {
            $('#candidates .candidate').prop('checked', true);
            $('#check_all_candidates').prop('checked', $('#candidates .candidate').length > 0);
        });

        $('#btn_pilih_semua_current').on('click', function() {
            $('#current .current-check:not(:disabled)').prop('checked', true);
            $('#check_all_current').prop('checked', $('#current .current-check:not(:disabled)').length > 0);
        });

        $('#check_all_candidates').on('change', function() {
            $('#candidates .candidate').prop('checked', this.checked);
        });

        $('#check_all_current').on('change', function() {
            $('#current .current-check:not(:disabled)').prop('checked', this.checked);
        });

        $('#tagihan').on('change', function() {
            $('#kelas_filter').html('<option value="">Semua Kelas</option>');
            $('#content').addClass('d-none');
            if ($(this).val()) {
                loadData();
            }
        });

        $('#search').on('keyup', function(event) {
            if (event.key === 'Enter') loadData();
        });

        $('#dt-length-current').on('change', refreshCurrentPagination);
        $('#dt-length-candidates').on('change', refreshCandidatePagination);

        $('#btn_export_siswa').on('click', function(event) {
            event.preventDefault();
            var id = $('#tagihan').val();
            if (!id) {
                Swal.fire('Perhatian', 'Pilih tagihan terlebih dahulu.', 'warning');
                return;
            }

            this.href = '<?= base_url('admin/tagihan/siswa_pembayar/export') ?>' +
                '?id_tagihan=' + encodeURIComponent(id) +
                '&id_kelas_setting=' + encodeURIComponent($('#kelas_filter').val() || '') +
                '&search=' + encodeURIComponent($('#search').val() || '');
            window.location.href = this.href;
        });

        if ($('#tagihan').val()) {
            loadData();
        } else {
            refreshCurrentPagination();
            refreshCandidatePagination();
        }
    });

    function loadData() {
        var id = $('#tagihan').val();

        if (!id) {
            $('#content').addClass('d-none');
            return Swal.fire('Perhatian', 'Pilih tagihan terlebih dahulu.', 'warning');
        }

        $.ajax({
            url: '<?= base_url('admin/tagihan/siswa_pembayar/result') ?>',
            type: 'POST',
            data: {
                id_tagihan: id,
                id_kelas_setting: $('#kelas_filter').val(),
                search: $('#search').val(),
                status_tagihan: ''
            },
            dataType: 'JSON',
            success: function(response) {
                if (response.result !== 'true') {
                    Swal.fire('Gagal', response.message, 'error');
                    return;
                }

                $('#content').removeClass('d-none');
                updateKelasFilter(response.classes || []);

                var current = response.current || [];
                var candidates = response.candidates || [];
                var currentHtml = '';
                var candidateHtml = '';

                $('#count_current').text(current.length);
                $('#check_all_current').prop('checked', false);
                $('#check_all_candidates').prop('checked', false);

                if (!current.length) {
                    currentHtml = '<tr><td colspan="7"><div class="empty-state">Belum ada siswa pembayar sesuai filter.</div></td></tr>';
                } else {
                    current.forEach(function(row) {
                        var statusPembayaran = row.status_pembayaran || '-';
                        var badgePembayaran = 'secondary';

                        if (statusPembayaran === 'Lunas') badgePembayaran = 'success';
                        else if (statusPembayaran === 'Dibayar Sebagian') badgePembayaran = 'warning';
                        else if (statusPembayaran === 'Belum Dibayar') badgePembayaran = 'danger';
                        else if (statusPembayaran === 'Dibebaskan') badgePembayaran = 'info';

                        currentHtml +=
                            '<tr class="current-student-row">' +
                            '<td><input type="checkbox" class="form-check-input current-check" value="' + Number(row.id_siswa) + '" data-sudah-membayar="' + (Number(row.dibayar || 0) > 0 ? '1' : '0') + '"></td>' +
                            '<td><strong>' + escapeHtml(row.nama_siswa || '-') + '</strong></td>' +
                            '<td>' + escapeHtml(row.nis || '-') + '<br><small class="text-muted">' + escapeHtml(row.nisn || '-') + '</small></td>' +
                            '<td>' + escapeHtml(row.nama_kelas || '-') + '</td>' +
                            '<td class="text-end">' + formatRupiah(row.tarif || 0) + '</td>' +
                            '<td><span class="badge bg-' + badgePembayaran + '-subtle text-' + badgePembayaran + '">' + escapeHtml(statusPembayaran) + '</span></td>' +
                            '<td><span class="badge bg-success-subtle text-success">' + escapeHtml(row.status_penerima || 'Aktif') + '</span></td>' +
                            // '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="keluarkan(' + Number(row.id_siswa) + ')">Keluarkan</button></td>' +
                            '</tr>';
                    });
                }

                if (!candidates.length) {
                    candidateHtml = '<tr><td colspan="7"><div class="empty-state">Tidak ada calon siswa sesuai filter.</div></td></tr>';
                } else {
                    candidates.forEach(function(row) {
                        candidateHtml +=
                            '<tr class="candidate-student-row">' +
                            '<td><input type="checkbox" class="form-check-input candidate" value="' + Number(row.id_siswa) + '"></td>' +
                            '<td><strong>' + escapeHtml(row.nama_siswa || '-') + '</strong></td>' +
                            '<td>' + escapeHtml(row.nis || '-') + '<br><small class="text-muted">' + escapeHtml(row.nisn || '-') + '</small></td>' +
                            '<td>' + escapeHtml(row.nama_kelas || '-') + '</td>' +
                            '<td class="text-end">' + formatRupiah(row.tarif || 0) + '</td>' +
                            '<td><span class="text-muted">-</span></td>' +
                            '<td><span class="badge bg-' + (row.status_penerima === 'Dikeluarkan' ? 'warning-subtle text-warning' : 'secondary-subtle text-secondary') + '">' + escapeHtml(row.status_penerima || 'Belum Ditambahkan') + '</span>' +
                            (row.status_penerima === 'Dikeluarkan' && Number(row.perlu_pembebasan || 0) > 0 ? ' <button type="button" class="btn btn-sm btn-outline-warning ms-2" onclick="keluarkan(' + Number(row.id_siswa) + ')">Selesaikan Pembebasan Lama</button>' : '') + '</td>' +
                            '</tr>';
                    });
                }

                $('#current').html(currentHtml);
                $('#candidates').html(candidateHtml);
                refreshCurrentPagination();
                refreshCandidatePagination();
            },
            error: function(xhr, status, error) {
                ajaxError(xhr);
            }
        });
    }

    function updateKelasFilter(classes) {
        var selected = String($('#kelas_filter').val() || '');
        var options = '<option value="">Semua Kelas</option>';

        classes.forEach(function(row) {
            options += '<option value="' + Number(row.id) + '">' + escapeHtml(row.nama_kelas || '-') + '</option>';
        });

        $('#kelas_filter').html(options);
        if (selected !== '' && $('#kelas_filter option[value="' + selected + '"]').length) {
            $('#kelas_filter').val(selected);
        }
    }

    function refreshCurrentPagination() {
        paging(
            $('#current .current-student-row'),
            parseInt($('#dt-length-current').val(), 10) || 10,
            '#pagination-current'
        );
    }

    function refreshCandidatePagination() {
        paging(
            $('#candidates .candidate-student-row'),
            parseInt($('#dt-length-candidates').val(), 10) || 10,
            '#pagination-candidates'
        );
    }

    function tambah() {
        var ids = $('.candidate:checked').map(function() {
            return this.value;
        }).get();

        if (!ids.length) {
            return Swal.fire('Perhatian', 'Pilih siswa yang akan ditambahkan.', 'warning');
        }

        confirmAction(
            'Tambahkan siswa?',
            'Tarif mengikuti tarif khusus, tarif kelas, lalu tarif umum. Siswa yang dimasukkan kembali akan memiliki tagihan aktif dan sisa kewajiban yang belum lunas.',
            function() {
                $.ajax({
                    url: '<?= base_url('admin/tagihan/siswa_pembayar/tambah') ?>',
                    type: 'POST',
                    data: {
                        id_tagihan: $('#tagihan').val(),
                        id_siswa: ids
                    },
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
                    success: function(response) {
                        var berhasil = response.result === 'true';
                        Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
                        if (berhasil) loadData();
                    },
                    error: function(xhr, status, error) {
                        ajaxError(xhr);
                    }
                });
            }
        );
    }

    function keluarkanTerpilih() {
        var ids = $('.current-check:checked:not(:disabled)').map(function() {
            return this.value;
        }).get();

        if (!ids.length) {
            return Swal.fire('Perhatian', 'Pilih siswa yang akan dikeluarkan.', 'warning');
        }

        var sudahMembayar = $('.current-check:checked').filter(function() {
            return $(this).attr('data-sudah-membayar') === '1';
        }).length;

        confirmAction(
            'Keluarkan siswa terpilih?',
            (sudahMembayar ? sudahMembayar + ' siswa sudah memiliki pembayaran. ' : '') +
            'Pembayaran dan riwayat transaksi tetap tersimpan, uang tidak dikembalikan. ' +
            'Seluruh sisa tagihan, termasuk bulan berjalan dan sebelumnya, dibebaskan dan tidak lagi tampil sebagai kewajiban pembayaran.',
            function() {
                $.ajax({
                    url: '<?= base_url('admin/tagihan/siswa_pembayar/keluarkan') ?>',
                    type: 'POST',
                    data: {
                        id_tagihan: $('#tagihan').val(),
                        id_siswa: ids
                    },
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
                    success: function(response) {
                        var berhasil = response.result === 'true';
                        Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
                        if (berhasil) loadData();
                    },
                    error: function(xhr, status, error) {
                        ajaxError(xhr);
                    }
                });
            }
        );
    }

    function keluarkan(id) {
        confirmAction(
            'Keluarkan siswa?',
            'Pembayaran dan riwayat transaksi tetap tersimpan, uang tidak dikembalikan. ' +
            'Seluruh sisa tagihan, termasuk bulan berjalan dan sebelumnya, dibebaskan dan tidak lagi tampil sebagai kewajiban pembayaran.',
            function() {
                $.ajax({
                    url: '<?= base_url('admin/tagihan/siswa_pembayar/keluarkan') ?>',
                    type: 'POST',
                    data: {
                        id_tagihan: $('#tagihan').val(),
                        id_siswa: id
                    },
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
                    success: function(response) {
                        var berhasil = response.result === 'true';
                        Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
                        if (berhasil) loadData();
                    },
                    error: function(xhr, status, error) {
                        ajaxError(xhr);
                    }
                });
            }
        );
    }
</script>