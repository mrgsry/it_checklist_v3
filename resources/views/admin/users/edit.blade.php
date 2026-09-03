@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User: ' . $user->name)

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Hak Akses Modul</label>
                <div class="row g-2">
                    @foreach($permissions as $permission)
                    <div class="col-md-6 col-lg-4">
                        @php($currentLevel = old("permissions.{$permission['key']}", $user->hasModuleAccess($permission['key'], 'write') ? 'write' : ($user->hasModuleAccess($permission['key']) ? 'read' : 'none')))
                        <label class="form-label small mb-1" for="permission-{{ $permission['key'] }}">{{ $permission['label'] }}</label>
                        <select class="form-select form-select-sm" name="permissions[{{ $permission['key'] }}]" id="permission-{{ $permission['key'] }}">
                            <option value="none" @selected($currentLevel === 'none')>Tidak ada akses</option><option value="read" @selected($currentLevel === 'read')>Read Only</option><option value="write" @selected($currentLevel === 'write')>Read / Write</option>
                        </select>
                    </div>
                    @endforeach
                </div>
                @error('permissions.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password Baru <span class="text-muted fw-normal">(kosongkan jika
                        tidak diubah)</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User / Teknisi
                    </option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Super
                        Admin</option>
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection