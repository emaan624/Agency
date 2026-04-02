@extends('layouts.app')

@section('title', Setting::get('site_name', 'LuxMotion Agency') . ' - ' . Setting::get('site_tagline', 'Premium Creative Services'))

@section('content')
{{-- Hero --}}
<section class="hero-section relative overflow-hidden py-24 px-6">
    <div class="max-w-7xl mx-auto text-center">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight">
            {!! Setting::get('hero_title', 'Creative Agency for <span class="text-primary">Modern Brands</span>') !!}
        </h1>
        <p class="text-xl md:text-2xl text-muted max-w-3xl mx-auto mb-10">
            {{ Setting::get('hero_subtitle', 'We deliver premium design, development, and marketing services.') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('pricing') }}" class="btn btn-primary btn-lg">View Packages</a>
            <a href="{{ route('portfolio') }}" class="btn btn-outline btn-lg">See Our Work</a>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="py-16 px-6 bg-surface">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
        <div>
            <div class="text-5xl font-extrabold text-primary">{{ Setting::get('orders_total', '1,200') }}+</div>
            <div class="text-muted mt-2">Projects Completed</div>
        </div>
        <div>
            <div class="text-5xl font-extrabold text-primary">{{ Setting::get('clients_total', '340') }}+</div>
            <div class="text-muted mt-2">Happy Clients</div>
        </div>
        <div>
            <div class="text-5xl font-extrabold text-primary">{{ Setting::get('satisfaction_rate', '98') }}%</div>
            <div class="text-muted mt-2">Satisfaction Rate</div>
        </div>
    </div>
</section>

{{-- Services --}}
<section class="py-20 px-6">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-4xl font-bold text-center mb-12">What We Do</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['icon' => '🎨', 'title' => 'Brand Identity', 'desc' => 'Logos, color systems, and visual guidelines that make your brand unforgettable.'],
                ['icon' => '💻', 'title' => 'Web Design', 'desc' => 'Beautiful, conversion-optimized websites built with modern technologies.'],
                ['icon' => '📈', 'title' => 'Digital Marketing', 'desc' => 'Data-driven campaigns that grow your audience and drive revenue.'],
            ] as $service)
            <div class="card p-8 text-center">
                <div class="text-5xl mb-4">{{ $service['icon'] }}</div>
                <h3 class="text-xl font-semibold mb-3">{{ $service['title'] }}</h3>
                <p class="text-muted">{{ $service['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Pricing Preview --}}
<section class="py-20 px-6 bg-surface">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-4xl font-bold text-center mb-4">Simple, Transparent Pricing</h2>
        <p class="text-center text-muted mb-12">Choose the package that fits your needs.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($packages as $pkg)
            <div class="card p-8 {{ $pkg->slug === 'standard' ? 'border-primary border-2' : '' }}">
                @if($pkg->slug === 'standard')
                    <div class="text-xs font-bold text-primary uppercase tracking-widest mb-3">Most Popular</div>
                @endif
                <h3 class="text-2xl font-bold mb-2">{{ $pkg->name }}</h3>
                <div class="text-4xl font-extrabold text-primary mb-4">${{ number_format($pkg->price, 0) }}</div>
                <p class="text-muted text-sm mb-6">{{ $pkg->description }}</p>
                <a href="{{ route('pricing') }}#{{ $pkg->slug }}" class="btn btn-primary w-full text-center block">Get Started</a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 px-6 text-center">
    <div class="max-w-3xl mx-auto">
        <h2 class="text-4xl font-bold mb-6">Ready to Transform Your Brand?</h2>
        <p class="text-muted text-lg mb-8">Join hundreds of businesses that trust LuxMotion Agency for their creative needs.</p>
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Start Your Project Today</a>
    </div>
</section>
@endsection
