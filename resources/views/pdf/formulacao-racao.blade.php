<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $programa->nome }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; padding: 32px; background: #fff; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #2D6A4F; }
        .header-titulo { font-size: 20px; font-weight: bold; color: #2D6A4F; }
        .header-subtitulo { font-size: 11px; color: #666; margin-top: 4px; }
        .header-data { font-size: 11px; color: #666; text-align: right; }
        .nome { font-size: 16px; font-weight: bold; margin-bottom: 20px; }
        .secao { margin-bottom: 20px; }
        .secao-titulo { font-size: 13px; font-weight: bold; color: #2D6A4F; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e0e0e0; }
        .resumo-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
        .resumo-card { background: #F0FAF5; border-radius: 8px; padding: 12px; text-align: center; border: 1px solid #d8f3dc; }
        .resumo-card-valor { font-size: 16px; font-weight: bold; color: #2D6A4F; }
        .resumo-card-label { font-size: 10px; color: #666; margin-top: 2px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .info-item { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
        .info-label { color: #666; }
        .info-valor { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead tr { background: #2D6A4F; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        thead th:last-child, thead th:nth-child(3), thead th:nth-child(4) { text-align: right; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        tbody td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #efefef; }
        tbody td:last-child, tbody td:nth-child(3), tbody td:nth-child(4) { text-align: right; }
        tfoot tr { background: #F0FAF5; font-weight: bold; }
        tfoot td { padding: 8px 10px; font-size: 12px; border-top: 2px solid #2D6A4F; }
        tfoot td:last-child { text-align: right; color: #2D6A4F; font-size: 14px; }
        .balanco-item { margin-bottom: 12px; }
        .balanco-header { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .balanco-label { font-weight: bold; font-size: 11px; }
        .balanco-badge { font-size: 10px; padding: 2px 8px; border-radius: 10px; }
        .badge-ok { background: #D8F3DC; color: #2D6A4F; }
        .badge-baixo { background: #FFE3E3; color: #FA5252; }
        .badge-alto { background: #FFF3BF; color: #FAB005; }
        .balanco-bar { height: 6px; background: #e0e0e0; border-radius: 3px; margin-bottom: 2px; overflow: hidden; }
        .balanco-fill { height: 100%; border-radius: 3px; }
        .balanco-valores { font-size: 10px; color: #666; }
        .contrato-box { background: #F0FAF5; border: 1px solid #95D5B2; border-radius: 8px; padding: 12px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: flex-end; }
        .footer-linha { width: 200px; border-top: 1px solid #1a1a1a; margin-bottom: 4px; }
        .footer-label { font-size: 10px; color: #666; }
        .footer-sistema { font-size: 10px; color: #999; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="header-titulo">Colchete</div>
        <div class="header-subtitulo">Formulação de Ração — BR-CORTE 2016</div>
    </div>
    <div class="header-data">
        Emitido em: {{ now()->format('d/m/Y H:i') }}<br>
        Responsável: {{ $programa->criador->name ?? '—' }}
    </div>
</div>

<div class="nome">{{ $programa->nome }}</div>

{{-- Resumo --}}
<div class="secao">
    <div class="secao-titulo">Resumo</div>
    <div class="resumo-grid">
        <div class="resumo-card">
            <div class="resumo-card-valor">{{ $programa->quantidade_animais }}</div>
            <div class="resumo-card-label">animais</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-card-valor">{{ number_format($programa->exig_cms_kg, 2, ',', '.') }}</div>
            <div class="resumo-card-label">kg MS/animal/dia</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-card-valor">R$ {{ number_format($programa->custo_animal_dia, 2, ',', '.') }}</div>
            <div class="resumo-card-label">custo/animal/dia</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-card-valor">R$ {{ number_format($programa->custo_animal_dia * $programa->quantidade_animais, 2, ',', '.') }}</div>
            <div class="resumo-card-label">custo total/dia</div>
        </div>
    </div>
</div>

{{-- Dados do animal --}}
<div class="secao">
    <div class="secao-titulo">Dados do animal</div>
    <div class="info-grid">
        <div class="info-item"><span class="info-label">Espécie</span><span class="info-valor">{{ $programa->especie->nome }}</span></div>
        <div class="info-item"><span class="info-label">Raça</span><span class="info-valor">{{ $programa->raca->nome }}</span></div>
        <div class="info-item"><span class="info-label">Categoria</span><span class="info-valor">{{ $programa->categoria->nome }}</span></div>
        <div class="info-item"><span class="info-label">Sistema</span><span class="info-valor">{{ $programa->sistema->nome }}</span></div>
        <div class="info-item"><span class="info-label">Peso inicial</span><span class="info-valor">{{ $programa->peso_inicial_kg }} kg</span></div>
        <div class="info-item"><span class="info-label">Peso final</span><span class="info-valor">{{ $programa->peso_final_kg }} kg</span></div>
        <div class="info-item"><span class="info-label">GMD desejado</span><span class="info-valor">{{ $programa->gmd_kg }} kg/dia</span></div>
        <div class="info-item"><span class="info-label">Referência</span><span class="info-valor">{{ $programa->referencia_nutricional }}</span></div>
    </div>
</div>

{{-- Exigências --}}
<div class="secao">
    <div class="secao-titulo">Exigências nutricionais</div>
    <div class="info-grid">
        <div class="info-item"><span class="info-label">CMS</span><span class="info-valor">{{ $programa->exig_cms_kg }} kg/dia</span></div>
        <div class="info-item"><span class="info-label">ELm</span><span class="info-valor">{{ $programa->exig_elm_mcal }} Mcal/dia</span></div>
        <div class="info-item"><span class="info-label">ELg</span><span class="info-valor">{{ $programa->exig_elg_mcal }} Mcal/dia</span></div>
        <div class="info-item"><span class="info-label">PB</span><span class="info-valor">{{ $programa->exig_pb_g }} g/dia</span></div>
        <div class="info-item"><span class="info-label">Ca</span><span class="info-valor">{{ $programa->exig_ca_g }} g/dia</span></div>
        <div class="info-item"><span class="info-label">P</span><span class="info-valor">{{ $programa->exig_p_g }} g/dia</span></div>
    </div>
</div>

{{-- Ingredientes --}}
@if($programa->ingredientes->count() > 0)
    <div class="secao">
        <div class="secao-titulo">Composição da dieta</div>
        <table>
            <thead>
            <tr>
                <th>Ingrediente</th>
                <th>Tipo</th>
                <th>Proporção</th>
                <th>Consumo MS (kg/dia)</th>
                <th>Consumo MN (kg/dia)</th>
                <th>Custo/dia</th>
            </tr>
            </thead>
            <tbody>
            @foreach($programa->ingredientes as $ing)
                <tr>
                    <td>{{ $ing->ingrediente->nome }}</td>
                    <td>{{ str_replace('_', ' ', $ing->tipo) }}</td>
                    <td>{{ number_format($ing->proporcao_pct, 0) }}%</td>
                    <td>{{ number_format($ing->consumo_ms_kg, 3, ',', '.') }}</td>
                    <td>{{ number_format($ing->consumo_mn_kg, 3, ',', '.') }}</td>
                    <td>R$ {{ number_format($ing->custo_animal_dia, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">Total</td>
                <td>R$ {{ number_format($programa->custo_animal_dia, 2, ',', '.') }}</td>
            </tr>
            </tfoot>
        </table>
    </div>

    {{-- Balanço nutricional --}}
    <div class="secao">
        <div class="secao-titulo">Balanço nutricional</div>
        @php
            $fornELm = $programa->ingredientes->sum('contrib_elm_mcal');
            $fornPB  = $programa->ingredientes->sum('contrib_pb_g');
            $fornCa  = $programa->ingredientes->sum('contrib_ca_g');
            $fornP   = $programa->ingredientes->sum('contrib_p_g');

            function statusBalanco($forn, $exig) {
                if ($exig <= 0) return 'ok';
                $pct = ($forn / $exig) * 100;
                if ($pct >= 95 && $pct <= 115) return 'ok';
                return $pct < 95 ? 'baixo' : 'alto';
            }

            function corBalanco($status) {
                return match($status) {
                    'ok'    => '#2D6A4F',
                    'baixo' => '#FA5252',
                    'alto'  => '#FAB005',
                    default => '#999',
                };
            }

            $itens = [
                ['label' => 'Energia Líquida Mantença', 'forn' => $fornELm, 'exig' => $programa->exig_elm_mcal, 'unidade' => 'Mcal'],
                ['label' => 'Proteína Bruta',            'forn' => $fornPB,  'exig' => $programa->exig_pb_g,    'unidade' => 'g'],
                ['label' => 'Cálcio',                    'forn' => $fornCa,  'exig' => $programa->exig_ca_g,    'unidade' => 'g'],
                ['label' => 'Fósforo',                   'forn' => $fornP,   'exig' => $programa->exig_p_g,     'unidade' => 'g'],
            ];
        @endphp

        @foreach($itens as $item)
            @php
                $status = statusBalanco($item['forn'], $item['exig']);
                $cor = corBalanco($status);
                $pct = $item['exig'] > 0 ? min(($item['forn'] / $item['exig']) * 100, 100) : 0;
                $label = match($status) { 'ok' => '✓ Atendido', 'baixo' => '↓ Abaixo', 'alto' => '↑ Acima' };
                $badgeClass = match($status) { 'ok' => 'badge-ok', 'baixo' => 'badge-baixo', 'alto' => 'badge-alto' };
            @endphp
            <div class="balanco-item">
                <div class="balanco-header">
                    <span class="balanco-label">{{ $item['label'] }}</span>
                    <span class="balanco-badge {{ $badgeClass }}">{{ $label }}</span>
                </div>
                <div class="balanco-bar">
                    <div class="balanco-fill" style="width: {{ $pct }}%; background: {{ $cor }};"></div>
                </div>
                <div class="balanco-valores">
                    {{ number_format($item['forn'], 2, ',', '.') }} {{ $item['unidade'] }} / {{ number_format($item['exig'], 2, ',', '.') }} {{ $item['unidade'] }}
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Contrato --}}
@if($programa->contrato)
    <div class="secao">
        <div class="secao-titulo">Contrato vinculado</div>
        <div class="contrato-box">
            <strong>{{ $programa->contrato->fazenda->name ?? '—' }}</strong>
            &nbsp;·&nbsp; @{{ $programa->contrato->fazendeiro->username ?? '—' }}
        </div>
    </div>
@endif

<div class="footer">
    <div>
        <div class="footer-linha"></div>
        <div class="footer-label">Responsável técnico</div>
    </div>
    <div class="footer-sistema">
        Gerado pelo Colchete<br>
        {{ now()->format('d/m/Y \à\s H:i') }}
    </div>
</div>

</body>
</html>
