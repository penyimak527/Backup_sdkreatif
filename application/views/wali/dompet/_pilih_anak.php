<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($anak_list)): ?>
    <form method="get" class="d-flex flex-column flex-md-row align-items-md-end gap-2 mb-3">
        <div class="flex-grow-1">
            <label class="form-label fw-semibold" for="pilih-anak">Pilih anak</label>
            <select class="form-select" id="pilih-anak" name="id_siswa" onchange="this.form.submit()">
                <?php foreach ($anak_list as $item): ?>
                    <option value="<?= (int) $item['id_siswa'] ?>" <?= $anak && (int) $anak['id_siswa'] === (int) $item['id_siswa'] ? 'selected' : '' ?>>
                        <?= html_escape($item['nama_siswa']) ?> (<?= html_escape($item['nis']) ?>)<?= $item['nama_kelas'] ? ' - '.html_escape($item['nama_kelas']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <noscript><button class="btn btn-primary" type="submit">Tampilkan</button></noscript>
    </form>
<?php endif; ?>
