@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'IT Checklist Dashboard')

@section('content')

@php($metrics = $dashboardMetrics ?? [])

{{-- Monitoring Cards --}}
<div class="row g-3 mb-4" id="metrics-cards-container">
    <div class="col-12 col-md-6 col-xl-3">
        <button type="button" class="card-createspace p-3 stat-card dashboard-detail-trigger text-start w-100 border-0" data-stat="forms" data-card-detail="forms" style="border-left: 4px solid #E11D48;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="text-caption mb-1">Submit Form Hari Ini</div>
                    <h3 class="mb-0 font-headline text-primary"><span data-metric="todaySubmissions">{{ $metrics['todaySubmissions'] ?? 0 }}</span> <small class="text-muted fs-6">/ <span data-metric="todayAssignments">{{ $metrics['todayAssignments'] ?? 0 }}</span></small></h3>
                    <small class="text-muted"><span data-metric="submissionProgress">{{ $metrics['submissionProgress'] ?? 0 }}</span>% selesai · <span data-metric="pendingSubmissions">{{ $metrics['pendingSubmissions'] ?? 0 }}</span> belum submit</small>
                </div>
                <i class="fas fa-wpforms fa-2x text-primary opacity-25 ms-2"></i>
            </div>
        </button>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <button type="button" class="card-createspace p-3 stat-card dashboard-detail-trigger text-start w-100 border-0" data-stat="daily" data-card-detail="daily" style="border-left: 4px solid #16A34A;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="text-caption mb-1">Daily Activity Hari Ini</div>
                    <h3 class="mb-0 font-headline text-success"><span data-metric="dailyActivityCompleted">{{ $metrics['dailyActivityCompleted'] ?? 0 }}</span> <small class="text-muted fs-6">/ <span data-metric="dailyActivityTotal">{{ $metrics['dailyActivityTotal'] ?? 0 }}</span></small></h3>
                    <small class="text-muted"><span data-metric="dailyActivityProgress">{{ $metrics['dailyActivityProgress'] ?? 0 }}</span>% selesai · <span data-metric="dailyActivityInProgress">{{ $metrics['dailyActivityInProgress'] ?? 0 }}</span> berjalan</small>
                </div>
                <i class="fas fa-inbox fa-2x text-success opacity-25 ms-2"></i>
            </div>
        </button>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <button type="button" class="card-createspace p-3 stat-card dashboard-detail-trigger text-start w-100 border-0" data-stat="compliance" data-card-detail="attention" style="border-left: 4px solid #2563EB;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="text-caption mb-1">Perlu Perhatian</div>
                    <h3 class="mb-0 font-headline text-info"><span data-metric="attentionCount">{{ $metrics['attentionCount'] ?? 0 }}</span></h3>
                    <small class="text-muted">Belum submit, blocked, atau issue</small>
                </div>
                <i class="fas fa-chart-line fa-2x text-info opacity-25 ms-2"></i>
            </div>
        </button>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <button type="button" class="card-createspace p-3 stat-card dashboard-detail-trigger text-start w-100 border-0" data-stat="issues" data-card-detail="issues" style="border-left: 4px solid #DC2626;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="text-caption mb-1">Daily Activity Terhambat</div>
                    <h3 class="mb-0 font-headline text-error"><span data-metric="dailyActivityBlocked">{{ $metrics['dailyActivityBlocked'] ?? 0 }}</span></h3>
                    <small class="text-muted">Issue submit form: {{ $issuesToday ?? 0 }}</small>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x text-error opacity-25 ms-2"></i>
            </div>
        </button>
    </div>
</div>

<div class="modal fade" id="dashboard-detail-modal" tabindex="-1" aria-labelledby="dashboard-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="dashboard-detail-title">Detail Dashboard</h5>
                    <p class="text-muted small mb-0" id="dashboard-detail-summary"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0" id="dashboard-detail-content"></div>
        </div>
    </div>
</div>

<div class="card mb-4" id="activity-monitor-panel">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">Aktivitas User Saat Ini</h5>
                <p class="text-muted small mb-0">Daily Activity dan Submit Form terbaru</p>
            </div>
            <span class="badge text-bg-light" id="activity-monitor-status">Memuat...</span>
        </div>
        <div class="table-responsive activity-monitor-scroll">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="sticky-top bg-white"><tr><th>User</th><th>User Request</th><th>Aktivitas Terakhir</th><th>Jenis</th><th>Kategori</th><th>Status</th><th>Waktu Pembaruan</th></tr></thead>
                <tbody id="activity-monitor-body">
                @forelse($activityFeed as $item)
                    <tr data-activity-id="{{ $item['id'] }}">
                        <td class="fw-semibold">{{ $item['user'] }}</td>
                        <td>{{ $item['user_request'] ?? '-' }}</td>
                        <td>{{ $item['activity'] }}</td>
                        <td><span class="badge text-bg-info">{{ $item['type'] }}</span></td>
                        <td>{{ $item['category'] ?? '-' }}</td>
                        <td>
                            @php($statusText = match($item['status']) {
                                'completed' => 'Selesai',
                                'in_progress' => 'Progress',
                                'pending' => 'Pending',
                                'blocked' => 'Terhambat',
                                default => $item['status'],
                            })
                            <span class="chip {{ in_array($item['status'], ['in_progress', 'pending']) ? 'chip-status-pending' : ($item['status'] === 'completed' ? 'chip-status-done' : 'chip-status-archived') }}">
                                <span class="chip-buffering-label">{{ $statusText }}</span>
                            </span>
                        </td>
                        <td>@if($item['ticket_url'] ?? null)<a href="{{ $item['ticket_url'] }}" target="_blank" rel="noopener" title="Lihat progress ticket"><i class="fas fa-chart-line"></i></a>@else{{ $item['updated_label'] }}@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas user.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row g-3 mb-4">
    {{-- Date Range Filter --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold mb-0 mb-md-0">Dashboard Analytics</h6>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end align-items-stretch align-items-md-center">
                            <select id="timeRange" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                                <option value="30">30 Hari Terakhir</option>
                                <option value="7" selected>7 Hari Terakhir</option>
                                <option value="90">90 Hari Terakhir</option>
                            </select>
                            <button id="refreshCharts" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> <span class="d-none d-sm-inline">Refresh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Charts Row --}}
    <div class="col-12 col-lg-8">
        <div class="card p-3 h-100">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
                <div>
                    <h6 class="fw-bold mb-0">Trend Daily Activity</h6>
                    <small class="text-muted">Menampilkan kurva aktivitas harian berdasarkan periode</small>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex">
                        <select name="trend_period" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="week" @selected(($trendPeriod ?? 'week') === 'week')>1 Minggu</option>
                            <option value="month" @selected(($trendPeriod ?? 'week') === 'month')>1 Bulan</option>
                            <option value="year" @selected(($trendPeriod ?? 'week') === 'year')>1 Tahun</option>
                        </select>
                    </form>
                    <div class="btn-group btn-group-sm w-100 w-sm-auto" role="group">
                        <button type="button" class="btn btn-outline-primary active flex-fill" data-chart-type="line">
                            <i class="fas fa-chart-line d-sm-none"></i> <span class="d-none d-sm-inline">Line</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary flex-fill" data-chart-type="bar">
                            <i class="fas fa-chart-bar d-sm-none"></i> <span class="d-none d-sm-inline">Bar</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary flex-fill" data-chart-type="area">
                            <i class="fas fa-chart-area d-sm-none"></i> <span class="d-none d-sm-inline">Area</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="submissionsTrendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-1">Daily Task per User</h6>
            <p class="text-muted small mb-2">Jumlah daily task yang ditangani tiap user</p>
            <div class="chart-container">
                <canvas id="statusPieChart" style="max-height: 250px;"></canvas>
            </div>
            <div class="mt-3">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                            <div class="small text-muted">Daily selesai</div>
                            <div class="fw-bold text-success">{{ $metrics['dailyActivityCompleted'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-warning bg-opacity-10 rounded">
                            <div class="small text-muted">Masih progress</div>
                            <div class="fw-bold text-warning">{{ $metrics['dailyActivityInProgress'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Charts Row --}}
    <div class="col-12 col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">Form Usage (Top 5)</h6>
            <div class="chart-container">
                <canvas id="formUsageChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">User Activity (Top 5)</h6>
            <div class="chart-container">
                <canvas id="userActivityChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Issues Chart --}}
    <div class="col-12">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Issues by Form</h6>
            <div class="chart-container">
                <canvas id="issuesByFormChart" style="max-height: 200px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Schedule Widget --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Form Mendatang
                    </h6>
                    <small class="text-muted">7 hari ke depan</small>
                </div>
                <div class="schedule-list" style="max-height: 400px; overflow-y: auto;">
                    @forelse($upcomingForms ?? [] as $schedule)
                    <div class="schedule-item d-flex align-items-center p-2 border-bottom">
                        <div class="schedule-icon me-3">
                            @if($schedule['is_today'])
                                <i class="fas fa-clock text-warning fa-lg"></i>
                            @elseif($schedule['is_overdue'])
                                <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                            @else
                                <i class="fas fa-calendar text-info fa-lg"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-truncate">{{ $schedule['form']->title }}</div>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>{{ $schedule['user']->name }}
                                <span class="ms-2">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    @if($schedule['is_today'])
                                        <span class="badge bg-warning text-dark">Hari Ini</span>
                                    @elseif($schedule['is_overdue'])
                                        <span class="badge bg-danger">Terlambat</span>
                                    @else
                                        {{ $schedule['date']->isoFormat('D MMM') }}
                                    @endif
                                </span>
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-check fa-2x mb-2 opacity-50"></i>
                        <div>Tidak ada jadwal form mendatang</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Submissions --}}
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Submission Terbaru</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="d-none d-md-table-header-group">
                            <tr>
                                <th>Form</th>
                                <th>User</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $recentSubmissionItems = $recentSubmissions ?? []; ?>
                            <?php if (count($recentSubmissionItems) > 0): ?>
                                <?php foreach ($recentSubmissionItems as $sub): ?>
                                    <?php $flagged = $sub->answers->where('is_flagged', true)->count(); ?>
                                    <tr>
                                        <td class="fw-semibold">{{ $sub->form->title ?? '-' }}</td>
                                        <td>{{ $sub->submitter->name ?? '-' }}</td>
                                        <td>{{ $sub->submission_date?->isoFormat('D MMM Y') }}</td>
                                        <td>
                                            <?php if ($flagged > 0): ?>
                                                <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>Ada Masalah</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Lengkap</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                        <div>Belum ada submission</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

{{-- Siapkan data PHP sebelum script --}}
<script>
(() => {
    let isPageVisible = true;
    let isUserIdle = false;
    let idleTimeout;
    let metricsInFlight = false;
    let monitorInFlight = false;
    const pollIntervalMs = 5000;

    document.addEventListener('visibilitychange', () => {
        isPageVisible = !document.hidden;
        if (isPageVisible) {
            refreshActivityMonitor();
            refreshMetrics();
        }
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

    const monitorBody = document.getElementById('activity-monitor-body');
    const monitorStatus = document.getElementById('activity-monitor-status');
    const monitorEndpoint = @json(route('admin.activity-monitor'));
    const cardDetailsEndpoint = @json(route('admin.dashboard.card-details', ['card' => '__CARD__']));
    let knownActivityIds = new Set([...monitorBody.querySelectorAll('[data-activity-id]')].map(row => row.dataset.activityId));

    const escapeMonitorValue = value => String(value ?? '').replace(/[&<>'"]/g, character => ({ '&': '&', '<': '<', '>': '>', "'": '&#039;', '"': '"' }[character]));
    const monitorStatusLabel = status => ({ submitted: 'Submitted', completed: 'Selesai', in_progress: 'Progress', pending: 'Pending', blocked: 'Terhambat' }[status] || status);

    async function refreshActivityMonitor() {
        if (!isPageVisible) return;
        if (monitorInFlight) return;
        try {
            monitorInFlight = true;
            const response = await fetch(monitorEndpoint, {
                cache: 'no-store',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return;
            const items = (await response.json()).data;
            const newIds = new Set(items.map(item => item.id));
            const hasNew = items.some(item => !knownActivityIds.has(item.id));
            monitorBody.innerHTML = items.length ? items.map(item => {
                const status = item.status === 'completed'
                    ? 'chip-status-done'
                    : (item.status === 'in_progress' || item.status === 'pending')
                        ? 'chip-status-pending'
                        : 'chip-status-archived';

                return `<tr data-activity-id="${escapeMonitorValue(item.id)}"${!knownActivityIds.has(item.id) ? ' class="table-success"' : ''}><td class="fw-semibold">${escapeMonitorValue(item.user)}</td><td>${escapeMonitorValue(item.user_request || '-')}</td><td>${escapeMonitorValue(item.activity)}</td><td><span class="badge text-bg-info">${escapeMonitorValue(item.type)}</span></td><td>${escapeMonitorValue(item.category || '-')}</td><td><span class="chip ${status}"><span class="chip-buffering-label">${escapeMonitorValue(monitorStatusLabel(item.status))}</span></span></td><td>${item.ticket_url ? `<a href="${escapeMonitorValue(item.ticket_url)}" target="_blank" rel="noopener" title="Lihat progress ticket"><i class="fas fa-chart-line"></i></a>` : escapeMonitorValue(item.updated_label)}</td></tr>`;
            }).join('') : '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas user.</td></tr>';
            knownActivityIds = newIds;
            monitorStatus.textContent = hasNew ? 'Ada pembaruan baru' : 'Diperbarui baru saja';
            monitorStatus.className = `badge ${hasNew ? 'text-bg-success' : 'text-bg-light'}`;
            if (hasNew) setTimeout(() => monitorBody.querySelectorAll('.table-success').forEach(row => row.classList.remove('table-success')), 2500);
        } catch (error) {
            monitorStatus.textContent = 'Monitoring offline';
            monitorStatus.className = 'badge text-bg-warning';
        } finally {
            monitorInFlight = false;
        }
    }

    refreshActivityMonitor();
    window.setInterval(refreshActivityMonitor, pollIntervalMs);

    // Poll and update dashboard metrics
    const metricsEndpoint = @json(route('admin.dashboard.metrics'));
    async function refreshMetrics() {
        if (!isPageVisible || isUserIdle) return;
        if (metricsInFlight) return;
        try {
            metricsInFlight = true;
            const response = await fetch(metricsEndpoint, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const metrics = await response.json();

            document.querySelectorAll('[data-metric]').forEach(el => {
                const metricKey = el.dataset.metric;
                if (metrics[metricKey] !== undefined) {
                    const oldValue = el.textContent;
                    const newValue = metrics[metricKey];
                    if (oldValue !== String(newValue)) {
                        el.textContent = newValue;
                        el.parentElement.style.transition = 'background-color 0.3s ease';
                        el.parentElement.style.backgroundColor = 'rgba(34, 197, 94, 0.1)';
                        setTimeout(() => {
                            el.parentElement.style.backgroundColor = 'transparent';
                        }, 500);
                    }
                }
            });
        } catch (error) {
            console.error('Failed to refresh metrics:', error);
        } finally {
            metricsInFlight = false;
        }
    }

    refreshMetrics();
    window.setInterval(refreshMetrics, pollIntervalMs);

    const detailModalElement = document.getElementById('dashboard-detail-modal');
    let detailModalBackdrop;

    function showDetailModal() {
        detailModalElement.style.display = 'block';
        detailModalElement.removeAttribute('aria-hidden');
        detailModalElement.setAttribute('aria-modal', 'true');
        detailModalElement.classList.add('show');
        document.body.classList.add('modal-open');

        detailModalBackdrop = document.createElement('div');
        detailModalBackdrop.className = 'modal-backdrop fade show';
        detailModalBackdrop.addEventListener('click', hideDetailModal);
        document.body.appendChild(detailModalBackdrop);
    }

    function hideDetailModal() {
        detailModalElement.classList.remove('show');
        detailModalElement.style.display = 'none';
        detailModalElement.setAttribute('aria-hidden', 'true');
        detailModalElement.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        detailModalBackdrop?.remove();
        detailModalBackdrop = null;
    }

    detailModalElement.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', hideDetailModal);
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && detailModalElement.classList.contains('show')) {
            hideDetailModal();
        }
    });
    const detailTitle = document.getElementById('dashboard-detail-title');
    const detailSummary = document.getElementById('dashboard-detail-summary');
    const detailContent = document.getElementById('dashboard-detail-content');
    const detailStatusLabel = status => ({
        submitted: 'Disubmit',
        completed: 'Selesai',
        in_progress: 'Progress',
        pending: 'Pending',
        blocked: 'Terhambat',
        issue: 'Ada issue',
    }[status] || status);
    const detailStatusClass = status => ({
        submitted: 'text-bg-success',
        completed: 'text-bg-success',
        in_progress: 'text-bg-warning',
        pending: 'text-bg-secondary',
        blocked: 'text-bg-danger',
        issue: 'text-bg-danger',
    }[status] || 'text-bg-secondary');

    function renderDetailLoading() {
        detailTitle.textContent = 'Memuat detail';
        detailSummary.textContent = '';
        detailContent.innerHTML = '<div class="d-flex justify-content-center align-items-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div></div>';
    }

    function renderDetailRows(detail) {
        detailTitle.textContent = detail.title;
        detailSummary.textContent = detail.summary;
        if (!detail.rows.length) {
            detailContent.innerHTML = '<div class="text-center text-muted py-5">Tidak ada data untuk ditampilkan.</div>';
            return;
        }

        const headers = detail.columns.map(column => `<th>${escapeMonitorValue(column)}</th>`).join('');
        const rows = detail.rows.map(row => `<tr><td class="fw-semibold">${escapeMonitorValue(row.user)}</td>${Object.hasOwn(row, 'user_request') ? `<td>${escapeMonitorValue(row.user_request || '-')}</td>` : ''}<td>${escapeMonitorValue(row.item)}</td><td><span class="badge ${detailStatusClass(row.status)}">${escapeMonitorValue(detailStatusLabel(row.status))}</span></td><td>${escapeMonitorValue(row.updated_label)}</td></tr>`).join('');
        detailContent.innerHTML = `<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>${headers}</tr></thead><tbody>${rows}</tbody></table></div>`;
    }

    document.querySelectorAll('.dashboard-detail-trigger').forEach(trigger => {
        trigger.addEventListener('click', async () => {
            renderDetailLoading();
            showDetailModal();
            try {
                const endpoint = cardDetailsEndpoint.replace('__CARD__', encodeURIComponent(trigger.dataset.cardDetail));
                const response = await fetch(endpoint, { cache: 'no-store', headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Failed to load card details');
                renderDetailRows(await response.json());
            } catch (error) {
                detailTitle.textContent = 'Detail tidak tersedia';
                detailSummary.textContent = '';
                detailContent.innerHTML = '<div class="text-center text-muted py-5">Data detail tidak dapat dimuat. Silakan coba lagi.</div>';
            }
        });
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    // Data dari PHP
    const dailyLabels = {!! json_encode($dailyLabels ?? []) !!};
    const dailySubmissionsData = {!! json_encode($dailySubmissionsData ?? []) !!};
    const trendLabels = {!! json_encode($trendLabels ?? []) !!};
    const dailyActivityTrendData = {!! json_encode($dailyActivityTrendData ?? []) !!};
    const weeklyData = {!! json_encode($weeklyComplianceData ?? [0, 0, 0, 0]) !!};
    const formUsageData = {!! json_encode($formUsageData ?? []) !!};
    const userActivityData = {!! json_encode($userActivityData ?? []) !!};
    const issuesByFormData = {!! json_encode($issuesByFormData ?? []) !!};
    const dailyTaskByUserData = {!! json_encode($dailyTaskByUserData ?? []) !!};
    const statusData = {!! json_encode($statusData ?? ['submit' => ['completed' => 0, 'pending' => 0], 'daily' => ['completed' => 0, 'in_progress' => 0, 'blocked' => 0]]) !!};

    let submissionsTrendChart;
    let currentChartType = 'line';

    // Chart configuration
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: true,
                position: window.innerWidth < 768 ? 'bottom' : 'top',
                labels: {
                    padding: window.innerWidth < 768 ? 10 : 20,
                    usePointStyle: true,
                    font: {
                        size: window.innerWidth < 768 ? 12 : 14
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgba(255, 255, 255, 0.2)',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true,
                padding: window.innerWidth < 768 ? 8 : 12,
                callbacks: {
                    title: function(context) {
                        return context[0].label;
                    },
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y;
                    }
                }
            }
        },
        scales: {
            x: {
                display: true,
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: window.innerWidth < 768 ? 10 : 12
                    },
                    maxRotation: window.innerWidth < 768 ? 90 : 45,
                    minRotation: window.innerWidth < 768 ? 90 : 45
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    stepSize: 1,
                    font: {
                        size: window.innerWidth < 768 ? 10 : 12
                    }
                }
            }
        }
    };

    // Initialize all charts
    function initCharts() {
        initSubmissionsTrendChart();
        initStatusPieChart();
        initFormUsageChart();
        initUserActivityChart();
        initIssuesByFormChart();
    }

    // Submissions Trend Chart (Line/Bar/Area)
    function initSubmissionsTrendChart() {
        const ctx = document.getElementById('submissionsTrendChart');
        if (submissionsTrendChart) {
            submissionsTrendChart.destroy();
        }

        const datasets = [{
            label: 'Daily Activity',
            data: dailyActivityTrendData,
            borderColor: '#2d5a8e',
            backgroundColor: currentChartType === 'area' ? 'rgba(45, 90, 142, 0.1)' : '#2d5a8e',
            fill: currentChartType === 'area',
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6
        }];

        submissionsTrendChart = new Chart(ctx, {
            type: currentChartType === 'area' ? 'line' : currentChartType,
            data: {
                labels: trendLabels,
                datasets: datasets
            },
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    legend: {
                        display: false
                    },
                    tooltip: {
                        ...chartConfig.plugins.tooltip,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return 'Daily Activity: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
    }

    // Status Pie Chart
    function initStatusPieChart() {
        const ctx = document.getElementById('statusPieChart');
        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        const labels = dailyTaskByUserData.map(item => item.label);
        const values = dailyTaskByUserData.map(item => item.value);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['Belum ada data'],
                datasets: [{
                    data: values.length ? values : [0],
                    backgroundColor: ['#198754', '#0d6efd', '#6f42c1', '#f59e0b', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Form Usage Chart
    function initFormUsageChart() {
        const ctx = document.getElementById('formUsageChart');
        const labels = formUsageData.map(item => item.label);
        const values = formUsageData.map(item => item.value);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Submissions',
                    data: values,
                    backgroundColor: [
                        '#2d5a8e',
                        '#3a7bd5',
                        '#4a90e2',
                        '#5ba0f2',
                        '#6bb6ff'
                    ],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ...chartConfig.scales.x,
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        ...chartConfig.scales.y
                    }
                }
            }
        });
    }

    // User Activity Chart
    function initUserActivityChart() {
        const ctx = document.getElementById('userActivityChart');
        const labels = userActivityData.map(item => item.label);
        const values = userActivityData.map(item => item.value);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Submissions',
                    data: values,
                    backgroundColor: [
                        '#28a745',
                        '#20c997',
                        '#17a2b8',
                        '#6f42c1',
                        '#e83e8c'
                    ],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ...chartConfig.scales.x
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Issues by Form Chart
    function initIssuesByFormChart() {
        const ctx = document.getElementById('issuesByFormChart');
        const labels = issuesByFormData.map(item => item.label);
        const values = issuesByFormData.map(item => item.value);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Issues',
                    data: values,
                    backgroundColor: '#dc3545',
                    borderColor: '#c82333',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ...chartConfig.scales.x,
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        ...chartConfig.scales.y,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Chart type switching
    document.querySelectorAll('[data-chart-type]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-chart-type]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentChartType = this.dataset.chartType;
            initSubmissionsTrendChart();
        });
    });

    // Refresh charts
    document.getElementById('refreshCharts').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        setTimeout(() => {
            initCharts();
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }, 500);
    });

    // Time range selector (placeholder for future enhancement)
    document.getElementById('timeRange').addEventListener('change', function() {
        // Future: implement dynamic data loading based on selected range
        console.log('Time range changed to:', this.value);
    });

    // Stat card interactions
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', function() {
            const statType = this.dataset.stat;

            // Remove active class from all cards
            document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
            // Add active class to clicked card
            this.classList.add('active');

            // Highlight related chart based on stat type
            const chartHighlights = {
                'forms': 'formUsageChart',
                'submissions': 'submissionsTrendChart',
                'compliance': 'statusPieChart',
                'issues': 'issuesByFormChart'
            };

            // Simple visual feedback - could be enhanced to filter charts
            console.log('Selected stat:', statType);
        });
    });

    // Initialize all charts on page load
    initCharts();

    // Handle window resize for responsive chart updates
    window.addEventListener('resize', function() {
        // Update chart configurations for responsive behavior
        if (submissionsTrendChart) {
            submissionsTrendChart.options.plugins.legend.position = window.innerWidth < 768 ? 'bottom' : 'top';
            submissionsTrendChart.options.plugins.legend.labels.font.size = window.innerWidth < 768 ? 12 : 14;
            submissionsTrendChart.options.plugins.legend.labels.padding = window.innerWidth < 768 ? 10 : 20;
            submissionsTrendChart.options.plugins.tooltip.padding = window.innerWidth < 768 ? 8 : 12;
            submissionsTrendChart.options.scales.x.ticks.font.size = window.innerWidth < 768 ? 10 : 12;
            submissionsTrendChart.options.scales.y.ticks.font.size = window.innerWidth < 768 ? 10 : 12;
            submissionsTrendChart.update();
        }

        // Update other charts similarly if needed
        document.querySelectorAll('canvas').forEach(canvas => {
            const chart = Chart.getChart(canvas);
            if (chart) {
                chart.options.plugins.legend.position = window.innerWidth < 768 ? 'bottom' : 'top';
                chart.options.plugins.legend.labels.font.size = window.innerWidth < 768 ? 12 : 14;
                chart.options.plugins.legend.labels.padding = window.innerWidth < 768 ? 10 : 20;
                chart.options.plugins.tooltip.padding = window.innerWidth < 768 ? 8 : 12;
                if (chart.options.scales && chart.options.scales.x) {
                    chart.options.scales.x.ticks.font.size = window.innerWidth < 768 ? 10 : 12;
                }
                if (chart.options.scales && chart.options.scales.y) {
                    chart.options.scales.y.ticks.font.size = window.innerWidth < 768 ? 10 : 12;
                }
                chart.update();
            }
        });
    });
});
</script>

<style>
.stat-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card.active {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    border-width: 5px !important;
}

.stat-card .fa-2x {
    transition: transform 0.3s ease;
}

.stat-card:hover .fa-2x {
    transform: scale(1.1);
}

.chart-container {
    position: relative;
    min-height: 200px;
}

.chart-container canvas {
    width: 100% !important;
    height: auto !important;
}

/* Tampilkan sekitar 8 aktivitas terbaru; aktivitas lain tetap dapat diakses dengan scroll. */
.activity-monitor-scroll {
    max-height: 345px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.25) transparent;
}

.activity-monitor-scroll::-webkit-scrollbar {
    width: 6px;
}

.activity-monitor-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.activity-monitor-scroll::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.25);
    border-radius: 3px;
}

.activity-monitor-scroll thead th {
    z-index: 1;
    box-shadow: inset 0 -1px 0 var(--bs-border-color);
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* Mobile responsive adjustments */
@media (max-width: 767.98px) {
    .stat-card {
        padding: 1rem !important;
    }

    .stat-card .fa-2x {
        font-size: 1.5rem !important;
    }

    .stat-card h3 {
        font-size: 1.5rem;
    }

    .btn-group {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .btn-group .btn {
        border-radius: 0 !important;
        border-left: none;
        border-right: none;
    }

    .btn-group .btn:first-child {
        border-left: 1px solid #dee2e6 !important;
        border-top-left-radius: 0.375rem !important;
        border-bottom-left-radius: 0.375rem !important;
    }

    .btn-group .btn:last-child {
        border-right: 1px solid #dee2e6 !important;
        border-top-right-radius: 0.375rem !important;
        border-bottom-right-radius: 0.375rem !important;
    }

    .card {
        margin-bottom: 1rem;
    }

    .chart-container {
        min-height: 250px;
    }

    /* Mobile table styles */
    .table-responsive {
        border: none;
    }

    .activity-monitor-scroll {
        max-height: 320px;
    }

    .table-responsive .table {
        margin-bottom: 0;
    }

    .table-responsive .table td {
        padding: 0.75rem 0;
    }

    .table-responsive .table tr.d-block {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }

    .table-responsive .table tr.d-block td {
        display: block;
        padding: 1rem;
    }

    .table-responsive .table tr.d-block:last-child {
        margin-bottom: 0;
    }
}

@media (max-width: 575.98px) {
    .stat-card h3 {
        font-size: 1.25rem;
    }

    .stat-card .small {
        font-size: 0.75rem;
    }

    .card .card-body {
        padding: 1rem;
    }

    .chart-container {
        min-height: 200px;
    }
}

/* Loading animation for charts */
@keyframes chart-loading {
    0% { opacity: 0.5; }
    50% { opacity: 1; }
    100% { opacity: 0.5; }
}

.chart-loading {
    animation: chart-loading 1.5s ease-in-out infinite;
}

/* Improve chart responsiveness */
@media (max-width: 991.98px) {
    .col-lg-8, .col-lg-4, .col-lg-6 {
        margin-bottom: 1.5rem;
    }
}

/* Schedule widget styles */
.schedule-list {
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

.schedule-list::-webkit-scrollbar {
    width: 6px;
}

.schedule-list::-webkit-scrollbar-track {
    background: transparent;
}

.schedule-list::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.schedule-item {
    transition: background-color 0.2s ease;
}

.schedule-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.schedule-icon {
    min-width: 40px;
    text-align: center;
}

/* Mobile responsive for schedule widget */
@media (max-width: 767.98px) {
    .schedule-item {
        padding: 1rem 0.5rem;
    }

    .schedule-icon {
        min-width: 30px;
    }

    .schedule-list {
        max-height: 300px;
    }
}
</style>

@endsection