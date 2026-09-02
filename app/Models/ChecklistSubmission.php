<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistSubmission extends Model
{
    protected $fillable = [
        'form_id','submitted_by','submission_date',
        'submitted_at','notes','status','ticketing_data'
    ];

    protected $casts = [
        'submission_date' => 'date',
        'submitted_at'    => 'datetime',
        'ticketing_data'  => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(ChecklistForm::class, 'form_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function answers()
    {
        return $this->hasMany(SubmissionAnswer::class, 'submission_id');
    }

    public function userRequests()
    {
        return $this->hasMany(UserRequest::class, 'submission_id');
    }

    public function flaggedAnswers()
    {
        return $this->hasMany(SubmissionAnswer::class, 'submission_id')->where('is_flagged', true);
    }
}