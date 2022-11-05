<?php

namespace App\Providers;

use App\Support\Reddit\PostProcessor;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Storage;

use GuzzleHttp\{
    Client,
    ClientInterface
};
use Illuminate\Database\Eloquent\{
    Relations\Relation,
    Model
};

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, Client::class);

        $this->app->singleton(PostProcessor::class);
        $this->app->when(PostProcessor::class)
            ->needs('$disk')->give('reddit');
        $this->app->when(PostProcessor::class)
            ->needs(Filesystem::class)
            ->give(
                fn () => Storage::disk('reddit')
            );

        Model::shouldBeStrict();
        Relation::requireMorphMap();
    }

    public function boot(): void
    {
        //
    }
}
