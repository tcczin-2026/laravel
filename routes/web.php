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

Route::get('/', [App\Http\Controllers\Principal::class, 'principal']);




/*
|--------------------------------------------------------------------------
| Colecao de games - CRUD
|--------------------------------------------------------------------------
*/

Route::get('/colecao', [App\Http\Controllers\PainelController::class, 'index'])->name('painel.index');

// Tabelas principais: console, controle, jogo e acessorio
Route::resource('console', App\Http\Controllers\ConsoleController::class)->except('show');
Route::get('console/{console}', [App\Http\Controllers\ConsoleController::class, 'show'])->name('console.show');

Route::resource('controle', App\Http\Controllers\ControleController::class)->except('show');
Route::get('controle/{controle}', [App\Http\Controllers\ControleController::class, 'show'])->name('controle.show');

Route::resource('jogo', App\Http\Controllers\JogoController::class)->except('show');
Route::get('jogo/{jogo}', [App\Http\Controllers\JogoController::class, 'show'])->name('jogo.show');

Route::resource('acessorio', App\Http\Controllers\AcessorioController::class)->except('show');
Route::get('acessorio/{acessorio}', [App\Http\Controllers\AcessorioController::class, 'show'])->name('acessorio.show');

// Tabelas auxiliares: marca, cor, retro, usado, digital, desbloqueado, edicao_especial
Route::prefix('auxiliar/{tipo}')->name('auxiliar.')->group(function () {
    Route::get('/', [App\Http\Controllers\AuxiliarController::class, 'index'])->name('index');
    Route::get('/novo', [App\Http\Controllers\AuxiliarController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\AuxiliarController::class, 'store'])->name('store');
    Route::get('/{id}/editar', [App\Http\Controllers\AuxiliarController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\AuxiliarController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\AuxiliarController::class, 'destroy'])->name('destroy');
});
