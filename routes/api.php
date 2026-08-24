<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\Fazenda\FazendaController;
use App\Http\Controllers\Api\Consultor\ContratoController;
use App\Http\Controllers\Api\BuscaController;
use App\Http\Controllers\Api\Racao\ExigenciaController;
use App\Http\Controllers\Api\Racao\IngredienteController;
use App\Http\Controllers\Api\Racao\RacaoController;
use App\Http\Controllers\Api\Clima\ClimaController;
use App\Http\Controllers\Api\Projecao\ProjecaoVendaController;
use App\Http\Controllers\Api\Notificacao\NotificacaoController;
use App\Http\Controllers\Api\Auth\RecuperacaoSenhaController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\DoisFatoresController;
use Illuminate\Support\Facades\Route;

// ── Rotas públicas ────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60');
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,2');

    // Login com Google
    Route::post('/google', [GoogleAuthController::class, 'login'])->middleware('throttle:10,1');

    // Recuperação de senha
    Route::post('/recuperar-senha', [RecuperacaoSenhaController::class, 'solicitar'])->middleware('throttle:5,2');
    Route::post('/redefinir-senha', [RecuperacaoSenhaController::class, 'redefinir'])->middleware('throttle:5,2');
});

// ── 2FA no login ─────────────────────
Route::prefix('2fa')->group(function () {
    Route::post('/login/enviar-email', [DoisFatoresController::class, 'enviarCodigoEmail'])->middleware('throttle:5,2');
    Route::post('/login/verificar',    [DoisFatoresController::class, 'verificarLogin'])->middleware('throttle:10,2');
});

// ── Rotas protegidas — token JWT ───────────────────────────
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
        Route::get('/',                [ContratoController::class, 'index']);
        Route::post('/',               [ContratoController::class, 'store']);
        Route::get('/{id}',            [ContratoController::class, 'show']);
        Route::post('/{id}/responder', [ContratoController::class, 'responder']);
        Route::post('/{id}/encerrar',  [ContratoController::class, 'encerrar']);
    });

    Route::prefix('busca')->group(function () {
        Route::get('/fazendeiros',            [BuscaController::class, 'fazendeiros']);
        Route::get('/fazendeiros/{username}', [BuscaController::class, 'perfilFazendeiro']);
    });

    // ── Programa de Formulação de Ração ───────────────────────────────────────
    Route::prefix('racao')->group(function () {

        // Exigências nutricionais
        Route::post('/exigencias', [ExigenciaController::class, 'calcular']);

        // Ingredientes
        Route::get('/ingredientes',              [IngredienteController::class, 'index']);
        Route::post('/ingredientes',             [IngredienteController::class, 'store']);
        Route::get('/ingredientes/{id}',         [IngredienteController::class, 'show']);
        Route::patch('/ingredientes/{id}/preco', [IngredienteController::class, 'atualizarPreco']);

        // Programas de ração
        Route::get('/programas',                          [RacaoController::class, 'index']);
        Route::post('/programas',                         [RacaoController::class, 'store']);
        Route::get('/programas/{id}',                     [RacaoController::class, 'show']);
        Route::post('/programas/{id}/ingredientes',       [RacaoController::class, 'salvarIngredientes']);
        Route::post('/programas/{id}/encerrar',           [RacaoController::class, 'encerrar']);
        Route::get('/programas/{id}/pdf', [RacaoController::class, 'pdf']);
    });

    // ── Dados de referência — espécies, raças, categorias, sistemas ───────────
    Route::prefix('referencia')->group(function () {
        Route::get('/especies',    fn() => response()->json(['especies'  => \App\Models\Especie::where('ativo', true)->get()]));
        Route::get('/racas',       fn() => response()->json(['racas'     => \App\Models\Raca::where('ativo', true)->get()]));
        Route::get('/categorias',  fn() => response()->json(['categorias'=> \App\Models\CategoriaAnimal::where('ativo', true)->get()]));
        Route::get('/sistemas',    fn() => response()->json(['sistemas'  => \App\Models\SistemaProducao::where('ativo', true)->get()]));

        // Filtradas por espécie
        Route::get('/racas/{especie_id}',      fn($id) => response()->json(['racas'     => \App\Models\Raca::where('especie_id', $id)->where('ativo', true)->get()]));
        Route::get('/categorias/{especie_id}', fn($id) => response()->json(['categorias'=> \App\Models\CategoriaAnimal::where('especie_id', $id)->where('ativo', true)->get()]));
        Route::get('/sistemas/{especie_id}',   fn($id) => response()->json(['sistemas'  => \App\Models\SistemaProducao::where('especie_id', $id)->where('ativo', true)->get()]));
    });

    Route::prefix('clima')->group(function () {
        Route::get('/anual', [ClimaController::class, 'anual']);
    });

    Route::prefix('projecoes')->group(function () {
        Route::get('/',        [ProjecaoVendaController::class, 'index']);
        Route::post('/',       [ProjecaoVendaController::class, 'store']);
        Route::get('/{id}',    [ProjecaoVendaController::class, 'show']);
        Route::delete('/{id}', [ProjecaoVendaController::class, 'destroy']);
        Route::get('/{id}/pdf',[ProjecaoVendaController::class, 'pdf']);
        Route::patch('/{id}/contrato', [ProjecaoVendaController::class, 'vincularContrato']);
    });

    // ── Notificações direcionadas ───────────
    Route::prefix('notificacoes')->group(function () {
        Route::get('/',                    [NotificacaoController::class, 'index']);
        Route::get('/ultimas',             [NotificacaoController::class, 'ultimas']);
        Route::get('/nao-lidas',           [NotificacaoController::class, 'contarNaoLidas']);
        Route::patch('/{id}/lida',         [NotificacaoController::class, 'marcarLida']);
        Route::patch('/todas/lidas',       [NotificacaoController::class, 'marcarTodasLidas']);
    });

    // ── 2FA — gestão (requer estar logado) ────────────────────────────────────
    Route::prefix('2fa')->group(function () {
        Route::post('/gerar',     [DoisFatoresController::class, 'gerar']);
        Route::post('/confirmar', [DoisFatoresController::class, 'confirmar']);
        Route::post('/desativar', [DoisFatoresController::class, 'desativar']);
    });
});
