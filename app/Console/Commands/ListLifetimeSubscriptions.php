<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ListLifetimeSubscriptions extends Command
{
    protected $signature = 'subscriptions:list-lifetime';

    protected $description = 'Lista os usuários com assinatura vitalícia';

    public function handle(): int
    {
        $lifetime = Subscription::with('user')
            ->where('status', 'lifetime')
            ->orderBy('starts_at')
            ->get();

        if ($lifetime->isEmpty()) {
            $this->info('Nenhum usuário com assinatura vitalícia.');
            return self::SUCCESS;
        }

        $this->table(
            ['Email', 'Nome', 'Cliente desde'],
            $lifetime->map(fn (Subscription $subscription) => [
                $subscription->user?->email ?? $subscription->user_id,
                $subscription->user?->name ?? '-',
                $subscription->starts_at?->timezone('America/Sao_Paulo')->format('d/m/Y'),
            ])
        );

        $this->info("Total: {$lifetime->count()}");

        return self::SUCCESS;
    }
}
