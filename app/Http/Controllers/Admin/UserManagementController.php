<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UserManagementController extends Controller
{
    public function index()
    {
        // Hanya superadmin
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat mengakses halaman ini.');
        }

        $users = User::withCount(['submissions', 'assignments'])->orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('admin.users.create', ['permissions' => PermissionRegistry::modules()]);
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:superadmin,admin,user',
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'in:none,read,write'],
        ], [
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $access = $request->input('permissions', []);
        if ($user->role === 'user' && $access === []) {
            $access = collect(PermissionRegistry::modules())->filter(fn (array $module) => $module['default_user'])
                ->mapWithKeys(fn (array $module) => [$module['key'] => 'read'])->all();
        }
        $this->ensureRegisteredPermissionsExist();
        $user->syncPermissions($this->permissionsFromAccess($access, $user->role));

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$request->name}\" berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('admin.users.edit', [
            'user' => $user,
            'permissions' => PermissionRegistry::modules(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:superadmin,admin,user',
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'in:none,read,write'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $this->ensureRegisteredPermissionsExist();
        $user->syncPermissions($this->permissionsFromAccess($request->input('permissions', []), $request->input('role')));

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" berhasil diupdate.");
    }

    public function destroy(User $user)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$name}\" berhasil dihapus.");
    }

    private function permissionsFromAccess(array $access, string $role): array
    {
        return collect($access)->flatMap(function (?string $level, string $module) use ($role): array {
            if ($level === 'none' || ! in_array($module, collect(PermissionRegistry::modules())->pluck('key')->all(), true)) {
                return [];
            }
            if ($module === 'user-management' && $role !== 'superadmin') {
                return [];
            }

            return [PermissionRegistry::read($module), ...($level === 'write' ? [PermissionRegistry::write($module)] : [])];
        })->values()->all();
    }

    private function ensureRegisteredPermissionsExist(): void
    {
        foreach (PermissionRegistry::names() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
