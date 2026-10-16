<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrantLifetimeSubscription extends Command
{
    protected $signature = 'subscriptions:grant-lifetime
                            {emails* : Um ou mais emails, separados por espaço}
                            {--force : Concede sem pedir confirmação}';

    protected $description = 'Concede assinatura vitalícia (cortesia) a um ou mais usuários';

    public function handle(SubscriptionService $subscriptions): int
    {
        // Mesmo padrão do cadastro: email em minúsculas e sem espaços
        $emails = collect($this->argument('emails'))
            ->map(fn (string $email) => strtolower(trim($email)))
            ->unique();

        $users = User::with('subscription')
            ->whereIn('email', $emails)
            ->get()
            ->keyBy('email');

        $notFound = $emails->diff($users->keys());

        foreach ($notFound as $email) {
            $this->error("Usuário não encontrado: {$email}");
        }

        if ($users->isEmpty()) {
            return self::FAILURE;
        }

        $this->table(
            ['Email', 'Nome', 'Status atual'],
            $users->map(fn (User $user) => [
                $user->email,
                $user->name,
                $user->subscription?->status ?? 'sem assinatura',
            ])->values()
        );

        if (!$this->option('force') && !$this->confirm('Conceder assinatura vitalícia para os usuários acima?')) {
            $this->warn('Cancelado. Nada foi alterado.');
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $subscriptions->grantLifetime($user);

            $this->info("Vitalícia concedida: {$user->email}");
            Log::info('Assinatura vitalícia concedida', ['user_id' => $user->id, 'email' => $user->email]);
        }

        // Se algum email não foi encontrado, sinaliza falha para quem rodou perceber
        return $notFound->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
