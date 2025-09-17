<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizaciones', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('nombre');
            $table->string('ruc', 32)->nullable();
            $table->enum('estado', ['ACTIVA','INACTIVA'])->default('ACTIVA');
            $table->timestamps();
            $table->index('estado', 'idx_org_estado');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('organizaciones');
    }
};
