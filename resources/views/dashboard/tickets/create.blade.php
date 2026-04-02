@extends('layouts.dashboard')
@section('title', 'New Ticket')
@section('page-title', 'Open Support Ticket')
@section('content')
<div class="max-w-2xl">
    <form action="{{ route('dashboard.tickets.store') }}" method="POST" class="card p-8 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-2">Subject</label>
            <input type="text" name="subject" class="input w-full" required value="{{ old('subject') }}">
            @error('subject')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Priority</label>
            <select name="priority" class="input w-full">
                <option value="low">Low</option>
                <option value="normal" selected>Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Message</label>
            <textarea name="body" rows="6" class="input w-full" required>{{ old('body') }}</textarea>
            @error('body')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn btn-primary">Submit Ticket</button>
            <a href="{{ route('dashboard.tickets.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
