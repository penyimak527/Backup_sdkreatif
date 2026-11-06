<div class="card">
    <div class="card-header app-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="header-title mb-0">Generate Jenis Biaya</h4>
        <div class="dropdown">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="ri-add-line me-1"></i>Buat Jenis Biaya
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="<?= base_url('admin/tagihan/tagihan_bulanan') ?>">Jenis Biaya Bulanan</a>
                <a class="dropdown-item" href="<?= base_url('admin/tagihan/tagihan_langsung') ?>">Jenis Biaya Langsung</a>
                <a class="dropdown-item" href="<?= base_url('admin/tagihan/tagihan_tahunan') ?>">Jenis Biaya Tahunan</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-lg-2 col-md-4"><select id="periode" class="form-select">
                    <option value="0">Semua Tahun Ajaran</option><?php foreach ($periode as $r): ?><option value="<?= $r['id'] ?>"><?= html_escape($r['periode']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-lg-2 col-md-4"><select id="tipe" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option>Bulanan</option>
                    <option>Langsung</option>
                    <option>Tahunan</option>
                </select></div>
            <div class="col-lg-2 col-md-4"><select id="jenis" class="form-select">
                    <option value="0">Semua Pos Penerimaan</option><?php foreach ($jenis as $r): ?><option value="<?= $r['id'] ?>"><?= html_escape($r['nama_jenis']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-lg-2 col-md-4"><select id="filter_status_tagihan" class="form-select">
                    <option value="">Semua Status</option>
                    <option>Draft</option>
                    <option>Aktif</option>
                    <option>Dibatalkan</option>
                </select></div>
            <div class="col-lg-2 col-md-4"><input id="search" class="form-control" placeholder="Cari nama atau kode ..."></div>
            <div class="col-lg-2 col-md-4 d-grid"><button class="btn btn-primary" type="button" onclick="loadData()"><i class="ri-search-line me-1"></i>Cari</button></div>
        </div>
        <div id="data" class="crud-list"></div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <ul class="pagination pagination-sm pagination-boxed mb-0" id="pagination-daftar-tagihan"></ul>
            <div class="d-flex align-items-center gap-2">
                <label for="dt-length-daftar-tagihan" class="mb-0">Tampilkan</label>
                <select class="form-select form-select-sm" id="dt-length-daftar-tagihan">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entri</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tagihan</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail_content"></div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditTagihan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="edit_modal_title">Edit Tagihan</h5><small class="text-muted" id="edit_modal_hint"></small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form_edit_tagihan">
                    <input type="hidden" id="edit_id">
                    <input type="hidden" id="edit_mode">
                    <input type="hidden" id="edit_tipe">
                    <input type="hidden" id="edit_id_periode">
                    <input type="hidden" id="edit_tunggakan" value="Ya">
                    <div class="row g-3" id="draft_info_area">
                        <div class="col-md-4" id="edit_periode_group">
                            <label class="form-label">Tahun Ajaran</label>
                            <select class="form-select" id="edit_periode">
                                <?php foreach ($periode as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>"><?= html_escape($r['periode']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4" id="edit_jenis_group"><label class="form-label">Jenis Tagihan</label><select id="edit_jenis" class="form-select">
                                <?php foreach ($jenis as $r): ?><option value="<?= (int) $r['id'] ?>" data-tipe="<?= html_escape($r['tipe_default']) ?>"><?= html_escape($r['nama_jenis']) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-8" id="edit_nama_group"><label class="form-label">Nama Tagihan</label><input type="text" class="form-control" id="edit_nama"></div>
                        <div class="col-md-4" id="edit_target_group"><label class="form-label">Target Tagihan</label><select id="edit_target" class="form-select">
                                <option value="Semua">Semua Siswa Aktif</option>
                                <option value="Kelas">Kelas Tertentu</option>
                                <option value="Siswa">Siswa Tertentu</option>
                            </select></div>
                    </div>

                    <div class="mt-3" id="draft_target_kelas_area">
                        <label class="form-label">Kelas Target</label>
                        <div class="row g-2" id="draft_kelas_options"></div>
                    </div>
                    <div class="mt-3 d-none" id="draft_target_siswa_area">
                        <label class="form-label">Siswa Target</label>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-10"><input type="text" id="draft_cari_siswa" class="form-control" placeholder="Nama / NIS / NISN"></div>
                            <div class="col-md-2 d-grid"><button type="button" class="btn btn-outline-primary" id="btn_draft_cari_siswa">Cari</button></div>
                        </div>
                        <div id="draft_hasil_siswa" class="mt-2"></div>
                        <div id="draft_siswa_terpilih" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0">Periode, Nominal dan Jatuh Tempo</label>
                            <button type="button" class="btn btn-sm btn-outline-primary d-none" id="btn_draft_semua_bulan">Pilih Semua Bulan</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="draft-col-pilih" style="width:60px">Pilih</th>
                                        <th>Periode</th>
                                        <th style="width:240px">Nominal</th>
                                        <th style="width:190px">Jatuh Tempo</th>
                                    </tr>
                                </thead>
                                <tbody id="edit_period_rows"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3" id="draft_keterangan_area"><label class="form-label">Keterangan</label><textarea class="form-control" id="edit_keterangan" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button type="button" class="btn btn-primary" id="btn_simpan_edit">Simpan Perubahan</button></div>
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


    var modalDetail;
    var modalEditTagihan;
    var draftStudents = {};
    var draftClasses = [];
    var draftMaster = {};
    var bulanNama = {
        1: 'Januari',
        2: 'Februari',
        3: 'Maret',
        4: 'April',
        5: 'Mei',
        6: 'Juni',
        7: 'Juli',
        8: 'Agustus',
        9: 'September',
        10: 'Oktober',
        11: 'November',
        12: 'Desember'
    };

    $(document).ready(function() {
        modalDetail = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetail'));
        modalEditTagihan = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditTagihan'));
        $('#dt-length-daftar-tagihan').on('change', refreshDaftarTagihanPagination);
        $('#btn_simpan_edit').on('click', simpanEditTagihan);
        $('#edit_target').on('change', toggleDraftTarget);
        $('#edit_periode').on('change', gantiTahunAjaranDraft);
        $('#btn_draft_cari_siswa').on('click', cariDraftSiswa);
        $('#draft_cari_siswa').on('keyup', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                cariDraftSiswa();
            }
        });
        $('#btn_draft_semua_bulan').on('click', pilihSemuaBulanDraft);
        loadData();
    });

    function loadData() {
        $.ajax({
            url: '<?= base_url('admin/tagihan/daftar_tagihan/result') ?>',
            type: 'POST',
            data: {
                id_periode: $('#periode').val(),
                tipe: $('#tipe').val(),
                id_jenis: $('#jenis').val(),
                status: $('#filter_status_tagihan').val(),
                search: $('#search').val()
            },
            dataType: 'JSON',
            success: function(rows) {
                if (!rows.length) {
                    $('#data').html('<div class="empty-state">Belum ada tagihan.</div>');
                    refreshDaftarTagihanPagination();
                    return;
                }
                var html = rows.map(function(r, index) {
                    var badge = r.status === 'Aktif' ? 'bg-success' : (r.status === 'Draft' ? 'bg-warning' : 'bg-danger');
                    var actions = '<button class="btn btn-outline-primary btn-icon" title="Detail" onclick="detail(' + r.id + ')"><i class="ri-eye-line"></i></button>' +
                        '<a class="btn btn-outline-warning btn-icon" title="Tarif" href="<?= base_url('admin/tagihan/tarif_per_kelas?id_tagihan=') ?>' + r.id + '"><i class="ri-money-dollar-circle-line"></i></a>';
                    if (r.status === 'Draft') {
                        actions += '<button class="btn btn-outline-warning btn-icon" title="Edit Draft" onclick="editDraft(' + r.id + ')"><i class="ri-edit-line"></i></button>' +
                            '<button class="btn btn-outline-success btn-icon" title="Terbitkan" onclick="terbitkan(' + r.id + ')"><i class="ri-send-plane-line"></i></button>' +
                            '<button class="btn btn-outline-danger btn-icon" title="Hapus Draft" onclick="hapusDraft(' + r.id + ')"><i class="ri-delete-bin-line"></i></button>';
                    } else if (r.status === 'Aktif') {
                        actions += '<button class="btn btn-outline-warning btn-icon" title="Edit Tagihan" onclick="editTerbit(' + r.id + ')"><i class="ri-edit-line"></i></button>';
                        // '<button class="btn btn-outline-danger btn-icon" title="Batalkan Sisa" onclick="batalkan(' + r.id + ')"><i class="ri-close-circle-line"></i></button>';
                    }
                    return '<div class="card-mapel">' +
                        '<p class="keterangan-hari">Kode : ' + escapeHtml(r.kode_tagihan) + '</p>' +
                        '<div class="keterangan-mapel">' +
                        '<div class="keterangan-mapel-kiri">' +
                        '<h5 class="judul-mapel" style="margin:0; margin-top:8px;">' + (index + 1) + '. ' + escapeHtml(r.nama_tagihan) + '</h5>' +
                        '<p class="keterangan-jam-mapel">Tipe : ' + escapeHtml(r.tipe_tagihan) + ' | Tahun : ' + escapeHtml(r.periode) + ' | Target : ' + escapeHtml(r.target_tagihan) + '</p>' +
                        '<p class="keterangan-jam-mapel">Siswa : ' + Number(r.jumlah_siswa || 0) + ' | Total : ' + formatRupiah(r.total_nominal || 0) + '</p>' +
                        '<p class="keterangan-jam-mapel">Belum : ' + Number(r.belum_bayar || 0) + ' | Sebagian : ' + Number(r.sebagian || 0) + ' | Lunas : ' + Number(r.lunas || 0) + '</p>' +
                        '<p class="keterangan-jam-mapel">Status : <span class="badge ' + badge + '">' + escapeHtml(r.status) + '</span></p>' +
                        '</div><div class="keterangan-mapel-kanan"><div class="d-flex justify-content-center gap-2 flex-wrap">' + actions + '</div></div></div></div>';
                }).join('');
                $('#data').html(html);
                refreshDaftarTagihanPagination();
            },
            error: function(xhr) {
                ajaxError(xhr);
            }
        });
    }

    function detail(id) {
        $.ajax({
            url: '<?= base_url('admin/tagihan/daftar_tagihan/detail') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'JSON',
            success: function(r) {
                if (r.result !== 'true') {
                    Swal.fire('Gagal', r.message, 'error');
                    return;
                }

                var m = r.master;
                var h = '<div class="row g-3"><div class="col-md-6"><div class="border rounded p-3"><h5>' + escapeHtml(m.nama_tagihan) + '</h5>' +
                    '<div>Kode: ' + escapeHtml(m.kode_tagihan) + '</div><div>Tipe: ' + escapeHtml(m.tipe_tagihan) + '</div><div>Tahun Ajaran: ' + escapeHtml(m.periode) + '</div><div>Status: ' + escapeHtml(m.status) + '</div><div>Dihitung tunggakan: ' + escapeHtml(m.dianggap_tunggakan) + '</div></div></div>' +
                    '<div class="col-md-6"><div class="border rounded p-3"><h6>Periode dan Tarif</h6><ul class="mb-0">';

                (r.periods || []).forEach(function(x) {
                    h += '<li>' + escapeHtml(x.nama_bulan) + ' ' + x.tahun + ' - ' + formatRupiah(x.nominal) + ' - jatuh tempo ' + escapeHtml(x.tanggal_jatuh_tempo || '-') + '</li>';
                });

                h += '</ul></div></div></div><h6 class="mt-4">Kelas Target</h6><div class="d-flex flex-wrap gap-2">';
                if (!(r.classes || []).length) {
                    h += '<span class="text-muted">Tidak ada kelas khusus.</span>';
                } else {
                    (r.classes || []).forEach(function(x) {
                        h += '<span class="badge bg-primary-subtle text-primary p-2">' + escapeHtml(x.nama_kelas) + '</span>';
                    });
                }
                h += '</div>';

                $('#detail_content').html(h);
                modalDetail.show();
            },
            error: function(xhr) {
                ajaxError(xhr);
            }
        });
    }

    function editDraft(id) {
        $.ajax({
            url: '<?= base_url('admin/tagihan/daftar_tagihan/draft_detail') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'JSON',
            success: function(r) {
                if (r.result !== 'true') {
                    Swal.fire('Gagal', r.message, 'error');
                    return;
                }

                var m = r.master;
                draftMaster = $.extend({}, m);
                $('#edit_mode').val('Draft');
                $('#edit_id').val(m.id);
                $('#edit_tipe').val(m.tipe_tagihan);
                $('#edit_id_periode').val(m.id_periode);
                $('#edit_modal_title').text('Edit Draft Tagihan');
                $('#edit_modal_hint').text('Draft belum diterbitkan: Tahun Ajaran, informasi, target siswa/kelas, periode, nominal, dan jatuh tempo dapat diubah.');
                $('#draft_info_area, #edit_periode_group, #edit_jenis_group, #edit_nama_group, #edit_target_group, #draft_keterangan_area').removeClass('d-none');
                $('#edit_periode').val(m.id_periode);
                $('#edit_nama').val(m.nama_tagihan || '');
                $('#edit_tunggakan').val('Ya');
                $('#edit_target').val(m.target_tagihan || 'Semua');
                $('#edit_keterangan').val(m.keterangan || '');
                $('#edit_jenis option').each(function() {
                    $(this).prop('hidden', String($(this).data('tipe')) !== String(m.tipe_tagihan));
                });
                $('#edit_jenis').val(m.id_jenis_tagihan);

                draftClasses = r.available_classes || [];
                renderDraftClasses(r.classes || []);
                draftStudents = {};
                (r.students || []).forEach(function(row) {
                    draftStudents[row.id_siswa] = {
                        id: row.id_siswa,
                        nama_lengkap: row.nama_siswa,
                        nis: row.nis,
                        nama_kelas: row.nama_kelas
                    };
                });
                renderDraftStudents();
                renderDraftPeriods(m, r.periods || []);
                toggleDraftTarget();
                modalEditTagihan.show();
            },
            error: function(xhr) {
                ajaxError(xhr);
            }
        });
    }

    function editTerbit(id) {
        $.ajax({
            url: '<?= base_url('admin/tagihan/daftar_tagihan/terbit_detail') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'JSON',
            success: function(r) {
                if (r.result !== 'true') {
                    Swal.fire('Gagal', r.message, 'error');
                    return;
                }

                var m = r.master;
                draftMaster = {};
                $('#edit_mode').val('Aktif');
                $('#edit_id').val(m.id);
                $('#edit_tipe').val(m.tipe_tagihan);
                $('#edit_id_periode').val(m.id_periode);
                $('#edit_modal_title').text('Edit Tagihan yang Sudah Diterbitkan');
                $('#edit_modal_hint').text('Nama tagihan, nominal, dan tanggal jatuh tempo dapat diubah. Siswa yang sudah memiliki pembayaran tetap menggunakan snapshot lama.');
                $('#draft_info_area, #edit_nama_group').removeClass('d-none');
                $('#edit_periode_group, #edit_jenis_group, #edit_target_group, #draft_target_kelas_area, #draft_target_siswa_area, #draft_keterangan_area, #btn_draft_semua_bulan').addClass('d-none');
                $('#edit_nama').val(m.nama_tagihan || '');
                $('.draft-col-pilih').addClass('d-none');

                var html = '';
                (r.periods || []).forEach(function(row) {
                    html += '<tr class="edit-period-item" data-id="' + Number(row.id) + '"><td class="draft-col-pilih d-none"></td><td><strong>' + escapeHtml(row.nama_bulan || '-') + ' ' + Number(row.tahun || 0) + '</strong></td>' +
                        '<td><div class="input-group input-group-sm"><span class="input-group-text">Rp</span><input type="text" class="form-control money-input edit-period-nominal" value="' + Number(row.nominal || 0).toLocaleString('id-ID') + '"></div></td>' +
                        '<td><input type="text" class="form-control form-control-sm edit-period-due" value="' + escapeHtml(row.tanggal_jatuh_tempo || '') + '"></td></tr>';
                });
                $('#edit_period_rows').html(html);
                flatpickr('#edit_period_rows .edit-period-due', {
                    dateFormat: 'd-m-Y',
                    allowInput: true
                });
                modalEditTagihan.show();
            },
            error: function(xhr) {
                ajaxError(xhr);
            }
        });
    }

    function gantiTahunAjaranDraft() {
        if ($('#edit_mode').val() !== 'Draft') {
            return;
        }

        var idPeriode = Number($('#edit_periode').val() || 0);
        var periodeBaru = $('#edit_periode option:selected').text().trim();
        if (!idPeriode || !periodeBaru) {
            return;
        }

        var periodeLama = String(draftMaster.periode || '').split('/');
        var periodeBaruParts = periodeBaru.split('/');
        var tahunAwalLama = Number(periodeLama[0] || 0);
        var tahunAwalBaru = Number(periodeBaruParts[0] || 0);
        var tahunAkhirBaru = Number(periodeBaruParts[1] || (tahunAwalBaru + 1));
        var selisihTahun = tahunAwalLama > 0 ? tahunAwalBaru - tahunAwalLama : 0;
        var selectedPeriods = [];

        $('#edit_period_rows .edit-period-item').each(function() {
            var row = $(this);
            var check = row.find('.draft-period-check');
            if (check.length && !check.is(':checked')) {
                return;
            }

            var bulan = Number(row.data('bulan') || 0);
            var jatuhTempo = String(row.find('.edit-period-due').val() || '');
            var tanggalParts = jatuhTempo.split('-');
            if (tanggalParts.length === 3 && selisihTahun !== 0) {
                tanggalParts[2] = String(Number(tanggalParts[2]) + selisihTahun);
                jatuhTempo = tanggalParts.join('-');
            }

            selectedPeriods.push({
                id: 0,
                bulan: bulan,
                tahun: bulan >= 7 ? tahunAwalBaru : tahunAkhirBaru,
                nama_bulan: String(row.data('nama') || bulanNama[bulan] || ''),
                nominal: row.find('.edit-period-nominal').val(),
                tanggal_jatuh_tempo: jatuhTempo
            });
        });

        draftMaster.id_periode = idPeriode;
        draftMaster.periode = periodeBaru;
        $('#edit_id_periode').val(idPeriode);
        renderDraftPeriods(draftMaster, selectedPeriods);

        draftStudents = {};
        renderDraftStudents();
        $('#draft_hasil_siswa').empty();
        $('#draft_cari_siswa').val('');

        $.ajax({
            url: '<?= base_url('admin/tagihan/daftar_tagihan/kelas_periode') ?>',
            type: 'POST',
            data: {
                id_periode: idPeriode
            },
            dataType: 'JSON',
            success: function(rows) {
                draftClasses = rows || [];
                renderDraftClasses([]);
                toggleDraftTarget();
            },
            error: function(xhr) {
                ajaxError(xhr);
            }
        });
    }

    function renderDraftClasses(selectedRows) {
        var selected = {};
        (selectedRows || []).forEach(function(row) {
            selected[Number(row.id_kelas_setting)] = true;
        });
        var html = '';
        draftClasses.forEach(function(row) {
            html += '<div class="col-md-4"><label class="border rounded p-2 w-100"><input type="checkbox" class="form-check-input me-1 draft-kelas-check" value="' + Number(row.id) + '" ' + (selected[Number(row.id)] ? 'checked' : '') + '> ' + escapeHtml(row.nama_kelas) + '</label></div>';
        });
        $('#draft_kelas_options').html(html || '<div class="text-muted">Tidak ada kelas pada tahun ajaran ini.</div>');
    }

    function renderDraftStudents() {
        var html = '';
        Object.keys(draftStudents).forEach(function(id) {
            var row = draftStudents[id];
            html += '<span class="badge bg-primary-subtle text-primary p-2">' + escapeHtml(row.nama_lengkap || row.nama_siswa || '-') + ' - ' + escapeHtml(row.nama_kelas || '-') + ' <button type="button" class="btn-close btn-close-sm ms-1" onclick="hapusDraftSiswa(' + Number(id) + ')"></button></span>';
        });
        $('#draft_siswa_terpilih').html(html || '<span class="text-muted">Belum ada siswa dipilih.</span>');
    }

    function cariDraftSiswa() {
        $.ajax({
            url: '<?= base_url('admin/tagihan/daftar_tagihan/cari_siswa') ?>',
            type: 'POST',
            data: {
                id_periode: $('#edit_id_periode').val(),
                q: $('#draft_cari_siswa').val()
            },
            dataType: 'JSON',
            success: function(rows) {
                var html = '';
                (rows || []).forEach(function(row) {
                    html += '<button type="button" class="btn btn-sm btn-outline-primary me-1 mb-1 draft-add-student" data-row="' + btoa(unescape(encodeURIComponent(JSON.stringify(row)))) + '">' + escapeHtml(row.nama_lengkap) + ' - ' + escapeHtml(row.nama_kelas || '-') + '</button>';
                });
                $('#draft_hasil_siswa').html(html || '<span class="text-muted">Siswa tidak ditemukan.</span>');
                $('.draft-add-student').off('click').on('click', function() {
                    var row = JSON.parse(decodeURIComponent(escape(atob($(this).data('row')))));
                    draftStudents[row.id] = row;
                    renderDraftStudents();
                });
            },
            error: function(xhr) {
                ajaxError(xhr);
            }
        });
    }

    function hapusDraftSiswa(id) {
        delete draftStudents[id];
        renderDraftStudents();
    }

    function toggleDraftTarget() {
        if ($('#edit_mode').val() !== 'Draft') return;
        var target = $('#edit_target').val();
        $('#draft_target_kelas_area').toggleClass('d-none', target !== 'Kelas');
        $('#draft_target_siswa_area').toggleClass('d-none', target !== 'Siswa');
    }

    function renderDraftPeriods(master, selectedPeriods) {
        var selected = {};
        (selectedPeriods || []).forEach(function(row) {
            selected[Number(row.bulan) + '-' + Number(row.tahun)] = row;
        });
        var html = '';
        $('.draft-col-pilih').removeClass('d-none');
        $('#btn_draft_semua_bulan').toggleClass('d-none', master.tipe_tagihan !== 'Bulanan');
        if (master.tipe_tagihan === 'Bulanan') {
            var years = String(master.periode || '').split('/');
            var awal = Number(years[0] || 0),
                akhir = Number(years[1] || (awal + 1));
            [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6].forEach(function(bulan) {
                var tahun = bulan >= 7 ? awal : akhir;
                var key = bulan + '-' + tahun;
                var row = selected[key] || {};
                var checked = !!selected[key];
                var nominal = Number(row.nominal || master.nominal_default || 0);
                html += '<tr class="edit-period-item" data-id="' + Number(row.id || 0) + '" data-bulan="' + bulan + '" data-tahun="' + tahun + '" data-nama="' + bulanNama[bulan] + '">' +
                    '<td class="draft-col-pilih"><input type="checkbox" class="form-check-input draft-period-check" ' + (checked ? 'checked' : '') + '></td>' +
                    '<td><strong>' + bulanNama[bulan] + ' ' + tahun + '</strong></td>' +
                    '<td><div class="input-group input-group-sm"><span class="input-group-text">Rp</span><input type="text" class="form-control money-input edit-period-nominal" value="' + nominal.toLocaleString('id-ID') + '" ' + (checked ? '' : 'disabled') + '></div></td>' +
                    '<td><input type="text" class="form-control form-control-sm edit-period-due" value="' + escapeHtml(row.tanggal_jatuh_tempo || '') + '" ' + (checked ? '' : 'disabled') + '></td></tr>';
            });
        } else {
            (selectedPeriods || []).forEach(function(row) {
                html += '<tr class="edit-period-item" data-id="' + Number(row.id) + '" data-bulan="' + Number(row.bulan) + '" data-tahun="' + Number(row.tahun) + '" data-nama="' + escapeHtml(row.nama_bulan || '') + '">' +
                    '<td class="draft-col-pilih"><input type="checkbox" class="form-check-input draft-period-check" checked disabled></td><td><strong>' + escapeHtml(row.nama_bulan || '-') + ' ' + Number(row.tahun || 0) + '</strong></td>' +
                    '<td><div class="input-group input-group-sm"><span class="input-group-text">Rp</span><input type="text" class="form-control money-input edit-period-nominal" value="' + Number(row.nominal || 0).toLocaleString('id-ID') + '"></div></td>' +
                    '<td><input type="text" class="form-control form-control-sm edit-period-due" value="' + escapeHtml(row.tanggal_jatuh_tempo || '') + '"></td></tr>';
            });
        }
        $('#edit_period_rows').html(html);
        $('#edit_period_rows .draft-period-check').on('change', function() {
            var row = $(this).closest('tr'),
                aktif = $(this).is(':checked');
            row.find('.edit-period-nominal,.edit-period-due').prop('disabled', !aktif);
        });
        flatpickr('#edit_period_rows .edit-period-due', {
            dateFormat: 'd-m-Y',
            allowInput: true
        });
    }

    function pilihSemuaBulanDraft() {
        var checks = $('#edit_period_rows .draft-period-check:not(:disabled)');
        var all = checks.length > 0 && checks.filter(':checked').length === checks.length;
        checks.prop('checked', !all).trigger('change');
        $('#btn_draft_semua_bulan').text(all ? 'Pilih Semua Bulan' : 'Batalkan Pilih Semua');
    }

    function simpanEditTagihan() {
        var mode = $('#edit_mode').val();
        var periods = [];

        $('#edit_period_rows .edit-period-item').each(function() {
            var row = $(this);
            var check = row.find('.draft-period-check');
            if (mode === 'Draft' && check.length && !check.is(':checked')) {
                return;
            }
            periods.push({
                id: Number(row.data('id') || 0),
                bulan: Number(row.data('bulan') || 0),
                tahun: Number(row.data('tahun') || 0),
                nama_bulan: String(row.data('nama') || row.find('td:eq(1)').text().trim().split(' ')[0]),
                nominal: row.find('.edit-period-nominal').val(),
                tanggal_jatuh_tempo: row.find('.edit-period-due').val()
            });
        });

        if (!periods.length) {
            Swal.fire('Perhatian', 'Pilih minimal satu periode tagihan.', 'warning');
            return;
        }

        var data = {
            id: $('#edit_id').val(),
            period_json: JSON.stringify(periods)
        };
        var url = '<?= base_url('admin/tagihan/daftar_tagihan/update_terbit') ?>';

        if (mode === 'Draft') {
            var targetKelas = $('#draft_kelas_options .draft-kelas-check:checked').map(function() {
                return Number(this.value);
            }).get();
            data.id_periode = $('#edit_periode').val();
            data.id_jenis_tagihan = $('#edit_jenis').val();
            data.nama_tagihan = $('#edit_nama').val();
            data.dianggap_tunggakan = $('#edit_tunggakan').val();
            data.target_tagihan = $('#edit_target').val();
            data.keterangan = $('#edit_keterangan').val();
            data.target_kelas_json = JSON.stringify(targetKelas);
            data.target_siswa_json = JSON.stringify(Object.keys(draftStudents).map(Number));
            url = '<?= base_url('admin/tagihan/daftar_tagihan/update_draft') ?>';
        } else {
            data.nama_tagihan = $('#edit_nama').val();
        }

        $('#btn_simpan_edit').prop('disabled', true);
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
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
            success: function(r) {
                var ok = r.result === 'true';
                Swal.fire(ok ? 'Berhasil' : 'Gagal', r.message, ok ? 'success' : 'error');
                if (ok) {
                    modalEditTagihan.hide();
                    loadData();
                }
            },
            error: function(xhr) {
                ajaxError(xhr);
            },
            complete: function() {
                $('#btn_simpan_edit').prop('disabled', false);
            }
        });
    }

    function hapusDraft(id) {
        confirmAction('Hapus draft tagihan?', 'Draft yang belum diterbitkan akan dihapus bersama target dan tarif draft.', function() {
            $.ajax({
                url: '<?= base_url('admin/tagihan/daftar_tagihan/hapus_draft') ?>',
                type: 'POST',
                data: {
                    id: id
                },
                dataType: 'JSON',
                success: function(r) {
                    var ok = r.result === 'true';
                    Swal.fire(ok ? 'Berhasil' : 'Gagal', r.message, ok ? 'success' : 'error');
                    if (ok) loadData();
                },
                error: function(xhr) {
                    ajaxError(xhr);
                }
            });
        });
    }

    // Cegah permintaan ganda saat satu draft sedang diproses atau statusnya sedang dicek.
    var terbitkanSedangProses = {};

    function terbitkan(id) {
        if (terbitkanSedangProses[id]) return;
        confirmAction('Terbitkan draft tagihan?', 'Pastikan target dan tarif sudah benar. Setelah diterbitkan, target siswa tidak dapat diubah dari Daftar Tagihan.', function() {
            if (terbitkanSedangProses[id]) return;
            terbitkanSedangProses[id] = true;
            $.ajax({
                url: '<?= base_url('admin/tagihan/daftar_tagihan/terbitkan') ?>',
                type: 'POST',
                data: {
                    id: id
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
                success: function(r) {
                    terbitkanSedangProses[id] = false;
                    var ok = r.result === 'true';
                    Swal.fire(ok ? 'Berhasil' : 'Gagal', r.message, ok ? 'success' : 'error');
                    // Selalu ambil status terbaru, termasuk jika draft ternyata telah terbit.
                    loadData();
                },
                error: function() {
                    // 504/koneksi terputus belum tentu berarti transaksi penerbitan gagal.
                    $.ajax({
                        url: '<?= base_url('admin/tagihan/daftar_tagihan/detail') ?>',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'JSON',
                        success: function(r) {
                            if (r.result === 'true' && r.master && r.master.status === 'Aktif') {
                                Swal.fire('Berhasil', 'Tagihan telah diterbitkan dan sekarang berstatus Aktif.', 'success');
                            } else {
                                Swal.fire('Periksa Status Tagihan', 'Respons penerbitan terputus. Proses mungkin masih berjalan. Periksa status terbaru sebelum mencoba lagi.', 'warning');
                            }
                        },
                        error: function() {
                            Swal.fire('Periksa Status Tagihan', 'Koneksi terputus saat penerbitan. Periksa status tagihan setelah memuat ulang halaman sebelum mencoba lagi.', 'warning');
                        },
                        complete: function() {
                            terbitkanSedangProses[id] = false;
                            loadData();
                        }
                    });
                }
            });
        });
    }

    function batalkan(id) {
        Swal.fire({
            title: 'Batalkan sisa tagihan',
            input: 'textarea',
            inputLabel: 'Alasan wajib',
            showCancelButton: true,
            confirmButtonText: 'Batalkan Sisa',
            preConfirm: function(v) {
                if (!v) Swal.showValidationMessage('Alasan wajib diisi');
                return v;
            }
        }).then(function(res) {
            if (!res.isConfirmed) return;

            $.ajax({
                url: '<?= base_url('admin/tagihan/daftar_tagihan/batalkan_sisa') ?>',
                type: 'POST',
                data: {
                    id: id,
                    alasan: res.value
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
                success: function(r) {
                    var ok = r.result === 'true';
                    Swal.fire(ok ? 'Berhasil' : 'Gagal', r.message, ok ? 'success' : 'error');
                    if (ok) loadData();
                },
                error: function(xhr) {
                    ajaxError(xhr);
                }
            });
        });
    }

    function refreshDaftarTagihanPagination() {
        paging($('#data .card-mapel'),
            parseInt($('#dt-length-daftar-tagihan').val(), 10) || 10, '#pagination-daftar-tagihan');
    }
</script>