@extends('layouts.admin')

@section('title', 'Detail Submission')
@section('page-title', 'Detail Submission')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $submission->form->title ?? 'Form' }}</h5>
                        <div class="text-muted small">
                            <i class="fas fa-user me-1"></i>{{ $submission->submitter->name ?? '-' }}
                            <span class="mx-2">|</span>
                            <i
                                class="fas fa-calendar me-1"></i>{{ $submission->submission_date?->isoFormat('dddd, D MMMM Y') }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-1"></i>{{ $submission->submitted_at?->format('H:i') }}
                        </div>
                    </div>
                    @php
                    $flaggedCount = $submission->answers->where('is_flagged', true)->count();
                    @endphp
                    @if($flaggedCount > 0)
                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>{{ $flaggedCount }}
                        Masalah</span>
                    @else
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Lengkap</span>
                    @endif
                </div>

                @if($submission->notes)
                <div class="alert alert-light border mb-3">
                    <div class="small text-muted mb-1">Catatan</div>
                    {{ $submission->notes }}
                </div>
                @endif

                <h6 class="fw-bold mb-3">Jawaban</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Jawaban</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submission->answers as $answer)
                            <tr class="{{ $answer->is_flagged ? 'flagged-row' : '' }}">
                                <td class="fw-semibold">{{ $answer->formItem->label ?? '-' }}</td>
                                <td class="{{ $answer->is_flagged ? 'flagged-cell' : '' }}">
                                    @if($answer->formItem?->field_type === 'photo' && $answer->answer_value)
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ Storage::url($answer->answer_value) }}" target="_blank">
                                            <img src="{{ Storage::url($answer->answer_value) }}" class="img-fluid rounded" style="max-width: 240px; max-height: 240px; object-fit: cover;">
                                        </a>
                                        <small class="text-muted">Klik untuk lihat ukuran penuh</small>
                                    </div>
                                    @else
                                    {{ $answer->answer_value ?? '-' }}
                                    @endif
                                    @if($answer->is_flagged)
                                    <i class="fas fa-exclamation-circle text-danger ms-1"></i>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informasi</h6>
                <div class="mb-2">
                    <div class="small text-muted">Status</div>
                    <span class="badge bg-{{ $submission->status == 'submitted' ? 'success' : 'warning' }}">
                        {{ ucfirst($submission->status) }}
                    </span>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">Form</div>
                    <div>{{ $submission->form->title ?? '-' }}</div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">User</div>
                    <div>{{ $submission->submitter->name ?? '-' }}</div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">Tanggal Submit</div>
                    <div>{{ $submission->submission_date?->isoFormat('D MMMM Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection