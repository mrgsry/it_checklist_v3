<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use Barryvdh\DomPDF\Facade\Pdf;
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

        return view('admin.assets.index', compact('assets', 'categories'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $assets = $this->filteredAssets($filters)->get();

        return Pdf::loadView('admin.assets.pdf', compact('assets', 'filters'))
            ->setPaper('a4', 'landscape')
            ->download('asset-inventory-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $assets = $this->filteredAssets($filters)->get();

        return response()->view('admin.assets.excel', compact('assets', 'filters'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="asset-inventory-'.now()->format('Ymd-His').'.xls"');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'asset_category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_year' => ['nullable', 'integer', 'between:1900,'.now()->year],
        ]);
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
        return view('admin.assets.create', [
            'categories' => AssetCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Asset::create($this->validatedData($request));

        return redirect()->route('admin.assets.index')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function show(Asset $asset): View
    {
        $asset->load('category');

        return view('admin.assets.show', compact('asset'));
    }

    public function edit(Asset $asset): View
    {
        return view('admin.assets.edit', [
            'asset' => $asset,
            'categories' => AssetCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $asset->update($this->validatedData($request, $asset));

        return redirect()->route('admin.assets.index')->with('success', 'Asset berhasil diperbarui.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')->with('success', 'Asset berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'asset_category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'purchase_year' => ['required', 'integer', 'between:1900,'.now()->year],
            'brand' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:100'],
            'item_code' => ['required', 'string', 'max:100', Rule::unique('assets', 'item_code')->ignore($asset?->id)],
            'inventory_number' => ['required', 'string', 'max:100', Rule::unique('assets', 'inventory_number')->ignore($asset?->id)],
            'serial_number' => ['required', 'string', 'max:150', Rule::unique('assets', 'serial_number')->ignore($asset?->id)],
            'quantity' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
