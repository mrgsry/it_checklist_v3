@extends('layouts.user')

@section('title', 'Dashboard')
@section('page-title', 'Checklist Saya')

@section('content')
@php
    $formProgress = $formTotal > 0 ? round(($formCompleted / $formTotal) * 100) : 0;
    $activityProgress = $dailyActivityTotal > 0 ? round(($dailyActivityCompleted / $dailyActivityTotal) * 100) : 0;
    $combinedTotal = $formTotal + $dailyActivityTotal;
    $combinedCompleted = $formCompleted + $dailyActivityCompleted;
    $combinedProgress = $combinedTotal > 0 ? round(($combinedCompleted / $combinedTotal) * 100) : 0;
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
    <div><div class="text-caption text-uppercase">Monitoring pribadi</div><h4 class="font-headline font-semibold mb-1">Ringkasan pekerjaan hari ini</h4><p class="text-muted mb-0">Pantau submit form dan aktivitas harian dalam satu tampilan.</p></div>
    <div class="text-muted small"><i class="fas fa-calendar-day me-1"></i>{{ $today->isoFormat('dddd, D MMMM Y') }}</div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3"><div class="card-createspace p-3 h-100" style="border-left:4px solid #2563EB"><div class="d-flex justify-content-between"><span class="text-caption">Submit Form</span><i class="fas fa-file-signature text-primary"></i></div><h3 class="font-headline mb-1">{{ $formCompleted }} <small class="text-muted fs-6">/ {{ $formTotal }}</small></h3><div class="progress mb-2" style="height:6px"><div class="progress-bar bg-primary" style="width:{{ $formProgress }}%"></div></div><small class="text-muted">{{ $formProgress }}% selesai · {{ $pendingCount }} belum submit</small></div></div>
    <div class="col-12 col-md-6 col-xl-3"><div class="card-createspace p-3 h-100" style="border-left:4px solid #16A34A"><div class="d-flex justify-content-between"><span class="text-caption">Daily Activity</span><i class="fas fa-clipboard-list text-success"></i></div><h3 class="font-headline mb-1">{{ $dailyActivityCompleted }} <small class="text-muted fs-6">/ {{ $dailyActivityTotal }}</small></h3><div class="progress mb-2" style="height:6px"><div class="progress-bar bg-success" style="width:{{ $activityProgress }}%"></div></div><small class="text-muted">{{ $activityProgress }}% selesai · {{ $dailyActivityInProgress }} berjalan</small></div></div>
    <div class="col-12 col-md-6 col-xl-3"><div class="card-createspace p-3 h-100" style="border-left:4px solid #7C3AED"><div class="d-flex justify-content-between"><span class="text-caption">Progress Mingguan</span><i class="fas fa-chart-line" style="color:#7C3AED"></i></div><h3 class="font-headline mb-1">{{ $weeklyProgress }}%</h3><div class="progress mb-2" style="height:6px"><div class="progress-bar" style="width:{{ $weeklyProgress }}%;background:#7C3AED"></div></div><small class="text-muted">{{ $weeklyCompleted }} dari {{ $weeklyTotal }} task selesai</small></div></div>
    <div class="col-12 col-md-6 col-xl-3"><div class="card-createspace p-3 h-100" style="border-left:4px solid #DC2626"><div class="d-flex justify-content-between"><span class="text-caption">Perlu Perhatian</span><i class="fas fa-exclamation-triangle text-danger"></i></div><h3 class="font-headline mb-1 text-danger">{{ max(0, $attentionCount) }}</h3><small class="text-muted">{{ $dailyActivityBlocked }} aktivitas terhambat · {{ $streak }} hari streak submit</small></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-5"><div class="card-createspace p-3 h-100"><h5 class="font-headline font-semibold mb-1">Komposisi progress hari ini</h5><p class="text-muted small mb-2">Selesai dibandingkan task yang perlu dikerjakan.</p><div class="position-relative mx-auto" style="max-width:260px;height:230px"><canvas id="userProgressDonut"></canvas><div class="position-absolute top-50 start-50 translate-middle text-center"><strong class="font-headline fs-3">{{ $combinedProgress }}%</strong><div class="small text-muted">selesai</div></div></div><div class="row g-2 text-center small"><div class="col-6"><span class="d-inline-block rounded-circle bg-success me-1" style="width:8px;height:8px"></span>Selesai <strong>{{ $combinedCompleted }}</strong></div><div class="col-6"><span class="d-inline-block rounded-circle bg-light border me-1" style="width:8px;height:8px"></span>Belum <strong>{{ max(0, $combinedTotal - $combinedCompleted) }}</strong></div></div></div></div>
    <div class="col-12 col-lg-7"><div class="card-createspace p-3 h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="font-headline font-semibold mb-1">Progress 7 hari</h5><p class="text-muted small mb-0">Form terjadwal + Daily Activity</p></div><span class="chip chip-status-active">{{ $weeklyCompleted }}/{{ $weeklyTotal }}</span></div><div class="d-flex flex-column gap-2">@foreach($weeklyTaskDays as $day)<div class="d-flex align-items-center gap-2"><div style="width:48px" class="small fw-semibold">{{ $day['date']->isoFormat('ddd') }}<br><span class="text-muted fw-normal">{{ $day['date']->format('d/m') }}</span></div><div class="flex-grow-1"><div class="progress" style="height:8px"><div class="progress-bar {{ $day['has_blocked'] ? 'bg-danger' : 'bg-success' }}" style="width:{{ $day['progress'] }}%"></div></div></div><div class="small text-end" style="width:55px">{{ $day['completed'] }}/{{ $day['total'] }}</div></div>@endforeach</div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7"><div class="card-createspace h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="font-headline font-semibold mb-0"><i class="fas fa-tasks me-2"></i>Checklist hari ini</h5><a href="{{ route('user.checklist.index') }}" class="btn-createspace btn-sm btn-ghost">Lihat semua</a></div>@forelse($formsDue as $form)@php($submission = $submittedToday->firstWhere('form_id', $form->id))<div class="d-flex align-items-center gap-3 py-2 border-bottom"><i class="fas {{ $submission ? 'fa-check-circle text-success' : 'fa-clock text-warning' }} fa-lg"></i><div class="flex-grow-1"><div class="fw-semibold">{{ $form->title }}</div><small class="text-muted">{{ $submission ? 'Sudah diisi hari ini' : 'Menunggu submit' }}</small></div>@if(!$submission)<a href="{{ route('user.checklist.fill', $form->id) }}" class="btn-createspace btn-sm btn-primary">Isi</a>@endif</div>@empty<div class="text-center text-muted py-4">Tidak ada form terjadwal hari ini.</div>@endforelse</div></div></div>
    <div class="col-12 col-lg-5"><div class="card-createspace h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="font-headline font-semibold mb-0"><i class="fas fa-clipboard-list me-2"></i>Daily Activity</h5><a href="{{ route('user.daily-activities.index') }}" class="btn-createspace btn-sm btn-ghost">Kelola</a></div>@forelse($dailyActivitiesToday as $activity)<div class="d-flex align-items-start gap-2 py-2 border-bottom"><i class="fas {{ $activity->status === 'completed' ? 'fa-check text-success' : ($activity->status === 'blocked' ? 'fa-ban text-danger' : 'fa-spinner text-warning') }} mt-1"></i><div><div class="fw-semibold">{{ $activity->activity }}</div><small class="text-muted">{{ str_replace('_', ' ', ucfirst($activity->status)) }}{{ $activity->isAssigned() ? ' · Assigned' : '' }}</small></div></div>@empty<div class="text-center text-muted py-4">Belum ada aktivitas untuk hari ini.</div>@endforelse</div></div></div>
</div>

@if($streak > 2)<div class="alert alert-success d-inline-flex align-items-center"><i class="fas fa-fire me-2 text-warning"></i><strong>Streak {{ $streak }} hari!</strong>&nbsp; Konsisten menyelesaikan checklist.</div>@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>document.addEventListener('DOMContentLoaded',()=>{const canvas=document.getElementById('userProgressDonut');if(!canvas)return;new Chart(canvas,{type:'doughnut',data:{labels:['Selesai','Belum selesai'],datasets:[{data:[{{ $combinedCompleted }},{{ max(0, $combinedTotal - $combinedCompleted) }}],backgroundColor:['#16A34A','#E5E7EB'],borderWidth:0}]},options:{cutout:'72%',plugins:{legend:{display:false}}}});});</script>
@endpush