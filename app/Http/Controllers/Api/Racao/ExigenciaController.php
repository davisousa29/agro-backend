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
        $PV          = ($pesoInicial + $pesoFinal) / 2;
        $GMD         = (float) $request->gmd;

        // Busca dados de referência
        $raca      = Raca::find($request->raca_id);
        $categoria = CategoriaAnimal::find($request->categoria_id);
        $sistema   = SistemaProducao::find($request->sistema_id);

        // Busca todos os coeficientes do banco para essa combinação
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

        // ── Passo 1: PCJ ─────────────────────────────────────────────────────
        // PCJ = coef_a × PV (varia por raça)
        $coefPCJ = $coefs->get('PCJ');
        if (!$coefPCJ) {
            return response()->json(['message' => 'Coeficiente PCJ não encontrado.'], 404);
        }
        $PCJ = (float) $coefPCJ->coef_a * $PV;

        // ── Passo 2: PCVZ (Peso de Corpo Vazio) ──────────────────────────────
        // Usa coeficiente do banco: PCVZ = coef_a × PCJ
        $coefPCVZ = $coefs->get('PCVZ');
        if (!$coefPCVZ) {
            return response()->json(['message' => 'Coeficiente PCVZ não encontrado.'], 404);
        }
        $PCVZ = (float) $coefPCVZ->coef_a * $PCJ;

        // ── Passo 3: GPCVZ (Ganho de Peso de Corpo Vazio) ────────────────────
        // GPCVZ = GMD × 0.96 (padrão BR-CORTE)
        $GPCVZ = $GMD * 0.96;

        // ── Passo 4: PCVZeq ──────────────────────────────────────────────────
        // PCVZeq = coef_a × PCVZ (linear, varia por raça/categoria)
        $coefPCVZeq = $coefs->get('PCVZeq');
        if (!$coefPCVZeq) {
            return response()->json(['message' => 'Coeficiente PCVZeq não encontrado.'], 404);
        }
        $PCVZeq = (float) $coefPCVZeq->coef_a * $PCVZ;

        // ── Passo 5: ELm ─────────────────────────────────────────────────────
        // ELm = 0.075 × PCVZ^0.75 (usa PCVZ, não PCVZeq!)
        $coefELm = $coefs->get('ELm');
        if (!$coefELm) {
            return response()->json(['message' => 'Coeficiente ELm não encontrado.'], 404);
        }
        $ELm = (float) $coefELm->coef_a * pow($PCVZ, (float) $coefELm->coef_b);

        // ── Passo 6: ELg ─────────────────────────────────────────────────────
        // ELg = coef_a × PCVZeq^0.75 × GPCVZ^coef_b
        $coefELg = $coefs->get('ELg');
        if (!$coefELg) {
            return response()->json(['message' => 'Coeficiente ELg não encontrado.'], 404);
        }
        $ELg = (float) $coefELg->coef_a
            * pow($PCVZeq, 0.75)
            * pow($GPCVZ, (float) $coefELg->coef_b);

        // ── Passo 7: CMS (Consumo de Matéria Seca) ───────────────────────────
        // Usa coeficiente do banco: CMS = coef_a × PV^coef_b × e^(coef_c × PV)
        $coefCMS = $coefs->get('CMS');
        if (!$coefCMS) {
            return response()->json(['message' => 'Coeficiente CMS não encontrado.'], 404);
        }
        $CMS = (float) $coefCMS->coef_a
            * pow($PV, (float) $coefCMS->coef_b)
            * exp((float) $coefCMS->coef_c * $PV);

        // ── Passo 8: NDT ─────────────────────────────────────────────────────
        // NDT = EL_total / 1.9670 (fator BR-CORTE 2016)
        $EL_total = $ELm + $ELg;
        $NDT = $EL_total / 1.9670;

        // ── Passo 9: PB (Proteína Bruta) ─────────────────────────────────────
        // PBmic = NDT × Ef.Mic (Ef.Mic = 118.929 g/kg NDT — BR-CORTE 2016)
        // PB = PBmic / (PDR_pct / 100)
        $coefPDR = $coefs->get('PDR_pct');
        $PDR_pct = $coefPDR ? (float) $coefPDR->coef_a : 66.44;
        $PBmic = $NDT * 118.929;
        $PB    = $PBmic / ($PDR_pct / 100);
        $PDR   = $PBmic; // PDR = PBmic (BR-CORTE 2016)

        // ── Passo 10: Ca e P ─────────────────────────────────────────────────
        // Ca = Cam + Cag
        // Cam = 0.0206 × PV (mantença)
        // Cag = coef_raca × GPCVZ (ganho — varia por raça)
        $coefCag = $coefs->get('Cag');
        $coefPg  = $coefs->get('Pg');

        $Cam = 0.0206 * $PV;
        $Cag = $coefCag ? (float) $coefCag->coef_a * $GPCVZ : 19.62 * $GPCVZ;
        $Ca  = $Cam + $Cag;

        // P = Pm + Pg
        // Pm = 0.0199 × PV (mantença)
        $Pm = 0.0199 * $PV;
        $Pg = $coefPg ? (float) $coefPg->coef_a * $GPCVZ : 9.32 * $GPCVZ;
        $P  = $Pm + $Pg;

        return response()->json([
            'inputs' => [
                'peso_inicial' => $pesoInicial,
                'peso_final'   => $pesoFinal,
                'peso_medio'   => round($PV, 2),
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
                'ELm_mcal_dia'  => round($ELm, 3),
                'ELg_mcal_dia'  => round($ELg, 3),
                'EL_total_mcal' => round($EL_total, 3),
                'CMS_kg_dia'    => round($CMS, 3),
                'NDT_kg_dia'    => round($NDT, 3),
                'PB_g_dia'      => round($PB, 2),
                'PDR_g_dia'     => round($PDR, 2),
                'Ca_g_dia'      => round($Ca, 2),
                'P_g_dia'       => round($P, 2),
            ],
            'referencia' => 'BR-CORTE 2016',
        ]);
    }
}
