<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->upperCase()
                    ->maxLength(50),
                Forms\Components\Select::make('type')
                    ->options(['percent' => 'Percent (%)', 'fixed' => 'Fixed ($)'])
                    ->required()
                    ->default('percent'),
                Forms\Components\TextInput::make('value')->numeric()->required(),
                Forms\Components\TextInput::make('min_order')->numeric()->prefix('$')->default(0),
                Forms\Components\TextInput::make('max_uses')->numeric()->placeholder('Unlimited'),
                Forms\Components\DateTimePicker::make('expires_at')->label('Expiry Date'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable()->weight('bold'),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors(['primary' => 'percent', 'success' => 'fixed']),
                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn ($record) => $record->type === 'percent' ? $record->value . '%' : '$' . $record->value),
                Tables\Columns\TextColumn::make('used_count')->label('Used'),
                Tables\Columns\TextColumn::make('max_uses')->label('Max')->default('∞'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->placeholder('Never'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
