<?php defined('BASEPATH') or exit('No direct script access allowed'); $s = $setting ?: []; ?>

<style>
#form .select2-container--default .select2-selection--multiple .select2-selection__choice,
#form .select2-container--default .select2-selection--multiple .select2-selection__choice__display,
#form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #111 !important;
}
</style>

<div class="alert alert-info">
    Pengaturan ini menjadi nilai bawaan seluruh wallet siswa. Pengaturan individual pada siswa tetap dapat menggantikan limit global.
</div>

<div class="card">
    <div class="card-body">
        <form id="form">
            <input type="hidden" name="web_csrf_token" value="<?= html_escape($csrf_token) ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Minimal Isi Saldo</label>
                    <div class="input-group"><span class="input-group-text">Rp</span><input name="minimal_isi_saldo" type="text" inputmode="numeric" class="form-control rupiah-input" value="<?= (int) ($s['minimal_isi_saldo'] ?? 0) ?>"></div>
                    <small class="text-muted">Nominal pokok paling kecil yang dapat dimasukkan.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Maksimal Isi Saldo</label>
                    <div class="input-group"><span class="input-group-text">Rp</span><input name="maksimal_isi_saldo" type="text" inputmode="numeric" class="form-control rupiah-input" value="<?= (int) ($s['maksimal_isi_saldo'] ?? 0) ?>"></div>
                    <small class="text-muted">Batas nominal pokok untuk satu proses isi saldo.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Layanan</label>
                    <select name="status_layanan" class="form-select">
                        <option value="AKTIF" <?= ($s['status_layanan'] ?? '') === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                        <option value="NONAKTIF" <?= ($s['status_layanan'] ?? '') === 'NONAKTIF' ? 'selected' : '' ?>>NONAKTIF</option>
                    </select>
                    <small class="text-muted">NONAKTIF menghentikan layanan Dompet secara global tanpa menghapus saldo.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Batas Harian Global</label>
                    <div class="input-group"><span class="input-group-text">Rp</span><input name="batas_harian_default" type="text" inputmode="numeric" class="form-control rupiah-input" value="<?= (int) ($s['batas_harian_default'] ?? 0) ?>"></div>
                    <small class="text-muted">Total maksimum transaksi siswa dalam satu hari bila tidak mempunyai limit individual.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Batas per Transaksi Global</label>
                    <div class="input-group"><span class="input-group-text">Rp</span><input name="batas_transaksi_default" type="text" inputmode="numeric" class="form-control rupiah-input" value="<?= (int) ($s['batas_transaksi_default'] ?? 0) ?>"></div>
                    <small class="text-muted">Nominal maksimum untuk satu transaksi dan tidak boleh melebihi batas harian.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Batas Pembatalan</label>
                    <div class="input-group"><input name="batas_waktu_pembatalan_menit" type="number" min="1" class="form-control" value="<?= (int) ($s['batas_waktu_pembatalan_menit'] ?? 60) ?>"><span class="input-group-text">menit</span></div>
                    <small class="text-muted">Jangka waktu pengajuan pembatalan setelah transaksi.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nominal Cepat</label>
                    <select name="nominal_cepat[]" class="form-select js-nominal-cepat" multiple>
                        <?php foreach ($nominal_options as $nominal): ?>
                            <option value="<?= (int) $nominal ?>" <?= in_array((int) $nominal, $nominal_selected, true) ? 'selected' : '' ?>>Rp <?= number_format((int) $nominal, 0, ',', '.') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Pilih beberapa nominal yang akan ditawarkan sebagai tombol pilihan cepat.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input name="nomor_whatsapp" class="form-control" inputmode="tel" value="<?= html_escape($s['nomor_whatsapp'] ?? '') ?>">
                    <small class="text-muted">Nomor layanan yang ditampilkan kepada wali.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kebijakan Persetujuan Pembatalan</label>
                    <select name="kebijakan_persetujuan_pembatalan" class="form-select">
                        <option value="PERSETUJUAN" <?= ($s['kebijakan_persetujuan_pembatalan'] ?? '') === 'PERSETUJUAN' ? 'selected' : '' ?>>PERSETUJUAN</option>
                        <option value="OTOMATIS" <?= ($s['kebijakan_persetujuan_pembatalan'] ?? '') === 'OTOMATIS' ? 'selected' : '' ?>>OTOMATIS</option>
                    </select>
                    <small class="text-muted">PERSETUJUAN membutuhkan tindakan petugas; OTOMATIS mengikuti aturan sistem yang tersedia.</small>
                </div>
            </div>
            <button type="button" id="save" class="btn btn-primary mt-3">Simpan Pengaturan</button>
        </form>
    </div>
</div>

<script>
$(function () {
    $('.js-nominal-cepat').select2({width: '100%', placeholder: 'Pilih nominal cepat', closeOnSelect: false});
    $('#save').on('click', function () {
        $.post(<?= json_encode(base_url('admin/dompet/pengaturan_dompet/simpan'), JSON_UNESCAPED_SLASHES) ?>, $('#form').serialize(), null, 'json')
            .done(function (response) { Swal.fire('Berhasil', response.message, 'success'); })
            .fail(function (xhr) { Swal.fire('Gagal', (xhr.responseJSON || {}).message || 'Terjadi kesalahan.', 'error'); });
    });
});
</script>
