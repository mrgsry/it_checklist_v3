@extends('layouts.admin')

@section('title', 'Activity Monitor')
@section('page-title', 'Activity Monitor')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="card-title mb-1"><i class="fas fa-filter text-primary me-2"></i>Filter Activity Monitor</h5>
                <p class="text-muted small mb-0">Cari dan filter aktivitas yang ingin ditampilkan.</p>
            </div>
            @if(request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('admin.activity-monitor') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-rotate-left me-1"></i>Reset</a>
            @endif
        </div>
        <form method="GET" class="row g-2">
            <div class="col-lg-5 col-md-6"><label for="search" class="form-label small mb-1">Cari aktivitas</label><input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="User, request, aktivitas, kategori, atau form..."></div>
            <div class="col-lg-3 col-md-3"><label for="type" class="form-label small mb-1">Jenis</label><select id="type" name="type" class="form-select"><option value="">Semua jenis</option><option value="daily_activity" @selected(request('type') === 'daily_activity')>Daily Activity</option><option value="ticketing" @selected(request('type') === 'ticketing')>Ticketing</option><option value="submission" @selected(request('type') === 'submission')>Submit Form</option></select></div>
            <div class="col-lg-2 col-md-3"><label for="status" class="form-label small mb-1">Status</label><select id="status" name="status" class="form-select"><option value="">Semua status</option><option value="completed" @selected(request('status') === 'completed')>Selesai</option><option value="in_progress" @selected(request('status') === 'in_progress')>Dalam Proses</option><option value="blocked" @selected(request('status') === 'blocked')>Terhambat</option><option value="submitted" @selected(request('status') === 'submitted')>Submitted</option></select></div>
            <div class="col-lg-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Cari</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0" id="activity-monitor-panel">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">Aktivitas User Saat Ini</h5>
                <p class="text-muted small mb-0">Daily Activity dan Submit Form terbaru</p>
            </div>
            <span class="badge text-bg-light">{{ $activities->total() }} aktivitas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>User Request</th>
                        <th>Aktivitas Terakhir</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Waktu Pembaruan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $item)
                        @php($statusLabel = ['completed' => 'Selesai', 'in_progress' => 'Dalam Proses', 'blocked' => 'Terhambat', 'submitted' => 'Submitted'][$item['status']] ?? $item['status'])
                        @php($statusColor = ['completed' => 'success', 'in_progress' => 'warning', 'blocked' => 'danger', 'submitted' => 'info'][$item['status']] ?? 'secondary')
                        <tr data-activity-id="{{ $item['id'] }}">
                            <td class="fw-semibold">{{ $item['user'] }}</td>
                            <td>{{ $item['user_request'] ?? '-' }}</td>
                            <td>{{ $item['activity'] }}</td>
                            <td><span class="badge text-bg-info">{{ $item['type'] }}</span></td>
                            <td>{{ $item['category'] ?? '-' }}</td>
                            <td><span class="badge text-bg-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                            <td>@if($item['ticket_url'])<a href="{{ $item['ticket_url'] }}" target="_blank" rel="noopener" title="Lihat progress ticket"><i class="fas fa-chart-line"></i></a>@else{{ $item['updated_label'] }}@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())<div class="mt-4">{{ $activities->links() }}</div>@endif
    </div>
</div>
@endsection
