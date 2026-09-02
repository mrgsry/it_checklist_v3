<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Asset Inventory</title></head>
<body>
    <table>
        <thead><tr><th colspan="11">Asset Inventory</th></tr><tr><th>Kategori</th><th>Nama</th><th>Merk</th><th>Type</th><th>Kode Barang</th><th>No Invent</th><th>Serial Number</th><th>Tahun Pembelian</th><th>Jumlah</th><th>Lokasi</th><th>Keterangan</th></tr></thead>
        <tbody>
        @forelse($assets as $asset)
            <tr><td>{{ $asset->category?->name ?? '-' }}</td><td>{{ $asset->name }}</td><td>{{ $asset->brand }}</td><td>{{ $asset->type }}</td><td>{{ $asset->item_code }}</td><td>{{ $asset->inventory_number }}</td><td>{{ $asset->serial_number }}</td><td>{{ $asset->purchase_year }}</td><td>{{ $asset->quantity }}</td><td>{{ $asset->location }}</td><td>{{ $asset->description ?: '-' }}</td></tr>
        @empty
            <tr><td colspan="11">Belum ada asset sesuai filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>