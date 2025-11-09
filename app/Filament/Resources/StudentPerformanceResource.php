<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentPerformanceResource\Pages;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\SlideProgress;
use App\Models\QuizResult;
use App\Models\Chapter;
use App\Models\Slide;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class StudentPerformanceResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Student Performance';

    protected static ?string $modelLabel = 'Student Performance';

    protected static ?string $pluralModelLabel = 'Student Performance';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'student');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('grade')
                            ->disabled(),
                        Forms\Components\Toggle::make('is_premium')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Student Name'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('is_premium')
                    ->label('Premium')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Premium' : 'Free')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade')
                    ->options([
                        '7' => 'Grade 7',
                        '8' => 'Grade 8',
                        '9' => 'Grade 9',
                        '10' => 'Grade 10',
                        '11' => 'Grade 11',
                        '12' => 'Grade 12',
                    ]),

                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium Status')
                    ->placeholder('All students')
                    ->trueLabel('Premium only')
                    ->falseLabel('Free only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Removed delete action as we don't want to delete students from performance view
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentPerformances::route('/'),
            'view' => Pages\ViewStudentPerformance::route('/{record}'),
        ];
    }
}
