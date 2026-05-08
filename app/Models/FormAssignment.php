<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormAssignment extends Model
{
    protected $fillable = ['form_id', 'user_id', 'assigned_at'];

    public function form()
    {
        return $this->belongsTo(ChecklistForm::class, 'form_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}