@extends('layouts.admin')

@section('title', 'Monitoring Daily Activity')
@section('page-title', 'Monitoring Daily Activity')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3"><i class="fas fa-user-plus text-primary me-2"></i>Assign Daily Activity</h5>
        <form method="POST" action="{{ route('admin.daily-activities.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3"><label class="form-label" for="assign_user_id">User</label><select id="assign_user_id" name="user_id" class="form-select" required><option value="">Pilih user</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label" for="assign_date">Tanggal</label><input id="assign_date" type="date" name="activity_date" class="form-control" value="{{ old('activity_date', today()->toDateString()) }}" required></div>
            <div class="col-md-3"><label class="form-label" for="assign_activity">Aktivitas</label><input id="assign_activity" type="text" name="activity" class="form-control" value="{{ old('activity') }}" maxlength="255" required></div>
            <div class="col-md-3"><label class="form-label" for="assign_user_request">User Request</label><input id="assign_user_request" type="text" name="user_request" class="form-control" value="{{ old('user_request') }}" maxlength="255"></div>
            <div class="col-md-2"><label class="form-label" for="assign_category">Kategori</label><select id="assign_category" name="category" class="form-select" required><option value="">Pilih kategori</option>@foreach(\App\Models\DailyActivity::CATEGORIES as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label" for="assign_notes">Catatan</label><input id="assign_notes" type="text" name="notes" class="form-control" value="{{ old('notes') }}" maxlength="2000"></div>
            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit" title="Simpan penugasan"><i class="fas fa-paper-plane"></i></button></div>
        </form>
        @if($errors->any())<div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>@endif
        @if(session('success'))<div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>@endif
    </div>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div><h5 class="card-title mb-1"><i class="fas fa-clipboard-list text-primary me-2"></i>Daily Activity Staff</h5><p class="text-muted small mb-0">Pantau aktivitas harian seluruh staff.</p></div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.daily-activities.export-pdf', request()->query()) }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <a href="{{ route('admin.daily-activities.export-excel', request()->query()) }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel me-1"></i>Excel</a>
            </div>
        </div>
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-3"><label class="form-label small mb-1" for="filter_date_from">Dari Tanggal</label><input id="filter_date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" aria-label="Dari tanggal"></div>
            <div class="col-md-3"><label class="form-label small mb-1" for="filter_date_to">Sampai Tanggal</label><input id="filter_date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" aria-label="Sampai tanggal"></div>
            <div class="col-md-2"><label class="form-label small mb-1" for="filter_user_id">Staff</label><select id="filter_user_id" name="user_id" class="form-select"><option value="">Semua Staff</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small mb-1" for="filter_status">Status</label><select id="filter_status" name="status" class="form-select"><option value="">Semua Status</option><option value="completed" @selected(request('status') === 'completed')>Selesai</option><option value="in_progress" @selected(request('status') === 'in_progress')>Dalam Proses</option><option value="blocked" @selected(request('status') === 'blocked')>Terhambat</option></select></div>
            <div class="col-md-2"><label class="form-label small mb-1" for="filter_type">Jenis</label><select id="filter_type" name="type" class="form-select"><option value="">Semua Jenis</option><option value="daily_activity" @selected(request('type') === 'daily_activity')>Daily Activity</option><option value="ticketing" @selected(request('type') === 'ticketing')>Ticketing</option></select></div>
            <div class="col-md-2"><label class="form-label small mb-1" for="filter_category">Kategori</label><select id="filter_category" name="category" class="form-select"><option value="">Semua Kategori</option>@foreach(\App\Models\DailyActivity::CATEGORIES as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></div>
            <div class="col-md-2 d-flex align-items-end gap-2"><button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i>Filter</button><a href="{{ route('admin.daily-activities.index') }}" class="btn btn-outline-secondary">Reset</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Staff</th><th>User Request</th><th>Jenis</th><th>Kategori</th><th>Aktivitas</th><th>Status</th><th>Catatan</th><th>Diperbarui</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php($status = ['completed' => ['success', 'Selesai'], 'in_progress' => ['warning', 'Dalam Proses'], 'blocked' => ['danger', 'Terhambat']][$activity->status])
                        @php($whatsAppMessage = implode("\n", array_filter([
                            'Halo ' . $activity->user->name . ',',
                            '',
                            'Berikut pembaruan Daily Task Anda.',
                            '',
                            'Tanggal: ' . $activity->activity_date->isoFormat('D MMM Y'),
                            'Tugas: ' . $activity->activity,
                            'Status: ' . $status[1],
                            $activity->notes ? 'Catatan: ' . $activity->notes : null,
                            '',
                            'Silakan perbarui status setelah pekerjaan selesai.',
                            'Terima kasih.',
                        ], fn ($line) => $line !== null)))
                        <tr><td>{{ $activity->activity_date->isoFormat('D MMM Y') }}</td><td class="fw-semibold">{{ $activity->user->name }}</td><td>{{ $activity->user_request ?? '-' }}</td><td>{{ $activity->type === 'ticketing' ? 'Ticketing' : 'Daily Activity' }}</td><td>{{ $activity->category }}</td><td>{{ $activity->activity }} @if($activity->ticket_url)<a href="{{ $activity->ticket_url }}" target="_blank" rel="noopener" title="Lihat progress ticket"><i class="fas fa-chart-line"></i></a>@endif</td><td><span class="badge text-bg-{{ $status[0] }}">{{ $status[1] }}</span></td><td class="text-muted">{{ $activity->notes ?: '-' }}</td><td>{{ $activity->updated_at->format('d M Y, H:i') }}</td><td class="text-end"><a href="https://wa.me/?text={{ rawurlencode($whatsAppMessage) }}" class="btn btn-sm btn-success" target="_blank" rel="noopener noreferrer" title="Kirim detail tugas ke WhatsApp" aria-label="Kirim detail tugas ke WhatsApp"><i class="fab fa-whatsapp"></i></a></td></tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-clipboard-list d-block fa-2x mb-2"></i>Belum ada daily activity yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())<div class="mt-4">{{ $activities->links() }}</div>@endif
    </div>
</div>
@endsection