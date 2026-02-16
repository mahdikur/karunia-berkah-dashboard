<x-app-layout>
    <x-slot name="title">Edit Item</x-slot>
    <div class="page-header">
        <h1>Edit Item</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('items.index') }}">Items</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>
    <div class="row"><div class="col-lg-8">
        <div class="card"><div class="card-body">
            <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Item <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $item->code) }}" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Item <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $item->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="unit" value="{{ old('unit', $item->unit) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="2">{{ old('description', $item->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Foto</label>
                    @if($item->photo)
                        <div class="mb-2"><img src="{{ Storage::url($item->photo) }}" class="rounded" style="max-height: 100px;"></div>
                    @endif
                    <input type="file" class="form-control" name="photo" accept="image/*">
                    <div class="form-text">Kosongkan jika tidak ingin mengubah foto.</div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div></div>
    </div></div>
</x-app-layout>
