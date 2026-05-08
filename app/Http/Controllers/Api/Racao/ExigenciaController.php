<?php

namespace App\Http\Controllers\Api\Racao;

use App\Http\Controllers\Controller;
use App\Models\CoeficienteNutricional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExigenciaController extends Controller
{
    // ── Calcula as exigências nutricionais do animal ───────────────────────────

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
        $gmd         = (float) $request->gmd;

        // Busca todos os coeficientes para essa combinação
        $coeficientes = CoeficienteNutricional::where('especie_id',   $request->especie_id)
            ->where('raca_id',      $request->raca_id)
            ->where('categoria_id', $request->categoria_id)
            ->where('sistema_id',   $request->sistema_id)
            ->where('ativo', true)
            ->get()
            ->keyBy('nutriente');

        if ($coeficientes->isEmpty()) {
            return response()->json([
                'message' => 'Coeficientes nutricionais não encontrados para essa combinação.',
            ], 404);
        }

        // ── Cálculos encadeados ───────────────────────────────────────────────

        // 1. PCJ (Peso Corporal em Jejum) — usa peso médio
        $PCJ = $pesoMedio * 0.96;

        // 2. PCVZ (Peso de Corpo Vazio)
        $PCVZ = $this->aplicarFormula($coeficientes->get('PCVZ'), $PCJ);

        // 3. PCVZeq (Peso Metabólico)
        $PCVZeq = $this->aplicarFormula($coeficientes->get('PCVZeq'), $PCVZ);

        // 4. GPCVZ (Ganho de Peso de Corpo Vazio)
        $GPCVZ = $gmd * 0.96;

        // 5. ELm (Energia Líquida de Manutenção) — Mcal/dia
        $ELm = $this->aplicarFormula($coeficientes->get('ELm'), $PCVZeq);

        // 6. ELg (Energia Líquida de Ganho) — Mcal/dia
        $ELg = $this->calcularELg($coeficientes->get('ELg'), $PCVZeq, $GPCVZ);

        // 7. CMS (Consumo de Matéria Seca) — kg/dia
        $CMS = $this->calcularCMS($coeficientes->get('CMS'), $pesoMedio);

        // 8. NDT (Nutrientes Digestíveis Totais) — kg/dia
        // NDT = (ELm + ELg) / 1.65 (fator de conversão médio)
        $NDT = ($ELm + $ELg) / 1.65;

        // 9. PB (Proteína Bruta) — g/dia
        // PB = CMS × 0.12 × 1000 (estimativa 12% da MS)
        $PB = $CMS * 0.12 * 1000;

        // 10. PDR (Proteína Degradável no Rúmen) — g/dia
        $PDR_pct = isset($coeficientes['PDR_pct'])
            ? (float) $coeficientes['PDR_pct']->coef_a
            : 65.0;
        $PDR = $PB * ($PDR_pct / 100);

        // 11. Ca e P — g/dia (estimativas baseadas no CMS)
        $Ca = $CMS * 4.0;   // ~4 g Ca/kg MS
        $P  = $CMS * 2.8;   // ~2.8 g P/kg MS

        return response()->json([
            'inputs' => [
                'peso_inicial' => $pesoInicial,
                'peso_final'   => $pesoFinal,
                'peso_medio'   => round($pesoMedio, 2),
                'gmd'          => $gmd,
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
                'EL_total_mcal'=> round($ELm + $ELg, 3),
                'CMS_kg_dia'   => round($CMS, 3),
                'NDT_kg_dia'   => round($NDT, 3),
                'PB_g_dia'     => round($PB, 2),
                'PDR_g_dia'    => round($PDR, 2),
                'Ca_g_dia'     => round($Ca, 2),
                'P_g_dia'      => round($P, 2),
            ],
            'referencia' => $coeficientes->first()->referencia ?? 'BR-CORTE 2016',
        ]);
    }

    // ── Helpers de cálculo ────────────────────────────────────────────────────

    private function aplicarFormula($coef, float $variavel): float
    {
        if (!$coef) return 0.0;

        $a = (float) $coef->coef_a;
        $b = (float) $coef->coef_b;
        $c = (float) $coef->coef_c;

        return match ($coef->formula_tipo) {
            'linear'      => $a * $variavel + $c,
            'exponencial' => $a * pow($variavel, $b) + $c,
            default       => $a * $variavel + $c,
        };
    }

    private function calcularELg($coef, float $PCVZeq, float $GPCVZ): float
    {
        if (!$coef) return 0.0;

        $a = (float) $coef->coef_a;
        $b = (float) $coef->coef_b;

        // ELg = a × PCVZeq^0.75 × GPCVZ^b
        return $a * pow($PCVZeq, 0.75) * pow($GPCVZ, $b);
    }

    private function calcularCMS($coef, float $PV): float
    {
        if (!$coef) {
            // Fallback: CMS = 2.2% do PV
            return $PV * 0.022;
        }

        $a = (float) $coef->coef_a;
        $b = (float) $coef->coef_b;
        $c = (float) $coef->coef_c;

        // CMS = a × PV^b × e^(c × PV)
        return $a * pow($PV, $b) * exp($c * $PV);
    }
}
