<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrg
{
    protected static function bootBelongsToOrg(): void
    {
        static::creating(function ($model) {
            if (empty($model->org_id) && Auth::check()) {
                $model->org_id = Auth::user()->org_id;
            }
        });

        static::addGlobalScope('org', function (Builder $builder) {
            // Si el usuario autenticado no es "superadmin", filtrar por su org
            $user = Auth::user();
            if ($user && !$user->is_superadmin) {
                $builder->where($builder->getModel()->getTable().'.org_id', $user->org_id);
            }
        });
    }
}
