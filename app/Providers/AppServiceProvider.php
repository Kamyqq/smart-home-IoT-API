<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('device_api', function (Request $request) {
            $device = $request->attributes->get('device');

            return $device
                ? Limit::perMinute(120)->by($device->id)
                : Limit::perMinute(10)->by($request->ip());
        });
    }
}
