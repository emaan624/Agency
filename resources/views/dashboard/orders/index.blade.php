@extends('layouts.dashboard')
@section('title', 'My Orders')
@section('page-title', 'My Orders')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">All Orders</h2>
    <a href="{{ route('dashboard.orders.create') }}" class="btn btn-primary">+ New Order</a>
</div>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-border">
            <tr class="text-muted">
                <th class="text-left p-4">Order #</th>
                <th class="text-left p-4">Package</th>
                <th class="text-left p-4">Total</th>
                <th class="text-left p-4">Status</th>
                <th class="text-left p-4">Date</th>
                <th class="p-4"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr class="border-b border-border last:border-0">
                <td class="p-4 font-mono">{{ $order->order_number }}</td>
                <td class="p-4">{{ $order->package?->name ?? 'Custom' }}</td>
                <td class="p-4 text-primary font-semibold">${{ number_format($order->total, 2) }}</td>
                <td class="p-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ ucfirst($order->status) }}</span></td>
                <td class="p-4 text-muted">{{ $order->created_at->format('M d, Y') }}</td>
                <td class="p-4"><a href="{{ route('dashboard.orders.show', $order) }}" class="btn btn-outline text-xs">View</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-8 text-center text-muted">No orders yet. <a href="{{ route('dashboard.orders.create') }}" class="text-primary">Place your first order.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
