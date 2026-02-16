<x-app-layout>
    <x-slot name="title">Tambah Modal</x-slot>
    <div class="page-header"><h1>Tambah Modal</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('modals.index') }}">Modal</a></li><li class="breadcrumb-item active">Tambah</li></ol></nav></div>
    <div class="row"><div class="col-lg-8">
        <div class="card"><div class="card-body">
            <form action="{{ route('modals.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Jumlah Modal <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" class="form-control @error('total_amount') is-invalid @enderror" name="total_amount" value="{{ old('total_amount') }}" min="0.01" step="0.01" required>@error('total_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" class="form-control" name="modal_date" value="{{ old('modal_date', date('Y-m-d')) }}" required></div>
                </div>
                <div class="mb-3"><label class="form-label">Catatan</label><textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea></div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    <a href="{{ route('modals.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div></div>
    </div></div>
</x-app-layout>
