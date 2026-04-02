@extends('layouts.dashboard')
@section('title', 'Tickets')
@section('page-title', 'Support Tickets')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">My Tickets</h2>
    <a href="{{ route('dashboard.tickets.create') }}" class="btn btn-primary">+ New Ticket</a>
</div>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-border"><tr class="text-muted"><th class="text-left p-4">Ticket #</th><th class="text-left p-4">Subject</th><th class="text-left p-4">Priority</th><th class="text-left p-4">Status</th><th class="text-left p-4">Date</th><th class="p-4"></th></tr></thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr class="border-b border-border last:border-0">
                <td class="p-4 font-mono text-primary">{{ $ticket->ticket_number }}</td>
                <td class="p-4">{{ $ticket->subject }}</td>
                <td class="p-4"><span class="px-2 py-1 rounded text-xs font-medium bg-primary/10 text-primary">{{ ucfirst($ticket->priority) }}</span></td>
                <td class="p-4"><span class="px-2 py-1 rounded text-xs {{ $ticket->status === 'open' ? 'bg-green-900/30 text-green-400' : 'bg-gray-700 text-gray-400' }}">{{ ucfirst($ticket->status) }}</span></td>
                <td class="p-4 text-muted">{{ $ticket->created_at->format('M d, Y') }}</td>
                <td class="p-4"><a href="{{ route('dashboard.tickets.show', $ticket) }}" class="btn btn-outline text-xs">View</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-8 text-center text-muted">No tickets yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
