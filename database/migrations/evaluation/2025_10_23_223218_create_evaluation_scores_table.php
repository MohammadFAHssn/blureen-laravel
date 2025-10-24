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
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluatee_id')->constrained('evaluatees')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('evaluation_questions')->restrictOnDelete();
            $table->unique(['evaluatee_id', 'question_id']);
            $table->unsignedTinyInteger('score')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
    }
};
