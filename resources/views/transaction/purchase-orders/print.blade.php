<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO - {{ $purchaseOrder->po_number }}</title>
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; font-size: 11px; }
        .signature-area { display: flex; justify-content: space-between; margin-top: 60px; }
        .signature-box { width: 150px; text-align: center; }
        .signature-line { margin-top: 60px; border-top: 1px solid #000; font-size: 11px; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
            .table th { background: #2e7d32 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
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
                <h2>PURCHASE ORDER</h2>
                <div class="doc-details">
                    <div>No: <strong>{{ $purchaseOrder->po_number }}</strong></div>
                    <div>Tgl: {{ $purchaseOrder->po_date->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Kepada Yth:</h3>
                <p><strong>{{ $purchaseOrder->client->name }}</strong><br>
                {{ $purchaseOrder->client->address ?? 'Alamat tidak tersedia' }}<br>
                Telp: {{ $purchaseOrder->client->phone ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h3>Informasi PO:</h3>
                <p>Tgl Pengiriman: {{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? '-' }}<br>
                Status: <strong>{{ ucfirst($purchaseOrder->status) }}</strong></p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Item</th>
                    <th style="width: 80px; text-align: right;">Qty</th>
                    <th style="width: 70px; text-align: center;">Satuan</th>
                    <th style="width: 110px; text-align: right;">Harga Beli</th>
                    <th style="width: 110px; text-align: right;">Harga Jual</th>
                    <th style="width: 110px; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item->name }} ({{ $item->item->code }})</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->selling_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->selling_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f5f5f5;">
                    <td colspan="6" class="text-right" style="border-top: 2px solid #000;"><strong>Total Harga Jual</strong></td>
                    <td class="text-right" style="border-top: 2px solid #000;">
                        <strong>Rp {{ number_format($purchaseOrder->items->sum(fn($i) => $i->quantity * $i->selling_price), 0, ',', '.') }}</strong>
                    </td>
                </tr>
                <tr style="background: #f5f5f5;">
                    <td colspan="6" class="text-right"><strong>Total Harga Beli</strong></td>
                    <td class="text-right">
                        <strong>Rp {{ number_format($purchaseOrder->items->sum(fn($i) => $i->quantity * $i->purchase_price), 0, ',', '.') }}</strong>
                    </td>
                </tr>
                <tr style="background: #d4edda;">
                    <td colspan="6" class="text-right"><strong>Estimasi Keuntungan</strong></td>
                    <td class="text-right">
                        <strong>Rp {{ number_format($purchaseOrder->items->sum(fn($i) => $i->quantity * ($i->selling_price - $i->purchase_price)), 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        @if($purchaseOrder->notes)
        <div style="margin-bottom: 20px;">
            <strong>Catatan:</strong><br>{{ $purchaseOrder->notes }}
        </div>
        @endif

        <div class="signature-area">
            <div class="signature-box">
                <p>Pembuat</p>
                <div class="signature-line">( {{ $purchaseOrder->creator->name ?? '.................' }} )</div>
            </div>
            @if($purchaseOrder->approver)
            <div class="signature-box">
                <p>Approved</p>
                <div class="signature-line">( {{ $purchaseOrder->approver->name ?? '.................' }} )</div>
            </div>
            @endif
            <div class="signature-box">
                <p>Hormat Kami</p>
                <div class="signature-line">KB Supplier</div>
            </div>
        </div>
    </div>
</body>
</html>
