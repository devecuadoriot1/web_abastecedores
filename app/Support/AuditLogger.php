<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function log(
        string $orgId,
        string $actorUserId,
        Model $target,
        string $action,
        array $extra = [],
        ?Request $request = null
    ): void {
        DB::table('audit_logs')->insert([
            'id' => (string) Str::uuid(),
            'org_id' => $orgId,
            'actor_user_id' => $actorUserId,
            'target_type' => get_class($target),
            'target_id' => $target->getKey(),
            'action' => $action,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'extra' => empty($extra) ? null : json_encode($extra),
            'created_at' => now(),
        ]);
    }
}
