<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FazendeiroPublicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'username' => $this->username,

            'localizacao' => [
                'cidade' => $this->fazendeiroProfile?->location_city,
                'estado' => $this->fazendeiroProfile?->location_state,
            ],

            'fazendas' => $this->whenLoaded('fazendas', function () {
                return $this->fazendas->map(fn ($fazenda) => [
                    'id'             => $fazenda->id,
                    'name'           => $fazenda->name,
                    'area_hectares'  => $fazenda->area_hectares,
                    'city'           => $fazenda->city,
                    'state'          => $fazenda->state,
                ]);
            }),
        ];
    }
}
