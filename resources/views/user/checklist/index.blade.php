@extends('layouts.user')

@section('title', 'Checklist Saya')
@section('page-title', 'Checklist Saya')

@section('content')
<div class="mb-3">
    <h6 class="text-muted">Hari ini: <strong>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</strong></h6>
</div>

@if(count($formsDue) > 0)
<div class="row g-3">
    @foreach($formsDue as $form)
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
    @endforeach
</div>
@else
<div class="card p-4 text-center text-muted">
    <i class="fas fa-clipboard-check fa-3x mb-3 text-success"></i>
    <h5>Semua checklist sudah diisi!</h5>
    <p class="small">Tidak ada checklist yang harus diisi hari ini.</p>
</div>
@endif
@endsection