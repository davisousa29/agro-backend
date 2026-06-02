<?php

namespace App\Http\Controllers\Api\Projecao;

use App\Http\Controllers\Controller;
use App\Models\ProjecaoVenda;
use App\Models\ProjecaoAnimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjecaoVendaController extends Controller
{
    // ── Lista projeções do usuário ────────────────────────────────────────────
    public function index()
    {
        $user = auth()->user();

        $projecoes = ProjecaoVenda::where('criado_por', $user->id)
            ->with('contrato.fazenda', 'contrato.fazendeiro')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['projecoes' => $projecoes]);
    }

    // ── Exibe uma projeção com animais ────────────────────────────────────────
    public function show($id)
    {
        $user = auth()->user();

        $projecao = ProjecaoVenda::where('criado_por', $user->id)
            ->with('animais', 'contrato.fazenda', 'contrato.fazendeiro')
            ->findOrFail($id);

        return response()->json(['projecao' => $projecao]);
    }

    // ── Salva uma nova projeção com animais ───────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome'           => 'required|string|max:255',
            'modalidade'     => 'required|in:arroba,kg,cabeca',
            'preco_unitario' => 'required|numeric|min:0',
            'contrato_id'    => 'nullable|uuid|exists:contratos,id',
            'observacoes'    => 'nullable|string',
            'animais'        => 'required|array|min:1',
            'animais.*.numero_animal' => 'nullable|string|max:50',
            'animais.*.prenhez'       => 'required|boolean',
            'animais.*.peso_kg'       => 'nullable|numeric|min:0',
            'animais.*.quantidade'    => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user          = auth()->user();
        $modalidade    = $request->modalidade;
        $preco         = (float) $request->preco_unitario;
        $animaisInput  = $request->animais;

        // ── Calcular valores de cada animal ───────────────────────────────────
        $animaisCalculados = [];
        $totalAnimais    = 0;
        $totalVazias     = 0;
        $totalPrenhas    = 0;
        $totalPesoKg     = 0;
        $totalArrobas    = 0;
        $totalValor      = 0;
        $pesosVazias     = [];

        foreach ($animaisInput as $index => $animal) {
            $qty     = (int) $animal['quantidade'];
            $pesoKg  = isset($animal['peso_kg']) ? (float) $animal['peso_kg'] : null;
            $prenhez = (bool) $animal['prenhez'];

            // Calcula por animal unitário (depois multiplica pela quantidade)
            $arrobas       = null;
            $valorUnitario = 0;

            switch ($modalidade) {
                case 'arroba':
                    if ($pesoKg !== null) {
                        $arrobas       = $pesoKg / 30;
                        $valorUnitario = $arrobas * $preco;
                    }
                    break;
                case 'kg':
                    if ($pesoKg !== null) {
                        $valorUnitario = $pesoKg * $preco;
                    }
                    break;
                case 'cabeca':
                    $valorUnitario = $preco;
                    break;
            }

            $valorTotal = $valorUnitario * $qty;

            $animaisCalculados[] = [
                'numero_animal'  => $animal['numero_animal'] ?? null,
                'prenhez'        => $prenhez,
                'peso_kg'        => $pesoKg,
                'quantidade'     => $qty,
                'arrobas'        => $arrobas,
                'valor_unitario' => round($valorUnitario, 2),
                'valor_total'    => round($valorTotal, 2),
                'ordem'          => $index + 1,
            ];

            // Acumula totais (considerando quantidade)
            $totalAnimais += $qty;
            $totalValor   += $valorTotal;

            if ($pesoKg !== null) {
                $totalPesoKg  += $pesoKg * $qty;
                if ($arrobas !== null) {
                    $totalArrobas += $arrobas * $qty;
                }
            }

            if ($prenhez) {
                $totalPrenhas += $qty;
            } else {
                $totalVazias += $qty;
                if ($pesoKg !== null) {
                    for ($i = 0; $i < $qty; $i++) {
                        $pesosVazias[] = $pesoKg;
                    }
                }
            }
        }

        $mediaPesoVazias = count($pesosVazias) > 0
            ? array_sum($pesosVazias) / count($pesosVazias)
            : null;

        // ── Salva no banco ────────────────────────────────────────────────────
        $projecao = DB::transaction(function () use (
            $request, $user, $animaisCalculados,
            $totalAnimais, $totalVazias, $totalPrenhas,
            $totalPesoKg, $totalArrobas, $mediaPesoVazias, $totalValor
        ) {
            $projecao = ProjecaoVenda::create([
                'criado_por'        => $user->id,
                'contrato_id'       => $request->contrato_id,
                'nome'              => $request->nome,
                'modalidade'        => $request->modalidade,
                'preco_unitario'    => $request->preco_unitario,
                'status'            => 'finalizado',
                'total_animais'     => $totalAnimais,
                'total_vazias'      => $totalVazias,
                'total_prenhas'     => $totalPrenhas,
                'total_peso_kg'     => $totalPesoKg,
                'total_arrobas'     => $totalArrobas > 0 ? $totalArrobas : null,
                'media_peso_vazias' => $mediaPesoVazias,
                'valor_total'       => $totalValor,
                'observacoes'       => $request->observacoes,
            ]);

            foreach ($animaisCalculados as $animal) {
                ProjecaoAnimal::create([
                    'projecao_id' => $projecao->id,
                    ...$animal,
                ]);
            }

            return $projecao;
        });

        return response()->json([
            'message'  => 'Projeção salva com sucesso.',
            'projecao' => $projecao->load('animais'),
        ], 201);
    }

    // ── Exclui uma projeção ───────────────────────────────────────────────────
    public function destroy($id)
    {
        $user = auth()->user();

        $projecao = ProjecaoVenda::where('criado_por', $user->id)->findOrFail($id);
        $projecao->delete();

        return response()->json(['message' => 'Projeção excluída com sucesso.']);
    }

    // ── Gera PDF da projeção ──────────────────────────────────────────────────
    public function pdf($id)
    {
        $user = auth()->user();

        $projecao = ProjecaoVenda::where('criado_por', $user->id)
            ->with('animais', 'contrato.fazenda', 'contrato.fazendeiro')
            ->findOrFail($id);

        $modalidadeLabel = [
            'arroba' => 'Arroba (@)',
            'kg'     => 'Quilograma (kg)',
            'cabeca' => 'Cabeça',
        ][$projecao->modalidade] ?? $projecao->modalidade;

        $html = view('pdf.projecao-venda', compact('projecao', 'modalidadeLabel'))->render();

        return response()->json([
            'html'     => $html,
            'projecao' => $projecao,
        ]);
    }
}
