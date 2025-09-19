<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Organizacion extends Model
{
    use HasUuid;

    protected $table = 'organizaciones';
    protected $fillable = ['nombre','descripcion','ruc','estado','direccion','telefono','email','web_url','logo','tipo','token','tiempo_api','fecha_caducidad_inicio','fecha_caducidad_fin'];
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
