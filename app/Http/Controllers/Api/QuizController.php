<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuizResult;

class QuizController extends Controller
{
    public function submitQuiz(Request $request)
    {
        $request->validate([
            'chapter' => 'required|string',
            'score' => 'required|integer',
            'total_questions' => 'required|integer',
            'answers' => 'array'
        ]);

        $quizResult = QuizResult::create([
            'user_id' => auth()->id(),
            'chapter' => $request->chapter,
            'score' => $request->score,
            'total_questions' => $request->total_questions,
            'answers' => $request->answers
        ]);

        return response()->json([
            'message' => 'Quiz submitted successfully',
            'result' => $quizResult
        ]);
    }

    public function getResults(Request $request)
    {
        $results = QuizResult::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($results);
    }
}
