@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'IT Checklist Dashboard')

@section('content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 border-start border-4 border-primary h-100 stat-card" data-stat="forms">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="small text-muted mb-1">Total Form</div>
                    <h3 class="mb-0 fw-bold text-primary">{{ $totalForms ?? 0 }}</h3>
                    <small class="text-muted d-none d-sm-inline">Active checklists</small>
                </div>
                <i class="fas fa-wpforms fa-2x text-primary opacity-25 ms-2"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 border-start border-4 border-success h-100 stat-card" data-stat="submissions">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="small text-muted mb-1">Submissions</div>
                    <h3 class="mb-0 fw-bold text-success">{{ $totalSubmissions ?? 0 }}</h3>
                    <small class="text-muted d-none d-sm-inline">Total completed</small>
                </div>
                <i class="fas fa-inbox fa-2x text-success opacity-25 ms-2"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 border-start border-4 border-info h-100 stat-card" data-stat="compliance">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="small text-muted mb-1">Compliance Rate</div>
                    <h3 class="mb-0 fw-bold text-info">{{ $complianceRate ?? 0 }}%</h3>
                    <small class="text-muted d-none d-sm-inline">This week</small>
                </div>
                <i class="fas fa-chart-line fa-2x text-info opacity-25 ms-2"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 border-start border-4 border-danger h-100 stat-card" data-stat="issues">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="small text-muted mb-1">Issues Today</div>
                    <h3 class="mb-0 fw-bold text-danger">{{ $issuesToday ?? 0 }}</h3>
                    <small class="text-muted d-none d-sm-inline">Need attention</small>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-25 ms-2"></i>
            </div>
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
                <h6 class="fw-bold mb-0">Submissions Trend</h6>
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
            <div class="chart-container">
                <canvas id="submissionsTrendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">Status Overview</h6>
            <div class="chart-container">
                <canvas id="statusPieChart" style="max-height: 250px;"></canvas>
            </div>
            <div class="mt-3">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                            <div class="small text-muted">OK</div>
                            <div class="fw-bold text-success">{{ $statusData['ok'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                            <div class="small text-muted">Issues</div>
                            <div class="fw-bold text-danger">{{ $statusData['issues'] ?? 0 }}</div>
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
                            @forelse($recentSubmissions ?? [] as $sub)
                            <tr class="d-md-table-row d-block d-md-none border-bottom mb-3 pb-3">
                                <td class="d-block border-0 p-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong class="text-truncate me-2">{{ $sub->form->title ?? '-' }}</strong>
                                        @php
                                        $flagged = $sub->answers->where('is_flagged', true)->count();
                                        @endphp
                                        @if($flagged > 0)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>Ada Masalah
                                        </span>
                                        @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Lengkap
                                        </span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-user me-1"></i>{{ $sub->submitter->name ?? '-' }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-calendar me-1"></i>{{ $sub->submission_date?->isoFormat('D MMM Y') }}
                                    </div>
                                </td>
                            </tr>
                            <tr class="d-none d-md-table-row">
                                <td class="fw-semibold">{{ $sub->form->title ?? '-' }}</td>
                                <td>{{ $sub->submitter->name ?? '-' }}</td>
                                <td>{{ $sub->submission_date?->isoFormat('D MMM Y') }}</td>
                                <td>
                                    @php
                                    $flagged = $sub->answers->where('is_flagged', true)->count();
                                    @endphp
                                    @if($flagged > 0)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>Ada Masalah
                                    </span>
                                    @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Lengkap
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                    <div>Belum ada submission</div>
                                </td>
                            </tr>
                            @endforelse
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
@php
$okCount = ($totalSubmissions ?? 0) - ($issuesToday ?? 0);
$issueCount = $issuesToday ?? 0;
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari PHP
    const dailyLabels = {!! json_encode($dailyLabels ?? []) !!};
    const dailySubmissionsData = {!! json_encode($dailySubmissionsData ?? []) !!};
    const weeklyData = {!! json_encode($weeklyComplianceData ?? [0, 0, 0, 0]) !!};
    const formUsageData = {!! json_encode($formUsageData ?? []) !!};
    const userActivityData = {!! json_encode($userActivityData ?? []) !!};
    const issuesByFormData = {!! json_encode($issuesByFormData ?? []) !!};
    const statusData = {!! json_encode($statusData ?? ['ok' => 0, 'issues' => 0]) !!};

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
            label: 'Submissions',
            data: dailySubmissionsData,
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
                labels: dailyLabels,
                datasets: datasets
            },
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Status Pie Chart
    function initStatusPieChart() {
        const ctx = document.getElementById('statusPieChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['OK', 'Bermasalah'],
                datasets: [{
                    data: [statusData.ok, statusData.issues],
                    backgroundColor: ['#198754', '#dc3545'],
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
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
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