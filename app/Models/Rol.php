<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\BelongsToOrg;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasUuid, BelongsToOrg;

    protected $table = 'roles';
    protected $fillable = ['org_id','nombre','slug'];
    public $timestamps = true;
}
