<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'quiz_id', 'attempt_number', 'answers', 'questions_data', 'score', 'total_questions', 'percentage', 'passed', 'time_taken'
    ];

    protected $casts = [
        'answers' => 'array',
        'questions_data' => 'array',
        'passed' => 'boolean',
        'time_taken' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
