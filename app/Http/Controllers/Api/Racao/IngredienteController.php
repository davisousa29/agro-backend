<?php

namespace App\Http\Controllers\Api\Racao;

use App\Http\Controllers\Controller;
use App\Models\Ingrediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IngredienteController extends Controller
{
    // ── Lista todos os ingredientes com filtros opcionais ─────────────────────

    public function index(Request $request)
    {
        $query = Ingrediente::where('ativo', true);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('grupo')) {
            $query->where('grupo', $request->grupo);
        }

        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%');
        }

        $ingredientes = $query->orderBy('nome')->get();

        return response()->json([
            'ingredientes' => $ingredientes,
        ]);
    }

    // ── Exibe um ingrediente específico ───────────────────────────────────────

    public function show($id)
    {
        $ingrediente = Ingrediente::where('ativo', true)->findOrFail($id);

        return response()->json([
            'ingrediente' => $ingrediente,
        ]);
    }

    // ── Cria um ingrediente personalizado ─────────────────────────────────────

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome'      => 'required|string|max:255',
            'tipo'      => 'required|in:concentrado,volumoso,mineral,aditivo',
            'grupo'     => 'nullable|string|max:100',
            'ms_pct'    => 'required|numeric|min:0|max:100',
            'ndt_pct'   => 'nullable|numeric|min:0|max:100',
            'pb_pct'    => 'nullable|numeric|min:0|max:300',
            'pdr_pct'   => 'nullable|numeric|min:0|max:100',
            'pndr_pct'  => 'nullable|numeric|min:0|max:100',
            'fdn_pct'   => 'nullable|numeric|min:0|max:100',
            'ee_pct'    => 'nullable|numeric|min:0|max:100',
            'ca_pct'    => 'nullable|numeric|min:0|max:100',
            'p_pct'     => 'nullable|numeric|min:0|max:100',
            'elm_mcal'  => 'nullable|numeric|min:0',
            'elg_mcal'  => 'nullable|numeric|min:0',
            'preco_kg'  => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $ingrediente = Ingrediente::create([
            ...$request->only([
                'nome', 'tipo', 'grupo',
                'ms_pct', 'ndt_pct', 'pb_pct', 'pdr_pct', 'pndr_pct',
                'fdn_pct', 'fda_pct', 'ee_pct',
                'ca_pct', 'p_pct', 'mg_pct', 'k_pct', 'na_pct', 's_pct',
                'elm_mcal', 'elg_mcal', 'ed_mcal', 'em_mcal',
                'preco_kg',
            ]),
            'fonte' => 'personalizado',
            'ativo' => true,
        ]);

        return response()->json([
            'message'     => 'Ingrediente criado com sucesso.',
            'ingrediente' => $ingrediente,
        ], 201);
    }

    // ── Atualiza o preço de um ingrediente ────────────────────────────────────

    public function atualizarPreco(Request $request, $id)
    {
        $ingrediente = Ingrediente::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'preco_kg' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $ingrediente->update(['preco_kg' => $request->preco_kg]);

        return response()->json([
            'message'     => 'Preço atualizado com sucesso.',
            'ingrediente' => $ingrediente,
        ]);
    }
}
