<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('usuarios', function (Blueprint $table) {
            // Asegura email_verified_at si no existe (tu create_usuarios no lo incluía)
            if (!Schema::hasColumn('usuarios', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('usuarios', 'telefono')) {
                $table->string('telefono')->nullable()->after('email');
            }
            if (!Schema::hasColumn('usuarios', 'ultimo_login_at')) {
                $table->timestamp('ultimo_login_at')->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('usuarios', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('ultimo_login_at');
            }
            if (!Schema::hasColumn('usuarios', 'must_reset_password')) {
                $table->boolean('must_reset_password')->default(true)->after('mfa_enabled');
            }
            if (!Schema::hasColumn('usuarios', 'last_password_change_at')) {
                $table->timestamp('last_password_change_at')->nullable()->after('must_reset_password');
            }

            // Índice compuesto útil para listados (sin email para no exceder key length)
            // Nombre único para no colisionar con idx_usuarios_org / idx_usuarios_rol existentes.
            $table->index(['org_id', 'rol_id'], 'idx_usuarios_org_rol_combo');
        });
    }

    public function down(): void {
        Schema::table('usuarios', function (Blueprint $table) {
            // Quitar índice compuesto (sin DBAL, usamos ALTER TABLE)
            try {
                DB::statement('ALTER TABLE `usuarios` DROP INDEX `idx_usuarios_org_rol_combo`');
            } catch (\Throwable $e) {
                // si no existe, no pasa nada
            }

            // Eliminar columnas que agregamos (si existen)
            foreach (['telefono','ultimo_login_at','mfa_enabled','must_reset_password','last_password_change_at'] as $col) {
                if (Schema::hasColumn('usuarios', $col)) {
                    $table->dropColumn($col);
                }
            }

            // No tocamos email_verified_at en down para no romper otros flujos.
        });
    }
};
