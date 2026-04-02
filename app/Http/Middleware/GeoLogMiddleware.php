<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeoLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            Log::create([
                'user_id' => $request->user()->id,
                'action' => $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'country' => null,
                'payload' => null,
            ]);
        }

        return $response;
    }
}
