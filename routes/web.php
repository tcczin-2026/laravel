<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LogAcessoMiddleware;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Login (só para quem ainda não entrou)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\LoginController::class, 'create'])->name('login');
    Route::post('/login', [App\Http\Controllers\LoginController::class, 'store'])->name('login.store');

    // Esqueci minha senha (os nomes password.* são os que o Laravel espera)
    Route::get('/esqueci-senha', [App\Http\Controllers\SenhaController::class, 'pedir'])->name('password.request');
    Route::post('/esqueci-senha', [App\Http\Controllers\SenhaController::class, 'enviarLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/redefinir-senha/{token}', [App\Http\Controllers\SenhaController::class, 'formulario'])->name('password.reset');
    Route::post('/redefinir-senha', [App\Http\Controllers\SenhaController::class, 'redefinir'])->name('password.update');
});

Route::post('/logout', [App\Http\Controllers\LoginController::class, 'destroy'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Colecao de games - CRUD (precisa estar logado)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::redirect('/', '/colecao');

    Route::get('/colecao', [App\Http\Controllers\PainelController::class, 'index'])->name('painel.index');

    // ---- Gerente e funcionario: console, controle, jogo e acessorio ----

    Route::resource('console', App\Http\Controllers\ConsoleController::class)->except('show');
    Route::get('console/{console}', [App\Http\Controllers\ConsoleController::class, 'show'])->name('console.show');

    Route::resource('controle', App\Http\Controllers\ControleController::class)->except('show');

    Route::resource('jogo', App\Http\Controllers\JogoController::class)->except('show');
    Route::get('jogo/{jogo}', [App\Http\Controllers\JogoController::class, 'show'])->name('jogo.show');

    Route::resource('acessorio', App\Http\Controllers\AcessorioController::class)->except('show');
    Route::get('acessorio/{acessorio}', [App\Http\Controllers\AcessorioController::class, 'show'])->name('acessorio.show');

    // Reverter alterações pelo histórico (tipo: console, controle, jogo, acessorio; id: linha do histórico)
    Route::post('historico/{tipo}/{id}/desfazer', [App\Http\Controllers\HistoricoController::class, 'desfazer'])->name('historico.desfazer');
    Route::post('historico/{tipo}/{id}/restaurar', [App\Http\Controllers\HistoricoController::class, 'restaurar'])->name('historico.restaurar');

    // ---- So gerente: tabelas auxiliares e usuarios ----

    Route::middleware('perfil:gerente')->group(function () {
        // Tabelas auxiliares: marca, cor, retro, usado, digital, desbloqueado, edicao_especial
        Route::prefix('auxiliar/{tipo}')->name('auxiliar.')->group(function () {
            Route::get('/', [App\Http\Controllers\AuxiliarController::class, 'index'])->name('index');
            Route::get('/novo', [App\Http\Controllers\AuxiliarController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\AuxiliarController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [App\Http\Controllers\AuxiliarController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\AuxiliarController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\AuxiliarController::class, 'destroy'])->name('destroy');
        });

        Route::resource('usuario', App\Http\Controllers\UsuarioController::class)->except('show');
    });
});
