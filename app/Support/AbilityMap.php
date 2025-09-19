<?php

namespace App\Support;

use App\Models\Usuario;

class AbilityMap
{
    public static function forUser(Usuario $u): array
    {
        // SuperAdmin
        if ($u->is_superadmin) {
            return config('abilities.superadmin', ['*']);
        }

        $byRole = config('abilities.by_role', []);
        $abilities = [];

        // union de abilities de *todos* los roles del usuario
        $slugs = $u->roles()->pluck('slug')->all();
        foreach ($slugs as $slug) {
            $abilities = array_merge($abilities, $byRole[$slug] ?? []);
        }

        // si además quieres considerar el "rol primario" legacy:
        if ($u->rol && !in_array($u->rol->slug, $slugs, true)) {
            $abilities = array_merge($abilities, $byRole[$u->rol->slug] ?? []);
        }

        // únicos y reindexados
        return array_values(array_unique($abilities));
    }
}
