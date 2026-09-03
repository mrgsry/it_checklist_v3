<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'asset_category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_year' => ['nullable', 'integer', 'between:1900,'.now()->year],
        ]);

        $assets = $this->filteredAssets($filters)
            ->paginate(20)
            ->withQueryString();

        $categories = AssetCategory::query()->orderBy('name')->get();

        return view('user.assets.index', compact('assets', 'categories'));
    }

    private function filteredAssets(array $filters): Builder
    {
        return Asset::query()
            ->with('category')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    foreach (['name', 'brand', 'type', 'item_code', 'inventory_number', 'serial_number', 'location', 'description'] as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->when($filters['asset_category_id'] ?? null, fn (Builder $query, int $categoryId) => $query->where('asset_category_id', $categoryId))
            ->when($filters['location'] ?? null, fn (Builder $query, string $location) => $query->where('location', 'like', "%{$location}%"))
            ->when($filters['purchase_year'] ?? null, fn (Builder $query, int $year) => $query->where('purchase_year', $year))
            ->latest('id');
    }

    public function create(): View
    {
        return view('user.assets.create', [
            'categories' => AssetCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Asset::create($this->validatedData($request));

        return redirect()->route('user.assets.index')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function edit(Asset $asset): View
    {
        return view('user.assets.edit', [
            'asset' => $asset,
            'categories' => AssetCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $asset->update($this->validatedData($request, $asset));

        return redirect()->route('user.assets.index')->with('success', 'Asset berhasil diperbarui.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()->route('user.assets.index')->with('success', 'Asset berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Asset $asset = null): array
    {
        $data = $request->validate([
            'asset_category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'purchase_year' => ['required', 'integer', 'between:1900,'.now()->year],
            'brand' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'item_code' => ['nullable', 'string', 'max:100', Rule::unique('assets', 'item_code')->ignore($asset?->id)],
            'inventory_number' => ['nullable', 'string', 'max:100', Rule::unique('assets', 'inventory_number')->ignore($asset?->id)],
            'serial_number' => ['nullable', 'string', 'max:150', Rule::unique('assets', 'serial_number')->ignore($asset?->id)],
            'quantity' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        foreach (['brand', 'type', 'item_code', 'inventory_number', 'serial_number'] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
