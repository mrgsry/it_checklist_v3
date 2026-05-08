@extends('layouts.admin')

@section('title', 'Laporan')
@section('page-title', 'Laporan & Analitik')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="fas fa-filter me-2"></i>Filter</h6>
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Form</label>
                <select name="form_id" class="form-select">
                    <option value="">Semua Form</option>
                    @foreach($forms as $f)
                    <option value="{{ $f->id }}" {{ request('form_id') == $f->id ? 'selected' : '' }}>{{ $f->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">User</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedForm)
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="small text-muted mb-1">Total Submissions</div>
            <h3 class="fw-bold text-primary mb-0">{{ $summaryStats['total'] ?? 0 }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="small text-muted mb-1">Bermasalah</div>
            <h3 class="fw-bold text-danger mb-0">{{ $summaryStats['flagged'] ?? 0 }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="small text-muted mb-1">Normal</div>
            <h3 class="fw-bold text-success mb-0">{{ $summaryStats['clean'] ?? 0 }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="small text-muted mb-1">Rate Masalah</div>
            <h3 class="fw-bold text-warning mb-0">{{ $summaryStats['flagged_rate'] ?? 0 }}%</h3>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="card-title mb-0">Hasil Rekap</h6>
            <a href="{{ route('admin.reports.export-excel') }}?{{ http_build_query(request()->all()) }}"
                class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
        </div>

        @if($submissions->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>User</th>
                        <th>Tanggal</th>
                        @if($selectedForm)
                        @foreach($selectedForm->items as $item)
                        <th class="small">{{ Str::limit($item->label, 20) }}</th>
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
                        <td>{{ $sub->submission_date?->isoFormat('D MMM Y') }}</td>
                        @if($selectedForm)
                        @foreach($selectedForm->items as $item)
                        @php
                        $ans = $sub->answers->firstWhere('form_item_id', $item->id);
                        @endphp
                        <td class="{{ $ans?->is_flagged ? 'flagged-cell' : '' }}">
                            {{ Str::limit($ans?->answer_value ?? '-', 15) }}
                        </td>
                        @endforeach
                        @endif
                        <td>
                            @if($flagged > 0)
                            <span class="badge bg-danger">⚠ Masalah</span>
                            @else
                            <span class="badge bg-success">✓ OK</span>
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
@endsection