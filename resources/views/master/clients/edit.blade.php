<x-app-layout>
    <x-slot name="title">Edit Client</x-slot>
    <div class="page-header">
        <h1>Edit Client</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Client</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>
    <div class="row"><div class="col-lg-8">
        <div class="card"><div class="card-body">
            <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kode Client <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $client->code) }}" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $client->name) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" name="address" rows="2">{{ old('address', $client->address) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $client->email) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $client->phone) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama PIC</label>
                        <input type="text" class="form-control" name="pic_name" value="{{ old('pic_name', $client->pic_name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon PIC</label>
                        <input type="text" class="form-control" name="pic_phone" value="{{ old('pic_phone', $client->pic_phone) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NPWP</label>
                        <input type="text" class="form-control" name="npwp" value="{{ old('npwp', $client->npwp) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Termin Pembayaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="payment_terms" value="{{ old('payment_terms', $client->payment_terms) }}" min="0" required>
                            <span class="input-group-text">hari</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Credit Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="credit_limit" value="{{ old('credit_limit', $client->credit_limit) }}" min="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    @if($client->logo)
                        <div class="mb-2"><img src="{{ Storage::url($client->logo) }}" class="rounded" style="max-height: 60px;"></div>
                    @endif
                    <label class="form-label">Logo</label>
                    <input type="file" class="form-control" name="logo" accept="image/*">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $client->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div></div>
    </div></div>
</x-app-layout>
