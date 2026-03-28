<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'Missing API key'], 401);
        }

        $device = Device::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $device->update([
            'last_seen_at' => now(),
        ]);

        $request->attributes->set('device', $device);

        return $next($request);
    }
}
