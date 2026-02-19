<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $deliveryNote->dn_number }}</title>
    <style>
        body { font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 12px; line-height: 1.2; color: #000; }
        .container { max-width: 800px; margin: 0 auto; padding: 10px; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 80px; margin-right: 15px; }
        .company-info { flex: 1; }
        .company-info h1 { margin: 0; font-size: 20px; color: #2e7d32; }
        .company-info p { margin: 2px 0; font-size: 11px; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 24px; color: #2e7d32; }
        .doc-details { margin-top: 5px; font-size: 11px; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px; }
        .info-box { flex: 1; border: 1px solid #2e7d32; padding: 8px; border-radius: 4px; }
        .info-box h3 { font-size: 12px; margin: 0 0 5px; border-bottom: 1px solid #2e7d32; padding-bottom: 3px; color: #2e7d32; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        .table th { background: #2e7d32; color: #fff; padding: 5px; text-align: left; border: 1px solid #1b5e20; }
        .table td { border: 1px solid #ccc; padding: 4px 5px; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; text-align: center; font-size: 11px; }
        .signature-box { width: 150px; }
        .signature-line { margin-top: 65px; border-top: 1px solid #000; }
        .special-instruction { border: 1px dashed #2e7d32; padding: 8px; margin-bottom: 20px; font-size: 11px; background: #e8f5e9; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
            .table th { background: #2e7d32 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
            .special-instruction { background: #e8f5e9 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <img src="{{ asset('Karunia Berkah.png') }}" class="logo" alt="Logo">
            <div class="company-info">
                <h1>KB SUPPLIER</h1>
                <p>Perum. Pesona Cilebut 2, Cilebut Barat, Bogor</p>
                <p>Tel: 083811930011 / 088213234992 | Email: kb.suplier@gmail.com</p>
            </div>
            <div class="doc-title">
                <h2>SURAT JALAN</h2>
                <div class="doc-details">
                    <div>No: <strong>{{ $deliveryNote->dn_number }}</strong></div>
                    <div>Tgl: {{ $deliveryNote->dn_date->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Title section removed as it is now in header -->

        <div class="info-grid">
            <div class="info-box">
                <h3>Kepada Yth:</h3>
                <p><strong>{{ $deliveryNote->client->name }}</strong><br>
                {{ $deliveryNote->client->address ?? 'Alamat tidak tersedia' }}<br>
                Telp: {{ $deliveryNote->client->phone ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h3>Referensi:</h3>
                <p>No PO: {{ $deliveryNote->purchaseOrder->po_number }}<br>
                Tipe Pengiriman: {{ ucfirst($deliveryNote->delivery_type) }}</p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Nama Barang</th>
                    <th style="width: 100px; text-align: right;">Qty</th>
                    <th style="width: 80px; text-align: center;">Satuan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->item->name }} ({{ $item->item->code }})</td>
                    <td style="text-align: right;">{{ number_format($item->quantity_delivered, 2) }}</td>
                    <td style="text-align: center;">{{ $item->unit }}</td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($deliveryNote->notes)
            <div style="margin-bottom: 20px;">
                <strong>Catatan:</strong> {{ $deliveryNote->notes }}
            </div>
        @endif

        <div class="special-instruction" style="border: 1px solid #000; padding: 10px; margin-bottom: 30px; font-size: 12px;">
            <strong>Instruksi Khusus:</strong><br>
            Penerima harus memeriksa semua item saat pengiriman tiba dan melaporkan kerusakan jika ada, dalam waktu 3 jam.
        </div>

        <div class="footer">
            <div class="signature-box">
                <p>Penerima</p>
                <div class="signature-line">( .................... )</div>
            </div>
            <div class="signature-box">
                <p>Pengirim</p>
                <div class="signature-line">( .................... )</div>
            </div>
            <div class="signature-box">
                <p>Hormat Kami</p>
                <div class="signature-line">KB Supplier</div>
            </div>
        </div>
    </div>
</body>
</html>
