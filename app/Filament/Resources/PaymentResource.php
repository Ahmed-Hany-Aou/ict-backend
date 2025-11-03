<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payment Approvals';

    protected static ?string $navigationGroup = 'Monetization';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\Placeholder::make('user_email')
                            ->label('Email')
                            ->content(fn ($record) => $record?->user?->email ?? 'N/A')
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Instapay Reference')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('EGP')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\FileUpload::make('screenshot_path')
                            ->label('Payment Screenshot')
                            ->disk('public')
                            ->directory('payment_screenshots')
                            ->image()
                            ->maxSize(10240)
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\ToggleButtons::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approve',
                                'rejected' => 'Reject',
                            ])
                            ->icons([
                                'pending' => 'heroicon-o-clock',
                                'approved' => 'heroicon-o-check-circle',
                                'rejected' => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            ])
                            ->inline()
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('approved_by')
                            ->label('Approved By')
                            ->content(fn ($record) => $record?->approver?->name ?? 'N/A'),

                        Forms\Components\Placeholder::make('approved_at')
                            ->label('Approved At')
                            ->content(fn ($record) => $record?->approved_at?->format('Y-m-d H:i:s') ?? 'N/A'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('payment_reference')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('screenshot_path')
                    ->label('Screenshot')
                    ->square()
                    ->height(50),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->isPending())
                    ->action(function (Payment $record) {
                        $record->approve(auth()->id(), 30);

                        Notification::make()
                            ->title('Payment Approved')
                            ->success()
                            ->body('User has been granted premium access for 30 days.')
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->reject(auth()->id(), $data['admin_notes']);

                        Notification::make()
                            ->title('Payment Rejected')
                            ->danger()
                            ->body('Payment has been rejected.')
                            ->send();
                    }),
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
            'index' => Pages\ListPayments::route('/'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Payments are only created via API from frontend
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
