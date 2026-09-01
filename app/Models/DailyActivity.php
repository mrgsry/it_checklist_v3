<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_by',
        'assigned_at',
        'activity_date',
        'activity',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'assigned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isAssigned(): bool
    {
        return $this->assigned_by !== null;
    }
}
