<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\SlideProgress;
use App\Models\QuizResult;
use App\Models\Chapter;
use App\Models\Slide;
use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminStudentPerformanceController extends Controller
{
    use ApiResponse;

    /**
     * Get all students with their performance summary
     */
    public function getAllStudents(Request $request)
    {
        $query = User::where('role', 'student')
            ->with(['progress', 'quizResults'])
            ->select('id', 'name', 'email', 'grade', 'is_premium', 'created_at');

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        $students = $query->get()->map(function ($student) {
            return $this->calculateStudentPerformance($student);
        });

        return $this->successResponse([
            'students' => $students,
            'total' => $students->count()
        ], 'Students retrieved successfully');
    }

    /**
     * Get detailed performance for a specific student
     */
    public function getStudentPerformance($userId)
    {
        $student = User::where('role', 'student')->findOrFail($userId);

        $performance = $this->calculateDetailedPerformance($student);

        return $this->successResponse([
            'student' => $performance
        ], 'Student performance retrieved successfully');
    }

    /**
     * Generate performance report for a specific period
     */
    public function generateReport(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|in:weekly,monthly,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $student = User::where('role', 'student')->findOrFail($userId);

        // Determine date range
        [$startDate, $endDate] = $this->getDateRange($request->period, $request->start_date, $request->end_date);

        // Get performance data for the period
        $report = $this->generatePerformanceReport($student, $startDate, $endDate);

        return $this->successResponse([
            'report' => $report,
            'period' => [
                'type' => $request->period,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ], 'Report generated successfully');
    }

    /**
     * Get performance analytics with charts data
     */
    public function getAnalytics($userId, Request $request)
    {
        $student = User::where('role', 'student')->findOrFail($userId);

        $period = $request->get('period', 'monthly'); // weekly, monthly, all
        [$startDate, $endDate] = $this->getDateRange($period);

        $analytics = [
            'overview' => $this->calculateDetailedPerformance($student),
            'quiz_performance_over_time' => $this->getQuizPerformanceTimeline($student, $startDate, $endDate),
            'chapter_completion_timeline' => $this->getChapterCompletionTimeline($student, $startDate, $endDate),
            'time_distribution' => $this->getTimeDistribution($student),
            'engagement_metrics' => $this->getEngagementMetrics($student),
            'quiz_category_performance' => $this->getQuizCategoryPerformance($student),
        ];

        return $this->successResponse($analytics, 'Analytics retrieved successfully');
    }

    /**
     * Helper: Calculate basic student performance summary
     */
    private function calculateStudentPerformance($student)
    {
        $totalChapters = Chapter::where('is_published', true)->count();
        $completedChapters = UserProgress::where('user_id', $student->id)
            ->where('status', 'completed')
            ->count();

        $totalSlides = Slide::whereHas('chapter', function($q) {
            $q->where('is_published', true);
        })->count();

        $completedSlides = SlideProgress::where('user_id', $student->id)
            ->where('completed', true)
            ->count();

        $totalQuizzes = Quiz::where('is_active', true)->count();
        $passedQuizzes = QuizResult::where('user_id', $student->id)
            ->where('passed', true)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $totalAttempts = QuizResult::where('user_id', $student->id)->count();

        $totalTimeSpent = SlideProgress::where('user_id', $student->id)
            ->sum('time_spent');

        $quizTimeSpent = QuizResult::where('user_id', $student->id)
            ->sum('time_taken');

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'grade' => $student->grade,
            'is_premium' => $student->is_premium,
            'joined_date' => $student->created_at->format('Y-m-d'),
            'performance' => [
                'chapters_completed' => $completedChapters,
                'total_chapters' => $totalChapters,
                'chapters_percentage' => $totalChapters > 0 ? round(($completedChapters / $totalChapters) * 100, 2) : 0,
                'slides_completed' => $completedSlides,
                'total_slides' => $totalSlides,
                'slides_percentage' => $totalSlides > 0 ? round(($completedSlides / $totalSlides) * 100, 2) : 0,
                'quizzes_passed' => $passedQuizzes,
                'total_quizzes' => $totalQuizzes,
                'quiz_attempts' => $totalAttempts,
                'quiz_percentage' => $totalQuizzes > 0 ? round(($passedQuizzes / $totalQuizzes) * 100, 2) : 0,
                'total_time_spent_minutes' => round($totalTimeSpent / 60, 2),
                'quiz_time_spent_minutes' => round($quizTimeSpent / 60, 2),
                'total_time_minutes' => round(($totalTimeSpent + $quizTimeSpent) / 60, 2),
            ]
        ];
    }

    /**
     * Helper: Calculate detailed performance metrics
     */
    private function calculateDetailedPerformance($student)
    {
        $basic = $this->calculateStudentPerformance($student);

        // Chapter-level details
        $chapterDetails = UserProgress::where('user_id', $student->id)
            ->with('chapter')
            ->get()
            ->map(function($progress) use ($student) {
                $chapter = $progress->chapter;

                $chapterSlides = Slide::where('chapter_id', $chapter->id)->count();
                $completedSlides = SlideProgress::where('user_id', $student->id)
                    ->where('chapter_id', $chapter->id)
                    ->where('completed', true)
                    ->count();

                $slideTimeSpent = SlideProgress::where('user_id', $student->id)
                    ->where('chapter_id', $chapter->id)
                    ->sum('time_spent');

                return [
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'status' => $progress->status,
                    'slides_completed' => $completedSlides,
                    'total_slides' => $chapterSlides,
                    'time_spent_minutes' => round($slideTimeSpent / 60, 2),
                    'started_at' => $progress->started_at?->format('Y-m-d H:i:s'),
                    'completed_at' => $progress->completed_at?->format('Y-m-d H:i:s'),
                ];
            });

        // Quiz attempts details
        $quizAttempts = QuizResult::where('user_id', $student->id)
            ->with('quiz')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($result) {
                return [
                    'quiz_id' => $result->quiz_id,
                    'quiz_title' => $result->quiz->title,
                    'category' => $result->quiz->category,
                    'attempt_number' => $result->attempt_number,
                    'score' => $result->score,
                    'total_questions' => $result->total_questions,
                    'percentage' => $result->percentage,
                    'passed' => $result->passed,
                    'time_taken_minutes' => $result->time_taken ? round($result->time_taken / 60, 2) : 0,
                    'attempted_at' => $result->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Slide engagement details
        $slideEngagement = SlideProgress::where('user_id', $student->id)
            ->with('slide')
            ->get()
            ->map(function($progress) {
                return [
                    'slide_id' => $progress->slide_id,
                    'chapter_id' => $progress->chapter_id,
                    'completed' => $progress->completed,
                    'view_count' => $progress->view_count,
                    'time_spent_seconds' => $progress->time_spent,
                    'started_at' => $progress->started_at?->format('Y-m-d H:i:s'),
                    'last_viewed_at' => $progress->last_viewed_at?->format('Y-m-d H:i:s'),
                ];
            });

        return array_merge($basic, [
            'chapters' => $chapterDetails,
            'quiz_attempts' => $quizAttempts,
            'slide_engagement' => $slideEngagement,
        ]);
    }

    /**
     * Helper: Get date range based on period
     */
    private function getDateRange($period, $customStart = null, $customEnd = null)
    {
        if ($period === 'custom' && $customStart && $customEnd) {
            return [
                Carbon::parse($customStart)->startOfDay(),
                Carbon::parse($customEnd)->endOfDay()
            ];
        }

        $endDate = Carbon::now()->endOfDay();

        $startDate = match($period) {
            'weekly' => Carbon::now()->subWeek()->startOfDay(),
            'monthly' => Carbon::now()->subMonth()->startOfDay(),
            default => Carbon::now()->subMonth()->startOfDay(),
        };

        return [$startDate, $endDate];
    }

    /**
     * Helper: Generate performance report for specific period
     */
    private function generatePerformanceReport($student, $startDate, $endDate)
    {
        $chaptersCompleted = UserProgress::where('user_id', $student->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->with('chapter')
            ->get()
            ->map(function($progress) {
                return [
                    'chapter_title' => $progress->chapter->title,
                    'completed_at' => $progress->completed_at->format('Y-m-d H:i:s'),
                ];
            });

        $slidesCompleted = SlideProgress::where('user_id', $student->id)
            ->where('completed', true)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $quizResults = QuizResult::where('user_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('quiz')
            ->get();

        $quizzesPassed = $quizResults->where('passed', true)->unique('quiz_id')->count();
        $totalAttempts = $quizResults->count();
        $averageScore = $quizResults->avg('percentage');

        $timeSpentOnSlides = SlideProgress::where('user_id', $student->id)
            ->whereBetween('last_viewed_at', [$startDate, $endDate])
            ->sum('time_spent');

        $timeSpentOnQuizzes = QuizResult::where('user_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('time_taken');

        $totalTimeMinutes = round(($timeSpentOnSlides + $timeSpentOnQuizzes) / 60, 2);

        return [
            'student_info' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'grade' => $student->grade,
            ],
            'summary' => [
                'chapters_completed' => $chaptersCompleted->count(),
                'slides_completed' => $slidesCompleted,
                'quizzes_passed' => $quizzesPassed,
                'quiz_attempts' => $totalAttempts,
                'average_quiz_score' => round($averageScore, 2),
                'total_time_spent_minutes' => $totalTimeMinutes,
                'slide_time_minutes' => round($timeSpentOnSlides / 60, 2),
                'quiz_time_minutes' => round($timeSpentOnQuizzes / 60, 2),
            ],
            'chapters_completed_details' => $chaptersCompleted,
            'quiz_results' => $quizResults->map(function($result) {
                return [
                    'quiz_title' => $result->quiz->title,
                    'category' => $result->quiz->category,
                    'attempt_number' => $result->attempt_number,
                    'score' => $result->score,
                    'percentage' => round($result->percentage, 2),
                    'passed' => $result->passed,
                    'time_taken_minutes' => $result->time_taken ? round($result->time_taken / 60, 2) : 0,
                    'date' => $result->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }

    /**
     * Helper: Get quiz performance timeline
     */
    private function getQuizPerformanceTimeline($student, $startDate, $endDate)
    {
        $results = QuizResult::where('user_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function($result) {
                return $result->created_at->format('Y-m-d');
            })
            ->map(function($dayResults, $date) {
                return [
                    'date' => $date,
                    'attempts' => $dayResults->count(),
                    'average_score' => round($dayResults->avg('percentage'), 2),
                    'passed' => $dayResults->where('passed', true)->count(),
                ];
            })
            ->values();

        return $results;
    }

    /**
     * Helper: Get chapter completion timeline
     */
    private function getChapterCompletionTimeline($student, $startDate, $endDate)
    {
        $completions = UserProgress::where('user_id', $student->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->with('chapter')
            ->orderBy('completed_at', 'asc')
            ->get()
            ->map(function($progress) {
                return [
                    'date' => $progress->completed_at->format('Y-m-d'),
                    'chapter_title' => $progress->chapter->title,
                    'chapter_id' => $progress->chapter_id,
                ];
            });

        return $completions;
    }

    /**
     * Helper: Get time distribution across chapters
     */
    private function getTimeDistribution($student)
    {
        $timeByChapter = SlideProgress::where('user_id', $student->id)
            ->select('chapter_id', DB::raw('SUM(time_spent) as total_time'))
            ->groupBy('chapter_id')
            ->with('chapter')
            ->get()
            ->map(function($item) {
                return [
                    'chapter_id' => $item->chapter_id,
                    'chapter_title' => $item->chapter->title ?? 'Unknown',
                    'time_spent_minutes' => round($item->total_time / 60, 2),
                ];
            });

        return $timeByChapter;
    }

    /**
     * Helper: Get engagement metrics
     */
    private function getEngagementMetrics($student)
    {
        $slideStats = SlideProgress::where('user_id', $student->id)
            ->selectRaw('
                COUNT(*) as total_slides_viewed,
                SUM(view_count) as total_views,
                AVG(view_count) as avg_views_per_slide,
                AVG(time_spent) as avg_time_per_slide,
                SUM(CASE WHEN time_spent < 30 THEN 1 ELSE 0 END) as potentially_skipped
            ')
            ->first();

        $quizStats = QuizResult::where('user_id', $student->id)
            ->selectRaw('
                AVG(time_taken) as avg_quiz_time,
                SUM(CASE WHEN time_taken < 60 THEN 1 ELSE 0 END) as quick_attempts
            ')
            ->first();

        return [
            'total_slides_viewed' => $slideStats->total_slides_viewed ?? 0,
            'total_slide_views' => $slideStats->total_views ?? 0,
            'avg_views_per_slide' => round($slideStats->avg_views_per_slide ?? 0, 2),
            'avg_time_per_slide_seconds' => round($slideStats->avg_time_per_slide ?? 0, 2),
            'potentially_skipped_slides' => $slideStats->potentially_skipped ?? 0,
            'avg_quiz_time_seconds' => round($quizStats->avg_quiz_time ?? 0, 2),
            'quick_quiz_attempts' => $quizStats->quick_attempts ?? 0,
        ];
    }

    /**
     * Helper: Get quiz performance by category
     */
    private function getQuizCategoryPerformance($student)
    {
        $categoryPerformance = QuizResult::where('user_id', $student->id)
            ->join('quizzes', 'quiz_results.quiz_id', '=', 'quizzes.id')
            ->select(
                'quizzes.category',
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(CASE WHEN quiz_results.passed = 1 THEN 1 ELSE 0 END) as passed_count'),
                DB::raw('AVG(quiz_results.percentage) as avg_percentage')
            )
            ->groupBy('quizzes.category')
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category,
                    'total_attempts' => $item->total_attempts,
                    'passed_count' => $item->passed_count,
                    'average_score' => round($item->avg_percentage, 2),
                ];
            });

        return $categoryPerformance;
    }

    /**
     * Export student performance to Excel (CSV format)
     */
    public function exportToExcel(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|in:weekly,monthly,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $student = User::where('role', 'student')->findOrFail($userId);
        [$startDate, $endDate] = $this->getDateRange($request->period, $request->start_date, $request->end_date);
        $report = $this->generatePerformanceReport($student, $startDate, $endDate);

        // Generate CSV content
        $csvData = $this->generateCSV($report);

        $filename = "student_performance_{$student->name}_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";

        return response()->streamDownload(function() use ($csvData) {
            echo $csvData;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export student performance to PDF
     */
    public function exportToPdf(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|in:weekly,monthly,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $student = User::where('role', 'student')->findOrFail($userId);
        [$startDate, $endDate] = $this->getDateRange($request->period, $request->start_date, $request->end_date);
        $report = $this->generatePerformanceReport($student, $startDate, $endDate);

        // Generate HTML content for PDF
        $html = $this->generatePDFHTML($report, $startDate, $endDate);

        $filename = "student_performance_{$student->name}_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.html";

        // For now, return as downloadable HTML. To convert to PDF, you can:
        // 1. Use a service like Puppeteer/Chrome headless
        // 2. Install wkhtmltopdf
        // 3. Use a package like barryvdh/laravel-dompdf (after enabling gd extension)

        return response()->streamDownload(function() use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html',
        ]);
    }

    /**
     * Send performance report via email
     */
    public function emailReport(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|in:weekly,monthly,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
            'recipient_email' => 'required|email',
            'message' => 'nullable|string',
        ]);

        $student = User::where('role', 'student')->findOrFail($userId);
        [$startDate, $endDate] = $this->getDateRange($request->period, $request->start_date, $request->end_date);
        $report = $this->generatePerformanceReport($student, $startDate, $endDate);

        // Generate email content
        $emailBody = $this->generateEmailBody($report, $startDate, $endDate, $request->message);

        // Send email using Laravel Mail
        try {
            \Mail::send([], [], function($message) use ($request, $emailBody, $student, $startDate, $endDate) {
                $message->to($request->recipient_email)
                        ->subject("Performance Report for {$student->name} ({$startDate->format('M d')} - {$endDate->format('M d, Y')})")
                        ->html($emailBody);
            });

            return $this->successResponse([
                'sent' => true,
                'recipient' => $request->recipient_email
            ], 'Report sent successfully');
        } catch (\Exception $e) {
            \Log::error('Email send error: ' . $e->getMessage());
            return $this->errorResponse('Failed to send email. Please check email configuration.', 500);
        }
    }

    /**
     * Helper: Generate CSV content from report
     */
    private function generateCSV($report)
    {
        $output = fopen('php://temp', 'w');

        // Student Info
        fputcsv($output, ['Student Performance Report']);
        fputcsv($output, ['']);
        fputcsv($output, ['Student Name', $report['student_info']['name']]);
        fputcsv($output, ['Email', $report['student_info']['email']]);
        fputcsv($output, ['Grade', $report['student_info']['grade'] ?? 'N/A']);
        fputcsv($output, ['']);

        // Summary
        fputcsv($output, ['Performance Summary']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Chapters Completed', $report['summary']['chapters_completed']]);
        fputcsv($output, ['Slides Completed', $report['summary']['slides_completed']]);
        fputcsv($output, ['Quizzes Passed', $report['summary']['quizzes_passed']]);
        fputcsv($output, ['Quiz Attempts', $report['summary']['quiz_attempts']]);
        fputcsv($output, ['Average Quiz Score', $report['summary']['average_quiz_score'] . '%']);
        fputcsv($output, ['Total Time Spent (minutes)', $report['summary']['total_time_spent_minutes']]);
        fputcsv($output, ['Slide Time (minutes)', $report['summary']['slide_time_minutes']]);
        fputcsv($output, ['Quiz Time (minutes)', $report['summary']['quiz_time_minutes']]);
        fputcsv($output, ['']);

        // Chapters Completed
        if (!empty($report['chapters_completed_details'])) {
            fputcsv($output, ['Chapters Completed']);
            fputcsv($output, ['Chapter Title', 'Completed At']);
            foreach ($report['chapters_completed_details'] as $chapter) {
                fputcsv($output, [$chapter['chapter_title'], $chapter['completed_at']]);
            }
            fputcsv($output, ['']);
        }

        // Quiz Results
        if (!empty($report['quiz_results'])) {
            fputcsv($output, ['Quiz Results']);
            fputcsv($output, ['Quiz Title', 'Category', 'Attempt', 'Score', 'Percentage', 'Passed', 'Time (min)', 'Date']);
            foreach ($report['quiz_results'] as $quiz) {
                fputcsv($output, [
                    $quiz['quiz_title'],
                    $quiz['category'],
                    $quiz['attempt_number'],
                    $quiz['score'],
                    $quiz['percentage'] . '%',
                    $quiz['passed'] ? 'Yes' : 'No',
                    $quiz['time_taken_minutes'],
                    $quiz['date'],
                ]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Helper: Generate PDF HTML content
     */
    private function generatePDFHTML($report, $startDate, $endDate)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #666; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .summary-item { padding: 10px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
        .passed { color: green; font-weight: bold; }
        .failed { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Student Performance Report</h1>
    <p><strong>Period:</strong> ' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y') . '</p>

    <h2>Student Information</h2>
    <table>
        <tr><th>Name</th><td>' . htmlspecialchars($report['student_info']['name']) . '</td></tr>
        <tr><th>Email</th><td>' . htmlspecialchars($report['student_info']['email']) . '</td></tr>
        <tr><th>Grade</th><td>' . htmlspecialchars($report['student_info']['grade'] ?? 'N/A') . '</td></tr>
    </table>

    <h2>Performance Summary</h2>
    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Chapters Completed</td><td>' . $report['summary']['chapters_completed'] . '</td></tr>
        <tr><td>Slides Completed</td><td>' . $report['summary']['slides_completed'] . '</td></tr>
        <tr><td>Quizzes Passed</td><td>' . $report['summary']['quizzes_passed'] . '</td></tr>
        <tr><td>Quiz Attempts</td><td>' . $report['summary']['quiz_attempts'] . '</td></tr>
        <tr><td>Average Quiz Score</td><td>' . $report['summary']['average_quiz_score'] . '%</td></tr>
        <tr><td>Total Time Spent</td><td>' . $report['summary']['total_time_spent_minutes'] . ' minutes</td></tr>
        <tr><td>Slide Time</td><td>' . $report['summary']['slide_time_minutes'] . ' minutes</td></tr>
        <tr><td>Quiz Time</td><td>' . $report['summary']['quiz_time_minutes'] . ' minutes</td></tr>
    </table>';

        if (!empty($report['chapters_completed_details'])) {
            $html .= '<h2>Chapters Completed</h2><table>
                <tr><th>Chapter Title</th><th>Completed At</th></tr>';
            foreach ($report['chapters_completed_details'] as $chapter) {
                $html .= '<tr><td>' . htmlspecialchars($chapter['chapter_title']) . '</td><td>' . $chapter['completed_at'] . '</td></tr>';
            }
            $html .= '</table>';
        }

        if (!empty($report['quiz_results'])) {
            $html .= '<h2>Quiz Results</h2><table>
                <tr><th>Quiz Title</th><th>Category</th><th>Attempt</th><th>Score</th><th>%</th><th>Status</th><th>Time</th><th>Date</th></tr>';
            foreach ($report['quiz_results'] as $quiz) {
                $status = $quiz['passed'] ? '<span class="passed">PASSED</span>' : '<span class="failed">FAILED</span>';
                $html .= '<tr>
                    <td>' . htmlspecialchars($quiz['quiz_title']) . '</td>
                    <td>' . $quiz['category'] . '</td>
                    <td>' . $quiz['attempt_number'] . '</td>
                    <td>' . $quiz['score'] . '</td>
                    <td>' . $quiz['percentage'] . '%</td>
                    <td>' . $status . '</td>
                    <td>' . $quiz['time_taken_minutes'] . ' min</td>
                    <td>' . $quiz['date'] . '</td>
                </tr>';
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Helper: Generate email body
     */
    private function generateEmailBody($report, $startDate, $endDate, $customMessage = null)
    {
        $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $html .= '<h2 style="color: #4CAF50;">Student Performance Report</h2>';
        $html .= '<p><strong>Student:</strong> ' . htmlspecialchars($report['student_info']['name']) . '</p>';
        $html .= '<p><strong>Period:</strong> ' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y') . '</p>';

        if ($customMessage) {
            $html .= '<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;">';
            $html .= '<p>' . nl2br(htmlspecialchars($customMessage)) . '</p>';
            $html .= '</div>';
        }

        $html .= '<h3>Performance Summary</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr style="background: #4CAF50; color: white;"><th style="padding: 10px; text-align: left;">Metric</th><th style="padding: 10px; text-align: left;">Value</th></tr>';
        $html .= '<tr><td style="padding: 10px; border: 1px solid #ddd;">Chapters Completed</td><td style="padding: 10px; border: 1px solid #ddd;">' . $report['summary']['chapters_completed'] . '</td></tr>';
        $html .= '<tr><td style="padding: 10px; border: 1px solid #ddd;">Slides Completed</td><td style="padding: 10px; border: 1px solid #ddd;">' . $report['summary']['slides_completed'] . '</td></tr>';
        $html .= '<tr><td style="padding: 10px; border: 1px solid #ddd;">Quizzes Passed</td><td style="padding: 10px; border: 1px solid #ddd;">' . $report['summary']['quizzes_passed'] . '</td></tr>';
        $html .= '<tr><td style="padding: 10px; border: 1px solid #ddd;">Average Quiz Score</td><td style="padding: 10px; border: 1px solid #ddd;">' . $report['summary']['average_quiz_score'] . '%</td></tr>';
        $html .= '<tr><td style="padding: 10px; border: 1px solid #ddd;">Total Time Spent</td><td style="padding: 10px; border: 1px solid #ddd;">' . $report['summary']['total_time_spent_minutes'] . ' minutes</td></tr>';
        $html .= '</table>';

        $html .= '<p style="margin-top: 30px; color: #666; font-size: 12px;">This is an automated report from the Learning Management System.</p>';
        $html .= '</div>';

        return $html;
    }
}
