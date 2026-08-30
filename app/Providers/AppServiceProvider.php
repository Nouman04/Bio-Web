<?php

namespace App\Providers;

use App\Models\CourseModule;
use App\Observers\ModuleObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\View\Composers\SidebarComposer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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

        // The password policy, in one place: every form that sets a password
        // asks for Password::defaults(), so this is what all of them enforce.
        // Eight characters, at least one number and at least one symbol.
        Password::defaults(fn () => Password::min(8)->numbers()->symbols());
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // The sidebar badges. Composed rather than queried in the partials so
        // the views stay markup and the counts run once per request.
        View::composer(
            ['layouts.partials.sidebar', 'layouts.partials.student-sidebar'],
            SidebarComposer::class
        );

        // Content registers itself as a trackable module as it is created, so
        // progress denominators stay right without anyone remembering to.
        foreach (CourseModule::TYPES as $model) {
            $model::observe(ModuleObserver::class);
        }
    }
}
