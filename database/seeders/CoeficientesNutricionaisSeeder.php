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

        // Raças
        $zebuino      = DB::table('racas')->where('nome', 'Zebuíno')->first();
        $cruzadoCorte = DB::table('racas')->where('nome', 'Cruzado Corte')->first();
        $cruzadoLeite = DB::table('racas')->where('nome', 'Cruzado Leite')->first();

        // Categorias
        $machoNaoCastrado = DB::table('categorias_animais')->where('nome', 'Macho não castrado')->first();
        $machoCastrado    = DB::table('categorias_animais')->where('nome', 'Macho castrado')->first();
        $femea            = DB::table('categorias_animais')->where('nome', 'Fêmea')->first();

        // Sistemas
        $pasto        = DB::table('sistemas_producao')->where('nome', 'Pasto')->where('especie_id', $bovino->id)->first();
        $confinamento = DB::table('sistemas_producao')->where('nome', 'Confinamento')->where('especie_id', $bovino->id)->first();

        $coeficientes = [];

        // ─────────────────────────────────────────────────────────────────────
        // BR-CORTE 2016 — PASTO
        // Fonte: Planilha BR-CORTE 2016 (Benedeti et al., 2016)
        // Fórmulas: resultado = coef_a × variavel_base ^ coef_b + coef_c
        // ─────────────────────────────────────────────────────────────────────

        $combsPasto = [
            [$zebuino->id,      $machoNaoCastrado->id],
            [$zebuino->id,      $machoCastrado->id],
            [$zebuino->id,      $femea->id],
            [$cruzadoCorte->id, $machoNaoCastrado->id],
            [$cruzadoCorte->id, $machoCastrado->id],
            [$cruzadoCorte->id, $femea->id],
            [$cruzadoLeite->id, $machoNaoCastrado->id],
            [$cruzadoLeite->id, $machoCastrado->id],
            [$cruzadoLeite->id, $femea->id],
        ];

        // Coeficientes ELm (Energia Líquida Mantença) — Mcal/dia
        // ELm = 0.047 × PCVZeq^1  (igual para todas as combinações em pasto)
        foreach ($combsPasto as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $pasto->id,
                'ELm', 'Mcal/dia', 'linear', 'BR-CORTE 2016',
                0.047, 1.0, 0.0, 'PCVZeq',
                'ELm = 0.047 × PCVZeq'
            );
        }

        // Coeficientes PCVZ (Peso Corpo Vazio) — kg
        // PCVZ = PCJ × 0.851 (Pasto, igual para todos)
        foreach ($combsPasto as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $pasto->id,
                'PCVZ', 'kg', 'linear', 'BR-CORTE 2016',
                0.851, 1.0, 0.0, 'PCJ',
                'PCVZ = 0.851 × PCJ'
            );
        }

        // Coeficientes PCVZeq (Peso Corpo Vazio equivalente) — kg
        // PCVZeq = PCVZ^0.75
        foreach ($combsPasto as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $pasto->id,
                'PCVZeq', 'kg', 'exponencial', 'BR-CORTE 2016',
                1.0, 0.75, 0.0, 'PCVZ',
                'PCVZeq = PCVZ^0.75'
            );
        }

        // ELg (Energia Líquida Ganho) — Mcal/dia
        // Varia por raça e categoria — BR-CORTE 2016 Pasto
        $elgPasto = [
            // [raca_id, categoria_id, coef_a, coef_b]
            // Zebuíno Pasto
            [$zebuino->id, $machoNaoCastrado->id, 0.0557, 0.4775],
            [$zebuino->id, $machoCastrado->id,     0.0557, 0.4775],
            [$zebuino->id, $femea->id,             0.0557, 0.4775],
            // Cruzado Corte Pasto
            [$cruzadoCorte->id, $machoNaoCastrado->id, 0.0557, 0.4775],
            [$cruzadoCorte->id, $machoCastrado->id,    0.0557, 0.4775],
            [$cruzadoCorte->id, $femea->id,            0.0684, 0.4775],
            // Cruzado Leite Pasto
            [$cruzadoLeite->id, $machoNaoCastrado->id, 0.0557, 0.4775],
            [$cruzadoLeite->id, $machoCastrado->id,    0.0557, 0.4775],
            [$cruzadoLeite->id, $femea->id,            0.0684, 0.4775],
        ];

        foreach ($elgPasto as [$racaId, $catId, $a, $b]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $pasto->id,
                'ELg', 'Mcal/dia', 'exponencial', 'BR-CORTE 2016',
                $a, $b, 0.0, 'GPCVZ_PCVZeq',
                'ELg = a × PCVZeq^0.75 × GPCVZ^b'
            );
        }

        // CMS (Consumo Matéria Seca) — kg/dia
        // CMS = 0.024 × PV^0.75 × e^(-0.00015 × PV) (Pasto BR-CORTE 2016)
        foreach ($combsPasto as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $pasto->id,
                'CMS', 'kg/dia', 'exponencial', 'BR-CORTE 2016',
                0.024, 0.75, -0.00015, 'PV',
                'CMS = 0.024 × PV^0.75 × e^(-0.00015 × PV)'
            );
        }

        // PDR (Proteína Degradável no Rúmen) — % da PB
        // PDR = 65.49% da PB (Pasto BR-CORTE 2016, Cruzado)
        foreach ($combsPasto as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $pasto->id,
                'PDR_pct', '%', 'linear', 'BR-CORTE 2016',
                65.49, 1.0, 0.0, 'PB',
                'PDR = 65.49% da PB'
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // BR-CORTE 2016 — CONFINAMENTO
        // ─────────────────────────────────────────────────────────────────────

        $combsConf = [
            [$zebuino->id,      $machoNaoCastrado->id],
            [$zebuino->id,      $machoCastrado->id],
            [$zebuino->id,      $femea->id],
            [$cruzadoCorte->id, $machoNaoCastrado->id],
            [$cruzadoCorte->id, $machoCastrado->id],
            [$cruzadoCorte->id, $femea->id],
            [$cruzadoLeite->id, $machoNaoCastrado->id],
            [$cruzadoLeite->id, $machoCastrado->id],
            [$cruzadoLeite->id, $femea->id],
        ];

        // ELm Confinamento
        foreach ($combsConf as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $confinamento->id,
                'ELm', 'Mcal/dia', 'linear', 'BR-CORTE 2016',
                0.047, 1.0, 0.0, 'PCVZeq',
                'ELm = 0.047 × PCVZeq'
            );
        }

        // PCVZ Confinamento
        foreach ($combsConf as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $confinamento->id,
                'PCVZ', 'kg', 'linear', 'BR-CORTE 2016',
                0.891, 1.0, 0.0, 'PCJ',
                'PCVZ = 0.891 × PCJ (Confinamento)'
            );
        }

        // PCVZeq Confinamento
        foreach ($combsConf as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $confinamento->id,
                'PCVZeq', 'kg', 'exponencial', 'BR-CORTE 2016',
                1.0, 0.75, 0.0, 'PCVZ',
                'PCVZeq = PCVZ^0.75'
            );
        }

        // ELg Confinamento — varia por raça e categoria
        $elgConf = [
            [$zebuino->id,      $machoNaoCastrado->id, 0.0389, 0.9116],
            [$zebuino->id,      $machoCastrado->id,    0.0461, 0.9116],
            [$zebuino->id,      $femea->id,            0.0461, 0.9116],
            [$cruzadoCorte->id, $machoNaoCastrado->id, 0.0373, 0.9116],
            [$cruzadoCorte->id, $machoCastrado->id,    0.0441, 0.9116],
            [$cruzadoCorte->id, $femea->id,            0.0553, 0.9116],
            [$cruzadoLeite->id, $machoNaoCastrado->id, 0.0338, 0.9116],
            [$cruzadoLeite->id, $machoCastrado->id,    0.0400, 0.9116],
            [$cruzadoLeite->id, $femea->id,            0.0502, 0.9116],
        ];

        foreach ($elgConf as [$racaId, $catId, $a, $b]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $confinamento->id,
                'ELg', 'Mcal/dia', 'exponencial', 'BR-CORTE 2016',
                $a, $b, 0.0, 'GPCVZ_PCVZeq',
                'ELg = a × PCVZeq^0.75 × GPCVZ^b'
            );
        }

        // CMS Confinamento
        foreach ($combsConf as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $confinamento->id,
                'CMS', 'kg/dia', 'exponencial', 'BR-CORTE 2016',
                0.0245, 0.75, -0.00012, 'PV',
                'CMS = 0.0245 × PV^0.75 × e^(-0.00012 × PV)'
            );
        }

        // PDR Confinamento
        foreach ($combsConf as [$racaId, $catId]) {
            $coeficientes[] = $this->montar(
                $bovino->id, $racaId, $catId, $confinamento->id,
                'PDR_pct', '%', 'linear', 'BR-CORTE 2016',
                62.03, 1.0, 0.0, 'PB',
                'PDR = 62.03% da PB (Confinamento)'
            );
        }

        // Insere todos os coeficientes
        foreach ($coeficientes as $coef) {
            DB::table('coeficientes_nutricionais')->insert($coef);
        }
    }

    private function montar(
        string $especieId,
        string $racaId,
        string $categoriaId,
        string $sistemaId,
        string $nutriente,
        string $unidade,
        string $formulaTipo,
        string $referencia,
        float  $coefA,
        float  $coefB,
        float  $coefC,
        string $variavelBase,
        string $observacao = ''
    ): array {
        return [
            'id'            => Str::uuid(),
            'especie_id'    => $especieId,
            'raca_id'       => $racaId,
            'categoria_id'  => $categoriaId,
            'sistema_id'    => $sistemaId,
            'nutriente'     => $nutriente,
            'unidade'       => $unidade,
            'formula_tipo'  => $formulaTipo,
            'referencia'    => $referencia,
            'coef_a'        => $coefA,
            'coef_b'        => $coefB,
            'coef_c'        => $coefC,
            'variavel_base' => $variavelBase,
            'observacao'    => $observacao,
            'ativo'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
