<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Ciclo de vida das assinaturas: vencimento, período de graça e exclusão ────
Schedule::command('subscriptions:process')
    ->dailyAt('08:00')
    ->timezone('America/Sao_Paulo') // o app está em UTC; o horário é de Brasília
    ->withoutOverlapping()          // não inicia se a execução anterior ainda estiver rodando
    ->onOneServer();                // com mais de um servidor, só um executa
