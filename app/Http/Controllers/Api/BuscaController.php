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
        $query = User::where('role', 'fazendeiro')
            ->where('active', true)
            ->with(['fazendeiroProfile', 'fazendas']);

        // Busca por @username
        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        // Busca por estado
        if ($request->filled('estado')) {
            $query->whereHas('fazendeiroProfile', function ($q) use ($request) {
                $q->where('location_state', $request->estado);
            });
        }

        // Busca por cidade
        if ($request->filled('cidade')) {
            $query->whereHas('fazendeiroProfile', function ($q) use ($request) {
                $q->where('location_city', 'like', '%' . $request->cidade . '%');
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
