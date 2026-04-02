@extends('layouts.dashboard')
@section('title', 'Ticket ' . $ticket->ticket_number)
@section('page-title', 'Ticket: ' . $ticket->subject)
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="space-y-4">
        @foreach($ticket->messages as $msg)
        <div class="card p-5 {{ $msg->user_id === auth()->id() ? 'ml-8' : 'mr-8' }}">
            <div class="flex items-center gap-3 mb-3">
                <span class="font-semibold text-sm">{{ $msg->user->name }}</span>
                <span class="text-muted text-xs">{{ $msg->created_at->diffForHumans() }}</span>
                @if($msg->user->is_admin)<span class="px-2 py-0.5 rounded text-xs bg-primary/20 text-primary">Staff</span>@endif
            </div>
            <p class="text-sm">{{ $msg->body }}</p>
        </div>
        @endforeach
    </div>
    @if($ticket->status !== 'closed')
    <div class="card p-6">
        <h3 class="font-semibold mb-4">Reply</h3>
        <form action="{{ route('dashboard.tickets.reply', $ticket) }}" method="POST" class="space-y-4">
            @csrf
            <textarea name="body" rows="4" class="input w-full" placeholder="Type your reply..." required></textarea>
            <button type="submit" class="btn btn-primary">Send Reply</button>
        </form>
    </div>
    @endif
</div>
@endsection
