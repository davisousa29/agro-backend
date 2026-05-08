<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ClimaService
{
    public function buscarDadosAnuais(float $latitude, float $longitude): array
    {
        $anoAnterior = now()->year - 1;

        $response = Http::timeout(30)->get(
            'https://climate-api.open-meteo.com/v1/climate',
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'start_date' => "{$anoAnterior}-01-01",
                'end_date' => "{$anoAnterior}-12-31",

                'models' => 'CMCC_CM2_VHR4',

                'daily' => implode(',', [
                    'temperature_2m_min',
                    'temperature_2m_max',
                    'precipitation_sum',
                ]),
            ]
        );

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        $daily = $response->json('daily');

        return $this->agruparPorMes($daily);
    }

    private function agruparPorMes(array $daily): array
    {
        $meses = [];

        foreach ($daily['time'] as $index => $date) {

            $mes = Carbon::parse($date)->month;

            if (!isset($meses[$mes])) {
                $meses[$mes] = [
                    'mes' => $mes,
                    'temp_min' => [],
                    'temp_max' => [],
                    'chuva' => 0,
                ];
            }

            $meses[$mes]['temp_min'][] = $daily['temperature_2m_min'][$index];

            $meses[$mes]['temp_max'][] = $daily['temperature_2m_max'][$index];

            $meses[$mes]['chuva'] += $daily['precipitation_sum'][$index];
        }

        return collect($meses)->map(function ($mes) {

            $tempMin = round(collect($mes['temp_min'])->avg(), 1);

            $tempMax = round(collect($mes['temp_max'])->avg(), 1);

            $chuva = round($mes['chuva'], 1);

            return [
                'mes' => $mes['mes'],
                'temperatura_minima' => $tempMin,
                'temperatura_maxima' => $tempMax,
                'precipitacao_mm' => $chuva,
                'classificacao' => $this->classificarPeriodo($chuva),
            ];
        })->values()->toArray();
    }

    private function classificarPeriodo(float $chuva): string
    {
        if ($chuva >= 150) {
            return 'chuva';
        }

        if ($chuva >= 50) {
            return 'transicao';
        }

        return 'seca';
    }
}
