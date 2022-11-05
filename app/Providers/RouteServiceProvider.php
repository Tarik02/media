<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    RateLimiter,
    Route
};

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();

        include \base_path('routes/bindings.php');

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(\base_path('routes/api.php'));

            Route::middleware('web')
                ->group(\base_path('routes/web.php'));

            Route::middleware('global')
                ->group(\base_path('routes/global.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(180)->by($request->user()?->id ?: $request->ip());
        });
    }
}
