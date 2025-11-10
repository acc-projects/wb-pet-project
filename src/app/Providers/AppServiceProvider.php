<?php

namespace App\Providers;

use App\Services\Contracts\ApiClientInterface;
use App\Services\Contracts\DataProcessorInterface;
use App\Services\DataProcessor;
use App\Services\WbApiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ApiClientInterface::class, function ($app) {
            return new WbApiClient(
                config('services.wb_api.base_url'),
                config('services.wb_api.token')
            );
        });
        $this->app->bind(DataProcessorInterface::class, DataProcessor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
