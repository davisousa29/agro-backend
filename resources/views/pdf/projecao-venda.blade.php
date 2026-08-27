<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $projecao->nome }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            padding: 32px;
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #2D6A4F;
        }

        .header-titulo {
            font-size: 20px;
            font-weight: bold;
            color: #2D6A4F;
        }

        .header-subtitulo {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }

        .header-data {
            font-size: 11px;
            color: #666;
            text-align: right;
        }

        .nome-projecao {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .secao {
            margin-bottom: 20px;
        }

        .secao-titulo {
            font-size: 13px;
            font-weight: bold;
            color: #2D6A4F;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e0e0e0;
        }

        .resumo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .resumo-card {
            background: #F0FAF5;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            border: 1px solid #d8f3dc;
        }

        .resumo-card-valor {
            font-size: 16px;
            font-weight: bold;
            color: #2D6A4F;
        }

        .resumo-card-label {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .config-label {
            color: #666;
        }

        .config-valor {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background: #2D6A4F;
            color: white;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        thead th:last-child,
        thead th:nth-child(3),
        thead th:nth-child(4) {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        tbody tr:hover {
            background: #f0faf5;
        }

        tbody td {
            padding: 7px 10px;
            font-size: 11px;
            border-bottom: 1px solid #efefef;
        }

        tbody td:last-child,
        tbody td:nth-child(3),
        tbody td:nth-child(4) {
            text-align: right;
        }

        .td-prenhez-sim {
            color: #2D6A4F;
            font-weight: bold;
        }

        .td-prenhez-nao {
            color: #999;
        }

        tfoot tr {
            background: #F0FAF5;
            font-weight: bold;
        }

        tfoot td {
            padding: 8px 10px;
            font-size: 12px;
            border-top: 2px solid #2D6A4F;
        }

        tfoot td:last-child {
            text-align: right;
            color: #2D6A4F;
            font-size: 14px;
        }

        .contrato-box {
            background: #F0FAF5;
            border: 1px solid #95D5B2;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
        }

        .contrato-nome {
            font-weight: bold;
            color: #1a1a1a;
        }

        .contrato-fazendeiro {
            color: #666;
            font-size: 11px;
            margin-top: 2px;
        }

        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-assinatura {
            text-align: center;
        }

        .footer-linha {
            width: 200px;
            border-top: 1px solid #1a1a1a;
            margin-bottom: 4px;
        }

        .footer-label {
            font-size: 10px;
            color: #666;
        }

        .footer-sistema {
            font-size: 10px;
            color: #999;
            text-align: right;
        }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div>
        <div class="header-titulo">Colchete</div>
        <div class="header-subtitulo">Projeção de Valor de Venda</div>
    </div>
    <div class="header-data">
        Emitido em: {{ now()->format('d/m/Y H:i') }}<br>
        Responsável: {{ $projecao->criador->name ?? '—' }}
    </div>
</div>

{{-- Nome da projeção --}}
<div class="nome-projecao">{{ $projecao->nome }}</div>

{{-- Resumo em cards --}}
<div class="secao">
    <div class="secao-titulo">Resumo</div>
    <div class="resumo-grid">
        <div class="resumo-card">
            <div class="resumo-card-valor">{{ $projecao->total_animais }}</div>
            <div class="resumo-card-label">Total de animais</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-card-valor">{{ $projecao->total_vazias }}</div>
            <div class="resumo-card-label">Vazias</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-card-valor">{{ $projecao->total_prenhas }}</div>
            <div class="resumo-card-label">Prenhas</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-card-valor">
                R$ {{ number_format($projecao->valor_total, 2, ',', '.') }}
            </div>
            <div class="resumo-card-label">Valor total</div>
        </div>
    </div>
</div>

{{-- Configuração --}}
<div class="secao">
    <div class="secao-titulo">Configuração</div>
    <div class="config-grid">
        <div class="config-item">
            <span class="config-label">Modalidade</span>
            <span class="config-valor">{{ $modalidadeLabel }}</span>
        </div>
        <div class="config-item">
            <span class="config-label">Preço unitário</span>
            <span class="config-valor">R$ {{ number_format($projecao->preco_unitario, 2, ',', '.') }}</span>
        </div>
        @if($projecao->total_peso_kg > 0)
            <div class="config-item">
                <span class="config-label">Peso total</span>
                <span class="config-valor">{{ number_format($projecao->total_peso_kg, 0, ',', '.') }} kg</span>
            </div>
        @endif
        @if($projecao->total_arrobas)
            <div class="config-item">
                <span class="config-label">Total arrobas</span>
                <span class="config-valor">{{ number_format($projecao->total_arrobas, 3, ',', '.') }} @</span>
            </div>
        @endif
        @if($projecao->media_peso_vazias)
            <div class="config-item">
                <span class="config-label">Média peso vazias</span>
                <span class="config-valor">{{ number_format($projecao->media_peso_vazias, 1, ',', '.') }} kg</span>
            </div>
        @endif
    </div>
</div>

{{-- Tabela de animais --}}
@if($projecao->animais->count() > 0)
    <div class="secao">
        <div class="secao-titulo">Animais</div>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>N° Animal</th>
                <th>Prenhez</th>
                <th>Peso (kg)</th>
                @if($projecao->modalidade === 'arroba')
                    <th style="text-align:right">Arrobas (@)</th>
                @endif
                <th>Qtd</th>
                <th>Valor Unit.</th>
                <th>Valor Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($projecao->animais as $index => $animal)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $animal->numero_animal ?? '—' }}</td>
                    <td class="{{ $animal->prenhez ? 'td-prenhez-sim' : 'td-prenhez-nao' }}">
                        {{ $animal->prenhez ? 'Sim' : 'Não' }}
                    </td>
                    <td>{{ $animal->peso_kg ? number_format($animal->peso_kg, 0, ',', '.') : '—' }}</td>
                    @if($projecao->modalidade === 'arroba')
                        <td style="text-align:right">{{ $animal->arrobas ? number_format($animal->arrobas, 3, ',', '.') : '—' }}</td>
                    @endif
                    <td>{{ $animal->quantidade }}</td>
                    <td>R$ {{ number_format($animal->valor_unitario, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($animal->valor_total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="{{ $projecao->modalidade === 'arroba' ? 5 : 4 }}">Total</td>
                <td>{{ $projecao->total_animais }}</td>
                <td></td>
                <td>R$ {{ number_format($projecao->valor_total, 2, ',', '.') }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
@endif

{{-- Contrato vinculado --}}
@if($projecao->contrato)
    <div class="secao">
        <div class="secao-titulo">Contrato vinculado</div>
        <div class="contrato-box">
            <div>
                <div class="contrato-nome">{{ $projecao->contrato->fazenda->name ?? '—' }}</div>
                <div class="contrato-fazendeiro">@{{ $projecao->contrato->fazendeiro->username ?? '—' }}</div>
            </div>
        </div>
    </div>
@endif

{{-- Footer --}}
<div class="footer">
    <div class="footer-assinatura">
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
