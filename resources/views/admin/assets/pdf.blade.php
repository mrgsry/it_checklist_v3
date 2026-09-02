<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Asset Inventory</title>
    <style>
        @page { margin: 12mm 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1f2937; }
        h1 { color: #163b68; font-size: 18px; margin: 0 0 4px; }
        p { color: #64748b; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1d4f86; color: #fff; font-weight: bold; text-align: left; }
        th, td { border: 1px solid #dbe3ed; padding: 5px; vertical-align: top; }
        tr:nth-child(even) { background: #f7fafe; }
        .empty { color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <h1>Asset Inventory</h1>
    <p>Diekspor {{ now()->format('d/m/Y H:i') }} | {{ $assets->count() }} asset</p>
    <table>
        <thead><tr><th>Kategori</th><th>Nama</th><th>Merk</th><th>Type</th><th>Kode Barang</th><th>No Invent</th><th>Serial Number</th><th>Tahun</th><th>Jumlah</th><th>Lokasi</th><th>Keterangan</th></tr></thead>
        <tbody>
        @forelse($assets as $asset)
            <tr><td>{{ $asset->category?->name ?? '-' }}</td><td>{{ $asset->name }}</td><td>{{ $asset->brand }}</td><td>{{ $asset->type }}</td><td>{{ $asset->item_code }}</td><td>{{ $asset->inventory_number }}</td><td>{{ $asset->serial_number }}</td><td>{{ $asset->purchase_year }}</td><td>{{ $asset->quantity }}</td><td>{{ $asset->location }}</td><td>{{ $asset->description ?: '-' }}</td></tr>
        @empty
            <tr><td class="empty" colspan="11">Belum ada asset sesuai filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>