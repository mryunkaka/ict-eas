<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Permohonan E-Mail - FMR-ICT-05</title>
<style>
    @page {
        size: A4;
        margin: 12mm 12mm 12mm 12mm;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.5pt;
        color: #000;
        line-height: 1.3;
    }

    /* ===================== OUTER WRAPPER ===================== */
    .outer-border {
        border: 1.5pt solid #000;
        width: 100%;
    }

    /* ===================== HEADER ===================== */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 1.5pt solid #000;
    }

    .header-logo-cell {
        width: 28mm;
        border-right: 1.5pt solid #000;
        padding: 3mm 2mm;
        text-align: center;
        vertical-align: middle;
    }

    .logo-img {
    width: 90px;
    height: auto;
}

    .logo-box {
        display: inline-block;
        text-align: center;
    }

    .logo-shield {
        width: 40px;
        height: 46px;
        background-color: #c0392b;
        border-radius: 4px 4px 20px 20px;
        display: block;
        margin: 0 auto 2px auto;
        position: relative;
        overflow: hidden;
    }

    .logo-text-eas {
        font-size: 15pt;
        font-weight: bold;
        color: #fff;
        line-height: 1;
        display: block;
        padding-top: 6px;
        letter-spacing: 1px;
    }

    .logo-text-agro {
        font-size: 4pt;
        color: #fff;
        display: block;
        letter-spacing: 0.5px;
        line-height: 1.1;
    }

    .logo-company-name {
        font-size: 5.5pt;
        font-weight: bold;
        color: #000;
        display: block;
        margin-top: 2px;
        letter-spacing: 0.3px;
    }

    .header-title-cell {
        text-align: center;
        vertical-align: middle;
        padding: 4mm 2mm;
    }

    .header-title-cell h1 {
        font-size: 14pt;
        font-weight: bold;
        letter-spacing: 1px;
        margin: 0;
    }

    .header-fmr-cell {
        width: 24mm;
        padding: 15mm 2mm 0.5mm 2mm;
        vertical-align: middle;
        font-size: 7.5pt;
        line-height: 1.2;
        text-align: right;
    }

    /* ===================== FORM BODY ===================== */
    .form-body {
        padding: 1.5mm 3mm;
    }

    .field-row {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .field-row td {
        padding: 0;
        vertical-align: baseline;
        font-size: 10pt;
        line-height: 1.3;
    }

    .label-col {
        width: 28mm;
        font-weight: normal;
        white-space: nowrap;
    }

    .colon-col {
        width: 4mm;
        text-align: center;
    }

    .value-col {
        /* flexible */
    }

    /* Horizontal rule under field lines */
    .field-underline {
        border-bottom: 0.75pt solid #000;
        display: block;
        min-height: 3mm;
        width: 100%;
    }

    /* ===================== SEPARATOR LINE ===================== */
    .sep-line {
        border-top: 0.75pt solid #000;
        margin: 1.5mm 0;
    }

    /* ===================== CHECKBOX ===================== */
    .cb {
        display: inline-block;
        width: 9px;
        height: 9px;
        border: 1pt solid #000;
        margin-right: 2px;
        vertical-align: middle;
        line-height: 9px;
        text-align: center;
        font-size: 7pt;
    }

    .cb-group {
    display: inline-block;
    vertical-align: middle;
    margin-right: 8mm; /* ganti &nbsp;&nbsp; */
}

.cb-box {
    display: inline-block;
    width: 9px;
    height: 9px;
    border: 1pt solid #000;
    margin-right: 1.5mm;
    vertical-align: middle;
    position: relative;
    top: -0.8px; /* tuning paling pas */
}

.cb-text {
    display: inline-block;
    vertical-align: middle;
}

    /* ===================== ITALIC / SMALL TEXT ===================== */
    .italic {
        font-style: italic;
    }

    .small {
        font-size: 7.5pt;
    }

    .bold {
        font-weight: bold;
    }

    /* ===================== AKSES EMAIL SECTION ===================== */
    .akses-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1mm;
    }

    .akses-table td {
        padding: 0.5mm 0;
        vertical-align: top;
    }

    /* ===================== EMAIL ADDRESS ROW ===================== */
    .email-row {
        border-top: 0.75pt solid #000;
        border-bottom: 0.75pt solid #000;
        padding: 1.5mm 0;
        margin: 1.5mm 0;
        width: 100%;
        border-collapse: collapse;
    }

    .email-row td {
        padding: 1mm 0;
        vertical-align: bottom;
    }

    .email-underline,
    .keterangan-underline {
        display: inline-block;
        border-bottom: 1pt solid #000;
        vertical-align: baseline;
        position: relative;
        top: -1px;
    }

    .email-underline {
        width: 65mm;
    }

    .keterangan-underline {
        width: 100mm;
    }

    /* ===================== SIGNATURE TABLE ===================== */
    .sig-outer {
        border-top: 1.5pt solid #000;
        margin-top: 2mm;
    }

    .sig-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sig-table td {
        border: 1pt solid #000;
        text-align: center;
        vertical-align: top;
        padding: 1mm 1mm 0 1mm;
        font-size: 8pt;
    }

    .sig-table td.no-left {
        border-left: none;
    }

    .sig-table td.no-right {
        border-right: none;
    }

    .sig-table td.no-top {
        border-top: none;
    }

    .sig-header-row td {
        background-color: #fff;
        font-weight: normal;
        padding: 1mm;
        font-size: 8pt;
        height: 6mm;
        vertical-align: middle;
    }

    .sig-name-row td {
        font-size: 8pt;
        font-weight: normal;
        height: 5mm;
        vertical-align: middle;
    }

    .sig-sign-row td {
        height: 22mm;
        vertical-align: top;
        padding-top: 1mm;
    }

    .sig-jabatan-row td {
        font-size: 8pt;
        height: 5mm;
        vertical-align: middle;
        border-top: 1pt solid #000;
    }

    /* ===================== PERHATIAN ===================== */
    .perhatian-section {
        padding: 2mm 1mm 0 1mm;
        font-size: 7.5pt;
        line-height: 1.5;
        border-top: 1.5pt solid #000;
        margin-top: 2mm;
    }

    .perhatian-title {
        font-weight: bold;
        margin-bottom: 0.5mm;
    }

    .perhatian-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .perhatian-list li {
        margin-bottom: 0.5mm;
        padding-left: 0;
        text-indent: -3mm;
        padding-left: 3mm;
    }

    /* ===================== STATUS / AKSES ROW ===================== */
    .status-akses-table {
        width: 100%;
        border-collapse: collapse;
    }

    .status-akses-table td {
        vertical-align: top;
        padding: 0.3mm 0;
    }
</style>
</head>
<body>
@php
    $logoPath = public_path('images/eas-new.png');
    $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath)) : null;

    $unitName = $emailRequest->unit?->name ?? '-';

    $dateLabel = optional($emailRequest->created_at)->format('d / m / Y') ?: now()->format('d / m / Y');

    $isLocal  = ($emailRequest->access_level ?? 'internal') === 'internal';
    $isGlobal = ! $isLocal;

    $emailLocalPart = $emailRequest->requested_email
        ? str_replace('@easgroup.co.id', '', $emailRequest->requested_email)
        : '';
@endphp

<div class="outer-border">

    <!-- ============================================================ -->
    <!-- HEADER                                                        -->
    <!-- ============================================================ -->
    <table class="header-table">
        <tr>
            <!-- LOGO -->
            <td class="header-logo-cell">
                @if ($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="EAS Logo" class="logo-img">
                @else
                    <div class="logo-box">
                        <div class="logo-shield">
                            <span class="logo-text-eas">EAS</span>
                            <span class="logo-text-agro">AGRO</span>
                        </div>
                        <span class="logo-company-name">ESHAN AGRO SENTOSA</span>
                    </div>
                @endif
            </td>

            <!-- TITLE -->
            <td class="header-title-cell">
                <h1>PERMOHONAN E-MAIL</h1>
            </td>

            <!-- FORM NUMBER -->
            <td class="header-fmr-cell">
                FMR-ICT-05<br>
                REV. 00
            </td>
        </tr>
    </table>

    <!-- ============================================================ -->
    <!-- FORM BODY                                                      -->
    <!-- ============================================================ -->
    <div class="form-body">

        <!-- PT -->
        <table class="field-row" style="margin-bottom:1mm;">
            <tr>
                <td class="label-col">PT</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $unitName }}</td>
            </tr>
        </table>

        <!-- Nama Pemohon -->
        <table class="field-row" style="margin-bottom:1mm;">
            <tr>
                <td class="label-col">Nama Pemohon</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="padding:0;">{{ $emailRequest->employee_name }}</td>
                            <td style="width:22mm; text-align:right; padding:0; white-space:nowrap; padding-left:2mm;">: &nbsp;{{ $dateLabel }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Jabatan -->
        <table class="field-row" style="margin-bottom:1mm;">
            <tr>
                <td class="label-col">Jabatan</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $emailRequest->job_title ?: '' }}</td>
            </tr>
        </table>

        <!-- Department -->
        <table class="field-row" style="margin-bottom:2mm;">
            <tr>
                <td class="label-col">Department</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="padding:0;">{{ $emailRequest->department_name }}</td>
                            <td style="width:60mm; text-align:right; padding:0; font-size:8pt; padding-left:2mm; white-space:nowrap;">
                                (Baru / Mutasi / Promosi / Lain2) <span class="italic">(Coret yang tidak perlu)</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Permohonan -->
        <table class="field-row" style="margin-bottom:1.5mm; margin-top:1mm;">
            <tr>
                <td class="label-col">Permohonan</td>
                <td class="colon-col">:</td>
                <td class="value-col">
    <span class="cb-group">
        <span class="cb-box"></span>
        <span class="cb-text"><strong>E-Mail Account</strong></span>
    </span>

    <span class="cb-group">
        <span class="cb-box"></span>
        <span class="cb-text"><strong>Perubahan Hak Akses Email Account</strong></span>
    </span>
</td>
            </tr>
        </table>

        <!-- Untuk -->
        <table class="field-row" style="margin-top:1mm; margin-bottom:0.5mm;">
            <tr>
                <td class="label-col">Untuk</td>
                <td class="colon-col">:</td>
                <td class="value-col">
    <span class="cb-group">
        <span class="cb-box"></span>
        <span class="cb-text">
            <strong>Setting e-mail group pada mobile device pribadi/kantor</strong>
        </span>
    </span>
</td>
            </tr>
        </table>

        <!-- Perangkat -->
        <table class="field-row" style="margin-bottom:0.3mm;">
            <tr>
                <td style="width:32mm;">&nbsp;</td>
                <td>Perangkat :</td>
            </tr>
        </table>

        <!-- Device list -->
        <table class="field-row" style="margin-bottom:0.3mm;">
            <tr>
                <td style="width:44mm;">&nbsp;</td>
                <td><span class="cb">&nbsp;</span>&nbsp; Blackberry</td>
            </tr>
        </table>
        <table class="field-row" style="margin-bottom:0.3mm;">
            <tr>
                <td style="width:44mm;">&nbsp;</td>
                <td><span class="cb">&nbsp;</span>&nbsp; Android (Samsung,Dll)</td>
            </tr>
        </table>
        <table class="field-row" style="margin-bottom:0.3mm;">
            <tr>
                <td style="width:44mm;">&nbsp;</td>
                <td><span class="cb">&nbsp;</span>&nbsp; Apple IOS (Iphone,Ipad)</td>
            </tr>
        </table>
        <table class="field-row" style="margin-bottom:2mm;">
            <tr>
                <td style="width:44mm;">&nbsp;</td>
                <td><span class="cb">&nbsp;</span>&nbsp; Perangkat Lainnya : ..............................................</td>
            </tr>
        </table>

        <!-- Status + Akses E-Mail -->
        <table class="status-akses-table" style="margin-top:1mm; margin-bottom:1mm;">
            <tr>
                <td style="width:28mm; vertical-align:top; padding-top:0.5mm;">Status</td>
                <td style="width:4mm; vertical-align:top; padding-top:0.5mm;">:</td>
                <td>
                    <!-- Akses E-Mail block -->
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="padding:0 0 1mm 0; vertical-align:top;">
                                Akses E-Mail :
                            </td>
                        </tr>
                        <!-- Lokal row -->
                        <tr>
                            <td style="padding:0.3mm 0; vertical-align:top;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <tr>
                                        <td style="width:14mm; vertical-align:top; padding:0;">
                                            <span class="cb"></span> <strong>Lokal</strong> :
                                        </td>
                                        <td style="vertical-align:top; padding:0;">
                                            - &nbsp;Hanya bisa terima dan kirim e-mail internal
                                            <span class="italic">(<span class="italic">sesama@easgroup.co.id</span>) - Default</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- Global row -->
                        <tr>
                            <td style="padding:0.3mm 0; vertical-align:top;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <tr>
                                        <td style="width:15mm; vertical-align:top; padding:0;">
                                            <span class="cb"></span> <strong>Global*</strong>
                                        </td>
                                        <td style="vertical-align:top; padding:0;">
                                            - &nbsp;<span class="cb">&nbsp;</span>Hanya bisa kirim e-mail keluar
                                            <span class="italic">(Diluar domain @easgroup.co.id)</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top; padding:0;">&nbsp;</td>
                                        <td style="vertical-align:top; padding:0.3mm 0 0 0;">
                                            - &nbsp;<span class="cb">&nbsp;</span>Hanya bisa terima e-mail dari luar
                                            <span class="italic">(Diluar domain @easgroup.co.id)</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top; padding:0;">&nbsp;</td>
                                        <td style="vertical-align:top; padding:0.3mm 0 0 0;">
                                            - &nbsp;<span class="cb">&nbsp;</span>Bisa kirim &amp; terima e-mail dari luar
                                            <span class="italic">(Diluar domain @easgroup.co.id) - Full Akses</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Alamat E-Mail -->
        <table class="field-row" style="margin-top:1.5mm; margin-bottom:0.5mm;">
            <tr>
                <td style="width:56mm;">Alamat E-Mail <span class="italic">(Diisi Oleh HR)</span></td>
                <td style="width:4mm; text-align:center;">:</td>
                <td>
                    <span class="email-underline">{{ $emailLocalPart }}</span>
                    <strong>@easgroup.co.id</strong>
                </td>
            </tr>
        </table>

        <!-- Keterangan -->
        <table class="field-row" style="margin-bottom:2mm; margin-top:1mm;">
            <tr>
                <td style="width:56mm;">Keterangan</td>
                <td style="width:4mm; text-align:center;">:</td>
                <td>
                    <span class="keterangan-underline">{{ $emailRequest->justification }}</span>
                </td>
            </tr>
        </table>

    </div><!-- /form-body -->

    <!-- ============================================================ -->
    <!-- SIGNATURE TABLE                                               -->
    <!-- ============================================================ -->
    <div class="sig-outer">
        <table class="sig-table">
            <!-- Row 1: Headers -->
            <tr class="sig-header-row">
                <td rowspan="2" style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000; vertical-align:middle;">
                    Dibuat
                </td>
                <td colspan="2" style="width:38%; border-right:1pt solid #000; border-bottom:1pt solid #000; vertical-align:middle;">
                    Diketahui
                </td>
                <td style="width:22%; border-right:1pt solid #000; border-bottom:1pt solid #000; vertical-align:middle;">
                    Disetujui
                </td>
                <td style="width:21%; border-bottom:1pt solid #000; vertical-align:middle;">
                    Pelaksana
                </td>
            </tr>
            <!-- Row 2: Sub-headers -->
            <tr class="sig-name-row">
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">Dept. Head</td>
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">Div. Head</td>
                <td style="width:22%; border-right:1pt solid #000; border-bottom:1pt solid #000;">Div. Head HRGA EAS</td>
                <td style="width:21%; border-bottom:1pt solid #000;">Dept Head ICT EAS</td>
            </tr>
            <!-- Row 3: Signature space -->
            <tr class="sig-sign-row">
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">&nbsp;</td>
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">&nbsp;</td>
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">&nbsp;</td>
                <td style="width:22%; border-right:1pt solid #000; border-bottom:1pt solid #000;">&nbsp;</td>
                <td style="width:21%; border-bottom:1pt solid #000;">&nbsp;</td>
            </tr>
            <!-- Row 4: Nama -->
            <tr class="sig-name-row">
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">{{ $emailRequest->employee_name }}</td>
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">{{ $emailRequest->diketahui_dept_head_name ?: '' }}</td>
                <td style="width:19%; border-right:1pt solid #000; border-bottom:1pt solid #000;">{{ $emailRequest->diketahui_div_head_name ?: '' }}</td>
                <td style="width:22%; border-right:1pt solid #000; border-bottom:1pt solid #000;">{{ $emailRequest->disetujui_hrga_head_name ?: '' }}</td>
                <td style="width:21%; border-bottom:1pt solid #000;">{{ $emailRequest->pelaksana_ict_head_name ?: '' }}</td>
            </tr>
            <!-- Row 5: Jabatan -->
            <tr class="sig-jabatan-row">
                <td style="width:19%; border-right:1pt solid #000;">{{ $emailRequest->job_title ?: 'Jabatan' }}</td>
                <td style="width:19%; border-right:1pt solid #000;">{{ $emailRequest->diketahui_dept_head_title ?: 'Jabatan' }}</td>
                <td style="width:19%; border-right:1pt solid #000;">{{ $emailRequest->diketahui_div_head_title ?: 'Jabatan' }}</td>
                <td style="width:22%; border-right:1pt solid #000;">{{ $emailRequest->disetujui_hrga_head_title ?: 'Jabatan' }}</td>
                <td style="width:21%;">{{ $emailRequest->pelaksana_ict_head_title ?: 'Jabatan' }}</td>
            </tr>
        </table>
    </div>

    <!-- ============================================================ -->
    <!-- PERHATIAN                                                      -->
    <!-- ============================================================ -->
    <div class="perhatian-section">
        <p class="perhatian-title">Perhatian :</p>
        <ol style="padding-left:4mm; font-size:7.5pt; margin:0; list-style-type:decimal;">
            <li style="margin-bottom:0.5mm;">
                Hak akses grup sepenuhnya menjadi tanggung jawab departemen yang bersangkutan dan tidak ada hubungannya dengan Departemen ICT
            </li>
            <li style="margin-bottom:0.5mm;">
                User/ pemohon bertanggung jawab atas keamanan data perusahaan adalah untuk kepentingan kantor dan departemen terkait.<br>
                &nbsp;&nbsp;User/ pemohon tidak diperbolehkan membocorkan data perusahaan.
            </li>
            <li style="margin-bottom:0.5mm;">
                Apabila user/ pemohon ditemukan melakukan hal tersebut maka akan dikenakan sanksi sesuai dengan peraturan yang berlaku.
            </li>
            <li style="margin-bottom:1mm;">
                Pengajuan ini harus ditandatangani oleh Dept. Head/ Manager User/ pemohon yang mengajukan permohonan untuk mentransfer data ke<br>
                &nbsp;&nbsp;USB yang dilakukan oleh tim ICT.
            </li>
        </ol>
    </div>

</div><!-- /outer-border -->

</body>
</html>
