<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('org_id');
            $table->uuid('actor_user_id');
            $table->string('target_type');
            $table->uuid('target_id');
            $table->string('action', 100);
            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('extra')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['org_id','action','created_at'], 'idx_audit_org_action_ts');
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
