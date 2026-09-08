<?php

namespace App\Providers;
use App\Models\Console;
use App\Models\Controle;
use App\Observers\ConsoleObserver;
use App\Observers\ControleObserver;


use Illuminate\Support\ServiceProvider;

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
        Console::observe(ConsoleObserver::class);
        Controle::observe(ControleObserver::class);
    }
}
