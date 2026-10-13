<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Apaga os dados de trabalho do consultor ao fim do período de graça.
 * A conta (usuário, perfil, 2FA, assinatura e pagamentos) é mantida.
 */
class WorkDataPurgeService
{
    // Tabela => coluna que liga o registro ao consultor.
    // Os filhos (racao_ingredientes, projecao_animais) saem junto pelo ON DELETE CASCADE.
    private const TABLES = [
        'programas_racao' => 'criado_por',
        'projecoes_venda' => 'criado_por',
        'contratos'       => 'consultor_id',
        'notificacoes'    => 'user_id',
    ];

    /**
     * Quantos registros seriam apagados (usado no --dry-run).
     */
    public function preview(User $user): array
    {
        return collect(self::TABLES)
            ->map(fn (string $column, string $table) => DB::table($table)->where($column, $user->id)->count())
            ->all();
    }

    /**
     * Apaga os dados de trabalho e marca a assinatura com data_purged_at.
     * Trava a linha e revalida antes: se pagou nesse meio-tempo, não apaga nada (retorna null).
     */
    public function purge(Subscription $subscription): ?array
    {
        return DB::transaction(function () use ($subscription) {
            $locked = Subscription::whereKey($subscription->id)->lockForUpdate()->first();

            if (
                !$locked
                || $locked->status !== 'expired'
                || $locked->data_purged_at !== null
                || !$locked->grace_ends_at
                || $locked->grace_ends_at->isFuture()
            ) {
                return null;
            }

            $removed = [];
            foreach (self::TABLES as $table => $column) {
                $removed[$table] = DB::table($table)->where($column, $locked->user_id)->delete();
            }

            $locked->update(['data_purged_at' => now()]);

            return $removed;
        });
    }
}
