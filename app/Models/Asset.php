<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_category_id',
        'name',
        'purchase_year',
        'brand',
        'type',
        'item_code',
        'inventory_number',
        'serial_number',
        'quantity',
        'location',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'purchase_year' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}
