@extends('layouts.dashboard')
@section('title', 'Wallet')
@section('page-title', 'My Wallet')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="card p-8 text-center lg:col-span-1">
        <p class="text-muted text-sm mb-2">Available Balance</p>
        <div class="text-4xl font-extrabold text-primary">${{ number_format($balance, 2) }}</div>
        <p class="text-muted text-xs mt-2">USD</p>
    </div>
    <div class="card p-8 lg:col-span-2">
        <h3 class="font-semibold mb-4">Add Funds</h3>
        <form action="{{ route('dashboard.wallet.deposit') }}" method="POST" class="flex gap-4">
            @csrf
            <input type="number" name="amount" class="input flex-1" placeholder="Amount (min $5)" min="5" max="10000" step="0.01" required>
            <button type="submit" class="btn btn-primary">Deposit</button>
        </form>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="p-6 border-b border-border font-semibold">Transaction History</div>
    <table class="w-full text-sm">
        <thead class="border-b border-border">
            <tr class="text-muted"><th class="text-left p-4">Type</th><th class="text-left p-4">Amount</th><th class="text-left p-4">Description</th><th class="text-left p-4">Date</th></tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr class="border-b border-border last:border-0">
                <td class="p-4"><span class="px-2 py-1 rounded text-xs font-medium {{ $tx->type === 'deposit' || $tx->type === 'refund' ? 'bg-green-900/30 text-green-400' : 'bg-red-900/30 text-red-400' }}">{{ ucfirst($tx->type) }}</span></td>
                <td class="p-4 font-semibold {{ $tx->type === 'deposit' || $tx->type === 'refund' ? 'text-green-400' : 'text-red-400' }}">${{ number_format($tx->amount, 2) }}</td>
                <td class="p-4 text-muted">{{ $tx->description ?? '—' }}</td>
                <td class="p-4 text-muted">{{ $tx->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-8 text-center text-muted">No transactions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
