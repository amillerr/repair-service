<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('action', 50); // created, assigned, canceled, taken, completed
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->json('metadata')->nullable(); // extra data: master_id, etc.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_audit_logs');
    }
};
