<?php

namespace App\Filament\Pages;

use App\Models\Chapter;
use App\Models\Quiz;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ImportQuiz extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.import-quiz';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Import Quiz';

    protected static ?string $navigationLabel = 'Import Quiz';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Import Quiz for Existing Chapter')
                    ->description('Import a quiz and attach it to an existing chapter. Use this when you\'ve already imported a chapter without a quiz.')
                    ->schema([
                        Select::make('chapter_id')
                            ->label('Select Chapter')
                            ->options(Chapter::orderBy('chapter_number')->pluck('title', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Choose the chapter this quiz belongs to')
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    $chapter = Chapter::find($state);
                                    if ($chapter && $chapter->quizzes()->exists()) {
                                        Notification::make()
                                            ->warning()
                                            ->title('Chapter Already Has Quiz')
                                            ->body("Chapter \"{$chapter->title}\" already has {$chapter->quizzes()->count()} quiz(zes). Importing will add another quiz.")
                                            ->persistent()
                                            ->send();
                                    }
                                }
                            }),

                        Textarea::make('quiz_data')
                            ->label('Quiz JSON')
                            ->placeholder($this->getQuizJsonExample())
                            ->required()
                            ->rows(15)
                            ->helperText('Paste the JSON containing quiz information and questions')
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
            // Decode JSON input
            $quizData = json_decode($data['quiz_data'], true);

            // Validate JSON decoding
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
            }

            if (!$quizData) {
                throw new \Exception('Quiz data is required and must be valid JSON.');
            }

            // Validate required fields
            $this->validateQuizData($quizData);

            // Get the selected chapter
            $chapter = Chapter::findOrFail($data['chapter_id']);

            // Import in a database transaction
            DB::transaction(function () use ($quizData, $chapter) {
                $quiz = Quiz::create([
                    'chapter_id' => $chapter->id,
                    'category' => $quizData['category'] ?? 'chapter',
                    'title' => $quizData['title'],
                    'description' => $quizData['description'] ?? '',
                    'questions' => $quizData['questions'],
                    'passing_score' => $quizData['passing_score'] ?? 70,
                    'is_active' => $quizData['is_active'] ?? true,
                ]);

                $this->importedQuiz = $quiz;
            });

            // Success notification
            $questionsCount = count($this->importedQuiz->questions);

            Notification::make()
                ->title('Quiz Imported Successfully!')
                ->success()
                ->body("Quiz \"{$this->importedQuiz->title}\" with {$questionsCount} question(s) has been imported for chapter \"{$chapter->title}\".")
                ->send();

            // Reset form
            $this->form->fill();

            // Redirect to quizzes list
            redirect()->route('filament.admin.resources.quizzes.index');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->danger()
                ->body($e->getMessage())
                ->persistent()
                ->send();
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

    protected function getQuizJsonExample(): string
    {
        return json_encode([
            'title' => 'Chapter Quiz',
            'description' => 'Test your knowledge',
            'category' => 'chapter',
            'passing_score' => 70,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'What does ICT stand for?',
                    'options' => [
                        'A. Internet Communication Technology',
                        'B. Information and Communication Technology',
                        'C. Integrated Computer Technology',
                        'D. International Computing Technology'
                    ],
                    'correct_answer' => 'B',
                    'explanation' => 'ICT stands for Information and Communication Technology'
                ],
                [
                    'question' => 'Which is a component of ICT?',
                    'options' => [
                        'A. Hardware',
                        'B. Books',
                        'C. Paper',
                        'D. Pencils'
                    ],
                    'correct_answer' => 'A',
                    'explanation' => 'Hardware is a key component of ICT'
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('import')
                ->label('Import Quiz')
                ->icon('heroicon-o-clipboard-document-check')
                ->submit('import'),
        ];
    }

    protected $importedQuiz = null;
}
