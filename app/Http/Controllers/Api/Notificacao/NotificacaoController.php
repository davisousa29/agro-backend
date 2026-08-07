<?php

namespace App\Http\Controllers\Api\Notificacao;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use Illuminate\Http\Request;

class NotificacaoController extends Controller
{
    // ── Lista paginada de notificações (tela "ver todas") ─────────────────────
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Notificacao::where('user_id', $user->id);

        // Filtro por estado de leitura
        if ($request->filled('lida')) {
            $query->where('lida', $request->boolean('lida'));
        }

        // Ordenação (padrão: mais recentes)
        $ordem = $request->input('ordem', 'recentes');
        $query->orderBy('created_at', $ordem === 'antigos' ? 'asc' : 'desc');

        $notificacoes = $query->paginate(10);

        return response()->json($notificacoes);
    }

    // ── Últimas 3 (dropdown do sino) ──────────────────────────────────────────
    public function ultimas()
    {
        $user = auth()->user();

        $notificacoes = Notificacao::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'notificacoes' => $notificacoes,
        ]);
    }

    // ── Contagem de não-lidas (badge do sino) ─────────────────────────────────
    public function contarNaoLidas()
    {
        $user = auth()->user();

        $total = Notificacao::where('user_id', $user->id)
            ->where('lida', false)
            ->count();

        return response()->json([
            'nao_lidas' => $total,
        ]);
    }

    // ── Marca uma notificação como lida ───────────────────────────────────────
    public function marcarLida($id)
    {
        $user = auth()->user();

        $notificacao = Notificacao::where('user_id', $user->id)
            ->findOrFail($id);

        if (!$notificacao->lida) {
            $notificacao->update([
                'lida'    => true,
                'lida_em' => now(),
            ]);
        }

        return response()->json([
            'message'      => 'Notificação marcada como lida.',
            'notificacao'  => $notificacao,
        ]);
    }

    // ── Marca todas como lidas ────────────────────────────────────────────────
    public function marcarTodasLidas()
    {
        $user = auth()->user();

        Notificacao::where('user_id', $user->id)
            ->where('lida', false)
            ->update([
                'lida'    => true,
                'lida_em' => now(),
            ]);

        return response()->json([
            'message' => 'Todas as notificações foram marcadas como lidas.',
        ]);
    }
}
