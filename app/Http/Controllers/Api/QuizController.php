<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizResult;

class QuizController extends Controller
{
    /**
     * Get quiz by chapter ID with randomized questions/answers
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

        // Randomize questions and their answers
        $questions = $quiz->questions;
        shuffle($questions); // Randomize question order

        // Randomize answers within each question
        foreach ($questions as &$question) {
            if (isset($question['options']) && is_array($question['options'])) {
                // Store the correct answer text before shuffling
                $correctAnswerText = $question['options'][$question['correct_answer']];
                
                // Shuffle the options
                shuffle($question['options']);
                
                // Find the new index of the correct answer
                $question['correct_answer'] = array_search($correctAnswerText, $question['options']);
            }
        }

        $quizData = $quiz->toArray();
        $quizData['questions'] = $questions;

        return response()->json([
            'success' => true,
            'quiz' => $quizData
        ]);
    }

    /**
     * Get quiz by ID with randomization
     */
    public function getQuiz($quizId)
    {
        $quiz = Quiz::where('id', $quizId)
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz not found'
            ], 404);
        }

        // Apply same randomization as above
        $questions = $quiz->questions;
        shuffle($questions);

        foreach ($questions as &$question) {
            if (isset($question['options']) && is_array($question['options'])) {
                $correctAnswerText = $question['options'][$question['correct_answer']];
                shuffle($question['options']);
                $question['correct_answer'] = array_search($correctAnswerText, $question['options']);
            }
        }

        $quizData = $quiz->toArray();
        $quizData['questions'] = $questions;

        return response()->json([
            'success' => true,
            'quiz' => $quizData
        ]);
    }

    /**
     * Get all quizzes grouped by category
     */
    public function getAllQuizzes()
    {
        $quizzes = Quiz::with('chapter')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('category');

        return response()->json([
            'success' => true,
            'quizzes' => $quizzes
        ]);
    }

    /**
     * Get quizzes by category
     */
    public function getQuizzesByCategory($category)
    {
        $quizzes = Quiz::with('chapter')
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'category' => $category,
            'quizzes' => $quizzes
        ]);
    }

    /**
     * Submit quiz answers and calculate score
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $request->validate([
            'answers' => 'required|array',
            'time_taken' => 'nullable|integer'
        ]);

        $quiz = Quiz::findOrFail($quizId);
        $userAnswers = $request->answers;
        $questions = $quiz->questions;

        // Calculate attempt number
        $attemptNumber = QuizResult::where('user_id', auth()->id())
            ->where('quiz_id', $quizId)
            ->count() + 1;

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
            'attempt_number' => $attemptNumber,
            'answers' => $userAnswers,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'percentage' => $percentage,
            'passed' => $passed,
            'time_taken' => $request->time_taken
        ]);

        return response()->json([
            'success' => true,
            'message' => $passed ? 'Congratulations! You passed!' : 'Keep studying and try again!',
            'result' => [
                'id' => $quizResult->id,
                'attempt_number' => $attemptNumber,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'percentage' => round($percentage, 2),
                'passed' => $passed,
                'passing_score' => $quiz->passing_score,
                'time_taken' => $request->time_taken
            ]
        ]);
    }

    /**
     * Get all user's quiz results
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
     * Get all attempts for a specific quiz
     */
    public function getQuizAttempts($quizId)
    {
        $attempts = QuizResult::with('quiz')
            ->where('user_id', auth()->id())
            ->where('quiz_id', $quizId)
            ->orderBy('attempt_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'total_attempts' => $attempts->count(),
            'attempts' => $attempts
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
