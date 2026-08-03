<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FazendeiroPublicoResource;
use App\Models\User;
use Illuminate\Http\Request;

class BuscaController extends Controller
{
    // ── Busca fazendeiros por username ou localização ─────────────────────────

    public function fazendeiros(Request $request)
    {
        $username = trim($request->input('username', ''));
        $estado   = $request->input('estado');
        $cidade   = trim($request->input('cidade', ''));

        // ── Exige critério mínimo de busca ────────────────────────────────────
        // Pelo menos: 2 caracteres no username OU um estado OU cidade
        $temUsernameValido = strlen($username) >= 2;
        $temEstado         = !empty($estado);
        $temCidade         = strlen($cidade) >= 2;

        if (!$temUsernameValido && !$temEstado && !$temCidade) {
            return response()->json([
                'message' => 'Informe ao menos 2 caracteres no @ ou selecione um estado.',
                'data'    => [],
                'meta'    => [
                    'current_page' => 1,
                    'last_page'    => 1,
                    'total'        => 0,
                ],
            ], 422);
        }

        $query = User::where('role', 'fazendeiro')
            ->where('active', true)
            ->with(['fazendeiroProfile', 'fazendas']);

        if ($temUsernameValido) {
            $query->where('username', 'ilike', '%' . $username . '%');
        }

        if ($temEstado) {
            $query->whereHas('fazendeiroProfile', function ($q) use ($estado) {
                $q->where('location_state', $estado);
            });
        }

        if ($temCidade) {
            $query->whereHas('fazendeiroProfile', function ($q) use ($cidade) {
                $q->where('location_city', 'ilike', '%' . $cidade . '%');
            });
        }

        $fazendeiros = $query->paginate(10);

        return FazendeiroPublicoResource::collection($fazendeiros);
    }

    // ── Exibe o perfil público de um fazendeiro pelo username ─────────────────

    public function perfilFazendeiro($username)
    {
        $fazendeiro = User::where('username', $username)
            ->where('role', 'fazendeiro')
            ->where('active', true)
            ->with(['fazendeiroProfile', 'fazendas'])
            ->firstOrFail();

        return new FazendeiroPublicoResource($fazendeiro);
    }
}
