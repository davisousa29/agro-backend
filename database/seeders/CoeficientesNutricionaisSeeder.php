<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CoeficientesNutricionaisSeeder extends Seeder
{
    public function run(): void
    {
        $bovino = DB::table('especies')->where('nome', 'Bovino')->first();

        $zebuino      = DB::table('racas')->where('nome', 'Zebuíno')->first();
        $cruzadoCorte = DB::table('racas')->where('nome', 'Cruzado Corte')->first();
        $cruzadoLeite = DB::table('racas')->where('nome', 'Cruzado Leite')->first();

        $MNC  = DB::table('categorias_animais')->where('nome', 'Macho não castrado')->first();
        $MC   = DB::table('categorias_animais')->where('nome', 'Macho castrado')->first();
        $F    = DB::table('categorias_animais')->where('nome', 'Fêmea')->first();

        $pasto        = DB::table('sistemas_producao')->where('nome', 'Pasto')->where('especie_id', $bovino->id)->first();
        $confinamento = DB::table('sistemas_producao')->where('nome', 'Confinamento')->where('especie_id', $bovino->id)->first();

        $coefs = [];

        // ─────────────────────────────────────────────────────────────────────
        // NOTAS SOBRE AS FÓRMULAS (BR-CORTE 2016 - Benedeti et al.)
        //
        // PCJ  = PV × coef_a  (varia por raça)
        // PCVZ = PCJ × coef_a (varia por raça, categoria, sistema)
        // PCVZeq = PCVZ × coef_a (varia por raça e categoria)
        // ELm  = 0.075 × PCVZ^0.75 (igual para todos)
        // ELg  = coef_a × PCVZeq^0.75 × GPCVZ^0.9116 (varia por raça/cat/sistema)
        // CMS  = coef_a × PV^0.75 × e^(coef_c × PV) (varia por raça e sistema)
        // PDR  = PB × coef_a% (varia por sistema)
        // ─────────────────────────────────────────────────────────────────────

        foreach ([$pasto, $confinamento] as $sistema) {
            $isPasto = $sistema->nome === 'Pasto';

            // ── PCJ por raça ─────────────────────────────────────────────────
            // Zebuíno: PCJ = PV × 0.9724
            // Cruzado: PCJ = PV × 0.9758
            foreach ([
                         [$zebuino->id,      0.9724],
                         [$cruzadoCorte->id, 0.9758],
                         [$cruzadoLeite->id, 0.9758],
                     ] as [$racaId, $coefPCJ]) {
                foreach ([$MNC->id, $MC->id, $F->id] as $catId) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'PCJ', 'kg', 'linear', 'BR-CORTE 2016',
                        $coefPCJ, 1.0, 0.0, 'PV',
                        "PCJ = {$coefPCJ} × PV"
                    );
                }
            }

            // ── PCVZ por raça, categoria e sistema ───────────────────────────
            // Confinamento:
            //   Zebuíno MNC: 0.8768, MC: 0.8813, Fêmea: 0.8922
            //   Cruzado MNC: 0.8663, MC: 0.8744, Fêmea: 0.8888
            // Pasto (aproximação baseada no BR-CORTE 2016):
            //   Zebuíno MNC: 0.8510, MC: 0.8560, Fêmea: 0.8660
            //   Cruzado MNC: 0.8410, MC: 0.8490, Fêmea: 0.8610
            $coefsPCVZ = $isPasto ? [
                $zebuino->id      => [$MNC->id => 0.8510, $MC->id => 0.8560, $F->id => 0.8660],
                $cruzadoCorte->id => [$MNC->id => 0.8410, $MC->id => 0.8490, $F->id => 0.8610],
                $cruzadoLeite->id => [$MNC->id => 0.8410, $MC->id => 0.8490, $F->id => 0.8610],
            ] : [
                $zebuino->id      => [$MNC->id => 0.8768, $MC->id => 0.8813, $F->id => 0.8922],
                $cruzadoCorte->id => [$MNC->id => 0.8663, $MC->id => 0.8744, $F->id => 0.8888],
                $cruzadoLeite->id => [$MNC->id => 0.8663, $MC->id => 0.8744, $F->id => 0.8888],
            ];

            foreach ($coefsPCVZ as $racaId => $cats) {
                foreach ($cats as $catId => $coefPCVZ) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'PCVZ', 'kg', 'linear', 'BR-CORTE 2016',
                        $coefPCVZ, 1.0, 0.0, 'PCJ',
                        "PCVZ = {$coefPCVZ} × PCJ"
                    );
                }
            }

            // ── PCVZeq por raça e categoria ───────────────────────────────────
            // PCVZeq = PCVZ × fator
            // Zebuíno MNC: 1.0000, MC: 1.1940, Fêmea: 1.2861
            // CruzadoCorte MNC: 0.9232, MC: 1.0726, Fêmea: 1.2398
            // CruzadoLeite MNC: 0.8393, MC: 0.9760, Fêmea: 1.1280
            $coefsPCVZeq = [
                $zebuino->id      => [$MNC->id => 1.0000, $MC->id => 1.1940, $F->id => 1.2861],
                $cruzadoCorte->id => [$MNC->id => 0.9232, $MC->id => 1.0726, $F->id => 1.2398],
                $cruzadoLeite->id => [$MNC->id => 0.8393, $MC->id => 0.9760, $F->id => 1.1280],
            ];

            foreach ($coefsPCVZeq as $racaId => $cats) {
                foreach ($cats as $catId => $fator) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'PCVZeq', 'kg', 'linear', 'BR-CORTE 2016',
                        $fator, 1.0, 0.0, 'PCVZ',
                        "PCVZeq = {$fator} × PCVZ"
                    );
                }
            }

            // ── ELm — igual para todos ────────────────────────────────────────
            // ELm = 0.075 × PCVZ^0.75 (BR-CORTE 2016)
            foreach ([$zebuino->id, $cruzadoCorte->id, $cruzadoLeite->id] as $racaId) {
                foreach ([$MNC->id, $MC->id, $F->id] as $catId) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'ELm', 'Mcal/dia', 'exponencial', 'BR-CORTE 2016',
                        0.075, 0.75, 0.0, 'PCVZ',
                        'ELm = 0.075 × PCVZ^0.75'
                    );
                }
            }

            // ── ELg por raça, categoria e sistema ────────────────────────────
            // ELg = coef_a × PCVZeq^0.75 × GPCVZ^0.9116 (confinamento)
            // ELg = coef_a × PCVZeq^0.75 × GPCVZ^0.4775 (pasto)
            $coef_b_ELg = $isPasto ? 0.4775 : 0.9116;

            $coefsELg = $isPasto ? [
                // Pasto — coef_a BR-CORTE 2016
                $zebuino->id      => [$MNC->id => 0.0472, $MC->id => 0.0560, $F->id => 0.0684],
                $cruzadoCorte->id => [$MNC->id => 0.0472, $MC->id => 0.0560, $F->id => 0.0684],
                $cruzadoLeite->id => [$MNC->id => 0.0472, $MC->id => 0.0560, $F->id => 0.0684],
            ] : [
                // Confinamento — coef_a BR-CORTE 2016
                $zebuino->id      => [$MNC->id => 0.0553, $MC->id => 0.0461, $F->id => 0.0461],
                $cruzadoCorte->id => [$MNC->id => 0.0553, $MC->id => 0.0441, $F->id => 0.0553],
                $cruzadoLeite->id => [$MNC->id => 0.0553, $MC->id => 0.0400, $F->id => 0.0502],
            ];

            foreach ($coefsELg as $racaId => $cats) {
                foreach ($cats as $catId => $coefA) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'ELg', 'Mcal/dia', 'exponencial', 'BR-CORTE 2016',
                        $coefA, $coef_b_ELg, 0.0, 'GPCVZ_PCVZeq',
                        "ELg = {$coefA} × PCVZeq^0.75 × GPCVZ^{$coef_b_ELg}"
                    );
                }
            }

            // ── CMS por raça e sistema ────────────────────────────────────────
            // CMS = coef_a × PV^0.75 × e^(coef_c × PV)
            $coef_c_CMS = $isPasto ? -0.00015 : -0.00012;

            $coefsCMS = $isPasto ? [
                $zebuino->id      => 0.081428,
                $cruzadoCorte->id => 0.084256,
                $cruzadoLeite->id => 0.075649,
            ] : [
                $zebuino->id      => 0.080698,
                $cruzadoCorte->id => 0.083501,
                $cruzadoLeite->id => 0.074972,
            ];

            foreach ($coefsCMS as $racaId => $coefA) {
                foreach ([$MNC->id, $MC->id, $F->id] as $catId) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'CMS', 'kg/dia', 'exponencial', 'BR-CORTE 2016',
                        $coefA, 0.75, $coef_c_CMS, 'PV',
                        "CMS = {$coefA} × PV^0.75 × e^({$coef_c_CMS} × PV)"
                    );
                }
            }

            // ── PDR por sistema ───────────────────────────────────────────────
            $pdrPct = $isPasto ? 65.49 : 62.03;
            foreach ([$zebuino->id, $cruzadoCorte->id, $cruzadoLeite->id] as $racaId) {
                foreach ([$MNC->id, $MC->id, $F->id] as $catId) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'PDR_pct', '%', 'linear', 'BR-CORTE 2016',
                        $pdrPct, 1.0, 0.0, 'PB',
                        "PDR = {$pdrPct}% da PB"
                    );
                }
            }
        }

        // ── Cag e Pg (Ca e P de ganho) — variam por raça ─────────────────────
        // Cag = coef_a × GPCVZ (g/dia)
        // Pg  = coef_a × GPCVZ (g/dia)
        // Fonte: tabela minerais BR-CORTE 2016 — para GPCVZ=0.48
        // Zebuíno: Cag=7.744 → coef=16.13, Pg=3.698 → coef=7.70
        // Cruzado: Cag=9.417 → coef=19.62, Pg=4.473 → coef=9.32
        $coefsCag = [
            $zebuino->id      => 16.13,
            $cruzadoCorte->id => 19.62,
            $cruzadoLeite->id => 19.62,
        ];
        $coefsPg = [
            $zebuino->id      => 7.70,
            $cruzadoCorte->id => 9.32,
            $cruzadoLeite->id => 9.32,
        ];

        foreach ([$pasto, $confinamento] as $sistema) {
            foreach ($coefsCag as $racaId => $coefA) {
                foreach ([$MNC->id, $MC->id, $F->id] as $catId) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'Cag', 'g/dia', 'linear', 'BR-CORTE 2016',
                        $coefA, 1.0, 0.0, 'GPCVZ',
                        "Ca ganho = {$coefA} × GPCVZ"
                    );
                }
            }
            foreach ($coefsPg as $racaId => $coefA) {
                foreach ([$MNC->id, $MC->id, $F->id] as $catId) {
                    $coefs[] = $this->m(
                        $bovino->id, $racaId, $catId, $sistema->id,
                        'Pg', 'g/dia', 'linear', 'BR-CORTE 2016',
                        $coefA, 1.0, 0.0, 'GPCVZ',
                        "P ganho = {$coefA} × GPCVZ"
                    );
                }
            }
        }

        DB::table('coeficientes_nutricionais')->insert($coefs);
    }

    private function m(
        string $eId, string $rId, string $cId, string $sId,
        string $nutriente, string $unidade, string $tipo,
        string $ref, float $a, float $b, float $c,
        string $var, string $obs = ''
    ): array {
        return [
            'id'            => Str::uuid(),
            'especie_id'    => $eId,
            'raca_id'       => $rId,
            'categoria_id'  => $cId,
            'sistema_id'    => $sId,
            'nutriente'     => $nutriente,
            'unidade'       => $unidade,
            'formula_tipo'  => $tipo,
            'referencia'    => $ref,
            'coef_a'        => $a,
            'coef_b'        => $b,
            'coef_c'        => $c,
            'variavel_base' => $var,
            'observacao'    => $obs,
            'ativo'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
