<?php

namespace App\Http\Controllers\Api\Clima;

use App\Http\Controllers\Controller;
use App\Services\ClimaService;
use Illuminate\Http\Request;

class ClimaController extends Controller
{
    public function __construct(
        private ClimaService $climaService
    ) {}

    public function anual(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {

            $dados = $this->climaService->buscarDadosAnuais(
                $request->latitude,
                $request->longitude
            );

            return response()->json([
                'success' => true,
                'data' => $dados,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
