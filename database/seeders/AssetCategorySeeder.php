<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tools', 'Switch', 'Access Point', 'Router', 'Printer', 'Others'] as $name) {
            AssetCategory::firstOrCreate(['name' => $name]);
        }
    }
}
