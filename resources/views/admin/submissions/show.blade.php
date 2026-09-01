@extends('layouts.admin')

@section('title', 'Detail Submission')
@section('page-title', 'Detail Submission')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-createspace">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h5 class="font-headline mb-1">{{ $submission->form->title ?? 'Form' }}</h5>
                        <div class="text-muted text-caption">
                            <i class="fas fa-user me-1"></i>{{ $submission->submitter->name ?? '-' }}
                            <span class="mx-2">|</span>
                            <i
                                class="fas fa-calendar me-1"></i>{{ $submission->submission_date?->isoFormat('dddd, D MMMM Y') }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-1"></i>{{ $submission->submitted_at?->format('H:i') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php
                        $flaggedCount = $submission->answers->where('is_flagged', true)->count();
                        @endphp
                        @if($flaggedCount > 0)
                        <span class="chip chip-status-archived" style="background-color: #FEE2E2; color: #DC2626;"><i class="fas fa-exclamation-triangle me-1"></i>{{ $flaggedCount }}
                            Masalah</span>
                        @else
                        <span class="chip chip-status-complete">Lengkap</span>
                        @endif
                        <a href="{{ route('admin.submissions.export-pdf', $submission) }}" class="btn-createspace btn-sm btn-primary">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                    </div>
                </div>

                @if($submission->notes)
                <div class="card-createspace mb-4">
                    <div class="card-body p-3">
                        <div class="text-muted text-caption mb-2">Catatan</div>
                        {{ $submission->notes }}
                    </div>
                </div>
                @endif

                <h6 class="font-headline font-bold mb-3">Jawaban</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Jawaban</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submission->answers as $answer)
                            <tr class="{{ $answer->is_flagged ? 'flagged-row' : '' }}">
                                <td class="font-semibold">{{ $answer->formItem->label ?? '-' }}</td>
                                <td class="{{ $answer->is_flagged ? 'flagged-cell' : '' }}">
                                    @if($answer->formItem?->field_type === 'photo' && filled($answer->answer_value))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($answer->photoPaths() as $path)
                                        <a href="{{ Storage::url($path) }}" target="_blank">
                                            <img src="{{ Storage::url($path) }}" class="img-fluid rounded" style="max-width: 240px; max-height: 240px; object-fit: cover;">
                                        </a>
                                        @endforeach
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
        <div class="card-createspace">
            <div class="card-body">
                <h6 class="font-headline font-bold mb-3">Informasi</h6>
                <div class="mb-3">
                    <div class="text-muted text-caption mb-1">Status</div>
                    <span class="chip chip-{{ $submission->status == 'submitted' ? 'status-active' : 'status-archived' }}" style="background-color: {{ $submission->status == 'submitted' ? '#DBEAFE' : '#FEF3C7' }}; color: {{ $submission->status == 'submitted' ? '#1E40AF' : '#92400E' }}">
                        {{ ucfirst($submission->status) }}
                    </span>
                </div>
                <div class="mb-3">
                    <div class="text-muted text-caption mb-1">Form</div>
                    <div class="font-semibold">{{ $submission->form->title ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted text-caption mb-1">User</div>
                    <div class="font-semibold">{{ $submission->submitter->name ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted text-caption mb-1">Tanggal Submit</div>
                    <div>{{ $submission->submission_date?->isoFormat('D MMMM Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
