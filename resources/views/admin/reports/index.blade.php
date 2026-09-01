@extends('layouts.admin')

@section('title', 'Laporan')
@section('page-title', 'Laporan Checklist')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div><h6 class="fw-bold mb-1"><i class="fas fa-filter me-2"></i>Filter Laporan</h6><small class="text-muted">Data hanya menampilkan checklist berstatus submitted.</small></div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo me-1"></i>Reset</a>
        </div>
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="filter_form_id" class="form-label small">Form</label>
                <select id="filter_form_id" name="form_id" class="form-select">
                    <option value="">Semua Form</option>
                    @foreach($forms as $f)
                    <option value="{{ $f->id }}" {{ request('form_id') == $f->id ? 'selected' : '' }}>{{ $f->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_user_id" class="form-label small">User</label>
                <select id="filter_user_id" name="user_id" class="form-select">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_date_from" class="form-label small">Dari Tanggal</label>
                <input type="date" id="filter_date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="filter_date_to" class="form-label small">Sampai Tanggal</label>
                <input type="date" id="filter_date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Tampilkan</button></div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Ringkasan Checklist</h6>
    <small class="text-muted">{{ request('date_from') ?: 'Semua tanggal' }}{{ request('date_to') ? ' - ' . request('date_to') : '' }}</small>
</div>
<div class="row g-3 mb-4" id="checklist-summary">
    @php
        $checklistMetrics = [
            ['label' => 'Total Submission', 'value' => $summaryStats['total'] ?? 0, 'caption' => 'Checklist submitted', 'icon' => 'fa-file-circle-check', 'color' => 'primary'],
            ['label' => 'Submission Bersih', 'value' => $summaryStats['clean'] ?? 0, 'caption' => ($summaryStats['flagged_rate'] ?? 0) . '% terindikasi masalah', 'icon' => 'fa-circle-check', 'color' => 'success'],
            ['label' => 'Submission Bermasalah', 'value' => $summaryStats['flagged'] ?? 0, 'caption' => ($summaryStats['flagged_answers'] ?? 0) . ' jawaban perlu perhatian', 'icon' => 'fa-triangle-exclamation', 'color' => 'danger'],
            ['label' => 'Kelengkapan Jawaban', 'value' => ($summaryStats['completion_rate'] ?? 0) . '%', 'caption' => ($summaryStats['answers'] ?? 0) . ' dari ' . ($summaryStats['expected_answers'] ?? 0) . ' jawaban', 'icon' => 'fa-list-check', 'color' => 'warning'],
        ];
    @endphp
    @foreach($checklistMetrics as $metric)
    <div class="col-6 col-xl-3">
        <div class="card h-100 p-3 border-start border-4 border-{{ $metric['color'] }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-muted mb-1">{{ $metric['label'] }}</div>
                    <h3 class="fw-bold text-{{ $metric['color'] }} mb-1">{{ $metric['value'] }}</h3>
                    <small class="text-muted">{{ $metric['caption'] }}</small>
                </div>
                <i class="fas {{ $metric['icon'] }} text-{{ $metric['color'] }} fs-4"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0"><i class="fas fa-person-running text-primary me-2"></i>Ringkasan Daily Activity</h6>
    <small class="text-muted" id="daily-activity-total-text">{{ $summaryStats['daily_total'] ?? 0 }} aktivitas tercatat</small>
</div>
<div class="row g-3 mb-4" id="daily-activity-summary">
    <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-success"><div class="small text-muted">Selesai</div><h4 class="fw-bold text-success mb-0">{{ $summaryStats['daily_completed'] ?? 0 }}</h4><small class="text-muted">Aktivitas tuntas</small></div></div>
    <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-warning"><div class="small text-muted">Dalam Proses</div><h4 class="fw-bold text-warning mb-0">{{ $summaryStats['daily_in_progress'] ?? 0 }}</h4><small class="text-muted">Masih dikerjakan</small></div></div>
    <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-danger"><div class="small text-muted">Terhambat</div><h4 class="fw-bold text-danger mb-0">{{ $summaryStats['daily_blocked'] ?? 0 }}</h4><small class="text-muted">Membutuhkan tindak lanjut</small></div></div>
    <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-info"><div class="small text-muted">Tingkat Penyelesaian</div><h4 class="fw-bold text-info mb-0">{{ $summaryStats['daily_completion_rate'] ?? 0 }}%</h4><small class="text-muted">Dari seluruh aktivitas</small></div></div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-1"><i class="fas fa-list-ul text-primary me-2"></i>Detail Daily Activity</h6>
                <small class="text-muted">Aktivitas harian staff pada rentang filter yang dipilih.</small>
            </div>
            <div class="d-flex gap-2" id="daily-activity-badges">
                <span class="badge text-bg-success">Selesai {{ $summaryStats['daily_completed'] ?? 0 }}</span>
                <span class="badge text-bg-warning">Proses {{ $summaryStats['daily_in_progress'] ?? 0 }}</span>
                <span class="badge text-bg-danger">Terhambat {{ $summaryStats['daily_blocked'] ?? 0 }}</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Staff</th>
                        <th>Aktivitas</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Penugasan</th>
                        <th>Diperbarui</th>
                    </tr>
                </thead>
                <tbody id="daily-activity-table-body">
                    @forelse($dailyActivities as $activity)
                    @php
                        $statusText = match($activity->status) {
                            'completed' => 'Selesai',
                            'in_progress' => 'Pending',
                            'blocked' => 'Terhambat',
                            default => $activity->status,
                        };
                        $statusClass = match($activity->status) {
                            'completed' => 'chip-status-done',
                            'in_progress' => 'chip-status-pending',
                            'blocked' => 'chip-status-archived',
                            default => 'chip-status-archived',
                        };
                    @endphp
                    <tr>
                        <td>{{ $activity->activity_date?->format('d/m/Y') }}</td>
                        <td class="fw-semibold">{{ $activity->user?->name ?? '-' }}</td>
                        <td>{{ $activity->activity }}</td>
                        <td>
                            <span class="chip {{ $statusClass }}">
                                <span class="chip-buffering-label">{{ $statusText }}</span>
                            </span>
                        </td>
                        <td class="text-muted">{{ $activity->notes ?: '-' }}</td>
                        <td>{{ $activity->assigner?->name ?? 'Mandiri' }}</td>
                        <td>{{ $activity->updated_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada daily activity sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Rekap Per Form</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Form</th>
                                <th>Submission</th>
                                <th>Masalah</th>
                                <th>Jawaban Terisi</th>
                                <th>Lengkap</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($formSummary as $formId => $stats)
                            <tr>
                                <td>{{ $forms->firstWhere('id', $formId)?->title ?? '-' }}</td>
                                <td>{{ $stats['total'] ?? 0 }}</td>
                                <td class="text-danger">{{ $stats['flagged'] ?? 0 }}</td>
                                <td>{{ $stats['answers'] ?? 0 }}</td>
                                <td>{{ $stats['completion_rate'] ?? 0 }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Rekap Per User</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Submission</th>
                                <th>Masalah</th>
                                <th>Jawaban Terisi</th>
                                <th>Kelengkapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userSummary as $userId => $stats)
                            <tr>
                                <td>{{ $users->firstWhere('id', $userId)?->name ?? '-' }}</td>
                                <td>{{ $stats['total'] ?? 0 }}</td>
                                <td class="text-danger">{{ $stats['flagged'] ?? 0 }}</td>
                                <td>{{ $stats['answers'] ?? 0 }}</td>
                                <td>{{ $stats['completion_rate'] ?? 0 }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="card-title mb-1">Detail Submission</h6>
                <small class="text-muted">{{ $submissions->count() }} data sesuai filter</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export-pdf', request()->query()) }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf me-1"></i>Export PDF</a>
                <a href="{{ route('admin.reports.export-excel', request()->query()) }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
            </div>
        </div>

        @if($submissions->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>User</th>
                        <th>Tanggal / Waktu</th>
                        @if($selectedForm)
                        @foreach($selectedForm->items as $item)
                        <th class="small">{{ $item->label }}</th>
                        @endforeach
                        @endif
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                    @php
                        $flagged = $sub->answers->where('is_flagged', true)->count();
                    @endphp
                    <tr class="{{ $flagged > 0 ? 'flagged-row' : '' }}">
                        <td>{{ $sub->form->title ?? '-' }}</td>
                        <td>{{ $sub->submitter->name ?? '-' }}</td>
                        <td>{{ $sub->submission_date?->format('d/m/Y') }}<br><small class="text-muted">{{ $sub->submitted_at?->format('H:i') ?? '-' }}</small></td>
                        @if($selectedForm)
                        @foreach($selectedForm->items as $item)
                        @php
                            $ans = $sub->answers->firstWhere('form_item_id', $item->id);
                        @endphp
                        <td class="{{ $ans?->is_flagged ? 'flagged-cell' : '' }}">
                            @if($item->field_type === 'photo')
                                {{ $ans?->photoPaths() ? count($ans->photoPaths()).' foto' : '-' }}
                            @else
                                {{ $ans?->answer_value ?? '-' }}
                            @endif
                        </td>
                        @endforeach
                        @endif
                        <td>
                            @if($flagged > 0)
                            <span class="badge bg-danger">{{ $flagged }} masalah</span>
                            @else
                            <span class="badge bg-success">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4 text-muted">
            <i class="fas fa-inbox fa-2x mb-2"></i>
            <p>Tidak ada data untuk ditampilkan. Pilih filter untuk melihat laporan.</p>
        </div>
        @endif
    </div>
</div>

<script>
(() => {
    let isPageVisible = true;
    let isUserIdle = false;
    let idleTimeout;

    document.addEventListener('visibilitychange', () => {
        isPageVisible = !document.hidden;
    });

    function resetIdleTimer() {
        clearTimeout(idleTimeout);
        isUserIdle = false;
        idleTimeout = setTimeout(() => {
            isUserIdle = true;
        }, 120000);
    }

    document.addEventListener('mousemove', resetIdleTimer);
    document.addEventListener('keydown', resetIdleTimer);
    document.addEventListener('click', resetIdleTimer);
    resetIdleTimer();

    const dataUrl = @json(route('admin.reports.data', request()->query()));
    const tableBody = document.getElementById('daily-activity-table-body');
    const totalText = document.getElementById('daily-activity-total-text');
    const summaryWrapper = document.getElementById('daily-activity-summary');
    const badgeWrapper = document.getElementById('daily-activity-badges');

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&',
        '<': '<',
        '>': '>',
        "'": '&#039;',
        '"': '"',
    }[character]));

    const statusMeta = (status) => {
        const map = {
            completed: ['chip-status-done', 'Selesai'],
            in_progress: ['chip-status-pending', 'Pending'],
            blocked: ['chip-status-archived', 'Terhambat'],
        };
        return map[status] ?? ['chip-status-archived', status];
    };

    const renderRow = (activity) => {
        const [statusClass, label] = statusMeta(activity.status);
        return `
            <tr>
                <td>${escapeHtml(activity.date)}</td>
                <td class="fw-semibold">${escapeHtml(activity.staff)}</td>
                <td>${escapeHtml(activity.activity)}</td>
                <td><span class="chip ${statusClass}"><span class="chip-buffering-label">${escapeHtml(label)}</span></span></td>
                <td class="text-muted">${escapeHtml(activity.notes)}</td>
                <td>${escapeHtml(activity.assignee)}</td>
                <td>${escapeHtml(activity.updated_at)}</td>
            </tr>
        `;
    };

    const renderSummary = (summary) => {
        totalText.textContent = `${summary.daily_total ?? 0} aktivitas tercatat`;
        summaryWrapper.innerHTML = `
            <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-success"><div class="small text-muted">Selesai</div><h4 class="fw-bold text-success mb-0">${summary.daily_completed ?? 0}</h4><small class="text-muted">Aktivitas tuntas</small></div></div>
            <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-warning"><div class="small text-muted">Dalam Proses</div><h4 class="fw-bold text-warning mb-0">${summary.daily_in_progress ?? 0}</h4><small class="text-muted">Masih dikerjakan</small></div></div>
            <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-danger"><div class="small text-muted">Terhambat</div><h4 class="fw-bold text-danger mb-0">${summary.daily_blocked ?? 0}</h4><small class="text-muted">Membutuhkan tindak lanjut</small></div></div>
            <div class="col-6 col-xl-3"><div class="card p-3 border-start border-4 border-info"><div class="small text-muted">Tingkat Penyelesaian</div><h4 class="fw-bold text-info mb-0">${summary.daily_completion_rate ?? 0}%</h4><small class="text-muted">Dari seluruh aktivitas</small></div></div>
        `;
        if (badgeWrapper) {
            badgeWrapper.innerHTML = `
                <span class="badge text-bg-success">Selesai ${summary.daily_completed ?? 0}</span>
                <span class="badge text-bg-warning">Proses ${summary.daily_in_progress ?? 0}</span>
                <span class="badge text-bg-danger">Terhambat ${summary.daily_blocked ?? 0}</span>
            `;
        }
    };

    const renderActivities = (items) => {
        tableBody.innerHTML = items.length
            ? items.map(renderRow).join('')
            : `<tr><td colspan="7" class="text-center text-muted py-4">Belum ada daily activity sesuai filter.</td></tr>`;
    };

    const fetchData = async () => {
        if (!isPageVisible || isUserIdle) return;
        try {
            const response = await fetch(dataUrl);
            if (!response.ok) return;
            const payload = await response.json();
            renderSummary(payload.summaryStats || {});
            renderActivities(payload.dailyActivities || []);
        } catch (e) {
            console.error('Polling error:', e);
        }
    };

    setInterval(fetchData, 10000);
})();
</script>
@endsection