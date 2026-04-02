<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Message;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Support';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled(),
                Forms\Components\TextInput::make('ticket_number')->disabled(),
                Forms\Components\TextInput::make('subject')->disabled(),
                Forms\Components\Select::make('status')
                    ->options(['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed']),
                Forms\Components\Select::make('priority')
                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('user.name')->searchable()->label('Client'),
                Tables\Columns\TextColumn::make('subject')->searchable()->limit(40),
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors(['success' => 'low', 'primary' => 'normal', 'warning' => 'high', 'danger' => 'urgent']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'open', 'warning' => 'in_progress', 'secondary' => 'closed']),
                Tables\Columns\TextColumn::make('messages_count')->counts('messages')->label('Msgs'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed']),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\Textarea::make('body')->label('Reply Message')->required()->rows(4),
                    ])
                    ->action(function (Ticket $record, array $data) {
                        Message::create([
                            'ticket_id' => $record->id,
                            'user_id' => auth()->id(),
                            'body' => $data['body'],
                        ]);
                        if ($record->status === 'open') {
                            $record->update(['status' => 'in_progress']);
                        }
                        Notification::make()->title('Reply sent.')->success()->send();
                    })
                    ->visible(fn (Ticket $record) => $record->status !== 'closed'),
                Tables\Actions\Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Ticket $record) => $record->update(['status' => 'closed']))
                    ->visible(fn (Ticket $record) => $record->status !== 'closed'),
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
            'index' => Pages\ListTickets::route('/'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
