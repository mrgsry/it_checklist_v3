<?php

namespace App\Services;

use App\Models\ChecklistSubmission;
use App\Models\SubmissionAnswer;

class AnomalyDetectionService
{
    /**
     * Anomaly keywords that trigger automatic flagging
     */
    protected array $anomalyKeywords = [
        'offline', 'no signal', 'poor', 'error', 'broken', 'rusak', 'mati'
    ];

    /**
     * Detect and flag anomalies for a submission
     */
    public function detectForSubmission(ChecklistSubmission $submission): array
    {
        $anomalies = [];
        
        // Flag answers containing anomaly keywords
        $anomalies = array_merge($anomalies, $this->flagKeywordAnomalies($submission));
        
        // Check for time-based anomalies
        $anomalies = array_merge($anomalies, $this->detectTimeAnomalies($submission));
        
        return $anomalies;
    }

    /**
     * Flag answers that contain anomaly keywords
     */
    private function flagKeywordAnomalies(ChecklistSubmission $submission): array
    {
        $anomalies = [];
        
        foreach ($submission->answers as $answer) {
            if ($answer->formItem?->field_type === 'checkbox' && $answer->hasAbnormalCheckboxStatus()) {
                $answer->update(['is_flagged' => true]);
                $anomalies[] = [
                    'type' => 'checkbox_status',
                    'severity' => 'high',
                    'message' => "Item '{$answer->formItem->label}' memiliki detail check Tidak Normal.",
                ];

                continue;
            }

            $value = strtolower($answer->answer_value ?? '');
            
            foreach ($this->anomalyKeywords as $keyword) {
                if (str_contains($value, $keyword)) {
                    $answer->update(['is_flagged' => true]);
                    $anomalies[] = [
                        'type' => 'keyword',
                        'severity' => 'high',
                        'message' => "Item '{$answer->formItem->label}' mengandung nilai kritis: {$answer->answer_value}",
                    ];
                    break; // Only flag once per answer
                }
            }
        }
        
        return $anomalies;
    }

    /**
     * Detect time-based anomalies (e.g., submitting too fast)
     */
    private function detectTimeAnomalies(ChecklistSubmission $submission): array
    {
        $anomalies = [];
        
        $createdAt = $submission->created_at;
        $submittedAt = $submission->submitted_at;
        
        if ($createdAt && $submittedAt) {
            $timeDiff = $submittedAt->diffInSeconds($createdAt);
            
            if ($timeDiff < 10) {
                $anomalies[] = [
                    'type' => 'time',
                    'severity' => 'low',
                    'message' => 'Submission terlalu cepat (' . $timeDiff . ' detik)',
                ];
            }
        }
        
        return $anomalies;
    }

    /**
     * Get statistics for anomaly detection
     */
    public function getAnomalyStats(?int $userId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ChecklistSubmission::query();
        
        if ($userId) {
            $query->where('submitted_by', $userId);
        }
        
        if ($startDate) {
            $query->whereDate('submission_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('submission_date', '<=', $endDate);
        }
        
        $total = $query->clone()->where('status', 'submitted')->count();
        $flagged = $query->clone()
            ->where('status', 'submitted')
            ->whereHas('answers', fn($q) => $q->where('is_flagged', true))
            ->count();
        
        return [
            'total_submissions' => $total,
            'flagged_count' => $flagged,
            'clean_count' => $total - $flagged,
            'flagged_rate' => $total > 0 ? round(($flagged / $total) * 100, 1) : 0,
        ];
    }
}