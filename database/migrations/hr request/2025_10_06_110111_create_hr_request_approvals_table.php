<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_request_id')
                ->constrained('hr_requests')
                ->cascadeOnDelete();
            $table->foreignId('approval_flow_id')
                ->constrained('approval_flows')
                ->restrictOnDelete();
            $table->foreignId('approver_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->integer('status_id')->index();
            $table->text('description')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_request_approvals');
    }
};
