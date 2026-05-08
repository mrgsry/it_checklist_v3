@extends('layouts.user')

@section('title', 'Riwayat Checklist')
@section('page-title', 'Riwayat Pengisian')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">Riwayat Submission</h5>

        @if($submissions->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Foto</th>
                        <th>Tanggal</th>
                        <th>Waktu Submit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                    @php
                        $photoAnswers = $sub->answers->filter(fn($answer) =>
                            $answer->formItem?->field_type === 'photo' && !empty($answer->answer_value)
                        );
                    @endphp
                    <tr>
                        <td>{{ $sub->form->title ?? '-' }}</td>
                        <td>
                            @if($photoAnswers->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($photoAnswers as $answer)
                                        <img src="{{ Storage::url($answer->answer_value) }}"
                                            alt="Preview Foto"
                                            class="img-thumbnail"
                                            style="width:72px;height:72px;object-fit:cover;">
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $sub->submission_date?->isoFormat('D MMM Y') }}</td>
                        <td>{{ $sub->submitted_at?->format('H:i') }}</td>
                        <td>
                            @if($sub->answers->where('is_flagged', true)->count() > 0)
                            <span class="badge bg-danger">Ada Masalah</span>
                            @else
                            <span class="badge bg-success">Lengkap</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" disabled
                                title="Hanya Admin yang dapat melihat detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $submissions->links() }}
        @else
        <div class="text-center py-4 text-muted">
            <i class="fas fa-inbox fa-2x mb-2"></i>
            <p>Belum ada riwayat submission</p>
        </div>
        @endif
    </div>
    @endsection