<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycResource\Pages;
use App\Models\KycVerification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KycResource extends Resource
{
    protected static ?string $model = KycVerification::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'KYC Verifications';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled(),
            Forms\Components\Select::make('document_type')
                ->options(['passport' => 'Passport', 'id_card' => 'ID Card', 'driver_license' => "Driver's License"])
                ->disabled(),
            Forms\Components\Select::make('status')
                ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            Forms\Components\Textarea::make('notes')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->label('User'),
                Tables\Columns\TextColumn::make('document_type')->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
                Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewed By'),
                Tables\Columns\TextColumn::make('reviewed_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (KycVerification $record) {
                        $record->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
                        $record->user->update(['kyc_status' => 'approved']);
                        Notification::make()->title('KYC Approved.')->success()->send();
                    })
                    ->visible(fn (KycVerification $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('notes')->label('Rejection Reason')->required(),
                    ])
                    ->action(function (KycVerification $record, array $data) {
                        $record->update(['status' => 'rejected', 'notes' => $data['notes'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
                        $record->user->update(['kyc_status' => 'rejected']);
                        Notification::make()->title('KYC Rejected.')->warning()->send();
                    })
                    ->visible(fn (KycVerification $record) => $record->status === 'pending'),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKycs::route('/'),
            'edit' => Pages\EditKyc::route('/{record}/edit'),
        ];
    }
}
