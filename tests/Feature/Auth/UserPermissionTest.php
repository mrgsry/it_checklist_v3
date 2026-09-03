<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Support\PermissionRegistry;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_is_idempotent_and_assigns_defaults(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->seed(PermissionSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->assertDatabaseCount('permissions', count(PermissionRegistry::names()));
        $this->assertSame(
            collect(PermissionRegistry::defaultUserNames())->sort()->values()->all(),
            $user->fresh()->getPermissionNames()->sort()->values()->all(),
        );
    }

    public function test_default_user_can_only_open_default_modules(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->seed(PermissionSeeder::class);

        $this->actingAs($user)->get(route('user.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('user.checklist.index'))->assertOk();
        $this->actingAs($user)->get(route('user.daily-activities.index'))->assertOk();
        $this->actingAs($user)->get(route('user.history'))->assertOk();
        $this->actingAs($user)->get(route('admin.assets.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.submissions.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($user)->get(route('user.assets.index'))->assertForbidden();
    }

    public function test_user_with_asset_permission_can_see_and_open_asset_module(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo(PermissionRegistry::read('asset'));

        $category = AssetCategory::create(['name' => 'Perangkat jaringan']);
        $asset = Asset::create([
            'asset_category_id' => $category->id,
            'name' => 'Switch lantai 1',
            'purchase_year' => now()->year,
            'brand' => 'Ubiquiti',
            'type' => 'USW-24',
            'item_code' => 'IT-SW-USER-001',
            'inventory_number' => 'INV-USER-001',
            'serial_number' => 'SN-USER-001',
            'quantity' => 1,
            'location' => 'Lantai 1',
            'description' => 'Asset untuk pengujian akses user.',
        ]);

        $response = $this->actingAs($user)->get(route('user.assets.index'));

        $response->assertOk()
            ->assertSee('Asset')
            ->assertSee($asset->name)
            ->assertSee('user/assets');
    }

    public function test_superadmin_bypass_allows_every_permission(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->seed(PermissionSeeder::class);

        foreach (PermissionRegistry::names() as $permission) {
            $this->assertTrue($superadmin->can($permission));
        }
    }

    public function test_invalid_permission_is_rejected_when_updating_user(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'user']);
        $this->seed(PermissionSeeder::class);

        $this->actingAs($superadmin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'user',
                'permissions' => ['invalid' => 'admin'],
            ])
            ->assertSessionHasErrors('permissions.invalid');
    }

    public function test_read_only_user_cannot_use_a_write_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->seed(PermissionSeeder::class);
        $user->syncPermissions([PermissionRegistry::read('asset')]);

        $this->actingAs($user)->post(route('admin.assets.store'), [])->assertForbidden();
        $this->actingAs($user)->get(route('user.assets.create'))->assertForbidden();
    }

    public function test_user_with_asset_write_permission_can_manage_assets(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->seed(PermissionSeeder::class);
        $user->syncPermissions([PermissionRegistry::read('asset'), PermissionRegistry::write('asset')]);
        $category = AssetCategory::create(['name' => 'Router']);
        $data = [
            'asset_category_id' => $category->id,
            'name' => 'Router lantai 2',
            'purchase_year' => now()->year,
            'brand' => 'Ubiquiti',
            'type' => 'UDR',
            'item_code' => 'IT-USER-001',
            'inventory_number' => 'INV-USER-001',
            'serial_number' => 'SN-USER-001',
            'quantity' => 1,
            'location' => 'Lantai 2',
            'description' => 'Asset user write.',
        ];

        $this->actingAs($user)->get(route('user.assets.index'))->assertOk()->assertSee('Tambah Asset');
        $this->actingAs($user)->post(route('user.assets.store'), $data)->assertRedirect(route('user.assets.index'));
        $asset = Asset::where('item_code', 'IT-USER-001')->firstOrFail();

        $this->actingAs($user)->get(route('user.assets.edit', $asset))->assertOk()->assertSee('Edit Asset');
        $this->actingAs($user)->put(route('user.assets.update', $asset), array_merge($data, ['name' => 'Router lantai 2 updated']))->assertRedirect(route('user.assets.index'));
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Router lantai 2 updated']);

        $this->actingAs($user)->delete(route('user.assets.destroy', $asset))->assertRedirect(route('user.assets.index'));
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }
}
