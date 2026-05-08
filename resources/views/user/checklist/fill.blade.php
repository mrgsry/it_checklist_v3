@extends('layouts.user')

@section('title', 'Isi Checklist')
@section('page-title', $form->title)

@section('content')
<div class="mb-3">
    <a href="{{ route('user.dashboard') }}" class="text-decoration-none small text-muted">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<form method="POST" action="{{ route('user.checklist.submit', $form->id) }}" id="checklistForm" enctype="multipart/form-data">
    @csrf

    @foreach($form->items as $item)
    <div class="card mb-3">
        <div class="card-body">
            <label class="fw-semibold d-block mb-2">
                {{ $item->label }}
                @if($item->is_required)
                <span class="text-danger">*</span>
                @endif
            </label>
            @if($item->helper_text)
            <div class="small text-muted mb-2">{{ $item->helper_text }}</div>
            @endif

            @switch($item->field_type)

            @case('text')
            <input type="text" name="answers[{{ $item->id }}]"
                class="form-control @error('answers.'.$item->id) is-invalid @enderror"
                placeholder="{{ $item->placeholder }}" value="{{ old('answers.'.$item->id) }}"
                {{ $item->is_required ? 'required' : '' }}>
            @break

            @case('number')
            <input type="number" name="answers[{{ $item->id }}]"
                class="form-control @error('answers.'.$item->id) is-invalid @enderror"
                placeholder="{{ $item->placeholder }}" value="{{ old('answers.'.$item->id) }}"
                {{ $item->is_required ? 'required' : '' }}>
            @break

            @case('textarea')
            <textarea name="answers[{{ $item->id }}]"
                class="form-control @error('answers.'.$item->id) is-invalid @enderror"
                placeholder="{{ $item->placeholder }}" rows="3"
                {{ $item->is_required ? 'required' : '' }}>{{ old('answers.'.$item->id) }}</textarea>
            @break

            @case('checkbox')
            @if(is_array($item->options) && count($item->options) > 0)
            @foreach($item->options as $opt)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="answers[{{ $item->id }}][]" value="{{ $opt }}"
                    id="cb_{{ $item->id }}_{{ $loop->index }}"
                    {{ (is_array(old('answers.'.$item->id)) && in_array($opt, old('answers.'.$item->id))) ? 'checked' : '' }}>
                <label class="form-check-label" for="cb_{{ $item->id }}_{{ $loop->index }}">
                    {{ $opt }}
                </label>
            </div>
            @endforeach
            @endif
            @break

            @case('radio')
            @if(is_array($item->options) && count($item->options) > 0)
            <div class="d-flex flex-wrap gap-3">
                @foreach($item->options as $opt)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[{{ $item->id }}]" value="{{ $opt }}"
                        id="rb_{{ $item->id }}_{{ $loop->index }}"
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
                class="form-select @error('answers.'.$item->id) is-invalid @enderror"
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
            <input type="file" name="answers[{{ $item->id }}]"
                class="form-control @error('answers.'.$item->id) is-invalid @enderror"
                accept="image/*" capture="environment"
                {{ $item->is_required ? 'required' : '' }}>
            @break

            @case('signal')
            <div class="d-flex gap-2 flex-wrap">
                @php
                $signalOptions = is_array($item->options) && count($item->options) > 0
                ? $item->options
                : ['Good', 'Fair', 'Poor', 'No Signal'];
                @endphp
                @foreach($signalOptions as $opt)
                <input type="radio" class="btn-check" name="answers[{{ $item->id }}]"
                    id="sig_{{ $item->id }}_{{ $loop->index }}" value="{{ $opt }}"
                    {{ old('answers.'.$item->id) == $opt ? 'checked' : '' }} {{ $item->is_required ? 'required' : '' }}>
                <label
                    class="btn btn-outline-{{ $loop->index == 0 ? 'success' : ($loop->index == 1 ? 'info' : ($loop->index == 2 ? 'warning' : 'danger')) }}"
                    for="sig_{{ $item->id }}_{{ $loop->index }}">
                    {{ $opt }}
                </label>
                @endforeach
            </div>
            @break

            @endswitch

            @error('answers.'.$item->id)
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    @endforeach

    <div class="card mb-3">
        <div class="card-body">
            <label class="fw-semibold d-block mb-2">Catatan Tambahan</label>
            <textarea name="notes" class="form-control" rows="2"
                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 mb-4">
        <i class="fas fa-paper-plane me-2"></i>Submit Checklist
    </button>
</form>
@endsection