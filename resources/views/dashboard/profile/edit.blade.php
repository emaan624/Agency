@extends('layouts.dashboard')
@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('content')
<div class="max-w-2xl space-y-8">
    {{-- Profile Edit --}}
    <div class="card p-8">
        <h3 class="font-semibold text-lg mb-6">Edit Profile</h3>
        <form action="{{ route('dashboard.profile.update') }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div><label class="block text-sm font-medium mb-1">Full Name</label><input type="text" name="name" class="input w-full" value="{{ old('name', $user->name) }}" required></div>
            <div><label class="block text-sm font-medium mb-1">Email</label><input type="email" class="input w-full opacity-60" value="{{ $user->email }}" disabled></div>
            <div><label class="block text-sm font-medium mb-1">Phone</label><input type="text" name="phone" class="input w-full" value="{{ old('phone', $user->phone) }}"></div>
            <div><label class="block text-sm font-medium mb-1">Timezone</label><input type="text" name="timezone" class="input w-full" value="{{ old('timezone', $user->timezone) }}" placeholder="UTC"></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    {{-- KYC Upload --}}
    <div class="card p-8">
        <h3 class="font-semibold text-lg mb-2">KYC Verification</h3>
        <p class="text-muted text-sm mb-6">Status: <span class="text-primary font-medium">{{ ucfirst($user->kyc_status) }}</span></p>
        @if($user->kyc_status === 'none')
        <form action="{{ route('dashboard.profile.kyc') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div><label class="block text-sm font-medium mb-1">Document Type</label>
                <select name="document_type" class="input w-full"><option value="passport">Passport</option><option value="id_card">ID Card</option><option value="driver_license">Driver's License</option></select></div>
            <div><label class="block text-sm font-medium mb-1">Front Side</label><input type="file" name="front" class="input w-full" required></div>
            <div><label class="block text-sm font-medium mb-1">Back Side (optional)</label><input type="file" name="back" class="input w-full"></div>
            <button type="submit" class="btn btn-primary">Submit KYC</button>
        </form>
        @else
        <p class="text-sm text-muted">Your documents have been submitted.</p>
        @endif
    </div>

    {{-- Referral --}}
    <div class="card p-8">
        <h3 class="font-semibold text-lg mb-4">Referral Program</h3>
        <p class="text-muted text-sm mb-3">Share your link and earn commission on referred orders.</p>
        <div class="flex gap-3">
            <input type="text" class="input flex-1" value="{{ $referralLink }}" readonly onclick="this.select()">
            <button onclick="navigator.clipboard.writeText('{{ $referralLink }}')" class="btn btn-outline text-sm">Copy</button>
        </div>
    </div>
</div>
@endsection
