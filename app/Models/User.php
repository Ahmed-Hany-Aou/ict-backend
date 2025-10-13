<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'grade', 'role', 'is_active', 'is_paid'
    ];

    protected $hidden = ['password', 'remember_token'];

        protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean'
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
}
