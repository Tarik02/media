<?php

namespace App\Providers;

use Illuminate\Support\{
    Facades\Broadcast,
    ServiceProvider
};

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Broadcast::routes();
    }
}
