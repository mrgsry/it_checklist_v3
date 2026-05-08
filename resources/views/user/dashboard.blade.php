@extends('layouts.user')

@section('title', 'Dashboard')
@section('page-title', 'Checklist Saya')

@section('content')
@php
$todayLabel = \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y');
@endphp

<div class="mb-3">
    <h6 class="text-muted">Hari ini: <strong>{{ $todayLabel }}</strong></h6>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-start border-4 border-warning">
            <div class="small text-muted mb-1">Belum Diisi</div>
            <h4 class="mb-0 fw-bold text-warning">{{ $pendingCount ?? count($formsDue ?? []) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-start border-4 border-success">
            <div class="small text-muted mb-1">Sudah Diisi</div>
            <h4 class="mb-0 fw-bold text-success">{{ $submittedToday->count() ?? 0 }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-start border-4 border-info">
            <div class="small text-muted mb-1">Total Bulan Ini</div>
            <h4 class="mb-0 fw-bold text-info">{{ $totalThisMonth ?? 0 }}</h4>
        </div>
    </div>
</div>

<h5 class="fw-semibold mb-3"><i class="fas fa-tasks me-2"></i>Checklist Hari Ini</h5>

@if(!empty($formsDue) || !empty($submittedToday))
<div class="row g-3">
    @forelse($formsDue as $form)
    @php
    $isSubmitted = ($submittedToday ?? collect())->firstWhere('form_id', $form->id);
    @endphp
    @if(!$isSubmitted)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-start border-4 border-danger">
            <div class="card-body">
                <span class="badge bg-danger mb-2">Belum Diisi</span>
                <h6 class="card-title fw-bold">{{ $form->title }}</h6>
                <p class="card-text small text-muted mb-1">
                    <i class="fas fa-clock me-1"></i>
                    Deadline: Hari ini | {{ ucfirst($form->schedule_type) }}
                </p>
                <a href="{{ route('user.checklist.fill', $form->id) }}" class="btn btn-sm btn-primary mt-3 w-100">
                    <i class="fas fa-edit me-1"></i>Isi Sekarang
                </a>
            </div>
        </div>
    </div>
    @endif
    @empty
    @endforelse

    @foreach($submittedToday ?? [] as $sub)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-start border-4 border-success">
            <div class="card-body">
                <span class="badge bg-success mb-2"><i class="fas fa-check me-1"></i>Sudah Diisi</span>
                <h6 class="card-title fw-bold">{{ $sub->form->title ?? 'Form' }}</h6>
                <p class="card-text small text-muted mb-1">
                    <i class="fas fa-clock me-1"></i>
                    Diisi: {{ $sub->submitted_at?->format('H:i') }} | Oleh:
                    {{ $sub->submitter->name ?? auth()->user()->name }}
                </p>
                @if($sub->answers->where('is_flagged', true)->count() > 0)
                <div class="alert alert-danger py-1 px-2 small mt-2 mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Ada {{ $sub->answers->where('is_flagged', true)->count() }} masalah terdeteksi
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card p-4 text-center text-muted">
    <i class="fas fa-clipboard-check fa-3x mb-3 text-success"></i>
    <h5>Tidak ada checklist untuk hari ini</h5>
</div>
@endif

@if(($streak ?? 0) > 2)
<div class="alert alert-info mt-4 d-inline-flex align-items-center">
    <i class="fas fa-fire me-2 text-warning"></i>
    <strong>Streak {{ $streak }} hari!</strong>&nbsp;Mantap terus mengisi checklist.
</div>
@endif
@endsection