<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daily Activity</title>
    <style>
        @page { margin: 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1f2937; }
        h1 { color: #163b68; margin: 0 0 4px; font-size: 18px; }
        p { color: #64748b; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1d4f86; color: white; padding: 6px; text-align: left; }
        td { border: 1px solid #dbe3ed; padding: 6px; vertical-align: top; }
        tr:nth-child(even) { background: #f7fafe; }
        .status { font-weight: bold; }
        .completed { color: #15803d; }.in_progress { color: #a16207; }.blocked { color: #b42318; }
    </style>
</head>
<body>
    <h1>Daily Activity Staff</h1>
    <p>Diekspor pada {{ now()->format('d/m/Y H:i') }} — {{ $activities->count() }} aktivitas sesuai filter.</p>
    <table>
        <thead><tr><th>Tanggal</th><th>Staff</th><th>Aktivitas</th><th>Status</th><th>Catatan</th><th>Penugasan</th><th>Diperbarui</th></tr></thead>
        <tbody>
        @forelse($activities as $activity)
            @php($status = ['completed' => 'Selesai', 'in_progress' => 'Dalam Proses', 'blocked' => 'Terhambat'][$activity->status] ?? $activity->status)
            <tr>
                <td>{{ $activity->activity_date?->format('d/m/Y') }}</td>
                <td>{{ $activity->user?->name ?? '-' }}</td>
                <td>{{ $activity->activity }}</td>
                <td class="status {{ $activity->status }}">{{ $status }}</td>
                <td>{{ $activity->notes ?: '-' }}</td>
                <td>{{ $activity->assigner?->name ?? 'Mandiri' }}</td>
                <td>{{ $activity->updated_at?->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada aktivitas sesuai filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>