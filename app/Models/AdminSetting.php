<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSetting extends Model
{
    protected $fillable = [
        'company_name',
        'address',
        'email',
        'default_dept',
        'logo',
        'provider',
        'base_url',
        'api_key',
        'model',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
        ];
    }
}