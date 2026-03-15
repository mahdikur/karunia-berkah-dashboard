<x-app-layout>
    <x-slot name="title">Detail Batch: {{ $invoiceBatch->batch_name }}</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>{{ $invoiceBatch->batch_name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoice</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('invoice-batches.index') }}">Batch Invoice</a></li>
                    <li class="breadcrumb-item active">{{ $invoiceBatch->batch_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoice-batches.print', $invoiceBatch) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-printer me-1"></i>Cetak
            </a>
            @if($invoiceBatch->status !== 'paid')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPay">
                <i class="bi bi-cash-coin me-1"></i>Bayar Batch
            </button>
            @endif
            @if((float)$invoiceBatch->paid_amount == 0)
            <form action="{{ route('invoice-batches.destroy', $invoiceBatch) }}" method="POST"
                  onsubmit="return confirm('Hapus batch ini? Invoice akan kembali tersedia untuk batch lain.');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-3 mb-3">
        {{-- Info Batch --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-info-circle me-2"></i>Informasi Batch</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="150">No. Batch</td>
                            <td class="fw-semibold">{{ $invoiceBatch->batch_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Batch</td>
                            <td class="fw-bold">{{ $invoiceBatch->batch_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Client</td>
                            <td>{{ $invoiceBatch->client->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>{!! $invoiceBatch->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat oleh</td>
                            <td>{{ $invoiceBatch->creator->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tgl Dibuat</td>
                            <td>{{ $invoiceBatch->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($invoiceBatch->notes)
                        <tr>
                            <td class="text-muted">Catatan</td>
                            <td>{{ $invoiceBatch->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Summary Keuangan --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>Ringkasan Keuangan</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Total Invoice</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($invoiceBatch->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Diskon</td>
                            <td class="text-end text-danger">- Rp {{ number_format($invoiceBatch->total_discount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">Grand Total</td>
                            <td class="text-end fw-bold fs-5 text-primary">Rp {{ number_format($invoiceBatch->grand_total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sudah Dibayar</td>
                            <td class="text-end text-success fw-semibold">Rp {{ number_format($invoiceBatch->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">Sisa Tagihan</td>
                            <td class="text-end fw-bold fs-5 {{ $invoiceBatch->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($invoiceBatch->remaining_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>

                    @if($invoiceBatch->grand_total > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Progress Pembayaran</span>
                            <span>{{ number_format(($invoiceBatch->paid_amount / $invoiceBatch->grand_total) * 100, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ min(100, ($invoiceBatch->paid_amount / $invoiceBatch->grand_total) * 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Invoice dalam Batch --}}
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-receipt me-2"></i>Invoice dalam Batch ({{ $invoiceBatch->items->count() }} invoice)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No. Invoice</th>
                            <th>No. PO</th>
                            <th>Tgl Invoice</th>
                            <th class="text-end">Total Invoice</th>
                            <th class="text-end">Diskon Batch</th>
                            <th class="text-end">Nett</th>
                            <th>Status Invoice</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoiceBatch->items as $i => $batchItem)
                            @php $inv = $batchItem->invoice; @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                                <td>{{ $inv->purchaseOrder->po_number ?? '-' }}</td>
                                <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                <td class="text-end">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">
                                    @if($batchItem->discount_amount > 0)
                                        - Rp {{ number_format($batchItem->discount_amount, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">Rp {{ number_format($batchItem->nett_amount, 0, ',', '.') }}</td>
                                <td>{!! $inv->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-info" title="Detail Invoice">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total:</td>
                            <td class="text-end">Rp {{ number_format($invoiceBatch->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">- Rp {{ number_format($invoiceBatch->total_discount, 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($invoiceBatch->grand_total, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Bayar --}}
    @if($invoiceBatch->status !== 'paid')
    <div class="modal fade" id="modalPay" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Bayar Batch: {{ $invoiceBatch->batch_name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('invoice-batches.pay', $invoiceBatch) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Sisa tagihan:</strong> Rp {{ number_format($invoiceBatch->remaining_amount, 0, ',', '.') }}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pembayaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="amount"
                                       value="{{ $invoiceBatch->remaining_amount }}" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select class="form-select" name="payment_method">
                                <option value="transfer">Transfer Bank</option>
                                <option value="cash">Cash</option>
                                <option value="giro">Giro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Referensi</label>
                            <input type="text" class="form-control" name="reference_number" placeholder="Opsional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Opsional"></textarea>
                        </div>
                        <div class="alert alert-warning small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Pembayaran akan otomatis terdistribusi ke <strong>{{ $invoiceBatch->items->count() }} invoice</strong> satuan secara berurutan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Konfirmasi Bayar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</x-app-layout>
