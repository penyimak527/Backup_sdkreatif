<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header app-card-header d-flex align-items-start justify-content-between gap-2">
                <div>
                    <h4 class="header-title mb-1">Daftar Format Bukti</h4>
                    <small class="text-muted">Pilih format untuk diedit atau dijadikan default.</small>
                </div>
                <button type="button" id="btnBaru" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i>Format Baru</button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Jenis Format</label>
                    <select id="filterJenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="Bukti Pembayaran">Bukti Pembayaran</option>
                        <option value="Surat Tunggakan">Surat Tunggakan</option>
                    </select>
                </div>
                <div id="formatList">
                    <div class="text-center text-muted py-4">Memuat format...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header app-card-header">
                <h4 class="header-title mb-1" id="formTitle">Tambah Format Bukti</h4>
                <small class="text-muted">Atur ukuran, identitas sekolah, elemen cetak, dan tanda tangan seperti konsep Almahbaro.</small>
            </div>
            <div class="card-body">
                <form id="formFormat" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="formatId">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Jenis Dokumen</label>
                            <select class="form-select" name="jenis_dokumen" id="jenisDokumen">
                                <option value="Bukti Pembayaran">Bukti Pembayaran</option>
                                <option value="Surat Tunggakan">Surat Tunggakan</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Nama Format</label><input class="form-control" name="nama_format" id="namaFormat" placeholder="Contoh: Bukti 58mm" required></div>
                        <div class="col-md-2"><label class="form-label">Jadikan Default</label><select class="form-select" name="status_default" id="statusDefault">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select></div>
                        <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status" id="statusFormat">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Tidak Aktif</option>
                            </select></div>

                        <div class="col-md-4"><label class="form-label">Ukuran Kertas</label><select class="form-select" name="ukuran_kertas" id="ukuranKertas">
                                <option value="58mm">58mm</option>
                                <option value="80mm">80mm</option>
                                <option value="A5">A5</option>
                                <option value="A4">A4</option>
                                <option value="Custom">Custom</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Orientasi</label><select class="form-select" name="orientasi" id="orientasi">
                                <option value="Portrait">Portrait</option>
                                <option value="Landscape">Landscape</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Judul Bukti</label><input class="form-control" name="judul" id="judulDokumen" required></div>
                        <div class="col-12 d-none" id="customSize">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Lebar Kertas Custom (mm)</label><input type="number" min="1" step="0.1" class="form-control" name="lebar_kertas" id="lebarKertas"></div>
                                <div class="col-md-6"><label class="form-label">Tinggi Kertas Custom (mm)</label><input type="number" min="1" step="0.1" class="form-control" name="tinggi_kertas" id="tinggiKertas"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <h5 class="fs-15 mb-0">Identitas Sekolah</h5>
                        </div>
                        <div class="col-md-6"><label class="form-label">Nama Sekolah</label><input class="form-control" name="nama_sekolah" id="namaSekolah" value="<?= html_escape(config_item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Telepon Sekolah</label><input class="form-control" name="telepon_sekolah" id="teleponSekolah"></div>
                        <div class="col-12"><label class="form-label">Alamat Sekolah</label><textarea class="form-control" name="alamat_sekolah" id="alamatSekolah" rows="2"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Logo Sekolah</label><input type="file" class="form-control" name="logo" id="logoSekolah" accept="image/png,image/jpeg,image/webp"><small class="text-muted" id="logoInfo">Kosongkan saat edit untuk mempertahankan logo lama. File disimpan di storage/spp/format_bukti/.</small></div>
                        <div class="col-md-6"><label class="form-label">Tampilkan Logo</label><select class="form-select" name="tampilkan_logo" id="tampilkanLogo">
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select></div>
                        <div class="col-12"><label class="form-label">Teks Header Tambahan</label><textarea class="form-control" name="header_text" id="headerText" rows="2" placeholder="Teks tambahan di bawah identitas sekolah"></textarea></div>

                        <div class="col-12">
                            <hr class="my-1">
                            <h5 class="fs-15 mb-0">Margin Cetak (mm)</h5>
                        </div>
                        <div class="col-md-3"><label class="form-label">Atas</label><input type="number" min="0" step="0.1" class="form-control" name="margin_atas" id="marginAtas" value="0.5"></div>
                        <div class="col-md-3"><label class="form-label">Bawah</label><input type="number" min="0" step="0.1" class="form-control" name="margin_bawah" id="marginBawah" value="0.5"></div>
                        <div class="col-md-3"><label class="form-label">Kiri</label><input type="number" min="0" step="0.1" class="form-control" name="margin_kiri" id="marginKiri" value="0.5"></div>
                        <div class="col-md-3"><label class="form-label">Kanan</label><input type="number" min="0" step="0.1" class="form-control" name="margin_kanan" id="marginKanan" value="0.5"></div>

                        <div class="col-12 pembayaran-only"><label class="form-label">Elemen yang Ditampilkan</label>
                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label small mb-1">Terbilang</label><select class="form-select" name="tampilkan_terbilang" id="showTerbilang">
                                        <option value="Ya">Ya</option>
                                        <option value="Tidak">Tidak</option>
                                    </select></div>
                                <div class="col-md-3"><label class="form-label small mb-1">Uang Diterima</label><select class="form-select" name="tampilkan_uang_diterima" id="showUang">
                                        <option value="Ya">Ya</option>
                                        <option value="Tidak">Tidak</option>
                                    </select></div>
                                <div class="col-md-3"><label class="form-label small mb-1">Kembalian</label><select class="form-select" name="tampilkan_kembalian" id="showKembali">
                                        <option value="Ya">Ya</option>
                                        <option value="Tidak">Tidak</option>
                                    </select></div>
                                <div class="col-md-3"><label class="form-label small mb-1">Sisa Tagihan</label><select class="form-select" name="tampilkan_sisa_tagihan" id="showSisa">
                                        <option value="Ya">Ya</option>
                                        <option value="Tidak">Tidak</option>
                                    </select></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <h5 class="fs-15 mb-0">Tanda Tangan dan Footer</h5>
                        </div>
                        <div class="col-md-5"><label class="form-label">Nama Penandatangan</label><input class="form-control" name="nama_penandatangan" id="namaTtd"></div>
                        <div class="col-md-4"><label class="form-label">Jabatan</label><input class="form-control" name="jabatan_penandatangan" id="jabatanTtd"></div>
                        <div class="col-md-3"><label class="form-label">Posisi</label><select class="form-select" name="posisi_tanda_tangan" id="posisiTtd">
                                <option value="Kiri">Kiri</option>
                                <option value="Tengah">Tengah</option>
                                <option value="Kanan">Kanan</option>
                            </select></div>
                        <div class="col-12"><label class="form-label">Teks Footer</label><textarea class="form-control" name="footer_text" id="footerText" rows="2" placeholder="Contoh: Simpan bukti ini sebagai tanda pembayaran yang sah"></textarea></div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4"><button type="button" id="btnPreview" class="btn btn-outline-secondary"><i class="ri-eye-line me-1"></i>Preview</button><button type="button" id="btnTesCetak" class="btn btn-outline-primary"><i class="ri-printer-line me-1"></i>Tes Cetak</button><button type="submit" id="btnSimpan" class="btn btn-primary"><i class="ri-save-line me-1"></i>Simpan Pengaturan</button></div>
                </form>
                <div id="previewBox" class="border rounded p-3 mt-3 d-none"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailFormat" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Format Bukti</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailFormatBody"></div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<script>
    $(function() {
        const base = <?= json_encode(site_url('admin/spp/pengaturan_cetak/')) ?>;
        let rows = [];
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));
        const request = (path, data) => $.ajax({
            url: base + path,
            type: 'POST',
            dataType: 'json',
            data: data || {}
        });
        const fail = x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Permintaan gagal diproses.', 'error');
        const detailModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailFormat'));

        function cfg(r) {
            try {
                return JSON.parse(r && r.pengaturan_json ? r.pengaturan_json : '{}') || {};
            } catch (e) {
                return {};
            }
        }

        function applyDefaultPrintByType() {
            if ($('#formatId').val()) return;
            const pay = $('#jenisDokumen').val() === 'Bukti Pembayaran';
            $('#ukuranKertas').val(pay ? '58mm' : 'A4');
            $('#orientasi').val('Portrait');
            $('#judulDokumen').val(pay ? 'BUKTI PEMBAYARAN' : 'SURAT PEMBERITAHUAN TUNGGAKAN');
            $('#marginAtas,#marginBawah,#marginKiri,#marginKanan').val(pay ? '0.5' : '18');
            updateSize();
        }

        function resetForm() {
            $('#formFormat')[0].reset();
            $('#formatId').val('');
            $('#jenisDokumen').val('Bukti Pembayaran');
            $('#statusDefault').val('Tidak');
            $('#statusFormat').val('Aktif');
            $('#namaSekolah').val(<?= json_encode(config_item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah') ?>);
            $('#tampilkanLogo,#showTerbilang,#showUang,#showKembali,#showSisa').val('Ya');
            $('#posisiTtd').val('Kanan');
            $('#previewBox').addClass('d-none').empty();
            $('#logoInfo').text('Kosongkan saat edit untuk mempertahankan logo lama. File disimpan di storage/spp/format_bukti/.');
            applyDefaultPrintByType();
            updateType();
            $('#formTitle').text('Tambah Format Bukti');
        }

        function updateType() {
            let pay = $('#jenisDokumen').val() === 'Bukti Pembayaran';
            $('.pembayaran-only').toggleClass('d-none', !pay);
            if (!$('#formatId').val()) applyDefaultPrintByType();
        }

        function updateSize() {
            $('#customSize').toggleClass('d-none', $('#ukuranKertas').val() !== 'Custom');
        }

        function renderList() {
            let jenis = $('#filterJenis').val();
            let data = rows.filter(r => !jenis || r.jenis_dokumen === jenis);
            $('#formatList').html(data.length ? data.map(r => `<div class="border rounded p-3 mb-2"><div class="d-flex justify-content-between gap-2"><div><strong>${esc(r.nama_format)}</strong><div class="small text-muted mt-1">${esc(r.jenis_dokumen)} · ${esc(r.ukuran_kertas)} · ${esc(r.orientasi)}</div></div>${r.status_default==='Ya'?'<span class="badge bg-primary align-self-start">Default</span>':''}</div><div class="mt-2"><span class="badge ${r.status==='Aktif'?'bg-success':'bg-secondary'}">${r.status==='Aktif'?'Aktif':'Tidak Aktif'}</span></div><div class="d-flex gap-2 mt-3"><button class="btn btn-sm btn-outline-info btn-detail" data-id="${r.id}" title="Detail"><i class="ri-eye-line"></i></button><button class="btn btn-sm btn-outline-warning btn-edit" data-id="${r.id}"><i class="ri-edit-line me-1"></i>Edit</button>${r.status_default!=='Ya'&&r.status==='Aktif'?`<button class="btn btn-sm btn-outline-primary btn-default" data-id="${r.id}"><i class="ri-star-line me-1"></i>Default</button>`:''}</div></div>`).join('') : '<div class="text-center text-muted py-4">Belum ada format sesuai pilihan.</div>');
        }

        function load() {
            request('data', {}).done(r => {
                rows = r.data || [];
                renderList();
            }).fail(fail);
        }

        function fill(row) {
            resetForm();
            $('#formatId').val(row.id);
            $('#jenisDokumen').val(row.jenis_dokumen);
            $('#namaFormat').val(row.nama_format);
            $('#statusDefault').val(row.status_default);
            $('#statusFormat').val(row.status);
            $('#ukuranKertas').val(row.ukuran_kertas);
            $('#orientasi').val(row.orientasi);
            $('#judulDokumen').val(row.judul);
            $('#lebarKertas').val(row.lebar_kertas || '');
            $('#tinggiKertas').val(row.tinggi_kertas || '');
            $('#headerText').val(row.header_text || '');
            $('#footerText').val(row.footer_text || '');
            $('#namaTtd').val(row.nama_penandatangan || '');
            $('#jabatanTtd').val(row.jabatan_penandatangan || '');
            let c = cfg(row);
            $('#namaSekolah').val(c.nama_sekolah || <?= json_encode(config_item('nama_sekolah') ?: 'SD Kreatif Muhammadiyah') ?>);
            $('#teleponSekolah').val(c.telepon_sekolah || '');
            $('#alamatSekolah').val(c.alamat_sekolah || '');
            $('#tampilkanLogo').val(c.tampilkan_logo || 'Ya');
            let marginDefault = row.jenis_dokumen === 'Bukti Pembayaran' ? 0.5 : 18;
            $('#marginAtas').val(c.margin_atas ?? marginDefault);
            $('#marginBawah').val(c.margin_bawah ?? marginDefault);
            $('#marginKiri').val(c.margin_kiri ?? marginDefault);
            $('#marginKanan').val(c.margin_kanan ?? marginDefault);
            $('#showTerbilang').val(c.tampilkan_terbilang || 'Ya');
            $('#showUang').val(c.tampilkan_uang_diterima || 'Ya');
            $('#showKembali').val(c.tampilkan_kembalian || 'Ya');
            $('#showSisa').val(c.tampilkan_sisa_tagihan || 'Ya');
            $('#posisiTtd').val(c.posisi_tanda_tangan || 'Kanan');
            if (c.logo_sekolah) $('#logoInfo').text('Logo tersimpan: ' + c.logo_sekolah);
            updateType();
            updateSize();
            $('#formTitle').text('Edit Format ' + row.jenis_dokumen);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function detail(id) {
            request('detail', {
                id: id
            }).done(r => {
                if (r.result !== 'true') return Swal.fire('Gagal', r.message, 'error');
                let d = r.data,
                    c = d.pengaturan || cfg(d);
                $('#detailFormatBody').html(`<div class="row g-3"><div class="col-md-6"><small class="text-muted d-block">Jenis</small><strong>${esc(d.jenis_dokumen)}</strong></div><div class="col-md-6"><small class="text-muted d-block">Nama Format</small><strong>${esc(d.nama_format)}</strong></div><div class="col-md-6"><small class="text-muted d-block">Ukuran</small>${esc(d.ukuran_kertas)} / ${esc(d.orientasi)}</div><div class="col-md-6"><small class="text-muted d-block">Status</small>${esc(d.status)}${d.status_default==='Ya'?' · Default':''}</div><div class="col-12"><small class="text-muted d-block">Judul</small>${esc(d.judul)}</div><div class="col-12"><small class="text-muted d-block">Identitas Sekolah</small>${esc(c.nama_sekolah||'-')}<br><span class="text-muted">${esc(c.alamat_sekolah||'')}</span></div></div>`);
                detailModal.show();
            }).fail(fail);
        }

        function setDefault(id) {
            Swal.fire({
                title: 'Jadikan format default?',
                text: 'Format default untuk jenis yang sama akan diganti.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then(x => {
                if (!x.isConfirmed) return;
                request('set_default', {
                    id: id
                }).done(r => {
                    Swal.fire(r.result === 'true' ? 'Berhasil' : 'Gagal', r.message, r.result === 'true' ? 'success' : 'error');
                    if (r.result === 'true') load();
                }).fail(fail);
            });
        }

        function previewHtml() {
            let type = $('#jenisDokumen').val(),
                pay = type === 'Bukti Pembayaran',
                align = $('#posisiTtd').val() === 'Kiri' ? 'start' : ($('#posisiTtd').val() === 'Tengah' ? 'center' : 'end');
            let logo = $('#tampilkanLogo').val() === 'Ya' ? '<div class="mb-1"><span class="badge bg-light text-dark border">LOGO</span></div>' : '';
            let body = pay ? `<div class="d-flex justify-content-between mt-3"><span>No Transaksi</span><strong>BYR/202610/00001</strong></div><div class="d-flex justify-content-between"><span>Siswa</span><span>CONTOH SISWA</span></div><table class="table table-sm mt-2"><tr><td>SPP Oktober</td><td class="text-end">Rp150.000</td></tr></table><div class="d-flex justify-content-between"><strong>Total</strong><strong>Rp150.000</strong></div>${$('#showTerbilang').val()==='Ya'?'<div class="small mt-1">Terbilang: Seratus lima puluh ribu rupiah</div>':''}${$('#showUang').val()==='Ya'?'<div class="d-flex justify-content-between"><span>Uang diterima</span><span>Rp200.000</span></div>':''}${$('#showKembali').val()==='Ya'?'<div class="d-flex justify-content-between"><span>Kembalian</span><span>Rp50.000</span></div>':''}${$('#showSisa').val()==='Ya'?'<div class="d-flex justify-content-between"><span>Sisa tagihan</span><span>Rp0</span></div>':''}` : `<div class="mt-3">Nomor: <strong>STG/202610/00001</strong></div><p class="mt-2">Yth. Bapak/Ibu Wali dari <strong>CONTOH SISWA</strong></p><p>Terdapat tunggakan sebesar <strong>Rp350.000</strong>.</p>`;
            return `<div class="text-center">${logo}<h5 class="mb-0">${esc($('#namaSekolah').val()||'Sekolah')}</h5><small>${esc($('#alamatSekolah').val()||'')}</small><br><small>${esc($('#teleponSekolah').val()||'')}</small>${$('#headerText').val()?'<div class="mt-1">'+esc($('#headerText').val())+'</div>':''}<hr><strong>${esc($('#judulDokumen').val())}</strong></div>${body}<div class="text-${align} mt-4"><small>${esc($('#jabatanTtd').val()||'Petugas')}</small><div style="height:45px"></div><strong>${esc($('#namaTtd').val()||'(........................)')}</strong></div>${$('#footerText').val()?'<div class="text-center border-top pt-2 mt-3 small">'+esc($('#footerText').val())+'</div>':''}`;
        }

        function preview() {
            $('#previewBox').removeClass('d-none').html(previewHtml());
        }

        function testPrintPageSize() {
            let size = $('#ukuranKertas').val(),
                orientation = String($('#orientasi').val() || 'Portrait').toLowerCase();
            if (size === 'A4' || size === 'A5') return size + ' ' + (orientation === 'landscape' ? 'landscape' : 'portrait');
            if (size === 'Custom') {
                let w = parseFloat($('#lebarKertas').val() || 0),
                    h = parseFloat($('#tinggiKertas').val() || 0);
                if (w > 0 && h > 0) return w + 'mm ' + h + 'mm';
                return 'A4 portrait';
            }
            return size + ' auto';
        }

        function testPrintCss() {
            let mt = parseFloat($('#marginAtas').val() || 0),
                mb = parseFloat($('#marginBawah').val() || 0),
                ml = parseFloat($('#marginKiri').val() || 0),
                mr = parseFloat($('#marginKanan').val() || 0);
            return '@page{size:' + testPrintPageSize() + ';margin:' + mt + 'mm ' + mr + 'mm ' + mb + 'mm ' + ml + 'mm}*{box-sizing:border-box}html,body{margin:0;padding:0}body{font-family:Arial;font-size:12px}.paper{width:100%;max-width:none;margin:0}.text-center{text-align:center}.text-start{text-align:left}.text-end{text-align:right}.d-flex{display:flex}.justify-content-between{justify-content:space-between}.table{width:100%;border-collapse:collapse}.table td{padding:6px;border-bottom:1px solid #ddd}.mt-1{margin-top:4px}.mt-2{margin-top:8px}.mt-3{margin-top:16px}.mt-4{margin-top:24px}.border-top{border-top:1px solid #ccc}.pt-2{padding-top:8px}.small,small{font-size:12px}';
        }
        $('#btnPreview').on('click', preview);
        $('#btnTesCetak').on('click', function() {
            preview();
            let w = window.open('', '_blank', 'width=720,height=820');
            if (!w) return;
            w.document.write('<!doctype html><html><head><title>Tes Cetak</title><style>' + testPrintCss() + '</style></head><body><div class="paper">' + previewHtml() + '</div><script>window.onload=function(){window.print()}<\/script></body></html>');
            w.document.close();
        });
        $('#btnBaru').on('click', resetForm);
        $('#filterJenis').on('change', renderList);
        $('#jenisDokumen').on('change', updateType);
        $('#ukuranKertas').on('change', updateSize);
        $('#statusDefault').on('change', function() {
            if (this.value === 'Ya') $('#statusFormat').val('Aktif');
        });
        $('#formatList').on('click', '.btn-edit', function() {
            let id = Number($(this).data('id'));
            let row = rows.find(x => Number(x.id) === id);
            if (row) fill(row);
        }).on('click', '.btn-detail', function() {
            detail(Number($(this).data('id')));
        }).on('click', '.btn-default', function() {
            setDefault(Number($(this).data('id')));
        });
        $('#formFormat').on('submit', function(e) {
            e.preventDefault();
            let fd = new FormData(this),
                btn = $('#btnSimpan').prop('disabled', true);
            $.ajax({
                url: base + 'simpan',
                type: 'POST',
                data: fd,
                dataType: 'json',
                processData: false,
                contentType: false
            }).done(r => {
                Swal.fire(r.result === 'true' ? 'Berhasil' : 'Gagal', r.message, r.result === 'true' ? 'success' : 'error');
                if (r.result === 'true') {
                    load();
                    let savedId = Number(r.id);
                    setTimeout(() => {
                        let row = rows.find(x => Number(x.id) === savedId);
                        if (row) fill(row);
                    }, 250);
                }
            }).fail(fail).always(() => btn.prop('disabled', false));
        });
        resetForm();
        load();
    });
</script>