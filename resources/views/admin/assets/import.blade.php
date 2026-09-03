@extends('layouts.admin')

@section('title', 'Import Asset')
@section('page-title', 'Batch Import Asset')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0"><i class="fas fa-file-import text-primary me-2"></i>Batch Import Asset</h5>
            <a href="{{ route('admin.assets.import.template') }}" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Download Template</a>
        </div>
        @if($errors->any())
            <div class="alert alert-danger"><strong>Import dibatalkan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>Gunakan template agar nama kolom sesuai. Kategori harus sudah tersedia di master kategori. Jika ada satu baris yang tidak valid, seluruh batch tidak akan disimpan.</div>
        <form method="POST" action="{{ route('admin.assets.import') }}" enctype="multipart/form-data">
            @csrf
            <label for="file" class="form-label fw-semibold">File CSV / XLS / XLSX</label>
            <input id="file" type="file" name="file" accept=".csv,.xls,.xlsx" class="form-control @error('file') is-invalid @enderror" required>
            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Maksimal 10 MB dan 5.000 baris data.</div>
            <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Import Asset</button><a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Batal</a></div>
        </form>
    </div>
</div>
@endsection