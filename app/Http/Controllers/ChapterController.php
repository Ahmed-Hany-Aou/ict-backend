<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChapterController extends Controller
{
    /**
     * Get all chapters for the learning platform
     * This demonstrates clean architecture with proper response formatting
     */
    public function getChapters(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Mock data - replace with database queries
            $chapters = [
                [
                    'id' => 1,
                    'title' => 'Data, Information, and Knowledge',
                    'description' => 'Understand fundamental concepts of data in ICT',
                    'icon' => '📊',
                    'completed' => false,
                    'progress' => 0,
                    'slidesCount' => 15,
                    'quizCount' => 2,
                ],
                [
                    'id' => 2,
                    'title' => 'Information Systems',
                    'description' => 'Explore how information systems work',
                    'icon' => '💻',
                    'completed' => false,
                    'progress' => 0,
                    'slidesCount' => 20,
                    'quizCount' => 3,
                ],
                [
                    'id' => 3,
                    'title' => 'Cyber Security Fundamentals',
                    'description' => 'Learn about protecting digital information',
                    'icon' => '🔒',
                    'completed' => false,
                    'progress' => 0,
                    'slidesCount' => 18,
                    'quizCount' => 2,
                ],
                [
                    'id' => 4,
                    'title' => 'Computing Devices',
                    'description' => 'Understand hardware and computing devices',
                    'icon' => '🖥️',
                    'completed' => false,
                    'progress' => 0,
                    'slidesCount' => 16,
                    'quizCount' => 2,
                ],
                [
                    'id' => 5,
                    'title' => 'Computer Networks',
                    'description' => 'Learn about networking concepts',
                    'icon' => '🌐',
                    'completed' => false,
                    'progress' => 0,
                    'slidesCount' => 22,
                    'quizCount' => 3,
                ],
                [
                    'id' => 6,
                    'title' => 'Internet & World Wide Web',
                    'description' => 'Explore the internet and web technologies',
                    'icon' => '🌍',
                    'completed' => false,
                    'progress' => 0,
                    'slidesCount' => 20,
                    'quizCount' => 2,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Chapters retrieved successfully',
                'data' => $chapters,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chapters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific chapter details
     */
    public function getChapter(Request $request, int $id): JsonResponse
    {
        try {
            $request->user();

            $chapter = [
                'id' => $id,
                'title' => 'Chapter Title',
                'description' => 'Chapter Description',
                'slides' => [
                    ['id' => 1, 'title' => 'Slide 1', 'content' => 'Content here'],
                    ['id' => 2, 'title' => 'Slide 2', 'content' => 'Content here'],
                ],
                'quiz' => [
                    ['id' => 1, 'question' => 'Question 1', 'options' => ['A', 'B', 'C', 'D']],
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $chapter
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chapter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user progress
     */
    public function getUserProgress(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $progress = [
                'totalChapters' => 6,
                'completedChapters' => 0,
                'totalSlides' => 111,
                'viewedSlides' => 0,
                'totalQuizzes' => 14,
                'completedQuizzes' => 0,
                'overallProgress' => 0,
                'joinDate' => $user->created_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $progress
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}