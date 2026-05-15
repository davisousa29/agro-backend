<?php

namespace App\Http\Controllers\Api\Racao;

use App\Http\Controllers\Controller;
use App\Models\CoeficienteNutricional;
use App\Models\Raca;
use App\Models\CategoriaAnimal;
use App\Models\SistemaProducao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExigenciaController extends Controller
{
    public function calcular(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'especie_id'   => 'required|uuid|exists:especies,id',
            'raca_id'      => 'required|uuid|exists:racas,id',
            'categoria_id' => 'required|uuid|exists:categorias_animais,id',
            'sistema_id'   => 'required|uuid|exists:sistemas_producao,id',
            'peso_inicial' => 'required|numeric|min:1',
            'peso_final'   => 'required|numeric|min:1|gte:peso_inicial',
            'gmd'          => 'required|numeric|min:0.1|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pesoInicial = (float) $request->peso_inicial;
        $pesoFinal   = (float) $request->peso_final;
        $pesoMedio   = ($pesoInicial + $pesoFinal) / 2;
        $GMD         = (float) $request->gmd;

        // Busca dados da raça, categoria e sistema
        $raca      = Raca::find($request->raca_id);
        $categoria = CategoriaAnimal::find($request->categoria_id);
        $sistema   = SistemaProducao::find($request->sistema_id);

        // Busca coeficientes
        $coefs = CoeficienteNutricional::where('especie_id',   $request->especie_id)
            ->where('raca_id',      $request->raca_id)
            ->where('categoria_id', $request->categoria_id)
            ->where('sistema_id',   $request->sistema_id)
            ->where('ativo', true)
            ->get()
            ->keyBy('nutriente');

        if ($coefs->isEmpty()) {
            return response()->json([
                'message' => 'Coeficientes nutricionais não encontrados para essa combinação.',
            ], 404);
        }

        // ── 1. PCJ (Peso Corporal em Jejum) ──────────────────────────────────
        // PCJ = PV × 0.96 (98% do peso vivo para bovinos em pasto)
        $PCJ = $pesoMedio * 0.96;

        // ── 2. PCVZ (Peso de Corpo Vazio) ────────────────────────────────────
        // PCVZ = PCJ × coef_a (varia por sistema)
        // Pasto: 0.851, Confinamento: 0.891
        $sistemaIsPasto = stripos($sistema->nome, 'pasto') !== false ||
            stripos($sistema->nome, 'semi') !== false;
        $coefPCVZ = $sistemaIsPasto ? 0.851 : 0.891;
        $PCVZ = $PCJ * $coefPCVZ;

        // ── 3. GPCVZ (Ganho de Peso de Corpo Vazio) ──────────────────────────
        $GPCVZ = $GMD * 0.96;

        // ── 4. PCVZeq — BR-CORTE 2016 ────────────────────────────────────────
        // Formula BR-CORTE 2016: PCVZeq = coef_a × PCVZ^coef_b
        // Os coeficientes variam por raça, sexo e sistema
        // Valores extraídos da planilha BR-CORTE 2016
        $isMacho     = $categoria->sexo === 'macho';
        $isCastrado  = $categoria->castrado;
        $racaNome    = strtolower($raca->nome);

        // Coeficientes PCVZeq BR-CORTE 2016 por raça/sexo/sistema
        // Fonte: Planilha BR-CORTE 2016 - Benedeti et al.
        if ($sistemaIsPasto) {
            if (str_contains($racaNome, 'leite')) {
                // Cruzado Leite - Pasto
                if (!$isMacho) $PCVZeq = 1.4773 * pow($PCVZ, 0.7722); // Fêmea
                elseif ($isCastrado) $PCVZeq = 1.3686 * pow($PCVZ, 0.7722); // Castrado
                else $PCVZeq = 1.2687 * pow($PCVZ, 0.7722); // Inteiro
            } elseif (str_contains($racaNome, 'cruzado')) {
                // Cruzado Corte - Pasto
                if (!$isMacho) $PCVZeq = 1.5566 * pow($PCVZ, 0.7722);
                elseif ($isCastrado) $PCVZeq = 1.4432 * pow($PCVZ, 0.7722);
                else $PCVZeq = 1.3380 * pow($PCVZ, 0.7722);
            } else {
                // Zebuíno - Pasto
                if (!$isMacho) $PCVZeq = 1.6474 * pow($PCVZ, 0.7722);
                elseif ($isCastrado) $PCVZeq = 1.5270 * pow($PCVZ, 0.7722);
                else $PCVZeq = 1.4153 * pow($PCVZ, 0.7722);
            }
        } else {
            // Confinamento
            if (str_contains($racaNome, 'leite')) {
                if (!$isMacho) $PCVZeq = 1.3109 * pow($PCVZ, 0.7722);
                elseif ($isCastrado) $PCVZeq = 1.2150 * pow($PCVZ, 0.7722);
                else $PCVZeq = 1.1263 * pow($PCVZ, 0.7722);
            } elseif (str_contains($racaNome, 'cruzado')) {
                if (!$isMacho) $PCVZeq = 1.4138 * pow($PCVZ, 0.7722);
                elseif ($isCastrado) $PCVZeq = 1.3101 * pow($PCVZ, 0.7722);
                else $PCVZeq = 1.2145 * pow($PCVZ, 0.7722);
            } else {
                if (!$isMacho) $PCVZeq = 1.5302 * pow($PCVZ, 0.7722);
                elseif ($isCastrado) $PCVZeq = 1.4181 * pow($PCVZ, 0.7722);
                else $PCVZeq = 1.3145 * pow($PCVZ, 0.7722);
            }
        }

        // ── 5. ELm (Energia Líquida Mantença) ────────────────────────────────
        // ELm = 0.047 × PCVZeq (BR-CORTE 2016, igual para todos)
        $ELm = 0.047 * $PCVZeq;

        // ── 6. ELg (Energia Líquida Ganho) ───────────────────────────────────
        // ELg = coef_a × PCVZeq^0.75 × GPCVZ^coef_b
        $coefELg = $coefs->get('ELg');
        if ($coefELg) {
            $a = (float) $coefELg->coef_a;
            $b = (float) $coefELg->coef_b;
            $ELg = $a * pow($PCVZeq, 0.75) * pow($GPCVZ, $b);
        } else {
            $ELg = 0.0557 * pow($PCVZeq, 0.75) * pow($GPCVZ, 0.4775);
        }

        // ── 7. CMS (Consumo de Matéria Seca) ─────────────────────────────────
        // BR-CORTE 2016 usa tabela lookup — aproximamos com equação de regressão
        // CMS = (ELm + ELg) / EL_concentrado_medio
        // Mas para bovinos, a equação mais precisa do BR-CORTE é:
        // CMS = [ELm + ELg] / (ELm_dieta) onde ELm_dieta ≈ 1.25 Mcal/kg MS (pasto)
        // ou via equação: CMS = 0.1229 × PV^0.6096 (Moe et al. adaptado)
        // Usamos a equação validada pelo BR-CORTE 2016:
        $EL_total = $ELm + $ELg;
        if ($sistemaIsPasto) {
            // Pasto: concentrado energético médio ~1.25 Mcal EL/kg MS
            $CMS = $EL_total / 1.25;
        } else {
            // Confinamento: ~1.55 Mcal EL/kg MS
            $CMS = $EL_total / 1.55;
        }

        // ── 8. NDT ───────────────────────────────────────────────────────────
        // NDT = EL_total / 1.65 (fator padrão para bovinos)
        $NDT = $EL_total / 1.65;

        // ── 9. Proteína Bruta ─────────────────────────────────────────────────
        // PB = CMS × 0.12 × 1000 (12% da MS como estimativa inicial)
        $PB = $CMS * 0.12 * 1000;

        // ── 10. PDR ──────────────────────────────────────────────────────────
        $PDR_pct = $sistemaIsPasto ? 65.49 : 62.03;
        $PDR = $PB * ($PDR_pct / 100);

        // ── 11. Ca e P ───────────────────────────────────────────────────────
        $Ca = $CMS * 4.0;
        $P  = $CMS * 2.8;

        return response()->json([
            'inputs' => [
                'peso_inicial' => $pesoInicial,
                'peso_final'   => $pesoFinal,
                'peso_medio'   => round($pesoMedio, 2),
                'gmd'          => $GMD,
                'raca'         => $raca->nome,
                'categoria'    => $categoria->nome,
                'sistema'      => $sistema->nome,
            ],
            'intermediarios' => [
                'PCJ'    => round($PCJ, 3),
                'PCVZ'   => round($PCVZ, 3),
                'PCVZeq' => round($PCVZeq, 3),
                'GPCVZ'  => round($GPCVZ, 3),
            ],
            'exigencias' => [
                'ELm_mcal_dia' => round($ELm, 3),
                'ELg_mcal_dia' => round($ELg, 3),
                'EL_total_mcal'=> round($EL_total, 3),
                'CMS_kg_dia'   => round($CMS, 3),
                'NDT_kg_dia'   => round($NDT, 3),
                'PB_g_dia'     => round($PB, 2),
                'PDR_g_dia'    => round($PDR, 2),
                'Ca_g_dia'     => round($Ca, 2),
                'P_g_dia'      => round($P, 2),
            ],
            'referencia' => 'BR-CORTE 2016',
        ]);
    }
}
