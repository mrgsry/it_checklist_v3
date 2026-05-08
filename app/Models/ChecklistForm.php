<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','description','schedule_type','schedule_days',
        'schedule_interval','start_date','end_date','is_active','created_by'
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'is_active'     => 'boolean',
        'start_date'    => 'date',
        'end_date'      => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections()
    {
        return $this->hasMany(FormSection::class, 'form_id')->orderBy('order_index');
    }

    public function items()
    {
        return $this->hasMany(FormItem::class, 'form_id')->orderBy('order_index');
    }

    public function assignments()
    {
        return $this->hasMany(FormAssignment::class, 'form_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'form_assignments', 'form_id', 'user_id');
    }

    public function submissions()
    {
        return $this->hasMany(ChecklistSubmission::class, 'form_id');
    }
}