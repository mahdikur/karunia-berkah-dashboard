<x-app-layout>
    <x-slot name="title">Detail PO - {{ $purchaseOrder->po_number }}</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>{{ $purchaseOrder->po_number }}</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">PO</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</a>
            @if($purchaseOrder->status === 'approved')
                <a href="{{ route('delivery-notes.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-outline-success"><i class="bi bi-truck me-1"></i>Buat Surat Jalan</a>
                @if(!$purchaseOrder->invoice)
                <a href="{{ route('invoices.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-outline-info"><i class="bi bi-receipt me-1"></i>Buat Invoice</a>
                @endif
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 text-center">{!! $purchaseOrder->status_badge !!}</div>
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tr><td class="text-muted">Client</td><td class="fw-semibold">{{ $purchaseOrder->client->name }}</td></tr>
                        <tr><td class="text-muted">Tgl PO</td><td>{{ $purchaseOrder->po_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Tgl Kirim</td><td>{{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dibuat oleh</td><td>{{ $purchaseOrder->creator->name ?? '-' }}</td></tr>
                        @if($purchaseOrder->approver)
                            <tr><td class="text-muted">Approved oleh</td><td>{{ $purchaseOrder->approver->name }}</td></tr>
                            <tr><td class="text-muted">Tgl Approve</td><td>{{ $purchaseOrder->approved_at?->format('d/m/Y H:i') }}</td></tr>
                        @endif
                        @if($purchaseOrder->rejected_reason)
                            <tr><td class="text-muted">Alasan Tolak</td><td class="text-danger">{{ $purchaseOrder->rejected_reason }}</td></tr>
                        @endif
                        @if($purchaseOrder->notes)
                            <tr><td class="text-muted">Catatan</td><td>{{ $purchaseOrder->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Approval Actions --}}
            @if(auth()->user()->isSuperadmin() && $purchaseOrder->status === 'pending_approval')
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark"><i class="bi bi-exclamation-circle me-2"></i>Approval</div>
                <div class="card-body">
                    <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve PO ini?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </form>
                    <button class="btn btn-outline-danger w-100" data-bs-toggle="collapse" data-bs-target="#rejectForm"><i class="bi bi-x-lg me-1"></i>Reject</button>
                    <div class="collapse mt-2" id="rejectForm">
                        <form action="{{ route('purchase-orders.reject', $purchaseOrder) }}" method="POST">
                            @csrf
                            <textarea class="form-control mb-2" name="rejected_reason" placeholder="Alasan penolakan..." required></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Konfirmasi Reject</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- Modal Allocation --}}
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-wallet-fill me-2"></i>Alokasi Modal</div>
                <div class="card-body">
                    @if($purchaseOrder->modalAllocations->count())
                    <table class="table table-sm mb-3" style="font-size: 12px;">
                        <thead><tr><th>Modal</th><th>Jumlah</th></tr></thead>
                        <tbody>
                            @foreach($purchaseOrder->modalAllocations as $alloc)
                            <tr>
                                <td><a href="{{ route('modals.show', $alloc->modal) }}">{{ $alloc->modal->modal_number }}</a></td>
                                <td class="fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($alloc->allocated_amount) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td class="fw-bold">Total Alokasi</td>
                                <td class="fw-bold">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->modalAllocations->sum('allocated_amount')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p class="text-muted small mb-3">Belum ada alokasi modal.</p>
                    @endif

                    @if($availableModals->count())
                    <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="collapse" data-bs-target="#setModalForm"><i class="bi bi-plus-lg me-1"></i>Tambah Alokasi Modal</button>
                    <div class="collapse mt-2" id="setModalForm">
                        <form action="{{ route('purchase-orders.set-modal', $purchaseOrder) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Pilih Modal</label>
                                <select class="form-select form-select-sm" name="modal_id" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach($availableModals as $modal)
                                    <option value="{{ $modal->id }}">{{ $modal->modal_number }} (Sisa: {{ \App\Helpers\FormatHelper::rupiah($modal->remaining_amount) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Jumlah Alokasi</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="allocated_amount" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">Simpan Alokasi</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Expenses --}}
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-cash-stack me-2"></i>Pengeluaran PO</div>
                <div class="card-body">
                    @if($purchaseOrder->expenses->count())
                    <table class="table table-sm mb-3" style="font-size: 12px;">
                        <thead><tr><th>Kategori</th><th>Jumlah</th><th>Tgl</th></tr></thead>
                        <tbody>
                            @foreach($purchaseOrder->expenses as $exp)
                            <tr>
                                <td>{{ $exp->category }}</td>
                                <td class="fw-semibold text-danger">{{ \App\Helpers\FormatHelper::rupiah($exp->amount) }}</td>
                                <td>{{ $exp->expense_date->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td class="fw-bold">Total</td>
                                <td class="fw-bold text-danger">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->expenses->sum('amount')) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p class="text-muted small mb-3">Belum ada pengeluaran.</p>
                    @endif

                    <button class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="collapse" data-bs-target="#addExpenseForm"><i class="bi bi-plus-lg me-1"></i>Tambah Pengeluaran</button>
                    <div class="collapse mt-2" id="addExpenseForm">
                        <form action="{{ route('purchase-orders.add-expense', $purchaseOrder) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Kategori</label>
                                <select class="form-select form-select-sm" name="category" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Belanja bahan">Belanja bahan</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Tenaga kerja">Tenaga kerja</option>
                                    <option value="Kemasan">Kemasan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Jumlah</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="expense_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Deskripsi</label>
                                <input type="text" class="form-control form-control-sm" name="description" placeholder="Opsional">
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Simpan Pengeluaran</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Daftar Item</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th width="80">Qty</th>
                                    <th>Satuan</th>
                                    <th>Harga Beli</th>
                                    <th>Subtotal (B)</th>
                                    <th>Harga Jual</th>
                                    <th>Subtotal (J)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $i => $poItem)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $poItem->item->name ?? '-' }}</td>
                                        <td>{{ number_format($poItem->quantity, 2) }}</td>
                                        <td>{{ $poItem->unit }}</td>
                                        <td class="text-muted">{{ \App\Helpers\FormatHelper::rupiah($poItem->purchase_price) }}</td>
                                        <td class="text-muted">{{ \App\Helpers\FormatHelper::rupiah($poItem->quantity * $poItem->purchase_price) }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::rupiah($poItem->selling_price) }}</td>
                                        <td class="fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($poItem->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end text-muted">Total (Beli):</td>
                                    <td class="text-muted">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->total_purchase) }}</td>
                                    <td class="text-end fw-bold">Total (Jual):</td>
                                    <td class="fw-bold">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->total_amount) }}</td>
                                </tr>
                                @if(auth()->user()->isSuperadmin())
                                <tr class="table-success">
                                    <td colspan="7" class="text-end fw-bold">Margin / Profit Estimate:</td>
                                    <td class="fw-bold">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->total_amount - $purchaseOrder->total_purchase) }}</td>
                                </tr>
                                @if($purchaseOrder->expenses->count())
                                <tr class="table-danger">
                                    <td colspan="7" class="text-end fw-bold">Total Pengeluaran:</td>
                                    <td class="fw-bold text-danger">- {{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->expenses->sum('amount')) }}</td>
                                </tr>
                                <tr class="table-info">
                                    <td colspan="7" class="text-end fw-bold">Net Profit:</td>
                                    <td class="fw-bold">{{ \App\Helpers\FormatHelper::rupiah(($purchaseOrder->total_amount - $purchaseOrder->total_purchase) - $purchaseOrder->expenses->sum('amount')) }}</td>
                                </tr>
                                @endif
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Delivery Notes --}}
            @if($purchaseOrder->deliveryNotes->count())
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-truck me-2"></i>Surat Jalan</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0"><thead><tr><th>No. SJ</th><th>Tanggal</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                        @foreach($purchaseOrder->deliveryNotes as $dn)
                            <tr>
                                <td><a href="{{ route('delivery-notes.show', $dn) }}">{{ $dn->dn_number }}</a></td>
                                <td>{{ $dn->dn_date->format('d/m/Y') }}</td>
                                <td>{{ ucfirst($dn->delivery_type) }}</td>
                                <td>{!! $dn->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('return-notes.create', ['dn_id' => $dn->id]) }}" class="btn btn-sm btn-outline-danger" title="Buat Retur"><i class="bi bi-arrow-return-left me-1"></i>Retur</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody></table>
                </div>
            </div>
            @endif

            {{-- Return Notes --}}
            @if($purchaseOrder->returnNotes->count())
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-arrow-return-left me-2"></i>Retur</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0"><thead><tr><th>No. Retur</th><th>Tgl Retur</th><th>No. SJ</th><th>Status</th><th></th></tr></thead><tbody>
                        @foreach($purchaseOrder->returnNotes as $rn)
                            <tr>
                                <td><a href="{{ route('return-notes.show', $rn) }}">{{ $rn->return_number }}</a></td>
                                <td>{{ $rn->return_date->format('d/m/Y') }}</td>
                                <td>{{ $rn->deliveryNote->dn_number ?? '-' }}</td>
                                <td>{!! $rn->status_badge !!}</td>
                                <td><a href="{{ route('return-notes.show', $rn) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody></table>
                </div>
            </div>
            @endif

            {{-- Invoice --}}
            @if($purchaseOrder->invoice)
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-receipt me-2"></i>Invoice</div>
                <div class="card-body">
                    <a href="{{ route('invoices.show', $purchaseOrder->invoice) }}" class="fw-semibold">{{ $purchaseOrder->invoice->invoice_number }}</a>
                    <span class="ms-2">{!! $purchaseOrder->invoice->status_badge !!}</span>
                    <span class="ms-2">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->invoice->total_amount) }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
