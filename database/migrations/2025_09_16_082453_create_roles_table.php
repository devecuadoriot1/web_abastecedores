<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('org_id', 36);
            $table->string('nombre', 100);
            $table->string('slug', 100);
            $table->timestamps();

            $table->unique(['org_id','slug'], 'uniq_roles_org_slug');
            $table->index('org_id', 'idx_roles_org');

            $table->foreign('org_id')->references('id')->on('organizaciones')
                  ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
