@extends('layouts.admin')

@section('title', 'Detail Asset')
@section('page-title', 'Detail Asset')

@section('content')
<div class="card shadow-sm border-0"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4"><h5 class="mb-0"><i class="fas fa-box text-primary me-2"></i>{{ $asset->name }}</h5><div class="d-flex gap-2"><a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i>Edit</a><a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali</a></div></div>
    <div class="row g-3">
        @foreach([['Kategori', $asset->category->name], ['Nama', $asset->name], ['Tahun Pembelian', $asset->purchase_year], ['Merk', $asset->brand], ['Type', $asset->type], ['Kode Barang', $asset->item_code], ['No Invent', $asset->inventory_number], ['SN', $asset->serial_number], ['Jumlah', $asset->quantity], ['Lokasi', $asset->location], ['Keterangan', $asset->description ?: '-']] as [$label, $value])
            <div class="col-md-6"><div class="border rounded p-3 h-100"><div class="text-muted small">{{ $label }}</div><div class="fw-semibold mt-1">{{ $value }}</div></div></div>
        @endforeach
    </div>
</div></div>
@endsection