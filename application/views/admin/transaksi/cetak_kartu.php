<?php
$formats = is_array($format_list ?? null) ? $format_list : array();
$defaultFormatId = !empty($format['id']) ? (int) $format['id'] : (!empty($formats[0]['id']) ? (int) $formats[0]['id'] : 0);
$payload = array();
foreach ($formats as $row) {
    $config = array();
    if (!empty($row['pengaturan_json'])) {
        $decoded = json_decode($row['pengaturan_json'], true);
        if (is_array($decoded)) {
            $config = $decoded;
        }
    }

    $payload[] = array(
        'id' => (int) $row['id'],
        'nama_format' => $row['nama_format'],
        'status_default' => $row['status_default'],
        'lebar_kertas' => is_numeric($row['lebar_kertas'] ?? null) ? (float) $row['lebar_kertas'] : (float)($config['lebar'] ?? 210),
        'tinggi_kertas' => is_numeric($row['tinggi_kertas'] ?? null) ? (float) $row['tinggi_kertas'] : (float)($config['tinggi'] ?? 148),
        'jumlah_baris' => max(1, (int) ($config['jumlah_baris'] ?? $config['jumlahBaris'] ?? 12)),
        'jarak_baris' => max(0, (float) ($config['jarak_baris'] ?? $config['jarak'] ?? 8)),
        'posisi_x' => max(0, (float) ($config['posisi_x'] ?? $config['x'] ?? 10)),
        'posisi_y' => max(0, (float) ($config['posisi_y'] ?? $config['y'] ?? 10)),
        'lebar_tanggal' => max(0, (float) ($config['lebar_tanggal'] ?? $config['wTanggal'] ?? 25)),
        'lebar_jenis' => max(0, (float) ($config['lebar_jenis'] ?? $config['wJenis'] ?? 70)),
        'lebar_nominal' => max(0, (float) ($config['lebar_nominal'] ?? $config['wNominal'] ?? 35)),
        'lebar_petugas' => max(0, (float) ($config['lebar_petugas'] ?? $config['wPetugas'] ?? 20)),
        'kolom' => !empty($config['kolom']) && is_array($config['kolom'])
            ? array_values($config['kolom'])
            : array('Tanggal', 'Jenis/Bulan', 'Nominal', 'Petugas')
    );
}

$namaTagihan = array();
foreach ($detail as $d) {
    $nama = trim((string) ($d['nama_tagihan'] ?? ''));
    if ($nama !== '' && !in_array($nama, $namaTagihan, true)) {
        $namaTagihan[] = $nama;
    }
}
$jenisBulan = implode(' + ', $namaTagihan);

$inisial = '';
foreach (preg_split('/\s+/', trim((string) $header['nama_user'])) as $piece) {
    if ($piece !== '') {
        $inisial .= strtoupper(substr($piece, 0, 1));
    }
    if (strlen($inisial) >= 3) {
        break;
    }
}
if ($inisial === '') {
    $inisial = '-';
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Cetak Kartu <?= html_escape($header['no_transaksi']) ?></title>
    <script src="<?= base_url('assets/js/vendor.min.js') ?>"></script>
    <script>
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.ajax !== 'function') {
            document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
        }
    </script>
    <style id="pageStyle">
        @page {
            size: 210mm 148mm;
            margin: 0;
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #111;
            background: #f5f6f8;
        }

        .wrap {
            max-width: 1040px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .panel {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 16px;
        }

        .title {
            margin: 0 0 4px;
            font-size: 20px;
        }

        .muted {
            color: #666;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        label {
            display: block;
            font-weight: 600;
        }

        select,
        input {
            width: 100%;
            margin-top: 6px;
            padding: 9px 10px;
            border: 1px solid #cfd3d8;
            border-radius: 5px;
            background: #fff;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .btn {
            border: 0;
            border-radius: 5px;
            padding: 9px 14px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-primary {
            background: #1677ff;
            color: #fff;
        }

        .btn-secondary {
            background: #e9ecef;
            color: #222;
        }

        .btn-warning {
            background: #f59f00;
            color: #fff;
        }

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 5px;
            margin-top: 12px;
            display: none;
        }

        .alert-danger {
            display: block;
            background: #fdecec;
            color: #a61e1e;
            border: 1px solid #f5c2c7;
        }

        .preview-title {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .preview-text {
            font-family: "Courier New", monospace;
            color: #222;
            font-size: 14px;
            white-space: nowrap;
            overflow-x: auto;
        }

        .print-only {
            display: none;
        }

        .paper-wrap {
            overflow: visible;
            padding: 0;
            background: #fff;
        }

        .card-paper {
            position: relative;
            background: #fff;
            border: 1px dashed #777;
            margin: auto;
            overflow: hidden;
        }

        .print-line {
            position: absolute;
            display: flex;
            align-items: center;
            white-space: nowrap;
            line-height: 1.15;
            font-family: "Courier New", monospace;
            font-size: 12px;
        }

        .print-cell {
            overflow: hidden;
            white-space: nowrap;
        }

        .baris-cell {
            width: 8mm;
            font-weight: 700;
        }

        .separator {
            width: 4mm;
            text-align: center;
            flex: 0 0 4mm;
        }

        .empty {
            padding: 16px;
            text-align: center;
            color: #777;
        }

        @media (max-width: 800px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .wrap {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .print-only {
                display: block !important;
            }

            .paper-wrap {
                padding: 0;
                background: #fff;
                overflow: visible;
            }

            .card-paper {
                margin: 0;
                border: 0;
            }

            .print-line {
                font-family: "Courier New", monospace;
                color: #000;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="panel no-print">
            <h1 class="title">Cetak ke Kartu Pembayaran</h1>
            <div class="muted"><?= html_escape($header['no_transaksi'] . ' - ' . $header['nama_siswa']) ?></div>

            <?php if (!$formats): ?>
                <div class="alert alert-danger">Belum ada Format Kartu Pembayaran yang aktif. Atur terlebih dahulu pada Pengaturan → Format Kartu.</div>
            <?php else: ?>
                <div class="form-grid">
                    <label>Format Kartu
                        <select id="format">
                            <?php foreach ($formats as $row): ?>
                                <option value="<?= (int) $row['id'] ?>" <?= (int) $row['id'] === $defaultFormatId ? 'selected' : '' ?>>
                                    <?= html_escape($row['nama_format']) ?><?= ($row['status_default'] ?? '') === 'Ya' ? ' (Default)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Baris Cetak
                        <select id="baris"></select>
                    </label>
                    <label>Posisi X (mm)
                        <input id="x" type="number" min="0" step="0.1">
                    </label>
                    <label>Posisi Y (mm)
                        <input id="y" type="number" min="0" step="0.1">
                    </label>
                </div>


                <div class="actions">
                    <button type="button" class="btn btn-secondary" id="btnTest">Uji Cetak</button>
                    <button type="button" class="btn btn-primary" id="btnPrint">Cetak</button>
                    <button type="button" class="btn btn-warning" id="btnMark">Tandai Baris Sudah Digunakan</button>
                </div>

            <?php endif; ?>
        </div>

        <div class="panel no-print" style="padding-bottom:12px">
            <div class="preview-title">Preview Teks</div>
            <div id="previewText" class="preview-text"></div>
        </div>

        <div class="print-only">
            <div class="paper-wrap">
                <div id="paper" class="card-paper">
                    <div id="printLine" class="print-line"></div>
                </div>
            </div>
        </div>
    </div>


    <script>
        var formatRows = <?= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var paymentId = <?= (int) $header['id'] ?>;
        var values = {
            'Tanggal': <?= json_encode($header['tanggal_transaksi'], JSON_UNESCAPED_UNICODE) ?>,
            'Jenis/Bulan': <?= json_encode($jenisBulan, JSON_UNESCAPED_UNICODE) ?>,
            'Nominal': <?= json_encode(number_format((float) $header['total_pembayaran'], 0, ',', '.'), JSON_UNESCAPED_UNICODE) ?>,
            'Petugas': <?= json_encode($inisial, JSON_UNESCAPED_UNICODE) ?>
        };

        function currentFormat() {
            var id = Number($('#format').val() || 0);
            return formatRows.find(function(row) {
                return Number(row.id) === id;
            }) || null;
        }

        function resetRows() {
            var f = currentFormat();
            if (!f) return;

            var current = Math.max(1, Number($('#baris').val() || 1));
            var html = '';
            for (var i = 1; i <= Number(f.jumlah_baris || 1); i++) {
                html += '<option value="' + i + '">Baris ' + String(i).padStart(2, '0') + '</option>';
            }
            $('#baris').html(html).val(Math.min(current, Number(f.jumlah_baris || 1)));
        }

        function setDefaultPosition() {
            var f = currentFormat();
            if (!f) return;

            var row = Math.max(1, Number($('#baris').val() || 1));
            $('#x').val(Number(f.posisi_x || 0).toFixed(1));
            $('#y').val((Number(f.posisi_y || 0) + ((row - 1) * Number(f.jarak_baris || 0))).toFixed(1));
        }

        function escapeText(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function printTextParts(f, row) {
            var columns = Array.isArray(f.kolom) && f.kolom.length ?
                f.kolom :
                ['Tanggal', 'Jenis/Bulan', 'Nominal', 'Petugas'];

            var parts = [String(row).padStart(2, '0')];
            columns.forEach(function(column) {
                parts.push(values[column] || '');
            });
            return parts;
        }

        function renderPreview() {
            var f = currentFormat();
            if (!f) {
                $('#previewText').text('Format kartu belum tersedia.');
                $('#paper').css({
                    width: '210mm',
                    height: '148mm'
                });
                $('#printLine').empty();
                return;
            }

            var pageWidth = Number(f.lebar_kertas || 210);
            var pageHeight = Number(f.tinggi_kertas || 148);
            var row = Math.max(1, Number($('#baris').val() || 1));
            var x = Math.max(0, Number($('#x').val() || 0));
            var y = Math.max(0, Number($('#y').val() || 0));
            var columns = Array.isArray(f.kolom) && f.kolom.length ?
                f.kolom :
                ['Tanggal', 'Jenis/Bulan', 'Nominal', 'Petugas'];
            var widths = {
                'Tanggal': Number(f.lebar_tanggal || 0),
                'Jenis/Bulan': Number(f.lebar_jenis || 0),
                'Nominal': Number(f.lebar_nominal || 0),
                'Petugas': Number(f.lebar_petugas || 0)
            };

            $('#pageStyle').text('@page { size: ' + pageWidth + 'mm ' + pageHeight + 'mm; margin: 0; }');
            $('#paper').css({
                width: pageWidth + 'mm',
                height: pageHeight + 'mm'
            });
            $('#printLine').css({
                left: x + 'mm',
                top: y + 'mm'
            });

            var html = '<div class="print-cell baris-cell">' + String(row).padStart(2, '0') + '</div>';
            columns.forEach(function(column) {
                var width = Math.max(0, Number(widths[column] || 0));
                html += '<div class="separator">|</div>';
                html += '<div class="print-cell" style="width:' + width + 'mm">' + escapeText(values[column] || '') + '</div>';
            });

            $('#printLine').html(html);
            $('#previewText').text(printTextParts(f, row).join(' | '));
        }

        function checkRow(callback) {
            var f = currentFormat();
            if (!f) {
                if (typeof callback === 'function') {
                    callback({
                        result: 'false',
                        message: 'Format kartu belum tersedia.'
                    });
                }
                return;
            }

            $.ajax({
                url: '<?= base_url('admin/transaksi/pembayaran/cek_baris_kartu') ?>',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: paymentId,
                    id_format: f.id,
                    nomor_baris: $('#baris').val()
                },
                success: function(response) {
                    if (response.result !== 'true') {
                        alert(response.message || 'Gagal memeriksa penggunaan baris.');
                        return;
                    }

                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function(xhr) {
                    var message = 'Gagal memeriksa penggunaan baris.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert(message);
                }
            });
        }

        function recordCard(action, callback) {
            var f = currentFormat();
            if (!f) return;

            $('#btnPrint, #btnMark').prop('disabled', true);

            $.ajax({
                url: '<?= base_url('admin/transaksi/pembayaran/catat_cetak_kartu') ?>',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    id: paymentId,
                    id_format: f.id,
                    nomor_baris: $('#baris').val(),
                    posisi_x: $('#x').val(),
                    posisi_y: $('#y').val(),
                    aksi: action
                },
                success: function(response) {
                    $('#btnPrint, #btnMark').prop('disabled', false);

                    if (response.result !== 'true') {
                        alert(response.message || 'Gagal mencatat penggunaan kartu.');
                        return;
                    }

                    alert(response.message || 'Berhasil dicatat.');

                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function(xhr) {
                    $('#btnPrint, #btnMark').prop('disabled', false);
                    var message = 'Gagal mencatat penggunaan kartu.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert(message);
                }
            });
        }

        function testPrint() {
            renderPreview();
            window.print();
        }

        function actualPrint() {
            renderPreview();

            checkRow(function(status) {
                if (status.pernah_digunakan === 'Ya') {
                    if (!window.confirm('Baris ' + String(Number($('#baris').val())).padStart(2, '0') + ' sudah pernah digunakan. Apakah tetap ingin melakukan cetak ulang?')) {
                        return;
                    }
                }

                // window.print() bersifat blocking pada browser desktop. Setelah dialog cetak ditutup,
                // pencatatan dilakukan langsung agar tidak bergantung pada event afterprint.
                window.print();
                recordCard('cetak');
            });
        }

        $(function() {
            if (typeof $.ajax !== 'function') {
                $('#btnPrint, #btnMark').prop('disabled', true);
                alert('jQuery AJAX tidak tersedia. Pastikan jQuery full berhasil dimuat.');
                renderPreview();
                return;
            }

            if (!formatRows.length) {
                renderPreview();
                return;
            }

            $('#format').on('change', function() {
                resetRows();
                setDefaultPosition();
                renderPreview();
            });

            $('#baris').on('change', function() {
                setDefaultPosition();
                renderPreview();
            });

            $('#x,#y').on('input', renderPreview);

            $('#btnTest').on('click', function() {
                testPrint();
            });

            $('#btnPrint').on('click', function() {
                actualPrint();
            });

            $('#btnMark').on('click', function() {
                checkRow(function(status) {
                    var rowLabel = String(Number($('#baris').val())).padStart(2, '0');

                    if (status.pernah_digunakan === 'Ya') {
                        alert('Baris ' + rowLabel + ' sudah pernah digunakan.');
                        return;
                    }

                    if (window.confirm('Tandai Baris ' + rowLabel + ' sebagai sudah digunakan tanpa mencetak?')) {
                        recordCard('tandai');
                    }
                });
            });

            resetRows();
            setDefaultPosition();
            renderPreview();
        });
    </script>
</body>

</html>