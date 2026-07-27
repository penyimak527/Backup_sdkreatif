<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        /* ===== PRINT SETTING ===== */
        @page {
            size: A4;
            margin: 0.8cm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-page {
                display: grid;
                grid-template-columns: repeat(3, 5.4cm);
                grid-auto-rows: 8.5cm;
                gap: 0.4cm;
                justify-content: center;
            }

            .id-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        /* ===== GLOBAL ===== */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* ===== CARD ===== */
        .id-card {
            width: 5.4cm;
            height: 8.5cm;
            background: #ffffff;
            border-radius: 0.35cm;
            border: 0.04cm solid #cfdfee;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            color: #003a6b;
        }

        /* ===== HEADER ===== */
        .header {
            display: flex;
            align-items: center;
            gap: 0.25cm;
            padding-top: 0.35cm;
            padding-left: 0.35cm;
            padding-right: 0.35cm;
        }

        .logo {
            width: 0.9cm;
            height: 0.9cm;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company .name {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.2;
        }

        /* ===== PHOTO ===== */
        .photo {
            width: 2.2cm;
            height: 2.7cm;
            border: 0.04cm solid #3fa9f5;
            border-radius: 0.2cm;
            margin: 0.2cm auto 0.15cm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo img {
            width: 2cm;
            height: 2.5cm;
            object-fit: cover;
            border-radius: 0.15cm;
        }

        /* ===== INFO ===== */
        .info {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 0.2cm;
        }

        .info-inner {
            width: 100%;
            text-align: center;
            padding: 0.2cm 0.5cm;
        }

        .info h2 {
            font-size: 10px;
            margin: 0.1cm 0;
            text-transform: uppercase;
        }

        /* ===== QR ===== */
        .qr {

            margin-top: 0.25cm;
            display: flex;
            justify-content: center;
        }

        #qrcode {
            /* width: 1.8cm; */
            height: 1.8cm;
            padding: 0.1cm;
            border-radius: 0.1cm;
        }

        /* ===== BOTTOM DESIGN ===== */
        .bottom-shape {
            position: absolute;
            bottom: -0.9cm;
            /* sebelumnya -1.2cm */
            left: -0.6cm;
            /* sebelumnya -1cm */
            width: 7.2cm;
            /* sebelumnya 7cm */
            height: 1.5cm;
            /* sebelumnya 3cm */
            background: linear-gradient(135deg, #3fa9f5, #9fd9ff);
            transform: rotate(-5deg);
        }
    </style>
</head>

<body>
    <div class="print-page">
        <?php foreach ($pegawai as $p): ?>
            <div class="id-card">

                <!-- HEADER -->
                <div class="header">
                    <div class="logo">
                        <img src="<?= base_url('assets/sdkreative.png') ?>">
                    </div>
                    <div class="company">
                        <div class="name">SD Kreatif Muhammadiyah 1 Lumajang</div>
                    </div>
                </div>

                <!-- PHOTO -->
                <div class="photo">
                    <img src="<?= base_url('assets/user.png') ?>">
                </div>

                <!-- INFO -->
                <div class="info">
                    <div class="info-inner">
                        <h2><?= $p['nama_pegawai'] ?></h2>
                        <div class="qr">
                            <div class="qrcode" data-id="<?= $p['id'] ?>"></div>
                        </div>
                    </div>
                </div>

                <div class="bottom-shape"></div>
            </div>
        <?php endforeach; ?>
    </div>


</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.querySelectorAll('.qrcode').forEach(el => {
        new QRCode(el, {
            text: el.dataset.id,
            width: 80,
            height: 80,
            correctLevel: QRCode.CorrectLevel.H
        });
    });
    // window.onload = () => window.print();
    window.onload = function () {
        window.print();
    };

    window.onafterprint = function () {
        window.location.href = "<?= base_url('admin/pegawai/pegawai_jabatan'); ?>";
    };
</script>

</html>