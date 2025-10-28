<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChapterResource\Pages;
use App\Filament\Resources\ChapterResource\RelationManagers;
use App\Models\Chapter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Chapter Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Chapter Title')
                            ->placeholder('e.g., Introduction to ICT'),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->label('Description')
                            ->placeholder('Brief description of what students will learn')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('chapter_number')
                            ->required()
                            ->numeric()
                            ->label('Chapter Number')
                            ->helperText('Determines the display order'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Chapter Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Video & Meeting Settings')
                    ->schema([
                        Forms\Components\Select::make('video_type')
                            ->required()
                            ->default('none')
                            ->options([
                                'none' => 'No Video',
                                'recorded' => 'Recorded Video',
                                'scheduled' => 'Scheduled Meeting',
                            ])
                            ->reactive()
                            ->label('Video Type'),

                        Forms\Components\TextInput::make('video_url')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn ($get) => in_array($get('video_type'), ['recorded', 'scheduled']))
                            ->label('Video/Meeting URL'),

                        Forms\Components\DateTimePicker::make('meeting_datetime')
                            ->visible(fn ($get) => $get('video_type') === 'scheduled')
                            ->label('Meeting Date & Time'),

                        Forms\Components\TextInput::make('meeting_link')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('video_type') === 'scheduled')
                            ->label('Meeting Link'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publication Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->default(true)
                            ->label('Published')
                            ->helperText('Students can only see published chapters'),

                        Forms\Components\Toggle::make('is_premium')
                            ->default(false)
                            ->label('Premium Content')
                            ->helperText('Requires premium subscription to access'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('slides'))
            ->columns([
                Tables\Columns\TextColumn::make('chapter_number')
                    ->sortable()
                    ->label('#')
                    ->badge(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Title')
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('video_type')
                    ->colors([
                        'secondary' => 'none',
                        'success' => 'recorded',
                        'warning' => 'scheduled',
                    ])
                    ->label('Video'),

                Tables\Columns\TextColumn::make('slides_count')
                    ->counts('slides')
                    ->label('Slides')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),

                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean()
                    ->label('Premium'),

                Tables\Columns\TextColumn::make('meeting_datetime')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Meeting Time'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('video_type')
                    ->options([
                        'none' => 'No Video',
                        'recorded' => 'Recorded',
                        'scheduled' => 'Scheduled',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published Status'),

                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium Content'),
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
            ->defaultSort('chapter_number')
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
            'index' => Pages\ListChapters::route('/'),
            'create' => Pages\CreateChapter::route('/create'),
            'edit' => Pages\EditChapter::route('/{record}/edit'),
        ];
    }
}
