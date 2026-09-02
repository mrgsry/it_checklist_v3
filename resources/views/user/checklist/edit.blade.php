@extends($layout ?? 'layouts.user')

@section('title', 'Edit Checklist')
@section('page-title', 'Edit '.$submission->form->title)

@section('content')
<div class="container-fluid" style="max-width: 720px;">
    <div class="mb-3"><a href="{{ $backRoute ?? route('user.history') }}" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Kembali</a></div>
    @if ($errors->any())<div class="alert alert-danger">Periksa kembali jawaban yang belum valid.</div>@endif
    <form method="POST" action="{{ $updateRoute ?? route('user.submissions.update', $submission) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @foreach($submission->form->items as $item)
            @php($answer = $submission->answers->firstWhere('form_item_id', $item->id))
            @php($value = old('answers.'.$item->id, $answer?->answer_value))
            @php($checkboxValues = old('answers.'.$item->id, $answer?->checkboxStatuses() ?? []))
            <div class="card-createspace mb-3"><div class="card-body">
                <label class="font-body font-semibold d-block mb-2">{{ $item->label }} @if($item->is_required)<span class="text-error">*</span>@endif</label>
                @if($item->helper_text)<div class="text-muted small mb-2">{{ $item->helper_text }}</div>@endif
                @if($item->field_type === 'textarea')
                    <textarea name="answers[{{ $item->id }}]" class="input-createspace" rows="3" {{ $item->is_required ? 'required' : '' }}>{{ $value }}</textarea>
                @elseif($item->field_type === 'checkbox')
                    @foreach($item->options ?? [] as $index => $option)
                        <div class="mb-2"><span>{{ $option }}</span>
                        <label class="ms-2"><input type="radio" name="answers[{{ $item->id }}][{{ $index }}]" value="normal" {{ ($checkboxValues[$option] ?? $checkboxValues[$index] ?? null) === 'normal' ? 'checked' : '' }} {{ $item->is_required ? 'required' : '' }}> Normal</label>
                        <label class="ms-2"><input type="radio" name="answers[{{ $item->id }}][{{ $index }}]" value="tidak_normal" {{ ($checkboxValues[$option] ?? $checkboxValues[$index] ?? null) === 'tidak_normal' ? 'checked' : '' }}> Tidak Normal</label></div>
                    @endforeach
                @elseif($item->field_type === 'photo')
                    @if($answer?->photoPaths() !== [])<div class="d-flex flex-wrap gap-2 mb-2">@foreach($answer?->photoPaths() ?? [] as $path)<img src="{{ Storage::url($path) }}" alt="Foto sebelumnya" class="img-thumbnail" style="width:90px;height:90px;object-fit:cover;">@endforeach</div>@endif
                    <input type="file" name="answers[{{ $item->id }}][]" class="form-control" accept="image/jpeg,image/png" multiple>
                    <small class="text-muted">Pilih foto baru untuk mengganti seluruh foto di atas (maksimal 5).</small>
                @elseif($item->field_type === 'signal')
                    @foreach($item->options ?? ['Good', 'Fair', 'Poor', 'No Signal'] as $option)<label class="me-3"><input type="radio" name="answers[{{ $item->id }}]" value="{{ $option }}" {{ $value == $option ? 'checked' : '' }} {{ $item->is_required ? 'required' : '' }}> {{ $option }}</label>@endforeach
                @else
                    <input type="{{ $item->field_type === 'number' ? 'number' : 'text' }}" name="answers[{{ $item->id }}]" class="input-createspace" value="{{ $value }}" {{ $item->is_required ? 'required' : '' }}>
                @endif
                @error('answers.'.$item->id)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div></div>
        @endforeach
        <div class="card-createspace mb-3"><div class="card-body"><label class="font-body font-semibold d-block mb-2">Catatan Tambahan</label><textarea name="notes" class="input-createspace" rows="2">{{ old('notes', $submission->notes) }}</textarea></div></div>
        <button type="submit" class="btn-createspace btn-lg btn-primary w-100"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
    </form>
</div>
@endsection