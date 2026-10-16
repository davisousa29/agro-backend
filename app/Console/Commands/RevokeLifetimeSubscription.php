<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RevokeLifetimeSubscription extends Command
{
    protected $signature = 'subscriptions:revoke-lifetime
                            {emails* : Um ou mais emails, separados por espaço}
                            {--days=30 : Dias até a assinatura vencer (0 = vence na próxima execução do agendador)}
                            {--force : Revoga sem pedir confirmação}';

    protected $description = 'Revoga a assinatura vitalícia: o cliente volta ao ciclo normal com N dias até vencer';

    public function handle(SubscriptionService $subscriptions): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($days === false) {
            $this->error('O --days precisa ser um número inteiro maior ou igual a zero.');
            return self::FAILURE;
        }

        $emails = collect($this->argument('emails'))
            ->map(fn (string $email) => strtolower(trim($email)))
            ->unique();

        $users = User::with('subscription')
            ->whereIn('email', $emails)
            ->get()
            ->keyBy('email');

        $hasProblem = false;

        foreach ($emails->diff($users->keys()) as $email) {
            $this->error("Usuário não encontrado: {$email}");
            $hasProblem = true;
        }

        // Só revoga quem é de fato vitalício
        $lifetime = $users->filter(fn (User $user) => $user->subscription?->status === 'lifetime');

        foreach ($users->diffKeys($lifetime) as $user) {
            $status = $user->subscription?->status ?? 'sem assinatura';
            $this->warn("Não é vitalício ({$status}), ignorado: {$user->email}");
            $hasProblem = true;
        }

        if ($lifetime->isEmpty()) {
            return self::FAILURE;
        }

        $newExpiry = now()->addDays($days)->timezone('America/Sao_Paulo')->format('d/m/Y H:i');

        $this->table(
            ['Email', 'Nome', 'Acesso até'],
            $lifetime->map(fn (User $user) => [$user->email, $user->name, $newExpiry])->values()
        );

        if (!$this->option('force') && !$this->confirm('Revogar a assinatura vitalícia dos usuários acima?')) {
            $this->warn('Cancelado. Nada foi alterado.');
            return self::SUCCESS;
        }

        foreach ($lifetime as $user) {
            $subscriptions->revokeLifetime($user, $days);

            $this->info("Vitalícia revogada: {$user->email} (acesso até {$newExpiry})");
            Log::info('Assinatura vitalícia revogada', ['user_id' => $user->id, 'email' => $user->email, 'days' => $days]);
        }

        return $hasProblem ? self::FAILURE : self::SUCCESS;
    }
}
