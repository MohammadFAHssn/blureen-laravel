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
        Schema::create('evaluatees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('evaluator_id')->constrained('evaluators')->cascadeOnDelete();
            $table->unique(['user_id', 'evaluator_id']);
            $table->double('final_score')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluatees');
    }
};
