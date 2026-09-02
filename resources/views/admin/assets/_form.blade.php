@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="asset_category_id" class="form-label fw-semibold">Kategori Aset</label>
        <select id="asset_category_id" name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('asset_category_id', $asset->asset_category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('asset_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="name" class="form-label fw-semibold">Nama</label>
        <input id="name" type="text" name="name" maxlength="255" value="{{ old('name', $asset->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="purchase_year" class="form-label fw-semibold">Tahun Pembelian</label>
        <input id="purchase_year" type="number" name="purchase_year" min="1900" max="{{ now()->year }}" value="{{ old('purchase_year', $asset->purchase_year ?? '') }}" class="form-control @error('purchase_year') is-invalid @enderror" required>
        @error('purchase_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="brand" class="form-label fw-semibold">Merk</label>
        <input id="brand" type="text" name="brand" maxlength="100" value="{{ old('brand', $asset->brand ?? '') }}" class="form-control @error('brand') is-invalid @enderror" required>
        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="type" class="form-label fw-semibold">Type</label>
        <input id="type" type="text" name="type" maxlength="100" value="{{ old('type', $asset->type ?? '') }}" class="form-control @error('type') is-invalid @enderror" required>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="item_code" class="form-label fw-semibold">Kode Barang</label>
        <input id="item_code" type="text" name="item_code" maxlength="100" value="{{ old('item_code', $asset->item_code ?? '') }}" class="form-control @error('item_code') is-invalid @enderror" required>
        @error('item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="inventory_number" class="form-label fw-semibold">No Invent</label>
        <input id="inventory_number" type="text" name="inventory_number" maxlength="100" value="{{ old('inventory_number', $asset->inventory_number ?? '') }}" class="form-control @error('inventory_number') is-invalid @enderror" required>
        @error('inventory_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="serial_number" class="form-label fw-semibold">SN</label>
        <input id="serial_number" type="text" name="serial_number" maxlength="150" value="{{ old('serial_number', $asset->serial_number ?? '') }}" class="form-control @error('serial_number') is-invalid @enderror" required>
        @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="quantity" class="form-label fw-semibold">Jumlah</label>
        <input id="quantity" type="number" name="quantity" min="1" value="{{ old('quantity', $asset->quantity ?? 1) }}" class="form-control @error('quantity') is-invalid @enderror" required>
        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label for="location" class="form-label fw-semibold">Lokasi</label>
        <input id="location" type="text" name="location" maxlength="255" value="{{ old('location', $asset->location ?? '') }}" class="form-control @error('location') is-invalid @enderror" required>
        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Keterangan</label>
        <textarea id="description" name="description" rows="3" maxlength="5000" class="form-control @error('description') is-invalid @enderror">{{ old('description', $asset->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ $submitLabel }}</button>
    <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Batal</a>
</div>