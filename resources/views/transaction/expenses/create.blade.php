<x-app-layout>
    <x-slot name="title">Catat Pengeluaran</x-slot>
    <div class="page-header"><h1>Catat Pengeluaran</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Pengeluaran</a></li><li class="breadcrumb-item active">Catat</li></ol></nav></div>
    <div class="row"><div class="col-lg-6">
        <div class="card"><div class="card-body">
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" name="category" required>
                        <option value="">Pilih Kategori</option>
                        @foreach(\App\Models\Expense::CATEGORIES as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3"><label class="form-label">Jumlah <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required>@error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                <div class="mb-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" class="form-control" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required></div>
                <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea></div>
                <div class="mb-3"><label class="form-label">Bukti / Receipt</label><input type="file" class="form-control" name="receipt_file" accept="image/*,.pdf"><div class="form-text">JPG, PNG, atau PDF. Maks 5MB.</div></div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div></div>
    </div></div>
</x-app-layout>
