<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id', 'category', 'title', 'description', 'questions', 'passing_score', 'is_active', 'is_premium'
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean'
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }
}
