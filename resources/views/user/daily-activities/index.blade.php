@extends('layouts.user')

@section('title', 'Daily Activity')
@section('page-title', 'Daily Activity')

@section('content')
@php($canWriteDailyActivity = auth()->user()->hasModuleAccess('daily-activity', 'write'))
<div class="row g-4">
    <div class="col-lg-4">
        @if($canWriteDailyActivity)
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Tambah Aktivitas</h5>
                <form method="POST" action="{{ route('user.daily-activities.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="activity_date" class="form-label">Tanggal</label>
                        <input id="activity_date" type="date" name="activity_date" class="form-control @error('activity_date') is-invalid @enderror" value="{{ old('activity_date', $selectedDate) }}" max="{{ today()->toDateString() }}" required>
                        @error('activity_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="activity" class="form-label">Aktivitas / Task</label>
                        <input id="activity" type="text" name="activity" class="form-control @error('activity') is-invalid @enderror" value="{{ old('activity') }}" maxlength="255" placeholder="Contoh: Monitoring server cabang" required>
                        @error('activity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="user_request" class="form-label">User Request</label>
                        <input id="user_request" type="text" name="user_request" class="form-control @error('user_request') is-invalid @enderror" value="{{ old('user_request') }}" maxlength="255">
                        @error('user_request')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Pilih kategori</option>
                            @foreach(\App\Models\DailyActivity::CATEGORIES as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="completed" @selected(old('status') === 'completed')>Selesai</option>
                            <option value="in_progress" @selected(old('status') === 'in_progress')>Dalam Proses</option>
                            <option value="blocked" @selected(old('status') === 'blocked')>Terhambat</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan <span class="text-muted">(opsional)</span></label>
                        <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Hasil, kendala, atau informasi tambahan">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-save me-1"></i>Simpan Aktivitas</button>
                </form>
            </div>
        </div>
        @else
        <div class="alert alert-secondary"><i class="fas fa-lock me-1"></i>Akses Daily Activity hanya baca.</div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="card-title mb-0"><i class="fas fa-list-check text-primary me-2"></i>Aktivitas Saya</h5>
                    <form method="GET" action="{{ route('user.daily-activities.index') }}" class="d-flex gap-2">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}" max="{{ today()->toDateString() }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Tampilkan</button>
                    </form>
                </div>
                <p class="text-muted small">{{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM Y') }}</p>

                @forelse($activities as $dailyActivity)
                    @php($status = ['completed' => ['success', 'Selesai'], 'in_progress' => ['warning', 'Dalam Proses'], 'blocked' => ['danger', 'Terhambat']][$dailyActivity->status])
                    @php($isEditing = $editingActivityId === $dailyActivity->id)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">{{ $dailyActivity->activity }}</div>
                                <span class="badge text-bg-light border">{{ $dailyActivity->category }}</span>
                                @if($dailyActivity->notes)<p class="text-muted mb-0 mt-1">{{ $dailyActivity->notes }}</p>@endif
                                <small class="text-muted">Diperbarui {{ $dailyActivity->updated_at->format('H:i') }}</small>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="badge text-bg-{{ $status[0] }}">{{ $status[1] }}</span>
                                @if($canWriteDailyActivity)<div class="mt-2">
                                    <a href="{{ route('user.daily-activities.index', ['date' => $selectedDate, 'edit' => $dailyActivity->id]) }}" class="btn btn-sm btn-outline-secondary" title="Perbarui status dan catatan"><i class="fas fa-pen"></i></a>
                                    @unless($dailyActivity->isAssigned())
                                        <form class="d-inline" action="{{ route('user.daily-activities.destroy', $dailyActivity) }}" method="POST" onsubmit="return confirm('Hapus aktivitas ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus aktivitas"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endunless
                                </div>@endif
                            </div>
                        </div>
                        @if($isEditing && $canWriteDailyActivity)
                        <div class="mt-3">
                            <form method="POST" action="{{ route('user.daily-activities.update', $dailyActivity) }}" class="row g-2 border-top pt-3">
                                @csrf @method('PUT')
                                @if($dailyActivity->isAssigned())<div class="col-12"><small class="text-muted"><i class="fas fa-lock me-1"></i>Tugas ini diberikan admin dan isinya tidak dapat diubah.</small></div>@endif
                                <div class="col-md-4"><select name="status" class="form-select"><option value="completed" @selected($dailyActivity->status === 'completed')>Selesai</option><option value="in_progress" @selected($dailyActivity->status === 'in_progress')>Dalam Proses</option><option value="blocked" @selected($dailyActivity->status === 'blocked')>Terhambat</option></select></div>
                                <div class="col-12"><textarea name="notes" class="form-control" rows="2" maxlength="2000" placeholder="Catatan">{{ $dailyActivity->notes }}</textarea></div>
                                <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ route('user.daily-activities.index', ['date' => $selectedDate]) }}" class="btn btn-sm btn-outline-secondary">Batal</a><button class="btn btn-sm btn-primary" type="submit">Simpan Perubahan</button></div>
                            </form>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-5"><i class="fas fa-clipboard-list fa-2x mb-2"></i><p class="mb-0">Belum ada aktivitas pada tanggal ini.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection