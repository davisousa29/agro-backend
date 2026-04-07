<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\Fazenda\FazendaController;
use App\Http\Controllers\Api\Consultor\ContratoController;
use App\Http\Controllers\Api\BuscaController;
use Illuminate\Support\Facades\Route;

// ── Rotas públicas — não precisam de token ────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ── Rotas protegidas — precisam de token JWT válido ───────────────────────────
Route::middleware('auth:api')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('perfil')->group(function () {
        Route::get('/',  [PerfilController::class, 'show']);
        Route::post('/', [PerfilController::class, 'save']);
    });

    Route::prefix('fazendas')->group(function () {
        Route::get('/',        [FazendaController::class, 'index']);
        Route::post('/',       [FazendaController::class, 'store']);
        Route::get('/{id}',    [FazendaController::class, 'show']);
        Route::put('/{id}',    [FazendaController::class, 'update']);
        Route::delete('/{id}', [FazendaController::class, 'destroy']);
    });

    Route::prefix('contratos')->group(function () {
        Route::get('/',                    [ContratoController::class, 'index']);
        Route::post('/',                   [ContratoController::class, 'store']);
        Route::get('/{id}',                [ContratoController::class, 'show']);
        Route::post('/{id}/responder',     [ContratoController::class, 'responder']);
        Route::post('/{id}/encerrar',      [ContratoController::class, 'encerrar']);
    });

    Route::prefix('busca')->group(function () {
        Route::get('/fazendeiros',              [BuscaController::class, 'fazendeiros']);
        Route::get('/fazendeiros/{username}',   [BuscaController::class, 'perfilFazendeiro']);
    });

});
