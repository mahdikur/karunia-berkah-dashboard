<x-app-layout>
    <x-slot name="title">Batch Invoice</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Batch Invoice</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoice</a></li>
                    <li class="breadcrumb-item active">Batch Invoice</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-building me-2"></i>Pilih Client</div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Client <span class="text-danger">*</span></label>
                    <select class="form-select" id="clientSelect">
                        <option value="">-- Pilih Client --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" id="btnLoadInvoices">
                        <i class="bi bi-search me-1"></i>Tampilkan Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="invoiceSection" style="display: none;">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>Daftar Invoice</span>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkAll">
                    <label class="form-check-label" for="checkAll">Pilih Semua</label>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">
                                    <i class="bi bi-check2-square"></i>
                                </th>
                                <th>No. Invoice</th>
                                <th>No. PO</th>
                                <th class="text-center">Total Item</th>
                                <th>Tanggal PO</th>
                                <th class="text-end">Total Invoice</th>
                                <th class="text-end" style="width: 160px;">Diskon (Rp)</th>
                                <th class="text-end">Nett</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceTableBody">
                            <tr id="loadingRow">
                                <td colspan="9" class="text-center py-4 text-muted">Pilih client terlebih dahulu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="terbilang-box p-3 rounded" style="background: #e8f5e9; border: 1px dashed #2e7d32;" id="terbilangBox">
                            <strong>Terbilang:</strong>
                            <span id="terbilangText" class="fst-italic">-</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-end text-muted">Total Invoice Terpilih:</td>
                                <td class="text-end fw-semibold" style="width: 200px;" id="totalInvoiceDisplay">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-end text-muted">Total Diskon:</td>
                                <td class="text-end text-danger" id="totalDiscountDisplay">- Rp 0</td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-end fw-bold fs-6">Grand Total:</td>
                                <td class="text-end fw-bold fs-5 text-success" id="grandTotalDisplay">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="button" class="btn btn-success btn-lg" id="btnBatchPrint">
                <i class="bi bi-printer me-2"></i>Cetak Batch Invoice
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-lg">Kembali</a>
        </div>
    </div>

    @push('scripts')
    <script>
        const invoicesData = {};
        let allInvoices = [];

        function formatRupiah(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
        }

        function terbilang(nilai) {
            nilai = Math.abs(Math.round(nilai));
            const huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

            if (nilai < 12) return " " + huruf[nilai];
            if (nilai < 20) return terbilang(nilai - 10) + " belas";
            if (nilai < 100) return terbilang(Math.floor(nilai / 10)) + " puluh" + terbilang(nilai % 10);
            if (nilai < 200) return " seratus" + terbilang(nilai - 100);
            if (nilai < 1000) return terbilang(Math.floor(nilai / 100)) + " ratus" + terbilang(nilai % 100);
            if (nilai < 2000) return " seribu" + terbilang(nilai - 1000);
            if (nilai < 1000000) return terbilang(Math.floor(nilai / 1000)) + " ribu" + terbilang(nilai % 1000);
            if (nilai < 1000000000) return terbilang(Math.floor(nilai / 1000000)) + " juta" + terbilang(nilai % 1000000);
            if (nilai < 1000000000000) return terbilang(Math.floor(nilai / 1000000000)) + " milyar" + terbilang(nilai % 1000000000);
            if (nilai < 1000000000000000) return terbilang(Math.floor(nilai / 1000000000000)) + " trilyun" + terbilang(nilai % 1000000000000);
            return "";
        }

        function capitalize(str) {
            return str.trim().replace(/\b\w/g, l => l.toUpperCase());
        }

        function recalcTotals() {
            let totalInvoice = 0;
            let totalDiscount = 0;

            document.querySelectorAll('.invoice-checkbox:checked').forEach(cb => {
                const id = cb.value;
                const inv = allInvoices.find(i => i.id == id);
                if (!inv) return;

                const amount = parseFloat(inv.total_amount) || 0;
                const discountInput = document.getElementById('discount_' + id);
                const discount = parseFloat(discountInput?.value) || 0;
                const nett = amount - discount;

                totalInvoice += amount;
                totalDiscount += discount;

                const nettCell = document.getElementById('nett_' + id);
                if (nettCell) nettCell.textContent = formatRupiah(nett);
            });

            // Reset nett for unchecked
            document.querySelectorAll('.invoice-checkbox:not(:checked)').forEach(cb => {
                const id = cb.value;
                const nettCell = document.getElementById('nett_' + id);
                if (nettCell) nettCell.textContent = '-';
            });

            const grandTotal = totalInvoice - totalDiscount;

            document.getElementById('totalInvoiceDisplay').textContent = formatRupiah(totalInvoice);
            document.getElementById('totalDiscountDisplay').textContent = '- ' + formatRupiah(totalDiscount);
            document.getElementById('grandTotalDisplay').textContent = formatRupiah(grandTotal);

            if (grandTotal > 0) {
                document.getElementById('terbilangText').textContent = '# ' + capitalize(terbilang(grandTotal)) + ' Rupiah #';
            } else {
                document.getElementById('terbilangText').textContent = '-';
            }
        }

        function renderInvoices(invoices) {
            allInvoices = invoices;
            const tbody = document.getElementById('invoiceTableBody');

            if (invoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada invoice yang belum lunas untuk client ini.</td></tr>';
                return;
            }

            let html = '';
            invoices.forEach(inv => {
                const statusBadge = {
                    'unpaid': '<span class="badge bg-warning text-dark">Unpaid</span>',
                    'partial': '<span class="badge bg-info">Partial</span>',
                    'overdue': '<span class="badge bg-danger">Overdue</span>',
                }[inv.status] || '<span class="badge bg-secondary">' + inv.status + '</span>';

                html += `<tr>
                    <td class="text-center">
                        <input class="form-check-input invoice-checkbox" type="checkbox" value="${inv.id}" id="inv_${inv.id}">
                    </td>
                    <td><strong>${inv.invoice_number}</strong></td>
                    <td>${inv.po_number || '-'}</td>
                    <td class="text-center">${inv.total_items}</td>
                    <td>${inv.po_date || '-'}</td>
                    <td class="text-end">${formatRupiah(inv.total_amount)}</td>
                    <td class="text-end">
                        <input type="number" class="form-control form-control-sm text-end discount-input"
                            id="discount_${inv.id}" value="0" min="0" step="1000"
                            data-invoice-id="${inv.id}" style="width: 140px; margin-left: auto;">
                    </td>
                    <td class="text-end fw-semibold" id="nett_${inv.id}">-</td>
                    <td>${statusBadge}</td>
                </tr>`;
            });

            tbody.innerHTML = html;

            // Bind events
            document.querySelectorAll('.invoice-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const discInput = document.getElementById('discount_' + this.value);
                    if (discInput) {
                        discInput.disabled = !this.checked;
                        if (!this.checked) discInput.value = 0;
                    }
                    recalcTotals();
                    updateCheckAll();
                });
            });

            document.querySelectorAll('.discount-input').forEach(input => {
                input.disabled = true;
                input.addEventListener('input', recalcTotals);
            });

            recalcTotals();
        }

        function updateCheckAll() {
            const all = document.querySelectorAll('.invoice-checkbox');
            const checked = document.querySelectorAll('.invoice-checkbox:checked');
            document.getElementById('checkAll').checked = all.length > 0 && all.length === checked.length;
            document.getElementById('checkAll').indeterminate = checked.length > 0 && checked.length < all.length;
        }

        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('.invoice-checkbox').forEach(cb => {
                cb.checked = this.checked;
                const discInput = document.getElementById('discount_' + cb.value);
                if (discInput) {
                    discInput.disabled = !this.checked;
                    if (!this.checked) discInput.value = 0;
                }
            });
            recalcTotals();
        });

        document.getElementById('btnLoadInvoices').addEventListener('click', function() {
            const clientId = document.getElementById('clientSelect').value;
            if (!clientId) {
                alert('Silakan pilih client terlebih dahulu.');
                return;
            }

            const tbody = document.getElementById('invoiceTableBody');
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat...</td></tr>';
            document.getElementById('invoiceSection').style.display = 'block';

            fetch(`{{ url('invoices/batch/invoices') }}?client_id=${clientId}`)
                .then(r => r.json())
                .then(data => renderInvoices(data))
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Gagal memuat data invoice.</td></tr>';
                    console.error(err);
                });
        });

        document.getElementById('btnBatchPrint').addEventListener('click', function() {
            const selected = document.querySelectorAll('.invoice-checkbox:checked');
            if (selected.length === 0) {
                alert('Pilih minimal 1 invoice untuk dicetak.');
                return;
            }

            const params = new URLSearchParams();
            const clientId = document.getElementById('clientSelect').value;
            params.append('client_id', clientId);

            selected.forEach(cb => {
                params.append('invoice_ids[]', cb.value);
                const discInput = document.getElementById('discount_' + cb.value);
                const discount = parseFloat(discInput?.value) || 0;
                params.append('discounts[' + cb.value + ']', discount);
            });

            window.open(`{{ route('invoices.batch-print') }}?${params.toString()}`, '_blank');
        });
    </script>
    @endpush
</x-app-layout>
