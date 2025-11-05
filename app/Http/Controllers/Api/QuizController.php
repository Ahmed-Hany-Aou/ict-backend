<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizResult;

class QuizController extends Controller
{
    use ApiResponse;
    /**
     * Get quiz by chapter ID with shuffled questions and options
     */
    public function getQuizByChapter($chapterId)
    {
        $quiz = Quiz::where('chapter_id', $chapterId)
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            return $this->notFoundResponse('No quiz found for this chapter');
        }

        // Check if quiz is premium and user doesn't have access
        if ($quiz->is_premium && !auth()->user()->isPremiumActive()) {
            return $this->errorResponse('This quiz is premium content. Please upgrade to access.', 403);
        }

        // Get questions and shuffle both questions and options
        $questions = $quiz->questions;

        // Shuffle options within each question
        foreach ($questions as &$question) {
            if (isset($question['options']) && is_array($question['options'])) {
                // Store the correct answer text before shuffling
                $correctAnswerText = $question['options'][$question['correct_answer']];

                // Shuffle the options
                shuffle($question['options']);

                // Find the new index of the correct answer after shuffling
                $question['correct_answer'] = array_search($correctAnswerText, $question['options']);
            }
        }

        // Shuffle the questions array
        shuffle($questions);

        $quizData = $quiz->toArray();
        $quizData['questions'] = $questions;

        return $this->successResponse([
            'quiz' => $quizData
        ], 'Quiz retrieved successfully');
    }

    /**
     * Get quiz by ID with shuffled questions and options
     */
    public function getQuiz($quizId)
    {
        $quiz = Quiz::where('id', $quizId)
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            return $this->notFoundResponse('Quiz not found');
        }

        // Check if quiz is premium and user doesn't have access
        if ($quiz->is_premium && !auth()->user()->isPremiumActive()) {
            return $this->errorResponse('This quiz is premium content. Please upgrade to access.', 403);
        }

        // Get questions and shuffle both questions and options
        $questions = $quiz->questions;

        // Shuffle options within each question
        foreach ($questions as &$question) {
            if (isset($question['options']) && is_array($question['options'])) {
                // Store the correct answer text before shuffling
                $correctAnswerText = $question['options'][$question['correct_answer']];

                // Shuffle the options
                shuffle($question['options']);

                // Find the new index of the correct answer after shuffling
                $question['correct_answer'] = array_search($correctAnswerText, $question['options']);
            }
        }

        // Shuffle the questions array
        shuffle($questions);

        $quizData = $quiz->toArray();
        $quizData['questions'] = $questions;

        return $this->successResponse([
            'quiz' => $quizData
        ], 'Quiz retrieved successfully');
    }

    /**
     * Get all quizzes grouped by category
     */
    public function getAllQuizzes()
    {
        $user = auth()->user();
        $quizzes = Quiz::with('chapter')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($quiz) use ($user) {
                $quiz->is_locked = $quiz->is_premium && !$user->isPremiumActive();
                return $quiz;
            })
            ->groupBy('category');

        return $this->successResponse([
            'quizzes' => $quizzes
        ], 'Quizzes retrieved successfully');
    }

    /**
     * Get quizzes by category
     */
    public function getQuizzesByCategory($category)
    {
        $user = auth()->user();
        $quizzes = Quiz::with('chapter')
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($quiz) use ($user) {
                $quiz->is_locked = $quiz->is_premium && !$user->isPremiumActive();
                return $quiz;
            });

        return $this->successResponse([
            'category' => $category,
            'quizzes' => $quizzes
        ], 'Quizzes retrieved successfully');
    }

    /**
     * Submit quiz answers and calculate score
     */
    public function submitQuiz(Request $request, $quizId)
    {
        try {
            $request->validate([
                'answers' => 'required|array',
                'questions' => 'required|array', // The shuffled questions from frontend
                'time_taken' => 'nullable|integer'
            ]);

            $quiz = Quiz::findOrFail($quizId);
            $userAnswers = $request->answers;

            // IMPORTANT: Use the shuffled questions sent from frontend for accurate scoring
            // The frontend receives questions+options shuffled and sends them back with answers
            // This ensures the correct_answer index matches the shuffled options array
            $questions = $request->questions;

            if (empty($questions)) {
                return $this->errorResponse('Quiz has no questions', 400);
            }

            // Validate that the number of questions matches the original quiz
            if (count($questions) !== count($quiz->questions)) {
                return $this->errorResponse('Invalid quiz data: question count mismatch', 400);
            }

            // Calculate attempt number
            $attemptNumber = QuizResult::where('user_id', auth()->id())
                ->where('quiz_id', $quizId)
                ->count() + 1;

            // Calculate score and prepare detailed results
            $score = 0;
            $totalQuestions = count($questions);
            $detailedResults = [];

            foreach ($questions as $index => $question) {
                $userAnswer = $userAnswers[$index] ?? null;

                // Compare user's answer index with correct answer index in the shuffled options
                $isCorrect = $userAnswer !== null && $userAnswer == $question['correct_answer'];

                if ($isCorrect) {
                    $score++;
                }

                // Store detailed question data for review
                $detailedResults[] = [
                    'question' => $question['question'],
                    'options' => $question['options'],
                    'user_answer' => $userAnswer,
                    'correct_answer' => $question['correct_answer'],
                    'explanation' => $question['explanation'] ?? null,
                    'is_correct' => $isCorrect
                ];
            }

            $percentage = ($score / $totalQuestions) * 100;
            $passed = $percentage >= $quiz->passing_score;

            // Save result
            $quizResult = QuizResult::create([
                'user_id' => auth()->id(),
                'quiz_id' => $quiz->id,
                'attempt_number' => $attemptNumber,
                'answers' => $userAnswers,
                'questions_data' => $detailedResults,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'percentage' => $percentage,
                'passed' => $passed,
                'time_taken' => $request->time_taken ?? null
            ]);

            return $this->successResponse([
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
            ], $passed ? 'Congratulations! You passed!' : 'Keep studying and try again!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Quiz submission error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to submit quiz. Please try again.', $e);
        }
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

        return $this->successResponse([
            'results' => $results
        ], 'Quiz results retrieved successfully');
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

        return $this->successResponse([
            'total_attempts' => $attempts->count(),
            'attempts' => $attempts
        ], 'Quiz attempts retrieved successfully');
    }

    /**
     * Get specific quiz result with detailed answers
     */
    public function getResult($resultId)
    {
        $result = QuizResult::with('quiz.chapter')
            ->where('user_id', auth()->id())
            ->findOrFail($resultId);

        // If questions_data exists, enhance it; otherwise build it from quiz questions
        if (!$result->questions_data && $result->quiz) {
            $questions = $result->quiz->questions;
            $userAnswers = $result->answers;
            $detailedResults = [];

            foreach ($questions as $index => $question) {
                $userAnswer = $userAnswers[$index] ?? null;
                $isCorrect = $userAnswer !== null && $userAnswer == $question['correct_answer'];

                $detailedResults[] = [
                    'question_number' => $index + 1,
                    'question' => $question['question'],
                    'options' => $question['options'],
                    'user_answer' => $userAnswer,
                    'user_answer_text' => $userAnswer !== null && isset($question['options'][$userAnswer]) ? $question['options'][$userAnswer] : 'Not answered',
                    'correct_answer' => $question['correct_answer'],
                    'correct_answer_text' => $question['options'][$question['correct_answer']],
                    'explanation' => $question['explanation'] ?? null,
                    'is_correct' => $isCorrect
                ];
            }

            $result->questions_data = $detailedResults;
        } else if ($result->questions_data) {
            // Ensure all questions have the text fields
            $enhanced = [];
            foreach ($result->questions_data as $index => $item) {
                $enhancedItem = $item;

                if (!isset($enhancedItem['question_number'])) {
                    $enhancedItem['question_number'] = $index + 1;
                }
                if (!isset($enhancedItem['user_answer_text'])) {
                    if ($enhancedItem['user_answer'] !== null && isset($enhancedItem['options'][$enhancedItem['user_answer']])) {
                        $enhancedItem['user_answer_text'] = $enhancedItem['options'][$enhancedItem['user_answer']];
                    } else {
                        $enhancedItem['user_answer_text'] = 'Not answered';
                    }
                }
                if (!isset($enhancedItem['correct_answer_text'])) {
                    $enhancedItem['correct_answer_text'] = $enhancedItem['options'][$enhancedItem['correct_answer']];
                }

                $enhanced[] = $enhancedItem;
            }
            $result->questions_data = $enhanced;
        }

        return $this->successResponse([
            'result' => $result
        ], 'Quiz result retrieved successfully');
    }

    /**
     * Get detailed review of a quiz result (all questions with answers)
     */
    public function getDetailedResult($resultId)
    {
        try {
            $result = QuizResult::with('quiz.chapter')
                ->where('user_id', auth()->id())
                ->findOrFail($resultId);

            if (!$result->quiz) {
                return $this->notFoundResponse('Quiz data not found');
            }

            // Build detailed results if not stored
            if (!$result->questions_data || empty($result->questions_data)) {
                $questions = $result->quiz->questions;
                $userAnswers = $result->answers;
                $detailedResults = [];

                foreach ($questions as $index => $question) {
                    $userAnswer = $userAnswers[$index] ?? null;
                    $isCorrect = $userAnswer !== null && $userAnswer == $question['correct_answer'];

                    $detailedResults[] = [
                        'question_number' => $index + 1,
                        'question' => $question['question'],
                        'options' => $question['options'],
                        'user_answer' => $userAnswer,
                        'user_answer_text' => $userAnswer !== null && isset($question['options'][$userAnswer]) ? $question['options'][$userAnswer] : 'Not answered',
                        'correct_answer' => $question['correct_answer'],
                        'correct_answer_text' => $question['options'][$question['correct_answer']],
                        'explanation' => $question['explanation'] ?? null,
                        'is_correct' => $isCorrect
                    ];
                }
            } else {
                $detailedResults = [];
                // Add question numbers and answer text if missing
                foreach ($result->questions_data as $index => $item) {
                    $detailedItem = $item;

                    if (!isset($detailedItem['question_number'])) {
                        $detailedItem['question_number'] = $index + 1;
                    }
                    if (!isset($detailedItem['user_answer_text'])) {
                        if ($detailedItem['user_answer'] !== null && isset($detailedItem['options'][$detailedItem['user_answer']])) {
                            $detailedItem['user_answer_text'] = $detailedItem['options'][$detailedItem['user_answer']];
                        } else {
                            $detailedItem['user_answer_text'] = 'Not answered';
                        }
                    }
                    if (!isset($detailedItem['correct_answer_text'])) {
                        $detailedItem['correct_answer_text'] = $detailedItem['options'][$detailedItem['correct_answer']];
                    }

                    $detailedResults[] = $detailedItem;
                }
            }

            return $this->successResponse([
                'result' => [
                    'id' => $result->id,
                    'quiz_title' => $result->quiz->title,
                    'chapter_name' => $result->quiz->chapter->title ?? 'N/A',
                    'attempt_number' => $result->attempt_number,
                    'score' => $result->score,
                    'total_questions' => $result->total_questions,
                    'percentage' => round($result->percentage, 2),
                    'passed' => $result->passed,
                    'passing_score' => $result->quiz->passing_score,
                    'time_taken' => $result->time_taken,
                    'created_at' => $result->created_at,
                    'questions' => $detailedResults
                ]
            ], 'Detailed quiz result retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Get detailed result error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get detailed result', $e);
        }
    }
}
