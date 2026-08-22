<?php

namespace App\Providers;

use App\Models\CourseModule;
use App\Observers\ModuleObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Content registers itself as a trackable module as it is created, so
        // progress denominators stay right without anyone remembering to.
        foreach (CourseModule::TYPES as $model) {
            $model::observe(ModuleObserver::class);
        }
    }
}
