<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DailyActivity extends Model
{
    use HasFactory;

    public const TYPES = ['daily_activity', 'ticketing'];

    public const CATEGORIES = [
        'Account & Identity (IAM)',
        'Email & Collaboration',
        'Hardware & Devices',
        'Infrastructure & Server',
        'IT Service Request (Non-Incident)',
        'Network & Connectivity',
        'Software & Application',
    ];

    public const DEFAULT_CATEGORY = 'IT Service Request (Non-Incident)';

    protected $fillable = [
        'user_id',
        'type',
        'category',
        'assigned_by',
        'assigned_at',
        'activity_date',
        'activity',
        'status',
        'notes',
        'submission_id',
        'user_request',
        'ticket_item',
        'ticket_number',
        'ticket_url',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'assigned_at' => 'datetime',
        ];
    }

    public function getTypeAttribute($value): string
    {
        return str_starts_with((string) ($this->attributes['activity'] ?? ''), 'Selesaikan Ticket #')
            ? 'ticketing'
            : ($value ?: 'daily_activity');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where(function (Builder $query) use ($type): void {
            if ($type === 'ticketing') {
                $query->where('type', 'ticketing')
                    ->orWhere('activity', 'like', 'Selesaikan Ticket #%');
            } else {
                $query->where('type', $type)
                    ->where('activity', 'not like', 'Selesaikan Ticket #%');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ChecklistSubmission::class, 'submission_id');
    }

    public function isAssigned(): bool
    {
        return $this->assigned_by !== null;
    }
}
