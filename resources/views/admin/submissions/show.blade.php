@extends('layouts.admin')

@section('title', 'Detail Submission')
@section('page-title', 'Detail Submission')

@section('content')
<style>
    .submission-answer-table {
        table-layout: fixed;
        width: 100%;
    }

    .submission-answer-table th:nth-child(1),
    .submission-answer-table td:nth-child(1) { width: 30%; }
    .submission-answer-table th:nth-child(2),
    .submission-answer-table td:nth-child(2) { width: 40%; }
    .submission-answer-table th:nth-child(3),
    .submission-answer-table td:nth-child(3),
    .submission-answer-table th:nth-child(4),
    .submission-answer-table td:nth-child(4) { width: 15%; }
    .submission-answer-table td { overflow-wrap: anywhere; }
    .submission-answer-table .abnormal-detail,
    .submission-answer-table .abnormal-mark { color: #dc2626; font-weight: 700; }
</style>
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
                        <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn btn-sm btn-primary" title="Edit submission"><i class="fas fa-pen me-1"></i>Edit</a>
                        <form method="POST" action="{{ route('admin.submissions.destroy', $submission) }}" onsubmit="return confirm('Hapus submission ini? Jawaban dan foto akan dihapus, tetapi histori aktivitas tetap tersimpan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus submission"><i class="fas fa-trash me-1"></i>Hapus</button>
                        </form>
                        @php
                        $problemList = collect();
                        foreach ($submission->answers as $answer) {
                            if ($answer->formItem?->field_type === 'checkbox' && filled($answer->formItem?->options)) {
                                foreach ($answer->checkboxStatuses() as $option => $status) {
                                    if ($status === 'tidak_normal') {
                                        $problemList->push([
                                            'item' => $answer->formItem?->label ?? '-',
                                            'detail' => $option,
                                        ]);
                                    }
                                }
                            } elseif ($answer->is_flagged) {
                                $problemList->push([
                                    'item' => $answer->formItem?->label ?? '-',
                                    'detail' => $answer->answer_value ?: '-',
                                ]);
                            }
                        }
                        $problemGroups = $problemList->groupBy('item');
                        $problemCount = $problemList->count();
                        @endphp
                        @if($problemCount > 0)
                        <button type="button" class="chip chip-status-archived border-0" style="background-color: #FEE2E2; color: #DC2626; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#submission-problems-modal">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $problemCount }} Masalah
                        </button>
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
                    <table class="table table-hover align-middle submission-answer-table">
                        <colgroup><col style="width:30%"><col style="width:40%"><col style="width:15%"><col style="width:15%"></colgroup>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Detail Check</th>
                                <th class="text-center">Normal</th>
                                <th class="text-center">Tidak Normal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submission->answers as $answer)
                            @if($answer->formItem?->field_type === 'checkbox' && filled($answer->formItem?->options))
                            @php($checkboxStatuses = $answer->checkboxStatuses())
                            @foreach($answer->formItem->options as $option)
                            @php($isAbnormal = ($checkboxStatuses[$option] ?? null) === 'tidak_normal')
                            <tr>
                                @if($loop->first)<td class="font-semibold" rowspan="{{ count($answer->formItem->options) }}">{{ $answer->formItem->label ?? '-' }}</td>@endif
                                <td class="{{ $isAbnormal ? 'abnormal-detail' : '' }}">{{ $option }}</td>
                                <td class="text-center">{{ ($checkboxStatuses[$option] ?? null) === 'normal' ? '☑' : '☐' }}</td>
                                <td class="text-center {{ $isAbnormal ? 'abnormal-mark' : '' }}">{{ $isAbnormal ? '☑' : '☐' }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td class="font-semibold">{{ $answer->formItem->label ?? '-' }}</td>
                                <td colspan="3" class="{{ $answer->is_flagged ? 'text-danger fw-bold' : '' }}">
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
                            @endif
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

@if($problemCount > 0)
<div class="modal fade" id="submission-problems-modal" tabindex="-1" aria-labelledby="submission-problems-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-headline" id="submission-problems-title">
                    <i class="fas fa-triangle-exclamation text-danger me-2"></i>Daftar Masalah
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    @foreach($problemGroups as $item => $problems)
                    @php($ticket = $submission->ticketing_data[$item] ?? null)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold">{{ $item }}</div>
                                <div class="text-danger">{{ $problems->pluck('detail')->implode(', ') }}</div>
                            </div>
                            @if(filled($ticket['ticket_url'] ?? null))
                            <a href="{{ $ticket['ticket_url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success text-nowrap">
                                <i class="fas fa-chart-line me-1"></i>Lihat Progress
                            </a>
                            @else
                            <button type="button" class="btn btn-sm btn-outline-danger text-nowrap create-ticket-button" data-ticket-item="{{ $item }}" data-ticket-description="{{ 'Masalah ' . $problems->pluck('detail')->implode(', ') . ' pada ' . $item . '.' }}">
                                <i class="fas fa-ticket-alt me-1"></i>Create Ticket
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($problemCount > 0)
<div class="modal fade" id="ticket-draft-modal" tabindex="-1" aria-labelledby="ticket-draft-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-headline" id="ticket-draft-title">
                    <i class="fas fa-ticket-alt text-primary me-2"></i>Draft Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="card-createspace bg-light mb-3">
                    <div class="card-body p-3">
                        <div class="text-muted text-caption mb-1">Item</div>
                        <div class="font-semibold" id="ticket-draft-item"></div>
                    </div>
                </div>
                <form id="ticket-create-form">
                    @csrf
                    <input type="hidden" name="item" id="ticket-item-input">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ticket-department" class="form-label">Service Department</label>
                            <select name="service_department" id="ticket-department" class="form-select" required disabled><option value="">Memuat department...</option></select>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket-type" class="form-label">Tipe Ticket</label>
                            <select name="type" id="ticket-type" class="form-select" required disabled><option value="">Pilih department dahulu</option></select>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket-category" class="form-label">Kategori</label>
                            <select name="category" id="ticket-category" class="form-select" required disabled><option value="">Pilih tipe ticket dahulu</option></select>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket-user" class="form-label">Nama Pelapor</label>
                            <input name="user" id="ticket-user" class="form-control" value="{{ $submission->submitter?->name ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket-departement" class="form-label">Departemen</label>
                            <input name="departement" id="ticket-departement" class="form-control" value="IT" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket-contact" class="form-label">Kontak</label>
                            <input name="contact" id="ticket-contact" class="form-control" placeholder="Nomor telepon" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ticket-email" class="form-label">Email</label>
                            <input type="email" name="email" id="ticket-email" class="form-control" value="{{ $submission->submitter?->email ?? '' }}" required>
                        </div>
                        <div class="col-12">
                            <label for="ticket-draft-description" class="form-label font-semibold">Deskripsi Masalah</label>
                            <textarea name="detail" id="ticket-draft-description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-8">
                            <label for="ticket-location" class="form-label">Lokasi</label>
                            <input name="location" id="ticket-location" class="form-control" placeholder="Lokasi masalah" required>
                        </div>
                        <div class="col-md-4">
                            <label for="ticket-remote-code" class="form-label">Remote Code <span class="text-muted">(opsional)</span></label>
                            <input name="remote_code" id="ticket-remote-code" class="form-control">
                        </div>
                    </div>
                    <div id="ticket-form-status" class="small mt-3" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="ticket-create-form" class="btn btn-primary" id="ticket-submit-button">
                    <i class="fas fa-paper-plane me-1"></i>Eskalasi ke Ticketing
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const problemsModalElement = document.getElementById('submission-problems-modal');
        const ticketDraftModalElement = document.getElementById('ticket-draft-modal');
        const ticketItemElement = document.getElementById('ticket-draft-item');
        const ticketDescriptionElement = document.getElementById('ticket-draft-description');
        const ticketItemInput = document.getElementById('ticket-item-input');
        const ticketForm = document.getElementById('ticket-create-form');
        const departmentElement = document.getElementById('ticket-department');
        const typeElement = document.getElementById('ticket-type');
        const categoryElement = document.getElementById('ticket-category');
        const statusElement = document.getElementById('ticket-form-status');
        const submitButton = document.getElementById('ticket-submit-button');
        let activeCreateButton = null;

        const lookup = async (url) => {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const body = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(body.message || 'Lookup ticketing gagal.');
            return Array.isArray(body) ? body : (body.data || body.departments || body.types || body.categories || []);
        };

        const optionValue = (option) => option.module_id ?? option.department_code ?? option.category ?? option.id ?? option.value ?? option.name ?? option;
        const optionLabel = (option) => option.department_name ?? option.module_name ?? option.type_name ?? option.category_name ?? option.name ?? option.label ?? option.title ?? option.category ?? option;
        const fillSelect = (element, options, placeholder) => {
            element.innerHTML = `<option value="">${placeholder}</option>`;
            options.forEach((option) => element.add(new Option(optionLabel(option), optionValue(option))));
            element.disabled = options.length === 0;
        };

        const loadDepartments = async () => {
            const departments = await lookup('{{ route('admin.ticketing.departments') }}');
            fillSelect(departmentElement, departments, 'Pilih department');
            const defaultDepartment = departments.find((department) => optionLabel(department).toUpperCase() === 'IT');
            if (defaultDepartment) {
                departmentElement.value = optionValue(defaultDepartment);
                departmentElement.dispatchEvent(new Event('change'));
            }
        };

        departmentElement.addEventListener('change', async () => {
            typeElement.disabled = true;
            categoryElement.disabled = true;
            fillSelect(typeElement, [], 'Memuat tipe ticket...');
            fillSelect(categoryElement, [], 'Pilih tipe ticket dahulu');
            if (!departmentElement.value) return;
            try {
                const types = await lookup(`{{ route('admin.ticketing.types') }}?department=${encodeURIComponent(departmentElement.value)}`);
                fillSelect(typeElement, types, 'Pilih tipe ticket');
            } catch (error) { statusElement.textContent = error.message; statusElement.className = 'small mt-3 text-danger'; }
        });

        typeElement.addEventListener('change', async () => {
            fillSelect(categoryElement, [], 'Memuat kategori...');
            if (!typeElement.value || !departmentElement.value) return;
            try {
                const categories = await lookup(`{{ route('admin.ticketing.categories') }}?department=${encodeURIComponent(departmentElement.value)}&type=${encodeURIComponent(typeElement.value)}`);
                fillSelect(categoryElement, categories, 'Pilih kategori');
            } catch (error) { statusElement.textContent = error.message; statusElement.className = 'small mt-3 text-danger'; }
        });

        document.querySelectorAll('.create-ticket-button').forEach(function(button) {
            button.addEventListener('click', function() {
                activeCreateButton = button;
                ticketItemElement.textContent = button.dataset.ticketItem;
                ticketItemInput.value = button.dataset.ticketItem;
                ticketDescriptionElement.value = button.dataset.ticketDescription;
                statusElement.textContent = 'Memuat pilihan ticketing...';
                statusElement.className = 'small mt-3 text-muted';
                loadDepartments().catch((error) => { statusElement.textContent = error.message; statusElement.className = 'small mt-3 text-danger'; });

                problemsModalElement.addEventListener('hidden.bs.modal', function() {
                    bootstrap.Modal.getOrCreateInstance(ticketDraftModalElement).show();
                }, { once: true });
                bootstrap.Modal.getOrCreateInstance(problemsModalElement).hide();
            });
        });

        ticketForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            submitButton.disabled = true;
            statusElement.textContent = 'Mengirim ticket...';
            statusElement.className = 'small mt-3 text-muted';
            try {
                const response = await fetch('{{ route('admin.ticketing.create', $submission) }}', {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: new FormData(ticketForm),
                });
                const body = await response.json().catch(() => ({}));
                if (!response.ok || body.success === false) throw new Error(body.message || 'Ticket gagal dibuat.');
                const ticket = body.data || body.ticket || body;
                if (!ticket.ticket_number && !ticket.ticket_url && !ticket.number && !ticket.url) throw new Error(body.message || 'Respons ticketing tidak berisi nomor atau URL ticket.');
                const ticketNumber = ticket.ticket_number || ticket.number || ticket.ticket_no || '-';
                const ticketUrl = ticket.ticket_url || ticket.url || '';
                statusElement.innerHTML = `Ticket berhasil dibuat: <strong>${ticketNumber}</strong>${ticketUrl ? ` — <a href="${ticketUrl}" target="_blank" rel="noopener">Buka ticket</a>` : ''}`;
                statusElement.className = 'small mt-3 text-success';
                if (ticketUrl && activeCreateButton) {
                    const progressLink = document.createElement('a');
                    progressLink.href = ticketUrl;
                    progressLink.target = '_blank';
                    progressLink.rel = 'noopener';
                    progressLink.className = 'btn btn-sm btn-outline-success text-nowrap';
                    progressLink.innerHTML = '<i class="fas fa-chart-line me-1"></i>Lihat Progress';
                    activeCreateButton.replaceWith(progressLink);
                    activeCreateButton = null;
                }
            } catch (error) {
                statusElement.textContent = error.message;
                statusElement.className = 'small mt-3 text-danger';
            } finally { submitButton.disabled = false; }
        });
    });
</script>
@endif
@endsection
