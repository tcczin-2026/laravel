<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/sync', function (Request $request) {
    $data = $request->input('data');

    foreach ($data as $item) {
        DB::table('your_table')->updateOrInsert(
            ['id' => $item['id']],
            $item
        );
    }

    return response()->json(['message' => 'Dados sincronizados com sucesso!']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('teste', function() {});