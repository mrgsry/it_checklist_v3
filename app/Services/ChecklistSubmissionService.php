<?php

namespace App\Services;

use App\Models\ChecklistSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChecklistSubmissionService
{
    public function delete(ChecklistSubmission $submission): void
    {
        $paths = $submission->answers()
            ->with('formItem')
            ->get()
            ->filter(fn ($answer) => $answer->formItem?->field_type === 'photo')
            ->flatMap(fn ($answer) => $answer->photoPaths())
            ->unique()
            ->values()
            ->all();

        DB::transaction(fn () => $submission->delete());

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}