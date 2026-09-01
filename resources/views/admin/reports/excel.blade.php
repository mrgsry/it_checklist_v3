<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Laporan Aktivitas</title></head>
<body>
    <table>
        <thead><tr><th colspan="8">Laporan Daily &amp; Weekly Activity</th></tr><tr><th>Jenis</th><th>Kategori</th><th>Tanggal</th><th>Form / Aktivitas</th><th>User / Staff</th><th>Status</th><th>Catatan / Jawaban</th><th>Diperbarui / Waktu</th></tr></thead>
        <tbody>
        @forelse($dailyActivities as $activity)
            @php($status = ['completed' => 'Selesai', 'in_progress' => 'Dalam Proses', 'blocked' => 'Terhambat'][$activity->status] ?? $activity->status)
            <tr><td>{{ $activity->type === 'ticketing' ? 'Ticketing' : 'Daily Activity' }}</td><td>{{ $activity->category }}</td><td>{{ $activity->activity_date?->format('d/m/Y') }}</td><td>{{ $activity->activity }}{{ $activity->ticket_url ? ' - '.$activity->ticket_url : '' }}</td><td>{{ $activity->user?->name ?? '-' }}</td><td>{{ $status }}</td><td>{{ $activity->notes ?: '-' }}</td><td>{{ $activity->updated_at?->format('d/m/Y H:i') }}</td></tr>
        @empty
        @endforelse
        @forelse($submissions as $submission)
            @php($flagged = $submission->answers->where('is_flagged', true)->count())
            <tr><td>Checklist</td><td>-</td><td>{{ $submission->submission_date?->format('d/m/Y') }}</td><td>{{ $submission->form?->title ?? '-' }}</td><td>{{ $submission->submitter?->name ?? '-' }}</td><td>{{ $flagged ? $flagged.' masalah' : 'Selesai' }}</td><td>@foreach($submission->answers as $answer)<strong>{{ $answer->formItem?->label ?? 'Item' }}</strong>@if($answer->formItem?->field_type === 'checkbox' && filled($answer->formItem?->options))@php($checkboxStatuses = $answer->checkboxStatuses())<br>@foreach($answer->formItem->options as $option){{ $option }} — Normal {{ ($checkboxStatuses[$option] ?? null) === 'normal' ? '☑' : '☐' }} | Tidak Normal {{ ($checkboxStatuses[$option] ?? null) === 'tidak_normal' ? '☑' : '☐' }}@if(!$loop->last)<br>@endif @endforeach @else: {{ $answer->formItem?->field_type === 'photo' ? count($answer->photoPaths()).' foto' : ($answer->answer_value ?: '-') }}@endif @if(!$loop->last)<br><br>@endif @endforeach</td><td>{{ $submission->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td></tr>
        @empty
        @endforelse
        @if($dailyActivities->isEmpty() && $submissions->isEmpty())<tr><td colspan="8">Belum ada data sesuai filter.</td></tr>@endif
        </tbody>
    </table>
</body>
</html>