<x-app-layout>
    <x-slot name="title">Detail Pengeluaran</x-slot>
    <div class="page-header"><h1>{{ $expense->expense_number }}</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Pengeluaran</a></li><li class="breadcrumb-item active">Detail</li></ol></nav></div>
    <div class="row"><div class="col-lg-6">
        <div class="card"><div class="card-body">
            <table class="table table-borderless" style="font-size: 13px;">
                <tr><td class="text-muted">Kategori</td><td><span class="badge bg-light text-dark">{{ $expense->category }}</span></td></tr>
                <tr><td class="text-muted">Jumlah</td><td class="fw-bold text-danger fs-5">{{ \App\Helpers\FormatHelper::rupiah($expense->amount) }}</td></tr>
                <tr><td class="text-muted">Tanggal</td><td>{{ $expense->expense_date->format('d/m/Y') }}</td></tr>
                <tr><td class="text-muted">Deskripsi</td><td>{{ $expense->description ?? '-' }}</td></tr>
                <tr><td class="text-muted">Dibuat oleh</td><td>{{ $expense->creator->name ?? '-' }}</td></tr>
            </table>
            @if($expense->receipt_file)
                <hr>
                <h6>Bukti / Receipt</h6>
                @if(Str::endsWith($expense->receipt_file, '.pdf'))
                    <a href="{{ Storage::url($expense->receipt_file) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-pdf me-1"></i>Lihat PDF</a>
                @else
                    <img src="{{ Storage::url($expense->receipt_file) }}" class="rounded img-fluid" style="max-height: 300px;">
                @endif
            @endif
        </div></div>
    </div></div>
</x-app-layout>
