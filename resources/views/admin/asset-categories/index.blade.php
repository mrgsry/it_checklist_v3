@extends('layouts.admin')

@section('title', 'Kategori Asset')
@section('page-title', 'Kategori Asset')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4"><h5 class="card-title mb-0"><i class="fas fa-tags text-primary me-2"></i>Master Kategori</h5><a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali ke Asset</a></div>
        <form method="POST" action="{{ route('admin.asset-categories.store') }}" class="row g-2 mb-4">@csrf<div class="col-md-8"><label for="category_name" class="form-label">Nama kategori</label><input id="category_name" type="text" name="name" maxlength="100" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary" type="submit"><i class="fas fa-plus me-1"></i>Tambah Kategori</button></div></form>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nama Kategori</th><th>Jumlah Asset</th><th class="text-end">Aksi</th></tr></thead><tbody>@forelse($categories as $category)<tr><td><form method="POST" action="{{ route('admin.asset-categories.update', $category) }}" class="d-flex gap-2">@csrf @method('PUT')<input type="text" name="name" value="{{ $category->name }}" maxlength="100" class="form-control form-control-sm" required><button class="btn btn-sm btn-outline-warning" type="submit" title="Simpan perubahan" aria-label="Simpan perubahan"><i class="fas fa-save"></i></button></form></td><td>{{ $category->assets_count }}</td><td class="text-end"><form method="POST" action="{{ route('admin.asset-categories.destroy', $category) }}" onsubmit="return confirm('Yakin hapus kategori ini?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus kategori" aria-label="Hapus kategori" {{ $category->assets_count ? 'disabled' : '' }}><i class="fas fa-trash"></i></button></form></td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori.</td></tr>@endforelse</tbody></table></div>
    </div>
</div>
@endsection