<div class="card">
    <div class="card-header app-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="header-title mb-0">Data Pos Penerimaan</h4>
        <button type="button" class="btn btn-outline-primary" onclick="openForm()">
            <i class="ri-add-line me-1"></i>Tambah
        </button>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <input type="text" id="search" class="form-control" placeholder="Cari Pos Penerimaan ...">
            </div>
            <div class="col-md-2">
                <select id="tipe" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="Bulanan">Bulanan</option>
                    <option value="Langsung">Langsung</option>
                    <option value="Tahunan">Tahunan</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 ">
                <button type="button" class="btn btn-primary" onclick="loadData()">
                    <i class="ri-search-line me-1"></i>Cari
                </button>
            </div>
        </div>
        <div id="data" class="crud-list"></div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-center flex-wrap gap-2 mt-3">
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

<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Tambah Pos Penerimaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="form">
                    <input type="hidden" name="id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode_jenis" class="form-control" placeholder="Otomatis jika kosong">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Pos Penerimaan</label>
                            <input type="text" name="nama_jenis" class="form-control" placeholder="Nama pos penerimaan ..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe</label>
                            <select name="tipe_default" class="form-select">
                                <option value="Bulanan">Bulanan</option>
                                <option value="Langsung">Langsung</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" placeholder="Keterangan ..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="btn_simpan" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pos Penerimaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="detail_content"></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
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
    }).then(function (result) {
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

    $form.find('.money-input').each(function () {
        original.push({ element: this, value: this.value });
        this.value = String(parseMoneyInput(this.value));
    });

    var data = $form.serialize();

    original.forEach(function (item) {
        item.element.value = item.value;
    });

    return data;
}

$(document)
    .off('input.tagihanMoney', '.money-input:not([readonly]):not([disabled])')
    .on('input.tagihanMoney', '.money-input:not([readonly]):not([disabled])', function () {
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
        onPageChange: function (page) {
            var start = page.pageSize * (page.currentPage - 1);
            var end = start + page.pageSize;
            $rows.hide();
            $rows.slice(start, end).show();
        }
    });
}


let modalForm;
let modalDetail;
let dataRows = [];

$(document).ready(function () {
    modalForm = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalForm'));
    modalDetail = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetail'));
    loadData();

    $('#search').on('keyup', function (event) {
        if (event.key === 'Enter') loadData();
    });
    $('#btn_simpan').on('click', saveData);
    $('#dt-length-0').on('change', refreshPagination);
});

function loadData() {
    $.post('<?= base_url('admin/tagihan/jenis_tagihan/result') ?>', {
        search: $('#search').val(),
        tipe: $('#tipe').val(),
        status: $('#filter_status').val()
    }, function (rows) {
        dataRows = rows || [];
        if (!rows.length) {
            $('#data').html(`
                <div class="card-mapel">
                    <div class="keterangan-mapel">
                        <div class="keterangan-mapel-kiri">
                            <h5 class="judul-mapel" style="margin:0; margin-top:8px;">Tidak ada data</h5>
                        </div>
                    </div>
                </div>
            `);
            refreshPagination();
            return;
        }

        const html = rows.map(function (row, index) {
            const aktif = row.status === 'Aktif';
            const nama = escapeHtml(row.nama_jenis || '-');
            const tipe = escapeHtml(row.tipe_default || '-');
            const kode = escapeHtml(row.kode_jenis || '-');
            const keterangan = escapeHtml(row.keterangan || '-');
            const statusBadge = `<span class="badge ${aktif ? 'bg-success' : 'bg-secondary'}">${escapeHtml(row.status || '-')}</span>`;

            return `
                <div class="card-mapel">
                    <p class="keterangan-hari">Status : ${statusBadge}</p>
                    <div class="keterangan-mapel">
                        <div class="keterangan-mapel-kiri">
                            <h5 class="judul-mapel" style="margin:0; margin-top:8px;">${index + 1}. ${nama}</h5>
                            <p class="keterangan-jam-mapel">Tipe : ${tipe}</p>
                            <p class="keterangan-jam-mapel">Kode : ${kode}</p>
                            <p class="keterangan-jam-mapel">Keterangan : ${keterangan}</p>
                        </div>
                        <div class="keterangan-mapel-kanan">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Detail" onclick="detailData(${row.id})">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Edit" onclick="openFormById(${row.id})">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm ${aktif ? 'btn-outline-danger' : 'btn-outline-success'}" title="${aktif ? 'Nonaktifkan' : 'Aktifkan'}" onclick="toggleStatus(${row.id})">
                                    <i class="${aktif ? 'ri-close-circle-line' : 'ri-check-line'}"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        }).join('');

        $('#data').html(html);
        refreshPagination();
    }, 'json').fail(ajaxError);
}


function detailData(id) {
    const row = dataRows.find(function (item) { return Number(item.id) === Number(id); });
    if (!row) return;
    $('#detail_content').html(
        '<div class="row g-3">' +
            '<div class="col-md-6"><strong>Nama Jenis</strong><div>' + escapeHtml(row.nama_jenis || '-') + '</div></div>' +
            '<div class="col-md-3"><strong>Tipe</strong><div>' + escapeHtml(row.tipe_default || '-') + '</div></div>' +
            '<div class="col-md-3"><strong>Status</strong><div><span class="badge bg-' + (row.status === 'Aktif' ? 'success' : 'secondary') + '">' + escapeHtml(row.status || '-') + '</span></div></div>' +
            '<div class="col-12"><strong>Keterangan</strong><div>' + escapeHtml(row.keterangan || '-') + '</div></div>' +
        '</div>'
    );
    modalDetail.show();
}

function openFormById(id) {
    const row = dataRows.find(function (item) { return Number(item.id) === Number(id); });
    openForm(row);
}

function openForm(row) {
    $('#form')[0].reset();
    $('#form [name="id"]').val(row ? row.id : '');
    $('#modal_title').text(row ? 'Edit Pos Penerimaan' : 'Tambah Pos Penerimaan');

    if (row) {
        Object.keys(row).forEach(function (key) {
            $('#form [name="' + key + '"]').val(row[key]);
        });
    }

    modalForm.show();
}

function saveData() {
    const button = $('#btn_simpan');
    button.prop('disabled', true);

    var id = Number($('#form [name="id"]').val() || 0);
    var url = id > 0
        ? '<?= base_url('admin/tagihan/jenis_tagihan/edit') ?>'
        : '<?= base_url('admin/tagihan/jenis_tagihan/tambah') ?>';

    $.post(url, $('#form').serialize(), function (response) {
        const berhasil = response.result === 'true';
        if (berhasil) {
            modalForm.hide();
            loadData();
        }
        Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
    }, 'json').fail(ajaxError).always(function () {
        button.prop('disabled', false);
    });
}

function toggleStatus(id) {
    confirmAction('Ubah status?', 'Jenis yang sudah digunakan tidak dihapus, hanya statusnya yang diubah.', function () {
        $.post('<?= base_url('admin/tagihan/jenis_tagihan/status') ?>', {id: id}, function (response) {
            const berhasil = response.result === 'true';
            Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
            if (berhasil) loadData();
        }, 'json').fail(ajaxError);
    });
}

function refreshPagination() {
    paging($('#data .card-mapel'), parseInt($('#dt-length-0').val(), 10) || 10, '#pagination');
}

</script>
