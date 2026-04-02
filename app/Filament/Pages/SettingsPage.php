<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings-page';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name'                   => Setting::get('site_name', 'LuxMotion Agency'),
            'site_tagline'                => Setting::get('site_tagline', 'Premium Creative Services'),
            'site_email'                  => Setting::get('site_email', ''),
            'site_phone'                  => Setting::get('site_phone', ''),
            'site_address'                => Setting::get('site_address', ''),
            'currency'                    => Setting::get('currency', 'USD'),
            'stripe_enabled'              => (bool) Setting::get('stripe_enabled', '1'),
            'wallet_enabled'              => (bool) Setting::get('wallet_enabled', '1'),
            'referral_commission_percent' => Setting::get('referral_commission_percent', '5'),
            'kyc_required'                => (bool) Setting::get('kyc_required', '0'),
            'maintenance_mode'            => (bool) Setting::get('maintenance_mode', '0'),
            'theme'                       => Setting::get('theme', 'dark'),
            'hero_title'                  => Setting::get('hero_title', ''),
            'hero_subtitle'               => Setting::get('hero_subtitle', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('General')->schema([
                TextInput::make('site_name')->required(),
                TextInput::make('site_tagline'),
                TextInput::make('site_email')->email(),
                TextInput::make('site_phone'),
                TextInput::make('site_address'),
            ])->columns(2),

            Section::make('Appearance')->schema([
                Select::make('theme')->options(['dark' => 'Dark', 'luxury' => 'Luxury (Gold)', 'neon' => 'Neon'])->default('dark'),
                TextInput::make('hero_title'),
                TextInput::make('hero_subtitle'),
            ])->columns(1),

            Section::make('Payment')->schema([
                Select::make('currency')->options(['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'])->default('USD'),
                Toggle::make('stripe_enabled')->label('Enable Stripe Payments'),
                Toggle::make('wallet_enabled')->label('Enable Wallet Payments'),
            ])->columns(3),

            Section::make('Referral & KYC')->schema([
                TextInput::make('referral_commission_percent')->numeric()->suffix('%'),
                Toggle::make('kyc_required')->label('Require KYC for Orders'),
            ])->columns(2),

            Section::make('Maintenance')->schema([
                Toggle::make('maintenance_mode')->label('Maintenance Mode'),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            $group = match(true) {
                in_array($key, ['site_name','site_tagline','site_email','site_phone','site_address','maintenance_mode']) => 'general',
                in_array($key, ['theme','hero_title','hero_subtitle']) => 'appearance',
                in_array($key, ['currency','stripe_enabled','wallet_enabled']) => 'payment',
                in_array($key, ['referral_commission_percent']) => 'referral',
                in_array($key, ['kyc_required']) => 'kyc',
                default => 'general',
            };
            Setting::set($key, is_bool($value) ? (int) $value : $value, $group);
        }

        Notification::make()->title('Settings saved successfully.')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Save Settings')->submit('save'),
        ];
    }
}
