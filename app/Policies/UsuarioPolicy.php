<?php

namespace App\Policies;

use App\Models\Usuario;

class UsuarioPolicy
{
    public function viewAny(Usuario $actor): bool
    {
        return $actor->is_superadmin || $actor->tokenCan('org.members.read');
    }

    public function create(Usuario $actor): bool
    {
        return $actor->is_superadmin || $actor->tokenCan('org.members.create');
    }

    public function updateRole(Usuario $actor, Usuario $target): bool
    {
        if ($actor->id === $target->id) return false;
        if ($actor->is_superadmin) return true;
        return $actor->org_id === $target->org_id && $actor->tokenCan('org.members.update');
    }

    public function updateStatus(Usuario $actor, Usuario $target): bool
    {
        if ($actor->is_superadmin) return true;
        return $actor->org_id === $target->org_id && $actor->tokenCan('org.members.update');
    }

    public function delete(Usuario $actor, Usuario $target): bool
    {
        if ($actor->is_superadmin) return true;
        return $actor->org_id === $target->org_id && $actor->tokenCan('org.members.delete');
    }
}
