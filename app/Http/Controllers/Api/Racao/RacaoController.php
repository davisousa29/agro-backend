<?php

namespace App\Http\Controllers\Api\Racao;

use App\Http\Controllers\Controller;
use App\Models\ProgramaRacao;
use App\Models\RacaoIngrediente;
use App\Models\Ingrediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RacaoController extends Controller
{
    // ── Lista programas de ração do contrato ──────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        $programas = ProgramaRacao::whereHas('contrato', function ($q) use ($user) {
            $q->where('consultor_id', $user->id)
                ->orWhere('fazendeiro_id', $user->id);
        })
            ->with(['especie', 'raca', 'categoria', 'sistema'])
            ->when($request->filled('contrato_id'), fn($q) =>
            $q->where('contrato_id', $request->contrato_id)
            )
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'programas' => $programas,
        ]);
    }

    // ── Exibe um programa completo com ingredientes ───────────────────────────

    public function show($id)
    {
        $user = auth()->user();

        $programa = ProgramaRacao::whereHas('contrato', function ($q) use ($user) {
            $q->where('consultor_id', $user->id)
                ->orWhere('fazendeiro_id', $user->id);
        })
            ->with([
                'especie', 'raca', 'categoria', 'sistema',
                'ingredientes.ingrediente',
                'contrato',
            ])
            ->findOrFail($id);

        return response()->json([
            'programa' => $programa,
        ]);
    }

    // ── Cria um novo programa de ração ────────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'contrato_id'      => 'required|uuid|exists:contratos,id',
            'nome'             => 'required|string|max:255',
            'especie_id'       => 'required|uuid|exists:especies,id',
            'raca_id'          => 'required|uuid|exists:racas,id',
            'categoria_id'     => 'required|uuid|exists:categorias_animais,id',
            'sistema_id'       => 'required|uuid|exists:sistemas_producao,id',
            'peso_inicial_kg'  => 'required|numeric|min:1',
            'peso_final_kg'    => 'required|numeric|min:1|gte:peso_inicial_kg',
            'gmd_kg'           => 'required|numeric|min:0.1|max:3',
            'quantidade_animais' => 'required|integer|min:1',
            'tipo_aplicacao'   => 'required|in:individual,lote',
            'identificacao_animal' => 'nullable|string|max:100',
            'data_inicio'      => 'nullable|date',
            'data_fim'         => 'nullable|date|after_or_equal:data_inicio',
            'observacoes'      => 'nullable|string',

            // Exigências calculadas (vindas do ExigenciaController)
            'exig_cms_kg'      => 'nullable|numeric',
            'exig_ndt_kg'      => 'nullable|numeric',
            'exig_pb_g'        => 'nullable|numeric',
            'exig_elm_mcal'    => 'nullable|numeric',
            'exig_elg_mcal'    => 'nullable|numeric',
            'exig_ca_g'        => 'nullable|numeric',
            'exig_p_g'         => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pesoMedio = ($request->peso_inicial_kg + $request->peso_final_kg) / 2;

        $programa = ProgramaRacao::create([
            ...$request->only([
                'contrato_id', 'nome', 'status',
                'especie_id', 'raca_id', 'categoria_id', 'sistema_id',
                'peso_inicial_kg', 'peso_final_kg', 'gmd_kg',
                'quantidade_animais', 'tipo_aplicacao',
                'identificacao_animal', 'lote_id',
                'exig_cms_kg', 'exig_ndt_kg', 'exig_pb_g',
                'exig_elm_mcal', 'exig_elg_mcal', 'exig_ca_g', 'exig_p_g',
                'data_inicio', 'data_fim', 'observacoes',
            ]),
            'criado_por'              => $user->id,
            'peso_medio_kg'           => $pesoMedio,
            'status'                  => 'rascunho',
            'referencia_nutricional'  => 'BR-CORTE 2016',
        ]);

        return response()->json([
            'message'  => 'Programa de ração criado com sucesso.',
            'programa' => $programa->load(['especie', 'raca', 'categoria', 'sistema']),
        ], 201);
    }

    // ── Adiciona ou atualiza ingredientes do programa ─────────────────────────

    public function salvarIngredientes(Request $request, $id)
    {
        $user = auth()->user();

        $programa = ProgramaRacao::whereHas('contrato', function ($q) use ($user) {
            $q->where('consultor_id', $user->id);
        })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ingredientes'                  => 'required|array|min:1',
            'ingredientes.*.ingrediente_id' => 'required|uuid|exists:ingredientes,id',
            'ingredientes.*.tipo'           => 'required|in:volumoso_principal,volumoso_suplementar,concentrado',
            'ingredientes.*.proporcao_pct'  => 'required|numeric|min:0|max:100',
            'ingredientes.*.preco_kg_local' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $programa) {
            // Remove ingredientes anteriores
            RacaoIngrediente::where('programa_id', $programa->id)->delete();

            $custoTotalDia = 0;
            $ordem = 1;

            foreach ($request->ingredientes as $item) {
                $ingrediente = Ingrediente::findOrFail($item['ingrediente_id']);
                $proporcao   = (float) $item['proporcao_pct'];
                $precoKg     = $item['preco_kg_local'] ?? $ingrediente->preco_kg ?? 0;

                // Consumo matéria natural = CMS do programa / MS% × proporção
                $cms         = (float) ($programa->exig_cms_kg ?? 0);
                $consumoMS   = $cms * ($proporcao / 100);
                $consumoMN   = $ingrediente->ms_pct > 0
                    ? $consumoMS / ($ingrediente->ms_pct / 100)
                    : 0;

                // Contribuições nutricionais
                $contribNDT  = $consumoMS * ($ingrediente->ndt_pct / 100);
                $contribPB   = $consumoMS * ($ingrediente->pb_pct / 100) * 1000;
                $contribPDR  = $contribPB * ($ingrediente->pdr_pct / 100);
                $contribELm  = $consumoMS * $ingrediente->elm_mcal;
                $contribELg  = $consumoMS * $ingrediente->elg_mcal;
                $contribCa   = $consumoMS * ($ingrediente->ca_pct / 100) * 1000;
                $contribP    = $consumoMS * ($ingrediente->p_pct / 100) * 1000;

                // Custo
                $custoAnimalDia = $consumoMN * $precoKg;
                $custoTotalDia += $custoAnimalDia;

                RacaoIngrediente::create([
                    'programa_id'       => $programa->id,
                    'ingrediente_id'    => $ingrediente->id,
                    'tipo'              => $item['tipo'],
                    'ordem'             => $ordem++,
                    'proporcao_pct'     => $proporcao,
                    'preco_kg_local'    => $precoKg,
                    'consumo_mn_kg'     => round($consumoMN, 4),
                    'consumo_ms_kg'     => round($consumoMS, 4),
                    'contrib_ndt_kg'    => round($contribNDT, 4),
                    'contrib_pb_g'      => round($contribPB, 2),
                    'contrib_pdr_g'     => round($contribPDR, 2),
                    'contrib_elm_mcal'  => round($contribELm, 4),
                    'contrib_elg_mcal'  => round($contribELg, 4),
                    'contrib_ca_g'      => round($contribCa, 2),
                    'contrib_p_g'       => round($contribP, 2),
                    'custo_animal_dia'  => round($custoAnimalDia, 4),
                ]);
            }

            // Atualiza custo total no programa
            $programa->update([
                'custo_animal_dia' => round($custoTotalDia, 4),
                'status'           => 'ativo',
            ]);
        });

        return response()->json([
            'message'  => 'Ingredientes salvos com sucesso.',
            'programa' => $programa->fresh()->load(['ingredientes.ingrediente']),
        ]);
    }

    // ── Encerra um programa ───────────────────────────────────────────────────

    public function encerrar($id)
    {
        $user = auth()->user();

        $programa = ProgramaRacao::whereHas('contrato', function ($q) use ($user) {
            $q->where('consultor_id', $user->id)
                ->orWhere('fazendeiro_id', $user->id);
        })
            ->findOrFail($id);

        $programa->update(['status' => 'encerrado']);

        return response()->json([
            'message'  => 'Programa encerrado com sucesso.',
            'programa' => $programa,
        ]);
    }
}
