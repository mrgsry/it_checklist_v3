<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Submission #{{ $submission->id }}</title>
    <style>
        @page { margin: 18mm 12mm 15mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.45; color: #1f2937; }
        .footer { position: fixed; bottom: -9mm; left: 0; right: 0; color: #6b7280; font-size: 7.5px; border-top: 1px solid #dbe3ed; padding-top: 3mm; }
        .page-number:after { content: counter(page); }
        .header { width: 100%; border-bottom: 3px solid #2563a8; padding-bottom: 9px; margin-bottom: 12px; }
        .header td, .metadata td { border: 0; vertical-align: middle; }
        .logo { width: 138px; height: auto; max-height: 48px; display: block; }
        .title { color: #163b68; font-size: 18px; font-weight: bold; line-height: 1.2; margin: 0 0 3px; }
        .subtitle, .muted { color: #64748b; font-size: 8px; margin: 0; }
        .metadata { width: 100%; margin: 0 0 13px; border-collapse: separate; border-spacing: 0; background: #f6f9fc; border: 1px solid #dbe5f0; }
        .metadata td { padding: 6px 8px; width: 25%; }
        .label { display: block; color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .submission { margin-bottom: 16px; }
        .submission-title { color: #163b68; font-size: 12px; font-weight: bold; border-left: 3px solid #3c8ed1; padding-left: 6px; margin: 0 0 7px; }
        .data { width: 100%; border-collapse: collapse; margin: 0; }
        .data th { background: #1d4f86; color: #fff; font-size: 8px; font-weight: bold; padding: 5px 6px; text-align: left; }
        .data td { border: 1px solid #dbe3ed; padding: 6px; vertical-align: top; }
        .data tr { page-break-inside: avoid; }
        .data tbody tr:nth-child(even) { background: #f7fafe; }
        .flagged { color: #b42318; font-weight: bold; }
        .status { display: inline-block; padding: 2px 5px; font-size: 7px; font-weight: bold; }
        .status-ok { color: #166534; background: #dcfce7; }.status-issue { color: #991b1b; background: #fee2e2; }
        .photo { max-width: 300px; max-height: 260px; display: block; margin-top: 5px; }
        .empty { color: #64748b; text-align: center; padding: 14px; }
    </style>
</head>
<body>
    <div class="footer"><span>PT Tera Data Indonusa Tbk | IT Support | Export Submission</span><span style="float:right">Halaman <span class="page-number"></span></span></div>
    <table class="header"><tr>
        <td style="width:160px">@if($logoDataUri)<img class="logo" src="{{ $logoDataUri }}" alt="Logo PT Tera Data Indonusa">@endif</td>
        <td><div class="title">Export Submission Checklist</div><p class="subtitle">PT Tera Data Indonusa Tbk &middot; IT Support</p></td>
        <td style="width:135px;text-align:right" class="subtitle">Dicetak<br><strong style="color:#334155">{{ now()->format('d/m/Y H:i') }}</strong></td>
    </tr></table>
    <table class="metadata"><tr>
        <td><span class="label">Nomor Submission</span>#{{ $submission->id }}</td>
        <td><span class="label">Form</span>{{ $submission->form?->title ?? '-' }}</td>
        <td><span class="label">Penginput</span>{{ $submission->submitter?->name ?? '-' }}</td>
        <td><span class="label">Tanggal Submit</span>{{ $submission->submission_date?->translatedFormat('d F Y') ?? '-' }}</td>
    </tr></table>

    @php($flaggedCount = $submission->answers->where('is_flagged', true)->count())
    <section class="submission">
        <h2 class="submission-title">{{ $submission->form?->title ?? 'Form' }}</h2>
        <table class="metadata" style="margin-bottom:7px"><tr>
            <td><span class="label">Penginput</span>{{ $submission->submitter?->name ?? '-' }}</td>
            <td><span class="label">Tanggal Submit</span>{{ $submission->submission_date?->translatedFormat('d F Y') ?? '-' }}</td>
            <td><span class="label">Waktu</span>{{ $submission->submitted_at?->format('H:i') ?? '-' }}</td>
            <td><span class="label">Status</span><span class="status {{ $flaggedCount ? 'status-issue' : 'status-ok' }}">{{ $flaggedCount ? $flaggedCount.' masalah' : 'Lengkap' }}</span></td>
        </tr></table>
        @if($submission->notes)<p><strong>Catatan:</strong> {{ $submission->notes }}</p>@endif
        <table class="data"><thead><tr><th style="width:35%">Item</th><th>Jawaban</th></tr></thead><tbody>
            @forelse($submission->answers as $answer)
                <tr><td class="{{ $answer->is_flagged ? 'flagged' : '' }}">{{ $answer->formItem?->label ?? '-' }}@if($answer->is_flagged)<br><span class="muted">Perlu perhatian</span>@endif</td><td class="{{ $answer->is_flagged ? 'flagged' : '' }}">@if($answer->formItem?->field_type === 'photo') @forelse($answer->photoDataUris ?? [] as $photoDataUri)<img class="photo" src="{{ $photoDataUri }}" alt="Foto jawaban">@empty <span class="muted">Foto tidak tersedia.</span> @endforelse @else {{ $answer->answer_value ?: '-' }} @endif</td></tr>
            @empty
                <tr><td colspan="2" class="empty">Tidak ada jawaban.</td></tr>
            @endforelse
        </tbody></table>
    </section>
</body>
</html>