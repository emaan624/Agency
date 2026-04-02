<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static string  $view            = 'filament.pages.settings-page';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?int    $navigationSort  = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // General
            'site_name'    => Setting::get('site_name',    'LuxMotion Agency'),
            'site_tagline' => Setting::get('site_tagline', 'Premium Creative Services'),
            'site_email'   => Setting::get('site_email',   ''),
            'site_phone'   => Setting::get('site_phone',   ''),
            'site_address' => Setting::get('site_address', ''),

            // Appearance
            'theme'        => Setting::get('theme',        'dark'),
            'hero_title'   => Setting::get('hero_title',   ''),
            'hero_subtitle'=> Setting::get('hero_subtitle',''),

            // Global payment
            'currency'       => Setting::get('currency',       'USD'),
            'wallet_enabled' => (bool) Setting::get('wallet_enabled', '1'),

            // Stripe
            'stripe_enabled'        => (bool) Setting::get('stripe_enabled', '1'),
            'stripe_key'            => Setting::get('stripe_key',            ''),
            'stripe_secret'         => Setting::get('stripe_secret',         ''),
            'stripe_webhook_secret' => Setting::get('stripe_webhook_secret', ''),

            // PayPal
            'paypal_enabled'       => (bool) Setting::get('paypal_enabled',       '0'),
            'paypal_client_id'     => Setting::get('paypal_client_id',     ''),
            'paypal_client_secret' => Setting::get('paypal_client_secret', ''),
            'paypal_mode'          => Setting::get('paypal_mode',          'sandbox'),

            // Crypto
            'crypto_enabled'         => (bool) Setting::get('crypto_enabled',         '0'),
            'nowpayments_api_key'    => Setting::get('nowpayments_api_key',    ''),
            'nowpayments_ipn_secret' => Setting::get('nowpayments_ipn_secret', ''),
            'crypto_accepted_coins'  => Setting::get('crypto_accepted_coins',  'BTC,ETH,USDT'),

            // Referral & KYC
            'referral_commission_percent' => Setting::get('referral_commission_percent', '5'),
            'kyc_required'                => (bool) Setting::get('kyc_required', '0'),

            // Maintenance
            'maintenance_mode' => (bool) Setting::get('maintenance_mode', '0'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            // ── General ───────────────────────────────────────────────────
            Section::make('General')
                ->icon('heroicon-o-building-office')
                ->schema([
                    TextInput::make('site_name')->required()->label('Site Name'),
                    TextInput::make('site_tagline')->label('Tagline'),
                    TextInput::make('site_email')->email()->label('Contact Email'),
                    TextInput::make('site_phone')->tel()->label('Phone'),
                    TextInput::make('site_address')->label('Address'),
                ])->columns(2),

            // ── Appearance ────────────────────────────────────────────────
            Section::make('Appearance')
                ->icon('heroicon-o-paint-brush')
                ->schema([
                    Select::make('theme')
                        ->options(['dark' => 'Dark', 'luxury' => 'Luxury (Gold)', 'neon' => 'Neon'])
                        ->default('dark')
                        ->label('Color Theme'),
                    TextInput::make('hero_title')->label('Hero Title'),
                    TextInput::make('hero_subtitle')->label('Hero Subtitle'),
                ])->columns(1),

            // ── Global Payment ────────────────────────────────────────────
            Section::make('Payment — General')
                ->icon('heroicon-o-credit-card')
                ->description('Choose the default currency and whether the wallet feature is active.')
                ->schema([
                    Select::make('currency')
                        ->options(['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)'])
                        ->default('USD')
                        ->label('Default Currency'),
                    Toggle::make('wallet_enabled')->label('Enable Wallet Payments'),
                ])->columns(2),

            // ── Stripe ────────────────────────────────────────────────────
            Section::make('Stripe — Fiat Card Payments')
                ->icon('heroicon-o-banknotes')
                ->description('Accept card payments via Stripe Checkout. Keys stored here override environment variables.')
                ->schema([
                    Toggle::make('stripe_enabled')
                        ->label('Enable Stripe')
                        ->columnSpanFull(),
                    TextInput::make('stripe_key')
                        ->label('Publishable Key')
                        ->placeholder('pk_live_…')
                        ->helperText('Starts with pk_test_ or pk_live_'),
                    TextInput::make('stripe_secret')
                        ->label('Secret Key')
                        ->password()
                        ->revealable()
                        ->placeholder('sk_live_…')
                        ->helperText('Never share this. Starts with sk_test_ or sk_live_'),
                    TextInput::make('stripe_webhook_secret')
                        ->label('Webhook Signing Secret')
                        ->password()
                        ->revealable()
                        ->placeholder('whsec_…')
                        ->helperText('Found in your Stripe dashboard → Webhooks'),
                ])->columns(2),

            // ── PayPal ────────────────────────────────────────────────────
            Section::make('PayPal — Fiat Payments')
                ->icon('heroicon-o-globe-alt')
                ->description('Accept payments via PayPal. Use the PayPal Developer Dashboard to obtain credentials.')
                ->schema([
                    Toggle::make('paypal_enabled')
                        ->label('Enable PayPal')
                        ->columnSpanFull(),
                    TextInput::make('paypal_client_id')
                        ->label('Client ID')
                        ->placeholder('AYS…')
                        ->helperText('From your PayPal app credentials'),
                    TextInput::make('paypal_client_secret')
                        ->label('Client Secret')
                        ->password()
                        ->revealable()
                        ->placeholder('EK0…')
                        ->helperText('From your PayPal app credentials'),
                    Select::make('paypal_mode')
                        ->label('Mode')
                        ->options(['sandbox' => 'Sandbox (testing)', 'live' => 'Live (production)'])
                        ->default('sandbox')
                        ->helperText('Switch to "Live" only when ready for real transactions'),
                ])->columns(2),

            // ── Crypto ────────────────────────────────────────────────────
            Section::make('Crypto — NowPayments Gateway')
                ->icon('heroicon-o-currency-dollar')
                ->description('Accept cryptocurrency payments via NowPayments.io. Register at nowpayments.io to get your API key.')
                ->schema([
                    Toggle::make('crypto_enabled')
                        ->label('Enable Crypto Payments')
                        ->columnSpanFull(),
                    TextInput::make('nowpayments_api_key')
                        ->label('NowPayments API Key')
                        ->password()
                        ->revealable()
                        ->placeholder('Your NowPayments API Key'),
                    TextInput::make('nowpayments_ipn_secret')
                        ->label('IPN Secret')
                        ->password()
                        ->revealable()
                        ->placeholder('Your IPN notification secret')
                        ->helperText('Used to verify incoming payment notifications'),
                    TextInput::make('crypto_accepted_coins')
                        ->label('Accepted Coins')
                        ->placeholder('BTC,ETH,USDT,LTC,BNB')
                        ->helperText('Comma-separated list of coin symbols (first entry is default)'),
                ])->columns(2),

            // ── Referral & KYC ────────────────────────────────────────────
            Section::make('Referral & KYC')
                ->icon('heroicon-o-users')
                ->schema([
                    TextInput::make('referral_commission_percent')
                        ->numeric()
                        ->suffix('%')
                        ->label('Referral Commission'),
                    Toggle::make('kyc_required')->label('Require KYC for Orders'),
                ])->columns(2),

            // ── Maintenance ───────────────────────────────────────────────
            Section::make('Maintenance')
                ->icon('heroicon-o-wrench-screwdriver')
                ->schema([
                    Toggle::make('maintenance_mode')->label('Maintenance Mode'),
                ]),

        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $groupMap = [
            'site_name'    => 'general', 'site_tagline' => 'general',
            'site_email'   => 'general', 'site_phone'   => 'general',
            'site_address' => 'general', 'maintenance_mode' => 'general',

            'theme' => 'appearance', 'hero_title' => 'appearance', 'hero_subtitle' => 'appearance',

            'currency'       => 'payment', 'wallet_enabled' => 'payment',

            'stripe_enabled' => 'payment', 'stripe_key' => 'payment',
            'stripe_secret'  => 'payment', 'stripe_webhook_secret' => 'payment',

            'paypal_enabled'       => 'payment', 'paypal_client_id'     => 'payment',
            'paypal_client_secret' => 'payment', 'paypal_mode'          => 'payment',

            'crypto_enabled'         => 'payment', 'nowpayments_api_key'    => 'payment',
            'nowpayments_ipn_secret' => 'payment', 'crypto_accepted_coins'  => 'payment',

            'referral_commission_percent' => 'referral',
            'kyc_required'                => 'kyc',
        ];

        foreach ($data as $key => $value) {
            $group = $groupMap[$key] ?? 'general';
            Setting::set($key, is_bool($value) ? (int) $value : (string) $value, $group);
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
