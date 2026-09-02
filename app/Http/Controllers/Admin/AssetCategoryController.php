<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(): View
    {
        $categories = AssetCategory::query()->withCount('assets')->orderBy('name')->get();

        return view('admin.asset-categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        AssetCategory::create($this->validatedData($request));

        return back()->with('success', 'Kategori asset berhasil ditambahkan.');
    }

    public function update(Request $request, AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->update($this->validatedData($request, $assetCategory));

        return back()->with('success', 'Kategori asset berhasil diperbarui.');
    }

    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        if ($assetCategory->assets()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh asset.');
        }

        $assetCategory->delete();

        return back()->with('success', 'Kategori asset berhasil dihapus.');
    }

    private function validatedData(Request $request, ?AssetCategory $assetCategory = null): array
    {
        $name = trim((string) $request->input('name'));
        $request->merge(['name' => $name]);

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_categories', 'name')->ignore($assetCategory?->id),
            ],
        ]);
    }
}
