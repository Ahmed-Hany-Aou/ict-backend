<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quiz Information')
                    ->schema([
                        Forms\Components\Select::make('chapter_id')
                            ->relationship('chapter', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Chapter'),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Quiz Title')
                            ->placeholder('e.g., Chapter 1 Quiz'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'chapter' => 'Chapter Quiz',
                                'midterm' => 'Midterm Exam',
                                'final' => 'Final Exam',
                                'practice' => 'Practice Quiz',
                            ])
                            ->default('chapter')
                            ->label('Category'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->label('Description')
                            ->placeholder('Brief description of the quiz')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('passing_score')
                            ->required()
                            ->numeric()
                            ->default(70)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->label('Passing Score'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active')
                            ->helperText('Students can only take active quizzes')
                            ->reactive(),

                        Forms\Components\Toggle::make('is_premium')
                            ->default(false)
                            ->label('Premium Content'),

                        Forms\Components\DateTimePicker::make('publish_at')
                            ->label('Scheduled Publish Date')
                            ->helperText('Leave empty to publish immediately. Set a future date to schedule publishing.')
                            ->timezone('Africa/Cairo')
                            ->visible(fn ($get) => $get('is_active'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Quiz Questions')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->schema([
                                Forms\Components\Textarea::make('question')
                                    ->required()
                                    ->rows(2)
                                    ->label('Question')
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('options')
                                    ->simple(
                                        Forms\Components\TextInput::make('option')
                                            ->required()
                                            ->label('Option')
                                            ->placeholder('Enter option text')
                                    )
                                    ->minItems(2)
                                    ->maxItems(6)
                                    ->defaultItems(2)
                                    ->addActionLabel('Add Option')
                                    ->columnSpanFull()
                                    ->label('Answer Options'),

                                Forms\Components\TextInput::make('correct_answer')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->label('Correct Answer Index')
                                    ->helperText('Enter the index (0-based) of the correct option'),

                                Forms\Components\Textarea::make('explanation')
                                    ->rows(2)
                                    ->label('Explanation (Optional)')
                                    ->placeholder('Explain why this is the correct answer')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? 'New Question')
                            ->addActionLabel('Add Question')
                            ->columnSpanFull()
                            ->minItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('chapter'))
            ->columns([
                Tables\Columns\TextColumn::make('chapter.title')
                    ->searchable()
                    ->sortable()
                    ->label('Chapter')
                    ->limit(30),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Quiz Title')
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'chapter',
                        'warning' => 'midterm',
                        'danger' => 'final',
                        'success' => 'practice',
                    ])
                    ->label('Category'),

                Tables\Columns\TextColumn::make('questions_count')
                    ->getStateUsing(fn ($record) => count($record->questions ?? []))
                    ->label('Questions')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('passing_score')
                    ->suffix('%')
                    ->sortable()
                    ->label('Pass %'),

                Tables\Columns\BadgeColumn::make('publish_status')
                    ->label('Status')
                    ->getStateUsing(fn (Quiz $record) => $record->getPublishStatus())
                    ->colors([
                        'danger' => 'inactive',
                        'warning' => 'scheduled',
                        'success' => 'active',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'inactive',
                        'heroicon-o-clock' => 'scheduled',
                        'heroicon-o-check-circle' => 'active',
                    ]),

                Tables\Columns\TextColumn::make('publish_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Publish Date')
                    ->placeholder('Immediate')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean()
                    ->label('Premium'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'chapter' => 'Chapter Quiz',
                        'midterm' => 'Midterm',
                        'final' => 'Final',
                        'practice' => 'Practice',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\SelectFilter::make('chapter')
                    ->relationship('chapter', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
