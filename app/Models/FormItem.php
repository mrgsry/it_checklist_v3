<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormItem extends Model
{
    protected $fillable = [
        'form_id','section_id','label','field_type',
        'options','is_required','order_index','placeholder','helper_text'
    ];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(ChecklistForm::class, 'form_id');
    }

    public function section()
    {
        return $this->belongsTo(FormSection::class, 'section_id');
    }

    public function answers()
    {
        return $this->hasMany(SubmissionAnswer::class, 'form_item_id');
    }
}