@extends('layouts.app')
@section('title', 'Contact - ' . Setting::get('site_name', 'LuxMotion Agency'))
@section('content')
<section class="py-24 px-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-5xl font-extrabold text-center mb-4">Get In Touch</h1>
        <p class="text-center text-muted mb-12">Have a project in mind? We'd love to hear from you.</p>
        <form class="card p-8 space-y-6" method="POST" action="#">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" class="input w-full" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" class="input w-full" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Message</label>
                <textarea name="message" rows="5" class="input w-full" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full">Send Message</button>
        </form>
    </div>
</section>
@endsection
