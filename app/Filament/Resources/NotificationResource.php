<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'system' => 'System',
                        'personal' => 'Personal',
                        'broadcast' => 'Broadcast',
                    ])
                    ->required()
                    ->default('system'),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(3),

                Forms\Components\Select::make('sent_to')
                    ->options([
                        'all' => 'All Users',
                        'selected' => 'Selected Users',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('sent_to_users')
                    ->multiple()
                    ->searchable()
                    ->options(User::where('role', 'student')->pluck('email', 'id'))
                    ->visible(fn ($get) => $get('sent_to') === 'selected'),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'system',
                        'success' => 'broadcast',
                        'warning' => 'personal',
                    ]),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('sent_to')
                    ->badge()
                    ->colors([
                        'success' => 'all',
                        'warning' => 'selected',
                    ]),
                Tables\Columns\TextColumn::make('userNotifications_count')
                    ->counts('userNotifications')
                    ->label('Sent To'),
                Tables\Columns\TextColumn::make('read_count')
                    ->label('Read By')
                    ->getStateUsing(function ($record) {
                        return $record->userNotifications()->where('is_read', true)->count();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'system' => 'System',
                        'personal' => 'Personal',
                        'broadcast' => 'Broadcast',
                    ]),
                Tables\Filters\SelectFilter::make('sent_to')
                    ->options([
                        'all' => 'All Users',
                        'selected' => 'Selected Users',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
