<?php

namespace App\Filament\Resources\StudentPerformanceResource\Pages;

use App\Filament\Resources\StudentPerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use App\Models\UserProgress;
use App\Models\SlideProgress;
use App\Models\QuizResult;
use App\Models\Chapter;
use App\Models\Slide;
use App\Models\Quiz;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ViewStudentPerformance extends ViewRecord
{
    protected static string $resource = StudentPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_weekly_report')
                ->label('Weekly Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return $this->downloadReport('weekly');
                }),

            Actions\Action::make('download_monthly_report')
                ->label('Monthly Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action(function () {
                    return $this->downloadReport('monthly');
                }),

            Actions\Action::make('send_email')
                ->label('Email Report')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('period')
                        ->label('Report Period')
                        ->options([
                            'weekly' => 'Weekly (Last 7 Days)',
                            'monthly' => 'Monthly (Last 30 Days)',
                        ])
                        ->default('monthly')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('recipient_email')
                        ->label('Recipient Email')
                        ->email()
                        ->default(fn() => $this->record->email)
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('message')
                        ->label('Custom Message (Optional)')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->sendEmail($data);
                })
                ->requiresConfirmation()
                ->modalHeading('Send Performance Report')
                ->modalDescription('Send a performance report to the student via email.')
                ->modalSubmitActionLabel('Send Email'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $student = $this->record;

        // Calculate metrics
        $totalChapters = Chapter::where('is_published', true)->count();
        $completedChapters = UserProgress::where('user_id', $student->id)
            ->where('status', 'completed')
            ->count();

        $totalSlides = Slide::whereHas('chapter', fn($q) => $q->where('is_published', true))->count();
        $completedSlides = SlideProgress::where('user_id', $student->id)
            ->where('completed', true)
            ->count();

        $totalQuizzes = Quiz::where('is_active', true)->count();
        $passedQuizzes = QuizResult::where('user_id', $student->id)
            ->where('passed', true)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $totalAttempts = QuizResult::where('user_id', $student->id)->count();

        $slideTime = SlideProgress::where('user_id', $student->id)->sum('time_spent');
        $quizTime = QuizResult::where('user_id', $student->id)->sum('time_taken');
        $totalTime = round(($slideTime + $quizTime) / 60, 1);

        $avgQuizScore = QuizResult::where('user_id', $student->id)->avg('percentage');

        return $infolist
            ->schema([
                Section::make('Student Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')->label('Name'),
                                TextEntry::make('email')->label('Email'),
                                TextEntry::make('grade')->label('Grade')->badge()->color('info'),
                            ]),
                    ]),

                Section::make('Performance Summary')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('chapters')
                                    ->label('Chapters Completed')
                                    ->default("{$completedChapters} / {$totalChapters}")
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('slides')
                                    ->label('Slides Completed')
                                    ->default("{$completedSlides} / {$totalSlides}")
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('quizzes')
                                    ->label('Quizzes Passed')
                                    ->default("{$passedQuizzes} / {$totalQuizzes}")
                                    ->badge()
                                    ->color('primary'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('attempts')
                                    ->label('Total Quiz Attempts')
                                    ->default($totalAttempts)
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('avg_score')
                                    ->label('Average Quiz Score')
                                    ->default(round($avgQuizScore, 1) . '%')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('time')
                                    ->label('Total Time Spent')
                                    ->default($totalTime . ' minutes')
                                    ->badge()
                                    ->color('danger'),
                            ]),
                    ]),

                Section::make('Chapter Progress')
                    ->schema([
                        TextEntry::make('chapter_details')
                            ->label('')
                            ->default(function () use ($student) {
                                $chapters = UserProgress::where('user_id', $student->id)
                                    ->with('chapter')
                                    ->get();

                                if ($chapters->isEmpty()) {
                                    return 'No chapter progress yet.';
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($chapters as $progress) {
                                    $chapter = $progress->chapter;
                                    $status = $progress->status;
                                    $statusColor = $status === 'completed' ? 'green' : ($status === 'in_progress' ? 'yellow' : 'gray');

                                    $chapterSlides = Slide::where('chapter_id', $chapter->id)->count();
                                    $completedSlides = SlideProgress::where('user_id', $student->id)
                                        ->where('chapter_id', $chapter->id)
                                        ->where('completed', true)
                                        ->count();

                                    $html .= "<div class='flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg'>";
                                    $html .= "<span class='font-medium'>{$chapter->title}</span>";
                                    $html .= "<div class='flex gap-2'>";
                                    $html .= "<span class='px-2 py-1 text-xs rounded bg-{$statusColor}-100 text-{$statusColor}-700 dark:bg-{$statusColor}-900 dark:text-{$statusColor}-300'>" . ucfirst($status) . "</span>";
                                    $html .= "<span class='px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'>{$completedSlides}/{$chapterSlides} slides</span>";
                                    $html .= "</div>";
                                    $html .= "</div>";
                                }
                                $html .= '</div>';

                                return $html;
                            })
                            ->html(),
                    ]),

                Section::make('Recent Quiz Attempts')
                    ->schema([
                        TextEntry::make('quiz_attempts')
                            ->label('')
                            ->default(function () use ($student) {
                                $attempts = QuizResult::where('user_id', $student->id)
                                    ->with('quiz')
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();

                                if ($attempts->isEmpty()) {
                                    return 'No quiz attempts yet.';
                                }

                                $html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
                                $html .= '<thead class="bg-gray-50 dark:bg-gray-800"><tr>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Quiz</th>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Category</th>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Attempt</th>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Score</th>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Time</th>';
                                $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Date</th>';
                                $html .= '</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">';

                                foreach ($attempts as $attempt) {
                                    $statusColor = $attempt->passed ? 'green' : 'red';
                                    $statusText = $attempt->passed ? 'PASSED' : 'FAILED';

                                    $html .= '<tr class="bg-white dark:bg-gray-900">';
                                    $html .= "<td class='px-3 py-2 text-sm'>{$attempt->quiz->title}</td>";
                                    $html .= "<td class='px-3 py-2 text-sm'>" . ucfirst($attempt->quiz->category) . "</td>";
                                    $html .= "<td class='px-3 py-2 text-sm'>#{$attempt->attempt_number}</td>";
                                    $html .= "<td class='px-3 py-2 text-sm'>{$attempt->score}/{$attempt->total_questions} (" . round($attempt->percentage, 1) . "%)</td>";
                                    $html .= "<td class='px-3 py-2 text-sm'><span class='px-2 py-1 text-xs rounded bg-{$statusColor}-100 text-{$statusColor}-700 dark:bg-{$statusColor}-900 dark:text-{$statusColor}-300'>{$statusText}</span></td>";
                                    $html .= "<td class='px-3 py-2 text-sm'>" . round($attempt->time_taken / 60, 1) . " min</td>";
                                    $html .= "<td class='px-3 py-2 text-sm'>{$attempt->created_at->format('M d, Y H:i')}</td>";
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody></table></div>';

                                return $html;
                            })
                            ->html(),
                    ]),
            ]);
    }

    protected function downloadReport(string $period)
    {
        $student = $this->record;
        $controller = app(\App\Http\Controllers\Api\AdminStudentPerformanceController::class);

        $request = request();
        $request->merge(['period' => $period]);

        $response = $controller->exportToExcel($request, $student->id);

        return $response;
    }

    protected function sendEmail(array $data)
    {
        $student = $this->record;
        $controller = app(\App\Http\Controllers\Api\AdminStudentPerformanceController::class);

        $request = request();
        $request->merge([
            'period' => $data['period'],
            'recipient_email' => $data['recipient_email'],
            'message' => $data['message'] ?? null,
        ]);

        $result = $controller->emailReport($request, $student->id);

        \Filament\Notifications\Notification::make()
            ->title('Email Sent Successfully')
            ->success()
            ->body("Report sent to {$data['recipient_email']}")
            ->send();

        return $result;
    }
}
