<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'org_id'    => $this->org_id,
            'rol_id'    => $this->rol_id,
            'nombre'    => $this->nombre,
            'email'     => $this->email,
            'estado'    => $this->estado,
            'superadmin'=> (bool) $this->is_superadmin,
            'created_at'=> optional($this->created_at)->toIso8601String(),
            'updated_at'=> optional($this->updated_at)->toIso8601String(),
        ];
    }
}
