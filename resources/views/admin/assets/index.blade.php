@extends('layouts.admin')

@section('title', 'Asset')
@section('page-title', 'Manajemen Asset')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div><h5 class="card-title mb-1"><i class="fas fa-boxes-stacked text-primary me-2"></i>Daftar Asset</h5><p class="text-muted small mb-0">Kelola inventaris perangkat dan perlengkapan IT.</p></div>
            <div class="d-flex gap-2">
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.asset-categories.index') }}" class="btn btn-outline-secondary"><i class="fas fa-tags me-1"></i>Kategori</a>
                @endif
                <a href="{{ route('admin.assets.export-excel', request()->query()) }}" class="btn btn-outline-success" title="Export ke Excel"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="{{ route('admin.assets.import.template') }}" class="btn btn-outline-secondary" title="Download template import"><i class="fas fa-download me-1"></i>Template</a>
                <a href="{{ route('admin.assets.import.form') }}" class="btn btn-outline-primary"><i class="fas fa-file-import me-1"></i>Import</a>
                <a href="{{ route('admin.assets.export-pdf', request()->query()) }}" class="btn btn-outline-danger" title="Export ke PDF"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <a href="{{ route('admin.assets.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Tambah Asset</a>
            </div>
        </div>
        <form method="GET" class="row g-2">
            <div class="col-lg-4 col-md-6"><label for="search" class="form-label small mb-1">Cari asset</label><input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama, kode, SN, lokasi..."></div>
            <div class="col-lg-3 col-md-6"><label for="asset_category_id" class="form-label small mb-1">Kategori</label><select id="asset_category_id" name="asset_category_id" class="form-select"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('asset_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-4"><label for="purchase_year" class="form-label small mb-1">Tahun</label><input id="purchase_year" type="number" name="purchase_year" min="1900" max="{{ now()->year }}" value="{{ request('purchase_year') }}" class="form-control"></div>
            <div class="col-lg-3 col-md-8"><label for="location" class="form-label small mb-1">Lokasi</label><div class="d-flex gap-2"><input id="location" type="search" name="location" value="{{ request('location') }}" class="form-control" placeholder="Filter lokasi"><button type="submit" class="btn btn-primary" title="Filter asset"><i class="fas fa-filter"></i></button><a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-rotate-left"></i></a></div></div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Kategori</th><th>Nama</th><th>Merk / Type</th><th>Kode Barang</th><th>No Invent</th><th>SN</th><th>Tahun</th><th>Jumlah</th><th>Lokasi</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td><span class="badge text-bg-light">{{ $asset->category->name }}</span></td>
                            <td class="fw-semibold">{{ $asset->name }}</td>
                            <td>{{ $asset->brand }}<br><small class="text-muted">{{ $asset->type }}</small></td>
                            <td><code>{{ $asset->item_code }}</code></td>
                            <td><code>{{ $asset->inventory_number }}</code></td>
                            <td><code>{{ $asset->serial_number }}</code></td>
                            <td>{{ $asset->purchase_year }}</td>
                            <td>{{ $asset->quantity }}</td>
                            <td>{{ $asset->location }}</td>
                            <td>{{ $asset->description ?: '-' }}</td>
                            <td class="text-end"><div class="d-flex justify-content-end gap-1"><a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-sm btn-outline-info" title="Lihat detail" aria-label="Lihat detail"><i class="fas fa-eye"></i></a><a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-sm btn-outline-warning" title="Edit asset" aria-label="Edit asset"><i class="fas fa-edit"></i></a><form method="POST" action="{{ route('admin.assets.destroy', $asset) }}" onsubmit="return confirm('Yakin hapus asset ini?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus asset" aria-label="Hapus asset"><i class="fas fa-trash"></i></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-5"><i class="fas fa-box-open d-block fa-2x mb-2"></i>Belum ada asset yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())<div class="mt-4">{{ $assets->links() }}</div>@endif
    </div>
</div>
@endsection