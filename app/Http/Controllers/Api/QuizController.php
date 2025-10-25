<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizResult;

class QuizController extends Controller
{
    /**
     * Get quiz by chapter ID
     */
    public function getQuizByChapter($chapterId)
    {
        $quiz = Quiz::where('chapter_id', $chapterId)
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'No quiz found for this chapter'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'quiz' => $quiz
        ]);
    }

    /**
     * Submit quiz answers and calculate score
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $request->validate([
            'answers' => 'required|array'
        ]);

        $quiz = Quiz::findOrFail($quizId);
        $userAnswers = $request->answers;
        $questions = $quiz->questions;

        // Calculate score
        $score = 0;
        $totalQuestions = count($questions);

        foreach ($questions as $index => $question) {
            $userAnswer = $userAnswers[$index] ?? null;
            if ($userAnswer !== null && $userAnswer == $question['correct_answer']) {
                $score++;
            }
        }

        $percentage = ($score / $totalQuestions) * 100;
        $passed = $percentage >= $quiz->passing_score;

        // Save result
        $quizResult = QuizResult::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'answers' => $userAnswers,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'percentage' => $percentage,
            'passed' => $passed
        ]);

        return response()->json([
            'success' => true,
            'message' => $passed ? 'Congratulations! You passed!' : 'Keep studying and try again!',
            'result' => [
                'score' => $score,
                'total_questions' => $totalQuestions,
                'percentage' => round($percentage, 2),
                'passed' => $passed,
                'passing_score' => $quiz->passing_score
            ]
        ]);
    }

    /**
     * Get user's quiz results
     */
    public function getResults(Request $request)
    {
        $results = QuizResult::with('quiz.chapter')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Get specific quiz result
     */
    public function getResult($resultId)
    {
        $result = QuizResult::with('quiz')
            ->where('user_id', auth()->id())
            ->findOrFail($resultId);

        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }
}
