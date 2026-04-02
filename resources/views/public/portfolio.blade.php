@extends('layouts.app')
@section('title', 'Portfolio - ' . Setting::get('site_name', 'LuxMotion Agency'))
@section('content')
<section class="py-24 px-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-5xl font-extrabold text-center mb-4">Our Portfolio</h1>
        <p class="text-center text-muted mb-16 text-lg">A showcase of our finest work across industries.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach(range(1, 6) as $i)
            <div class="card overflow-hidden">
                <div class="bg-gradient-to-br from-primary/20 to-primary/5 h-48 flex items-center justify-center text-4xl">🎨</div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-2">Project {{ $i }}</h3>
                    <p class="text-muted text-sm">Brand identity &amp; digital design project.</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
