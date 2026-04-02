@extends('layouts.dashboard')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">All Notifications</h2>
    <form action="{{ route('dashboard.notifications.mark-all-read') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline text-sm">Mark All Read</button>
    </form>
</div>
<div class="space-y-3">
    @forelse($notifications as $n)
    <div class="card p-5 flex items-start gap-4 {{ $n->read_at ? 'opacity-60' : '' }}">
        <div class="text-2xl">{{ ['info'=>'ℹ️','success'=>'✅','warning'=>'⚠️','error'=>'❌'][$n->type] ?? 'ℹ️' }}</div>
        <div class="flex-1">
            <p class="font-medium text-sm">{{ $n->title }}</p>
            @if($n->body)<p class="text-muted text-xs mt-1">{{ $n->body }}</p>@endif
            <p class="text-muted text-xs mt-2">{{ $n->created_at->diffForHumans() }}</p>
        </div>
        @if(!$n->read_at)
        <form action="{{ route('dashboard.notifications.mark-read', $n) }}" method="POST">
            @csrf
            <button type="submit" class="text-xs text-primary hover:underline">Mark read</button>
        </form>
        @endif
    </div>
    @empty
    <div class="card p-12 text-center text-muted">No notifications yet.</div>
    @endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
