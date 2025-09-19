<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'org_id'  => $this->org_id,
            'nombre'  => $this->nombre,
            'email'   => $this->email,
            'verified'=> $this->email_verified_at ? true : false,
            'telefono'=> $this->telefono,
            'estado'  => $this->estado,
            'roles'   => $this->roles->map(fn($r) => [
                'id' => $r->id,
                'slug' => $r->slug,
                'nombre' => $r->nombre,
            ])->values(),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
