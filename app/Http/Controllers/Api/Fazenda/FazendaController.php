<?php

namespace App\Http\Controllers\Api\Fazenda;

use App\Http\Controllers\Controller;
use App\Models\Fazenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FazendaController extends Controller
{
    // ── Lista todas as fazendas do fazendeiro autenticado ─────────────────────

    public function index()
    {
        $user = auth()->user();

        $fazendas = Fazenda::where('fazendeiro_id', $user->id)->get();

        return response()->json([
            'fazendas' => $fazendas,
        ]);
    }

    // ── Cria uma nova fazenda ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'fazendeiro') {
            return response()->json([
                'message' => 'Apenas fazendeiros podem cadastrar fazendas.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'area_hectares'    => 'nullable|numeric|min:0',
            'address'          => 'nullable|string|max:255',
            'inscricao_estadual' => 'nullable|string|max:20',
            'city'             => 'nullable|string|max:255',
            'state'            => 'nullable|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fazenda = Fazenda::create([
            'fazendeiro_id'    => $user->id,
            'name'             => $request->name,
            'area_hectares'    => $request->area_hectares,
            'address'          => $request->address,
            'inscricao_estadual' => $request->inscricao_estadual,
            'city'             => $request->city,
            'state'            => $request->state,
        ]);

        return response()->json([
            'message' => 'Fazenda cadastrada com sucesso.',
            'fazenda' => $fazenda,
        ], 201);
    }

    // ── Exibe uma fazenda específica ──────────────────────────────────────────

    public function show($id)
    {
        $user = auth()->user();

        $fazenda = Fazenda::where('fazendeiro_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'fazenda' => $fazenda,
        ]);
    }

    // ── Atualiza uma fazenda ──────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $fazenda = Fazenda::where('fazendeiro_id', $user->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'                  => 'sometimes|string|max:255',
            'area_hectares'         => 'nullable|numeric|min:0',
            'address'               => 'nullable|string|max:255',
            'inscricao_estadual'    => 'nullable|string|max:20',
            'city'                  => 'nullable|string|max:255',
            'state'                 => 'nullable|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fazenda->update($request->only([
            'name',
            'area_hectares',
            'address',
            'inscricao_estadual',
            'city',
            'state',
        ]));

        return response()->json([
            'message' => 'Fazenda atualizada com sucesso.',
            'fazenda' => $fazenda,
        ]);
    }

    // ── Remove uma fazenda ────────────────────────────────────────────────────

    public function destroy($id)
    {
        $user = auth()->user();

        $fazenda = Fazenda::where('fazendeiro_id', $user->id)
            ->findOrFail($id);

        $fazenda->delete();

        return response()->json([
            'message' => 'Fazenda removida com sucesso.',
        ]);
    }
}
