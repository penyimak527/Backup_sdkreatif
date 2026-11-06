<?php
$app_admin = $this->session->userdata('admin');
$app_admin = is_array($app_admin) ? $app_admin : array();
$app_admin_name = !empty($app_admin['nama_lengkap']) ? $app_admin['nama_lengkap'] : 'Administrator';
?>

<div class="card" id="card_cari_siswa">
    <div class="card-header app-card-header">
        <div>
            <h4 class="header-title mb-1">1. Cari Siswa</h4>
            <p class="text-muted mb-0">Cari siswa yang akan melakukan pembayaran.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-10">
                <label class="form-label" for="cari_siswa">Nama / NIS / NISN / Scan Kode</label>
                <input type="text" id="cari_siswa"
                    class="form-control"
                    placeholder="Masukkan nama, NIS, NISN, atau scan kode siswa"
                    autocomplete="off"
                >
            </div>
            <div class="col-md-2 d-grid">
                <button id="btn_cari_siswa" type="button" class="btn btn-primary">
                    <i class="ri-search-line me-1"></i>Cari
                </button>
            </div>
        </div>

        <div id="hasil_siswa" class="crud-list mt-3"></div>
        <div id="pagination_siswa" class="sdk-pager-wrap d-none">
            <div>
                <small id="pagination_siswa_info" class="text-muted"></small>
                <div class="mt-2" id="pagination_siswa_pages"></div>
            </div>
            <div class="sdk-page-size">
                <label for="pagination_siswa_limit" class="mb-0">Tampilkan</label>
                <select id="pagination_siswa_limit" class="form-select form-select-sm">
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

<div id="card_identitas_siswa" class="card d-none student-summary-card">
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="flex-grow-1 min-w-0" id="siswa_dipilih"></div>
            <button type="button" id="btn_ganti_siswa" class="btn btn-outline-primary flex-shrink-0">
                <i class="ri-user-search-line me-1"></i>Ganti Siswa
            </button>
        </div>
    </div>
</div>

<div id="area_transaksi" class="d-none">
    <div class="row g-3 align-items-start">
        <div class="col-xl-8">
            <div class="card" id="card_tagihan">
                <div class="card-header app-card-header">
                    <div>
                        <h4 class="header-title mb-1">2. Pilih Tagihan</h4>
                        <p class="text-muted mb-0">Klik card tagihan yang ingin dibayar. Pilihan langsung masuk ke Pembayaran Dipilih.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary" id="jumlah_tagihan">0 tagihan</span>
                </div>
                <div class="card-body">
                    <div class="quick-filter-wrap mb-3" id="quick_filter_tagihan">
                        <button type="button" class="btn btn-sm btn-primary quick-filter active" data-quick-filter="all">Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-filter" data-quick-filter="unpaid">Belum Bayar</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-filter" data-quick-filter="partial">Cicilan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-filter" data-quick-filter="overdue">Tunggakan</button>
                        <button type="button" id="btn_bayar_lebih_awal" class="btn btn-sm btn-outline-success ms-sm-auto">
                            <i class="ri-calendar-forward-line me-1"></i>Bayar Lebih Awal
                            <span class="badge bg-success-subtle text-success ms-1" id="jumlah_tagihan_mendatang">0</span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="collapse"
                            data-bs-target="#filter_lainnya"
                            aria-expanded="false"
                            aria-controls="filter_lainnya"
                        >
                            <i class="ri-filter-3-line me-1"></i>Filter Lainnya
                            <i class="ri-arrow-down-s-line ms-1"></i>
                        </button>
                    </div>

                    <div class="collapse" id="filter_lainnya">
                        <div class="advanced-filter-box mb-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label" for="filter_tahun">Tahun Ajaran</label>
                                    <select id="filter_tahun" class="form-select">
                                        <option value="">Semua Tahun</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label" for="filter_bulan">Bulan</label>
                                    <select id="filter_bulan" class="form-select">
                                        <option value="">Semua Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label" for="filter_tipe">Tipe</label>
                                    <select id="filter_tipe" class="form-select">
                                        <option value="">Semua Tipe</option>
                                        <option value="Bulanan">Bulanan</option>
                                        <option value="Langsung">Langsung</option>
                                        <option value="Tahunan">Tahunan</option>
                                    </select>
                                </div>
                                <div class="col-lg-5">
                                    <label class="form-label" for="filter_tagihan">Nama Tagihan</label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            id="filter_tagihan"
                                            class="form-control"
                                            placeholder="Cari nama atau nomor tagihan"
                                        >
                                        <button id="btn_reset_filter" class="btn btn-outline-secondary" type="button">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="daftar_tagihan"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card" id="keranjang-pembayaran">
                <div class="card-header app-card-header">
                    <div>
                        <h4 class="header-title mb-1">3. Pembayaran Dipilih</h4>
                        <p class="text-muted mb-0" id="ringkasan_pilihan">Belum ada tagihan dipilih.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div id="keranjang">
                        <div class="empty-state py-3">
                            <i class="ri-checkbox-multiple-line empty-icon"></i>
                            Klik card tagihan di sebelah kiri untuk mulai pembayaran.
                        </div>
                    </div>

                    <div class="payment-total-box mt-3">
                        <div>
                            <small class="text-muted d-block">Total Pembayaran</small>
                            <strong class="text-success fs-24" id="total_keranjang">Rp0</strong>
                        </div>
                        <span class="badge bg-light text-dark" id="jumlah_pilihan">0 tagihan</span>
                    </div>

                    <button type="button" id="btn_selesaikan_pembayaran" class="btn btn-primary btn-lg w-100 mt-3" disabled>
                        <i class="ri-secure-payment-line me-1"></i>Bayar Sekarang
                    </button>

                    <div class="text-center mt-2">
                        <button type="button" id="btn_kosongkan" class="btn btn-link btn-sm text-danger text-decoration-none" disabled>
                            Batalkan semua pilihan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_bayar_lebih_awal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1"><i class="ri-calendar-forward-line me-1 text-success"></i>Bayar Lebih Awal</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    Tagihan yang dipilih di sini tetap masuk ke <strong>Pembayaran Dipilih</strong> seperti tagihan biasa.
                    Untuk membayar SPP beberapa bulan atau sampai akhir tahun ajaran, cari SPP lalu gunakan <strong>Pilih Semua yang Tampil</strong>.
                </div>

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="future_filter_tahun">Tahun Ajaran</label>
                        <select id="future_filter_tahun" class="form-select">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="future_filter_tipe">Tipe</label>
                        <select id="future_filter_tipe" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="Bulanan">Bulanan</option>
                            <option value="Langsung">Langsung</option>
                            <option value="Tahunan">Tahunan</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="future_filter_tagihan">Nama Tagihan</label>
                        <input type="text" id="future_filter_tagihan" class="form-control" placeholder="Contoh: SPP">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
                    <small class="text-muted" id="future_filter_info">0 tagihan mendatang</small>
                    <button type="button" id="btn_pilih_semua_mendatang" class="btn btn-sm btn-outline-success">
                        <i class="ri-checkbox-multiple-line me-1"></i>Pilih Semua yang Tampil
                    </button>
                </div>

                <div id="daftar_tagihan_mendatang"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_checkout_pembayaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content checkout-modal-content">
            <div class="modal-header checkout-modal-header">
                <div>
                    <h5 class="modal-title mb-1">
                        <i class="ri-secure-payment-line me-1 text-primary"></i>Selesaikan Pembayaran
                    </h5>
                    <small class="text-muted" id="checkout_siswa">-</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <div class="checkout-total-box checkout-total-box-primary text-center mb-3">
                    <small class="text-muted d-block mb-1">TOTAL PEMBAYARAN</small>
                    <strong class="text-primary" id="checkout_total">Rp0</strong>
                    <div class="small text-muted mt-1" id="checkout_jumlah">0 tagihan</div>
                </div>

                <form id="form_pembayaran">
                    <input type="hidden" id="id_siswa">
                    <input type="hidden" id="token_pembayaran" value="<?= html_escape($token_pembayaran) ?>">

                    <div class="mb-3">
                        <label class="form-label" for="id_metode">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select id="id_metode" class="form-select" required>
                            <option value="">Pilih metode pembayaran</option>
                            <?php foreach ($metode as $row): ?>
                                <option value="<?= (int) $row['id'] ?>" data-tunai="<?= preg_match('/(?:^|[^a-z])(tunai|cash)(?:$|[^a-z])/i', (string)$row['nama_metode']) ? 'Ya' : 'Tidak' ?>">
                                    <?= html_escape($row['nama_metode']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="blok_tunai" class="d-none">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="uang_diterima">Uang Diterima</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off"
                                        id="uang_diterima" class="form-control money-input" value="" placeholder="Masukkan nominal">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="kembalian">Kembalian</label>
                                <div class="change-box" id="kembalian">Rp0</div>
                            </div>
                        </div>
                    </div>

                    <div id="blok_referensi" class="mb-3 d-none">
                        <label class="form-label" for="referensi">Referensi Pembayaran <span class="text-muted fw-normal">(opsional)</span></label>
                        <input id="referensi" class="form-control" placeholder="Contoh: nomor transfer / referensi QRIS">
                    </div>

                    <div class="payment-optional-box border rounded p-3 mb-3 bg-light-subtle">
                        <button
                            type="button"
                            class="btn btn-link p-0 text-decoration-none fw-semibold"
                            data-bs-toggle="collapse"
                            data-bs-target="#opsi_lainnya_pembayaran"
                            aria-expanded="false"
                            aria-controls="opsi_lainnya_pembayaran"
                        >
                            <i class="ri-add-circle-line me-1"></i>Opsi Lainnya
                        </button>

                        <div class="collapse mt-3" id="opsi_lainnya_pembayaran">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="tanggal_pembayaran">Tanggal Pembayaran</label>
                                    <input type="text" id="tanggal_pembayaran" class="form-control tanggal-picker"
                                        value="<?= date('d-m-Y') ?>" placeholder="dd-mm-yyyy" autocomplete="off" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Petugas</label>
                                    <div class="form-control bg-body-tertiary"><?= html_escape($app_admin_name) ?></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="catatan">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                                    <textarea id="catatan" class="form-control" rows="2" placeholder="Tambahkan catatan bila diperlukan"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-summary-box border rounded p-3 bg-body-tertiary">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <strong>Ringkasan Tagihan</strong>
                                <div class="small text-muted">Periksa pilihan bila diperlukan.</div>
                            </div>
                            <button
                                class="btn btn-sm btn-outline-secondary"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#checkout_daftar_wrap"
                                aria-expanded="false"
                                aria-controls="checkout_daftar_wrap"
                            >Lihat</button>
                        </div>
                        <div class="collapse mt-3" id="checkout_daftar_wrap">
                            <div id="checkout_daftar"></div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="form_pembayaran" id="btn_simpan" class="btn btn-primary" disabled>
                    <i class="ri-check-line me-1"></i>Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_berhasil" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-success"><i class="ri-checkbox-circle-line me-1"></i>Pembayaran Berhasil</h5>
            </div>
            <div class="modal-body">
                <div id="isi_berhasil"></div>
                <div class="success-action-buttons d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-center gap-2 mt-3 mb-2">
                    <a id="link_bukti" target="_blank" class="btn btn-primary">
                        <i class="ri-printer-line me-1"></i>Cetak Bukti
                    </a>
                    <button type="button" id="btn_whatsapp" class="btn btn-success" aria-controls="wa_inline_section" aria-expanded="false">
                        <i class="ri-whatsapp-line me-1"></i>Kirim WhatsApp
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Pilihan Lainnya
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a id="link_kartu" target="_blank" class="dropdown-item" href="#">
                                    <i class="ri-id-card-line me-2"></i>Cetak ke Kartu Pembayaran
                                </a>
                            </li>
                            <li>
                                <button type="button" id="btn_lihat_detail" class="dropdown-item">
                                    <i class="ri-eye-line me-2"></i>Lihat Detail Transaksi
                                </button>
                            </li>
                        </ul>
                    </div>

                    <button type="button" id="btn_transaksi_baru" class="btn btn-outline-dark">Tutup</button>
                </div>
                <div id="wa_inline_section" class="d-none border-top mt-3 pt-3" aria-hidden="true">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="mb-0">Kirim Bukti Pembayaran melalui WhatsApp</h5>
                        <button type="button" id="btn_tutup_wa_inline" class="btn btn-sm btn-outline-secondary">
                            <i class="ri-arrow-up-s-line me-1"></i>Sembunyikan
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Sumber Nomor WhatsApp</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check"><input class="form-check-input wa-source-payment" type="radio" name="wa_source_payment" id="wa_source_parent" value="Orang Tua" checked><label class="form-check-label" for="wa_source_parent">Orang Tua</label></div>
                            <div class="form-check"><input class="form-check-input wa-source-payment" type="radio" name="wa_source_payment" id="wa_source_other" value="Nomor Lain"><label class="form-check-label" for="wa_source_other">Nomor Lain</label></div>
                        </div>
                        <!-- <small class="text-muted">Data Orang Tua diambil dari Master Wali Siswa sebagai nilai awal dan dapat disesuaikan sebelum dikirim.</small> -->
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label" for="wa_nama">Nama Penerima / Wali</label><input id="wa_nama" class="form-control" maxlength="150"></div>
                        <div class="col-md-3"><label class="form-label" for="wa_hubungan">Hubungan</label><input id="wa_hubungan" class="form-control" maxlength="50"></div>
                        <div class="col-md-5" id="wa_parent_phone_wrap"><label class="form-label" for="wa_nomor">Nomor HP Orang Tua</label><input id="wa_nomor" class="form-control" inputmode="tel" maxlength="25"></div>
                        <div class="col-12 d-none" id="wa_other_phone_wrap"><label class="form-label" for="wa_nomor_lain">Nomor Lain</label><input id="wa_nomor_lain" class="form-control" inputmode="tel" maxlength="25" placeholder="Contoh: 081234567890"><small class="text-muted">Nomor ini hanya dipakai untuk pengiriman saat ini dan tidak disimpan ke Master Wali.</small></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                            <label class="form-label mb-0" for="wa_pesan">Pesan</label>
                            <button type="button" id="btn_muat_template_wa" class="btn btn-sm btn-outline-secondary">
                                <i class="ri-refresh-line me-1"></i>Muat Template
                            </button>
                        </div>
                        <textarea id="wa_pesan" class="form-control" rows="7" placeholder="Template default Bukti Pembayaran akan dimuat otomatis"></textarea>
                        <div id="wa_template_info" class="small text-primary mt-1"></div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <button type="button" id="btn_batal_wa_inline" class="btn btn-secondary">Batal</button>
                        <button type="button" id="btn_kirim_wa" class="btn btn-success"><i class="ri-whatsapp-line me-1"></i>Buka WhatsApp</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_detail_transaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="isi_detail_transaksi"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<style>
.min-w-0 { min-width: 0; }


/* Hasil pencarian siswa mengikuti pola list Almahbaro, tetapi tetap memakai warna SD Kreatif. */
#hasil_siswa.crud-list {
    border: 1px solid var(--ct-border-color);
    border-radius: var(--ct-border-radius-lg);
    overflow: hidden;
    max-height: 430px;
    overflow-y: auto;
    background: var(--ct-body-bg);
}

#hasil_siswa.crud-list:empty {
    display: none;
}

#hasil_siswa .crud-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .9rem 1rem;
    border-bottom: 1px solid var(--ct-border-color);
    background: var(--ct-body-bg);
    transition: background-color .15s ease;
}

#hasil_siswa .crud-list-item:last-child {
    border-bottom: 0;
}

#hasil_siswa .crud-list-item:hover {
    background: var(--ct-tertiary-bg);
}

#hasil_siswa .crud-content {
    flex: 1 1 auto;
    min-width: 0;
}

#hasil_siswa .crud-title {
    font-size: .96rem;
    font-weight: 700;
    color: var(--ct-emphasis-color);
    line-height: 1.35;
}

#hasil_siswa .crud-meta {
    color: var(--ct-secondary-color);
    font-size: .88rem;
    line-height: 1.5;
}

#hasil_siswa .crud-actions {
    flex: 0 0 auto;
}

#hasil_siswa .empty-state {
    padding: 1.4rem;
    text-align: center;
    color: var(--ct-secondary-color);
}

#hasil_siswa .empty-icon {
    display: block;
    margin: 0 auto .4rem;
    font-size: 1.55rem;
    color: var(--ct-primary);
}

.student-summary-card {
    border-left: 4px solid var(--ct-primary);
}

.student-summary-main {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.student-summary-name {
    min-width: 220px;
    flex: 1 1 280px;
}

.student-summary-meta {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.student-summary-meta > div {
    min-width: 110px;
}

.student-summary-outstanding {
    min-width: 170px;
}

.quick-filter-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
}

.advanced-filter-box {
    border: 1px solid var(--ct-border-color);
    border-radius: var(--ct-border-radius-lg);
    background: var(--ct-tertiary-bg);
    padding: 1rem;
}

.bill-section + .bill-section {
    margin-top: 1.5rem;
}

.bill-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: .75rem;
}

.bill-item {
    border: 1px solid var(--ct-border-color);
    cursor: pointer;
    border-radius: var(--ct-border-radius-lg);
    padding: .9rem 1rem;
    margin-bottom: .75rem;
    background: var(--ct-body-bg);
    transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
}

.bill-item.is-selected {
    border-color: var(--ct-primary);
    background: var(--ct-tertiary-bg);
}


.bill-main-value {
    min-width: 130px;
    text-align: right;
}

.bill-detail {
    border-top: 1px dashed var(--ct-border-color);
    margin-top: .85rem;
    padding-top: .85rem;
}

.bill-detail-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .65rem 1rem;
}

.bill-detail-grid small {
    display: block;
    color: var(--ct-secondary-color);
    margin-bottom: .1rem;
}

.cart-item {
    border: 1px solid var(--ct-border-color);
    border-radius: var(--ct-border-radius-lg);
    padding: .9rem;
    margin-bottom: .75rem;
}

.cart-mode-box {
    background: var(--ct-tertiary-bg);
    border-radius: var(--ct-border-radius);
    padding: .65rem .75rem;
    margin-top: .75rem;
}

.payment-total-box {
    border-top: 1px solid var(--ct-border-color);
    padding-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.checkout-total-box {
    border: 1px solid var(--ct-success);
    background: var(--ct-tertiary-bg);
    border-radius: var(--ct-border-radius-lg);
    padding: 1rem;
}

.checkout-total-box strong {
    font-size: 2rem;
    line-height: 1.1;
}

.checkout-bill-item {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: .65rem 0;
    border-bottom: 1px solid var(--ct-border-color);
}

.checkout-bill-item:last-child {
    border-bottom: 0;
}

.change-box {
    min-height: calc(1.5em + .9rem + 2px);
    display: flex;
    align-items: center;
    padding: .45rem .9rem;
    border-radius: var(--ct-border-radius);
    background: var(--ct-tertiary-bg);
    border: 1px solid var(--ct-border-color);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--ct-success);
}

.checkout-modal-content {
    border: 0;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 1rem 2rem rgba(47, 108, 229, .12);
}

.checkout-modal-header {
    background: linear-gradient(180deg, rgba(47,108,229,.08) 0%, rgba(47,108,229,.02) 100%);
    border-bottom: 1px solid rgba(47,108,229,.12);
}

.checkout-total-box-primary {
    border: 1px solid rgba(47,108,229,.35);
    background: linear-gradient(180deg, rgba(47,108,229,.08) 0%, rgba(47,108,229,.03) 100%);
}

.payment-optional-box {
    border-color: rgba(47,108,229,.15) !important;
    background: rgba(47,108,229,.04) !important;
}

.payment-summary-box {
    border-color: rgba(47,108,229,.12) !important;
    background: rgba(47,108,229,.03) !important;
}

#modal_checkout_pembayaran .modal-footer {
    border-top: 1px solid rgba(47,108,229,.1);
    padding-top: 1rem;
}

#modal_checkout_pembayaran .btn-primary {
    box-shadow: 0 .4rem .85rem rgba(47,108,229,.18);
}

/* Rapikan tombol pembayaran berhasil dalam body scrollable, tetap responsif. */
#modal_berhasil .success-action-buttons > * {
    margin: 0;
}

/* Modal Selesaikan Pembayaran: gaya sederhana Almahbaro,
   dengan aksen biru tema SD Kreatif. Hanya CSS visual modal ini. */
#modal_checkout_pembayaran .checkout-modal-content {
    border: 1px solid var(--ct-border-color);
    border-radius: 6px;
    background: var(--ct-card-bg, #fff);
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
}

#modal_checkout_pembayaran .checkout-modal-header {
    background: var(--ct-card-bg, #fff);
    border-bottom-color: var(--ct-border-color);
}

#modal_checkout_pembayaran .checkout-total-box-primary {
    border-color: var(--ct-primary, #1684dc);
    border-radius: 6px;
    background: var(--ct-tertiary-bg, #f5f7fa);
}

#modal_checkout_pembayaran .payment-optional-box {
    border-color: var(--ct-border-color) !important;
    border-radius: 6px !important;
    background: var(--ct-card-bg, #fff) !important;
}

#modal_checkout_pembayaran .payment-summary-box {
    border-color: var(--ct-border-color) !important;
    border-radius: 6px !important;
    background: var(--ct-tertiary-bg, #f5f7fa) !important;
}

#modal_checkout_pembayaran .modal-footer {
    background: var(--ct-card-bg, #fff);
    border-top-color: var(--ct-border-color);
}

#modal_checkout_pembayaran .btn-primary {
    box-shadow: none;
}

/* Samakan tinggi kotak Kembalian dengan input Uang Diterima. */
#modal_checkout_pembayaran #kembalian {
    font-size: .9rem;
    line-height: 1.5;
}

@media (min-width: 1200px) {
    #keranjang-pembayaran {
        position: sticky;
        top: 92px;
        max-height: calc(100vh - 110px);
        overflow: auto;
    }
}

@media (max-width: 767.98px) {

    #hasil_siswa .crud-list-item {
        align-items: flex-start;
        flex-direction: column;
    }

    #hasil_siswa .crud-actions,
    #hasil_siswa .crud-actions .btn {
        width: 100%;
    }
    .student-summary-main,
    .student-summary-meta {
        gap: .7rem;
    }

    .student-summary-meta {
        width: 100%;
    }

    .student-summary-meta > div,
    .student-summary-outstanding {
        flex: 1 1 45%;
        min-width: 0;
    }

    .bill-item .bill-top-row {
        align-items: flex-start !important;
    }

    .bill-main-value {
        width: 100%;
        text-align: left;
        margin-top: .5rem;
    }

    .bill-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .checkout-total-box strong {
        font-size: 1.7rem;
    }
}

@media (max-width: 575.98px) {
    .quick-filter-wrap .quick-filter {
        flex: 1 1 calc(50% - .5rem);
    }

    .quick-filter-wrap > [data-bs-toggle="collapse"] {
        width: 100%;
        margin-left: 0 !important;
    }

    .bill-section-title {
        align-items: flex-start;
        flex-direction: column;
    }

    .bill-detail-grid {
        grid-template-columns: 1fr;
    }

    .student-summary-meta > div,
    .student-summary-outstanding {
        flex-basis: 100%;
    }
}
</style>

<script>
// Helper lokal Pembayaran Tagihan.
// Pembayaran Almahbaro memakai helper global dari footer Almahbaro; template SD Kreatif
// tidak menyediakan helper tersebut, sehingga helper yang diperlukan ditempatkan lokal
// agar tidak mengubah template/global JS dan tidak memengaruhi modul lain.
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

function parseMoneyInput(value) {
    if (typeof value === 'number') return Number.isFinite(value) ? value : 0;

    var text = String(value == null ? '' : value)
        .replace(/Rp/gi, '')
        .replace(/\s/g, '')
        .trim();

    // Input Rupiah memakai titik sebagai pemisah ribuan, bukan desimal.
    // Nilai database diterima sebagai number dari data transaksi, bukan string input.
    var raw = text.replace(/[^0-9-]/g, '');
    return raw === '' || raw === '-' ? 0 : Number(raw);
}

function formatMoneyInput(value) {
    return new Intl.NumberFormat('id-ID').format(parseMoneyInput(value));
}

function setMoneyInputValue(target, value) {
    $(target).val(formatMoneyInput(value));
}

// Pertahankan posisi kursor pada input Bayar Sebagian saat format ribuan berubah.
function formatCartAmountWithCaret(input) {
    var before = String(input.value || '');
    var position = typeof input.selectionStart === 'number' ? input.selectionStart : before.length;
    var digitsBeforeCursor = before.slice(0, position).replace(/\D/g, '').length;
    var digits = before.replace(/\D/g, '');
    var formatted = digits === '' ? '' : new Intl.NumberFormat('id-ID').format(Number(digits));

    if (input.value !== formatted) input.value = formatted;

    if (typeof input.setSelectionRange === 'function') {
        var nextPosition = 0;
        var counted = 0;
        while (nextPosition < formatted.length && counted < digitsBeforeCursor) {
            if (/\d/.test(formatted.charAt(nextPosition))) counted++;
            nextPosition++;
        }
        if (position === before.length) nextPosition = formatted.length;
        input.setSelectionRange(nextPosition, nextPosition);
    }
}

// Menghapus titik ribuan harus tetap menghapus angka, bukan mengembalikan titiknya.
function handleCartAmountSeparatorDeletion(event) {
    if ((event.key !== 'Backspace' && event.key !== 'Delete') ||
        event.ctrlKey || event.altKey || event.metaKey ||
        this.selectionStart !== this.selectionEnd) return;

    var position = this.selectionStart;
    var value = String(this.value || '');
    var start = -1;
    var end = -1;

    if (event.key === 'Backspace' && position > 1 && value.charAt(position - 1) === '.') {
        start = position - 2;
        end = position;
    } else if (event.key === 'Delete' && value.charAt(position) === '.' && position + 1 < value.length) {
        start = position;
        end = position + 2;
    }

    if (start >= 0 && typeof this.setRangeText === 'function') {
        event.preventDefault();
        this.setRangeText('', start, end, 'start');
        this.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

// Format Uang Diterima tetap seperti sebelumnya. Nominal cicilan ditangani di #keranjang.
$(document).on('input', '.money-input:not([readonly]):not([disabled]):not(.cart-amount)', function () {
    var value = String(this.value || '').replace(/[^0-9]/g, '');
    this.value = value === '' ? '' : new Intl.NumberFormat('id-ID').format(Number(value));
});

var studentCache = {};
var studentSearchRequest = null;
var selectedStudent = null;
var billRows = [];
var futureBillRows = [];
var paymentCart = [];
var lastPaymentId = 0;
var lastPaymentNumber = '';
var currentAcademicPeriod = '';
var studentOutstandingTotal = 0;
var activeQuickFilter = 'all';
var paymentSuccessModal;
var earlyPaymentModal;
var checkoutPaymentModal;
var transactionDetailModal;
var waPesanEdited = false;
var serverNowAtLoad = <?= (int) round(microtime(true) * 1000) ?>;
var browserNowAtLoad = Date.now();

$(document).ready(function () {
    flatpickr('#tanggal_pembayaran', {
        dateFormat: 'd-m-Y',
        allowInput: true,
        disableMobile: true
    });

    paymentSuccessModal = new bootstrap.Modal(document.getElementById('modal_berhasil'), {backdrop: 'static', keyboard: false});
    earlyPaymentModal = new bootstrap.Modal(document.getElementById('modal_bayar_lebih_awal'));
    checkoutPaymentModal = new bootstrap.Modal(document.getElementById('modal_checkout_pembayaran'));
    transactionDetailModal = new bootstrap.Modal(document.getElementById('modal_detail_transaksi'));

    updateTransactionClock();
    setInterval(updateTransactionClock, 1000);

    $('#btn_cari_siswa').click(function () {
        searchStudent();
    });

    $('#cari_siswa').keyup(function (event) {
        if (event.key === 'Enter') {
            searchStudent();
        }
    });

    $('#hasil_siswa').on('click', '[data-student-id]', function () {
        selectStudent($(this).data('student-id'));
    });

    $('#pagination_siswa_limit').on('change', function () {
        searchStudent(1);
    });

    $('#btn_ganti_siswa').click(function () {
        changeStudent();
    });

    $('#quick_filter_tagihan').on('click', '.quick-filter', function () {
        activeQuickFilter = String($(this).data('quick-filter') || 'all');
        $('.quick-filter')
            .removeClass('btn-primary active')
            .addClass('btn-outline-secondary');
        $(this)
            .removeClass('btn-outline-secondary')
            .addClass('btn-primary active');
        drawBills();
    });

    $('#filter_tahun, #filter_bulan, #filter_tipe').change(function () {
        drawBills();
    });

    $('#filter_tagihan').on('input', function () {
        drawBills();
    });

    $('#btn_reset_filter').click(function () {
        $('#filter_tahun, #filter_bulan, #filter_tipe').val('');
        $('#filter_tagihan').val('');
        drawBills();
    });

    $('#daftar_tagihan').on('click', '.bill-item', function (event) {
        if ($(event.target).closest('[data-bill-detail]').length) return;
        toggleBillSelection(Number($(this).data('bill-id')));
    });

    $('#daftar_tagihan').on('keydown', '.bill-item', function (event) {
        if ($(event.target).closest('[data-bill-detail]').length) return;
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleBillSelection(Number($(this).data('bill-id')));
        }
    });

    $('#daftar_tagihan').on('click', '[data-bill-detail]', function (event) {
        event.stopPropagation();
        toggleBillDetail.call(this);
    });

    $('#btn_bayar_lebih_awal').click(function () {
        openEarlyPayment();
    });

    $('#future_filter_tahun, #future_filter_tipe').change(function () {
        drawFutureBills();
    });

    $('#future_filter_tagihan').on('input', function () {
        drawFutureBills();
    });

    $('#daftar_tagihan_mendatang').on('click', '.future-bill-item', function () {
        toggleFutureBillSelection(Number($(this).data('bill-id')));
    });

    $('#daftar_tagihan_mendatang').on('keydown', '.future-bill-item', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleFutureBillSelection(Number($(this).data('bill-id')));
        }
    });

    $('#btn_pilih_semua_mendatang').click(function () {
        selectAllVisibleFutureBills();
    });

    $('#keranjang').on('change', '.payment-mode', function () {
        updatePaymentMode.call(this);
    });

    $('#keranjang').on('keydown', '.cart-amount', handleCartAmountSeparatorDeletion);

    $('#keranjang').on('input', '.cart-amount', function () {
        formatCartAmountWithCaret(this);
        updateCartAmount.call(this);
    });

    $('#keranjang').on('click', '[data-remove-cart]', function () {
        removeCartItem.call(this);
    });

    $('#btn_kosongkan').click(function () {
        emptyCart();
    });

    $('#btn_selesaikan_pembayaran').click(function () {
        openCheckoutPayment();
    });

    $('#id_metode').change(function () {
        // Ganti metode: jangan membawa nominal tunai maupun nilai otomatis dari metode lain.
        $('#uang_diterima').val('');
        toggleCashFields();
    });

    $('#uang_diterima').on('input', function () {
        calculateCart();
    });

    $('#form_pembayaran').submit(function (event) {
        savePayment(event);
    });

    $('#btn_lihat_detail').click(function () {
        showTransactionDetail();
    });

    $('#btn_whatsapp').click(function () {
        openWhatsapp();
    });

    $('#btn_tutup_wa_inline, #btn_batal_wa_inline').click(function () {
        hideWhatsappInline();
    });

    $('#btn_kirim_wa').click(function () {
        sendWhatsapp();
    });

    $('#btn_muat_template_wa').click(function () {
        loadWhatsappTemplate(true);
    });

    $('#wa_pesan').on('input', function () {
        waPesanEdited = true;
    });
    $(document).on('change', '.wa-source-payment', togglePaymentWaSource);

    $('#btn_transaksi_baru').click(function () {
        location.reload();
    });

    drawBills();
    drawCart();
    $('#cari_siswa').focus();

    var presetStudent = new URLSearchParams(window.location.search).get('siswa');

    if (presetStudent) {
        $.ajax({
            url: '<?= base_url('admin/transaksi/pembayaran/siswa'); ?>',
            type: 'POST',
            data: { id: presetStudent },
            dataType: 'JSON',
            success: function (data) {
                if (data.result == 'true') {
                    studentCache[data.siswa.id] = data.siswa;
                    selectStudent(data.siswa.id);
                } else if (data.result == 'false') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            },
            error: function (xhr) {
                ajaxError(xhr);
            }
        });
    }
});

function getStudentAdministrativeStatus(status) {
    var normalized = $.trim(String(status || ''));
    if (normalized.toUpperCase() === 'AKTIF') return 'Aktif';
    if (normalized.toUpperCase() === 'NONAKTIF') return 'Nonaktif';
    var inactiveStatuses = ['Lulus', 'Pindah Sekolah', 'Berhenti', 'Nonaktif'];
    return inactiveStatuses.indexOf(normalized) !== -1 ? normalized : 'Aktif';
}

function getPaymentStatusLabel(status) {
    var normalized = $.trim(String(status || ''));
    if (normalized === 'Belum Dibayar') return 'Belum Bayar';
    if (normalized === 'Dibayar Sebagian') return 'Cicilan';
    if (normalized === 'Lunas') return 'Lunas';
    if (normalized === 'Dibebaskan') return 'Dibebaskan';
    return normalized || '-';
}

function getPaymentStatusTone(status) {
    if (status === 'Dibayar Sebagian') return 'warning';
    if (status === 'Lunas') return 'success';
    if (status === 'Dibebaskan') return 'info';
    return 'secondary';
}

function updateTransactionClock() {
    var now = new Date(serverNowAtLoad + (Date.now() - browserNowAtLoad));
    var dateText = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(now).replace(/\//g, '-');
    var timeText = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    }).format(now).replace(/\./g, ':');

    $('#tanggal_transaksi_info').text(dateText);
    $('#jam_transaksi_info').text(timeText);
}

function searchStudent(page) {
    var keyword = $.trim($('#cari_siswa').val());
    page = Math.max(1, parseInt(page, 10) || 1);

    if (keyword.length < 2) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Masukkan minimal 2 karakter pencarian.'
        });
        return;
    }

    if (studentSearchRequest && studentSearchRequest.readyState !== 4) {
        studentSearchRequest.abort();
    }

    var button = $('#btn_cari_siswa');
    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mencari');

    studentSearchRequest = $.ajax({
        url: '<?= base_url('admin/transaksi/pembayaran/cari_siswa'); ?>',
        type: 'POST',
        data: { q: keyword, page: page, limit: $('#pagination_siswa_limit').val() },
        dataType: 'JSON',
        success: function (response) {
            studentCache = {};
            var students = response.data || [];
            var table = '';

            if (students.length === 0) {
                table = `
                    <div class="empty-state">
                        <i class="ri-user-search-line empty-icon"></i>
                        Siswa tidak ditemukan.
                    </div>
                `;
            } else {
                students.forEach(function (item) {
                    studentCache[item.id] = item;
                    var studentStatus = getStudentAdministrativeStatus(item.status_siswa);
                    var statusTone = studentStatus === 'Aktif' ? 'success' : 'secondary';

                    table += `
                        <div class="crud-list-item">
                            <div class="crud-content">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <div class="crud-title mb-0">${escapeHtml(item.nama_lengkap)}</div>
                                    <span class="badge bg-${statusTone}-subtle text-${statusTone}">${escapeHtml(studentStatus)}</span>
                                </div>
                                <div class="crud-meta">
                                    NIS ${escapeHtml(item.nis || '-')} &nbsp;•&nbsp;
                                    NISN ${escapeHtml(item.nisn || '-')} &nbsp;•&nbsp;
                                    Kelas ${escapeHtml(item.nama_kelas || 'Belum ditempatkan')}
                                </div>
                            </div>
                            <div class="crud-actions">
                                <button type="button" class="btn btn-primary" data-student-id="${Number(item.id)}">
                                    <i class="ri-check-line me-1"></i>Pilih Siswa
                                </button>
                            </div>
                        </div>
                    `;
                });
            }

            $('#hasil_siswa').html(table);

            var total = Number(response.total || 0);
            var currentPage = Number(response.page || 1);
            var limit = Number(response.limit || 10);

            if (total > 0) {
                $('#pagination_siswa_info').text(total + ' siswa');
                $('#pagination_siswa').removeClass('d-none');
                SDKreatifUI.serverPager('#pagination_siswa_pages', {
                    total: total,
                    page: currentPage,
                    limit: limit
                }, function (paging) {
                    searchStudent(paging.currentPage);
                });
            } else {
                $('#pagination_siswa_info, #pagination_siswa_pages').empty();
                $('#pagination_siswa').addClass('d-none');
            }
        },
        error: function (xhr, status) {
            if (status !== 'abort') ajaxError(xhr);
        },
        complete: function (xhr) {
            if (studentSearchRequest === xhr) {
                studentSearchRequest = null;
                button.prop('disabled', false).html('<i class="ri-search-line me-1"></i>Cari');
            }
        }
    });
}

function selectStudent(id) {
    var nextStudent = studentCache[id];
    if (!nextStudent) return;

    if (selectedStudent && Number(selectedStudent.id) !== Number(id) && paymentCart.length) {
        Swal.fire({
            title: 'Ganti siswa?',
            text: 'Pilihan pembayaran siswa sebelumnya akan dikosongkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ganti Siswa',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) applySelectedStudent(nextStudent);
        });
        return;
    }

    applySelectedStudent(nextStudent);
}

function applySelectedStudent(student) {
    selectedStudent = student;
    paymentCart = [];
    billRows = [];
    futureBillRows = [];
    studentOutstandingTotal = 0;
    currentAcademicPeriod = student.periode || '';
    activeQuickFilter = 'all';

    if (studentSearchRequest && studentSearchRequest.readyState !== 4) studentSearchRequest.abort();
    $('#id_siswa').val(student.id);
    $('#hasil_siswa').empty();
    $('#pagination_siswa_info, #pagination_siswa_pages').empty();
    $('#pagination_siswa').addClass('d-none');
    $('#filter_tahun').html('<option value="">Semua Tahun</option>');
    $('#filter_bulan').val('');
    $('#filter_tipe').val('');
    $('#filter_tagihan').val('');
    $('#future_filter_tahun').html('<option value="">Semua Tahun</option>');
    $('#future_filter_tipe').val('');
    $('#future_filter_tagihan').val('');
    $('#id_metode').val('');
    $('#referensi').val('');
    $('#catatan').val('');
    $('#uang_diterima').val('');
    $('#blok_tunai, #blok_referensi').addClass('d-none');
    $('#opsi_lainnya_pembayaran, #checkout_daftar_wrap').removeClass('show');

    $('.quick-filter')
        .removeClass('btn-primary active')
        .addClass('btn-outline-secondary');
    $('.quick-filter[data-quick-filter="all"]')
        .removeClass('btn-outline-secondary')
        .addClass('btn-primary active');

    renderStudentSummary();
    $('#card_cari_siswa').addClass('d-none');
    $('#card_identitas_siswa').removeClass('d-none');
    $('#area_transaksi').removeClass('d-none');

    drawCart();
    loadBills();
}

function renderStudentSummary() {
    if (!selectedStudent) return;

    $('#siswa_dipilih').html(`
        <div class="student-summary-main">
            <div class="student-summary-name">
                <small class="text-muted d-block">Siswa</small>
                <strong class="fs-17 d-block text-truncate">${escapeHtml(selectedStudent.nama_lengkap)}</strong>
            </div>
            <div class="student-summary-meta">
                <div>
                    <small class="text-muted d-block">Kelas</small>
                    <strong>${escapeHtml(selectedStudent.nama_kelas || 'Belum ditempatkan')}</strong>
                </div>
                <div>
                    <small class="text-muted d-block">NIS</small>
                    <strong>${escapeHtml(selectedStudent.nis || '-')}</strong>
                </div>
                <div>
                    <small class="text-muted d-block">Tanggal</small>
                    <strong id="tanggal_transaksi_info">-</strong>
                </div>
                <div>
                    <small class="text-muted d-block">Jam</small>
                    <strong><span id="jam_transaksi_info">-</span> WIB</strong>
                </div>
            </div>
            <div class="student-summary-outstanding">
                <small class="text-muted d-block">Total Sisa Tagihan</small>
                <strong class="text-danger fs-17" id="total_sisa_siswa">${billRows.length ? formatRupiah(studentOutstandingTotal) : 'Memuat...'}</strong>
            </div>
        </div>
    `);
    updateTransactionClock();
}

function changeStudent() {
    if (paymentCart.length) {
        Swal.fire({
            title: 'Ganti siswa?',
            text: 'Semua tagihan yang sudah dipilih akan dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ganti Siswa',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) resetStudentSelection();
        });
    } else {
        resetStudentSelection();
    }
}

function resetStudentSelection() {
    selectedStudent = null;
    paymentCart = [];
    billRows = [];
    futureBillRows = [];
    currentAcademicPeriod = '';
    studentOutstandingTotal = 0;
    activeQuickFilter = 'all';

    $('#id_siswa').val('');
    $('#card_identitas_siswa').addClass('d-none');
    $('#siswa_dipilih').empty();
    $('#area_transaksi').addClass('d-none');
    $('#card_cari_siswa').removeClass('d-none');
    $('#cari_siswa').val('').focus();
    $('#hasil_siswa').empty();
    $('#pagination_siswa_info, #pagination_siswa_pages').empty();
    $('#pagination_siswa').addClass('d-none');

    $('#filter_tahun').html('<option value="">Semua Tahun</option>');
    $('#filter_bulan').val('');
    $('#filter_tipe').val('');
    $('#filter_tagihan').val('');
    $('#future_filter_tahun').html('<option value="">Semua Tahun</option>');
    $('#future_filter_tipe').val('');
    $('#future_filter_tagihan').val('');
    $('#jumlah_tagihan').text('0 tagihan');
    $('#jumlah_tagihan_mendatang').text('0');
    $('#id_metode').val('');
    $('#referensi, #catatan').val('');
    $('#uang_diterima').val('');
    $('#blok_tunai, #blok_referensi').addClass('d-none');

    drawBills();
    drawFutureBills();
    drawCart();
}

function loadBills() {
    if (!selectedStudent) return;

    $('#daftar_tagihan').html(`
        <div class="empty-state">
            <span class="spinner-border spinner-border-sm me-1"></span>
            Memuat tagihan...
        </div>
    `);

    $.ajax({
        url: '<?= base_url('admin/transaksi/pembayaran/tagihan_siswa'); ?>',
        type: 'POST',
        data: {
            id_siswa: selectedStudent.id,
            periode: '',
            tipe: '',
            status: '',
            search: ''
        },
        dataType: 'JSON',
        success: function (data) {
            if (data.result == 'true') {
                billRows = data.tagihan || [];
                futureBillRows = data.tagihan_mendatang || [];
                currentAcademicPeriod = data.periode_aktif || selectedStudent.periode || '';
                studentOutstandingTotal = Number(data.total_sisa_tagihan || 0);

                var normalYears = Array.from(new Set(billRows.map(function (item) {
                    return String(item.periode || '');
                }).filter(Boolean))).sort().reverse();
                var futureYears = Array.from(new Set(futureBillRows.map(function (item) {
                    return String(item.periode || '');
                }).filter(Boolean))).sort().reverse();

                var options = '<option value="">Semua Tahun</option>';
                normalYears.forEach(function (year) {
                    options += `<option value="${escapeHtml(year)}">${escapeHtml(year)}</option>`;
                });
                $('#filter_tahun').html(options);

                var futureOptions = '<option value="">Semua Tahun</option>';
                futureYears.forEach(function (year) {
                    futureOptions += `<option value="${escapeHtml(year)}">${escapeHtml(year)}</option>`;
                });
                $('#future_filter_tahun').html(futureOptions);
                $('#jumlah_tagihan_mendatang').text(futureBillRows.length);
                $('#total_sisa_siswa').text(formatRupiah(studentOutstandingTotal));
                drawBills();
                drawFutureBills();
            } else if (data.result == 'false') {
                billRows = [];
                futureBillRows = [];
                studentOutstandingTotal = 0;
                $('#jumlah_tagihan_mendatang').text('0');
                $('#total_sisa_siswa').text(formatRupiah(0));
                drawBills();
                drawFutureBills();
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        },
        error: function (xhr) {
            billRows = [];
            futureBillRows = [];
            studentOutstandingTotal = 0;
            $('#jumlah_tagihan_mendatang').text('0');
            $('#total_sisa_siswa').text(formatRupiah(0));
            drawBills();
            drawFutureBills();
            ajaxError(xhr);
        }
    });
}

function isPreviousBill(item) {
    return String(item.is_tunggakan || '') === 'Ya';
}

function academicMonthYear(period, month) {
    var selectedMonth = Number(month || 0);
    var match = String(period || '').match(/(\d{4})\D+(\d{4})/);

    if (!match || selectedMonth < 1 || selectedMonth > 12) return 0;

    return selectedMonth >= 7 ? Number(match[1]) : Number(match[2]);
}

function matchesMonthFilter(item, selectedMonth) {
    var month = Number(selectedMonth || 0);
    if (!month) return true;

    var itemMonth = Number(item.bulan || 0);
    var itemYear = Number(item.tahun || 0);
    var type = String(item.tipe_tagihan || '');

    if (type === 'Tahunan') {
        var targetYear = academicMonthYear(item.periode, month);
        if (!targetYear || !itemYear || !itemMonth) {
            return itemMonth === month;
        }

        return ((itemYear * 100) + itemMonth) <= ((targetYear * 100) + month);
    }

    return itemMonth === month;
}

function getFilteredBills() {
    var year = String($('#filter_tahun').val() || '');
    var month = String($('#filter_bulan').val() || '');
    var type = String($('#filter_tipe').val() || '');
    var search = $.trim(String($('#filter_tagihan').val() || '')).toLowerCase();

    return billRows.filter(function (item) {
        if (year !== '' && String(item.periode || '') !== year) return false;
        if (!matchesMonthFilter(item, month)) return false;
        if (type !== '' && String(item.tipe_tagihan || '') !== type) return false;

        if (search !== '') {
            var haystack = (
                String(item.nama_tagihan || '') + ' ' +
                String(item.nama_jenis_tagihan || '') + ' ' +
                String(item.no_tagihan || '')
            ).toLowerCase();
            if (haystack.indexOf(search) === -1) return false;
        }

        var category = String(item.kategori_pembayaran || '');
        if (activeQuickFilter === 'unpaid' && category !== 'unpaid') return false;
        if (activeQuickFilter === 'partial' && category !== 'partial') return false;
        if (activeQuickFilter === 'overdue' && category !== 'overdue') return false;

        return true;
    });
}

function drawBills() {
    var filteredRows = selectedStudent ? getFilteredBills() : [];
    var currentRows = [];
    var previousRows = [];

    filteredRows.forEach(function (item) {
        if (isPreviousBill(item)) previousRows.push(item);
        else currentRows.push(item);
    });

    $('#jumlah_tagihan').text(
        filteredRows.length + ' tagihan' +
        (billRows.length !== filteredRows.length ? ' dari ' + billRows.length : '')
    );

    if (!selectedStudent) {
        $('#daftar_tagihan').html('');
        return;
    }

    var html = '';

    if (activeQuickFilter !== 'overdue') {
        html += buildBillSection(
            'Tagihan Saat Ini',
            currentRows,
            'primary',
            'current',
            'Tidak ada tagihan bulan berjalan/cicilan yang sesuai filter.'
        );
    }

    if (activeQuickFilter === 'all' || activeQuickFilter === 'overdue' || previousRows.length) {
        html += buildBillSection(
            'Tunggakan',
            previousRows,
            'warning',
            'previous',
            activeQuickFilter === 'overdue'
                ? 'Tidak ada tagihan yang sudah melewati jatuh tempo dan dianggap tunggakan.'
                : 'Tidak ada tunggakan yang sesuai filter.'
        );
    }

    $('#daftar_tagihan').html(html);
}

function buildBillSection(title, rows, tone, key, emptyMessage) {
    var html = `
        <div class="bill-section" id="bill-section-${key}">
            <div class="bill-section-title">
                <h5 class="mb-0">${escapeHtml(title)}</h5>
                <span class="badge bg-${tone}-subtle text-${tone}">${rows.length} tagihan</span>
            </div>
    `;

    if (!rows.length) {
        html += `
            <div class="empty-state py-3">
                <i class="ri-file-search-line empty-icon"></i>
                ${escapeHtml(emptyMessage || 'Tidak ada data.')}
            </div>
        `;
    } else {
        rows.forEach(function (item) {
            var id = Number(item.id);
            var inCart = paymentCart.some(function (cartItem) { return cartItem.id === id; });
            var paymentLabel = getPaymentStatusLabel(item.status_pembayaran);
            var statusTone = getPaymentStatusTone(item.status_pembayaran);
            var periodText = $.trim((item.nama_bulan || '') + ' ' + (item.tahun || ''));
            var previous = isPreviousBill(item);
            var overdue = String(item.is_tunggakan || '') === 'Ya';
            var reduction = Number(item.nilai_keringanan || 0);
            var dueDate = item.tanggal_jatuh_tempo || '-';

            html += `
                <div class="bill-item ${inCart ? 'is-selected' : ''}" data-bill-id="${id}" role="button" tabindex="0" aria-pressed="${inCart ? 'true' : 'false'}">
                    <div class="d-flex gap-3 bill-top-row flex-wrap flex-md-nowrap">
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <strong class="d-block fs-15">${escapeHtml(item.nama_tagihan)}</strong>
                                        ${inCart ? '<span class="badge bg-primary-subtle text-primary"><i class="ri-check-line me-1"></i>Dipilih</span>' : ''}
                                    </div>
                                    ${periodText !== '' ? `<small class="text-muted d-block mt-1">${escapeHtml(periodText)}</small>` : ''}
                                    <small class="text-muted d-block mt-1">Jatuh tempo ${escapeHtml(dueDate)}</small>
                                    <div class="d-flex gap-1 flex-wrap mt-2">
                                        ${overdue
                                            ? '<span class="badge bg-warning-subtle text-warning">Tunggakan</span>'
                                            : (previous ? '<span class="badge bg-secondary-subtle text-secondary">Tagihan Sebelumnya</span>' : '')}
                                        <span class="badge bg-${statusTone}-subtle text-${statusTone}">${escapeHtml(paymentLabel)}</span>
                                        ${item.status_pembayaran === 'Dibayar Sebagian'
                                            ? `<span class="small text-muted align-self-center">Sudah dibayar ${formatRupiah(item.nominal_dibayar)}</span>`
                                            : ''}
                                    </div>
                                </div>

                                <div class="bill-main-value">
                                    <small class="text-muted d-block">Sisa Tagihan</small>
                                    <strong class="text-danger fs-16">${formatRupiah(item.sisa_tagihan)}</strong>
                                </div>
                            </div>

                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-link px-0 text-decoration-none" data-bill-detail="${id}">
                                    Detail <i class="ri-arrow-down-s-line"></i>
                                </button>
                            </div>

                            <div class="bill-detail d-none" id="bill_detail_${id}">
                                <div class="bill-detail-grid">
                                    <div><small>No. Tagihan</small><strong>${escapeHtml(item.no_tagihan || '-')}</strong></div>
                                    <div><small>Tipe</small><strong>${escapeHtml(item.tipe_tagihan || '-')}</strong></div>
                                    <div><small>Tahun Ajaran</small><strong>${escapeHtml(item.periode || '-')}</strong></div>
                                    <div><small>Jatuh Tempo</small><strong>${escapeHtml(dueDate)}</strong></div>
                                    <div><small>Nominal Awal</small><strong>${formatRupiah(item.nominal_awal)}</strong></div>
                                    <div><small>Potongan/Pembebasan</small><strong>${formatRupiah(reduction)}</strong></div>
                                    <div><small>Nominal Akhir</small><strong>${formatRupiah(item.nominal_tagihan)}</strong></div>
                                    <div><small>Sudah Dibayar</small><strong>${formatRupiah(item.nominal_dibayar)}</strong></div>
                                    <div><small>Sisa Tagihan</small><strong>${formatRupiah(item.sisa_tagihan)}</strong></div>
                                    <div><small>Status</small><strong>${escapeHtml(paymentLabel)}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    html += '</div>';
    return html;
}

function getFilteredFutureBills() {
    var year = String($('#future_filter_tahun').val() || '');
    var type = String($('#future_filter_tipe').val() || '');
    var search = $.trim(String($('#future_filter_tagihan').val() || '')).toLowerCase();

    return futureBillRows.filter(function (item) {
        if (year !== '' && String(item.periode || '') !== year) return false;
        if (type !== '' && String(item.tipe_tagihan || '') !== type) return false;

        if (search !== '') {
            var haystack = (
                String(item.nama_tagihan || '') + ' ' +
                String(item.nama_jenis_tagihan || '') + ' ' +
                String(item.no_tagihan || '')
            ).toLowerCase();
            if (haystack.indexOf(search) === -1) return false;
        }

        return true;
    });
}

function openEarlyPayment() {
    if (!selectedStudent) return;

    if (!futureBillRows.length) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Ada Tagihan Mendatang',
            text: 'Tidak ada tagihan setelah bulan berjalan yang masih dapat dibayar.'
        });
        return;
    }

    drawFutureBills();
    earlyPaymentModal.show();
}

function drawFutureBills() {
    var rows = selectedStudent ? getFilteredFutureBills() : [];
    $('#future_filter_info').text(rows.length + ' tagihan mendatang');
    $('#btn_pilih_semua_mendatang').prop('disabled', rows.length === 0);

    if (!selectedStudent || !rows.length) {
        $('#daftar_tagihan_mendatang').html(`
            <div class="empty-state py-3">
                <i class="ri-calendar-check-line empty-icon"></i>
                Tidak ada tagihan mendatang yang sesuai filter.
            </div>
        `);
        return;
    }

    var html = rows.map(function (item) {
        var id = Number(item.id);
        var inCart = paymentCart.some(function (cartItem) { return cartItem.id === id; });
        var periodText = $.trim((item.nama_bulan || '') + ' ' + (item.tahun || ''));
        var dueDate = item.tanggal_jatuh_tempo || '-';

        return `
            <div class="bill-item future-bill-item ${inCart ? 'is-selected' : ''}" data-bill-id="${id}" role="button" tabindex="0" aria-pressed="${inCart ? 'true' : 'false'}">
                <div class="d-flex justify-content-between gap-3 flex-wrap">
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong>${escapeHtml(item.nama_tagihan)}</strong>
                            ${inCart ? '<span class="badge bg-primary-subtle text-primary"><i class="ri-check-line me-1"></i>Dipilih</span>' : ''}
                        </div>
                        <small class="text-muted d-block mt-1">
                            ${escapeHtml(periodText || '-')} • ${escapeHtml(item.tipe_tagihan || '-')} • Jatuh tempo ${escapeHtml(dueDate)}
                        </small>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <small class="text-muted d-block">Sisa Tagihan</small>
                        <strong class="text-danger">${formatRupiah(item.sisa_tagihan)}</strong>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    $('#daftar_tagihan_mendatang').html(html);
}

function selectAllVisibleFutureBills() {
    var rows = getFilteredFutureBills();
    rows.forEach(function (row) {
        addBillToCart(row);
    });
    drawCart();
    drawBills();
    drawFutureBills();
}

function findBillById(id) {
    var row = billRows.find(function (item) { return Number(item.id) === Number(id); });
    if (row) return row;
    return futureBillRows.find(function (item) { return Number(item.id) === Number(id); }) || null;
}

function addBillToCart(row) {
    if (!row) return;
    var id = Number(row.id);
    if (paymentCart.some(function (item) { return item.id === id; })) return;

    paymentCart.push({
        id: id,
        name: row.nama_tagihan,
        period: $.trim((row.nama_bulan || '') + ' ' + (row.tahun || '')) + (row.periode ? ' | ' + row.periode : ''),
        balance: Number(row.sisa_tagihan),
        pay: Number(row.sisa_tagihan),
        mode: 'full'
    });
}

function toggleBillSelection(id) {
    var row = findBillById(id);
    if (!row) return;

    var index = paymentCart.findIndex(function (item) { return item.id === Number(id); });
    if (index >= 0) {
        paymentCart.splice(index, 1);
    } else {
        addBillToCart(row);
    }

    drawCart();
    drawBills();
    drawFutureBills();
}

function toggleFutureBillSelection(id) {
    toggleBillSelection(id);
}

function toggleBillDetail() {
    var id = Number($(this).data('bill-detail'));
    var target = $('#bill_detail_' + id);
    var willOpen = target.hasClass('d-none');
    target.toggleClass('d-none', !willOpen);
    $(this).html(willOpen
        ? 'Tutup Detail <i class="ri-arrow-up-s-line"></i>'
        : 'Detail <i class="ri-arrow-down-s-line"></i>'
    );
}

function drawCart() {
    if (!paymentCart.length) {
        $('#keranjang').html(`
            <div class="empty-state py-3">
                <i class="ri-checkbox-multiple-line empty-icon"></i>
                Klik card tagihan di sebelah kiri untuk mulai pembayaran.
            </div>
        `);
    } else {
        $('#keranjang').html(paymentCart.map(function (item, index) {
            var partial = item.mode === 'partial';
            var validationMessage = getCartValidationMessage(item);

            return `
                <div class="cart-item">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="min-w-0">
                            <strong class="d-block text-truncate">${escapeHtml(item.name)}</strong>
                            <small class="text-muted d-block">${escapeHtml(item.period || '-')}</small>
                            <small class="text-muted">Sisa ${formatRupiah(item.balance)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" data-remove-cart="${index}" title="Hapus pilihan">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>

                    <div class="cart-mode-box">
                        <div class="form-check mb-2">
                            <input class="form-check-input payment-mode" type="radio" name="payment_mode_${index}" id="payment_full_${index}" data-cart-index="${index}" value="full" ${!partial ? 'checked' : ''}>
                            <label class="form-check-label" for="payment_full_${index}">
                                <strong>Bayar Penuh</strong>
                                <small class="text-muted d-block">${formatRupiah(item.balance)}</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input payment-mode" type="radio" name="payment_mode_${index}" id="payment_partial_${index}" data-cart-index="${index}" value="partial" ${partial ? 'checked' : ''}>
                            <label class="form-check-label" for="payment_partial_${index}">
                                <strong>Bayar Sebagian</strong>
                                <small class="text-muted d-block">Untuk pembayaran cicilan.</small>
                            </label>
                        </div>

                        <div class="partial-payment-wrap mt-2 ${partial ? '' : 'd-none'}">
                            <label class="form-label mb-1" for="cart_amount_${index}">Nominal yang Dibayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    id="cart_amount_${index}"
                                    class="form-control money-input cart-amount ${validationMessage ? 'is-invalid' : ''}"
                                    data-cart-index="${index}"
                                    value="${partial && Number(item.pay) > 0 ? formatMoneyInput(item.pay) : ''}"
                                    placeholder="Masukkan nominal"
                                >
                            </div>
                            <div class="${validationMessage ? 'invalid-feedback d-block' : 'form-text'} cart-amount-feedback">
                                ${validationMessage ? escapeHtml(validationMessage) : 'Maksimal ' + formatRupiah(item.balance)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join(''));
    }

    calculateCart();
}

function updatePaymentMode() {
    var index = Number($(this).data('cart-index'));
    var mode = String($(this).val() || 'full');
    var item = paymentCart[index];
    if (!item) return;

    item.mode = mode;
    if (mode === 'full') {
        item.pay = Number(item.balance);
    } else if (Number(item.pay) >= Number(item.balance) || Number(item.pay) <= 0) {
        item.pay = 0;
    }

    drawCart();
    if (mode === 'partial') {
        setTimeout(function () { $('#cart_amount_' + index).focus(); }, 0);
    }
}

function updateCartAmount() {
    var index = Number($(this).data('cart-index'));
    var item = paymentCart[index];
    if (!item) return;

    item.pay = parseMoneyInput($(this).val());
    var message = getCartValidationMessage(item);
    $(this).toggleClass('is-invalid', message !== '');

    var feedback = $(this).closest('.partial-payment-wrap').find('.cart-amount-feedback');
    feedback
        .toggleClass('invalid-feedback d-block', message !== '')
        .toggleClass('form-text', message === '')
        .text(message || ('Maksimal ' + formatRupiah(item.balance)));

    calculateCart();
}

function getCartValidationMessage(item) {
    if (item.mode !== 'partial') return '';
    if (Number(item.pay) <= 0) return 'Nominal harus lebih dari Rp0.';
    if (Number(item.pay) > Number(item.balance)) return 'Nominal tidak boleh melebihi sisa ' + formatRupiah(item.balance) + '.';
    return '';
}

function isCartValid() {
    if (!paymentCart.length) return false;
    return paymentCart.every(function (item) {
        return Number(item.pay) > 0 && Number(item.pay) <= Number(item.balance);
    });
}

function removeCartItem() {
    var index = Number($(this).data('remove-cart'));
    paymentCart.splice(index, 1);
    drawCart();
    drawBills();
    drawFutureBills();
}

function emptyCart() {
    if (!paymentCart.length) return;

    Swal.fire({
        title: 'Batalkan semua pilihan?',
        text: 'Semua tagihan akan dikeluarkan dari Pembayaran Dipilih.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Kembali'
    }).then(function (result) {
        if (result.isConfirmed) {
            paymentCart = [];
            drawCart();
            drawBills();
            drawFutureBills();
        }
    });
}

function cartTotal() {
    return paymentCart.reduce(function (total, item) {
        return total + Number(item.pay || 0);
    }, 0);
}

function calculateCart() {
    var total = cartTotal();
    var received = parseMoneyInput($('#uang_diterima').val());

    $('#total_keranjang').text(formatRupiah(total));
    $('#jumlah_pilihan').text(paymentCart.length + ' tagihan');
    $('#ringkasan_pilihan').text(
        paymentCart.length
            ? paymentCart.length + ' tagihan dipilih.'
            : 'Belum ada tagihan dipilih.'
    );
    $('#checkout_total').text(formatRupiah(total));
    $('#checkout_jumlah').text(paymentCart.length + ' tagihan');
    $('#kembalian').text(formatRupiah(Math.max(0, received - total)));

    $('#btn_kosongkan').prop('disabled', !paymentCart.length);
    $('#btn_selesaikan_pembayaran').prop('disabled', !selectedStudent || !isCartValid());

    updateCheckoutButton();

    if ($('#modal_checkout_pembayaran').hasClass('show')) {
        renderCheckoutPayment();
    }
}

function updateCheckoutButton() {
    var methodSelected = $('#id_metode').val() !== '';
    var moneyReceived = parseMoneyInput($('#uang_diterima').val());
    var amountValid = moneyReceived >= cartTotal();

    $('#btn_simpan').prop('disabled', !selectedStudent || !isCartValid() || !methodSelected || !amountValid);
}

function openCheckoutPayment() {
    if (!selectedStudent || !paymentCart.length) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Belum ada tagihan yang dipilih.' });
        return;
    }

    if (!isCartValid()) {
        Swal.fire({
            icon: 'warning',
            title: 'Periksa Nominal Pembayaran',
            text: 'Nominal Bayar Sebagian harus lebih dari nol dan tidak boleh melebihi sisa tagihan.'
        });
        return;
    }

    renderCheckoutPayment();
    toggleCashFields();
    calculateCart();
    checkoutPaymentModal.show();
}

function renderCheckoutPayment() {
    $('#checkout_siswa').text(
        selectedStudent
            ? selectedStudent.nama_lengkap + ' • ' + (selectedStudent.nama_kelas || 'Belum ditempatkan')
            : '-'
    );

    $('#checkout_total').text(formatRupiah(cartTotal()));
    $('#checkout_jumlah').text(paymentCart.length + ' tagihan');

    var html = paymentCart.map(function (item) {
        return `
            <div class="checkout-bill-item">
                <div class="min-w-0">
                    <strong class="d-block text-truncate">${escapeHtml(item.name)}</strong>
                    <small class="text-muted">${escapeHtml(item.period || '-')}</small>
                </div>
                <strong class="text-success flex-shrink-0">${formatRupiah(item.pay)}</strong>
            </div>
        `;
    }).join('');

    $('#checkout_daftar').html(html || '<div class="empty-state py-2">Belum ada tagihan dipilih.</div>');
}

function toggleCashFields() {
    var methodSelected = $('#id_metode').val() !== '';
    var isTunai = methodSelected && String($('#id_metode option:selected').data('tunai')) === 'Ya';

    // Semua metode memerlukan input uang diterima; referensi tetap untuk non-tunai.
    $('#blok_tunai').toggleClass('d-none', !methodSelected);
    $('#blok_referensi').toggleClass('d-none', !methodSelected || isTunai);

    if (isTunai) {
        $('#referensi').val('');
    }

    calculateCart();
}

function savePayment(event) {
    event.preventDefault();

    if (!isCartValid()) {
        Swal.fire({
            icon: 'warning',
            title: 'Periksa Nominal Pembayaran',
            text: 'Nominal setiap tagihan harus lebih dari nol dan tidak boleh melebihi sisa tagihan.'
        });
        return;
    }

    if (!$('#id_metode').val()) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih metode pembayaran.' });
        return;
    }

    var total = cartTotal();
    var moneyReceived = parseMoneyInput($('#uang_diterima').val());

    if (moneyReceived < total) {
        Swal.fire({
            icon: 'warning',
            title: 'Uang Diterima Kurang',
            text: 'Uang diterima minimal sama dengan total pembayaran.'
        });
        return;
    }

    Swal.fire({
        title: 'Simpan transaksi pembayaran?',
        html: '<div class="text-start">' +
            '<div class="mb-1"><strong>' + escapeHtml(selectedStudent.nama_lengkap) + '</strong></div>' +
            '<div>Total: <strong class="text-success">' + formatRupiah(total) + '</strong></div>' +
            '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var button = $('#btn_simpan');
        var paymentSaved = false;
        var data = {
            token: $('#token_pembayaran').val(),
            id_siswa: selectedStudent.id,
            id_metode: $('#id_metode').val(),
            tanggal: $('#tanggal_pembayaran').val(),
            uang_diterima: moneyReceived,
            referensi: $('#referensi').val(),
            catatan: $('#catatan').val(),
            items: JSON.stringify(paymentCart.map(function (item) {
                return {
                    id_tagihan_siswa: item.id,
                    nominal_bayar: item.pay
                };
            }))
        };

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan');

        $.ajax({
            url: '<?= base_url('admin/transaksi/pembayaran/simpan'); ?>',
            type: 'POST',
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if (response.result == 'true') {
                    paymentSaved = true;
                    lastPaymentId = Number(response.id_pembayaran);
                    lastPaymentNumber = response.no_transaksi;

                    $('#isi_berhasil').html(`
                        <div class="text-center py-2">
                            <div class="avatar-lg rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3">
                                <i class="ri-check-line fs-30"></i>
                            </div>
                            <small class="text-muted d-block">No. Transaksi</small>
                            <h4 class="mb-3">${escapeHtml(response.no_transaksi)}</h4>
                            <div class="border rounded p-3 text-start">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-muted">Siswa</span>
                                    <strong class="text-end">${escapeHtml(selectedStudent.nama_lengkap)}</strong>
                                </div>
                                <div class="d-flex justify-content-between gap-3">
                                    <span class="text-muted">Total Dibayar</span>
                                    <strong class="text-success fs-17">${formatRupiah(response.total)}</strong>
                                </div>
                            </div>
                        </div>
                    `);

                    $('#wa_inline_section').addClass('d-none').attr('aria-hidden', 'true').removeData('initialized');
                    $('#btn_whatsapp').attr('aria-expanded', 'false');

                    $('#link_bukti').attr('href', '<?= base_url('admin/transaksi/pembayaran/bukti/'); ?>' + lastPaymentId);
                    $('#link_kartu').attr('href', '<?= base_url('admin/transaksi/pembayaran/cetak_kartu/'); ?>' + lastPaymentId);

                    checkoutPaymentModal.hide();
                    setTimeout(function () { paymentSuccessModal.show(); }, 200);
                } else if (response.result == 'false') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
                }
            },
            error: function (xhr) {
                ajaxError(xhr);
            },
            complete: function () {
                if (paymentSaved) {
                    button.prop('disabled', true).html('<i class="ri-check-line me-1"></i>Pembayaran Tersimpan');
                } else {
                    button.html('<i class="ri-check-line me-1"></i>Bayar Sekarang');
                    updateCheckoutButton();
                }
            }
        });
    });
}

// Perpindahan modal: tunggu modal berhasil benar-benar tertutup
// sebelum membuka modal berikutnya agar backdrop dan fokus tidak bertumpuk.
function showModalFromPaymentSuccess(targetModal, targetSelector) {
    var successElement = $('#modal_berhasil');
    var nextElement = $(targetSelector);

    var openNext = function () {
        // Saat modal kedua ditutup, ringkasan transaksi berhasil dapat digunakan lagi.
        nextElement.one('hidden.bs.modal', function () {
            paymentSuccessModal.show();
        });
        targetModal.show();
    };

    if (successElement.hasClass('show')) {
        successElement.one('hidden.bs.modal', openNext);
        paymentSuccessModal.hide();
    } else {
        targetModal.show();
    }
}

function showTransactionDetail() {
    if (!lastPaymentId) return;

    $.ajax({
        url: '<?= base_url('admin/transaksi/pembayaran/detail'); ?>',
        type: 'POST',
        data: { id: lastPaymentId },
        dataType: 'JSON',
        success: function (data) {
            if (data.result == 'true') {
                var header = data.header;
                var table = `
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><small class="text-muted">Nomor</small><div class="fw-semibold">${escapeHtml(header.no_transaksi)}</div></div>
                        <div class="col-md-6"><small class="text-muted">Status</small><div><span class="badge bg-success-subtle text-success">${escapeHtml(header.status_transaksi)}</span></div></div>
                        <div class="col-md-6"><small class="text-muted">Siswa</small><div>${escapeHtml(header.nama_siswa)}</div></div>
                        <div class="col-md-6"><small class="text-muted">Kelas</small><div>${escapeHtml(header.nama_kelas || '-')}</div></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead><tr><th>Tagihan</th><th class="text-end">Dibayar</th><th class="text-end">Sisa</th></tr></thead>
                            <tbody>
                `;

                data.detail.forEach(function (item) {
                    table += `
                        <tr>
                            <td>${escapeHtml(item.nama_tagihan)}</td>
                            <td class="text-end">${formatRupiah(item.nominal_bayar)}</td>
                            <td class="text-end">${formatRupiah(item.sisa_setelah)}</td>
                        </tr>
                    `;
                });

                table += `
                            </tbody>
                            <tfoot><tr><th>Total</th><th class="text-end">${formatRupiah(header.total_pembayaran)}</th><th></th></tr></tfoot>
                        </table>
                    </div>
                `;

                $('#isi_detail_transaksi').html(table);
                showModalFromPaymentSuccess(transactionDetailModal, '#modal_detail_transaksi');
            } else if (data.result == 'false') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        },
        error: function (xhr) {
            ajaxError(xhr);
        }
    });
}

function hideWhatsappInline() {
    $('#wa_inline_section').addClass('d-none').attr('aria-hidden', 'true');
    $('#btn_whatsapp').attr('aria-expanded', 'false');
    $('#modal_berhasil .modal-body').stop(true).animate({ scrollTop: 0 }, 200);
    paymentSuccessModal.handleUpdate();
}

function openWhatsapp() {
    if (!selectedStudent || !lastPaymentId) return;

    var panel = $('#wa_inline_section');
    if (!panel.hasClass('d-none')) {
        hideWhatsappInline();
        return;
    }

    // Muat data awal sekali saja agar isian yang telah diedit tidak hilang saat disembunyikan.
    if (!panel.data('initialized')) {
        $('#wa_source_parent').prop('checked', true);
        $('#wa_source_other').prop('checked', false);
        $('#wa_nama').val(selectedStudent.nama_wali || '');
        $('#wa_hubungan').val(selectedStudent.hubungan_wali || 'Wali');
        $('#wa_nomor').val(selectedStudent.no_telp_wali || '');
        $('#wa_nomor_lain').val('');
        togglePaymentWaSource();
        $('#wa_pesan').val('');
        $('#wa_template_info').text('Memuat template...');
        $('#wa_bukti_link').attr('href', '<?= base_url('admin/transaksi/pembayaran/bukti/'); ?>' + lastPaymentId);
        waPesanEdited = false;
        panel.data('initialized', true);
        loadWhatsappTemplate(true);
    }

    panel.removeClass('d-none').attr('aria-hidden', 'false');
    $('#btn_whatsapp').attr('aria-expanded', 'true');
    paymentSuccessModal.handleUpdate();

    // Gulir hanya isi modal berhasil, bukan halaman di belakang modal.
    var body = $('#modal_berhasil .modal-body');
    var top = body.scrollTop() + panel.offset().top - body.offset().top - 12;
    body.stop(true).animate({ scrollTop: Math.max(0, top) }, 250);
}

function loadWhatsappTemplate(force) {
    if (!lastPaymentId) return;
    if (!force && waPesanEdited) return;

    var button = $('#btn_muat_template_wa');
    button.prop('disabled', true);
    $('#wa_template_info').text('Memuat template...');

    $.ajax({
        url: '<?= base_url('admin/transaksi/pembayaran/preview_whatsapp'); ?>',
        type: 'POST',
        data: { id: lastPaymentId },
        dataType: 'JSON',
        success: function (data) {
            if (data.result == 'true') {
                $('#wa_pesan').val(data.pesan || '');
                $('#wa_template_info').text('');
                waPesanEdited = false;
            } else {
                $('#wa_template_info').text(data.message || 'Template tidak dapat dimuat.');
            }
        },
        error: function (xhr) {
            $('#wa_template_info').text('Template tidak dapat dimuat.');
            ajaxError(xhr);
        },
        complete: function () {
            button.prop('disabled', false);
        }
    });
}

function togglePaymentWaSource() {
    var source = $('input[name="wa_source_payment"]:checked').val() || 'Orang Tua';
    $('#wa_parent_phone_wrap').toggleClass('d-none', source !== 'Orang Tua');
    $('#wa_other_phone_wrap').toggleClass('d-none', source !== 'Nomor Lain');
}

function sendWhatsapp() {
    var source = $('input[name="wa_source_payment"]:checked').val() || 'Orang Tua';
    var phone = source === 'Nomor Lain' ? $.trim($('#wa_nomor_lain').val()) : $.trim($('#wa_nomor').val());
    if (!phone) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: source === 'Nomor Lain' ? 'Nomor lain wajib diisi.' : 'Nomor HP orang tua/wali wajib diisi.' });
        return;
    }
    if (!$.trim($('#wa_nama').val())) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Nama penerima/wali wajib diisi.' });
        return;
    }
    var button = $('#btn_kirim_wa');
    var data = {
        id: lastPaymentId,
        sumber_nomor: source,
        nama_penerima: $('#wa_nama').val(),
        hubungan_penerima: $('#wa_hubungan').val(),
        nomor_whatsapp: phone,
        pesan: waPesanEdited ? $('#wa_pesan').val() : ''
    };

    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan');

    $.ajax({
        url: '<?= base_url('admin/transaksi/pembayaran/siapkan_whatsapp'); ?>',
        type: 'POST',
        data: data,
        dataType: 'JSON',
        success: function (response) {
            if (response.result == 'true') {
                window.open(response.url, '_blank');
            } else if (response.result == 'false') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
            }
        },
        error: function (xhr) {
            ajaxError(xhr);
        },
        complete: function () {
            button.prop('disabled', false).html('<i class="ri-whatsapp-line me-1"></i>Buka WhatsApp');
        }
    });
}
</script>