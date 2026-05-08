@extends('layouts.admin')

@section('title', 'Buat Form')
@section('page-title', 'Buat Form Checklist Baru')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.forms.store') }}" id="formBuilder">
            @csrf

            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Form</h6>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Form</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" placeholder="Contoh: Checklist Access Point Lt.2" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Schedule</label>
                    <select name="schedule_type" id="scheduleType"
                        class="form-select @error('schedule_type') is-invalid @enderror" required>
                        <option value="daily" {{ old('schedule_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('schedule_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="custom" {{ old('schedule_type') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                    @error('schedule_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3" id="scheduleDaysContainer" style="display: none;">
                <label class="form-label fw-semibold">Hari</label>
                <div class="d-flex flex-wrap gap-3">
                    @php
                    $days =
                    ['Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu','Sun'=>'Minggu'];
                    @endphp
                    @foreach($days as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="schedule_days[]" value="{{ $val }}"
                            id="day_{{ $val }}"
                            {{ (is_array(old('schedule_days')) && in_array($val, old('schedule_days'))) ? 'checked' : '' }}>
                        <label class="form-check-label" for="day_{{ $val }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="row g-3 mb-3" id="customIntervalContainer" style="display: none;">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Interval (hari)</label>
                    <input type="number" name="schedule_interval" class="form-control"
                        value="{{ old('schedule_interval', 1) }}" min="1">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Berakhir <span
                            class="text-muted fw-normal">(opsional)</span></label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi / Tujuan</label>
                <textarea name="description" class="form-control" rows="2"
                    placeholder="Jelaskan tujuan form ini...">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Assign ke User</label>
                <select name="assigned_users[]" class="form-select" multiple size="4">
                    @foreach($users as $u)
                    <option value="{{ $u->id }}"
                        {{ (is_array(old('assigned_users')) && in_array($u->id, old('assigned_users'))) ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                    @endforeach
                </select>
                <div class="form-text">Ctrl+Click untuk memilih banyak user</div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-primary mb-0"><i class="fas fa-list-ul me-2"></i>Item Checklist</h6>
                <button type="button" class="btn btn-sm btn-success" onclick="addItem()">
                    <i class="fas fa-plus me-1"></i>Tambah Item
                </button>
            </div>

            <div id="itemsContainer">
                <!-- Items akan ditambahkan di sini -->
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Publish Form
                </button>
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = 0;

function addItem(data = null) {
    const idx = itemIndex++;
    const container = document.getElementById('itemsContainer');

    const itemTypes = [{
            value: 'text',
            label: 'Text Input'
        },
        {
            value: 'number',
            label: 'Number'
        },
        {
            value: 'textarea',
            label: 'Textarea'
        },
        {
            value: 'checkbox',
            label: 'Checkbox'
        },
        {
            value: 'radio',
            label: 'Radio (Yes/No/NA)'
        },
        {
            value: 'dropdown',
            label: 'Dropdown'
        },
        {
            value: 'signal',
            label: 'Signal Strength'
        },
        {
            value: 'photo',
            label: 'Upload Foto'
        },
    ];

    let typeOptions = itemTypes.map(t =>
        `<option value="${t.value}" ${data?.field_type == t.value ? 'selected' : ''}>${t.label}</option>`
    ).join('');

    const html = `
        <div class="card mb-3 item-card" data-index="${idx}" draggable="true">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-secondary item-number">#${idx + 1}</span>
                    <input type="hidden" name="items[${idx}][order_index]" class="item-order-index" value="${idx}">
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary drag-handle" title="Geser item" style="cursor: move;">
                            <i class="fas fa-grip-lines"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="duplicateItem(this)" title="Copy item">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)" title="Hapus item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Label</label>
                        <input type="text" name="items[${idx}][label]" class="form-control"
                            placeholder="Contoh: IP Address" value="${data?.label || ''}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tipe Field</label>
                        <select name="items[${idx}][field_type]" class="form-select item-type"
                            onchange="toggleOptions(this, ${idx})" required>
                            ${typeOptions}
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end pb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                name="items[${idx}][is_required]" id="req_${idx}"
                                ${data?.is_required ? 'checked' : ''}>
                            <label class="form-check-label" for="req_${idx}">Wajib diisi</label>
                        </div>
                    </div>
                    <div class="col-md-12 options-row-${idx}"
                        style="display: ${['checkbox','radio','dropdown','signal'].includes(data?.field_type) ? 'block' : 'none'};">
                        <label class="form-label small fw-semibold">Opsi (pisahkan dengan koma)</label>
                        <input type="text" name="items[${idx}][options_raw]" class="form-control"
                            placeholder="Online, Offline, Maintenance" value="${data?.options_raw || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Placeholder</label>
                        <input type="text" name="items[${idx}][placeholder]" class="form-control"
                            value="${data?.placeholder || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Helper Text</label>
                        <input type="text" name="items[${idx}][helper_text]" class="form-control"
                            value="${data?.helper_text || ''}">
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    initItemCard(container.lastElementChild);
}

let draggedItem = null;
const itemsContainer = document.getElementById('itemsContainer');

function initItemCard(card) {
    card.addEventListener('dragstart', function(e) {
        draggedItem = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
    });

    card.addEventListener('dragend', function() {
        card.classList.remove('dragging');
    });

    card.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(itemsContainer, e.clientY);
        if (afterElement == null) {
            itemsContainer.appendChild(draggedItem);
        } else {
            itemsContainer.insertBefore(draggedItem, afterElement);
        }
    });

    card.addEventListener('drop', function(e) {
        e.preventDefault();
        refreshItemOrder();
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.item-card:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function refreshItemOrder() {
    document.querySelectorAll('#itemsContainer .item-card').forEach((card, index) => {
        card.querySelector('.item-number').textContent = `#${index + 1}`;
        const orderInput = card.querySelector('.item-order-index');
        if (orderInput) {
            orderInput.value = index;
        }
    });
}

function readItemData(card) {
    return {
        label: card.querySelector('[name$="[label]"]').value,
        field_type: card.querySelector('[name$="[field_type]"]').value,
        options_raw: card.querySelector('[name$="[options_raw]"]').value,
        is_required: card.querySelector('[name$="[is_required]"]').checked,
        placeholder: card.querySelector('[name$="[placeholder]"]').value,
        helper_text: card.querySelector('[name$="[helper_text]"]').value,
    };
}

function duplicateItem(btn) {
    const card = btn.closest('.item-card');
    addItem(readItemData(card));
    refreshItemOrder();
}

function removeItem(btn) {
    btn.closest('.item-card').remove();
    refreshItemOrder();
}

function toggleOptions(select, idx) {
    const needsOptions = ['checkbox', 'radio', 'dropdown', 'signal'].includes(select.value);
    document.querySelector(`.options-row-${idx}`).style.display = needsOptions ? 'block' : 'none';
}

// Schedule type toggle
document.getElementById('scheduleType').addEventListener('change', function() {
    document.getElementById('scheduleDaysContainer').style.display = this.value === 'weekly' ? 'block' : 'none';
    document.getElementById('customIntervalContainer').style.display = this.value === 'custom' ? 'flex' :
        'none';
});

// Init visibility on page load
const oldSchedule = document.getElementById('scheduleType').value;
document.getElementById('scheduleDaysContainer').style.display = oldSchedule === 'weekly' ? 'block' : 'none';
document.getElementById('customIntervalContainer').style.display = oldSchedule === 'custom' ? 'flex' : 'none';
</script>

@if(old('items'))
<script>
const oldItems = {!! json_encode(old('items')) !!};
oldItems.forEach(item => addItem(item));
</script>
@else
<script>
addItem();
</script>
@endif

@endsection