<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Support\PermissionRegistry;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

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

    public function hasModuleAccess(string $module, string $level = 'read'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->getPermissionNames();
        $legacy = PermissionRegistry::legacy($module);
        $read = PermissionRegistry::read($module);
        $write = PermissionRegistry::write($module);

        if ($level === 'write') {
            return $permissions->contains($write);
        }

        return $permissions->contains($read) || $permissions->contains($write) || $permissions->contains($legacy);
    }
}
