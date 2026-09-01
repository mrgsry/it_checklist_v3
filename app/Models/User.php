<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi
    public function assignments()
    {
        return $this->hasMany(FormAssignment::class);
    }

    public function assignedForms()
    {
        return $this->belongsToMany(ChecklistForm::class, 'form_assignments', 'user_id', 'form_id');
    }

    public function submissions()
    {
        return $this->hasMany(ChecklistSubmission::class, 'submitted_by');
    }

    public function dailyActivities()
    {
        return $this->hasMany(DailyActivity::class);
    }

    // Helper role
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }
}
