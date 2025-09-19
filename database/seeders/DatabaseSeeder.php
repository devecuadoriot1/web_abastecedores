<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Organizacion;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Organización base
        $org = Organizacion::firstOrCreate(
            ['ruc' => '9999999999'],
            [
                'id'     => (string) Str::uuid(),
                'nombre' => 'GAD Municipal de Portoviejo',
                'estado' => 'ACTIVA',
            ]
        );

        // 2) Definición de roles
        $rolesDef = [
            ['nombre' => 'Superadministrador',    'slug' => 'superadmin',  'is_system' => true],
            ['nombre' => 'Admin de Organización', 'slug' => 'admin_org',   'is_system' => true],
            ['nombre' => 'Planificador',          'slug' => 'planificador'],
            ['nombre' => 'Autorizador',           'slug' => 'autorizador'],
            ['nombre' => 'Despachador',           'slug' => 'despachador'],
            ['nombre' => 'Conductor',             'slug' => 'conductor'],
            ['nombre' => 'Auditor',               'slug' => 'auditor'],
        ];

        // 2.1) Detección de columnas opcionales
        $rolesHaveOrg      = Schema::hasColumn('roles', 'org_id');
        $rolesHaveIsSystem = Schema::hasColumn('roles', 'is_system');

        // 2.2) Crear/asegurar roles
        $roles = collect();
        foreach ($rolesDef as $r) {
            $attrs = ['slug' => $r['slug']];
            if ($rolesHaveOrg) {
                $attrs['org_id'] = $org->id;
            }

            $values = [
                'id'     => (string) Str::uuid(),
                'nombre' => $r['nombre'],
            ];
            if ($rolesHaveOrg) {
                $values['org_id'] = $org->id;
            }
            if ($rolesHaveIsSystem) {
                $values['is_system'] = $r['is_system'] ?? false;
            }

            $role = Rol::firstOrCreate($attrs, $values);
            $roles->put($r['slug'], $role->id);
        }

        // 3) Usuario superadmin
        $usuariosHaveRolId = Schema::hasColumn('usuarios', 'rol_id');

        // Rol de respaldo para campo legado "rol_id" (si existe y es NOT NULL)
        $fallbackRolId = $roles->get('admin_org')
            ?? $roles->get('superadmin')
            ?? $roles->first();

        $userAttrs = ['email' => 'davidclaudio5000@gmail.com'];
        $userValues = [
            'id'                => (string) Str::uuid(),
            'org_id'            => $org->id,
            'nombre'            => 'SuperAdmin',
            'password_hash'     => Hash::make('Admin123'), // cámbialo luego
            'estado'            => 'ACTIVO',
            'is_superadmin'     => true,
            'email_verified_at' => now(),
        ];
        if ($usuariosHaveRolId && $fallbackRolId) {
            // Satisface restricción NOT NULL si aún existe usuarios.rol_id
            $userValues['rol_id'] = $fallbackRolId;
        }

        $user = Usuario::firstOrCreate($userAttrs, $userValues);

        // 4) Asignar rol "superadmin" por pivot (multi-rol)
        $superadminId = $roles->get('superadmin');
        if ($superadminId) {
            if (method_exists($user, 'roles')) {
                $user->roles()->syncWithoutDetaching([$superadminId]);
            } else {
                // Fallback si aún no definiste la relación en el modelo
                DB::table('rol_usuario')->updateOrInsert(
                    ['usuario_id' => $user->id, 'rol_id' => $superadminId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
