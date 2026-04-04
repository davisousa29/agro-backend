<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultorProfile;
use App\Models\FazendeiroProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    // ── Retorna o perfil do usuário autenticado ────────────────────────────────

    public function show()
    {
        $user = auth()->user();

        $perfil = $this->getPerfil($user);

        return response()->json([
            'user'   => $user,
            'perfil' => $perfil,
        ]);
    }

    // ── Cria ou atualiza o perfil ─────────────────────────────────────────────

    public function save(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'consultor') {
            return $this->saveConsultor($request, $user);
        }

        return $this->saveFazendeiro($request, $user);
    }

    // ── Perfil do consultor ───────────────────────────────────────────────────

    private function saveConsultor(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'crea_number'      => 'nullable|string|max:20',
            'specialization'   => 'nullable|string|max:255',
            'bio'              => 'nullable|string',
            'location_state'   => 'nullable|string|size:2',
            'location_city'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $perfil = ConsultorProfile::firstOrNew(['user_id' => $user->id]);

        $perfil->fill([
            'user_id'        => $user->id,
            'crea_number'    => $request->crea_number,
            'specialization' => $request->specialization,
            'bio'            => $request->bio,
            'location_state' => $request->location_state,
            'location_city'  => $request->location_city,
        ]);

        $perfil->save();

        return response()->json([
            'message' => 'Perfil salvo com sucesso.',
            'perfil'  => $perfil,
        ]);
    }

    // ── Perfil do fazendeiro ──────────────────────────────────────────────────

    private function saveFazendeiro(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj'       => 'nullable|string|max:18',
            'location_state' => 'nullable|string|size:2',
            'location_city'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $perfil = FazendeiroProfile::firstOrNew(['user_id' => $user->id]);

        $perfil->fill([
            'user_id'        => $user->id,
            'cpf_cnpj'       => $request->cpf_cnpj,
            'location_state' => $request->location_state,
            'location_city'  => $request->location_city,
        ]);

        $perfil->save();

        return response()->json([
            'message' => 'Perfil salvo com sucesso.',
            'perfil'  => $perfil,
        ]);
    }

    // ── Helper — retorna o perfil correto pelo role ───────────────────────────

    private function getPerfil($user)
    {
        if ($user->role === 'consultor') {
            return ConsultorProfile::where('user_id', $user->id)->first();
        }

        return FazendeiroProfile::where('user_id', $user->id)->first();
    }
}
