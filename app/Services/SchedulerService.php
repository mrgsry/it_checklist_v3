<?php

namespace App\Services;

use App\Models\ChecklistForm;
use App\Models\FormAssignment;
use Carbon\Carbon;

class SchedulerService
{
    /**
     * Get forms that are due today for a specific user
     */
    public function getFormsDueToday($user)
    {
        $today = Carbon::today();
        $dayName = $today->format('D'); // Mon, Tue, Wed, Thu, Fri, Sat, Sun
        
        // Get user's form assignments
        $assignments = FormAssignment::where('user_id', $user->id)
            ->with('form')
            ->get();
        
        $formsDue = [];
        
        foreach ($assignments as $assignment) {
            $form = $assignment->form;
            
            if (!$form || !$form->is_active) {
                continue;
            }
            
            $isDue = $this->isFormDueOnDate($form, $today, $dayName);
            
            if ($isDue) {
                $formsDue[] = $form;
            }
        }
        
        return $formsDue;
    }
    
    /**
     * Check if a form is due on a specific date
     */
    private function isFormDueOnDate($form, Carbon $date, string $dayName): bool
    {
        // Check if within date range
        if ($form->start_date && $date->lt(Carbon::parse($form->start_date))) {
            return false;
        }
        
        if ($form->end_date && $date->gt(Carbon::parse($form->end_date))) {
            return false;
        }
        
        // Check schedule type
        switch ($form->schedule_type) {
            case 'daily':
                return true;
                
            case 'weekly':
                $scheduleDays = is_array($form->schedule_days) 
                    ? $form->schedule_days 
                    : json_decode($form->schedule_days, true);
                return in_array($dayName, $scheduleDays ?? []);
                
            case 'custom':
                // Custom interval logic
                if ($form->start_date) {
                    $startDate = Carbon::parse($form->start_date);
                    $interval = $form->schedule_interval ?? 1;
                    return $date->diffInDays($startDate) % $interval === 0;
                }
                return false;
                
            default:
                return false;
        }
    }
    
    /**
     * Get forms due in a date range
     */
    public function getFormsDueBetween($user, Carbon $startDate, Carbon $endDate)
    {
        $formsDue = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $formsForDay = $this->getFormsDueToday($user);
            
            foreach ($formsForDay as $form) {
                $formsDue[] = [
                    'form' => $form,
                    'date' => $currentDate->copy(),
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $formsDue;
    }
}