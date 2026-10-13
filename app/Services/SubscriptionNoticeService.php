<?php

namespace App\Services;

use App\Mail\SubscriptionNoticeMail;
use App\Models\Subscription;
use App\Models\SubscriptionNotice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SubscriptionNoticeService
{
    public const EXPIRING_3D = 'expiring_3d';
    public const EXPIRING_1D = 'expiring_1d';
    public const EXPIRED     = 'expired';
    public const PURGE_3D    = 'purge_3d';
    public const PURGE_1D    = 'purge_1d';

    // O app roda em UTC; as datas mostradas ao usuário ficam no horário de Brasília
    private const TIMEZONE = 'America/Sao_Paulo';

    /**
     * Aviso de vencimento que cabe agora (trial/ativa vencendo em até 3 dias), ou null.
     */
    public function dueExpiringNotice(Subscription $subscription): ?string
    {
        if (
            !in_array($subscription->status, ['trial', 'active'], true)
            || !$subscription->expires_at->isFuture()
        ) {
            return null;
        }

        return $this->windowFor($subscription->expires_at, self::EXPIRING_1D, self::EXPIRING_3D);
    }

    /**
     * Aviso de exclusão que cabe agora (vencida, com a graça terminando em até 3 dias), ou null.
     */
    public function duePurgeNotice(Subscription $subscription): ?string
    {
        if (
            $subscription->status !== 'expired'
            || $subscription->data_purged_at !== null
            || !$subscription->grace_ends_at?->isFuture()
        ) {
            return null;
        }

        return $this->windowFor($subscription->grace_ends_at, self::PURGE_1D, self::PURGE_3D);
    }

    /**
     * Este aviso já foi enviado para o período atual?
     */
    public function wasSent(Subscription $subscription, string $type): bool
    {
        return SubscriptionNotice::where('subscription_id', $subscription->id)
            ->where('type', $type)
            ->where('reference_at', $this->referenceFor($subscription, $type))
            ->exists();
    }

    /**
     * Envia o aviso por email e pela notificação do app, e registra o envio.
     * O email vai primeiro: se falhar, nada é registrado e o aviso é tentado de novo no próximo dia.
     */
    public function send(Subscription $subscription, string $type): bool
    {
        if ($this->wasSent($subscription, $type)) {
            return false;
        }

        $user = $subscription->user;
        [$title, $message] = $this->texts($subscription, $type);

        Mail::to($user->email)->send(new SubscriptionNoticeMail($user->name, $title, $message));

        DB::transaction(function () use ($subscription, $type, $user, $title, $message) {
            NotificacaoService::criar($user->id, 'assinatura', $title, $message, ['rota' => 'assinatura']);

            SubscriptionNotice::create([
                'subscription_id' => $subscription->id,
                'type'            => $type,
                'reference_at'    => $this->referenceFor($subscription, $type),
            ]);
        });

        return true;
    }

    // ── Internos ──────────────────────────────────────────────────────────────

    // Até 24h antes → aviso de 1 dia; até 3 dias antes → aviso de 3 dias
    private function windowFor(Carbon $deadline, string $oneDay, string $threeDays): ?string
    {
        $daysLeft = now()->floatDiffInDays($deadline, false);

        return match (true) {
            $daysLeft <= 1 => $oneDay,
            $daysLeft <= 3 => $threeDays,
            default        => null,
        };
    }

    private function referenceFor(Subscription $subscription, string $type): Carbon
    {
        return in_array($type, [self::PURGE_3D, self::PURGE_1D], true)
            ? $subscription->grace_ends_at
            : $subscription->expires_at;
    }

    private function format(Carbon $date): string
    {
        $local = $date->copy()->timezone(self::TIMEZONE);

        return $local->format('d/m/Y') . ' às ' . $local->format('H:i');
    }

    /**
     * Título e mensagem de cada aviso.
     * Quem nunca pagou (is_subscriber = false) recebe o texto de teste grátis.
     */
    private function texts(Subscription $subscription, string $type): array
    {
        $isTrial    = !$subscription->is_subscriber;
        $expiresAt  = $this->format($subscription->expires_at);
        $graceEnds  = $subscription->grace_ends_at ? $this->format($subscription->grace_ends_at) : '';
        $renewHint  = $isTrial
            ? 'Assine um plano para continuar usando o Colchete.'
            : 'Renove agora: os dias que faltam não são perdidos.';
        $purgeText  = "Seus contratos, programas de ração e projeções de venda serão apagados definitivamente em {$graceEnds}. Assine um plano para mantê-los.";

        return match ($type) {
            self::EXPIRING_3D => [
                $isTrial ? 'Seu teste grátis está terminando' : 'Sua assinatura está perto de vencer',
                ($isTrial ? "Seu teste grátis termina em {$expiresAt}. " : "Sua assinatura vence em {$expiresAt}. ") . $renewHint,
            ],
            self::EXPIRING_1D => [
                $isTrial ? 'Seu teste grátis termina em menos de 24 horas' : 'Sua assinatura vence em menos de 24 horas',
                ($isTrial ? "Seu teste grátis termina em {$expiresAt}. " : "Sua assinatura vence em {$expiresAt}. ") . $renewHint,
            ],
            self::EXPIRED => [
                'Seu acesso ao Colchete foi bloqueado',
                ($isTrial ? "Seu teste grátis terminou em {$expiresAt}. " : "Sua assinatura venceu em {$expiresAt}. ")
                . "Seus dados ficam guardados até {$graceEnds}. Assine um plano para recuperar o acesso.",
            ],
            self::PURGE_3D => ['Seus dados serão apagados em breve', $purgeText],
            self::PURGE_1D => ['Seus dados serão apagados em menos de 24 horas', $purgeText],
        };
    }
}
