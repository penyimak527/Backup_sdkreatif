

<div class="card">
    <div class="card-header app-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h4 class="header-title mb-0">Data Metode Pembayaran</h4>
        <button type="button" class="btn btn-outline-primary" id="btn_tambah_metode">
            <i class="ri-add-line me-1"></i>Tambah
        </button>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <input type="text" id="search_metode" class="form-control" placeholder="Cari metode pembayaran ...">
            </div>
            <div class="col-md-3">
                <select id="status_metode" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary" id="btn_cari_metode">
                    <i class="ri-search-line me-1"></i>Cari
                </button>
            </div>
        </div>

        <div id="data_metode">
            <div class="card-mapel">
                <div class="keterangan-mapel">
                    <div class="keterangan-mapel-kiri">
                        <h5 class="judul-mapel" style="margin:0; margin-top:8px;">Memuat data metode pembayaran...</h5>
                    </div>
                </div>
            </div>
        </div>
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

<div class="modal fade" id="modalMetode" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_metode_title">Tambah Metode Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="form_metode">
                    <input type="hidden" name="id">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="nama_metode" class="form-label">Nama Metode</label>
                            <input type="text" id="nama_metode" name="nama_metode" class="form-control" placeholder="Contoh: Tunai, Transfer Bank, QRIS" required>
                        </div>
                        <div class="col-md-12">
                            <label for="status_form_metode" class="form-label">Status</label>
                            <select id="status_form_metode" name="status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn_simpan_metode">Simpan</button>
            </div>
        </div>
    </div>
</div>


<script>
// Helper lokal Metode Pembayaran.
// Dibuat lokal agar halaman ini tidak bergantung pada helper JavaScript dari view lain.
function escapeHtml(text) {
    return $('<div>').text(text == null ? '' : text).html();
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

var modalMetode = null;
var metodeRows = [];

$(document).ready(function () {
    modalMetode = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMetode'));

    // Pastikan fokus keluar dari modal sebelum Bootstrap memasang aria-hidden.
    // Ini mencegah warning aksesibilitas ketika modal ditutup dari tombol Simpan/Tutup.
    $('#modalMetode').on('hide.bs.modal', function () {
        var active = document.activeElement;
        if (active && this.contains(active) && typeof active.blur === 'function') {
            active.blur();
        }
    });

    $('#btn_tambah_metode').on('click', function () { openMetodeForm(); });
    $('#btn_cari_metode').on('click', loadMetodeData);
    $('#search_metode').on('keyup', function (event) {
        if (event.key === 'Enter') loadMetodeData();
    });
    $('#btn_simpan_metode').on('click', saveMetodeData);
    $('#dt-length-0').on('change', refreshMetodePagination);

    loadMetodeData();
});

function loadMetodeData() {
    $('#data_metode').html('<div class="card-mapel"><div class="keterangan-mapel"><div class="keterangan-mapel-kiri"><h5 class="judul-mapel" style="margin:0; margin-top:8px;">Memuat data metode pembayaran...</h5></div></div></div>');

    $.post('<?= base_url('admin/tagihan/metode_pembayaran/data') ?>', {
        search: $('#search_metode').val(),
        status: $('#status_metode').val()
    }, function (response) {
        metodeRows = (response && response.data) ? response.data : [];

        if (!metodeRows.length) {
            $('#data_metode').html('<div class="card-mapel"><div class="keterangan-mapel"><div class="keterangan-mapel-kiri"><h5 class="judul-mapel" style="margin:0; margin-top:8px;">Tidak ada data</h5></div></div></div>');
            refreshMetodePagination();
            return;
        }

        var html = metodeRows.map(function (row, index) {
            var aktif = row.status === 'Aktif';
            return '<div class="card-mapel">' +
                '<p class="keterangan-hari">Status : <span class="badge bg-' + (aktif ? 'success' : 'secondary') + '">' + escapeHtml(row.status || '-') + '</span></p>' +
                '<div class="keterangan-mapel">' +
                    '<div class="keterangan-mapel-kiri">' +
                        '<h5 class="judul-mapel" style="margin:0; margin-top:8px;">' + (index + 1) + '. ' + escapeHtml(row.nama_metode || '-') + '</h5>' +
                    '</div>' +
                    '<div class="keterangan-mapel-kanan">' +
                        '<div class="d-flex justify-content-center gap-2 flex-wrap">' +
                            '<button type="button" class="btn btn-sm btn-outline-warning btn-edit-metode" data-id="' + row.id + '" title="Edit"><i class="ri-edit-line"></i></button>' +
                            '<button type="button" class="btn btn-sm ' + (aktif ? 'btn-outline-danger' : 'btn-outline-success') + ' btn-status-metode" data-id="' + row.id + '" title="' + (aktif ? 'Nonaktifkan' : 'Aktifkan') + '">' +
                                '<i class="' + (aktif ? 'ri-close-circle-line' : 'ri-check-line') + '"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        $('#data_metode').html(html);
        refreshMetodePagination();
    }, 'json').fail(ajaxError);
}

$(document).on('click', '.btn-edit-metode', function () {
    var id = Number($(this).data('id'));
    var row = metodeRows.find(function (item) { return Number(item.id) === id; });
    openMetodeForm(row || null);
});

$(document).on('click', '.btn-status-metode', function () {
    var id = Number($(this).data('id'));
    var row = metodeRows.find(function (item) { return Number(item.id) === id; });
    if (!row) return;

    var action = row.status === 'Aktif' ? 'nonaktifkan' : 'aktifkan';
    confirmAction(
        (action === 'nonaktifkan' ? 'Nonaktifkan' : 'Aktifkan') + ' metode pembayaran?',
        'Riwayat transaksi lama tidak akan berubah.',
        function () {
            $.post('<?= base_url('admin/tagihan/metode_pembayaran/status') ?>', {id: id}, function (response) {
                var berhasil = response.result === 'true';
                Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
                if (berhasil) loadMetodeData();
            }, 'json').fail(ajaxError);
        }
    );
});

function openMetodeForm(row) {
    $('#form_metode')[0].reset();
    $('#form_metode [name="id"]').val(row ? row.id : '');
    $('#status_form_metode').val(row ? row.status : 'Aktif');
    $('#modal_metode_title').text(row ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran');

    if (row) {
        $('#nama_metode').val(row.nama_metode || '');
    }

    modalMetode.show();
}

function saveMetodeData() {
    var button = $('#btn_simpan_metode');
    button.prop('disabled', true);

    $.post('<?= base_url('admin/tagihan/metode_pembayaran/simpan') ?>', $('#form_metode').serialize(), function (response) {
        var berhasil = response.result === 'true';
        if (berhasil) {
            modalMetode.hide();
            loadMetodeData();
        }
        Swal.fire(berhasil ? 'Berhasil' : 'Gagal', response.message, berhasil ? 'success' : 'error');
    }, 'json').fail(ajaxError).always(function () {
        button.prop('disabled', false);
    });
}

function refreshMetodePagination() {
    paging($('#data_metode .card-mapel'), parseInt($('#dt-length-0').val(), 10) || 10, '#pagination');
}

</script>
