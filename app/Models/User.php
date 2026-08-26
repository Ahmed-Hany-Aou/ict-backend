<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'grade', 'role', 'is_active', 'is_paid', 'is_premium', 'premium_expires_at', 'payment_reference', 'payment_screenshot_path'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'is_premium' => 'boolean',
        'premium_expires_at' => 'datetime'
    ];




    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return in_array($this->role, ['admin', 'teacher']);
    }
    public function canAccessPanel(Panel $panel): bool
    {
        // This is the rule that grants access.
        // It checks if the user's email is the one you use to log in.
        return $this->role === 'admin';
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications()
    {
        return $this->hasManyThrough(
            Notification::class,
            UserNotification::class,
            'user_id',
            'id',
            'id',
            'notification_id'
        );
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function isPremiumActive()
    {
        return $this->is_premium &&
               ($this->premium_expires_at === null || $this->premium_expires_at->isFuture());
    }

    public function activatePremium($duration_days = 30)
    {
        $this->is_premium = true;
        $this->premium_expires_at = now()->addDays($duration_days);
        $this->save();
    }

    public function deactivatePremium()
    {
        $this->is_premium = false;
        $this->premium_expires_at = null;
        $this->save();
    }
}
