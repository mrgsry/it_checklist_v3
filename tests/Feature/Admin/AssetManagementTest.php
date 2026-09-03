<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;
use Database\Seeders\AssetCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_optional_asset_identity_fields_may_be_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = AssetCategory::create(['name' => 'Others']);

        $this->actingAs($admin)
            ->post(route('admin.assets.store'), $this->assetData($category, [
                'brand' => '',
                'type' => '',
                'item_code' => '',
                'inventory_number' => '',
                'serial_number' => '',
            ]))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertDatabaseHas('assets', [
            'asset_category_id' => $category->id,
            'brand' => null,
            'type' => null,
            'item_code' => null,
            'inventory_number' => null,
            'serial_number' => null,
        ]);
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

    public function test_admin_can_import_assets_from_csv_and_download_template(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = AssetCategory::create(['name' => 'Switch']);
        $csv = implode(',', ['kategori', 'nama', 'tahun_pembelian', 'merk', 'tipe', 'kode_barang', 'nomor_inventaris', 'serial_number', 'jumlah', 'lokasi', 'keterangan'])."\n";
        $csv .= implode(',', ['Switch', 'UniFi Switch 48', 2026, 'Ubiquiti', 'USW-48', 'IT-SW-048', 'INV-2026-048', 'SN-048', 1, 'Server Room', 'Asset import']);

        $this->actingAs($admin)
            ->get(route('admin.assets.import.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel');

        $this->actingAs($admin)
            ->post(route('admin.assets.import'), ['file' => UploadedFile::fake()->createWithContent('assets.csv', $csv)])
            ->assertRedirect(route('admin.assets.index'))
            ->assertSessionHas('success', '1 asset berhasil diimport.');

        $this->assertDatabaseHas('assets', [
            'asset_category_id' => $category->id,
            'item_code' => 'IT-SW-048',
            'serial_number' => 'SN-048',
        ]);
    }

    public function test_invalid_asset_import_is_rejected_atomically(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AssetCategory::create(['name' => 'Switch']);
        $headers = ['kategori', 'nama', 'tahun_pembelian', 'merk', 'tipe', 'kode_barang', 'nomor_inventaris', 'serial_number', 'jumlah', 'lokasi', 'keterangan'];
        $rows = [
            ['Switch', 'Valid Asset', 2026, 'Ubiquiti', 'USW-24', 'IT-VALID', 'INV-VALID', 'SN-VALID', 1, 'Server Room', ''],
            ['Unknown', 'Invalid Asset', 2026, 'Brand', 'Type', 'IT-INVALID', 'INV-INVALID', 'SN-INVALID', 1, 'Office', ''],
        ];
        $csv = implode(',', $headers).'\n'.implode("\n", array_map(fn (array $row) => implode(',', $row), $rows));

        $this->actingAs($admin)
            ->from(route('admin.assets.import.form'))
            ->post(route('admin.assets.import'), ['file' => UploadedFile::fake()->createWithContent('assets.csv', $csv)])
            ->assertRedirect(route('admin.assets.import.form'))
            ->assertSessionHasErrors('baris_3');

        $this->assertDatabaseMissing('assets', ['item_code' => 'IT-VALID']);
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
