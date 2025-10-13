<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\UserProgress;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $chapters = Chapter::where('is_published', true)
            ->orderBy('chapter_number')
            ->get();

        // Add user progress to each chapter
        foreach ($chapters as $chapter) {
            $progress = UserProgress::where('user_id', $user->id)
                ->where('chapter_id', $chapter->id)
                ->first();
            
            $chapter->user_progress = $progress ? $progress->status : 'not_started';
            
            // Check if user can access premium content
            if ($chapter->is_premium && !$user->is_paid) {
                $chapter->content = null; // Hide content for non-paid users
                $chapter->is_locked = true;
            } else {
                $chapter->is_locked = false;
            }
        }

        return response()->json($chapters);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $chapter = Chapter::where('is_published', true)->findOrFail($id);

        // Check access permissions
        if ($chapter->is_premium && !$user->is_paid) {
            return response()->json(['message' => 'Premium content requires payment'], 403);
        }

        // Update user progress
        UserProgress::updateOrCreate(
            ['user_id' => $user->id, 'chapter_id' => $chapter->id],
            ['status' => 'in_progress', 'started_at' => now()]
        );

        return response()->json($chapter);
    }

    public function markComplete(Request $request, $id)
    {
        $user = $request->user();
        
        UserProgress::updateOrCreate(
            ['user_id' => $user->id, 'chapter_id' => $id],
            [
                'status' => 'completed',
                'completed_at' => now(),
                'started_at' => now() // In case it wasn't started before
            ]
        );

        return response()->json(['message' => 'Chapter marked as completed']);
    }
}
