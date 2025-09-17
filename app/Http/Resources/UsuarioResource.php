<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'org_id'=> $this->org_id,
            'rol'   => $this->whenLoaded('rol', function () {
                return [
                    'id'   => $this->rol->id,
                    'nombre'=> $this->rol->nombre,
                    'slug' => $this->rol->slug,
                ];
            }),
            'nombre'=> $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'estado'=> $this->estado,
            'email_verified_at' => optional($this->email_verified_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
