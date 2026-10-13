<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionNoticeService;
use App\Services\SubscriptionService;
use App\Services\WorkDataPurgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionLifecycle extends Command
{
    protected $signature = 'subscriptions:process {--dry-run : Mostra o que seria feito, sem alterar nada}';

    protected $description = 'Vencimento, avisos, período de graça e exclusão dos dados de trabalho das assinaturas';

    public function handle(
        SubscriptionService $subscriptions,
        SubscriptionNoticeService $notices,
        WorkDataPurgeService $purger,
    ): int {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo simulação: nada será alterado e nenhum aviso será enviado.');
        }

        $this->expireSubscriptions($subscriptions, $notices, $dryRun);
        $this->sendUpcomingNotices($notices, $dryRun);
        $this->purgeAfterGrace($purger, $dryRun);

        return self::SUCCESS;
    }

    // ── 1. Trial/ativa com vencimento no passado → expired + aviso de bloqueio ──
    private function expireSubscriptions(
        SubscriptionService $subscriptions,
        SubscriptionNoticeService $notices,
        bool $dryRun,
    ): void {
        $expired = Subscription::with('user')
            ->whereIn('status', ['trial', 'active'])
            ->where('expires_at', '<', now())
            ->get();

        $this->info("Assinaturas vencidas: {$expired->count()}");

        foreach ($expired as $subscription) {
            $email       = $subscription->user?->email ?? $subscription->user_id;
            $graceEndsAt = $subscriptions->graceEndsAt($subscription);

            if ($dryRun) {
                $this->line("  - {$email} ({$subscription->status}) → dados guardados até {$graceEndsAt->format('d/m/Y')}");
                continue;
            }

            if (!$subscriptions->expire($subscription)) {
                $this->line("  - {$email} → ignorada (renovada nesse meio-tempo)");
                continue;
            }

            $this->line("  - {$email} → vencida, dados guardados até {$graceEndsAt->format('d/m/Y')}");
            Log::info('Assinatura vencida', ['user_id' => $subscription->user_id, 'grace_ends_at' => $graceEndsAt]);

            // Só avisa "dados guardados até X" se essa data ainda não passou
            $subscription->refresh();
            if ($subscription->grace_ends_at->isFuture()) {
                $this->notify($notices, $subscription, SubscriptionNoticeService::EXPIRED, false);
            }
        }
    }

    // ── 2. Avisos de vencimento e de exclusão próximos ────────────────────────
    private function sendUpcomingNotices(SubscriptionNoticeService $notices, bool $dryRun): void
    {
        $expiring = Subscription::with('user')
            ->whereIn('status', ['trial', 'active'])
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->get();

        $purging = Subscription::with('user')
            ->where('status', 'expired')
            ->whereNull('data_purged_at')
            ->whereBetween('grace_ends_at', [now(), now()->addDays(3)])
            ->get();

        $this->info("Avisos a verificar: {$expiring->count()} vencendo, {$purging->count()} com exclusão próxima");

        foreach ($expiring as $subscription) {
            if ($type = $notices->dueExpiringNotice($subscription)) {
                $this->notify($notices, $subscription, $type, $dryRun);
            }
        }

        foreach ($purging as $subscription) {
            if ($type = $notices->duePurgeNotice($subscription)) {
                $this->notify($notices, $subscription, $type, $dryRun);
            }
        }
    }

    // ── 3. Graça encerrada → apaga os dados de trabalho ───────────────────────
    private function purgeAfterGrace(WorkDataPurgeService $purger, bool $dryRun): void
    {
        $toPurge = Subscription::with('user')
            ->where('status', 'expired')
            ->whereNull('data_purged_at')
            ->where('grace_ends_at', '<=', now())
            ->get();

        $this->info("Períodos de graça encerrados: {$toPurge->count()}");

        foreach ($toPurge as $subscription) {
            $user = $subscription->user;

            if (!$user) {
                continue;
            }

            $summary = $dryRun ? $purger->preview($user) : $purger->purge($subscription);

            if ($summary === null) {
                $this->line("  - {$user->email} → ignorada (renovada nesse meio-tempo)");
                continue;
            }

            $detail = collect($summary)->map(fn (int $n, string $table) => "{$table}: {$n}")->implode(', ');
            $this->line("  - {$user->email} → " . ($dryRun ? 'seriam apagados ' : 'apagados ') . $detail);

            if (!$dryRun) {
                Log::info('Dados de trabalho apagados após o período de graça', ['user_id' => $user->id] + $summary);
            }
        }
    }

    // ── Envio de um aviso, com tratamento de erro por usuário ─────────────────
    private function notify(
        SubscriptionNoticeService $notices,
        Subscription $subscription,
        string $type,
        bool $dryRun,
    ): void {
        if (!$subscription->user || $notices->wasSent($subscription, $type)) {
            return;
        }

        $email = $subscription->user->email;

        if ($dryRun) {
            $this->line("  - {$email} → aviso {$type} seria enviado");
            return;
        }

        try {
            $notices->send($subscription, $type);
            $this->line("  - {$email} → aviso {$type} enviado");
        } catch (\Throwable $e) {
            $this->error("  - {$email} → falha ao enviar aviso {$type}: {$e->getMessage()}");
            Log::error('Falha ao enviar aviso de assinatura', [
                'user_id' => $subscription->user_id,
                'type'    => $type,
                'erro'    => $e->getMessage(),
            ]);
        }
    }
}
