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
                    @foreach($forms as $f)
                    <option value="{{ $f->id }}" {{ request('form_id') == $f->id ? 'selected' : '' }}>{{ $f->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="text-caption font-semibold mb-1 d-block">User</label>
                <select name="user_id" class="input-createspace">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="text-caption font-semibold mb-1 d-block">Dari</label>
                <input type="date" name="date_from" class="input-createspace" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="text-caption font-semibold mb-1 d-block">Sampai</label>
                <input type="date" name="date_to" class="input-createspace" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn-createspace btn-md btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card-createspace">
    <div class="card-body">
        <h5 class="font-headline font-semibold mb-4">Daftar Submission</h5>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-muted">Data akan diperbarui otomatis saat ada submission baru.</small>
            <span class="badge text-bg-light" id="submissions-refresh-status">Memuat...</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-muted">Data akan diperbarui otomatis saat ada submission baru.</small>
            <span class="badge text-bg-light" id="submissions-refresh-status">Memuat...</span>
        </div>
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
                <tbody id="submissions-table-body">
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
                            <span class="chip chip-status-archived" style="background-color: #FEE2E2; color: #DC2626;"><i
                                    class="fas fa-exclamation-circle me-1"></i>{{ $flaggedCount }} Masalah</span>
                            @else
                            <span class="chip chip-status-complete"><i class="fas fa-check me-1"></i>Lengkap</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.submissions.show', $sub) }}" class="btn-createspace btn-sm btn-secondary" style="min-width: 32px; padding: 0;">
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

        <div id="submissions-pagination">
            {{ $submissions->links() }}
        </div>
    </div>
</div>

<script>
(() => {
    const tableBody = document.getElementById('submissions-table-body');
    const refreshStatus = document.getElementById('submissions-refresh-status');
    const pagination = document.getElementById('submissions-pagination');
    const endpoint = new URL(window.location.href);
    endpoint.searchParams.set('format_json', '1');

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&',
        '<': '<',
        '>': '>',
        "'": '&#039;',
        '"': '"',
    }[character]));

    function renderRow(item) {
        const statusBadge = item.flagged_count > 0
            ? `<span class="chip chip-status-archived" style="background-color: #FEE2E2; color: #DC2626;"><i class="fas fa-exclamation-circle me-1"></i>${escapeHtml(item.flagged_count)} Masalah</span>`
            : `<span class="chip chip-status-complete"><i class="fas fa-check me-1"></i>Lengkap</span>`;

        return `
            <tr>
                <td>${escapeHtml(item.form_title)}</td>
                <td>${escapeHtml(item.user_name)}</td>
                <td>${escapeHtml(item.submission_date)}</td>
                <td>${escapeHtml(item.submitted_at)}</td>
                <td>${statusBadge}</td>
                <td>
                    <a href="${escapeHtml(item.show_url)}" class="btn-createspace btn-sm btn-secondary" style="min-width: 32px; padding: 0;">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    }

    async function refreshSubmissions() {
        try {
            const response = await fetch(endpoint.toString(), { headers: { Accept: 'application/json' } });
            if (!response.ok) return;

            const payload = await response.json();
            const items = payload.data ?? [];

            tableBody.innerHTML = items.length
                ? items.map(renderRow).join('')
                : `<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data</td></tr>`;

            refreshStatus.textContent = `Diperbarui ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
            refreshStatus.className = 'badge text-bg-success';
        } catch (error) {
            refreshStatus.textContent = 'Polling offline';
            refreshStatus.className = 'badge text-bg-warning';
        }
    }

    refreshSubmissions();
    window.setInterval(refreshSubmissions, 10000);
})();
</script>
@endsection
