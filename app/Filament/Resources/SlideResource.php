<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlideResource\Pages;
use App\Filament\Resources\SlideResource\RelationManagers;
use App\Models\Slide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Slide Information')
                    ->schema([
                        Forms\Components\Select::make('chapter_id')
                            ->relationship('chapter', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Chapter'),

                        Forms\Components\TextInput::make('slide_number')
                            ->required()
                            ->numeric()
                            ->label('Slide Number')
                            ->helperText('Determines the order within the chapter'),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'title' => 'Title Slide',
                                'content' => 'Content Slide',
                                'quiz' => 'Quiz Slide',
                                'scenario' => 'Scenario Slide',
                                'review' => 'Review Slide',
                                'answers' => 'Answers Slide',
                                'completion' => 'Completion Slide',
                            ])
                            ->default('content')
                            ->reactive()
                            ->label('Slide Type')
                            ->helperText('Select the slide type to see relevant fields'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Slide Content')
                    ->schema([
                        // Common Fields
                        Forms\Components\TextInput::make('content.title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter slide title')
                            ->columnSpanFull(),

                        // Title Slide Fields
                        Forms\Components\TextInput::make('content.subtitle')
                            ->label('Subtitle')
                            ->maxLength(255)
                            ->placeholder('Enter subtitle')
                            ->visible(fn ($get) => $get('type') === 'title')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content.description')
                            ->label('Description')
                            ->rows(2)
                            ->placeholder('Enter description')
                            ->visible(fn ($get) => $get('type') === 'title')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content.footer')
                            ->label('Footer')
                            ->rows(2)
                            ->placeholder('Footer text (e.g., ICT Curriculum)')
                            ->visible(fn ($get) => $get('type') === 'title')
                            ->columnSpanFull(),

                        // Content Slide Fields
                        Forms\Components\Textarea::make('content.definition')
                            ->label('Definition')
                            ->rows(3)
                            ->placeholder('Main definition or explanation')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('content.keyPoint')
                            ->label('Key Point')
                            ->placeholder('Main takeaway point')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content.note')
                            ->label('Note')
                            ->rows(2)
                            ->placeholder('Additional note or tip')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.examples')
                            ->simple(
                                Forms\Components\TextInput::make('example')
                                    ->label('Example')
                                    ->placeholder('Enter an example')
                            )
                            ->addActionLabel('Add Example')
                            ->label('Examples')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.points')
                            ->simple(
                                Forms\Components\TextInput::make('point')
                                    ->label('Point')
                                    ->placeholder('Enter a point')
                            )
                            ->addActionLabel('Add Point')
                            ->label('Points')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.cards')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->label('Card Title'),
                                Forms\Components\Textarea::make('desc')
                                    ->required()
                                    ->label('Description')
                                    ->rows(2),
                                Forms\Components\TextInput::make('example')
                                    ->label('Example'),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Card')
                            ->addActionLabel('Add Card')
                            ->label('Cards')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.table')
                            ->schema([
                                Forms\Components\TextInput::make('type')
                                    ->required()
                                    ->label('Type'),
                                Forms\Components\Textarea::make('desc')
                                    ->required()
                                    ->label('Description')
                                    ->rows(2),
                                Forms\Components\TextInput::make('example')
                                    ->label('Example'),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? 'Row')
                            ->addActionLabel('Add Row')
                            ->label('Table Data')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.lifecycle')
                            ->schema([
                                Forms\Components\TextInput::make('step')
                                    ->required()
                                    ->label('Step'),
                                Forms\Components\Textarea::make('desc')
                                    ->required()
                                    ->label('Description')
                                    ->rows(2),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['step'] ?? 'Step')
                            ->addActionLabel('Add Step')
                            ->label('Lifecycle Steps')
                            ->visible(fn ($get) => $get('type') === 'content')
                            ->columnSpanFull(),

                        // Scenario Slide Fields
                        Forms\Components\Textarea::make('content.scenario')
                            ->label('Scenario')
                            ->rows(3)
                            ->placeholder('Describe the scenario')
                            ->visible(fn ($get) => $get('type') === 'scenario')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content.data')
                            ->label('Data')
                            ->rows(2)
                            ->placeholder('Raw data in the scenario')
                            ->visible(fn ($get) => $get('type') === 'scenario')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content.information')
                            ->label('Information')
                            ->rows(2)
                            ->placeholder('Processed information from data')
                            ->visible(fn ($get) => $get('type') === 'scenario')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content.knowledge')
                            ->label('Knowledge')
                            ->rows(2)
                            ->placeholder('Knowledge gained from information')
                            ->visible(fn ($get) => $get('type') === 'scenario')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.breakdown')
                            ->schema([
                                Forms\Components\TextInput::make('type')
                                    ->required()
                                    ->label('Type')
                                    ->placeholder('e.g., Privacy Action, Question'),
                                Forms\Components\Textarea::make('content')
                                    ->required()
                                    ->label('Content')
                                    ->rows(2),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? 'Item')
                            ->addActionLabel('Add Item')
                            ->label('Scenario Breakdown')
                            ->visible(fn ($get) => $get('type') === 'scenario')
                            ->columnSpanFull(),

                        // Quiz Slide Fields
                        Forms\Components\Repeater::make('content.questions')
                            ->schema([
                                Forms\Components\Textarea::make('q')
                                    ->required()
                                    ->label('Question')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('options')
                                    ->simple(
                                        Forms\Components\TextInput::make('option')
                                            ->required()
                                            ->label('Option')
                                    )
                                    ->label('Answer Options (Optional)')
                                    ->helperText('Leave empty for open-ended questions')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('answer')
                                    ->label('Correct Answer')
                                    ->helperText('For multiple choice questions'),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => substr($state['q'] ?? 'Question', 0, 50))
                            ->addActionLabel('Add Question')
                            ->label('Questions')
                            ->visible(fn ($get) => in_array($get('type'), ['quiz', 'review']))
                            ->columnSpanFull(),

                        // Answers Slide Fields
                        Forms\Components\Repeater::make('content.answers')
                            ->schema([
                                Forms\Components\TextInput::make('q')
                                    ->required()
                                    ->label('Question'),
                                Forms\Components\Textarea::make('a')
                                    ->required()
                                    ->label('Answer')
                                    ->rows(2),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['q'] ?? 'Answer')
                            ->addActionLabel('Add Answer')
                            ->label('Model Answers')
                            ->visible(fn ($get) => $get('type') === 'answers')
                            ->columnSpanFull(),

                        // Completion Slide Fields
                        Forms\Components\Textarea::make('content.message')
                            ->label('Completion Message')
                            ->rows(3)
                            ->placeholder('Congratulatory or summary message')
                            ->visible(fn ($get) => $get('type') === 'completion')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('content.nextSteps')
                            ->simple(
                                Forms\Components\TextInput::make('step')
                                    ->label('Next Step')
                                    ->placeholder('What to do next')
                            )
                            ->addActionLabel('Add Step')
                            ->label('Next Steps')
                            ->visible(fn ($get) => $get('type') === 'completion')
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('slide_number')
                    ->numeric()
                    ->sortable()
                    ->label('#')
                    ->badge(),

                Tables\Columns\TextColumn::make('content.title')
                    ->searchable()
                    ->label('Slide Title')
                    ->limit(50)
                    ->default('Untitled'),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'title',
                        'success' => 'content',
                        'warning' => 'quiz',
                        'info' => 'scenario',
                        'secondary' => 'review',
                        'danger' => 'answers',
                        'gray' => 'completion',
                    ])
                    ->label('Type'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('chapter')
                    ->relationship('chapter', 'title', fn ($query) => $query->orderBy('chapter_number')),
                

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'title' => 'Title',
                        'content' => 'Content',
                        'quiz' => 'Quiz',
                        'scenario' => 'Scenario',
                        'review' => 'Review',
                        'answers' => 'Answers',
                        'completion' => 'Completion',
                    ]),
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
            ->defaultSort('slide_number')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
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
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
