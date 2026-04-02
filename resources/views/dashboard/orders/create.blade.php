@extends('layouts.dashboard')
@section('title', 'New Order')
@section('page-title', 'Place New Order')
@section('content')
<div class="max-w-2xl">
    <form action="{{ route('dashboard.orders.store') }}" method="POST" class="card p-8 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-2">Select Package</label>
            <select name="package_id" class="input w-full" required>
                <option value="">Choose a package...</option>
                @foreach($packages as $pkg)
                    <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                        {{ $pkg->name }} — ${{ number_format($pkg->price, 2) }}
                    </option>
                @endforeach
            </select>
            @error('package_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Project Requirements</label>
            <textarea name="requirements" rows="5" class="input w-full" placeholder="Describe your project requirements...">{{ old('requirements') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Coupon Code (optional)</label>
            <input type="text" name="coupon_code" class="input w-full" placeholder="e.g. DEMO20" value="{{ old('coupon_code') }}">
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn btn-primary">Place Order</button>
            <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
