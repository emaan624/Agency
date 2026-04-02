<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Info')->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\Select::make('user_id')->label('Client')
                    ->relationship('user', 'name')->searchable()->required(),
                Forms\Components\Select::make('package_id')->label('Package')
                    ->relationship('package', 'name'),
                Forms\Components\Select::make('assigned_to')->label('Assigned To')
                    ->options(User::where('is_admin', true)->pluck('name', 'id'))
                    ->searchable(),
            ])->columns(2),

            Forms\Components\Section::make('Status & Payment')->schema([
                Forms\Components\Select::make('status')
                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'revision' => 'Revision', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                    ->required(),
                Forms\Components\Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded']),
                Forms\Components\Select::make('payment_method')
                    ->options(['stripe' => 'Stripe', 'wallet' => 'Wallet']),
                Forms\Components\DateTimePicker::make('due_at')->label('Due Date'),
            ])->columns(2),

            Forms\Components\Section::make('Financials')->schema([
                Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$'),
                Forms\Components\TextInput::make('discount')->numeric()->prefix('$'),
                Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled(),
            ])->columns(3),

            Forms\Components\Textarea::make('requirements')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('user.name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('package.name')->label('Package'),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assigned'),
                Tables\Columns\TextColumn::make('total')->money('usd')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'pending', 'primary' => 'in_progress', 'info' => 'revision', 'success' => 'completed', 'danger' => 'cancelled']),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors(['danger' => 'unpaid', 'success' => 'paid', 'warning' => 'refunded']),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'revision' => 'Revision', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('staff_id')
                            ->label('Assign to')
                            ->options(User::where('is_admin', true)->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        $staff = User::findOrFail($data['staff_id']);
                        app(OrderService::class)->assign($record, $staff);
                        Notification::make()->title('Order assigned successfully.')->success()->send();
                    })
                    ->visible(fn (Order $record) => in_array($record->status, ['pending', 'in_progress'])),
                Tables\Actions\Action::make('complete')
                    ->label('Mark Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(OrderService::class)->complete($record);
                        Notification::make()->title('Order marked as completed.')->success()->send();
                    })
                    ->visible(fn (Order $record) => in_array($record->status, ['in_progress', 'revision'])),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(OrderService::class)->cancel($record);
                        Notification::make()->title('Order cancelled.')->warning()->send();
                    })
                    ->visible(fn (Order $record) => !in_array($record->status, ['completed', 'cancelled'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
