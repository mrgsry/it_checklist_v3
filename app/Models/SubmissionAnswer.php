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

    public function photoPaths(): array
    {
        if (blank($this->answer_value)) {
            return [];
        }

        $paths = json_decode($this->answer_value, true);

        if (! is_array($paths)) {
            return [$this->answer_value];
        }

        return array_values(array_filter($paths, 'is_string'));
    }
}