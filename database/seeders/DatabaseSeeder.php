<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Organizacion;
use App\Models\Rol;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organizacion::create([
            'id'     => (string) Str::uuid(),
            'nombre' => 'GAD Municipal de Portoviejo',
            'ruc'    => '9999999999',
            'estado' => 'ACTIVA',
        ]);

        $rolAdmin = Rol::create([
            'id'     => (string) Str::uuid(),
            'org_id' => $org->id,
            'nombre' => 'Administrador',
            'slug'   => 'admin',
        ]);

        Usuario::create([
            'id'            => (string) Str::uuid(),
            'org_id'        => $org->id,
            'rol_id'        => $rolAdmin->id,
            'nombre'        => 'Admin',
            'email'         => 'davidclaudio5000@gmail.com',
            'password_hash' => Hash::make('Admin123'),
            'estado'        => 'ACTIVO',
            'is_superadmin' => true,
        ]);
    }
}
