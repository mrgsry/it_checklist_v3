@extends('layouts.admin')

@section('title', 'Detail Form')
@section('page-title', $form->title)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        @if($form->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-secondary">Inactive</span>
                        @endif
                        <span class="badge bg-info ms-1">{{ ucfirst($form->schedule_type) }}</span>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.forms.toggle', $form) }}" class="d-inline">
                            @csrf
                            <button type="submit"
                                class="btn btn-sm btn-outline-{{ $form->is_active ? 'secondary' : 'success' }}">
                                <i class="fas fa-power-off"></i> {{ $form->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.forms.duplicate', $form) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-copy"></i> Duplikat
                            </button>
                        </form>
                    </div>
                </div>

                <h5 class="fw-bold">{{ $form->title }}</h5>
                <p class="text-muted">{{ $form->description ?? 'Tidak ada deskripsi.' }}</p>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="small text-muted">Jadwal</div>
                        <div class="fw-semibold">{{ ucfirst($form->schedule_type) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Tanggal Mulai</div>
                        <div class="fw-semibold">{{ $form->start_date?->isoFormat('D MMM Y') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Tanggal Berakhir</div>
                        <div class="fw-semibold">{{ $form->end_date?->isoFormat('D MMM Y') ?? '-' }}</div>
                    </div>
                </div>

                @if($form->schedule_type == 'weekly' && is_array($form->schedule_days))
                <div class="mb-3">
                    <div class="small text-muted mb-1">Hari</div>
                    <div class="d-flex flex-wrap gap-1">
                        @php
                        $dayLabels =
                        ['Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu','Sun'=>'Minggu'];
                        @endphp
                        @foreach($form->schedule_days as $day)
                        <span class="badge bg-light text-dark border">{{ $dayLabels[$day] ?? $day }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Item Checklist ({{ $form->items->count() }})</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Label</th>
                                <th>Tipe</th>
                                <th>Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($form->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->label }}</td>
                                <td><span class="badge bg-light text-dark">{{ $item->field_type }}</span></td>
                                <td>
                                    @if($item->is_required)
                                    <i class="fas fa-check-circle text-success"></i>
                                    @else
                                    <i class="fas fa-minus-circle text-muted"></i>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">User Assignments</h6>
                @if($form->assignedUsers->count())
                <ul class="list-group list-group-flush">
                    @foreach($form->assignedUsers as $u)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        {{ $u->name }}
                        <span class="badge bg-secondary rounded-pill">{{ $u->email }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted small mb-0">Belum ada user yang di-assign</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Submission Terbaru</h6>
                @if($form->submissions->count())
                <ul class="list-group list-group-flush">
                    @foreach($form->submissions->take(5) as $sub)
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <span>{{ $sub->submitter->name ?? '-' }}</span>
                            <span class="small text-muted">{{ $sub->submission_date?->isoFormat('D MMM') }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted small mb-0">Belum ada submission</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection