<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->char('usuario_id', 36);
            $table->char('rol_id', 36);
            $table->primary(['usuario_id','rol_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('rol_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        // Backfill: copiar rol_id existente como rol primario al pivot
        DB::statement("
            INSERT INTO usuario_rol (usuario_id, rol_id)
            SELECT id, rol_id FROM usuarios WHERE rol_id IS NOT NULL
        ");
    }

    public function down(): void {
        Schema::dropIfExists('usuario_rol');
    }
};
