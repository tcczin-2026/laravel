<?php

namespace App\Providers;
use App\Models\Console;
use App\Models\Controle;
use App\Observers\ConsoleObserver;
use App\Observers\ControleObserver;
use App\Models\Acessorio;
use App\Observers\AcessorioObserver;
use App\Models\Jogo;
use App\Observers\JogoObserver;

use Illuminate\Pagination\Paginator;
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
        Acessorio::observe(AcessorioObserver::class);
        Jogo::observe(JogoObserver::class);

        // Links de paginação no estilo do Bootstrap 5 (o padrão é Tailwind)
        Paginator::useBootstrapFive();
    }





}


