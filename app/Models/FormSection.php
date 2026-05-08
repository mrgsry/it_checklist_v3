<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    protected $fillable = ['form_id', 'title', 'order_index'];

    public function form()
    {
        return $this->belongsTo(ChecklistForm::class, 'form_id');
    }

    public function items()
    {
        return $this->hasMany(FormItem::class, 'section_id')->orderBy('order_index');
    }
}