@extends('layouts.dashboard')
@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order Details')
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="card p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold">{{ $order->order_number }}</h2>
                <p class="text-muted text-sm mt-1">Placed {{ $order->created_at->format('M d, Y') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-primary/10 text-primary">{{ ucfirst($order->status) }}</span>
        </div>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-muted">Package</dt><dd class="font-medium mt-1">{{ $order->package?->name ?? 'Custom' }}</dd></div>
            <div><dt class="text-muted">Payment</dt><dd class="font-medium mt-1">{{ ucfirst($order->payment_status) }}</dd></div>
            <div><dt class="text-muted">Subtotal</dt><dd class="font-medium mt-1">${{ number_format($order->subtotal, 2) }}</dd></div>
            <div><dt class="text-muted">Discount</dt><dd class="font-medium mt-1">-${{ number_format($order->discount, 2) }}</dd></div>
            <div><dt class="text-muted">Total</dt><dd class="font-bold text-primary text-lg mt-1">${{ number_format($order->total, 2) }}</dd></div>
            <div><dt class="text-muted">Due Date</dt><dd class="font-medium mt-1">{{ $order->due_at?->format('M d, Y') ?? 'TBD' }}</dd></div>
        </dl>
        @if($order->requirements)
            <div class="mt-6 pt-6 border-t border-border">
                <p class="text-sm text-muted mb-2">Requirements</p>
                <p class="text-sm">{{ $order->requirements }}</p>
            </div>
        @endif
    </div>

    @if(in_array($order->status, ['in_progress', 'completed']))
    <div class="card p-6">
        <h3 class="font-semibold mb-4">Request Revision</h3>
        <form action="{{ route('dashboard.orders.revision', $order) }}" method="POST" class="space-y-4">
            @csrf
            <textarea name="notes" rows="3" class="input w-full" placeholder="Describe the changes needed..." required></textarea>
            <button type="submit" class="btn btn-outline">Submit Revision Request</button>
        </form>
    </div>
    @endif
</div>
@endsection
