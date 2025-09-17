<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('org_id', 36);
            $table->char('rol_id', 36);
            $table->string('nombre', 150);
            $table->string('email', 190)->unique('uniq_usuarios_email');
            $table->string('password_hash', 255);
            $table->boolean('is_superadmin')->default(false);
            $table->enum('estado', ['ACTIVO','INACTIVO'])->default('INACTIVO');
            $table->rememberToken();
            $table->timestamps();

            $table->index('org_id', 'idx_usuarios_org');
            $table->index('rol_id', 'idx_usuarios_rol');

            $table->foreign('org_id')->references('id')->on('organizaciones')
                  ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('rol_id')->references('id')->on('roles')
                  ->restrictOnDelete()->cascadeOnUpdate();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
