<x-app-layout>
    <x-slot name="title">Daftar Batch Invoice</x-slot>
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
        <a href="{{ route('invoice-batches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Buat Batch Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari batch number / nama...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="client_id">
                        <option value="">Semua Client</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>No. Batch</th>
                            <th>Nama Batch</th>
                            <th>Client</th>
                            <th class="text-center">Jml Invoice</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            <tr>
                                <td>{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}</td>
                                <td><a href="{{ route('invoice-batches.show', $batch) }}" class="fw-semibold text-decoration-none">{{ $batch->batch_number }}</a></td>
                                <td>{{ $batch->batch_name }}</td>
                                <td>{{ $batch->client->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $batch->items_count ?? $batch->items()->count() }}</span>
                                </td>
                                <td class="text-end fw-semibold">Rp {{ number_format($batch->grand_total, 0, ',', '.') }}</td>
                                <td class="text-end text-success">Rp {{ number_format($batch->paid_amount, 0, ',', '.') }}</td>
                                <td class="text-end {{ $batch->remaining_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                    Rp {{ number_format($batch->remaining_amount, 0, ',', '.') }}
                                </td>
                                <td>{!! $batch->status_badge !!}</td>
                                <td>
                                    <small>{{ $batch->creator->name ?? '-' }}</small><br>
                                    <small class="text-muted">{{ $batch->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('invoice-batches.show', $batch) }}" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('invoice-batches.print', $batch) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak"><i class="bi bi-printer"></i></a>
                                        @if($batch->status !== 'paid')
                                        <button class="btn btn-sm btn-outline-success" title="Bayar"
                                            data-bs-toggle="modal" data-bs-target="#modalPay{{ $batch->id }}">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Bayar --}}
                            @if($batch->status !== 'paid')
                            <div class="modal fade" id="modalPay{{ $batch->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Bayar Batch: {{ $batch->batch_name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('invoice-batches.pay', $batch) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <strong>Sisa tagihan:</strong> Rp {{ number_format($batch->remaining_amount, 0, ',', '.') }}
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
                                                               value="{{ $batch->remaining_amount }}" min="0.01" step="0.01" required>
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
                                                    <i class="bi bi-info-circle me-1"></i>Pembayaran akan otomatis terdistribusi ke invoice satuan.
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

                        @empty
                            <tr><td colspan="11" class="text-center py-4 text-muted">Belum ada batch invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($batches->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $batches->withQueryString()->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</x-app-layout>
