<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageApiController extends Controller
{
    public function index()
    {
        $packages = Package::where('is_active', true)->orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $packages, 'message' => 'OK']);
    }
}
