<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRequest extends Model
{
    protected $fillable = [
        'submission_id', 'ticket_item', 'requester', 'departement',
        'contact', 'email', 'detail', 'location',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ChecklistSubmission::class);
    }
}