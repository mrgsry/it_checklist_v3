@extends('layouts.admin')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card-createspace">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="font-headline font-semibold mb-0">Daftar User</h5>
            <a href="{{ route('admin.users.create') }}" class="btn-createspace btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Tambah User
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Assignments</th>
                        <th>Submissions</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'superadmin')
                                <span class="chip chip-status-archived" style="background-color: #1F2937; color: #FFFFFF;">Super Admin</span>
                            @elseif($user->role === 'admin')
                                <span class="chip chip-status-active">Admin</span>
                            @else
                                <span class="chip chip-status-archived">User</span>
                            @endif
                        </td>
                        <td>{{ $user->assignments_count ?? 0 }}</td>
                        <td>{{ $user->submissions_count ?? 0 }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-createspace btn-sm btn-secondary" style="min-width: 32px; padding: 0; background-color: #FACC15; color: #000;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Yakin hapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-createspace btn-sm btn-destructive" style="min-width: 32px; padding: 0;" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

