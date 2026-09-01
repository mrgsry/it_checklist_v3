<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Aktivitas - {{ $reportPeriod }}</title>
    <style>
        @page { margin: 18mm 12mm 15mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; line-height: 1.45; color: #1f2937; }
        .footer { position: fixed; bottom: -9mm; left: 0; right: 0; color: #6b7280; font-size: 7.5px; border-top: 1px solid #dbe3ed; padding-top: 3mm; }
        .page-number:after { content: counter(page); }
        .header { width: 100%; border-bottom: 3px solid #2563a8; padding-bottom: 9px; margin-bottom: 12px; }
        .header td { border: 0; padding: 0; vertical-align: middle; }
        .logo { width: 138px; height: auto; max-height: 48px; display: block; }
        .title { color: #163b68; font-size: 18px; font-weight: bold; line-height: 1.2; margin: 0 0 3px; }
        .subtitle { color: #64748b; font-size: 9px; margin: 0; }
        .metadata { width: 100%; margin: 0 0 13px; border-collapse: separate; border-spacing: 0; background: #f6f9fc; border: 1px solid #dbe5f0; }
        .metadata td { border: 0; padding: 6px 8px; vertical-align: top; width: 25%; }
        .metadata .label, .kpi .label { display: block; color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .section { margin: 0 0 14px; page-break-inside: avoid; }
        .section-title { color: #163b68; font-size: 11px; font-weight: bold; border-left: 3px solid #3c8ed1; padding-left: 6px; margin: 0 0 7px; }
        .section-note { color: #64748b; font-size: 7.5px; margin: -4px 0 7px 9px; }
        .kpi { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin: 0 -5px; }
        .kpi td { width: 16.66%; border: 1px solid #dbe5f0; background: #fff; padding: 7px 8px; text-align: center; }
        .kpi strong { display: block; color: #163b68; font-size: 16px; line-height: 1.1; margin-top: 3px; }
        .success { color: #15803d !important; }.warning { color: #a16207 !important; }.danger { color: #b42318 !important; }.accent { color: #087990 !important; }
        table.data { width: 100%; border-collapse: collapse; margin: 0; }
        .data th { background: #1d4f86; color: #fff; font-size: 7.5px; font-weight: bold; padding: 5px 6px; text-align: left; vertical-align: middle; }
        .data td { border: 1px solid #dbe3ed; padding: 5px 6px; vertical-align: top; }
        .data tbody tr:nth-child(even) { background: #f7fafe; }.data tr { page-break-inside: avoid; }
        .number { text-align: center; white-space: nowrap; }.nowrap { white-space: nowrap; }.muted { color: #64748b; }
        .status { display: inline-block; padding: 2px 5px; font-size: 7px; font-weight: bold; white-space: nowrap; }
        .status-completed, .status-ok { color: #166534; background: #dcfce7; }.status-progress { color: #92400e; background: #fef3c7; }.status-blocked, .status-issue { color: #991b1b; background: #fee2e2; }
        .danger-text { color: #b42318; font-weight: bold; }.answer { margin-bottom: 3px; padding-bottom: 3px; border-bottom: 1px solid #e7edf4; }.answer:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: 0; }
        .empty { color: #64748b; text-align: center; padding: 14px !important; }.detail-section { page-break-before: always; }
    </style>
</head>
<body>
    <div class="footer"><span>PT Tera Data Indonusa Tbk | IT Support | Laporan Aktivitas</span><span style="float:right">Halaman <span class="page-number"></span></span></div>
    <table class="header"><tr>
        <td style="width:160px">@if($logoDataUri)<img class="logo" src="{{ $logoDataUri }}" alt="Logo PT Tera Data Indonusa">@endif</td>
        <td><div class="title">Laporan Daily &amp; Weekly Activity</div><p class="subtitle">PT Tera Data Indonusa Tbk &middot; IT Support &middot; Periode {{ $reportPeriod }}</p></td>
        <td style="width:135px;text-align:right" class="subtitle">Dicetak<br><strong style="color:#334155">{{ now()->format('d/m/Y H:i') }}</strong></td>
    </tr></table>
    <table class="metadata"><tr>
        <td><span class="label">Periode Data</span>{{ request('date_from') ?: 'Awal data' }} s/d {{ request('date_to') ?: 'Hari ini' }}</td>
        <td><span class="label">User</span>{{ $selectedUser?->name ?? 'Semua User' }}</td>
        <td><span class="label">Form Checklist</span>{{ $selectedForm?->title ?? 'Semua Form' }}</td>
        <td><span class="label">Departemen</span>IT Support</td>
    </tr></table>
    <section class="section"><h2 class="section-title">Ringkasan Checklist</h2><table class="kpi"><tr>
        <td><span class="label">Total Submission</span><strong>{{ $summaryStats['total'] }}</strong></td><td><span class="label">Normal</span><strong class="success">{{ $summaryStats['clean'] }}</strong></td><td><span class="label">Perlu Perhatian</span><strong class="danger">{{ $summaryStats['flagged'] }}</strong></td><td><span class="label">Item Ditandai</span><strong class="warning">{{ $summaryStats['flagged_answers'] }}</strong></td><td><span class="label">Jawaban Terisi</span><strong>{{ $summaryStats['answers'] }}/{{ $summaryStats['expected_answers'] }}</strong></td><td><span class="label">Kelengkapan</span><strong class="accent">{{ $summaryStats['completion_rate'] }}%</strong></td>
    </tr></table></section>
    <section class="section"><h2 class="section-title">Rekap Per Form</h2><table class="data"><thead><tr><th>Form</th><th class="number">Submission</th><th class="number">Masalah</th><th class="number">Jawaban</th><th class="number">Kelengkapan</th></tr></thead><tbody>
        @forelse($formSummary as $formId => $stats)<tr><td>{{ $forms->firstWhere('id', $formId)?->title ?? '-' }}</td><td class="number">{{ $stats['total'] }}</td><td class="number">{{ $stats['flagged'] }}</td><td class="number">{{ $stats['answers'] }}</td><td class="number">{{ $stats['completion_rate'] }}%</td></tr>@empty<tr><td class="empty" colspan="5">Belum ada data submission pada filter yang dipilih.</td></tr>@endforelse
    </tbody></table></section>
    <section class="section"><h2 class="section-title">Ringkasan Daily Task</h2><table class="kpi"><tr>
        <td><span class="label">Total Aktivitas</span><strong>{{ $summaryStats['daily_total'] }}</strong></td><td><span class="label">Selesai</span><strong class="success">{{ $summaryStats['daily_completed'] }}</strong></td><td><span class="label">Dalam Proses</span><strong class="warning">{{ $summaryStats['daily_in_progress'] }}</strong></td><td><span class="label">Terhambat</span><strong class="danger">{{ $summaryStats['daily_blocked'] }}</strong></td><td colspan="2"><span class="label">Tingkat Penyelesaian</span><strong class="accent">{{ $summaryStats['daily_completion_rate'] }}%</strong></td>
    </tr></table></section>
    <section class="section"><h2 class="section-title">Detail Daily Activity</h2><table class="data"><thead><tr><th style="width:9%">Tanggal</th><th style="width:13%">Staff</th><th style="width:27%">Aktivitas</th><th style="width:11%">Status</th><th style="width:20%">Catatan</th><th style="width:11%">Penugasan</th><th style="width:9%">Diperbarui</th></tr></thead><tbody>
        @forelse($dailyActivities as $activity) @php($status = ['completed' => ['Selesai', 'status-completed'], 'in_progress' => ['Dalam Proses', 'status-progress'], 'blocked' => ['Terhambat', 'status-blocked']][$activity->status] ?? [$activity->status, ''])<tr><td class="nowrap">{{ $activity->activity_date?->format('d/m/Y') }}</td><td>{{ $activity->user?->name ?? '-' }}</td><td>{{ $activity->activity }}</td><td><span class="status {{ $status[1] }}">{{ $status[0] }}</span></td><td>{{ $activity->notes ?: '-' }}</td><td>{{ $activity->assigner?->name ?? 'Mandiri' }}</td><td class="nowrap">{{ $activity->updated_at?->format('d/m/Y') }}<br><span class="muted">{{ $activity->updated_at?->format('H:i') }}</span></td></tr>@empty<tr><td class="empty" colspan="7">Belum ada daily activity pada filter yang dipilih.</td></tr>@endforelse
    </tbody></table></section>
    <section class="section detail-section"><h2 class="section-title">Detail Submission Checklist</h2><p class="section-note">Jawaban yang memerlukan perhatian ditampilkan dalam warna merah.</p><table class="data"><thead><tr><th style="width:17%">Form</th><th style="width:12%">User</th><th style="width:9%">Tanggal</th><th style="width:7%">Waktu</th><th style="width:10%">Status</th><th>Jawaban Checklist</th></tr></thead><tbody>
        @forelse($submissions as $submission) @php($flagged = $submission->answers->where('is_flagged', true)->count())<tr><td>{{ $submission->form?->title ?? '-' }}</td><td>{{ $submission->submitter?->name ?? '-' }}</td><td class="nowrap">{{ $submission->submission_date?->format('d/m/Y') }}</td><td class="nowrap">{{ $submission->submitted_at?->format('H:i') ?? '-' }}</td><td><span class="status {{ $flagged ? 'status-issue' : 'status-ok' }}">{{ $flagged ? $flagged . ' masalah' : 'OK' }}</span></td><td>@forelse($submission->answers as $answer)<div class="answer {{ $answer->is_flagged ? 'danger-text' : '' }}"><strong>{{ $answer->formItem?->label ?? 'Item' }}</strong>: {{ $answer->formItem?->field_type === 'photo' ? count($answer->photoPaths()).' foto' : ($answer->answer_value ?: '-') }}</div>@empty<span class="muted">Tidak ada jawaban.</span>@endforelse</td></tr>@empty<tr><td class="empty" colspan="6">Belum ada submission pada filter yang dipilih.</td></tr>@endforelse
    </tbody></table></section>
</body>
</html>