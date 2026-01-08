<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('org_chart_node_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('org_chart_node_id')->constrained('org_chart_nodes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('role', ['primary', 'deputy'])->default('primary');

            $table->unique(['user_id', 'org_chart_node_id']);
            $table->index(['org_chart_node_id', 'role']);
            $table->index(['user_id', 'role']);

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_chart_node_users');
    }
};
