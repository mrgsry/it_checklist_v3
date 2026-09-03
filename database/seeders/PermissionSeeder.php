<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionRegistry::names() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $defaultPermissions = Permission::whereIn('name', PermissionRegistry::defaultUserNames())
            ->where('guard_name', 'web')->get();

        User::query()->where('role', 'user')->each(function (User $user) use ($defaultPermissions): void {
            $legacyPermissions = $user->getPermissionNames()->filter(fn (string $permission) => in_array($permission, collect(PermissionRegistry::modules())->map(fn (array $module) => PermissionRegistry::legacy($module['key']))->all(), true));
            if ($user->permissions()->doesntExist()) {
                $user->givePermissionTo($defaultPermissions);
            } elseif ($legacyPermissions->isNotEmpty()) {
                $user->givePermissionTo($legacyPermissions->map(fn (string $permission) => PermissionRegistry::read(str_replace('module.', '', $permission)))->all());
            }
        });

        User::query()->where('role', 'admin')->each(function (User $user): void {
            $user->givePermissionTo(collect(PermissionRegistry::modules())->flatMap(fn (array $module) => [PermissionRegistry::read($module['key']), PermissionRegistry::write($module['key'])])->all());
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
