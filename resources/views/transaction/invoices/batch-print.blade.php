<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Batch Print Invoice</title>
    <style>
        body { font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 12px; line-height: 1.2; color: #000; margin: 0; padding: 0; }
        .invoice-page { max-width: 800px; margin: 0 auto; padding: 10px; page-break-after: always; }
        .invoice-page:last-child { page-break-after: auto; }
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
        .terbilang { background: #e8f5e9; border: 1px dashed #2e7d32; padding: 5px 10px; margin: 10px 0; font-style: italic; font-weight: bold; }
        .payment-info { border: 1px solid #2e7d32; padding: 8px; width: 55%; border-radius: 4px; }
        .signature-box { width: 180px; text-align: center; }
        .signature-line { margin-top: 40px; border-top: 1px solid #000; }
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
        <h3>Batch Print - {{ $invoices->count() }} Invoice</h3>
        <p>Total Tagihan: <strong>Rp {{ number_format($invoices->sum('total_amount'), 0, ',', '.') }}</strong></p>
        <button onclick="window.print()" style="padding: 10px 30px; background: #2e7d32; color: #fff; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
            🖨️ Print Semua
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #999; color: #fff; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            ✕ Tutup
        </button>
    </div>

    @foreach($invoices as $invoice)
    <div class="invoice-page">
        <div class="header">
            <img src="{{ asset('Karunia Berkah.png') }}" class="logo" alt="Logo">
            <div class="company-info">
                <h1>KB SUPPLIER</h1>
                <p>Perum. Pesona Cilebut 2, Cilebut Barat, Bogor</p>
                <p>Tel: 083811930011 / 088213234992 | Email: kb.suplier@gmail.com</p>
            </div>
            <div class="doc-title">
                <h2>INVOICE</h2>
                <div class="doc-details">
                    <div>No: <strong>{{ $invoice->invoice_number }}</strong></div>
                    <div>Tgl: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    <div>Jatuh Tempo: {{ $invoice->due_date->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Tagihan Kepada:</h3>
                <p><strong>{{ $invoice->client->name }}</strong><br>
                {{ $invoice->client->address ?? 'Alamat tidak tersedia' }}<br>
                Telp: {{ $invoice->client->phone ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h3>Referensi:</h3>
                <p>No PO: {{ $invoice->purchaseOrder->po_number }}</p>
                @if($invoice->status !== 'unpaid')
                <p>Status: {{ ucfirst($invoice->status) }}</p>
                <p>Terbayar: Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</p>
                @endif
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Deskripsi</th>
                    <th style="width: 80px; text-align: right;">Qty</th>
                    <th style="width: 120px; text-align: right;">Harga Satuan</th>
                    <th style="width: 120px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Subtotal</strong></td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td colspan="4" class="text-right">Diskon</td>
                    <td class="text-right">-{{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td colspan="4" class="text-right">Pajak ({{ $invoice->tax_percentage }}%)</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="4" class="text-right"><strong>Total Tagihan</strong></td>
                    <td class="text-right" style="border-top: 2px solid #000;"><strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong></td>
                </tr>
                @if($invoice->remaining_amount != $invoice->total_amount)
                <tr>
                    <td colspan="4" class="text-right"><strong>Sisa Tagihan</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</strong></td>
                </tr>
                @endif
            </tfoot>
        </table>

        <div class="terbilang">
            Terbilang: # {{ ucwords(\App\Helpers\FormatHelper::terbilang($invoice->remaining_amount > 0 ? $invoice->remaining_amount : $invoice->total_amount)) }} Rupiah #
        </div>

        <div class="footer" style="display: flex; gap: 30px;">
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
                <p style="font-style: italic; margin-top: 15px;">* Mohon lakukan pembayaran sebelum tanggal jatuh tempo.</p>
            </div>
            <div style="width: 40%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;">
                <div class="signature-box">
                    <p style="margin-bottom: 60px;">Hormat Kami,</p>
                    <div class="signature-line">Finance KB Supplier</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
