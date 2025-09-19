<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            // PK y FKs (UUID/CHAR(36))
            $table->uuid('id')->primary();
            $table->uuid('org_id');
            $table->uuid('rol_id'); // si luego migras a multi-rol, este podría salir y usar un pivot

            // Datos de cuenta
            $table->string('nombre', 150);
            $table->string('email', 191)->unique('uniq_usuarios_email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('telefono', 30)->nullable();

            // Seguridad / autenticación
            $table->string('password_hash', 255);
            $table->boolean('mfa_enabled')->default(false);
            $table->boolean('must_reset_password')->default(true);
            $table->timestamp('last_password_change_at')->nullable();
            $table->timestamp('ultimo_login_at')->nullable();
            $table->rememberToken();

            // Estado y metadatos
            $table->boolean('is_superadmin')->default(false);
            $table->enum('estado', ['ACTIVO','INACTIVO'])->default('INACTIVO');

            $table->timestamps();

            // Índices
            $table->index('org_id', 'idx_usuarios_org');
            $table->index('rol_id', 'idx_usuarios_rol');
            $table->index(['org_id', 'rol_id'], 'idx_usuarios_org_rol_combo');

            // FKs (requieren que las tablas existan antes de correr esta migración)
            $table->foreign('org_id')
                  ->references('id')->on('organizaciones')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('rol_id')
                  ->references('id')->on('roles')
                  ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
