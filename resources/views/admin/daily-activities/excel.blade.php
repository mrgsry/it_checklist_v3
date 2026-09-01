<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Daily Activity</title></head>
<body>
    <table>
        <thead><tr><th colspan="7">Daily Activity Staff</th></tr><tr><th>Tanggal</th><th>Staff</th><th>Aktivitas</th><th>Status</th><th>Catatan</th><th>Penugasan</th><th>Diperbarui</th></tr></thead>
        <tbody>
        @forelse($activities as $activity)
            @php($status = ['completed' => 'Selesai', 'in_progress' => 'Dalam Proses', 'blocked' => 'Terhambat'][$activity->status] ?? $activity->status)
            <tr><td>{{ $activity->activity_date?->format('d/m/Y') }}</td><td>{{ $activity->user?->name ?? '-' }}</td><td>{{ $activity->activity }}</td><td>{{ $status }}</td><td>{{ $activity->notes ?: '-' }}</td><td>{{ $activity->assigner?->name ?? 'Mandiri' }}</td><td>{{ $activity->updated_at?->format('d/m/Y H:i') }}</td></tr>
        @empty
            <tr><td colspan="7">Belum ada aktivitas sesuai filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>