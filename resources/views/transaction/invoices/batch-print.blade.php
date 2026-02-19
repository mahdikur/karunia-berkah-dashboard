<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Batch Invoice - {{ $client->name }}</title>
    <style>
        body { font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 12px; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .page { max-width: 800px; margin: 0 auto; padding: 15px; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 80px; margin-right: 15px; }
        .company-info { flex: 1; }
        .company-info h1 { margin: 0; font-size: 20px; color: #2e7d32; }
        .company-info p { margin: 2px 0; font-size: 11px; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 22px; color: #2e7d32; }
        .doc-details { margin-top: 5px; font-size: 11px; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px; }
        .info-box { flex: 1; border: 1px solid #2e7d32; padding: 8px; border-radius: 4px; }
        .info-box h3 { font-size: 12px; margin: 0 0 5px; border-bottom: 1px solid #2e7d32; padding-bottom: 3px; color: #2e7d32; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        .table th { background: #2e7d32; color: #fff; padding: 6px 8px; text-align: left; border: 1px solid #1b5e20; }
        .table td { border: 1px solid #ccc; padding: 5px 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .terbilang { background: #e8f5e9; border: 1px dashed #2e7d32; padding: 8px 12px; margin: 15px 0; font-style: italic; font-weight: bold; font-size: 12px; }
        .payment-info { border: 1px solid #2e7d32; padding: 8px; border-radius: 4px; }
        .signature-box { width: 180px; text-align: center; }
        .signature-line { margin-top: 40px; border-top: 1px solid #000; }
        .summary-table { width: 50%; margin-left: auto; }
        .summary-table td { padding: 4px 8px; font-size: 12px; }
        .no-print { text-align: center; padding: 20px; background: #f8f9fa; border-bottom: 2px solid #ddd; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
            .table th { background: #2e7d32 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
            .terbilang { background: #e8f5e9 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <h3>Batch Invoice - {{ $client->name }} ({{ $invoices->count() }} Invoice)</h3>
        <p>Grand Total: <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></p>
        <button onclick="window.print()" style="padding: 10px 30px; background: #2e7d32; color: #fff; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
            🖨️ Print
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #999; color: #fff; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            ✕ Tutup
        </button>
    </div>

    <div class="page">
        <div class="header">
            <img src="{{ asset('Karunia Berkah.png') }}" class="logo" alt="Logo">
            <div class="company-info">
                <h1>KB SUPPLIER</h1>
                <p>Perum. Pesona Cilebut 2, Cilebut Barat, Bogor</p>
                <p>Tel: 083811930011 / 088213234992 | Email: kb.suplier@gmail.com</p>
            </div>
            <div class="doc-title">
                <h2>BATCH INVOICE</h2>
                <div class="doc-details">
                    <div>Tanggal: <strong>{{ now()->format('d/m/Y') }}</strong></div>
                    <div>Jumlah Invoice: <strong>{{ $invoices->count() }}</strong></div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Tagihan Kepada:</h3>
                <p><strong>{{ $client->name }}</strong><br>
                {{ $client->address ?? 'Alamat tidak tersedia' }}<br>
                Telp: {{ $client->phone ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h3>Keterangan:</h3>
                <p>Penagihan gabungan {{ $invoices->count() }} invoice</p>
                <p>Periode: {{ $invoices->min('invoice_date')->format('d/m/Y') }} s/d {{ $invoices->max('invoice_date')->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Invoice Summary Table --}}
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">No</th>
                    <th>No. Invoice</th>
                    <th>No. PO</th>
                    <th class="text-center">Total Item</th>
                    <th class="text-center">Tanggal PO</th>
                    <th class="text-right">Total Invoice</th>
                    <th class="text-right">Diskon</th>
                    <th class="text-right">Nett</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $index => $invoice)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                    <td>{{ $invoice->purchaseOrder->po_number ?? '-' }}</td>
                    <td class="text-center">{{ number_format($invoice->purchaseOrder->items->sum('quantity'), 0) }}</td>
                    <td class="text-center">{{ $invoice->purchaseOrder->po_date ? $invoice->purchaseOrder->po_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $invoice->batch_discount > 0 ? number_format($invoice->batch_discount, 0, ',', '.') : '-' }}</td>
                    <td class="text-right"><strong>{{ number_format($invoice->batch_nett, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Totals --}}
        <table class="summary-table">
            <tr>
                <td class="text-right" style="color: #666;">Total Invoice:</td>
                <td class="text-right" style="width: 150px;">Rp {{ number_format($grandSubtotal, 0, ',', '.') }}</td>
            </tr>
            @if($grandDiscount > 0)
            <tr>
                <td class="text-right" style="color: #666;">Total Diskon:</td>
                <td class="text-right" style="color: red;">- Rp {{ number_format($grandDiscount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="border-top: 2px solid #000;">
                <td class="text-right"><strong style="font-size: 14px;">Grand Total:</strong></td>
                <td class="text-right"><strong style="font-size: 14px;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        <div class="terbilang">
            Terbilang: # {{ ucwords(\App\Helpers\FormatHelper::terbilang($grandTotal)) }} Rupiah #
        </div>

        <div style="display: flex; gap: 30px; margin-top: 20px;">
            <div class="payment-info" style="width: 60%; font-size: 13px;">
                <p style="font-weight: bold; margin-bottom: 10px;">Pembayaran dapat ditransfer ke:</p>
                <div style="display: grid; grid-template-columns: 100px 10px auto; margin-bottom: 5px;">
                    <div>Bank</div><div>:</div><div><strong>Mandiri</strong></div>
                    <div>Atas Nama</div><div>:</div><div>Mahdi Kurniadi</div>
                    <div>No. Rekening</div><div>:</div><div><strong>1330030505697</strong></div>
                </div>
                <div style="display: grid; grid-template-columns: 100px 10px auto; margin-top: 10px;">
                    <div>Bank</div><div>:</div><div><strong>BCA</strong></div>
                    <div>Atas Nama</div><div>:</div><div>Mahdi Kurniadi</div>
                    <div>No. Rekening</div><div>:</div><div><strong>7360885091</strong></div>
                </div>
                <p style="font-style: italic; margin-top: 15px;">* Mohon lakukan pembayaran sesuai total tagihan di atas.</p>
            </div>
            <div style="width: 40%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;">
                <div class="signature-box">
                    <p style="margin-bottom: 60px;">Hormat Kami,</p>
                    <div class="signature-line">Finance KB Supplier</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
