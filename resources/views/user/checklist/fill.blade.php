@extends('layouts.user')

@section('title', 'Isi Checklist')
@section('page-title', $form->title)

@section('content')
<style>
    /* ===== Responsive & UX tweaks for checklist form ===== */
    .checklist-wrapper {
        max-width: 720px;
        margin: 0 auto;
        padding-bottom: 90px; /* space for sticky submit bar on mobile */
    }

    .card-createspace {
        border-radius: 14px;
    }

    .card-createspace .card-body {
        padding: 1rem 1.1rem;
    }

    /* Bigger, easier-to-tap form controls on touch devices */
    .input-createspace,
    textarea.input-createspace,
    select.input-createspace {
        width: 100%;
        min-height: 46px;
        font-size: 1rem;
        padding: 0.6rem 0.75rem;
    }

    textarea.input-createspace {
        min-height: 90px;
        resize: vertical;
    }

    /* Checkbox / radio: bigger tap target, spacing between options */
    .form-check {
        min-height: 44px;
        display: flex;
        align-items: center;
        padding-left: 0;
        margin-bottom: 0.4rem;
    }

    .form-check-input {
        width: 1.15em;
        height: 1.15em;
        margin-right: 0.6rem;
        margin-left: 0;
        flex-shrink: 0;
    }

    .form-check-label {
        line-height: 1.3;
    }

    /* Radio groups (plain radio list) wrap nicely and stack on small screens */
    .radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1.25rem;
    }

    /* Signal / pill-style buttons: full width rows on mobile */
    .signal-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .signal-group label.btn-createspace {
        flex: 1 1 auto;
        min-width: 100px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin: 0;
    }

    .btn-check:checked + label.btn-createspace {
        box-shadow: inset 0 0 0 2px currentColor;
        font-weight: 600;
    }

    /* Photo upload buttons */
    .photo-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .photo-actions .btn-createspace {
        flex: 1 1 140px;
        min-height: 44px;
    }

    #prev_placeholder_img,
    .img-thumbnail {
        max-width: 100%;
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* Sticky submit bar so the main action is always reachable */
    .submit-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--bs-body-bg, #fff);
        padding: 0.75rem 0 calc(0.75rem + env(safe-area-inset-bottom));
        margin-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.06);
        z-index: 5;
    }

    .submit-bar .btn-createspace {
        min-height: 50px;
        font-size: 1.05rem;
    }

    .field-error-text {
        font-size: 0.85rem;
    }

    .upload-limit-note {
        font-size: 0.85rem;
    }

    /* Small screens: tighten padding, single-column layout is already default */
    @media (max-width: 576px) {
        .checklist-wrapper {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        .card-createspace .card-body {
            padding: 0.85rem 0.9rem;
        }

        .signal-group label.btn-createspace {
            min-width: 45%;
        }
    }
</style>

<div class="checklist-wrapper">

    <div class="mb-3">
        <a href="{{ route('user.dashboard') }}" class="text-decoration-none text-body small">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-triangle-exclamation me-1"></i>
        Beberapa jawaban belum lengkap atau tidak valid. Mohon periksa kembali sebelum submit.
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-triangle-exclamation me-1"></i>{{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('user.checklist.submit', $form->id) }}" id="checklistForm"
        enctype="multipart/form-data" novalidate>
        @csrf

        @foreach($form->items as $item)
        <div class="card-createspace mb-3">
            <div class="card-body">
                <label class="font-body font-semibold d-block mb-2">
                    {{ $item->label }}
                    @if($item->is_required)
                    <span class="text-error">*</span>
                    @endif
                </label>
                @if($item->helper_text)
                <div class="text-caption text-muted mb-2">{{ $item->helper_text }}</div>
                @endif

                @switch($item->field_type)

                @case('text')
                <input type="text" name="answers[{{ $item->id }}]"
                    class="input-createspace @error('answers.'.$item->id) input-error @enderror"
                    placeholder="{{ $item->placeholder }}" value="{{ old('answers.'.$item->id) }}"
                    {{ $item->is_required ? 'required' : '' }}>
                @break

                @case('number')
                <input type="number" inputmode="decimal" name="answers[{{ $item->id }}]"
                    class="input-createspace @error('answers.'.$item->id) input-error @enderror"
                    placeholder="{{ $item->placeholder }}" value="{{ old('answers.'.$item->id) }}"
                    {{ $item->is_required ? 'required' : '' }}>
                @break

                @case('textarea')
                <textarea name="answers[{{ $item->id }}]"
                    class="input-createspace @error('answers.'.$item->id) input-error @enderror"
                    placeholder="{{ $item->placeholder }}" rows="3"
                    {{ $item->is_required ? 'required' : '' }}>{{ old('answers.'.$item->id) }}</textarea>
                @break

                @case('checkbox')
                @if(is_array($item->options) && count($item->options) > 0)
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Detail Check</th><th class="text-center">Normal</th><th class="text-center">Tidak Normal</th></tr></thead>
                        <tbody>
                @foreach($item->options as $index => $opt)
                <tr>
                    <td>{{ $opt }}</td>
                    <td class="text-center"><input class="form-check-input" type="radio" name="answers[{{ $item->id }}][{{ $index }}]" value="normal" id="cb_{{ $item->id }}_{{ $index }}_normal" {{ old('answers.'.$item->id.'.'.$index) === 'normal' ? 'checked' : '' }} {{ $item->is_required ? 'required' : '' }} aria-label="{{ $opt }} Normal"></td>
                    <td class="text-center"><input class="form-check-input" type="radio" name="answers[{{ $item->id }}][{{ $index }}]" value="tidak_normal" id="cb_{{ $item->id }}_{{ $index }}_abnormal" {{ old('answers.'.$item->id.'.'.$index) === 'tidak_normal' ? 'checked' : '' }} aria-label="{{ $opt }} Tidak Normal"></td>
                </tr>
                @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @break

                @case('radio')
                @if(is_array($item->options) && count($item->options) > 0)
                <div class="radio-group">
                    @foreach($item->options as $opt)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[{{ $item->id }}]"
                            value="{{ $opt }}" id="rb_{{ $item->id }}_{{ $loop->index }}"
                            {{ old('answers.'.$item->id) == $opt ? 'checked' : '' }}
                            {{ $item->is_required ? 'required' : '' }}>
                        <label class="form-check-label" for="rb_{{ $item->id }}_{{ $loop->index }}">
                            {{ $opt }}
                        </label>
                    </div>
                    @endforeach
                </div>
                @endif
                @break

                @case('dropdown')
                <select name="answers[{{ $item->id }}]"
                    class="input-createspace @error('answers.'.$item->id) input-error @enderror"
                    {{ $item->is_required ? 'required' : '' }}>
                    <option value="">-- Pilih --</option>
                    @if(is_array($item->options))
                    @foreach($item->options as $opt)
                    <option value="{{ $opt }}" {{ old('answers.'.$item->id) == $opt ? 'selected' : '' }}>
                        {{ $opt }}
                    </option>
                    @endforeach
                    @endif
                </select>
                @break

                @case('photo')
                <div>
                    <div class="photo-actions mb-2">
                        <button type="button" class="btn-createspace btn-sm btn-secondary"
                            onclick="triggerPhoto('photo_{{ $item->id }}', false)">
                            <i class="fas fa-images me-1"></i>Galeri
                        </button>
                        <button type="button" class="btn-createspace btn-sm btn-primary"
                            onclick="triggerPhoto('photo_{{ $item->id }}', true)">
                            <i class="fas fa-camera me-1"></i>Kamera
                        </button>
                    </div>

                    <input type="file" id="photo_{{ $item->id }}" name="answers[{{ $item->id }}][]"
                        class="d-none @error('answers.'.$item->id) input-error @enderror" accept="image/*" multiple
                        {{ $item->is_required ? 'required' : '' }}
                        onchange="previewPhotos(this, 'prevwrap_{{ $item->id }}')">

                    <div class="text-muted upload-limit-note mt-2">Maksimal 5 foto per item, 5 MB per foto, dan total semua foto maksimal 20 MB. Foto akan dikompres otomatis saat dipublish menjadi di bawah 1 MB.</div>

                    <div id="prevwrap_{{ $item->id }}" class="d-flex flex-wrap gap-2 mt-2 d-none"></div>

                    @error('answers.'.$item->id)
                    <div class="invalid-feedback d-block field-error-text">{{ $message }}</div>
                    @enderror
                    @error('answers.'.$item->id.'.*')
                    <div class="invalid-feedback d-block field-error-text">{{ $message }}</div>
                    @enderror
                </div>
                @break

                @case('signal')
                <div class="signal-group">
                    @php
                    $signalOptions = is_array($item->options) && count($item->options) > 0
                    ? $item->options
                    : ['Good', 'Fair', 'Poor', 'No Signal'];
                    @endphp
                    @foreach($signalOptions as $opt)
                    <input type="radio" class="btn-check" name="answers[{{ $item->id }}]"
                        id="sig_{{ $item->id }}_{{ $loop->index }}" value="{{ $opt }}"
                        {{ old('answers.'.$item->id) == $opt ? 'checked' : '' }}
                        {{ $item->is_required ? 'required' : '' }}>
                    <label class="btn-createspace btn-md btn-secondary" for="sig_{{ $item->id }}_{{ $loop->index }}">
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
                @break

                @endswitch

                @error('answers.'.$item->id)
                <div class="invalid-feedback d-block field-error-text">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endforeach

        <div class="card-createspace mb-3">
            <div class="card-body">
                <label class="font-body font-semibold d-block mb-2">Catatan Tambahan</label>
                <textarea name="notes" class="input-createspace" rows="2"
                    placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="submit-bar">
            <button type="submit" class="btn-createspace btn-lg btn-primary w-100" id="submitBtn">
                <i class="fas fa-paper-plane me-2"></i><span id="submitBtnText">Submit Checklist</span>
            </button>
        </div>
    </form>
</div>

<script>
function triggerPhoto(inputId, useCamera) {
    const input = document.getElementById(inputId);
    if (useCamera) {
        input.setAttribute('capture', 'environment');
    } else {
        input.removeAttribute('capture');
    }
    input.click();
}

function previewPhotos(input, wrapId) {
    const files = [...(input._photoFiles || []), ...Array.from(input.files || [])];
    const wrap = document.getElementById(wrapId);

    if (files.length > 5) {
        alert('Maksimal 5 foto untuk setiap item checklist.');
        updatePhotoFiles(input, input._photoFiles || []);
        return;
    }

    updatePhotoFiles(input, files);
}

function updatePhotoFiles(input, files) {
    const wrap = document.getElementById('prevwrap_' + input.id.split('_')[1]);
    const transfer = new DataTransfer();
    files.forEach(file => transfer.items.add(file));
    input._photoFiles = files;
    input.files = transfer.files;
    wrap.replaceChildren();
    files.forEach((file, index) => {
        const container = document.createElement('div');
        container.className = 'position-relative';
        const preview = document.createElement('img');
        preview.className = 'img-thumbnail';
        preview.style.cssText = 'width:96px;height:96px;object-fit:cover;';
        preview.src = URL.createObjectURL(file);
        preview.onload = () => URL.revokeObjectURL(preview.src);
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
        remove.setAttribute('aria-label', 'Hapus foto');
        remove.innerHTML = '<i class="fas fa-times"></i>';
        remove.addEventListener('click', () => {
            updatePhotoFiles(input, files.filter((_, selectedIndex) => selectedIndex !== index));
        });
        container.append(preview, remove);
        wrap.appendChild(container);
    });
    wrap.classList.toggle('d-none', files.length === 0);
}

// Prevent double-submit and give feedback while the form is posting
document.getElementById('checklistForm').addEventListener('submit', function (e) {
    const form = this;
    if (!form.checkValidity()) {
        // Let native validation messages show, but scroll to first invalid field
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) {
            e.preventDefault();
            form.reportValidity();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    const maxTotalBytes = 20 * 1024 * 1024;
    const selectedFiles = Array.from(form.querySelectorAll('input[type="file"]'))
        .flatMap(input => Array.from(input.files || []));
    const totalBytes = selectedFiles.reduce((total, file) => total + file.size, 0);

    if (totalBytes > maxTotalBytes) {
        e.preventDefault();
        alert('Ukuran total foto terlalu besar. Maksimal total upload adalah 20 MB. Silakan kurangi atau kompres foto.');
        const firstPhoto = form.querySelector('input[type="file"]');
        if (firstPhoto) firstPhoto.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('submitBtnText');
    btn.disabled = true;
    btnText.textContent = 'Mengirim...';
});
</script>
@endsection