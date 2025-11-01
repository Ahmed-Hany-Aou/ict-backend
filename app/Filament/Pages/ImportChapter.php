<?php

namespace App\Filament\Pages;

use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\Slide;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ImportChapter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.pages.import-chapter';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Import Chapter';

    protected static ?string $navigationLabel = 'Import Chapter';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Import Chapter with Slides')
                    ->description('Import a chapter with slides, and optionally a quiz. To import only a quiz for an existing chapter, use "Import Quiz" instead.')
                    ->schema([
                        Textarea::make('chapter_data')
                            ->label('Chapter & Slides JSON')
                            ->placeholder($this->getChapterJsonExample())
                            ->required()
                            ->rows(12)
                            ->helperText('Paste the JSON containing chapter information and slides array')
                            ->columnSpanFull(),

                        Textarea::make('quiz_data')
                            ->label('Quiz JSON (Optional)')
                            ->placeholder($this->getQuizJsonExample())
                            ->rows(12)
                            ->helperText('Optional: Paste the JSON containing quiz information and questions. Leave empty to import chapter without quiz. You can add quiz later using "Import Quiz".')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        try {
            // Decode JSON inputs
            $chapterData = json_decode($data['chapter_data'], true);

            // Quiz data is optional
            $quizData = null;
            if (!empty($data['quiz_data'])) {
                $quizData = json_decode($data['quiz_data'], true);

                // Validate quiz JSON if provided
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid Quiz JSON format: ' . json_last_error_msg());
                }
            }

            // Validate chapter JSON
            if (!$chapterData) {
                throw new \Exception('Chapter data is required and must be valid JSON.');
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid Chapter JSON format: ' . json_last_error_msg());
            }

            // Validate required fields in chapter data
            $this->validateChapterData($chapterData);

            // Only validate quiz if provided
            if ($quizData) {
                $this->validateQuizData($quizData);
            }

            // Import in a database transaction
            DB::transaction(function () use ($chapterData, $quizData) {
                // Create Chapter
                $chapter = Chapter::create([
                    'title' => $chapterData['title'],
                    'description' => $chapterData['description'],
                    'chapter_number' => $chapterData['chapter_number'],
                    'content' => $chapterData['content'] ?? null,
                    'video_url' => $chapterData['video_url'] ?? null,
                    'video_type' => $chapterData['video_type'] ?? 'none',
                    'meeting_link' => $chapterData['meeting_link'] ?? null,
                    'meeting_datetime' => $chapterData['meeting_datetime'] ?? null,
                    'is_published' => $chapterData['is_published'] ?? true,
                    'is_premium' => $chapterData['is_premium'] ?? false,
                ]);

                // Create Slides
                if (isset($chapterData['slides']) && is_array($chapterData['slides'])) {
                    foreach ($chapterData['slides'] as $slideData) {
                        Slide::create([
                            'chapter_id' => $chapter->id,
                            'slide_number' => $slideData['slide_number'],
                            'type' => $slideData['type'] ?? 'content',
                            'content' => $slideData['content'],
                            'meeting_link' => $slideData['meeting_link'] ?? null,
                            'video_url' => $slideData['video_url'] ?? null,
                        ]);
                    }
                }

                // Create Quiz (only if quiz data provided)
                if ($quizData) {
                    Quiz::create([
                        'chapter_id' => $chapter->id,
                        'category' => $quizData['category'] ?? 'chapter',
                        'title' => $quizData['title'],
                        'description' => $quizData['description'] ?? '',
                        'questions' => $quizData['questions'],
                        'passing_score' => $quizData['passing_score'] ?? 70,
                        'is_active' => $quizData['is_active'] ?? true,
                    ]);
                }

                $this->importedChapter = $chapter;
            });

            // Success notification
            $slidesCount = $this->importedChapter->slides()->count();
            $quizCount = $this->importedChapter->quizzes()->count();

            $message = "Chapter \"{$this->importedChapter->title}\" with {$slidesCount} slide(s)";
            if ($quizCount > 0) {
                $message .= " and quiz";
            }
            $message .= " has been imported successfully.";

            Notification::make()
                ->title('Chapter Imported Successfully!')
                ->success()
                ->body($message)
                ->send();

            // Reset form
            $this->form->fill();

            // Redirect to the chapters list
            redirect()->route('filament.admin.resources.chapters.index');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->danger()
                ->body($e->getMessage())
                ->persistent()
                ->send();
        }
    }

    protected function validateChapterData(array $data): void
    {
        $required = ['title', 'description', 'chapter_number', 'slides'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing required field in chapter data: {$field}");
            }
        }

        if (!is_array($data['slides']) || empty($data['slides'])) {
            throw new \Exception('Chapter must contain at least one slide in the slides array.');
        }

        // Validate each slide
        foreach ($data['slides'] as $index => $slide) {
            if (!isset($slide['slide_number']) || !isset($slide['content'])) {
                throw new \Exception("Slide at index {$index} is missing required fields (slide_number, content).");
            }
        }
    }

    protected function validateQuizData(array $data): void
    {
        $required = ['title', 'questions'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing required field in quiz data: {$field}");
            }
        }

        if (!is_array($data['questions']) || empty($data['questions'])) {
            throw new \Exception('Quiz must contain at least one question.');
        }

        // Validate each question
        foreach ($data['questions'] as $index => $question) {
            $requiredQuestionFields = ['question', 'options', 'correct_answer'];
            foreach ($requiredQuestionFields as $field) {
                if (!isset($question[$field])) {
                    throw new \Exception("Question at index {$index} is missing required field: {$field}");
                }
            }
        }
    }

    protected function getChapterJsonExample(): string
    {
        return json_encode([
            'title' => 'Chapter Title',
            'description' => 'Chapter description',
            'chapter_number' => 1,
            'video_type' => 'none',
            'is_published' => true,
            'is_premium' => false,
            'slides' => [
                [
                    'slide_number' => 1,
                    'type' => 'content',
                    'content' => [
                        'title' => 'Slide Title',
                        'body' => 'Slide content...'
                    ]
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }

    protected function getQuizJsonExample(): string
    {
        return json_encode([
            'title' => 'Quiz Title',
            'description' => 'Quiz description',
            'category' => 'chapter',
            'passing_score' => 70,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Question text?',
                    'options' => ['A. Option 1', 'B. Option 2', 'C. Option 3', 'D. Option 4'],
                    'correct_answer' => 'A'
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }

    protected $importedChapter = null;

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('import')
                ->label('Import Chapter')
                ->icon('heroicon-o-arrow-down-tray')
                ->submit('import'),
        ];
    }
}
