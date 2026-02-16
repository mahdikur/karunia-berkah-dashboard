<x-app-layout>
    <x-slot name="title">Catat Pembayaran</x-slot>
    <div class="page-header">
        <h1>Catat Pembayaran</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Pembayaran</a></li><li class="breadcrumb-item active">Catat</li></ol></nav>
    </div>
    <div class="row"><div class="col-lg-6">
        <div class="card"><div class="card-body">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Invoice <span class="text-danger">*</span></label>
                    <select class="form-select @error('invoice_id') is-invalid @enderror" name="invoice_id" id="invoiceSelect" required>
                        <option value="">Pilih Invoice</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}" {{ ($selectedInvoice?->id ?? old('invoice_id')) == $inv->id ? 'selected' : '' }}
                                data-remaining="{{ $inv->remaining_amount }}" data-client="{{ $inv->client->name }}">
                                {{ $inv->invoice_number }} - {{ $inv->client->name }} (Sisa: {{ \App\Helpers\FormatHelper::rupiah($inv->remaining_amount) }})
                            </option>
                        @endforeach
                    </select>
                    @error('invoice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="mt-1" id="remainingInfo" style="font-size: 12px;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" name="amount" id="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required>
                    </div>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Metode <span class="text-danger">*</span></label>
                    <select class="form-select" name="payment_method" required>
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Cash</option>
                        <option value="giro">Giro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Referensi</label>
                    <input type="text" class="form-control" name="reference_number" value="{{ old('reference_number') }}" placeholder="No. bukti transfer, giro, dll">
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div></div>
    </div></div>

    @push('scripts')
    <script>
        document.getElementById('invoiceSelect').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const rem = opt.dataset.remaining;
            const info = document.getElementById('remainingInfo');
            if (rem) {
                info.innerHTML = `<span class="text-danger">Sisa tagihan: <strong>Rp ${new Intl.NumberFormat('id-ID').format(rem)}</strong></span>`;
                document.getElementById('amount').max = rem;
            } else {
                info.innerHTML = '';
            }
        });
        document.getElementById('invoiceSelect').dispatchEvent(new Event('change'));
    </script>
    @endpush
</x-app-layout>
