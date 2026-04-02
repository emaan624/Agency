<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Setting;

class PublicController extends Controller
{
    public function home()
    {
        $packages = Package::where('is_active', true)->orderBy('sort_order')->get();
        return view('public.home', compact('packages'));
    }

    public function pricing()
    {
        $packages = Package::where('is_active', true)->orderBy('sort_order')->get();
        return view('public.pricing', compact('packages'));
    }

    public function portfolio()
    {
        return view('public.portfolio');
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }
}
