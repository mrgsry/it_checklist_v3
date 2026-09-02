<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;
use Database\Seeders\AssetCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    private function assetData(AssetCategory $category, array $overrides = []): array
    {
        return array_merge([
            'asset_category_id' => $category->id,
            'name' => 'UniFi Switch 24',
            'purchase_year' => 2025,
            'brand' => 'Ubiquiti',
            'type' => 'USW-24',
            'item_code' => 'IT-SW-001',
            'inventory_number' => 'INV-2025-001',
            'serial_number' => 'SN-UNIFI-001',
            'quantity' => 1,
            'location' => 'Server Room',
            'description' => 'Asset aktif untuk kebutuhan operasional.',
        ], $overrides);
    }

    public function test_admin_can_create_and_view_asset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = AssetCategory::create(['name' => 'Switch']);

        $this->actingAs($admin)
            ->post(route('admin.assets.store'), $this->assetData($category))
            ->assertRedirect(route('admin.assets.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'asset_category_id' => $category->id,
            'item_code' => 'IT-SW-001',
            'serial_number' => 'SN-UNIFI-001',
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.assets.index'))
            ->assertOk()
            ->assertSee('UniFi Switch 24')
            ->assertSee('Asset aktif untuk kebutuhan operasional.')
            ->assertSee('Switch');
    }

    public function test_asset_validation_rejects_invalid_data_and_duplicates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = AssetCategory::create(['name' => 'Tools']);
        Asset::create($this->assetData($category));

        $this->actingAs($admin)
            ->post(route('admin.assets.store'), $this->assetData($category, [
                'name' => '',
                'purchase_year' => now()->year + 1,
                'item_code' => 'IT-SW-001',
                'inventory_number' => 'INV-2025-001',
                'serial_number' => 'SN-UNIFI-001',
                'quantity' => 0,
            ]))
            ->assertSessionHasErrors(['name', 'purchase_year', 'item_code', 'inventory_number', 'serial_number', 'quantity']);
    }

    public function test_admin_can_update_and_delete_asset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = AssetCategory::create(['name' => 'Printer']);
        $asset = Asset::create($this->assetData($category));

        $this->actingAs($admin)
            ->put(route('admin.assets.update', $asset), $this->assetData($category, [
                'name' => 'Printer Updated',
                'location' => 'Front Office',
            ]))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Printer Updated', 'location' => 'Front Office']);

        $this->actingAs($admin)
            ->delete(route('admin.assets.destroy', $asset))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_admin_can_search_and_filter_assets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $switch = AssetCategory::create(['name' => 'Switch']);
        $printer = AssetCategory::create(['name' => 'Printer']);
        Asset::create($this->assetData($switch));
        Asset::create($this->assetData($printer, [
            'name' => 'Office Printer',
            'purchase_year' => 2024,
            'item_code' => 'IT-PR-001',
            'inventory_number' => 'INV-2024-001',
            'serial_number' => 'SN-PRINT-001',
            'location' => 'Front Office',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.assets.index', [
                'search' => 'Office',
                'asset_category_id' => $printer->id,
                'location' => 'Front',
                'purchase_year' => 2024,
            ]))
            ->assertOk()
            ->assertSee('Office Printer')
            ->assertDontSee('UniFi Switch 24');
    }

    public function test_admin_can_export_filtered_assets_to_pdf_and_excel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = AssetCategory::create(['name' => 'Switch']);
        Asset::create($this->assetData($category));

        $query = ['search' => 'UniFi', 'asset_category_id' => $category->id];

        $this->actingAs($admin)
            ->get(route('admin.assets.export-excel', $query))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertSee('UniFi Switch 24');

        $this->actingAs($admin)
            ->get(route('admin.assets.export-pdf', $query))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_cannot_access_asset_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = AssetCategory::create(['name' => 'Router']);

        $this->actingAs($user)->get(route('admin.assets.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.assets.store'), $this->assetData($category))->assertForbidden();
        $this->actingAs($user)->get(route('admin.assets.export-excel'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.assets.export-pdf'))->assertForbidden();
    }

    public function test_only_superadmin_can_manage_categories_and_used_category_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $category = AssetCategory::create(['name' => 'Router']);

        $this->actingAs($admin)->get(route('admin.asset-categories.index'))->assertForbidden();
        $this->actingAs($superadmin)
            ->post(route('admin.asset-categories.store'), ['name' => 'Others'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Asset::create($this->assetData($category));

        $this->actingAs($superadmin)
            ->delete(route('admin.asset-categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('asset_categories', ['id' => $category->id]);
    }

    public function test_default_category_seeder_is_idempotent(): void
    {
        $this->seed(AssetCategorySeeder::class);
        $this->seed(AssetCategorySeeder::class);

        $this->assertDatabaseCount('asset_categories', 6);
        $this->assertDatabaseHas('asset_categories', ['name' => 'Access Point']);
    }
}
