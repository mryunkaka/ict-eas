<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Serah Terima Asset</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111111;
            margin: 0;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .header h2 {
            font-size: 14px;
            margin: 0 0 5px 0;
        }

        .header p {
            margin: 2px 0;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-grid td {
            padding: 4px;
            border: none;
        }

        .info-grid td.label {
            width: 30%;
            font-weight: normal;
        }

        .info-grid td.separator {
            width: 5%;
            text-align: center;
        }

        .info-grid td.value {
            width: 65%;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-grid {
            width: 100%;
            margin-top: 20px;
        }

        .signature-grid td {
            text-align: center;
            padding: 10px;
            border: none;
            width: 25%;
        }

        .signature-space {
            height: 80px;
        }

        .underline {
            text-decoration: underline;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BERITA ACARA SERAH TERIMA ASSET</h1>
        <h2>{{ strtoupper($ictRequest->subject) }}</h2>
        <p>Nomor Form ICT: {{ $ictRequest->form_number }}</p>
        <p>Tanggal: {{ now()->format('d F Y') }}</p>
    </div>

    <div class="section">
        <div class="section-title">I. INFORMASI ASSET</div>
        <table>
            <tr>
                <th width="30%">Nama Asset</th>
                <td width="70%">{{ $item->item_name }}</td>
            </tr>
            <tr>
                <th>Kategori</th>
                <td>{{ ucfirst($item->item_category) }}</td>
            </tr>
            <tr>
                <th>Merk / Tipe</th>
                <td>{{ $item->brand_type }}</td>
            </tr>
            @if($handover->model_specification)
            <tr>
                <th>Model / Spesifikasi</th>
                <td>{{ $handover->model_specification }}</td>
            </tr>
            @endif
            @if($handover->serial_number)
            <tr>
                <th>Nomor Seri</th>
                <td>{{ $handover->serial_number }}</td>
            </tr>
            @endif
            @if($handover->asset_number)
            <tr>
                <th>Nomor Asset</th>
                <td>{{ $handover->asset_number }}</td>
            </tr>
            @endif
            @if($handover->dept)
            <tr>
                <th>Dept / Lokasi</th>
                <td>{{ $handover->dept }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. PIHAK PENYERAH (HRGA)</div>
        <table class="info-grid">
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td class="value">{{ $handover->deliverer_name ?? '....................................' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="separator">:</td>
                <td class="value">{{ $handover->deliverer_position ?? '....................................' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">III. PIHAK PENERIMA</div>
        <table class="info-grid">
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td class="value">{{ $handover->recipient_name ?? '....................................' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="separator">:</td>
                <td class="value">{{ $handover->recipient_position ?? '....................................' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">IV. SAKSI / MENGETAHUI (ICT STAFF)</div>
        <table class="info-grid">
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td class="value">{{ $handover->witness_name ?? '....................................' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="separator">:</td>
                <td class="value">{{ $handover->witness_position ?? '....................................' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">V. PERNYATAAN</div>
        <p style="text-align: justify; margin-bottom: 15px;">
            Dengan ini menyatakan bahwa asset tersebut di atas telah diserahkan dan diterima dengan baik dalam kondisi yang sesuai. 
            Penyerahan dan penerimaan ini dilakukan dengan sadar dan tanpa paksaan dari pihak manapun.
        </p>
    </div>

    <div class="signature-section">
        <div class="section-title">VI. TANDA TANGAN</div>
        <table class="signature-grid">
            <tr>
                <td>
                    <p>PIHAK PENYERAH</p>
                    <p>(HRGA)</p>
                    <div class="signature-space"></div>
                    <p class="underline">{{ $handover->deliverer_name ?? '....................................' }}</p>
                    <p>{{ $handover->deliverer_position ?? '....................................' }}</p>
                </td>
                <td>
                    <p>SAKSI</p>
                    <p>(ICT STAFF)</p>
                    <div class="signature-space"></div>
                    <p class="underline">{{ $handover->witness_name ?? '....................................' }}</p>
                    <p>{{ $handover->witness_position ?? '....................................' }}</p>
                </td>
                <td>
                    <p>PIHAK PENERIMA</p>
                    <div class="signature-space"></div>
                    <p class="underline">{{ $handover->recipient_name ?? '....................................' }}</p>
                    <p>{{ $handover->recipient_position ?? '....................................' }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis pada {{ now()->format('d F Y H:i') }}</p>
        <p>Berita Acara Serah Terima Asset - {{ $ictRequest->form_number }}</p>
    </div>
</body>
</html>
