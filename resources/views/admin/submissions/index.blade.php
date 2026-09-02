@extends('layouts.admin')

@section('title', 'Submissions')
@section('page-title', 'Daftar Submission')

@section('content')
<div class="card-createspace mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="text-caption font-semibold mb-1 d-block">Form</label>
                <select name="form_id" class="input-createspace">
                    <option value="">Semua Form</option>
                    @foreach($forms as $form)
                    <option value="{{ $form->id }}" @selected(request('form_id') == $form->id)>{{ $form->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="text-caption font-semibold mb-1 d-block">User</label>
                <select name="user_id" class="input-createspace">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="text-caption font-semibold mb-1 d-block">Dari</label><input type="date" name="date_from" class="input-createspace" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><label class="text-caption font-semibold mb-1 d-block">Sampai</label><input type="date" name="date_to" class="input-createspace" value="{{ request('date_to') }}"></div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn-createspace btn-md btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<div class="card-createspace">
    <div class="card-body">
        <h5 class="font-headline font-semibold mb-4">Daftar Submission</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Form</th><th>User</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($submissions as $submission)
                    @php($flaggedCount = $submission->answers->where('is_flagged', true)->count())
                    <tr class="{{ $flaggedCount > 0 ? 'flagged-row' : '' }}">
                        <td>{{ $submission->form->title ?? '-' }}</td>
                        <td>{{ $submission->submitter->name ?? '-' }}</td>
                        <td>{{ $submission->submission_date?->isoFormat('D MMM Y') }}</td>
                        <td>{{ $submission->submitted_at?->format('H:i') }}</td>
                        <td>@if($flaggedCount > 0)<span class="chip chip-status-archived" style="background-color: #FEE2E2; color: #DC2626;"><i class="fas fa-exclamation-circle me-1"></i>{{ $flaggedCount }} Masalah</span>@else<span class="chip chip-status-complete"><i class="fas fa-check me-1"></i>Lengkap</span>@endif</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.submissions.show', $submission) }}" class="btn-createspace btn-sm btn-secondary" title="Lihat detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn-createspace btn-sm btn-primary" title="Edit submission"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('admin.submissions.destroy', $submission) }}" onsubmit="return confirm('Hapus submission ini? Jawaban dan foto akan dihapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-createspace btn-sm btn-destructive" title="Hapus submission"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $submissions->links() }}
    </div>
</div>
@endsection