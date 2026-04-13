<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Permohonan Fasilitas ICT</title>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.6px;
            color: #111111;
            margin: 0;
        }

        .page {
            border: 1px solid #111111;
            position: relative;
        }

        .page-break {
            page-break-before: always;
        }

        .copy-mode {
            color: #000000;
        }

        .copy-watermark {
            position: absolute;
            top: 43%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 4px;
            color: rgba(120, 120, 120, 0.16);
            white-space: nowrap;
            z-index: 0;
        }

        .page-inner {
            position: relative;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #111111;
        }

        .detail-table {
            table-layout: fixed;
        }

        .header td {
            vertical-align: middle;
            border-bottom: 1px solid #111111;
        }

        .logo-cell {
            width: 132px;
            text-align: center;
            padding: 3px 6px;
            border-right: 1px solid #111111;
        }

        .logo-cell img {
            width: 72px;
            display: block;
            margin: 0 auto;
        }

        .title-cell {
            text-align: center;
            font-size: 9.6px;
            font-weight: 700;
            letter-spacing: 0.3px;
            padding: 3px 8px;
        }

        .code-cell {
            width: 92px;
            text-align: right;
            font-size: 6.5px;
            line-height: 1.3;
            padding: 3px 8px 2px;
            vertical-align: bottom !important;
        }

        .content {
            padding: 10px 18px 18px;
        }

        .meta-layout td {
            border: none;
            vertical-align: top;
        }

        .meta-left {
            width: 40%;
            padding-right: 18px;
        }

        .meta-left table td,
        .meta-right table td {
            border: none;
            padding: 2px 0;
            font-size: 7.4px;
            vertical-align: middle;
        }

        .meta-label {
            width: 58px;
            font-weight: 700;
        }

        .meta-colon {
            width: 10px;
            text-align: center;
        }

        .meta-value {
            border-bottom: 1px solid #111111 !important;
            padding-left: 8px !important;
            height: 14px;
        }

        .meta-value-text {
            display: block;
            width: 100%;
            line-height: 14px;
        }

        .meta-right {
            width: 30%;
        }

        .check-table {
            width: 100%;
        }

        .check-table td {
            padding: 6px 0;
            font-size: 7.4px;
            font-weight: 700;
        }

        .check-label {
            width: 72px;
        }

        .check-colon {
            width: 12px;
            text-align: center;
        }

        .checkbox {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1px solid #111111;
            vertical-align: middle;
            position: relative;
            text-align: center;
        }

        .checkbox.checked::after {
            content: "X";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            margin-top: -6px;
            margin-left: -4px;
            line-height: 8px;
            text-align: center;
            font-size: 8px;
            font-weight: 700;
        }

        .section-title {
            margin: 16px 0 10px 3px;
            font-size: 8px;
            font-weight: 700;
        }

        .detail-table th {
            font-size: 6.9px;
            font-weight: 700;
            text-align: center;
            padding: 4px 3px;
        }

        .detail-table td {
            font-size: 6.6px;
            line-height: 1.2;
            padding: 4px 4px;
            word-break: break-word;
        }

        .detail-table .center {
            text-align: center;
            vertical-align: middle;
        }

        .detail-table .money {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .detail-table .row-fixed {
            height: 19px;
        }

        .item-name {
            font-weight: 700;
        }

        .text-sm {
            font-size: 6.5px;
        }

        .text-xs {
            font-size: 6.1px;
        }

        .total-row td {
            font-weight: 700;
        }

        .total-row .total-label {
            text-align: right;
            padding-right: 10px;
        }

        .reason-title {
            margin: 14px 0 6px 3px;
            font-size: 7.8px;
            font-weight: 700;
        }

        .line-block {
            min-height: 66px;
        }

        .dot-line {
            border-bottom: 1px dotted #666666;
            height: 12px;
        }

        .dot-line.text {
            height: auto;
            min-height: 14px;
            padding: 0 2px 2px;
            line-height: 1.3;
        }

        .signatures {
            margin-top: 18px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .signatures td {
            width: 25%;
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }

        .sign-card {
            width: 100%;
            text-align: center;
            border: none;
        }

        .sign-label {
            font-size: 7px;
            text-align: center;
            min-height: 16px;
            line-height: 1.25;
        }

        .sign-approvals {
            text-align: center;
        }

        .approval-head {
            font-size: 7px;
            text-align: center;
            min-height: 14px;
            margin-bottom: 0;
        }

        .sign-spacer-row td {
            height: 66px;
            padding: 0;
        }

        .sign-name {
            font-size: 7px;
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 2px;
        }

        .sign-title {
            font-size: 6.9px;
        }

        .pta-title {
            margin: 16px 0 2px 3px;
            font-size: 9px;
            font-weight: 700;
        }

        .pta-copy {
            font-size: 7px;
            line-height: 1.25;
            margin: 0 3px 10px;
        }

        .pta-table td {
            border: none;
            padding: 3px 0;
            font-size: 7px;
            vertical-align: top;
        }

        .pta-number {
            width: 18px;
            text-align: center;
        }

        .pta-signoff {
            width: 28%;
            margin-top: 12px;
        }

        .pta-signoff td {
            border: none;
            padding: 0;
            text-align: center;
        }

        .pta-manual-space {
            height: 64px;
        }

        .pta-manual-line {
            font-size: 7px;
            line-height: 1.3;
            white-space: nowrap;
            text-align: center;
        }

        .gallery-wrapper {
            padding: 16px 12px 18px;
        }

        .gallery-table td {
            width: 25%;
            border: none;
            padding: 8px;
            text-align: center;
            vertical-align: top;
        }

        .gallery-caption {
            border: 1px solid #c8c8c8;
            min-height: 24px;
            padding: 5px;
            margin-bottom: 8px;
            font-size: 6.7px;
            line-height: 1.2;
        }

        .gallery-image-box {
            height: 120px;
        }

        .gallery-image-box img {
            max-width: 100%;
            max-height: 118px;
        }

        .muted {
            color: #666666;
            font-size: 6.8px;
        }
    </style>
</head>
<body class="{{ $isCopy ? 'copy-mode' : '' }}">
    @php
        $minimumRows = 8;
        $emptyRows = max($minimumRows - $items->count(), 0);
    @endphp

    <div class="page">
        @if ($isCopy)
            <div class="copy-watermark">DOCUMENT COPY</div>
        @endif
        <div class="page-inner">
        <table class="header">
            <tr>
                <td class="logo-cell">
                    @if ($logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="EAS">
                    @endif
                </td>
                <td class="title-cell">PERMOHONAN FASILITAS ICT</td>
                <td class="code-cell">
                    FMR-ICT-01<br>
                    {{ $revisionLabel }}
                </td>
            </tr>
        </table>

        <div class="content">
            <table class="meta-layout">
                <tr>
                    <td class="meta-left">
                        <table>
                            <tr>
                                <td class="meta-label">PT</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="meta-value-text">{{ $companyLabel }}</span></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Tanggal</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="meta-value-text">{{ $requestDate ?: '-' }}</span></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Pengguna</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="meta-value-text">{{ $requesterLabel }}</span></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Dept.</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="meta-value-text">{{ $departmentLabel }}</span></td>
                            </tr>
                        </table>
                    </td>
                    <td class="meta-right">
                        <table class="check-table">
                            <tr>
                                <td class="check-label">URGENT</td>
                                <td class="check-colon">:</td>
                                <td><span class="checkbox {{ $ictRequest->priority === 'urgent' ? 'checked' : '' }}"></span></td>
                            </tr>
                            <tr>
                                <td class="check-label">NORMAL</td>
                                <td class="check-colon">:</td>
                                <td><span class="checkbox {{ $ictRequest->priority === 'normal' ? 'checked' : '' }}"></span></td>
                            </tr>
                        </table>
                    </td>
                    <td class="meta-right">
                        <table class="check-table">
                            <tr>
                                <td class="check-label">HARDWARE</td>
                                <td class="check-colon">:</td>
                                <td><span class="checkbox {{ $ictRequest->request_category === 'hardware' ? 'checked' : '' }}"></span></td>
                            </tr>
                            <tr>
                                <td class="check-label">SOFTWARE</td>
                                <td class="check-colon">:</td>
                                <td><span class="checkbox {{ $ictRequest->request_category === 'software' ? 'checked' : '' }}"></span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="section-title">Detail Fasilitas ICT</div>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 34%;">Nama Barang</th>
                        <th style="width: 10%;">Merk/Tipe</th>
                        <th style="width: 6%;">Jumlah</th>
                        <th style="width: 10%;">Harga</th>
                        <th style="width: 11%;">Total</th>
                        <th style="width: 25%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $itemNameLength = strlen($item['item_name']);
                            $itemNotesLength = strlen($item['notes']);
                            $itemNameClass = $itemNameLength > 80 ? 'text-xs' : ($itemNameLength > 45 ? 'text-sm' : '');
                            $itemNotesClass = $itemNotesLength > 170 ? 'text-xs' : ($itemNotesLength > 95 ? 'text-sm' : '');
                        @endphp
                        <tr class="row-fixed">
                            <td class="center">{{ $item['number'] }}</td>
                            <td class="item-name {{ $itemNameClass }}">{{ $item['item_name'] }}</td>
                            <td class="center">{{ $item['brand_type'] }}</td>
                            <td class="center">{{ $item['quantity_label'] }}</td>
                            <td class="money">Rp. {{ number_format($item['estimated_price'], 0, ',', '.') }}</td>
                            <td class="money">Rp. {{ number_format($item['total_price'], 0, ',', '.') }}</td>
                            <td class="{{ $itemNotesClass }}">{!! nl2br(e($item['notes'] !== '' ? $item['notes'] : '-')) !!}</td>
                        </tr>
                    @endforeach
                    @for ($row = 0; $row < $emptyRows; $row++)
                        <tr class="row-fixed">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                    <tr class="total-row">
                        <td>&nbsp;</td>
                        <td colspan="4" class="total-label">TOTAL</td>
                        <td class="money">Rp. {{ number_format($totalEstimatedPrice, 0, ',', '.') }}</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>

            <div class="reason-title">Alasan Kebutuhan :</div>
            <div class="line-block">
                @if (filled($ictRequest->justification))
                    <div class="dot-line text">{{ $ictRequest->justification }}</div>
                    @for ($line = 0; $line < 4; $line++)
                        <div class="dot-line"></div>
                    @endfor
                @else
                    @for ($line = 0; $line < 5; $line++)
                        <div class="dot-line"></div>
                    @endfor
                @endif
            </div>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="sign-label">{{ $signatureBlocks[0]['label'] }}</div>
                    </td>
                    <td>
                        <div class="sign-label">{{ $signatureBlocks[1]['label'] }}</div>
                    </td>
                    <td colspan="2" class="sign-approvals">
                        <div class="approval-head">Disetujui oleh</div>
                    </td>
                </tr>
                <tr class="sign-spacer-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <div class="sign-name">{{ $signatureBlocks[0]['name'] ?: 'Nama' }}</div>
                        <div class="sign-title">{{ $signatureBlocks[0]['title'] ?: 'Jabatan' }}</div>
                    </td>
                    <td>
                        <div class="sign-name">{{ $signatureBlocks[1]['name'] ?: 'Nama' }}</div>
                        <div class="sign-title">{{ $signatureBlocks[1]['title'] ?: 'Jabatan' }}</div>
                    </td>
                    <td>
                        <div class="sign-name">{{ $signatureBlocks[2]['name'] ?: 'Nama' }}</div>
                        <div class="sign-title">{{ $signatureBlocks[2]['title'] ?: 'Jabatan' }}</div>
                    </td>
                    <td>
                        <div class="sign-name">{{ $signatureBlocks[3]['name'] ?: 'Nama' }}</div>
                        <div class="sign-title">{{ $signatureBlocks[3]['title'] ?: 'Jabatan' }}</div>
                    </td>
                </tr>
            </table>

            <div class="pta-title">Permohonan Pembuatan PTA</div>
            <div class="pta-copy">
                Mengingat permintaan barang tersebut diatas tidak kami anggarkan ditahun ini, maka mohon dibuatkan Permintaan Tambahan Anggaran (PTA) atas barang tersebut, dengan alasan :
            </div>

            <table class="pta-table">
                <tr>
                    <td class="pta-number">1.</td>
                    <td>Tambahan anggaran digunakan untuk :</td>
                </tr>
                <tr>
                    <td></td>
                    <td><div class="dot-line text">{{ $ictRequest->additional_budget_reason ?: ' ' }}</div></td>
                </tr>
                <tr>
                    <td class="pta-number">2.</td>
                    <td>Anggaran tidak dicantumkan ditahun ini, karena :</td>
                </tr>
                <tr>
                    <td></td>
                    <td><div class="dot-line text">{{ $ictRequest->pta_budget_not_listed_reason ?: ' ' }}</div></td>
                </tr>
                <tr>
                    <td class="pta-number">3.</td>
                    <td>Tambahan anggaran diadakan karena :</td>
                </tr>
                <tr>
                    <td></td>
                    <td><div class="dot-line text">{{ $ictRequest->pta_additional_budget_reason ?: ' ' }}</div></td>
                </tr>
            </table>

            <table class="pta-signoff">
                <tr>
                    <td>
                        <div class="sign-label">{{ $ptaApprovals[1]['label'] }}<br>{{ $ptaApprovals[1]['title'] ?: 'Dept. Head Budget & Control' }}</div>
                        <div class="pta-manual-space"></div>
                        <div class="pta-manual-line">(.........................................)</div>
                        <div class="sign-title">Dept. Head Budget</div>
                    </td>
                </tr>
            </table>
        </div>
        </div>
    </div>

    <div class="page page-break">
        @if ($isCopy)
            <div class="copy-watermark">DOCUMENT COPY</div>
        @endif
        <div class="page-inner">
        <div class="gallery-wrapper">
            <table class="gallery-table">
                <tbody>
                    @foreach ($items->chunk(4) as $row)
                        <tr>
                            @foreach ($row as $item)
                                <td>
                                    <div class="gallery-caption">{{ $item['number'] }}. {{ $item['photo_title'] }}</div>
                                    <div class="gallery-image-box">
                                        @if ($item['photo_data_uri'])
                                            <img src="{{ $item['photo_data_uri'] }}" alt="{{ $item['photo_title'] }}">
                                        @else
                                            <div class="muted">Tidak ada foto</div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            @for ($i = $row->count(); $i < 4; $i++)
                                <td></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>
</body>
</html>
