@extends('layouts.admin')

@section('title', 'Form Checklist')
@section('page-title', 'Form Checklist')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Daftar Form</h5>
            <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Buat Form Baru
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Form</th>
                        <th>Jadwal</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $form->title }}</div>
                            <div class="small text-muted">{{ Str::limit($form->description, 40) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ ucfirst($form->schedule_type) }}</span>
                        </td>
                        <td>{{ $form->items_count ?? $form->items->count() }}</td>
                        <td>
                            @if($form->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.forms.show', $form) }}" class="btn btn-sm btn-info"
                                    title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-sm btn-warning"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.forms.toggle', $form) }}" class="d-inline"
                                    title="Toggle Status">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-{{ $form->is_active ? 'secondary' : 'success' }}">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.forms.duplicate', $form) }}"
                                    class="d-inline" title="Duplikat">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" class="d-inline"
                                    onsubmit="return confirm('Yakin hapus form ini?')" title="Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada form checklist</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $forms->links() }}
    </div>
</div>
@endsection