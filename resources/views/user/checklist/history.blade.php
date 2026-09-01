@extends('layouts.user')

@section('title', 'Riwayat Saya')
@section('page-title', 'Riwayat Saya')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Riwayat Daily Activity</h5>

        @if($dailyActivities->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Aktivitas / Task</th>
                        <th>Catatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyActivities as $dailyActivity)
                    @php($status = ['completed' => ['success', 'Selesai'], 'in_progress' => ['warning', 'Dalam Proses'], 'blocked' => ['danger', 'Terhambat']][$dailyActivity->status])
                    <tr>
                        <td>{{ $dailyActivity->activity_date?->isoFormat('D MMM Y') }}</td>
                        <td class="fw-semibold">{{ $dailyActivity->activity }}</td>
                        <td>{{ $dailyActivity->notes ?: '-' }}</td>
                        <td><span class="badge text-bg-{{ $status[0] }}">{{ $status[1] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $dailyActivities->links() }}
        @else
        <div class="text-center py-4 text-muted">
            <i class="fas fa-clipboard-list fa-2x mb-2"></i>
            <p class="mb-0">Belum ada riwayat daily activity</p>
        </div>
        @endif
    </div>
</div>

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
                    <tr>
                        <td>{{ $sub->form->title ?? '-' }}</td>
                        <td>
                            @php($hasPhotos = $sub->answers->contains(fn ($answer) => $answer->formItem?->field_type === 'photo' && $answer->photoPaths() !== []))
                            @unless($hasPhotos)
                                <span class="text-muted">-</span>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($sub->answers as $answer)
                                        @if($answer->formItem?->field_type === 'photo')
                                            @foreach($answer->photoPaths() as $path)
                                            <img src="{{ Storage::url($path) }}"
                                                alt="Preview Foto"
                                                class="img-thumbnail"
                                                style="width:72px;height:72px;object-fit:cover;">
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            @endunless
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
</div>
@endsection