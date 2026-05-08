<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionAnswer extends Model
{
    protected $fillable = ['submission_id', 'form_item_id', 'answer_value', 'is_flagged'];

    protected $casts = [
        'is_flagged' => 'boolean',
    ];

    public function submission()
    {
        return $this->belongsTo(ChecklistSubmission::class, 'submission_id');
    }

    public function formItem()
    {
        return $this->belongsTo(FormItem::class, 'form_item_id');
    }
}