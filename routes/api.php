<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PerfilController;
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

});
