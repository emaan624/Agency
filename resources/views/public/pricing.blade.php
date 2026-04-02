@extends('layouts.app')
@section('title', 'Pricing - ' . Setting::get('site_name', 'LuxMotion Agency'))
@section('content')
<section class="py-20 px-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-5xl font-extrabold text-center mb-4">Pricing Plans</h1>
        <p class="text-center text-muted mb-16 text-lg">No hidden fees. Pick a plan and get started today.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($packages as $pkg)
            <div id="{{ $pkg->slug }}" class="card p-10 {{ $pkg->slug === 'standard' ? 'border-primary border-2 scale-105' : '' }}">
                @if($pkg->slug === 'standard')
                    <div class="text-xs font-bold text-primary uppercase tracking-widest mb-4">⭐ Most Popular</div>
                @endif
                <h2 class="text-3xl font-bold mb-2">{{ $pkg->name }}</h2>
                <div class="text-5xl font-extrabold text-primary my-4">${{ number_format($pkg->price, 0) }}</div>
                <p class="text-muted mb-6">{{ $pkg->description }}</p>
                <ul class="space-y-3 mb-8">
                    @foreach($pkg->features ?? [] as $feature)
                        <li class="flex items-center gap-2 text-sm"><span class="text-green-400">✓</span> {{ $feature }}</li>
                    @endforeach
                    <li class="flex items-center gap-2 text-sm"><span class="text-green-400">✓</span> {{ $pkg->revisions }} Revision(s)</li>
                    <li class="flex items-center gap-2 text-sm"><span class="text-green-400">✓</span> {{ $pkg->delivery_days }}-day Delivery</li>
                </ul>
                @auth
                    <form action="{{ route('payment.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $pkg->id }}">
                        <button type="submit" class="btn btn-primary w-full">Order Now</button>
                    </form>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary w-full text-center block">Get Started</a>
                @endauth
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
