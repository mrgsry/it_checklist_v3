<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstruksiKerjaDocument extends Model
{
    protected $fillable = [
        'document_id',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}