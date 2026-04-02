@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach([
        ['label' => 'Total Orders',    'value' => $stats['orders_total'],   'icon' => '📦'],
        ['label' => 'Active Orders',   'value' => $stats['orders_active'],  'icon' => '⏳'],
        ['label' => 'Completed',       'value' => $stats['orders_done'],    'icon' => '✅'],
        ['label' => 'Wallet Balance',  'value' => '$' . number_format($stats['wallet_balance'], 2), 'icon' => '💰'],
        ['label' => 'Open Tickets',    'value' => $stats['tickets_open'],   'icon' => '🎫'],
        ['label' => 'Unread Alerts',   'value' => $stats['notifications'],  'icon' => '🔔'],
    ] as $card)
    <div class="card p-6 flex items-center gap-4">
        <div class="text-4xl">{{ $card['icon'] }}</div>
        <div>
            <div class="text-2xl font-bold text-primary">{{ $card['value'] }}</div>
            <div class="text-sm text-muted">{{ $card['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>
@endsection
