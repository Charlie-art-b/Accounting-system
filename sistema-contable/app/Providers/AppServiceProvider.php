<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\AccountReceivable;
use App\Observers\AccountReceivableObserver;

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
        // Cargar la extensión intl si está disponible
       // if (!extension_loaded('intl')) {
            // Intentar cargar dinámicamente si está disponible
          //  @dl('php_intl.dll');
        //}

        AccountReceivable::observe(AccountReceivableObserver::class);
    }
}

