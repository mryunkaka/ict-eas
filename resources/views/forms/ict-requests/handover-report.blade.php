<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Serah Terima Fasilitas ICT</title>
    <style>
        @page {
            margin: 5px 6px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #111111;
            margin: 0;
            line-height: 1.12;
        }

        .page {
            position: relative;
            border: 1px solid #000;
            padding: 3px 4px 4px 4px;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 5px;
        }

        .logo {
            width: 52px;
            height: 52px;
        }

        .logo-wrap {
            width: 58px;
        }

        .header-table td {
            padding: 3px 4px;
            border: none;
            vertical-align: middle;
        }

        .header-top td {
            border-bottom: 1px solid #000;
        }

        .header-logo {
            border-right: 1px solid #000;
        }

        .header-meta {
            border-left: 1px solid #000;
        }

        .header-pt {
            border-right: 1px solid #000;
        }

        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }

        .doc-meta {
            text-align: right;
            font-size: 8px;
            line-height: 1.1;
        }

        .top-info {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .top-info td {
            border: none;
            padding: 1px 0;
            font-size: 9px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 5px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: top;
        }

        .items-table th {
            font-weight: bold;
            text-align: center;
            background: #fff;
        }

        .item-summary {
            margin: -2px 0 5px 0;
            font-size: 8px;
        }

        .asset-photo-box {
            border: 1px solid #000;
            padding: 4px;
            margin-top: 5px;
        }

        .asset-photo-label {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .asset-photo-frame {
            text-align: left;
            min-height: 130px;
        }

        .asset-photo-frame img {
            max-width: 260px;
            max-height: 160px;
            object-fit: contain;
        }

        .asset-photo-empty {
            padding-top: 50px;
            color: #444;
        }

        .terms-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 4px;
        }

        .terms-box td {
            border: none;
            padding: 4px 5px;
            vertical-align: top;
        }

        .terms-separator {
            border-top: 1px solid #000;
            margin: 4px 0 0 0;
        }

        .terms-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .terms-list td {
            border: none;
            padding: 1px 0;
            vertical-align: top;
        }

        .terms-list .num {
            width: 14px;
            padding-right: 4px;
        }

        .terms-list .indent {
            padding-left: 14px;
        }

        .section-label {
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 2px;
        }

        .sign-main {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 5px;
        }

        .sign-main td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
        }

        .sign-title {
            font-weight: bold;
            text-align: center;
            padding: 4px;
        }

        .field-table {
            width: 100%;
            border-collapse: collapse;
        }

        .field-table td {
            border: none;
            padding: 1px 0;
        }

        .field-table .label {
            width: 28%;
        }

        .field-table .colon {
            width: 8px;
        }

        .paren {
            text-align: center;
        }

        .paren-wrap {
            display: inline-block;
            white-space: nowrap;
        }

        .paren-line {
            display: inline-block;
            width: 95px;
            border-bottom: 1px solid #000;
            height: 8px;
            vertical-align: bottom;
            margin: 0 3px;
        }

        .position-label {
            text-align: center;
        }

        .sign-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .sign-grid td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .sign-box {
            width: 100%;
            border-collapse: collapse;
        }

        .sign-box td {
            border: none;
            padding: 1px 0;
        }

        .sign-space {
            height: 22px;
        }

        .sign-line {
            display: inline-block;
            min-width: 140px;
            border-bottom: 1px solid #000;
            height: 9px;
            vertical-align: bottom;
        }

        .small {
            font-size: 8px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/eas-new.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;
    @endphp

    <div class="page">
        <div class="content">
            <table class="header-table">
                <tr class="header-top">
                    <td class="logo-wrap header-logo" style="width: 20%;">
                        @if ($logoDataUri)
                            <img src="{{ $logoDataUri }}" alt="EAS" class="logo">
                        @endif
                    </td>
                    <td style="width: 60%;">
                        <div class="doc-title">SERAH TERIMA FASILITAS ICT</div>
                    </td>
                    <td class="header-meta" style="width: 20%;">
                        <div class="doc-meta">
                            FMR-ICT-02<br>
                            REV. 00
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="header-pt">
                        <table class="top-info">
                            <tr>
                                <td style="width: 34px;">PT</td>
                                <td style="width: 10px;">:</td>
                                <td>{{ $ictRequest->unit?->code ? \Illuminate\Support\Str::before($ictRequest->unit->code, '-') : '-' }}</td>
                            </tr>
                            <tr>
                                <td>Dept.</td>
                                <td>:</td>
                                <td>{{ $handover->dept ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="header-meta">&nbsp;</td>
                </tr>
            </table>

            @php
                $itemType = $item->item_category ?: $item->item_name;
                $itemQuantity = max((int) ($item->quantity ?? 1), 1);
                $assetPhotoDataUri = null;
                $assetPhotoAlt = $item->item_name;
                $serahTerimaIsNonImage = false;
                // Foto di PDF: utamakan Upload Foto Barang (serah terima) per unit; fallback ke foto lampiran item pengadaan hanya jika belum ada unggahan non-gambar.
                $serahPath = $handover->serah_terima_path ? storage_path('app/public/'.$handover->serah_terima_path) : null;
                if ($serahPath && is_file($serahPath)) {
                    $mimeType = mime_content_type($serahPath) ?: 'application/octet-stream';
                    if (str_starts_with((string) $mimeType, 'image/')) {
                        $assetPhotoDataUri = 'data:'.$mimeType.';base64,'.base64_encode((string) file_get_contents($serahPath));
                        $assetPhotoAlt = $handover->serah_terima_name ?: $assetPhotoAlt;
                    } else {
                        $serahTerimaIsNonImage = true;
                    }
                }
                if (! $assetPhotoDataUri && ! $serahTerimaIsNonImage) {
                    $itemPhotoPath = $item->photo_path ? storage_path('app/public/'.$item->photo_path) : null;
                    if ($itemPhotoPath && is_file($itemPhotoPath)) {
                        $mimeType = mime_content_type($itemPhotoPath) ?: 'image/jpeg';
                        if (str_starts_with((string) $mimeType, 'image/')) {
                            $assetPhotoDataUri = 'data:'.$mimeType.';base64,'.base64_encode((string) file_get_contents($itemPhotoPath));
                            $assetPhotoAlt = $item->photo_name ?: $item->item_name;
                        }
                    }
                }
            @endphp

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">No.</th>
                        <th style="width: 29%;">Jenis Barang</th>
                        <th style="width: 35%;">Model</th>
                        <th style="width: 15%;">Serial Number</th>
                        <th style="width: 15%;">No. Aset</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>{{ $itemType ?: '-' }}</td>
                        <td style="white-space: pre-line;">{{ $handover->model_specification ?: '-' }}</td>
                        <td>{{ $handover->serial_number ?: '-' }}</td>
                        <td>{{ $handover->asset_number ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
            <table class="terms-box">
                <tr>
                    <td>
                        <div class="section-label">HARDWARE</div>
                        <table class="terms-list">
                            <tr>
                                <td class="num">1</td>
                                <td>Aset ICT adalah milik Ehsan Agro Sentosa Group</td>
                            </tr>
                            <tr>
                                <td class="num">2</td>
                                <td>
                                    User/ Pengguna bertanggung jawab untuk pemeliharaan, menjaga dari kehilangan dan kerusakan yang dikarenakan kelalaian user/ pengguna
                                    <div class="indent">
                                        User/ pengguna memastikan Aset ICT Perusahaan<br>
                                        (i) Selalu berada ditempat/ tidak adanya pemindahan<br>
                                        (ii) Tidak akan membiarkan dalam kondisi tanpa pengawasan
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="num">3</td>
                                <td>
                                    Akan mengembalikan aset ICT Perusahaan ketika :
                                    <div class="indent">
                                        (i) Tidak diperlukan lagi atau diminta untuk menyerahkan kepada orang yang berhak atau<br>
                                        (ii) Berakhirnya Kontrak kerja / Berhenti Bekerja
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <div class="terms-separator"></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="section-label">SOFTWARE</div>
                        <table class="terms-list">
                            <tr>
                                <td class="num">1</td>
                                <td>Dilarang menginstalasi atau menggunakan software tanpa licensi yang dimiliki oleh perusahaan (seperti game dan video)</td>
                            </tr>
                            <tr>
                                <td class="num">2</td>
                                <td>
                                    Dilarang menggunakan Aset ICT perusahaan untuk memakai program perusak jaringan atau server
                                    <div class="indent small">(e.g.v viruses, worms, trojan horses, email bombs, Ultrasuft, etc)</div>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top: 6px;">
                            Dengan ini saya menerima aset ICT dan memenuhi syarat - syarat dan kondisi tersebut diatas :
                        </div>
                    </td>
                </tr>
            </table>

            <table class="sign-main">
                <tr>
                    <td style="width: 50%;" class="sign-title">Nama dan tanda tangan Pengguna/Penerima Aset</td>
                    <td style="width: 50%;" class="sign-title">Diketahui Atasan Pemakai</td>
                </tr>
                <tr>
                    <td>
                        <table class="field-table">
                            <tr>
                                <td class="label">Nama</td>
                                <td class="colon">:</td>
                                <td style="width: 67%;">{{ $handover->recipient_name ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Divisi/Dept.</td>
                                <td class="colon">:</td>
                                <td>{{ $handover->dept ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tanda tangan</td>
                                <td class="colon">:</td>
                                <td style="height: 22px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="paren"><span class="paren-wrap">(<span class="paren-line"></span>)</span></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="position-label">{{ $handover->recipient_position ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal</td>
                                <td class="colon">:</td>
                                <td>{{ now()->format('d F Y') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table class="field-table">
                            <tr>
                                <td class="label">Nama</td>
                                <td class="colon">:</td>
                                <td style="width: 67%;">{{ $handover->supervisor_name ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Divisi/Dept.</td>
                                <td class="colon">:</td>
                                <td>{{ $handover->dept ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tanda tangan</td>
                                <td class="colon">:</td>
                                <td style="height: 22px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="paren"><span class="paren-wrap">(<span class="paren-line"></span>)</span></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="position-label">{{ $handover->supervisor_position ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal</td>
                                <td class="colon">:</td>
                                <td>{{ now()->format('d F Y') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="sign-title">Diserahkan oleh</td>
                    <td class="sign-title">Dikembalikan Oleh</td>
                </tr>
                <tr>
                    <td>
                        <table class="field-table">
                            <tr>
                                <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                                    <table class="field-table">
                                        <tr>
                                            <td class="label">Nama</td>
                                            <td class="colon">:</td>
                                            <td>{{ $handover->witness_name ?: '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Divisi/Dept.</td>
                                            <td class="colon">:</td>
                                            <td>{{ $handover->dept ?: '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Tanda tangan</td>
                                            <td class="colon">:</td>
                                            <td style="height: 20px;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="paren"><span class="paren-wrap">(<span class="paren-line"></span>)</span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="position-label">{{ $handover->witness_position ?: '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Tanggal</td>
                                            <td class="colon">:</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                                    <table class="field-table">
                                        <tr>
                                            <td class="label">Nama</td>
                                            <td class="colon">:</td>
                                            <td>{{ $handover->deliverer_name ?: '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Divisi/Dept.</td>
                                            <td class="colon">:</td>
                                            <td>{{ $handover->dept ?: '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Tanda tangan</td>
                                            <td class="colon">:</td>
                                            <td style="height: 20px;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="paren"><span class="paren-wrap">(<span class="paren-line"></span>)</span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="position-label">{{ $handover->deliverer_position ?: '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Tanggal</td>
                                            <td class="colon">:</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table class="field-table">
                            <tr>
                                <td class="label">Nama</td>
                                <td class="colon">:</td>
                                <td style="width: 67%;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="label">Divisi/Dept.</td>
                                <td class="colon">:</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="label">Tanda tangan</td>
                                <td class="colon">:</td>
                                <td style="height: 22px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="paren">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
                            </tr>
                            <tr>
                                <td colspan="3">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal</td>
                                <td class="colon">:</td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="asset-photo-box">
                <div class="asset-photo-label">Foto Barang (dokumentasi serah terima)</div>
                <div class="asset-photo-frame">
                    @if ($assetPhotoDataUri)
                        <img src="{{ $assetPhotoDataUri }}" alt="{{ $assetPhotoAlt }}">
                    @else
                        <div class="asset-photo-empty">
                            @if ($serahTerimaIsNonImage)
                                Lampiran serah terima bukan gambar (mis. PDF). Unggah foto JPG/PNG di penerimaan barang agar tampil di berita acara.
                            @else
                                Belum ada foto unggahan penerimaan barang
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
