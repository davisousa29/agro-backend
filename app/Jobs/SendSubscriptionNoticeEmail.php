<?php

namespace App\Jobs;

use App\Mail\SubscriptionNoticeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;

/**
 * Envia o e-mail de um aviso de assinatura pela fila.
 * Recebe só textos (e não o model), para o e-mail sair igual ao que foi registrado.
 */
class SendSubscriptionNoticeEmail implements ShouldQueue
{
    use Queueable;

    // Falhas reais do servidor de e-mail: desiste depois de 5
    public int $maxExceptions = 5;

    public function __construct(
        public string $email,
        public string $nome,
        public string $titulo,
        public string $mensagem,
    ) {}

    // Passa pelo limitador 'emails': se o segundo está cheio, volta para a fila e tenta logo depois
    public function middleware(): array
    {
        return [new RateLimited('emails')];
    }

    // Espera entre tentativas depois de uma falha: 1, 5 e 15 minutos (depois, de 15 em 15)
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    // Prazo total: 12 horas sem conseguir enviar → falha de vez (vai para failed_jobs)
    public function retryUntil(): \DateTime
    {
        return now()->addHours(12);
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(new SubscriptionNoticeMail($this->nome, $this->titulo, $this->mensagem));
    }
}
