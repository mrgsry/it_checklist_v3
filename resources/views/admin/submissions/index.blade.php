@extends('layouts.admin')

@section('title', 'Submissions')
@section('page-title', 'Daftar Submission')

@section('content')
<div class="card mb-3">
    <div class="card-body">
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
                <label class="form-label small">Dari</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Sampai</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">Daftar Submission</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>User</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions ?? [] as $sub)
                    @php
                    $flaggedCount = $sub->answers->where('is_flagged', true)->count();
                    @endphp
                    <tr class="{{ $flaggedCount > 0 ? 'flagged-row' : '' }}">
                        <td>{{ $sub->form->title ?? '-' }}</td>
                        <td>{{ $sub->submitter->name ?? '-' }}</td>
                        <td>{{ $sub->submission_date?->isoFormat('D MMM Y') }}</td>
                        <td>{{ $sub->submitted_at?->format('H:i') }}</td>
                        <td>
                            @if($flaggedCount > 0)
                            <span class="badge bg-danger"><i
                                    class="fas fa-exclamation-circle me-1"></i>{{ $flaggedCount }} Masalah</span>
                            @else
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Lengkap</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.submissions.show', $sub) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $submissions->links() }}
    </div>
</div>
@endsection