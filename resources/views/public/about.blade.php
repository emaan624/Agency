@extends('layouts.app')
@section('title', 'About Us - ' . Setting::get('site_name', 'LuxMotion Agency'))
@section('content')
<section class="py-24 px-6">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-5xl font-extrabold mb-6">About LuxMotion Agency</h1>
        <p class="text-xl text-muted mb-12">We are a team of passionate creatives dedicated to helping brands grow through exceptional design and digital experiences.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <div class="card p-8 text-center"><div class="text-4xl mb-4">🎯</div><h3 class="font-bold text-lg mb-2">Our Mission</h3><p class="text-muted text-sm">Empower every business with world-class creative solutions.</p></div>
            <div class="card p-8 text-center"><div class="text-4xl mb-4">👁️</div><h3 class="font-bold text-lg mb-2">Our Vision</h3><p class="text-muted text-sm">To be the most trusted creative agency globally.</p></div>
            <div class="card p-8 text-center"><div class="text-4xl mb-4">💎</div><h3 class="font-bold text-lg mb-2">Our Values</h3><p class="text-muted text-sm">Quality, transparency, and client satisfaction above all.</p></div>
        </div>
    </div>
</section>
@endsection
