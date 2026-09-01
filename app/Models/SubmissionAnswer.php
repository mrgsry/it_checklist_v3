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

    public function selectedCheckboxOptions(): array
    {
        if (blank($this->answer_value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            explode(',', $this->answer_value)
        ), fn (string $option) => $option !== ''));
    }

    public function checkboxStatuses(): array
    {
        $statuses = json_decode($this->answer_value ?? '', true);

        if (! is_array($statuses)) {
            return array_fill_keys($this->selectedCheckboxOptions(), 'normal');
        }

        return collect($statuses)
            ->filter(fn ($status, $option) => is_string($option) && in_array($status, ['normal', 'tidak_normal'], true))
            ->all();
    }

    public function hasAbnormalCheckboxStatus(): bool
    {
        return in_array('tidak_normal', $this->checkboxStatuses(), true);
    }
}